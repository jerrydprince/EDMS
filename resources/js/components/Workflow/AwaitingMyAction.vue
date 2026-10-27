<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center shadow-xs">
          <Icon icon="lucide:hourglass" width="18" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-base font-bold text-gray-800">Awaiting My Action</h3>
            <span v-if="tasks.length > 0" class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-full">
              {{ tasks.length }}
            </span>
          </div>
          <p class="text-xs text-gray-500">Tasks requiring your endorsement, signature, or review with live SLA countdowns</p>
        </div>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button 
          @click="fetchQueue" 
          :disabled="isLoading"
          class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <Icon icon="lucide:refresh-cw" width="13" :class="{'animate-spin': isLoading}" />
          <span>Refresh Queue</span>
        </button>
      </div>
    </div>

    <!-- Filter & SLA Radar Controls Toolbar -->
    <div v-if="tasks.length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/70 p-3 rounded-2xl border border-gray-200/80">
      <!-- SLA Radar Filter Pills -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <button
          type="button"
          @click="activeSlaFilter = 'all'"
          :class="[
            'px-3 py-1 rounded-xl text-xs font-semibold transition-all cursor-pointer',
            activeSlaFilter === 'all' ? 'bg-white text-blue-600 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-800'
          ]"
        >
          All ({{ tasks.length }})
        </button>

        <button
          type="button"
          @click="activeSlaFilter = 'overdue'"
          :class="[
            'px-2.5 py-1 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer',
            activeSlaFilter === 'overdue' ? 'bg-rose-100 text-rose-800 shadow-2xs font-bold' : 'text-rose-600 hover:bg-rose-50'
          ]"
        >
          <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
          <span>Overdue ({{ overdueCount }})</span>
        </button>

        <button
          type="button"
          @click="activeSlaFilter = 'urgent'"
          :class="[
            'px-2.5 py-1 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer',
            activeSlaFilter === 'urgent' ? 'bg-amber-100 text-amber-800 shadow-2xs font-bold' : 'text-amber-600 hover:bg-amber-50'
          ]"
        >
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
          <span>Urgent ({{ urgentCount }})</span>
        </button>
      </div>

      <!-- Search Input & Master Checkbox -->
      <div class="flex items-center gap-3">
        <div class="relative w-full sm:w-48">
          <Icon icon="lucide:search" width="13" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search queue..."
            class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-xl focus:border-blue-400 outline-none transition-all shadow-2xs"
          />
        </div>

        <!-- Master Select All -->
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 cursor-pointer select-none shrink-0 px-2 py-1 bg-white border border-gray-200 rounded-xl shadow-2xs hover:bg-gray-50">
          <input 
            type="checkbox" 
            :checked="isAllSelected" 
            @change="toggleSelectAll" 
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer"
          />
          <span>Select All</span>
        </label>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!isLoading && tasks.length === 0" class="text-center py-12 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200 text-gray-400">
      <Icon icon="lucide:check-circle-2" width="36" class="mx-auto mb-2 text-emerald-400" />
      <p class="text-sm font-semibold text-gray-700">All Caught Up!</p>
      <p class="text-xs text-gray-400 mt-0.5">No tasks are currently awaiting your signature or action.</p>
    </div>

    <!-- Filtered Empty State -->
    <div v-else-if="!isLoading && filteredTasks.length === 0" class="text-center py-8 bg-gray-50/40 rounded-2xl border border-gray-200 text-gray-400">
      <p class="text-xs font-semibold text-gray-600">No tasks match the active filter or search query.</p>
    </div>

    <!-- Task List -->
    <div v-else class="space-y-3">
      <div 
        v-for="item in filteredTasks" 
        :key="item.id"
        class="bg-white rounded-2xl border p-4 transition-all shadow-2xs hover:shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4 group"
        :class="[
          selectedTaskIds.includes(item.id) 
            ? 'border-blue-400 bg-blue-50/20 ring-2 ring-blue-100' 
            : 'border-gray-200/90 hover:border-blue-300'
        ]"
      >
        <div class="flex items-start gap-3.5 min-w-0">
          <!-- Multi-Select Checkbox -->
          <div class="pt-1 shrink-0">
            <input 
              type="checkbox" 
              :value="item.id" 
              v-model="selectedTaskIds" 
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer transition-all"
            />
          </div>

          <!-- Priority Icon Avatar -->
          <div 
            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs mt-0.5"
            :class="getPriorityClass(item.task?.priority)"
          >
            <Icon icon="lucide:file-text" width="20" />
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-[11px] font-mono text-gray-400 font-semibold">{{ item.task?.tracking_id }}</span>
              <span 
                class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
                :class="getSlaBadge(item).badgeClass"
              >
                {{ getSlaBadge(item).label }}
              </span>
            </div>

            <h4 class="text-sm font-bold text-gray-800 truncate mt-1 group-hover:text-blue-600 transition-colors" :title="item.task?.title">
              {{ item.task?.title || 'Untitled Task' }}
            </h4>

            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
              <span>Assigned by: <strong class="text-gray-700">{{ item.assigned_by?.first_name }} {{ item.assigned_by?.last_name }}</strong></span>
              <span v-if="item.assigned_by?.department?.title" class="text-gray-400">({{ item.assigned_by.department.title }})</span>
              <span>• Received: {{ formatDate(item.created_at) }}</span>
            </p>
          </div>
        </div>

        <!-- Action CTA -->
        <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
          <div class="text-right hidden sm:block">
            <p class="text-[10px] uppercase font-bold text-gray-400">SLA Timer</p>
            <p class="text-xs font-bold" :class="getSlaBadge(item).textClass">
              {{ getSlaBadge(item).timeText }}
            </p>
          </div>

          <Link 
            :href="`/dashboard/view-task/${item.task_id}`"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-xs hover:shadow-md transition-all flex items-center gap-1.5 hover:scale-105 active:scale-95"
          >
            <span>Open & Endorse</span>
            <Icon icon="lucide:chevron-right" width="14" />
          </Link>
        </div>
      </div>
    </div>

    <!-- Floating Sticky Batch Action Bar -->
    <Transition name="fade-slide">
      <div 
        v-if="selectedTaskIds.length > 0"
        class="sticky bottom-4 z-40 bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center justify-between gap-4 border border-slate-700 max-w-2xl mx-auto"
      >
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">
            {{ selectedTaskIds.length }}
          </span>
          <div>
            <p class="text-xs font-bold text-slate-100">
              {{ selectedTaskIds.length }} Task{{ selectedTaskIds.length === 1 ? '' : 's' }} Selected
            </p>
            <p class="text-[11px] text-slate-400">Ready for fast-clearance batch approval</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="selectedTaskIds = []"
            class="px-3 py-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
          >
            Cancel
          </button>
          
          <button
            type="button"
            @click="openBatchApprovalModal = true"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 hover:scale-105 active:scale-95 cursor-pointer"
          >
            <Icon icon="lucide:badge-check" width="16" />
            <span>Batch Approve ({{ selectedTaskIds.length }})</span>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Fast-Clearance Batch Approval Modal -->
    <Dialog 
      v-model:visible="openBatchApprovalModal"
      modal 
      :style="{ width: '38rem' }" 
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
      :pt="{
        root: { class: 'bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 font-sans' },
        header: { class: 'bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-100' },
        content: { class: 'p-6' }
      }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
            <Icon icon="lucide:check-check" width="22" />
          </div>
          <div>
            <h3 class="text-base font-bold text-gray-900">Fast-Clearance Batch Approval</h3>
            <p class="text-xs text-gray-500">Sign and clear {{ selectedTaskIds.length }} tasks with a single PIN verification</p>
          </div>
        </div>
      </template>

      <div class="space-y-4">
        <!-- Tasks Summary Strip -->
        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 text-xs text-gray-600 flex items-center justify-between">
          <span>Batch Size: <strong class="text-gray-900">{{ selectedTaskIds.length }} files/tasks</strong></span>
          <span class="text-[11px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">
            All Queued for Endorsement
          </span>
        </div>

        <!-- Executive Minute Directive -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Official Executive Minute / Directive:
          </label>
          <!-- Quick Preset Chips -->
          <div class="flex flex-wrap gap-1.5 mb-2">
            <button
              type="button"
              v-for="chip in batchPresetDirectives"
              :key="chip"
              @click="batchDirective = chip"
              class="text-[11px] px-2.5 py-1 rounded-lg border bg-gray-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition-colors cursor-pointer text-left"
              :class="batchDirective === chip ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold' : 'text-gray-600 border-gray-200'"
            >
              {{ chip }}
            </button>
          </div>
          <textarea
            v-model="batchDirective"
            rows="3"
            placeholder="Enter batch directive..."
            class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all leading-relaxed"
          ></textarea>
        </div>

        <!-- Digital Signature PIN -->
        <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl flex flex-col items-center justify-center text-center">
          <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 mb-1">
            <Icon icon="lucide:lock" width="14" />
            <span>Authorized Digital Signature Verification</span>
          </div>
          <p class="text-[11px] text-amber-700 mb-3">Enter your 4-digit signature PIN to endorse this batch</p>
          <input
            v-model="batchPin"
            type="password"
            maxlength="4"
            placeholder="••••"
            class="w-36 text-center text-2xl font-bold tracking-widest py-2 bg-white border border-amber-300 rounded-xl focus:border-blue-500 outline-none shadow-inner"
            @keyup.enter="executeBatchApproval"
          />
          <p v-if="batchError" class="text-xs text-rose-600 mt-2 font-bold">{{ batchError }}</p>
        </div>

        <!-- Live Processing Progress -->
        <div v-if="isProcessingBatch" class="p-3 bg-blue-50 rounded-xl border border-blue-200 flex items-center gap-3">
          <Icon icon="lucide:loader-2" class="animate-spin text-blue-600 shrink-0" width="20" />
          <div class="flex-1 min-w-0 text-xs">
            <p class="font-bold text-blue-900">Processing Approvals...</p>
            <p class="text-blue-700 text-[11px]">Endorsing task {{ batchProgress.current }} of {{ batchProgress.total }}</p>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="openBatchApprovalModal = false"
            :disabled="isProcessingBatch"
            class="px-4 py-2 rounded-xl text-gray-600 hover:bg-gray-100 text-xs font-semibold transition-colors cursor-pointer"
          >
            Cancel
          </button>
          
          <button
            type="button"
            @click="executeBatchApproval"
            :disabled="isProcessingBatch || !batchPin"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Icon v-if="isProcessingBatch" icon="lucide:loader-2" class="animate-spin" width="14" />
            <Icon v-else icon="lucide:check-circle" width="14" />
            <span>{{ isProcessingBatch ? 'Approving Batch...' : 'Confirm & Sign All' }}</span>
          </button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Swal from 'sweetalert2';

const tasks = ref([]);
const isLoading = ref(false);

const selectedTaskIds = ref([]);
const activeSlaFilter = ref('all'); // 'all', 'overdue', 'urgent'
const searchQuery = ref('');

const openBatchApprovalModal = ref(false);
const batchDirective = ref('Approved as recommended and cleared for further administrative action.');
const batchPin = ref('');
const batchError = ref('');
const isProcessingBatch = ref(false);
const batchProgress = ref({ current: 0, total: 0 });

const batchPresetDirectives = [
  'Approved as recommended and cleared for further action.',
  'Endorsed for onward processing and final compliance.',
  'Approved. Proceed to disbursement and official dispatch.'
];

const fetchQueue = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/dashboard/tasks-awaiting-my-action');
    tasks.value = res.data?.data || [];
  } catch (err) {
    console.error('Failed to load awaiting action queue:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchQueue();
});

// SLA calculations
const getDiffHours = (created_at) => {
  const created = new Date(created_at);
  const now = new Date();
  return Math.floor((now - created) / (1000 * 60 * 60));
};

const overdueCount = computed(() => {
  return tasks.value.filter(t => getDiffHours(t.created_at) >= 48).length;
});

const urgentCount = computed(() => {
  return tasks.value.filter(t => {
    const h = getDiffHours(t.created_at);
    return h >= 24 && h < 48;
  }).length;
});

// Filtered tasks based on SLA radar and search
const filteredTasks = computed(() => {
  let list = [...tasks.value];

  // SLA filter
  if (activeSlaFilter.value === 'overdue') {
    list = list.filter(t => getDiffHours(t.created_at) >= 48);
  } else if (activeSlaFilter.value === 'urgent') {
    list = list.filter(t => {
      const h = getDiffHours(t.created_at);
      return h >= 24 && h < 48;
    });
  }

  // Search query filter
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase();
    list = list.filter(t => {
      const title = (t.task?.title || '').toLowerCase();
      const tracking = (t.task?.tracking_id || '').toLowerCase();
      const assigner = `${t.assigned_by?.first_name || ''} ${t.assigned_by?.last_name || ''}`.toLowerCase();
      return title.includes(q) || tracking.includes(q) || assigner.includes(q);
    });
  }

  return list;
});

// Master Selection
const isAllSelected = computed(() => {
  if (filteredTasks.value.length === 0) return false;
  return filteredTasks.value.every(t => selectedTaskIds.value.includes(t.id));
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedTaskIds.value = [];
  } else {
    selectedTaskIds.value = filteredTasks.value.map(t => t.id);
  }
};

// Batch Approval Execution
const executeBatchApproval = async () => {
  if (!batchPin.value) {
    batchError.value = 'Please enter your digital signature PIN.';
    return;
  }

  batchError.value = '';
  isProcessingBatch.value = true;
  const itemsToApprove = tasks.value.filter(t => selectedTaskIds.value.includes(t.id));
  batchProgress.value = { current: 0, total: itemsToApprove.length };

  let successCount = 0;
  let failCount = 0;

  for (let i = 0; i < itemsToApprove.length; i++) {
    const item = itemsToApprove[i];
    batchProgress.value.current = i + 1;

    try {
      await axios.post('/dashboard/approve-task', {
        task_id: item.task_id,
        task_assignment_id: item.id,
        directive: batchDirective.value,
        approval_pin: batchPin.value
      });
      successCount++;
    } catch (err) {
      console.error(`Failed to approve task ${item.task_id}:`, err);
      failCount++;
      if (err.response?.data?.errors?.approval_pin) {
        batchError.value = 'Invalid digital signature PIN. Batch halted.';
        isProcessingBatch.value = false;
        return;
      }
    }
  }

  isProcessingBatch.value = false;
  openBatchApprovalModal.value = false;
  selectedTaskIds.value = [];
  batchPin.value = '';

  await fetchQueue();

  if (successCount > 0) {
    Swal.fire({
      icon: 'success',
      title: 'Batch Endorsed',
      text: `${successCount} task${successCount === 1 ? '' : 's'} successfully endorsed and cleared.`,
      timer: 2500,
      showConfirmButton: false,
      customClass: {
        popup: 'rounded-3xl shadow-xl'
      }
    });
  }
};

const getPriorityClass = (priority) => {
  if (priority == 3) return 'bg-rose-50 text-rose-600 border border-rose-100';
  if (priority == 2) return 'bg-amber-50 text-amber-600 border border-amber-100';
  return 'bg-blue-50 text-blue-600 border border-blue-100';
};

const getSlaBadge = (assignment) => {
  const diffHours = getDiffHours(assignment.created_at);

  if (diffHours >= 48) {
    return {
      label: '48h Escalated',
      badgeClass: 'bg-rose-100 text-rose-800 border border-rose-200 animate-pulse',
      timeText: `${diffHours}h elapsed (Escalated)`,
      textClass: 'text-rose-600'
    };
  } else if (diffHours >= 24) {
    return {
      label: '24h Reminder Sent',
      badgeClass: 'bg-amber-100 text-amber-800 border border-amber-200',
      timeText: `${diffHours}h elapsed (Overdue)`,
      textClass: 'text-amber-600'
    };
  } else {
    const remaining = Math.max(0, 24 - diffHours);
    return {
      label: 'Within SLA',
      badgeClass: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
      timeText: `${remaining}h remaining`,
      textClass: 'text-emerald-600'
    };
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease-out;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}
</style>
