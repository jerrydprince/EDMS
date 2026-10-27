<template>
  <div class="space-y-6">
    <!-- Top Statutory Pool Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white shadow-md border border-blue-900/60 relative overflow-hidden">
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-3 py-0.5 rounded-full text-[11px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30 uppercase tracking-wider">
              National Central Register
            </span>
            <span class="text-xs text-slate-300">Section 1.1 • Standard Operating Procedures</span>
          </div>
          <h2 class="text-xl font-black mt-1 tracking-tight text-white flex items-center gap-2">
            <span>Federal Treasury Pool Management Deck</span>
            <Icon icon="lucide:shield-check" class="text-emerald-400" width="20" />
          </h2>
          <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
            Central registry of Federal Treasury Officers (GL 08–17) across all Federal MDAs, Foreign Missions, Parastatals, and Military/Police Formations. Controlled strictly under the authority of the Director of Administration & AD Pool Management.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="openRegisterOfficerModal = true"
            class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Icon icon="lucide:user-plus" width="16" />
            <span>Register Treasury Officer</span>
          </button>
          <button
            @click="openNewRequisitionModal = true"
            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Icon icon="lucide:file-plus" width="16" />
            <span>Log MDA Requisition</span>
          </button>
          <button
            @click="openStampModal = true"
            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition-all flex items-center gap-1.5 cursor-pointer"
            title="Configure Digital Stamp & Seal"
          >
            <Icon icon="lucide:stamp" width="16" class="text-amber-400" />
            <span>Digital Stamp</span>
          </button>
        </div>
      </div>

      <!-- Quick Census KPI Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-6 pt-5 border-t border-white/10 text-xs">
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">Total Treasury Pool</div>
          <div class="text-lg font-black text-white mt-0.5">{{ metrics.total_officers || 0 }}</div>
          <div class="text-[10px] text-blue-300">National Central Roll</div>
        </div>
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">Active Deployed</div>
          <div class="text-lg font-black text-emerald-400 mt-0.5">{{ metrics.active_deployed || 0 }}</div>
          <div class="text-[10px] text-slate-400">Stationed at MDAs</div>
        </div>
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">Due for Rotation</div>
          <div class="text-lg font-black text-amber-400 mt-0.5">{{ metrics.due_rotation || 0 }}</div>
          <div class="text-[10px] text-amber-300">Tenancy >= 3.0 Yrs</div>
        </div>
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">Awaiting Posting</div>
          <div class="text-lg font-black text-sky-400 mt-0.5">{{ metrics.awaiting_posting || 0 }}</div>
          <div class="text-[10px] text-slate-400">Orders issued</div>
        </div>
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">MDA Requisitions</div>
          <div class="text-lg font-black text-purple-400 mt-0.5">{{ metrics.pending_requisitions || 0 }}</div>
          <div class="text-[10px] text-purple-300">Appendix C Requests</div>
        </div>
        <div class="bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-xs">
          <div class="text-slate-400 text-[10px] uppercase font-bold">Pending Assumption</div>
          <div class="text-lg font-black text-rose-400 mt-0.5">{{ metrics.pending_assumptions || 0 }}</div>
          <div class="text-[10px] text-rose-300">Form OAGF/POOL/02</div>
        </div>
      </div>
    </div>

    <!-- Federal Character Geopolitical Balance Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs shadow-xs">
      <div class="flex items-center justify-between mb-2">
        <span class="font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
          <Icon icon="lucide:scale" width="14" class="text-blue-600" />
          <span>Federal Character Representation by Geopolitical Zone</span>
        </span>
        <span class="text-[11px] text-slate-500">PSR & FCC Extant Guidelines</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
        <div v-for="(count, zone) in metrics.by_zone" :key="zone" class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
          <div class="text-[10px] font-bold text-slate-500 truncate">{{ zone }}</div>
          <div class="text-sm font-black text-slate-900 mt-0.5">{{ count }} Officers</div>
        </div>
      </div>
    </div>

    <!-- Sub-Module Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
      <button
        @click="subTab = 'officers'"
        :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', subTab === 'officers' ? 'bg-blue-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100']"
      >
        <Icon icon="lucide:users" width="16" />
        <span>National Officer Census</span>
      </button>
      <button
        @click="subTab = 'requisitions'"
        :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', subTab === 'requisitions' ? 'bg-blue-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100']"
      >
        <Icon icon="lucide:inbox" width="16" />
        <span>MDA Requisitions (Appendix C)</span>
        <span v-if="metrics.pending_requisitions" class="px-1.5 py-0.2 rounded-full text-[10px] bg-purple-100 text-purple-800 font-bold">
          {{ metrics.pending_requisitions }}
        </span>
      </button>
      <button
        @click="subTab = 'postings'"
        :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', subTab === 'postings' ? 'bg-blue-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100']"
      >
        <Icon icon="lucide:send" width="16" />
        <span>Postings & Assumptions (Form 01 & 02)</span>
        <span v-if="metrics.pending_assumptions" class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-100 text-rose-800 font-bold">
          {{ metrics.pending_assumptions }}
        </span>
      </button>
    </div>

    <!-- TAB 1: National Officer Census Table -->
    <div v-if="subTab === 'officers'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="relative max-w-md w-full">
          <Icon icon="lucide:search" width="16" class="absolute left-3 top-3 text-slate-400" />
          <input
            v-model="officerSearch"
            @input="debouncedFetchOfficers"
            type="text"
            placeholder="Search by Name, IPPIS, Cadre, MDA, Zone..."
            class="w-full text-xs pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 outline-none shadow-xs"
          />
        </div>

        <div class="flex items-center gap-2 flex-wrap text-xs">
          <select v-model="filterCadre" @change="fetchOfficers" class="p-2 rounded-xl border border-slate-300 bg-white font-medium outline-none">
            <option value="all">All Cadres</option>
            <option value="Accountant">Accountant</option>
            <option value="Auditor">Auditor</option>
            <option value="Executive Officer (Accounts)">Executive Officer (Accounts)</option>
            <option value="Executive Officer (Audit)">Executive Officer (Audit)</option>
          </select>

          <select v-model="filterZone" @change="fetchOfficers" class="p-2 rounded-xl border border-slate-300 bg-white font-medium outline-none">
            <option value="all">All Geopolitical Zones</option>
            <option value="North Central">North Central</option>
            <option value="North East">North East</option>
            <option value="North West">North West</option>
            <option value="South East">South East</option>
            <option value="South South">South South</option>
            <option value="South West">South West</option>
          </select>

          <select v-model="filterStatus" @change="fetchOfficers" class="p-2 rounded-xl border border-slate-300 bg-white font-medium outline-none">
            <option value="all">All Statuses</option>
            <option value="active">Active Deployed</option>
            <option value="due_rotation">Due for Rotation (>=3 yrs)</option>
            <option value="awaiting_posting">Awaiting Posting</option>
            <option value="recalled">Recalled</option>
          </select>
        </div>
      </div>

      <!-- Officers Table -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingOfficers" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-blue-600 mb-2" width="28" />
          <p class="text-xs">Accessing National Central Register...</p>
        </div>
        <div v-else-if="officersList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:users" class="mx-auto text-slate-300 mb-2" width="40" />
          <p class="text-sm font-bold text-slate-700">No Treasury Officers Found</p>
          <p class="text-xs text-slate-400 mt-1">Adjust filters or register a new treasury officer.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Officer / IPPIS</th>
                <th class="py-3 px-4">Cadre & GL</th>
                <th class="py-3 px-4">Current Station / Tenancy</th>
                <th class="py-3 px-4">Origin / Zone</th>
                <th class="py-3 px-4">Competencies</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="off in officersList" :key="off.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ off.first_name }} {{ off.last_name }}</div>
                  <div class="text-[11px] font-mono font-bold text-blue-700 mt-0.5">IPPIS: {{ off.ippis_no }}</div>
                  <div class="text-[10px] text-slate-500">{{ off.email || 'No email' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800">{{ off.cadre }}</div>
                  <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-bold border border-slate-200 text-[10px] mt-0.5">
                    {{ off.grade_level }} / Step {{ off.step }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-900">{{ off.current_mda_name }}</div>
                  <div class="text-[11px] mt-0.5 flex items-center gap-1.5">
                    <span class="font-bold text-slate-700">{{ off.tenancy_years }} Years</span>
                    <span
                      v-if="off.tenancy_years >= 3.0"
                      class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300"
                    >
                      Due Rotation
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800">{{ off.state_of_origin }} State</div>
                  <div class="text-[10px] text-slate-500">{{ off.geopolitical_zone }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex flex-wrap gap-1 max-w-xs">
                    <span
                      v-for="c in off.special_competencies"
                      :key="c"
                      class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                    >
                      {{ c }}
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': off.status === 'active',
                      'bg-amber-50 text-amber-700 border border-amber-200': off.status === 'due_rotation',
                      'bg-sky-50 text-sky-700 border border-sky-200': off.status === 'awaiting_posting',
                      'bg-purple-50 text-purple-700 border border-purple-200': off.status === 'recalled',
                    }"
                  >
                    {{ off.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    @click="initiatePosting(off)"
                    class="px-2.5 py-1 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:send" width="12" />
                    <span>Post Officer</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: MDA Requisitions (Appendix C) -->
    <div v-if="subTab === 'requisitions'" class="space-y-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingRequisitions" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-blue-600 mb-2" width="28" />
          <p class="text-xs">Loading MDA requisitions...</p>
        </div>
        <div v-else-if="requisitionsList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:inbox" class="mx-auto text-slate-300 mb-2" width="40" />
          <p class="text-sm font-bold text-slate-700">No Pending Requisitions</p>
          <p class="text-xs text-slate-400 mt-1">All MDA personnel requests are treated.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Requisition No / Ref</th>
                <th class="py-3 px-4">Requesting MDA</th>
                <th class="py-3 px-4">Requirement</th>
                <th class="py-3 px-4">Appendix C Checklist</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="req in requisitionsList" :key="req.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-blue-900">{{ req.requisition_no }}</div>
                  <div class="text-[11px] text-slate-500">Ref: {{ req.letter_ref }} ({{ req.letter_date }})</div>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ req.mda_name }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-800">{{ req.number_required }} Officer(s) - {{ req.grade_level_required }}</div>
                  <div class="text-[10px] text-slate-500 mt-0.5">
                    Skills: {{ (req.special_skills_required || []).join(', ') || 'Standard Accounting' }}
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2 text-[10px]">
                    <span :class="req.has_letterhead ? 'text-emerald-700 font-bold' : 'text-red-600'">
                      Letterhead: {{ req.has_letterhead ? 'YES' : 'NO' }}
                    </span>
                    •
                    <span :class="req.has_accommodation ? 'text-emerald-700 font-bold' : 'text-amber-600'">
                      Housing: {{ req.has_accommodation ? 'PROVIDED' : 'N/A' }}
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-purple-50 text-purple-700 border border-purple-200': req.status === 'pending',
                      'bg-blue-50 text-blue-700 border border-blue-200': req.status === 'shortlisted',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': req.status === 'fulfilled',
                    }"
                  >
                    {{ req.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    @click="matchCandidatesForRequisition(req)"
                    class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:sparkles" width="12" />
                    <span>Match Candidates</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 3: Postings & Assumptions Tracker -->
    <div v-if="subTab === 'postings'" class="space-y-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingPostings" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-blue-600 mb-2" width="28" />
          <p class="text-xs">Loading postings ledger...</p>
        </div>
        <div v-else-if="postingsList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:send" class="mx-auto text-slate-300 mb-2" width="40" />
          <p class="text-sm font-bold text-slate-700">No Postings on Record</p>
          <p class="text-xs text-slate-400 mt-1">Issue a new posting order to view tracking lifecycle.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Posting Ref / Authority</th>
                <th class="py-3 px-4">Officer (IPPIS)</th>
                <th class="py-3 px-4">Movement (From -> To)</th>
                <th class="py-3 px-4">Effective / Due Dates</th>
                <th class="py-3 px-4">Assumption Status</th>
                <th class="py-3 px-4 text-center">Statutory Documents</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="p in postingsList" :key="p.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-slate-900">{{ p.posting_ref }}</div>
                  <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase mt-0.5 bg-slate-100 text-slate-700">
                    Authority: {{ p.approval_authority === 'agf' ? 'AGF (GL 14+)' : 'DA (GL 08-13)' }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ p.pool_officer?.first_name }} {{ p.pool_officer?.last_name }}</div>
                  <div class="text-[11px] font-mono text-blue-700 font-bold">IPPIS: {{ p.pool_officer?.ippis_no }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800">{{ p.from_mda_name }}</div>
                  <div class="text-[11px] text-indigo-700 font-bold flex items-center gap-1 mt-0.5">
                    <Icon icon="lucide:arrow-right" width="12" />
                    <span>{{ p.to_mda_name }}</span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <div>Effective: <strong>{{ p.effective_date }}</strong></div>
                  <div class="text-[11px] text-rose-600 font-bold mt-0.5">Due: {{ p.assumption_due_date }} (14-day rule)</div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-amber-50 text-amber-700 border border-amber-200': p.assumption_status === 'pending',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': p.assumption_status === 'assumed',
                      'bg-red-50 text-red-700 border border-red-200': p.assumption_status === 'overdue',
                    }"
                  >
                    {{ p.assumption_status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="viewStatutoryDoc('posting_order', p)"
                      class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                      title="Print Form OAGF/POOL/01"
                    >
                      <Icon icon="lucide:printer" width="12" />
                      <span>Form 01</span>
                    </button>
                    <button
                      v-if="p.assumption_status === 'pending'"
                      @click="openAssumptionModal(p)"
                      class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                      title="Record Form OAGF/POOL/02"
                    >
                      <Icon icon="lucide:check-circle" width="12" />
                      <span>Verify 02</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Candidate Matching Drawer -->
    <div v-if="selectedRequisitionForMatch" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-300">Intelligent Matching Engine</span>
            <h3 class="font-bold text-base mt-0.5">Requisition: {{ selectedRequisitionForMatch.mda_name }}</h3>
            <p class="text-xs text-slate-300">
              Grade Level: {{ selectedRequisitionForMatch.grade_level_required }} • Required: {{ selectedRequisitionForMatch.number_required }} Officer(s)
            </p>
          </div>
          <button @click="selectedRequisitionForMatch = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="20" />
          </button>
        </div>

        <div class="p-5 overflow-y-auto space-y-4 flex-1">
          <div v-if="loadingMatch" class="py-12 text-center text-slate-500">
            <Icon icon="lucide:loader" class="animate-spin mx-auto text-indigo-600 mb-2" width="30" />
            <p class="text-xs">Evaluating national pool candidates across Federal Character and Tenancy rules...</p>
          </div>
          <div v-else-if="matchedCandidates.length === 0" class="py-12 text-center text-slate-500">
            <p class="text-xs">No suitable matching officers found for the specified criteria.</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="cand in matchedCandidates"
              :key="cand.officer.id"
              class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white transition-all shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
              <div>
                <div class="flex items-center gap-2">
                  <div class="font-bold text-slate-900 text-sm">{{ cand.officer.first_name }} {{ cand.officer.last_name }}</div>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900">
                    Compatibility: {{ cand.score }}%
                  </span>
                  <span v-if="cand.is_due_rotation" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900">
                    Due Rotation ({{ cand.officer.tenancy_years }} yrs)
                  </span>
                </div>
                <div class="text-xs text-slate-600 mt-1">
                  {{ cand.officer.cadre }} ({{ cand.officer.grade_level }}) • Current MDA: <strong>{{ cand.officer.current_mda_name }}</strong>
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                  State: {{ cand.federal_character.state }} ({{ cand.federal_character.zone }}) • IPPIS: {{ cand.officer.ippis_no }}
                </div>
              </div>

              <button
                @click="deployCandidateToRequisition(cand.officer, selectedRequisitionForMatch)"
                class="px-3.5 py-2 bg-indigo-900 hover:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
              >
                <Icon icon="lucide:check-circle" width="14" />
                <span>Issue Posting (Form 01)</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Issue Posting Order -->
    <div v-if="postingOfficer" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-blue-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:send" width="18" class="text-blue-400" />
            <h3 class="font-bold text-sm">Issue Posting Order (Form OAGF/POOL/01)</h3>
          </div>
          <button @click="postingOfficer = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitPostingOrder" class="p-6 space-y-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="font-bold text-slate-900 text-sm">{{ postingOfficer.first_name }} {{ postingOfficer.last_name }}</div>
            <div class="text-slate-600 mt-0.5">
              {{ postingOfficer.cadre }} ({{ postingOfficer.grade_level }}) • IPPIS: {{ postingOfficer.ippis_no }}
            </div>
            <div class="text-slate-500 mt-0.5">Current Station: {{ postingOfficer.current_mda_name }}</div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Target Federal MDA *</label>
            <input
              v-model="postingForm.to_mda_name"
              type="text"
              required
              placeholder="e.g. Federal Ministry of Finance / Federal High Court"
              class="w-full p-2.5 rounded-lg border border-slate-300 focus:border-blue-600 outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Posting Type *</label>
              <select v-model="postingForm.posting_type" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none">
                <option value="routine">Routine Posting</option>
                <option value="rotation">3-Year Rotation Policy</option>
                <option value="emergency">Emergency Operational</option>
                <option value="promotion">Post-Promotion Deployment</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Effective Date *</label>
              <input v-model="postingForm.effective_date" type="date" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
          </div>

          <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200 text-blue-900 flex items-start gap-2">
            <Icon icon="lucide:info" width="16" class="shrink-0 mt-0.5" />
            <div>
              <div class="font-bold">Statutory 14-Day Assumption Rule</div>
              <div class="text-[11px] text-blue-800 mt-0.5">
                The officer must assume duty and submit Form OAGF/POOL/02 within 14 calendar days of the effective date.
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="postingOfficer = null"
              class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Generate & Issue Order</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Record Assumption Slip (Form 02) -->
    <div v-if="assumptionPosting" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-emerald-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:file-check" width="18" class="text-emerald-400" />
            <h3 class="font-bold text-sm">Verify Assumption Slip (Form OAGF/POOL/02)</h3>
          </div>
          <button @click="assumptionPosting = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitAssumptionSlip" class="p-6 space-y-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="font-bold text-slate-900">{{ assumptionPosting.pool_officer?.first_name }} {{ assumptionPosting.pool_officer?.last_name }}</div>
            <div class="text-slate-600 mt-0.5">Ref: {{ assumptionPosting.posting_ref }} • Assigned to: {{ assumptionPosting.to_mda_name }}</div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Actual Date of Assumption *</label>
            <input v-model="assumptionForm.assumption_slip_returned_at" type="date" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">CFO / Director Finance & Accounts Remarks</label>
            <textarea v-model="assumptionForm.remarks" rows="3" placeholder="Confirmation notes from receiving MDA..." class="w-full p-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="assumptionPosting = null"
              class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Confirm Active Deployment</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Register New Treasury Officer -->
    <div v-if="openRegisterOfficerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-blue-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:user-plus" width="18" class="text-blue-400" />
            <h3 class="font-bold text-sm">Register Treasury Officer into National Central Pool</h3>
          </div>
          <button @click="openRegisterOfficerModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitRegisterOfficer" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">First Name *</label>
              <input v-model="officerForm.first_name" type="text" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Last Name *</label>
              <input v-model="officerForm.last_name" type="text" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">IPPIS Number *</label>
              <input v-model="officerForm.ippis_no" type="text" required placeholder="IPPIS-12345" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-mono font-bold" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Cadre *</label>
              <select v-model="officerForm.cadre" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-medium">
                <option value="Accountant">Accountant</option>
                <option value="Auditor">Auditor</option>
                <option value="Executive Officer (Accounts)">Executive Officer (Accounts)</option>
                <option value="Executive Officer (Audit)">Executive Officer (Audit)</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Grade Level *</label>
              <select v-model="officerForm.grade_level" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                <option v-for="gl in ['GL 08', 'GL 09', 'GL 10', 'GL 12', 'GL 13', 'GL 14', 'GL 15', 'GL 16', 'GL 17']" :key="gl" :value="gl">
                  {{ gl }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">State of Origin *</label>
              <input v-model="officerForm.state_of_origin" type="text" required placeholder="e.g. Kano, Enugu, Oyo" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Geopolitical Zone *</label>
              <select v-model="officerForm.geopolitical_zone" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none">
                <option value="North Central">North Central</option>
                <option value="North East">North East</option>
                <option value="North West">North West</option>
                <option value="South East">South East</option>
                <option value="South South">South South</option>
                <option value="South West">South West</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Current Station (MDA) *</label>
              <input v-model="officerForm.current_mda_name" type="text" required placeholder="e.g. Ministry of Health" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tenancy at Current MDA (Years) *</label>
              <input v-model.number="officerForm.tenancy_years" type="number" step="0.1" min="0" max="40" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Special Competencies (Comma separated)</label>
            <input v-model="competenciesInput" type="text" placeholder="GIFMIS, IPSAS, TSA, Final Accounts, Forensic Audit" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="openRegisterOfficerModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Enroll Officer in Register</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Log MDA Requisition (Appendix C) -->
    <div v-if="openNewRequisitionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:file-plus" width="18" class="text-indigo-400" />
            <h3 class="font-bold text-sm">Log MDA Requisition (Appendix C)</h3>
          </div>
          <button @click="openNewRequisitionModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitNewRequisition" class="p-6 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Requesting MDA Name *</label>
            <input v-model="reqForm.mda_name" type="text" required placeholder="e.g. Federal Ministry of Defence" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Letter Ref Number *</label>
              <input v-model="reqForm.letter_ref" type="text" required placeholder="e.g. MOD/ACCT/REQ/2026/04" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-mono" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Letter Date *</label>
              <input v-model="reqForm.letter_date" type="date" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Grade Level Required *</label>
              <select v-model="reqForm.grade_level_required" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                <option v-for="gl in ['GL 08', 'GL 09', 'GL 10', 'GL 12', 'GL 13', 'GL 14', 'GL 15', 'GL 16', 'GL 17']" :key="gl" :value="gl">
                  {{ gl }}
                </option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Number of Officers *</label>
              <input v-model.number="reqForm.number_required" type="number" min="1" max="20" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Special Competencies Needed</label>
            <input v-model="reqSkillsInput" type="text" placeholder="GIFMIS, IPSAS, TSA, Final Accounts" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Justification / Operational Purpose *</label>
            <textarea v-model="reqForm.justification" rows="3" required placeholder="Provide reasons for requisition and establishment vacancy..." class="w-full p-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
          </div>

          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
            <span class="font-bold text-slate-700 uppercase tracking-wide text-[10px]">Appendix C Compliance Checklist</span>
            <div class="flex items-center gap-6">
              <label class="flex items-center gap-2 cursor-pointer font-semibold">
                <input v-model="reqForm.has_letterhead" type="checkbox" class="rounded text-indigo-600" />
                <span>Original MDA Letterhead Validated</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer font-semibold">
                <input v-model="reqForm.has_accommodation" type="checkbox" class="rounded text-indigo-600" />
                <span>Station Accommodation Provided</span>
              </label>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="openNewRequisitionModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-indigo-900 hover:bg-indigo-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Log Requisition</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modals: Stamp & Statutory Document Preview -->
    <DigitalStampModal :show="openStampModal" @close="openStampModal = false" />
    <StatutoryDocumentModal
      :show="showDocModal"
      :docType="activeDocType"
      :docData="activeDocData"
      @close="showDocModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import DigitalStampModal from './DigitalStampModal.vue';
import StatutoryDocumentModal from './StatutoryDocumentModal.vue';

const subTab = ref('officers');
const loadingOfficers = ref(false);
const loadingRequisitions = ref(false);
const loadingPostings = ref(false);
const loadingMatch = ref(false);
const saving = ref(false);

const metrics = ref({});
const officersList = ref([]);
const requisitionsList = ref([]);
const postingsList = ref([]);

// Modals
const openStampModal = ref(false);
const openRegisterOfficerModal = ref(false);
const openNewRequisitionModal = ref(false);
const selectedRequisitionForMatch = ref(null);
const matchedCandidates = ref([]);
const postingOfficer = ref(null);
const assumptionPosting = ref(null);

// Statutory doc modal
const showDocModal = ref(false);
const activeDocType = ref('posting_order');
const activeDocData = ref(null);

// Filters
const officerSearch = ref('');
const filterCadre = ref('all');
const filterZone = ref('all');
const filterStatus = ref('all');

// Form States
const competenciesInput = ref('GIFMIS, IPSAS');
const officerForm = ref({
  first_name: '',
  last_name: '',
  ippis_no: '',
  cadre: 'Accountant',
  grade_level: 'GL 10',
  step: 2,
  state_of_origin: '',
  geopolitical_zone: 'North Central',
  current_mda_name: '',
  tenancy_years: 1.0,
  confirmation_status: 'confirmed',
  status: 'active',
});

const reqSkillsInput = ref('GIFMIS, IPSAS');
const reqForm = ref({
  mda_name: '',
  letter_ref: '',
  letter_date: new Date().toISOString().split('T')[0],
  grade_level_required: 'GL 10',
  number_required: 1,
  justification: '',
  has_letterhead: true,
  has_accommodation: false,
});

const postingForm = ref({
  to_mda_name: '',
  posting_type: 'routine',
  effective_date: new Date().toISOString().split('T')[0],
  pool_requisition_id: null,
});

const assumptionForm = ref({
  assumption_slip_returned_at: new Date().toISOString().split('T')[0],
  remarks: '',
});

let debounceTimer = null;
const debouncedFetchOfficers = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchOfficers();
  }, 400);
};

const fetchOfficers = async () => {
  loadingOfficers.value = true;
  try {
    const res = await axios.get('/dashboard/administration/pool', {
      params: {
        search: officerSearch.value,
        cadre: filterCadre.value,
        geopolitical_zone: filterZone.value,
        status: filterStatus.value,
      }
    });
    metrics.value = res.data.data?.metrics || {};
    officersList.value = res.data.data?.officers?.data || res.data.data?.officers || [];
  } catch (err) {
    console.error('Failed to load pool census:', err);
  } finally {
    loadingOfficers.value = false;
  }
};

const fetchRequisitions = async () => {
  loadingRequisitions.value = true;
  try {
    const res = await axios.get('/dashboard/administration/pool/requisitions');
    requisitionsList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load requisitions:', err);
  } finally {
    loadingRequisitions.value = false;
  }
};

const fetchPostings = async () => {
  loadingPostings.value = true;
  try {
    const res = await axios.get('/dashboard/administration/pool/postings');
    postingsList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load postings:', err);
  } finally {
    loadingPostings.value = false;
  }
};

const initiatePosting = (officer) => {
  postingOfficer.value = officer;
  postingForm.value = {
    to_mda_name: '',
    posting_type: (officer.tenancy_years >= 3.0) ? 'rotation' : 'routine',
    effective_date: new Date().toISOString().split('T')[0],
    pool_requisition_id: null,
  };
};

const submitPostingOrder = async () => {
  if (!postingOfficer.value) return;
  saving.value = true;
  try {
    const res = await axios.post('/dashboard/administration/pool/postings/issue', {
      pool_officer_id: postingOfficer.value.id,
      pool_requisition_id: postingForm.value.pool_requisition_id,
      to_mda_name: postingForm.value.to_mda_name,
      posting_type: postingForm.value.posting_type,
      effective_date: postingForm.value.effective_date,
    });
    alert(res.data.message || 'Posting order issued.');
    postingOfficer.value = null;
    fetchOfficers();
    fetchPostings();
  } catch (err) {
    alert(err.response?.data?.message || 'Error issuing posting.');
  } finally {
    saving.value = false;
  }
};

const matchCandidatesForRequisition = async (req) => {
  selectedRequisitionForMatch.value = req;
  loadingMatch.value = true;
  try {
    const res = await axios.get(`/dashboard/administration/pool/requisitions/${req.id}/match`);
    matchedCandidates.value = res.data.data?.candidates || [];
  } catch (err) {
    console.error('Failed to match candidates:', err);
  } finally {
    loadingMatch.value = false;
  }
};

const deployCandidateToRequisition = (officer, req) => {
  selectedRequisitionForMatch.value = null;
  postingOfficer.value = officer;
  postingForm.value = {
    to_mda_name: req.mda_name,
    posting_type: 'routine',
    effective_date: new Date().toISOString().split('T')[0],
    pool_requisition_id: req.id,
  };
};

const openAssumptionModal = (posting) => {
  assumptionPosting.value = posting;
  assumptionForm.value = {
    assumption_slip_returned_at: new Date().toISOString().split('T')[0],
    remarks: '',
  };
};

const submitAssumptionSlip = async () => {
  if (!assumptionPosting.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/administration/pool/postings/${assumptionPosting.value.id}/assume`, assumptionForm.value);
    alert(res.data.message || 'Assumption verified.');
    assumptionPosting.value = null;
    fetchPostings();
    fetchOfficers();
  } catch (err) {
    alert(err.response?.data?.message || 'Error recording assumption.');
  } finally {
    saving.value = false;
  }
};

const submitRegisterOfficer = async () => {
  saving.value = true;
  try {
    const competencies = competenciesInput.value.split(',').map(s => s.trim()).filter(Boolean);
    const payload = { ...officerForm.value, special_competencies: competencies };
    const res = await axios.post('/dashboard/administration/pool/officers', payload);
    alert(res.data.message || 'Officer registered.');
    openRegisterOfficerModal.value = false;
    fetchOfficers();
  } catch (err) {
    alert(err.response?.data?.message || 'Error registering officer.');
  } finally {
    saving.value = false;
  }
};

const submitNewRequisition = async () => {
  saving.value = true;
  try {
    const skills = reqSkillsInput.value.split(',').map(s => s.trim()).filter(Boolean);
    const payload = { ...reqForm.value, special_skills_required: skills };
    const res = await axios.post('/dashboard/administration/pool/requisitions', payload);
    alert(res.data.message || 'Requisition logged.');
    openNewRequisitionModal.value = false;
    fetchRequisitions();
    fetchOfficers();
  } catch (err) {
    alert(err.response?.data?.message || 'Error logging requisition.');
  } finally {
    saving.value = false;
  }
};

const viewStatutoryDoc = (type, data) => {
  activeDocType.value = type;
  activeDocData.value = data;
  showDocModal.value = true;
};

onMounted(() => {
  fetchOfficers();
  fetchRequisitions();
  fetchPostings();
});
</script>
