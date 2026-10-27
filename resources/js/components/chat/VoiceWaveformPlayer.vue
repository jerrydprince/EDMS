<template>
  <div
    class="flex items-center gap-3 p-2.5 px-3.5 rounded-2xl shadow-xs border transition-all max-w-[310px] sm:max-w-[340px]"
    :class="isSent 
      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-blue-400/20 shadow-sm' 
      : 'bg-white text-gray-800 border-gray-100 shadow-sm'"
  >
    <!-- Play/Pause / Buffering Button -->
    <button
      type="button"
      @click="togglePlay"
      :disabled="isLoading"
      class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-150 active:scale-95 shadow-xs"
      :class="isSent 
        ? (isPlaying ? 'bg-white text-blue-600 ring-2 ring-white/50' : 'bg-white/20 text-white hover:bg-white/30') 
        : (isPlaying ? 'bg-primary text-white ring-2 ring-primary/20' : 'bg-primary/10 text-primary hover:bg-primary/20')"
    >
      <Icon v-if="isLoading" icon="lucide:loader-2" class="text-xl animate-spin" />
      <Icon v-else-if="isPlaying" icon="material-symbols:pause-rounded" class="text-2xl" />
      <Icon v-else icon="material-symbols:play-arrow-rounded" class="text-2xl ml-0.5" />
    </button>

    <!-- Waveform & Scrubbing Area -->
    <div class="flex flex-col flex-1 min-w-0 select-none">
      <div
        class="h-7 flex items-center justify-between gap-[2px] cursor-pointer relative py-0.5 w-full"
        ref="waveformContainer"
        @mousedown="onMouseDown"
        @touchstart.passive="onTouchStart"
        @touchmove.passive="onTouchMove"
        @touchend="onTouchEnd"
      >
        <!-- Waveform Bars -->
        <div
          v-for="(height, index) in waveformHeights"
          :key="index"
          class="flex-1 min-w-[2px] max-w-[4px] rounded-full transition-all duration-75 block flex-shrink-0"
          :style="{
            height: getBarHeight(height),
            backgroundColor: getBarColor(index)
          }"
        ></div>

        <!-- Scrubber Thumb -->
        <div
          v-if="progress > 0 && progress < 1"
          class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 w-3 h-3 rounded-full shadow-md pointer-events-none transition-all duration-75"
          :class="isSent ? 'bg-sky-300 ring-2 ring-white' : 'bg-primary ring-2 ring-white'"
          :style="{ left: `${progress * 100}%` }"
        ></div>
      </div>

      <!-- Time Stamps & Speed Multiplier -->
      <div
        class="flex items-center justify-between text-[10.5px] font-medium mt-0.5"
        :class="isSent ? 'text-blue-100' : 'text-gray-500'"
      >
        <span>{{ formatTime(currentTime) }}</span>
        
        <div class="flex items-center gap-2">
          <span>{{ displayDuration }}</span>
          <button
            type="button"
            @click.stop="cycleSpeed"
            class="px-1.5 py-0.5 rounded font-bold text-[9.5px] transition-colors"
            :class="isSent 
              ? 'bg-white/20 text-white hover:bg-white/30' 
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            {{ playbackRate }}x
          </button>
        </div>
      </div>
    </div>

    <!-- Hidden Native Audio Element -->
    <audio
      ref="audioRef"
      :src="src"
      preload="metadata"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @canplay="onCanPlay"
      @ended="onEnded"
      @error="onError"
      class="hidden"
    ></audio>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  src: {
    type: String,
    required: true,
  },
  isSent: {
    type: Boolean,
    default: false,
  },
});

// Unique instance ID to prevent multiple audios playing at once
const playerId = 'vn_' + Math.random().toString(36).substring(2, 9);

const audioRef = ref(null);
const waveformContainer = ref(null);
const isPlaying = ref(false);
const isLoading = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const playbackRate = ref(1);
const isDragging = ref(false);
const realWaveformBars = ref([]);

const barCount = 30;

// Read duration hint embedded in filename (e.g., audio_1234_dur5200.webm => 5.2s)
const checkHintedDuration = () => {
  const match = (props.src || '').match(/_dur(\d+)/i);
  if (match) {
    const ms = parseInt(match[1]);
    if (isFinite(ms) && ms > 0) {
      duration.value = ms / 1000;
    }
  }
};

// Fallback bars shaped like realistic speech activity (vocal dynamic envelope)
const fallbackBars = computed(() => {
  const str = props.src || 'voice-note';
  let seed = 0;
  for (let i = 0; i < str.length; i++) {
    seed = (seed << 5) - seed + str.charCodeAt(i);
    seed |= 0;
  }
  const bars = [];
  for (let i = 0; i < barCount; i++) {
    const norm = i / (barCount - 1);
    const envelope = Math.sin(norm * Math.PI); // soft edges, vocal center peaks
    const wave1 = Math.abs(Math.sin((Math.abs(seed) + i * 13) * 0.45));
    const wave2 = Math.abs(Math.cos((Math.abs(seed) + i * 7) * 0.3));
    const combined = 0.25 + 0.75 * envelope * ((wave1 + wave2) / 2);
    bars.push(Math.round(Math.max(16, Math.min(100, combined * 100))));
  }
  return bars;
});

const waveformHeights = computed(() => {
  if (realWaveformBars.value && realWaveformBars.value.length === barCount) {
    return realWaveformBars.value;
  }
  return fallbackBars.value;
});

const progress = computed(() => {
  const d = duration.value;
  const c = currentTime.value;
  if (!isFinite(d) || d <= 0) {
    return isPlaying.value ? 0.05 : 0;
  }
  return Math.min(1, Math.max(0, c / d));
});

const displayDuration = computed(() => {
  if (isPlaying.value && duration.value > 0 && isFinite(duration.value)) {
    const remaining = Math.max(0, duration.value - currentTime.value);
    return `-${formatTime(remaining)}`;
  }
  return formatTime(duration.value);
});

const getBarHeight = (height) => {
  const h = (typeof height === 'number' && isFinite(height) && height > 0) ? height : 35;
  return `${Math.max(5, Math.min(24, Math.round(24 * (h / 100))))}px`;
};

const getBarColor = (index) => {
  const barRatio = (index + 0.5) / barCount;
  const isPlayed = barRatio <= progress.value;

  if (props.isSent) {
    return isPlayed ? '#38BDF8' : 'rgba(255, 255, 255, 0.45)';
  } else {
    return isPlayed ? '#2563EB' : '#CBD5E1';
  }
};

const formatTime = (seconds) => {
  if (!seconds || !isFinite(seconds) || isNaN(seconds) || seconds < 0) return '0:00';
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Extract real audio peaks via AudioContext
const loadAudioData = async () => {
  if (!props.src) return;
  checkHintedDuration();

  try {
    const response = await fetch(props.src);
    if (!response.ok) return;
    const arrayBuffer = await response.arrayBuffer();
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;

    const audioCtx = new AudioContextClass();
    const decodedBuffer = await audioCtx.decodeAudioData(arrayBuffer);

    if (decodedBuffer && isFinite(decodedBuffer.duration) && decodedBuffer.duration > 0) {
      duration.value = decodedBuffer.duration;
    }

    const channelData = decodedBuffer.getChannelData(0);
    if (!channelData || channelData.length < barCount) {
      audioCtx.close();
      return;
    }

    const blockSize = Math.floor(channelData.length / barCount);
    if (blockSize <= 0) {
      audioCtx.close();
      return;
    }

    const bars = [];
    for (let i = 0; i < barCount; i++) {
      let sum = 0;
      const start = i * blockSize;
      for (let j = 0; j < blockSize; j++) {
        sum += Math.abs(channelData[start + j] || 0);
      }
      const avg = sum / blockSize;
      const calculatedHeight = Math.max(16, Math.min(100, Math.round((isFinite(avg) ? avg : 0.08) * 450)));
      bars.push(isFinite(calculatedHeight) ? calculatedHeight : 35);
    }

    if (bars.length === barCount) {
      realWaveformBars.value = bars;
    }
    audioCtx.close();
  } catch (e) {
    // Graceful fallback to fallbackBars
  }
};

const onLoadedMetadata = () => {
  if (!audioRef.value) return;
  const d = audioRef.value.duration;
  if (isFinite(d) && d > 0) {
    duration.value = d;
  }
};

const onCanPlay = () => {
  isLoading.value = false;
};

const onTimeUpdate = () => {
  if (!audioRef.value || isDragging.value) return;
  currentTime.value = audioRef.value.currentTime;

  // Dynamically expand duration if unknown so progress bar is always moving
  if (!isFinite(duration.value) || duration.value <= 0 || currentTime.value > duration.value) {
    duration.value = Math.max(duration.value || 0, currentTime.value);
  }
};

const onEnded = () => {
  isPlaying.value = false;
  if (audioRef.value) {
    duration.value = Math.max(duration.value || 0, audioRef.value.currentTime);
    audioRef.value.currentTime = 0;
  }
  currentTime.value = 0;
};

const onError = () => {
  isLoading.value = false;
  isPlaying.value = false;
};

const togglePlay = () => {
  if (!audioRef.value) return;

  if (isPlaying.value) {
    audioRef.value.pause();
    isPlaying.value = false;
  } else {
    // Pause any other playing audio on the page to eliminate double playback / echo
    document.querySelectorAll('audio, video').forEach((el) => {
      if (el !== audioRef.value) {
        el.pause();
      }
    });

    window.dispatchEvent(new CustomEvent('voice-note-play', { detail: { playerId } }));

    isLoading.value = true;
    audioRef.value
      .play()
      .then(() => {
        isPlaying.value = true;
        isLoading.value = false;
      })
      .catch((err) => {
        isLoading.value = false;
        isPlaying.value = false;
      });
  }
};

const seekToPosition = (clientX) => {
  if (!waveformContainer.value || !audioRef.value) return;
  const rect = waveformContainer.value.getBoundingClientRect();
  const clickX = clientX - rect.left;
  const ratio = Math.max(0, Math.min(1, clickX / rect.width));

  const maxDur = (isFinite(duration.value) && duration.value > 0) ? duration.value : 0;
  if (maxDur > 0) {
    const target = ratio * maxDur;
    if (isFinite(target) && !isNaN(target)) {
      audioRef.value.currentTime = target;
      currentTime.value = target;
    }
  }
};

const onMouseDown = (event) => {
  isDragging.value = true;
  seekToPosition(event.clientX);
  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', onMouseUp);
};

const onMouseMove = (event) => {
  if (isDragging.value) {
    seekToPosition(event.clientX);
  }
};

const onMouseUp = () => {
  if (isDragging.value) {
    isDragging.value = false;
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
  }
};

const onTouchStart = (event) => {
  if (event.touches.length > 0) {
    isDragging.value = true;
    seekToPosition(event.touches[0].clientX);
  }
};

const onTouchMove = (event) => {
  if (isDragging.value && event.touches.length > 0) {
    seekToPosition(event.touches[0].clientX);
  }
};

const onTouchEnd = () => {
  isDragging.value = false;
};

const cycleSpeed = () => {
  if (!audioRef.value) return;
  const rates = [1, 1.5, 2];
  const nextIdx = (rates.indexOf(playbackRate.value) + 1) % rates.length;
  playbackRate.value = rates[nextIdx];
  audioRef.value.playbackRate = playbackRate.value;
};

// Stop playback if another voice note starts playing
const handleOtherAudioPlay = (e) => {
  if (e.detail?.playerId !== playerId && isPlaying.value) {
    if (audioRef.value) {
      audioRef.value.pause();
    }
    isPlaying.value = false;
  }
};

onMounted(() => {
  window.addEventListener('voice-note-play', handleOtherAudioPlay);
  loadAudioData();
});

watch(() => props.src, (newSrc) => {
  if (newSrc) {
    currentTime.value = 0;
    isPlaying.value = false;
    loadAudioData();
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('voice-note-play', handleOtherAudioPlay);
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', onMouseUp);
  if (audioRef.value) {
    audioRef.value.pause();
  }
});
</script>
