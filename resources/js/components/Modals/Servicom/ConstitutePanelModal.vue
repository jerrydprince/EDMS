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
          <div class="p-3 border rounded-xl bg-purple-50 text-purple-700 shadow-2xs">
            <Icon icon="lucide:users-round" width="28" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Constitute SERVICOM Investigation Panel</h1>
            <p class="text-xs text-gray-500 mt-0.5">Appoint Committee Members to investigate report {{ report?.tracking_code }}</p>
          </div>
        </div>
      </div>
    </template>

    <section class="bg-white mx-2 rounded-xl p-5 space-y-4 shadow-2xs">
      <!-- Report Context Banner -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-gray-700 space-y-1">
        <div class="flex items-center justify-between">
          <span class="font-mono font-bold text-purple-700">{{ report?.tracking_code }}</span>
          <span class="capitalize font-semibold text-gray-600">Category: {{ formatCategory(report?.category) }}</span>
        </div>
        <p class="font-semibold text-gray-900">
          Subject: <span class="text-red-700">{{ report?.accused?.first_name }} {{ report?.accused?.last_name }}</span>
          ({{ report?.accused?.department?.title || 'OAGF' }})
        </p>
        <p class="text-gray-500 line-clamp-2 italic">"{{ report?.complaint_details }}"</p>
      </div>

      <!-- Panel Title -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Panel Title / Terms of Reference</label>
        <input
          type="text"
          v-model="form.panel_title"
          class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-purple-500/20"
        />
      </div>

      <!-- Chairperson and Secretary -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Panel Chairperson</label>
          <select v-model="form.chairperson_id" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-purple-500/20">
            <option value="">Select Chairperson</option>
            <option v-for="u in staffUsers" :key="'chair-' + u.id" :value="u.id">
              {{ u.first_name }} {{ u.last_name }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Panel Secretary</label>
          <select v-model="form.secretary_id" class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-purple-500/20">
            <option value="">Select Secretary</option>
            <option v-for="u in staffUsers" :key="'sec-' + u.id" :value="u.id">
              {{ u.first_name }} {{ u.last_name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Scheduled Hearing Date -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Hearing / Sitting Date</label>
        <input
          type="date"
          v-model="form.hearing_date"
          class="w-full p-2.5 border border-gray-300 rounded-xl mt-1.5 text-sm outline-none focus:ring-2 focus:ring-purple-500/20"
        />
      </div>

      <!-- Committee Members Selector -->
      <div>
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Appointed Committee Members</label>
        <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-xl p-2 mt-1.5 space-y-1 bg-slate-50">
          <label
            v-for="u in staffUsers"
            :key="'mem-' + u.id"
            class="flex items-center gap-2 p-2 rounded-lg hover:bg-white text-xs cursor-pointer transition-colors"
          >
            <input
              type="checkbox"
              :value="u.id"
              v-model="form.member_ids"
              class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
            />
            <span class="font-medium text-gray-800">{{ u.first_name }} {{ u.last_name }}</span>
            <span class="text-gray-400 text-[11px]">({{ u.email }})</span>
          </label>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Selected: {{ form.member_ids.length }} committee member(s)</p>
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
          :disabled="form.processing || !form.chairperson_id || !form.secretary_id"
          class="inline-flex items-center gap-2 px-6 py-2 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 disabled:opacity-50 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:user-plus" width="14" />
          <span>Constitute Investigation Panel</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";

const props = defineProps(["modelValue", "report", "staffUsers"]);
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
  panel_title: "",
  chairperson_id: "",
  secretary_id: "",
  hearing_date: "",
  member_ids: [],
});

watch(
  () => props.report,
  (newVal) => {
    if (newVal) {
      form.panel_title = `Investigation Panel on Alleged ${formatCategory(newVal.category)} (${newVal.tracking_code})`;
    }
  },
  { immediate: true }
);

const formatCategory = (cat) => {
  if (!cat) return "Misconduct";
  return cat.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if (!props.report?.id) return;

  form.post(`/dashboard/servicom/panel/${props.report.id}`, {
    onSuccess: () => {
      emit("update:modelValue", false);
      toast.add({
        severity: "success",
        summary: "Panel Constituted",
        detail: "The SERVICOM investigation panel has been officially established.",
        life: 4000,
      });
    },
    onError: (err) => {
      toast.add({
        severity: "error",
        summary: "Setup Error",
        detail: Object.values(err)[0] || "Could not constitute panel.",
        life: 4000,
      });
    },
  });
};
</script>
