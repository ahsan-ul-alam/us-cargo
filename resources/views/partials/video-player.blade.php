{{-- Minimal player: only play/pause and mute/unmute. Expects $video (VideoEmbed::parse) and $id. --}}
<div class="vp" id="{{ $id }}" data-provider="{{ $video['provider'] }}">
  <div class="vp-frame">
    @if($video['provider'] === 'file')
      <video src="{{ $video['src'] }}" autoplay muted loop playsinline preload="metadata"></video>
    @else
      <iframe src="{{ $video['src'] }}" allow="autoplay; encrypted-media" referrerpolicy="strict-origin-when-cross-origin" tabindex="-1" title="Promotional video"></iframe>
    @endif
    <div class="vp-shield"></div>
  </div>
  <div class="vp-controls">
    <button type="button" class="vp-btn" data-act="play" aria-label="Play / Pause">
      <svg class="i-pause" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
      <svg class="i-play" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="display:none"><path d="M7 4l13 8-13 8z"/></svg>
    </button>
    <button type="button" class="vp-btn" data-act="mute" aria-label="Sound on / off">
      <svg class="i-muted" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M3 9v6h4l5 4V5L7 9H3zm13.6 3l2.7-2.7-1.4-1.4-2.7 2.7-2.7-2.7-1.4 1.4 2.7 2.7-2.7 2.7 1.4 1.4 2.7-2.7 2.7 2.7 1.4-1.4z"/></svg>
      <svg class="i-sound" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="display:none"><path d="M3 9v6h4l5 4V5L7 9H3zm13.5 3A4.5 4.5 0 0 0 14 8v8a4.5 4.5 0 0 0 2.5-4zM14 3.2v2.1a7 7 0 0 1 0 13.4v2.1a9 9 0 0 0 0-17.6z"/></svg>
    </button>
    <input type="range" class="vp-vol" min="0" max="100" value="100" aria-label="Volume" />
  </div>
</div>

@once
@push('scripts')
<script>
(function () {
  var players = document.querySelectorAll('.vp');
  if (!players.length) return;

  function load(src) { var s = document.createElement('script'); s.src = src; document.head.appendChild(s); return s; }

  function setup(vp, api) {
    var playing = true, muted = true;
    function sync() {
      vp.querySelector('.i-pause').style.display = playing ? '' : 'none';
      vp.querySelector('.i-play').style.display = playing ? 'none' : '';
      vp.querySelector('.i-muted').style.display = muted ? '' : 'none';
      vp.querySelector('.i-sound').style.display = muted ? 'none' : '';
    }
    vp.querySelector('[data-act=play]').addEventListener('click', function () {
      playing = !playing; playing ? api.play() : api.pause(); sync();
    });
    var vol = vp.querySelector('.vp-vol'), level = 100;
    function applyMute() { api.setMuted(muted); vol.value = muted ? 0 : level; sync(); }
    vp.querySelector('[data-act=mute]').addEventListener('click', function () {
      muted = !muted;
      if (!muted && level === 0) level = 100;
      applyMute();
      if (!muted) api.setVolume(level);
    });
    vol.addEventListener('input', function () {
      var v = +vol.value;
      if (v > 0) { level = v; muted = false; api.setMuted(false); api.setVolume(v); }
      else { muted = true; api.setMuted(true); }
      sync();
    });
    vol.value = 0;
    api.onState(function (isPlaying) { playing = isPlaying; sync(); });
    sync();
  }

  var ytQueue = [], vimeoQueue = [];
  players.forEach(function (vp) {
    var provider = vp.dataset.provider;
    var video = vp.querySelector('video'), iframe = vp.querySelector('iframe');
    if (video) {
      setup(vp, {
        play: function () { video.play(); }, pause: function () { video.pause(); },
        setMuted: function (m) { video.muted = m; },
        setVolume: function (v) { video.volume = v / 100; },
        onState: function (cb) { video.addEventListener('play', function () { cb(true); }); video.addEventListener('pause', function () { cb(false); }); }
      });
    } else if (provider === 'youtube') ytQueue.push([vp, iframe]);
    else if (provider === 'vimeo') vimeoQueue.push([vp, iframe]);
  });

  // Commands issued before the embedded player is ready are queued, not lost.
  function gate() {
    var ready = false, q = [];
    return {
      run: function (fn) { ready ? fn() : q.push(fn); },
      open: function () { ready = true; q.splice(0).forEach(function (fn) { fn(); }); }
    };
  }

  if (ytQueue.length) {
    var ytPrev = window.onYouTubeIframeAPIReady;
    window.onYouTubeIframeAPIReady = function () {
      if (ytPrev) ytPrev();
      ytQueue.forEach(function (it) {
        var cb = function () {}, g = gate(), p;
        p = new YT.Player(it[1], { events: {
          onReady: function () { g.open(); },
          onStateChange: function (e) {
            if (e.data === 1 || e.data === 3) cb(true);        // playing / buffering
            else if (e.data === 2 || e.data === 5) cb(false);  // paused / not started
          }
        } });
        setup(it[0], {
          play: function () { g.run(function () { p.playVideo(); }); },
          pause: function () { g.run(function () { p.pauseVideo(); }); },
          setMuted: function (m) { g.run(function () { if (m) p.mute(); else p.unMute(); }); },
          setVolume: function (v) { g.run(function () { p.setVolume(v); }); },
          onState: function (f) { cb = f; }
        });
      });
    };
    if (window.YT && YT.Player) window.onYouTubeIframeAPIReady();
    else load('https://www.youtube.com/iframe_api');
  }

  if (vimeoQueue.length) {
    var startVimeo = function () {
      vimeoQueue.forEach(function (it) {
        var p = new Vimeo.Player(it[1]), g = gate();
        p.ready().then(function () { g.open(); });
        setup(it[0], {
          play: function () { g.run(function () { p.play().catch(function () {}); }); },
          pause: function () { g.run(function () { p.pause().catch(function () {}); }); },
          setMuted: function (m) { g.run(function () { p.setMuted(m).catch(function () {}); }); },
          setVolume: function (v) { g.run(function () { p.setVolume(v / 100).catch(function () {}); }); },
          onState: function (f) { p.on('play', function () { f(true); }); p.on('pause', function () { f(false); }); }
        });
      });
    };
    if (window.Vimeo && Vimeo.Player) startVimeo();
    else load('https://player.vimeo.com/api/player.js').onload = startVimeo;
  }
})();
</script>
@endpush
@endonce
