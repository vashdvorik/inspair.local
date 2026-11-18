<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { get, post, points, t } from './api';
import Avatar from './Avatar.vue';

const props = defineProps({ api: Object, factsDone: Boolean });
const emit = defineEmits(['me', 'levelup', 'network-changed', 'goto']);

const state = ref('loading'); // loading | ok | empty | wait | closed | error
const quest = ref(null);
const result = ref(null);
const chosen = ref(null);
const busy = ref(false);
const wait = ref(0);
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
        // 404: игру выключили, пока страница была открыта; повторять запрос бессмысленно
        state.value = e.status === 404 ? 'closed' : 'error';
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
        if (r.connected) emit('network-changed');
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

function reload() { location.reload(); }

onMounted(load);
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div>
        <div v-if="state === 'loading'" class="g-muted">{{ t('loading_quest') }}</div>

        <div v-else-if="state === 'empty'" class="g-center">
            <div class="g-big-emoji">🌱</div>
            <h3>{{ t('empty_title') }}</h3>
            <p class="g-muted">{{ t('empty_text') }}</p>
            <div class="g-row" style="justify-content:center"><button class="g-btn" @click="emit('goto', 'facts')">{{ factsDone ? t('empty_more_facts') : t('empty_cta') }}</button></div>
        </div>

        <div v-else-if="state === 'wait'" class="g-center">
            <h3>{{ t('rest', { n: wait }) }}</h3>
            <p class="g-muted">{{ t('rest_text') }}</p>
        </div>

        <div v-else-if="state === 'closed'" class="g-center">
            <p class="g-muted">{{ t('game_closed') }}</p>
            <div class="g-row" style="justify-content:center"><button class="g-btn g-btn--ghost" @click="reload">{{ t('reload') }}</button></div>
        </div>

        <div v-else-if="state === 'error'" class="g-center">
            <p class="g-muted">{{ error || t('load_failed') }}</p>
            <button class="g-btn" @click="load">{{ t('retry') }}</button>
        </div>

        <div v-else>
            <h2 class="g-q">{{ quest.question }}</h2>

            <button v-for="o in quest.options" :key="o.id" class="g-opt"
                    :class="{ ok: result && o.id === result.correct_user_id, bad: result && chosen === o.id && !result.correct }"
                    :disabled="!!result || busy" @click="pick(o)">
                <Avatar :person="o" :size="38" />
                <span><b>{{ o.name }}</b></span>
            </button>

            <p v-if="!result" class="g-muted g-src">{{ t('ai_note') }}</p>

            <div v-if="result" class="g-res" :class="result.correct ? 'good' : 'miss'">
                <b v-if="result.correct">{{ t('right', { name: result.subject?.name, points: points(result.gained) }) }}</b>
                <b v-else>{{ t('wrong', { name: result.subject?.name, points: points(result.gained) }) }}</b>
                <span>{{ result.explanation }}</span>
            </div>
            <p v-if="error && !result" class="g-err">{{ error }}</p>

            <div class="g-row">
                <button v-if="result" class="g-btn" :disabled="wait > 0" @click="load">
                    {{ wait > 0 ? t('next_in', { n: wait }) : t('next') }}
                </button>
            </div>
        </div>
    </div>
</template>
