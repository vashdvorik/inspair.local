<script setup>
import { ref, computed, watch } from 'vue';
import { post, t } from './api';

// Факты о себе: правятся прямо в игре. Из них ИИ составляет вопросы, а за каждый факт идут очки.
const props = defineProps({ facts: Array, api: Object, max: Number, start: { type: Number, default: 3 } });
const emit = defineEmits(['saved']);

const existing = ref({});   // id -> текст
const added = ref([]);      // новые поля
const saving = ref(false);
const done = ref(false);
const error = ref('');

function reset() {
    existing.value = Object.fromEntries((props.facts || []).map((f) => [f.id, f.body]));
    // пустых полей столько, чтобы всего было не меньше трёх (а если фактов больше, одно пустое, пока есть место)
    const free = Math.max(0, props.max - (props.facts || []).length);
    added.value = Array.from({ length: Math.min(free, Math.max(1, props.start - (props.facts || []).length)) }, () => '');
}
reset();
watch(() => props.facts, reset);

const count = computed(() => (props.facts || []).length);
const canAdd = computed(() => count.value + added.value.length < props.max);

function addField() { if (canAdd.value) added.value.push(''); }

async function save() {
    saving.value = true; error.value = ''; done.value = false;
    try {
        const state = await post(props.api.facts, { facts: existing.value, new_facts: added.value });
        emit('saved', state);
        done.value = true;
        setTimeout(() => { done.value = false; }, 2500);
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <div class="g-eyebrow">{{ t('facts_title') }} · {{ t('facts_count', { n: count, start: start }) }}</div>
        <p class="g-muted" style="margin:6px 0 12px">{{ t('facts_lead') }}</p>

        <div class="g-facts-form">
            <input v-for="f in facts" :key="f.id" v-model="existing[f.id]" type="text" maxlength="300" class="g-input">
            <input v-for="(_, i) in added" :key="'n' + i" v-model="added[i]" type="text" maxlength="300" class="g-input" :placeholder="t('facts_ph')">
        </div>
        <p class="g-muted" style="margin-top:8px;font-size:.76rem">{{ t('facts_hint', { max: max }) }}</p>
        <p v-if="error" class="g-err" style="margin-top:8px">{{ error }}</p>

        <div class="g-row">
            <button class="g-btn" :disabled="saving" @click="save">{{ saving ? '…' : t('facts_save') }}</button>
            <button v-if="canAdd" class="g-btn g-btn--ghost" type="button" @click="addField">+</button>
            <span v-if="done" class="g-muted" style="color:#3d932e;font-weight:700">✓ {{ t('facts_saved') }}</span>
        </div>
    </div>
</template>
