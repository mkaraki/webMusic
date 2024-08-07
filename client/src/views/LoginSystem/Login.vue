<script setup lang="ts">
import { onMounted, Ref, ref } from "vue";
import Loading from '../../components/Loading.vue';

const emit = defineEmits(['loginSucceed']);

const serverSelected = ref(false);
const loading = ref(false);
const loadingMsg: Ref<string | undefined> = ref(undefined);

const baseUrl = ref('');

const formServerUrl: Ref<string | null> = ref(null);
const formLogin: Ref<any> = ref(null);

function tryLoginWithServer(_: any) {
    loading.value = true;
    if (formServerUrl.value === null)
        return false;
    let baseurl: string = (formServerUrl.value ?? '');
    if (baseurl.endsWith('/'))
        baseurl = baseurl.slice(0, -1)

    tryLoginWithServerAddress(baseurl);
}

// Try login with saved credential (Cookie)
function tryLoginWithServerAddress(baseurl: string) {
    loading.value = true;
    loadingMsg.value = `Connecting to ${baseurl}`;

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
                        formServerUrl.value = baseurl;
                        localStorage.setItem('lastConnectedServer', baseurl);
                        emit('loginSucceed', baseurl);
                    }
                    else {
                        serverSelected.value = false;
                    }
                });
            }
            else if (response.status === 401) {
                baseUrl.value = baseurl;
                formServerUrl.value = baseurl;
                serverSelected.value = true;
            }
            else {
                serverSelected.value = false;
            }
        })
        .catch(error => {
            console.error(error);
            serverSelected.value = false;
        })
        .finally(() => {
            loading.value = false;
        });
}

// Login to Server with string credentials
function loginToServer() {
    loading.value = true;
    loadingMsg.value = 'Logging in';

    let baseurl: string = (formServerUrl.value ?? '');
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
                    if (json['token'] !== undefined) {
                        localStorage.setItem('lastConnectedServer', baseurl);
                        loadingMsg.value = 'Just a moment';
                        setTimeout(() => {
                            emit('loginSucceed', baseurl);
                        }, 2000);
                    }
                    else {
                        serverSelected.value = false;
                    }
                });
            }
        })
        .catch(error => {
            console.error(error);
            serverSelected.value = false;
        })
        .finally(() => {
            loading.value = false;
        });

    return false;
}


onMounted(() => {
    baseUrl.value = localStorage.getItem('lastConnectedServer') ?? (window.location.origin);
    tryLoginWithServerAddress(baseUrl.value);
});

</script>
<template>
    <Loading v-if="loading" :message="loadingMsg"></Loading>
    <div v-else class="d-flex d-flex-fullscreen justify-content-center align-items-center">
        <div v-if="!serverSelected" class="login-dialog">
            <form v-on:submit="tryLoginWithServer" ref="server">
                <div class="mb-3">
                    <label for="login-serveraddr" class="form-label">Server Address</label>
                    <input type="url" class="form-control" id="login-serveraddr" v-model="formServerUrl">
                </div>
                <button type="submit" class="btn btn-primary">Connect</button>
            </form>
        </div>
        <div v-else class="login-dialog">
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
    </div>
</template>