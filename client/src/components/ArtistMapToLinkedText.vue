<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    artists: Array<any>
}>()

</script>

<template>
    <span v-for="artist in artists" :key="artist['sequence']"><a>{{artist['dispName']}}</a>{{artist['joinPhrase']}}</span>
</template>

<style>
</style>