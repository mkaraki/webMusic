<script setup lang="ts">
import { inject, onMounted, Ref, ref } from 'vue'
import { emitter } from '../../emitter';
import ArtistMapToLinkedText from '../../components/ArtistMapToLinkedText.vue';
import { useRoute } from 'vue-router';
const route = useRoute();

const libraryId = route.params.libraryId;

const emit = defineEmits<{
    togglePlaybackQueue: [];
    playbackEnded: [isLoop: boolean];
    onTimeUpdate: [time: number];
}>();

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const pos = ref(0.0);

const trackId: Ref<string | number> = ref('');
const title = ref('Not playing');
const albumName = ref('');
const albumMbid = ref('');
const artistMap = ref([]);
const artistString = ref('');
const coverurl = ref('');

const loopMode = ref(0);

const displayVolumeControl = ref(true);

const player = new Audio();
player.autoplay = false;
player.ontimeupdate = function () {
    pos.value = player.currentTime / player.duration;
    emit('onTimeUpdate', player.currentTime);

    if (!scrobbleSent.value && pos.value >= 0.5) {
        scrobbleSent.value = true;
        fetch(`${baseurl}/history`, {
            credentials: 'include',
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${trackId.value}`
        });
    }
}
player.onended = function () {
    goNext();
}
player.onplay = function () {
    if ('mediaSession' in navigator) {
        navigator.mediaSession.metadata = new MediaMetadata({
            title: title.value,
            artist: artistString.value,
            album: albumName.value,
            artwork: [
                { src: coverurl.value },
            ]
        });

        navigator.mediaSession.setActionHandler('play', togglePlaybackStete);
        navigator.mediaSession.setActionHandler('pause', togglePlaybackStete);
        navigator.mediaSession.setActionHandler('seekbackward', function () { });
        navigator.mediaSession.setActionHandler('seekforward', function () { });
        navigator.mediaSession.setActionHandler('previoustrack', function () { });
        navigator.mediaSession.setActionHandler('nexttrack', function () { });
    }

    console.log('Playback started');
}

function goNext() {
    emit('playbackEnded', loopMode.value == 2);
}

const scrobbleSent = ref(true);

emitter.on('newTrackSelected', (t) => {
    console.info('New track selected: ', t);
    const url = baseurl + `/library/${libraryId}/track/` + t;
    fetch(url, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(res => {
            player.src = url + '/file';
            trackId.value = res['id'];
            title.value = res['title'];
            albumName.value = res['albumName'];
            albumMbid.value = res['releaseMbid'];
            artistMap.value = res['artist'];
            artistString.value = res['artistString'];
            coverurl.value = baseurl + res['artworkUrl'];
            pos.value = 0;


            emitter.emit<any>('gotPlayingInformation', res);
            setTimeout(function () {
                setMarqueeEnabled();
                player.load();
                player.play();
                scrobbleSent.value = false;
            }, 500);
        });
});

function togglePlaybackStete() {
    if (player.paused || player.ended)
        player.play();
    else
        player.pause();
}

function toggleLoopState() {
    loopMode.value++;
    if (loopMode.value > 2)
        loopMode.value = 0;

    player.loop = (loopMode.value == 1);

    localStorage.setItem('playerLoop', loopMode.value.toString());
}

function controller_playback_position_click(event: any) {
    const newPos = event.clientX * 1.0 / window.innerWidth;
    player.currentTime = player.duration * newPos;
}

onMounted(() => {
    if (
        navigator.userAgent.includes('Android') ||
        navigator.userAgent.includes('iOS')
    ) {
        player.volume = 1;
        displayVolumeControl.value = false;
    }
    else {
        const savedValue = localStorage.getItem('playerVolume') ?? '1';
        player.volume = parseFloat(savedValue);
    }

    loopMode.value = parseInt(localStorage.getItem('playerLoop') ?? '0');
    player.loop = (loopMode.value == 1);
});

window.onresize = setMarqueeEnabled;

const elm_mediainfo_text_holder: Ref<any> = ref(null);
const elm_mediainfo_title: Ref<any> = ref(null);
const elm_mediainfo_from: Ref<any> = ref(null);

function setMarqueeEnabled() {
    if (elm_mediainfo_text_holder.value.offsetWidth < elm_mediainfo_title.value.offsetWidth)
        elm_mediainfo_title.value.classList.add('marquee');
    else
        elm_mediainfo_title.value.classList.remove('marquee');

    if (elm_mediainfo_text_holder.value.offsetWidth < elm_mediainfo_from.value.offsetWidth)
        elm_mediainfo_from.value.classList.add('marquee');
    else
        elm_mediainfo_from.value.classList.remove('marquee');
}

function saveVolume(event: any) {
    const volume = event.target.value;
    localStorage.setItem('playerVolume', volume);
}
</script>

<template>
    <div class="controller">
        <div class="controller-playback-position-holder" v-on:click="controller_playback_position_click">
            <div class="controller-playback-position-view">
                <div class="controller-playback-position-indicator" v-bind:style="{ width: (pos * 100) + '%' }"></div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between">
            <div class="controller-playback-mediainfo-holder">
                <div class="controller-playback-mediainfo-image-holder">
                    <img :src="coverurl" alt="Coverart" class="img-fluid">
                </div>
                <div class="controller-playback-mediainfo-text-holder" ref="elm_mediainfo_text_holder">
                    <p class="controller-playback-mediainfo-title" ref="elm_mediainfo_title">{{ title }}</p><br />
                    <p class="controller-playback-mediainfo-from" ref="elm_mediainfo_from">{{ albumName }} ・
                        <artist-map-to-linked-text :artists="artistMap"></artist-map-to-linked-text>
                    </p>
                </div>
            </div>
            <div class="controller-playback-control-holder">
                <div class="controller-playback-control-volume-control" v-if="displayVolumeControl">
                    <div class="controller-playback-control-volume-holder">
                        <input type="range" max="1" min="0" step="0.02" v-model="player.volume" v-on:change="saveVolume" />
                    </div>
                    <button class="controller-playback-control-button controller-playback-control-volume-button"
                        v-on:click="player.muted = !player.muted">
                        <i v-if="player.muted" class="bi bi-volume-mute-fill"></i>
                        <i v-else-if="player.volume >= 0.5" class="bi bi-volume-up-fill"></i>
                        <i v-else-if="player.volume < 0.5" class="bi bi-volume-down-fill"></i>
                        <i v-else class="bi bi-volume-off-fill"></i>
                    </button>
                </div>
                <div>
                    <button class="controller-playback-control-button" v-on:click="togglePlaybackStete">
                        <i v-if="!(player.paused || player.ended)" class="bi bi-pause"></i>
                        <i v-else class="bi bi-play-fill"></i>
                    </button>
                    <button class="controller-playback-control-button" v-on:click="goNext">
                        <i class="bi bi-skip-forward-fill"></i>
                    </button>
                    <button class="controller-playback-control-button" v-on:click="toggleLoopState">
                        <i v-if="loopMode === 1" class="bi bi-repeat-1"></i>
                        <i v-else-if="loopMode === 2" class="bi bi-repeat"></i>
                        <i v-else class="bi bi-repeat grayout"></i>
                    </button>
                    <button class="controller-playback-control-button" v-on:click="emit('togglePlaybackQueue')">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.controller-playback-position-holder {
    height: 5px;
    padding-bottom: 0;
    cursor: pointer;
}

.controller-playback-position-view {
    position: relative;
    height: 100%;
    width: 100vw;
    background-color: black;
}

.controller-playback-position-holder:hover {
    padding-bottom: 0;
}

.controller-playback-position-indicator {
    position: relative;
    height: 100%;
    width: 0;
    background-color: #999;
    overflow: hidden;
}

.controller-playback-mediainfo-holder {
    height: 65px;
    padding: 5px;
}

.controller-playback-mediainfo-holder>div {
    float: left;
}

.controller-playback-mediainfo-image-holder {
    height: 100%;
    width: 55px;
    margin-right: 15px;
}

.controller-playback-mediainfo-image-holder img {
    height: 100%;
    width: 100%;
    object-fit: contain;
}

.controller-playback-mediainfo-title {
  color: white;
  font-size: x-large;
  margin-bottom: -4px !important;
}

.controller-playback-mediainfo-from {
  font-size: smaller;
  color: darkgray
}

.controller-playback-mediainfo-text-holder {
    width: calc(calc(100vw - 80px) - 290px);
    overflow: hidden;
}

.controller-playback-mediainfo-text-holder>p {
    margin: 0;
    display: inline-block;
    white-space: nowrap;
}

.controller-playback-mediainfo-text-holder>p.marquee {
    animation: marquee linear 8s infinite;
}

@keyframes marquee {
    0% {
        transform: translate(0%);
    }

    20% {
        transform: translate(0%);
    }

    90% {
        transform: translate(-100%);
    }

    100% {
        transform: translate(-100%);
    }
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
    font-size: 20pt;
    margin-top: 10px;
}

.controller-playback-control-volume-holder {
    float: left;
    display: none;
    margin-top: 18px;
    height: 10px;
}

.controller-playback-control-volume-holder input[type=range] {
    height: 10px;
    width: 80px;
}

.controller-playback-control-volume-control:hover .controller-playback-control-volume-holder {
    display: block;
}

.grayout {
    color: gray;
}

@media screen and (max-width: 510px) {
    .controller-playback-mediainfo-holder {
        width: 100vw;
    }

    .controller-playback-mediainfo-text-holder {
        width: calc(100vw - 80px);
    }

    .controller-playback-control-holder {
        margin: auto;
        text-align: center;
    }
}
</style>