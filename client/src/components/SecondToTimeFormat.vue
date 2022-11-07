<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    duration: number
}>();

</script>

<template>
    <span v-if="duration >= 3600"><span>{{Math.floor(duration / 3600)}}</span>:</span>
    <span>
        <span v-if="duration >= 3600 && duration / 60 % 60 < 10">0</span><span>{{Math.floor(duration / 60) % 60}}</span>:
    </span>
    <span>
        <span v-if="duration >= 60 && duration % 60 < 10">0</span><span>{{duration % 60}}</span>
    </span>
</template>