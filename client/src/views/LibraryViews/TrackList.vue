<script setup lang="ts">
import { onMounted, ref, inject } from 'vue'
import ArtistMapToLinkedText from '../../components/ArtistMapToLinkedText.vue';
import { emitter } from '../../emitter';
import { useRoute } from 'vue-router';
const route = useRoute();

const libraryId = route.params.libraryId;

let items = ref([]);

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

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

function sendSelectedTrackInfo(track: any, trackId: number) {
    emitter.emit('setPlaylist', {
        'list': [track],
        'playing': 0,
    });

    emitter.emit<any>('newTrackSelected', trackId);
}

</script>

<template>
    <ol class="list-group" ref="itemList">
        <li class="list-group-item d-flex justify-content-between align-items-start" v-for="item in items"
            :key="item['id']">
            <div class="ms-2 me-auto">
                <div class="fw-bold"><a v-on:click="sendSelectedTrackInfo(item, item['id'])" href="javascript:void(0)"
                        class="list-group-item-action">{{ item['title'] }}</a></div>
                <artist-map-to-linked-text :artists="item['artist']"></artist-map-to-linked-text>
                -
                <RouterLink :to="`/library/${libraryId}/album/${item['albumId']}/`">{{ item['albumName'] }}</RouterLink>
                <span v-if="item['diskNo'] > 0 && item['trackNo'] > 0">
                    (
                    <span v-if="item['diskNo'] > 0">Disk {{ item['diskNo'] }}</span> <span v-if="item['trackNo'] > 0">Track
                        {{ item['trackNo'] }}</span>
                    )
                </span>
            </div>
        </li>
    </ol>
</template>


<style scoped>
.list-group,
.list-group-item {
    background-color: transparent;
}

.list-group-item {
    color: white;
}

.list-group-item a {
    color: white;
}

.list-group-item a:hover {
    background-color: #333;
}
</style>