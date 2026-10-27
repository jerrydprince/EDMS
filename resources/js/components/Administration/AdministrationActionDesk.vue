<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden font-sans">
    <!-- Header banner -->
    <div class="bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white p-5 border-b border-blue-900/40">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">
              Department 14: Administration & Human Resources
            </span>
            <span class="text-xs text-slate-300">Public Service Rules (PSR 2021 Edition) • SOP Version 1.0</span>
          </div>
          <h2 class="text-xl font-bold mt-1 tracking-tight text-white flex items-center gap-2">
            <span>Statutory Administration & HR Action Deck</span>
            <Icon icon="lucide:shield-check" class="text-emerald-400" width="20" />
          </h2>
          <p class="text-xs text-slate-300 mt-0.5">
            National Federal Treasury Pool Register • Nominal Roll • 24-Month Confirmation • APER Vetting • 48-Hr Welfare SLA • 24-Hr Table SLA Watchdog
          </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="openStampModal = true"
            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
            title="Configure Digital Circular SVG Stamp or Uploaded Seal"
          >
            <Icon icon="lucide:stamp" width="16" class="text-amber-400" />
            <span>Digital Stamp</span>
          </button>
          <button
            v-if="activeTab === 'queries'"
            @click="openIssueQueryModal = true"
            class="px-3.5 py-2 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Icon icon="lucide:alert-triangle" width="16" />
            <span>Issue PSR Query</span>
          </button>
          <button
            @click="refreshCurrentTab"
            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl transition-all cursor-pointer"
            title="Refresh"
          >
            <Icon icon="lucide:refresh-cw" width="16" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>

      <!-- Action Tabs Navigation -->
      <div class="flex items-center gap-2 mt-4 pt-3 border-t border-white/10 overflow-x-auto">
        <!-- TAB: Federal Accountants Pool (Strictly DA + AD Pool) -->
        <button
          v-if="canAccessPool"
          @click="switchTab('pool')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'pool' ? 'bg-amber-400 text-slate-950 shadow-sm' : 'text-amber-300 bg-amber-400/10 hover:bg-amber-400/20 border border-amber-400/30']"
        >
          <Icon icon="lucide:globe" width="14" />
          <span>National Treasury Pool (GL 08–17)</span>
        </button>

        <button
          @click="switchTab('nominal')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'nominal' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:users" width="14" />
          <span>Nominal Roll & Retirement</span>
        </button>

        <button
          @click="switchTab('confirmations')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'confirmations' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:award" width="14" />
          <span>Confirmations & APER (Sec 1.3/1.4)</span>
        </button>

        <button
          @click="switchTab('leaves')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'leaves' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:calendar-days" width="14" />
          <span>Statutory Leaves Desk (Sec 91)</span>
        </button>

        <button
          @click="switchTab('queries')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'queries' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:shield-alert" width="14" />
          <span>PSR Misconduct & Queries (Sec 90)</span>
        </button>

        <button
          @click="switchTab('welfare')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'welfare' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:heart-handshake" width="14" />
          <span>Welfare & Training (48-Hr SLA)</span>
        </button>

        <button
          @click="switchTab('sub_registry')"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 shrink-0', activeTab === 'sub_registry' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:folder-sync" width="14" />
          <span>Sub-Registries & Appendix A (24-Hr SLA)</span>
        </button>
      </div>
    </div>

    <!-- Active Content Area -->
    <div class="p-5">
      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center text-slate-500">
        <Icon icon="lucide:loader" width="28" class="animate-spin mx-auto mb-2 text-blue-600" />
        <p class="text-sm">Accessing civil service registers...</p>
      </div>

      <!-- TAB: National Treasury Pool Management -->
      <div v-else-if="activeTab === 'pool' && canAccessPool">
        <PoolManagementDesk />
      </div>

      <!-- TAB: Confirmations & APER Lifecycle -->
      <div v-else-if="activeTab === 'confirmations'">
        <ConfirmationAperDesk />
      </div>

      <!-- TAB: Welfare & Training -->
      <div v-else-if="activeTab === 'welfare'">
        <TrainingWelfareDesk />
      </div>

      <!-- TAB: Sub-Registries & Appendix A Movement -->
      <div v-else-if="activeTab === 'sub_registry'">
        <AdminSubRegistryDesk />
      </div>

      <!-- TAB 1: Nominal Roll & Gazetting -->
      <div v-else-if="activeTab === 'nominal'">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
          <div class="relative max-w-sm w-full">
            <Icon icon="lucide:search" width="16" class="absolute left-3 top-3 text-slate-400" />
            <input
              v-model="nominalSearch"
              @input="debouncedNominalSearch"
              type="text"
              placeholder="Search staff by name, email, IPPIS..."
              class="w-full text-xs pl-9 pr-3 py-2 rounded-lg border border-slate-300 focus:border-blue-500 outline-none"
            />
          </div>
          <div class="text-xs text-slate-500 flex items-center gap-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Active Staff
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 ml-2"></span> Confirmation Due
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 ml-2"></span> Retirement Near / Due
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-3">Officer Name / IPPIS</th>
                <th class="py-3 px-3">Directorate / Placement</th>
                <th class="py-3 px-3">GL / Step</th>
                <th class="py-3 px-3">Leave Days (PSR)</th>
                <th class="py-3 px-3">PSR 35/60 Retirement</th>
                <th class="py-3 px-3 text-center">Audit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="u in nominalUsers" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-3">
                  <div class="font-bold text-slate-900">{{ u.first_name }} {{ u.last_name }}</div>
                  <div class="text-[11px] text-slate-500">{{ u.email }}</div>
                  <div class="text-[11px] font-mono text-blue-700 font-bold mt-0.5">
                    IPPIS: {{ u.ippis_no || 'NOT ASSIGNED' }}
                  </div>
                </td>
                <td class="py-3 px-3">
                  <div class="font-semibold text-slate-800">{{ u.department?.name || 'Department' }}</div>
                  <div class="text-[11px] text-slate-500">
                    {{ u.division?.title || 'Division' }} • {{ u.section?.title || 'Section' }}
                  </div>
                </td>
                <td class="py-3 px-3">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-bold border border-slate-200">
                    GL {{ u.grade_level || '08' }} / {{ u.step || '1' }}
                  </span>
                </td>
                <td class="py-3 px-3">
                  <span class="font-bold text-slate-800">
                    {{ u.statutory_data?.annual_leave_entitlement_days || 30 }} Days
                  </span>
                  <div class="text-[10px] text-slate-500">per calendar annum</div>
                </td>
                <td class="py-3 px-3">
                  <div class="font-semibold text-slate-900">
                    {{ u.statutory_data?.statutory_retirement_date || 'N/A' }}
                  </div>
                  <div
                    class="text-[10px] font-bold mt-0.5"
                    :class="{
                      'text-red-600': u.statutory_data?.is_due_retirement,
                      'text-amber-600': u.statutory_data?.days_to_retirement <= 180 && !u.statutory_data?.is_due_retirement,
                      'text-slate-500': u.statutory_data?.days_to_retirement > 180
                    }"
                  >
                    {{ u.statutory_data?.years_of_service }} Yrs in Service ({{ u.statutory_data?.days_to_retirement }} Days left)
                  </div>
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    @click="openVerifyUserModal(u)"
                    class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:user-check" width="12" />
                    <span>Audit / IPPIS</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: Statutory Leaves Desk -->
      <div v-else-if="activeTab === 'leaves'">
        <div v-if="leavesList.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-xl">
          <Icon icon="lucide:calendar-check" width="36" class="mx-auto text-slate-400 mb-2" />
          <h4 class="text-sm font-semibold text-slate-700">No Pending Leave Applications</h4>
          <p class="text-xs text-slate-500 mt-1">All officer leave schedules are fully audited.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-3">Applicant Officer</th>
                <th class="py-3 px-3">Leave Type</th>
                <th class="py-3 px-3">Duration & Dates</th>
                <th class="py-3 px-3">Relief Officer</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="leave in leavesList" :key="leave.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-3">
                  <div class="font-bold text-slate-900">{{ leave.user?.first_name }} {{ leave.user?.last_name }}</div>
                  <div class="text-[11px] text-slate-500">{{ leave.user?.department?.name || 'Department' }}</div>
                </td>
                <td class="py-3 px-3 font-semibold text-slate-800">{{ leave.leave_type || 'Annual Leave' }}</td>
                <td class="py-3 px-3">
                  <div class="text-slate-800 font-medium">{{ leave.start_date }} to {{ leave.end_date }}</div>
                  <div class="text-[10px] text-slate-500">Destination: {{ leave.destination || 'Nigeria' }}</div>
                </td>
                <td class="py-3 px-3">
                  <div class="font-medium text-slate-800">{{ leave.relief_user?.first_name }} {{ leave.relief_user?.last_name }}</div>
                </td>
                <td class="py-3 px-3">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-amber-50 text-amber-700 border border-amber-200': leave.status === 'pending',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': leave.status === 'approved',
                      'bg-red-50 text-red-700 border border-red-200': leave.status === 'rejected',
                    }"
                  >
                    {{ leave.status }}
                  </span>
                </td>
                <td class="py-3 px-3 text-center">
                  <div v-if="leave.status === 'pending'" class="flex items-center justify-center gap-1.5">
                    <button
                      @click="processLeaveAction(leave.id, 'approve')"
                      class="px-2 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer"
                    >
                      Approve
                    </button>
                    <button
                      @click="processLeaveAction(leave.id, 'reject')"
                      class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer"
                    >
                      Reject
                    </button>
                  </div>
                  <span v-else class="text-[11px] text-slate-400">Processed</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: PSR Disciplinary Queries -->
      <div v-else-if="activeTab === 'queries'">
        <div v-if="queriesList.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-xl">
          <Icon icon="lucide:shield-check" width="36" class="mx-auto text-emerald-500 mb-2" />
          <h4 class="text-sm font-semibold text-slate-700">No Disciplinary Queries on Record</h4>
          <p class="text-xs text-slate-500 mt-1">Full compliance with Public Service Rules observed.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-3">Query Ref / Subject</th>
                <th class="py-3 px-3">Issued To</th>
                <th class="py-3 px-3">Category</th>
                <th class="py-3 px-3">Deadline & Countdown</th>
                <th class="py-3 px-3">Committee Verdict</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="q in queriesList" :key="q.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3 px-3">
                  <div class="font-bold text-slate-900">{{ q.subject }}</div>
                  <div class="text-[11px] font-mono text-red-600 font-bold mt-0.5">{{ q.query_ref }}</div>
                </td>
                <td class="py-3 px-3">
                  <div class="font-semibold text-slate-800">{{ q.recipient?.first_name }} {{ q.recipient?.last_name }}</div>
                  <div class="text-[11px] text-slate-500">{{ q.recipient?.department?.name || 'Department' }}</div>
                </td>
                <td class="py-3 px-3">
                  <span
                    class="px-2 py-0.5 rounded text-[11px] font-bold"
                    :class="q.incident_category === 'Serious Misconduct' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                  >
                    {{ q.incident_category }}
                  </span>
                </td>
                <td class="py-3 px-3">
                  <div class="font-bold text-slate-800">{{ q.deadline_hours }} Hours Limit</div>
                  <div class="text-[10px] text-slate-500">Due: {{ q.deadline_at }}</div>
                </td>
                <td class="py-3 px-3">
                  <span
                    v-if="q.verdict"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200"
                  >
                    {{ q.verdict }}
                  </span>
                  <span v-else class="text-[11px] text-amber-600 font-bold">
                    Pending Adjudication
                  </span>
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    v-if="!q.verdict"
                    @click="openVerdictModal(q)"
                    class="px-2.5 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:scale" width="12" />
                    <span>Adjudicate</span>
                  </button>
                  <span v-else class="text-[11px] text-slate-400">Decided</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Verify / Audit Nominal Roll Officer -->
    <div
      v-if="selectedUser"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-blue-900 to-slate-900 text-white p-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:user-cog" width="18" class="text-blue-400" />
            <h3 class="font-bold text-sm">Audit & IPPIS Record Verification</h3>
          </div>
          <button @click="selectedUser = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitVerifyUser" class="p-5 space-y-4">
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
            <div class="font-bold text-slate-900">{{ selectedUser.first_name }} {{ selectedUser.last_name }}</div>
            <div class="text-slate-600 mt-0.5">{{ selectedUser.email }} • {{ selectedUser.department?.name }}</div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">IPPIS Number *</label>
              <input
                v-model="userForm.ippis_no"
                type="text"
                required
                placeholder="e.g. IPPIS-98214"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Grade Level (GL) *</label>
              <input
                v-model.number="userForm.grade_level"
                type="number"
                min="1"
                max="17"
                required
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none font-bold"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Step</label>
              <input
                v-model.number="userForm.step"
                type="number"
                min="1"
                max="15"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
              <input
                v-model="userForm.date_of_birth"
                type="date"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Date of First Appointment</label>
              <input
                v-model="userForm.date_of_first_appointment"
                type="date"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Confirmation</label>
              <input
                v-model="userForm.date_of_confirmation"
                type="date"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-blue-500 outline-none"
              />
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="selectedUser = null"
              class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Save & Verify Record</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Issue PSR 2021 Query -->
    <div
      v-if="openIssueQueryModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-red-900 to-slate-900 text-white p-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:alert-octagon" width="18" class="text-red-400" />
            <h3 class="font-bold text-sm">Issue Disciplinary Query (PSR 2021)</h3>
          </div>
          <button @click="openIssueQueryModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitIssueQuery" class="p-5 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Officer (Respondent) *</label>
            <select
              v-model="queryForm.issued_to"
              required
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-red-500 outline-none"
            >
              <option value="" disabled>Select officer to query...</option>
              <option v-for="u in nominalUsers" :key="u.id" :value="u.id">
                {{ u.first_name }} {{ u.last_name }} ({{ u.department?.name }})
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">PSR Incident Category *</label>
              <select
                v-model="queryForm.incident_category"
                required
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-red-500 outline-none"
              >
                <option value="Misconduct">Misconduct (PSR 030301)</option>
                <option value="Serious Misconduct">Serious Misconduct (PSR 030401)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Response Deadline *</label>
              <select
                v-model.number="queryForm.deadline_hours"
                required
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-red-500 outline-none font-bold"
              >
                <option :value="48">48 Hours (Standard)</option>
                <option :value="72">72 Hours (Extended)</option>
                <option :value="24">24 Hours (Urgent)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Subject / Allegation *</label>
            <input
              v-model="queryForm.subject"
              type="text"
              required
              placeholder="e.g. Dereliction of Official Duty and Unauthorized Absence"
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-red-500 outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Facts & Circumstances of Infraction *</label>
            <textarea
              v-model="queryForm.facts_description"
              rows="4"
              required
              placeholder="Explicitly detail the dates, times, official duties neglected, and specific Public Service Rules breached..."
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-red-500 outline-none"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="openIssueQueryModal = false"
              class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Issue Official Query</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Adjudicate Query Verdict -->
    <div
      v-if="selectedQuery"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-purple-900 to-slate-900 text-white p-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:gavel" width="18" class="text-purple-400" />
            <h3 class="font-bold text-sm">Disciplinary Committee Adjudication</h3>
          </div>
          <button @click="selectedQuery = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitVerdict" class="p-5 space-y-4">
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
            <div class="font-bold text-slate-900">{{ selectedQuery.subject }}</div>
            <div class="text-slate-600 mt-1">Respondent: <strong>{{ selectedQuery.recipient?.first_name }} {{ selectedQuery.recipient?.last_name }}</strong></div>
            <div class="text-slate-600 mt-0.5">Ref: <span class="font-mono">{{ selectedQuery.query_ref }}</span></div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Committee Verdict *</label>
            <select
              v-model="verdictForm.verdict"
              required
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-purple-500 outline-none font-bold"
            >
              <option value="accepted">Exonerated (Explanation Accepted)</option>
              <option value="cautioned">Written Caution / Reprimand</option>
              <option value="under_review">Under Further Review</option>
              <option value="escalated_to_dc">Escalated to Senior Disciplinary Committee (Suspension/Dismissal)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Committee Findings & Legal Rationale *</label>
            <textarea
              v-model="verdictForm.verdict_comment"
              rows="4"
              required
              placeholder="Record committee evaluation of officer's written representation, mitigating factors, and administrative ruling..."
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-purple-500 outline-none"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="selectedQuery = null"
              class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Record Committee Verdict</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Digital Stamp Modal -->
    <DigitalStampModal :show="openStampModal" @close="openStampModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { usePermissions } from '../../Composables/usePermissions';
import PoolManagementDesk from './PoolManagementDesk.vue';
import ConfirmationAperDesk from './ConfirmationAperDesk.vue';
import TrainingWelfareDesk from './TrainingWelfareDesk.vue';
import AdminSubRegistryDesk from './AdminSubRegistryDesk.vue';
import DigitalStampModal from './DigitalStampModal.vue';

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  department: { type: Object, default: () => ({}) },
  initialTab: { type: String, default: null }
});

const page = usePage();
const { can, hasRole } = usePermissions();

const loading = ref(false);
const saving = ref(false);
const openStampModal = ref(false);

// Strict civil service boundary for National Federal Accountants Pool:
// Restricted strictly to Director of Administration and AD Pool Management (Dept 14),
// or users with explicit Spatie 'pool' permissions, or Super Admins.
const canAccessPool = computed(() => {
  if (hasRole('super admin') || hasRole('super user')) return true;

  const deptId = Number(props.user?.department_id || page.props.user?.department_id || page.props.userDepartment?.id || 0);
  if (deptId !== 14) return false;

  // Director of Administration
  if (hasRole('director')) return true;

  // Assistant Director (AD) Pool Management
  const userObj = props.user?.id ? props.user : (page.props.user || {});
  const desig = (userObj?.designation?.name || userObj?.designation || '').toLowerCase();
  if (hasRole('assistant director') && (desig.includes('pool') || desig.includes('posting'))) {
    return true;
  }

  // Explicit Spatie permissions
  if (can('pool view') || can('pool manage')) {
    return true;
  }

  return false;
});

// Default to initialTab or 'pool' tab if authorized, otherwise 'nominal'
const activeTab = ref(props.initialTab || (canAccessPool.value ? 'pool' : 'nominal'));

watch(() => props.initialTab, (newTab) => {
  if (newTab) {
    activeTab.value = newTab;
    refreshCurrentTab();
  }
});

// Tab 1: Nominal
const nominalUsers = ref([]);
const nominalSearch = ref('');
const selectedUser = ref(null);
const userForm = ref({
  ippis_no: '',
  grade_level: 8,
  step: 1,
  date_of_birth: '',
  date_of_first_appointment: '',
  date_of_confirmation: '',
});

// Tab 2: Leaves
const leavesList = ref([]);

// Tab 3: Queries
const queriesList = ref([]);
const openIssueQueryModal = ref(false);
const selectedQuery = ref(null);
const queryForm = ref({
  issued_to: '',
  incident_category: 'Misconduct',
  deadline_hours: 48,
  subject: '',
  facts_description: '',
});
const verdictForm = ref({
  verdict: 'cautioned',
  verdict_comment: '',
});

const switchTab = (tab) => {
  activeTab.value = tab;
  refreshCurrentTab();
};

const refreshCurrentTab = () => {
  if (activeTab.value === 'nominal') fetchNominalRoll();
  else if (activeTab.value === 'leaves') fetchLeaves();
  else if (activeTab.value === 'queries') fetchQueries();
};

let debounceTimeout = null;
const debouncedNominalSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchNominalRoll();
  }, 400);
};

const fetchNominalRoll = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/dashboard/administration/nominal-roll', {
      params: { search: nominalSearch.value }
    });
    nominalUsers.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to fetch nominal roll:', err);
  } finally {
    loading.value = false;
  }
};

const fetchLeaves = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/dashboard/administration/leaves');
    leavesList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to fetch leaves:', err);
  } finally {
    loading.value = false;
  }
};

const fetchQueries = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/dashboard/administration/queries');
    queriesList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to fetch queries:', err);
  } finally {
    loading.value = false;
  }
};

const openVerifyUserModal = (u) => {
  selectedUser.value = u;
  userForm.value = {
    ippis_no: u.ippis_no || '',
    grade_level: u.grade_level || 8,
    step: u.step || 1,
    date_of_birth: u.date_of_birth || '',
    date_of_first_appointment: u.date_of_first_appointment || '',
    date_of_confirmation: u.date_of_confirmation || '',
  };
};

const submitVerifyUser = async () => {
  if (!selectedUser.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/administration/nominal-roll/${selectedUser.value.id}/verify`, userForm.value);
    alert(res.data.message || 'Nominal roll record verified.');
    selectedUser.value = null;
    fetchNominalRoll();
  } catch (err) {
    alert(err.response?.data?.message || 'Error updating nominal roll.');
  } finally {
    saving.value = false;
  }
};

const processLeaveAction = async (leaveId, action) => {
  if (!confirm(`Are you sure you want to ${action} this leave request?`)) return;
  try {
    const res = await axios.post(`/dashboard/administration/leaves/${leaveId}/process`, {
      action: action,
      supervisor_comment: `Processed by Administration Action Desk.`
    });
    alert(res.data.message || 'Leave request processed.');
    fetchLeaves();
  } catch (err) {
    alert(err.response?.data?.message || 'Error processing leave.');
  }
};

const submitIssueQuery = async () => {
  saving.value = true;
  try {
    const res = await axios.post('/dashboard/administration/queries/issue', queryForm.value);
    alert(res.data.message || 'Query issued successfully.');
    openIssueQueryModal.value = false;
    queryForm.value = {
      issued_to: '',
      incident_category: 'Misconduct',
      deadline_hours: 48,
      subject: '',
      facts_description: '',
    };
    fetchQueries();
  } catch (err) {
    alert(err.response?.data?.message || 'Error issuing query.');
  } finally {
    saving.value = false;
  }
};

const openVerdictModal = (query) => {
  selectedQuery.value = query;
  verdictForm.value = {
    verdict: 'cautioned',
    verdict_comment: '',
  };
};

const submitVerdict = async () => {
  if (!selectedQuery.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/administration/queries/${selectedQuery.value.id}/resolve`, verdictForm.value);
    alert(res.data.message || 'Verdict recorded.');
    selectedQuery.value = null;
    fetchQueries();
  } catch (err) {
    alert(err.response?.data?.message || 'Error recording verdict.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  if (activeTab.value === 'nominal') {
    fetchNominalRoll();
  }
});
</script>
