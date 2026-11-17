import { createApp } from 'vue';
import App from './App.vue';
import { setStrings } from './api';

const el = document.getElementById('game-app');

if (el) {
    setStrings(JSON.parse(el.dataset.i18n || '{}'));
    createApp(App, {
        api: JSON.parse(el.dataset.api),
        locked: el.dataset.locked || '',
    }).mount(el);
}
