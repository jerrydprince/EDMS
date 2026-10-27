<template>
  <div v-if="task && task.workflow_version_id" class="bg-gradient-to-r from-emerald-50/80 via-teal-50/60 to-blue-50/50 border border-emerald-200/90 rounded-2xl p-4 md:p-5 mb-5 shadow-xs backdrop-blur-sm">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <!-- Left: Institutional Workflow Identity & Step -->
      <div class="flex items-start md:items-center gap-3.5 min-w-0">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20 border border-emerald-400/40">
          <Icon icon="lucide:git-commit" width="22" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-bold text-gray-900 text-sm md:text-base tracking-tight truncate">
              {{ task.workflow_version?.workflow?.title || 'Official Statutory Workflow' }}
            </h3>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 px-2 py-0.5 rounded-full">
              Civil Service Blueprint v{{ task.workflow_version?.version_number || 1 }}
            </span>
          </div>

          <div class="flex items-center gap-2 mt-1 flex-wrap text-xs">
            <span class="text-gray-500 font-medium">Current Milestone:</span>
            <span class="font-bold text-emerald-900 bg-white/90 border border-emerald-200 px-2.5 py-0.5 rounded-lg shadow-2xs">
              {{ activeStepName }}
            </span>
            <span v-if="primaryAction?.to_step?.name" class="text-gray-500 font-medium flex items-center gap-1">
              <Icon icon="lucide:arrow-right" width="12" class="text-gray-400" />
              <span>Next: <strong class="text-emerald-800">{{ primaryAction.to_step.name }}</strong></span>
            </span>
          </div>
        </div>
      </div>

      <!-- Right: SLA Status & Stage-Aware Actions -->
      <div class="flex items-center gap-2.5 flex-wrap self-end lg:self-auto justify-end">
        <!-- SLA Countdown Pill -->
        <div v-if="activeSteps.length > 0 && activeSteps[0].due_date" class="px-3 py-1.5 rounded-xl border flex items-center gap-2 text-xs font-semibold shadow-2xs"
          :class="isOverdue ? 'bg-red-50 text-red-700 border-red-200 animate-pulse' : 'bg-amber-50 text-amber-800 border-amber-200'">
          <Icon :icon="isOverdue ? 'lucide:alert-circle' : 'lucide:clock'" width="15" />
          <span>{{ slaLabel }}</span>
        </div>

        <!-- Available Workflow Transitions -->
        <div v-if="loadingActions" class="text-xs text-gray-400 flex items-center gap-1.5 px-3 py-2">
          <Icon icon="lucide:loader-2" class="animate-spin text-emerald-600" width="16" />
          <span>Checking actions...</span>
        </div>

        <template v-else-if="availableActions.length > 0">
          <button 
            v-for="action in availableActions" 
            :key="action.id"
            @click="openActionModal(action)"
            class="font-bold text-xs rounded-xl px-4 py-2.5 transition-all shadow-md hover:shadow-lg flex items-center gap-2 text-white cursor-pointer"
            :class="getActionColor(action.action_type)"
          >
            <Icon :icon="getActionIcon(action.action_type)" width="16" />
            <span>{{ action.action_name || action.name }}</span>
          </button>
        </template>

        <!-- Submitted Badge when with reviewing authority -->
        <div v-else class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200 shadow-2xs">
          <Icon icon="lucide:check-circle" width="16" class="text-emerald-600" />
          <span>Submitted • Docket with Reviewing Authority</span>
        </div>

        <!-- Admin Only: Pause SLA / Initiate Sub-Workflow -->
        <template v-if="canManageSla && activeSteps.length > 0">
          <button 
            v-if="!isPaused"
            @click="togglePause(true)"
            class="font-semibold text-xs rounded-xl px-3 py-2 border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 transition-colors flex items-center gap-1.5 cursor-pointer"
            title="Pause Statutory SLA"
          >
            <Icon icon="lucide:pause" width="14" />
            <span>Pause SLA</span>
          </button>
          <button 
            v-else
            @click="togglePause(false)"
            class="font-semibold text-xs rounded-xl px-3 py-2 border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition-colors flex items-center gap-1.5 cursor-pointer"
            title="Resume Statutory SLA"
          >
            <Icon icon="lucide:play" width="14" />
            <span>Resume SLA</span>
          </button>

          <button 
            v-if="availableActions.length > 0"
            @click="showSubWorkflowModal = true"
            class="font-semibold text-xs rounded-xl px-3 py-2 border border-indigo-300 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors flex items-center gap-1.5 cursor-pointer"
            title="Initiate Departmental Sub-Workflow"
          >
            <Icon icon="lucide:git-pull-request" width="14" />
            <span>Sub-Workflow</span>
          </button>
        </template>
      </div>
    </div>

    <!-- Checklists (If any required in current active step) -->
    <div class="mt-4 pt-3.5 border-t border-emerald-200/80" v-if="activeChecklists.length > 0">
      <h4 class="font-bold text-gray-700 mb-2.5 text-xs flex items-center gap-2">
        <Icon icon="lucide:check-square" class="text-emerald-600" width="16" />
        <span>Statutory Pre-Submission Checklist:</span>
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <label v-for="item in activeChecklists" :key="item.id" class="flex items-start gap-2.5 p-2 bg-white/80 rounded-xl border border-emerald-100 text-xs text-gray-700 cursor-pointer hover:bg-white transition-colors">
          <input type="checkbox" v-model="checklistState[item.id]" class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 cursor-pointer">
          <span class="leading-relaxed">{{ item.item_description || item.item_text }} <span v-if="item.is_required" class="text-rose-600 font-bold">*</span></span>
        </label>
      </div>
    </div>

    <!-- Dynamic Action & Digital Signature Executive Modal -->
    <Teleport to="body">
      <div v-if="showActionModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 overflow-y-auto">
        <div class="bg-white rounded-3xl shadow-2xl w-[95vw] md:w-[680px] max-h-[92vh] flex flex-col border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
          <!-- Modal Header -->
          <div 
            class="px-6 py-5 flex items-center justify-between text-white shrink-0 shadow-xs relative"
            :class="getModalHeaderColor(selectedAction?.action_type)"
          >
            <div class="flex items-center gap-3.5">
              <div class="w-11 h-11 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 shrink-0">
                <Icon :icon="getActionIcon(selectedAction?.action_type)" width="22" class="text-white" />
              </div>
              <div>
                <h3 class="text-base md:text-lg font-bold text-white tracking-tight">
                  {{ selectedAction?.action_name || selectedAction?.name || 'Workflow Statutory Action' }}
                </h3>
                <p class="text-xs opacity-90 mt-0.5">
                  Official Digital Signature, Minute & Milestone Clearance
                </p>
              </div>
            </div>
            <button 
              @click="showActionModal = false" 
              type="button"
              class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white cursor-pointer transition-colors"
            >
              <Icon icon="lucide:x" width="18" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 md:p-7 flex flex-col gap-5 overflow-y-auto max-h-[calc(92vh-150px)] custom-scrollbar bg-slate-50/50">
            <!-- Next Destination Info Card -->
            <div v-if="selectedAction?.to_step?.name || selectedAction?.target_user" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                  <Icon icon="lucide:arrow-right-circle" width="22" />
                </div>
                <div>
                  <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Statutory Destination</p>
                  <div class="text-sm font-bold text-slate-800 flex items-center gap-2 flex-wrap mt-0.5">
                    <span v-if="selectedAction.to_step?.name">{{ selectedAction.to_step.name }}</span>
                    <span v-if="selectedAction.target_user" class="text-xs px-2.5 py-0.5 bg-emerald-100 text-emerald-850 rounded-md font-semibold">
                      {{ selectedAction.target_user.name }} • {{ selectedAction.target_user.role }}
                    </span>
                  </div>
                </div>
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg shrink-0 hidden sm:inline-block">
                Milestone Transit
              </span>
            </div>

            <!-- Pre-Submission Checklist Verification Inside Modal -->
            <div v-if="activeChecklists.length > 0" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                  <Icon icon="lucide:check-square" width="15" class="text-emerald-600" />
                  Statutory Pre-Submission Verification
                </label>
                <span class="text-[11px] text-slate-400">Confirm all requirements before signing</span>
              </div>
              <div class="space-y-2">
                <label 
                  v-for="item in activeChecklists" 
                  :key="item.id" 
                  class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer"
                  :class="checklistState[item.id] ? 'bg-emerald-50/60 border-emerald-300 text-emerald-950 font-semibold' : 'bg-slate-50/80 border-slate-200 text-slate-700 hover:bg-slate-100/60'"
                >
                  <input 
                    type="checkbox" 
                    v-model="checklistState[item.id]" 
                    class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 cursor-pointer"
                  />
                  <span class="text-xs leading-relaxed">
                    {{ item.item_description || item.item_text }}
                    <span v-if="item.is_required" class="text-rose-600 font-bold ml-1">* (Mandatory)</span>
                  </span>
                </label>
              </div>
            </div>

            <!-- Minute Directive / Remarks with Suggestion Chips -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                  <Icon icon="lucide:message-square" width="14" class="text-emerald-600" />
                  Official Minute / Remarks <span v-if="selectedAction?.requires_comment" class="text-rose-500">*</span>
                </label>
                <span class="text-[11px] text-slate-400">Recorded on official docket minute sheet</span>
              </div>

            <div class="flex flex-wrap gap-2">
              <button 
                type="button"
                v-for="(chip, i) in signChips" 
                :key="i"
                @click="actionComments = chip"
                class="text-xs px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
                :class="actionComments === chip ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'"
              >
                {{ chip }}
              </button>
            </div>

            <textarea 
              v-model="actionComments" 
              rows="3" 
              class="w-full text-xs border border-slate-200 rounded-xl p-3.5 outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/15 text-slate-800 resize-none transition-all placeholder:text-slate-400" 
              placeholder="Enter official endorsement notes or instructions for minute sheet..."
            ></textarea>
          </div>

          <!-- Supporting Attachments (Optional) -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <Icon icon="lucide:paperclip" width="14" class="text-emerald-600" />
                Supporting Attachments (Optional)
              </label>
              <span class="text-[11px] text-slate-400">PDF, DOCX, PNG, JPG (max 20MB)</span>
            </div>

            <div class="relative border-2 border-dashed border-slate-200 hover:border-emerald-400 bg-slate-50/50 hover:bg-emerald-50/20 rounded-xl p-3.5 transition-all text-center cursor-pointer group">
              <input type="file" multiple @change="handleFiles" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" />
              <div class="flex flex-col items-center justify-center gap-1 pointer-events-none">
                <Icon icon="lucide:upload-cloud" width="22" class="text-slate-400 group-hover:text-emerald-600 transition-colors" />
                <p class="text-xs text-slate-600 font-medium">
                  <span class="text-emerald-600 font-bold">Click to upload</span> or drag and drop supporting clearance documents
                </p>
              </div>
            </div>

            <div v-if="attachments.length > 0" class="flex flex-wrap gap-2 pt-1">
              <div v-for="(file, idx) in attachments" :key="idx" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-1.5 rounded-lg text-xs font-medium">
                <Icon icon="lucide:file" width="14" />
                <span class="truncate max-w-[200px]">{{ file.name }}</span>
                <button @click="removeFile(idx)" type="button" class="text-emerald-600 hover:text-rose-600 transition-colors cursor-pointer ml-1">
                  <Icon icon="lucide:x" width="14" />
                </button>
              </div>
            </div>
          </div>

          <!-- Executive Digital Signature PIN Card -->
          <div class="bg-gradient-to-br from-amber-500/10 via-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-200 shadow-inner flex flex-col items-center gap-2">
            <div class="flex items-center gap-2 text-amber-900 font-bold text-xs uppercase tracking-wider">
              <Icon icon="lucide:key" width="15" class="text-amber-700" />
              <span>Digital Signature PIN</span>
            </div>
            <p class="text-xs text-amber-700/90 text-center">Verify your PIN to stamp official digital signature and endorse docket</p>

            <div class="w-full max-w-xs relative mt-1">
              <input 
                v-model="actionPin" 
                type="password" 
                placeholder="••••" 
                autocomplete="off"
                @keyup.enter="executeAction"
                class="w-full text-center tracking-[0.4em] text-lg font-bold bg-white border-2 border-amber-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 rounded-xl py-2 px-4 outline-none transition-all text-amber-950 placeholder:text-amber-200 shadow-xs"
              />
            </div>
          </div>

          <div v-if="actionError" class="p-3 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 flex items-center gap-2">
            <Icon icon="lucide:alert-circle" width="16" class="shrink-0 text-rose-600" />
            <span>{{ actionError }}</span>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
          <button 
            type="button" 
            @click="showActionModal = false" 
            class="px-4 py-2.5 rounded-xl font-medium text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
          >
            Cancel
          </button>
          <button 
            type="button" 
            @click="executeAction" 
            class="px-6 py-2.5 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-all" 
            :class="getActionColor(selectedAction?.action_type)" 
            :disabled="isProcessing || !actionPin || hasUncheckedRequiredChecklist"
          >
            <Icon v-if="isProcessing" icon="lucide:loader-2" class="animate-spin" width="16" />
            <Icon v-else :icon="getActionIcon(selectedAction?.action_type)" width="16" />
            <span>{{ isProcessing ? 'Signing & Submitting...' : (selectedAction?.action_name || selectedAction?.name || 'Sign & Forward Docket') }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>

    <!-- Sub-Workflow Modal -->
    <Teleport to="body">
      <div v-if="showSubWorkflowModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
             <Icon icon="lucide:git-pull-request" class="text-indigo-600" width="20" />
             <span>Initiate Departmental Sub-Workflow</span>
          </h3>
          
          <p class="mb-4 text-xs text-gray-500">
            Spawn a sub-workflow docket for inter-branch clearances.
          </p>

          <div class="mb-3.5">
            <label class="block text-xs font-bold text-gray-700 mb-1">Sub-Workflow Title <span class="text-rose-500">*</span></label>
            <input type="text" v-model="subWorkflowTitle" class="w-full border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="e.g. MDA Verification Schedule">
          </div>

          <div class="mb-4">
            <label class="block text-xs font-bold text-gray-700 mb-1">Workflow Blueprint <span class="text-rose-500">*</span></label>
            <select v-model="selectedSubWorkflowId" class="w-full border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-indigo-500">
              <option value="" disabled>Select a workflow template</option>
              <option v-for="wv in workflowVersions" :key="wv.id" :value="wv.id">
                {{ wv.name || wv.workflow?.title || 'Blueprint v' + wv.version_number }}
              </option>
            </select>
          </div>

          <div v-if="subWorkflowError" class="mb-3 text-xs text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-200">
            {{ subWorkflowError }}
          </div>

          <div class="flex justify-end gap-2.5 pt-2">
            <button @click="showSubWorkflowModal = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">Cancel</button>
            <button @click="startSubWorkflow" class="px-5 py-2 text-white rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 cursor-pointer" :disabled="isProcessing">
              {{ isProcessing ? 'Starting...' : 'Start Sub-Workflow' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from '@iconify/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  task: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.user || {});
const userRoles = computed(() => (page.props.user_roles || page.props.roles || []).map(r => String(r).toLowerCase()));
const canManageSla = computed(() => userRoles.value.some(r => ['super user', 'super admin', 'admin', 'director'].includes(r)));

const activeSteps = ref([]);
const availableActions = ref([]);
const workflowLogs = ref([]);
const activeChecklists = ref([]);
const checklistState = ref({});

const loadingActions = ref(true);
const isProcessing = ref(false);

const showActionModal = ref(false);
const selectedAction = ref(null);
const actionComments = ref('Respectfully submitted for your consideration and recommendation, sir.');
const actionPin = ref('');
const actionUserId = ref('');
const actionFile = ref(null);
const attachments = ref([]);
const actionError = ref('');

// Sub-Workflow State
const showSubWorkflowModal = ref(false);
const subTasks = ref([]);
const workflowVersions = ref([]);
const selectedSubWorkflowId = ref('');
const subWorkflowTitle = ref('');
const subWorkflowError = ref('');

const isPaused = computed(() => {
  return activeSteps.value.some(s => s.is_paused);
});

const activeStepName = computed(() => {
  if (activeSteps.value.length === 0) return 'Completed';
  const s = activeSteps.value[0];
  return s.step?.name || s.workflow_step?.name || 'Milestone Step';
});

const primaryAction = computed(() => {
  if (!availableActions.value || availableActions.value.length === 0) return null;
  return availableActions.value.find(a => !a.is_auto_return && a.action_type !== 'return') || availableActions.value[0];
});

const isOverdue = computed(() => {
  if (activeSteps.value.length === 0 || !activeSteps.value[0].due_date) return false;
  return new Date(activeSteps.value[0].due_date) < new Date();
});

const slaLabel = computed(() => {
  if (activeSteps.value.length === 0 || !activeSteps.value[0].due_date) return 'No Deadline';
  const due = new Date(activeSteps.value[0].due_date);
  const now = new Date();
  const diffHours = Math.round((due - now) / (1000 * 60 * 60));
  if (diffHours < 0) return `Overdue (${Math.abs(diffHours)}h ago)`;
  if (diffHours <= 24) return `Due in ${Math.max(1, diffHours)}h`;
  const diffDays = Math.round(diffHours / 24);
  return `Due in ${diffDays}d (${due.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })})`;
});

const hasUncheckedRequiredChecklist = computed(() => {
  return activeChecklists.value.some(item => item.is_required && !checklistState.value[item.id]);
});

const signChips = computed(() => {
  const type = selectedAction.value?.action_type;
  if (type === 'return' || type === 'reject') {
    return [
      'Returned for necessary date adjustment and clarification.',
      'Kindly revise application details and resubmit.',
      'Returned to desk officer for supporting documentation.'
    ];
  }
  return [
    'Respectfully submitted for your consideration and recommendation, sir.',
    'Recommended for executive approval as requested.',
    'Office operations coverage confirmed during absence.',
    'Departmental release endorsement minuted and transmitted to DA.'
  ];
});

const handleFiles = (e) => {
  const files = Array.from(e.target.files || []);
  attachments.value.push(...files);
};

const removeFile = (idx) => {
  attachments.value.splice(idx, 1);
};

const fetchTaskState = async () => {
  try {
    loadingActions.value = true;
    const res = await axios.get(`/dashboard/generic-workflow/tasks/${props.task.id}/state`);
    activeSteps.value = res.data.active_steps || [];
    availableActions.value = res.data.available_actions || [];
    workflowLogs.value = res.data.logs || [];
    subTasks.value = res.data.sub_tasks || [];
    workflowVersions.value = res.data.workflow_versions || [];
    
    // Extract checklists from active steps
    activeChecklists.value = [];
    activeSteps.value.forEach(step => {
      const stepObj = step.step || step.workflow_step;
      if (stepObj && stepObj.checklists) {
        stepObj.checklists.forEach(cl => {
          activeChecklists.value.push(cl);
          if (checklistState.value[cl.id] === undefined) {
            checklistState.value[cl.id] = false;
          }
        });
      }
    });
  } catch (error) {
    console.error("Failed to load workflow state", error);
  } finally {
    loadingActions.value = false;
  }
};

const openActionModal = (action) => {
  selectedAction.value = action;
  attachments.value = [];
  const type = action?.action_type;
  if (type === 'return' || type === 'reject') {
    actionComments.value = 'Returned for necessary date adjustment and clarification.';
  } else {
    actionComments.value = 'Respectfully submitted for your consideration and recommendation, sir.';
  }
  actionPin.value = '';
  actionError.value = '';
  showActionModal.value = true;
};

const executeAction = async () => {
  if (!selectedAction.value) return;

  if (hasUncheckedRequiredChecklist.value) {
    actionError.value = "Please complete all mandatory pre-submission checklist items before signing.";
    return;
  }

  if (selectedAction.value.requires_comment && !actionComments.value.trim()) {
    actionError.value = "Comments are required for this action.";
    return;
  }

  if (!actionPin.value) {
    actionError.value = "Please enter your digital signature PIN.";
    return;
  }

  isProcessing.value = true;
  actionError.value = '';

  try {
    // 1. Verify PIN
    const pinRes = await axios.post('/dashboard/verify-signature-pin', { pin: actionPin.value });
    if (!pinRes.data?.valid) {
      actionError.value = pinRes.data?.message || 'Invalid digital signature PIN.';
      isProcessing.value = false;
      return;
    }

    // 2. Execute transition
    const formData = new FormData();
    formData.append('transition_ids[]', selectedAction.value.id);
    formData.append('comments', actionComments.value);
    formData.append('pin', actionPin.value);
    formData.append('task_id', props.task.id);
    if (actionUserId.value) formData.append('target_user_id', actionUserId.value);
    
    // Support multiple attachments and single fallback
    if (attachments.value.length > 0) {
      attachments.value.forEach(f => formData.append('attachments[]', f));
      formData.append('attachment', attachments.value[0]);
    } else if (actionFile.value) {
      formData.append('attachment', actionFile.value);
    }

    if (Object.keys(checklistState.value).length > 0) {
      formData.append('checklists', JSON.stringify(checklistState.value));
    }

    await axios.post(`/dashboard/generic-workflow/tasks/${props.task.id}/execute`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    showActionModal.value = false;
    actionPin.value = '';
    attachments.value = [];

    await Swal.fire({
      icon: 'success',
      title: 'Action Executed',
      text: `Docket successfully forwarded to ${selectedAction.value.to_step?.name || 'Reviewing Authority'}.`,
      timer: 2500,
      showConfirmButton: false
    });

    router.reload();
  } catch (err) {
    actionError.value = err.response?.data?.error || err.response?.data?.message || "An error occurred executing the action.";
  } finally {
    isProcessing.value = false;
  }
};

const togglePause = async (pause) => {
  const action = pause ? 'pause' : 'resume';
  try {
    await axios.post(`/dashboard/generic-workflow/tasks/${props.task.id}/${action}`);
    await fetchTaskState();
    router.reload();
  } catch (err) {
    alert(err.response?.data?.error || "Failed to update pause state.");
  }
};

const startSubWorkflow = async () => {
  if (!selectedSubWorkflowId.value || !subWorkflowTitle.value.trim()) {
    subWorkflowError.value = "Title and Workflow Blueprint are required.";
    return;
  }

  isProcessing.value = true;
  subWorkflowError.value = '';

  try {
    await axios.post(`/dashboard/generic-workflow/tasks/${props.task.id}/start-sub-workflow`, {
      title: subWorkflowTitle.value,
      workflow_version_id: selectedSubWorkflowId.value
    });
    showSubWorkflowModal.value = false;
    await fetchTaskState();
    router.reload();
  } catch (err) {
    subWorkflowError.value = err.response?.data?.error || err.response?.data?.message || "Failed to start sub-workflow.";
  } finally {
    isProcessing.value = false;
  }
};

const getActionColor = (type) => {
  if (type === 'approve' || !type) return 'bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800';
  if (type === 'reject' || type === 'return') return 'bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-700 hover:to-red-800';
  if (type === 'delegate' || type === 'forward') return 'bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800';
  return 'bg-gradient-to-r from-slate-700 to-gray-800';
};

const getModalHeaderColor = (type) => {
  if (type === 'reject' || type === 'return') return 'bg-gradient-to-r from-rose-600 via-rose-700 to-red-800';
  if (type === 'approve' || !type) return 'bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-900';
  if (type === 'delegate' || type === 'forward') return 'bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800';
  return 'bg-gradient-to-r from-slate-800 to-slate-900';
};

const getActionIcon = (type) => {
  if (type === 'approve') return 'lucide:check-circle';
  if (type === 'reject') return 'lucide:x-circle';
  if (type === 'return') return 'lucide:undo-2';
  if (type === 'delegate' || type === 'forward') return 'lucide:send';
  return 'lucide:pen-tool';
};

onMounted(() => {
  fetchTaskState();
});
</script>
