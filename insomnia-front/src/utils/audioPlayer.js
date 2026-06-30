/**
 * audioPlayer.js — Modul Pemutar Musik Global Persisten
 * 
 * Mengelola state audio, UI player bar, dan persistensi lintas halaman.
 * Singleton: objek Audio dan state disimpan di window.__audioPlayerData
 * sehingga tetap hidup saat View Transitions mengganti konten halaman.
 */

import { getToken, isLoggedIn } from './auth.js';

const STORAGE_KEY = 'tidurnyenyak_player_state';
const DEFAULT_THUMB = "data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%231a2729%22/></svg>";

// =============================================
// HELPER
// =============================================

function formatTime(secs) {
  if (isNaN(secs) || secs < 0) return '00:00';
  const m = Math.floor(secs / 60);
  const s = Math.floor(secs % 60);
  return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
}

// =============================================
// PERSISTENSI STATE (localStorage)
// =============================================

function saveState(p) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      track: p.currentTrack,
      tracks: p.tracks,
      trackIndex: p.currentTrackIndex,
      time: p.audio ? p.audio.currentTime : 0,
      volume: p.volume,
      isRepeat: p.isRepeat,
    }));
  } catch (e) { /* abaikan error kuota penuh */ }
}

function loadState() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch { return null; }
}

// =============================================
// UPDATE UI
// =============================================

function updatePlayPauseIcon(p) {
  const playIcon = document.getElementById('play-icon');
  const pauseIcon = document.getElementById('pause-icon');
  if (p.isPlaying) {
    playIcon?.classList.add('hidden');
    pauseIcon?.classList.remove('hidden');
  } else {
    playIcon?.classList.remove('hidden');
    pauseIcon?.classList.add('hidden');
  }
}

function updateVolumeUI(p) {
  const sliders = [document.getElementById('volume-slider'), document.getElementById('mobile-volume-slider')];
  const fills = [document.getElementById('volume-bar-fill'), document.getElementById('mobile-volume-bar-fill')];
  const highIcons = [document.getElementById('volume-high'), document.getElementById('mobile-volume-high')];
  const muteIcons = [document.getElementById('volume-mute'), document.getElementById('mobile-volume-mute')];

  sliders.forEach(s => { if (s) s.value = p.volume; });
  fills.forEach(f => { if (f) f.style.height = `${p.volume}%`; });

  if (p.volume === 0) {
    highIcons.forEach(i => i?.classList.add('hidden'));
    muteIcons.forEach(i => i?.classList.remove('hidden'));
  } else {
    highIcons.forEach(i => i?.classList.remove('hidden'));
    muteIcons.forEach(i => i?.classList.add('hidden'));
  }
}

function updateRepeatUI(p) {
  const btns = [document.getElementById('player-repeat-btn'), document.getElementById('mobile-player-repeat-btn')];
  btns.forEach(btn => {
    if (!btn) return;
    if (p.isRepeat) {
      btn.classList.remove('text-muted');
      btn.classList.add('text-accent');
    } else {
      btn.classList.remove('text-accent');
      btn.classList.add('text-muted');
    }
  });
}

function showPlayerBar(p) {
  const bar = document.getElementById('bottom-player-bar');
  const title = document.getElementById('player-title');
  const artist = document.getElementById('player-artist');
  const thumb = document.getElementById('player-thumbnail');

  if (!p.currentTrack) return;

  if (title) title.textContent = p.currentTrack.title;
  if (artist) {
    const artistName = p.currentTrack.artist_name || 'Tidur Nyenyak';
    artist.innerHTML = `${artistName} &bull; <span id="player-time-left">0:00 left</span>`;
  }
  if (thumb) thumb.src = p.currentTrack.thumbnail_url || DEFAULT_THUMB;

  if (bar) bar.classList.remove('translate-y-full');
}

function updateProgressUI(p) {
  if (!p.audio || p.isDraggingProgress) return;
  const current = p.audio.currentTime;
  const duration = p.audio.duration || 0;
  const remain = Math.max(0, duration - current);

  const slider = document.getElementById('progress-slider');
  const fill = document.getElementById('progress-bar-fill');
  const curText = document.getElementById('player-current-time');
  const timeLeft = document.getElementById('player-time-left');

  if (slider) slider.value = current;
  if (fill && duration > 0) fill.style.width = `${(current / duration) * 100}%`;
  if (curText) curText.textContent = formatTime(current);
  if (timeLeft) timeLeft.textContent = `${formatTime(remain)} left`;
}

function updateDurationUI(p) {
  if (!p.audio) return;
  const dur = document.getElementById('player-total-duration');
  const slider = document.getElementById('progress-slider');
  if (dur) dur.textContent = formatTime(p.audio.duration);
  if (slider) slider.max = p.audio.duration;
}

// =============================================
// AUDIO PLAYBACK
// =============================================

function setupAudioListeners(p) {
  if (!p.audio) return;

  p.audio.addEventListener('timeupdate', () => updateProgressUI(p));

  p.audio.addEventListener('loadedmetadata', () => {
    if (typeof window.hideMoonLoader === 'function') window.hideMoonLoader();
    updateDurationUI(p);
    updateProgressUI(p);
  });

  p.audio.addEventListener('ended', () => {
    if (p.isRepeat) {
      p.audio.currentTime = 0;
      p.audio.play();
    } else {
      playNext(p);
    }
  });

  p.audio.addEventListener('error', (e) => {
    if (typeof window.hideMoonLoader === 'function') window.hideMoonLoader();
    console.error('Audio error:', e);
  });
}

function playTrack(p, track, tracks, index) {
  // Hentikan audio sebelumnya
  if (p.audio) {
    p.audio.pause();
    p.audio.src = '';
    p.audio = null;
  }

  p.currentTrack = track;
  p.tracks = tracks;
  p.currentTrackIndex = index;

  if (typeof window.showMoonLoader === 'function') {
    window.showMoonLoader('Menyiapkan Audio...');
  }

  showPlayerBar(p);

  const token = getToken();
  const streamUrl = `http://127.0.0.1:8000/api/stream/${track.id}?token=${token}`;

  p.audio = new Audio(streamUrl);
  p.audio.volume = p.volume / 100;

  setupAudioListeners(p);

  p.audio.play()
    .then(() => {
      p.isPlaying = true;
      updatePlayPauseIcon(p);
      saveState(p);
    })
    .catch(err => {
      if (typeof window.hideMoonLoader === 'function') window.hideMoonLoader();
      console.error('Audio playback error:', err);
      alert('Gagal memutar audio. Pastikan berkas audio valid.');
    });
}

function togglePlayPause(p) {
  if (!p.audio) {
    if (p.tracks.length > 0) {
      playTrack(p, p.tracks[0], p.tracks, 0);
    }
    return;
  }

  if (p.isPlaying) {
    p.audio.pause();
    p.isPlaying = false;
  } else {
    p.audio.play();
    p.isPlaying = true;
  }
  updatePlayPauseIcon(p);
  saveState(p);
}

function playNext(p) {
  if (p.tracks.length === 0) return;
  let next = p.currentTrackIndex + 1;
  if (next >= p.tracks.length) next = 0;
  playTrack(p, p.tracks[next], p.tracks, next);
}

function playPrev(p) {
  if (p.tracks.length === 0) return;
  let prev = p.currentTrackIndex - 1;
  if (prev < 0) prev = p.tracks.length - 1;
  playTrack(p, p.tracks[prev], p.tracks, prev);
}

// =============================================
// EVENT BINDING (hanya dipanggil sekali)
// =============================================

function bindPlayerEvents(p) {
  const playBtn = document.getElementById('player-play-btn');
  const prevBtn = document.getElementById('player-prev-btn');
  const nextBtn = document.getElementById('player-next-btn');
  const repeatBtns = [document.getElementById('player-repeat-btn'), document.getElementById('mobile-player-repeat-btn')];
  const progressSlider = document.getElementById('progress-slider');
  
  const volumeSliders = [document.getElementById('volume-slider'), document.getElementById('mobile-volume-slider')];
  const volumeIconBtn = document.getElementById('volume-icon-btn');

  const mobileVolIconBtn = document.getElementById('mobile-volume-icon-btn');
  const mobileVolPopup = document.getElementById('mobile-volume-popup');

  playBtn?.addEventListener('click', () => togglePlayPause(p));
  prevBtn?.addEventListener('click', () => playPrev(p));
  nextBtn?.addEventListener('click', () => playNext(p));

  repeatBtns.forEach(btn => {
    btn?.addEventListener('click', () => {
      p.isRepeat = !p.isRepeat;
      updateRepeatUI(p);
      saveState(p);
    });
  });

  progressSlider?.addEventListener('input', (e) => {
    p.isDraggingProgress = true;
    if (!p.audio) return;
    const targetTime = parseFloat(e.target.value);
    
    // Update visual teks menit dan bar fill
    const curText = document.getElementById('player-current-time');
    const fill = document.getElementById('progress-bar-fill');
    if (curText) curText.textContent = formatTime(targetTime);
    if (fill && p.audio.duration > 0) {
      fill.style.width = `${(targetTime / p.audio.duration) * 100}%`;
    }
  });

  progressSlider?.addEventListener('change', (e) => {
    if (!p.audio) return;
    p.audio.currentTime = parseFloat(e.target.value);
    p.isDraggingProgress = false;
  });

  volumeSliders.forEach(slider => {
    slider?.addEventListener('input', (e) => {
      const vol = parseFloat(e.target.value);
      p.volume = vol;
      if (p.audio) p.audio.volume = vol / 100;
      updateVolumeUI(p);
      saveState(p);
    });
  });

  const desktopVolPopup = document.getElementById('desktop-volume-popup');

  // Desktop Volume Icon (Toggles Popup)
  volumeIconBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    desktopVolPopup?.classList.toggle('hidden');
    desktopVolPopup?.classList.toggle('flex');
  });

  // Mobile Volume Icon (Toggles Popup)
  mobileVolIconBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileVolPopup?.classList.toggle('hidden');
    mobileVolPopup?.classList.toggle('flex');
  });

  document.addEventListener('click', (e) => {
    if (!mobileVolIconBtn?.contains(e.target) && !mobileVolPopup?.contains(e.target)) {
      mobileVolPopup?.classList.add('hidden');
      mobileVolPopup?.classList.remove('flex');
    }
    if (!volumeIconBtn?.contains(e.target) && !desktopVolPopup?.contains(e.target)) {
      desktopVolPopup?.classList.add('hidden');
      desktopVolPopup?.classList.remove('flex');
    }
  });
}

// =============================================
// INISIALISASI (dipanggil setiap astro:page-load)
// =============================================

export function initAudioPlayer() {
  // Buat singleton — objek ini hidup di window dan bertahan lintas navigasi
  if (!window.__audioPlayerData) {
    window.__audioPlayerData = {
      audio: null,
      currentTrack: null,
      tracks: [],
      currentTrackIndex: -1,
      isPlaying: false,
      isRepeat: false,
      volume: 80,
      previousVolume: 80,
      isDraggingProgress: false,
      _uiBound: false,
      _saveInterval: null,
    };
  }

  const p = window.__audioPlayerData;

  // Bind event listener UI hanya SEKALI (elemen player bar bertahan via transition:persist)
  if (!p._uiBound) {
    bindPlayerEvents(p);
    p._uiBound = true;

    // Pulihkan state dari localStorage (hanya saat pertama kali dimuat)
    const saved = loadState();
    if (saved && saved.track && isLoggedIn()) {
      p.currentTrack = saved.track;
      p.tracks = saved.tracks || [];
      p.currentTrackIndex = saved.trackIndex ?? 0;
      p.volume = saved.volume ?? 80;
      p.isRepeat = saved.isRepeat || false;

      // Tampilkan player bar dengan info lagu terakhir (status: dijeda)
      showPlayerBar(p);
      updatePlayPauseIcon(p);
      updateVolumeUI(p);
      updateRepeatUI(p);

      // Siapkan objek Audio di posisi terakhir, tapi JANGAN putar otomatis
      const token = getToken();
      if (token && saved.track.id) {
        const streamUrl = `http://127.0.0.1:8000/api/stream/${saved.track.id}?token=${token}`;
        p.audio = new Audio(streamUrl);
        p.audio.volume = p.volume / 100;

        p.audio.addEventListener('loadedmetadata', () => {
          if (saved.time && saved.time > 0) {
            p.audio.currentTime = saved.time;
          }
          updateDurationUI(p);
          updateProgressUI(p);
        }, { once: true });

        setupAudioListeners(p);
      }
    }
  } else {
    // Navigasi berikutnya: perbarui UI agar sinkron dengan state yang sedang berjalan
    if (p.currentTrack) {
      showPlayerBar(p);
      updatePlayPauseIcon(p);
      updateVolumeUI(p);
      updateRepeatUI(p);
      if (p.audio) updateProgressUI(p);
    }
  }

  // Simpan state secara berkala setiap 3 detik
  if (p._saveInterval) clearInterval(p._saveInterval);
  p._saveInterval = setInterval(() => {
    if (p.currentTrack) saveState(p);
  }, 3000);

  // Ekspos API publik untuk dipakai halaman lain (soundscape.astro, dll.)
  window.audioPlayer = {
    playTrack: (track, tracksList, idx) => playTrack(p, track, tracksList, idx),
    getCurrentTrackId: () => p.currentTrack?.id,
    isCurrentlyPlaying: () => p.isPlaying,
  };
}
