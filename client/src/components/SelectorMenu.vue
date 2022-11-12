<script setup lang="ts">
import { defineEmits } from 'vue';
import 'bootstrap/dist/js/bootstrap.bundle.min'
import { emitter } from '../emitter';

const emit = defineEmits(['back', 'changeView']);

defineProps<{
    backDisabled?: boolean | null,
    currentView: string,
}>();

function cngV(view: string) { 
    emitter.emit('changeView', view);
    emit('changeView', view);
}

</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" v-on:click="emit('back')" v-if="!backDisabled"><i class="bi bi-arrow-left"></i></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a :class="'nav-link ' + (currentView === 'album' ? 'active' : '')" href="#" v-on:click="cngV('album')">Albums</a>
                    </li>
                    <li class="nav-item">
                        <a :class="'nav-link ' + (currentView === 'track' ? 'active' : '')" href="#" v-on:click="cngV('track')">Tracks</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <button class="btn-sm btn btn-outline-info me-2" v-on:click="emitter.emit('changeLibrary')">Switch Library</button>
                    <button class="btn-sm btn btn-outline-light" v-on:click="emitter.emit('logout')">Logout</button>
                </div>
            </div>
        </div>
    </nav>
</template>