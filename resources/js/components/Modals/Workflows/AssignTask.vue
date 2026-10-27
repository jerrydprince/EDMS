<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 font-sans' },
      content: { class: 'p-0 max-h-[85vh] overflow-y-auto' },
      header: { class: 'hidden' },
      footer: { class: 'hidden' },
    }"
    :style="{ width: '72rem', maxWidth: '96vw' }"
    :breakpoints="{ '1280px': '85vw', '960px': '92vw', '640px': '98vw' }"
    class="custom-dialog-redesign"
  >
    <!-- 1. EXECUTIVE MODAL HEADER -->
    <div class="px-7 py-5 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex items-center justify-between border-b border-slate-700/60 sticky top-0 z-30 shadow-sm">
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-inner"
             :class="assignmentType === 'workflow' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-300 border border-blue-400/30'">
          <Icon :icon="assignmentType === 'workflow' ? 'lucide:file-text' : 'lucide:file-plus-2'" width="22" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold tracking-tight text-white">
              {{ assignmentType === 'workflow' ? 'Official Application & Staff Request' : 'Administrative Directive / Task Delegation' }}
            </h2>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-xs"
                  :class="assignmentType === 'workflow' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-300 border border-blue-400/30'">
              {{ assignmentType === 'workflow' ? 'Civil Service Protocol' : 'Internal Memo' }}
            </span>
          </div>
          <p class="text-xs text-slate-300 mt-0.5">
            {{ assignmentType === 'workflow' ? 'Submit an official statutory request (Leave, DTA, Welfare) along the approved clearance pathway' : 'Direct operational task assignment and directive issuance to specific desk officers' }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="getFile"
          :class="{ 'opacity-50 pointer-events-none': buttonSwitch === 3 }"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-slate-200 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
          title="Quick Attach File"
        >
          <Icon icon="lucide:paperclip" width="15" />
          <span class="hidden sm:inline">Attach</span>
          <span v-if="files.length > 0" class="ml-0.5 bg-emerald-500 text-slate-950 font-bold text-[10px] w-4 h-4 rounded-full flex items-center justify-center">
            {{ files.length }}
          </span>
        </button>

        <button
          type="button"
          @click="closeModal"
          class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all cursor-pointer"
        >
          <Icon icon="mingcute:close-line" width="20" />
        </button>
      </div>

      <input type="file" @change="handleFileUpload" id="fileUpload" multiple class="hidden" />
    </div>

    <!-- 2. TOP PRIMARY NAVIGATION TABS (APPLICATION vs DIRECTIVE) -->
    <div v-if="workflowsList && workflowsList.length > 0" class="px-7 pt-4 pb-2 bg-slate-50 border-b border-gray-200/80">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl">
        <button
          type="button"
          @click="assignmentType = 'workflow'"
          class="flex items-center gap-3 p-3 rounded-2xl border text-left transition-all cursor-pointer relative"
          :class="assignmentType === 'workflow' 
            ? 'bg-white border-emerald-500 shadow-sm ring-2 ring-emerald-500/20' 
            : 'bg-white/60 border-gray-200 text-gray-500 hover:bg-white hover:border-gray-300'"
        >
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
               :class="assignmentType === 'workflow' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">
            <Icon icon="lucide:file-text" width="18" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="text-xs font-bold" :class="assignmentType === 'workflow' ? 'text-gray-900' : 'text-gray-600'">
                Official Application / Request
              </span>
              <span v-if="assignmentType === 'workflow'" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </div>
            <p class="text-[11px] text-gray-400 truncate">Leave, DTA, welfare, training & statutory requests</p>
          </div>
        </button>

        <button
          type="button"
          @click="assignmentType = 'custom'; form.workflow_version_id = ''"
          class="flex items-center gap-3 p-3 rounded-2xl border text-left transition-all cursor-pointer relative"
          :class="assignmentType === 'custom' 
            ? 'bg-white border-blue-500 shadow-sm ring-2 ring-blue-500/20' 
            : 'bg-white/60 border-gray-200 text-gray-500 hover:bg-white hover:border-gray-300'"
        >
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
               :class="assignmentType === 'custom' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400'">
            <Icon icon="lucide:check-square" width="18" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="text-xs font-bold" :class="assignmentType === 'custom' ? 'text-gray-900' : 'text-gray-600'">
                Directive / Assigned Task
              </span>
              <span v-if="assignmentType === 'custom'" class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
            </div>
            <p class="text-[11px] text-gray-400 truncate">Direct assignments issued to specific officers</p>
          </div>
        </button>
      </div>
    </div>

    <!-- 3. MAIN FORM BODY (TWO-COLUMN ARCHITECTURE) -->
    <div class="p-7 space-y-6">
      
      <!-- HERO SUBJECT / TITLE CARD -->
      <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs hover:border-gray-300 transition-colors">
        <div class="flex items-center justify-between mb-2">
          <label for="task_title" class="text-xs font-bold uppercase tracking-wider flex items-center gap-2"
                 :class="assignmentType === 'workflow' ? 'text-emerald-800' : 'text-blue-800'">
            <Icon :icon="assignmentType === 'workflow' ? 'lucide:scroll-text' : 'lucide:file-edit'" width="15" />
            <span>{{ assignmentType === 'workflow' ? 'Application Subject / Docket Title' : 'Task / Directive Title' }}</span>
            <span class="text-rose-500">*</span>
          </label>
          <span class="text-[11px] text-gray-400 font-mono">
            {{ form.title ? form.title.length : 0 }} chars
          </span>
        </div>

        <input
          type="text"
          id="task_title"
          v-model="form.title"
          class="w-full text-lg sm:text-xl font-bold text-gray-900 placeholder:text-gray-300 outline-none bg-transparent transition-all border-b border-gray-100 pb-2 focus:border-emerald-500 focus:pb-2.5"
          :placeholder="assignmentType === 'workflow' ? 'e.g., Application for 3 Days Casual Leave – J. Osepo' : 'e.g., Directive: Reconcile First Quarter Vote Book Ledger'"
        />
        <div v-if="form.errors.title" class="text-rose-500 text-xs font-medium mt-1.5 flex items-center gap-1">
          <Icon icon="lucide:alert-circle" width="13" />
          <span>{{ form.errors.title }}</span>
        </div>
      </div>

      <!-- MAIN SPLIT: LEFT CONTENT & RIGHT GOVERNANCE ROUTING -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
        
        <!-- LEFT COLUMN (7 COLS): JUSTIFICATION, QUICK MINUTING, ATTACHMENTS -->
        <div class="lg:col-span-7 space-y-6">

          <!-- CONTENT FORMAT SUB-SWITCHER -->
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Submission Content</span>
            <div class="bg-gray-100 p-1 rounded-xl flex items-center gap-1 border border-gray-200/80">
              <button
                type="button"
                @click="activeButton('')"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="buttonSwitch === 1 ? 'bg-white shadow-2xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-700'"
              >
                <Icon icon="lucide:layers" width="13" />
                <span>Memo + Attachments</span>
              </button>
              <button
                type="button"
                @click="activeButton('text_only')"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="buttonSwitch === 3 ? 'bg-white shadow-2xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-700'"
              >
                <Icon icon="lucide:file-text" width="13" />
                <span>Memo Only</span>
              </button>
              <button
                type="button"
                @click="activeButton('document_only')"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="buttonSwitch === 2 ? 'bg-white shadow-2xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-700'"
              >
                <Icon icon="lucide:paperclip" width="13" />
                <span>Documents Only</span>
              </button>
            </div>
          </div>

          <!-- SECTION: MEMO / PARTICULARS & JUSTIFICATION -->
          <div v-show="buttonSwitch !== 2" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-gray-800 font-bold text-sm">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                  <Icon :icon="assignmentType === 'workflow' ? 'lucide:feather' : 'lucide:message-square'" width="15" />
                </div>
                <h3>{{ assignmentType === 'workflow' ? 'Particulars & Statutory Justification' : 'Directive Details & Instructions' }}</h3>
              </div>
              <span class="text-[11px] text-gray-400">Civil Service standard minuting</span>
            </div>

            <!-- QUICK MINUTING PRESETS -->
            <div class="space-y-1.5 pt-1">
              <div class="flex items-center gap-1.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                <Icon icon="lucide:sparkles" class="text-amber-500" width="13" />
                <span>Quick Standard Minuting Assist (click to insert):</span>
              </div>
              <div class="flex flex-wrap gap-1.5">
                <button
                  type="button"
                  v-for="(preset, i) in (assignmentType === 'workflow' ? applicationMemos : assignmentDirectives)"
                  :key="i"
                  @click="applyDirectivePreset(preset)"
                  class="text-[11px] px-2.5 py-1 rounded-lg border transition-all cursor-pointer text-left font-medium flex items-center gap-1.5"
                  :class="form.description.includes(preset)
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-semibold shadow-2xs'
                    : 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-600 hover:border-gray-300'"
                >
                  <Icon :icon="form.description.includes(preset) ? 'lucide:check' : 'lucide:plus'" width="12" class="shrink-0" />
                  <span class="truncate max-w-[280px]">{{ preset }}</span>
                </button>
              </div>
            </div>

            <textarea
              v-model="form.description"
              rows="6"
              :placeholder="assignmentType === 'workflow' ? 'State the grounds, justification, handover particulars, and official remarks for this application...' : 'State the full scope, reference numbers, and instructions for this directive...'"
              class="w-full p-4 text-sm text-gray-800 bg-gray-50/80 border border-gray-200 rounded-xl outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 transition-all resize-y shadow-inner leading-relaxed font-sans"
            ></textarea>
            <div v-if="form.errors.description" class="text-rose-500 text-xs font-medium">{{ form.errors.description }}</div>
          </div>

          <!-- SECTION: ATTACHED DOCUMENTS & STATUTORY EVIDENCE -->
          <div v-show="buttonSwitch !== 3" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs space-y-4">
            
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-gray-800 font-bold text-sm">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                  <Icon icon="lucide:paperclip" width="15" />
                </div>
                <h3>Attached Documents & Statutory Evidence</h3>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-semibold">
                  {{ allAttachedDocs.length }} {{ allAttachedDocs.length === 1 ? 'file' : 'files' }}
                </span>
              </div>

              <!-- Extra upload trigger button if in template mode -->
              <button
                v-if="assignmentType === 'workflow' && attachedTemplate && docSourceMode === 'template'"
                type="button"
                @click="getFile"
                class="flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all cursor-pointer border border-slate-300"
              >
                <Icon icon="lucide:paperclip" width="13" />
                <span>+ Upload Extra File</span>
              </button>
              <button
                v-else
                type="button"
                @click="getFile"
                class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold transition-all cursor-pointer border border-indigo-200/60"
              >
                <Icon icon="lucide:plus" width="14" />
                <span>Upload Document</span>
              </button>
            </div>

            <!-- OPTION SWITCHER: USE TEMPLATE VS UPLOAD DOCUMENT (When template is attached) -->
            <div v-if="assignmentType === 'workflow' && attachedTemplate" class="p-1 bg-slate-100/90 rounded-xl flex items-center gap-1 border border-slate-200/90">
              <button
                type="button"
                @click="docSourceMode = 'template'"
                class="flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                :class="docSourceMode === 'template'
                  ? 'bg-white text-emerald-800 shadow-2xs border border-emerald-300 font-extrabold'
                  : 'text-slate-600 hover:text-slate-800'"
              >
                <Icon icon="lucide:stamp" class="text-emerald-600" width="15" />
                <span>Use Official Template</span>
                <span class="text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Attached</span>
              </button>
              <button
                type="button"
                @click="docSourceMode = 'upload'"
                class="flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                :class="docSourceMode === 'upload'
                  ? 'bg-white text-indigo-800 shadow-2xs border border-indigo-300 font-extrabold'
                  : 'text-slate-600 hover:text-slate-800'"
              >
                <Icon icon="lucide:upload-cloud" class="text-indigo-600" width="15" />
                <span>Upload Document</span>
              </button>
            </div>

            <!-- MODE 1: OFFICIAL TEMPLATE PRESENTATION -->
            <div v-if="assignmentType === 'workflow' && attachedTemplate && docSourceMode === 'template'" class="space-y-3">
              
              <!-- State A: Form not generated yet -->
              <div v-if="!generatedTemplateDoc" class="bg-gradient-to-br from-emerald-50/70 via-teal-50/40 to-slate-50 border border-emerald-200/90 rounded-2xl p-5 shadow-2xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-emerald-600/20 shadow-sm shrink-0">
                      <Icon icon="lucide:file-signature" width="20" />
                    </div>
                    <div>
                      <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-snug">{{ attachedTemplate.title }}</h4>
                        <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded uppercase">Statutory Form</span>
                      </div>
                      <p class="text-[11px] text-gray-500 mt-0.5">Civil Service standardized form pre-configured for this application.</p>
                    </div>
                  </div>
                </div>

                <div class="p-3 bg-white/95 rounded-xl border border-emerald-100 text-xs text-gray-600 flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 text-[11px]">
                    <Icon icon="lucide:sparkles" class="text-amber-500 shrink-0" width="14" />
                    <span>Auto-populates with your officer name, service file number, and directorate.</span>
                  </div>
                  <span class="text-[10px] text-emerald-700 font-semibold shrink-0">Ready to complete</span>
                </div>

                <div class="flex items-center gap-2 pt-1">
                  <button
                    type="button"
                    @click="openTemplateFillModal"
                    class="flex-1 py-2 px-4 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer flex items-center justify-center gap-2"
                  >
                    <Icon icon="lucide:pencil-line" width="14" />
                    <span>Fill & Attach Official Form</span>
                  </button>
                  <button
                    type="button"
                    @click="openBlankTemplatePreview"
                    class="py-2 px-3 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl border border-gray-200 transition-all cursor-pointer flex items-center gap-1.5"
                  >
                    <Icon icon="lucide:eye" width="14" />
                    <span>Preview Template</span>
                  </button>
                </div>
              </div>

              <!-- State B: Form has been generated and attached -->
              <div v-else class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-2 border-emerald-400/80 rounded-2xl p-4 shadow-2xs space-y-2">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                      <Icon icon="lucide:check-circle-2" width="19" />
                    </div>
                    <div class="min-w-0">
                      <div class="flex items-center gap-1.5">
                        <span class="text-xs font-extrabold text-emerald-950 truncate">{{ generatedTemplateDoc.original_name }}</span>
                        <span class="text-[9px] bg-emerald-200/80 text-emerald-900 font-bold px-1.5 py-0.2 rounded shrink-0">Attached Form</span>
                      </div>
                      <p class="text-[11px] text-emerald-700">Official statutory docket generated ({{ generatedTemplateDoc.size }})</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <button
                      type="button"
                      @click="openGeneratedPreview"
                      class="px-2.5 py-1.5 text-xs font-bold bg-white text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-all cursor-pointer flex items-center gap-1 shadow-2xs"
                    >
                      <Icon icon="lucide:eye" width="13" />
                      <span>Preview</span>
                    </button>
                    <button
                      type="button"
                      @click="openTemplateFillModal"
                      class="px-2.5 py-1.5 text-xs font-bold bg-white text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-200 transition-all cursor-pointer flex items-center gap-1 shadow-2xs"
                    >
                      <Icon icon="lucide:edit-3" width="13" />
                      <span>Edit</span>
                    </button>
                    <button
                      type="button"
                      @click="removeGeneratedTemplateDoc"
                      class="w-7 h-7 flex items-center justify-center text-rose-500 hover:bg-rose-50 rounded-lg transition-all cursor-pointer"
                      title="Remove Form"
                    >
                      <Icon icon="lucide:trash-2" width="14" />
                    </button>
                  </div>
                </div>
              </div>

            </div>

            <!-- MODE 2: UPLOAD DROPZONE -->
            <div
              v-if="!(assignmentType === 'workflow' && attachedTemplate && docSourceMode === 'template')"
              @click="getFile"
              class="border-2 border-dashed border-gray-200 hover:border-indigo-400 bg-slate-50/60 hover:bg-indigo-50/30 rounded-2xl p-6 transition-all cursor-pointer flex flex-col items-center justify-center text-center group"
            >
              <div class="w-11 h-11 rounded-2xl bg-white shadow-xs border border-gray-200/80 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform mb-2">
                <Icon icon="lucide:upload-cloud" width="22" />
              </div>
              <p class="text-xs font-bold text-gray-700">Click to upload or drag & drop documents</p>
              <p class="text-[11px] text-gray-400 mt-0.5">Supports PDF, DOCX, Scanned JPG/PNG up to 10MB per file</p>
            </div>

            <!-- UNIFIED ATTACHED DOCUMENTS LIST -->
            <div v-if="allAttachedDocs.length > 0" class="space-y-2 pt-1">
              <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between">
                <span>Dockets & Files in Submission Dossier ({{ allAttachedDocs.length }}):</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                  v-for="(file, index) in allAttachedDocs"
                  :key="index"
                  class="border rounded-xl p-3 flex items-center justify-between gap-3 group transition-all shadow-2xs"
                  :class="file.is_template_generated ? 'bg-emerald-50/50 border-emerald-200 hover:bg-white hover:border-emerald-300' : 'bg-gray-50 border-gray-200/80 hover:border-indigo-300 hover:bg-white'"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                      <Icon v-if="file.is_template_generated" icon="lucide:file-signature" class="text-emerald-600" width="22" />
                      <img v-else-if="isImage(file)" :src="getFileURL(file)" class="w-full h-full object-cover" />
                      <Icon v-else-if="isPDF(file)" icon="vscode-icons:file-type-pdf2" width="22" />
                      <Icon v-else icon="lucide:file-text" class="text-indigo-600" width="20" />
                    </div>
                    <div class="min-w-0">
                      <div class="flex items-center gap-1.5">
                        <p class="text-xs font-bold text-gray-800 truncate" :title="file.original_name || file.name || file.file_name">
                          {{ file.original_name || file.name || file.file_name }}
                        </p>
                      </div>
                      <div class="flex items-center gap-1.5 mt-0.5">
                        <span v-if="file.is_template_generated" class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1 py-0.2 rounded">
                          Official Form
                        </span>
                        <span v-else class="text-[9px] bg-indigo-50 text-indigo-700 font-medium px-1 py-0.2 rounded">
                          Uploaded
                        </span>
                        <span class="text-[10px] text-gray-400">{{ formatSize(file.size) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-1 shrink-0">
                    <button
                      type="button"
                      @click.stop="openPreview(file)"
                      class="w-7 h-7 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 flex items-center justify-center transition-colors cursor-pointer"
                      title="Preview Document"
                    >
                      <Icon icon="lucide:eye" width="14" />
                    </button>
                    <button
                      type="button"
                      @click.stop="removeAttachedDoc(file, index)"
                      class="w-7 h-7 rounded-lg hover:bg-rose-50 text-gray-400 hover:text-rose-600 flex items-center justify-center transition-colors cursor-pointer"
                      title="Remove File"
                    >
                      <Icon icon="lucide:trash-2" width="14" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- RIGHT COLUMN (5 COLS): GOVERNANCE, ROUTING & SECURITY SEAL -->
        <div class="lg:col-span-5 space-y-5">
          
          <div class="bg-gradient-to-b from-slate-50 to-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
            
            <div class="flex items-center justify-between border-b border-gray-200/80 pb-3">
              <div class="flex items-center gap-2">
                <Icon icon="lucide:shield-check" class="text-emerald-600" width="18" />
                <h3 class="font-bold text-gray-900 text-sm">
                  {{ assignmentType === 'workflow' ? 'Institutional Routing & Lifecycle' : 'Directive Delegation Parameters' }}
                </h3>
              </div>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase"
                    :class="assignmentType === 'workflow' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'">
                {{ assignmentType === 'workflow' ? 'Statutory' : 'Directive' }}
              </span>
            </div>

            <!-- 1. OFFICIAL APPLICATION / REQUEST SELECTION -->
            <div v-if="assignmentType === 'workflow'" class="space-y-3">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                  <Icon icon="lucide:file-text" class="text-emerald-600" width="14" />
                  <span>Application / Request Type</span>
                  <span class="text-rose-500">*</span>
                </label>

                <div class="relative">
                  <select
                    v-model="form.workflow_version_id"
                    class="w-full bg-white border border-gray-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-800 outline-none transition-all shadow-2xs appearance-none pr-9 cursor-pointer"
                  >
                    <option value="" disabled>Select request type (Leave, DTA, Welfare, Confirmation)...</option>
                    <option v-for="wf in workflowsList" :key="wf.id" :value="wf.id">
                      {{ formatApplicationOption(wf) }}
                    </option>
                  </select>
                  <Icon icon="lucide:chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="16" />
                </div>
              </div>

              <!-- APPLICATION REVIEW & CLEARANCE PATHWAY -->
              <div v-if="workflowSteps && workflowSteps.length > 0" class="bg-white rounded-xl border border-emerald-200/80 p-3.5 shadow-2xs space-y-2.5">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1">
                    <Icon icon="lucide:git-commit" width="14" class="text-emerald-600" />
                    <span>Application Clearance Pathway ({{ workflowSteps.length }} Stages)</span>
                  </span>
                  <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold border border-emerald-200/60">
                    Official Channel
                  </span>
                </div>

                <div class="relative pl-4 space-y-2.5 before:content-[''] before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-emerald-200">
                  <div
                    v-for="(step, idx) in workflowSteps"
                    :key="step.id"
                    class="relative flex items-start gap-2.5 text-left"
                  >
                    <div
                      class="w-3.5 h-3.5 rounded-full border-2 bg-white flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                      :class="idx === 0 ? 'border-emerald-600 bg-emerald-600 ring-2 ring-emerald-100' : 'border-gray-300'"
                    >
                      <div v-if="idx === 0" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                    </div>
                    <div class="min-w-0">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <p class="text-xs font-bold leading-tight" :class="idx === 0 ? 'text-emerald-900' : 'text-gray-700'">
                          {{ step.name }}
                        </p>
                        <span v-if="idx === 0" class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded">
                          You (Applicant / Initiator)
                        </span>
                      </div>
                      <p v-if="step.role_required" class="text-[10px] text-gray-400 capitalize">
                        Clearing Authority: {{ step.role_required }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- PROTOCOL ASSURANCE BADGE -->
              <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/90 rounded-xl p-3.5 flex items-start gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                  <Icon icon="lucide:shield-check" width="16" />
                </div>
                <div class="text-xs text-emerald-950 leading-relaxed">
                  <p class="font-bold">Official Civil Service Protocol</p>
                  <p class="text-[11px] text-emerald-800 mt-0.5">
                    Upon entering your digital signature PIN, this application is transmitted directly along the clearance pathway to your <strong>Departmental Director</strong> for official review & recommendation. No downward assignment needed.
                  </p>
                </div>
              </div>
            </div>

            <!-- 2. DIRECTIVE ASSIGNEES (IF CUSTOM DIRECTIVE) -->
            <div v-else class="space-y-3">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                  <Icon icon="lucide:users" class="text-blue-600" width="14" />
                  <span>Assign To Desk Officers / Staff</span>
                  <span class="text-gray-400 font-normal text-[11px]">(optional)</span>
                </label>

                <MultiSelect
                  v-model="selectedUsers"
                  :options="usersList"
                  optionLabel="display_name"
                  placeholder="Search desk officers by name, role or unit..."
                  :filter="true"
                  display="chip"
                  :pt="{
                    root: { class: 'w-full text-left bg-white border border-gray-300 rounded-xl hover:border-blue-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none flex items-center justify-between p-1' },
                    label: { class: 'py-1.5 px-2 text-xs flex-1' },
                    trigger: { class: 'w-8 flex items-center justify-center text-gray-400' },
                    panel: { class: 'bg-white shadow-2xl rounded-2xl border border-gray-200 mt-2 p-1 overflow-hidden z-50' },
                    header: { class: 'p-2 border-b border-gray-100 flex items-center justify-between gap-2' },
                    filterInput: { class: 'w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-400 outline-none' },
                    wrapper: { class: 'max-h-[220px] overflow-auto py-1' },
                    item: ({ context }) => ({ class: ['px-3 py-2 hover:bg-gray-50 transition-colors flex items-center gap-2.5 rounded-lg mx-1 cursor-pointer text-xs', context.selected ? 'bg-blue-50 text-blue-900 font-semibold' : ''] }),
                  }"
                  @change="updateUsersMultiSelect"
                >
                  <template #option="slotProps">
                    <div class="flex items-center justify-between w-full gap-2">
                      <div class="flex items-center gap-2 min-w-0">
                        <img :src="getImageForGroup(slotProps.option)" class="w-6 h-6 rounded-full object-cover shrink-0" />
                        <div class="min-w-0">
                          <p class="font-bold truncate text-gray-800">{{ slotProps.option.display_name }}</p>
                          <p v-if="slotProps.option.email" class="text-[10px] text-gray-400 truncate">{{ slotProps.option.email }}</p>
                        </div>
                      </div>
                      <span v-if="slotProps.option.is_on_leave == 1" class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded-full shrink-0">
                        On Leave
                      </span>
                    </div>
                  </template>
                </MultiSelect>
                <div v-if="form.errors.users" class="text-rose-500 text-xs">{{ form.errors.users }}</div>
              </div>

              <!-- Parallel Step Splitting Option -->
              <div
                @click="form.allow_step_splitting = !form.allow_step_splitting"
                class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-all cursor-pointer shadow-2xs"
              >
                <input
                  type="checkbox"
                  v-model="form.allow_step_splitting"
                  class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                />
                <div class="text-xs">
                  <p class="font-bold text-gray-800">Allow Parallel Desk Splitting</p>
                  <p class="text-[11px] text-gray-400">Multiple officers may treat in parallel dockets</p>
                </div>
              </div>
            </div>

            <!-- 3. DATE & PRIORITY CONTROLS -->
            <div class="space-y-3 pt-1">
              <!-- Effective / Due Date Executive Card -->
              <div class="bg-white p-4 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2.5">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <Icon icon="lucide:calendar-clock" class="text-purple-600" width="16" />
                    <span>{{ assignmentType === 'workflow' ? 'Effective Date / Handover' : 'Target Due Date' }}</span>
                    <span class="text-rose-500">*</span>
                  </label>
                  <span v-if="humanFormattedDate" class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200/70 flex items-center gap-1">
                    <Icon icon="lucide:check-circle-2" width="12" class="text-purple-600" />
                    {{ humanFormattedDate }}
                  </span>
                </div>

                <div class="relative flex items-center">
                  <div class="absolute left-3.5 text-gray-400 pointer-events-none flex items-center">
                    <Icon icon="lucide:calendar" width="16" />
                  </div>
                  <input
                    type="date"
                    v-model="form.due_date"
                    :min="todayIsoString"
                    class="w-full bg-slate-50/70 hover:bg-white focus:bg-white border border-gray-300 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 rounded-xl pl-10 pr-9 py-2.5 text-xs font-bold text-gray-800 outline-none transition-all cursor-pointer shadow-2xs"
                  />
                  <button
                    v-if="form.due_date"
                    type="button"
                    @click="form.due_date = ''"
                    class="absolute right-3 text-gray-400 hover:text-rose-500 transition-colors p-1 cursor-pointer"
                    title="Clear date"
                  >
                    <Icon icon="lucide:x" width="14" />
                  </button>
                </div>

                <!-- Quick Presets -->
                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                  <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Quick Select:</span>
                  <button
                    type="button"
                    @click="setQuickDate(0)"
                    class="px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer border"
                    :class="form.due_date === todayIsoString ? 'bg-purple-600 text-white border-purple-600 shadow-2xs' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                  >
                    Today
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate(3)"
                    class="px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer border"
                    :class="form.due_date && form.due_date !== todayIsoString ? 'bg-purple-100 text-purple-800 border-purple-300 shadow-2xs' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                  >
                    +3 Days (Casual Leave)
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate(7)"
                    class="px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer border bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100"
                  >
                    +1 Week
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate(14)"
                    class="px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer border bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100"
                  >
                    +2 Weeks
                  </button>
                </div>

                <div v-if="form.errors.due_date" class="text-rose-500 text-xs font-semibold mt-1">
                  {{ form.errors.due_date }}
                </div>
              </div>

              <!-- Priority / Urgency Level Card -->
              <div class="bg-white p-4 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <Icon icon="lucide:flag" class="text-orange-500" width="15" />
                    <span>Urgency / Priority Level</span>
                  </label>
                  <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded"
                    :class="priority === 'High' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (priority === 'Medium' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-700 border border-slate-200')">
                    {{ priority }} Priority
                  </span>
                </div>

                <div class="grid grid-cols-3 gap-1.5 bg-gray-100/90 p-1 rounded-xl border border-gray-200/80">
                  <button
                    v-for="p in Priorities"
                    :key="p"
                    type="button"
                    @click="updatePriority(p)"
                    class="py-1.5 text-xs font-bold rounded-lg transition-all text-center cursor-pointer"
                    :class="priority === p
                      ? (p === 'High' ? 'bg-white text-rose-700 shadow-2xs border border-rose-200 font-extrabold' :
                         p === 'Medium' ? 'bg-white text-amber-700 shadow-2xs border border-amber-200 font-extrabold' :
                         'bg-white text-slate-800 shadow-2xs border border-slate-200 font-extrabold')
                      : 'text-gray-500 hover:text-gray-800'"
                  >
                    {{ p }}
                  </button>
                </div>
              </div>
            </div>

            <!-- 4. OFFICIAL DIGITAL SIGNATURE PIN SEAL -->
            <div class="mt-2 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-4 border border-slate-700/80 shadow-md relative overflow-hidden">
              <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>

              <div class="flex items-center gap-2.5 mb-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 flex items-center justify-center shrink-0">
                  <Icon icon="lucide:lock" width="15" />
                </div>
                <div>
                  <h4 class="text-xs font-bold tracking-tight text-white uppercase">
                    {{ assignmentType === 'workflow' ? "Applicant's Digital Signature Seal" : "Supervisor's Digital Signature Seal" }}
                  </h4>
                  <p class="text-[10px] text-slate-300">Enter your 4-digit security PIN to seal and authorize</p>
                </div>
              </div>

              <div class="mt-3 flex flex-col items-center">
                <div class="relative w-44">
                  <input
                    id="approval_pin"
                    v-model="form.approval_pin"
                    type="password"
                    maxlength="4"
                    placeholder="••••"
                    autocomplete="off"
                    class="w-full text-center tracking-[0.5em] text-xl font-bold bg-slate-800/90 text-emerald-300 border border-slate-600 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/20 rounded-xl py-2 outline-none transition-all placeholder:text-slate-500"
                  />
                  <Icon icon="lucide:shield-check" class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-400 pointer-events-none" width="16" />
                </div>

                <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                  <Icon icon="lucide:check-circle-2" class="text-emerald-400" width="12" />
                  <span>Enforces Public Service cryptographic non-repudiation</span>
                </p>
                <div v-if="form.errors.approval_pin" class="text-rose-400 text-xs font-semibold mt-1">
                  {{ form.errors.approval_pin }}
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- 4. EXECUTIVE MODAL FOOTER -->
    <div class="px-7 py-4 bg-slate-50 border-t border-gray-200/90 flex flex-col sm:flex-row items-center justify-between gap-3 sticky bottom-0 z-30">
      <div class="flex items-center gap-2 text-xs text-gray-500">
        <span class="w-2 h-2 rounded-full" :class="form.title && form.approval_pin ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'"></span>
        <span v-if="assignmentType === 'workflow'">
          {{ form.title && form.approval_pin ? 'Application ready for transmission along official chain of command' : 'Fill in subject & enter your signature PIN to submit' }}
        </span>
        <span v-else>
          {{ form.title && form.approval_pin ? 'Directive ready for issuance' : 'Fill in title & signature PIN to issue directive' }}
        </span>
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
        <button
          type="button"
          @click="closeModal"
          :disabled="form.processing"
          class="px-5 py-2.5 text-xs font-bold text-gray-600 hover:text-gray-900 bg-white hover:bg-gray-100 rounded-xl border border-gray-200 transition-all cursor-pointer disabled:opacity-50"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="submit"
          :disabled="form.processing"
          class="px-6 py-2.5 font-bold text-xs text-white rounded-xl shadow-md transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          :class="assignmentType === 'workflow' 
            ? 'bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 shadow-emerald-700/20' 
            : 'bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 shadow-blue-700/20'"
        >
          <Icon v-if="form.processing" icon="line-md:loading-twotone-loop" width="16" />
          <Icon v-else :icon="assignmentType === 'workflow' ? 'lucide:send' : 'lucide:check-circle-2'" width="16" />
          <span>
            {{ form.processing 
                ? (assignmentType === 'workflow' ? 'Transmitting Upward...' : 'Issuing Directive...') 
                : (assignmentType === 'workflow' ? 'Submit Application Upward' : 'Issue Directive / Task') 
            }}
          </span>
        </button>
      </div>
    </div>

  </Dialog>

  <!-- Document Preview Modal -->
  <Dialog
    v-model:visible="previewVisible"
    header="Document Evidence Preview"
    modal
    :style="{ width: '52rem' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
  >
    <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-2xl min-h-[420px]">
      <img v-if="previewFile && isImage(previewFile)" :src="previewFileUrl" class="max-w-full max-h-[600px] object-contain shadow-md rounded-xl" />
      <object v-else-if="previewFile && isPDF(previewFile)" :data="previewFileUrl" type="application/pdf" width="100%" height="600px" class="rounded-xl shadow-md">
        <p class="text-sm text-gray-600">PDF preview is not available in your browser. <a :href="previewFileUrl" target="_blank" class="text-primary font-bold">Download it here</a>.</p>
      </object>
      <div v-else class="text-center text-gray-500">
        <Icon icon="ph:file-x" width="64" class="mx-auto mb-4 text-gray-300" />
        <p class="font-medium text-sm">Preview is not available for this file type.</p>
        <p class="text-xs text-gray-400 mt-1">{{ previewFile?.name }}</p>
      </div>
    </div>
  </Dialog>

  <!-- Fill & Generate Official Template Dialog -->
  <Dialog
    v-model:visible="templateDialogVisible"
    header="Fill Official Application Form"
    modal
    :style="{ width: '68rem' }"
    :breakpoints="{ '1280px': '85vw', '960px': '92vw', '640px': '96vw' }"
  >
    <div class="space-y-4 p-1">
      
      <!-- Sub-header bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
            <Icon icon="lucide:stamp" width="16" />
          </div>
          <div>
            <h4 class="text-xs font-bold text-gray-900">{{ attachedTemplate?.title }}</h4>
            <p class="text-[10px] text-gray-500">PSR Standard Format • Civil Service Statutory Docket</p>
          </div>
        </div>
        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full uppercase tracking-wider self-start sm:self-auto">
          Official Government Form
        </span>
      </div>

      <!-- Split: Form Inputs on Left, Live Document Preview on Right -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- Left: Form Input Particulars (6 cols) -->
        <div class="lg:col-span-6 space-y-3.5 max-h-[580px] overflow-y-auto pr-1">
          
          <!-- Officer Pre-filled Credentials -->
          <div class="bg-gray-50/90 p-3.5 rounded-xl border border-gray-200/90 space-y-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
              <Icon icon="lucide:user-check" class="text-emerald-600" width="13" />
              <span>Officer Particulars (Pre-filled from Profile)</span>
            </span>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="bg-white p-2 rounded-lg border border-gray-200/80">
                <span class="text-[10px] text-gray-400 block font-medium">Applicant:</span>
                <span class="font-bold text-gray-800 text-[11px] truncate block">{{ currentUserName }}</span>
              </div>
              <div class="bg-white p-2 rounded-lg border border-gray-200/80">
                <span class="text-[10px] text-gray-400 block font-medium">Service / IPPIS No:</span>
                <span class="font-bold text-gray-800 text-[11px] truncate block">{{ templateFormFields.file_number }}</span>
              </div>
              <div class="bg-white p-2 rounded-lg border border-gray-200/80">
                <span class="text-[10px] text-gray-400 block font-medium">Directorate:</span>
                <span class="font-bold text-gray-800 text-[11px] truncate block">{{ templateFormFields.department_name }}</span>
              </div>
              <div class="bg-white p-2 rounded-lg border border-gray-200/80">
                <span class="text-[10px] text-gray-400 block font-medium">Designation / GL:</span>
                <span class="font-bold text-gray-800 text-[11px] truncate block">{{ templateFormFields.designation }} (GL {{ templateFormFields.grade_level }})</span>
              </div>
            </div>
          </div>

          <!-- Leave Application Specific Inputs -->
          <div v-if="isLeaveApplication" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">
                  {{ isAnnualLeave ? 'Leave Entitlement (Calendar Days)' : 'Duration (Working Days)' }}
                </label>
                <input
                  v-model="templateFormFields.number_of_days"
                  type="text"
                  placeholder="e.g. 3"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Relieving Officer</label>
                <input
                  v-model="templateFormFields.relieving_officer"
                  type="text"
                  placeholder="e.g. Mr. Audu Yakubu (GL 10)"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Start / Departure Date</label>
                <input
                  v-model="templateFormFields.start_date"
                  type="date"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Resumption / End Date</label>
                <input
                  v-model="templateFormFields.end_date"
                  type="date"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
            </div>

            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Grounds / Reason for Leave</label>
              <textarea
                v-model="templateFormFields.grounds"
                rows="3"
                placeholder="State the official grounds and justification for this leave application..."
                class="w-full p-3 text-xs bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50 resize-y"
              ></textarea>
            </div>

            <div v-if="isAnnualLeave">
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Leave Address & Contact Phone</label>
              <input
                v-model="templateFormFields.contact_address"
                type="text"
                placeholder="e.g. Plot 14, Gwarinpa Estate, Abuja / 0803 123 4567"
                class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
              />
            </div>
          </div>

          <!-- DTA Application Specific Inputs -->
          <div v-else-if="isDtaApplication" class="space-y-3">
            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Destination / Duty Station</label>
              <input
                v-model="templateFormFields.destination"
                type="text"
                placeholder="e.g. Lagos State Treasury Liaison Office, Marina"
                class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Departure Date</label>
                <input
                  v-model="templateFormFields.departure_date"
                  type="date"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Return Date</label>
                <input
                  v-model="templateFormFields.return_date"
                  type="date"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Number of Tour Days</label>
                <input
                  v-model="templateFormFields.number_of_days"
                  type="text"
                  placeholder="e.g. 4"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Estimated DTA Amount (₦)</label>
                <input
                  v-model="templateFormFields.total_dta_amount"
                  type="text"
                  placeholder="e.g. ₦185,000.00"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
            </div>

            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Purpose of Official Tour</label>
              <textarea
                v-model="templateFormFields.purpose_of_tour"
                rows="3"
                placeholder="State the official mission, terms of reference, and expected deliverables..."
                class="w-full p-3 text-xs bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50 resize-y"
              ></textarea>
            </div>
          </div>

          <!-- Vehicle Requisition Specific Inputs -->
          <div v-else-if="isVehicleApplication" class="space-y-3">
            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Destination / Route</label>
              <input
                v-model="templateFormFields.destination"
                type="text"
                placeholder="e.g. Federal Ministry of Finance / National Assembly Complex"
                class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Departure Time</label>
                <input
                  v-model="templateFormFields.departure_time"
                  type="text"
                  placeholder="e.g. 09:30 AM"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
              <div>
                <label class="text-[11px] font-bold text-gray-700 block mb-1">Expected Return</label>
                <input
                  v-model="templateFormFields.expected_return"
                  type="text"
                  placeholder="e.g. 02:00 PM"
                  class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
                />
              </div>
            </div>

            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Number of Passengers / Officials</label>
              <input
                v-model="templateFormFields.number_of_passengers"
                type="text"
                placeholder="e.g. 3 Officers"
                class="w-full px-3 py-2 text-xs font-semibold bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50"
              />
            </div>

            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Official Purpose</label>
              <textarea
                v-model="templateFormFields.official_purpose"
                rows="3"
                placeholder="Specify the official assignment requiring vehicle dispatch..."
                class="w-full p-3 text-xs bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50 resize-y"
              ></textarea>
            </div>
          </div>

          <!-- Generic Fallback Inputs -->
          <div v-else class="space-y-3">
            <div>
              <label class="text-[11px] font-bold text-gray-700 block mb-1">Grounds & Remarks</label>
              <textarea
                v-model="templateFormFields.grounds"
                rows="4"
                placeholder="State the remarks and particulars for this document..."
                class="w-full p-3 text-xs bg-white border border-gray-300 rounded-xl outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-50 resize-y"
              ></textarea>
            </div>
          </div>

        </div>

        <!-- Right: Live Hydrated Document Preview (6 cols) -->
        <div class="lg:col-span-6 space-y-2">
          <div class="flex items-center justify-between text-xs font-bold text-gray-500 px-1">
            <span class="flex items-center gap-1.5 uppercase tracking-wider text-[10px]">
              <Icon icon="lucide:eye" class="text-emerald-600" width="13" />
              <span>Live Statutory Document Preview</span>
            </span>
            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">
              Updates in real-time
            </span>
          </div>

          <div class="bg-slate-100 p-3 rounded-2xl border border-slate-300/80 shadow-inner max-h-[580px] overflow-y-auto">
            <div class="bg-white p-5 rounded-xl shadow border border-gray-200/90 text-slate-800 text-xs font-sans leading-relaxed scale-[0.98] origin-top" v-html="hydratedLiveHtml"></div>
          </div>
        </div>

      </div>

      <!-- Action Footer -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-200 mt-2">
        <button
          type="button"
          @click="templateDialogVisible = false"
          class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-900 bg-white hover:bg-gray-100 rounded-xl border border-gray-200 transition-all cursor-pointer"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="generateTemplatePdf"
          :disabled="isGeneratingPdf"
          class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-2 disabled:opacity-50"
        >
          <Icon v-if="isGeneratingPdf" icon="line-md:loading-twotone-loop" width="16" />
          <Icon v-else icon="lucide:check-circle-2" width="16" />
          <span>{{ isGeneratingPdf ? 'Generating Official PDF...' : 'Attach Generated Form to Application' }}</span>
        </button>
      </div>

    </div>
  </Dialog>

  <!-- Blank Template Preview Modal -->
  <Dialog
    v-model:visible="templatePreviewVisible"
    header="Official Template Format"
    modal
    :style="{ width: '50rem' }"
    :breakpoints="{ '1199px': '75vw', '575px': '92vw' }"
  >
    <div class="p-3 bg-slate-100 rounded-2xl max-h-[600px] overflow-y-auto">
      <div class="bg-white p-6 rounded-xl shadow border border-gray-200 text-slate-800" v-html="hydratedLiveHtml"></div>
    </div>
  </Dialog>
</template>

<script setup>
import { computed, ref, watch, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from "@inertiajs/vue3";
import MultiSelect from 'primevue/multiselect';
import Calendar from 'primevue/calendar';

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const page = usePage();
const toast = useToast();

const files = ref([]);
const buttonSwitch = ref(1); // 1: both, 2: doc only, 3: text only
const clickedButton = ref("");
const search_type = ref("");
const usersList = ref([]);
const selectedUsers = ref([]);
const assignmentType = ref("workflow");
const workflowsList = ref([]);
const minDate = ref(new Date());

const previewVisible = ref(false);
const previewFile = ref(null);
const previewFileUrl = ref("");

const docSourceMode = ref("template"); // 'template' | 'upload'
const templateDialogVisible = ref(false);
const templatePreviewVisible = ref(false);
const isGeneratingPdf = ref(false);
const generatedTemplateDoc = ref(null);

const templateFormFields = ref({
  applicant_name: "",
  file_number: "",
  department_name: "",
  designation: "",
  grade_level: "",
  number_of_days: "3",
  annual_leave_days: "30",
  start_date: "",
  end_date: "",
  resumption_date: "",
  relieving_officer: "",
  grounds: "",
  contact_address: "",
  destination: "",
  departure_date: "",
  return_date: "",
  total_dta_amount: "",
  purpose_of_tour: "",
  departure_time: "",
  expected_return: "",
  number_of_passengers: "",
  official_purpose: ""
});

const form = useForm({
  files: [],
  extra_files: [],
  title: "",
  due_date: "",
  priority: 2,
  description: "",
  users: [],
  allow_step_splitting: false,
  approval_pin: "",
  workflow_version_id: ""
});

const applicationMemos = [
  "Submitted for Director's kind consideration and recommendation.",
  "Forwarded in line with Public Service Rules for necessary processing.",
  "Particulars attached and desk handover minuted; submitted for approval.",
  "Submitted for official review and transmission to Director Administration."
];

const assignmentDirectives = [
  "For your necessary action and report back (FNA).",
  "Please study, verify against ledger, and advise.",
  "Approved as recommended. Prepare response/draft.",
  "Urgent: Treat with priority and submit today.",
  "For information and records (FIAR)."
];

const selectedWorkflow = computed(() => {
  if (!form.workflow_version_id || !workflowsList.value) return null;
  return workflowsList.value.find(w => w.id === form.workflow_version_id);
});

const attachedTemplate = computed(() => {
  return selectedWorkflow.value?.workflow?.document_template || null;
});

const isLeaveApplication = computed(() => {
  const t = (selectedWorkflow.value?.workflow?.title || "").toLowerCase();
  return t.includes("leave");
});

const isAnnualLeave = computed(() => {
  const t = (selectedWorkflow.value?.workflow?.title || "").toLowerCase();
  return t.includes("annual");
});

const isDtaApplication = computed(() => {
  const t = (selectedWorkflow.value?.workflow?.title || "").toLowerCase();
  return t.includes("dta") || t.includes("tour") || t.includes("allowance");
});

const isVehicleApplication = computed(() => {
  const t = (selectedWorkflow.value?.workflow?.title || "").toLowerCase();
  return t.includes("vehicle") || t.includes("pool");
});

const allAttachedDocs = computed(() => {
  const list = [...files.value];
  if (generatedTemplateDoc.value) {
    list.unshift(generatedTemplateDoc.value);
  }
  return list;
});

const hydratedLiveHtml = computed(() => {
  if (!attachedTemplate.value || !attachedTemplate.value.content_html) return '';
  let html = attachedTemplate.value.content_html;
  const applicantName = currentUserName.value;
  const fileNum = currentUser.value?.ippis_no || currentUser.value?.staff_number || `FTH/EST/${currentUser.value?.id || '101'}`;
  const deptName = currentUser.value?.department?.title || 'Office of the Accountant-General of the Federation';
  const designation = (currentUser.value?.designation ? currentUser.value.designation.name : null) || 'Administrative Officer';
  const gradeLevel = currentUser.value?.grade_level || '12';
  const todayFormatted = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });

  const map = {
    applicant_name: applicantName,
    officer_name: applicantName,
    user_name: applicantName,
    file_number: fileNum,
    staff_file_number: fileNum,
    service_no: fileNum,
    department_name: deptName,
    department: deptName,
    designation: designation,
    grade_level: gradeLevel,
    date: todayFormatted,
    today: todayFormatted,
    submission_date: todayFormatted,
    ...templateFormFields.value
  };

  for (const [k, v] of Object.entries(map)) {
    const reg = new RegExp(`\\{\\{\\s*${k}\\s*\\}\\}`, 'gi');
    html = html.replace(reg, v || `<span style="color: #d97706; background-color: #fef3c7; padding: 2px 4px; border-radius: 4px;">[${k}]</span>`);
  }
  return html;
});

const workflowSteps = computed(() => {
  return selectedWorkflow.value?.steps || [];
});

const currentUser = computed(() => page.props.auth?.user || null);
const currentUserName = computed(() => {
  if (!currentUser.value) return 'Officer';
  if (currentUser.value.first_name || currentUser.value.last_name) {
    return `${currentUser.value.first_name || ''} ${currentUser.value.last_name || ''}`.trim();
  }
  return currentUser.value.name || 'Officer';
});

const cleanApplicationTitle = (title) => {
  if (!title) return 'Official Application';
  let t = title.replace(/^ADM-\d+(-\d+)?\s*/i, '').replace(/^Cross-Departmental\s*/i, '').trim();
  if (!t.toLowerCase().includes('application') && !t.toLowerCase().includes('request') && !t.toLowerCase().includes('requisition')) {
    t += ' Application';
  }
  return t;
};

const formatApplicationOption = (wf) => {
  if (!wf || !wf.workflow) return 'Official Request';
  const emoji = wf.workflow.emoji ? wf.workflow.emoji + ' ' : '';
  const cleanTitle = cleanApplicationTitle(wf.workflow.title);
  if (wf.workflow.department_id && wf.workflow.department) {
    return `${emoji}${cleanTitle} (${wf.workflow.department.title} Directorate)`;
  }
  return `${emoji}${cleanTitle}`;
};

// Automatic Subject generation for Official Applications (Requirement #1)
const autoSetApplicationSubject = (versionId) => {
  if (!versionId || assignmentType.value !== 'workflow') return;
  const wf = workflowsList.value.find(w => w.id === versionId);
  if (wf && wf.workflow) {
    const appTitle = cleanApplicationTitle(wf.workflow.title);
    form.title = `${currentUserName.value} - ${appTitle}`;
  }
};

const openTemplateFillModal = () => {
  if (!attachedTemplate.value) return;
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  const plus3 = new Date(today);
  plus3.setDate(plus3.getDate() + 3);
  const plus30 = new Date(today);
  plus30.setDate(plus30.getDate() + 30);

  templateFormFields.value = {
    ...templateFormFields.value,
    applicant_name: currentUserName.value,
    file_number: currentUser.value?.ippis_no || currentUser.value?.staff_number || `FTH/EST/${currentUser.value?.id || '101'}`,
    department_name: currentUser.value?.department?.title || 'Office of the Accountant-General of the Federation',
    designation: (currentUser.value?.designation ? currentUser.value.designation.name : null) || 'Administrative Officer',
    grade_level: currentUser.value?.grade_level || '12',
    number_of_days: templateFormFields.value.number_of_days || (isAnnualLeave.value ? '30' : '3'),
    annual_leave_days: templateFormFields.value.annual_leave_days || '30',
    start_date: templateFormFields.value.start_date || formatDate(tomorrow),
    end_date: templateFormFields.value.end_date || (isAnnualLeave.value ? formatDate(plus30) : formatDate(plus3)),
    resumption_date: templateFormFields.value.resumption_date || formatDate(plus30),
    relieving_officer: templateFormFields.value.relieving_officer || 'Mr. Audu Yakubu (GL 10)',
    grounds: templateFormFields.value.grounds || form.description || 'Submitted in line with Public Service Rules for official consideration.',
    contact_address: templateFormFields.value.contact_address || 'Plot 14, Gwarinpa Estate, FCT Abuja',
    destination: templateFormFields.value.destination || 'Lagos State Treasury Liaison Office, Marina',
    departure_date: templateFormFields.value.departure_date || formatDate(tomorrow),
    return_date: templateFormFields.value.return_date || formatDate(plus3),
    total_dta_amount: templateFormFields.value.total_dta_amount || '₦185,000.00',
    purpose_of_tour: templateFormFields.value.purpose_of_tour || form.description || 'Statutory financial inspection and audit review.',
    departure_time: templateFormFields.value.departure_time || '09:00 AM',
    expected_return: templateFormFields.value.expected_return || '03:00 PM',
    number_of_passengers: templateFormFields.value.number_of_passengers || '3 Officers',
    official_purpose: templateFormFields.value.official_purpose || form.description || 'Inter-agency fiscal consultation.'
  };

  templateDialogVisible.value = true;
};

const openBlankTemplatePreview = () => {
  openTemplateFillModal();
  templatePreviewVisible.value = true;
};

const openGeneratedPreview = () => {
  if (generatedTemplateDoc.value) {
    openPreview(generatedTemplateDoc.value);
  }
};

const removeGeneratedTemplateDoc = () => {
  generatedTemplateDoc.value = null;
  form.extra_files = [];
  toast.add({
    severity: "info",
    summary: "Form Detached",
    detail: "Official template form was removed from the application dossier.",
    life: 2500
  });
};

const removeAttachedDoc = (file, index) => {
  if (file.is_template_generated) {
    removeGeneratedTemplateDoc();
  } else {
    const idx = files.value.findIndex(f => f === file);
    if (idx !== -1) {
      removeFile(idx);
    } else {
      removeFile(index);
    }
  }
};

const generateTemplatePdf = async () => {
  if (!attachedTemplate.value) return;
  isGeneratingPdf.value = true;
  try {
    const response = await axios.post("/dashboard/document-templates/generate-file", {
      template_id: attachedTemplate.value.id,
      placeholders: templateFormFields.value,
      title: form.title
    });

    if (response.data && response.data.success) {
      const generatedFile = response.data.file;
      generatedTemplateDoc.value = generatedFile;
      form.extra_files = [generatedFile];

      if (!form.description) {
        form.description = templateFormFields.value.grounds || 
          templateFormFields.value.purpose_of_tour || 
          templateFormFields.value.official_purpose || 
          "Submitted using official civil service statutory template.";
      }

      templateDialogVisible.value = false;
      toast.add({
        severity: "success",
        summary: "Official Form Attached",
        detail: `${generatedFile.original_name} generated and attached to application`,
        life: 3500
      });
    }
  } catch (err) {
    console.error("Template PDF generation failed", err);
    toast.add({
      severity: "error",
      summary: "Generation Failed",
      detail: err.response?.data?.message || "Failed to generate official PDF document",
      life: 4000
    });
  } finally {
    isGeneratingPdf.value = false;
  }
};

watch(() => form.workflow_version_id, (newVal) => {
  autoSetApplicationSubject(newVal);
  if (assignmentType.value === 'workflow') {
    if (attachedTemplate.value) {
      docSourceMode.value = 'template';
      generatedTemplateDoc.value = null;
      form.extra_files = [];
    } else {
      docSourceMode.value = 'upload';
    }
  }
});

watch(() => assignmentType.value, (newType) => {
  if (newType === 'workflow') {
    if (workflowsList.value.length > 0 && !form.workflow_version_id) {
      form.workflow_version_id = workflowsList.value[0].id;
    }
    autoSetApplicationSubject(form.workflow_version_id);
    if (attachedTemplate.value) {
      docSourceMode.value = 'template';
    }
  } else {
    form.workflow_version_id = '';
    docSourceMode.value = 'upload';
    generatedTemplateDoc.value = null;
    form.extra_files = [];
  }
});

const openPreview = (file) => {
  previewFile.value = file;
  if (file.is_template_generated) {
    previewFileUrl.value = page.props.base + file.path;
  } else if (file.type) {
    previewFileUrl.value = URL.createObjectURL(file);
  } else {
    const extension = (file.name || file.original_name || "").split(".").pop();
    const mimeTypes = {
      jpg: "image/jpeg",
      jpeg: "image/jpeg",
      png: "image/png",
      gif: "image/gif",
      pdf: "application/pdf"
    };
    previewFileUrl.value = URL.createObjectURL(
      new Blob([file], { type: mimeTypes[extension] || "application/octet-stream" })
    );
  }
  previewVisible.value = true;
};

const isImage = (file) => {
  if (file.is_template_generated) return false;
  const t = file.type || "";
  if (t.startsWith("image/")) return true;
  const ext = (file.name || file.original_name || file.file_name || "").split(".").pop().toLowerCase();
  return ["jpg", "jpeg", "png", "gif"].includes(ext);
};

const isPDF = (file) => {
  if (file.is_template_generated) return true;
  const t = file.type || "";
  if (t === "application/pdf") return true;
  const ext = (file.name || file.original_name || file.file_name || "").split(".").pop().toLowerCase();
  return ext === "pdf";
};

const downloadAttachedFile = (file) => {
  const url = URL.createObjectURL(file);
  const a = document.createElement("a");
  a.href = url;
  a.download = file.name;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const applyDirectivePreset = (preset) => {
  if (!form.description) {
    form.description = preset;
  } else if (!form.description.includes(preset)) {
    form.description = form.description + "\n" + preset;
  }
};

const getFile = () => {
  const file = document.getElementById("fileUpload");
  if (file) file.click();
};

const formatSize = (size) => {
  if (!size) return "0 B";
  const units = ["B", "KB", "MB", "GB", "TB"];
  let i = 0;
  while (size >= 1024) {
    size /= 1024;
    i++;
  }
  return size.toFixed(1) + " " + units[i];
};

const handleFileUpload = (event) => {
  const uploaded = Array.from(event.target.files);
  files.value = [...files.value, ...uploaded];
  form.files = files.value;
};

const removeFile = (index) => {
  files.value.splice(index, 1);
  form.files.splice(index, 1);
};

function getImageForGroup(user) {
  if (search_type.value === "unit") {
    return page.props.base + "/images/unit_active.svg";
  } else if (search_type.value === "division") {
    return page.props.base + "/images/division_active.svg";
  } else if (search_type.value === "department") {
    return page.props.base + "/images/department_active.svg";
  } else {
    if (user.profile_image) {
      return page.props.base + "/" + user.profile_image;
    } else {
      return page.props.base + "/images/user.svg";
    }
  }
}

const activeButton = (event) => {
  if (event === "") buttonSwitch.value = 1;
  if (event === "document_only") buttonSwitch.value = 2;
  if (event === "text_only") buttonSwitch.value = 3;
  clickedButton.value = event;
};

const getFileURL = (file) => {
  if (file.type) {
    if (file.type.startsWith("image/")) {
      return URL.createObjectURL(file);
    }
    if (file.type === "application/pdf") {
      return page.props.base + "/images/pdf.svg";
    }
    if (file.type.includes("word")) {
      return page.props.base + "/images/word.svg";
    }
    if (file.type.includes("excel") || file.type.includes("spreadsheet")) {
      return page.props.base + "/images/excel.svg";
    }
  }
  const extension = file.name.split(".").pop().toLowerCase();
  if (["jpg", "jpeg", "png", "gif"].includes(extension)) {
    return URL.createObjectURL(file);
  }
  return page.props.base + "/images/pdf.svg";
};

const fetchWorkflows = () => {
  axios
    .get("/dashboard/available-workflows")
    .then((response) => {
      workflowsList.value = response.data || [];
      if (workflowsList.value.length > 0 && !form.workflow_version_id) {
        assignmentType.value = "workflow";
        form.workflow_version_id = workflowsList.value[0].id;
        if (!form.title) {
          autoSetApplicationSubject(form.workflow_version_id);
        }
      }
    })
    .catch((err) => {
      console.error("Failed to fetch workflows", err);
    });
};

const fetchRecipients = () => {
  axios
    .get("/dashboard/search-users", { params: { search: "" } })
    .then((response) => {
      search_type.value = response.data.type;
      usersList.value = (response.data.data || []).map((item) => {
        item.display_name = item.title || (item.first_name + " " + item.last_name);
        return item;
      });
    })
    .catch((err) => {
      console.error("Failed to fetch recipients", err);
    });
};

onMounted(() => {
  fetchRecipients();
  fetchWorkflows();
});

const updateUsersMultiSelect = (e) => {
  form.users = (e.value || []).map((user) => {
    if (user.user && user.user.length >= 0) {
      user.users = user.user;
    }
    return user;
  });
};

const formatDate = (value) => {
  if (!value) return "";
  const d = value instanceof Date ? value : new Date(value);
  if (isNaN(d.getTime())) return "";
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const formattedDate = computed(() => formatDate(form.due_date));

const todayIsoString = computed(() => {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
});

const humanFormattedDate = computed(() => {
  if (!form.due_date) return null;
  const val = form.due_date instanceof Date ? formatDate(form.due_date) : String(form.due_date);
  const parts = val.split("-");
  if (parts.length !== 3) return val;
  const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
  if (isNaN(d.getTime())) return val;
  return d.toLocaleDateString("en-GB", { weekday: "short", day: "numeric", month: "short", year: "numeric" });
});

const setQuickDate = (days) => {
  const d = new Date();
  d.setDate(d.getDate() + days);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  form.due_date = `${year}-${month}-${day}`;
};

watch(
  () => form.due_date,
  (val) => {
    if (val instanceof Date) {
      form.due_date = formatDate(val);
    }
  }
);

const priority = ref("Medium");
const Priorities = ref(["Low", "Medium", "High"]);

const updatePriority = (event) => {
  priority.value = event;
  if (priority.value === "Low") {
    form.priority = 1;
  } else if (priority.value === "Medium") {
    form.priority = 2;
  } else if (priority.value === "High") {
    form.priority = 3;
  }
};

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  }
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if (assignmentType.value === "workflow") {
    form.users = []; // Strict Civil Service: no manual downward assignment in workflow mode
  }
  if (!form.title) {
    toast.add({
      severity: "error",
      summary: "Missing Title",
      detail: assignmentType.value === "workflow" ? "Application subject cannot be empty" : "Title field cannot be empty",
      life: 3000
    });
    return false;
  }
  if (assignmentType.value === "workflow" && !form.workflow_version_id) {
    toast.add({
      severity: "error",
      summary: "Blueprint Required",
      detail: "Please choose a standard workflow process / blueprint",
      life: 3000
    });
    return false;
  }
  if (!form.due_date) {
    toast.add({
      severity: "error",
      summary: "Date Required",
      detail: assignmentType.value === "workflow" ? "Please select the effective date" : "Date field cannot be empty",
      life: 3000
    });
    return false;
  }
  if (!form.priority) {
    form.priority = 2;
  }
  if (!form.approval_pin) {
    toast.add({
      severity: "error",
      summary: "Signature PIN Required",
      detail: "4-digit digital signature PIN is required to authorize submission",
      life: 3000
    });
    return false;
  }

  if (generatedTemplateDoc.value) {
    form.extra_files = [generatedTemplateDoc.value];
  } else {
    form.extra_files = [];
  }

  form.post("/dashboard/create-task", {
    onSuccess: () => {
      form.reset();
      files.value = [];
      form.files = [];
      generatedTemplateDoc.value = null;
      form.extra_files = [];
      emit("update:modelValue", false);
      toast.add({
        severity: "success",
        summary: "Submitted Successfully",
        detail:
          assignmentType.value === "workflow"
            ? "Official application submitted upward along chain of command"
            : "Directive issued successfully",
        life: 3000
      });
    }
  });
};
</script>

<style scoped>
.custom-dialog-redesign :deep(.p-dialog-header) {
  display: none !important;
}

.custom-dialog-redesign :deep(.p-dialog-content) {
  padding: 0 !important;
  border-radius: 1.5rem !important;
}

.custom-dialog-redesign :deep(.p-dialog) {
  border-radius: 1.5rem !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}
</style>
