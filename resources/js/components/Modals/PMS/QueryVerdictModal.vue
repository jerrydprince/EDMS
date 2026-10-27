<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-2xl max-w-2xl' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-4' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '40rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '80vw', '575px': '95vw' }">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-xl bg-purple-50 text-purple-700 shadow-2xs">
            <Icon icon="lucide:gavel" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Adjudicate Official Query</h1>
            <p class="text-xs text-gray-500 mt-0.5">Review subordinate's representation and record formal administrative verdict</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-2xs">
      <!-- Query & Subordinate Response Review Box -->
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
        <div class="flex items-center justify-between text-xs">
          <span class="font-mono font-bold text-gray-700">{{ query?.query_ref }}</span>
          <span class="text-gray-500">Target Officer: <strong>{{ query?.recipient?.first_name }} {{ query?.recipient?.last_name }}</strong></span>
        </div>

        <div>
          <h4 class="font-bold text-sm text-gray-900">{{ query?.subject }}</h4>
        </div>

        <!-- Subordinate Representation -->
        <div class="pt-2 border-t border-gray-200 text-xs bg-white p-3.5 rounded-xl border border-gray-200">
          <div class="flex items-center justify-between mb-1.5">
            <span class="font-bold text-blue-900">Subordinate's Written Representation:</span>
            <span v-if="query?.responded_at" class="text-[11px] text-gray-400">
              Submitted {{ formatDate(query?.responded_at) }}
            </span>
          </div>
          <p class="whitespace-pre-line text-gray-700 leading-relaxed">{{ query?.response_text || 'No written defense submitted.' }}</p>

          <div v-if="query?.response_evidence_path" class="mt-2.5 pt-2 border-t border-gray-100">
            <a :href="query.response_evidence_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-700 hover:underline font-semibold">
              <Icon icon="lucide:paperclip" width="13" />
              <span>Download Attached Exculpatory Evidence</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Verdict Decision Buttons -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Administrative Verdict</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mt-1.5">
          <label
            :class="form.verdict === 'accepted' ? 'border-emerald-500 bg-emerald-50 text-emerald-950 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer text-center text-xs transition-all"
          >
            <input type="radio" v-model="form.verdict" value="accepted" class="sr-only" />
            <Icon icon="lucide:check-circle" width="18" class="text-emerald-600 mb-1" />
            <span>Explanation Accepted</span>
            <span class="text-[10px] text-emerald-700 font-normal">Exonerated</span>
          </label>

          <label
            :class="form.verdict === 'cautioned' ? 'border-amber-500 bg-amber-50 text-amber-950 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer text-center text-xs transition-all"
          >
            <input type="radio" v-model="form.verdict" value="cautioned" class="sr-only" />
            <Icon icon="lucide:alert-triangle" width="18" class="text-amber-600 mb-1" />
            <span>Letter of Advice</span>
            <span class="text-[10px] text-amber-700 font-normal">Administrative Caution</span>
          </label>

          <label
            :class="form.verdict === 'escalated_to_dc' ? 'border-red-500 bg-red-50 text-red-950 font-bold' : 'border-gray-200 bg-slate-50 text-gray-700'"
            class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer text-center text-xs transition-all"
          >
            <input type="radio" v-model="form.verdict" value="escalated_to_dc" class="sr-only" />
            <Icon icon="lucide:shield-alert" width="18" class="text-red-600 mb-1" />
            <span>Escalate to DC</span>
            <span class="text-[10px] text-red-700 font-normal">Disciplinary Committee</span>
          </label>
        </div>
      </div>

      <!-- Verdict Remarks -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Superior Adjudication Note / Directive</label>
        <textarea
          v-model="form.verdict_comment"
          rows="3"
          placeholder="State reason for decision, administrative counsel, or referral notes to the Disciplinary Committee..."
          class="w-full border border-gray-300 rounded-xl p-3 mt-1.5 text-sm outline-none focus:ring-2 focus:ring-purple-500/20 resize-none"
        ></textarea>
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
          :disabled="form.processing || !form.verdict || !form.verdict_comment"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:check-check" width="14" />
          <span>Record Formal Verdict</span>
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
  verdict: "accepted",
  verdict_comment: "",
});

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return moment(dateStr).format("DD MMM YYYY, h:mm A");
};

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if (!props.query?.id) return;

  form.post(`/dashboard/queries/${props.query.id}/verdict`, {
    onSuccess: () => {
      emit("update:modelValue", false);
      form.reset();
      toast.add({
        severity: "success",
        summary: "Verdict Recorded",
        detail: "The query verdict has been archived into the officer's administrative file.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: Object.values(err)[0] || "Could not record verdict.",
        life: 4000,
      });
    },
  });
};
</script>
