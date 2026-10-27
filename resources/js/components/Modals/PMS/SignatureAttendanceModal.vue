<template>
  <Dialog
    v-model:visible="visible"
    :closable="true"
    modal
    :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100' },
      content: { class: 'p-6 sm:p-8' },
      header: { class: 'hidden' }
    }"
    :style="{ width: '32rem', padding: '0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '92vw' }"
  >
    <div class="relative">
      <!-- Close Button -->
      <button
        @click="closeModal"
        class="absolute -top-2 -right-2 p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
      >
        <Icon icon="lucide:x" width="20" />
      </button>

      <!-- Modal Header -->
      <div class="flex items-start gap-4 mb-6">
        <div
          :class="props.action === 'check-out' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-blue-50 text-blue-600 border-blue-200'"
          class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border"
        >
          <Icon :icon="props.action === 'check-out' ? 'lucide:log-out' : 'lucide:log-in'" width="24" />
        </div>
        <div>
          <span
            :class="props.action === 'check-out' ? 'bg-amber-100/70 text-amber-800' : 'bg-blue-100/70 text-blue-800'"
            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
          >
            Digital Signature Verification
          </span>
          <h2 class="text-xl font-black text-gray-900 mt-1">
            {{ props.action === 'check-out' ? 'Shift Check-Out' : 'Duty Check-In' }}
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Record your verified attendance session for {{ todayFormatted }}
          </p>
        </div>
      </div>

      <!-- Security Notice Banner -->
      <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 text-xs text-slate-600 leading-relaxed">
        <div class="flex items-center gap-1.5 font-bold text-slate-800 mb-1">
          <Icon icon="lucide:shield-check" width="15" class="text-emerald-600" />
          <span>Non-Repudiation Security Protocol</span>
        </div>
        <p>
          Attendance requires your confidential <strong>Digital Signature Password</strong> (Approval PIN). This credentials gives signing authority on official documents and must never be shared or delegated.
        </p>
      </div>

      <!-- PIN Input Form -->
      <form @submit.prevent="submitAttendance" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
            Enter Digital Signature Password / PIN
          </label>
          <div class="relative">
            <input
              ref="pinInputRef"
              :type="showPin ? 'text' : 'password'"
              v-model="pin"
              maxlength="10"
              placeholder="••••"
              class="w-full tracking-widest text-lg font-bold text-center px-4 py-3 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-900"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              @click="showPin = !showPin"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1"
            >
              <Icon :icon="showPin ? 'lucide:eye-off' : 'lucide:eye'" width="18" />
            </button>
          </div>
          <p v-if="errorMessage" class="text-xs text-red-500 font-semibold mt-1.5 flex items-center gap-1">
            <Icon icon="lucide:alert-circle" width="14" />
            <span>{{ errorMessage }}</span>
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button
            type="button"
            @click="closeModal"
            class="px-5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-xl transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !pin"
            :class="props.action === 'check-out' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700'"
            class="inline-flex items-center gap-2 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Icon v-if="loading" icon="lucide:loader-2" width="16" class="animate-spin" />
            <Icon v-else :icon="props.action === 'check-out' ? 'lucide:check-circle-2' : 'lucide:key-round'" width="16" />
            <span>{{ props.action === 'check-out' ? 'Confirm Check-Out' : 'Authorize Check-In' }}</span>
          </button>
        </div>
      </form>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue"
import axios from "axios"
import moment from "moment"
import { useToast } from "primevue/usetoast"

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  action: { type: String, default: 'check-in' } // 'check-in' or 'check-out'
})

const emit = defineEmits(["update:modelValue", "verified"])

const toast = useToast()
const pin = ref("")
const showPin = ref(false)
const loading = ref(false)
const errorMessage = ref("")
const pinInputRef = ref(null)

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val)
})

const todayFormatted = computed(() => {
  return moment().format("dddd, MMMM Do, YYYY")
})

const closeModal = () => {
  pin.value = ""
  errorMessage.value = ""
  emit("update:modelValue", false)
}

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    pin.value = ""
    errorMessage.value = ""
    nextTick(() => {
      pinInputRef.value?.focus()
    })
  }
})

const submitAttendance = async () => {
  if (!pin.value.trim()) return

  loading.value = true
  errorMessage.value = ""

  const endpoint = props.action === 'check-out'
    ? '/dashboard/pms/attendance/check-out'
    : '/dashboard/pms/attendance/check-in'

  try {
    const response = await axios.post(endpoint, {
      approval_pin: pin.value.trim()
    })

    if (response.data.success) {
      toast.add({
        severity: "success",
        summary: props.action === 'check-out' ? "Check-Out Verified" : "Check-In Verified",
        detail: response.data.message || "Attendance recorded successfully.",
        life: 4000
      })
      emit("verified", response.data)
      closeModal()
    } else {
      errorMessage.value = response.data.message || "Attendance verification failed."
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = err.response.data.message
    } else {
      errorMessage.value = "An error occurred during verification. Please try again."
    }
  } finally {
    loading.value = false
  }
}
</script>
