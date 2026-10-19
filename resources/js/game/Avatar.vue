<script setup>
import { computed } from 'vue';
import { initials } from './api';

const props = defineProps({
    person: { type: Object, required: true },
    size: { type: Number, default: 44 },
});

const color = computed(() => props.person.level?.color || '#019eff');
const style = computed(() => ({
    width: props.size + 'px',
    height: props.size + 'px',
    fontSize: props.size * 0.34 + 'px',
    '--ring': color.value,
}));
</script>

<template>
    <span class="g-av" :style="style">
        <img v-if="person.avatar" :src="person.avatar" :alt="person.name">
        <template v-else>{{ initials(person.name) }}</template>
    </span>
</template>

<style>
.g-av { display: inline-grid; place-items: center; flex: none; border-radius: 50%; overflow: hidden; color: #fff; font-weight: 700;
        background: linear-gradient(135deg, #006de0, #019eff); border: 3px solid var(--ring, #019eff); box-sizing: border-box; }
.g-av img { width: 100%; height: 100%; object-fit: cover; display: block; }
</style>
