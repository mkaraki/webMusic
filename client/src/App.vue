<script setup lang="ts">
// This starter template is using Vue 3 <script setup> SFCs
// Check out https://vuejs.org/api/sfc-script-setup.html#script-setup
import { Ref, ref, provide } from 'vue';
import PlaybackController from './components/PlaybackController.vue'
import PlayingQueueController from './components/PlayingQueueController.vue';
import MusicSelectorTrack from './components/MusicSelectorTrack.vue';
import MusicSelectorAlbum from './components/MusicSelectorAlbum.vue';
import Login from './components/Login.vue';
import { emitter } from './emitter';

const displayPlaybackQueue = ref(false);

const coverUrl = ref('');

emitter.on('gotPlayingInformation', (i: any) => {
    coverUrl.value = i['artworkUrl'];
});

const loggedIn = ref(false);

</script>

<template>

  <div v-if="loggedIn">
    <div class="queue-controller">
      <playing-queue-controller v-if="displayPlaybackQueue"
        :coverUrl="coverUrl"></playing-queue-controller>
      <div v-else>
        <music-selector-album />
        <music-selector-track />
      </div>
    </div>
  
    <div class="controller">
      <playback-controller 
        v-on:toggle-playback-queue="displayPlaybackQueue = !displayPlaybackQueue"
        ></playback-controller>
    </div>
  </div>
  <login v-else v-on:login-succeed="loggedIn = true"></login>


</template>

<style scoped>
.queue-controller,
.queue-controller div {
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
