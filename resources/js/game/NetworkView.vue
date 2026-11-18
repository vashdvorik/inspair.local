<script setup>
import { ref, onMounted, watch } from 'vue';
import { get, t } from './api';
import Avatar from './Avatar.vue';

const props = defineProps({ api: Object, refreshKey: Number });
const emit = defineEmits(['me']);

const nodes = ref([]);
const selected = ref(null);
const loading = ref(true);

async function load() {
    loading.value = true;
    try {
        const r = await get(props.api.network);
        nodes.value = r.nodes;
        emit('me', r.me);
        if (selected.value) selected.value = nodes.value.find((p) => p.id === selected.value.id) || null;
    } finally {
        loading.value = false;
    }
}

const toggle = (p) => { selected.value = selected.value && selected.value.id === p.id ? null : p; };

onMounted(load);
watch(() => props.refreshKey, load);
</script>

<template>
    <div>
        <div class="g-eyebrow">{{ t('my_network', { n: nodes.length }) }}</div>
        <p v-if="!nodes.length && !loading" class="g-muted" style="margin-top:8px">{{ t('net_empty') }}</p>

        <div class="g-people">
            <button v-for="p in nodes" :key="p.id" class="g-pcard" :class="{ on: selected && selected.id === p.id }" @click="toggle(p)">
                <Avatar :person="p" :size="46" />
                <span><b>{{ p.name }}</b><small :style="{ color: p.level.color }">{{ p.level.name }}</small></span>
            </button>
        </div>

        <div v-if="selected" class="g-sub">
            <div class="g-person">
                <Avatar :person="selected" :size="56" />
                <div>
                    <b>{{ selected.name }}</b>
                    <div class="g-lvl" :style="{ color: selected.level.color }"><i :style="{ background: selected.level.color }"></i>{{ selected.level.name }}</div>
                </div>
            </div>
            <p v-if="selected.description" class="g-line">{{ selected.description }}</p>
            <ul v-if="selected.facts && selected.facts.length" class="g-facts"><li v-for="f in selected.facts" :key="f">{{ f }}</li></ul>
            <div class="g-row">
                <a v-if="selected.write_url" class="g-btn" :href="selected.write_url" target="_blank" rel="noopener">{{ t('write_tg') }}</a>
                <span v-else class="g-muted">{{ t('contacts_hidden') }}</span>
                <a class="g-btn g-btn--ghost" :href="selected.profile_url">{{ t('profile') }}</a>
            </div>
        </div>
    </div>
</template>
