/**
 * Elegant crystal-clear chime for real-time notifications
 * Built with Web Audio API (zero external assets needed, zero network latency)
 */
let audioCtx = null;

export const playNotificationChime = () => {
    try {
        if (typeof window !== 'undefined' && localStorage.getItem('edms_chime_muted') === 'true') {
            return;
        }
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;

        if (!audioCtx) {
            audioCtx = new AudioContextClass();
        }

        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const now = audioCtx.currentTime;

        // Note 1: E5 (659.25 Hz) - bright harmonic chime
        const osc1 = audioCtx.createOscillator();
        const gain1 = audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(659.25, now);
        gain1.gain.setValueAtTime(0.25, now);
        gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.start(now);
        osc1.stop(now + 0.35);

        // Note 2: B5 (987.77 Hz) - sweet high resolving tone
        const osc2 = audioCtx.createOscillator();
        const gain2 = audioCtx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(987.77, now + 0.1);
        gain2.gain.setValueAtTime(0.3, now + 0.1);
        gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
        osc2.connect(gain2);
        gain2.connect(audioCtx.destination);
        osc2.start(now + 0.1);
        osc2.stop(now + 0.55);
    } catch (e) {
        console.warn('Audio chime could not play:', e);
    }
};
