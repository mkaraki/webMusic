<script setup lang="ts">
// This starter template is using Vue 3 <script setup> SFCs
// Check out https://vuejs.org/api/sfc-script-setup.html#script-setup
import { Ref, ref, provide } from 'vue';
import PlaybackController from './components/PlaybackController.vue'
import PlayingQueueController from './components/PlayingQueueController.vue';
import MusicSelectorTrack from './components/MusicSelectorTrack.vue';
import { emitter } from './emitter';

const displayPlaybackQueue = ref(false);

const coverUrl = ref('');

emitter.on('gotPlayingInformation', (i: any) => {
    coverUrl.value = i['artworkUrl'];
});

</script>

<template>
  <div class="queue-controller">
    <playing-queue-controller v-if="displayPlaybackQueue"
      :coverUrl="coverUrl"></playing-queue-controller>
    <music-selector-track v-else></music-selector-track>
  </div>

  <div class="controller">
    <playback-controller 
      v-on:toggle-playback-queue="displayPlaybackQueue = !displayPlaybackQueue"
      ></playback-controller>
  </div>
</template>

<style scoped>
.queue-controller {
  height: calc(100vh - 100px);
  overflow-y: auto;
}

.controller {
  position: fixed;
  height: 100px;
  width: 100vw;
  bottom: 0;
  background-color: black;
}
</style>
