import { createApp } from 'vue'
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './style.css'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router'

// Import for routes
import SelectLibrary from './views/LoginSystem/SelectLibrary.vue';
import LibraryView from './views/LibraryView.vue';
import LibraryMenu from './views/LibraryMenu.vue';
import AlbumList from './views/LibraryViews/AlbumList.vue';
import AlbumInspect from './views/LibraryViews/AlbumInspect.vue';
import TrackList from './views/LibraryViews/TrackList.vue';

const routes = [
    { path: '/', component: SelectLibrary },
    {
        path: '/library/:libraryId(\\d+)/',
        component: LibraryView,
        children: [
            {
                path: '',
                component: LibraryMenu,
            },
            {
                path: 'album/',
                component: AlbumList,
            },
            {
                path: 'album/:albumId/',
                component: AlbumInspect
            },
            {
                path: 'track/',
                component: TrackList
            }
        ]
    },
]

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
})

const app = createApp(App);
app.use(router);
app.mount('#app');
