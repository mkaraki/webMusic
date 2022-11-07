<script setup lang="ts">
import { onMounted, ref } from "vue";
import { emitter } from "../emitter";

defineProps<{
    coverUrl: string,
}>()

const playingControlToggle = ref(true);

onMounted(() => {
    playingControlToggle.value = (localStorage.getItem('playQueueFullCover') ?? '0') !== '1';
});

function changeFullCover() { 
    playingControlToggle.value = !playingControlToggle.value;
    localStorage.setItem('playQueueFullCover', playingControlToggle.value ? '0' : '1')
}

</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div :class="playingControlToggle ? 'col-12 col-lg-6' : 'col-12'" :style="{ '--bgImage': 'url(' + coverUrl + ')' }">
                <div class="playing-coverart d-flex justify-content-center align-items-center">
                    <img :src="coverUrl" alt="Coverart" class="img-fluid">
                </div>

                <div class="playing-control-container-toggler">
                    <button class="btn btn-light"
                        v-on:click="changeFullCover">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>

            <div class="col-12 col-lg-6 playlist-control-container" v-if="playingControlToggle">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Playlist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Lyric</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>


<style scoped>
.container-fluid,
.container-fluid .row {
    height: calc(100vh - 100px);
}

.playing-coverart {
    height: calc(100vh - 100px);
    z-index: 0;
    position: relative;
}

.playing-coverart:before {
    content: '';
    background-image: var(--bgImage);
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    filter:
        blur(40px) brightness(30%);
    z-index: -1;
}

.playing-coverart img {
    filter: none;
    max-width: 80%;
    max-height: 80%;
    margin: auto auto;
}

.playlist-control-container {
    padding: 50px;
}

div:has(.playing-control-container-toggler) {
    position: relative;
}

.playing-control-container-toggler button {
    position: absolute;
    bottom: 15px;
    right: 15px;
}

</style>