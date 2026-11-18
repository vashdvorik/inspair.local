<script setup>
import { computed } from 'vue';
import { t } from './api';

// «Быстро набрать очки»: фото, рассказ о себе и факты. Фото и «о себе» правятся в профиле, факты здесь же, в игре.
const props = defineProps({ checklist: Array, editUrl: String });
const needsProfile = computed(() => (props.checklist || []).some((c) => c.key !== 'facts' && !c.done));
</script>

<template>
    <div>
        <div class="g-eyebrow">{{ t('quick') }}</div>
        <p class="g-muted" style="margin-bottom:6px">{{ t('quick_text') }}</p>
        <div class="g-check" v-for="c in checklist" :key="c.key" :class="{ done: c.done }">
            <span class="g-tick">{{ c.done ? '✓' : '' }}</span>
            <span>{{ c.label }}</span>
            <b>+{{ c.points }}</b>
        </div>
        <div v-if="needsProfile" class="g-row"><a class="g-btn g-btn--ghost g-btn--sm" :href="editUrl">{{ t('fill_btn') }}</a></div>
    </div>
</template>
