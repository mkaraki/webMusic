<script setup lang="ts">
import { onMounted, ref, provide, inject } from 'vue'
import ArtistMapToLinkedText from './ArtistMapToLinkedText.vue';
import { emitter } from '../emitter';
import SelectorMenu from './SelectorMenu.vue';

let items = ref([]);

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const libraryIdGetter: any = inject('libraryId');
const libraryId = libraryIdGetter();

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
    addItemsRecursive(`/library/${libraryId}/track`);
});

function sendSelectedTrackInfo(trackId: number) { 
    emitter.emit<any>('newTrackSelected', trackId)
}

</script>

<template>
    <selector-menu current-view="track" :back-disabled="true"></selector-menu>
    <ol class="list-group" ref="itemList">

        <li class="list-group-item d-flex justify-content-between align-items-start" v-for="item in items" :key="item['id']">
            <div class="ms-2 me-auto">
                <div class="fw-bold"><a v-on:click="sendSelectedTrackInfo(item['id'])" href="#" class="list-group-item-action">{{item['title']}}</a></div>
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