<script setup lang="ts">
import { onMounted, ref, provide } from 'vue'
import axios from 'axios';
import ArtistMapToLinkedText from './ArtistMapToLinkedText.vue';
import { emitter } from '../emitter';

let items = ref([]);

function addItemsRecursive(baseurl: string, url: string) {
    fetch(baseurl + url, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(res => {
            items.value = items.value.concat(res['result'])
            if (res['next'] !== null)
                addItemsRecursive(baseurl, res['next']);
        });
}

onMounted(() => {
    addItemsRecursive('http://localhost:8080', '/library/1/track');
});

function sendSelectedTrackInfo(trackId: number) { 
    emitter.emit<any>('newTrackSelected', trackId)
}

</script>

<template>
    <ol class="list-group" ref="itemList">
        <li class="list-group-item d-flex justify-content-between align-items-start" v-for="item in items" :key="item['id']">
            <div class="ms-2 me-auto">
                <div class="fw-bold"><a v-on:click="sendSelectedTrackInfo(item['id'])">{{item['title']}}</a></div>
                <artist-map-to-linked-text :artists="item['artist']"></artist-map-to-linked-text>
                -
                <a>{{item['albumName']}}</a>
                <span v-if="item['diskNo'] > 0 && item['trackNo'] > 0">
                    (
                        <span v-if="item['diskNo'] > 0">Disk {{item['diskNo']}}</span> <span v-if="item['trackNo'] > 0">Track {{item['trackNo']}}</span>
                    )
                </span>
            </div>
        </li>
    </ol>
</template>


<style scoped>

</style>