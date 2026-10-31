@extends('layouts.app')
@section('title', 'মূল্য তালিকা')
@section('description', 'যুক্তরাজ্য ও বাংলাদেশের মধ্যে এয়ার কার্গোর স্বচ্ছ মূল্য তালিকা — মাত্র £৬ প্রতি কেজি থেকে।')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / মূল্য তালিকা</div>
    <h1>স্বচ্ছ মূল্য — মাত্র £৬ প্রতি কেজি থেকে</h1>
    <p>আপনি যা দেখছেন, তাই দিচ্ছেন। কোনো লুকানো চার্জ নেই। নিচের রেট ও প্যাকেজ থেকে আপনার প্রয়োজন বেছে নিন।</p>
  </div>
</div>

@php
  $serviceLabels = ['air_cargo' => 'এয়ার কার্গো', 'door_to_door' => 'ডোর-টু-ডোর', 'parcel' => 'পার্সেল'];
  $directions = ['uk_to_bd' => 'যুক্তরাজ্য → বাংলাদেশ', 'bd_to_uk' => 'বাংলাদেশ → যুক্তরাজ্য'];
@endphp

<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">আমাদের রেট</span>
      <h2>প্রতি কেজি শিপিং রেট</h2>
      <p>স্বচ্ছ ও সাশ্রয়ী মূল্যে আপনার পণ্য পাঠান। নিচের রেট ন্যূনতম ওজনের উপর প্রযোজ্য।</p>
    </div>

    @forelse($directions as $key => $label)
      @if(($rates[$key] ?? collect())->isNotEmpty())
        <div style="margin-bottom:48px">
          <h3 style="margin-bottom:18px">{{ $label }}</h3>
          <table class="rate-table">
            <thead>
              <tr>
                <th style="text-align:left">সেবা</th>
                <th>ন্যূনতম ওজন</th>
                <th>রেট</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rates[$key] as $rate)
                <tr>
                  <td style="text-align:left">{{ $serviceLabels[$rate->service_type] ?? $rate->service_type }}</td>
                  <td>{{ $rate->min_weight_kg }} কেজি</td>
                  <td class="rate-val">£{{ number_format($rate->price_per_kg, 2) }}/কেজি</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    @empty
      <p style="text-align:center;color:var(--muted)">এই মুহূর্তে কোনো রেট পাওয়া যায়নি।</p>
    @endforelse

    <p style="font-size:.85rem;color:var(--muted)">* নূন্যতম চার্জ এবং ভলিউমেট্রিক ওজন প্রযোজ্য হতে পারে। সঠিক কোটেশনের জন্য আমাদের সাথে যোগাযোগ করুন।</p>
  </div>
</section>

<section style="background:#fff;border-block:1px solid var(--line)">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">প্যাকেজ</span>
      <h2>আপনার জন্য উপযুক্ত প্যাকেজ</h2>
    </div>
    <div class="grid grid-3">
      <div class="card price-card">
        <div class="plan">স্ট্যান্ডার্ড</div>
        <div class="price">£৬.০০<small> /কেজি</small></div>
        <p class="desc">এয়ারপোর্ট-টু-এয়ারপোর্ট</p>
        <ul>
          <li><span class="tick-mark"></span>এয়ার কার্গো শিপিং</li>
          <li><span class="tick-mark"></span>অনলাইন ট্র্যাকিং</li>
          <li><span class="tick-mark"></span>বেসিক প্যাকেজিং</li>
          <li><span class="tick-mark"></span>কাস্টমস সহায়তা</li>
        </ul>
        <a href="{{ route('quote') }}" class="btn btn-outline btn-block">বুকে নিন</a>
      </div>
      <div class="card price-card popular">
        <span class="badge">জনপ্রিয়</span>
        <div class="plan">এক্সপ্রেস</div>
        <div class="price">£৭.৫০<small> /কেজি</small></div>
        <p class="desc">ডোর-টু-ডোর + ইনস্যুরেন্স</p>
        <ul>
          <li><span class="tick-mark"></span>দ্রুত এয়ার কার্গো শিপিং</li>
          <li><span class="tick-mark"></span>ডোর-টু-ডোর ডেলিভারি</li>
          <li><span class="tick-mark"></span>রিয়েল-টাইম ট্র্যাকিং</li>
          <li><span class="tick-mark"></span>ফ্রি ইনস্যুরেন্স</li>
          <li><span class="tick-mark"></span>অগ্রাধিকার সাপোর্ট</li>
        </ul>
        <a href="{{ route('quote') }}" class="btn btn-primary btn-block">বুকে নিন</a>
      </div>
      <div class="card price-card">
        <div class="plan">বিজনেস</div>
        <div class="price">কাস্টম</div>
        <p class="desc">বাণিজ্যিক ও বাল্ক এয়ার চালান</p>
        <ul>
          <li><span class="tick-mark"></span>ভলিউম ডিস্কাউন্ট রেট</li>
          <li><span class="tick-mark"></span>ডেডিকেটেড ম্যানেজার</li>
          <li><span class="tick-mark"></span>সম্পূর্ণ কাস্টমস হ্যান্ডলিং</li>
          <li><span class="tick-mark"></span>অগ্রাধিকার বুকিং</li>
          <li><span class="tick-mark"></span>মাসিক বিলিং সুবিধা</li>
        </ul>
        <a href="{{ route('contact') }}" class="btn btn-outline btn-block">যোগাযোগ করুন</a>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band">
      <h2>সঠিক মূল্য জানতে কোটেশন নিন</h2>
      <p>আপনার পণ্যের বিস্তারিত দিন, আমরা ২৪ ঘণ্টার মধ্যে নির্ভুল কোটেশন পাঠাবো।</p>
      <a href="{{ route('quote') }}" class="btn btn-lg btn-navy">ফ্রি কোটেশন নিন</a>
    </div>
  </div>
</section>
@endsection
