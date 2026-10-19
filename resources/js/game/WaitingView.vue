<script setup>
import { computed } from 'vue';

const props = defineProps({ waiting: Object, checklist: Array, editUrl: String });
const percent = computed(() => Math.min(100, Math.round((props.waiting.ready / props.waiting.target) * 100)));
const left = computed(() => Math.max(0, props.waiting.target - props.waiting.ready));
</script>

<template>
    <div class="g-card">
        <div class="g-eyebrow">Игра скоро откроется</div>
        <h2 class="g-q">Ждём, пока нас станет больше</h2>
        <p class="g-muted">Для интересных загадок нужно минимум {{ waiting.target }} участников с заполненным профилем.</p>
        <div class="g-prog"><div :style="{ width: percent + '%' }"></div></div>
        <div class="g-prog-n"><span>Уже <b>{{ waiting.ready }}</b> из {{ waiting.target }}</span><span v-if="left">осталось {{ left }}</span><span v-else>достаточно, ждём запуска</span></div>

        <h3 style="margin:22px 0 4px">Пока ждём, заполните профиль</h3>
        <p class="g-muted" style="margin-bottom:6px">Очки уже копятся: в игру вы войдёте с запасом.</p>
        <div class="g-check" v-for="c in checklist" :key="c.key" :class="{ done: c.done }">
            <span class="g-tick">{{ c.done ? '✓' : '' }}</span>
            <span>{{ c.label }}</span>
            <b>+{{ c.points }}</b>
        </div>
        <div class="g-row"><a class="g-btn" :href="editUrl">Дополнить профиль</a></div>
    </div>
</template>
