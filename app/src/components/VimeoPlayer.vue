<template>
  <div class="player">
    <div class="video">
      <div id="player-video"></div>
    </div>
    <div class="controls">
      <ul class="controls__list">
        <li
          v-for="chapter in chapters"
          :key="chapter"
          @click="setVideoToChapter(chapter)"
          :class="{ active: activeChapter.title == chapter.title }"
        >
          {{ chapter.title }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "@vue/reactivity";
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
const localStorageKey = `player-progress-${props.uid}-${props.vimeoId}`;
const localStorageKeyLastUpdate = `player-progress-last-update-${props.uid}-${props.vimeoId}`;

onMounted(() => {
  postPlayerInterval = setInterval(postPlayerProgress, 5000);

  localStorage.setItem(localStorageKeyLastUpdate, JSON.stringify({
    percent: 0
  }));

  player = new Player("player-video", {
    id: props.vimeoId,
    width: 400,
  });

  player.on("timeupdate", function (event) {
    const playerProgress = {
      uid: props.uid,
      vid: props.vimeoId,
      percent: event.percent,
    };
    localStorage.setItem(localStorageKey, JSON.stringify(playerProgress));

    activeChapter.value = props.chapters.filter(chapter => chapter.startTime <= event.seconds).at(-1)
  });

  fetch(
    `http://localhost:8080/api/v1/progress/video/${props.uid}/${props.vimeoId}`
  )
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
      player.getDuration().then((duration) => {
        player.setCurrentTime(result.percent * duration);
        player.play();
      });
    })
    .catch(console.log);
});

onUnmounted(() => clearInterval(postPlayerInterval));

const postPlayerProgress = () => {
  const playerStorage = JSON.parse(localStorage.getItem(localStorageKey));
  const playerStorageLastUpdate = JSON.parse(
    localStorage.getItem(localStorageKeyLastUpdate)
  );

  if (
    playerStorage != null &&
    playerStorageLastUpdate?.percent !== playerStorage?.percent
  ) {
    fetch("http://localhost:8080/api/v1/progress", {
      method: "POST",
      body: JSON.stringify(playerStorage),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((result) => {
        localStorage.setItem(localStorageKeyLastUpdate, JSON.stringify(playerStorage));
      })
      .catch((error) => console.log(error));
  }
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
