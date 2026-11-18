<script setup>
import { ref } from 'vue';
import { computed } from 'vue';
import { points, t } from './api';

const props = defineProps({ rules: Object, levels: Array });
const levelNames = computed(() => (props.levels || []).map((l) => l.name).join(', '));

const open = ref(true);   // правила живут на своей вкладке и всегда раскрыты
</script>

<template>
    <div class="g-rules">
        <div class="g-eyebrow">{{ t('how_to_play') }}</div>

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
