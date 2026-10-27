<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'border-0 rounded-3xl overflow-hidden shadow-2xl' },
      header: { class: 'hidden' },
      content: { class: 'p-0 bg-white dark:bg-gray-900 overflow-hidden' },
      footer: { class: 'hidden' }
    }"
    :style="{ width: '30rem' }"
    :breakpoints="{ '1199px': '75vw', '575px': '92vw' }"
    class="verify-dialog-wrapper"
  >
    <div class="relative w-full bg-white dark:bg-gray-900 rounded-3xl overflow-hidden">
      
      <!-- Top Decorative Banner & Grid Pattern -->
      <div class="relative w-full pt-8 pb-10 flex flex-col items-center justify-center bg-gradient-to-b from-slate-50 to-white dark:from-gray-800/40 dark:to-gray-900">
        <!-- Close 'X' Button -->
        <button
          type="button"
          @click="closeModal"
          class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition"
          aria-label="Close"
        >
          <Icon icon="lucide:x" class="text-sm" />
        </button>

        <!-- Subtle Grid Icon Background -->
        <img :src="gridIcon" alt="" class="h-20 opacity-35 select-none pointer-events-none" />

        <!-- Floating Icon Badge -->
        <div
          v-if="activePage === 1"
          class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 border-2 border-emerald-500/20 shadow-lg shadow-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 absolute -bottom-8"
        >
          <Icon icon="mdi:email-outline" class="text-3xl" />
        </div>

        <div
          v-else
          class="w-16 h-16 rounded-2xl bg-emerald-500 border-2 border-emerald-400 shadow-lg shadow-emerald-500/25 flex items-center justify-center text-white absolute -bottom-8"
        >
          <Icon icon="lucide:check-circle-2" class="text-3xl" />
        </div>
      </div>

      <!-- Main Body -->
      <div class="px-6 sm:px-8 pt-10 pb-6 flex flex-col items-center text-center">

        <!-- Step 1: OTP Input -->
        <template v-if="activePage === 1">
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
            Verify Email Address
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-2 max-w-xs leading-relaxed">
            Please enter the OTP sent to
            <span class="font-semibold text-slate-800 dark:text-gray-200 break-all">{{ targetEmail }}</span>
            to verify email address.
          </p>

          <!-- 6-Digit Individual Fields (Completely blank by default, strictly 1 digit) -->
          <div class="flex items-center justify-center gap-2 sm:gap-3.5 my-6">
            <template v-for="(digit, idx) in digits" :key="idx">
              <!-- Separator dash between 3rd and 4th digit -->
              <span v-if="idx === 3" class="text-xl font-bold text-slate-300 dark:text-gray-600 select-none mx-0.5">-</span>
              <input
                :id="'verify-otp-' + idx"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                autocomplete="off"
                :value="digits[idx]"
                @keydown="onKeyDown($event, idx)"
                @input="onInput($event, idx)"
                @paste="onPaste($event)"
                @focus="$event.target.select()"
                class="w-11 h-14 sm:w-13 sm:h-16 text-center text-2xl sm:text-3xl font-bold font-mono border-2 rounded-2xl outline-none transition-all select-all shadow-xs"
                :class="digits[idx] 
                  ? 'border-[#3575DD] bg-blue-50/20 text-[#3575DD] dark:text-blue-400 dark:border-blue-500' 
                  : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-800 dark:text-white focus:border-[#3575DD] focus:ring-4 focus:ring-blue-500/10'"
              />
            </template>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="w-full mt-2 space-y-3">
            <button
              type="button"
              @click="submitOtp"
              :disabled="!isComplete || isVerifying"
              class="w-full py-3 px-4 text-sm font-semibold rounded-xl bg-[#3575DD] hover:bg-[#2c66c7] text-white shadow-sm shadow-blue-500/20 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <Icon v-if="isVerifying" icon="lucide:loader-2" class="animate-spin text-base" />
              <span>{{ isVerifying ? 'Verifying...' : 'Verify your account' }}</span>
            </button>

            <!-- Resend Link -->
            <button
              type="button"
              @click="resendOtp"
              :disabled="resendCountdown > 0 || isResending"
              class="w-full text-xs font-semibold text-[#3575DD] dark:text-blue-400 hover:underline flex items-center justify-center gap-1.5 transition py-1 disabled:opacity-50"
            >
              <Icon icon="lucide:refresh-cw" class="text-sm" :class="{ 'animate-spin': isResending }" />
              <span>{{ resendCountdown > 0 ? `Resend Code (${resendCountdown}s)` : 'Resend Code' }}</span>
            </button>
          </div>
        </template>

        <!-- Step 2: Verification Success -->
        <template v-else>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
            Email Verified
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-2 max-w-xs leading-relaxed">
            Email address has been successfully verified.
          </p>

          <div class="w-full mt-8">
            <button
              type="button"
              @click="submitSuccess"
              class="w-full py-3 px-4 text-sm font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition"
            >
              Confirm & Close
            </button>
          </div>
        </template>

      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from "vue";
import { useToast } from "primevue/usetoast";
import { usePage } from "@inertiajs/vue3";
import gridIcon from "../../../../public/images/grid.svg";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  email: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(["update:modelValue", "verified"]);

const page = usePage();
const toast = useToast();

const activePage = ref(1);
const digits = ref(["", "", "", "", "", ""]);
const isVerifying = ref(false);
const isResending = ref(false);
const resendCountdown = ref(0);
let countdownTimer = null;

const targetEmail = computed(() => {
  return props.email || page.props.user?.email || "your email address";
});

const isComplete = computed(() => {
  return digits.value.every(d => d !== "" && /^[0-9]$/.test(d));
});

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  }
});

// Auto-focus first empty field when modal opens
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    activePage.value = 1;
    digits.value = ["", "", "", "", "", ""];
    focusIndex(0);
  }
});

const focusIndex = (index) => {
  nextTick(() => {
    const el = document.getElementById("verify-otp-" + index);
    if (el) {
      el.focus();
      el.select();
    }
  });
};

// Strict keyboard interaction: allows only 1 digit, replaces cleanly, auto-advances
const onKeyDown = (event, index) => {
  const key = event.key;

  // Numbers 0-9
  if (/^[0-9]$/.test(key)) {
    event.preventDefault();
    digits.value[index] = key;
    if (index < 5) {
      focusIndex(index + 1);
    }
    return;
  }

  // Backspace: clears current digit or steps back
  if (key === "Backspace") {
    event.preventDefault();
    if (digits.value[index]) {
      digits.value[index] = "";
    } else if (index > 0) {
      digits.value[index - 1] = "";
      focusIndex(index - 1);
    }
    return;
  }

  // Delete
  if (key === "Delete") {
    event.preventDefault();
    digits.value[index] = "";
    return;
  }

  // Arrow navigation
  if (key === "ArrowLeft" && index > 0) {
    event.preventDefault();
    focusIndex(index - 1);
    return;
  }
  if (key === "ArrowRight" && index < 5) {
    event.preventDefault();
    focusIndex(index + 1);
    return;
  }

  // Allow tab and escape
  if (["Tab", "Enter", "Escape"].includes(key) || event.ctrlKey || event.metaKey) {
    return;
  }

  // Block any non-numeric characters (letters, punctuation, etc.)
  event.preventDefault();
};

// Fallback for mobile virtual keyboards
const onInput = (event, index) => {
  const rawVal = event.target.value.replace(/\D/g, "");
  if (rawVal.length > 0) {
    const singleDigit = rawVal.slice(-1);
    digits.value[index] = singleDigit;
    event.target.value = singleDigit;
    if (index < 5) {
      focusIndex(index + 1);
    }
  } else {
    digits.value[index] = "";
    event.target.value = "";
  }
};

// Paste handler distributes 6 numbers across all 6 boxes
const onPaste = (event) => {
  event.preventDefault();
  const pasteText = (event.clipboardData || window.clipboardData).getData("text") || "";
  const numbers = pasteText.replace(/\D/g, "").slice(0, 6);

  if (numbers.length > 0) {
    const chars = numbers.split("");
    for (let i = 0; i < 6; i++) {
      digits.value[i] = chars[i] || "";
    }
    const nextIdx = Math.min(numbers.length, 5);
    focusIndex(nextIdx);
  }
};

// Resend OTP
const resendOtp = () => {
  if (resendCountdown.value > 0 || isResending.value) return;

  isResending.value = true;
  setTimeout(() => {
    isResending.value = false;
    resendCountdown.value = 60;

    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
      if (resendCountdown.value > 0) {
        resendCountdown.value--;
      } else {
        clearInterval(countdownTimer);
      }
    }, 1000);

    toast.add({
      severity: "info",
      summary: "OTP Dispatched",
      detail: `A new 6-digit verification code was sent to ${targetEmail.value}.`,
      life: 3500
    });
  }, 600);
};

// Submit verification
const submitOtp = () => {
  if (!isComplete.value) {
    toast.add({
      severity: "warn",
      summary: "Incomplete Code",
      detail: "Please enter all 6 digits of your verification code.",
      life: 3000
    });
    const firstEmpty = digits.value.findIndex(d => !d);
    if (firstEmpty !== -1) {
      focusIndex(firstEmpty);
    }
    return;
  }

  isVerifying.value = true;
  const fullCode = digits.value.join("");

  setTimeout(() => {
    isVerifying.value = false;
    activePage.value = 2;
    toast.add({
      severity: "success",
      summary: "Email Verified",
      detail: "Your email address has been successfully verified.",
      life: 3000
    });
    emit("verified", fullCode);
  }, 600);
};

const closeModal = () => {
  digits.value = ["", "", "", "", "", ""];
  activePage.value = 1;
  emit("update:modelValue", false);
};

const submitSuccess = () => {
  closeModal();
};

onBeforeUnmount(() => {
  if (countdownTimer) clearInterval(countdownTimer);
});
</script>

<style scoped>
:deep(.p-dialog-header),
:deep(.p-dialog-footer) {
  display: none !important;
}
:deep(.p-dialog-content) {
  padding: 0 !important;
  border-radius: 1.5rem !important;
  overflow: hidden !important;
}
</style>
