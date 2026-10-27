<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100' },
      content: { class: 'p-6 sm:p-8 max-h-[80vh] overflow-y-auto' },
      header: { class: 'hidden' },
      footer: { class: 'bg-slate-50 px-6 sm:px-8 py-4 border-t border-gray-100 flex items-center justify-end gap-3' }
    }"
    :style="{ width: '38rem', padding: '0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
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
          :class="form.leave_type === 'official_assignment' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 'bg-blue-50 text-blue-600 border-blue-200'"
          class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border"
        >
          <Icon :icon="form.leave_type === 'official_assignment' ? 'lucide:briefcase' : 'lucide:calendar-days'" width="24" />
        </div>
        <div>
          <span
            :class="form.leave_type === 'official_assignment' ? 'bg-indigo-100/70 text-indigo-800' : 'bg-blue-100/70 text-blue-800'"
            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
          >
            {{ isSelfApplication ? 'Self-Service Roster Request' : 'Supervisor Roster Entry' }}
          </span>
          <h2 class="text-xl font-black text-gray-900 mt-1">
            {{ isSelfApplication ? 'Apply for Leave / Duty Assignment' : 'Record Leave / Official Assignment' }}
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            {{ isSelfApplication ? 'Submit your scheduled dates and designate a relief officer for handover' : 'Register an approved leave window or official duty travel for this staff member' }}
          </p>
        </div>
      </div>

      <!-- Form Inputs -->
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Leave Type Selector -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Category / Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.leave_type"
            class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-gray-800 transition-all"
            required
          >
            <option value="annual">Annual Leave</option>
            <option value="casual">Casual / Emergency Leave</option>
            <option value="sick">Sick / Medical Leave</option>
            <option value="official_assignment">Official Duty Assignment / Out-of-Station Travel</option>
            <option value="maternity">Maternity / Paternity Leave</option>
            <option value="study">Study / Training Leave</option>
            <option value="compassionate">Compassionate Leave</option>
          </select>
        </div>

        <!-- Official Assignment Specific Fields -->
        <div v-if="form.leave_type === 'official_assignment'" class="p-4 bg-indigo-50/60 border border-indigo-100 rounded-2xl space-y-3">
          <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-900">
            <Icon icon="lucide:plane" width="14" class="text-indigo-600" />
            <span>Official Duty Travel Details</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                Destination / Station <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.destination"
                placeholder="e.g. Lagos Zonal Office, Field Audit"
                class="w-full text-xs px-3 py-2 bg-white border border-indigo-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800"
                required
              />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                Memo / Approval Ref
              </label>
              <input
                type="text"
                v-model="form.duty_memo_ref"
                placeholder="e.g. OAGF/ADM/2026/049"
                class="w-full text-xs px-3 py-2 bg-white border border-indigo-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800"
              />
            </div>
          </div>
          <p class="text-[11px] text-indigo-700 italic">
            * Approved official assignments are granted attendance exemption; days are logged as "Official Duty (Exempt)" and do not count as absences.
          </p>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
              Start Date <span class="text-red-500">*</span>
            </label>
            <input
              type="date"
              v-model="form.start_date"
              id="start_date"
              class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-gray-800 transition-all"
              required
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
              Completion Date <span class="text-red-500">*</span>
            </label>
            <input
              type="date"
              v-model="form.end_date"
              id="end_date"
              class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-gray-800 transition-all"
              required
            />
          </div>
        </div>

        <!-- Relief Officer Dropdown -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            {{ isAgf ? 'Designated Acting AGF (Directors of OAGF)' : 'Designated Relief / Handover Officer' }}
          </label>
          <select
            v-model="form.relief_user_id"
            class="w-full text-sm px-3.5 py-2.5 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-gray-800 transition-all"
          >
            <option :value="null">-- {{ isAgf ? 'Select an Acting Director' : 'No Relief Officer Designated' }} --</option>
            <option v-for="colleague in colleagues" :key="colleague.id" :value="colleague.id">
              {{ colleague.display_label || `${colleague.first_name} ${colleague.last_name} (${colleague.email})` }}
            </option>
          </select>
          <p class="text-[11px] text-gray-400 mt-1">
            <span v-if="isAgf" class="text-indigo-600 font-medium">
              As Accountant-General of the Federation, you can designate any Departmental Director to act and oversee workflows during your leave.
            </span>
            <span v-else>
              During leave, workflows and pending tasks will be delegated to this officer.
            </span>
          </p>
        </div>

        <!-- Reason / Narrative -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Reason / Handover Notes <span class="text-gray-400 text-[11px] font-normal">(Optional)</span>
          </label>
          <textarea
            v-model="form.reason"
            rows="3"
            placeholder="Add relevant notes, handover instructions, or comments..."
            class="w-full text-sm px-3.5 py-2 bg-slate-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-gray-800 transition-all resize-none"
          ></textarea>
        </div>
      </form>
    </div>

    <!-- Modal Footer Actions -->
    <template #footer>
      <button
        type="button"
        @click="closeModal"
        class="px-5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-slate-200 rounded-xl transition-colors"
      >
        Cancel
      </button>
      <button
        type="button"
        @click="submit"
        :disabled="form.processing || !form.start_date || !form.end_date"
        :class="form.leave_type === 'official_assignment' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-blue-600 hover:bg-blue-700'"
        class="inline-flex items-center gap-2 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Icon v-if="form.processing" icon="lucide:loader-2" width="16" class="animate-spin" />
        <Icon v-else :icon="isSelfApplication ? 'lucide:send' : 'lucide:check-circle'" width="16" />
        <span>{{ isSelfApplication ? 'Submit Application' : 'Save Record' }}</span>
      </button>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, onMounted } from "vue"
import { useToast } from "primevue/usetoast"
import { useForm, usePage, router } from "@inertiajs/vue3"
import axios from "axios"

const props = defineProps(["modelValue", "user"])
const emit = defineEmits(["update:modelValue"])
const page = usePage()
const toast = useToast()

const colleagues = ref([])
const isAgf = ref(false)

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val)
})

const isSelfApplication = computed(() => {
  return page.props.user?.id === getUserId()
})

const getUserId = () => {
  if (props.user?.id) return props.user.id
  let url = page.url
  const urlParts = url.split('/')
  const lastPart = urlParts[urlParts.length - 1]
  const num = parseInt(lastPart, 10)
  return !isNaN(num) ? num : page.props.user?.id
}

const form = useForm({
  user_id: null,
  start_date: '',
  end_date: '',
  leave_type: 'annual',
  relief_user_id: null,
  destination: '',
  duty_memo_ref: '',
  reason: ''
})

const fetchColleagues = async () => {
  try {
    const res = await axios.get('/dashboard/get-leave-relief-officers', {
      params: { user_id: getUserId() }
    })
    if (res.data) {
      isAgf.value = !!res.data.is_agf
      colleagues.value = res.data.officers || []
    }
  } catch (err) {
    console.error('Error fetching relief officers:', err)
  }
}

onMounted(() => {
  fetchColleagues()
})

const closeModal = () => {
  emit("update:modelValue", false)
}

const submit = () => {
  if (!form.start_date) {
    document.getElementById('start_date')?.focus()
    return
  }
  if (!form.end_date) {
    document.getElementById('end_date')?.focus()
    return
  }
  if (form.end_date < form.start_date) {
    toast.add({
      severity: "error",
      summary: "Invalid Date Range",
      detail: "Completion date cannot be earlier than start date.",
      life: 4000
    })
    return
  }

  form.user_id = getUserId()

  const endpoint = isSelfApplication.value ? '/dashboard/apply-leave' : '/dashboard/add-leave'

  form.post(endpoint, {
    onSuccess: () => {
      router.reload({ only: ['user'] })
      emit("update:modelValue", false)
      toast.add({
        severity: "success",
        summary: "Submitted Successfully",
        detail: isSelfApplication.value
          ? "Your request has been submitted for supervisor approval."
          : "Leave / Duty assignment recorded successfully.",
        life: 4000
      })
    },
    onError: (errors) => {
      toast.add({
        severity: "error",
        summary: "Submission Failed",
        detail: Object.values(errors)[0] || "Could not process request.",
        life: 4000
      })
    }
  })
}
</script>
