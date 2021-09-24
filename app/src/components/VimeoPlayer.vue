<template>
  <div class="player">
    <div class="video">
      <div id="player-video" class="video__embed"></div>
    </div>
    <div class="controls">
      <ul class="controls__list">
        <li
          v-for="chapter in chapters"
          :key="chapter"
          @click="setVideoToChapter(chapter)"
          :class="{
            active: activeChapter.title == chapter.title,
            featured: chapter.featured,
          }"
        >
          {{ chapter.title }} <span v-if="chapter.starred">✴️</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { config } from "../config";
import postPlayerProgress from "../utils/progress";
import { ref } from "@vue/reactivity";
import { onMounted, onUnmounted } from "@vue/runtime-core";
import Player from "@vimeo/player";

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
const localStorageKey = `video-player-progress-${props.userId}-${props.videoId}`;
const localStorageKeyLastUpdate = `video-player-last-update-${props.userId}-${props.videoId}`;

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
    width: "800",
  });

  player.on("timeupdate", function (event) {
    const playerProgress = {
      userId: props.userId,
      vid: props.videoId,
      percent: event.percent,
    };
    localStorage.setItem(localStorageKey, JSON.stringify(playerProgress));

    setActiveChapterFromProgress(event.seconds);
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

onUnmounted(() => clearInterval(postPlayerInterval));

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
    setActiveChapterFromProgress(percent * duration);
    player.play();
  });
};

const setActiveChapterFromProgress = (time) => {
  activeChapter.value = props.chapters
    .filter((chapter) => chapter.startTime <= time)
    .at(-1);
};
</script>

<style>
.player {
  display: flex;
  flex-direction: row;
  background-color: navy;
  width: 100%;
  padding: 20px;
}

.controls {
  flex: 1;
  overflow-y: hidden;
  height: 450px;
  background-color: antiquewhite;
  color: darkslateblue;
  border-radius: 0.5em;
}

.controls__list {
  overflow-y: scroll;
  height: 100%;
  list-style: none;
  padding: 0;
  margin: 0;
}

.controls__list li {
  display: block;
  padding: 10px;
  margin: 0;
  border-bottom: 1px solid black;
  cursor: pointer;
}

.controls__list li.active {
  background-color: darkslateblue;
  color: antiquewhite;
}
.controls__list li.featured {
  font-weight: bold;
}
</style>
