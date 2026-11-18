<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Язык кабинета и страницы входа: ru, en или ro.
 *
 * Язык выбирается так: кука inspair_lang (её ставят переключатель в кабинете и публичный сайт),
 * иначе язык браузера (Accept-Language), иначе русский. Кука не шифруется, чтобы её мог писать и JavaScript сайта.
 * Тексты пишутся прямо в шаблонах: $tr('по-русски', 'in English', 'în română').
 */
final class Lang
{
    public const COOKIE = 'inspair_lang';

    public const LOCALES = ['ru', 'en', 'ro'];

    /**
     * Кука языка. Кабинет открывается внутри Telegram: в Telegram Web и некоторых клиентах это iframe, где обычная
     * кука (SameSite=Lax) не сохраняется, и переключатель «не срабатывал». Поэтому на https кука ставится как
     * SameSite=None; Secure; Partitioned (так же, как сессионная). Без https (локальная разработка) остаётся Lax.
     */
    public static function cookie(string $code, bool $secure): \Symfony\Component\HttpFoundation\Cookie
    {
        return new \Symfony\Component\HttpFoundation\Cookie(self::COOKIE, $code, now()->addYear(), '/', null, $secure, false, false, $secure ? 'none' : 'lax', $secure);
    }

    public static function current(): string
    {
        $request = request();
        $cookie = (string) $request->cookie(self::COOKIE);

        if (in_array($cookie, self::LOCALES, true)) {
            return $cookie;
        }

        // без заголовка Accept-Language Symfony подставил бы английский, а кабинет по умолчанию русский
        if (! $request->headers->has('Accept-Language')) {
            return 'ru';
        }

        $preferred = $request->getPreferredLanguage(self::LOCALES);

        return in_array($preferred, self::LOCALES, true) ? $preferred : 'ru';
    }

    /** Текст на текущем языке; если перевода нет, берётся русский. */
    public static function t(string $ru, ?string $en = null, ?string $ro = null): string
    {
        return match (self::current()) {
            'en' => $en ?? $ru,
            'ro' => $ro ?? $ru,
            default => $ru,
        };
    }

    /**
     * Число со словом в нужной форме.
     *
     * @param  array{0: string, 1: string, 2: string}  $ru  например ['участник', 'участника', 'участников']
     * @param  array{0: string, 1: string}  $en  ['participant', 'participants']
     * @param  array{0: string, 1: string, 2: string}  $ro  ['participant', 'participanți', 'de participanți']
     */
    public static function plural(int $n, array $ru, array $en, array $ro): string
    {
        $lang = self::current();

        return $n.' '.match ($lang) {
            'en' => $n === 1 ? $en[0] : $en[1],
            'ro' => self::roForm($n, $ro),
            default => self::ruForm($n, $ru),
        };
    }

    /** @param array{0: string, 1: string, 2: string} $f */
    private static function ruForm(int $n, array $f): string
    {
        $k = abs($n) % 100;
        $d = $k % 10;

        return $k > 10 && $k < 20 ? $f[2] : ($d === 1 ? $f[0] : ($d >= 2 && $d <= 4 ? $f[1] : $f[2]));
    }

    /** @param array{0: string, 1: string, 2: string} $f */
    private static function roForm(int $n, array $f): string
    {
        $k = abs($n) % 100;

        return $n === 1 ? $f[0] : ($n === 0 || ($k >= 1 && $k <= 19) ? $f[1] : $f[2]);
    }

    /** Тексты игры для Vue: плоский массив на текущем языке. Подстановки вида {n} делает сам Vue. */
    public static function game(): array
    {
        $lang = self::current();
        $i = ['ru' => 0, 'en' => 1, 'ro' => 2][$lang];

        return array_map(fn (array $v) => $v[$i], self::GAME);
    }

    private const GAME = [
        'lang'            => ['ru', 'en', 'ro'],
        'loading'         => ['Загрузка…', 'Loading…', 'Se încarcă…'],
        'request_failed'  => ['Ошибка запроса', 'Request failed', 'Eroare de cerere'],
        'locked'          => ['Раздел «{section}» пока закрыт: он откроется после запуска игры.', 'The “{section}” section is closed for now: it opens when the game launches.', 'Secțiunea „{section}” este deocamdată închisă: se deschide la lansarea jocului.'],
        'section_people'  => ['Люди', 'People', 'Oameni'],
        'section_opportunities' => ['Размещение возможностей', 'Posting opportunities', 'Publicarea oportunităților'],
        'to_level'        => ['до «{name}»: {n}', 'to “{name}”: {n}', 'până la „{name}”: {n}'],
        'max_level'       => ['максимальный уровень', 'top level', 'nivel maxim'],
        'new_level'       => ['Новый уровень', 'New level', 'Nivel nou'],
        'keep_going'      => ['Так держать! Копите очки дальше.', 'Keep it up! Keep collecting points.', 'Bravo! Continuă să aduni puncte.'],
        'continue'        => ['Продолжить', 'Continue', 'Continuă'],

        'how_to_play'     => ['Как играть', 'How to play', 'Cum se joacă'],
        'hide'            => ['Скрыть', 'Hide', 'Ascunde'],
        'show_rules'      => ['Показать правила', 'Show the rules', 'Arată regulile'],
        'r1_title'        => ['Расскажите о себе.', 'Tell us about yourself.', 'Povestește despre tine.'],
        'r1'              => ['В профиле добавьте несколько коротких фактов (до {max}): чем занимаетесь, что любите. За каждый факт вы получаете {fact}. Из этих рассказов составляются вопросы для других участников.', 'Add a few short facts to your profile (up to {max}): what you do, what you love. Each fact earns you {fact}. These stories become questions for other participants.', 'Adaugă în profil câteva fapte scurte (până la {max}): cu ce te ocupi, ce îți place. Pentru fiecare fapt primești {fact}. Din aceste povești se fac întrebări pentru ceilalți participanți.'],
        'r2_title'        => ['Угадывайте.', 'Make your guess.', 'Ghicește.'],
        'r2'              => ['Вам показывают вопрос про одного участника и трёх людей на выбор. Нажмите на того, о ком, по-вашему, речь.', 'You see a question about one participant and three people to choose from. Tap the one you think it is about.', 'Vezi o întrebare despre un participant și trei persoane la alegere. Apasă pe cea despre care crezi că e vorba.'],
        'r3_title'        => ['Получайте очки.', 'Earn points.', 'Câștigă puncte.'],
        'r3'              => ['Верный ответ даёт {correct}, неверный даёт {wrong}. После промаха нужно подождать {pause} секунд до следующего вопроса. Штрафов нет.', 'A correct answer gives {correct}, a wrong one gives {wrong}. After a miss, wait {pause} seconds for the next question. There are no penalties.', 'Un răspuns corect aduce {correct}, unul greșit aduce {wrong}. După o greșeală, așteaptă {pause} secunde până la următoarea întrebare. Nu există penalizări.'],
        'r4_title'        => ['Растите уровень.', 'Level up.', 'Crește nivelul.'],
        'r4'              => ['Чем больше очков, тем выше уровень: {levels}. Уровень это ваше звание в игре, он ничего не открывает и не закрывает.', 'The more points, the higher the level: {levels}. A level is your title in the game; it doesn’t open or close anything.', 'Cu cât ai mai multe puncte, cu atât nivelul e mai înalt: {levels}. Nivelul este titlul tău în joc; nu deschide și nu închide nimic.'],
        'r5_title'        => ['Вы решаете сами.', 'You decide.', 'Tu decizi.'],
        'r5'              => ['Если не хотите попадать в вопросы, отметьте это в профиле. Контакты тоже можно закрыть.', 'If you don’t want to appear in questions, switch that on in your profile. You can also hide your contacts.', 'Dacă nu vrei să apari în întrebări, bifează asta în profil. Poți ascunde și contactele.'],

        'loading_quest'   => ['Загружаю квест…', 'Loading the question…', 'Se încarcă întrebarea…'],
        'empty_title'     => ['Пока квестов нет', 'No questions yet', 'Încă nu există întrebări'],
        'empty_text'      => ['Новые вопросы появляются, когда участники рассказывают о себе. Загляните позже.', 'New questions appear as participants share facts about themselves. Check back later.', 'Întrebările noi apar când participanții povestesc despre ei. Revino mai târziu.'],
        'rest'            => ['Передохните {n} с', 'Take a break: {n} s', 'Fă o pauză: {n} s'],
        'rest_text'       => ['Следующий квест откроется через пару секунд.', 'The next question opens in a few seconds.', 'Următoarea întrebare se deschide în câteva secunde.'],
        'load_failed'     => ['Не удалось загрузить.', 'Could not load.', 'Nu s-a putut încărca.'],
        'retry'           => ['Повторить', 'Try again', 'Încearcă din nou'],
        'game_title'      => ['Угадай, кто это', 'Guess who it is', 'Ghicește cine e'],
        'ai_note'         => ['Вопрос составлен ИИ по рассказам участников · выберите одного из троих', 'The question was written by AI from participants’ stories · choose one of three', 'Întrebarea a fost scrisă de AI după poveștile participanților · alege una din trei'],
        'right'           => ['Верно! {name} · +{points}', 'Correct! {name} · +{points}', 'Corect! {name} · +{points}'],
        'wrong'           => ['Не угадали. Это {name} · +{points}', 'Not quite. It’s {name} · +{points}', 'Nu ai ghicit. E {name} · +{points}'],
        'next_in'         => ['Следующий вопрос через {n} с', 'Next question in {n} s', 'Următoarea întrebare în {n} s'],
        'next'            => ['Следующий вопрос', 'Next question', 'Următoarea întrebare'],

        'soon'            => ['Игра скоро откроется', 'The game opens soon', 'Jocul se deschide curând'],
        'wait_title'      => ['Ждём, пока нас станет больше', 'Waiting for more of us', 'Așteptăm să fim mai mulți'],
        'wait_text'       => ['Для интересных загадок нужно минимум {n} участников с заполненным профилем.', 'Good riddles need at least {n} participants with a filled-in profile.', 'Pentru ghicitori interesante trebuie cel puțin {n} participanți cu profilul completat.'],
        'already'         => ['Уже {ready} из {target}', 'Already {ready} of {target}', 'Deja {ready} din {target}'],
        'left'            => ['осталось {n}', '{n} to go', 'mai sunt {n}'],
        'enough'          => ['достаточно, ждём запуска', 'enough, waiting for launch', 'suficient, așteptăm lansarea'],
        'fill_title'      => ['Пока ждём, заполните профиль', 'While you wait, complete your profile', 'Până atunci, completează-ți profilul'],
        'fill_text'       => ['Очки уже копятся: в игру вы войдёте с запасом.', 'Points are already adding up: you’ll enter the game with a head start.', 'Punctele se adună deja: vei intra în joc cu avans.'],
        'fill_btn'        => ['Дополнить профиль', 'Complete your profile', 'Completează profilul'],

        'levels'          => ['Уровни', 'Levels', 'Niveluri'],
        'from'            => ['от {points}', 'from {points}', 'de la {points}'],
        'quick'           => ['Быстро набрать очки', 'Earn points fast', 'Adună rapid puncte'],
        'quick_text'      => ['Расскажите о себе: за это сразу дают очки.', 'Tell us about yourself: you get points right away.', 'Povestește despre tine: primești puncte imediat.'],

        'my_network'      => ['Мои знакомства · {n}', 'People I know · {n}', 'Cunoștințele mele · {n}'],
        'net_empty'       => ['Пока пусто. Отвечайте на вопросы: каждый верный ответ добавляет человека сюда.', 'Nothing here yet. Answer the questions: each correct answer adds a person here.', 'Încă e gol. Răspunde la întrebări: fiecare răspuns corect adaugă aici o persoană.'],
        'write_tg'        => ['Написать в Telegram', 'Message on Telegram', 'Scrie pe Telegram'],
        'contacts_hidden' => ['Контакты скрыты участником', 'Contacts are hidden by the participant', 'Contactele sunt ascunse de participant'],
        'profile'         => ['Профиль', 'Profile', 'Profil'],
    ];
}
