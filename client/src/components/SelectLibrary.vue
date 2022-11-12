<script setup lang="ts">
import { onMounted, ref, inject, Ref, defineEmits } from 'vue'
import { emitter } from '../emitter';

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const emit = defineEmits(['onLibrarySelected']); 

const loading = ref(false);

const availLibraries: Ref<any> = ref([]);

const libSel: Ref<any> = ref(null);

function tryAccessLibrary() {
    loading.value = true;

    const lastLib = localStorage.getItem('lastLibrary') ?? '::0';
    const coninfo = lastLib.split('::');

    if (coninfo.length !== 2 || baseurl !== coninfo[0]) { 
        loading.value = false;
        return;
    }

    accessLibrary(parseInt(coninfo[1]));
}

onMounted(() => { 
    loading.value = true;

    fetch(baseurl + '/library', {
        credentials: 'include'
    })
        .then(request => request.json())
        .then(data => { 
            availLibraries.value = data
        })
        .finally(() => { 
            loading.value = false;
            tryAccessLibrary();
        });
})

function accessLibrary(libId: number | null = null) { 
    if (libId === null)
        libId = parseInt(libSel.value.value);
    
    loading.value = true;

    fetch(baseurl + `/library/${libId}/check`, {
        credentials: 'include'
    })
        .then(res => { 
            if (res.status === 204) {
                localStorage.setItem('lastLibrary', `${baseurl}::${libId}`)
                emit('onLibrarySelected', libId);
            }
            else if (res.status !== 403) { 
                emitter.emit('logout')
            }
        })
        .catch((err) => { 
            emitter.emit('logout')
        })
        .finally(() => { 
            loading.value = false;
        } )
}

</script>

<template>
    <div class="d-flex justify-content-center align-items-center">
        <div v-if="loading" class="login-dialog">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else class="login-dialog">
            <form v-on:submit="accessLibrary()">
                <div class="mb-3">
                    <label for="library-sel" class="form-label">Library</label>
                    <select id="library-sel" class="form-select" required ref="libSel">
                        <option v-for="library in availLibraries" :key="library['id']" :value="library['id']">{{library['name'] ?? `Unnamed Library (${library['id']})`}}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Access</button>
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
    min-width: 250px;
}
</style>