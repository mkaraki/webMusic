<script setup lang="ts">
// This starter template is using Vue 3 <script setup> SFCs
// Check out https://vuejs.org/api/sfc-script-setup.html#script-setup
import { ref, provide, Ref } from 'vue';
import PlaybackController from './components/PlaybackController.vue'
import PlayingQueueController from './components/PlayingQueueController.vue';
import MusicSelectorTrack from './components/MusicSelectorTrack.vue';
import MusicSelectorAlbum from './components/MusicSelectorAlbum.vue';
import Login from './components/Login.vue';
import SelectLibrary from './components/SelectLibrary.vue';
import { emitter } from './emitter';

const displayPlaybackQueue = ref(false);

const coverUrl = ref('');

const selectorView = ref('album');

emitter.on('logout', () => {
  localStorage.removeItem('lastConnectedServer');
  localStorage.removeItem('lastLibrary');
  document.cookie ='auth=0; Max-Age=0'
  srvbaseurl.value = '';
});

emitter.on('changeLibrary', () => { 
  localStorage.removeItem('lastLibrary');
  libraryId.value = null;
});

emitter.on('gotPlayingInformation', (i: any) => {
  coverUrl.value = srvbaseurl.value + i['artworkUrl'];
});

emitter.on('changeView', (i: any) => {
  selectorView.value = i;
});

emitter.on('setPlaylist', (i: any) => {
  playlist.value = i['list'];
  playingNo.value = i['playing'];
});

const playlist = ref([]);
const playingNo = ref(0);

const srvbaseurl = ref('');

const libraryId: Ref<null|number> = ref(null);

provide('baseurl', function () {
  return srvbaseurl.value;
});

provide('libraryId', function () {
  return libraryId.value;
});

function playbackEnded(isLoop: boolean = false) { 
  playingNo.value++;

  if (playingNo.value < playlist.value.length) {
    emitter.emit('newTrackSelected', playlist.value[playingNo.value]['id']);
  }
  else { 
    playingNo.value = 0;
    if (isLoop)
      emitter.emit('newTrackSelected', playlist.value[playingNo.value]['id']);
  }
}

</script>

<template>

  <login v-if="srvbaseurl === ''" v-on:login-succeed="srvbaseurl = $event"></login>
  <select-library v-else-if="libraryId === null" v-on:on-library-selected="libraryId = $event"></select-library>
  <div v-else>
    <div class="queue-controller">
      <playing-queue-controller :class="(displayPlaybackQueue ? '' : 'hide')"
        :coverUrl="coverUrl"
        :playingNo="playingNo"
        :playlist="playlist"></playing-queue-controller>
      <div :class="(displayPlaybackQueue ? 'hide' : '')">
        <music-selector-track v-if="selectorView === 'track'" />
        <music-selector-album v-else />
      </div>
    </div>
  
    <div class="controller">
      <playback-controller 
        v-on:toggle-playback-queue="displayPlaybackQueue = !displayPlaybackQueue"
        v-on:playback-ended="playbackEnded"
        ></playback-controller>
    </div>
  </div>


</template>

<style scoped>
.hide {
  display: none;
}

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
