<script setup lang="ts">
import { onMounted, ref, provide, inject } from 'vue'
import ArtistMapToLinkedText from './ArtistMapToLinkedText.vue';
import TrackInAlbumSelecter from './TrackInAlbumSelecter.vue';
import Loading from './Loading.vue'
import { emitter } from '../emitter';
import SelectorMenu from './SelectorMenu.vue';

let items = ref([]);

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const libraryIdGetter: any = inject('libraryId');
const libraryId = libraryIdGetter();

const props = defineProps<{
    initialView: Array<string>
}>();

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
    history.replaceState({
        'view': 'album'
    }, '', `/app/album`);
    addItemsRecursive(`/library/${libraryId}/album`);

    if (props.initialView.length > 1 && props.initialView[0] == 'album')
        selectInspectItemById(props.initialView[1]);
});

function sendSelectedTrackInfo(trackId: number) {
    emitter.emit<any>('newTrackSelected', trackId)
}


const loading = ref(false);
const inspectingAlbum = ref(null);

function selectInspectItem(item: any) { 
    selectInspectItemById(item['id']);
}

function selectInspectItemById(id: string|number) { 
    loading.value = true;
    fetch(baseurl + `/library/${libraryId}/album/` + id, {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(r => {
            inspectingAlbum.value = r;
        })
        .finally(() => { 
            loading.value = false;
        });
}

function onBackFromTrackSelector() { 
    inspectingAlbum.value = null;
    history.replaceState(null, '', `/app/album`);
}

</script>

<template>
    <loading v-if="loading"></loading>
    <div class="container-fluid" v-else-if="inspectingAlbum === null">
        <div class="row">
            <div class="col p-0">
                <selector-menu current-view="album" :back-disabled="true"></selector-menu>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-6 row-cols-lg-6 row-cols-xl-6 row-cols-xxl12 g-6">
            <div class="col" v-for="item in items" :key="item['id']">
                <div class="card h-100">
                    <a v-on:click="selectInspectItem(item)" href="#">
                        <img :src="baseurl + item['artworkUrl']" class="card-img-top" :alt="item['albumName']">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title cut-overflow card-album">
                            <a v-on:click="selectInspectItem(item)" href="#" class="list-group-item-action">
                                {{ item['albumName'] }}
                            </a>
                        </h5>
                        <p class="card-text card-artist cut-overflow">
                            <artist-map-to-linked-text :artists="item['artist']" :link="false"/>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="album-info">
        <track-in-album-selecter :album="inspectingAlbum" class="album-info" v-on:back="onBackFromTrackSelector"></track-in-album-selecter>
    </div>
</template>


<style scoped>

.album-info {
    min-height: 100%;
}

.card-img-top {
    width: 100%;
}

.card, .card-body {
    background-color: transparent;
}

</style>