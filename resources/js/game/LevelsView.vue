<script setup>
defineProps({ levels: Array, me: Object, checklist: Array, editUrl: String });
</script>

<template>
    <div>
        <div class="g-eyebrow">Уровни</div>
        <div class="g-ladder">
            <div v-for="l in levels" :key="l.n" class="g-step"
                 :class="{ cur: l.n === me.level.n, locked: l.n > me.level.n }" :style="{ '--c': l.color }">
                <div class="g-dot">{{ l.n }}</div>
                <div>
                    <b>{{ l.name }}</b> <span class="g-muted" style="font-size:.74rem">от {{ l.from }} очков</span>
                    <ul><li v-for="u in l.unlocks" :key="u">{{ u }}</li></ul>
                </div>
            </div>
        </div>

        <div class="g-eyebrow" style="margin-top:22px">Быстро открыть доступ</div>
        <p class="g-muted" style="margin-bottom:6px">Расскажите о себе: это сразу даёт очки и открывает разделы.</p>
        <div class="g-check" v-for="c in checklist" :key="c.key" :class="{ done: c.done }">
            <span class="g-tick">{{ c.done ? '✓' : '' }}</span>
            <span>{{ c.label }}</span>
            <b>+{{ c.points }}</b>
        </div>
        <div class="g-row"><a class="g-btn" :href="editUrl">Дополнить профиль</a></div>
    </div>
</template>
