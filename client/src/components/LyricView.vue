<script setup lang="ts">
import { inject, onMounted, Ref, ref } from 'vue'

const props = defineProps<{
    trackId: number | string,
    currentTime: number,
}>()

const baseurlGetter: any = inject('baseurl');
const baseurl = baseurlGetter();

const libraryIdGetter: any = inject('libraryId');
const libraryId = libraryIdGetter();

const lyricInfo: Ref<any> = ref([]);

const gotLyric = ref('loading');

const holder: Ref<any> = ref(null);

let currentFocused = -1;

onMounted(() => {
    fetch(baseurl + `/library/${libraryId}/track/` + props.trackId + '/lyric', {
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            lyricInfo.value = data;
            gotLyric.value = 'ok';

            setInterval(function () {
                const msCurrent = props.currentTime * 1000;
                const detectedId = lyricInfo.value['lines'].findIndex((v: any) =>
                    v['time'] <= msCurrent &&
                    msCurrent < v['endtime']
                );
                if (detectedId > 0 && detectedId !== currentFocused) { 
                    document.getElementById(`lyric-l${(detectedId - 4 < 0) ? (0) : (detectedId - 4)}`)?.scrollIntoView(true);
                    currentFocused = detectedId;
                }
            }, 200);
        })
        .catch(() => {
            gotLyric.value = 'fail';
        });

});

</script>

<template>
    <div ref="holder">
        <div v-if="gotLyric === 'ok'" class="lyricView">
            <div v-for="(lyricLine, index) in lyricInfo['lines']" :key="index" :id="`lyric-l${index}`"
                :class="(lyricLine['time'] < (currentTime * 1000) ? 'playedLyricLine' : 'toPlayLyricLine') + ' ' + (lyricLine['endtime'] < (currentTime * 1000) ? 'endedLyricLine' : '')">
                <span v-for="(lyricSection, secindex) in lyricLine['sections']" :key="secindex"
                    :class="lyricSection['time'] < (currentTime * 1000) ? 'playedLyric' : 'toPlayLyric'">
                    {{ lyricSection['text'] }}
                </span>
            </div>
        </div>
        <div v-else-if="gotLyric === 'loading'">
            <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div v-else>
            Failed to fetch lyric.
        </div>
    </div>
</template>

<style scoped>
span {
    font-size: large;
}

span:empty:before {
    content: "\200b";
}

.playedLyric {
    color: white;
}

.toPlayLyric {
    color: darkgray;
}

.endedLyricLine * {
    color: #777;
}
</style>
