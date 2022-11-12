<script setup lang="ts">
import { onMounted, Ref, ref } from "vue";

const emit = defineEmits(['loginSucceed']);

const serverSelected = ref(false);
const loading = ref(false);

const baseUrl = ref('');

const formServerUrl: Ref<any> = ref(null);
const formLogin: Ref<any> = ref(null);

function tryLoginWithServer(e: any) {
    loading.value = true;
    if (formServerUrl.value === null)
        return false;
    let baseurl: string = (formServerUrl.value.value ?? '');
    if (baseurl.endsWith('/'))
        baseurl = baseurl.slice(0, -1)

    tryLoginWithServerAddress(baseurl);
}

function tryLoginWithServerAddress(baseurl: string) {
    fetch(baseurl + '/login/check', {
        credentials: 'include'
    })
        .then(response => {
            if (response.status === 200) {
                response.text().then(rawjson => {
                    const json = JSON.parse(rawjson);
                    console.log(json);
                    if (json['loggedUser'] !== undefined) {
                        baseUrl.value = baseurl;
                        localStorage.setItem('lastConnectedServer', baseurl);
                        emit('loginSucceed', baseurl);
                    }
                    else {
                        serverSelected.value = false;
                    }
                });
            }
            else if (response.status === 401) {
                serverSelected.value = true;
            }
            else {
                serverSelected.value = false;
            }
        })
        .catch(error => {
            serverSelected.value = false;
        })
        .finally(() => {
            loading.value = false;
        });
}

function loginToServer() {
    loading.value = true;

    let baseurl: string = (formServerUrl.value.value ?? '');
    if (baseurl.endsWith('/'))
        baseurl = baseurl.slice(0, -1)

    fetch(baseurl + '/login', {
        method: 'POST',
        body: new FormData(formLogin.value)
    })
        .then(response => {
            if (response.status === 200) {
                response.text().then(rawjson => {
                    const json = JSON.parse(rawjson);
                    console.log(json);
                    if (json['token'] !== undefined) {
                        localStorage.setItem('lastConnectedServer', baseurl);
                        emit('loginSucceed', baseurl);
                    }
                    else {
                        serverSelected.value = false;
                    }
                });
            }
        })
        .catch(error => {
            serverSelected.value = false;
        })
        .finally(() => {
            loading.value = false;
        });

    return false;
}


onMounted(() => {
    baseUrl.value = localStorage.getItem('lastConnectedServer') ?? (window.location.origin + '/');
    tryLoginWithServerAddress(baseUrl.value);
});

</script>

<template>
    <div class="d-flex justify-content-center align-items-center">
        <div v-if="loading" class="login-dialog">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="serverSelected" class="login-dialog">
            <form v-on:submit="loginToServer" ref="formLogin">
                <div class="mb-3">
                    <a href="#" v-on:click="serverSelected = false"><i class="bi bi-arrow-left"></i></a>
                </div>
                <div class="mb-3">
                    <label for="login-username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="login-username" name="username">
                </div>
                <div class="mb-3">
                    <label for="login-passwork" class="form-label">Password</label>
                    <input type="password" class="form-control" id="login-passwork" name="password">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
        <div v-else class="login-dialog">
            <form v-on:submit="tryLoginWithServer" ref="server">
                <div class="mb-3">
                    <label for="login-serveraddr" class="form-label">Server Address</label>
                    <input type="url" class="form-control" id="login-serveraddr" ref="formServerUrl">
                </div>
                <button type="submit" class="btn btn-primary">Connect</button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.d-flex {
    min-height: 100vh;
    min-width: 100vw;
}

.login-dialog {
    background-color: #DDD;
    color: black;
    padding: 30px;
}
</style>