<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\Connection;
use App\Models\Quest;
use App\Services\GameService;
use App\Services\QuestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(
        private readonly GameService $game,
        private readonly QuestService $quests,
    ) {}

    public function index(Request $request): View
    {
        return view('account.game', ['locked' => $request->query('locked')]);
    }

    // ───────────────────────── состояние ─────────────────────────

    public function state(): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $user->forceFill(['last_active_at' => now()])->saveQuietly();
        $this->game->syncProfileRewards($user);
        $user->refresh();

        $levels = [];
        foreach ($this->game->levels() as $n => $def) {
            $levels[] = $this->game->levelInfo($n);
        }

        return response()->json([
            'launched'   => $this->game->isLaunched(),
            'staff'      => $this->game->isStaff($user),
            'me'         => $this->me($user),
            'levels'     => $levels,
            'checklist'  => $this->checklist($user),
            'waiting'    => ['ready' => $this->game->readyParticipants(), 'target' => (int) config('game.launch_target')],
            'edit_url'   => route('account.profile.edit'),
            'rules'      => [
                'max_facts'           => (int) config('game.max_facts'),
                'fact'                => (int) config('game.xp.fact'),
                'correct'             => (int) config('game.xp.quest_correct'),
                'wrong'               => (int) config('game.xp.quest_wrong'),
                'pause'               => (int) config('game.miss_cooldown'),
            ],
            'links'      => ['people' => route('account.people')],
        ]);
    }

    /** @return array<string, mixed> */
    private function me(BotUser $user): array
    {
        $p = $this->game->progress($user);
        $info = $this->game->levelInfo($p['level']);

        return [
            'id'       => $user->id,
            'name'     => $user->full_name,
            'avatar'   => $this->avatar($user),
            'xp'       => $p['xp'],
            'level'    => $info,
            'next'     => $p['next_from'] ? $this->game->levelInfo($p['level'] + 1) : null,
            'percent'  => $p['percent'],
        ];
    }

    /** @return list<array{key: string, label: string, points: int, done: bool}> */
    private function checklist(BotUser $user): array
    {
        $xp = config('game.xp');
        $facts = $user->facts()->count();

        return [
            ['key' => 'avatar',      'label' => 'Фото профиля',               'points' => $xp['profile_avatar'],      'done' => (bool) $user->avatar_path],
            ['key' => 'description', 'label' => 'Расскажи о себе',            'points' => $xp['profile_description'], 'done' => mb_strlen(trim((string) $user->description)) >= 20],
            ['key' => 'expectation', 'label' => 'Чего ждёшь от сообщества',   'points' => $xp['profile_expectation'], 'done' => mb_strlen(trim((string) $user->expectation)) >= 20],
            ['key' => 'facts',       'label' => "Факты о себе ({$facts} из 3 для старта)", 'points' => $xp['fact'] * 3, 'done' => $facts >= 3],
        ];
    }

    // ───────────────────────── квесты ─────────────────────────

    public function quest(): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        if (! $this->game->isLaunched() && ! $this->game->isStaff($user)) {
            return response()->json(['status' => 'not_launched']);
        }

        if ($user->quest_locked_until && $user->quest_locked_until->isFuture()) {
            return response()->json(['status' => 'wait', 'wait' => (int) now()->diffInSeconds($user->quest_locked_until, false) + 1]);
        }

        $quest = $this->quests->nextFor($user);
        if (! $quest) {
            return response()->json(['status' => 'empty']);
        }

        $options = BotUser::whereIn('id', $quest->option_ids)->get()->keyBy('id');

        return response()->json([
            'status'  => 'ok',
            'id'      => $quest->id,
            'question' => $quest->question['ru'],
            'options' => collect($quest->option_ids)->map(fn ($id) => $this->person($options[$id], $user, false))->values(),
        ]);
    }

    public function answer(Request $request, Quest $quest): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $data = $request->validate(['chosen' => ['required', 'integer']]);

        if (! $this->game->isLaunched() && ! $this->game->isStaff($user)) {
            return response()->json(['message' => 'Игра ещё не запущена.'], 403);
        }
        if ($user->quest_locked_until && $user->quest_locked_until->isFuture()) {
            return response()->json(['message' => 'Подождите несколько секунд.', 'wait' => (int) now()->diffInSeconds($user->quest_locked_until, false) + 1], 429);
        }
        if (\App\Models\QuestAttempt::where('bot_user_id', $user->id)->where('quest_id', $quest->id)->exists()) {
            return response()->json(['message' => 'Вы уже отвечали на этот вопрос.'], 409);
        }

        try {
            $result = $this->quests->answer($user, $quest, (int) $data['chosen']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $user->refresh();
        $subject = BotUser::find($result['correct_user_id']);

        return response()->json($result + [
            'subject' => $subject ? $this->person($subject, $user, false) : null,
            'me'      => $this->me($user),
            'level_up_info' => $result['level_up'] ? $this->game->levelInfo($result['level_up']) : null,
        ]);
    }

    // ───────────────────────── сеть ─────────────────────────

    public function network(): JsonResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        $ids = Connection::where('user_id', $user->id)->latest()->pluck('target_id');
        $people = BotUser::approved()->with('facts')->whereIn('id', $ids)->get()->sortBy(fn (BotUser $p) => $ids->search($p->id))->values();

        return response()->json([
            'nodes' => $people->map(fn (BotUser $p) => $this->person($p, $user, true))->values(),
            'me'    => $this->me($user),
        ]);
    }

    // ───────────────────────── общее ─────────────────────────

    /**
     * Карточка человека с учётом уровня зрителя. Без $detailed только аватар и имя.
     *
     * @return array<string, mixed>
     */
    private function person(BotUser $p, BotUser $viewer, bool $detailed): array
    {
        $lvl = $this->game->levelInfo($p->level ?: 1);
        $out = [
            'id'     => $p->id,
            'name'   => $p->full_name,
            'avatar' => $this->avatar($p),
            'level'  => ['n' => $lvl['n'], 'name' => $lvl['name'], 'color' => $lvl['color']],
        ];

        if (! $detailed) {
            return $out;
        }

        $out['description'] = $p->description;
        $out['facts']       = $p->facts->pluck('body')->all();
        $out['expectation'] = $p->expectation;
        $out['write_url']   = $p->canBeContacted() ? 'https://t.me/' . $p->telegram_username : null;
        $out['profile_url'] = route('account.people.show', $p);

        return $out;
    }

    private function avatar(BotUser $u): ?string
    {
        return $u->avatar_path ? Storage::url($u->avatar_path) : null;
    }
}
