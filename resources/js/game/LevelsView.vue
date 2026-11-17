<script setup>
import { points, t } from './api';
defineProps({ levels: Array, me: Object, checklist: Array, editUrl: String });
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

        <div class="g-eyebrow" style="margin-top:18px">{{ t('quick') }}</div>
        <p class="g-muted" style="margin-bottom:6px">{{ t('quick_text') }}</p>
        <div class="g-check" v-for="c in checklist" :key="c.key" :class="{ done: c.done }">
            <span class="g-tick">{{ c.done ? '✓' : '' }}</span>
            <span>{{ c.label }}</span>
            <b>+{{ c.points }}</b>
        </div>
        <div class="g-row"><a class="g-btn" :href="editUrl">{{ t('fill_btn') }}</a></div>
    </div>
</template>
