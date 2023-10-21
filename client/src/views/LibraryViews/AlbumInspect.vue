<script setup lang="ts">
import { onMounted, ref, inject, Ref } from 'vue'
import { useRoute } from 'vue-router';
const route = useRoute();
import ArtistMapToLinkedText from '../../components/ArtistMapToLinkedText.vue';
import SecondToTimeFormat from '../../components/SecondToTimeFormat.vue';
import { emitter } from '../..//emitter';
import Loading from '../../components/Loading.vue';

let items = ref([]);

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const libraryId = route.params.libraryId;

const albumId = route.params.albumId;
const album: Ref<any> = ref(null);

const loading = ref(false);

function addItemsRecursive(url: string) {
    fetch(baseurl + url, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(res => {
            items.value = items.value.concat(res['result'])
            if (res['next'] !== null)
                addItemsRecursive(res['next']);
        });
}

onMounted(() => {
    addItemsRecursive(`/library/${libraryId}/album`);
});

function sendSelectedTrackInfo(trackId: number) {
    emitter.emit<any>('newTrackSelected', trackId);
}

function playQueue(trackList: Array<any>, track: any) {
    const playingNo = trackList.findIndex(v => v['id'] === track['id']);

    emitter.emit('setPlaylist', {
        'list': trackList,
        'playing': playingNo,
    });

    sendSelectedTrackInfo(track['id']);
}

onMounted(() => {
    loading.value = true;
    fetch(baseurl + `/library/${libraryId}/album/` + albumId, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(r => {
            album.value = r;
        })
        .finally(() => {
            loading.value = false;
        });
})


</script>

<template>
    <Loading v-if="loading" />
    <div v-else class="holder" :style="{ '--bgImage': 'url(' + baseurl + album['artworkUrl'] + ')' }">
        <div class="container-fluid">
            <div class="row coverart-bg">
                <div class="col-12 col-md-4 text-center">
                    <img :src="baseurl + album['artworkUrl']" alt="Artwork" class="img-fluid w-100">
                </div>
                <div class="col-12 col-md-8 content-info">
                    <h2>{{ album['albumName'] }}</h2>
                    <p class="artist-information-holder">
                        <artist-map-to-linked-text :artists="album['artist']"></artist-map-to-linked-text>
                    </p>
                    <div>
                        <div class="list-group">
                            <span class="list-group-item list-group-item-action" v-for="track in album['track']"
                                :key="track['id']">
                                <div class="d-flex justify-content-between align-items-start w-100">
                                    <div class="me-auto track-information-holder">
                                        <div class="track-no-information-holder">
                                            <span v-if="track['diskNo'] > 0">{{ track['diskNo'] }}</span>
                                            <span v-if="track['diskNo'] > 0 && track['trackNo'] > 0">-</span>
                                            <span v-if="track['trackNo'] > 0">{{ track['trackNo'] }}</span>
                                            <span v-if="track['diskNo'] > 0 || track['trackNo'] > 0">.</span>
                                        </div>
                                        <div>
                                            <div>
                                                <a href="javascript:void(0)"
                                                    v-on:click.prevent="playQueue(album['track'], track)" class="trackName">
                                                    {{ track['title'] }}
                                                </a>
                                            </div>
                                            <div class="artist-information-holder">
                                                <artist-map-to-linked-text
                                                    :artists="track['artist']"></artist-map-to-linked-text>
                                            </div>
                                        </div>
                                    </div>
                                    <second-to-time-format :duration="parseInt(track['duration'])"></second-to-time-format>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
body {
    background-color: transparent;
}

.holder {
    height: 100%;
    position: relative;
}

.container-fluid {
    overflow: hidden;
    position: relative;
    min-height: 100%;
}

.container-fluid:before {
    content: '';
    background-image: var(--bgImage);
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: -80px;
    filter:
        blur(40px) brightness(30%);
    z-index: -1;
}

.container-fluid,
.container-fluid * {
    background-color: transparent;
    z-index: 0;
}

img {
    max-width: 80vw;
    padding: 30px;
}

.row {
    height: 100%;
    overflow-y: auto;
}

.content-info {
    margin-top: 40px;
}

.track-information-holder>div {
    float: left;
}

.track-no-information-holder {
    min-width: 38px;
}

.artist-information-holder {
    font-size: 10pt;
    color: darkgray;
}

.list-group-item {
    color: white;
}

.trackName,
.trackName:link {
    color: white;
}
</style>
