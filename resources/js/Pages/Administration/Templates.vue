<template>
  <main class="py-3 px-2 sm:px-3 lg:px-4 w-full font-satoshi">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
          <Icon icon="lucide:file-signature" class="text-blue-600" width="28" />
          Document Templates & Taxonomy Studio
        </h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">
          Design institutional templates linked to Document & File Taxonomy with auto-generated Civil Service Reference IDs and dynamic metadata tokens.
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs md:text-sm font-semibold transition-all shadow-sm cursor-pointer"
      >
        <Icon icon="lucide:plus" width="16" />
        <span>Create Document Template</span>
      </button>
    </div>

    <!-- Templates DataTable -->
    <div class="bg-white p-4 md:p-5 rounded-2xl border border-gray-200 shadow-sm">
      <DataTable :value="templates" responsiveLayout="scroll" :paginator="true" :rows="10" class="text-xs">
        <Column field="id" header="ID" sortable class="w-14 text-center"></Column>
        
        <Column field="title" header="Template Title" sortable>
          <template #body="slotProps">
            <div class="flex flex-col">
              <span class="font-bold text-gray-900 text-sm">{{ slotProps.data.title }}</span>
              <span class="text-[11px] text-gray-400 font-mono">v{{ slotProps.data.version || 1 }}</span>
            </div>
          </template>
        </Column>

        <Column header="Document Taxonomy Type" sortable>
          <template #body="slotProps">
            <div class="flex flex-col gap-1">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 w-fit">
                <Icon icon="lucide:layers" width="13" class="text-blue-600" />
                {{ slotProps.data.document_type?.name || slotProps.data.type }}
              </span>
              <span v-if="slotProps.data.document_type?.metadata?.length" class="text-[10px] text-gray-500">
                {{ slotProps.data.document_type.metadata.length }} Metadata Fields Loaded
              </span>
            </div>
          </template>
        </Column>

        <Column header="Reference ID Scheme">
          <template #body="slotProps">
            <div class="flex flex-col gap-1">
              <span class="font-mono text-xs font-bold text-slate-800 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded w-fit">
                {{ slotProps.data.reference_prefix || 'OAGF/GEN' }}/.../####
              </span>
              <span class="text-[10px] text-gray-400 font-mono">Token: &#123;&#123; reference_id &#125;&#125;</span>
            </div>
          </template>
        </Column>

        <Column header="Organizational Scope" sortable>
          <template #body="slotProps">
            <div v-if="slotProps.data.is_universal || !slotProps.data.department_id" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
              <Icon icon="lucide:globe" width="13" class="text-emerald-600" />
              Global (All Departments)
            </div>
            <div v-else class="flex flex-col gap-0.5">
              <span class="text-xs font-bold text-indigo-900 flex items-center gap-1">
                <Icon icon="lucide:building-2" width="12" class="text-indigo-600" />
                {{ slotProps.data.department?.title || 'Department' }}
              </span>
              <span v-if="slotProps.data.division" class="text-[11px] text-indigo-700 pl-3">
                ↳ {{ slotProps.data.division?.title }}
              </span>
              <span v-if="slotProps.data.unit" class="text-[10px] text-indigo-600 pl-6">
                ↳ {{ slotProps.data.unit?.title }}
              </span>
              <span v-if="slotProps.data.section" class="text-[10px] text-indigo-500 pl-8">
                ↳ {{ slotProps.data.section?.title }}
              </span>
            </div>
          </template>
        </Column>

        <Column field="is_active" header="Status" sortable class="w-24 text-center">
          <template #body="slotProps">
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-bold"
              :class="slotProps.data.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
              {{ slotProps.data.is_active ? 'Active' : 'Inactive' }}
            </span>
          </template>
        </Column>

        <Column header="Actions" class="w-28 text-center">
          <template #body="slotProps">
            <div class="flex items-center justify-center gap-2">
              <button
                @click="editTemplate(slotProps.data)"
                class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                title="Edit Template"
              >
                <Icon icon="lucide:pencil" width="16" />
              </button>
              <button
                @click="confirmDelete(slotProps.data)"
                class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                title="Delete Template"
              >
                <Icon icon="lucide:trash-2" width="16" />
              </button>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Create/Edit Modal with Taxonomy & Reference Number Studio -->
    <Dialog
      v-model:visible="templateDialog"
      :style="{ width: '62rem', maxWidth: '96vw' }"
      :header="form.id ? 'Edit Document Template & Taxonomy' : 'Create New Document Template'"
      :modal="true"
      class="p-fluid"
      :breakpoints="{ '960px': '85vw', '640px': '98vw' }"
    >
      <form @submit.prevent="saveTemplate" class="p-2 sm:p-4 space-y-5 text-xs">
        
        <!-- File Import Dropzone (.docx, .doc, .pdf) -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50/60 via-indigo-50/40 to-slate-50 border-2 border-dashed border-blue-200 hover:border-blue-400 transition text-center relative">
          <input
            ref="fileInput"
            type="file"
            accept=".docx,.doc,.pdf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            @change="handleFileUpload"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            :disabled="isConvertingFile"
          />
          <div v-if="!isConvertingFile" class="flex flex-col items-center pointer-events-none">
            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center mb-1.5 shadow-2xs">
              <Icon icon="lucide:file-up" width="22" />
            </div>
            <p class="text-xs font-bold text-slate-800">
              Upload Existing Word (.docx, .doc) or PDF (.pdf) Document
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Drag and drop file here or click to browse. The parser will extract text, format, and detect placeholder tokens.
            </p>
            <div class="flex items-center gap-2 mt-2">
              <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-mono font-bold">.DOCX</span>
              <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-mono font-bold">.DOC</span>
              <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-[10px] font-mono font-bold">.PDF</span>
            </div>
          </div>
          <div v-else class="flex flex-col items-center py-2">
            <Icon icon="lucide:loader-2" class="animate-spin text-blue-600 mb-2" width="26" />
            <p class="text-xs font-bold text-slate-800">Parsing and converting document into editable template...</p>
            <p class="text-[11px] text-slate-500">Extracting structure, paragraphs, and placeholder variables</p>
          </div>
        </div>

        <!-- Conversion Error Alert -->
        <div v-if="convertError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 flex items-start gap-2.5">
          <Icon icon="lucide:alert-circle" width="16" class="text-red-600 shrink-0 mt-0.5" />
          <div class="flex-1">
            <span class="font-bold">Conversion Notice: </span>
            <span>{{ convertError }}</span>
          </div>
          <button type="button" @click="convertError = null" class="text-red-400 hover:text-red-700 font-bold text-sm leading-none">&times;</button>
        </div>

        <!-- Detected Placeholders Bar from Imported Document -->
        <div v-if="extractedPlaceholders.length" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs">
          <div class="flex items-center justify-between mb-1.5">
            <span class="font-bold text-amber-900 flex items-center gap-1.5">
              <Icon icon="lucide:tags" width="14" class="text-amber-700" />
              <span>Discovered Placeholders from Document ({{ extractedPlaceholders.length }})</span>
            </span>
            <span class="text-[10px] text-amber-700">Click a chip to insert token into content</span>
          </div>
          <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
            <button
              type="button"
              v-for="ph in extractedPlaceholders"
              :key="ph"
              @click="insertPlaceholder(ph)"
              class="px-2 py-0.5 bg-white border border-amber-300 rounded text-[11px] font-mono text-amber-900 hover:bg-amber-100 transition shadow-2xs cursor-pointer flex items-center gap-1"
              :title="'Insert {{ ' + ph + ' }} into template content'"
            >
              <Icon icon="lucide:plus" width="11" class="text-amber-600" />
              <span>&#123;&#123; {{ ph }} &#125;&#125;</span>
            </button>
          </div>
        </div>

        <!-- Row 1: Title & Taxonomy Document Type -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <!-- Template Title -->
          <div class="md:col-span-6 flex flex-col gap-1.5">
            <label class="font-bold text-gray-800 uppercase tracking-wider text-[11px]">
              Template Title <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.title"
              placeholder="e.g. Award Letter, Fiscal Clearance Memo, Demand Notice"
              class="border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none"
              required
            />
          </div>

          <!-- Document Type from Taxonomy -->
          <div class="md:col-span-6 flex flex-col gap-1.5">
            <label class="font-bold text-gray-800 uppercase tracking-wider text-[11px] flex items-center justify-between">
              <span class="flex items-center gap-1">
                <Icon icon="lucide:layers" width="13" class="text-blue-600" />
                Document Type (from Taxonomy) *
              </span>
              <span v-if="selectedDocType" class="text-[10px] text-blue-600 font-normal">
                {{ selectedDocType.metadata?.length || 0 }} Metadata Fields
              </span>
            </label>
            <select
              v-model="form.document_type_id"
              @change="onDocumentTypeChange"
              class="border border-gray-300 rounded-xl px-3 py-2 text-xs font-medium bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              required
            >
              <option :value="null" disabled>Select Document Type from File Taxonomy...</option>
              <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                {{ dt.name }} ({{ dt.metadata?.length || 0 }} fields)
              </option>
            </select>
          </div>
        </div>

        <!-- Dynamic Metadata Field Injection Drawer (Loaded from Selected Document Type) -->
        <div v-if="selectedDocType && selectedDocType.metadata?.length" class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-bold text-blue-950 flex items-center gap-1.5 text-xs">
              <Icon icon="lucide:tags" width="14" class="text-blue-600" />
              <span>Taxonomy Metadata Fields for "{{ selectedDocType.name }}":</span>
            </span>
            <span class="text-[10px] text-blue-700">Click any badge to insert token into template body</span>
          </div>

          <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto pt-1">
            <button
              type="button"
              v-for="meta in selectedDocType.metadata"
              :key="meta.id"
              @click="insertPlaceholder(formatTokenKey(meta.name))"
              class="px-2.5 py-1 bg-white border border-blue-300 rounded-lg text-[11px] font-mono text-blue-900 hover:bg-blue-100 transition shadow-2xs cursor-pointer flex items-center gap-1"
              :title="'Insert {{ ' + formatTokenKey(meta.name) + ' }} into template content'"
            >
              <Icon icon="lucide:plus" width="11" class="text-blue-600" />
              <span>&#123;&#123; {{ formatTokenKey(meta.name) }} &#125;&#125;</span>
              <span class="text-[9px] text-blue-500 bg-blue-50 px-1 rounded">{{ meta.is_default || 'Field' }}</span>
            </button>
          </div>
        </div>

        <!-- Cascading Institutional Scope & Reference ID Generation -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3.5">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200">
            <div>
              <h4 class="text-xs font-bold uppercase text-slate-900 tracking-wider flex items-center gap-1.5">
                <Icon icon="lucide:hash" width="14" class="text-indigo-600" />
                Organizational Hierarchy & Document Reference ID
              </h4>
              <p class="text-[11px] text-slate-500">
                The Reference ID is auto-generated based on the Department, Division, Unit, and Section hierarchy.
              </p>
            </div>

            <!-- Scope Toggle -->
            <div class="flex items-center gap-3 text-xs font-semibold">
              <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input
                  type="radio"
                  :value="true"
                  v-model="form.is_global"
                  @change="onScopeChange"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-slate-800">Global (All Departments)</span>
              </label>
              <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input
                  type="radio"
                  :value="false"
                  v-model="form.is_global"
                  @change="onScopeChange"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-slate-800">Department-Specific</span>
              </label>
            </div>
          </div>

          <!-- Cascading Selectors if Department-Specific -->
          <div v-if="!form.is_global" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            <!-- 1. Department -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">1. Department *</label>
              <select
                v-model="form.department_id"
                @change="onDepartmentSelect"
                required
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null" disabled>Select Department</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">
                  {{ d.title }}
                </option>
              </select>
            </div>

            <!-- 2. Division -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">2. Division (Optional)</label>
              <select
                v-model="form.division_id"
                @change="onDivisionSelect"
                :disabled="!availableDivisions.length"
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium disabled:bg-slate-100 disabled:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null">-- Whole Department --</option>
                <option v-for="div in availableDivisions" :key="div.id" :value="div.id">
                  {{ div.title }}
                </option>
              </select>
            </div>

            <!-- 3. Unit -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">3. Unit (Optional)</label>
              <select
                v-model="form.unit_id"
                @change="onUnitSelect"
                :disabled="!form.division_id || !availableUnits.length"
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium disabled:bg-slate-100 disabled:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null">-- Whole Division --</option>
                <option v-for="u in availableUnits" :key="u.id" :value="u.id">
                  {{ u.title }}
                </option>
              </select>
            </div>

            <!-- 4. Section -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">4. Section (Optional)</label>
              <select
                v-model="form.section_id"
                :disabled="!form.unit_id || !availableSections.length"
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium disabled:bg-slate-100 disabled:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null">-- Whole Unit --</option>
                <option v-for="s in availableSections" :key="s.id" :value="s.id">
                  {{ s.title }}
                </option>
              </select>
            </div>
          </div>

          <!-- Live Reference ID Scheme Preview Card -->
          <div class="p-3 bg-white border border-slate-300 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-2xs">
            <div class="space-y-0.5">
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Automated Reference ID Scheme</span>
              <p class="font-mono text-xs font-bold text-blue-900 flex items-center gap-1.5">
                <Icon icon="lucide:stamp" width="14" class="text-blue-600" />
                <span>{{ liveReferencePreview }}</span>
              </p>
              <p class="text-[10px] text-slate-500">
                Stamps into template via token: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-blue-700 font-bold">&#123;&#123; reference_id &#125;&#125;</code>
              </p>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
              <!-- Manual Prefix Override Input -->
              <input
                type="text"
                v-model="form.reference_prefix"
                placeholder="Prefix override (e.g. OAGF/SEC-ADM)"
                class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-medium focus:ring-1 focus:ring-blue-500 w-full md:w-60"
                title="Optional manual prefix override"
              />

              <button
                type="button"
                @click="insertPlaceholder('reference_id')"
                class="shrink-0 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors cursor-pointer"
                title="Insert {{ reference_id }} into template content"
              >
                <Icon icon="lucide:copy-plus" width="13" />
                <span>Insert Ref Tag</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Template Content (WYSIWYG Editor) -->
        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <label class="font-bold text-gray-800 uppercase tracking-wider text-[11px] flex items-center gap-1">
              <Icon icon="lucide:edit-3" width="13" class="text-blue-600" />
              Template Content & Document Formatting *
            </label>
            <div class="flex items-center gap-2 text-[11px] text-gray-500">
              <div class="hidden sm:flex items-center gap-1">
                <span>Standard Tokens:</span>
                <button
                  type="button"
                  v-for="tok in ['date', 'applicant_name', 'department', 'title']"
                  :key="tok"
                  @click="insertPlaceholder(tok)"
                  class="font-mono text-[10px] px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded cursor-pointer"
                >
                  + &#123;&#123; {{ tok }} &#125;&#125;
                </button>
              </div>
              <button
                type="button"
                @click="showHtmlSource = !showHtmlSource"
                class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:text-blue-600 hover:bg-blue-50 border border-slate-200 rounded-lg transition flex items-center gap-1 cursor-pointer"
                :title="showHtmlSource ? 'Switch to Visual Document Editor' : 'Switch to Raw HTML Code View'"
              >
                <Icon :icon="showHtmlSource ? 'lucide:eye' : 'lucide:code'" width="12" />
                <span>{{ showHtmlSource ? 'Visual Document View' : 'HTML Code' }}</span>
              </button>
            </div>
          </div>

          <div v-show="!showHtmlSource" class="rounded-2xl overflow-hidden border border-gray-300 bg-white shadow-2xs">
            <Editor v-model="form.content_html" editorStyle="height: 380px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;" />
          </div>

          <div v-show="showHtmlSource">
            <textarea
              v-model="form.content_html"
              rows="12"
              placeholder="HTML source code..."
              class="w-full border border-gray-300 rounded-xl p-3 font-mono text-xs text-gray-800 leading-relaxed focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50"
            ></textarea>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
          <button
            type="button"
            @click="hideDialog"
            class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer"
          >
            Cancel
          </button>

          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow disabled:opacity-50 cursor-pointer flex items-center gap-1.5"
          >
            <Icon icon="lucide:check" width="16" />
            <span>{{ form.processing ? 'Saving...' : 'Save Template' }}</span>
          </button>
        </div>
      </form>
    </Dialog>

    <ConfirmDialog />
  </main>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Editor from 'primevue/editor';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';
import Layout from "../../Shared/Layout.vue";
import { previewFullReferenceId } from '../../utils/referenceNumber.js';

const __default__ = {
  layout: Layout,
};

const props = defineProps({
  templates: { type: Array, default: () => [] },
  documentTypes: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  userDepartmentId: { type: Number, default: null }
});

const toast = useToast();
const confirm = useConfirm();

const templateDialog = ref(false);

const form = useForm({
  id: null,
  title: '',
  document_type_id: null,
  type: 'memo',
  content_html: '',
  is_global: false,
  department_id: null,
  division_id: null,
  unit_id: null,
  section_id: null,
  reference_prefix: '',
});

// Selected Document Type from Taxonomy
const selectedDocType = computed(() => {
  if (!form.document_type_id || !props.documentTypes) return null;
  return props.documentTypes.find(dt => dt.id === form.document_type_id) || null;
});

// Cascading Scope Computeds
const availableDivisions = computed(() => {
  if (!form.department_id || !props.departments) return [];
  const dept = props.departments.find(d => d.id === form.department_id);
  return dept?.division || dept?.divisions || [];
});

const availableUnits = computed(() => {
  if (!form.division_id) return [];
  const div = availableDivisions.value.find(d => d.id === form.division_id);
  return div?.unit || div?.units || [];
});

const availableSections = computed(() => {
  if (!form.unit_id) return [];
  const unit = availableUnits.value.find(u => u.id === form.unit_id);
  return unit?.sections || unit?.section || [];
});

// Real-Time Reference ID Preview
const liveReferencePreview = computed(() => {
  const dept = form.is_global ? null : props.departments?.find(d => d.id === form.department_id);
  const div = form.is_global ? null : availableDivisions.value?.find(d => d.id === form.division_id);
  const unit = form.is_global ? null : availableUnits.value?.find(u => u.id === form.unit_id);
  const sec = form.is_global ? null : availableSections.value?.find(s => s.id === form.section_id);

  const docTypeName = selectedDocType.value?.name || form.type || 'MEMO';

  return previewFullReferenceId(
    dept?.title,
    div?.title,
    unit?.title,
    sec?.title,
    docTypeName,
    form.reference_prefix
  );
});

const formatTokenKey = (name) => {
  if (!name) return 'field';
  return name.toLowerCase().replace(/[^a-z0-9]/g, '_');
};

const onDocumentTypeChange = () => {
  if (selectedDocType.value) {
    form.type = selectedDocType.value.name;
    // If title is empty, default to doc type name
    if (!form.title) {
      form.title = selectedDocType.value.name + ' Template';
    }
  }
};

const onScopeChange = () => {
  if (form.is_global) {
    form.department_id = null;
    form.division_id = null;
    form.unit_id = null;
    form.section_id = null;
  } else if (!form.department_id && props.departments?.length) {
    form.department_id = props.departments[0].id;
  }
};

const onDepartmentSelect = () => {
  form.division_id = null;
  form.unit_id = null;
  form.section_id = null;
};

const onDivisionSelect = () => {
  form.unit_id = null;
  form.section_id = null;
};

const onUnitSelect = () => {
  form.section_id = null;
};

const insertPlaceholder = (key) => {
  const token = `{{ ${key} }}`;
  if (!form.content_html) {
    form.content_html = `<p>${token}</p>`;
  } else {
    form.content_html += ` ${token}`;
  }
  toast.add({
    severity: 'info',
    summary: 'Token Inserted',
    detail: `Appended ${token} to template`,
    life: 2000
  });
};

// Template File Upload & Conversion States
const fileInput = ref(null);
const isConvertingFile = ref(false);
const convertError = ref(null);
const extractedPlaceholders = ref([]);
const showHtmlSource = ref(false);

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const allowedExts = ['docx', 'doc', 'pdf'];
  const ext = file.name.split('.').pop()?.toLowerCase();
  if (!allowedExts.includes(ext)) {
    toast.add({
      severity: 'warn',
      summary: 'Unsupported File Format',
      detail: 'Please upload a Microsoft Word (.docx, .doc) or PDF (.pdf) file.',
      life: 4000
    });
    return;
  }

  isConvertingFile.value = true;
  convertError.value = null;

  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await axios.post('/dashboard/templates/convert-template-file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data?.success) {
      const payload = res.data.data || res.data;
      if (!form.title || form.title.trim() === '') {
        form.title = payload.title || res.data.title || file.name.replace(/\.[^/.]+$/, "");
      }
      form.content_html = payload.content_html || payload.body_html || res.data.content_html || res.data.body_html || '';
      if (payload.type || res.data.type) {
        form.type = payload.type || res.data.type;
      }
      extractedPlaceholders.value = payload.placeholders || res.data.placeholders || [];

      const origName = res.data.original_filename || payload.original_filename || file.name;
      const fileType = (res.data.file_type || payload.file_type || ext || '').toUpperCase();

      toast.add({
        severity: 'success',
        summary: 'Converted Successfully',
        detail: `Extracted content from ${origName} (${fileType}). Found ${extractedPlaceholders.value.length} placeholders.`,
        life: 4000
      });
    } else {
      throw new Error(res.data?.message || res.data?.error || 'Failed to convert file to template.');
    }
  } catch (err) {
    const errorMsg =
      err.response?.data?.message ||
      err.response?.data?.error ||
      err.response?.data?.errors?.file?.[0] ||
      err.message ||
      'Failed to convert file to template.';
    convertError.value = errorMsg;
    toast.add({
      severity: 'error',
      summary: 'Conversion Error',
      detail: errorMsg,
      life: 6000
    });
  } finally {
    isConvertingFile.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
};

const openCreateModal = () => {
  form.reset();
  form.id = null;
  form.content_html = '<p><strong>REF:</strong> {{ reference_id }}</p><p><strong>DATE:</strong> {{ date }}</p><p><strong>SUBJECT:</strong> {{ subject }}</p><p><br></p><p>Type your official correspondence or docket details here...</p>';
  form.is_global = true;
  convertError.value = null;
  extractedPlaceholders.value = [];
  if (props.documentTypes?.length) {
    form.document_type_id = props.documentTypes[0].id;
    form.type = props.documentTypes[0].name;
  }
  templateDialog.value = true;
};

const editTemplate = (template) => {
  form.id = template.id;
  form.title = template.title;
  form.type = template.type;
  form.document_type_id = template.document_type_id || null;
  form.content_html = template.content_html;
  form.is_global = Boolean(template.is_universal || !template.department_id);
  form.department_id = template.department_id || null;
  form.division_id = template.division_id || null;
  form.unit_id = template.unit_id || null;
  form.section_id = template.section_id || null;
  form.reference_prefix = template.reference_prefix || '';
  convertError.value = null;
  extractedPlaceholders.value = [];

  // If document_type_id is null, try to match by name
  if (!form.document_type_id && props.documentTypes?.length) {
    const matched = props.documentTypes.find(dt => dt.name?.toLowerCase() === template.type?.toLowerCase());
    if (matched) {
      form.document_type_id = matched.id;
    }
  }

  templateDialog.value = true;
};

const hideDialog = () => {
  templateDialog.value = false;
  convertError.value = null;
  extractedPlaceholders.value = [];
  form.reset();
};

const saveTemplate = () => {
  if (form.id) {
    form.put(route('templates.update', form.id), {
      onSuccess: () => {
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Template Updated with Taxonomy', life: 3000 });
        hideDialog();
      },
    });
  } else {
    form.post(route('templates.store'), {
      onSuccess: () => {
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Template Created with Taxonomy', life: 3000 });
        hideDialog();
      },
    });
  }
};

const confirmDelete = (template) => {
  confirm.require({
    message: `Are you sure you want to delete template "${template.title}"?`,
    header: 'Confirm Deletion',
    icon: 'pi pi-exclamation-triangle',
    accept: () => {
      form.delete(route('templates.destroy', template.id), {
        onSuccess: () => {
          toast.add({ severity: 'success', summary: 'Successful', detail: 'Template Deleted', life: 3000 });
        }
      });
    }
  });
};
</script>

<style>
.p-editor-container {
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  overflow: hidden;
}
</style>
