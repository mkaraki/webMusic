<script setup lang="ts">
import { ref } from "vue";
import { emitter } from "../emitter";

defineProps<{
    coverUrl: string,
}>()

</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="playing-coverart d-flex justify-content-center align-items-center">
                    <img :src="coverUrl"
                        alt="Coverart" class="img-fluid">
                </div>
            </div>

            <div class="col-12 col-lg-6 playlist-control-container">
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

.playing-coverart:before  {
    content: '';
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    filter: 
        blur(40px)
        brightness(30%);
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
</style>