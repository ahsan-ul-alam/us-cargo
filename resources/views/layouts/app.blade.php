<!DOCTYPE html>
<html lang="bn">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', \App\Models\SiteSetting::siteName()){{ \App\Models\SiteSetting::siteTitle() !== '' ? ' — ' . \App\Models\SiteSetting::siteTitle() : '' }}</title>
  <meta name="description"
    content="@yield('description', \App\Models\SiteSetting::siteDescription())" />
  <link rel="icon" href="{{ \App\Models\SiteSetting::faviconUrl() }}" />
  <link rel="apple-touch-icon" href="{{ \App\Models\SiteSetting::faviconUrl() }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}" />
  @if($brandCss = \App\Models\SiteSetting::brandCss())
  <style>{!! $brandCss !!}</style>
  @endif
  <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>

<body>

  @php
    $navItems = [
      ['home', 'হোম'],
      ['services', 'সেবাসমূহ'],
      ['pricing', 'মূল্য তালিকা'],
      ['tracking', 'ট্র্যাকিং'],
      ['about', 'আমাদের সম্পর্কে'],
      ['faq', 'প্রশ্নোত্তর'],
      ['reviews', 'রিভিউ'],
      ['contact', 'যোগাযোগ'],
    ];
  @endphp

  <header class="site-header">
    <div class="container nav">
      <a href="{{ route('home') }}" class="brand">
        <img src="{{ \App\Models\SiteSetting::logoUrl() }}" alt="{{ \App\Models\SiteSetting::siteName() }}" />
      </a>
      <ul class="nav-links">
        @foreach($navItems as [$r, $label])
          <li><a href="{{ route($r) }}" @class(['active' => request()->routeIs($r)])>{{ $label }}</a></li>
        @endforeach
      </ul>
      <div class="nav-cta">
        <a href="{{ route('quote') }}" class="btn btn-primary">কোটেশন নিন</a>
        <button class="nav-toggle" type="button" aria-label="মেনু খুলুন" aria-controls="siteDrawer"
          aria-expanded="false"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  {{-- Mobile / tablet left drawer (kept outside the header: its backdrop-filter would trap position:fixed) --}}
  <div class="drawer-overlay" id="drawerOverlay" hidden></div>
  <aside class="drawer" id="siteDrawer" aria-label="মেনু" aria-hidden="true">
    <div class="drawer-head">
      <a href="{{ route('home') }}" class="brand"><img src="{{ \App\Models\SiteSetting::logoUrl() }}"
          alt="{{ \App\Models\SiteSetting::siteName() }}" /></a>
      <button class="drawer-close" type="button" aria-label="মেনু বন্ধ করুন">&times;</button>
    </div>
    <nav class="drawer-links">
      @foreach($navItems as [$r, $label])
        <a href="{{ route($r) }}" @class(['active' => request()->routeIs($r)])>{{ $label }}</a>
      @endforeach
    </nav>
    <div class="drawer-foot">
      <a href="{{ route('quote') }}" class="btn btn-primary">ফ্রি কোটেশন নিন</a>
      <a class="drawer-tel" href="tel:+447424197746">+44 7424 197746</a>
      <a class="drawer-tel" href="tel:+8801968851370">+880 1968-851370</a>
    </div>
  </aside>

  @yield('content')

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <a href="{{ route('home') }}" class="foot-brand"><img src="{{ \App\Models\SiteSetting::footerLogoUrl() }}" alt="{{ \App\Models\SiteSetting::siteName() }}" /></a>
          <p class="foot-about">যুক্তরাজ্য ও বাংলাদেশের মধ্যে দ্রুত, নিরাপদ ও সাশ্রয়ী কার্গো সার্ভিস।</p>
        </div>
        <div>
          <h4>সেবাসমূহ</h4>
          <ul>
            <li><a href="{{ route('services') }}">এয়ার কার্গো</a></li>
            <li><a href="{{ route('services') }}">পার্সেল ফরওয়ার্ডিং</a></li>
            <li><a href="{{ route('services') }}">ডোর-টু-ডোর</a></li>
            <li><a href="{{ route('services') }}">কাস্টমস ক্লিয়ারেন্স</a></li>
          </ul>
        </div>
        <div>
          <h4>কোম্পানি</h4>
          <ul>
            <li><a href="{{ route('about') }}">আমাদের সম্পর্কে</a></li>
            <li><a href="{{ route('pricing') }}">মূল্য তালিকা</a></li>
            <li><a href="{{ route('tracking') }}">ট্র্যাকিং</a></li>
            <li><a href="{{ route('faq') }}">প্রশ্নোত্তর</a></li>
          </ul>
        </div>
        <div>
          <h4>যোগাযোগ</h4>
          <ul>
            <li>লন্ডন: 736–740 Romford Road, Unit 2b, E12 6BT</li>
            <li>ঢাকা: উত্তরা, ঢাকা</li>
            <li>+44 7424 197746</li>
            <li>+880 1968-851370</li>
            <li>hello@primecargoukbd.com</li>
          </ul>
        </div>
      </div>
      <div class="foot-bottom">© {{ date('Y') }} {{ \App\Models\SiteSetting::legalName() }}। সর্বস্বত্ব সংরক্ষিত।</div>
    </div>
  </footer>

  <script src="{{ asset('js/main.js') }}?v={{ @filemtime(public_path('js/main.js')) }}"></script>
  @include('partials.popup')
  @stack('scripts')
</body>

</html>