<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'border-0 rounded-2xl shadow-2xl overflow-hidden' },
      content: { class: 'px-0 bg-white pb-6' },
      header: { class: 'p-0 border-b-0' }
    }"
    :style="{ width: '34rem', maxWidth: '95vw' }"
  >
    <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
          <Icon icon="carbon:trash-can" width="22" />
        </div>
        <div>
          <h2 class="text-lg font-bold">Empty Recycle Bin</h2>
          <p class="text-xs text-slate-400">Irreversible statutory data destruction</p>
        </div>
      </div>
      <button @click="closeModal" class="text-slate-400 hover:text-white p-1 rounded">
        <Icon icon="mdi:close" width="20" />
      </button>
    </div>

    <div class="p-6 space-y-4">
      <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
        <Icon icon="carbon:warning-filled" class="text-rose-600 mt-0.5 shrink-0" width="20" />
        <div class="text-xs leading-relaxed">
          <strong class="font-bold">Permanent Deletion Warning:</strong>
          This operation will permanently purge soft-deleted records and unlinked physical storage files from the server. This action <strong>cannot be undone</strong>.
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Purge Scope</label>
        <div class="grid grid-cols-2 gap-3 text-xs">
          <label
            class="p-3 border rounded-xl cursor-pointer flex flex-col gap-1 transition-all"
            :class="scope === 'current' ? 'border-rose-500 bg-rose-50/40 text-rose-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
          >
            <div class="flex items-center gap-2">
              <input type="radio" v-model="scope" value="current" class="text-rose-600 focus:ring-rose-500" />
              <span>Current Category</span>
            </div>
            <span class="text-[11px] font-normal text-slate-500 pl-5">Only purge items in '{{ currentType }}'</span>
          </label>

          <label
            class="p-3 border rounded-xl cursor-pointer flex flex-col gap-1 transition-all"
            :class="scope === 'all' ? 'border-rose-500 bg-rose-50/40 text-rose-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
          >
            <div class="flex items-center gap-2">
              <input type="radio" v-model="scope" value="all" class="text-rose-600 focus:ring-rose-500" />
              <span>Entire Recycle Bin</span>
            </div>
            <span class="text-[11px] font-normal text-slate-500 pl-5">Purge all categories across EDMS</span>
          </label>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3 w-full">
        <button
          type="button"
          @click="closeModal"
          class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="confirmEmpty"
          class="px-5 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"
        >
          <Icon icon="carbon:trash-can" width="16" />
          Empty Bin Permanently
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  currentType: {
    type: String,
    default: 'Deleted Files'
  }
});

const emit = defineEmits(["update:modelValue", "emptybin"]);
const scope = ref("current");

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  }
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const confirmEmpty = () => {
  emit("update:modelValue", false);
  emit("emptybin", scope.value === 'current' ? props.currentType : 'all');
};
</script>
