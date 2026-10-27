<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-2xl max-w-2xl' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-4' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '44rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '85vw', '575px': '95vw' }">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-xl bg-emerald-50 text-emerald-700 shadow-2xs">
            <Icon icon="lucide:clipboard-check" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Record Investigation Findings & Report</h1>
            <p class="text-xs text-gray-500 mt-0.5">Submit Panel Findings, Recommendations & Action Taken to AGF</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-2xs">
      <!-- Panel Info Banner -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-gray-700 space-y-1">
        <div class="flex items-center justify-between">
          <span class="font-bold text-gray-900">{{ panel?.panel_title }}</span>
          <span class="px-2 py-0.5 rounded-full font-bold bg-purple-100 text-purple-800 uppercase text-[10px]">
            {{ panel?.status }}
          </span>
        </div>
        <p class="text-gray-500">
          Chair: <strong>{{ panel?.chairperson?.first_name }} {{ panel?.chairperson?.last_name }}</strong> &bull;
          Secretary: <strong>{{ panel?.secretary?.first_name }} {{ panel?.secretary?.last_name }}</strong>
        </p>
      </div>

      <!-- Investigation Findings -->
      <div>
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Summary of Findings & Evidence</label>
          <span class="text-red-500 text-xs">* Required</span>
        </div>
        <textarea
          v-model="form.findings"
          rows="4"
          placeholder="State whether the allegations were substantiated, witnesses interviewed, and evidence evaluated..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500/20 resize-none"
        ></textarea>
      </div>

      <!-- Recommendations to the AGF -->
      <div>
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Recommendations to the Accountant-General</label>
          <span class="text-red-500 text-xs">* Required</span>
        </div>
        <textarea
          v-model="form.recommendations"
          rows="3"
          placeholder="e.g. Formal administrative warning, immediate redeployment, disciplinary surcharge, or exoneration..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500/20 resize-none"
        ></textarea>
      </div>

      <!-- Action Taken & Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Panel Status</label>
          <select v-model="form.status" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500/20">
            <option value="in_progress">Investigation In Progress</option>
            <option value="report_submitted">Report Submitted to AGF</option>
            <option value="resolved">Case Resolved / Sanctions Implemented</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Executive Action / Sanction Taken</label>
          <input
            type="text"
            v-model="form.action_taken"
            placeholder="e.g. Letter of Warning issued; Transferred to Zonal Directorate"
            class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500/20"
          />
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
          :disabled="form.processing || !form.findings || !form.recommendations"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:check" width="14" />
          <span>Save Findings & Recommendations</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";

const props = defineProps(["modelValue", "panel"]);
const emit = defineEmits(["update:modelValue"]);
const toast = useToast();

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const form = useForm({
  findings: "",
  recommendations: "",
  action_taken: "",
  status: "report_submitted",
});

watch(
  () => props.panel,
  (newVal) => {
    if (newVal) {
      form.findings = newVal.findings || "";
      form.recommendations = newVal.recommendations || "";
      form.action_taken = newVal.action_taken || "";
      form.status = newVal.status || "report_submitted";
    }
  },
  { immediate: true }
);

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if (!props.panel?.id) return;

  form.post(`/dashboard/servicom/panel/${props.panel.id}/findings`, {
    onSuccess: () => {
      emit("update:modelValue", false);
      toast.add({
        severity: "success",
        summary: "Findings Recorded",
        detail: "The investigation report and executive recommendations have been saved.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Save Error",
        detail: Object.values(err)[0] || "Could not save findings.",
        life: 4000,
      });
    },
  });
};
</script>
