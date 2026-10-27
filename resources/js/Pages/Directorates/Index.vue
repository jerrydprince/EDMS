<template>
  <main class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 font-sans">
    <Toast />

    <!-- Top Executive Header -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <span class="px-2.5 py-0.5 text-xs font-bold font-mono uppercase bg-blue-100 text-blue-800 rounded-md border border-blue-200">
            Departmental & Directorate Configuration
          </span>
          <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-md border border-emerald-200 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            4-Tier Governance Engine
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Organizational Capabilities & Function Activation
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Configure functions, document templates, and workflows hierarchically:
          <span class="font-semibold text-slate-700">Department</span> &rarr;
          <span class="font-semibold text-slate-700">Division</span> &rarr;
          <span class="font-semibold text-slate-700">Unit</span> &rarr;
          <span class="font-semibold text-slate-700">Section</span>
        </p>
      </div>

      <!-- Quick Executive Action Links -->
      <div class="flex flex-wrap items-center gap-2.5">
        <Link
          :href="'/dashboard/directorates/oversight?department_id=' + selectedDepartmentId"
          class="px-3.5 py-2 bg-gradient-to-r from-blue-700 to-indigo-700 hover:from-blue-800 hover:to-indigo-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:gauge" width="16" />
          <span>Directorate Oversight</span>
          <span class="px-1.5 py-0.2 bg-white/20 text-white rounded text-[10px] font-mono">Executive</span>
        </Link>
        <Link
          href="/dashboard/governance-hub"
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:network" width="16" class="text-indigo-600" />
          <span>Structure Organogram</span>
        </Link>
        <Link
          href="/dashboard/access-level"
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:shield-check" width="16" class="text-blue-600" />
          <span>Access Roles</span>
        </Link>
      </div>
    </div>

    <!-- Dynamic Department Switcher Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3 flex-1">
          <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center font-bold">
            <Icon icon="lucide:building-2" width="22" />
          </div>
          <div class="flex-1">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Selected Department / Directorate
            </label>
            <div class="flex items-center gap-3 mt-1">
              <div class="relative min-w-[280px] sm:min-w-[380px]">
                <select
                  v-model="selectedDepartmentId"
                  @change="switchDepartment"
                  class="w-full bg-white border border-slate-300 text-slate-900 text-sm font-semibold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-2xs"
                >
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                    {{ dept.title }} ({{ dept.staff_count || 0 }} Staff, {{ dept.divisions_count || 0 }} Divisions)
                  </option>
                </select>
              </div>
              <span class="text-xs text-slate-500 hidden sm:inline">
                Scales dynamically as new directorates are created.
              </span>
            </div>
          </div>
        </div>

        <!-- Breadcrumb Ribbon -->
        <div class="flex items-center flex-wrap gap-1.5 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 text-xs">
          <span class="font-bold text-slate-500 uppercase text-[10px] mr-1">Scope:</span>
          <button
            @click="selectDepartmentRoot"
            :class="props.activeTier === 'department' ? 'font-black text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded' : 'text-slate-600 hover:text-slate-900'"
          >
            {{ props.selectedDepartment?.title || 'Department' }}
          </button>
          <template v-for="(crumb, idx) in props.breadcrumbs.slice(1)" :key="idx">
            <Icon icon="lucide:chevron-right" width="12" class="text-slate-400" />
            <span
              :class="idx === props.breadcrumbs.length - 2 ? 'font-black text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded' : 'text-slate-600'"
            >
              {{ crumb.title }}
            </span>
          </template>
        </div>
      </div>
    </div>

    <!-- Main Two-Column 4-Tier Interactive Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      
      <!-- LEFT COLUMN: 4-Tier Hierarchical Organogram Tree (lg:col-span-4) -->
      <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sticky top-6">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
          <div>
            <h2 class="font-black text-sm text-slate-900 uppercase tracking-wide flex items-center gap-1.5">
              <Icon icon="lucide:folder-tree" width="16" class="text-blue-600" />
              <span>Directorate Tree</span>
            </h2>
            <p class="text-[11px] text-slate-500">Click any tier to configure its functions</p>
          </div>
          <button
            @click="expandAll = !expandAll"
            class="text-[11px] font-semibold text-blue-600 hover:text-blue-800"
          >
            {{ expandAll ? 'Collapse Tree' : 'Expand All' }}
          </button>
        </div>

        <!-- The Interactive Tree -->
        <div class="space-y-1.5 max-h-[calc(100vh-280px)] overflow-y-auto pr-1">
          
          <!-- Department Root Node -->
          <div
            @click="selectDepartmentRoot"
            class="p-2.5 rounded-xl border transition cursor-pointer flex items-center justify-between"
            :class="props.activeTier === 'department' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-500/20 text-blue-900 font-bold' : 'hover:bg-slate-50 border-slate-200 text-slate-800'"
          >
            <div class="flex items-center gap-2 truncate">
              <span class="w-2 h-2 rounded-full" :class="props.activeTier === 'department' ? 'bg-blue-600 animate-pulse' : 'bg-slate-400'"></span>
              <Icon icon="lucide:landmark" width="16" class="text-blue-600 shrink-0" />
              <span class="text-xs truncate font-bold">{{ props.selectedDepartment?.title }}</span>
            </div>
            <span class="px-2 py-0.5 text-[10px] font-mono font-semibold rounded bg-white border border-slate-200 text-slate-600">
              Directorate
            </span>
          </div>

          <!-- Divisions Sub-Tree -->
          <div v-for="div in props.orgTree" :key="div.id" class="pl-2 border-l-2 border-slate-200 ml-3 space-y-1 mt-1">
            
            <!-- Division Node Item -->
            <div
              class="p-2 rounded-xl border transition flex items-center justify-between cursor-pointer"
              :class="props.activeTier === 'division' && props.targetNode?.id === div.id ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-500/20 text-blue-900 font-bold' : 'hover:bg-slate-50 border-slate-200 text-slate-700'"
            >
              <div @click="selectDivision(div.id)" class="flex items-center gap-2 flex-1 truncate">
                <Icon icon="lucide:folder" width="14" class="text-amber-500 shrink-0" />
                <span class="text-xs truncate">{{ div.title }}</span>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-mono text-slate-400">{{ div.users_count || 0 }} staff</span>
                <button
                  v-if="div.unit?.length"
                  @click.stop="toggleCollapse('div_' + div.id)"
                  class="w-5 h-5 rounded hover:bg-slate-200/60 flex items-center justify-center text-slate-500"
                >
                  <Icon :icon="isCollapsed('div_' + div.id) ? 'lucide:chevron-right' : 'lucide:chevron-down'" width="12" />
                </button>
              </div>
            </div>

            <!-- Units Sub-Tree under Division -->
            <div v-if="!isCollapsed('div_' + div.id)" class="pl-2.5 border-l-2 border-amber-200 ml-2 space-y-1">
              <div v-for="unit in div.unit" :key="unit.id" class="space-y-1">
                
                <!-- Unit Node Item -->
                <div
                  class="p-1.5 px-2 rounded-lg border transition flex items-center justify-between cursor-pointer text-xs"
                  :class="props.activeTier === 'unit' && props.targetNode?.id === unit.id ? 'bg-blue-50 border-blue-300 ring-1 ring-blue-500/30 text-blue-900 font-bold' : 'hover:bg-slate-50 border-slate-200/80 text-slate-700'"
                >
                  <div @click="selectUnit(div.id, unit.id)" class="flex items-center gap-1.5 flex-1 truncate">
                    <Icon icon="lucide:git-commit" width="12" class="text-indigo-500 shrink-0" />
                    <span class="truncate">{{ unit.title }}</span>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <span class="text-[9px] font-mono text-slate-400">{{ unit.users_count || 0 }} staff</span>
                    <button
                      v-if="unit.sections?.length"
                      @click.stop="toggleCollapse('unit_' + unit.id)"
                      class="w-4 h-4 rounded hover:bg-slate-200/60 flex items-center justify-center text-slate-500"
                    >
                      <Icon :icon="isCollapsed('unit_' + unit.id) ? 'lucide:chevron-right' : 'lucide:chevron-down'" width="10" />
                    </button>
                  </div>
                </div>

                <!-- Sections Sub-Tree under Unit -->
                <div v-if="!isCollapsed('unit_' + unit.id)" class="pl-2.5 border-l-2 border-indigo-200 ml-2 space-y-0.5">
                  <div
                    v-for="sec in unit.sections"
                    :key="sec.id"
                    @click="selectSection(div.id, unit.id, sec.id)"
                    class="p-1.5 px-2 rounded-md border transition cursor-pointer flex items-center justify-between text-[11px]"
                    :class="props.activeTier === 'section' && props.targetNode?.id === sec.id ? 'bg-blue-100/70 border-blue-400 text-blue-950 font-black' : 'hover:bg-slate-50 border-slate-100 text-slate-600'"
                  >
                    <div class="flex items-center gap-1.5 truncate">
                      <Icon icon="lucide:file-text" width="11" class="text-emerald-600 shrink-0" />
                      <span class="truncate">{{ sec.title }}</span>
                    </div>
                    <span class="text-[9px] font-mono text-slate-400">{{ sec.users_count || 0 }} staff</span>
                  </div>
                  <div v-if="!unit.sections?.length" class="text-[10px] text-slate-400 italic pl-2">
                    No sections defined
                  </div>
                </div>

              </div>
              <div v-if="!div.unit?.length" class="text-[10px] text-slate-400 italic pl-2">
                No units defined
              </div>
            </div>

          </div>
          <div v-if="!props.orgTree?.length" class="text-xs text-slate-400 text-center py-6">
            No divisions registered for this department.
          </div>

        </div>
      </div>

      <!-- RIGHT COLUMN: Context-Sensitive Activation Canvas (lg:col-span-8) -->
      <div class="lg:col-span-8 space-y-6">

        <!-- Active Node Header & Bounding Envelope Notice -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 text-[11px] font-black uppercase font-mono rounded-md"
                  :class="{
                    'bg-purple-100 text-purple-800 border border-purple-200': props.activeTier === 'department',
                    'bg-amber-100 text-amber-800 border border-amber-200': props.activeTier === 'division',
                    'bg-indigo-100 text-indigo-800 border border-indigo-200': props.activeTier === 'unit',
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': props.activeTier === 'section',
                  }"
                >
                  {{ props.activeTier }} Level Configuration
                </span>
                <span v-if="props.targetNode?.parent_title" class="text-xs text-slate-500">
                  Parent: <strong class="text-slate-800">{{ props.targetNode.parent_title }}</strong>
                </span>
              </div>
              <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                {{ props.targetNode?.title || 'Configuration Target' }}
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ props.targetNode?.description || 'Operational mandate and function scoping for this administrative seat.' }}
              </p>
            </div>

            <!-- Active Node Metrics -->
            <div class="flex items-center gap-2">
              <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center min-w-[80px]">
                <span class="block text-xs font-mono font-bold text-slate-700">
                  {{ props.activeCapabilityCodes?.length || 0 }} / {{ props.allowedCapabilityCodes?.length || 0 }}
                </span>
                <span class="text-[10px] text-slate-400 font-semibold uppercase">Active Functions</span>
              </div>
              <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center min-w-[80px]">
                <span class="block text-xs font-mono font-bold text-slate-700">
                  {{ props.staff?.length || 0 }}
                </span>
                <span class="text-[10px] text-slate-400 font-semibold uppercase">Officers</span>
              </div>
            </div>
          </div>

          <!-- Bounding Envelope Clarification Alert -->
          <div class="mt-3.5 p-3 rounded-xl bg-blue-50/80 border border-blue-200 flex items-start gap-2.5 text-xs text-blue-900">
            <Icon icon="lucide:info" width="16" class="text-blue-600 shrink-0 mt-0.5" />
            <div>
              <strong class="font-bold">Bounding Envelope Rule:</strong>
              <span v-if="props.activeTier === 'department'">
                As the Directorate root, capabilities activated here define the master operational pool available to be assigned down to Divisions, Units, and Sections.
              </span>
              <span v-else>
                Only capabilities enabled at the parent (<strong class="font-bold">{{ props.targetNode?.parent_title || 'parent node' }}</strong>) can be activated at this tier. Turning off a function at a parent tier automatically cascades deactivation down to all children.
              </span>
            </div>
          </div>
        </div>

        <!-- 4 Configuration Tabs Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2 flex flex-wrap items-center gap-1.5">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer"
            :class="activeTab === tab.id ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <Icon :icon="tab.icon" width="15" />
            <span>{{ tab.name }}</span>
            <span
              v-if="tab.badge !== undefined"
              class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
              :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
            >
              {{ tab.badge }}
            </span>
          </button>
        </div>

        <!-- TAB 1: Capabilities & Function Activation (The Core Engine) -->
        <div v-if="activeTab === 'capabilities'" class="space-y-6">
          <div v-for="(caps, category) in groupedCapabilities" :key="category" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
              <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <span>{{ category }}</span>
              </h3>
              <span class="text-xs text-slate-400 font-mono">
                {{ caps.filter(c => isCapabilityActive(c.code)).length }} / {{ caps.length }} Active
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
              <div
                v-for="cap in caps"
                :key="cap.code"
                class="p-4 rounded-xl border transition flex flex-col justify-between"
                :class="isCapabilityActive(cap.code) ? 'bg-emerald-50/40 border-emerald-300 ring-1 ring-emerald-500/20' : (!isCapabilityAllowed(cap.code) ? 'bg-slate-50/60 border-slate-200 opacity-60' : 'bg-white border-slate-200 hover:border-slate-300')"
              >
                <div>
                  <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <Icon :icon="cap.icon || 'lucide:zap'" width="16" />
                      </div>
                      <div>
                        <h4 class="font-bold text-xs text-slate-900 leading-tight">{{ cap.name }}</h4>
                        <span class="text-[10px] font-mono text-slate-400">{{ cap.code }}</span>
                      </div>
                    </div>

                    <!-- Toggle Button -->
                    <div>
                      <button
                        v-if="isCapabilityAllowed(cap.code)"
                        @click="toggleCapability(cap.code)"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="isCapabilityActive(cap.code) ? 'bg-emerald-600' : 'bg-slate-300'"
                      >
                        <span
                          class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                          :class="isCapabilityActive(cap.code) ? 'translate-x-4' : 'translate-x-0'"
                        />
                      </button>
                      <span v-else class="text-[10px] font-mono font-semibold px-2 py-0.5 bg-slate-200 text-slate-500 rounded">
                        Locked
                      </span>
                    </div>
                  </div>

                  <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                    {{ cap.description }}
                  </p>
                </div>

                <!-- Footer Status & Tier Availability -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                  <span v-if="!isCapabilityAllowed(cap.code)" class="text-amber-700 font-semibold flex items-center gap-1">
                    <Icon icon="lucide:lock" width="11" />
                    <span>Requires activation on {{ props.targetNode?.parent_title || 'parent' }}</span>
                  </span>
                  <span v-else-if="isCapabilityActive(cap.code)" class="text-emerald-700 font-bold flex items-center gap-1">
                    <Icon icon="lucide:check-circle" width="11" />
                    <span>Active on this seat</span>
                  </span>
                  <span v-else class="text-slate-400">
                    Inactive
                  </span>

                  <span v-if="cap.default_route" class="font-mono text-[10px] text-blue-600">
                    {{ cap.default_route }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: Document Templates Scoping -->
        <div v-if="activeTab === 'templates'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide">
                    Document Templates for {{ props.targetNode?.title }}
                  </h3>
                  <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800">
                    {{ props.activeTier.toUpperCase() }} LEVEL
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  Templates originating or exclusively designated to this administrative hierarchy.
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  v-if="props.isSuperAdmin"
                  @click="openCreateTemplateModal"
                  class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                >
                  <Icon icon="lucide:upload-cloud" width="16" />
                  <span>Create / Upload Template</span>
                </button>
                <button
                  @click="showAssignTemplateModal = true"
                  class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs"
                >
                  <Icon icon="lucide:link" width="14" />
                  <span>Assign Scope</span>
                </button>
              </div>
            </div>

            <!-- Scoped Templates List -->
            <div class="mb-6">
              <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <Icon icon="lucide:folder-check" width="14" class="text-blue-600" />
                <span>Tier-Scoped Templates ({{ props.nodeTemplates?.length || 0 }})</span>
              </h4>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <div
                  v-for="tpl in props.nodeTemplates"
                  :key="tpl.id"
                  class="p-4 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition flex items-start justify-between gap-3 shadow-2xs"
                >
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                      <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-blue-100 text-blue-800">
                        {{ tpl.type || 'Draft' }}
                      </span>
                      <h5 class="text-xs font-bold text-slate-900 truncate" :title="tpl.title">{{ tpl.title }}</h5>
                    </div>
                    
                    <!-- Scope Badges -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-slate-500">
                      <span v-if="tpl.section" class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded font-medium">
                        Section: {{ tpl.section.title }}
                      </span>
                      <span v-else-if="tpl.unit" class="px-1.5 py-0.2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded font-medium">
                        Unit: {{ tpl.unit.title }}
                      </span>
                      <span v-else-if="tpl.division" class="px-1.5 py-0.2 bg-amber-50 text-amber-700 border border-amber-200 rounded font-medium">
                        Division: {{ tpl.division.title }}
                      </span>
                      <span v-else class="px-1.5 py-0.2 bg-purple-50 text-purple-700 border border-purple-200 rounded font-medium">
                        Department: {{ tpl.department?.title || 'All Department' }}
                      </span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <button
                      @click="previewTemplate(tpl)"
                      class="px-2.5 py-1 text-blue-600 hover:bg-blue-50 rounded-lg text-xs font-semibold flex items-center gap-1 transition"
                    >
                      <Icon icon="lucide:eye" width="13" />
                      <span>Preview</span>
                    </button>
                    <button
                      v-if="props.isSuperAdmin"
                      @click="deleteTemplate(tpl)"
                      class="px-2 py-1 text-red-500 hover:bg-red-50 rounded-lg text-xs transition"
                      title="Delete Template"
                    >
                      <Icon icon="lucide:trash-2" width="13" />
                    </button>
                  </div>
                </div>

                <div v-if="!props.nodeTemplates?.length" class="col-span-2 text-center py-8 px-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                  <Icon icon="lucide:file-question" width="28" class="text-slate-400 mx-auto mb-1.5" />
                  <p class="text-xs font-medium text-slate-600">No seat-specific templates designated for this {{ props.activeTier }}.</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Officers at this desk have access to Universal Institutional Templates below.</p>
                </div>
              </div>
            </div>

            <!-- Universal Templates Reference -->
            <div class="pt-5 border-t border-slate-100">
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                  <Icon icon="lucide:globe" width="14" class="text-indigo-600" />
                  <span>Universal Institutional Templates (Available To All Desks)</span>
                </h4>
                <span class="text-[11px] text-slate-400 font-mono">{{ props.universalTemplates?.length || 0 }} Universal</span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                <div
                  v-for="uTpl in props.universalTemplates"
                  :key="uTpl.id"
                  class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-slate-300 text-xs flex items-center justify-between gap-2 transition"
                >
                  <div class="min-w-0 flex-1">
                    <span class="font-bold text-slate-800 block truncate" :title="uTpl.title">{{ uTpl.title }}</span>
                    <span class="text-[10px] font-mono text-slate-400 uppercase">{{ uTpl.type || 'Universal' }}</span>
                  </div>
                  <div class="flex items-center gap-1.5 shrink-0">
                    <button @click="previewTemplate(uTpl)" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-semibold flex items-center gap-0.5">
                      <Icon icon="lucide:eye" width="12" />
                      <span>View</span>
                    </button>
                    <button
                      v-if="props.isSuperAdmin"
                      @click="deleteTemplate(uTpl)"
                      class="text-red-500 hover:text-red-700 text-xs p-1"
                      title="Delete Template"
                    >
                      <Icon icon="lucide:trash-2" width="12" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: Workflows & Routing Rules -->
        <div v-if="activeTab === 'workflows'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
              <div>
                <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide">
                  Workflows & Routing for {{ props.targetNode?.title }}
                </h3>
                <p class="text-xs text-slate-500">Direct file routing, step progression, and approval thresholds</p>
              </div>
              <Link
                href="/dashboard/workflow-builder"
                class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5"
              >
                <Icon icon="lucide:workflow" width="14" />
                <span>Launch Workflow Builder</span>
              </Link>
            </div>

            <div class="space-y-3">
              <div
                v-for="wf in props.workflows"
                :key="wf.id"
                class="p-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition flex items-center justify-between gap-3"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                    <Icon icon="lucide:git-pull-request" width="16" />
                  </div>
                  <div>
                    <h4 class="font-bold text-xs text-slate-900">{{ wf.name }}</h4>
                    <p class="text-[11px] text-slate-500 font-mono">{{ wf.workflow_type || 'Operational' }} &bull; {{ wf.steps_count || wf.versions?.[0]?.steps?.length || 0 }} Steps</p>
                  </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800">
                  Active
                </span>
              </div>
              <div v-if="!props.workflows?.length" class="text-center py-6 text-xs text-slate-400">
                No specific workflows registered for this department.
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: Staff & Desks at this Node -->
        <div v-if="activeTab === 'staff'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
              <div>
                <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide">
                  Officers Posted To {{ props.targetNode?.title }}
                </h3>
                <p class="text-xs text-slate-500">Personnel assigned to this specific operational seat</p>
              </div>
              <Link
                href="/dashboard/manage-users"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5"
              >
                <Icon icon="lucide:user-plus" width="14" />
                <span>Assign Staff Member</span>
              </Link>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-black tracking-wider text-slate-500 border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Officer Name</th>
                    <th class="px-4 py-3">Official Email</th>
                    <th class="px-4 py-3">Desk / Designation</th>
                    <th class="px-4 py-3">Grade Level</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="user in props.staff" :key="user.id" class="hover:bg-slate-50/50 transition">
                    <td class="px-4 py-3 font-bold text-slate-900 flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs uppercase overflow-hidden">
                        <img v-if="user.profile_image" :src="'/' + user.profile_image" class="w-full h-full object-cover" />
                        <span v-else>{{ user.first_name?.[0] }}{{ user.last_name?.[0] }}</span>
                      </div>
                      <span>{{ user.first_name }} {{ user.last_name }}</span>
                      <span v-if="user.is_department_head" class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-blue-100 text-blue-800 border border-blue-200">
                        HOD
                      </span>
                    </td>
                    <td class="px-4 py-3 font-mono text-[11px]">{{ user.email }}</td>
                    <td class="px-4 py-3">{{ user.designation?.title || 'Unassigned Desk' }}</td>
                    <td class="px-4 py-3 font-mono">GL {{ user.grade_level || 'N/A' }}</td>
                    <td class="px-4 py-3 text-right">
                      <Link
                        :href="'/dashboard/edit-user?id=' + user.id"
                        class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1"
                      >
                        <span>Edit Access</span>
                        <Icon icon="lucide:chevron-right" width="12" />
                      </Link>
                    </td>
                  </tr>
                  <tr v-if="!props.staff?.length">
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                      No officers currently posted to this {{ props.activeTier }}.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 5: Operating Calendar & SLA -->
        <div v-if="activeTab === 'calendar'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide mb-1">
              Working Days & Operating Schedule
            </h3>
            <p class="text-xs text-slate-500 mb-4">Official working days used to calculate file dwell times and turnaround SLA</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
              <div
                v-for="day in props.workingDays"
                :key="day.id"
                class="p-3 rounded-xl border text-center transition"
                :class="day.is_working_day ? 'bg-blue-50/70 border-blue-200 text-blue-900' : 'bg-slate-50 border-slate-200 text-slate-400 opacity-60'"
              >
                <span class="block text-xs font-bold">{{ day.day_name }}</span>
                <span class="text-[10px] font-mono mt-1 block">
                  {{ day.is_working_day ? (day.start_time?.slice(0, 5) + ' - ' + day.end_time?.slice(0, 5)) : 'Non-Working' }}
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Template Preview Modal -->
    <Dialog v-model:visible="showTemplateModal" modal header="Document Template Preview" :style="{ width: '42rem' }" :breakpoints="{ '960px': '75vw', '641px': '95vw' }">
      <div v-if="selectedTemplate" class="p-4 space-y-4">
        <div>
          <h3 class="font-bold text-base text-slate-900">{{ selectedTemplate.title }}</h3>
          <p class="text-xs text-slate-500 font-mono">Type: {{ selectedTemplate.type }} &bull; Version: {{ selectedTemplate.version }}</p>
        </div>
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl max-h-96 overflow-y-auto text-xs font-mono whitespace-pre-wrap">
          {{ selectedTemplate.content_html || 'Empty Template Body' }}
        </div>
      </div>
    </Dialog>

    <!-- Assign Template Scope Modal -->
    <Dialog v-model:visible="showAssignTemplateModal" modal header="Assign Template Scope" :style="{ width: '32rem' }">
      <form @submit.prevent="submitAssignTemplate" class="p-4 space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Select Template</label>
          <select v-model="assignTemplateForm.template_id" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs">
            <option v-for="t in props.universalTemplates" :key="t.id" :value="t.id">
              {{ t.title }} ({{ t.type }})
            </option>
          </select>
        </div>
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
          Target Scope: <strong>{{ props.targetNode?.title }} ({{ props.activeTier }})</strong>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showAssignTemplateModal = false" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800">
            Cancel
          </button>
          <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white font-bold text-xs rounded-xl shadow-xs">
            Confirm Scope Assignment
          </button>
        </div>
      </form>
    </Dialog>

    <!-- Create / Upload Document Template Modal (System Admin Only) -->
    <Dialog
      v-model:visible="showCreateTemplateModal"
      modal
      header="Create & Scope Document Template"
      :style="{ width: '58rem', maxWidth: '96vw' }"
      :breakpoints="{ '960px': '85vw', '641px': '98vw' }"
    >
      <form @submit.prevent="submitCreateTemplate" class="p-4 sm:p-6 space-y-5">
        
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
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center mb-2 shadow-2xs">
              <Icon icon="lucide:file-up" width="24" />
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
            <Icon icon="lucide:loader-2" class="animate-spin text-blue-600 mb-2" width="28" />
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

        <!-- Detected Placeholders Bar -->
        <div v-if="extractedPlaceholders.length" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs">
          <div class="flex items-center justify-between mb-1.5">
            <span class="font-bold text-amber-900 flex items-center gap-1.5">
              <Icon icon="lucide:tags" width="14" class="text-amber-700" />
              <span>Discovered Dynamic Placeholders ({{ extractedPlaceholders.length }})</span>
            </span>
            <span class="text-[10px] text-amber-700">Click a chip to copy token</span>
          </div>
          <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
            <button
              type="button"
              v-for="ph in extractedPlaceholders"
              :key="ph"
              @click="copyPlaceholder(ph)"
              class="px-2 py-0.5 bg-white border border-amber-300 rounded text-[11px] font-mono text-amber-900 hover:bg-amber-100 transition shadow-2xs cursor-pointer flex items-center gap-1"
              :title="'Copy token: ' + ph"
            >
              <span>&#123;&#123; {{ ph }} &#125;&#125;</span>
              <Icon icon="lucide:copy" width="10" class="text-amber-600" />
            </button>
          </div>
        </div>

        <!-- Basic Metadata -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Template Title *</label>
            <input
              v-model="createForm.title"
              type="text"
              required
              placeholder="e.g., Annual Casual Leave Requisition Docket"
              class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-slate-700 mb-1 flex items-center justify-between">
              <span>Document Type (from Taxonomy) *</span>
              <span v-if="selectedCreateDocType" class="text-[10px] text-blue-600 font-normal">
                {{ selectedCreateDocType.metadata?.length || 0 }} fields
              </span>
            </label>
            <select
              v-model="createForm.document_type_id"
              @change="onDocumentTypeSelect"
              required
              class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white"
            >
              <option :value="null" disabled>Select Document Type from Taxonomy...</option>
              <option v-for="dt in props.documentTypes" :key="dt.id" :value="dt.id">
                {{ dt.name }} ({{ dt.metadata?.length || 0 }} fields)
              </option>
            </select>
          </div>
        </div>

        <!-- Dynamic Taxonomy Metadata Tokens Bar -->
        <div v-if="selectedCreateDocType && selectedCreateDocType.metadata?.length" class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl text-xs space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="font-bold text-blue-900 flex items-center gap-1.5">
              <Icon icon="lucide:tags" width="14" class="text-blue-700" />
              <span>Taxonomy Metadata Fields for "{{ selectedCreateDocType.name }}":</span>
            </span>
            <span class="text-[10px] text-blue-600">Click to append placeholder token</span>
          </div>
          <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
            <button
              type="button"
              v-for="meta in selectedCreateDocType.metadata"
              :key="meta.id"
              @click="insertCreateToken(formatTokenKey(meta.name))"
              class="px-2 py-0.5 bg-white border border-blue-300 rounded text-[11px] font-mono text-blue-900 hover:bg-blue-100 transition shadow-2xs cursor-pointer flex items-center gap-1"
              :title="'Insert token {{ ' + formatTokenKey(meta.name) + ' }}'"
            >
              <span>+ &#123;&#123; {{ formatTokenKey(meta.name) }} &#125;&#125;</span>
            </button>
          </div>
        </div>

        <!-- Template Document Body (Rich WYSIWYG Editor with Full Document Formatting) -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold uppercase text-slate-800 flex items-center gap-1.5">
              <Icon icon="lucide:file-text" width="14" class="text-blue-600" />
              <span>Document Template Content & Formatting *</span>
            </label>
            <div class="flex items-center gap-2">
              <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">Dynamic tokens: &#123;&#123; applicant_name &#125;&#125;</span>
              <button
                type="button"
                @click="showCreateHtmlSource = !showCreateHtmlSource"
                class="px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:text-blue-600 hover:bg-blue-50 border border-slate-200 rounded-lg transition flex items-center gap-1 cursor-pointer"
                :title="showCreateHtmlSource ? 'Switch to Visual Document Editor' : 'Switch to Raw HTML Code View'"
              >
                <Icon :icon="showCreateHtmlSource ? 'lucide:eye' : 'lucide:code'" width="12" />
                <span>{{ showCreateHtmlSource ? 'Visual Document View' : 'HTML Code View' }}</span>
              </button>
            </div>
          </div>

          <!-- Visual WYSIWYG Document Editor (Default) -->
          <div v-show="!showCreateHtmlSource" class="rounded-2xl overflow-hidden border border-slate-300 shadow-2xs bg-white">
            <Editor
              v-model="createForm.content_html"
              editorStyle="height: 380px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;"
            />
          </div>

          <!-- HTML Code View (Optional for Power Users) -->
          <div v-show="showCreateHtmlSource">
            <textarea
              v-model="createForm.content_html"
              rows="12"
              placeholder="Type or edit raw HTML content here..."
              class="w-full border border-slate-300 rounded-xl p-3 font-mono text-xs text-slate-800 leading-relaxed focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50"
            ></textarea>
          </div>
        </div>

        <!-- Cascading Institutional Scope Assignment (Civil Service 4-Tier Hierarchy) -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3.5">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200">
            <div>
              <h5 class="text-xs font-bold uppercase text-slate-900 tracking-wider">Institutional Scope & Governance</h5>
              <p class="text-[11px] text-slate-500">Determine whether this template is institutional-wide or private to a department</p>
            </div>
            
            <div class="flex items-center gap-4 text-xs font-semibold">
              <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input
                  type="radio"
                  :value="true"
                  v-model="createForm.is_universal"
                  @change="onScopeTypeChange"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-slate-800">Global (All Departments)</span>
              </label>
              <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input
                  type="radio"
                  :value="false"
                  v-model="createForm.is_universal"
                  @change="onScopeTypeChange"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-slate-800">Department-Specific</span>
              </label>
            </div>
          </div>

          <!-- Cascading Selectors if Department-Specific -->
          <div v-if="!createForm.is_universal" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            <!-- 1. Department -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">1. Department *</label>
              <select
                v-model="createForm.department_id"
                @change="onDepartmentSelect"
                required
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null" disabled>Select Department</option>
                <option v-for="d in props.allDepartmentsStructure" :key="d.id" :value="d.id">
                  {{ d.title }}
                </option>
              </select>
            </div>

            <!-- 2. Division -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 mb-1">2. Division (Optional)</label>
              <select
                v-model="createForm.division_id"
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
                v-model="createForm.unit_id"
                @change="onUnitSelect"
                :disabled="!createForm.division_id || !availableUnits.length"
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
                v-model="createForm.section_id"
                :disabled="!createForm.unit_id || !availableSections.length"
                class="w-full border border-slate-300 rounded-xl px-2.5 py-2 text-xs bg-white font-medium disabled:bg-slate-100 disabled:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="null">-- Whole Unit --</option>
                <option v-for="s in availableSections" :key="s.id" :value="s.id">
                  {{ s.title }}
                </option>
              </select>
            </div>
          </div>

          <!-- Dynamic Scope Path Preview & Automated Reference ID Scheme -->
          <div class="p-3 rounded-xl bg-white border border-slate-200 text-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-2xs">
            <div class="space-y-0.5">
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Automated Document Reference ID</span>
              <p class="font-mono text-xs font-bold text-blue-900 flex items-center gap-1.5">
                <Icon icon="lucide:stamp" width="14" class="text-blue-600" />
                <span>{{ liveCreateRefPreview }}</span>
              </p>
              <p class="text-[10px] text-slate-500">
                Target: <strong class="text-slate-800">{{ createForm.is_universal ? 'Global' : selectedScopePath }}</strong>
              </p>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
              <input
                type="text"
                v-model="createForm.reference_prefix"
                placeholder="Prefix override (optional)"
                class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-mono font-medium focus:ring-1 focus:ring-blue-500 w-full md:w-56"
                title="Optional manual prefix override"
              />

              <button
                type="button"
                @click="insertCreateToken('reference_id')"
                class="shrink-0 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors cursor-pointer"
                title="Insert {{ reference_id }} into template"
              >
                <Icon icon="lucide:copy-plus" width="13" />
                <span>Insert Ref Tag</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Modal Action Buttons -->
        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
          <button
            type="button"
            @click="showCreateTemplateModal = false"
            class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 border border-slate-300 rounded-xl hover:bg-slate-100 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="createForm.processing || isConvertingFile"
            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition flex items-center gap-1.5 disabled:opacity-50"
          >
            <Icon v-if="createForm.processing" icon="lucide:loader-2" class="animate-spin" width="14" />
            <span>{{ createForm.processing ? 'Saving Template...' : 'Save & Scope Template' }}</span>
          </button>
        </div>

      </form>
    </Dialog>

  </main>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from '@iconify/vue';
import Toast from 'primevue/toast';
import Dialog from 'primevue/dialog';
import Editor from 'primevue/editor';
import { useToast } from 'primevue/usetoast';
import { previewFullReferenceId } from '../../utils/referenceNumber.js';

const props = defineProps({
  departments: { type: Array, default: () => [] },
  selectedDepartment: { type: Object, default: () => null },
  activeTier: { type: String, default: 'department' },
  targetNode: { type: Object, default: () => null },
  breadcrumbs: { type: Array, default: () => [] },
  orgTree: { type: Array, default: () => [] },
  systemCapabilities: { type: Array, default: () => [] },
  activeCapabilityCodes: { type: Array, default: () => [] },
  allowedCapabilityCodes: { type: Array, default: () => [] },
  universalTemplates: { type: Array, default: () => [] },
  nodeTemplates: { type: Array, default: () => [] },
  documentTypes: { type: Array, default: () => [] },
  workflows: { type: Array, default: () => [] },
  workflowRules: { type: Array, default: () => [] },
  workingDays: { type: Array, default: () => [] },
  holidays: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
  queries: { type: Array, default: () => [] },
  leaves: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  allDepartmentsStructure: { type: Array, default: () => [] },
});

const toast = useToast();
const selectedDepartmentId = ref(props.selectedDepartment?.id || props.departments?.[0]?.id || '');
const activeTab = ref('capabilities');
const expandAll = ref(false);
const collapsedNodes = ref({});

const showTemplateModal = ref(false);
const selectedTemplate = ref(null);
const showAssignTemplateModal = ref(false);

// Template Creation & File Upload States
const showCreateTemplateModal = ref(false);
const isConvertingFile = ref(false);
const convertError = ref(null);
const fileInput = ref(null);
const extractedPlaceholders = ref([]);
const showCreateHtmlSource = ref(false);

const createForm = useForm({
  title: '',
  document_type_id: null,
  type: 'memo',
  content_html: '',
  is_universal: false,
  department_id: null,
  division_id: null,
  unit_id: null,
  section_id: null,
  reference_prefix: '',
});

const selectedCreateDocType = computed(() => {
  if (!createForm.document_type_id || !props.documentTypes) return null;
  return props.documentTypes.find(dt => dt.id === createForm.document_type_id) || null;
});

const onDocumentTypeSelect = () => {
  if (selectedCreateDocType.value) {
    createForm.type = selectedCreateDocType.value.name;
    if (!createForm.title) {
      createForm.title = selectedCreateDocType.value.name + ' Template';
    }
  }
};

const formatTokenKey = (name) => {
  if (!name) return 'field';
  return name.toLowerCase().replace(/[^a-z0-9]/g, '_');
};

const insertCreateToken = (tokenKey) => {
  const token = `{{ ${tokenKey} }}`;
  if (!createForm.content_html) {
    createForm.content_html = `<p>${token}</p>`;
  } else {
    createForm.content_html += ` ${token}`;
  }
};

const liveCreateRefPreview = computed(() => {
  const dept = createForm.is_universal ? null : props.allDepartmentsStructure?.find(d => d.id === createForm.department_id);
  const div = createForm.is_universal ? null : availableDivisions.value?.find(d => d.id === createForm.division_id);
  const unit = createForm.is_universal ? null : availableUnits.value?.find(u => u.id === createForm.unit_id);
  const sec = createForm.is_universal ? null : availableSections.value?.find(s => s.id === createForm.section_id);

  const docTypeName = selectedCreateDocType.value?.name || createForm.type || 'MEMO';

  return previewFullReferenceId(
    dept?.title,
    div?.title,
    unit?.title,
    sec?.title,
    docTypeName,
    createForm.reference_prefix
  );
});

const assignTemplateForm = useForm({
  template_id: '',
  department_id: computed(() => props.activeTier === 'department' ? props.targetNode?.id : null),
  division_id: computed(() => props.activeTier === 'division' ? props.targetNode?.id : null),
  unit_id: computed(() => props.activeTier === 'unit' ? props.targetNode?.id : null),
  section_id: computed(() => props.activeTier === 'section' ? props.targetNode?.id : null),
  is_universal: false,
});

// Cascading Scope Helpers for Template Creation
const availableDivisions = computed(() => {
  if (!createForm.department_id || !props.allDepartmentsStructure) return [];
  const dept = props.allDepartmentsStructure.find(d => d.id === createForm.department_id);
  return dept?.division || dept?.divisions || [];
});

const availableUnits = computed(() => {
  if (!createForm.division_id) return [];
  const div = availableDivisions.value.find(d => d.id === createForm.division_id);
  return div?.unit || div?.units || [];
});

const availableSections = computed(() => {
  if (!createForm.unit_id) return [];
  const unit = availableUnits.value.find(u => u.id === createForm.unit_id);
  return unit?.sections || unit?.section || [];
});

const selectedScopePath = computed(() => {
  if (createForm.is_universal) return 'Global (Institutional-wide)';
  const dept = props.allDepartmentsStructure.find(d => d.id === createForm.department_id);
  if (!dept) return 'Please select a department';

  let path = dept.title;
  if (createForm.division_id) {
    const div = availableDivisions.value.find(d => d.id === createForm.division_id);
    if (div) path += ' > ' + div.title;
    if (createForm.unit_id) {
      const unit = availableUnits.value.find(u => u.id === createForm.unit_id);
      if (unit) path += ' > ' + unit.title;
      if (createForm.section_id) {
        const sec = availableSections.value.find(s => s.id === createForm.section_id);
        if (sec) path += ' > ' + sec.title;
      }
    }
  } else {
    path += ' (Entire Directorate)';
  }
  return path;
});

const onDepartmentSelect = () => {
  createForm.division_id = null;
  createForm.unit_id = null;
  createForm.section_id = null;
};

const onDivisionSelect = () => {
  createForm.unit_id = null;
  createForm.section_id = null;
};

const onUnitSelect = () => {
  createForm.section_id = null;
};

const onScopeTypeChange = () => {
  if (createForm.is_universal) {
    createForm.department_id = null;
    createForm.division_id = null;
    createForm.unit_id = null;
    createForm.section_id = null;
  } else {
    createForm.department_id = props.selectedDepartment?.id || (props.allDepartmentsStructure?.[0]?.id || null);
  }
};

const openCreateTemplateModal = () => {
  createForm.reset();
  createForm.clearErrors();
  createForm.title = '';
  createForm.type = 'memo';
  createForm.content_html = '';
  createForm.is_universal = false;
  createForm.department_id = props.selectedDepartment?.id || (props.allDepartmentsStructure?.[0]?.id || null);
  createForm.division_id = null;
  createForm.unit_id = null;
  createForm.section_id = null;
  extractedPlaceholders.value = [];
  convertError.value = null;
  showCreateTemplateModal.value = true;
};

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const extension = file.name.split('.').pop().toLowerCase();
  if (!['pdf', 'docx', 'doc'].includes(extension)) {
    toast.add({
      severity: 'error',
      summary: 'Unsupported File',
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
    const res = await axios.post('/dashboard/directorates/convert-template-file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data?.success) {
      const payload = res.data.data || res.data;
      if (!createForm.title || createForm.title.trim() === '') {
        createForm.title = payload.title || res.data.title || file.name.replace(/\.[^/.]+$/, "");
      }
      createForm.content_html = payload.content_html || payload.body_html || res.data.content_html || res.data.body_html || '';
      if (payload.type || res.data.type) {
        createForm.type = payload.type || res.data.type;
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

const copyPlaceholder = (ph) => {
  const token = `{{ ${ph} }}`;
  if (navigator?.clipboard?.writeText) {
    navigator.clipboard.writeText(token);
    toast.add({
      severity: 'info',
      summary: 'Token Copied',
      detail: `Copied ${token} to clipboard.`,
      life: 2500
    });
  }
};

const submitCreateTemplate = () => {
  createForm.post('/dashboard/directorates/create-template', {
    preserveScroll: true,
    onSuccess: () => {
      showCreateTemplateModal.value = false;
      createForm.reset();
      toast.add({
        severity: 'success',
        summary: 'Template Created',
        detail: 'Document template has been successfully created and scoped.',
        life: 3500
      });
    },
    onError: (errors) => {
      toast.add({
        severity: 'error',
        summary: 'Creation Failed',
        detail: errors?.title || errors?.content_html || errors?.department_id || 'Failed to save template. Please check the fields.',
        life: 4500
      });
    }
  });
};

const deleteTemplate = (template) => {
  if (!confirm(`Are you sure you want to permanently delete template "${template.title}"?`)) return;
  router.delete(`/dashboard/directorates/templates/${template.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Template Deleted',
        detail: `Template "${template.title}" was removed.`,
        life: 3000
      });
    },
    onError: (err) => {
      toast.add({
        severity: 'error',
        summary: 'Delete Failed',
        detail: err?.message || 'Could not delete template.',
        life: 4000
      });
    }
  });
};

const toggleCollapse = (nodeKey) => {
  collapsedNodes.value[nodeKey] = !collapsedNodes.value[nodeKey];
};

const isCollapsed = (nodeKey) => {
  if (expandAll.value) return false;
  return !!collapsedNodes.value[nodeKey];
};

const switchDepartment = () => {
  router.get('/dashboard/directorates', { department_id: selectedDepartmentId.value }, { preserveState: false });
};

const selectDepartmentRoot = () => {
  router.get('/dashboard/directorates', { department_id: selectedDepartmentId.value }, { preserveState: true });
};

const selectDivision = (divId) => {
  router.get('/dashboard/directorates', {
    department_id: selectedDepartmentId.value,
    division_id: divId,
  }, { preserveState: true });
};

const selectUnit = (divId, unitId) => {
  router.get('/dashboard/directorates', {
    department_id: selectedDepartmentId.value,
    division_id: divId,
    unit_id: unitId,
  }, { preserveState: true });
};

const selectSection = (divId, unitId, secId) => {
  router.get('/dashboard/directorates', {
    department_id: selectedDepartmentId.value,
    division_id: divId,
    unit_id: unitId,
    section_id: secId,
  }, { preserveState: true });
};

// Capabilities Helpers
const groupedCapabilities = computed(() => {
  const groups = {};
  props.systemCapabilities?.forEach(cap => {
    const cat = cap.category || 'General Administration';
    if (!groups[cat]) groups[cat] = [];
    groups[cat].push(cap);
  });
  return groups;
});

const isCapabilityActive = (code) => {
  return props.activeCapabilityCodes?.includes(code);
};

const isCapabilityAllowed = (code) => {
  return props.allowedCapabilityCodes?.includes(code);
};

const toggleCapability = (code) => {
  const currentlyActive = isCapabilityActive(code);
  const newStatus = !currentlyActive;

  router.post('/dashboard/directorates/toggle-capability', {
    entity_type: props.activeTier,
    entity_id: props.targetNode?.id,
    capability_code: code,
    is_active: newStatus,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: newStatus ? 'success' : 'info',
        summary: newStatus ? 'Function Activated' : 'Function Deactivated',
        detail: `Capability '${code}' is now ${newStatus ? 'enabled' : 'disabled'} for ${props.targetNode?.title}.`,
        life: 2500,
      });
    },
    onError: (errors) => {
      toast.add({
        severity: 'error',
        summary: 'Action Blocked',
        detail: errors?.error || 'Could not update capability.',
        life: 4000,
      });
    }
  });
};

const tabs = computed(() => [
  { id: 'capabilities', name: 'Capabilities & Functions', icon: 'lucide:zap', badge: props.activeCapabilityCodes?.length || 0 },
  { id: 'templates', name: 'Document Templates', icon: 'lucide:file-text', badge: props.nodeTemplates?.length || 0 },
  { id: 'workflows', name: 'Workflows & Routing', icon: 'lucide:git-branch', badge: props.workflows?.length || 0 },
  { id: 'staff', name: 'Staff & Desks', icon: 'lucide:users', badge: props.staff?.length || 0 },
  { id: 'calendar', name: 'Operating Calendar', icon: 'lucide:calendar', badge: props.workingDays?.filter(d => d.is_working_day).length || 0 },
]);

const previewTemplate = (tpl) => {
  selectedTemplate.value = tpl;
  showTemplateModal.value = true;
};

const submitAssignTemplate = () => {
  assignTemplateForm.post('/dashboard/directorates/assign-template', {
    onSuccess: () => {
      showAssignTemplateModal.value = false;
      toast.add({ severity: 'success', summary: 'Template Scoped', detail: 'Template scope successfully assigned.', life: 3000 });
    }
  });
};
</script>
