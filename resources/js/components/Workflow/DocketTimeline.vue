<template>
  <div class="docket-timeline flex flex-col h-full bg-slate-50/50">
    <!-- Top Filter & Search Toolbar -->
    <div class="p-4 bg-white border-b border-gray-200/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shrink-0">
      <!-- Filter Segmented Tabs -->
      <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
        <button
          type="button"
          @click="activeFilter = 'all'"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer',
            activeFilter === 'all' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <span>All Events</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-gray-100 text-gray-600 font-mono">
            {{ allDocketEntries.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeFilter = 'minutes'"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer',
            activeFilter === 'minutes' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <Icon icon="lucide:file-signature" width="13" />
          <span>Official Minutes</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-mono font-bold">
            {{ minutesCount }}
          </span>
        </button>

        <button
          type="button"
          @click="activeFilter = 'audit'"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer',
            activeFilter === 'audit' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <Icon icon="lucide:activity" width="13" />
          <span>Audit Trail</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-blue-100 text-blue-800 font-mono">
            {{ activitiesCount }}
          </span>
        </button>
      </div>

      <!-- Search & Sort Controls -->
      <div class="flex items-center gap-2">
        <!-- Search Input -->
        <div class="relative flex-1 sm:w-48">
          <Icon icon="lucide:search" width="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search docket..."
            class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all"
          />
          <button 
            v-if="searchQuery" 
            @click="searchQuery = ''" 
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs"
          >
            ×
          </button>
        </div>

        <!-- Sort Direction Toggle -->
        <button
          type="button"
          @click="sortAscending = !sortAscending"
          :title="sortAscending ? 'Order: Oldest First (Chronological)' : 'Order: Newest First'"
          class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 transition-colors cursor-pointer shrink-0"
        >
          <Icon :icon="sortAscending ? 'lucide:arrow-down-up' : 'lucide:arrow-up-down'" width="14" />
        </button>
      </div>
    </div>

    <!-- Timeline Entries Stream -->
    <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
      <!-- Empty State -->
      <div v-if="filteredEntries.length === 0" class="text-center py-16 px-4 bg-white rounded-2xl border border-dashed border-gray-200">
        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-3">
          <Icon icon="lucide:archive-restore" width="24" />
        </div>
        <h4 class="text-sm font-bold text-gray-700">No Docket Entries Found</h4>
        <p class="text-xs text-gray-400 max-w-xs mx-auto mt-1">
          {{ searchQuery ? 'No timeline entries match your current search query.' : 'No administrative minutes or audit events logged for this task yet.' }}
        </p>
      </div>

      <!-- Integrated Timeline Flow -->
      <div v-else class="relative pl-6 space-y-6 before:absolute before:left-3 before:top-3 before:bottom-3 before:w-0.5 before:bg-gray-200">
        <template v-for="(entry, index) in filteredEntries" :key="entry.key">
          <!-- 1. OFFICIAL ADMINISTRATIVE MINUTE CARD -->
          <div v-if="entry.type === 'minute'" class="relative group">
            <!-- Timeline Node Indicator -->
            <span
              class="absolute -left-6 top-3 w-5 h-5 rounded-full border-2 border-white shadow-xs flex items-center justify-center transition-transform group-hover:scale-125 z-10"
              :class="entry.raw.is_signed ? 'bg-emerald-500 ring-4 ring-emerald-100/80 text-white' : 'bg-blue-600 ring-4 ring-blue-100 text-white'"
            >
              <Icon :icon="entry.raw.is_signed ? 'lucide:check-circle-2' : 'lucide:pen-tool'" width="12" />
            </span>

            <!-- Official Minute Container -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition-all p-5 relative overflow-hidden">
              <!-- Top Header: Minute Index, Officer Rank & Date -->
              <div class="flex items-start justify-between gap-3 mb-3 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-3 min-w-0">
                  <!-- Officer Avatar -->
                  <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-100 border border-gray-200 shrink-0 shadow-2xs">
                    <img 
                      v-if="entry.userAvatar" 
                      :src="entry.userAvatar" 
                      :alt="entry.userName" 
                      class="w-full h-full object-cover" 
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-xs bg-slate-100">
                      {{ entry.userInitials }}
                    </div>
                  </div>

                  <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="text-xs font-bold text-gray-900 truncate">{{ entry.userName }}</span>
                      <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wider">
                        Minute #{{ entry.minuteNumber }}
                      </span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate">{{ entry.userRoleOrDept }}</p>
                  </div>
                </div>

                <!-- Timestamp -->
                <div class="text-right shrink-0">
                  <span class="text-[11px] font-mono text-gray-400 block">{{ formatDateTime(entry.timestamp) }}</span>
                  <span class="text-[10px] text-gray-400">{{ formatRelativeTime(entry.timestamp) }}</span>
                </div>
              </div>

              <!-- Action Directive Pill -->
              <div class="mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                  <Icon icon="lucide:file-text" width="13" class="text-blue-600" />
                  {{ entry.raw.action || 'Executive Minute' }}
                </span>
              </div>

              <!-- Formal Directive Text Quotation -->
              <div class="bg-gradient-to-br from-slate-50 to-blue-50/20 p-4 rounded-xl border border-gray-200/80 text-xs md:text-sm text-gray-800 whitespace-pre-wrap leading-relaxed shadow-inner font-serif">
                {{ entry.raw.directive || 'No written directive.' }}
              </div>

              <!-- Digital Verification Seal (PIN Signature) -->
              <div 
                v-if="entry.raw.is_signed" 
                class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-4 flex-wrap"
              >
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
                  <Icon icon="lucide:badge-check" width="16" class="text-emerald-600 shrink-0" />
                  <span>Digitally Verified & Sealed via Secure PIN</span>
                </div>

                <!-- Digital Signature Image -->
                <img 
                  v-if="entry.signatureImage" 
                  :src="entry.signatureImage" 
                  alt="Official Digital Signature" 
                  class="max-w-[120px] max-h-[44px] object-contain mix-blend-multiply border-b border-gray-300 pb-0.5" 
                />
              </div>
            </div>
          </div>

          <!-- 2. AUDIT TRAIL / SYSTEM EVENT CHIP -->
          <div v-else class="relative group">
            <!-- Timeline Node Indicator -->
            <span
              class="absolute -left-6 top-2 w-4 h-4 rounded-full border-2 border-white shadow-xs flex items-center justify-center bg-slate-300 ring-4 ring-slate-100 text-slate-600 z-10"
            >
              <Icon icon="lucide:activity" width="10" />
            </span>

            <!-- Audit Event Row -->
            <div class="bg-gray-50/80 hover:bg-white rounded-xl p-3 border border-gray-200/70 hover:border-blue-200 transition-all flex items-start justify-between gap-3 shadow-2xs">
              <div class="flex items-start gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 shrink-0 mt-0.5 shadow-2xs">
                  <Icon :icon="getAuditIcon(entry.raw.description)" width="14" class="text-blue-600" />
                </div>
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 leading-snug">
                    {{ entry.raw.description }}
                  </p>
                  <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                    <span class="font-medium text-gray-700">{{ entry.userName }}</span>
                    <span>•</span>
                    <span>{{ formatDateTime(entry.timestamp) }}</span>
                  </p>
                </div>
              </div>

              <!-- Relative Time Badge -->
              <span class="text-[10px] font-mono text-gray-400 shrink-0 bg-white px-2 py-0.5 rounded-md border border-gray-100">
                {{ formatRelativeTime(entry.timestamp) }}
              </span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import moment from 'moment';

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
});

const page = usePage();
const activeFilter = ref('all'); // 'all', 'minutes', 'audit'
const searchQuery = ref('');
const sortAscending = ref(false); // false = newest first, true = chronological

const formatDateTime = (date) => {
  if (!date) return '';
  return moment(date).format('DD MMM YYYY, HH:mm');
};

const formatRelativeTime = (date) => {
  if (!date) return '';
  return moment(date).fromNow();
};

const getInitials = (firstName, lastName) => {
  const f = firstName ? firstName.charAt(0).toUpperCase() : '';
  const l = lastName ? lastName.charAt(0).toUpperCase() : '';
  return (f + l) || 'OF';
};

const getAuditIcon = (desc) => {
  if (!desc) return 'lucide:activity';
  const d = desc.toLowerCase();
  if (d.includes('submit')) return 'lucide:send';
  if (d.includes('approv')) return 'lucide:check-circle';
  if (d.includes('return')) return 'lucide:rotate-ccw';
  if (d.includes('assign')) return 'lucide:user-plus';
  if (d.includes('upload') || d.includes('attach')) return 'lucide:paperclip';
  if (d.includes('complet')) return 'lucide:award';
  if (d.includes('reopen')) return 'lucide:refresh-cw';
  return 'lucide:activity';
};

// Unified Docket Entries List
const allDocketEntries = computed(() => {
  const entries = [];
  if (!props.task) return entries;

  // 1. Ingest Official Minutes
  const rawMinutes = props.task.minutes || [];
  rawMinutes.forEach((m, idx) => {
    const user = m.user;
    const userName = user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'Authorized Officer';
    const userInitials = user ? getInitials(user.first_name, user.last_name) : 'OF';
    let userRoleOrDept = user?.department?.title || 'Federal Registry';
    if (user?.roles && user.roles.length > 0) {
      const r = user.roles.map(x => typeof x === 'object' ? x.name : String(x)).join(', ');
      userRoleOrDept = r;
    }

    const userAvatar = user?.profile_image ? (page.props.base || '') + '/' + user.profile_image : null;
    const signatureImage = (m.is_signed && user?.digital_signature_path) ? (page.props.base || '') + '/' + user.digital_signature_path : null;

    entries.push({
      key: `min-${m.id || idx}`,
      type: 'minute',
      minuteNumber: idx + 1,
      raw: m,
      timestamp: m.created_at,
      userName: userName,
      userInitials: userInitials,
      userRoleOrDept: userRoleOrDept,
      userAvatar: userAvatar,
      signatureImage: signatureImage,
      searchContent: `${userName} ${userRoleOrDept} ${m.action || ''} ${m.directive || ''}`.toLowerCase()
    });
  });

  // 2. Ingest System Audit Activities
  const rawActivities = props.task.task_activity || props.task.taskActivity || [];
  rawActivities.forEach((act, idx) => {
    const user = act.user;
    const userName = user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'System Action';
    const userInitials = user ? getInitials(user.first_name, user.last_name) : 'SY';

    entries.push({
      key: `act-${act.id || idx}`,
      type: 'audit',
      raw: act,
      timestamp: act.created_at,
      userName: userName,
      userInitials: userInitials,
      userAvatar: user?.profile_image ? (page.props.base || '') + '/' + user.profile_image : null,
      searchContent: `${userName} ${act.description || ''}`.toLowerCase()
    });
  });

  // 3. Ingest Generic Workflow Logs into Docket Trail
  const rawWorkflowLogs = props.task.workflow_logs || props.task.workflowLogs || [];
  rawWorkflowLogs.forEach((log, idx) => {
    const user = log.action_by || log.actionBy;
    const userName = user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'Workflow Action';
    const userInitials = user ? getInitials(user.first_name, user.last_name) : 'WF';
    const desc = `${log.action_type || 'Workflow Action'}${log.comments ? ': ' + log.comments : ''}`;

    entries.push({
      key: `wf-${log.id || idx}`,
      type: 'audit',
      raw: { description: desc, created_at: log.created_at, user: user },
      timestamp: log.created_at,
      userName: userName,
      userInitials: userInitials,
      userAvatar: user?.profile_image ? (page.props.base || '') + '/' + user.profile_image : null,
      searchContent: `${userName} ${desc}`.toLowerCase()
    });
  });

  return entries;
});

const minutesCount = computed(() => {
  return allDocketEntries.value.filter(e => e.type === 'minute').length;
});

const activitiesCount = computed(() => {
  return allDocketEntries.value.filter(e => e.type === 'audit').length;
});

// Filter, Search, and Sort
const filteredEntries = computed(() => {
  let list = [...allDocketEntries.value];

  // 1. Filter by type
  if (activeFilter.value === 'minutes') {
    list = list.filter(e => e.type === 'minute');
  } else if (activeFilter.value === 'audit') {
    list = list.filter(e => e.type === 'audit');
  }

  // 2. Search query filter
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase();
    list = list.filter(e => e.searchContent.includes(q));
  }

  // 3. Sorting
  list.sort((a, b) => {
    const timeA = new Date(a.timestamp).getTime();
    const timeB = new Date(b.timestamp).getTime();
    return sortAscending.value ? timeA - timeB : timeB - timeA;
  });

  return list;
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
