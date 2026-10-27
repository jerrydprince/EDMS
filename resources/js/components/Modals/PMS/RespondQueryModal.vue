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
          <div class="p-3 border rounded-xl bg-blue-50 text-blue-600 shadow-2xs">
            <Icon icon="lucide:file-edit" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Respond to Official Query</h1>
            <p class="text-xs text-gray-500 mt-0.5">Formal Written Representation under Public Service Rules</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-2xs">
      <!-- Query Summary & Deadline Timer Banner -->
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
        <div class="flex items-center justify-between text-xs">
          <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
            {{ query?.query_ref }}
          </span>
          <span :class="isOverdue ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-900'" class="px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1">
            <Icon icon="lucide:clock" width="13" />
            <span>{{ timeRemainingText }}</span>
          </span>
        </div>

        <div>
          <h3 class="font-bold text-sm text-gray-900">{{ query?.subject }}</h3>
          <p class="text-xs text-gray-500 mt-0.5">Category: {{ query?.incident_category }} &bull; Issued By: {{ query?.issuer?.first_name }} {{ query?.issuer?.last_name }}</p>
        </div>

        <div class="pt-2 border-t border-gray-200 text-xs text-gray-700 leading-relaxed bg-white p-3 rounded-lg border border-gray-100">
          <p class="font-semibold text-gray-800 mb-1">Statement of Facts Alleged:</p>
          <p class="whitespace-pre-line">{{ query?.facts_description }}</p>
        </div>

        <div v-if="query?.evidence_path" class="pt-1">
          <a :href="query.evidence_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-700 hover:underline font-semibold">
            <Icon icon="lucide:paperclip" width="13" />
            <span>View Attached Supporting Proof</span>
          </a>
        </div>
      </div>

      <!-- Representation Textarea -->
      <div>
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Your Formal Defense / Explanation</label>
          <span class="text-red-500 text-xs">* Required</span>
        </div>
        <textarea
          v-model="form.response_text"
          rows="5"
          placeholder="State your representation clearly and respectfully: clarify the circumstances, operational impediments, medical factors, or approvals obtained..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 resize-none"
        ></textarea>
      </div>

      <!-- Exculpatory Evidence Upload -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Attach Exculpatory Evidence / Documents (Optional)</label>
        <div class="p-3 border border-dashed border-gray-300 rounded-xl mt-1.5 text-center bg-slate-50">
          <div v-if="!form.response_evidence" class="space-y-1">
            <Icon icon="lucide:paperclip" width="22" class="mx-auto text-gray-400" />
            <p class="text-xs font-semibold text-gray-700">Attach medical slips, approval memos, receipts, or email proofs</p>
            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 mt-2 text-xs font-bold text-blue-700 bg-white border border-gray-200 rounded-lg shadow-2xs hover:bg-slate-50 cursor-pointer">
              <Icon icon="lucide:upload" width="13" />
              <span>Choose File</span>
              <input type="file" @change="handleFileUpload" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
            </label>
          </div>
          <div v-else class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200 text-xs">
            <div class="flex items-center gap-2 truncate">
              <Icon icon="lucide:file-check" width="16" class="text-emerald-600" />
              <span class="font-medium text-gray-800 truncate">{{ form.response_evidence.name }}</span>
            </div>
            <button @click="form.response_evidence = null" class="text-red-500 hover:text-red-700 text-xs font-bold ml-2">Remove</button>
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
          :disabled="form.processing || !form.response_text"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:send" width="14" />
          <span>Submit Defense Response</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import moment from "moment";

const props = defineProps(["modelValue", "query"]);
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
  response_text: "",
  response_evidence: null,
});

const isOverdue = computed(() => {
  if (!props.query?.deadline_at) return false;
  return moment().isAfter(moment(props.query.deadline_at));
});

const timeRemainingText = computed(() => {
  if (!props.query?.deadline_at) return "—";
  const end = moment(props.query.deadline_at);
  const now = moment();
  if (now.isAfter(end)) {
    return `Expired (${end.fromNow()})`;
  }
  return `Due ${now.to(end)}`;
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const handleFileUpload = (event) => {
  form.response_evidence = event.target.files[0];
};

const submit = () => {
  if (!props.query?.id) return;

  form.post(`/dashboard/queries/${props.query.id}/respond`, {
    onSuccess: () => {
      emit("update:modelValue", false);
      form.reset();
      toast.add({
        severity: "success",
        summary: "Response Submitted",
        detail: "Your formal representation has been forwarded to the issuing superior.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Submission Error",
        detail: Object.values(err)[0] || "Could not submit response.",
        life: 4000,
      });
    },
  });
};
</script>
