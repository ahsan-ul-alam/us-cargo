@extends('layouts.app')
@section('title', 'শিপমেন্ট ট্র্যাকিং')

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="{{ route('home') }}">হোম</a> / ট্র্যাকিং</div>
    <h1>শিপমেন্ট ট্র্যাকিং</h1>
    <p>আপনার ট্র্যাকিং নম্বর দিন এবং রিয়েল-টাইমে পণ্যের অবস্থান জানুন।</p>
  </div>
</div>

<section>
  <div class="container">
    <div class="track-wrap">
      <form id="trackForm">
        @csrf
        <div class="track-input-row">
          <input type="text" id="trackCode" placeholder="ট্র্যাকিং নম্বর দিন, যেমন: PC-2026-00001" />
          <button class="btn btn-navy" type="submit">খুঁজুন</button>
        </div>
      </form>

      <div id="trackResult" class="hidden">
        <div id="trackCard" style="background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:32px;margin-bottom:28px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
            <div>
              <div style="font-size:.8rem;color:var(--muted);margin-bottom:4px;">ট্র্যাকিং নম্বর</div>
              <div style="font-size:1.3rem;font-weight:800;color:var(--navy)" id="resTrackNum"></div>
            </div>
            <span class="status-pill" id="resStatus"></span>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;padding-top:20px;border-top:1px solid var(--line);">
            <div><div style="font-size:.8rem;color:var(--muted)">প্রেরক</div><b id="resSender"></b></div>
            <div><div style="font-size:.8rem;color:var(--muted)">প্রাপক</div><b id="resRecipient"></b></div>
            <div><div style="font-size:.8rem;color:var(--muted)">ওজন</div><b id="resWeight"></b></div>
            <div><div style="font-size:.8rem;color:var(--muted)">আনুমানিক ডেলিভারি</div><b id="resEta"></b></div>
          </div>
        </div>

        <h3 style="margin-bottom:20px;color:var(--navy)">ট্র্যাকিং টাইমলাইন</h3>
        <div class="timeline" id="resTimeline"></div>
      </div>

      <div id="trackEmpty" class="hidden" style="text-align:center;padding:48px 24px;color:var(--muted);">
        <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
        <p>এই ট্র্যাকিং নম্বরটি পাওয়া যায়নি। দয়া করে আবার চেষ্টা করুন।</p>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
const form = document.getElementById('trackForm');
const resultDiv = document.getElementById('trackResult');
const emptyDiv = document.getElementById('trackEmpty');

function renderTimeline(events) {
  const tl = document.getElementById('resTimeline');
  if (!events.length) { tl.innerHTML = '<p style="color:var(--muted)">এখনো কোনো ট্র্যাকিং ইভেন্ট নেই।</p>'; return; }
  tl.innerHTML = events.map((e, i) => `
    <div class="tl-item ${i === 0 ? 'active' : 'done'}">
      <div class="dot"></div>
      <b>${e.status}</b>${e.location ? ` · <span style="color:var(--muted)">${e.location}</span>` : ''}
      <small>${e.occurred_at}</small>
      <p style="margin-top:4px;color:var(--muted)">${e.description}</p>
    </div>
  `).join('');
}

async function doTrack(code) {
  resultDiv.classList.add('hidden');
  emptyDiv.classList.add('hidden');
  const res = await fetch('{{ route('tracking.search') }}', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
    body: JSON.stringify({code})
  });
  const data = await res.json();
  if (!data.found) { emptyDiv.classList.remove('hidden'); return; }
  const s = data.shipment;
  document.getElementById('resTrackNum').textContent = s.tracking_number;
  document.getElementById('resStatus').textContent = s.status_label;
  document.getElementById('resSender').textContent = s.sender_name + ' (' + s.sender_country + ')';
  document.getElementById('resRecipient').textContent = s.recipient_name + ' (' + s.recipient_country + ')';
  document.getElementById('resWeight').textContent = s.weight_kg + ' kg';
  document.getElementById('resEta').textContent = s.estimated_delivery || 'অজানা';
  renderTimeline(s.events);
  resultDiv.classList.remove('hidden');
}

form.addEventListener('submit', (e) => {
  e.preventDefault();
  const code = document.getElementById('trackCode').value.trim();
  if (code) doTrack(code);
});

// Pre-fill from URL
const params = new URLSearchParams(window.location.search);
const code = params.get('code');
if (code) { document.getElementById('trackCode').value = code; doTrack(code); }
</script>
@endpush
