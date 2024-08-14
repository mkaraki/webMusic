<script setup lang="ts">
import { ref, provide } from 'vue';
import Login from './views/LoginSystem/Login.vue';
import { emitter } from './emitter';

emitter.on('logout', () => {
  localStorage.removeItem('lastConnectedServer');
  document.cookie = 'auth=0; Max-Age=0'
  srvbaseurl.value = '';
});

const srvbaseurl = ref('');

provide('baseurl', function () {
  return srvbaseurl.value;
});
</script>

<template>
  <login v-if="srvbaseurl === ''" v-on:login-succeed="srvbaseurl = $event" />
  <RouterView v-else />
</template>

<style lang="scss">
@import "./style.scss";
</style>