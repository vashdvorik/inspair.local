@extends('public.layout')

@section('title', 'Publications — INSPIRE EDU Network')
@section('description', 'Publications of the INSPIRE network: research papers, reports and materials. Coming soon.')

@section('content')
<section class="page-head i-airy" style="padding-bottom:84px">
    <div class="i-container">
        <p class="i-eyebrow i-in i-in-1">INSPIRE EDU Network</p>
        <h1 class="i-h1 i-in i-in-2" style="margin:10px 0 0">{!! $tm($site['publications']['title']) !!}</h1>
    </div>
</section>
<section class="i-section" style="padding-top:0">
    <div class="i-container">
        <div class="i-card stub i-reveal">
            <div class="ico">{!! $icon('doc', 'w-10 h-10') !!}</div>
            {!! $pm($site['publications']['lead'], 'i-lead') !!}
            <p class="text-sm text-gray-500 mt-4"><a href="mailto:globalstudy.xxi@gmail.com">globalstudy.xxi@gmail.com</a></p>
        </div>
    </div>
</section>
@endsection
