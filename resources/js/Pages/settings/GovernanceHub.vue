<template>
  <main class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8">
    <Toast />

    <!-- 1. Top Executive Banner & KPI Metrics Ribbon -->
    <div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
      <div>
        <div class="flex flex-wrap items-center gap-2 mb-1.5">
          <span class="px-2.5 py-0.5 text-xs font-bold font-mono uppercase bg-indigo-100 text-indigo-800 rounded-md border border-indigo-200">
            Civil Service Governance Hub
          </span>
          <span class="px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-md border border-emerald-200 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Unified Structural Engine v2.0
          </span>
          <span class="px-2.5 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 rounded-md border border-purple-200">
            28 Modules Synchronized
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          System Governance & Structure Hub
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Unified administration for Organizational Hierarchy, Functional Desks, Spatie Access Matrices, and Document Schemas.
        </p>
      </div>

      <!-- Quick Action Toolbar -->
      <div class="flex flex-wrap items-center gap-2.5">
        <button 
          @click="openOnboardingWizard"
          class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2"
        >
          <Icon icon="lucide:wand-2" width="16" />
          <span>Department Fast-Track Wizard</span>
        </button>

        <button 
          @click="openCreateBranchModal('department')"
          class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:building-2" width="16" class="text-indigo-600" />
          <span>New Dept</span>
        </button>

        <button 
          @click="openCreateDesignationModal()"
          class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:badge-check" width="16" class="text-emerald-600" />
          <span>New Desk</span>
        </button>

        <button 
          @click="showCreatePermissionModal = true"
          class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:key" width="16" class="text-amber-600" />
          <span>New Permission</span>
        </button>

        <Link 
          href="/dashboard/directorates"
          class="px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:sliders" width="16" class="text-blue-600" />
          <span>Departmental Capabilities</span>
        </Link>
      </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 mb-6">
      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs hover:border-indigo-300 transition-colors">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">Hierarchy Nodes</span>
          <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
            <Icon icon="lucide:network" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900">{{ stats.total_branches }}</p>
        <p class="text-xs text-slate-500 mt-0.5 truncate">
          {{ stats.total_departments }} Depts · {{ stats.total_divisions }} Divs · {{ stats.total_units }} Units
        </p>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition-colors">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Functional Desks</span>
          <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
            <Icon icon="lucide:shield-check" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900">{{ stats.total_designations }}</p>
        <p class="text-xs text-slate-500 mt-0.5 truncate">
          {{ stats.total_registry_desks }} Registries · {{ stats.total_heads }} Executive Heads
        </p>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs hover:border-purple-300 transition-colors">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-purple-700">Access Governance</span>
          <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
            <Icon icon="lucide:lock" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900">{{ stats.total_roles }} Roles</p>
        <p class="text-xs text-slate-500 mt-0.5 truncate">
          {{ stats.total_permissions }} Catalog Permissions across 28 Modules
        </p>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs hover:border-amber-300 transition-colors">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Taxonomy Classifications</span>
          <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
            <Icon icon="lucide:layers" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900">{{ (stats.total_document_types || 0) + (stats.total_file_types || 0) }} Types</p>
        <p class="text-xs text-slate-500 mt-0.5 truncate">
          {{ stats.total_document_types || 0 }} Doc Types · {{ stats.total_file_types || 0 }} File Types
        </p>
      </div>
    </div>

    <!-- 2. Main Split-Pane Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      
      <!-- LEFT RAIL: Interactive Organogram & Branch Explorer (4 cols) -->
      <aside class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 sticky top-4">
        <!-- Tree Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-3">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Icon icon="lucide:git-branch" width="16" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900">Civil Service Organogram</h2>
              <p class="text-[11px] text-slate-500">Select branch to scope workspace</p>
            </div>
          </div>
          <button 
            @click="clearBranchScope"
            :class="!activeScope ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all"
            title="View all branches globally"
          >
            Global Scope
          </button>
        </div>

        <!-- Organogram Search Input -->
        <div class="relative mb-3">
          <Icon icon="lucide:search" class="absolute left-3 top-2.5 text-slate-400" width="14" />
          <input 
            v-model="treeSearchQuery"
            placeholder="Search departments, divisions, units..."
            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 bg-slate-50/50"
          />
        </div>

        <!-- Organogram Tree View -->
        <div class="max-h-[620px] overflow-y-auto space-y-1 pr-1 custom-scrollbar">
          
          <!-- Global Scope Option -->
          <div 
            @click="clearBranchScope"
            :class="!activeScope ? 'bg-indigo-50/90 border-indigo-500 text-indigo-900 font-bold' : 'hover:bg-slate-50 text-slate-700 border-transparent'"
            class="p-2.5 rounded-xl border flex items-center justify-between cursor-pointer transition-all text-xs"
          >
            <div class="flex items-center gap-2">
              <Icon icon="lucide:globe" class="text-indigo-600" width="16" />
              <span>All OAGF Headquarters (Global)</span>
            </div>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono">
              {{ departments.length }} Depts
            </span>
          </div>

          <!-- Recursive Departments List -->
          <div v-for="dept in filteredDepartments" :key="dept.id" class="rounded-xl border border-slate-100 overflow-hidden bg-white">
            <!-- Department Node -->
            <div 
              @click="selectBranch('department', dept)"
              :class="activeScope?.type === 'department' && activeScope?.id === dept.id ? 'bg-indigo-50 text-indigo-950 font-bold border-l-4 border-indigo-600' : 'hover:bg-slate-50 text-slate-800'"
              class="p-2.5 flex items-center justify-between cursor-pointer transition-all text-xs"
            >
              <div class="flex items-center gap-2 truncate">
                <button 
                  @click.stop="toggleDeptExpand(dept.id)"
                  class="w-5 h-5 rounded hover:bg-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0"
                >
                  <Icon 
                    icon="lucide:chevron-right" 
                    width="14" 
                    class="transition-transform duration-200"
                    :class="{ 'rotate-90': expandedDepts[dept.id] }"
                  />
                </button>
                <Icon icon="lucide:building-2" class="text-indigo-600 flex-shrink-0" width="15" />
                <span class="truncate" :title="dept.title">{{ dept.title }}</span>
              </div>

              <div class="flex items-center gap-1.5 flex-shrink-0">
                <span v-if="dept.division?.length" class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-mono">
                  {{ dept.division.length }} Div
                </span>
                <button 
                  @click.stop="openCreateBranchModal('division', dept)"
                  class="w-5 h-5 rounded hover:bg-indigo-100 text-indigo-600 flex items-center justify-center"
                  title="Add Division"
                >
                  <Icon icon="lucide:plus" width="12" />
                </button>
              </div>
            </div>

            <!-- Divisions (Expanded) -->
            <div v-show="expandedDepts[dept.id] && dept.division?.length" class="bg-slate-50/60 pl-4 py-1 space-y-1 border-t border-slate-100">
              <div v-for="div in dept.division" :key="div.id" class="border-l-2 border-indigo-200 pl-2">
                <!-- Division Node -->
                <div 
                  @click="selectBranch('division', div, dept)"
                  :class="activeScope?.type === 'division' && activeScope?.id === div.id ? 'bg-indigo-100/70 text-indigo-950 font-bold' : 'hover:bg-white text-slate-700'"
                  class="p-1.5 rounded-lg flex items-center justify-between cursor-pointer transition-all text-xs"
                >
                  <div class="flex items-center gap-1.5 truncate">
                    <button 
                      @click.stop="toggleDivExpand(div.id)"
                      class="w-4 h-4 rounded hover:bg-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0"
                    >
                      <Icon 
                        icon="lucide:chevron-right" 
                        width="12" 
                        class="transition-transform duration-200"
                        :class="{ 'rotate-90': expandedDivs[div.id] }"
                      />
                    </button>
                    <Icon icon="lucide:folder-tree" class="text-blue-500 flex-shrink-0" width="13" />
                    <span class="truncate" :title="div.title">{{ div.title }}</span>
                  </div>

                  <div class="flex items-center gap-1 flex-shrink-0">
                    <span v-if="div.unit?.length" class="text-[9px] px-1 rounded bg-blue-50 text-blue-700 font-mono">
                      {{ div.unit.length }} U
                    </span>
                    <button 
                      @click.stop="openCreateBranchModal('unit', div, dept)"
                      class="w-4 h-4 rounded hover:bg-blue-100 text-blue-600 flex items-center justify-center"
                      title="Add Unit"
                    >
                      <Icon icon="lucide:plus" width="10" />
                    </button>
                  </div>
                </div>

                <!-- Units (Expanded) -->
                <div v-show="expandedDivs[div.id] && div.unit?.length" class="pl-4 py-0.5 space-y-0.5">
                  <div v-for="unit in div.unit" :key="unit.id" class="space-y-0.5">
                    <div 
                      @click="selectBranch('unit', unit, dept, div)"
                      :class="activeScope?.type === 'unit' && activeScope?.id === unit.id ? 'bg-indigo-200/60 text-indigo-950 font-bold' : 'hover:bg-white text-slate-600'"
                      class="p-1 rounded-md flex items-center justify-between cursor-pointer transition-all text-[11px]"
                    >
                      <div class="flex items-center gap-1.5 truncate">
                        <button 
                          v-if="unit.sections?.length"
                          @click.stop="toggleUnitExpand(unit.id)"
                          class="w-3.5 h-3.5 rounded hover:bg-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0"
                        >
                          <Icon 
                            icon="lucide:chevron-right" 
                            width="10" 
                            class="transition-transform duration-200"
                            :class="{ 'rotate-90': expandedUnits[unit.id] }"
                          />
                        </button>
                        <Icon icon="lucide:layers" class="text-emerald-500 flex-shrink-0" width="12" />
                        <span class="truncate" :title="unit.title">{{ unit.title }}</span>
                      </div>
                      <div class="flex items-center gap-1 flex-shrink-0">
                        <span v-if="unit.sections?.length" class="text-[8px] px-1 rounded bg-purple-50 text-purple-700 font-mono">
                          {{ unit.sections.length }} S
                        </span>
                        <span v-if="unit.users?.length" class="text-[9px] text-slate-400 font-mono">
                          {{ unit.users.length }}
                        </span>
                      </div>
                    </div>

                    <!-- Sections (Expanded) -->
                    <div v-show="expandedUnits[unit.id] && unit.sections?.length" class="pl-4 space-y-0.5">
                      <div 
                        v-for="sec in unit.sections" 
                        :key="sec.id"
                        @click="selectBranch('section', sec, dept, div, unit)"
                        :class="activeScope?.type === 'section' && activeScope?.id === sec.id ? 'bg-purple-100 text-purple-950 font-bold' : 'hover:bg-white text-slate-500'"
                        class="p-0.5 px-1.5 rounded flex items-center justify-between cursor-pointer transition-all text-[10px]"
                      >
                        <div class="flex items-center gap-1 truncate">
                          <Icon icon="lucide:git-commit" class="text-purple-500 flex-shrink-0" width="10" />
                          <span class="truncate" :title="sec.title">{{ sec.title }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- RIGHT WORKSPACE: Context-Aware Tabbed Command Center (8 cols) -->
      <section class="lg:col-span-8 space-y-5">
        
        <!-- Active Scope Context Ribbon -->
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-5 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 text-[10px] font-bold font-mono uppercase bg-white/10 text-white rounded border border-white/20">
                  {{ activeScope ? activeScope.type.toUpperCase() + ' SCOPE' : 'HEADQUARTERS GLOBAL SCOPE' }}
                </span>
                <span v-if="activeScope" class="text-xs text-indigo-300">
                  {{ activeScopeBreadcrumb }}
                </span>
              </div>
              <h2 class="text-xl font-black tracking-tight flex items-center gap-2">
                <Icon :icon="activeScopeIcon" width="22" class="text-indigo-400" />
                <span>{{ activeScope ? activeScope.title : 'All OAGF Headquarters & Federal Branches' }}</span>
              </h2>
              <p class="text-xs text-slate-300 mt-1">
                {{ activeScope?.description || 'Global civil service administration workspace across all operational arms and governance catalogs.' }}
              </p>
            </div>

            <!-- Scope Metrics Pill -->
            <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 backdrop-blur-xs">
              <div class="text-center">
                <p class="text-xs text-indigo-200">Scoped Desks</p>
                <p class="text-lg font-black text-white">{{ scopedDesignations.length }}</p>
              </div>
              <div class="w-px h-8 bg-white/10"></div>
              <div class="text-center">
                <p class="text-xs text-indigo-200">Staff Assigned</p>
                <p class="text-lg font-black text-white">{{ scopedStaffCount }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Workspace Tab Navigation -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
          <div class="border-b border-slate-100 bg-slate-50/50 px-4 pt-3 flex flex-wrap items-center gap-2">
            <button 
              v-for="tab in tabs" 
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="activeTab === tab.id 
                ? 'bg-white text-indigo-700 font-bold border-b-2 border-indigo-600 shadow-2xs' 
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60 font-medium'"
              class="px-4 py-2.5 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition-all relative -mb-px"
            >
              <Icon :icon="tab.icon" width="16" :class="activeTab === tab.id ? 'text-indigo-600' : 'text-slate-400'" />
              <span>{{ tab.name }}</span>
              <span 
                v-if="tab.badge !== undefined" 
                :class="activeTab === tab.id ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600'"
                class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded-full"
              >
                {{ tab.badge }}
              </span>
            </button>
          </div>

          <!-- TAB CONTENT PANES -->
          <div class="p-5">

            <!-- ========================================== -->
            <!-- TAB 1: Hierarchy & Branch Structure        -->
            <!-- ========================================== -->
            <div v-if="activeTab === 'branches'" class="space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900">
                    {{ activeScope ? activeScope.title + ' Sub-Structures' : 'Departmental Branches & Executive Divisions' }}
                  </h3>
                  <p class="text-xs text-slate-500">
                    Manage office hierarchy, administrative descriptions, and structural heads.
                  </p>
                </div>
                <button 
                  @click="openAddChildBranchModal"
                  class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 self-start sm:self-auto"
                >
                  <Icon icon="lucide:plus" width="15" />
                  <span>{{ activeScope ? 'Add Sub-Office' : 'Add Department' }}</span>
                </button>
              </div>

              <!-- Children / Sister Nodes Table -->
              <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse text-xs">
                  <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-[11px]">
                    <tr>
                      <th class="py-3 px-4">Office / Branch Title</th>
                      <th class="py-3 px-4">Level</th>
                      <th class="py-3 px-4">Sub-Branches</th>
                      <th class="py-3 px-4">Staff Count</th>
                      <th class="py-3 px-4">Desks</th>
                      <th class="py-3 px-4">Classifications</th>
                      <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-for="branch in currentLevelBranches" :key="branch.id" class="hover:bg-slate-50/80 transition-colors">
                      <td class="py-3 px-4">
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                          <Icon :icon="getBranchIcon(branch.level)" class="text-indigo-600" width="15" />
                          <span>{{ branch.title }}</span>
                        </div>
                        <p v-if="branch.description" class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                          {{ branch.description }}
                        </p>
                      </td>
                      <td class="py-3 px-4">
                        <span :class="getLevelBadgeClass(branch.level)" class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase">
                          {{ branch.level }}
                        </span>
                      </td>
                      <td class="py-3 px-4 font-mono">
                        {{ branch.childrenCount || 0 }}
                      </td>
                      <td class="py-3 px-4 font-mono">
                        {{ branch.usersCount || 0 }} staff
                      </td>
                      <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono text-[10px]">
                          {{ branch.designationsCount || 0 }} desks
                        </span>
                      </td>
                      <td class="py-3 px-4">
                        <button 
                          @click="openBranchClassificationsModal(branch)"
                          class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-[10px] font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs hover:scale-102"
                          title="Assign Authorized Document & File Classifications"
                        >
                          <Icon icon="lucide:layers" width="12" class="text-amber-600" />
                          <span>{{ branch.documentTypesCount || 0 }} Docs · {{ branch.fileTypesCount || 0 }} Files</span>
                          <Icon icon="lucide:sliders-horizontal" width="10" class="text-amber-600/70" />
                        </button>
                      </td>
                      <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                          <button 
                            @click="selectBranch(branch.level, branch)"
                            class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-bold text-[11px] transition-colors cursor-pointer"
                          >
                            Explore
                          </button>
                          <button 
                            @click="openBranchClassificationsModal(branch)"
                            class="p-1 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition-colors cursor-pointer"
                            title="Assign Office Classifications (Docs & Files)"
                          >
                            <Icon icon="lucide:layers" width="14" />
                          </button>
                          <button 
                            @click="openEditBranchModal(branch)"
                            class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                            title="Edit Title / Description"
                          >
                            <Icon icon="lucide:pencil" width="14" />
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="currentLevelBranches.length === 0">
                      <td colspan="6" class="py-8 text-center text-slate-400">
                        No sub-branches created under this node yet. Click "Add Sub-Office" above.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: Functional Desks & Designations     -->
            <!-- ========================================== -->
            <div v-if="activeTab === 'designations'" class="space-y-6">
              
              <!-- Tab 2 Top Toolbar & Filters -->
              <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pb-2 border-b border-slate-100">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <Icon icon="lucide:badge-check" class="text-emerald-600" width="18" />
                    <span>{{ activeScope ? activeScope.title + ' Functional Desks' : 'Civil Service Functional Desks & Offices' }}</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">
                    Categorized by Ministry Departments, specialized registries, executive heads, and common cadres.
                  </p>
                </div>

                <!-- Right Action Bar & View Mode Toggle -->
                <div class="flex flex-wrap items-center gap-2.5">
                  <!-- View Switcher -->
                  <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border border-slate-200">
                    <button 
                      @click="deskViewMode = 'grouped'"
                      :class="deskViewMode === 'grouped' ? 'bg-white text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                      class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5"
                      title="Group desks by Department"
                    >
                      <Icon icon="lucide:layers" width="13" />
                      <span>By Dept</span>
                    </button>
                    <button 
                      @click="deskViewMode = 'flat'"
                      :class="deskViewMode === 'flat' ? 'bg-white text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                      class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5"
                      title="Flat list of all desks"
                    >
                      <Icon icon="lucide:list" width="13" />
                      <span>Flat List</span>
                    </button>
                  </div>

                  <!-- Create Designation Button -->
                  <button 
                    @click="openCreateDesignationModal()"
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5"
                  >
                    <Icon icon="lucide:plus" width="15" />
                    <span>Create Designation</span>
                  </button>
                </div>
              </div>

              <!-- Search & Department Filter Bar -->
              <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 bg-slate-50/70 p-3 rounded-2xl border border-slate-200">
                <div class="sm:col-span-7 relative">
                  <Icon icon="lucide:search" class="absolute left-3 top-2.5 text-slate-400" width="14" />
                  <input 
                    v-model="deskSearchQuery"
                    placeholder="Search desks by title, cadre, grade level, or department..."
                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:border-indigo-600 focus:ring-indigo-600/20 bg-white"
                  />
                </div>

                <div class="sm:col-span-5 flex items-center gap-2">
                  <select 
                    v-model="selectedDeptFilter"
                    class="w-full text-xs rounded-xl border border-slate-300 py-1.5 px-3 focus:border-indigo-600 bg-white"
                  >
                    <option value="">All Departments</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.title }}</option>
                  </select>

                  <button 
                    v-if="deskSearchQuery || selectedDeptFilter" 
                    @click="deskSearchQuery = ''; selectedDeptFilter = ''"
                    class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1 flex-shrink-0"
                  >
                    Reset
                  </button>
                </div>
              </div>

              <!-- ============================================== -->
              <!-- VIEW MODE 1: CATEGORIZED BY DEPARTMENT         -->
              <!-- ============================================== -->
              <div v-if="deskViewMode === 'grouped'" class="space-y-5">
                
                <!-- SECTION A: GLOBAL / COMMON CIVIL SERVICE CADRE -->
                <div 
                  v-if="categorizedDepartmentDesks.globalDesks.length > 0"
                  class="border border-indigo-100 rounded-2xl overflow-hidden bg-white shadow-2xs"
                >
                  <div class="p-4 bg-gradient-to-r from-indigo-50/90 to-slate-50 border-b border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                        <Icon icon="lucide:globe" width="18" />
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <h4 class="text-sm font-black text-indigo-950">Global & Common Civil Service Cadre</h4>
                          <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-bold font-mono">
                            {{ categorizedDepartmentDesks.globalDesks.length }} Shared Desks
                          </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                          Standard cross-cutting civil service positions deployable across any operational branch or directorate.
                        </p>
                      </div>
                    </div>

                    <button 
                      @click="openCreateDesignationModal(null)"
                      class="px-3 py-1.5 text-[11px] font-bold text-indigo-700 bg-white hover:bg-indigo-50 border border-indigo-200 rounded-xl transition-all flex items-center gap-1.5 self-start sm:self-auto shadow-2xs"
                    >
                      <Icon icon="lucide:plus" width="13" />
                      <span>Add Global Cadre</span>
                    </button>
                  </div>

                  <!-- Global Desks Table -->
                  <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                      <thead class="bg-slate-50/70 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-100 text-[10px]">
                        <tr>
                          <th class="py-2.5 px-4">Designation Title</th>
                          <th class="py-2.5 px-4">Cadre & GL</th>
                          <th class="py-2.5 px-4">Desk Specialty</th>
                          <th class="py-2.5 px-4">Staff Count</th>
                          <th class="py-2.5 px-4 text-center">Status</th>
                          <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr v-for="desk in categorizedDepartmentDesks.globalDesks" :key="desk.id" class="hover:bg-indigo-50/30 transition-colors">
                          <td class="py-2.5 px-4">
                            <p class="font-bold text-slate-900">{{ desk.title }}</p>
                            <p v-if="desk.description" class="text-[10px] text-slate-400 line-clamp-1">{{ desk.description }}</p>
                          </td>
                          <td class="py-2.5 px-4">
                            <span class="font-semibold text-slate-800">{{ desk.cadre || 'General Duties' }}</span>
                            <span v-if="desk.grade_level" class="ml-1 px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-mono text-[10px]">
                              GL {{ desk.grade_level }}
                            </span>
                          </td>
                          <td class="py-2.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">
                              Universal Pool
                            </span>
                          </td>
                          <td class="py-2.5 px-4 font-mono">
                            {{ desk.users_count || 0 }} staff
                          </td>
                          <td class="py-2.5 px-4 text-center">
                            <button 
                              @click="toggleDesignationStatus(desk)"
                              :class="desk.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                              class="px-2 py-0.5 rounded-full text-[10px] font-bold transition-all"
                            >
                              {{ desk.is_active ? 'Active' : 'Disabled' }}
                            </button>
                          </td>
                          <td class="py-2.5 px-4 text-right">
                            <button 
                              @click="openEditDesignationModal(desk)"
                              class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                            >
                              <Icon icon="lucide:pencil" width="13" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- SECTION B: DEPARTMENTAL SPECIALIZED DESKS ACCORDIONS -->
                <div 
                  v-for="group in categorizedDepartmentDesks.departmentGroups" 
                  :key="group.department.id"
                  class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs transition-all hover:border-slate-300"
                >
                  <!-- Department Accordion Header -->
                  <div 
                    @click="toggleDeptDesksCollapse(group.department.id)"
                    class="p-4 bg-slate-50/90 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 cursor-pointer select-none"
                  >
                    <div class="flex items-center gap-3">
                      <button 
                        type="button" 
                        class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shadow-2xs"
                      >
                        <Icon 
                          icon="lucide:chevron-down" 
                          width="15" 
                          class="transition-transform duration-200"
                          :class="{ '-rotate-90': collapsedDepts[group.department.id] }"
                        />
                      </button>
                      <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                        <Icon icon="lucide:building-2" width="16" />
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <h4 class="text-sm font-bold text-slate-900">{{ group.department.title }}</h4>
                          <span class="px-2 py-0.2 rounded-full bg-slate-200/80 text-slate-700 text-[10px] font-bold font-mono">
                            {{ group.desks.length }} Desks
                          </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">
                          {{ group.department.description || 'Specialized directorate operational desks and functional workflows.' }}
                        </p>
                      </div>
                    </div>

                    <!-- Department Health Status & Actions -->
                    <div class="flex flex-wrap items-center gap-2 pl-9 md:pl-0">
                      <!-- Registry Status Badge -->
                      <span 
                        v-if="group.registryDesk"
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"
                        :title="group.registryDesk.title"
                      >
                        <Icon icon="lucide:shield-check" width="12" />
                        <span>Registry Active</span>
                      </span>
                      <span 
                        v-else 
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1"
                        title="Department lacks a dedicated registry officer desk for inbound/outbound files"
                      >
                        <Icon icon="lucide:alert-triangle" width="12" />
                        <span>No Registry Desk</span>
                      </span>

                      <!-- Executive Head Status Badge -->
                      <span 
                        v-if="group.headDesk"
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 flex items-center gap-1"
                        :title="group.headDesk.title"
                      >
                        <Icon icon="lucide:crown" width="12" />
                        <span>Director Headed</span>
                      </span>
                      <span 
                        v-else 
                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 flex items-center gap-1"
                      >
                        <Icon icon="lucide:user-x" width="11" />
                        <span>No Head Desk</span>
                      </span>

                      <!-- Add Desk to this Department Button -->
                      <button 
                        @click.stop="openCreateDesignationModal(group.department)"
                        class="px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1 transition-colors"
                      >
                        <Icon icon="lucide:plus" width="12" />
                        <span>Add Desk</span>
                      </button>
                    </div>
                  </div>

                  <!-- Department Desks Table (Expanded) -->
                  <div v-show="!collapsedDepts[group.department.id]" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                      <thead class="bg-slate-50/50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-100 text-[10px]">
                        <tr>
                          <th class="py-2.5 px-4">Functional Role Title</th>
                          <th class="py-2.5 px-4">Cadre & GL</th>
                          <th class="py-2.5 px-4">Sub-Branch Scope</th>
                          <th class="py-2.5 px-4">Desk Specialty</th>
                          <th class="py-2.5 px-4">Staff Count</th>
                          <th class="py-2.5 px-4 text-center">Status</th>
                          <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr v-for="desk in group.desks" :key="desk.id" class="hover:bg-slate-50/80 transition-colors">
                          <td class="py-2.5 px-4">
                            <p class="font-bold text-slate-900">{{ desk.title }}</p>
                            <p v-if="desk.description" class="text-[10px] text-slate-400 line-clamp-1">{{ desk.description }}</p>
                          </td>
                          <td class="py-2.5 px-4">
                            <span class="font-semibold text-slate-800">{{ desk.cadre || 'Departmental' }}</span>
                            <span v-if="desk.grade_level" class="ml-1 px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-mono text-[10px]">
                              GL {{ desk.grade_level }}
                            </span>
                          </td>
                          <td class="py-2.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-slate-100 text-slate-700">
                              {{ desk.division?.title || desk.unit?.title || 'Dept-Wide' }}
                            </span>
                          </td>
                          <td class="py-2.5 px-4">
                            <div class="flex flex-wrap gap-1">
                              <span v-if="desk.is_registry" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                                <Icon icon="lucide:shield" width="10" /> Registry Desk
                              </span>
                              <span v-if="desk.is_head_of_office" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1">
                                <Icon icon="lucide:crown" width="10" /> Head of Office
                              </span>
                              <span v-if="!desk.is_registry && !desk.is_head_of_office" class="text-slate-400 text-[10px]">
                                Operational Desk
                              </span>
                            </div>
                          </td>
                          <td class="py-2.5 px-4 font-mono">
                            {{ desk.users_count || 0 }} staff
                          </td>
                          <td class="py-2.5 px-4 text-center">
                            <button 
                              @click="toggleDesignationStatus(desk)"
                              :class="desk.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                              class="px-2 py-0.5 rounded-full text-[10px] font-bold transition-all"
                            >
                              {{ desk.is_active ? 'Active' : 'Disabled' }}
                            </button>
                          </td>
                          <td class="py-2.5 px-4 text-right">
                            <button 
                              @click="openEditDesignationModal(desk)"
                              class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                            >
                              <Icon icon="lucide:pencil" width="13" />
                            </button>
                          </td>
                        </tr>
                        <tr v-if="group.desks.length === 0">
                          <td colspan="7" class="py-6 text-center text-slate-400">
                            No specialized desks configured for {{ group.department.title }} yet. Click "Add Desk" to create one.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Empty State -->
                <div v-if="categorizedDepartmentDesks.departmentGroups.length === 0 && categorizedDepartmentDesks.globalDesks.length === 0" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                  <Icon icon="lucide:search-x" class="mx-auto mb-2 text-slate-300" width="32" />
                  <p class="font-bold text-slate-700">No matching functional desks found.</p>
                  <p class="text-xs text-slate-400 mt-1">Try resetting search filters or create a new designation above.</p>
                </div>
              </div>

              <!-- ============================================== -->
              <!-- VIEW MODE 2: FLAT SEARCHABLE TABLE             -->
              <!-- ============================================== -->
              <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-2xs">
                <table class="w-full text-left border-collapse text-xs">
                  <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200 text-[11px]">
                    <tr>
                      <th class="py-3 px-4">Designation Title</th>
                      <th class="py-3 px-4">Cadre & GL</th>
                      <th class="py-3 px-4">Assigned Department</th>
                      <th class="py-3 px-4">Special Desk Flags</th>
                      <th class="py-3 px-4">Staff Count</th>
                      <th class="py-3 px-4 text-center">Status</th>
                      <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-for="item in filteredFlatDesignations" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                      <td class="py-3 px-4">
                        <p class="font-bold text-slate-900">{{ item.title }}</p>
                        <p v-if="item.description" class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                          {{ item.description }}
                        </p>
                      </td>
                      <td class="py-3 px-4">
                        <span class="font-semibold text-slate-800">{{ item.cadre || 'General' }}</span>
                        <span v-if="item.grade_level" class="ml-1 px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-mono text-[10px]">
                          GL {{ item.grade_level }}
                        </span>
                      </td>
                      <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-slate-100 text-slate-700">
                          {{ item.department?.title || 'Global Cadre' }}
                        </span>
                      </td>
                      <td class="py-3 px-4">
                        <div class="flex flex-wrap gap-1">
                          <span v-if="item.is_registry" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                            <Icon icon="lucide:shield" width="10" /> Registry Desk
                          </span>
                          <span v-if="item.is_head_of_office" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1">
                            <Icon icon="lucide:crown" width="10" /> Head of Office
                          </span>
                          <span v-if="!item.is_registry && !item.is_head_of_office" class="text-slate-400 text-[10px]">
                            Standard Operational
                          </span>
                        </div>
                      </td>
                      <td class="py-3 px-4 font-mono">
                        {{ item.users_count || 0 }} staff
                      </td>
                      <td class="py-3 px-4 text-center">
                        <button 
                          @click="toggleDesignationStatus(item)"
                          :class="item.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                          class="px-2 py-0.5 rounded-full text-[10px] font-bold transition-all"
                        >
                          {{ item.is_active ? 'Active' : 'Disabled' }}
                        </button>
                      </td>
                      <td class="py-3 px-4 text-right">
                        <button 
                          @click="openEditDesignationModal(item)"
                          class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                        >
                          <Icon icon="lucide:pencil" width="14" />
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredFlatDesignations.length === 0">
                      <td colspan="7" class="py-8 text-center text-slate-400">
                        No designations found matching current filters.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- ========================================== -->
            <!-- ========================================== -->
            <!-- TAB 3: Civil Service User Permissions Hub  -->
            <!-- ========================================== -->
            <div v-if="activeTab === 'access'" class="space-y-6">
              
              <!-- Header Ribbon -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0">
                    <Icon icon="lucide:user-check" width="22" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="px-2 py-0.5 text-[10px] font-bold font-mono uppercase bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-700">
                        Capability Bounded
                      </span>
                      <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Civil Service User Permissions Matrix
                      </h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                      Assign granular module permissions to individual officers, strictly bounded by the active departmental capabilities in Organizational Capabilities & Function Activation.
                    </p>
                  </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                  <button 
                    @click="showCreatePermissionModal = true"
                    class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5"
                  >
                    <Icon icon="lucide:plus" width="14" />
                    <span>Custom Permission</span>
                  </button>
                  <button 
                    @click="savePermissionsMatrix"
                    :disabled="isSavingPermissions || !selectedUser"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                  >
                    <Icon icon="lucide:check" width="15" />
                    <span>{{ isSavingPermissions ? 'Saving...' : 'Save User Permissions' }}</span>
                  </button>
                </div>
              </div>

              <!-- Cascading Filter & Staff Officer Selection Bar -->
              <div class="p-4 bg-slate-50/80 dark:bg-slate-850/60 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                  <!-- Department Filter -->
                  <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                      Filter by Department
                    </label>
                    <select 
                      v-model="selectedDeptId" 
                      @change="selectedDivisionId = ''"
                      class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    >
                      <option v-for="d in departments" :key="d.id" :value="d.id">
                        {{ d.title }} ({{ d.user_count || d.user?.length || 0 }} Staff)
                      </option>
                    </select>
                  </div>

                  <!-- Division Filter (Optional) -->
                  <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                      Division (Optional)
                    </label>
                    <select 
                      v-model="selectedDivisionId"
                      class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    >
                      <option value="">All Divisions</option>
                      <option v-for="div in availableDivisions" :key="div.id" :value="div.id">
                        {{ div.title }}
                      </option>
                    </select>
                  </div>

                  <!-- Staff Officer Selector -->
                  <div class="md:col-span-5">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                      Target Staff Officer ({{ filteredUsers.length }} Available)
                    </label>
                    <select 
                      v-model="selectedUserId"
                      class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    >
                      <option v-if="filteredUsers.length === 0" disabled value="">No staff found in this placement</option>
                      <option v-for="u in filteredUsers" :key="u.id" :value="u.id">
                        {{ u.first_name }} {{ u.last_name }} &mdash; {{ u.designation?.title || 'Officer' }} ({{ u.email }})
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Quick Name Search Bar -->
                <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60 dark:border-slate-750">
                  <Icon icon="lucide:search" width="14" class="text-slate-400" />
                  <input 
                    type="text" 
                    v-model="userSearchQuery" 
                    placeholder="Quick search staff by name, email, or designation..."
                    class="bg-transparent border-0 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-0 w-full"
                  />
                  <button 
                    v-if="userSearchQuery" 
                    @click="userSearchQuery = ''" 
                    class="text-xs text-slate-400 hover:text-slate-600 cursor-pointer"
                  >
                    Clear
                  </button>
                </div>
              </div>

              <!-- Selected Officer Context & Capability Bounding Envelope -->
              <div v-if="selectedUser" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                  <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-base flex items-center justify-center shrink-0 shadow-xs">
                      {{ selectedUser.first_name ? selectedUser.first_name[0] : '' }}{{ selectedUser.last_name ? selectedUser.last_name[0] : '' }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">
                          {{ selectedUser.first_name }} {{ selectedUser.last_name }}
                        </h4>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                          {{ selectedUser.designation?.title || 'Civil Service Officer' }}
                        </span>
                        <span v-for="r in (selectedUser.roles || [])" :key="r.id" class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full">
                          {{ r.name }}
                        </span>
                      </div>
                      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ selectedUser.email }}
                      </p>
                    </div>
                  </div>

                  <!-- Placement Path Breadcrumb & Capabilities Count -->
                  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                    <div class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                      <span class="text-[10px] uppercase font-bold text-slate-400 block sm:inline mr-1">Placement:</span>
                      <span class="font-bold text-slate-700 dark:text-slate-300">{{ selectedUser.department?.title || 'No Dept' }}</span>
                      <span v-if="selectedUser.division" class="text-slate-400 mx-1">&rarr;</span>
                      <span v-if="selectedUser.division" class="text-slate-600 dark:text-slate-400">{{ selectedUser.division.title }}</span>
                      <span v-if="selectedUser.unit" class="text-slate-400 mx-1">&rarr;</span>
                      <span v-if="selectedUser.unit" class="text-slate-600 dark:text-slate-400">{{ selectedUser.unit.title }}</span>
                    </div>

                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-1.5">
                      <Icon icon="lucide:shield-check" width="14" />
                      <span>{{ Object.keys(boundedGroupedPermissions).length }} Active Modules</span>
                    </div>
                  </div>
                </div>

                <!-- Bounding Envelope Information Notice -->
                <div class="p-3 bg-indigo-50/70 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/60 flex items-start gap-2.5">
                  <Icon icon="lucide:info" class="text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" width="16" />
                  <p class="text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
                    Showing permissions dynamically bounded by <strong class="font-bold">{{ selectedUser.department?.title || 'Department' }}'s</strong> active capabilities configured in <strong class="font-bold">Organizational Capabilities & Function Activation</strong>. Modules not activated for this department (such as Financial Management for Administration) are automatically omitted and cannot be assigned.
                  </p>
                </div>
              </div>

              <!-- Module Permissions Matrix Grid (Strictly Bounded by Active Capabilities) -->
              <div v-if="selectedUser && Object.keys(boundedGroupedPermissions).length > 0" class="space-y-4">
                <div 
                  v-for="(perms, moduleName) in boundedGroupedPermissions" 
                  :key="moduleName"
                  class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800"
                >
                  <div class="p-3.5 bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                      <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide">{{ moduleName }}</h4>
                      <span class="text-[11px] text-slate-500 font-mono">({{ perms.length }} permissions)</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <button 
                        @click="toggleAllInModule(perms, true)" 
                        class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-bold px-1.5 py-0.5 rounded hover:bg-indigo-50 dark:hover:bg-slate-700 cursor-pointer"
                      >
                        Select All
                      </button>
                      <span class="text-slate-300 dark:text-slate-600">|</span>
                      <button 
                        @click="toggleAllInModule(perms, false)" 
                        class="text-[11px] text-slate-500 hover:text-slate-700 font-medium px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer"
                      >
                        Clear
                      </button>
                    </div>
                  </div>

                  <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <label 
                      v-for="p in perms" 
                      :key="p.id"
                      class="flex items-start gap-2.5 p-2 rounded-lg border border-slate-100 dark:border-slate-750 hover:bg-slate-50/80 dark:hover:bg-slate-750/50 cursor-pointer transition-colors text-xs"
                    >
                      <input 
                        type="checkbox" 
                        :value="p.name"
                        v-model="activeUserPermissions"
                        class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <div>
                        <p class="font-bold text-slate-900 dark:text-slate-200 font-mono text-[11px]">{{ p.name }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">{{ p.description }}</p>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Empty State if no staff or no active modules -->
              <div v-else-if="!selectedUser" class="p-10 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <Icon icon="lucide:user-x" class="mx-auto text-slate-300 dark:text-slate-600 mb-2" width="36" />
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No Staff Member Selected</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Please select an officer from the department above to configure their permissions.</p>
              </div>

              <div v-else class="p-10 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <Icon icon="lucide:lock" class="mx-auto text-amber-400 mb-2" width="36" />
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No Active Capabilities Configured</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  This user's placement has no active modules enabled in Organizational Capabilities & Function Activation.
                </p>
              </div>

              <!-- Permission Clearance Simulator Section -->
              <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white">
                  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <div>
                      <div class="flex items-center gap-2 mb-1">
                        <Icon icon="lucide:shield-alert" class="text-amber-400" width="18" />
                        <h4 class="text-sm font-bold uppercase tracking-wider text-indigo-200">
                          Live Permission Clearance Simulator
                        </h4>
                      </div>
                      <p class="text-xs text-slate-300">
                        Inspect effective clearance across all 28 modules for any selected staff officer.
                      </p>
                    </div>

                    <div class="w-full md:w-72">
                      <select 
                        v-model="simulatedUserId"
                        class="w-full text-xs rounded-xl bg-white/10 border-white/20 text-white py-2 px-3 focus:bg-slate-900"
                      >
                        <option value="" class="text-slate-900">Select Staff to Simulate...</option>
                        <option v-for="u in users" :key="u.id" :value="u.id" class="text-slate-900">
                          {{ u.first_name }} {{ u.last_name }} ({{ u.designation?.title || 'No desk' }})
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Simulator Results Ribbon -->
                  <div v-if="simulatedUser" class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-white/10 text-xs">
                    <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                      <p class="text-[10px] text-indigo-300 uppercase">Assigned Desk</p>
                      <p class="font-bold text-white truncate">{{ simulatedUser.designation?.title || 'None' }}</p>
                      <p class="text-[10px] text-slate-400 mt-0.5">GL {{ simulatedUser.designation?.grade_level || 'N/A' }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                      <p class="text-[10px] text-indigo-300 uppercase">Security Roles</p>
                      <p class="font-bold text-white truncate">{{ simulatedUser.roles?.map(r => r.name).join(', ') || 'None' }}</p>
                      <p class="text-[10px] text-slate-400 mt-0.5">{{ simulatedUser.roles?.length || 0 }} role profiles</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                      <p class="text-[10px] text-indigo-300 uppercase">Registry Authority</p>
                      <p class="font-bold" :class="simulatedUser.designation?.is_registry ? 'text-emerald-400' : 'text-slate-400'">
                        {{ simulatedUser.designation?.is_registry ? 'Authorized Desk' : 'Standard File Access' }}
                      </p>
                      <p class="text-[10px] text-slate-400 mt-0.5">Cross-dept dispatch</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                      <p class="text-[10px] text-indigo-300 uppercase">Executive Approval</p>
                      <p class="font-bold" :class="simulatedUser.designation?.is_head_of_office ? 'text-purple-400' : 'text-slate-400'">
                        {{ simulatedUser.designation?.is_head_of_office ? 'Head of Office Clearance' : 'Operational Officer' }}
                      </p>
                      <p class="text-[10px] text-slate-400 mt-0.5">Workflow sign-off</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: Document Taxonomy & Metadata Schemas-->
            <!-- ========================================== -->
            <!-- ========================================== -->
            <!-- TAB 4: Document & File Taxonomy Management -->
            <!-- ========================================== -->
            <div v-if="activeTab === 'metadata'" class="space-y-6">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900">Document & File Taxonomy Management</h3>
                  <p class="text-xs text-slate-500">
                    Configure document classifications, folder docket jackets, schema properties, and authorized branch scopes.
                  </p>
                </div>
                <div class="flex items-center gap-2">
                  <template v-if="activeTaxonomyTab === 'docs'">
                    <button 
                      @click="showCreateMetadataModal = true"
                      class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
                    >
                      <Icon icon="lucide:plus" width="15" />
                      <span>New Metadata Field</span>
                    </button>
                    <button 
                      @click="showCreateDocTypeModal = true"
                      class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
                    >
                      <Icon icon="lucide:file-plus" width="15" />
                      <span>New Document Type</span>
                    </button>
                  </template>
                  <template v-else>
                    <button 
                      @click="openCreateFileTypeModal"
                      class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
                    >
                      <Icon icon="lucide:folder-plus" width="15" />
                      <span>New File Type</span>
                    </button>
                  </template>
                </div>
              </div>

              <!-- Sub-tab Pills Switcher -->
              <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl w-fit">
                <button 
                  type="button" 
                  @click="activeTaxonomyTab = 'docs'"
                  :class="activeTaxonomyTab === 'docs' ? 'bg-white text-indigo-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                  class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
                >
                  <Icon icon="lucide:file-text" width="14" />
                  <span>Document Types & Schemas ({{ documentTypes?.length || 0 }})</span>
                </button>
                <button 
                  type="button" 
                  @click="activeTaxonomyTab = 'files'"
                  :class="activeTaxonomyTab === 'files' ? 'bg-white text-amber-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                  class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
                >
                  <Icon icon="lucide:folder-archive" width="14" />
                  <span>File & Docket Types ({{ fileTypes?.length || 0 }})</span>
                </button>
              </div>

              <!-- SUB-TAB A: Document Types & Schemas -->
              <div v-if="activeTaxonomyTab === 'docs'" class="space-y-6">
                <!-- Document Types & Attached Schemas Grid -->
                <div>
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                      <Icon icon="lucide:file-text" class="text-indigo-600" width="16" />
                      Document Types ({{ documentTypes?.length || 0 }})
                    </h4>
                  </div>

                  <div v-if="documentTypes?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                      v-for="docType in documentTypes" 
                      :key="docType.id"
                      class="p-4 rounded-xl border border-slate-200 bg-white hover:border-indigo-300 transition-colors"
                    >
                      <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <Icon icon="lucide:file-text" width="18" />
                          </div>
                          <div>
                            <h4 class="text-xs font-bold text-slate-900">{{ docType.name }}</h4>
                            <p class="text-[10px] text-slate-400">Created {{ docType.created_at ? new Date(docType.created_at).toLocaleDateString() : 'System' }}</p>
                          </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                          <button 
                            @click="configureDocTypeMetadata(docType)"
                            class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg border border-indigo-200 transition-colors cursor-pointer"
                          >
                            Configure Schema
                          </button>
                          <button 
                            @click="deleteDocumentType(docType)"
                            class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                            title="Delete Document Type"
                          >
                            <Icon icon="lucide:trash-2" width="14" />
                          </button>
                        </div>
                      </div>

                      <!-- Attached Metadata Badges -->
                      <div class="mt-3 pt-3 border-t border-slate-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                          Attached Schema Fields ({{ docType.metadata?.length || 0 }})
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                          <span 
                            v-for="m in docType.metadata" 
                            :key="m.id"
                            class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-[10px] border border-slate-200"
                          >
                            {{ m.name }}
                          </span>
                          <span v-if="!docType.metadata?.length" class="text-[11px] text-slate-400 italic">
                            No custom metadata fields bound.
                          </span>
                        </div>
                      </div>

                      <!-- Authorized Administrative Scope (Departments, Divisions, Units, Sections) -->
                      <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Authorized Offices ({{ getDocTypeTotalBranches(docType) }})
                          </p>
                          <div class="flex flex-wrap items-center gap-1.5">
                            <span v-if="getAuthorizedDeptsForDocType(docType).length" class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">
                              {{ getAuthorizedDeptsForDocType(docType).length }} Dept{{ getAuthorizedDeptsForDocType(docType).length > 1 ? 's' : '' }}
                            </span>
                            <span v-if="getAuthorizedDivsForDocType(docType).length" class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">
                              {{ getAuthorizedDivsForDocType(docType).length }} Div{{ getAuthorizedDivsForDocType(docType).length > 1 ? 's' : '' }}
                            </span>
                            <span v-if="getAuthorizedUnitsForDocType(docType).length" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                              {{ getAuthorizedUnitsForDocType(docType).length }} Unit{{ getAuthorizedUnitsForDocType(docType).length > 1 ? 's' : '' }}
                            </span>
                            <span v-if="getAuthorizedSecsForDocType(docType).length" class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold">
                              {{ getAuthorizedSecsForDocType(docType).length }} Sec{{ getAuthorizedSecsForDocType(docType).length > 1 ? 's' : '' }}
                            </span>
                            <span v-if="!getDocTypeTotalBranches(docType)" class="text-[11px] text-slate-400 italic">
                              Unassigned (Not scoped to any branch)
                            </span>
                          </div>
                        </div>

                        <button 
                          @click="openDocTypeBranchesModal(docType)"
                          class="px-2.5 py-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition-colors flex items-center gap-1 cursor-pointer self-start sm:self-auto shrink-0 shadow-2xs"
                        >
                          <Icon icon="lucide:network" width="13" />
                          <span>Assign Scope</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-else class="p-8 text-center rounded-xl border border-dashed border-slate-200 bg-white">
                    <Icon icon="lucide:file-question" class="mx-auto text-slate-300 mb-2" width="32" />
                    <p class="text-xs font-bold text-slate-700">No Document Types Defined</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Create a document type to start attaching metadata schemas and workflow routes.</p>
                    <button 
                      @click="showCreateDocTypeModal = true"
                      class="mt-3 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg cursor-pointer"
                    >
                      + Create Document Type
                    </button>
                  </div>
                </div>

                <!-- Section 2: Global Metadata Fields Catalog -->
                <div class="pt-6 border-t border-slate-200">
                  <div class="flex items-center justify-between mb-3">
                    <div>
                      <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <Icon icon="lucide:tags" class="text-amber-600" width="16" />
                        Global Metadata Fields Catalog ({{ metadatas?.length || 0 }})
                      </h4>
                      <p class="text-[11px] text-slate-500">
                        Standard property definitions available for binding to any document classification.
                      </p>
                    </div>
                    <button 
                      @click="showCreateMetadataModal = true"
                      class="text-xs text-amber-700 hover:text-amber-800 font-bold flex items-center gap-1 hover:underline cursor-pointer"
                    >
                      <Icon icon="lucide:plus-circle" width="14" />
                      <span>Add Field</span>
                    </button>
                  </div>

                  <!-- Metadata Grid -->
                  <div v-if="metadatas?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div 
                      v-for="meta in metadatas" 
                      :key="meta.id"
                      class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-amber-300 transition-all flex flex-col justify-between"
                    >
                      <div>
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                          <span class="text-xs font-bold text-slate-900 truncate" :title="meta.name">
                            {{ meta.name }}
                          </span>
                          <span 
                            :class="meta.is_default ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                            class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border shrink-0"
                          >
                            {{ meta.is_default ? 'Default' : 'Custom' }}
                          </span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mb-2">
                          <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-700 font-mono text-[10px]">
                            {{ meta.string || 'Text' }}
                          </span>
                          <span>Field #{{ meta.id }}</span>
                        </div>
                      </div>

                      <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                        <button 
                          v-if="!meta.is_default"
                          @click="makeMetadataDefault(meta)"
                          class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline flex items-center gap-1 cursor-pointer"
                          title="Set as default for all new document types"
                        >
                          <Icon icon="lucide:check-circle-2" width="13" />
                          <span>Make Default</span>
                        </button>
                        <span v-else class="text-emerald-600 font-semibold flex items-center gap-1">
                          <Icon icon="lucide:shield-check" width="13" />
                          <span>Global Default</span>
                        </span>

                        <button 
                          @click="deleteMetadataField(meta)"
                          class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded-md transition-colors cursor-pointer"
                          title="Delete Metadata Field"
                        >
                          <Icon icon="lucide:trash-2" width="13" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-else class="p-6 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                    <Icon icon="lucide:tags" class="mx-auto text-slate-300 mb-2" width="24" />
                    <p class="text-xs text-slate-500 font-medium">No metadata fields defined yet.</p>
                    <button 
                      @click="showCreateMetadataModal = true"
                      class="mt-2 text-xs font-bold text-amber-600 hover:underline cursor-pointer"
                    >
                      + Define first metadata field
                    </button>
                  </div>
                </div>
              </div>

              <!-- SUB-TAB B: File & Docket Types -->
              <div v-if="activeTaxonomyTab === 'files'" class="space-y-4">
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                      <Icon icon="lucide:folder-archive" class="text-amber-600" width="16" />
                      File & Docket Types ({{ fileTypes?.length || 0 }})
                    </h4>
                    <p class="text-[11px] text-slate-500">
                      Standard folder jackets, required metadata columns, and branch authorization scopes.
                    </p>
                  </div>
                  <button 
                    @click="openCreateFileTypeModal"
                    class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
                  >
                    <Icon icon="lucide:plus" width="14" />
                    <span>New File Type</span>
                  </button>
                </div>

                <div v-if="fileTypes?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div 
                    v-for="ft in fileTypes" 
                    :key="ft.id"
                    class="p-4 rounded-xl border border-slate-200 bg-white hover:border-amber-300 transition-colors"
                  >
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                          <Icon icon="lucide:folder-archive" width="18" />
                        </div>
                        <div>
                          <h4 class="text-xs font-bold text-slate-900">{{ ft.name }}</h4>
                          <p class="text-[10px] text-slate-400">
                            {{ ft.created_at ? 'Created ' + new Date(ft.created_at).toLocaleDateString() : 'System Docket' }}
                          </p>
                        </div>
                      </div>

                      <div class="flex items-center gap-1.5">
                        <button 
                          @click="openEditFileTypeModal(ft)"
                          class="px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors cursor-pointer"
                        >
                          Edit
                        </button>
                        <button 
                          @click="deleteFileType(ft)"
                          class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                          title="Delete File Type"
                        >
                          <Icon icon="lucide:trash-2" width="14" />
                        </button>
                      </div>
                    </div>

                    <p v-if="ft.description" class="text-xs text-slate-500 mb-3 line-clamp-2">
                      {{ ft.description }}
                    </p>

                    <!-- Custom Folder Jacket Headings Pills -->
                    <div class="mt-3 pt-3 border-t border-slate-100">
                      <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                        Folder Jacket Headings ({{ ft.headings?.length || 0 }})
                      </p>
                      <div class="flex flex-wrap gap-1.5">
                        <span 
                          v-for="(h, idx) in (ft.headings || [])" 
                          :key="idx"
                          class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 font-mono text-[10px] border border-amber-200"
                        >
                          {{ h }}
                        </span>
                        <span v-if="!ft.headings?.length" class="text-[11px] text-slate-400 italic">
                          Standard folder jacket columns.
                        </span>
                      </div>
                    </div>

                    <!-- Authorized Administrative Scope (Departments, Divisions, Units, Sections) -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                      <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                          Authorized Offices ({{ getFileTypeTotalBranches(ft) }})
                        </p>
                        <div class="flex flex-wrap items-center gap-1.5">
                          <span v-if="getAuthorizedDeptsForFileType(ft).length" class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">
                            {{ getAuthorizedDeptsForFileType(ft).length }} Dept{{ getAuthorizedDeptsForFileType(ft).length > 1 ? 's' : '' }}
                          </span>
                          <span v-if="getAuthorizedDivsForFileType(ft).length" class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">
                            {{ getAuthorizedDivsForFileType(ft).length }} Div{{ getAuthorizedDivsForFileType(ft).length > 1 ? 's' : '' }}
                          </span>
                          <span v-if="getAuthorizedUnitsForFileType(ft).length" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                            {{ getAuthorizedUnitsForFileType(ft).length }} Unit{{ getAuthorizedUnitsForFileType(ft).length > 1 ? 's' : '' }}
                          </span>
                          <span v-if="getAuthorizedSecsForFileType(ft).length" class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold">
                            {{ getAuthorizedSecsForFileType(ft).length }} Sec{{ getAuthorizedSecsForFileType(ft).length > 1 ? 's' : '' }}
                          </span>
                          <span v-if="!getFileTypeTotalBranches(ft)" class="text-[11px] text-slate-400 italic">
                            Unassigned (Not scoped to any branch)
                          </span>
                        </div>
                      </div>

                      <button 
                        @click="openFileTypeBranchesModal(ft)"
                        class="px-2.5 py-1 text-[11px] font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors flex items-center gap-1 cursor-pointer self-start sm:self-auto shrink-0 shadow-2xs"
                      >
                        <Icon icon="lucide:network" width="13" />
                        <span>Assign Scope</span>
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else class="p-8 text-center rounded-xl border border-dashed border-slate-200 bg-white">
                  <Icon icon="lucide:folder-search" class="mx-auto text-slate-300 mb-2" width="32" />
                  <p class="text-xs font-bold text-slate-700">No File Docket Types Defined</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Create a file type to specify standard folder jackets and authorized registry branches.</p>
                  <button 
                    @click="openCreateFileTypeModal"
                    class="mt-3 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg cursor-pointer"
                  >
                    + Create File Type
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: Department Fast-Track Onboarding Wizard         -->
    <!-- ======================================================== -->
    <Dialog v-model:visible="showOnboardingWizard" modal :closable="false" :style="{ width: '46rem' }" :breakpoints="{ '1199px': '80vw', '575px': '95vw' }">
      <div class="p-6">
        <!-- Wizard Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Icon icon="lucide:wand-2" width="22" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900">Fast-Track Department Onboarding Wizard</h3>
              <p class="text-xs text-slate-500">Step {{ wizardStep }} of 3: {{ wizardStepTitle }}</p>
            </div>
          </div>
          <button @click="showOnboardingWizard = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <!-- Wizard Step Indicators -->
        <div class="grid grid-cols-3 gap-2 mb-6">
          <div :class="wizardStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500'" class="py-1.5 px-3 rounded-lg text-xs font-bold text-center transition-all">
            1. Department Info
          </div>
          <div :class="wizardStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500'" class="py-1.5 px-3 rounded-lg text-xs font-bold text-center transition-all">
            2. Divisions & Units
          </div>
          <div :class="wizardStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500'" class="py-1.5 px-3 rounded-lg text-xs font-bold text-center transition-all">
            3. Key Desks & Finalize
          </div>
        </div>

        <!-- STEP 1: Department Info -->
        <div v-if="wizardStep === 1" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department Title <span class="text-rose-500">*</span></label>
            <input 
              v-model="wizardForm.title" 
              required 
              placeholder="e.g. Information and Communications Technology Department"
              class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mandate / Description</label>
            <textarea 
              v-model="wizardForm.description" 
              rows="3" 
              placeholder="Specify the official civil service mandate and functions of this department..."
              class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Appoint Head of Department (Director)</label>
            <select 
              v-model="wizardForm.head_user_id"
              class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20"
            >
              <option :value="null">-- Appoint Director Later --</option>
              <option v-for="u in users" :key="u.id" :value="u.id">
                {{ u.first_name }} {{ u.last_name }} ({{ u.email }})
              </option>
            </select>
          </div>
        </div>

        <!-- STEP 2: Sub-Divisions & Units -->
        <div v-if="wizardStep === 2" class="space-y-4">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-slate-700 uppercase">Operational Divisions</label>
            <button 
              @click="addWizardDivision" 
              type="button" 
              class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
            >
              <Icon icon="lucide:plus" width="14" /> Add Division
            </button>
          </div>

          <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
            <div 
              v-for="(div, dIndex) in wizardForm.divisions" 
              :key="dIndex"
              class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5"
            >
              <div class="flex items-center justify-between gap-2">
                <input 
                  v-model="div.title" 
                  placeholder="Division Title (e.g. Software Systems Division)" 
                  class="flex-1 text-xs rounded-lg border border-slate-300 py-1.5 px-3 focus:border-indigo-600"
                />
                <button 
                  v-if="wizardForm.divisions.length > 1" 
                  @click="removeWizardDivision(dIndex)" 
                  class="text-rose-500 hover:text-rose-700 p-1"
                >
                  <Icon icon="lucide:trash-2" width="14" />
                </button>
              </div>

              <!-- Units in this division -->
              <div class="pl-3 border-l-2 border-indigo-200 space-y-1.5">
                <p class="text-[10px] font-bold uppercase text-slate-500">Units in this division</p>
                <div v-for="(u, uIndex) in div.units" :key="uIndex" class="flex items-center gap-2">
                  <input 
                    v-model="div.units[uIndex]" 
                    placeholder="Unit Title (e.g. Database Management Unit)" 
                    class="flex-1 text-xs rounded-lg border border-slate-300 py-1 px-2.5 focus:border-indigo-600 bg-white"
                  />
                  <button @click="div.units.splice(uIndex, 1)" class="text-slate-400 hover:text-rose-500">
                    <Icon icon="lucide:x" width="12" />
                  </button>
                </div>
                <button 
                  @click="div.units.push('')" 
                  type="button" 
                  class="text-[11px] text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1"
                >
                  <Icon icon="lucide:plus" width="12" /> Add Unit
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 3: Key Desks & Finalize -->
        <div v-if="wizardStep === 3" class="space-y-4">
          <div class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/60 space-y-3">
            <h4 class="text-xs font-bold text-indigo-900 uppercase">Automated Civil Service Functional Desks</h4>
            
            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-800">
              <input type="checkbox" v-model="wizardForm.include_director_desk" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span>Auto-create <strong>Director / Accounting Head Desk (GL 17)</strong></span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-800">
              <input type="checkbox" v-model="wizardForm.include_registry_desk" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span>Auto-create <strong>Departmental Registry Officer Desk (GL 10)</strong></span>
            </label>

            <p class="text-[11px] text-slate-500">
              Divisional Deputy Director desks (GL 16) and Unit Head desks (GL 14) will be generated automatically for all divisions created in Step 2.
            </p>
          </div>

          <!-- Document Types to Bind -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Bind Standard Document Types</label>
            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 border border-slate-200 rounded-xl">
              <label 
                v-for="dt in documentTypes" 
                :key="dt.id" 
                class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer p-1 rounded hover:bg-slate-50"
              >
                <input type="checkbox" :value="dt.id" v-model="wizardForm.document_type_ids" class="rounded text-indigo-600 focus:ring-indigo-500" />
                <span class="truncate">{{ dt.name }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Wizard Navigation Footer -->
        <div class="flex items-center justify-between pt-5 border-t border-slate-100 mt-6">
          <button 
            v-if="wizardStep > 1" 
            @click="wizardStep--" 
            type="button" 
            class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl"
          >
            Back
          </button>
          <div v-else></div>

          <button 
            v-if="wizardStep < 3" 
            @click="advanceWizardStep" 
            type="button" 
            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs"
          >
            Continue
          </button>
          <button 
            v-else 
            @click="submitWizard" 
            :disabled="isSubmittingWizard"
            type="button" 
            class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-2 disabled:opacity-50"
          >
            <Icon icon="lucide:check" width="16" />
            <span>{{ isSubmittingWizard ? 'Provisioning...' : 'Provision Department' }}</span>
          </button>
        </div>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 2: Create / Edit Branch Modal                      -->
    <!-- ======================================================== -->
    <Dialog v-model:visible="showBranchModal" modal :closable="false" :style="{ width: '32rem' }">
      <div class="p-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
          <h3 class="text-base font-bold text-slate-900">
            {{ isEditingBranch ? 'Edit Branch' : 'Create New ' + branchModalLevel.toUpperCase() }}
          </h3>
          <button @click="showBranchModal = false" class="text-slate-400 hover:text-slate-600 p-1">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitBranchForm" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Title <span class="text-rose-500">*</span></label>
            <input 
              v-model="branchForm.title" 
              required 
              :placeholder="'e.g. ' + branchModalLevel + ' name'" 
              class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:border-indigo-600"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
            <textarea 
              v-model="branchForm.description" 
              rows="2" 
              placeholder="Operational scope or notes..." 
              class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:border-indigo-600"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3">
            <button @click="showBranchModal = false" type="button" class="px-4 py-2 border rounded-xl text-xs font-bold">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">Save</button>
          </div>
        </form>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 3: Create / Edit Designation Modal                 -->
    <!-- ======================================================== -->
    <Dialog v-model:visible="showDesignationModal" modal :closable="false" :style="{ width: '36rem' }">
      <div class="p-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
          <h3 class="text-base font-bold text-slate-900">
            {{ isEditingDesignation ? 'Edit Designation' : 'Create Functional Designation' }}
          </h3>
          <button @click="showDesignationModal = false" class="text-slate-400 hover:text-slate-600 p-1">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitDesignationForm" class="space-y-3.5">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Title <span class="text-rose-500">*</span></label>
            <input 
              v-model="designationForm.title" 
              required 
              placeholder="e.g. Checking Officer, Auditor, Registry Officer" 
              class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 focus:border-indigo-600"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cadre</label>
              <input 
                v-model="designationForm.cadre" 
                placeholder="e.g. Accountant, Executive" 
                class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 focus:border-indigo-600"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Grade Level (0-17)</label>
              <input 
                v-model.number="designationForm.grade_level" 
                type="number" 
                min="0" 
                max="17" 
                class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 focus:border-indigo-600"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assigned Department <span class="text-rose-500">*</span></label>
            <select 
              v-model="designationForm.department_id" 
              required
              class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 focus:border-indigo-600"
            >
              <option :value="null">-- Select Target Department --</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.title }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hierarchy Placement Scope</label>
            <select 
              v-model="designationForm.hierarchy_level"
              class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 focus:border-indigo-600"
            >
              <option value="department">Department-Wide Desk</option>
              <option value="division">Division-Level Desk</option>
              <option value="unit">Unit-Level Desk</option>
            </select>
          </div>

          <div class="flex items-center gap-4 pt-1">
            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input type="checkbox" v-model="designationForm.is_registry" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span>Registry Desk</span>
            </label>

            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input type="checkbox" v-model="designationForm.is_head_of_office" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span>Head of Office</span>
            </label>
          </div>

          <div class="flex justify-end gap-2 pt-3">
            <button @click="showDesignationModal = false" type="button" class="px-4 py-2 border rounded-xl text-xs font-bold">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold">Save Designation</button>
          </div>
        </form>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 4: Create Custom Permission Modal                  -->
    <!-- ======================================================== -->
    <CreatePermissionModal 
      v-model="showCreatePermissionModal"
      :visible="showCreatePermissionModal" 
      :existingModules="Object.keys(masterCatalog || groupedPermissions || {})"
      :allPermissions="Object.values(groupedPermissions || {}).flat()"
      @close="showCreatePermissionModal = false" 
      @created="onPermissionCreated"
    />

    <!-- ======================================================== -->
    <!-- MODAL 5: Create Metadata Field Modal                     -->
    <!-- ======================================================== -->
    <CreateMetadataDialog v-model="showCreateMetadataModal" />

    <!-- ======================================================== -->
    <!-- MODAL 6: Create Document Type Modal                      -->
    <!-- ======================================================== -->
    <CreateDocumentTypeDialog v-model="showCreateDocTypeModal" />

    <!-- ======================================================== -->
    <!-- MODAL 7: Configure Document Type Metadata Schema Modal   -->
    <!-- ======================================================== -->
    <ConfigureMetadataModal 
      v-model="showConfigureMetadataModal" 
      :doctype="selectedDocTypeForConfig"
      @selectedOptions="onSelectedMetadataOptions" 
    />

    <!-- ======================================================== -->
    <!-- ======================================================== -->
    <!-- MODAL 8: Unified Office Classifications Modal (Docs & Files) -->
    <!-- ======================================================== -->
    <Dialog 
      v-model:visible="showBranchClassificationsModal" 
      modal 
      :style="{ width: '42rem' }" 
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <Icon icon="lucide:layers" width="20" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-base font-bold text-slate-900">Assign Office Classifications</h3>
              <span :class="getLevelBadgeClass(selectedBranchForClassifications?.level)" class="px-2 py-0.2 rounded text-[10px] font-bold uppercase font-mono">
                {{ selectedBranchForClassifications?.level }}
              </span>
            </div>
            <p class="text-xs text-slate-500 truncate max-w-sm">
              {{ selectedBranchForClassifications?.title }}
            </p>
          </div>
        </div>
      </template>

      <div class="space-y-4 pt-2">
        <p class="text-xs text-slate-500">
          Authorize document types and folder docket types permitted for processing, routing, and filing in this office.
        </p>

        <!-- Sub-tabs for Classifications Modal -->
        <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl w-fit">
          <button 
            type="button" 
            @click="branchClassModalTab = 'docs'"
            :class="branchClassModalTab === 'docs' ? 'bg-white text-indigo-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
          >
            <Icon icon="lucide:file-text" width="14" />
            <span>Document Types ({{ selectedBranchDocTypeIds.length }}/{{ documentTypes.length }})</span>
          </button>
          <button 
            type="button" 
            @click="branchClassModalTab = 'files'"
            :class="branchClassModalTab === 'files' ? 'bg-white text-amber-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
          >
            <Icon icon="lucide:folder-archive" width="14" />
            <span>File Types / Dockets ({{ selectedBranchFileTypeIds.length }}/{{ fileTypes.length }})</span>
          </button>
        </div>

        <!-- TAB 1: Document Types Checklist -->
        <div v-if="branchClassModalTab === 'docs'" class="space-y-3">
          <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1">
              <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" />
              <input 
                v-model="branchDocTypeSearchQuery"
                type="text" 
                placeholder="Search document types..."
                class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
              />
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <button 
                type="button"
                @click="selectAllDocTypesForBranch"
                class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg border border-indigo-200 cursor-pointer"
              >
                Select All
              </button>
              <button 
                type="button"
                @click="selectedBranchDocTypeIds = []"
                class="px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200 cursor-pointer"
              >
                Clear
              </button>
            </div>
          </div>

          <div class="border border-slate-200 rounded-xl max-h-72 overflow-y-auto divide-y divide-slate-100 custom-scrollbar p-1">
            <label 
              v-for="dt in filteredDocTypesForBranch" 
              :key="dt.id"
              class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
            >
              <input 
                type="checkbox"
                :value="Number(dt.id)"
                v-model="selectedBranchDocTypeIds"
                class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-900 truncate">{{ dt.name }}</span>
                  <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-mono">
                    {{ dt.metadata?.length || 0 }} fields
                  </span>
                </div>
                <p v-if="dt.description" class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                  {{ dt.description }}
                </p>
              </div>
            </label>

            <div v-if="filteredDocTypesForBranch.length === 0" class="p-6 text-center text-xs text-slate-400">
              No matching document types found.
            </div>
          </div>
        </div>

        <!-- TAB 2: File Types / Dockets Checklist -->
        <div v-if="branchClassModalTab === 'files'" class="space-y-3">
          <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1">
              <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" />
              <input 
                v-model="branchFileTypeSearchQuery"
                type="text" 
                placeholder="Search file types / folder dockets..."
                class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
              />
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <button 
                type="button"
                @click="selectAllFileTypesForBranch"
                class="px-2.5 py-1 text-[11px] font-bold text-amber-600 hover:bg-amber-50 rounded-lg border border-amber-200 cursor-pointer"
              >
                Select All
              </button>
              <button 
                type="button"
                @click="selectedBranchFileTypeIds = []"
                class="px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200 cursor-pointer"
              >
                Clear
              </button>
            </div>
          </div>

          <div class="border border-slate-200 rounded-xl max-h-72 overflow-y-auto divide-y divide-slate-100 custom-scrollbar p-1">
            <label 
              v-for="ft in filteredFileTypesForBranch" 
              :key="ft.id"
              class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
            >
              <input 
                type="checkbox"
                :value="Number(ft.id)"
                v-model="selectedBranchFileTypeIds"
                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 cursor-pointer"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-900 truncate">{{ ft.name }}</span>
                  <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-mono">
                    {{ ft.headings?.length || 0 }} headings
                  </span>
                </div>
                <p v-if="ft.description" class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                  {{ ft.description }}
                </p>
              </div>
            </label>

            <div v-if="filteredFileTypesForBranch.length === 0" class="p-6 text-center text-xs text-slate-400">
              No matching file docket types found.
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <span class="text-xs text-slate-500 font-medium font-mono">
            {{ selectedBranchDocTypeIds.length }} Docs · {{ selectedBranchFileTypeIds.length }} Files selected
          </span>
          <div class="flex gap-2">
            <button 
              type="button" 
              @click="showBranchClassificationsModal = false"
              class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="saveBranchClassifications"
              :disabled="isSavingBranchClassifications"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="isSavingBranchClassifications" icon="lucide:loader-2" class="animate-spin" width="14" />
              <span>Save Classifications</span>
            </button>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 9: Assign Document Type across Structure Modal     -->
    <!-- ======================================================== -->
    <Dialog 
      v-model:visible="showDocTypeBranchesModal" 
      modal 
      :style="{ width: '44rem' }" 
      :breakpoints="{ '1199px': '80vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <Icon icon="lucide:network" width="20" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">Assign Document Type to Administrative Structure</h3>
            <p class="text-xs text-indigo-600 font-bold truncate max-w-md">
              {{ selectedDocTypeForBranches?.name }}
            </p>
          </div>
        </div>
      </template>

      <div class="space-y-4 pt-2">
        <p class="text-xs text-slate-500">
          Select the Departments, Divisions, Units, and Sections authorized to handle and process this document classification.
        </p>

        <!-- Search & Bulk Selection -->
        <div class="flex items-center justify-between gap-3">
          <div class="relative flex-1">
            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" />
            <input 
              v-model="docTypeBranchSearchQuery"
              type="text" 
              placeholder="Search branches across hierarchy..."
              class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
            />
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button 
              type="button"
              @click="selectAllBranchesForDocType"
              class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg border border-indigo-200 cursor-pointer"
            >
              Select All
            </button>
            <button 
              type="button"
              @click="clearAllBranchesForDocType"
              class="px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200 cursor-pointer"
            >
              Clear
            </button>
          </div>
        </div>

        <!-- 4-Tier Tree of Branches -->
        <div class="border border-slate-200 rounded-xl max-h-80 overflow-y-auto divide-y divide-slate-100 custom-scrollbar p-2 space-y-2">
          <div v-for="dept in filteredDepartmentsForDocTypeModal" :key="'modal-dept-' + dept.id" class="p-2.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
            <!-- Department Row -->
            <label class="flex items-center gap-2 cursor-pointer font-bold text-xs text-slate-900">
              <input 
                type="checkbox"
                :value="Number(dept.id)"
                v-model="selectedDocTypeDeptIds"
                class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
              />
              <Icon icon="lucide:building-2" class="text-indigo-600" width="14" />
              <span>{{ dept.title }}</span>
              <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 font-mono">Dept</span>
            </label>

            <!-- Divisions & Units -->
            <div v-if="dept.division?.length" class="pl-6 pt-2 space-y-2 border-l-2 border-indigo-200 ml-2 mt-1">
              <div v-for="div in dept.division" :key="'modal-div-' + div.id" class="space-y-1">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                  <input 
                    type="checkbox"
                    :value="Number(div.id)"
                    v-model="selectedDocTypeDivIds"
                    class="rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                  />
                  <Icon icon="lucide:folder-tree" class="text-blue-500" width="13" />
                  <span>{{ div.title }}</span>
                  <span class="text-[9px] px-1 py-0.2 rounded bg-blue-100 text-blue-800 font-mono">Div</span>
                </label>

                <!-- Units -->
                <div v-if="div.unit?.length" class="pl-5 space-y-1 border-l border-blue-200 ml-2">
                  <div v-for="u in div.unit" :key="'modal-unit-' + u.id" class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-[11px] font-medium text-slate-700">
                      <input 
                        type="checkbox"
                        :value="Number(u.id)"
                        v-model="selectedDocTypeUnitIds"
                        class="rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                      />
                      <Icon icon="lucide:layers" class="text-emerald-500" width="12" />
                      <span>{{ u.title }}</span>
                      <span class="text-[8px] px-1 py-0.2 rounded bg-emerald-100 text-emerald-800 font-mono">Unit</span>
                    </label>

                    <!-- Sections -->
                    <div v-if="u.sections?.length" class="pl-5 space-y-1 border-l border-emerald-200 ml-2">
                      <label 
                        v-for="s in u.sections" 
                        :key="'modal-sec-' + s.id"
                        class="flex items-center gap-2 cursor-pointer text-[10px] text-slate-600"
                      >
                        <input 
                          type="checkbox"
                          :value="Number(s.id)"
                          v-model="selectedDocTypeSecIds"
                          class="rounded text-purple-600 focus:ring-purple-500 cursor-pointer"
                        />
                        <Icon icon="lucide:git-commit" class="text-purple-500" width="10" />
                        <span>{{ s.title }}</span>
                        <span class="text-[8px] px-1 py-0.2 rounded bg-purple-100 text-purple-800 font-mono">Section</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="filteredDepartmentsForDocTypeModal.length === 0" class="p-6 text-center text-xs text-slate-400">
            No matching administrative branches found.
          </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <span class="text-xs text-slate-500 font-mono">
            Selected: {{ selectedDocTypeDeptIds.length }} Depts, {{ selectedDocTypeDivIds.length }} Divs, {{ selectedDocTypeUnitIds.length }} Units, {{ selectedDocTypeSecIds.length }} Secs
          </span>
          <div class="flex gap-2">
            <button 
              type="button" 
              @click="showDocTypeBranchesModal = false"
              class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="saveDocTypeBranches"
              :disabled="isSavingDocTypeBranches"
              class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="isSavingDocTypeBranches" icon="lucide:loader-2" class="animate-spin" width="14" />
              <span>Save Scope</span>
            </button>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 10: Assign File Type across Structure Modal        -->
    <!-- ======================================================== -->
    <Dialog 
      v-model:visible="showFileTypeBranchesModal" 
      modal 
      :style="{ width: '44rem' }" 
      :breakpoints="{ '1199px': '80vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <Icon icon="lucide:network" width="20" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">Assign File Type to Administrative Structure</h3>
            <p class="text-xs text-amber-700 font-bold truncate max-w-md">
              {{ selectedFileTypeForBranches?.name }}
            </p>
          </div>
        </div>
      </template>

      <div class="space-y-4 pt-2">
        <p class="text-xs text-slate-500">
          Select the Departments, Divisions, Units, and Sections authorized to create and maintain this folder docket classification.
        </p>

        <!-- Search & Bulk Selection -->
        <div class="flex items-center justify-between gap-3">
          <div class="relative flex-1">
            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" />
            <input 
              v-model="fileTypeBranchSearchQuery"
              type="text" 
              placeholder="Search branches across hierarchy..."
              class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
            />
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button 
              type="button"
              @click="selectAllBranchesForFileType"
              class="px-2.5 py-1 text-[11px] font-bold text-amber-700 hover:bg-amber-50 rounded-lg border border-amber-200 cursor-pointer"
            >
              Select All
            </button>
            <button 
              type="button"
              @click="clearAllBranchesForFileType"
              class="px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-200 cursor-pointer"
            >
              Clear
            </button>
          </div>
        </div>

        <!-- 4-Tier Tree of Branches -->
        <div class="border border-slate-200 rounded-xl max-h-80 overflow-y-auto divide-y divide-slate-100 custom-scrollbar p-2 space-y-2">
          <div v-for="dept in filteredDepartmentsForFileTypeModal" :key="'modal-ft-dept-' + dept.id" class="p-2.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
            <!-- Department Row -->
            <label class="flex items-center gap-2 cursor-pointer font-bold text-xs text-slate-900">
              <input 
                type="checkbox"
                :value="Number(dept.id)"
                v-model="selectedFileTypeDeptIds"
                class="rounded text-amber-600 focus:ring-amber-500 cursor-pointer"
              />
              <Icon icon="lucide:building-2" class="text-indigo-600" width="14" />
              <span>{{ dept.title }}</span>
              <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 font-mono">Dept</span>
            </label>

            <!-- Divisions & Units -->
            <div v-if="dept.division?.length" class="pl-6 pt-2 space-y-2 border-l-2 border-amber-200 ml-2 mt-1">
              <div v-for="div in dept.division" :key="'modal-ft-div-' + div.id" class="space-y-1">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                  <input 
                    type="checkbox"
                    :value="Number(div.id)"
                    v-model="selectedFileTypeDivIds"
                    class="rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                  />
                  <Icon icon="lucide:folder-tree" class="text-blue-500" width="13" />
                  <span>{{ div.title }}</span>
                  <span class="text-[9px] px-1 py-0.2 rounded bg-blue-100 text-blue-800 font-mono">Div</span>
                </label>

                <!-- Units -->
                <div v-if="div.unit?.length" class="pl-5 space-y-1 border-l border-blue-200 ml-2">
                  <div v-for="u in div.unit" :key="'modal-ft-unit-' + u.id" class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-[11px] font-medium text-slate-700">
                      <input 
                        type="checkbox"
                        :value="Number(u.id)"
                        v-model="selectedFileTypeUnitIds"
                        class="rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                      />
                      <Icon icon="lucide:layers" class="text-emerald-500" width="12" />
                      <span>{{ u.title }}</span>
                      <span class="text-[8px] px-1 py-0.2 rounded bg-emerald-100 text-emerald-800 font-mono">Unit</span>
                    </label>

                    <!-- Sections -->
                    <div v-if="u.sections?.length" class="pl-5 space-y-1 border-l border-emerald-200 ml-2">
                      <label 
                        v-for="s in u.sections" 
                        :key="'modal-ft-sec-' + s.id"
                        class="flex items-center gap-2 cursor-pointer text-[10px] text-slate-600"
                      >
                        <input 
                          type="checkbox"
                          :value="Number(s.id)"
                          v-model="selectedFileTypeSecIds"
                          class="rounded text-purple-600 focus:ring-purple-500 cursor-pointer"
                        />
                        <Icon icon="lucide:git-commit" class="text-purple-500" width="10" />
                        <span>{{ s.title }}</span>
                        <span class="text-[8px] px-1 py-0.2 rounded bg-purple-100 text-purple-800 font-mono">Section</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="filteredDepartmentsForFileTypeModal.length === 0" class="p-6 text-center text-xs text-slate-400">
            No matching administrative branches found.
          </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <span class="text-xs text-slate-500 font-mono">
            Selected: {{ selectedFileTypeDeptIds.length }} Depts, {{ selectedFileTypeDivIds.length }} Divs, {{ selectedFileTypeUnitIds.length }} Units, {{ selectedFileTypeSecIds.length }} Secs
          </span>
          <div class="flex gap-2">
            <button 
              type="button" 
              @click="showFileTypeBranchesModal = false"
              class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="saveFileTypeBranches"
              :disabled="isSavingFileTypeBranches"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="isSavingFileTypeBranches" icon="lucide:loader-2" class="animate-spin" width="14" />
              <span>Save Scope</span>
            </button>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- ======================================================== -->
    <!-- MODAL 11: Create / Edit File Type Modal                  -->
    <!-- ======================================================== -->
    <Dialog 
      v-model:visible="showFileTypeModal" 
      modal 
      :style="{ width: '36rem' }" 
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <Icon :icon="fileTypeForm.id ? 'lucide:pencil' : 'lucide:folder-plus'" width="20" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">
              {{ fileTypeForm.id ? 'Edit File Docket Type' : 'Create New File Type' }}
            </h3>
            <p class="text-xs text-slate-500">
              Define folder docket attributes & custom jacket headings
            </p>
          </div>
        </div>
      </template>

      <form @submit.prevent="submitFileTypeForm" class="space-y-4 pt-2">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">File Type Name *</label>
          <input 
            v-model="fileTypeForm.name" 
            type="text" 
            required 
            placeholder="e.g. Policy Docket, Staff Confidential File, Tender Jacket"
            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
          <textarea 
            v-model="fileTypeForm.description" 
            rows="2" 
            placeholder="Brief overview of operational purpose..."
            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          ></textarea>
        </div>

        <!-- Folder Jacket Headings Builder -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-slate-700">
              Folder Jacket Headings / Columns
            </label>
            <button 
              type="button" 
              @click="addFileTypeHeading" 
              class="px-2 py-0.5 text-xs text-amber-700 hover:bg-amber-50 border border-amber-300 rounded-lg font-bold flex items-center gap-1 cursor-pointer"
            >
              <Icon icon="lucide:plus" width="13" />
              <span>Add Column</span>
            </button>
          </div>

          <div class="space-y-2 max-h-44 overflow-y-auto custom-scrollbar p-1">
            <div 
              v-for="(h, idx) in fileTypeForm.headings" 
              :key="idx" 
              class="flex items-center gap-2"
            >
              <input 
                v-model="fileTypeForm.headings[idx]" 
                type="text" 
                placeholder="e.g. Minutes, Correspondence, Reference No."
                class="flex-1 text-xs px-3 py-1.5 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
              />
              <button 
                type="button" 
                @click="removeFileTypeHeading(idx)" 
                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer transition-colors"
                title="Remove Column"
              >
                <Icon icon="lucide:trash-2" width="14" />
              </button>
            </div>
            <div v-if="!fileTypeForm.headings?.length" class="text-xs text-slate-400 italic p-2 bg-slate-50 rounded-lg text-center">
              No custom columns added. Click "Add Column" above.
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
          <button 
            type="button" 
            @click="showFileTypeModal = false"
            class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="isSavingFileType"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <Icon v-if="isSavingFileType" icon="lucide:loader-2" class="animate-spin" width="14" />
            <span>{{ fileTypeForm.id ? 'Update File Type' : 'Create File Type' }}</span>
          </button>
        </div>
      </form>
    </Dialog>

  </main>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import CreatePermissionModal from '../../components/Modals/AccessLevel/CreatePermissionModal.vue';
import CreateMetadataDialog from '../../components/Modals/CreateMetadataDialog.vue';
import CreateDocumentTypeDialog from '../../components/Modals/CreateDocumentTypeDialog.vue';
import ConfigureMetadataModal from '../../components/Modals/ConfigureMetadataModal.vue';

const toast = useToast();

const props = defineProps({
  departments: { type: Array, default: () => [] },
  designations: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  groupedPermissions: { type: Object, default: () => ({}) },
  masterCatalog: { type: Object, default: () => ({}) },
  documentTypes: { type: Array, default: () => [] },
  fileTypes: { type: Array, default: () => [] },
  metadatas: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

// Workspace Tabs
const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const initialTab = urlParams ? urlParams.get('tab') : null;
const validTabs = ['branches', 'designations', 'access', 'metadata'];
const activeTab = ref(validTabs.includes(initialTab) ? initialTab : (initialTab === 'structure' ? 'branches' : 'branches'));
const activeTaxonomyTab = ref('docs'); // 'docs' | 'files'

const tabs = computed(() => [
  { id: 'branches', name: 'Structure & Branches', icon: 'lucide:network', badge: currentLevelBranches.value.length },
  { id: 'designations', name: 'Desks & Designations', icon: 'lucide:badge-check', badge: scopedDesignations.value.length },
  { id: 'access', name: 'User Permissions', icon: 'lucide:user-check', badge: props.users?.length || 0 },
  { id: 'metadata', name: 'Document & File Taxonomy', icon: 'lucide:layers', badge: props.documentTypes.length + (props.fileTypes?.length || 0) },
]);

// Interactive Organogram State
const activeScope = ref(null); // null = global, or { type: 'department'|'division'|'unit', id: 1, title: '...', ... }
const treeSearchQuery = ref('');
const expandedDepts = ref({});
const expandedDivs = ref({});
const expandedUnits = ref({});

const toggleDeptExpand = (id) => {
  expandedDepts.value[id] = !expandedDepts.value[id];
};

const toggleDivExpand = (id) => {
  expandedDivs.value[id] = !expandedDivs.value[id];
};

const toggleUnitExpand = (id) => {
  expandedUnits.value[id] = !expandedUnits.value[id];
};

const clearBranchScope = () => {
  activeScope.value = null;
};

const selectBranch = (type, node, parentDept = null, parentDiv = null) => {
  activeScope.value = {
    type,
    id: node.id,
    title: node.title,
    description: node.description,
    raw: node,
    parentDept: parentDept || (type === 'department' ? node : null),
    parentDiv: parentDiv || (type === 'division' ? node : null),
  };
};

const activeScopeBreadcrumb = computed(() => {
  if (!activeScope.value) return 'Headquarters';
  if (activeScope.value.type === 'department') return activeScope.value.title;
  if (activeScope.value.type === 'division') return `${activeScope.value.parentDept?.title || 'Dept'} > ${activeScope.value.title}`;
  if (activeScope.value.type === 'unit') return `${activeScope.value.parentDept?.title || 'Dept'} > ${activeScope.value.parentDiv?.title || 'Div'} > ${activeScope.value.title}`;
  if (activeScope.value.type === 'section') return `${activeScope.value.parentDept?.title || 'Dept'} > ${activeScope.value.parentDiv?.title || 'Div'} > ${activeScope.value.title}`;
  return activeScope.value.title;
});

const activeScopeIcon = computed(() => {
  if (!activeScope.value) return 'lucide:network';
  if (activeScope.value.type === 'department') return 'lucide:building-2';
  if (activeScope.value.type === 'division') return 'lucide:folder-tree';
  if (activeScope.value.type === 'unit') return 'lucide:layers';
  return 'lucide:git-commit';
});

// Organogram Filtered Departments
const filteredDepartments = computed(() => {
  if (!treeSearchQuery.value) return props.departments;
  const q = treeSearchQuery.value.toLowerCase().trim();
  return props.departments.filter(d => {
    if (d.title.toLowerCase().includes(q)) return true;
    return d.division?.some(div => {
      if (div.title.toLowerCase().includes(q)) return true;
      return div.unit?.some(u => {
        if (u.title.toLowerCase().includes(q)) return true;
        return u.sections?.some(s => s.title.toLowerCase().includes(q));
      });
    });
  });
});

// Current Level Branches for Tab 1
const currentLevelBranches = computed(() => {
  if (!activeScope.value) {
    // Show all Departments
    return props.departments.map(d => ({
      id: d.id,
      title: d.title,
      description: d.description,
      level: 'department',
      childrenCount: d.division?.length || 0,
      usersCount: d.user?.length || 0,
      designationsCount: d.designations?.length || 0,
      documentTypesCount: d.documentType?.length || d.document_type?.length || 0,
      documentTypes: d.documentType || d.document_type || [],
      fileTypesCount: d.fileType?.length || d.file_type?.length || 0,
      fileTypes: d.fileType || d.file_type || [],
      raw: d,
    }));
  }

  if (activeScope.value.type === 'department') {
    const dept = props.departments.find(d => d.id === activeScope.value.id);
    if (!dept || !dept.division) return [];
    return dept.division.map(div => ({
      id: div.id,
      title: div.title,
      description: div.description,
      level: 'division',
      childrenCount: div.unit?.length || 0,
      usersCount: div.users?.length || 0,
      designationsCount: div.designations?.length || 0,
      documentTypesCount: div.documentType?.length || div.document_type?.length || 0,
      documentTypes: div.documentType || div.document_type || [],
      fileTypesCount: div.fileType?.length || div.file_type?.length || 0,
      fileTypes: div.fileType || div.file_type || [],
      raw: div,
    }));
  }

  if (activeScope.value.type === 'division') {
    const div = activeScope.value.raw;
    if (!div || !div.unit) return [];
    return div.unit.map(u => ({
      id: u.id,
      title: u.title,
      description: u.description,
      level: 'unit',
      childrenCount: u.sections?.length || 0,
      usersCount: u.users?.length || 0,
      designationsCount: u.designations?.length || 0,
      documentTypesCount: u.documentType?.length || u.document_type?.length || 0,
      documentTypes: u.documentType || u.document_type || [],
      fileTypesCount: u.fileType?.length || u.file_type?.length || 0,
      fileTypes: u.fileType || u.file_type || [],
      raw: u,
    }));
  }

  if (activeScope.value.type === 'unit') {
    const unit = activeScope.value.raw;
    if (!unit || !unit.sections) return [];
    return unit.sections.map(s => ({
      id: s.id,
      title: s.title,
      description: s.description,
      level: 'section',
      childrenCount: 0,
      usersCount: s.users?.length || 0,
      designationsCount: 0,
      documentTypesCount: s.documentType?.length || s.document_type?.length || 0,
      documentTypes: s.documentType || s.document_type || [],
      fileTypesCount: s.fileType?.length || s.file_type?.length || 0,
      fileTypes: s.fileType || s.file_type || [],
      raw: s,
    }));
  }

  return [];
});

const getBranchIcon = (level) => {
  if (level === 'department') return 'lucide:building-2';
  if (level === 'division') return 'lucide:folder-tree';
  if (level === 'unit') return 'lucide:layers';
  return 'lucide:git-commit';
};

const getLevelBadgeClass = (level) => {
  if (level === 'department') return 'bg-indigo-100 text-indigo-800';
  if (level === 'division') return 'bg-blue-100 text-blue-800';
  if (level === 'unit') return 'bg-emerald-100 text-emerald-800';
  return 'bg-purple-100 text-purple-800';
};

// Scoped Designations for Tab 2
const deskSearchQuery = ref('');
const selectedDeptFilter = ref('');
const deskViewMode = ref('grouped'); // 'grouped' | 'flat'
const collapsedDepts = ref({});

const toggleDeptDesksCollapse = (deptId) => {
  collapsedDepts.value[deptId] = !collapsedDepts.value[deptId];
};

const scopedDesignations = computed(() => {
  if (!activeScope.value) return props.designations;
  return props.designations.filter(des => {
    if (activeScope.value.type === 'department') {
      return des.department_id === activeScope.value.id || des.hierarchy_level === 'global';
    }
    if (activeScope.value.type === 'division') {
      return des.division_id === activeScope.value.id || (des.department_id === activeScope.value.parentDept?.id && !des.division_id) || des.hierarchy_level === 'global';
    }
    if (activeScope.value.type === 'unit') {
      return des.unit_id === activeScope.value.id || des.hierarchy_level === 'global';
    }
    return true;
  });
});

const filteredFlatDesignations = computed(() => {
  let list = scopedDesignations.value;
  if (selectedDeptFilter.value) {
    if (selectedDeptFilter.value === 'global') {
      list = list.filter(d => d.hierarchy_level === 'global' || !d.department_id);
    } else {
      list = list.filter(d => d.department_id === Number(selectedDeptFilter.value));
    }
  }
  if (deskSearchQuery.value) {
    const q = deskSearchQuery.value.toLowerCase().trim();
    list = list.filter(d => 
      d.title.toLowerCase().includes(q) ||
      (d.cadre && d.cadre.toLowerCase().includes(q)) ||
      (d.grade_level && String(d.grade_level).includes(q)) ||
      (d.department && d.department.title.toLowerCase().includes(q))
    );
  }
  return list;
});

const categorizedDepartmentDesks = computed(() => {
  let list = scopedDesignations.value;
  if (deskSearchQuery.value) {
    const q = deskSearchQuery.value.toLowerCase().trim();
    list = list.filter(d => 
      d.title.toLowerCase().includes(q) ||
      (d.cadre && d.cadre.toLowerCase().includes(q)) ||
      (d.grade_level && String(d.grade_level).includes(q)) ||
      (d.department && d.department.title.toLowerCase().includes(q))
    );
  }

  // 1. Global / Shared Civil Service Cadres
  const global = list.filter(d => d.hierarchy_level === 'global' || !d.department_id);

  // 2. Departmental Groups
  let depts = props.departments;
  if (selectedDeptFilter.value) {
    if (selectedDeptFilter.value === 'global') {
      depts = [];
    } else {
      depts = depts.filter(d => d.id === Number(selectedDeptFilter.value));
    }
  } else if (activeScope.value?.type === 'department') {
    depts = depts.filter(d => d.id === activeScope.value.id);
  }

  const deptGroups = depts.map(dept => {
    const deptDesks = list.filter(d => d.department_id === dept.id);
    const registryDesk = deptDesks.find(d => d.is_registry);
    const headDesk = deptDesks.find(d => d.is_head_of_office);
    const staffCount = deptDesks.reduce((acc, d) => acc + (d.users_count || 0), 0);

    return {
      department: dept,
      desks: deptDesks,
      registryDesk,
      headDesk,
      staffCount,
    };
  }).filter(group => {
    if (deskSearchQuery.value) {
      return group.desks.length > 0;
    }
    return true;
  });

  return {
    globalDesks: (selectedDeptFilter.value && selectedDeptFilter.value !== 'global') ? [] : global,
    departmentGroups: deptGroups,
  };
});

const scopedStaffCount = computed(() => {
  if (!activeScope.value) return props.stats.total_staff || 0;
  if (activeScope.value.type === 'department') {
    const dept = props.departments.find(d => d.id === activeScope.value.id);
    return dept?.user_count || dept?.user?.length || 0;
  }
  return activeScope.value.raw?.users?.length || 0;
});

// ==========================================
// Tab 3: Civil Service User Permissions Matrix (Department-Capability Bounded)
// ==========================================
const selectedDeptId = ref(props.departments[0]?.id || null);
const selectedDivisionId = ref('');
const selectedUserId = ref(null);
const userSearchQuery = ref('');
const activeUserPermissions = ref([]);
const isSavingPermissions = ref(false);

// Filtered divisions under selected department
const availableDivisions = computed(() => {
  if (!selectedDeptId.value) return [];
  const dept = props.departments.find(d => d.id === selectedDeptId.value);
  return dept?.division || [];
});

// Filtered users based on department, division, and search query
const filteredUsers = computed(() => {
  let list = props.users || [];
  if (selectedDeptId.value) {
    list = list.filter(u => u.department_id === selectedDeptId.value);
  }
  if (selectedDivisionId.value) {
    list = list.filter(u => u.division_id === Number(selectedDivisionId.value));
  }
  if (userSearchQuery.value && userSearchQuery.value.trim()) {
    const q = userSearchQuery.value.toLowerCase().trim();
    list = list.filter(u => 
      ((u.first_name || '') + ' ' + (u.last_name || '')).toLowerCase().includes(q) ||
      (u.email || '').toLowerCase().includes(q) ||
      (u.designation?.title || '').toLowerCase().includes(q)
    );
  }
  return list;
});

// Currently selected user object
const selectedUser = computed(() => {
  if (!selectedUserId.value) return null;
  return props.users.find(u => u.id === selectedUserId.value) || null;
});

// Sync selected user permissions whenever selectedUserId changes
const updateActiveUserPermissions = () => {
  if (selectedUser.value) {
    activeUserPermissions.value = [
      ...(selectedUser.value.direct_permissions || selectedUser.value.effective_permissions || [])
    ];
  } else {
    activeUserPermissions.value = [];
  }
};

watch(selectedUserId, () => {
  updateActiveUserPermissions();
}, { immediate: true });

// Auto-select first user when filteredUsers changes if no user selected or previous user not in list
watch(filteredUsers, (newList) => {
  if (newList.length > 0) {
    if (!selectedUserId.value || !newList.some(u => u.id === selectedUserId.value)) {
      selectedUserId.value = newList[0].id;
    }
  } else {
    selectedUserId.value = null;
  }
}, { immediate: true });

// Auto-sync department when left-rail organogram scope changes to a department
watch(() => activeScope.value, (newScope) => {
  if (newScope && newScope.type === 'department' && newScope.id) {
    selectedDeptId.value = newScope.id;
    selectedDivisionId.value = '';
  }
});

// Bounded Grouped Permissions: ONLY contains modules allowed for the selected user!
const boundedGroupedPermissions = computed(() => {
  if (!selectedUser.value) return {};
  const allowed = selectedUser.value.allowed_modules || [];
  const result = {};
  for (const [moduleName, perms] of Object.entries(props.groupedPermissions)) {
    if (allowed.includes(moduleName)) {
      result[moduleName] = perms;
    }
  }
  return result;
});

const currentMatrixPermissions = computed({
  get() {
    return activeUserPermissions.value;
  },
  set(val) {
    activeUserPermissions.value = val;
  }
});

const toggleAllInModule = (perms, enable) => {
  const names = perms.map(p => p.name);
  if (enable) {
    names.forEach(n => {
      if (!activeUserPermissions.value.includes(n)) {
        activeUserPermissions.value.push(n);
      }
    });
  } else {
    activeUserPermissions.value = activeUserPermissions.value.filter(n => !names.includes(n));
  }
};

const savePermissionsMatrix = () => {
  if (!selectedUser.value) {
    return;
  }
  isSavingPermissions.value = true;
  router.post('/dashboard/add-permissions-to-user', {
    user_id: selectedUser.value.id,
    userId: selectedUser.value.id,
    permissions: activeUserPermissions.value
  }, {
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Permissions Synchronized',
        detail: `User permissions for ${selectedUser.value.first_name} ${selectedUser.value.last_name} updated successfully.`,
        life: 3000
      });
      isSavingPermissions.value = false;
      router.reload({ only: ['users'] });
    },
    onError: () => {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to update user permissions matrix.',
        life: 4000
      });
      isSavingPermissions.value = false;
    }
  });
};

const saveRolePermissions = savePermissionsMatrix;

// Clearance Simulator
const simulatedUserId = ref('');
const simulatedUser = computed(() => {
  if (!simulatedUserId.value) return null;
  return props.users.find(u => u.id === Number(simulatedUserId.value));
});

// ==========================================
// Modal 1: Onboarding Wizard Logic
// ==========================================
const showOnboardingWizard = ref(false);
const wizardStep = ref(1);
const isSubmittingWizard = ref(false);

const wizardForm = ref({
  title: '',
  description: '',
  head_user_id: null,
  divisions: [
    { title: '', units: [''] }
  ],
  include_director_desk: true,
  include_registry_desk: true,
  document_type_ids: []
});

const wizardStepTitle = computed(() => {
  if (wizardStep.value === 1) return 'Establish Departmental Identity & Leadership';
  if (wizardStep.value === 2) return 'Architect Sub-Divisions & Operational Units';
  return 'Generate Specialized Desks & Document Taxonomy';
});

const openOnboardingWizard = () => {
  wizardStep.value = 1;
  wizardForm.value = {
    title: '',
    description: '',
    head_user_id: null,
    divisions: [{ title: '', units: [''] }],
    include_director_desk: true,
    include_registry_desk: true,
    document_type_ids: []
  };
  showOnboardingWizard.value = true;
};

const addWizardDivision = () => {
  wizardForm.value.divisions.push({ title: '', units: [''] });
};

const removeWizardDivision = (index) => {
  wizardForm.value.divisions.splice(index, 1);
};

const advanceWizardStep = () => {
  if (wizardStep.value === 1) {
    if (!wizardForm.value.title.trim()) {
      toast.add({ severity: 'warn', summary: 'Required', detail: 'Please enter department title.', life: 3000 });
      return;
    }
  }
  wizardStep.value++;
};

const submitWizard = () => {
  isSubmittingWizard.value = true;
  router.post('/dashboard/governance-hub/onboard-department', wizardForm.value, {
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Onboarding Complete',
        detail: 'Department hierarchy, divisions, and baseline desks created successfully!',
        life: 4000
      });
      showOnboardingWizard.value = false;
      isSubmittingWizard.value = false;
      router.reload();
    },
    onError: (err) => {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: Object.values(err)[0] || 'Failed to onboard department.',
        life: 4000
      });
      isSubmittingWizard.value = false;
    }
  });
};

// ==========================================
// Modal 2: Create / Edit Branch Logic
// ==========================================
const showBranchModal = ref(false);
const isEditingBranch = ref(false);
const branchModalLevel = ref('department');
const branchForm = ref({
  id: null,
  title: '',
  description: '',
  department_id: null,
  division_id: null,
});

const openCreateBranchModal = (level, parentNode = null, grandParent = null) => {
  isEditingBranch.value = false;
  branchModalLevel.value = level;
  branchForm.value = {
    id: null,
    title: '',
    description: '',
    department_id: level === 'division' ? parentNode?.id : (level === 'unit' ? grandParent?.id : null),
    division_id: level === 'unit' ? parentNode?.id : null,
  };
  showBranchModal.value = true;
};

const openAddChildBranchModal = () => {
  if (!activeScope.value) {
    openCreateBranchModal('department');
  } else if (activeScope.value.type === 'department') {
    openCreateBranchModal('division', activeScope.value);
  } else if (activeScope.value.type === 'division') {
    openCreateBranchModal('unit', activeScope.value, activeScope.value.parentDept);
  }
};

const openEditBranchModal = (branch) => {
  isEditingBranch.value = true;
  branchModalLevel.value = branch.level;
  branchForm.value = {
    id: branch.id,
    title: branch.title,
    description: branch.description,
    department_id: branch.raw.department_id,
    division_id: branch.raw.division_id,
  };
  showBranchModal.value = true;
};

const submitBranchForm = () => {
  let endpoint = '/dashboard/create-department-division-unit';
  let payload = {};

  if (branchModalLevel.value === 'department') {
    if (isEditingBranch.value) {
      endpoint = '/dashboard/edit-department';
      payload = { id: branchForm.value.id, title: branchForm.value.title, description: branchForm.value.description };
    } else {
      payload = {
        department: { title: branchForm.value.title, description: branchForm.value.description }
      };
    }
  } else if (branchModalLevel.value === 'division') {
    if (isEditingBranch.value) {
      endpoint = '/dashboard/edit-division';
      payload = { id: branchForm.value.id, title: branchForm.value.title, description: branchForm.value.description };
    } else {
      endpoint = '/dashboard/add-division-to-department';
      payload = {
        department_id: branchForm.value.department_id || activeScope.value?.id,
        divisions: [{ title: branchForm.value.title, description: branchForm.value.description }]
      };
    }
  } else if (branchModalLevel.value === 'unit') {
    if (isEditingBranch.value) {
      endpoint = '/dashboard/edit-unit';
      payload = { id: branchForm.value.id, title: branchForm.value.title, description: branchForm.value.description };
    } else {
      endpoint = '/dashboard/add-unit-to-division';
      payload = {
        division_id: branchForm.value.division_id || activeScope.value?.id,
        units: [{ title: branchForm.value.title, description: branchForm.value.description }]
      };
    }
  }

  router.post(endpoint, payload, {
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Branch Updated', detail: 'Structure updated successfully', life: 3000 });
      showBranchModal.value = false;
      router.reload();
    },
    onError: () => {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update branch structure.', life: 4000 });
    }
  });
};

// ==========================================
// Modal 3: Create / Edit Designation Logic
// ==========================================
const showDesignationModal = ref(false);
const isEditingDesignation = ref(false);
const designationForm = ref({
  id: null,
  title: '',
  cadre: '',
  grade_level: 9,
  hierarchy_level: 'global',
  department_id: null,
  division_id: null,
  unit_id: null,
  is_registry: false,
  is_head_of_office: false,
});

const openCreateDesignationModal = (targetDept = null) => {
  isEditingDesignation.value = false;
  designationForm.value = {
    id: null,
    title: '',
    cadre: '',
    grade_level: 9,
    hierarchy_level: targetDept ? 'department' : (activeScope.value?.type || 'global'),
    department_id: targetDept ? targetDept.id : (activeScope.value?.type === 'department' ? activeScope.value.id : null),
    division_id: activeScope.value?.type === 'division' ? activeScope.value.id : null,
    unit_id: activeScope.value?.type === 'unit' ? activeScope.value.id : null,
    is_registry: false,
    is_head_of_office: false,
  };
  showDesignationModal.value = true;
};

const openEditDesignationModal = (item) => {
  isEditingDesignation.value = true;
  designationForm.value = {
    id: item.id,
    title: item.title,
    cadre: item.cadre || '',
    grade_level: item.grade_level || 9,
    hierarchy_level: item.hierarchy_level || 'global',
    department_id: item.department_id,
    division_id: item.division_id,
    unit_id: item.unit_id,
    is_registry: !!item.is_registry,
    is_head_of_office: !!item.is_head_of_office,
  };
  showDesignationModal.value = true;
};

const submitDesignationForm = () => {
  const url = isEditingDesignation.value
    ? `/dashboard/settings/designations/${designationForm.value.id}`
    : '/dashboard/settings/designations';

  router.post(url, designationForm.value, {
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Designation Saved', detail: 'Functional desk configured successfully', life: 3000 });
      showDesignationModal.value = false;
      router.reload({ only: ['designations', 'stats'] });
    },
    onError: (err) => {
      toast.add({ severity: 'error', summary: 'Error', detail: Object.values(err)[0] || 'Validation failed', life: 4000 });
    }
  });
};

const toggleDesignationStatus = (item) => {
  router.post(`/dashboard/settings/designations/${item.id}/toggle-status`, {}, {
    onSuccess: () => {
      toast.add({ severity: 'info', summary: 'Status Updated', detail: 'Designation status toggled.', life: 2000 });
      router.reload({ only: ['designations'] });
    }
  });
};

// Custom Permissions Modal Handler
const showCreatePermissionModal = ref(false);
const onPermissionCreated = () => {
  toast.add({ severity: 'success', summary: 'Permission Created', detail: 'New dynamic permission registered in catalog', life: 3000 });
  router.reload();
};

// Document Taxonomy & Metadata Schema Handlers
const showCreateMetadataModal = ref(false);
const showCreateDocTypeModal = ref(false);
const showConfigureMetadataModal = ref(false);
const selectedDocTypeForConfig = ref(null);

const configureDocTypeMetadata = (docType) => {
  selectedDocTypeForConfig.value = docType;
  showConfigureMetadataModal.value = true;
};

const onSelectedMetadataOptions = (val) => {
  if (selectedDocTypeForConfig.value?.id) {
    const unwrapped = (val && val.value !== undefined) ? val.value : val;
    const metadataIds = Array.isArray(unwrapped) ? unwrapped : [];

    router.post('/dashboard/configure-meta-data', {
      id: selectedDocTypeForConfig.value.id,
      metadata: metadataIds,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          severity: 'success',
          summary: 'Schema Updated',
          detail: `Metadata schema updated for "${selectedDocTypeForConfig.value?.name}".`,
          life: 3000,
        });
        showConfigureMetadataModal.value = false;
        router.reload({ only: ['documentTypes'] });
      },
      onError: (err) => {
        toast.add({
          severity: 'error',
          summary: 'Update Failed',
          detail: Object.values(err)[0] || 'Could not update document metadata schema.',
          life: 4000,
        });
      }
    });
  }
};

const deleteDocumentType = (docType) => {
  if (confirm(`Are you sure you want to delete document type "${docType.name}"?`)) {
    router.get('/dashboard/delete-document-type', { id: docType.id }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          severity: 'info',
          summary: 'Document Type Deleted',
          detail: 'Document type removed successfully.',
          life: 3000,
        });
        router.reload({ only: ['documentTypes', 'stats'] });
      }
    });
  }
};

const deleteMetadataField = (item) => {
  if (confirm(`Are you sure you want to delete the metadata field "${item.name}"?`)) {
    router.post('/dashboard/delete-metadata', { id: item.id }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          severity: 'info',
          summary: 'Metadata Field Deleted',
          detail: 'Metadata field removed successfully.',
          life: 3000,
        });
        router.reload({ only: ['metadatas', 'documentTypes', 'stats'] });
      }
    });
  }
};

const makeMetadataDefault = (item) => {
  router.post('/dashboard/set-default-metadata', { id: item.id }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Set as Default',
        detail: `"${item.name}" is now marked as a default field.`,
        life: 3000,
      });
      router.reload({ only: ['metadatas'] });
    }
  });
};

// ========================================================
// Document Taxonomy & Branch Association Handlers (Option C)
// ========================================================

// Helpers to get authorized branches for a Document Type across both direct pivot relations and the departments tree
const getAuthorizedDeptsForDocType = (dt) => {
  if (!dt) return [];
  const map = new Map();
  const direct = dt.departments || dt.department || [];
  direct.forEach(d => {
    if (d && d.id) map.set(Number(d.id), { id: Number(d.id), title: d.title || d.name });
  });
  (props.departments || []).forEach(dept => {
    const assigned = dept.documentTypes || dept.documentType || dept.document_type || [];
    if (assigned.some(d => Number(d.id) === Number(dt.id))) {
      map.set(Number(dept.id), { id: Number(dept.id), title: dept.title || dept.name });
    }
  });
  return Array.from(map.values());
};

const getAuthorizedDivsForDocType = (dt) => {
  if (!dt) return [];
  const map = new Map();
  const direct = dt.divisions || dt.division || [];
  direct.forEach(d => {
    if (d && d.id) map.set(Number(d.id), { id: Number(d.id), title: d.title || d.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      const assigned = div.documentTypes || div.documentType || div.document_type || [];
      if (assigned.some(d => Number(d.id) === Number(dt.id))) {
        map.set(Number(div.id), { id: Number(div.id), title: div.title || div.name });
      }
    });
  });
  return Array.from(map.values());
};

const getAuthorizedUnitsForDocType = (dt) => {
  if (!dt) return [];
  const map = new Map();
  const direct = dt.units || dt.unit || [];
  direct.forEach(u => {
    if (u && u.id) map.set(Number(u.id), { id: Number(u.id), title: u.title || u.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      (div.unit || []).forEach(u => {
        const assigned = u.documentTypes || u.documentType || u.document_type || [];
        if (assigned.some(d => Number(d.id) === Number(dt.id))) {
          map.set(Number(u.id), { id: Number(u.id), title: u.title || u.name });
        }
      });
    });
  });
  return Array.from(map.values());
};

const getAuthorizedSecsForDocType = (dt) => {
  if (!dt) return [];
  const map = new Map();
  const direct = dt.sections || dt.section || [];
  direct.forEach(s => {
    if (s && s.id) map.set(Number(s.id), { id: Number(s.id), title: s.title || s.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      (div.unit || []).forEach(u => {
        (u.sections || []).forEach(s => {
          const assigned = s.documentTypes || s.documentType || s.document_type || [];
          if (assigned.some(d => Number(d.id) === Number(dt.id))) {
            map.set(Number(s.id), { id: Number(s.id), title: s.title || s.name });
          }
        });
      });
    });
  });
  return Array.from(map.values());
};

// Total branches attached to a doc type (for Tab 4 badge)
const getDocTypeTotalBranches = (docType) => {
  if (!docType) return 0;
  return getAuthorizedDeptsForDocType(docType).length +
         getAuthorizedDivsForDocType(docType).length +
         getAuthorizedUnitsForDocType(docType).length +
         getAuthorizedSecsForDocType(docType).length;
};

// Helpers to get authorized branches for a File Type across both direct pivot relations and the departments tree
const getAuthorizedDeptsForFileType = (ft) => {
  if (!ft) return [];
  const map = new Map();
  const direct = ft.departments || ft.department || [];
  direct.forEach(d => {
    if (d && d.id) map.set(Number(d.id), { id: Number(d.id), title: d.title || d.name });
  });
  (props.departments || []).forEach(dept => {
    const assigned = dept.fileTypes || dept.fileType || dept.file_type || [];
    if (assigned.some(f => Number(f.id) === Number(ft.id))) {
      map.set(Number(dept.id), { id: Number(dept.id), title: dept.title || dept.name });
    }
  });
  return Array.from(map.values());
};

const getAuthorizedDivsForFileType = (ft) => {
  if (!ft) return [];
  const map = new Map();
  const direct = ft.divisions || ft.division || [];
  direct.forEach(d => {
    if (d && d.id) map.set(Number(d.id), { id: Number(d.id), title: d.title || d.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      const assigned = div.fileTypes || div.fileType || div.file_type || [];
      if (assigned.some(f => Number(f.id) === Number(ft.id))) {
        map.set(Number(div.id), { id: Number(div.id), title: div.title || div.name });
      }
    });
  });
  return Array.from(map.values());
};

const getAuthorizedUnitsForFileType = (ft) => {
  if (!ft) return [];
  const map = new Map();
  const direct = ft.units || ft.unit || [];
  direct.forEach(u => {
    if (u && u.id) map.set(Number(u.id), { id: Number(u.id), title: u.title || u.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      (div.unit || []).forEach(u => {
        const assigned = u.fileTypes || u.fileType || u.file_type || [];
        if (assigned.some(f => Number(f.id) === Number(ft.id))) {
          map.set(Number(u.id), { id: Number(u.id), title: u.title || u.name });
        }
      });
    });
  });
  return Array.from(map.values());
};

const getAuthorizedSecsForFileType = (ft) => {
  if (!ft) return [];
  const map = new Map();
  const direct = ft.sections || ft.section || [];
  direct.forEach(s => {
    if (s && s.id) map.set(Number(s.id), { id: Number(s.id), title: s.title || s.name });
  });
  (props.departments || []).forEach(dept => {
    (dept.division || []).forEach(div => {
      (div.unit || []).forEach(u => {
        (u.sections || []).forEach(s => {
          const assigned = s.fileTypes || s.fileType || s.file_type || [];
          if (assigned.some(f => Number(f.id) === Number(ft.id))) {
            map.set(Number(s.id), { id: Number(s.id), title: s.title || s.name });
          }
        });
      });
    });
  });
  return Array.from(map.values());
};

// Total branches attached to a file type (for Tab 4 badge)
const getFileTypeTotalBranches = (ft) => {
  if (!ft) return 0;
  return getAuthorizedDeptsForFileType(ft).length +
         getAuthorizedDivsForFileType(ft).length +
         getAuthorizedUnitsForFileType(ft).length +
         getAuthorizedSecsForFileType(ft).length;
};

// 1. Branch -> Unified Classifications (Tab 1 Modal 8)
const showBranchClassificationsModal = ref(false);
const showBranchDocTypesModal = showBranchClassificationsModal; // Compatibility alias
const selectedBranchForClassifications = ref(null);
const selectedBranchForDocTypes = selectedBranchForClassifications; // Compatibility alias
const branchClassModalTab = ref('docs'); // 'docs' | 'files'
const selectedBranchDocTypeIds = ref([]);
const selectedBranchFileTypeIds = ref([]);
const branchDocTypeSearchQuery = ref('');
const branchFileTypeSearchQuery = ref('');
const isSavingBranchClassifications = ref(false);
const isSavingBranchDocTypes = isSavingBranchClassifications; // Compatibility alias

const openBranchClassificationsModal = (branch) => {
  selectedBranchForClassifications.value = branch;
  const assignedDocs = branch.documentTypes || branch.documentType || branch.document_type || [];
  const assignedFiles = branch.fileTypes || branch.fileType || branch.file_type || [];
  selectedBranchDocTypeIds.value = assignedDocs.map(dt => Number(dt.id));
  selectedBranchFileTypeIds.value = assignedFiles.map(ft => Number(ft.id));
  branchDocTypeSearchQuery.value = '';
  branchFileTypeSearchQuery.value = '';
  branchClassModalTab.value = 'docs';
  showBranchClassificationsModal.value = true;
};
const openBranchDocTypesModal = openBranchClassificationsModal; // Compatibility alias

const filteredDocTypesForBranch = computed(() => {
  if (!branchDocTypeSearchQuery.value) return props.documentTypes;
  const q = branchDocTypeSearchQuery.value.toLowerCase().trim();
  return props.documentTypes.filter(dt => dt.name?.toLowerCase().includes(q));
});

const filteredFileTypesForBranch = computed(() => {
  if (!branchFileTypeSearchQuery.value) return props.fileTypes;
  const q = branchFileTypeSearchQuery.value.toLowerCase().trim();
  return props.fileTypes.filter(ft => ft.name?.toLowerCase().includes(q));
});

const selectAllDocTypesForBranch = () => {
  selectedBranchDocTypeIds.value = props.documentTypes.map(dt => Number(dt.id));
};

const selectAllFileTypesForBranch = () => {
  selectedBranchFileTypeIds.value = props.fileTypes.map(ft => Number(ft.id));
};

const saveBranchClassifications = () => {
  if (!selectedBranchForClassifications.value) return;
  isSavingBranchClassifications.value = true;

  router.post('/governance-hub/sync-branch-classifications', {
    branch_type: selectedBranchForClassifications.value.level,
    branch_id: selectedBranchForClassifications.value.id,
    document_type_ids: selectedBranchDocTypeIds.value,
    file_type_ids: selectedBranchFileTypeIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Classifications Updated',
        detail: `Authorized document and file classifications updated for ${selectedBranchForClassifications.value.title}.`,
        life: 3000,
      });
      showBranchClassificationsModal.value = false;
      isSavingBranchClassifications.value = false;
      router.reload({ only: ['departments', 'documentTypes', 'fileTypes'] });
    },
    onError: (err) => {
      isSavingBranchClassifications.value = false;
      toast.add({
        severity: 'error',
        summary: 'Save Failed',
        detail: Object.values(err)[0] || 'Could not update office classifications.',
        life: 4000,
      });
    }
  });
};
const saveBranchDocumentTypes = saveBranchClassifications; // Compatibility alias

// 2. Document Type -> Branches (Tab 4 Modal 9)
const showDocTypeBranchesModal = ref(false);
const selectedDocTypeForBranches = ref(null);
const selectedDocTypeDeptIds = ref([]);
const selectedDocTypeDivIds = ref([]);
const selectedDocTypeUnitIds = ref([]);
const selectedDocTypeSecIds = ref([]);
const docTypeBranchSearchQuery = ref('');
const isSavingDocTypeBranches = ref(false);

const openDocTypeBranchesModal = (docType) => {
  const fresh = (props.documentTypes || []).find(d => d.id === docType.id) || docType;
  selectedDocTypeForBranches.value = fresh;
  selectedDocTypeDeptIds.value = getAuthorizedDeptsForDocType(fresh).map(d => Number(d.id));
  selectedDocTypeDivIds.value = getAuthorizedDivsForDocType(fresh).map(d => Number(d.id));
  selectedDocTypeUnitIds.value = getAuthorizedUnitsForDocType(fresh).map(u => Number(u.id));
  selectedDocTypeSecIds.value = getAuthorizedSecsForDocType(fresh).map(s => Number(s.id));
  docTypeBranchSearchQuery.value = '';
  showDocTypeBranchesModal.value = true;
};

const filteredDepartmentsForDocTypeModal = computed(() => {
  if (!docTypeBranchSearchQuery.value) return props.departments;
  const q = docTypeBranchSearchQuery.value.toLowerCase().trim();
  return props.departments.filter(dept => {
    if (dept.title?.toLowerCase().includes(q)) return true;
    return dept.division?.some(div => {
      if (div.title?.toLowerCase().includes(q)) return true;
      return div.unit?.some(u => {
        if (u.title?.toLowerCase().includes(q)) return true;
        return u.sections?.some(s => s.title?.toLowerCase().includes(q));
      });
    });
  });
});

const selectAllBranchesForDocType = () => {
  const depts = [];
  const divs = [];
  const units = [];
  const secs = [];

  (props.departments || []).forEach(dept => {
    depts.push(Number(dept.id));
    (dept.division || []).forEach(div => {
      divs.push(Number(div.id));
      (div.unit || []).forEach(u => {
        units.push(Number(u.id));
        (u.sections || []).forEach(s => {
          secs.push(Number(s.id));
        });
      });
    });
  });

  selectedDocTypeDeptIds.value = depts;
  selectedDocTypeDivIds.value = divs;
  selectedDocTypeUnitIds.value = units;
  selectedDocTypeSecIds.value = secs;
};

const clearAllBranchesForDocType = () => {
  selectedDocTypeDeptIds.value = [];
  selectedDocTypeDivIds.value = [];
  selectedDocTypeUnitIds.value = [];
  selectedDocTypeSecIds.value = [];
};

const saveDocTypeBranches = () => {
  if (!selectedDocTypeForBranches.value) return;
  isSavingDocTypeBranches.value = true;

  router.post('/governance-hub/sync-doctype-branches', {
    document_type_id: selectedDocTypeForBranches.value.id,
    department_ids: selectedDocTypeDeptIds.value,
    division_ids: selectedDocTypeDivIds.value,
    unit_ids: selectedDocTypeUnitIds.value,
    section_ids: selectedDocTypeSecIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Branch Scope Updated',
        detail: `Administrative branch scope updated for "${selectedDocTypeForBranches.value.name}".`,
        life: 3000,
      });
      showDocTypeBranchesModal.value = false;
      isSavingDocTypeBranches.value = false;
      router.reload({ only: ['departments', 'documentTypes'] });
    },
    onError: (err) => {
      isSavingDocTypeBranches.value = false;
      toast.add({
        severity: 'error',
        summary: 'Save Failed',
        detail: Object.values(err)[0] || 'Could not update branch scope.',
        life: 4000,
      });
    }
  });
};

// 3. File Type -> Branches (Tab 4 Modal 10)
const showFileTypeBranchesModal = ref(false);
const selectedFileTypeForBranches = ref(null);
const selectedFileTypeDeptIds = ref([]);
const selectedFileTypeDivIds = ref([]);
const selectedFileTypeUnitIds = ref([]);
const selectedFileTypeSecIds = ref([]);
const fileTypeBranchSearchQuery = ref('');
const isSavingFileTypeBranches = ref(false);

const openFileTypeBranchesModal = (fileType) => {
  const fresh = (props.fileTypes || []).find(f => f.id === fileType.id) || fileType;
  selectedFileTypeForBranches.value = fresh;
  selectedFileTypeDeptIds.value = getAuthorizedDeptsForFileType(fresh).map(d => Number(d.id));
  selectedFileTypeDivIds.value = getAuthorizedDivsForFileType(fresh).map(d => Number(d.id));
  selectedFileTypeUnitIds.value = getAuthorizedUnitsForFileType(fresh).map(u => Number(u.id));
  selectedFileTypeSecIds.value = getAuthorizedSecsForFileType(fresh).map(s => Number(s.id));
  fileTypeBranchSearchQuery.value = '';
  showFileTypeBranchesModal.value = true;
};

const filteredDepartmentsForFileTypeModal = computed(() => {
  if (!fileTypeBranchSearchQuery.value) return props.departments;
  const q = fileTypeBranchSearchQuery.value.toLowerCase().trim();
  return props.departments.filter(dept => {
    if (dept.title?.toLowerCase().includes(q)) return true;
    return dept.division?.some(div => {
      if (div.title?.toLowerCase().includes(q)) return true;
      return div.unit?.some(u => {
        if (u.title?.toLowerCase().includes(q)) return true;
        return u.sections?.some(s => s.title?.toLowerCase().includes(q));
      });
    });
  });
});

const selectAllBranchesForFileType = () => {
  const depts = [];
  const divs = [];
  const units = [];
  const secs = [];

  (props.departments || []).forEach(dept => {
    depts.push(Number(dept.id));
    (dept.division || []).forEach(div => {
      divs.push(Number(div.id));
      (div.unit || []).forEach(u => {
        units.push(Number(u.id));
        (u.sections || []).forEach(s => {
          secs.push(Number(s.id));
        });
      });
    });
  });

  selectedFileTypeDeptIds.value = depts;
  selectedFileTypeDivIds.value = divs;
  selectedFileTypeUnitIds.value = units;
  selectedFileTypeSecIds.value = secs;
};

const clearAllBranchesForFileType = () => {
  selectedFileTypeDeptIds.value = [];
  selectedFileTypeDivIds.value = [];
  selectedFileTypeUnitIds.value = [];
  selectedFileTypeSecIds.value = [];
};

const saveFileTypeBranches = () => {
  if (!selectedFileTypeForBranches.value) return;
  isSavingFileTypeBranches.value = true;

  router.post('/governance-hub/sync-filetype-branches', {
    file_type_id: selectedFileTypeForBranches.value.id,
    department_ids: selectedFileTypeDeptIds.value,
    division_ids: selectedFileTypeDivIds.value,
    unit_ids: selectedFileTypeUnitIds.value,
    section_ids: selectedFileTypeSecIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Branch Scope Updated',
        detail: `Administrative branch scope updated for "${selectedFileTypeForBranches.value.name}".`,
        life: 3000,
      });
      showFileTypeBranchesModal.value = false;
      isSavingFileTypeBranches.value = false;
      router.reload({ only: ['departments', 'fileTypes'] });
    },
    onError: (err) => {
      isSavingFileTypeBranches.value = false;
      toast.add({
        severity: 'error',
        summary: 'Save Failed',
        detail: Object.values(err)[0] || 'Could not update branch scope.',
        life: 4000,
      });
    }
  });
};

// 4. File Type CRUD (Modal 11)
const showFileTypeModal = ref(false);
const isSavingFileType = ref(false);
const fileTypeForm = ref({
  id: null,
  name: '',
  description: '',
  headings: ['Title', 'Description', 'Amount'],
});

const openCreateFileTypeModal = () => {
  fileTypeForm.value = {
    id: null,
    name: '',
    description: '',
    headings: ['Title', 'Description', 'Amount'],
  };
  showFileTypeModal.value = true;
};

const openEditFileTypeModal = (ft) => {
  fileTypeForm.value = {
    id: ft.id,
    name: ft.name,
    description: ft.description || '',
    headings: Array.isArray(ft.headings) && ft.headings.length ? [...ft.headings] : ['Title', 'Description', 'Amount'],
  };
  showFileTypeModal.value = true;
};

const addFileTypeHeading = () => {
  if (!fileTypeForm.value.headings) fileTypeForm.value.headings = [];
  fileTypeForm.value.headings.push('');
};

const removeFileTypeHeading = (index) => {
  if (fileTypeForm.value.headings) {
    fileTypeForm.value.headings.splice(index, 1);
  }
};

const submitFileTypeForm = () => {
  isSavingFileType.value = true;
  const cleanedHeadings = (fileTypeForm.value.headings || []).filter(h => h && h.trim() !== '');

  router.post('/governance-hub/create-file-type', {
    id: fileTypeForm.value.id,
    name: fileTypeForm.value.name,
    description: fileTypeForm.value.description,
    headings: cleanedHeadings,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: fileTypeForm.value.id ? 'File Type Updated' : 'File Type Created',
        detail: `"${fileTypeForm.value.name}" saved successfully.`,
        life: 3000,
      });
      showFileTypeModal.value = false;
      isSavingFileType.value = false;
      router.reload({ only: ['fileTypes', 'stats'] });
    },
    onError: (err) => {
      isSavingFileType.value = false;
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: Object.values(err)[0] || 'Failed to save file type.',
        life: 4000,
      });
    }
  });
};

const deleteFileType = (fileType) => {
  if (confirm(`Are you sure you want to delete file type "${fileType.name}"?`)) {
    router.post('/governance-hub/delete-file-type', { id: fileType.id }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          severity: 'info',
          summary: 'File Type Deleted',
          detail: 'File type removed successfully.',
          life: 3000,
        });
        router.reload({ only: ['fileTypes', 'stats'] });
      },
      onError: (err) => {
        toast.add({
          severity: 'error',
          summary: 'Delete Failed',
          detail: Object.values(err)[0] || 'Could not delete file type.',
          life: 4000,
        });
      }
    });
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
