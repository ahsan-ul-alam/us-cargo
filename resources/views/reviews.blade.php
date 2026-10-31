@extends('layouts.app')
@section('title', 'গ্রাহক রিভিউ')
@section('description', 'প্রাইম কার্গো সম্পর্কে গ্রাহকদের মতামত পড়ুন এবং আপনার অভিজ্ঞতা জানান।')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / রিভিউ</div>
    <h1>গ্রাহকদের রিভিউ</h1>
    <p>যারা আমাদের সেবা নিয়েছেন তাদের অভিজ্ঞতা পড়ুন, এবং আপনার মতামত জানান।</p>
  </div>
</div>

<section>
  <div class="container">
    @if($reviews->count())
    <div class="grid grid-3">
      @foreach($reviews as $r)
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
    @else
      <p style="text-align:center">এখনো কোনো রিভিউ নেই। প্রথম রিভিউটি আপনিই দিন!</p>
    @endif

    <div class="review-form-wrap" id="write-review">
      <h3>আপনার অভিজ্ঞতা জানান</h3>
      <p style="margin-bottom:16px;font-size:.94rem">আপনার রিভিউ আমাদের টিম যাচাই করার পর ওয়েবসাইটে প্রকাশিত হবে।</p>
      @if(session('review_sent'))<div class="review-ok">{{ session('review_sent') }}</div>@endif
      @if($errors->any())<div class="review-err">{{ $errors->first() }}</div>@endif
      <form method="POST" action="{{ route('reviews.store') }}" class="review-form">
        @csrf
        <input type="text" name="name" placeholder="আপনার নাম" value="{{ old('name') }}" required maxlength="100" />
        <input type="text" name="location" placeholder="শহর / দেশ (ঐচ্ছিক)" value="{{ old('location') }}" maxlength="100" />
        <select name="rating" aria-label="রেটিং">
          @foreach([5,4,3,2,1] as $n)<option value="{{ $n }}" @selected(old('rating', 5) == $n)>{{ str_repeat('★', $n) }}</option>@endforeach
        </select>
        <textarea name="body" rows="4" placeholder="আপনার মতামত লিখুন" required maxlength="1000">{{ old('body') }}</textarea>
        <button class="btn btn-primary" type="submit">রিভিউ জমা দিন</button>
      </form>
    </div>
  </div>
</section>
@endsection
