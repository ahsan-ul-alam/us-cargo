@php
  $popup = \App\Models\Popup::current();
  $pv = $popup && $popup->type === 'video' ? \App\Support\VideoEmbed::parse($popup->video_url, true, true, false) : null;
  $link = $popup?->button_url;
@endphp
@if($popup && ($popup->type !== 'video' || $pv))
<div class="pp-overlay" id="offerPopup" role="dialog" aria-modal="true" aria-label="{{ $popup->title ?? $popup->name }}">
  <div class="pp-box {{ $popup->type === 'video' ? 'pp-wide' : '' }}">
    <button type="button" class="pp-close" aria-label="বন্ধ করুন">&times;</button>
    @if(in_array($popup->type, ['image', 'image_text']) && $popup->image)
      @if($popup->type === 'image' && $link)<a href="{{ $link }}"><img class="pp-img" src="{{ asset('storage/'.$popup->image) }}" alt="{{ $popup->name }}" /></a>
      @else<img class="pp-img" src="{{ asset('storage/'.$popup->image) }}" alt="{{ $popup->name }}" />@endif
    @endif
    @if($pv)
      @include('partials.video-player', ['video' => $pv, 'id' => 'popupVideo'])
    @endif
    @if($popup->type !== 'image' && ($popup->title || $popup->body || ($popup->button_text && $link)))
      <div class="pp-body">
        @if($popup->title)<h3>{{ $popup->title }}</h3>@endif
        @if($popup->body)<p>{{ $popup->body }}</p>@endif
        @if($popup->button_text && $link)<a href="{{ $link }}" class="btn btn-primary">{{ $popup->button_text }}</a>@endif
      </div>
    @elseif($popup->type === 'image' && $popup->button_text && $link)
      <div class="pp-body"><a href="{{ $link }}" class="btn btn-primary">{{ $popup->button_text }}</a></div>
    @endif
  </div>
</div>
@push('scripts')
<script>
(function () {
  var el = document.getElementById('offerPopup');
  if (!el) return;
  var key = 'popup_{{ $popup->id }}_{{ $popup->updated_at?->timestamp }}', freq = '{{ $popup->frequency }}';
  function seen() {
    try {
      if (freq === 'once_per_session') return sessionStorage.getItem(key);
      if (freq === 'once_per_day') return Date.now() - (+localStorage.getItem(key) || 0) < 864e5;
    } catch (e) {}
    return false;
  }
  function mark() {
    try {
      if (freq === 'once_per_session') sessionStorage.setItem(key, '1');
      if (freq === 'once_per_day') localStorage.setItem(key, Date.now());
    } catch (e) {}
  }
  if (seen()) { el.remove(); return; }
  function close() { el.classList.remove('open'); el.remove(); }  // remove() also stops popup video/audio
  setTimeout(function () { el.classList.add('open'); mark(); }, {{ (int) $popup->delay_seconds * 1000 }});
  el.querySelector('.pp-close').addEventListener('click', close);
  el.addEventListener('click', function (e) { if (e.target === el) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
@endpush
@endif
