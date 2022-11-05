<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    title: string,
    album: string,
    artist: string,
    src: string,
    coverurl: string,
    covercolor: string,
}>()

const pos = ref(0.0);

const player = new Audio('http://localhost:8080/library/1/track/1/file');
player.ontimeupdate = function () { 
    pos.value = player.currentTime / player.duration;
}

function togglePlaybackStete() { 
  if (player.paused || player.ended)
    player.play();
  else
    player.pause();
}

function controller_playback_position_click(event: any) {
    const newPos = event.clientX * 1.0 / window.innerWidth;
    player.currentTime = player.duration * newPos;
}

</script>

<template>
    <div class="controller">
        <div class="controller-playback-position-holder" v-on:click="controller_playback_position_click">
            <div class="controller-playback-position-view">
                <div class="controller-playback-position-indicator"
                    v-bind:style="{ backgroundColor: covercolor, width: (pos * 100) + '%' }"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <div class="controller-playback-mediainfo-holder">
                <div class="controller-playback-mediainfo-image-holder">
                    <img :src=coverurl alt="Coverart" class="img-fluid">
                </div>
                <div class="controller-playback-mediainfo-text-holder">
                    <p class="controller-playback-mediainfo-title">{{ title }}</p>
                    <p class="controller-playback-mediainfo-album">{{ album }}</p>
                    <p class="controller-playback-mediainfo-artist">{{ artist }}</p>
                </div>
            </div>
            <div class="controller-playback-control-holder">
                <div class="controller-playback-control-volume-control">
                    <div class="controller-playback-control-volume-holder">
                        <input type="range" max="1" min="0" step="0.02"
                            v-bind:value="player.volume"
                            v-on:input="(e) => player.volume = e.target.value">
                    </div>
                    <button class="controller-playback-control-button controller-playback-control-volume-button"
                        v-on:click="player.muted = !player.muted">
                        <i v-if="player.muted" class="bi bi-volume-mute-fill"></i>
                        <i v-else-if="player.volume>=0.5" class="bi bi-volume-up-fill"></i>
                        <i v-else-if="player.volume<0.5" class="bi bi-volume-down-fill"></i>
                        <i v-else class="bi bi-volume-off-fill"></i>
                    </button>
                </div>
                <div>
                    <button class="controller-playback-control-button"
                        v-on:click="togglePlaybackStete">
                        <i v-if="!(player.paused || player.ended)" class="bi bi-pause"></i>
                        <i v-else class="bi bi-play-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.controller-playback-position-holder {
    height: 15px;
    padding-bottom: 13px;
    cursor: pointer;
}

.controller-playback-position-view {
    height: 100%;
    width: 100vw;
    background-color: #333;
}

.controller-playback-position-holder:hover {
    padding-bottom: 0;
}

.controller-playback-position-indicator {
    height: 100%;
    width: 0%;
    background-color: #E33;
}

.controller-playback-mediainfo-holder {
    height: 85px;
    padding: 5px;
}

.controller-playback-mediainfo-holder>div {
    float: left;
}

.controller-playback-mediainfo-image-holder {
    height: 100%;
    margin-right: 15px;
}

.controller-playback-mediainfo-image-holder img {
    height: 100%;
}

.controller-playback-mediainfo-text-holder p {
    margin: 0;
}

.controller-playback-mediainfo-title {
    color: white;
    font-size: x-large;
}

.controller-playback-mediainfo-album,
.controller-playback-mediainfo-artist {
    font-size: smaller;
    color: darkgray
}

.controller-playback-control-holder {
    margin-right: 15px;
}

.controller-playback-control-holder>div {
    float: left;
}

.controller-playback-control-button {
    border: none;
    background: none;
    color: white;
    font-size: 32pt;
}

.controller-playback-control-volume-holder {
    float: left;
    display: none;
    position: abosolute;
    margin-top: 20px;
    height: 10px;
}

.controller-playback-control-volume-holder input[type=range] {
    height: 10px;
}

.controller-playback-control-volume-control:hover .controller-playback-control-volume-holder {
    display: block;
}

</style>