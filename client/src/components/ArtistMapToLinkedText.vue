<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    artists: Array<any>,
    link?: Boolean
}>()

</script>

<template>
    <span v-for="artist in artists" :key="artist['sequence']">
        <a v-if="link ?? true">{{artist['dispName']}}</a>
        <span v-else>{{artist['dispName']}}</span>{{artist['joinPhrase']}}
    </span>
</template>

<style>
</style>