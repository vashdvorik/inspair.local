<script setup>
import { ref } from 'vue';
import { points } from './api';

defineProps({ rules: Object });

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
            <span class="g-eyebrow">Как играть</span>
            <span class="g-rules-toggle">{{ open ? 'Скрыть' : 'Показать правила' }}</span>
        </button>

        <ol v-if="open" class="g-rules-list">
            <li>
                <b>Расскажите о себе.</b>
                В профиле добавьте несколько коротких фактов (до {{ rules.max_facts }}): чем занимаетесь, что любите. За каждый факт вы получаете {{ points(rules.fact) }}. Из этих рассказов составляются вопросы для других участников.
            </li>
            <li>
                <b>Угадывайте.</b>
                Вам показывают вопрос про одного участника и трёх людей на выбор. Нажмите на того, о ком, по-вашему, речь.
            </li>
            <li>
                <b>Получайте очки.</b>
                Верный ответ даёт {{ points(rules.correct) }}, неверный даёт {{ points(rules.wrong) }}. После промаха нужно подождать {{ rules.pause }} секунд до следующего вопроса. Штрафов нет.
            </li>
            <li>
                <b>Растите уровень.</b>
                Чем больше очков, тем выше уровень. С уровня «{{ rules.people_level }}» открывается раздел «Люди», с уровня «{{ rules.opportunities_level }}» можно размещать возможности, а профили других участников раскрываются полнее.
            </li>
            <li>
                <b>Вы решаете сами.</b>
                Если не хотите попадать в вопросы, отметьте это в профиле. Контакты тоже можно закрыть.
            </li>
        </ol>
    </div>
</template>
