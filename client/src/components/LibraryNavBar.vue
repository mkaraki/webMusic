<script setup lang="ts">
import { emitter } from '../emitter';
import { useRoute } from 'vue-router';
const route = useRoute();
const libraryId = route.params.libraryId;

const emit = defineEmits(['navigate']);
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <RouterLink :to="`/library/${libraryId}/`" class="nav-link" v-on:click="emit('navigate')">
                            Home
                        </RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink :to="`/library/${libraryId}/album/`" class="nav-link" v-on:click="emit('navigate')">
                            Album
                        </RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink :to="`/library/${libraryId}/artist/`" class="nav-link" v-on:click="emit('navigate')">
                            Artists
                        </RouterLink>
                    </li>
                    <li class="nav-item">
                        <RouterLink :to="`/library/${libraryId}/track/`" class="nav-link" v-on:click="emit('navigate')">
                            Tracks
                        </RouterLink>
                    </li>
                </ul>
                <div class="d-flex">
                    <RouterLink class="btn-sm btn btn-outline-info me-2" role="button" to="/" v-on:click="emit('navigate')">
                        Switch
                        Library</RouterLink>
                    <button class="btn-sm btn btn-outline-light" v-on:click="emitter.emit('logout')">Logout</button>
                </div>
            </div>
        </div>
    </nav>
</template>