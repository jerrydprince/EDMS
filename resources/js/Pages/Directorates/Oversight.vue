<template>
  <main class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 font-sans">
    <Toast />

    <!-- Executive Header -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <span class="px-2.5 py-0.5 text-xs font-bold font-mono uppercase bg-blue-900 text-blue-100 rounded-md border border-blue-800">
            Directorate Executive Suite
          </span>
          <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-md border border-emerald-200 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Real-Time Dwell Radar
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Directorate Oversight & Executive Clearance
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Executive monitoring radar, cross-division bottleneck detection, and direct file intervention for
          <strong class="text-slate-800">{{ props.selectedDepartment?.title || 'Selected Directorate' }}</strong>.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <Link
          :href="'/dashboard/directorates?department_id=' + selectedDeptId"
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:settings-2" width="16" class="text-blue-600" />
          <span>Configure Functions</span>
        </Link>
        <Link
          href="/dashboard/governance-hub"
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5"
        >
          <Icon icon="lucide:network" width="16" class="text-indigo-600" />
          <span>Structure Organogram</span>
        </Link>
      </div>
    </div>

    <!-- Directorate Switcher Strip -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="text-xs font-bold uppercase text-slate-500">Directorate:</span>
        <select
          v-model="selectedDeptId"
          @change="switchDepartment"
          class="bg-slate-50 border border-slate-200 text-slate-900 text-xs font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        >
          <option v-for="d in props.departments" :key="d.id" :value="d.id">
            {{ d.title }}
          </option>
        </select>
      </div>
      <div class="text-xs text-slate-500">
        Director / HOD: <strong class="text-slate-800">{{ props.selectedDepartment?.head_officer ? (props.selectedDepartment.head_officer.first_name + ' ' + props.selectedDepartment.head_officer.last_name) : 'Executive Office' }}</strong>
      </div>
    </div>

    <!-- Key Directorate Health Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
          <Icon icon="lucide:folder-open" width="24" />
        </div>
        <div>
          <span class="block text-2xl font-black text-slate-900">{{ totalActiveTasks }}</span>
          <span class="text-xs font-semibold text-slate-500">Active Dockets in Directorate</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
          <Icon icon="lucide:clock-alert" width="24" />
        </div>
        <div>
          <span class="block text-2xl font-black text-amber-700">{{ totalDelayedTasks }}</span>
          <span class="text-xs font-semibold text-slate-500">Delayed Files (&gt; 3 Days)</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold">
          <Icon icon="lucide:network" width="24" />
        </div>
        <div>
          <span class="block text-2xl font-black text-slate-900">{{ props.divisionStats?.length || 0 }}</span>
          <span class="text-xs font-semibold text-slate-500">Active Divisions</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
          <Icon icon="lucide:shield-check" width="24" />
        </div>
        <div>
          <span class="block text-2xl font-black text-emerald-700">
            {{ totalActiveTasks ? Math.round(((totalActiveTasks - totalDelayedTasks) / totalActiveTasks) * 100) : 100 }}%
          </span>
          <span class="text-xs font-semibold text-slate-500">SLA Compliance Rate</span>
        </div>
      </div>
    </div>

    <!-- Bottleneck & Dwell Time Heatmap by Division -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
        <div>
          <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <Icon icon="lucide:activity" width="16" class="text-blue-600" />
            <span>Division Dwell Radar & Performance Health</span>
          </h3>
          <p class="text-xs text-slate-500">Real-time status of workload and file stagnation across divisions</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 uppercase text-[10px] font-black tracking-wider text-slate-500 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3">Division Name</th>
              <th class="px-4 py-3">Units & Staff</th>
              <th class="px-4 py-3">Active Files</th>
              <th class="px-4 py-3">Overdue (&gt; 3 Days)</th>
              <th class="px-4 py-3">Radar Health Status</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="div in props.divisionStats" :key="div.id" class="hover:bg-slate-50/50 transition">
              <td class="px-4 py-3 font-bold text-slate-900">{{ div.title }}</td>
              <td class="px-4 py-3 font-mono text-[11px]">{{ div.units_count }} Units &bull; {{ div.staff_count }} Staff</td>
              <td class="px-4 py-3 font-bold font-mono">{{ div.active_tasks }} files</td>
              <td class="px-4 py-3 font-bold font-mono" :class="div.delayed_tasks > 0 ? 'text-amber-700' : 'text-slate-400'">
                {{ div.delayed_tasks }}
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono"
                  :class="{
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': div.health_status === 'healthy',
                    'bg-amber-100 text-amber-800 border border-amber-200': div.health_status === 'warning',
                    'bg-red-100 text-red-800 border border-red-200': div.health_status === 'critical',
                  }"
                >
                  {{ div.health_status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <Link
                  :href="'/dashboard/directorates?department_id=' + selectedDeptId + '&division_id=' + div.id"
                  class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1"
                >
                  <span>Inspect Division</span>
                  <Icon icon="lucide:chevron-right" width="12" />
                </Link>
              </td>
            </tr>
            <tr v-if="!props.divisionStats?.length">
              <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                No divisions registered under this directorate.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Executive Minute Stream & File Call-Up -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
        <div>
          <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <Icon icon="lucide:file-stack" width="16" class="text-indigo-600" />
            <span>Executive Minute Stream & Emergency Call-Up</span>
          </h3>
          <p class="text-xs text-slate-500">Live docket stream with power to immediately recall any stalled file to the Director's desk</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 uppercase text-[10px] font-black tracking-wider text-slate-500 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3">Tracking / File #</th>
              <th class="px-4 py-3">Title / Subject</th>
              <th class="px-4 py-3">Current Status</th>
              <th class="px-4 py-3">Logged Date</th>
              <th class="px-4 py-3 text-right">Executive Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="task in props.executiveStream" :key="task.id" class="hover:bg-slate-50/50 transition">
              <td class="px-4 py-3 font-mono font-bold text-blue-700">#{{ task.id }}</td>
              <td class="px-4 py-3 font-semibold text-slate-900">{{ task.title }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                  {{ task.status }}
                </span>
              </td>
              <td class="px-4 py-3 font-mono text-[11px] text-slate-500">
                {{ new Date(task.created_at).toLocaleDateString() }}
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="openCallUpModal(task)"
                  class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1"
                >
                  <Icon icon="lucide:arrow-down-left" width="13" />
                  <span>Call Up File</span>
                </button>
              </td>
            </tr>
            <tr v-if="!props.executiveStream?.length">
              <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                No active files currently streaming in this directorate.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Call Up Confirmation Modal -->
    <Dialog v-model:visible="showCallUpModal" modal header="Executive File Call-Up" :style="{ width: '32rem' }">
      <div v-if="selectedTask" class="p-4 space-y-4">
        <p class="text-xs text-slate-600">
          You are about to exercise executive power to recall <strong class="text-slate-900">File #{{ selectedTask.id }}: {{ selectedTask.title }}</strong> from its current desk directly to the Director's Executive Desk.
        </p>

        <div>
          <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Reason for Emergency Call-Up</label>
          <textarea
            v-model="callUpReason"
            rows="3"
            class="w-full border border-slate-300 rounded-xl p-3 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
            placeholder="e.g., File delayed past SLA; recalled for executive minute and urgent AGF clearance."
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="showCallUpModal = false" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800">
            Cancel
          </button>
          <button
            type="button"
            @click="submitCallUp"
            :disabled="!callUpReason.trim()"
            class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-xs"
          >
            Confirm Executive Recall
          </button>
        </div>
      </div>
    </Dialog>

  </main>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Toast from 'primevue/toast';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  departments: { type: Array, default: () => [] },
  selectedDepartment: { type: Object, default: () => null },
  divisionStats: { type: Array, default: () => [] },
  executiveStream: { type: Array, default: () => [] },
});

const toast = useToast();
const selectedDeptId = ref(props.selectedDepartment?.id || props.departments?.[0]?.id || '');
const showCallUpModal = ref(false);
const selectedTask = ref(null);
const callUpReason = ref('');

const totalActiveTasks = computed(() => {
  return props.divisionStats?.reduce((acc, curr) => acc + (curr.active_tasks || 0), 0) || 0;
});

const totalDelayedTasks = computed(() => {
  return props.divisionStats?.reduce((acc, curr) => acc + (curr.delayed_tasks || 0), 0) || 0;
});

const switchDepartment = () => {
  router.get('/dashboard/directorates/oversight', { department_id: selectedDeptId.value });
};

const openCallUpModal = (task) => {
  selectedTask.value = task;
  callUpReason.value = '';
  showCallUpModal.value = true;
};

const submitCallUp = () => {
  router.post('/dashboard/directorates/call-up-file', {
    task_id: selectedTask.value.id,
    reason: callUpReason.value,
  }, {
    onSuccess: () => {
      showCallUpModal.value = false;
      toast.add({
        severity: 'success',
        summary: 'File Recalled',
        detail: `File #${selectedTask.value.id} is now on the Director's desk.`,
        life: 3000,
      });
    }
  });
};
</script>
