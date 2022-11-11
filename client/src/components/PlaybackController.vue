<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { emitter } from '../emitter';
import ArtistMapToLinkedText from './ArtistMapToLinkedText.vue';

const emit = defineEmits(['togglePlaybackQueue']);

const baseurl = inject('baseurl')();

const pos = ref(0.0);

const title = ref('');
const albumName = ref('');
const albumMbid = ref('');
const artistMap = ref([]);
const coverurl = ref('');
const covercolor = ref('#999');

const player = new Audio();
player.ontimeupdate = function () { 
    pos.value = player.currentTime / player.duration;
}

emitter.on('newTrackSelected', (t) => {
    const url = baseurl + '/library/1/track/' + t;
    fetch(url, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(res => { 
            title.value = res['title'];
            albumName.value = res['albumName'];
            albumMbid.value = res['releaseMbid'];
            artistMap.value = res['artist'];
            player.src = url + '/file';
            coverurl.value = baseurl + res['artworkUrl'];
            covercolor.value = '#' + (res['artworkColor'] ?? '999');
            pos.value = 0;
            player.play();

            emitter.emit<any>('gotPlayingInformation', res);
        });
});

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

onMounted(() => {
    const savedValue = localStorage.getItem('playerVolume') ?? '1';
    player.volume = parseFloat(savedValue);
});


function saveVolume(event: any) { 
    const volume = event.target.value;
    localStorage.setItem('playerVolume', volume);
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
                    <img :src="coverurl" alt="Coverart" class="img-fluid">
                </div>
                <div class="controller-playback-mediainfo-text-holder">
                    <p class="controller-playback-mediainfo-title">{{ title }}</p>
                    <p class="controller-playback-mediainfo-album">{{ albumName }}</p>
                    <p class="controller-playback-mediainfo-artist">
                        <artist-map-to-linked-text :artists="artistMap"></artist-map-to-linked-text>
                    </p>
                </div>
            </div>
            <div class="controller-playback-control-holder">
                <div class="controller-playback-control-volume-control">
                    <div class="controller-playback-control-volume-holder">
                        <input type="range" max="1" min="0" step="0.02"
                            v-model="player.volume"
                            v-on:change="saveVolume"/>
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
                    <button class="controller-playback-control-button"
                        v-on:click="emit('togglePlaybackQueue')">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.controller-playback-position-holder {
    height: 15px;
    padding-bottom: 10px;
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