<script setup lang="ts">
const props = defineProps({
    message: {
        type: String,
        default: null,
        required: false,
    }
});
</script>

<template>
    <div class="d-flex d-flex-fullscreen justify-content-center align-items-center">
        <div class="login-dialog text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div v-if="props.message !== undefined">
                {{ props.message }}
            </div>
        </div>
    </div>
</template>