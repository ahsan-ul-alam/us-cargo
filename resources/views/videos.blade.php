@extends('layouts.app')
@section('title', 'ভিডিও')
@section('description', 'প্রাইম কার্গোর ভিডিও দেখুন।')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / ভিডিও</div>
    <h1>আমাদের ভিডিও</h1>
    <p>আমাদের সেবা, কার্যক্রম ও গ্রাহকদের অভিজ্ঞতার ভিডিওগুলো দেখুন।</p>
  </div>
</div>

<section>
  <div class="container">
    @if($videos->count())
    <div class="grid grid-3">
      @foreach($videos as $row)
      @php($v = $row['model'])
      @php($thumb = $v->thumbnailUrl())
      <button type="button" class="card video-card" data-provider="{{ $row['embed']['provider'] }}" data-src="{{ $row['embed']['src'] }}" data-title="{{ $v->title }}">
        <span class="video-thumb" @if($thumb) style="background-image:url('{{ $thumb }}')" @endif>
          <span class="video-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path d="M7 4l13 8-13 8z"/></svg></span>
        </span>
        <span class="video-meta">
          <b>{{ $v->title }}</b>
          @if($v->description)<small>{{ \Illuminate\Support\Str::limit($v->description, 110) }}</small>@endif
        </span>
      </button>
      @endforeach
    </div>
    @else
      <p style="text-align:center">এখনো কোনো ভিডিও যোগ করা হয়নি।</p>
    @endif
  </div>
</section>

<div class="video-modal" id="videoModal" hidden role="dialog" aria-modal="true" aria-label="ভিডিও প্লেয়ার">
  <div class="video-modal-box">
    <button type="button" class="video-close" aria-label="বন্ধ করুন">&times;</button>
    <div class="video-stage" id="videoStage"></div>
    <div class="video-modal-title" id="videoModalTitle"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var modal = document.getElementById('videoModal'), stage = document.getElementById('videoStage'),
      title = document.getElementById('videoModalTitle');

  function close() { modal.hidden = true; stage.innerHTML = ''; document.body.style.overflow = ''; }

  function open(card) {
    var src = card.dataset.src, el;
    if (card.dataset.provider === 'file') {
      el = document.createElement('video');
      el.src = src; el.controls = true; el.autoplay = true; el.playsInline = true;
    } else {
      el = document.createElement('iframe');
      el.src = src; el.allowFullscreen = true;
      el.allow = 'autoplay; encrypted-media; fullscreen; picture-in-picture';
      el.referrerPolicy = 'strict-origin-when-cross-origin';
    }
    stage.innerHTML = ''; stage.appendChild(el);
    title.textContent = card.dataset.title;
    modal.hidden = false; document.body.style.overflow = 'hidden';
  }

  document.querySelectorAll('.video-card').forEach(function (c) { c.addEventListener('click', function () { open(c); }); });
  modal.addEventListener('click', function (e) { if (e.target === modal || e.target.classList.contains('video-close')) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) close(); });
})();
</script>
@endpush
