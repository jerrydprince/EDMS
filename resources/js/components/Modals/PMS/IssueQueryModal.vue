<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-2xl max-w-2xl' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-4' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '42rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '80vw', '575px': '95vw' }">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-xl bg-amber-50 text-amber-700 shadow-2xs">
            <Icon icon="lucide:file-question" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Issue Official Disciplinary Query</h1>
            <p class="text-xs text-gray-500 mt-0.5">Formal administrative query governed by Public Service Rules (PSR)</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-2xs">
      <!-- PSR Guidance Banner -->
      <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 flex items-start gap-3 text-xs text-blue-900">
        <Icon icon="lucide:info" width="20" class="text-blue-600 shrink-0 mt-0.5" />
        <div>
          <p class="font-bold">Public Service Rules (Chapter 3 - Discipline)</p>
          <p class="text-blue-700 mt-0.5 leading-relaxed">
            Specify clearly the alleged acts of misconduct, time, and circumstances. The subordinate will be notified immediately and afforded the constitutional right to submit a formal written representation within the designated timeframe.
          </p>
        </div>
      </div>

      <!-- Recipient Subordinate -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Subordinate Recipient</label>
        <div v-if="props.user?.id" class="mt-1.5 p-3 rounded-xl bg-slate-50 border border-gray-200 flex items-center justify-between">
          <div>
            <p class="text-sm font-bold text-gray-900">{{ props.user.first_name }} {{ props.user.last_name }}</p>
            <p class="text-xs text-gray-500">{{ props.user.email }}</p>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Target Officer</span>
        </div>
        <div v-else>
          <select v-model="form.issued_to" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
            <option value="">-- Select Subordinate Staff --</option>
            <option v-for="u in userList" :key="u.id" :value="u.id">
              {{ u.first_name }} {{ u.last_name }} ({{ u.email }}) {{ u.department ? '- ' + (u.department.title || u.department.name) : '' }}
            </option>
          </select>
          <p v-if="userList.length === 0" class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200 mt-2">
            No eligible subordinates found under your purview. Under Public Service Rules, Assistant Directors can query Unit staff, Deputy Directors query Division staff, Directors query Department staff, and AGF can query all staff.
          </p>
        </div>
      </div>

      <!-- Query Subject & Category Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Subject / Matter</label>
          <input
            type="text"
            v-model="form.subject"
            placeholder="e.g. Query on Failure to Submit Monthly Reconciliations"
            class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20"
          />
        </div>

        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Grounds / PSR Infraction</label>
          <select v-model="form.incident_category" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
            <option value="">Select Grounds</option>
            <option value="Absence without Official Leave">Absence without Official Leave</option>
            <option value="Habitual Latecoming & Early Departure">Habitual Latecoming & Early Departure</option>
            <option value="Refusal of Lawful Directives / Insubordination">Refusal of Lawful Directives / Insubordination</option>
            <option value="Negligence of Duty & Task Dereliction">Negligence of Duty & Task Dereliction</option>
            <option value="Breach of Official Confidentiality">Breach of Official Confidentiality</option>
            <option value="Process & Compliance Breach">Process & Compliance Breach</option>
            <option value="General Misconduct">General Misconduct</option>
          </select>
        </div>
      </div>

      <!-- Deadline Hours Selection -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Mandatory Response Window</label>
        <div class="grid grid-cols-3 gap-3 mt-1.5">
          <label
            :class="form.deadline_hours === 24 ? 'border-amber-500 bg-amber-50/50 text-amber-900 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex items-center justify-center p-3 rounded-xl border cursor-pointer text-xs transition-all text-center"
          >
            <input type="radio" v-model="form.deadline_hours" :value="24" class="sr-only" />
            <span>24 Hours (Urgent)</span>
          </label>
          <label
            :class="form.deadline_hours === 48 ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex items-center justify-center p-3 rounded-xl border cursor-pointer text-xs transition-all text-center"
          >
            <input type="radio" v-model="form.deadline_hours" :value="48" class="sr-only" />
            <span>48 Hours (Standard PSR)</span>
          </label>
          <label
            :class="form.deadline_hours === 72 ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex items-center justify-center p-3 rounded-xl border cursor-pointer text-xs transition-all text-center"
          >
            <input type="radio" v-model="form.deadline_hours" :value="72" class="sr-only" />
            <span>72 Hours (Extended)</span>
          </label>
        </div>
      </div>

      <!-- Statement of Facts -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Statement of Facts & Circumstances</label>
        <textarea
          v-model="form.facts_description"
          rows="4"
          placeholder="State the specific dates, task references, directives given, and the exact nature of the default..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 resize-none"
        ></textarea>
      </div>

      <!-- Supporting Document / Proof Attachment -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Supporting Evidence / Document (Optional)</label>
        <div class="p-3 border border-dashed border-gray-300 rounded-xl mt-1.5 text-center bg-slate-50">
          <div v-if="!form.evidence" class="space-y-1">
            <Icon icon="lucide:paperclip" width="22" class="mx-auto text-gray-400" />
            <p class="text-xs font-semibold text-gray-700">Attach attendance logs, memos, task screenshots, or correspondence</p>
            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 mt-2 text-xs font-bold text-blue-700 bg-white border border-gray-200 rounded-lg shadow-2xs hover:bg-slate-50 cursor-pointer">
              <Icon icon="lucide:upload" width="13" />
              <span>Choose File</span>
              <input type="file" @change="handleFileUpload" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
            </label>
          </div>
          <div v-else class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200 text-xs">
            <div class="flex items-center gap-2 truncate">
              <Icon icon="lucide:file-check" width="16" class="text-blue-600" />
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
          :disabled="form.processing || !form.subject || !form.incident_category || !form.facts_description"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:send" width="14" />
          <span>Dispatch Official Query</span>
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

const form = useForm({
  issued_to: null,
  subject: "",
  incident_category: "",
  facts_description: "",
  deadline_hours: 48,
  evidence: null,
});

const canIssue = ref(true);

onMounted(() => {
  if (!props.user?.id) {
    axios.get("/dashboard/queries/subordinates").then((res) => {
      userList.value = res.data?.subordinates || [];
      canIssue.value = res.data?.can_issue ?? true;
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
  form.issued_to = props.user?.id || form.issued_to;

  if (!form.issued_to) {
    toast.add({
      severity: "warn",
      summary: "Missing Recipient",
      detail: "Please select the subordinate officer to receive this query.",
      life: 3000,
    });
    return;
  }

  form.post("/dashboard/queries", {
    onSuccess: () => {
      emit("update:modelValue", false);
      form.reset();
      toast.add({
        severity: "success",
        summary: "Query Dispatched",
        detail: "The official disciplinary query has been issued to the officer.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Validation Error",
        detail: Object.values(err)[0] || "Could not issue query. Check required fields.",
        life: 4000,
      });
    },
  });
};
</script>
