<script setup>
import { points, t } from './api';
defineProps({ levels: Array, me: Object });
</script>

<template>
    <div>
        <div class="g-eyebrow">{{ t('levels') }}</div>
        <div class="g-ladder">
            <div v-for="l in levels" :key="l.n" class="g-step"
                 :class="{ cur: l.n === me.level.n, locked: l.n > me.level.n }" :style="{ '--c': l.color }">
                <div class="g-dot">{{ l.n }}</div>
                <div>
                    <b>{{ l.name }}</b> <span class="g-muted" style="font-size:.74rem">{{ t('from', { points: points(l.from) }) }}</span>
                </div>
            </div>
        </div>

    </div>
</template>
