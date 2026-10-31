@extends('layouts.app')
@section('title', 'যোগাযোগ')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / যোগাযোগ</div>
    <h1>যোগাযোগ করুন</h1>
    <p>আমরা সবসময় আপনার পাশে আছি।</p>
  </div>
</div>

<section>
  <div class="container contact-grid">
    <div class="contact-info">
      <div class="info-item"><span class="label">যুক্তরাজ্য অফিস</span><b>লন্ডন</b><span>736–740 Romford Road, Unit 2b, London, E12 6BT<br/>+44 7424 197746</span></div>
      <div class="info-item"><span class="label">বাংলাদেশ অফিস</span><b>ঢাকা</b><span>উত্তরা, ঢাকা<br/>+880 1968-851370</span></div>
      <div class="info-item"><span class="label">ইমেইল</span><b>hello@primecargoukbd.com</b><span>যেকোনো প্রশ্নে ইমেইল করুন</span></div>
      <div class="info-item"><span class="label">হোয়াটসঅ্যাপ</span><b>+44 7424 197746</b><span>২৪/৭ বাংলা সাপোর্ট</span></div>
    </div>

    <div class="form-card">
      @if(session('success'))
        <div class="form-success" style="margin-bottom:20px">{{ session('success') }}</div>
      @endif
      <h2 style="font-size:1.5rem;margin-bottom:6px">আমাদের বার্তা পাঠান</h2>
      <p style="margin-bottom:20px">ফর্মটি পূরণ করুন, আমরা শীঘ্রই উত্তর দেবো।</p>
      <form method="POST" action="{{ route('contact.store') }}">
        @csrf
        <div class="form-row">
          <div class="field"><label>আপনার নাম *</label><input type="text" name="name" value="{{ old('name') }}" required placeholder="পূর্ণ নাম" /></div>
          <div class="field"><label>ফোন নম্বর</label><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+44 / +880" /></div>
        </div>
        <div class="field"><label>ইমেইল</label><input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" /></div>
        <div class="field">
          <label>বিষয়</label>
          <select name="subject">
            <option>সাধারণ জিজ্ঞাসা</option>
            <option>মূল্য / কোটেশন</option>
            <option>শিপমেন্ট ট্র্যাকিং</option>
            <option>অভিযোগ</option>
            <option>অন্যান্য</option>
          </select>
        </div>
        <div class="field"><label>বার্তা *</label><textarea name="message" required rows="5" placeholder="আপনার বার্তা লিখুন…">{{ old('message') }}</textarea></div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">বার্তা পাঠান</button>
      </form>
    </div>
  </div>
</section>
@endsection
