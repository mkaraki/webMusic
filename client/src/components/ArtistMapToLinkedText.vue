<script setup lang="ts">
import { useRoute } from 'vue-router';
const route = useRoute();
const libraryId = route.params.libraryId;

defineProps<{
    artists: Array<any>,
}>()

</script>

<template>
    <span v-for="artist in artists" :key="artist['sequence']">
        <RouterLink :to="`/library/${libraryId}/artist/${artist['artistId']}`">{{ artist['dispName'] }}</RouterLink>{{
            artist['joinPhrase'] }}
    </span>
</template>