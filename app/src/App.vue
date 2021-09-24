<script setup>
import VimeoPlayer from './components/VimeoPlayer.vue'
import chapters from './data/chapters.json'
import { config } from './config'
</script>

<template>
  <VimeoPlayer :video-id="config.video_id" :user-id="config.user_id" :chapters="chapters"></VimeoPlayer>
</template>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
