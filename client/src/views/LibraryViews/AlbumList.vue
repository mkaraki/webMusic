<script setup lang="ts">
import { onMounted, ref, inject } from 'vue'
import ArtistMapToLinkedText from '../../components/ArtistMapToLinkedText.vue';
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
    addItemsRecursive(`/library/${libraryId}/album`);
});

</script>

<template>
    <div class="container-fluid">
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-6 row-cols-lg-6 row-cols-xl-6 row-cols-xxl12 g-6">
            <div class="col" v-for="item in items" :key="item['id']">
                <div class="card h-100">
                    <RouterLink :to="`/library/${libraryId}/album/${item['id']}`">
                        <img :src="baseurl + item['artworkUrl']" class="card-img-top" :alt="item['albumName']">
                    </RouterLink>
                    <div class="card-body">
                        <h5 class="card-title cut-overflow card-album">
                            <RouterLink :to="`/library/${libraryId}/album/${item['id']}`" class="list-group-item-action">
                                {{ item['albumName'] }}
                            </RouterLink>
                        </h5>
                        <p class="card-text card-artist cut-overflow">
                            <artist-map-to-linked-text :artists="item['artist']" :link="false" />
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
.album-info {
    min-height: 100%;
}

.card-img-top {
    width: 100%;
}

.card,
.card-body {
    background-color: transparent;
}
</style>