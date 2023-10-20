<script setup lang="ts">
import { onMounted, ref, inject, Ref, defineEmits } from 'vue'
import { emitter } from '../../emitter';
import Loading from '../../components/Loading.vue';

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const emit = defineEmits(['onLibrarySelected']);

const loading = ref(false);
const loadingMsg = ref('Getting things ready');

const availLibraries: Ref<any> = ref([]);

onMounted(() => {
    loading.value = true;
    loadingMsg.value = 'Fetching library information'

    fetch(baseurl + '/library', {
        credentials: 'include'
    })
        .then(request => request.json())
        .then(data => {
            availLibraries.value = data
        })
        .catch(e => {
            console.error(e);
            emitter.emit('logout');
        })
        .finally(() => {
            loading.value = false;
        });
})

</script>

<template>
    <Loading v-if="loading" />
    <div v-else class="container-fluid">
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-6 row-cols-lg-6 row-cols-xl-6 row-cols-xxl12 g-6">
            <div class="col">
                <div class="card h-100" v-for="library in availLibraries" :key="library['id']">
                    <div class="card-body">
                        <h5 class="card-title cut-overflow card-album">
                            <RouterLink :to="`/library/${library['id']}/`" class="list-group-item-action">
                                {{ library['name'] ?? `Unnamed Library (${library['id']})` }}
                            </RouterLink>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>