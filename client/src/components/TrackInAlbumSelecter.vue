<script setup lang="ts">
import ArtistMapToLinkedText from './ArtistMapToLinkedText.vue';
import { onMounted, Ref, ref } from 'vue'


defineProps<{
    album: any,
}>()

const detailedAlbumInfo: Ref<any> = ref(null);

function fetchAlbumDetailedInfo(album: any) { 
    fetch('http://localhost:8080/' + 'library/1/album/' + album['mbid'])
        .then(response => response.json)
        .then(r => {
            detailedAlbumInfo.value = r;
        });
}

</script>

<template>
    <div class="holder" :style="{ '--bgImage': 'url(' + album['artworkUrl'] + ')' }">
        <div class="container-fluid">
            <div class="row coverart-bg">
                <div class="col-12 col-md-4">
                    <img :src="album['artworkUrl']" alt="Artwork" class="img-fluid w-100">
                </div>
                <div class="col-12 col-md-8 content-info">
                    <h2>{{ album['albumName'] }}</h2>
                    <p>
                        <artist-map-to-linked-text :artists="album['artist']"></artist-map-to-linked-text>
                    </p>
                    <div>
                        <div v-if="detailedAlbumInfo === null" v-on:load="fetchAlbumDetailedInfo(album)">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div v-else>

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
</style>
