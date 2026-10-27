<template>
  <main class="min-h-screen bg-slate-50/70 pb-20">
    <!-- Top Executive Header -->
    <header class="bg-white border-b border-slate-200/80 px-4 sm:px-6 py-4 sticky top-0 z-20 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
            <Icon icon="solar:users-group-two-rounded-bold-duotone" width="28" />
          </div>
          <div>
            <div class="flex items-center gap-2.5">
              <h1 class="text-2xl font-black text-slate-900 tracking-tight">Personnel & Team Management</h1>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                Civil Service Cadre
              </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
              Oversee officer hierarchy placement, access tiers, functional desks, credentials, and compliance.
            </p>
          </div>
        </div>

        <!-- Global Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <!-- Account Requests Modal Trigger -->
          <button 
            type="button"
            @click="account_request = true" 
            class="flex items-center gap-2 text-indigo-700 bg-indigo-50/80 hover:bg-indigo-100/90 border border-indigo-200/80 px-3.5 py-2 rounded-xl font-bold text-xs transition-all shadow-2xs cursor-pointer"
          >
            <Icon icon="solar:user-hand-up-bold-duotone" width="18" />
            <span>Account Requests</span>
            <span 
              v-if="props.notifications && props.notifications.length > 0"
              class="bg-amber-500 text-white text-[10px] px-2 py-0.2 rounded-full font-extrabold shadow-xs animate-pulse"
            >
              {{ props.notifications.length }}
            </span>
          </button>

          <!-- Download Filtered CSV -->
          <button 
            type="button"
            @click="exportCsv"
            :disabled="isExporting"
            class="flex items-center gap-2 text-slate-700 bg-white hover:bg-slate-50 border border-slate-300/80 px-3.5 py-2 rounded-xl font-bold text-xs transition-all shadow-2xs cursor-pointer disabled:opacity-50"
          >
            <Icon v-if="isExporting" icon="line-md:loading-loop" width="16" />
            <Icon v-else icon="solar:file-download-bold-duotone" width="16" class="text-emerald-600" />
            <span>Export CSV</span>
          </button>

          <!-- Add User Link -->
          <Link href="/dashboard/add-user">
            <button 
              type="button"
              class="flex items-center gap-2 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-sm shadow-blue-600/20 cursor-pointer"
            >
              <Icon icon="solar:user-plus-bold-duotone" width="16" />
              <span>Provision Officer</span>
            </button>
          </Link>
        </div>
      </div>
    </header>

    <div class="w-full px-4 sm:px-6 mt-5 space-y-5">

      <!-- Executive KPI Telemetry Bar -->
      <section class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        <!-- 1. Total Approved Staff -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-blue-300 transition-colors">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            <Icon icon="solar:users-group-rounded-bold-duotone" width="22" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Approved Officers</p>
            <p class="text-xl font-black text-slate-900 leading-tight">{{ props.stats?.total_approved ?? rawUsers.length }}</p>
            <p class="text-[10px] text-slate-500 font-medium">In active registry</p>
          </div>
        </div>

        <!-- 2. Pending Requests -->
        <div 
          @click="account_request = true"
          class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-amber-300 transition-colors cursor-pointer group"
        >
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
            <Icon icon="solar:user-hand-up-bold-duotone" width="22" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Pending Onboarding</p>
            <p class="text-xl font-black text-amber-600 leading-tight">{{ props.stats?.pending_requests ?? (props.notifications?.length || 0) }}</p>
            <p class="text-[10px] text-slate-500 font-medium">Requires clearance</p>
          </div>
        </div>

        <!-- 3. Heads of Office & Registry Desks -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-purple-300 transition-colors">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
            <Icon icon="solar:crown-star-bold-duotone" width="22" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Heads & Key Desks</p>
            <p class="text-xl font-black text-purple-700 leading-tight">{{ props.stats?.heads_of_office ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 font-medium">Dept/Div/Unit leads</p>
          </div>
        </div>

        <!-- 4. On Leave / In Relief -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-sky-300 transition-colors">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <Icon icon="solar:calendar-date-bold-duotone" width="22" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">On Leave / Relief</p>
            <p class="text-xl font-black text-sky-700 leading-tight">{{ props.stats?.on_leave ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 font-medium">Delegated authority</p>
          </div>
        </div>

        <!-- 5. Security & Credentials Compliance -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-emerald-300 transition-colors col-span-2 md:col-span-1">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <Icon icon="solar:shield-check-bold-duotone" width="22" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Signature / PIN Ready</p>
            <p class="text-xl font-black text-emerald-700 leading-tight">{{ signatureComplianceRate }}%</p>
            <p class="text-[10px] text-slate-500 font-medium">{{ props.stats?.with_signature || 0 }} signed / {{ props.stats?.with_pin || 0 }} PINs</p>
          </div>
        </div>
      </section>

      <!-- Multi-Dimensional Filter & View Control Toolbar -->
      <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-3">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
          
          <!-- Search Input -->
          <div class="relative flex-1 min-w-[260px]">
            <Icon icon="solar:magnifer-linear" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" width="18" />
            <input 
              type="text" 
              v-model="filters['global'].value" 
              placeholder="Search by officer name, email, IPPIS, phone, designation, or department..." 
              class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
            />
            <button 
              v-if="filters['global'].value"
              @click="filters['global'].value = ''"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
              aria-label="Clear Search"
            >
              <Icon icon="solar:close-circle-bold" width="16" />
            </button>
          </div>

          <!-- Filter Dropdowns Row -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Filter by Directorate / Department -->
            <Dropdown 
              v-model="selectedDepartment" 
              :options="departmentOptions" 
              optionLabel="label" 
              optionValue="value" 
              placeholder="All Departments" 
              class="w-44 text-xs bg-slate-50/70 border border-slate-200 rounded-xl"
              showClear
            />

            <!-- Filter by Spatie Role -->
            <Dropdown 
              v-model="selectedRole" 
              :options="roleOptions" 
              optionLabel="label" 
              optionValue="value" 
              placeholder="All System Roles" 
              class="w-44 text-xs bg-slate-50/70 border border-slate-200 rounded-xl"
              showClear
            />

            <!-- Filter by Grade Level Band -->
            <Dropdown 
              v-model="selectedGradeBand" 
              :options="gradeBandOptions" 
              optionLabel="label" 
              optionValue="value" 
              placeholder="All Grade Levels" 
              class="w-40 text-xs bg-slate-50/70 border border-slate-200 rounded-xl"
              showClear
            />

            <!-- Filter by Operational Compliance Status -->
            <Dropdown 
              v-model="selectedComplianceStatus" 
              :options="complianceStatusOptions" 
              optionLabel="label" 
              optionValue="value" 
              placeholder="All Duty Statuses" 
              class="w-44 text-xs bg-slate-50/70 border border-slate-200 rounded-xl"
              showClear
            />

            <!-- Layout Mode Switcher -->
            <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200/80 ml-auto lg:ml-0">
              <button 
                type="button" 
                @click="setViewMode('flat')" 
                :class="viewMode === 'flat' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                class="px-2.5 py-1 text-xs rounded-lg transition-all duration-150 flex items-center gap-1.5 cursor-pointer"
                title="Directory list view with multi-column sorting"
                aria-label="Directory list view"
              >
                <Icon icon="solar:list-bold-duotone" width="14" />
                <span>Directory</span>
              </button>
              <button 
                type="button" 
                @click="setViewMode('grouped')" 
                :class="viewMode === 'grouped' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                class="px-2.5 py-1 text-xs rounded-lg transition-all duration-150 flex items-center gap-1.5 cursor-pointer"
                title="Organize staff under primary role subheaders"
                aria-label="Organize staff by role"
              >
                <Icon icon="solar:layers-minimalistic-bold-duotone" width="14" />
                <span>By Role</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Active Filter Indicator Tags -->
        <div v-if="hasActiveFilters" class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Active Filters:</span>
            <span v-if="filters['global'].value" class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[11px] font-medium border border-blue-200">
              Query: "{{ filters['global'].value }}"
            </span>
            <span v-if="selectedDepartment" class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-medium border border-indigo-200">
              Dept: {{ getDeptTitle(selectedDepartment) }}
            </span>
            <span v-if="selectedRole" class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-medium border border-purple-200">
              Role: {{ selectedRole }}
            </span>
            <span v-if="selectedGradeBand" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-medium border border-emerald-200">
              Band: {{ selectedGradeBand }}
            </span>
            <span v-if="selectedComplianceStatus" class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[11px] font-medium border border-amber-200">
              Status: {{ selectedComplianceStatus }}
            </span>
          </div>

          <button 
            type="button" 
            @click="clearAllFilters" 
            class="text-[11px] text-rose-600 hover:text-rose-700 font-bold cursor-pointer hover:underline"
          >
            Reset Filters
          </button>
        </div>

        <!-- Interactive Role Cadre Filter Ribbon (When in 'By Role' mode) -->
        <div v-if="viewMode === 'grouped'" class="pt-2.5 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
          <span class="text-[10px] uppercase font-bold text-slate-400 shrink-0 mr-1 flex items-center gap-1">
            <Icon icon="solar:shield-user-bold-duotone" width="13" class="text-blue-600" />
            Cadre Filter:
          </span>
          <button 
            type="button"
            @click="selectedRole = null"
            :class="!selectedRole ? 'bg-blue-600 text-white shadow-2xs font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
            class="px-2.5 py-1 rounded-lg text-xs transition-all shrink-0 flex items-center gap-1.5 cursor-pointer"
          >
            <span>All Roles</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" :class="!selectedRole ? 'bg-blue-700 text-white' : 'bg-white text-slate-600'">
              {{ rawUsers.length }}
            </span>
          </button>
          <button 
            v-for="role in (props.roles || [])"
            :key="role.id"
            type="button"
            @click="selectedRole = selectedRole === role.name ? null : role.name"
            :class="selectedRole === role.name ? 'bg-blue-600 text-white shadow-2xs font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-medium'"
            class="px-2.5 py-1 rounded-lg text-xs transition-all shrink-0 flex items-center gap-1.5 cursor-pointer capitalize"
          >
            <span>{{ role.name }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono" :class="selectedRole === role.name ? 'bg-blue-700 text-white' : 'bg-slate-200 text-slate-600'">
              {{ role.user_count ?? getUserCountForRole(role.name) }}
            </span>
          </button>
        </div>
      </section>

      <!-- Bulk Actions Floating Bar (Active when rows selected) -->
      <transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-2 opacity-0">
        <div v-if="selectedUsers.length > 0" class="flex items-center justify-between p-3.5 bg-slate-900 text-white rounded-2xl shadow-lg border border-slate-800">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs font-black flex items-center justify-center">
              {{ selectedUsers.length }}
            </span>
            <span class="text-xs font-bold text-slate-200">Officer{{ selectedUsers.length > 1 ? 's' : '' }} Selected</span>
          </div>

          <div class="flex items-center gap-2">
            <button 
              type="button"
              @click="exportSelectedUsersCsv"
              class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Icon icon="solar:file-download-bold-duotone" width="14" />
              <span>Export Selected CSV</span>
            </button>
            <button 
              type="button"
              @click="selectedUsers = []"
              class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-lg transition-colors cursor-pointer"
            >
              Deselect All
            </button>
          </div>
        </div>
      </transition>

      <!-- View 1: Main Personnel Directory DataTable (Flat with Sorting & Paginator) -->
      <div v-if="viewMode === 'flat'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-x-auto">
        <DataTable 
          v-model:selection="selectedUsers"
          :value="displayUsers" 
          dataKey="id" 
          :paginator="true" 
          :rows="20" 
          :rowsPerPageOptions="[10, 20, 50, 100]"
          :filters="filters"
          tableStyle="width: 100%; min-width: 65rem"
          class="w-full text-xs manage-users-table"
        >
          <!-- Column 0: Multi-select Checkbox -->
          <Column selectionMode="multiple" headerStyle="width: 3rem" />

          <!-- Column 1: Officer Identity -->
          <Column field="last_name" header="Officer Particulars" style="width: 25%" :sortable="true">
            <template #body="slotProps">
              <div class="flex items-center gap-3 py-1.5">
                <!-- Avatar with online indicator -->
                <div class="relative shrink-0">
                  <img 
                    v-if="slotProps.data.profile_image" 
                    :src="'/' + slotProps.data.profile_image" 
                    class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs" 
                  />
                  <div 
                    v-else 
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700 flex items-center justify-center font-black text-xs border border-blue-200/80 shadow-2xs"
                  >
                    {{ (slotProps.data.first_name || '').charAt(0) }}{{ (slotProps.data.last_name || '').charAt(0) }}
                  </div>
                  <!-- Duty / Online Dot -->
                  <span 
                    class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white"
                    :class="slotProps.data.is_on_leave ? 'bg-amber-400' : 'bg-emerald-500'"
                    :title="slotProps.data.is_on_leave ? 'Status: On Official Leave / Delegated' : 'Status: Active Duty'"
                  ></span>
                </div>

                <div class="flex flex-col min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span 
                      @click="openOfficerDossier(slotProps.data.id)"
                      class="font-bold text-slate-900 hover:text-blue-600 transition-colors cursor-pointer truncate text-sm"
                    >
                      {{ slotProps.data.last_name }} {{ slotProps.data.first_name }}
                    </span>
                    <span 
                      v-if="slotProps.data.ippis_no" 
                      class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-100 text-slate-600 border border-slate-200 shrink-0"
                    >
                      {{ slotProps.data.ippis_no }}
                    </span>
                  </div>
                  <span class="text-[11px] text-slate-400 truncate mt-0.5">{{ slotProps.data.email }}</span>
                  <span v-if="slotProps.data.phone" class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                    <Icon icon="solar:phone-linear" width="10" /> {{ slotProps.data.phone }}
                  </span>
                </div>
              </div>
            </template>
          </Column>

          <!-- Column 2: Functional Desk & Roles -->
          <Column field="designation.title" header="Functional Desk & Roles" style="width: 25%" :sortable="true">
            <template #body="slotProps">
              <div class="flex flex-col gap-1 py-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-xs font-bold text-slate-800">
                    {{ slotProps.data.designation?.title || 'Unassigned Desk' }}
                  </span>
                  <!-- Head of Office Tag -->
                  <span 
                    v-if="slotProps.data.is_department_head || slotProps.data.is_division_head || slotProps.data.is_unit_head || slotProps.data.designation?.is_head_of_office" 
                    class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-0.5"
                  >
                    <Icon icon="solar:crown-minimalistic-bold" width="10" />
                    <span>Head</span>
                  </span>
                  <!-- Registry Desk Tag -->
                  <span 
                    v-if="slotProps.data.designation?.is_registry" 
                    class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-800 border border-blue-200"
                  >
                    Registry
                  </span>
                </div>

                <!-- Roles Pills -->
                <div class="flex items-center gap-1 flex-wrap mt-0.5">
                  <span 
                    v-for="role in (slotProps.data.roles || [])" 
                    :key="role.id" 
                    class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 capitalize"
                  >
                    {{ role.name }}
                  </span>
                  <span 
                    v-if="!slotProps.data.roles || slotProps.data.roles.length === 0" 
                    class="px-1.5 py-0.2 rounded text-[9px] font-medium text-slate-400 bg-slate-50 border border-slate-200 italic"
                  >
                    No System Role
                  </span>
                </div>
              </div>
            </template>
          </Column>

          <!-- Column 3: Hierarchy Placement -->
          <Column field="department.title" header="Structural Placement" style="width: 20%" :sortable="true">
            <template #body="slotProps">
              <div class="flex flex-col gap-0.5 py-1">
                <span class="text-xs font-semibold text-slate-800 flex items-center gap-1">
                  <Icon icon="solar:buildings-2-linear" class="text-indigo-500" width="12" />
                  <span>{{ slotProps.data.department?.title || 'Global / Unassigned' }}</span>
                </span>
                <span v-if="slotProps.data.division?.title" class="text-[11px] text-slate-500 flex items-center gap-1 pl-2">
                  <Icon icon="solar:alt-arrow-right-linear" width="10" class="text-slate-400" />
                  <span>{{ slotProps.data.division.title }}</span>
                </span>
                <span v-if="slotProps.data.unit?.title" class="text-[10px] text-slate-400 flex items-center gap-1 pl-4">
                  <Icon icon="solar:alt-arrow-right-linear" width="10" class="text-slate-300" />
                  <span>{{ slotProps.data.unit.title }}</span>
                </span>
              </div>
            </template>
          </Column>

          <!-- Column 4: Cadre & Grade Level -->
          <Column field="grade_level" header="Cadre / GL" style="width: 10%" :sortable="true">
            <template #body="slotProps">
              <div class="flex flex-col gap-1 py-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 w-fit">
                  GL {{ slotProps.data.grade_level || 'N/A' }}
                  <span v-if="slotProps.data.step" class="text-[9px] font-normal text-purple-500 ml-1">/ St {{ slotProps.data.step }}</span>
                </span>
                <span class="text-[10px] text-slate-400 truncate">
                  {{ slotProps.data.designation?.cadre || 'Officer Cadre' }}
                </span>
              </div>
            </template>
          </Column>

          <!-- Column 5: Security Credentials & Compliance Status -->
          <Column header="Compliance" style="width: 10%">
            <template #body="slotProps">
              <div class="flex items-center gap-1.5 py-1">
                <!-- PIN Status -->
                <span 
                  class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] border cursor-help transition-transform hover:scale-105"
                  :class="slotProps.data.has_pin ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-500 border-rose-200'"
                  :title="slotProps.data.has_pin ? 'Approval PIN: Configured' : 'Approval PIN: Missing'"
                >
                  <Icon :icon="slotProps.data.has_pin ? 'solar:key-bold-duotone' : 'solar:key-minimalistic-linear'" width="13" />
                </span>

                <!-- Signature Status -->
                <span 
                  class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] border cursor-help transition-transform hover:scale-105"
                  :class="slotProps.data.has_signature ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200'"
                  :title="slotProps.data.has_signature ? 'Digital Signature: Uploaded' : 'Digital Signature: Missing'"
                >
                  <Icon icon="solar:pen-bold-duotone" width="13" />
                </span>

                <!-- 2FA Status -->
                <span 
                  class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] border cursor-help transition-transform hover:scale-105"
                  :class="slotProps.data.is_two_factor_enabled ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-100 text-slate-400 border-slate-200'"
                  :title="slotProps.data.is_two_factor_enabled ? '2-Factor Auth: Enabled' : '2-Factor Auth: Disabled'"
                >
                  <Icon icon="solar:shield-check-bold-duotone" width="13" />
                </span>
              </div>
            </template>
          </Column>

          <!-- Column 6: Actions Menu -->
          <Column header="Actions" headerClass="text-right" style="width: 130px; min-width: 130px" :pt="{ headerContent: { class: '!justify-end' } }">
            <template #body="slotProps">
              <div class="flex items-center gap-1.5 justify-end">
                <!-- Quick Dossier View Button -->
                <button 
                  type="button"
                  @click.stop="openOfficerDossier(slotProps.data.id)"
                  class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 active:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                  title="View Officer 360° Dossier"
                  aria-label="View Officer 360° Dossier"
                >
                  <Icon icon="solar:user-id-bold-duotone" width="18" />
                </button>

                <!-- Edit Profile Button -->
                <button 
                  type="button"
                  @click.stop="navigateToEdit(slotProps.data.id)"
                  class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 active:bg-indigo-100 rounded-lg transition-colors cursor-pointer" 
                  title="Edit Officer Profile & Hierarchy"
                  aria-label="Edit Officer Profile & Hierarchy"
                >
                  <Icon icon="solar:pen-new-square-linear" width="18" />
                </button>

                <!-- Action Dropdown Toggle Button -->
                <button 
                  type="button"
                  @click.stop="toggleRowMenu($event, slotProps.data)"
                  class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 rounded-lg transition-colors cursor-pointer"
                  title="More Officer Actions"
                  aria-label="More Officer Actions"
                >
                  <Icon icon="solar:menu-dots-bold" width="16" />
                </button>
              </div>
            </template>
          </Column>

          <!-- Empty State -->
          <template #empty>
            <div class="py-16 text-center">
              <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <Icon icon="solar:users-group-two-rounded-broken" width="32" />
              </div>
              <h3 class="text-sm font-bold text-slate-800">No Matching Officers Found</h3>
              <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                No active personnel matches your current search criteria or applied filters.
              </p>
              <button 
                type="button"
                @click="clearAllFilters" 
                class="mt-3 px-3.5 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg hover:bg-blue-100 transition-colors cursor-pointer border border-blue-200"
              >
                Clear Filters
              </button>
            </div>
          </template>
        </DataTable>
      </div>

      <!-- View 2: By Role Cadre Sections (High-performance, No-freeze Grouped Organization) -->
      <div v-else-if="viewMode === 'grouped'" class="space-y-4">
        <div 
          v-for="cadre in roleCadreGroups" 
          :key="cadre.id"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
        >
          <!-- Cadre Header Banner -->
          <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-slate-50 via-white to-blue-50/20 border-b border-slate-200/80">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                <Icon icon="solar:shield-user-bold-duotone" width="18" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-bold text-slate-900 capitalize">{{ cadre.name }}</h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-100 text-blue-700">
                    {{ cadre.users.length }} Officer{{ cadre.users.length !== 1 ? 's' : '' }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ cadre.description }}</p>
              </div>
            </div>

            <button 
              type="button"
              @click="exportCadreCsv(cadre)"
              class="px-2.5 py-1 text-slate-600 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
            >
              <Icon icon="solar:file-download-linear" width="14" />
              <span>Export Cadre</span>
            </button>
          </div>

          <!-- Officers Table within this Cadre -->
          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50/80 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-500 tracking-wider">
                <tr>
                  <th class="py-3 px-4 w-[28%]">Officer Particulars</th>
                  <th class="py-3 px-4 w-[24%]">Functional Desk</th>
                  <th class="py-3 px-4 w-[22%]">Structural Placement</th>
                  <th class="py-3 px-4 w-[12%]">Cadre / GL</th>
                  <th class="py-3 px-4 w-[14%] text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr 
                  v-for="user in cadre.users" 
                  :key="user.id" 
                  class="hover:bg-slate-50/80 transition-colors"
                >
                  <!-- Particulars -->
                  <td class="py-2.5 px-4">
                    <div class="flex items-center gap-3">
                      <div class="relative shrink-0">
                        <img 
                          v-if="user.profile_image" 
                          :src="'/' + user.profile_image" 
                          class="w-9 h-9 rounded-xl object-cover border border-slate-200" 
                        />
                        <div 
                          v-else 
                          class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200/80"
                        >
                          {{ (user.first_name || '').charAt(0) }}{{ (user.last_name || '').charAt(0) }}
                        </div>
                        <span 
                          class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white"
                          :class="user.is_on_leave ? 'bg-amber-400' : 'bg-emerald-500'"
                        ></span>
                      </div>
                      <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5">
                          <span 
                            @click="openOfficerDossier(user.id)"
                            class="font-bold text-slate-900 hover:text-blue-600 transition-colors cursor-pointer truncate text-sm"
                          >
                            {{ user.last_name }} {{ user.first_name }}
                          </span>
                          <span v-if="user.ippis_no" class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                            {{ user.ippis_no }}
                          </span>
                        </div>
                        <span class="text-[11px] text-slate-400 truncate">{{ user.email }}</span>
                      </div>
                    </div>
                  </td>

                  <!-- Functional Desk -->
                  <td class="py-2.5 px-4">
                    <div class="flex flex-col gap-0.5">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-800">
                          {{ user.designation?.title || 'Unassigned Desk' }}
                        </span>
                        <span 
                          v-if="user.is_department_head || user.is_division_head || user.is_unit_head || user.designation?.is_head_of_office" 
                          class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200"
                        >
                          Head
                        </span>
                      </div>
                      <span class="text-[10px] text-slate-400">
                        Level {{ user.designation?.hierarchy_level ?? 'Standard' }}
                      </span>
                    </div>
                  </td>

                  <!-- Placement -->
                  <td class="py-2.5 px-4">
                    <div class="flex flex-col gap-0.5">
                      <span class="font-semibold text-slate-800 flex items-center gap-1 truncate">
                        <Icon icon="solar:buildings-2-linear" class="text-indigo-500 shrink-0" width="12" />
                        <span>{{ user.department?.title || 'Global / Unassigned' }}</span>
                      </span>
                      <span v-if="user.division?.title" class="text-[10px] text-slate-400 pl-2 truncate">
                        ↳ {{ user.division.title }}
                      </span>
                    </div>
                  </td>

                  <!-- Cadre / GL -->
                  <td class="py-2.5 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                      GL {{ user.grade_level || 'N/A' }}
                    </span>
                  </td>

                  <!-- Actions -->
                  <td class="py-2.5 px-4 text-right">
                    <div class="flex items-center gap-1.5 justify-end">
                      <button 
                        type="button"
                        @click.stop="openOfficerDossier(user.id)"
                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 active:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                        title="View 360° Dossier"
                      >
                        <Icon icon="solar:user-id-bold-duotone" width="16" />
                      </button>

                      <button 
                        type="button"
                        @click.stop="navigateToEdit(user.id)"
                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 active:bg-indigo-100 rounded-lg transition-colors cursor-pointer" 
                        title="Edit Officer Profile"
                      >
                        <Icon icon="solar:pen-new-square-linear" width="16" />
                      </button>

                      <button 
                        type="button"
                        @click.stop="toggleRowMenu($event, user)"
                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 rounded-lg transition-colors cursor-pointer"
                        title="More Actions"
                      >
                        <Icon icon="solar:menu-dots-bold" width="15" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty state when no cadres match filter -->
        <div v-if="roleCadreGroups.length === 0" class="p-12 text-center bg-white rounded-2xl border border-slate-200/80">
          <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2.5">
            <Icon icon="solar:users-group-two-rounded-broken" width="24" />
          </div>
          <p class="text-sm font-bold text-slate-700">No personnel found for the selected cadre filter</p>
          <button 
            type="button" 
            @click="clearAllFilters" 
            class="mt-2 text-xs font-bold text-blue-600 hover:underline"
          >
            Reset Filters
          </button>
        </div>
      </div>

    </div>

    <!-- Row Actions Context Menu (PrimeVue Menu) -->
    <Menu 
      ref="rowMenuRef" 
      :model="rowMenuItems" 
      :popup="true"
      :pt="{
        root: { class: '!bg-white !shadow-xl !rounded-xl !border !border-slate-200 !min-w-[220px] !py-1.5 !text-xs !z-50' },
        menu: { class: '!list-none !m-0 !p-0' },
        menuitem: { class: '!text-xs' },
        action: { class: '!flex !items-center !gap-2.5 !px-3.5 !py-2 !text-slate-700 hover:!bg-slate-100 hover:!text-slate-900 !transition-colors !text-xs !font-medium !cursor-pointer' },
        icon: { class: '!text-slate-400 !text-sm' },
        separator: { class: '!border-t !border-slate-100 !my-1' }
      }"
    />

    <!-- 360° Officer Dossier Slide-Over Sheet (Sidebar) -->
    <Sidebar 
      v-model:visible="showDossier" 
      position="right" 
      :showCloseIcon="false"
      class="w-full sm:w-[500px] p-0"
      :pt="{
        root: { class: 'bg-white shadow-2xl' },
        header: { class: '!p-0 !border-0' },
        content: { class: '!p-0' }
      }"
    >
      <template #header>
        <div class="flex items-center justify-between w-full px-6 py-4 bg-slate-50/80 border-b border-slate-200">
          <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
              <Icon icon="solar:user-id-bold-duotone" width="18" />
            </span>
            <div>
              <h2 class="text-sm font-bold text-slate-900 leading-tight">360° Officer Dossier</h2>
              <p class="text-[10px] text-slate-400">Personnel verification & clearance telemetry</p>
            </div>
          </div>

          <!-- Close Interface Icon -->
          <button 
            type="button" 
            @click="showDossier = false"
            class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors flex items-center justify-center cursor-pointer shrink-0"
            aria-label="Close Dossier"
          >
            <Icon icon="solar:close-circle-linear" width="22" />
          </button>
        </div>
      </template>

      <!-- Dossier Body -->
      <div v-if="loadingDossier" class="py-20 flex flex-col items-center justify-center text-center">
        <Icon icon="line-md:loading-loop" width="32" class="text-blue-600 mb-2" />
        <p class="text-xs font-bold text-slate-700">Retrieving Officer Records...</p>
      </div>

      <div v-else-if="activeDossier" class="divide-y divide-slate-100">
        <!-- Officer Primary Identity Banner -->
        <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white">
          <div class="flex items-start gap-4">
            <div class="relative shrink-0">
              <img 
                v-if="activeDossier.profile_image" 
                :src="'/' + activeDossier.profile_image" 
                class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 shadow-lg" 
              />
              <div 
                v-else 
                class="w-16 h-16 rounded-2xl bg-white/10 text-white flex items-center justify-center font-black text-xl border border-white/20 shadow-lg"
              >
                {{ (activeDossier.first_name || '').charAt(0) }}{{ (activeDossier.last_name || '').charAt(0) }}
              </div>
              <span 
                class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-slate-900"
                :class="activeDossier.is_on_leave ? 'bg-amber-400' : 'bg-emerald-400'"
              ></span>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-black tracking-tight text-white truncate">
                  {{ activeDossier.last_name }} {{ activeDossier.first_name }}
                </h3>
              </div>
              <p class="text-xs text-blue-200 font-medium truncate mt-0.5">
                {{ activeDossier.designation?.title || 'Unassigned Desk' }}
              </p>
              <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/15 text-white border border-white/20">
                  IPPIS: {{ activeDossier.ippis_no || 'N/A' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/30 text-blue-200 border border-blue-400/40">
                  GL {{ activeDossier.grade_level || 'N/A' }} - Step {{ activeDossier.step || '1' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Dossier Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-slate-50/70 px-4 text-xs font-bold">
          <button 
            type="button" 
            @click="dossierTab = 'placement'"
            :class="dossierTab === 'placement' ? 'border-b-2 border-blue-600 text-blue-700 bg-white' : 'text-slate-500 hover:text-slate-800'"
            class="px-4 py-2.5 transition-colors cursor-pointer"
          >
            Placement & Hierarchy
          </button>
          <button 
            type="button" 
            @click="dossierTab = 'clearance'"
            :class="dossierTab === 'clearance' ? 'border-b-2 border-blue-600 text-blue-700 bg-white' : 'text-slate-500 hover:text-slate-800'"
            class="px-4 py-2.5 transition-colors cursor-pointer"
          >
            Security & Clearances
          </button>
          <button 
            type="button" 
            @click="dossierTab = 'operational'"
            :class="dossierTab === 'operational' ? 'border-b-2 border-blue-600 text-blue-700 bg-white' : 'text-slate-500 hover:text-slate-800'"
            class="px-4 py-2.5 transition-colors cursor-pointer"
          >
            Workflow Pulse
          </button>
        </div>

        <!-- Tab 1: Structural Placement -->
        <div v-if="dossierTab === 'placement'" class="p-6 space-y-4">
          <div class="space-y-3">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Department</p>
              <p class="text-xs font-bold text-slate-900 mt-0.5">{{ activeDossier.department?.title || 'Global / Unassigned' }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Division</p>
              <p class="text-xs font-bold text-slate-900 mt-0.5">{{ activeDossier.division?.title || 'None Specified' }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unit</p>
              <p class="text-xs font-bold text-slate-900 mt-0.5">{{ activeDossier.unit?.title || 'None Specified' }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Section / Desk</p>
              <p class="text-xs font-bold text-slate-900 mt-0.5">{{ activeDossier.section?.title || 'None Specified' }}</p>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs">
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold">First Appointment</span>
              <p class="font-bold text-slate-800 mt-0.5">{{ activeDossier.date_of_first_appointment || 'Not Provided' }}</p>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold">Confirmation Date</span>
              <p class="font-bold text-slate-800 mt-0.5">{{ activeDossier.date_of_confirmation || 'Not Provided' }}</p>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold">Cadre</span>
              <p class="font-bold text-slate-800 mt-0.5">{{ activeDossier.designation?.cadre || 'General Cadre' }}</p>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold">Gender</span>
              <p class="font-bold text-slate-800 mt-0.5 capitalize">{{ activeDossier.gender || 'Not Specified' }}</p>
            </div>
          </div>
        </div>

        <!-- Tab 2: Security & Clearances -->
        <div v-if="dossierTab === 'clearance'" class="p-6 space-y-4">
          <!-- Roles Clearance -->
          <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Assigned System Roles</p>
            <div class="flex items-center gap-1.5 flex-wrap">
              <span 
                v-for="role in (activeDossier.roles || [])" 
                :key="role.id" 
                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 capitalize"
              >
                {{ role.name }}
              </span>
            </div>
          </div>

          <!-- Digital Signature Preview -->
          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
              <p class="text-xs font-bold text-slate-800">Executive Digital Signature</p>
              <span 
                class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                :class="activeDossier.has_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
              >
                {{ activeDossier.has_signature ? 'Configured' : 'Missing' }}
              </span>
            </div>
            <div v-if="activeDossier.digital_signature_path" class="p-3 bg-white rounded-lg border border-slate-200 max-h-28 flex items-center justify-center">
              <img :src="'/' + activeDossier.digital_signature_path" class="max-h-20 object-contain" />
            </div>
            <p v-else class="text-[11px] text-slate-400 italic">No digital stamp/signature has been uploaded yet.</p>
          </div>

          <!-- Approval PIN & 2FA -->
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase text-slate-400">Approval PIN</p>
              <p class="font-bold mt-1" :class="activeDossier.has_pin ? 'text-emerald-700' : 'text-rose-600'">
                {{ activeDossier.has_pin ? 'Active & Set' : 'Not Set' }}
              </p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
              <p class="text-[10px] font-bold uppercase text-slate-400">Two-Factor Auth</p>
              <p class="font-bold mt-1" :class="activeDossier.is_two_factor_enabled ? 'text-purple-700' : 'text-slate-500'">
                {{ activeDossier.is_two_factor_enabled ? 'Enforced' : 'Disabled' }}
              </p>
            </div>
          </div>

          <!-- Effective Capabilities Summary -->
          <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-200/80 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-indigo-900">Total Effective Capabilities</span>
              <span class="font-extrabold text-indigo-700 bg-white px-2 py-0.5 rounded-md border border-indigo-200 font-mono">
                {{ activeDossierData?.total_permissions_count || 0 }}
              </span>
            </div>
            <p class="text-[11px] text-indigo-600 mt-1">
              Includes {{ activeDossierData?.direct_permissions?.length || 0 }} desk-specific override permissions.
            </p>
          </div>
        </div>

        <!-- Tab 3: Workflow Pulse -->
        <div v-if="dossierTab === 'operational'" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200/80 text-center">
              <p class="text-2xl font-black text-blue-800">{{ activeDossierData?.user?.active_tasks_count || 0 }}</p>
              <p class="text-[11px] font-bold text-blue-600 mt-0.5">Active Files on Desk</p>
            </div>
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 text-center">
              <p class="text-2xl font-black text-emerald-800">{{ activeDossierData?.user?.completed_tasks_count || 0 }}</p>
              <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Completed Dispatches</p>
            </div>
          </div>

          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
            <p class="font-bold text-slate-800">Current Duty Status</p>
            <div class="flex items-center gap-2 mt-2">
              <span 
                class="w-2.5 h-2.5 rounded-full"
                :class="activeDossier.is_on_leave ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"
              ></span>
              <span class="font-bold" :class="activeDossier.is_on_leave ? 'text-amber-800' : 'text-emerald-800'">
                {{ activeDossier.is_on_leave ? 'On Authorized Leave' : 'Active Duty & Present' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Dossier Administrative Quick Actions Footer -->
        <div class="p-5 bg-slate-50 border-t border-slate-200 space-y-2">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Administrative Commands</p>
          
          <div class="grid grid-cols-2 gap-2">
            <!-- Edit Profile -->
            <Link :href="'/dashboard/edit-user?id=' + activeDossier.id" class="w-full">
              <button 
                type="button"
                class="w-full px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <Icon icon="solar:pen-new-square-linear" width="14" />
                <span>Edit Profile</span>
              </button>
            </Link>

            <!-- Reset Password -->
            <button 
              type="button"
              @click="handleSendPasswordReset(activeDossier.id)"
              class="w-full px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
            >
              <Icon icon="solar:lock-password-linear" width="14" />
              <span>Reset Password</span>
            </button>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <!-- Reset Approval PIN -->
            <button 
              type="button"
              @click="handleResetPin(activeDossier.id)"
              class="w-full px-3 py-2 bg-white hover:bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold text-amber-700 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
            >
              <Icon icon="solar:key-linear" width="14" />
              <span>Reset PIN</span>
            </button>

            <!-- Clear Digital Signature -->
            <button 
              type="button"
              @click="handleResetSignature(activeDossier.id)"
              class="w-full px-3 py-2 bg-white hover:bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
            >
              <Icon icon="solar:trash-bin-trash-linear" width="14" />
              <span>Clear Signature</span>
            </button>
          </div>

          <!-- Suspend / Activate Account -->
          <button 
            type="button"
            @click="handleToggleStatus(activeDossier)"
            class="w-full mt-2 px-3 py-2 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
            :class="activeDossier.is_approved ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
          >
            <Icon :icon="activeDossier.is_approved ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone'" width="15" />
            <span>{{ activeDossier.is_approved ? 'Deactivate / Suspend Officer Account' : 'Reactivate Officer Account' }}</span>
          </button>
        </div>
      </div>
    </Sidebar>

    <!-- Modals -->
    <DeleteUsers v-if="deleteModal" @confirmed="handleDelete" @cancelled="cancelDelete" />
    <AccountRequestModal v-model="account_request" :notifications="props.notifications" />
    <Toast />
  </main>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from "primevue/usetoast";
import axios from "axios";
import DeleteUsers from "../../components/Modals/DeleteUsers.vue";
import AccountRequestModal from "../../components/Modals/AccountRequestModal.vue";

const props = defineProps({
  users: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  notifications: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  departments: { type: Array, default: () => [] },
});

const toast = useToast();

// State
const deleteModal = ref(false);
const itemIdToDelete = ref(null);
const account_request = ref(false);
const viewMode = ref('flat'); // 'flat' | 'grouped'
const isExporting = ref(false);
const selectedUsers = ref([]);

const setViewMode = (mode) => {
  viewMode.value = mode;
};

// Filters
const selectedRole = ref(null);
const selectedDepartment = ref(null);
const selectedGradeBand = ref(null);
const selectedComplianceStatus = ref(null);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Row Context Menu
const rowMenuRef = ref(null);
const selectedRowUser = ref(null);

// 360° Dossier Drawer State
const showDossier = ref(false);
const loadingDossier = ref(false);
const activeDossier = ref(null);
const activeDossierData = ref(null);
const dossierTab = ref('placement'); // 'placement' | 'clearance' | 'operational'

// Compute Raw Users Array
const rawUsers = computed(() => {
  if (Array.isArray(props.users) && props.users.length) {
    return props.users;
  }
  // Fallback for legacy roles.users prop structure
  let extracted = [];
  (props.roles || []).forEach(role => {
    (role.users || []).forEach(u => {
      extracted.push({
        ...u,
        primary_role: role.name,
        role_sort: role.sort_order || 99,
        roles: [{ id: role.id, name: role.name }],
        role_names: [role.name],
      });
    });
  });
  return extracted;
});

// Filter Options
const departmentOptions = computed(() => {
  return (props.departments || []).map(d => ({ label: d.title, value: d.id }));
});

const roleOptions = computed(() => {
  return (props.roles || []).map(r => ({ label: capitalize(r.name), value: r.name }));
});

const gradeBandOptions = ref([
  { label: 'Executive Management (GL 15-17)', value: 'executive' },
  { label: 'Senior Directorate (GL 14)', value: 'directorate' },
  { label: 'Officer Cadre (GL 8-13)', value: 'officer' },
  { label: 'Junior Cadre (GL 1-7)', value: 'junior' },
]);

const complianceStatusOptions = ref([
  { label: 'Active Duty Only', value: 'active_duty' },
  { label: 'Currently on Leave', value: 'on_leave' },
  { label: 'Missing Digital Signature', value: 'missing_sig' },
  { label: 'Missing Approval PIN', value: 'missing_pin' },
  { label: '2FA Enabled Only', value: '2fa_enabled' },
]);

const hasActiveFilters = computed(() => {
  return !!(filters.value.global.value || selectedDepartment.value || selectedRole.value || selectedGradeBand.value || selectedComplianceStatus.value);
});

const clearAllFilters = () => {
  filters.value.global.value = '';
  selectedDepartment.value = null;
  selectedRole.value = null;
  selectedGradeBand.value = null;
  selectedComplianceStatus.value = null;
};

// Filtered Users List
const displayUsers = computed(() => {
  let list = rawUsers.value.filter(user => {
    // 1. Department filter
    if (selectedDepartment.value && user.department?.id !== selectedDepartment.value) {
      return false;
    }
    // 2. Role filter
    if (selectedRole.value) {
      const roles = user.role_names || (user.roles || []).map(r => r.name);
      if (!roles.includes(selectedRole.value)) return false;
    }
    // 3. Grade Band filter
    if (selectedGradeBand.value) {
      const gl = Number(user.grade_level || 0);
      if (selectedGradeBand.value === 'executive' && gl < 15) return false;
      if (selectedGradeBand.value === 'directorate' && gl !== 14) return false;
      if (selectedGradeBand.value === 'officer' && (gl < 8 || gl > 13)) return false;
      if (selectedGradeBand.value === 'junior' && (gl < 1 || gl > 7)) return false;
    }
    // 4. Compliance Status filter
    if (selectedComplianceStatus.value) {
      if (selectedComplianceStatus.value === 'active_duty' && user.is_on_leave) return false;
      if (selectedComplianceStatus.value === 'on_leave' && !user.is_on_leave) return false;
      if (selectedComplianceStatus.value === 'missing_sig' && user.has_signature) return false;
      if (selectedComplianceStatus.value === 'missing_pin' && user.has_pin) return false;
      if (selectedComplianceStatus.value === '2fa_enabled' && !user.is_two_factor_enabled) return false;
    }
    return true;
  });

  // Always default to alphabetical sort by last name for crisp predictability
  return [...list].sort((a, b) => (a.last_name || '').localeCompare(b.last_name || ''));
});

// Grouped Cadre Organization for 'By Role' view
const roleCadreGroups = computed(() => {
  const groups = [];
  const rolesList = (props.roles && props.roles.length) ? props.roles : [];
  
  // If user selected a specific role in the cadre ribbon, only show that role
  const activeRoles = selectedRole.value 
    ? rolesList.filter(r => r.name.toLowerCase() === selectedRole.value.toLowerCase())
    : rolesList;

  activeRoles.forEach(role => {
    const matchingUsers = displayUsers.value.filter(u => 
      (u.primary_role && u.primary_role.toLowerCase() === role.name.toLowerCase()) ||
      (u.role_names && u.role_names.some(rn => rn.toLowerCase() === role.name.toLowerCase()))
    );

    if (matchingUsers.length > 0 || selectedRole.value) {
      groups.push({
        id: role.id,
        name: role.name,
        description: role.description || `Civil service officers assigned to ${role.name} tier`,
        users: matchingUsers,
      });
    }
  });

  // Also include officers with no role or unrecognized role if "All Roles" is selected
  if (!selectedRole.value) {
    const unassigned = displayUsers.value.filter(u => 
      !u.primary_role || !rolesList.some(r => r.name.toLowerCase() === (u.primary_role || '').toLowerCase())
    );
    if (unassigned.length > 0) {
      groups.push({
        id: 'unassigned',
        name: 'General Staff / Unassigned Cadre',
        description: 'Personnel without elevated Spatie security roles',
        users: unassigned,
      });
    }
  }

  return groups;
});

const navigateToEdit = (id) => {
  if (!id) return;
  router.visit('/dashboard/edit-user?id=' + id);
};

const exportCadreCsv = (cadre) => {
  if (!cadre || !cadre.users.length) {
    toast.add({ severity: 'info', summary: 'Export', detail: 'No officers to export in this cadre.', life: 2500 });
    return;
  }
  const headers = ['IPPIS No,Last Name,First Name,Email,Phone,Department,Designation,Cadre,GL'];
  const rows = cadre.users.map(u => [
    u.ippis_no || 'N/A',
    `"${u.last_name}"`,
    `"${u.first_name}"`,
    `"${u.email}"`,
    `"${u.phone || 'N/A'}"`,
    `"${u.department?.title || 'None'}"`,
    `"${u.designation?.title || 'Unassigned'}"`,
    `"${u.designation?.cadre || 'General'}"`,
    u.grade_level || 'N/A'
  ].join(','));

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers, ...rows].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `${cadre.name.toLowerCase().replace(/\s+/g, '_')}_cadre_officers.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const groupedUserCounts = computed(() => {
  const counts = {};
  rawUsers.value.forEach(u => {
    const role = u.primary_role || 'regular user';
    counts[role] = (counts[role] || 0) + 1;
  });
  return counts;
});

const getGroupedUserCount = (roleName) => {
  return groupedUserCounts.value[roleName] || 0;
};

const signatureComplianceRate = computed(() => {
  const total = rawUsers.value.length;
  if (!total) return 0;
  const compliant = rawUsers.value.filter(u => u.has_signature && u.has_pin).length;
  return Math.round((compliant / total) * 100);
});

const getUserCountForRole = (roleName) => {
  return rawUsers.value.filter(u => (u.primary_role === roleName) || (u.role_names || []).includes(roleName)).length;
};

const getDeptTitle = (deptId) => {
  const dept = (props.departments || []).find(d => d.id === deptId);
  return dept ? dept.title : 'Department';
};

const capitalize = (str) => {
  if (!str) return '';
  return str.replace(/\b\w/g, char => char.toUpperCase());
};

// Row Context Menu Actions
const toggleRowMenu = (event, user) => {
  if (event?.stopPropagation) {
    event.stopPropagation();
  }
  selectedRowUser.value = user;
  if (rowMenuRef.value) {
    rowMenuRef.value.toggle(event);
  }
};

const rowMenuItems = computed(() => {
  if (!selectedRowUser.value) return [];
  const u = selectedRowUser.value;
  return [
    {
      label: 'View 360° Dossier',
      icon: 'pi pi-user',
      command: () => openOfficerDossier(u.id),
    },
    {
      label: 'Edit Officer Profile',
      icon: 'pi pi-pencil',
      command: () => router.visit('/dashboard/edit-user?id=' + u.id),
    },
    { separator: true },
    {
      label: u.is_approved ? 'Suspend Officer Account' : 'Reactivate Officer Account',
      icon: u.is_approved ? 'pi pi-ban' : 'pi pi-check',
      command: () => handleToggleStatus(u),
    },
    {
      label: 'Reset Approval PIN',
      icon: 'pi pi-key',
      command: () => handleResetPin(u.id),
    },
    {
      label: 'Clear Digital Signature',
      icon: 'pi pi-trash',
      command: () => handleResetSignature(u.id),
    },
    {
      label: 'Send Password Reset Link',
      icon: 'pi pi-send',
      command: () => handleSendPasswordReset(u.id),
    },
    { separator: true },
    {
      label: 'Delete Officer Record',
      icon: 'pi pi-trash text-red-500',
      command: () => showDeleteDialog(u.id),
    },
  ];
});

// 360° Dossier Drawer
const openOfficerDossier = (userId) => {
  showDossier.value = true;
  loadingDossier.value = true;
  dossierTab.value = 'placement';
  
  axios.get('/dashboard/manage-users/dossier/' + userId)
    .then(res => {
      activeDossierData.value = res.data;
      activeDossier.value = res.data.user;
      loadingDossier.value = false;
    })
    .catch(err => {
      loadingDossier.value = false;
      toast.add({
        severity: 'error',
        summary: 'Record Load Failed',
        detail: 'Could not fetch officer dossier records.',
        life: 3000,
      });
    });
};

// Administrative Operations Handlers
const handleToggleStatus = (user) => {
  const newStatus = user.is_approved ? 0 : 1;
  router.post('/dashboard/manage-users/toggle-status', {
    id: user.id,
    is_approved: newStatus,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Status Updated',
        detail: `Officer account ${newStatus ? 'activated' : 'suspended'} successfully.`,
        life: 3000,
      });
      if (activeDossier.value && activeDossier.value.id === user.id) {
        activeDossier.value.is_approved = newStatus;
      }
    },
    onError: (err) => {
      toast.add({
        severity: 'error',
        summary: 'Action Blocked',
        detail: Object.values(err)[0] || 'Could not update officer status.',
        life: 4000,
      });
    }
  });
};

const handleResetPin = (userId) => {
  router.post('/dashboard/manage-users/reset-pin', { id: userId }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Approval PIN Reset',
        detail: 'Officer approval PIN has been cleared. They will be prompted to re-create it on next sign-off.',
        life: 3500,
      });
      if (activeDossier.value && activeDossier.value.id === userId) {
        activeDossier.value.has_pin = false;
      }
    }
  });
};

const handleResetSignature = (userId) => {
  router.post('/dashboard/manage-users/reset-signature', { id: userId }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Signature Cleared',
        detail: 'Digital signature stamp removed. Officer must upload a new authorized signature.',
        life: 3500,
      });
      if (activeDossier.value && activeDossier.value.id === userId) {
        activeDossier.value.has_signature = false;
        activeDossier.value.digital_signature_path = null;
      }
    }
  });
};

const handleSendPasswordReset = (userId) => {
  router.post('/dashboard/manage-users/send-password-reset', { id: userId }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Reset Link Sent',
        detail: 'A password reset link was dispatched to the officer official email.',
        life: 3500,
      });
    }
  });
};

// CSV Export
const exportCsv = () => {
  isExporting.value = true;
  const params = new URLSearchParams();
  if (selectedDepartment.value) params.append('department_id', selectedDepartment.value);
  if (selectedRole.value) params.append('role', selectedRole.value);
  if (filters.value.global.value) params.append('search', filters.value.global.value);

  window.location.href = '/dashboard/manage-users/export-csv?' + params.toString();
  setTimeout(() => { isExporting.value = false; }, 2000);
};

const exportSelectedUsersCsv = () => {
  if (!selectedUsers.value.length) return;
  const headers = ['IPPIS No,Last Name,First Name,Email,Phone,Department,Designation,Cadre,GL'];
  const rows = selectedUsers.value.map(u => [
    u.ippis_no || 'N/A',
    `"${u.last_name}"`,
    `"${u.first_name}"`,
    `"${u.email}"`,
    `"${u.phone || 'N/A'}"`,
    `"${u.department?.title || 'None'}"`,
    `"${u.designation?.title || 'Unassigned'}"`,
    `"${u.designation?.cadre || 'General'}"`,
    u.grade_level || 'N/A'
  ].join(','));

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers, ...rows].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `selected_officers_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Delete Handlers
const showDeleteDialog = (itemId) => {
  itemIdToDelete.value = itemId;
  deleteModal.value = true;
};

const handleDelete = () => {
  axios.get('/dashboard/delete-user/' + itemIdToDelete.value).then(() => {
    itemIdToDelete.value = null;
    deleteModal.value = false;
    toast.add({
      severity: 'success',
      summary: 'Officer Record Deleted',
      detail: 'The personnel record has been soft-deleted from active registry.',
      life: 3000,
    });
    router.reload({ only: ['users', 'roles', 'stats'] });
  });
};

const cancelDelete = () => {
  deleteModal.value = false;
};
</script>

<style scoped>
:deep(.p-datatable .p-datatable-thead > tr > th) {
  background: #f8fafc;
  color: #475569;
  font-weight: 700;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
  background: #f8fafc;
}

:deep(.manage-users-table tr.p-rowgroup-header td) {
  padding: 0 !important;
  background: transparent !important;
  border: none !important;
}

:deep(.p-sidebar-header) {
  padding: 0 !important;
  border: none !important;
}

:deep(.p-paginator) {
  background: transparent;
  padding: 0.75rem 1rem;
  border-top: 1px solid #f1f5f9;
  font-size: 11px;
}
</style>
