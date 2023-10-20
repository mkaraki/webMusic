<script setup lang="ts">
import { useRoute } from 'vue-router';
const route = useRoute();
const libraryId = route.params.libraryId;
</script>

<template>
    <div class="container-fluid">
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-6 row-cols-lg-6 row-cols-xl-6 g-6">
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title cut-overflow card-album">
                            <RouterLink :to="`/library/${libraryId}/album/`" class="list-group-item-action">
                                Album View
                            </RouterLink>
                        </h5>
                    </div>
                </div>
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title cut-overflow card-album">
                            <RouterLink :to="`/library/${libraryId}/track/`" class="list-group-item-action">
                                Tracks
                            </RouterLink>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>