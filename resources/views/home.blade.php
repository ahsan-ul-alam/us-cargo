@extends('layouts.app')
@section('title', 'হোম')

@section('content')
<section class="hero">
  <div class="hero-bg">
    <img src="{{ asset('images/homeimg.jpg') }}" alt="প্রাইম কার্গো এয়ার শিপমেন্ট" />
  </div>
  <div class="container">
    <div class="hero-grid {{ $heroVideo ? 'has-video' : '' }}">
    <div class="hero-inner">
      <span class="route-tag">✈&nbsp; শুধুমাত্র এয়ার কার্গো &nbsp;·&nbsp; যুক্তরাজ্য ⇄ বাংলাদেশ</span>
      <h1>দুই দেশের মাঝে আপনার পণ্য পৌঁছে দিই <em>দ্রুত ও নিরাপদে</em></h1>
      <p class="lead">{{ \App\Models\SiteSetting::siteName() }} — যুক্তরাজ্য ও বাংলাদেশের মধ্যে এয়ার কার্গোর বিশ্বস্ত নাম। মাত্র £৬ প্রতি কেজিতে দ্রুততম আকাশপথে ডোর-টু-ডোর ডেলিভারি ও রিয়েল-টাইম ট্র্যাকিং।</p>
      <div class="hero-cta">
        <a href="{{ route('quote') }}" class="btn btn-primary btn-lg">ফ্রি কোটেশন নিন</a>
        <a href="{{ route('services') }}" class="btn btn-ghost btn-lg">সেবাগুলো দেখুন</a>
        <a href="{{ route('videos') }}" class="btn btn-ghost btn-lg">▶ ভিডিও দেখুন</a>
      </div>
      <div class="hero-badges">
        @foreach(\App\Support\Counters::get('hero') as $c)
        <div><b>{{ $c['value'] }}</b><small>{{ $c['label'] }}</small></div>
        @endforeach
      </div>
    </div>
    @if($heroVideo)
      <div class="hero-video">
        @include('partials.video-player', ['video' => $heroVideo, 'id' => 'heroVideo'])
      </div>
    @endif
    </div>
  </div>
  @php($promo = \App\Support\Counters::promo())
  @if($promo['promo_enabled'])
  <div class="promo-rail">
    <a href="{{ route('quote') }}" class="promo-badge" aria-label="ফ্রি কোটেশন নিন">
      <small>{{ $promo['promo_tag'] }}</small>
      <b>{{ $promo['promo_value'] }}</b>
      <span>{{ $promo['promo_cta'] }}</span>
    </a>
  </div>
  @endif
  @unless($heroVideo)
  <div class="hero-float">
    <span class="hf-label">সাপ্তাহিক ফ্লাইট</span>
    <strong>লন্ডন → ঢাকা</strong>
    <span class="hf-sub">প্রতি সপ্তাহে নিয়মিত শিপমেন্ট</span>
  </div>
  @endunless
</section>

{{-- Tracking Strip --}}
<div class="container">
  <div class="track-strip">
    <div>
      <h3>আপনার শিপমেন্ট ট্র্যাক করুন</h3>
      <p>ট্র্যাকিং নম্বর দিন, রিয়েল-টাইমে অবস্থান জানুন।</p>
    </div>
    <form class="track-box" id="homeTrackForm">
      @csrf
      <input type="text" id="homeTrackInput" placeholder="যেমন: PC-2026-00001" aria-label="ট্র্যাকিং নম্বর" />
      <button class="btn btn-navy" type="submit">খুঁজুন</button>
    </form>
  </div>
</div>

{{-- Services --}}
<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">আমাদের সেবাসমূহ</span>
      <h2>আপনার প্রতিটি প্রয়োজনের সমাধান</h2>
      <p>ছোট পার্সেল থেকে বড় বাণিজ্যিক চালান — প্রতিটি শিপমেন্ট আমরা সমান যত্নে পরিচালনা করি।</p>
    </div>
    <div class="grid grid-3">
      @foreach([['০১','এয়ার কার্গো','জরুরি পণ্যের জন্য দ্রুততম সমাধান। মাত্র ৫–৮ দিনে আকাশপথে ডেলিভারি।'],['০২','ডোর-টু-ডোর','আপনার বাসা বা অফিস থেকে পণ্য সংগ্রহ করে প্রাপকের দরজায় পৌঁছে দিই।'],['০৩','কাস্টমস ক্লিয়ারেন্স','দুই দেশের কাস্টমস নিয়ম মেনে দ্রুত ক্লিয়ারেন্স। সকল কাগজপত্র আমরাই সামলাই।'],['০৪','পার্সেল ও শপিং','যুক্তরাজ্যের যেকোনো অনলাইন শপ থেকে কেনাকাটা করুন — আমরা বাংলাদেশে পৌঁছে দেবো।'],['০৫','প্যাকেজিং ও স্টোরেজ','নিরাপদ প্যাকেজিং ও ফ্রি স্বল্পমেয়াদি স্টোরেজ।'],['০৬','কার্গো ইনস্যুরেন্স','প্রতিটি চালান বীমার আওতায় — মূল্যবান পণ্যের সম্পূর্ণ সুরক্ষা।']] as [$idx, $title, $desc])
      <div class="card">
        <span class="idx">{{ $idx }}</span>
        <h3>{{ $title }}</h3>
        <p>{{ $desc }}</p>
        <a href="{{ route('services') }}" class="link">বিস্তারিত</a>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- How it works --}}
<section style="background:#fff;border-block:1px solid var(--line)">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">কীভাবে কাজ করে</span>
      <h2>মাত্র ৪টি সহজ ধাপে</h2>
    </div>
    <div class="grid grid-4">
      @foreach([['১','বুকিং দিন','অনলাইনে বা ফোনে কল করে আপনার শিপমেন্ট বুক করুন এবং কোটেশন নিন।'],['২','পিকআপ','আমাদের টিম নির্ধারিত সময়ে আপনার ঠিকানা থেকে পণ্য সংগ্রহ করবে।'],['৩','শিপিং ও ট্র্যাকিং','দ্রুত আকাশপথে পাঠানো হয়; প্রতিটি ধাপ অনলাইনে ট্র্যাক করুন।'],['৪','ডেলিভারি','প্রাপকের দরজায় নিরাপদে পৌঁছে যায় আপনার মূল্যবান পণ্য।']] as [$num, $title, $desc])
      <div class="step"><div class="num">{{ $num }}</div><h3>{{ $title }}</h3><p>{{ $desc }}</p></div>
      @endforeach
    </div>
  </div>
</section>

{{-- Stats Band --}}
<section class="band">
  <div class="container">
    <div class="grid grid-4 stat-grid">
      @foreach(\App\Support\Counters::get('home') as $c)
      <div class="stat"><b>{{ $c['value'] }}</b><span>{{ $c['label'] }}</span></div>
      @endforeach
    </div>
  </div>
</section>

{{-- Reviews --}}
<section id="reviews">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">গ্রাহকদের মতামত</span>
      <h2>যারা আমাদের ওপর আস্থা রেখেছেন</h2>
    </div>
    @if($reviews->count())
    <div class="grid grid-3">
      @foreach($reviews->take(3) as $r)
      <div class="card review-card">
        <div class="stars" aria-label="{{ $r->rating }}/5">{{ str_repeat('★', $r->rating) }}<span>{{ str_repeat('★', 5 - $r->rating) }}</span></div>
        <p>"{{ $r->body }}"</p>
        <div class="reviewer">
          @if($r->photo)<img src="{{ asset('storage/'.$r->photo) }}" alt="{{ $r->name }}" />@else<span class="avatar">{{ mb_substr($r->name, 0, 1) }}</span>@endif
          <div><b>{{ $r->name }}</b>@if($r->location)<small>{{ $r->location }}</small>@endif</div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    <div style="text-align:center;margin-top:40px">
      <a href="{{ route('reviews') }}" class="btn btn-primary btn-lg">সব রিভিউ দেখুন ও আপনার রিভিউ দিন</a>
    </div>
  </div>
</section>

{{-- CTA Band --}}
<section>
  <div class="container">
    <div class="cta-band">
      <h2>আজই আপনার পণ্য পাঠানোর পরিকল্পনা করুন</h2>
      <p>মাত্র এক ক্লিকে ফ্রি কোটেশন নিন অথবা আমাদের সাথে কথা বলুন।</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('quote') }}" class="btn btn-lg btn-navy">ফ্রি কোটেশন নিন</a>
        <a href="{{ route('contact') }}" class="btn btn-lg btn-ghost">যোগাযোগ করুন</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('homeTrackForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const code = document.getElementById('homeTrackInput').value.trim();
  if (code) window.location.href = '{{ route('tracking') }}?code=' + encodeURIComponent(code);
});
</script>
@endpush
