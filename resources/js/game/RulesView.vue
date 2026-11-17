<script setup>
import { ref } from 'vue';
import { computed } from 'vue';
import { points, t } from './api';

const props = defineProps({ rules: Object, levels: Array });
const levelNames = computed(() => (props.levels || []).map((l) => l.name).join(', '));

const KEY = 'inspire_game_rules_hidden';
const open = ref(true);
try { open.value = localStorage.getItem(KEY) !== '1'; } catch (e) { /* без хранилища правила просто видны */ }

function toggle() {
    open.value = !open.value;
    try { localStorage.setItem(KEY, open.value ? '0' : '1'); } catch (e) { /* не критично */ }
}
</script>

<template>
    <div class="g-card g-rules">
        <button class="g-rules-head" type="button" :aria-expanded="open" @click="toggle">
            <span class="g-eyebrow">{{ t('how_to_play') }}</span>
            <span class="g-rules-toggle">{{ open ? t('hide') : t('show_rules') }}</span>
        </button>

        <ol v-if="open" class="g-rules-list">
            <li>
                <b>{{ t('r1_title') }}</b>
                {{ t('r1', { max: rules.max_facts, fact: points(rules.fact) }) }}
            </li>
            <li>
                <b>{{ t('r2_title') }}</b>
                {{ t('r2') }}
            </li>
            <li>
                <b>{{ t('r3_title') }}</b>
                {{ t('r3', { correct: points(rules.correct), wrong: points(rules.wrong), pause: rules.pause }) }}
            </li>
            <li>
                <b>{{ t('r4_title') }}</b>
                {{ t('r4', { levels: levelNames }) }}
            </li>
            <li>
                <b>{{ t('r5_title') }}</b>
                {{ t('r5') }}
            </li>
        </ol>
    </div>
</template>
