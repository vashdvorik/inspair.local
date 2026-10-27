@extends('public.layout')

@section('title', 'Video — INSPIRE EDU Network')
@section('description', 'Videos of the INSPIRE Leadership Academy and events of the network.')

@section('content')
<section class="page-head i-airy" style="padding-bottom:48px">
    <div class="i-container">
        <p class="i-eyebrow i-in i-in-1">INSPIRE EDU Network</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 0">{!! $t('Видео', 'Video', 'Video') !!}</h1>
    </div>
</section>
<section class="i-section" style="padding-top:20px">
    <div class="i-container">
        @include('public._videos', ['videos' => $videos])
    </div>
</section>
@endsection
