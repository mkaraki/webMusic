<script setup lang="ts">
import { inject, ref } from 'vue';
import LibraryNavBar from '../components/LibraryNavBar.vue';
import { emitter } from '../emitter';
import PlaybackController from './PlaybackController/PlaybackController.vue';
import PlayingQueueController from './PlaybackController/PlayingQueueController.vue';
import { useRoute } from 'vue-router';
const route = useRoute();

const libraryId = route.params.libraryId;

const displayPlaybackQueue = ref(false);

const coverUrl = ref('');

emitter.on('gotPlayingInformation', (i: any) => {
    coverUrl.value = baseurl + i['artworkUrl'];
});

emitter.on('setPlaylist', (i: any) => {
    console.info('New playlist: ', i)
    playlist.value = i['list'];
    playingNo.value = i['playing'];
});

const playingTime = ref(0.0);

const playlist = ref([]);
const playingNo = ref(0);

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

function playbackEnded(isLoop: boolean = false) {
    playingNo.value++;

    if (playingNo.value < playlist.value.length) {
        emitter.emit('newTrackSelected', playlist.value[playingNo.value]['id']);
    }
    else if (isLoop) {
        playingNo.value = 0;
        emitter.emit('newTrackSelected', playlist.value[playingNo.value]['id']);
        return;
    }
    else {
        fetch(`${baseurl}/library/${libraryId}/random/track`, {
            credentials: 'include'
        })
            .then(data => data.json())
            .then(data => {
                playlist.value = playlist.value.concat(data);
                emitter.emit('newTrackSelected', playlist.value[playingNo.value]['id']);
            });
    }
}

function onTimeUpdate(time: number) {
    playingTime.value = time;
}
</script>

<template>
    <LibraryNavBar />
    <div class="queue-controller">
        <PlayingQueueController v-if="displayPlaybackQueue" :coverUrl="coverUrl" :playingNo="playingNo" :playlist="playlist"
            :current-time="playingTime" />
        <RouterView v-else />
    </div>
    <div class="controller">
        <PlaybackController v-on:toggle-playback-queue="displayPlaybackQueue = !displayPlaybackQueue"
            v-on:playback-ended="playbackEnded" v-on:on-time-update="onTimeUpdate" />
    </div>
</template>

<style scoped>
.hide {
    display: none;
}

.queue-controller,
.queue-controller div {
    height: calc(100vh - 130px);
    overflow-y: auto;
}

.controller {
    position: fixed;
    height: 70px;
    width: 100vw;
    bottom: 0;
    background-color: black;
}

@media screen and (max-width: 510px) {

    .queue-controller,
    .queue-controller div {
        height: calc(100vh - 140px);
    }

    .controller {
        height: 140px;
    }
}
</style>