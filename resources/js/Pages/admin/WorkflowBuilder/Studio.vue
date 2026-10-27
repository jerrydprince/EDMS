<template>
  <main class="py-3 px-2 sm:px-3 lg:px-4 w-full">
    <!-- Breadcrumb & Navigation -->
    <div class="mb-3 flex items-center justify-between">
      <Link href="/dashboard/workflow-builder" class="text-blue-600 hover:text-blue-800 flex items-center gap-1.5 text-sm font-medium transition-colors">
        <Icon icon="lucide:arrow-left" width="16" /> Back to Workflow Registry
      </Link>
      <span class="text-xs px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full font-semibold border border-blue-200">
        {{ form.workflow_id ? 'Editing Workflow #' + form.workflow_id : 'New Workflow Blueprint' }}
      </span>
    </div>

    <!-- Header Section -->
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-gray-200">
      <div>
        <div class="flex items-center gap-3">
          <span class="text-3xl p-1.5 bg-white border border-gray-200 rounded-xl shadow-sm">{{ form.emoji || '📄' }}</span>
          <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">
              {{ form.title || 'Untitled Workflow Studio' }}
            </h1>
            <p class="text-gray-500 text-xs md:text-sm mt-0.5">
              Design sequential clearance pathways, designate responsible offices & roles, and attach official templates.
            </p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <button
          type="button"
          @click="submitStudio(false)"
          :disabled="form.processing"
          class="flex-1 md:flex-initial flex items-center justify-center gap-2 px-3.5 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-xs md:text-sm font-medium transition-all shadow-sm disabled:opacity-50">
          <Icon icon="lucide:save" width="16" class="text-gray-500" />
          {{ form.processing ? 'Saving...' : 'Save Draft' }}
        </button>

        <button
          type="button"
          @click="submitStudio(true)"
          :disabled="form.processing"
          class="flex-1 md:flex-initial flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs md:text-sm font-semibold transition-all shadow-sm hover:shadow disabled:opacity-50">
          <Icon icon="lucide:check-circle-2" width="16" />
          {{ form.processing ? 'Publishing...' : 'Save & Publish Workflow' }}
        </button>
      </div>
    </section>

    <!-- Error Banner if any validation issues -->
    <div v-if="Object.keys(form.errors).length > 0" class="mt-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-start gap-3">
      <Icon icon="lucide:alert-circle" width="20" class="shrink-0 mt-0.5 text-red-500" />
      <div>
        <p class="font-semibold">Please resolve the following issues before saving:</p>
        <ul class="list-disc pl-5 mt-1 text-xs space-y-0.5">
          <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
        </ul>
      </div>
    </div>

    <!-- Section 1: Workflow Configuration Card -->
    <section class="mt-4 bg-white border border-gray-200 rounded-2xl shadow-sm p-3.5 md:p-4">
      <div class="flex items-center gap-2 mb-3 pb-2.5 border-b border-gray-100">
        <Icon icon="lucide:sliders" width="18" class="text-blue-600" />
        <h2 class="text-sm md:text-base font-bold text-gray-800">1. Workflow Scope & Attached Template</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
        <!-- Workflow Title -->
        <div class="md:col-span-6 flex flex-col gap-1.5">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1">
            Workflow Title <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.title"
            type="text"
            placeholder="e.g. Casual Leave Application, Fiscal Clearance, Procurement Request"
            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            required />
          <span class="text-[11px] text-gray-500">This will be the title shown in official application menus.</span>
        </div>

        <!-- Emoji Picker -->
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">
            Icon / Emoji
          </label>
          <div class="flex items-center gap-2">
            <input
              v-model="form.emoji"
              type="text"
              class="w-14 text-center px-2 py-2.5 border border-gray-300 rounded-lg text-lg focus:ring-2 focus:ring-blue-500" />
            <div class="flex items-center gap-1 overflow-x-auto">
              <button
                v-for="em in ['📄', '✈️', '🏛️', '💰', '🛡️', '📋', '✍️', '💼']"
                :key="em"
                type="button"
                @click="form.emoji = em"
                class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-sm transition-colors"
                :class="{'bg-blue-50 border border-blue-200': form.emoji === em}">
                {{ em }}
              </button>
            </div>
          </div>
        </div>

        <!-- Scope Selection -->
        <div class="md:col-span-4 flex flex-col gap-1.5">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">
            Institutional Scope
          </label>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="setScope('global')"
              class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border text-xs font-semibold transition-all"
              :class="!form.department_id ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
              <Icon icon="lucide:globe" width="14" class="text-emerald-600" />
              Global (All Officers)
            </button>
            <button
              type="button"
              @click="setScope('departmental')"
              class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border text-xs font-semibold transition-all"
              :class="form.department_id ? 'bg-indigo-50 border-indigo-300 text-indigo-800 shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
              <Icon icon="lucide:shield-check" width="14" class="text-indigo-600" />
              Departmental
            </button>
          </div>
        </div>

        <!-- Department Dropdown (shown only when Departmental is active) -->
        <div v-if="form.department_id !== null" class="md:col-span-12 p-3.5 bg-indigo-50/70 border border-indigo-200 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:building-2" width="18" class="text-indigo-600 shrink-0" />
            <div>
              <p class="text-xs font-bold text-indigo-900">Select Department Authority</p>
              <p class="text-[11px] text-indigo-700">Departmental workflows can strictly be initiated only by the Director of the chosen department.</p>
            </div>
          </div>
          <div class="w-full md:w-72">
            <select
              v-model="form.department_id"
              class="w-full px-3 py-2 bg-white border border-indigo-300 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-indigo-500">
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.title }}
              </option>
            </select>
          </div>
        </div>

        <!-- Document Template Attachment (Key Requirement) -->
        <div class="md:col-span-8 flex flex-col gap-1.5">
          <label class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center justify-between">
            <span class="flex items-center gap-1.5">
              <Icon icon="lucide:file-text" width="14" class="text-blue-600" />
              Attached Document Template
            </span>
            <span v-if="selectedTemplate" class="text-[11px] text-blue-600 font-normal">
              Type: {{ selectedTemplate.type ? selectedTemplate.type.toUpperCase() : 'DOC' }}
            </span>
          </label>
          <div class="flex items-center gap-2">
            <select
              v-model="form.document_template_id"
              class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
              <option :value="null">-- No Template (Manual Document Uploads / Freeform File) --</option>
              <option v-for="tmpl in templates" :key="tmpl.id" :value="tmpl.id">
                {{ tmpl.title }} {{ tmpl.department_id ? '(Dept Specific)' : '(Global Template)' }}
              </option>
            </select>

            <button
              v-if="selectedTemplate"
              type="button"
              @click="openTemplatePreview(selectedTemplate)"
              class="shrink-0 px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium flex items-center gap-1.5 border border-gray-200 transition-colors"
              title="Preview attached template content">
              <Icon icon="lucide:eye" width="15" />
              Preview
            </button>
          </div>
          <p class="text-[11px] text-gray-500">
            Attaching a template allows officers to immediately generate or edit this standard format when initiating this workflow application.
          </p>
        </div>

        <!-- Confidentiality Switch -->
        <div class="md:col-span-4 flex flex-col justify-center">
          <label class="flex items-start gap-2.5 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors" :class="{'bg-red-50/50 border-red-200': form.is_confidential}">
            <input
              type="checkbox"
              v-model="form.is_confidential"
              class="mt-0.5 rounded text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer" />
            <div>
              <p class="text-xs font-bold text-gray-800 flex items-center gap-1">
                <Icon icon="lucide:lock" width="13" class="text-red-500" />
                Confidential Security Classification
              </p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                Restricts viewability strictly to designated clearing officers in this pathway.
              </p>
            </div>
          </label>
        </div>
      </div>
    </section>

    <!-- Section 2: Sequential Clearance Pathway Grid -->
    <section class="mt-4 bg-white border border-gray-200 rounded-2xl shadow-sm p-3.5 md:p-4">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-3 pb-2.5 border-b border-gray-100">
        <div>
          <div class="flex items-center gap-2">
            <Icon icon="lucide:git-pull-request" width="18" class="text-blue-600" />
            <h2 class="text-sm md:text-base font-bold text-gray-800">2. Clearance Pathway & Sequential Steps</h2>
          </div>
          <p class="text-xs text-gray-500 mt-0.5">
            Forward progression moves sequentially row-by-row. If a step queries or rejects, it automatically routes back to the <strong>Immediate Previous Officer</strong>.
          </p>
        </div>

        <!-- Quick Presets -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-[11px] font-bold text-gray-400 uppercase mr-1">⚡ Presets:</span>
          <button
            type="button"
            @click="loadPreset('civil_service_3')"
            class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-700 rounded-md border border-gray-200 transition-colors">
            3-Stage Civil Service
          </button>
          <button
            type="button"
            @click="loadPreset('executive_4')"
            class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-700 rounded-md border border-gray-200 transition-colors">
            4-Stage Executive
          </button>
          <button
            type="button"
            @click="loadPreset('agf_clearance')"
            class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-700 rounded-md border border-gray-200 transition-colors">
            AGF Executive Pathway
          </button>
        </div>
      </div>

      <!-- Steps Table -->
      <div class="overflow-x-auto border border-gray-200 rounded-xl">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase font-bold text-[10px] md:text-[11px] tracking-wider">
            <tr>
              <th class="py-2 px-2 w-14 text-center">Step #</th>
              <th class="py-2 px-2 w-44">
                <span class="flex items-center gap-1">
                  <Icon icon="lucide:user-check" width="13" class="text-blue-600" />
                  Target Officer / Office
                </span>
              </th>
              <th class="py-2 px-2 w-40">
                <span class="flex items-center gap-1">
                  <Icon icon="lucide:award" width="13" class="text-indigo-600" />
                  Required Role
                </span>
              </th>
              <th class="py-2 px-2">
                <span class="flex items-center gap-1">
                  <Icon icon="lucide:activity" width="13" class="text-emerald-600" />
                  Action to Perform
                </span>
              </th>
              <th class="py-2 px-2 w-44">Step Nature</th>
              <th class="py-2 px-2 w-52">Safeguards & SLA</th>
              <th class="py-2 px-1.5 w-14 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr
              v-for="(step, idx) in form.steps"
              :key="idx"
              class="hover:bg-blue-50/30 transition-colors"
              :class="{'bg-green-50/20': idx === 0, 'bg-emerald-50/20': idx === form.steps.length - 1 && form.steps.length > 1}">
              
              <!-- Order & Step Badge -->
              <td class="py-2.5 px-2 text-center align-top">
                <div class="flex flex-col items-center gap-1">
                  <span
                    class="w-6 h-6 flex items-center justify-center rounded-full font-bold text-xs shadow-sm"
                    :class="idx === 0 ? 'bg-green-600 text-white' : (idx === form.steps.length - 1 ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700')">
                    {{ idx + 1 }}
                  </span>
                  <span v-if="idx === 0" class="text-[9px] font-bold text-green-700 uppercase tracking-tighter">START</span>
                  <span v-else-if="idx === form.steps.length - 1" class="text-[9px] font-bold text-blue-700 uppercase tracking-tighter">FINAL</span>
                </div>
              </td>

              <!-- Column 1: Target Officer / Office -->
              <td class="py-2.5 px-2 align-top">
                <div class="flex flex-col gap-1.5">
                  <select
                    v-model="step.assignee_target"
                    @change="onAssigneeTargetChange(step, idx)"
                    class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs font-medium focus:ring-1 focus:ring-blue-500">
                    <option value="initiator">Initiating Officer (Applicant)</option>
                    <option value="supervisor">Immediate Line Supervisor</option>
                    <option value="unit_head">Head of Unit</option>
                    <option value="division_head">Head of Division</option>
                    <option value="director">Department Director</option>
                    <option value="director_admin">Director of Administration</option>
                    <option value="director_finance">Director of Finance & Accounts</option>
                    <option value="agf">Accountant-General of the Federation (AGF)</option>
                    <option value="role">Designated Role Officer</option>
                    <option value="custom">Custom Specified Office</option>
                  </select>

                  <input
                    v-if="step.assignee_target === 'custom'"
                    v-model="step.custom_office"
                    type="text"
                    placeholder="Enter Custom Office Title"
                    class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500" />
                </div>
              </td>

              <!-- Column 2: Role Required -->
              <td class="py-2.5 px-2 align-top">
                <div class="flex flex-col gap-1">
                  <select
                    v-model="step.role_required"
                    class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs font-medium focus:ring-1 focus:ring-blue-500">
                    <option :value="null">Any / Inherited from Office</option>
                    <option v-for="r in roles" :key="r" :value="r">
                      {{ r }}
                    </option>
                  </select>
                  <span class="text-[10px] text-gray-400">Civil Service Grade / Authority</span>
                </div>
              </td>

              <!-- Column 3: Action to Perform -->
              <td class="py-2.5 px-2 align-top">
                <div class="flex flex-col gap-1.5">
                  <input
                    v-model="step.name"
                    type="text"
                    placeholder="e.g. Draft & Submit, Scrutiny & Recommendation, Approval"
                    class="w-full px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 focus:ring-1 focus:ring-blue-500"
                    required />
                  
                  <div class="flex items-center gap-1 flex-wrap">
                    <button
                      v-for="presetAction in quickActions"
                      :key="presetAction"
                      type="button"
                      @click="step.name = presetAction"
                      class="text-[10px] px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded">
                      {{ presetAction }}
                    </button>
                  </div>
                </div>
              </td>

              <!-- Step Nature (Radio / Pills) -->
              <td class="py-2.5 px-2 align-top">
                <div class="grid grid-cols-2 gap-1">
                  <button
                    type="button"
                    @click="step.step_type = 'input'"
                    class="px-1.5 py-1 rounded text-[11px] font-medium border text-center transition-colors"
                    :class="step.step_type === 'input' ? 'bg-amber-100 border-amber-300 text-amber-800 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                    📝 Input
                  </button>
                  <button
                    type="button"
                    @click="step.step_type = 'review'"
                    class="px-1.5 py-1 rounded text-[11px] font-medium border text-center transition-colors"
                    :class="step.step_type === 'review' ? 'bg-blue-100 border-blue-300 text-blue-800 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                    🔍 Review
                  </button>
                  <button
                    type="button"
                    @click="step.step_type = 'approval'"
                    class="px-1.5 py-1 rounded text-[11px] font-medium border text-center transition-colors"
                    :class="step.step_type === 'approval' ? 'bg-emerald-100 border-emerald-300 text-emerald-800 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                    ✍️ Approve
                  </button>
                  <button
                    type="button"
                    @click="step.step_type = 'assign'"
                    class="px-1.5 py-1 rounded text-[11px] font-medium border text-center transition-colors"
                    :class="step.step_type === 'assign' ? 'bg-purple-100 border-purple-300 text-purple-800 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                    📌 Assign
                  </button>
                </div>
              </td>

              <!-- Safeguards & SLA (Checkboxes & SLA) -->
              <td class="py-2.5 px-2 align-top">
                <div class="flex flex-col gap-1.5">
                  <label class="flex items-center gap-1.5 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="step.requires_pin"
                      class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer" />
                    <span class="text-[11px] text-gray-700 font-medium">Digital Signature PIN</span>
                  </label>

                  <label class="flex items-center gap-1.5 cursor-pointer" :title="'If rejected, routes back to ' + getPreviousOfficerLabel(idx)">
                    <input
                      type="checkbox"
                      v-model="step.auto_return_on_rejection"
                      :disabled="idx === 0"
                      class="rounded text-red-600 focus:ring-red-500 w-3.5 h-3.5 cursor-pointer disabled:opacity-30" />
                    <span class="text-[11px] text-gray-700" :class="{'opacity-40': idx === 0}">
                      Return to Prev Officer on Query
                    </span>
                  </label>

                  <div class="flex items-center gap-1.5 mt-0.5">
                    <Icon icon="lucide:clock" width="12" class="text-gray-400" />
                    <span class="text-[10px] text-gray-500">SLA:</span>
                    <input
                      type="number"
                      v-model.number="step.sla_hours"
                      min="1"
                      max="720"
                      class="w-14 px-1 py-0.5 text-[11px] border border-gray-300 rounded text-center" />
                    <span class="text-[10px] text-gray-400">hours</span>
                  </div>
                </div>
              </td>

              <!-- Action Buttons (Reorder, Delete) -->
              <td class="py-2.5 px-1.5 text-center align-top">
                <div class="flex items-center justify-center gap-1">
                  <button
                    type="button"
                    @click="moveStepUp(idx)"
                    :disabled="idx === 0"
                    class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-20"
                    title="Move Step Up">
                    <Icon icon="lucide:chevron-up" width="16" />
                  </button>
                  <button
                    type="button"
                    @click="moveStepDown(idx)"
                    :disabled="idx === form.steps.length - 1"
                    class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-20"
                    title="Move Step Down">
                    <Icon icon="lucide:chevron-down" width="16" />
                  </button>
                  <button
                    type="button"
                    @click="removeStep(idx)"
                    :disabled="form.steps.length <= 1"
                    class="p-1 text-red-400 hover:text-red-600 disabled:opacity-20 ml-1"
                    title="Delete Step">
                    <Icon icon="lucide:trash-2" width="15" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Step Button -->
      <div class="mt-3 flex justify-between items-center">
        <button
          type="button"
          @click="addStep"
          class="flex items-center gap-2 px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold transition-colors">
          <Icon icon="lucide:plus" width="15" /> Add Next Step in Pathway
        </button>

        <p class="text-xs text-gray-500">
          Total Sequential Clearance Steps: <strong class="text-gray-800">{{ form.steps.length }}</strong>
        </p>
      </div>
    </section>

    <!-- Section 3: Visual Real-Time Clearance Pathway Flowchart -->
    <section class="mt-4 bg-white border border-gray-200 rounded-2xl shadow-sm p-3.5 md:p-4">
      <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-gray-100">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:workflow" width="18" class="text-blue-600" />
          <h2 class="text-sm md:text-base font-bold text-gray-800">3. Live Clearance Pathway Visualizer</h2>
        </div>
        <span class="text-xs text-gray-500">Forward clearance & backward query-return simulation</span>
      </div>

      <div class="p-3.5 md:p-4 bg-gray-50/80 rounded-xl border border-dashed border-gray-300 overflow-x-auto">
        <div class="flex items-center gap-3 min-w-max py-3">
          <template v-for="(step, idx) in form.steps" :key="'viz-' + idx">
            
            <!-- Step Node Card -->
            <div class="w-56 bg-white border rounded-xl p-3 shadow-sm transition-all relative flex flex-col justify-between"
              :class="idx === 0 ? 'border-green-300 ring-2 ring-green-100' : (idx === form.steps.length - 1 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-200')">
              
              <!-- Header tag -->
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                  :class="idx === 0 ? 'bg-green-100 text-green-800' : (idx === form.steps.length - 1 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700')">
                  Step {{ idx + 1 }}: {{ idx === 0 ? 'Initiator' : (idx === form.steps.length - 1 ? 'Final Authority' : 'Clearance') }}
                </span>
                <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded"
                  :class="{
                    'bg-amber-100 text-amber-800': step.step_type === 'input',
                    'bg-blue-100 text-blue-800': step.step_type === 'review',
                    'bg-emerald-100 text-emerald-800': step.step_type === 'approval',
                    'bg-purple-100 text-purple-800': step.step_type === 'assign'
                  }">
                  {{ step.step_type }}
                </span>
              </div>

              <!-- Action Title -->
              <p class="font-bold text-xs text-gray-900 line-clamp-1 mb-1">
                {{ step.name || 'Untitled Action' }}
              </p>

              <!-- Target / Role -->
              <div class="text-[11px] text-gray-600 space-y-0.5 mt-1 pt-1.5 border-t border-gray-100">
                <p class="flex items-center gap-1 font-medium text-gray-800">
                  <Icon icon="lucide:user" width="12" class="text-gray-400" />
                  {{ formatTargetLabel(step) }}
                </p>
                <p v-if="step.role_required" class="text-[10px] text-indigo-600 font-medium">
                  Grade: {{ step.role_required }}
                </p>
              </div>

              <!-- Footer Badges -->
              <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500">
                <span v-if="step.requires_pin" class="flex items-center gap-0.5 text-blue-600 font-medium" title="PIN Required">
                  <Icon icon="lucide:key-round" width="11" /> PIN
                </span>
                <span v-else class="text-gray-400">No PIN</span>

                <span class="flex items-center gap-0.5">
                  <Icon icon="lucide:clock" width="11" /> {{ step.sla_hours || 24 }}h
                </span>
              </div>
            </div>

            <!-- Inter-Step Escalation Connector -->
            <div v-if="idx < form.steps.length - 1" class="flex flex-col items-center justify-center px-1">
              <!-- Forward Green Arrow -->
              <div class="flex items-center gap-1 text-emerald-600 text-[10px] font-bold">
                <span>Endorse</span>
                <Icon icon="lucide:arrow-right" width="16" />
              </div>

              <!-- Rejection Reverse Red Arrow -->
              <div v-if="form.steps[idx + 1].auto_return_on_rejection" class="flex items-center gap-1 text-red-500 text-[9px] font-semibold mt-0.5">
                <Icon icon="lucide:corner-down-left" width="13" />
                <span>Query Return</span>
              </div>
            </div>

          </template>
        </div>
      </div>
    </section>

    <!-- Bottom Sticky Actions Bar -->
    <div class="sticky bottom-3 mt-6 bg-white/95 backdrop-blur border border-gray-200 rounded-2xl p-3 shadow-lg flex items-center justify-between gap-3">
      <Link href="/dashboard/workflow-builder" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3 py-1.5">
        Cancel & Discard Changes
      </Link>

      <div class="flex items-center gap-2.5">
        <button
          type="button"
          @click="submitStudio(false)"
          :disabled="form.processing"
          class="px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-xs font-semibold transition-all disabled:opacity-50">
          Save as Draft
        </button>

        <button
          type="button"
          @click="submitStudio(true)"
          :disabled="form.processing"
          class="flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm disabled:opacity-50">
          <Icon icon="lucide:check-circle-2" width="15" />
          Publish Pathway Live
        </button>
      </div>
    </div>

    <!-- Template Preview Modal -->
    <Dialog v-model:visible="isPreviewModalVisible" header="Template Preview" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '95vw' }" modal>
      <div v-if="previewTemplateData" class="p-2">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 mb-4">
          <div>
            <h3 class="font-bold text-gray-900 text-base">{{ previewTemplateData.title }}</h3>
            <p class="text-xs text-gray-500">Document Type: {{ (previewTemplateData.type || 'Standard').toUpperCase() }}</p>
          </div>
          <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full text-xs font-semibold">
            Active Template
          </span>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 max-h-[60vh] overflow-y-auto">
          <div v-if="previewTemplateData.content_html" v-html="previewTemplateData.content_html" class="prose prose-sm max-w-none text-gray-800 bg-white p-6 shadow-sm rounded-lg border border-gray-100 min-h-[300px]"></div>
          <div v-else class="text-center py-12 text-gray-500">
            <Icon icon="lucide:file-text" width="40" class="mx-auto text-gray-400 mb-2" />
            <p class="text-sm font-medium">Standard Template File Attached</p>
            <p class="text-xs text-gray-400 mt-1">This template contains structured form fields and dynamic placeholders.</p>
          </div>
        </div>
      </div>
    </Dialog>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Layout from "../../../Shared/Layout.vue";

const __default__ = {
  layout: Layout,
};

const props = defineProps({
  workflow: Object,
  version: Object,
  departments: Array,
  roles: Array,
  templates: Array,
});

const isPreviewModalVisible = ref(false);
const previewTemplateData = ref(null);

const quickActions = [
  'Draft & Submit',
  'Recommend & Forward',
  'Review & Minute',
  'Statutory Clearance',
  'Final Approval',
  'Registry Dispatch'
];

// Initialize form from props if editing existing workflow/version
const initialSteps = () => {
  if (props.version && props.version.steps && props.version.steps.length > 0) {
    return props.version.steps.map((s, idx) => ({
      name: s.name,
      role_required: s.role_required || null,
      assignee_target: s.assignee_target || 'role',
      custom_office: s.assignee_target === 'custom' ? s.name : '',
      step_type: s.step_type || 'review',
      requires_pin: s.requires_pin !== undefined ? Boolean(s.requires_pin) : true,
      auto_return_on_rejection: s.auto_return_on_rejection !== undefined ? Boolean(s.auto_return_on_rejection) : true,
      sla_hours: s.slas && s.slas.length > 0 ? s.slas[0].duration_hours : 24,
    }));
  }

  // Default 3-stage clearance pathway
  return [
    {
      name: 'Application Drafting & Submission',
      role_required: null,
      assignee_target: 'initiator',
      custom_office: '',
      step_type: 'input',
      requires_pin: false,
      auto_return_on_rejection: false,
      sla_hours: 24,
    },
    {
      name: 'Line Verification & Recommendation',
      role_required: 'Desk Officer',
      assignee_target: 'supervisor',
      custom_office: '',
      step_type: 'review',
      requires_pin: true,
      auto_return_on_rejection: true,
      sla_hours: 24,
    },
    {
      name: 'Executive Clearance & Final Approval',
      role_required: 'Director',
      assignee_target: 'director',
      custom_office: '',
      step_type: 'approval',
      requires_pin: true,
      auto_return_on_rejection: true,
      sla_hours: 48,
    }
  ];
};

const form = useForm({
  workflow_id: props.workflow ? props.workflow.id : null,
  version_id: props.version ? props.version.id : null,
  title: props.workflow ? props.workflow.title : '',
  emoji: props.workflow ? props.workflow.emoji : '📄',
  department_id: props.workflow ? props.workflow.department_id : null,
  document_template_id: props.workflow ? props.workflow.document_template_id : null,
  is_confidential: props.workflow ? Boolean(props.workflow.is_confidential) : false,
  publish_now: false,
  steps: initialSteps(),
});

const selectedTemplate = computed(() => {
  if (!form.document_template_id || !props.templates) return null;
  return props.templates.find(t => t.id === form.document_template_id) || null;
});

const setScope = (scope) => {
  if (scope === 'global') {
    form.department_id = null;
  } else {
    // Select first department if none set
    if (!form.department_id && props.departments && props.departments.length > 0) {
      form.department_id = props.departments[0].id;
    }
  }
};

const openTemplatePreview = (template) => {
  previewTemplateData.value = template;
  isPreviewModalVisible.value = true;
};

const onAssigneeTargetChange = (step, idx) => {
  if (step.assignee_target === 'initiator') {
    step.step_type = 'input';
    step.requires_pin = false;
    step.auto_return_on_rejection = false;
    if (!step.name || step.name.includes('Clearance')) {
      step.name = 'Application Drafting & Submission';
    }
  } else if (step.assignee_target === 'supervisor') {
    step.step_type = 'review';
    step.role_required = step.role_required || 'Desk Officer';
    if (!step.name) step.name = 'Line Verification & Scrutiny';
  } else if (step.assignee_target === 'director' || step.assignee_target === 'director_admin') {
    step.step_type = 'approval';
    step.role_required = 'Director';
    if (!step.name) step.name = 'Executive Statutory Approval';
  } else if (step.assignee_target === 'agf') {
    step.step_type = 'approval';
    step.role_required = 'Accountant-General';
    if (!step.name) step.name = 'Accountant-General Final Approval';
  }
};

const getPreviousOfficerLabel = (idx) => {
  if (idx <= 0) return 'None';
  const prev = form.steps[idx - 1];
  return formatTargetLabel(prev);
};

const formatTargetLabel = (step) => {
  if (!step) return '';
  switch (step.assignee_target) {
    case 'initiator': return 'Initiating Officer (Applicant)';
    case 'supervisor': return 'Immediate Supervisor';
    case 'unit_head': return 'Head of Unit';
    case 'division_head': return 'Head of Division';
    case 'director': return 'Department Director';
    case 'director_admin': return 'Director of Administration';
    case 'director_finance': return 'Director of Finance & Accounts';
    case 'agf': return 'Accountant-General (AGF)';
    case 'custom': return step.custom_office || 'Custom Office';
    default: return step.role_required ? 'Role: ' + step.role_required : 'Assigned Officer';
  }
};

const addStep = () => {
  const isFinal = form.steps.length >= 2;
  form.steps.push({
    name: isFinal ? 'Executive Review & Approval' : 'Verification & Recommendation',
    role_required: isFinal ? 'Director' : 'Desk Officer',
    assignee_target: isFinal ? 'director' : 'supervisor',
    custom_office: '',
    step_type: isFinal ? 'approval' : 'review',
    requires_pin: true,
    auto_return_on_rejection: true,
    sla_hours: 24,
  });
};

const removeStep = (idx) => {
  if (form.steps.length <= 1) return;
  form.steps.splice(idx, 1);
};

const moveStepUp = (idx) => {
  if (idx <= 0) return;
  const item = form.steps.splice(idx, 1)[0];
  form.steps.splice(idx - 1, 0, item);
};

const moveStepDown = (idx) => {
  if (idx >= form.steps.length - 1) return;
  const item = form.steps.splice(idx, 1)[0];
  form.steps.splice(idx + 1, 0, item);
};

const loadPreset = (presetKey) => {
  if (presetKey === 'civil_service_3') {
    form.steps = [
      {
        name: 'Application Drafting & Submission',
        role_required: null,
        assignee_target: 'initiator',
        custom_office: '',
        step_type: 'input',
        requires_pin: false,
        auto_return_on_rejection: false,
        sla_hours: 24,
      },
      {
        name: 'Verification & Recommendation',
        role_required: 'Desk Officer',
        assignee_target: 'supervisor',
        custom_office: '',
        step_type: 'review',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 24,
      },
      {
        name: 'Executive Statutory Approval',
        role_required: 'Director',
        assignee_target: 'director',
        custom_office: '',
        step_type: 'approval',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 48,
      }
    ];
  } else if (presetKey === 'executive_4') {
    form.steps = [
      {
        name: 'Initiation & Document Upload',
        role_required: null,
        assignee_target: 'initiator',
        custom_office: '',
        step_type: 'input',
        requires_pin: false,
        auto_return_on_rejection: false,
        sla_hours: 24,
      },
      {
        name: 'Unit Scrutiny & Compliance Check',
        role_required: 'Desk Officer',
        assignee_target: 'unit_head',
        custom_office: '',
        step_type: 'review',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 24,
      },
      {
        name: 'Division Clearance & Technical Vetting',
        role_required: 'Deputy Director',
        assignee_target: 'division_head',
        custom_office: '',
        step_type: 'review',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 48,
      },
      {
        name: 'Director Approval & Minute',
        role_required: 'Director',
        assignee_target: 'director',
        custom_office: '',
        step_type: 'approval',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 48,
      }
    ];
  } else if (presetKey === 'agf_clearance') {
    form.steps = [
      {
        name: 'Departmental Memo Initiation',
        role_required: 'Director',
        assignee_target: 'director',
        custom_office: '',
        step_type: 'input',
        requires_pin: true,
        auto_return_on_rejection: false,
        sla_hours: 24,
      },
      {
        name: 'Administration & Finance Clearance',
        role_required: 'Director',
        assignee_target: 'director_admin',
        custom_office: '',
        step_type: 'review',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 48,
      },
      {
        name: 'Accountant-General of the Federation Executive Approval',
        role_required: 'Accountant-General',
        assignee_target: 'agf',
        custom_office: '',
        step_type: 'approval',
        requires_pin: true,
        auto_return_on_rejection: true,
        sla_hours: 72,
      }
    ];
  }
};

const submitStudio = (publish = false) => {
  form.publish_now = publish;
  form.post('/dashboard/workflow-builder/studio/save', {
    preserveScroll: true,
  });
};
</script>
