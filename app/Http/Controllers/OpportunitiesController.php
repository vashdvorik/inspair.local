<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\OpportunityFeed;
use Illuminate\Contracts\View\View;

/**
 * «Возможности» на публичном сайте: новости о международных поездках (учебные курсы и мероприятия за рубежом).
 * Данные и их разбор: App\Support\OpportunityFeed (resources/data/opportunities.json).
 */
class OpportunitiesController extends Controller
{
    public function index(): View
    {
        $data = \App\Support\PublicUi::content('opportunities');

        return view('public.opportunities', ['title' => $data['title'], 'intro' => $data['intro'], 'items' => OpportunityFeed::all()]);
    }

    public function show(string $slug): View
    {
        $item = OpportunityFeed::find($slug);
        abort_if($item === null, 404);

        return view('public.opportunity', ['o' => $item]);
    }
}
