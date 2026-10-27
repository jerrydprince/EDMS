<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-950 via-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between border-b border-blue-900/50">
        <div class="flex items-center gap-3">
          <div class="p-2.5 rounded-xl bg-blue-500/20 text-blue-300 border border-blue-400/30">
            <Icon icon="lucide:stamp" width="22" />
          </div>
          <div>
            <h3 class="font-bold text-base tracking-tight">Official Digital Stamp & Seal Engine</h3>
            <p class="text-xs text-slate-300 mt-0.5">Automated Civil Service Circular SVG Generator & Authentic PNG Stamp Repository</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
          <Icon icon="lucide:x" width="20" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 overflow-y-auto space-y-6 flex-1 text-slate-800">
        <!-- Stamp Type Selector -->
        <div class="flex items-center gap-3 p-1.5 bg-slate-100 rounded-xl max-w-md">
          <button
            type="button"
            @click="activeMode = 'svg'"
            :class="['flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all cursor-pointer flex items-center justify-center gap-2', activeMode === 'svg' ? 'bg-white text-blue-950 shadow-sm' : 'text-slate-600 hover:text-slate-900']"
          >
            <Icon icon="lucide:sparkles" width="16" class="text-blue-600" />
            <span>Auto SVG Generator (Fallback)</span>
          </button>
          <button
            type="button"
            @click="activeMode = 'upload'"
            :class="['flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all cursor-pointer flex items-center justify-center gap-2', activeMode === 'upload' ? 'bg-white text-blue-950 shadow-sm' : 'text-slate-600 hover:text-slate-900']"
          >
            <Icon icon="lucide:upload-cloud" width="16" class="text-indigo-600" />
            <span>Upload Transparent PNG/SVG</span>
          </button>
        </div>

        <!-- Mode 1: Auto-generated SVG Preview & Controls -->
        <div v-if="activeMode === 'svg'" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <!-- Interactive Preview Card -->
          <div class="p-6 bg-gradient-to-b from-slate-50 to-slate-100 rounded-2xl border border-slate-200 flex flex-col items-center justify-center text-center shadow-inner relative min-h-[260px]">
            <span class="absolute top-3 left-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
              <Icon icon="lucide:shield-check" width="12" class="text-emerald-500" />
              Live Civil Service Seal Vector
            </span>

            <div v-if="loadingPreview" class="py-12">
              <Icon icon="lucide:loader" class="animate-spin text-blue-600 mx-auto" width="32" />
              <p class="text-xs text-slate-500 mt-2">Rendering concentric geometry...</p>
            </div>
            <div
              v-else
              v-html="previewSvg"
              class="w-48 h-48 drop-shadow-md hover:scale-105 transition-transform cursor-pointer"
              title="Click to copy SVG"
            ></div>

            <p class="text-[11px] text-slate-500 mt-4 max-w-xs font-medium">
              Concentric rings • Coat of Arms core • Dynamic civil service date • Designation arc
            </p>
          </div>

          <!-- Controls -->
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Official Designation Title *</label>
              <input
                v-model="form.designation_title"
                @input="debouncedFetchPreview"
                type="text"
                placeholder="e.g. DIRECTOR ADMINISTRATION"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-600 font-bold tracking-wide outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Ink Color Palette</label>
              <div class="flex items-center gap-2">
                <button
                  v-for="color in inkColors"
                  :key="color.hex"
                  type="button"
                  @click="selectInkColor(color.hex)"
                  :class="['px-3 py-1.5 rounded-lg text-xs font-semibold border flex items-center gap-2 cursor-pointer transition-all', form.ink_color === color.hex ? 'border-slate-800 ring-2 ring-slate-800/20 bg-slate-50' : 'border-slate-200 hover:bg-slate-50']"
                >
                  <span class="w-3.5 h-3.5 rounded-full" :style="{ backgroundColor: color.hex }"></span>
                  <span class="text-slate-700">{{ color.label }}</span>
                </button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Dynamic Date Window</label>
              <input
                v-model="form.date"
                @change="debouncedFetchPreview"
                type="date"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-600 outline-none"
              />
              <span class="text-[10px] text-slate-500 mt-1 block">Defaults to current date during official printing.</span>
            </div>

            <div class="pt-2">
              <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                <input v-model="form.is_default" type="checkbox" class="rounded text-blue-600" />
                <span>Set as default digital stamp for this designation</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Mode 2: Upload Transparent PNG/SVG -->
        <div v-else class="space-y-4">
          <div class="p-6 border-2 border-dashed border-slate-300 rounded-2xl text-center bg-slate-50 hover:bg-slate-100/50 transition-colors">
            <input
              type="file"
              ref="fileInput"
              @change="handleFileUpload"
              accept="image/png,image/svg+xml"
              class="hidden"
              id="stamp_upload"
            />
            <label for="stamp_upload" class="cursor-pointer flex flex-col items-center">
              <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl mb-2">
                <Icon icon="lucide:upload-cloud" width="32" />
              </div>
              <span class="text-xs font-bold text-slate-800">Click to upload physical digital stamp</span>
              <span class="text-[11px] text-slate-500 mt-0.5">Transparent PNG or SVG only (Max 2MB). Ideal dimensions: 400x400px.</span>
            </label>

            <div v-if="selectedFileName" class="mt-4 p-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-semibold inline-flex items-center gap-2">
              <Icon icon="lucide:check-circle" width="16" />
              <span>Selected: {{ selectedFileName }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Designation Title *</label>
              <input
                v-model="form.designation_title"
                type="text"
                placeholder="e.g. DIRECTOR ADMINISTRATION"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-indigo-600 outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Dominant Ink Color</label>
              <select v-model="form.ink_color" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-indigo-600 outline-none">
                <option value="#1e3a8a">Treasury Blue (#1e3a8a)</option>
                <option value="#991b1b">Burgundy Red (#991b1b)</option>
                <option value="#065f46">Forest Green (#065f46)</option>
                <option value="#111827">Midnight Black (#111827)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Saved Director Stamps Ledger -->
        <div class="pt-4 border-t border-slate-200">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
            <Icon icon="lucide:folder-check" width="14" class="text-blue-600" />
            <span>Configured Directorate Stamps</span>
          </h4>

          <div v-if="savedStamps.length === 0" class="text-xs text-slate-500 py-3 text-center">
            No stamps configured yet.
          </div>
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div
              v-for="stamp in savedStamps"
              :key="stamp.id"
              class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between"
            >
              <div>
                <div class="font-bold text-slate-900">{{ stamp.designation_title }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: stamp.ink_color }"></span>
                  <span>{{ stamp.stamp_type === 'svg_generated' ? 'Auto SVG' : 'Uploaded PNG' }}</span>
                </div>
              </div>
              <span
                v-if="stamp.is_default"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800"
              >
                Default
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
        <span class="text-[11px] text-slate-500">
          Public Service Rules & Department 14 SOP Compliant Seal
        </span>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition-all cursor-pointer"
          >
            Close
          </button>
          <button
            type="button"
            @click="saveStamp"
            :disabled="saving"
            class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-semibold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
            <Icon v-else icon="lucide:save" width="14" />
            <span>Save Stamp Configuration</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const props = defineProps({
  show: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'saved']);

const activeMode = ref('svg');
const loadingPreview = ref(false);
const saving = ref(false);
const previewSvg = ref('');
const savedStamps = ref([]);
const selectedFile = ref(null);
const selectedFileName = ref('');

const inkColors = [
  { label: 'Treasury Blue', hex: '#1e3a8a' },
  { label: 'Burgundy Red', hex: '#991b1b' },
  { label: 'Forest Green', hex: '#065f46' },
  { label: 'Midnight Black', hex: '#111827' },
];

const form = ref({
  designation_title: 'DIRECTOR ADMINISTRATION',
  ink_color: '#1e3a8a',
  date: new Date().toISOString().split('T')[0],
  is_default: true,
});

const selectInkColor = (hex) => {
  form.value.ink_color = hex;
  fetchSvgPreview();
};

let debounceTimer = null;
const debouncedFetchPreview = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchSvgPreview();
  }, 300);
};

const fetchSvgPreview = async () => {
  loadingPreview.value = true;
  try {
    const res = await axios.get('/dashboard/administration/stamps/preview', {
      params: {
        designation: form.value.designation_title,
        ink_color: form.value.ink_color,
        date: form.value.date,
      }
    });
    previewSvg.value = res.data.data?.svg || '';
  } catch (err) {
    console.error('Failed to preview SVG stamp:', err);
  } finally {
    loadingPreview.value = false;
  }
};

const fetchSavedStamps = async () => {
  try {
    const res = await axios.get('/dashboard/administration/stamps');
    savedStamps.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load stamps:', err);
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    selectedFileName.value = file.name;
  }
};

const saveStamp = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('designation_title', form.value.designation_title);
    formData.append('stamp_type', activeMode.value === 'svg' ? 'svg_generated' : 'uploaded_png');
    formData.append('ink_color', form.value.ink_color);
    formData.append('is_default', form.value.is_default ? '1' : '0');

    if (activeMode.value === 'upload' && selectedFile.value) {
      formData.append('custom_stamp_file', selectedFile.value);
    }

    const res = await axios.post('/dashboard/administration/stamps', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    alert(res.data.message || 'Digital stamp saved successfully.');
    fetchSavedStamps();
    emit('saved');
    emit('close');
  } catch (err) {
    alert(err.response?.data?.message || 'Error saving digital stamp.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSvgPreview();
  fetchSavedStamps();
});
</script>
