@extends('layouts.app')
@section('title', 'এজেন্ট হিসেবে আবেদন')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / এজেন্ট আবেদন</div>
    <h1>আমাদের এজেন্ট হোন</h1>
    <p>আমাদের সাথে ব্যবসায়িক অংশীদার হিসেবে যুক্ত হতে নিচের ফর্মটি পূরণ করুন।</p>
  </div>
</div>

<section>
  <div class="container" style="max-width:720px">
    <div class="form-card">
      @if(session('success'))
        <div class="form-success" style="margin-bottom:20px">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="form-error" style="margin-bottom:20px;color:#b42318">
          <ul style="margin:0;padding-left:18px">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      <h2 style="font-size:1.5rem;margin-bottom:6px">এজেন্ট আবেদন ফর্ম</h2>
      <p style="margin-bottom:20px">সব তথ্য সঠিকভাবে দিন। অনুমোদনের পর আমরা আপনার সাথে যোগাযোগ করবো।</p>

      <form method="POST" action="{{ route('agent.apply.store') }}">
        @csrf
        <div class="form-row">
          <div class="field"><label>আপনার নাম *</label><input type="text" name="name" value="{{ old('name') }}" required maxlength="200" placeholder="পূর্ণ নাম" /></div>
          <div class="field"><label>ফোন নম্বর *</label><input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="+44 / +880" /></div>
        </div>
        <div class="form-row">
          <div class="field"><label>কোম্পানির নাম *</label><input type="text" name="company_name" value="{{ old('company_name') }}" required maxlength="200" placeholder="কোম্পানি / প্রতিষ্ঠানের নাম" /></div>
          <div class="field"><label>ইমেইল *</label><input type="email" name="email" value="{{ old('email') }}" required maxlength="200" placeholder="you@example.com" /></div>
        </div>
        <div class="field"><label>ঠিকানা *</label><textarea name="address" required rows="3" maxlength="1000" placeholder="পূর্ণ ঠিকানা">{{ old('address') }}</textarea></div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">আবেদন জমা দিন</button>
      </form>
    </div>
  </div>
</section>
@endsection
