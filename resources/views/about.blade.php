@extends('layouts.app')
@section('title', 'আমাদের সম্পর্কে')
@section('description', 'প্রাইম কার্গো — ২০১৩ সাল থেকে যুক্তরাজ্য ও বাংলাদেশের মধ্যে বিশ্বস্ত কার্গো সেবা।')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / আমাদের সম্পর্কে</div>
    <h1>আমাদের সম্পর্কে</h1>
    <p>দুই দেশের মাঝে সেতুবন্ধন গড়ে তোলাই আমাদের লক্ষ্য — একই প্যাকেজ, একই বিশ্বাস দুই পাশেই।</p>
  </div>
</div>

<section>
  <div class="container split">
    <div>
      <span class="eyebrow">আমাদের গল্প</span>
      <h2>প্রবাসী বাংলাদেশিদের জন্য, প্রবাসীদের হাতেই গড়া</h2>
      <p>২০১৩ সালে লন্ডনের ইস্ট লন্ডন এলাকায় ছোট্ট একটি অফিস থেকে প্রাইম কার্গোর যাত্রা শুরু। উদ্দেশ্য ছিল একটাই — প্রবাসী বাংলাদেশিরা যেন নিশ্চিন্তে, সাশ্রয়ী মূল্যে দুই দেশে পণ্য পাঠাতে ও দেশ থেকে আনতে পারেন।</p>
      <p>আজ আমরা যুক্তরাজ্য ও বাংলাদেশের ৬টি প্রধান শহরে শাখা নিয়ে প্রতি সপ্তাহে শত শত চালান পরিচালনা করছি। তবে আমাদের মূল্যবোধ একই রয়ে গেছে — সততা, স্বচ্ছতা ও আন্তরিকতা।</p>
      <a href="{{ route('contact') }}" class="btn btn-outline" style="margin-top:14px">আমাদের সাথে কথা বলুন</a>
    </div>
    <div class="visual">
      <div>
        <div class="big">২০১৩</div>
        <h3>থেকে আস্থার সাথে</h3>
        <p>লন্ডন · ম্যানচেস্টার · বার্মিংহাম<br/>ঢাকা · সিলেট · চট্টগ্রাম</p>
      </div>
    </div>
  </div>
</section>

<section style="background:#fff;border-block:1px solid var(--line)">
  <div class="container">
    <div class="grid grid-3">
      <div class="card">
        <span class="idx">আমাদের লক্ষ্য</span>
        <h3>নির্ভরযোগ্য সেবা</h3>
        <p>যুক্তরাজ্য ও বাংলাদেশের মধ্যে সবচেয়ে নির্ভরযোগ্য, স্বচ্ছ ও সাশ্রয়ী কার্গো সেবা প্রদান করা — যাতে প্রতিটি গ্রাহক নিশ্চিন্ত থাকতে পারেন।</p>
      </div>
      <div class="card">
        <span class="idx">আমাদের দৃষ্টিভঙ্গি</span>
        <h3>বিশ্বস্ত ব্র্যান্ড</h3>
        <p>দুই দেশের মধ্যে পণ্য পরিবহনে সবচেয়ে বিশ্বস্ত ব্র্যান্ড হয়ে ওঠা এবং প্রবাসী কমিউনিটির পাশে সবসময় থাকা।</p>
      </div>
      <div class="card">
        <span class="idx">আমাদের মূল্যবোধ</span>
        <h3>সততা ও দায়বদ্ধতা</h3>
        <p>সততা, সময়ানুবর্তিতা, সুলভ মূল্য এবং গ্রাহকের প্রতি আন্তরিক দায়বদ্ধতা — এগুলোই আমাদের প্রতিটি সিদ্ধান্তের ভিত্তি।</p>
      </div>
    </div>
  </div>
</section>

<section class="band">
  <div class="container">
    <div class="section-head"><h2 style="color:#fff">সংখ্যায় প্রাইম কার্গো</h2></div>
    <div class="grid grid-4 stat-grid">
      @foreach(\App\Support\Counters::get('about') as $c)
      <div class="stat"><b>{{ $c['value'] }}</b><span>{{ $c['label'] }}</span></div>
      @endforeach
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">কেন আমরা আলাদা</span>
      <h2>যে কারণে গ্রাহকরা আমাদের বেছে নেন</h2>
    </div>
    <div class="grid grid-4">
      <div class="card"><h3>নিরাপদ ও বীমাকৃত</h3><p>প্রতিটি চালান বীমার আওতায়, সম্পূর্ণ সুরক্ষিত।</p></div>
      <div class="card"><h3>সময়মতো ডেলিভারি</h3><p>৯৯.৫% শিপমেন্ট নির্ধারিত সময়েই পৌঁছায়।</p></div>
      <div class="card"><h3>বাংলায় সাপোর্ট</h3><p>আপনার ভাষায় ২৪/৭ আন্তরিক সহায়তা।</p></div>
      <div class="card"><h3>সাশ্রয়ী মূল্য</h3><p>মাত্র £৬ প্রতি কেজি থেকে শুরু।</p></div>
    </div>
  </div>
</section>

<section style="background:#fff;border-top:1px solid var(--line)">
  <div class="container">
    <div class="cta-band">
      <h2>আমাদের পরিবারের অংশ হোন</h2>
      <p>হাজারো প্রবাসীর মতো আপনিও আস্থা রাখুন প্রাইম কার্গোতে।</p>
      <a href="{{ route('quote') }}" class="btn btn-lg btn-navy">এখনই শুরু করুন</a>
    </div>
  </div>
</section>
@endsection
