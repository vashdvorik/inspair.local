<script setup>
import { computed } from 'vue';
import { t } from './api';

const props = defineProps({ waiting: Object });
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

    </div>
</template>
