<script setup lang="ts">
const props = defineProps<{
    duration: number
}>();
</script>

<template>
    <span v-if="props.duration >= 3600"><span>{{ Math.floor(props.duration / 3600) }}</span>:</span>
    <span>
        <span v-if="props.duration >= 3600 && props.duration / 60 % 60 < 10">0</span><span>{{ Math.floor(props.duration /
            60) % 60 }}</span>:
    </span>
    <span>
        <span v-if="props.duration >= 60 && props.duration % 60 < 10">0</span><span>{{ props.duration % 60 }}</span>
    </span>
</template>