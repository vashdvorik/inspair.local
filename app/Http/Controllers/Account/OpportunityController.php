<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\OpportunityFeed;
use Illuminate\View\View;

/**
 * «Возможности» в кабинете: та же лента, что на сайте (поездки и учебные курсы за рубежом). Участники ничего не публикуют.
 */
class OpportunityController extends Controller
{
    public function index(): View
    {
        return view('account.opportunities.index', ['items' => OpportunityFeed::all()]);
    }

    /** Полная страница возможности внутри кабинета: человек не уходит на сайт (в мини-приложении Telegram это сбивало). */
    public function show(string $slug): View
    {
        $o = OpportunityFeed::find($slug);
        abort_if($o === null, 404);

        return view('account.opportunities.show', ['o' => $o]);
    }
}
