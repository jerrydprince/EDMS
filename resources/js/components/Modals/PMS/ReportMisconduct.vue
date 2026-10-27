<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-2xl max-w-2xl' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-4' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '40rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '92vw' }">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-xl bg-red-50 text-red-600 shadow-xs">
            <Icon icon="lucide:shield-alert" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Report to SERVICOM / Grievance Desk</h1>
            <p class="text-xs text-gray-500 mt-0.5">Confidential lodging of misconduct, harassment, intimidation, or unfair treatment</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-xs">
      <!-- Whistleblower Protection Notice -->
      <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 flex items-start gap-3 text-xs text-amber-900">
        <Icon icon="lucide:shield-check" width="20" class="text-amber-600 shrink-0 mt-0.5" />
        <div>
          <p class="font-bold">Federal Whistleblower & Anti-Victimization Protection</p>
          <p class="text-amber-700 mt-0.5 leading-relaxed">
            Reports lodged here are routed strictly to the SERVICOM Nodal Officer and Investigation Committee. Your complaint cannot be intercepted or viewed by the accused party.
          </p>
        </div>
      </div>

      <!-- Anonymous Toggle -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
        <div>
          <p class="text-sm font-semibold text-gray-800">Lodge as Anonymous Whistleblower</p>
          <p class="text-xs text-gray-500">Your user profile and identity will be completely omitted from the investigation file.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" v-model="form.is_anonymous" class="sr-only peer" />
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
        </label>
      </div>

      <!-- Accused Staff Info -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Accused Officer / Colleague</label>
        <div v-if="props.user?.id" class="mt-1.5 p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
          <div>
            <p class="text-sm font-bold text-gray-900">{{ props.user.first_name }} {{ props.user.last_name }}</p>
            <p class="text-xs text-gray-500">{{ props.user.email }}</p>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Subject of Report</span>
        </div>
        <select v-else v-model="form.accused_user_id" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
          <option value="">Select Staff Member</option>
          <option v-for="u in userList" :key="u.id" :value="u.id">
            {{ u.first_name }} {{ u.last_name }} ({{ u.email }})
          </option>
        </select>
      </div>

      <!-- Grievance Category -->
      <div>
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Incident Nature / Category</label>
          <span class="text-red-500 text-xs">* Required</span>
        </div>
        <select v-model="form.category" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
          <option value="">Select Category</option>
          <option value="harassment">Workplace Harassment (Verbal / Sexual / Bullying)</option>
          <option value="intimidation">Intimidation & Coercion by Senior Colleague</option>
          <option value="victimization">Victimization & Unfair Administrative Treatment</option>
          <option value="extortion">Extortion, Bribery Demands & Corrupt Practices</option>
          <option value="misconduct">Negligence of Duty, Insubordination & Process Breach</option>
          <option value="other">Other Civil Service Grievance</option>
        </select>
      </div>

      <!-- Detailed Statement -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Detailed Statement of Facts</label>
        <textarea
          v-model="form.complaint_details"
          rows="4"
          placeholder="State the facts chronologically: what happened, dates, locations, witnesses, or directives given..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 resize-none"
        ></textarea>
      </div>

      <!-- Supporting Document / Evidence Upload -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Supporting Evidence / Documents (Optional)</label>
        <div class="p-3 border border-dashed border-gray-300 rounded-xl mt-1.5 text-center bg-slate-50">
          <div v-if="!form.evidence" class="space-y-1">
            <Icon icon="lucide:cloud-upload" width="24" class="mx-auto text-gray-400" />
            <p class="text-xs font-semibold text-gray-700">Attach letters, memos, screenshots, audio, or PDFs</p>
            <p class="text-[11px] text-gray-400">Max size: 20MB</p>
            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 mt-2 text-xs font-bold text-blue-700 bg-white border border-gray-200 rounded-lg shadow-2xs hover:bg-slate-50 cursor-pointer">
              <Icon icon="lucide:folder-open" width="14" />
              <span>Browse Computer</span>
              <input type="file" @change="handleFileUpload" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp3,.wav" />
            </label>
          </div>
          <div v-else class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200 text-xs">
            <div class="flex items-center gap-2 truncate">
              <Icon icon="lucide:file-check" width="16" class="text-emerald-600" />
              <span class="font-medium text-gray-800 truncate">{{ form.evidence.name }}</span>
            </div>
            <button @click="form.evidence = null" class="text-red-500 hover:text-red-700 text-xs font-bold ml-2">Remove</button>
          </div>
        </div>
      </div>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-3 px-4 py-2">
        <button
          @click="closeModal"
          class="px-5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-slate-50 border border-gray-200 rounded-xl transition-all shadow-2xs"
        >
          Cancel
        </button>
        <button
          @click="submit"
          :disabled="form.processing || !form.category || !form.complaint_details"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:send" width="14" />
          <span>Lodge with SERVICOM</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const props = defineProps(["modelValue", "user"]);
const emit = defineEmits(["update:modelValue"]);
const page = usePage();
const toast = useToast();

const userList = ref([]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const getTargetUserId = () => {
  if (props.user?.id) return props.user.id;
  let url = page.url;
  const urlParts = url.split("/");
  const lastPart = urlParts[urlParts.length - 1];
  const num = parseInt(lastPart, 10);
  return !isNaN(num) ? num : null;
};

const form = useForm({
  accused_user_id: null,
  category: "",
  complaint_details: "",
  is_anonymous: false,
  evidence: null,
});

onMounted(() => {
  if (!props.user?.id) {
    axios.get("/dashboard/get-all-users").then((res) => {
      userList.value = res.data || [];
    }).catch(() => {});
  }
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const handleFileUpload = (event) => {
  form.evidence = event.target.files[0];
};

const submit = () => {
  form.accused_user_id = props.user?.id || getTargetUserId() || form.accused_user_id;

  if (!form.accused_user_id) {
    toast.add({
      severity: "warn",
      summary: "Missing Accused Officer",
      detail: "Please select the officer who is the subject of this report.",
      life: 3000,
    });
    return;
  }

  form.post("/dashboard/servicom/report", {
    onSuccess: () => {
      emit("update:modelValue", false);
      form.reset();
      toast.add({
        severity: "success",
        summary: "Report Lodged Successfully",
        detail: "Your grievance has been submitted securely to the SERVICOM Nodal Desk.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Submission Error",
        detail: Object.values(err)[0] || "Could not submit report. Check input.",
        life: 4000,
      });
    },
  });
};
</script>
