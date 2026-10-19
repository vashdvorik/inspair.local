import { createApp } from 'vue';
import App from './App.vue';

const el = document.getElementById('game-app');

if (el) {
    createApp(App, {
        api: JSON.parse(el.dataset.api),
        locked: el.dataset.locked || '',
    }).mount(el);
}
