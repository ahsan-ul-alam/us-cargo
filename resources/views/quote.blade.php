@extends('layouts.app')
@section('title', 'কোটেশন নিন')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / কোটেশন</div>
    <h1>ফ্রি কোটেশন নিন</h1>
    <p>ফর্মটি পূরণ করুন, আমরা দ্রুত আপনাকে সেরা মূল্য জানাবো।</p>
  </div>
</div>

<section>
  <div class="container" style="max-width:760px">
    @if(session('success'))
      <div class="form-success" style="margin-bottom:24px">{{ session('success') }}</div>
    @endif

    <div class="form-card">
      <div class="form-note" style="margin-bottom:24px">
        এয়ার কার্গো রেট: <b>£৬ প্রতি কেজি</b> · ডেলিভারি সময়: ৫–৮ কার্যদিবস
      </div>
      <form method="POST" action="{{ route('quote.store') }}">
        @csrf
        <div class="form-row">
          <div class="field">
            <label>আপনার নাম *</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="পূর্ণ নাম" />
            @error('name')<span style="color:red;font-size:.85rem">{{ $message }}</span>@enderror
          </div>
          <div class="field">
            <label>ফোন নম্বর *</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+44 / +880" />
          </div>
        </div>
        <div class="field">
          <label>ইমেইল</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" />
        </div>
        <div class="form-row">
          <div class="field">
            <label>আনুমানিক ওজন (কেজি)</label>
            <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" min="0" step="0.1" placeholder="যেমন: 10.5" />
          </div>
          <div class="field">
            <label>পাঠানোর দিক *</label>
            <select name="direction" required>
              <option value="uk_to_bd" @selected(old('direction')=='uk_to_bd')>যুক্তরাজ্য → বাংলাদেশ</option>
              <option value="bd_to_uk" @selected(old('direction')=='bd_to_uk')>বাংলাদেশ → যুক্তরাজ্য</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label>সার্ভিস টাইপ *</label>
          <select name="service_type" required>
            <option value="air_cargo" @selected(old('service_type')=='air_cargo')>এয়ার কার্গো</option>
            <option value="door_to_door" @selected(old('service_type')=='door_to_door')>ডোর-টু-ডোর</option>
            <option value="parcel" @selected(old('service_type')=='parcel')>পার্সেল</option>
          </select>
        </div>
        <div class="field">
          <label>পণ্যের বিবরণ</label>
          <textarea name="description" rows="3" placeholder="কী পাঠাচ্ছেন সংক্ষেপে লিখুন…">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">কোটেশন পাঠান</button>
      </form>
    </div>
  </div>
</section>
@endsection
