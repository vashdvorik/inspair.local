{{-- Click-to-play YouTube videos (the player is only loaded after a click) --}}
<div class="grid md:grid-cols-3 gap-6">
    @foreach($videos as $i => $v)
        <div class="i-reveal" style="--d:{{ $i * .08 }}s">
            <div class="yt i-card--hover">
                <button type="button" class="yt-btn" data-yt="{{ $v['id'] }}" data-title="{{ $v['title']['en'] }}" aria-label="{{ $v['title']['en'] }}">
                    <img src="https://i.ytimg.com/vi/{{ $v['id'] }}/hqdefault.jpg" alt="" loading="lazy" width="480" height="360">
                    <span class="yt-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                </button>
            </div>
            <p class="mt-3 text-sm font-semibold text-ink">{!! $tm($v['title']) !!}</p>
            <a class="text-sm" href="https://www.youtube.com/watch?v={{ $v['id'] }}" target="_blank" rel="noopener">{!! $t('Смотреть на YouTube', 'Watch on YouTube', 'Vezi pe YouTube') !!}</a>
        </div>
    @endforeach
</div>
