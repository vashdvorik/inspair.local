<script setup>
import { ref, onMounted, computed } from 'vue';
import { get, points, t } from './api';
import Avatar from './Avatar.vue';
import QuestView from './QuestView.vue';
import NetworkView from './NetworkView.vue';
import LevelsView from './LevelsView.vue';
import WaitingView from './WaitingView.vue';
import RulesView from './RulesView.vue';
import FactsView from './FactsView.vue';
import ChecklistView from './ChecklistView.vue';

const props = defineProps({ api: Object, locked: String });

const state = ref(null);
const me = ref(null);
const tab = ref('quest');
const levelUp = ref(null);
const netKey = ref(0);
const error = ref('');

const lockedLabel = { people: t('section_people'), opportunities: t('section_opportunities') };
const playable = computed(() => state.value && (state.value.launched || state.value.staff));
const factsCount = computed(() => (state.value?.facts || []).length);
const factsStart = 3;
const factsDone = computed(() => factsCount.value >= factsStart);

async function loadState() {
    try {
        state.value = await get(props.api.state);
        me.value = state.value.me;
        // первый заход без фактов: сначала расскажите о себе, из фактов делаются вопросы для всех
        tab.value = playable.value && factsDone.value ? 'quest' : 'facts';
    } catch (e) {
        error.value = e.status === 404 ? t('game_closed') : e.message;
    }
}

function onMe(m) { if (m) me.value = m; }
function onNetworkChanged() { netKey.value += 1; }
// после сохранения фактов сервер возвращает обновлённое состояние: очки, чек-лист и список фактов
function onFactsSaved(s) { state.value = s; me.value = s.me; }

onMounted(loadState);
</script>

<template>
    <div class="g-wrap">
        <div v-if="error" class="g-card g-err">{{ error }}</div>
        <div v-else-if="!state" class="g-muted">{{ t('loading') }}</div>

        <template v-else>
            <div v-if="locked" class="g-alert">
                {{ t('locked', { section: lockedLabel[locked] || locked }) }}
            </div>

            <!-- я: уровень и очки, всегда на виду -->
            <div class="g-me g-card g-me--compact">
                <Avatar :person="{ name: me.name, avatar: me.avatar, level: me.level }" :size="44" />
                <div class="g-me-main">
                    <b>{{ me.name }}</b>
                    <div class="g-lvl" :style="{ color: me.level.color }"><i :style="{ background: me.level.color }"></i>{{ me.level.name }} · {{ points(me.xp) }}</div>
                    <div v-if="playable" class="g-xp"><div :style="{ width: me.percent + '%', background: me.level.color }"></div></div>
                    <div v-if="playable && me.next" class="g-xpn"><span>{{ t('to_level', { name: me.next.name, n: me.next.from - me.xp }) }}</span></div>
                </div>
            </div>

            <!-- игра ещё не запущена: сколько людей нужно для старта -->
            <WaitingView v-if="!playable" :waiting="state.waiting" />

            <!-- разделы -->
            <nav class="g-tabs" role="tablist">
                <button v-if="playable" role="tab" :class="{ on: tab === 'quest' }" @click="tab = 'quest'">{{ t('tab_play') }}</button>
                <button role="tab" :class="{ on: tab === 'facts' }" @click="tab = 'facts'">
                    {{ t('tab_facts') }}<i v-if="!factsDone" class="g-badge">{{ factsCount }}/{{ factsStart }}</i>
                </button>
                <button v-if="playable" role="tab" :class="{ on: tab === 'people' }" @click="tab = 'people'">{{ t('tab_people') }}</button>
                <button role="tab" :class="{ on: tab === 'levels' }" @click="tab = 'levels'">{{ t('tab_levels') }}</button>
            </nav>

            <!-- играть: один вопрос на весь экран -->
            <div v-show="tab === 'quest'" v-if="playable" class="g-card">
                <QuestView :api="api" :facts-done="factsDone" @me="onMe" @levelup="levelUp = $event" @network-changed="onNetworkChanged" @goto="tab = $event" />
            </div>

            <!-- мои факты и быстрые очки -->
            <div v-if="tab === 'facts'" class="g-card">
                <FactsView :facts="state.facts" :api="api" :max="state.rules.max_facts" :start="factsStart" @saved="onFactsSaved" />
                <button v-if="playable && factsDone" class="g-btn g-btn--ghost g-btn--sm" style="margin-top:14px" @click="tab = 'quest'">{{ t('go_play') }}</button>
                <hr class="g-hr">
                <ChecklistView :checklist="state.checklist" :edit-url="state.edit_url" />
            </div>

            <!-- кого я уже узнал(а) -->
            <div v-if="playable" v-show="tab === 'people'" class="g-card">
                <NetworkView :api="api" :refresh-key="netKey" @me="onMe" />
            </div>

            <!-- уровни и правила -->
            <div v-if="tab === 'levels'" class="g-card">
                <LevelsView :levels="state.levels" :me="me" />
                <hr class="g-hr">
                <RulesView :rules="state.rules" :levels="state.levels" />
            </div>
        </template>
        <div v-if="levelUp" class="g-modal" @click.self="levelUp = null">
            <div class="g-modal-box">
                <div class="g-bigdot" :style="{ background: levelUp.color }">{{ levelUp.n }}</div>
                <div class="g-eyebrow">{{ t('new_level') }}</div>
                <h2 class="g-q" style="margin:6px 0">{{ levelUp.name }}</h2>
                <p class="g-muted" style="margin-bottom:18px">{{ t('keep_going') }}</p>
                <button class="g-btn" @click="levelUp = null">{{ t('continue') }}</button>
            </div>
        </div>
    </div>
</template>

<style>
.g-wrap { max-width: 760px; margin: 0 auto; font-family: Montserrat, system-ui, sans-serif; color: #10264a; }
.g-wrap * { box-sizing: border-box; }
.g-muted { color: #5b6f8d; font-size: .84rem; line-height: 1.5; margin: 0; }
.g-card { background: #fff; border: 1px solid #e3ecf8; border-radius: 22px; padding: 20px; margin-bottom: 14px; box-shadow: 0 8px 24px rgba(0, 56, 122, .06); }
.g-center { text-align: center; }
.g-hr { border: 0; border-top: 1px solid #e3ecf8; margin: 18px 0; }
.g-sub { margin-top: 14px; padding: 14px; background: #f6faff; border-radius: 16px; }
.g-me--compact { padding: 14px 16px; }
.g-tabs { display: flex; gap: 2px; margin: 0 0 14px; padding: 4px; background: #eaf3fe; border-radius: 16px; overflow-x: auto; scrollbar-width: none; }
.g-tabs::-webkit-scrollbar { display: none; }
.g-tabs button { flex: 1 1 auto; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap; border: 0; border-radius: 12px; padding: 9px 8px; background: none; color: #5b6f8d; font: 700 .78rem Montserrat, system-ui, sans-serif; cursor: pointer; transition: background .2s, color .2s; }
.g-tabs button.on { background: #fff; color: #006de0; box-shadow: 0 2px 8px rgba(0, 56, 122, .1); }
.g-badge { font-style: normal; padding: 1px 7px; border-radius: 99px; background: #ff6c0b; color: #fff; font-size: .68rem; }
.g-facts-form { display: grid; gap: 8px; }
.g-input { width: 100%; border: 1.5px solid #e3ecf8; border-radius: 14px; background: #f6faff; padding: 11px 14px; font: 500 .86rem Montserrat, system-ui, sans-serif; color: #10264a; outline: none; transition: border-color .2s, background .2s; }
.g-input:focus { border-color: #019eff; background: #fff; box-shadow: 0 0 0 3px rgba(1, 158, 255, .15); }
.g-input::placeholder { color: #8497b1; }
.g-big-emoji { font-size: 2.4rem; margin-bottom: 6px; }
.g-eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #006de0; }
.g-q { font-size: 1.2rem; font-weight: 700; line-height: 1.4; margin: 8px 0 6px; }
.g-src { margin-bottom: 16px; }
.g-err { color: #e31e27; font-size: .84rem; }
.g-alert { background: #fff5ea; border: 1px solid #ffdcb8; border-radius: 16px; padding: 12px 16px; font-size: .84rem; margin-bottom: 14px; }

.g-rules-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 0; border: 0; background: none; cursor: pointer; font: inherit; text-align: left; }
.g-rules-toggle { font-size: .76rem; font-weight: 600; color: #5b6f8d; text-decoration: underline; text-underline-offset: 3px; }
.g-rules-list { list-style: none; counter-reset: r; margin: 14px 0 0; padding: 0; display: grid; gap: 12px; }
.g-rules-list li { counter-increment: r; position: relative; padding-left: 40px; font-size: .86rem; line-height: 1.6; color: #26405f; }
.g-rules-list li::before { content: counter(r); position: absolute; left: 0; top: 0; width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; background: #eaf3fe; color: #006de0; font-weight: 800; font-size: .8rem; }
.g-rules-list b { color: #10264a; }
.g-me { display: flex; gap: 14px; align-items: center; }
.g-me-main { flex: 1; min-width: 0; }
.g-me-main > b { display: block; margin-bottom: 2px; }
.g-lvl { display: inline-flex; align-items: center; gap: 6px; font-size: .76rem; font-weight: 700; }
.g-lvl i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
.g-xp { height: 8px; border-radius: 99px; background: #e8f1fc; overflow: hidden; margin-top: 8px; }
.g-xp > div { height: 100%; border-radius: 99px; transition: width .6s cubic-bezier(.2, .8, .2, 1), background .4s; }
.g-xpn { display: flex; justify-content: space-between; font-size: .7rem; color: #5b6f8d; margin-top: 5px; }


.g-opt { display: flex; align-items: center; gap: 14px; width: 100%; text-align: left; background: #fff; border: 1.5px solid #e3ecf8; border-radius: 18px; padding: 12px 14px; margin-bottom: 10px; cursor: pointer; font: inherit; color: inherit; transition: transform .15s, border-color .2s, box-shadow .2s; }
.g-opt:hover:not(:disabled) { transform: translateY(-2px); border-color: #019eff; box-shadow: 0 10px 24px rgba(0, 109, 224, .12); }
.g-opt:disabled { cursor: default; }
.g-opt.ok { border-color: #3d932e; background: #eef8ec; animation: g-pop .45s; }
.g-opt.bad { border-color: #e31e27; background: #fdeeee; animation: g-shake .35s; }
@keyframes g-pop { 50% { transform: scale(1.03); } }
@keyframes g-shake { 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
.g-res { margin-top: 6px; border-radius: 18px; padding: 14px; font-size: .86rem; line-height: 1.5; animation: g-in .3s; }
.g-res b { display: block; margin-bottom: 4px; }
.g-res.good { background: #eef8ec; border: 1px solid #cfe8c9; }
.g-res.miss { background: #fff5ea; border: 1px solid #ffdcb8; }
@keyframes g-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

.g-btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 999px; padding: 12px 22px; font: 700 .85rem Montserrat, sans-serif; color: #fff; background: linear-gradient(135deg, #006de0, #019eff); cursor: pointer; text-decoration: none; box-shadow: 0 8px 22px rgba(0, 109, 224, .25); }
.g-btn:disabled { background: #cfdcef; box-shadow: none; cursor: default; }
.g-btn--sm { padding: 8px 14px; font-size: .76rem; }
.g-btn--ghost { background: #fff; color: #006de0; border: 1.5px solid #cfe3fb; box-shadow: none; }
.g-row { display: flex; gap: 10px; margin-top: 14px; align-items: center; flex-wrap: wrap; }

.g-people { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 10px; margin-top: 12px; }
.g-pcard { display: flex; align-items: center; gap: 12px; text-align: left; background: #fff; border: 1.5px solid #e3ecf8; border-radius: 18px; padding: 10px 12px; cursor: pointer; font: inherit; color: inherit; transition: border-color .2s, box-shadow .2s; }
.g-pcard:hover, .g-pcard.on { border-color: #019eff; box-shadow: 0 8px 20px rgba(0, 109, 224, .1); }
.g-pcard b { display: block; font-size: .86rem; }
.g-pcard small { display: block; font-size: .72rem; font-weight: 700; margin-top: 2px; }
.g-person { display: flex; gap: 14px; align-items: center; margin-bottom: 8px; }
.g-line { font-size: .86rem; margin: 6px 0; }
.g-facts { margin: 6px 0 6px 18px; font-size: .84rem; }
.g-lock { font-size: .78rem; color: #5b6f8d; margin: 4px 0; }

.g-ladder { display: grid; gap: 6px; margin-top: 10px; }
.g-step { display: flex; gap: 12px; align-items: center; padding: 8px 12px; border-radius: 14px; background: #f6faff; }
.g-step.cur { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
.g-step ul { margin: 4px 0 0 16px; font-size: .78rem; color: #5b6f8d; }
.g-dot { width: 28px; height: 28px; border-radius: 50%; background: var(--c); flex: none; display: grid; place-items: center; color: #fff; font-weight: 800; font-size: .8rem; }
.g-step.locked .g-dot { opacity: .4; }
.g-check { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid #e3ecf8; font-size: .86rem; }
.g-check b { margin-left: auto; color: #3d932e; font-size: .8rem; }
.g-tick { width: 20px; height: 20px; border-radius: 6px; border: 2px solid #cfdcef; display: grid; place-items: center; color: #fff; font-size: .75rem; flex: none; }
.g-check.done .g-tick { background: #3d932e; border-color: #3d932e; }
.g-check.done b { opacity: .5; }

.g-prog { height: 12px; border-radius: 99px; background: #e8f1fc; overflow: hidden; margin: 14px 0 8px; }
.g-prog > div { height: 100%; background: linear-gradient(90deg, #019eff, #006de0); border-radius: 99px; transition: width .6s; }
.g-prog-n { display: flex; justify-content: space-between; font-size: .8rem; color: #5b6f8d; }

.g-modal { position: fixed; inset: 0; background: rgba(16, 38, 74, .45); display: grid; place-items: center; z-index: 60; padding: 20px; }
.g-modal-box { background: #fff; border-radius: 28px; padding: 30px 26px; text-align: center; max-width: 340px; animation: g-pop .5s; }
.g-bigdot { width: 84px; height: 84px; border-radius: 50%; margin: 0 auto 14px; display: grid; place-items: center; color: #fff; font-size: 2rem; font-weight: 800; box-shadow: 0 14px 36px rgba(0, 0, 0, .18); }
@media (prefers-reduced-motion: reduce) { .g-wrap * { animation: none !important; transition: none !important; } }
</style>
