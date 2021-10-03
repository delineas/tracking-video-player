<template>
  <div class="player" :class="{ 'player--column': !areControlsInRow }">
    <div class="video" :class="{ 'video--full-width': !areControlsInRow }">
      <div class="video__container">
        <div id="player-video" class="video__container__iframe"></div>
      </div>
    </div>
    <div
      class="controls"
      :class="{ 'controls--full-width': !areControlsInRow }"
    >
      <div class="controls__wrapper">
        <div class="controls__container">
          <div class="controls__buttons">
            <button @click="toggleControls">
              <span v-if="areControlsInRow"><ColumnsH /></span
              ><span v-else><ColumnsV /></span>
            </button>
          </div>
          <ol class="controls__chapters">
            <li
              v-for="chapter in chapters"
              :key="chapter"
              @click="setVideoToChapter(chapter)"
              class="chapter"
              :class="{
                'chapter--active': activeChapter.title == chapter.title,
                'chapter--featured': chapter.featured,
              }"
            >
              <span class="chapter__timestamp">{{
                new Date(chapter.startTime * 1000).toISOString().substr(11, 8)
              }}</span>
              {{ chapter.title
              }}<span class="chapter__badge" v-if="chapter.starred">✴️</span><span class="chapter__link"
                ><a :href="`#${generateAnchorId(chapter.title)}`"><ShareIcon/></a></span
              >
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { config } from "../config";
import postPlayerProgress from "../utils/progress";
import { ref } from "@vue/reactivity";
import { onMounted, onUnmounted } from "@vue/runtime-core";
import Player from "@vimeo/player";
import ColumnsH from "../icons/ColumnsH.vue";
import ColumnsV from "../icons/ColumnsV.vue";
import ShareIcon from "../icons/ShareIcon.vue";

const props = defineProps({
  videoId: {
    type: [Number, String],
    required: true,
  },
  userId: {
    type: [Number, String],
    default: 0,
  },
  chapters: {
    type: Array,
    default: [],
  },
  serverTracking: {
    type: Boolean,
    default: false,
  },
});

const activeChapter = ref(props.chapters[0]);

let player;
let postPlayerInterval;
const areControlsInRow = ref(true);
const localStorageKey = `video-player-progress-${props.userId}-${props.videoId}`;
const localStorageKeyLastUpdate = `video-player-last-update-${props.userId}-${props.videoId}`;
const hasHash = window.location.hash.length > 0;

// onCreated
if (hasHash) {
  setTimeout(function () {
    window.scrollTo(0, 0);
  }, 500);
}

if(window.screen.width < 768) {
  areControlsInRow.value = false;
}


onMounted(() => {
  postPlayerInterval = setInterval(
    postPlayerProgress(
      props.serverTracking.value,
      localStorageKey,
      localStorageKeyLastUpdate
    ),
    10000
  );

  // Set initial progress update on 0
  localStorage.setItem(
    localStorageKeyLastUpdate,
    JSON.stringify({
      percent: 0,
    })
  );

  player = new Player("player-video", {
    id: props.videoId,
    width: "640",
  });

  player.on("timeupdate", function (event) {
    const playerProgress = {
      userId: props.userId,
      vid: props.videoId,
      percent: event.percent,
    };
    localStorage.setItem(localStorageKey, JSON.stringify(playerProgress));
    setActiveChapterFromProgress(event.seconds)(false);
  });

  const playerStorage = localStorage.getItem(localStorageKey);

  if (playerStorage) {
    const playerProgress = JSON.parse(playerStorage);
    setPlayerProgressCurrentTime(playerProgress.percent);
    return;
  }
  if (!playerStorage && props.serverTracking.value) {
    fetch(`${config.server_api_get_progress}/${props.userId}/${props.videoId}`)
      .then((response) => {
        if (response.status == 200) {
          return response.json();
        } else if (response.status === 404) {
          return Promise.reject("Error 404");
        } else {
          return Promise.reject("Some other error: " + response.status);
        }
      })
      .then((result) => {
        setPlayerProgressCurrentTime(result.percent);
      })
      .catch(console.log);
  }
});


onMounted(() => {
  if (hasHash) {
    setActiveChaptersFromHash();
  }
});

onUnmounted(() => clearInterval(postPlayerInterval));

const setActiveChaptersFromHash = () => {
  const activeChapterFromHash = props.chapters.find(
    (chapter) => generateAnchorId(chapter.title) == location.hash.substr(1)
  );
  if (activeChapterFromHash) {
    setVideoToChapter(activeChapterFromHash);
  }
};

const toggleControls = () => {
  areControlsInRow.value = !areControlsInRow.value;
};

const setVideoToChapter = (chapter) => {
  activeChapter.value = chapter;
  player
    .setCurrentTime(chapter.startTime)
    .then(() => {
      //player.play();
    })
    .catch(function (error) {
      switch (error.name) {
        case "RangeError":
          break;
        default:
          break;
      }
    });
};

const setPlayerProgressCurrentTime = (percent = 0) => {
  player.getDuration().then((duration) => {
    player.setCurrentTime(percent * duration);
    setActiveChapterFromProgress(percent * duration)(true);
    player.play();
  });
};

const setActiveChapterFromProgress = (time) => {
  const prevChapters = props.chapters.filter(
    (chapter) => chapter.startTime <= time
  );
  activeChapter.value = prevChapters[prevChapters.length - 1];
  return (arg) => {
    if (arg) {
      setTimeout(() => {
        document
          .querySelector(".player .controls li.chapter--active")
          .scrollIntoView(true);
      }, 200);
    }
  };
};

const generateAnchorId = (text) =>
  text
    .toLowerCase()
    .normalize("NFD")
    .replace(/([aeio])\u0301|(u)[\u0301\u0308]/gi, "$1$2")
    .normalize()
    .replace(/ñ/gi, "n")
    .replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, "")
    .replace(/ +/g, "-");
</script>

<style scoped>
.player {
  display: flex;
  flex-direction: row;
  background-color: navy;
  padding: 20px;
}
.player--column {
  flex-direction: column;
}

.video {
  width: 65%;
  margin-right: 1em;
  transition: all 0.5s 0s ease;
}

.video--full-width {
  width: 100%;
}

.video__container {
  position: relative;
  padding-bottom: 56.25%;
  overflow: hidden;
}

.video__container__iframe iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.controls {
  width: 35%;
  border-radius: 0.5em;
}

.controls--full-width {
  width: 100%;
  margin-top: 8px;
}
.controls--full-width .controls__wrapper {
  position: inherit;
  height: auto;
}
.controls--full-width .controls__container {
  position: inherit;
  width: auto;
}

.controls__wrapper {
  position: relative;
  height: 100%;
  overflow-y: auto;
}

.controls__container {
  position: absolute;
  width: 100%;
  overflow: hidden;
  background-color: antiquewhite;
  color: darkslateblue;
}

.controls__chapters {
  overflow-y: scroll;
  height: 100%;
  list-style: none;
  padding: 0;
  margin: 0;
}
.controls__chapters li {
  display: block;
  padding: 10px;
  margin: 0;
  border-bottom: 1px solid black;
  cursor: pointer;
}

.chapter {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}
.chapter.chapter--active {
  background-color: darkslateblue;
  color: antiquewhite;
}
.chapter.chapter--featured {
  font-weight: bold;
}
.chapter__timestamp {
  background: brown;
  color: white;
  font-size: 0.8em;
  border-radius: 10px;
  padding: 2px 5px;
  font-weight: normal;
  font-family: sans-serif;
  margin-right: 6px;
}

.chapter:hover .chapter__link {
  display: inline;
}
.chapter__link {
  display: none;
  padding: 0 0 0 15px;
}
.chapter__link a {
  text-decoration: none;
}
.chapter__badge {
  margin-left: 8px;
}
</style>
