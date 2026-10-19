<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { get, post, initials } from './api';
import Avatar from './Avatar.vue';

const props = defineProps({ api: Object, me: Object, incoming: Array, refreshKey: Number });
const emit = defineEmits(['me', 'changed']);

const nodes = ref([]);
const selected = ref(null);
const loading = ref(true);
const canvas = ref(null);
let raf = 0;
let last = 0;
const images = {};
let placed = [];

async function load() {
    loading.value = true;
    try {
        const r = await get(props.api.network);
        nodes.value = r.nodes;
        emit('me', r.me);
    } finally {
        loading.value = false;
    }
    layout();
}

function layout() {
    const n = nodes.value.length;
    placed = nodes.value.map((p, i) => {
        const ring = i % 2;
        const a = (i / Math.max(n, 1)) * Math.PI * 2 - 1.2;
        const r = 170 + ring * 70 + Math.floor(i / 10) * 40;
        if (p.avatar && !images[p.avatar]) {
            const img = new Image();
            img.src = p.avatar;
            images[p.avatar] = img;
        }
        return { p, x: 350 + Math.cos(a) * r, y: 340 + Math.sin(a) * r * 0.78, ph: i * 1.3, dx: 0, dy: 0 };
    });
}

function draw(t) {
    raf = requestAnimationFrame(draw);
    if (t - last < 33 || !canvas.value) return;
    last = t;
    const cx = canvas.value.getContext('2d');
    cx.clearRect(0, 0, 700, 680);

    placed.forEach((n) => { n.dx = Math.sin(t / 1500 + n.ph) * 6; n.dy = Math.cos(t / 1700 + n.ph) * 6; });

    placed.forEach((n) => {
        cx.lineWidth = n.p.mutual ? 4 : 2;
        cx.strokeStyle = n.p.mutual ? 'rgba(253,186,7,.75)' : 'rgba(0,109,224,.22)';
        cx.beginPath(); cx.moveTo(350, 340); cx.lineTo(n.x + n.dx, n.y + n.dy); cx.stroke();
    });

    placed.forEach((n) => {
        const x = n.x + n.dx, y = n.y + n.dy, col = n.p.level.color;
        cx.fillStyle = col + '33'; cx.beginPath(); cx.arc(x, y, 40, 0, 7); cx.fill();
        cx.fillStyle = col; cx.beginPath(); cx.arc(x, y, 31, 0, 7); cx.fill();
        const img = n.p.avatar ? images[n.p.avatar] : null;
        if (img && img.complete && img.naturalWidth) {
            cx.save(); cx.beginPath(); cx.arc(x, y, 27, 0, 7); cx.clip(); cx.drawImage(img, x - 27, y - 27, 54, 54); cx.restore();
        } else {
            cx.fillStyle = '#fff'; cx.font = '700 21px Montserrat, sans-serif'; cx.textAlign = 'center'; cx.fillText(initials(n.p.name), x, y + 8);
        }
    });

    const col = props.me?.level?.color || '#019eff';
    const g = cx.createRadialGradient(350, 340, 5, 350, 340, 62);
    g.addColorStop(0, '#fff'); g.addColorStop(1, 'rgba(1,158,255,.25)');
    cx.fillStyle = g; cx.beginPath(); cx.arc(350, 340, 60, 0, 7); cx.fill();
    cx.fillStyle = col; cx.beginPath(); cx.arc(350, 340, 40, 0, 7); cx.fill();
    cx.fillStyle = '#fff'; cx.font = '800 22px Montserrat, sans-serif'; cx.textAlign = 'center'; cx.fillText('ТЫ', 350, 348);
}

function onClick(e) {
    const r = canvas.value.getBoundingClientRect();
    const s = 700 / r.width;
    const x = (e.clientX - r.left) * s, y = (e.clientY - r.top) * s;
    const hit = placed.find((n) => Math.hypot(n.x + n.dx - x, n.y + n.dy - y) < 42);
    selected.value = hit ? hit.p : null;
}

async function reciprocate(p) {
    try {
        const r = await post(`${props.api.reciprocate}/${p.id}/reciprocate`, {});
        emit('me', r.me);
        emit('changed');
        await load();
    } catch (e) { /* ignore */ }
}

function onVisibility() {
    cancelAnimationFrame(raf);
    if (!document.hidden) raf = requestAnimationFrame(draw);
}

onMounted(async () => {
    await load();
    await nextTick();
    raf = requestAnimationFrame(draw);
    document.addEventListener('visibilitychange', onVisibility);
});
onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    document.removeEventListener('visibilitychange', onVisibility);
});
watch(() => props.refreshKey, load);
</script>

<template>
    <div>
        <div v-if="incoming && incoming.length" class="g-card" style="margin-bottom:14px">
            <div class="g-eyebrow">Вас узнали</div>
            <div v-for="p in incoming" :key="p.id" class="g-incoming">
                <Avatar :person="p" :size="40" />
                <span><b>{{ p.name }}</b> угадал(а) вас</span>
                <button class="g-btn g-btn--sm" @click="reciprocate(p)">Ответить взаимностью</button>
            </div>
        </div>

        <div class="g-eyebrow">Моя сеть · {{ nodes.length }} {{ nodes.length === 1 ? 'нить' : 'нитей' }}</div>
        <p v-if="!nodes.length && !loading" class="g-muted">Сеть пока пуста. Отвечайте на квесты: каждый верный ответ добавляет человека к вам.</p>
        <canvas ref="canvas" width="700" height="680" class="g-canvas" @click="onClick"></canvas>
        <p class="g-muted" style="margin-top:8px">Золотая нить означает, что вы узнали друг друга.</p>

        <div v-if="selected" class="g-card">
            <div class="g-person">
                <Avatar :person="selected" :size="56" />
                <div>
                    <b>{{ selected.name }}</b>
                    <div class="g-lvl" :style="{ color: selected.level.color }"><i :style="{ background: selected.level.color }"></i>{{ selected.level.name }}</div>
                </div>
            </div>
            <p v-if="selected.region" class="g-line">📍 {{ selected.region }}</p>
            <p v-if="selected.description" class="g-line">{{ selected.description }}</p>
            <ul v-if="selected.facts && selected.facts.length" class="g-facts"><li v-for="f in selected.facts" :key="f">{{ f }}</li></ul>
            <p v-for="l in selected.locks" :key="l.field" class="g-lock">🔒 {{ { region: 'Регион', description: 'О себе', expectation: 'Ожидания' }[l.field] }}: откроется на уровне «{{ l.level }}»</p>
            <div class="g-row">
                <a v-if="selected.write_url" class="g-btn" :href="selected.write_url" target="_blank" rel="noopener">Написать в Telegram</a>
                <span v-else class="g-muted">Контакты скрыты участником</span>
                <a class="g-btn g-btn--ghost" :href="selected.profile_url">Профиль</a>
            </div>
        </div>
    </div>
</template>
