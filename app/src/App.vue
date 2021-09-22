<script setup>
import VimeoPlayer from './components/VimeoPlayer.vue'
</script>

<template>
  <VimeoPlayer vimeo-id="19231868" uid="46453" :chapters="[
    {
      startTime: 0,
      title: 'Introducción',
    },
    {
      startTime: 25,
      title: 'Aquí se la pega',
    },
    {
      startTime: 45,
      title: 'Aquí se va el prota',
    }
  ]"></VimeoPlayer>
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
