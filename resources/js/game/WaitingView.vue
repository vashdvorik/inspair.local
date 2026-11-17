<script setup>
import { computed } from 'vue';
import { t } from './api';

const props = defineProps({ waiting: Object, checklist: Array, editUrl: String });
const percent = computed(() => Math.min(100, Math.round((props.waiting.ready / props.waiting.target) * 100)));
const left = computed(() => Math.max(0, props.waiting.target - props.waiting.ready));
</script>

<template>
    <div class="g-card">
        <div class="g-eyebrow">{{ t('soon') }}</div>
        <h2 class="g-q">{{ t('wait_title') }}</h2>
        <p class="g-muted">{{ t('wait_text', { n: waiting.target }) }}</p>
        <div class="g-prog"><div :style="{ width: percent + '%' }"></div></div>
        <div class="g-prog-n"><span>{{ t('already', { ready: waiting.ready, target: waiting.target }) }}</span><span v-if="left">{{ t('left', { n: left }) }}</span><span v-else>{{ t('enough') }}</span></div>

        <h3 style="margin:22px 0 4px">{{ t('fill_title') }}</h3>
        <p class="g-muted" style="margin-bottom:6px">{{ t('fill_text') }}</p>
        <div class="g-check" v-for="c in checklist" :key="c.key" :class="{ done: c.done }">
            <span class="g-tick">{{ c.done ? '✓' : '' }}</span>
            <span>{{ c.label }}</span>
            <b>+{{ c.points }}</b>
        </div>
        <div class="g-row"><a class="g-btn" :href="editUrl">{{ t('fill_btn') }}</a></div>
    </div>
</template>
