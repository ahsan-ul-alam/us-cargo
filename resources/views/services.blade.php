@extends('layouts.app')
@section('title', 'সেবাসমূহ')
@section('description', 'এয়ার কার্গো, ডোর-টু-ডোর ডেলিভারি, কাস্টমস ক্লিয়ারেন্স ও পার্সেল ফরওয়ার্ডিং — যুক্তরাজ্য ও বাংলাদেশের মধ্যে।')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / সেবাসমূহ</div>
    <h1>আমাদের সেবাসমূহ</h1>
    <p>যুক্তরাজ্য ও বাংলাদেশের মধ্যে আপনার পণ্য পরিবহনের প্রতিটি প্রয়োজনে আমরা প্রস্তুত — দ্রুত, নিরাপদ ও সাশ্রয়ী মূল্যে।</p>
  </div>
</div>

<section>
  <div class="container">
    <div class="grid grid-2">
      <div class="card">
        <span class="idx">এয়ার কার্গো</span>
        <h3>দ্রুততম আকাশপথ পরিবহন</h3>
        <p>জরুরি ডকুমেন্ট, উপহার বা মূল্যবান পণ্য — দ্রুততম সময়ে পৌঁছে দিতে এয়ার কার্গোই সেরা। মাত্র ৫–৮ কার্যদিবসে দুই দেশের মধ্যে ডেলিভারি।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>দ্রুততম ৫–৮ দিনের ক্রানটাইম</span></li>
          <li><span class="tick-mark"></span><span>ছোট পার্সেল থেকে বড় চালান</span></li>
          <li><span class="tick-mark"></span><span>প্রতিদিন ফ্লাইট শিডিউল</span></li>
        </ul>
      </div>
      <div class="card">
        <span class="idx">কার্গো ইনস্যুরেন্স</span>
        <h3>সম্পূর্ণ বীমা সুরক্ষা</h3>
        <p>প্রতিটি চালান বীমার আওতায়। মূল্যবান বা ভঙ্গুর পণ্যের জন্য অতিরিক্ত কভারেজ — পরিবহনে কোনো ঝুঁকি নেই, সম্পূর্ণ নিশ্চিন্ত।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>প্রতিটি প্যাকেজে মৌলিক বীমা</span></li>
          <li><span class="tick-mark"></span><span>মূল্যবান পণ্যে অতিরিক্ত কভারেজ</span></li>
          <li><span class="tick-mark"></span><span>দ্রুত ক্লেইম নিষ্পত্তি</span></li>
        </ul>
      </div>
      <div class="card">
        <span class="idx">ডোর-টু-ডোর</span>
        <h3>দরজা থেকে দরজা পর্যন্ত</h3>
        <p>প্রেরকের দরজা থেকে প্রাপকের দরজা পর্যন্ত সম্পূর্ণ দায়িত্ব আমাদের। আপনাকে কোথাও যেতে হবে না — আমরাই সব সামলাই।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>বাসা/অফিস থেকে ফ্রি পিকআপ</span></li>
          <li><span class="tick-mark"></span><span>প্রাপকের ঠিকানায় হোম ডেলিভারি</span></li>
          <li><span class="tick-mark"></span><span>SMS ও কল আপডেট</span></li>
        </ul>
      </div>
      <div class="card">
        <span class="idx">কাস্টমস ক্লিয়ারেন্স</span>
        <h3>ঝামেলাহীন কাস্টমস</h3>
        <p>দুই দেশের কঠিন কাস্টমস নিয়মকানুন আমরা সামলাই। সঠিক ডকুমেন্টেশন ও দ্রুত ক্লিয়ারেন্সে আপনার পণ্য আটকে থাকবে না।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>সম্পূর্ণ ডকুমেন্টেশন সহায়তা</span></li>
          <li><span class="tick-mark"></span><span>ডিউটি ও ট্যাক্স পরামর্শ</span></li>
          <li><span class="tick-mark"></span><span>অভিজ্ঞ কাস্টমস এজেন্ট</span></li>
        </ul>
      </div>
      <div class="card">
        <span class="idx">পার্সেল ফরওয়ার্ডিং</span>
        <h3>অনলাইন শপিং ও ফরওয়ার্ডিং</h3>
        <p>যুক্তরাজ্যের যেকোনো অনলাইন শপ থেকে কেনাকাটা করুন আমাদের UK ঠিকানায় পাঠান — আমরা একত্রে করে বাংলাদেশে পৌঁছে দেবো।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>ফ্রি UK শিপিং ঠিকানা</span></li>
          <li><span class="tick-mark"></span><span>একাধিক পার্সেল একত্রীকরণ</span></li>
          <li><span class="tick-mark"></span><span>পছন্দ অনুযায়ী সুবিধা</span></li>
        </ul>
      </div>
      <div class="card">
        <span class="idx">প্যাকেজিং ও স্টোরেজ</span>
        <h3>নিরাপদ প্যাকেজিং ও সংরক্ষণ</h3>
        <p>পেশাদার প্যাকেজিংয়ে আপনার পণ্য থাকবে সুরক্ষিত। প্রয়োজনে ফ্রি স্বল্পমেয়াদি স্টোরেজ সুবিধাও পাবেন।</p>
        <ul class="checklist">
          <li><span class="tick-mark"></span><span>ভঙ্গুর পণ্যের বিশেষ প্যাকেজিং</span></li>
          <li><span class="tick-mark"></span><span>নিরাপদ গুদামে সংরক্ষণ</span></li>
          <li><span class="tick-mark"></span><span>২৪ ঘণ্টা নিরাপত্তা</span></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section style="background:#fff;border-block:1px solid var(--line)">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">আমরা যা পাঠাতে পারি</span>
      <h2>প্রায় সব ধরনের পণ্য</h2>
      <p>ব্যক্তিগত উপহার থেকে বাণিজ্যিক চালান — নিয়ম মেনে যেকোনো ধরনের পণ্য আমরা নিরাপদে পরিবহন করি।</p>
    </div>
    <div class="grid grid-4">
      <div class="card"><h3>কাপড় ও পোশাক</h3></div>
      <div class="card"><h3>ইলেকট্রনিক্স</h3></div>
      <div class="card"><h3>ফার্নিচার</h3></div>
      <div class="card"><h3>ওষুধ ও কসমেটিক্স</h3></div>
      <div class="card"><h3>উপহার সামগ্রী</h3></div>
      <div class="card"><h3>বই ও ডকুমেন্ট</h3></div>
      <div class="card"><h3>শুকনো খাবার</h3></div>
      <div class="card"><h3>মেশিনারি</h3></div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band">
      <h2>আপনার পণ্যের জন্য সঠিক সেবা বেছে নিন</h2>
      <p>কোন সেবাটি আপনার জন্য উপযুক্ত বুঝতে পারছেন না? আমাদের সাথে কথা বলুন — বিনামূল্যে পরামর্শ দেবো।</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('quote') }}" class="btn btn-lg btn-navy">কোটেশন নিন</a>
        <a href="{{ route('pricing') }}" class="btn btn-lg btn-ghost">মূল্য তালিকা দেখুন</a>
      </div>
    </div>
  </div>
</section>
@endsection
