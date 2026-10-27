<template>
  <div class="workflow-metro-map bg-white/95 backdrop-blur-md rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden transition-all duration-300">
    <!-- Header bar -->
    <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 via-white to-blue-50/40 border-b border-gray-100 flex items-center justify-between gap-4">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600/10 text-blue-700 flex items-center justify-center font-bold">
          <Icon icon="lucide:route" width="18" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 class="font-bold text-gray-800 text-sm tracking-tight">Docket Flight Path</h3>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
              {{ stations.length }} Stations
            </span>
          </div>
          <p class="text-xs text-gray-500">Live administrative routing transit map</p>
        </div>
      </div>

      <!-- Controls -->
      <div class="flex items-center gap-2">
        <button 
          type="button"
          @click="isExpanded = !isExpanded"
          class="text-xs text-gray-500 hover:text-gray-800 px-2.5 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <span>{{ isExpanded ? 'Compact View' : 'Expanded Details' }}</span>
          <Icon :icon="isExpanded ? 'lucide:minimize-2' : 'lucide:maximize-2'" width="13" />
        </button>
      </div>
    </div>

    <!-- Metro Track Strip -->
    <div class="p-5 overflow-x-auto custom-scrollbar">
      <div class="min-w-fit flex items-start gap-0 relative">
        <template v-for="(station, idx) in stations" :key="station.id || idx">
          <!-- Station Node Block -->
          <div 
            class="flex flex-col items-center relative group cursor-pointer transition-all duration-200"
            :class="[
              isExpanded ? 'w-48 sm:w-56' : 'w-36 sm:w-44',
              station.isActive ? 'scale-102' : 'hover:-translate-y-0.5'
            ]"
            @click="onSelectStation(station)"
          >
            <!-- Connecting Line Segment (Left to Dot) -->
            <div 
              v-if="idx > 0"
              class="absolute top-5 -left-1/2 w-full h-1 -z-0 transition-colors duration-300"
              :class="[
                station.isCompleted || (station.isActive && stations[idx-1]?.isCompleted)
                  ? 'bg-gradient-to-r from-emerald-500 to-emerald-400'
                  : station.isActive
                    ? 'bg-gradient-to-r from-emerald-400 to-blue-500'
                    : 'bg-slate-200'
              ]"
            ></div>

            <!-- Station Node Icon Circle -->
            <div class="relative z-10 flex items-center justify-center">
              <!-- Active radar pulse effect -->
              <span 
                v-if="station.isActive"
                class="absolute -inset-1.5 rounded-full bg-blue-500/20 animate-ping"
              ></span>

              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-md border-2 transition-all duration-300 relative bg-white"
                :class="[
                  station.isCompleted 
                    ? 'border-emerald-500 text-emerald-600 bg-emerald-50/80 shadow-emerald-100' 
                    : station.isActive 
                      ? 'border-blue-600 text-blue-600 ring-4 ring-blue-100/80 shadow-blue-200' 
                      : 'border-slate-300 text-slate-400 bg-slate-50'
                ]"
              >
                <img 
                  v-if="station.avatar" 
                  :src="station.avatar" 
                  :alt="station.officerName" 
                  class="w-full h-full rounded-full object-cover" 
                />
                <Icon 
                  v-else-if="station.isCompleted" 
                  icon="lucide:check" 
                  width="18" 
                  class="stroke-[2.5]" 
                />
                <Icon 
                  v-else-if="station.isActive" 
                  icon="lucide:clock" 
                  width="18" 
                  class="animate-pulse" 
                />
                <span v-else class="text-[11px]">{{ idx + 1 }}</span>

                <!-- Status indicator dot -->
                <span 
                  class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white"
                  :class="[
                    station.isCompleted 
                      ? 'bg-emerald-500' 
                      : station.isActive 
                        ? 'bg-blue-600 animate-pulse' 
                        : 'bg-slate-300'
                  ]"
                ></span>
              </div>
            </div>

            <!-- Station Meta Content -->
            <div class="mt-3 text-center px-2 w-full flex flex-col items-center">
              <!-- Role / Station Title -->
              <span 
                class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full mb-1"
                :class="[
                  station.isActive 
                    ? 'bg-blue-100 text-blue-800' 
                    : station.isCompleted 
                      ? 'bg-emerald-50 text-emerald-700' 
                      : 'bg-gray-100 text-gray-500'
                ]"
              >
                {{ station.stationType }}
              </span>

              <!-- Officer Name -->
              <p 
                class="text-xs font-bold truncate max-w-full"
                :class="station.isActive ? 'text-blue-900 font-extrabold' : 'text-gray-800'"
                :title="station.officerName"
              >
                {{ station.officerName }}
              </p>

              <!-- Department / Role -->
              <p class="text-[11px] text-gray-500 truncate max-w-full" :title="station.roleOrDept">
                {{ station.roleOrDept }}
              </p>

              <!-- SLA / Tenure Time Spent -->
              <div 
                v-if="station.tenure"
                class="mt-1.5 flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-md"
                :class="station.isActive ? 'bg-blue-50 text-blue-700 font-semibold' : 'bg-gray-50 text-gray-500'"
              >
                <Icon icon="lucide:hourglass" width="11" />
                <span>{{ station.tenure }}</span>
              </div>

              <!-- Expanded directive preview snippet -->
              <div 
                v-if="isExpanded && station.directiveSnippet"
                class="mt-2 text-[11px] text-gray-600 bg-gray-50/80 p-2 rounded-lg border border-gray-100 text-left w-full line-clamp-2"
                :title="station.directiveSnippet"
              >
                <span class="font-semibold text-gray-700 mr-1">Directive:</span>
                {{ station.directiveSnippet }}
              </div>
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

const emit = defineEmits(['select-station']);

const page = usePage();
const isExpanded = ref(false);

const onSelectStation = (station) => {
  emit('select-station', station);
};

const getAvatarUrl = (user) => {
  if (user?.profile_image) {
    return (page.props.base || '') + '/' + user.profile_image;
  }
  return null;
};

const formatTenure = (startTime, endTime) => {
  if (!startTime) return '';
  const start = moment(startTime);
  const end = endTime ? moment(endTime) : moment();
  const diffHours = end.diff(start, 'hours');
  
  if (diffHours < 1) {
    const diffMinutes = Math.max(1, end.diff(start, 'minutes'));
    return `${diffMinutes}m`;
  }
  if (diffHours < 24) {
    return `${diffHours}h`;
  }
  const diffDays = Math.floor(diffHours / 24);
  const remHours = diffHours % 24;
  return remHours > 0 ? `${diffDays}d ${remHours}h` : `${diffDays}d`;
};

// Build the sequential stations for this task
const stations = computed(() => {
  const result = [];
  if (!props.task) return result;

  // 1. Origin Station (Task Creator / Registry)
  const creator = props.task.user || props.task.created_by_user;
  const creatorName = creator ? `${creator.first_name || ''} ${creator.last_name || ''}`.trim() : 'Initiating Desk';
  const creatorRole = creator?.department?.title || 'Originating Registry';

  result.push({
    id: 'origin',
    stationType: 'Origin',
    officerName: creatorName || 'Registry',
    roleOrDept: creatorRole,
    avatar: getAvatarUrl(creator),
    isCompleted: true,
    isActive: false,
    tenure: 'Initiated ' + moment(props.task.created_at).format('DD MMM'),
    directiveSnippet: props.task.description || '',
    user: creator
  });

  // 2. Sequential Assignments / Transit steps
  // Props.task.assignments is usually sorted DESC (latest first) in EDMS controllers.
  // We reverse it to get chronological route order (Initiator -> Lead -> Subordinate -> ...).
  const rawAssignments = props.task.assignments || [];
  const chronologicalAssignments = [...rawAssignments].reverse();

  chronologicalAssignments.forEach((assignment, index) => {
    const isLatest = index === chronologicalAssignments.length - 1;
    const isCompleted = (assignment.status || '').toLowerCase() === 'completed';
    const isUnderReview = ['under review', 'review'].includes((assignment.status || '').toLowerCase());
    const isPending = ['pending', 'in progress'].includes((assignment.status || '').toLowerCase());

    // Active desk is the latest incomplete assignment or one currently under review/in progress
    const isActive = isLatest && !isCompleted;

    const user = assignment.assigned_to;
    const officerName = user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'Assigned Staff';
    
    // Rank / Branch
    let roleOrDept = 'Staff Officer';
    if (user?.roles && user.roles.length > 0) {
      const r = user.roles.map(x => typeof x === 'object' ? x.name : String(x)).join(', ');
      roleOrDept = r;
    } else if (user?.department?.title) {
      roleOrDept = user.department.title;
    }

    // Station type label
    let stationType = `Desk ${index + 1}`;
    if (isUnderReview) {
      stationType = 'Under Review';
    } else if (isActive) {
      stationType = 'Active Desk';
    } else if (isCompleted) {
      stationType = 'Cleared';
    }

    // Tenure calculation
    const tenure = formatTenure(assignment.created_at, isCompleted ? assignment.updated_at : null);

    result.push({
      id: assignment.id,
      stationType: stationType,
      officerName: officerName,
      roleOrDept: roleOrDept,
      avatar: getAvatarUrl(user),
      isCompleted: isCompleted,
      isActive: isActive,
      tenure: isCompleted ? `Cleared in ${tenure}` : `At Desk for ${tenure}`,
      directiveSnippet: assignment.description || '',
      assignment: assignment,
      user: user
    });
  });

  // 3. Terminal Clearance Station (If task is completed or executive final approval)
  const isTaskCompleted = (props.task.status || '').toLowerCase() === 'completed';
  if (isTaskCompleted || result.length === 1) {
    result.push({
      id: 'terminal',
      stationType: isTaskCompleted ? 'Dispatched' : 'Final Clearance',
      officerName: isTaskCompleted ? 'Executive Approved' : 'Executive Clearance',
      roleOrDept: 'Accountant-General / DG Office',
      avatar: null,
      isCompleted: isTaskCompleted,
      isActive: false,
      tenure: isTaskCompleted && props.task.updated_at ? 'Completed ' + moment(props.task.updated_at).format('DD MMM') : '',
      directiveSnippet: isTaskCompleted ? 'All directives fulfilled and officially docketed.' : 'Awaiting final executive sign-off.'
    });
  }

  return result;
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
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
