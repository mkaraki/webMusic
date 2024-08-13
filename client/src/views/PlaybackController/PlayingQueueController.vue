<script setup lang="ts">
import { ref } from "vue";
import { emitter } from "../../emitter";
import ArtistMapToLinkedText from '../../components/ArtistMapToLinkedText.vue';
import SecondToTimeFormat from '../../components/SecondToTimeFormat.vue';
import LyricView from './LyricView.vue';

defineProps<{
    coverUrl: string,
    playlist: Array<any>,
    playingNo: number,
    currentTime: number,
}>()

function sendSelectedTrackInfo(trackId: number) {
    emitter.emit<any>('newTrackSelected', trackId)
}

function playQueue(trackList: Array<any>, track: any) {
    const playingNo = trackList.findIndex(v => v['id'] === track['id']);

    emitter.emit('setPlaylist', {
        'list': trackList,
        'playing': playingNo,
    });

    sendSelectedTrackInfo(track['id']);
}

const tabMode = ref('playlist');

</script>

<template>
    <div :style="{ '--bgImage': 'url(' + coverUrl + ')' }">
        <div class="base-holder">
            <div class="content-holder">
                <div class="container-fluid">
                    <div class="row">
                        <div class="d-none d-lg-block col-lg-6">
                            <div class="playing-coverart d-flex justify-content-center align-items-center">
                                <img :src="coverUrl" alt="Coverart" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 playlist-control-container">
                            <div class="information-container">
                                <div>
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a :class="'nav-link ' + (tabMode === 'playlist' ? 'active' : '')"
                                                href="javascript:void(0)" v-on:click="tabMode = 'playlist'">Playlist</a>
                                        </li>
                                        <li class="nav-item">
                                            <a :class="'nav-link ' + (tabMode === 'lyric' ? 'active' : '')"
                                                href="javascript:void(0)" v-on:click="tabMode = 'lyric'">Lyric</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="inner-information-container">
                                    <div v-if="tabMode === 'lyric'">
                                        <lyric-view :track-id="playlist[playingNo]['id']" :key="playlist[playingNo]['id']"
                                            :current-time="currentTime"></lyric-view>
                                    </div>
                                    <div v-else>
                                        <div class="list-group">
                                            <div :class="'list-group-item ' + (playlist[playingNo]['id'] === track['id'] ? 'active' : 'list-group-item-action')"
                                                v-for="(track, index) in playlist" :key="track['id']">
                                                <div class="d-flex justify-content-between align-items-start w-100">
                                                    <div class="me-auto track-information-holder">
                                                        <div class="track-no-information-holder">
                                                            {{ index + 1 }}.
                                                        </div>
                                                        <div>
                                                            <div class="track-name-information-holder">
                                                                <a href="javascript:void(0)" v-on:click="playQueue(playlist, track)" class="text-white">
                                                                    {{ track['title'] }}
                                                                </a>
                                                            </div>
                                                            <div class="artist-information-holder">
                                                                <artist-map-to-linked-text :artists="track['artist']">
                                                                </artist-map-to-linked-text>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <second-to-time-format :duration="parseInt(track['duration'])">
                                                    </second-to-time-format>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
.base-holder {
    height: 100%;
    position: relative;
}

.base-holder:before {
    content: '';
    background-image: var(--bgImage) !important;
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    position: absolute;
    top: 0px;
    left: 15px;
    right: 15px;
    bottom: 0px;
    filter:
        blur(40px) brightness(70%);
    z-index: 0;
}

.content-holder {
    height: 100%;
    overflow: hidden;
}

.container-fluid,
.container-fluid .row {
    position: relative;
    height: 100%;
}

.playing-coverart {
    height: 100%;
    z-index: 0;
    position: relative;
}

.playing-coverart img {
    filter: none;
    max-width: 80%;
    max-height: 80%;
    margin: auto auto;
}

.playlist-control-container {
    padding: 50px;
    height: 100%;

    background-color: rgba(0, 0, 0, 0.75);
}

div:has(.playing-control-container-toggler) {
    position: relative;
}

.playing-control-container-toggler a {
    position: absolute;
    bottom: 10px;
    right: 25px;
}

.information-container {
    height: 100%;
}

.inner-information-container {
    height: calc(100% - 42px);
    overflow: auto;
}

.nav-link.active {
    background-color: transparent;
    border: 1px white solid;
    color: white;
}

.track-no-information-holder {
    min-width: 25px;
}

.track-information-holder>div {
    float: left;
}

.track-information-holder>div {
    float: left;
}

.list-group-item {
    background-color: transparent;
    color: white;
    border: white 1px solid;
    border-top: none;
    border-radius: 0px;
    z-index: 0;
}

.list-group-item.active {
    background-color: rgba(47, 47, 118, 0.5);
    border: lightskyblue 1px solid;
    z-index: 1;
}

.track-name-information-holder {
    color: white;
}
</style>