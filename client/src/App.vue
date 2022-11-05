<script setup lang="ts">
// This starter template is using Vue 3 <script setup> SFCs
// Check out https://vuejs.org/api/sfc-script-setup.html#script-setup
import { Ref, ref, provide } from 'vue';
import HelloWorld from './components/HelloWorld.vue'
import PlaybackController from './components/PlaybackController.vue'
import PlayingQueueController from './components/PlayingQueueController.vue';

const pos: Ref<number> = ref(0);


</script>

<template>
  <div class="queue-controller">
    <playing-queue-controller></playing-queue-controller>
  </div>

  <div class="controller">
    <playback-controller title="Track 1" artist="Isekaijoucho" album="Anima"
      coverurl="https://coverartarchive.org/release/2e2be0e2-23c0-4e2b-b821-d63c17149fec/front" covercolor="#999"
      src="http://localhost:8080/library/1/track/1/file"
      v-bind:position="pos"
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
