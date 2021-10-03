<script setup>
import VimeoPlayer from './components/VimeoPlayer.vue'
import chapters from './data/chapters.json'
import { config } from './config'
</script>

<template>
  <VimeoPlayer :video-id="config.video_id" :user-id="config.user_id" :chapters="chapters"></VimeoPlayer>
</template>

<style>
body {
  margin: 0;
}
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  margin-top: 60px;
}
</style>
