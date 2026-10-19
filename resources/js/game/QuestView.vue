<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { get, post } from './api';
import Avatar from './Avatar.vue';

const props = defineProps({ api: Object });
const emit = defineEmits(['me', 'levelup', 'network-changed']);

const state = ref('loading'); // loading | ok | empty | wait | error
const quest = ref(null);
const result = ref(null);
const chosen = ref(null);
const busy = ref(false);
const wait = ref(0);
const reported = ref(false);
const error = ref('');
let timer = null;

function startCountdown(seconds, then) {
    clearInterval(timer);
    wait.value = seconds;
    timer = setInterval(() => {
        wait.value -= 1;
        if (wait.value <= 0) {
            clearInterval(timer);
            wait.value = 0;
            if (then) then();
        }
    }, 1000);
}

async function load() {
    state.value = 'loading';
    result.value = null;
    chosen.value = null;
    reported.value = false;
    error.value = '';
    try {
        const q = await get(props.api.quest);
        if (q.status === 'ok') {
            quest.value = q;
            state.value = 'ok';
        } else if (q.status === 'wait') {
            state.value = 'wait';
            startCountdown(q.wait, load);
        } else {
            state.value = 'empty';
        }
    } catch (e) {
        error.value = e.message;
        state.value = 'error';
    }
}

async function pick(option) {
    if (busy.value || result.value) return;
    busy.value = true;
    chosen.value = option.id;
    try {
        const r = await post(`${props.api.quest}/${quest.value.id}/answer`, { chosen: option.id });
        result.value = r;
        emit('me', r.me);
        if (r.connected || r.mutual) emit('network-changed');
        if (r.level_up_info) emit('levelup', r.level_up_info);
        if (r.wait > 0) startCountdown(r.wait);
    } catch (e) {
        error.value = e.message;
        if (e.status === 429 && e.data?.wait) {
            state.value = 'wait';
            startCountdown(e.data.wait, load);
        } else if (e.status === 409 || e.status === 422) {
            load();
        }
    } finally {
        busy.value = false;
    }
}

async function report() {
    if (reported.value) return;
    reported.value = true;
    try { await post(`${props.api.quest}/${quest.value.id}/report`, {}); } catch (e) { /* не критично */ }
}

onMounted(load);
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div>
        <div v-if="state === 'loading'" class="g-muted">Загружаю квест…</div>

        <div v-else-if="state === 'empty'" class="g-card g-center">
            <div class="g-big-emoji">🌱</div>
            <h3>Пока квестов нет</h3>
            <p class="g-muted">Новые загадки появляются, когда участники рассказывают о себе. Загляните позже: бот напомнит, когда появится новый квест.</p>
        </div>

        <div v-else-if="state === 'wait'" class="g-card g-center">
            <h3>Передохните {{ wait }} с</h3>
            <p class="g-muted">Следующий квест откроется через пару секунд.</p>
        </div>

        <div v-else-if="state === 'error'" class="g-card g-center">
            <p class="g-muted">{{ error || 'Не удалось загрузить.' }}</p>
            <button class="g-btn" @click="load">Повторить</button>
        </div>

        <div v-else>
            <div class="g-eyebrow">Квест</div>
            <h2 class="g-q">{{ quest.question }}</h2>
            <p class="g-muted g-src">Вопрос составлен ИИ по рассказам участников · выберите одного из троих</p>

            <button v-for="o in quest.options" :key="o.id" class="g-opt"
                    :class="{ ok: result && o.id === result.correct_user_id, bad: result && chosen === o.id && !result.correct }"
                    :disabled="!!result || busy" @click="pick(o)">
                <Avatar :person="o" :size="46" />
                <span><b>{{ o.name }}</b></span>
            </button>

            <div v-if="result" class="g-res" :class="result.correct ? 'good' : 'miss'">
                <b v-if="result.correct">Верно! {{ result.subject?.name }} теперь в вашей сети · +{{ result.gained }} очков<span v-if="result.mutual"> · Взаимная нить!</span></b>
                <b v-else>Не угадали. Это {{ result.subject?.name }} · +{{ result.gained }}</b>
                <span>{{ result.explanation }}</span>
            </div>
            <p v-if="error && !result" class="g-err">{{ error }}</p>

            <div class="g-row">
                <button v-if="result" class="g-btn" :disabled="wait > 0" @click="load">
                    {{ wait > 0 ? `Следующий вопрос через ${wait} с` : 'Следующий вопрос' }}
                </button>
                <button class="g-link" :disabled="reported" @click="report">{{ reported ? 'жалоба отправлена' : 'пожаловаться на вопрос' }}</button>
            </div>
        </div>
    </div>
</template>
