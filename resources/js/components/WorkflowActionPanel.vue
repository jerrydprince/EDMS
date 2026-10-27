<template>
  <div v-if="workflowRoute" class="bg-white border rounded-xl p-5 mb-5 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-bold text-gray-700 text-lg flex items-center gap-2">
          <Icon icon="mdi:source-branch" class="text-blue-500" />
          Workflow Status
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          Current Level: <span class="font-semibold uppercase text-blue-600">{{ workflowRoute.current_level }}</span> 
          | Direction: <span class="font-semibold text-gray-600">{{ formatDirection(workflowRoute.direction) }}</span>
        </p>
      </div>
      
      <!-- Actions based on role and state -->
      <div class="flex gap-3">
        <!-- Downward Delegation -->
        <button 
          v-if="canDelegate"
          @click="showDelegateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md px-4 py-2 transition-colors flex items-center gap-2"
        >
          <Icon icon="mdi:account-arrow-right" />
          Delegate Down
        </button>

        <!-- Upward Submission (For Staff) -->
        <button 
          v-if="canSubmit"
          @click="showSubmitModal = true"
          class="bg-green-600 hover:bg-green-700 text-white font-medium rounded-md px-4 py-2 transition-colors flex items-center gap-2"
        >
          <Icon icon="mdi:check-circle-outline" />
          Submit for Approval
        </button>

        <!-- Upward Approval (For Supervisors) -->
        <button 
          v-if="canApprove"
          @click="showApproveModal = true"
          class="bg-green-600 hover:bg-green-700 text-white font-medium rounded-md px-4 py-2 transition-colors flex items-center gap-2"
        >
          <Icon icon="mdi:check-all" />
          Approve & Forward Up
        </button>

        <!-- Return / Reject (For Supervisors) -->
        <button 
          v-if="canReturn"
          @click="showReturnModal = true"
          class="bg-red-500 hover:bg-red-600 text-white font-medium rounded-md px-4 py-2 transition-colors flex items-center gap-2"
        >
          <Icon icon="mdi:arrow-u-left-bottom" />
          Return / Reject
        </button>
      </div>
    </div>

    <!-- Workflow Audit Trail -->
    <div class="mt-6 border-t pt-4" v-if="workflowLogs && workflowLogs.length > 0">
      <h4 class="font-semibold text-gray-600 mb-3 text-sm">Audit Trail</h4>
      <div class="space-y-3">
        <div v-for="log in workflowLogs" :key="log.id" class="flex gap-3 text-sm">
          <div class="mt-1">
            <Icon 
              :icon="getLogIcon(log.action_type)" 
              :class="getLogColor(log.action_type)" 
              width="18"
            />
          </div>
          <div>
            <p class="text-gray-700">
              <span class="font-semibold">{{ log.action_by?.first_name }} {{ log.action_by?.last_name }}</span>
              {{ formatActionText(log.action_type) }}
              <span class="font-semibold" v-if="log.action_to">{{ log.action_to?.first_name }} {{ log.action_to?.last_name }}</span>
            </p>
            <p class="text-gray-500 text-xs mt-0.5">{{ new Date(log.created_at).toLocaleString() }}</p>
            <p v-if="log.comments" class="text-gray-600 mt-1 italic border-l-2 border-gray-200 pl-2">"{{ log.comments }}"</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Delegate Modal -->
    <div v-if="showDelegateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-bold mb-4">Delegate Task</h3>
        <p class="text-sm text-gray-500 mb-4">Select the users to delegate this task to. They must be in the direct level below you.</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Select Users (IDs comma separated for now)</label>
          <input v-model="delegateUserIds" type="text" class="w-full border rounded-md p-2" placeholder="e.g. 2, 4, 5">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Comments/Instructions</label>
          <textarea v-model="comments" class="w-full border rounded-md p-2 h-24" placeholder="Optional comments..."></textarea>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="showDelegateModal = false" class="px-4 py-2 border rounded-md hover:bg-gray-50">Cancel</button>
          <button @click="submitAction('delegate')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" :disabled="isProcessing">
            {{ isProcessing ? 'Processing...' : 'Delegate' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Action Modal (Submit/Approve/Return) -->
    <div v-if="showSubmitModal || showApproveModal || showReturnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-bold mb-4">
          {{ showSubmitModal ? 'Submit for Approval' : showApproveModal ? 'Approve & Forward' : 'Return / Reject' }}
        </h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
          <textarea v-model="comments" class="w-full border rounded-md p-2 h-24" placeholder="Add comments..."></textarea>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="closeActionModals" class="px-4 py-2 border rounded-md hover:bg-gray-50">Cancel</button>
          <button @click="submitAction(getActionType())" class="px-4 py-2 text-white rounded-md" :class="showReturnModal ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'" :disabled="isProcessing">
            {{ isProcessing ? 'Processing...' : 'Confirm' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Icon } from '@iconify/vue';

const props = defineProps({
  task: Object,
  workflowRoute: Object,
  workflowLogs: Array,
  currentUser: Object,
});

const page = usePage();

const showDelegateModal = ref(false);
const showSubmitModal = ref(false);
const showApproveModal = ref(false);
const showReturnModal = ref(false);
const comments = ref('');
const delegateUserIds = ref('');
const isProcessing = ref(false);

const getUserLevel = (user) => {
  if (user.is_super_admin) return 'head';
  if (user.is_department_head) return 'department';
  if (user.is_division_head) return 'division';
  if (user.is_unit_head) return 'unit';
  return 'staff';
};

const userLevel = computed(() => getUserLevel(props.currentUser));

const canDelegate = computed(() => {
  if (!props.workflowRoute) return false;
  return props.workflowRoute.direction === 'downward_delegation' && 
         props.workflowRoute.current_level === userLevel.value &&
         userLevel.value !== 'staff';
});

const canSubmit = computed(() => {
  if (!props.workflowRoute) return false;
  return props.workflowRoute.current_level === 'staff' && 
         userLevel.value === 'staff' &&
         props.task.status !== 'completed';
});

const canApprove = computed(() => {
  if (!props.workflowRoute) return false;
  return props.workflowRoute.direction === 'upward_approval' && 
         props.workflowRoute.current_level === userLevel.value &&
         props.workflowRoute.status !== 'approved';
});

const canReturn = computed(() => {
  if (!props.workflowRoute) return false;
  return props.workflowRoute.direction === 'upward_approval' && 
         props.workflowRoute.current_level === userLevel.value &&
         userLevel.value !== 'head';
});

const formatDirection = (dir) => {
  return dir === 'downward_delegation' ? 'Delegating Downwards' : 'Approving Upwards';
};

const formatActionText = (action) => {
  const map = {
    'delegated': ' delegated the task to ',
    'submitted': ' submitted the task for approval to ',
    'approved': ' approved and forwarded to ',
    'returned': ' returned the task for corrections to '
  };
  return map[action] || ` ${action} `;
};

const getLogIcon = (action) => {
  const map = {
    'delegated': 'mdi:account-arrow-right',
    'submitted': 'mdi:check-circle-outline',
    'approved': 'mdi:check-all',
    'returned': 'mdi:arrow-u-left-bottom'
  };
  return map[action] || 'mdi:circle-small';
};

const getLogColor = (action) => {
  const map = {
    'delegated': 'text-blue-500',
    'submitted': 'text-green-500',
    'approved': 'text-green-600',
    'returned': 'text-red-500'
  };
  return map[action] || 'text-gray-400';
};

const getActionType = () => {
  if (showSubmitModal.value) return 'submit';
  if (showApproveModal.value) return 'approve';
  if (showReturnModal.value) return 'return';
  return '';
};

const closeActionModals = () => {
  showDelegateModal.value = false;
  showSubmitModal.value = false;
  showApproveModal.value = false;
  showReturnModal.value = false;
  comments.value = '';
  delegateUserIds.value = '';
};

const submitAction = (action) => {
  isProcessing.value = true;
  const payload = { comments: comments.value };
  if (action === 'delegate') {
    payload.assigned_to = delegateUserIds.value.split(',').map(id => id.trim()).filter(id => id);
  }
  
  router.post(`/dashboard/workflow/${action}/${props.task.id}`, payload, {
    preserveScroll: true,
    onSuccess: () => {
      closeActionModals();
      isProcessing.value = false;
    },
    onError: (errors) => {
      alert(errors.error || 'An error occurred while processing the workflow action.');
      isProcessing.value = false;
    },
    onFinish: () => {
      isProcessing.value = false;
    }
  });
};
</script>
