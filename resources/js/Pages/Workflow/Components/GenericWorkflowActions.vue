<template>
    <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end" v-if="task && task.workflow_version_id">
        <div v-if="loading" class="text-sm text-gray-500 italic">
            Checking available actions...
        </div>
        <template v-else-if="actions.length > 0">
            <template v-if="!allowStepSplitting">
                <template v-for="action in actions" :key="action.id">
                    <button 
                        class="font-semibold px-3 py-2 border rounded-md transition-all shadow-sm"
                        :class="getButtonClass(action.action_name)"
                        @click="openActionModal([action])"
                    >
                        {{ action.action_name }}
                    </button>
                </template>
            </template>
            
            <template v-else>
                <div class="flex flex-col gap-3 mr-4">
                    <p class="text-xs text-gray-500 mb-1">Select multiple actions (Parallel Split)</p>
                    <div class="flex items-center gap-3" v-for="action in actions" :key="action.id">
                        <input type="checkbox" :id="'action_' + action.id" :value="action" v-model="selectedActions" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                        <label :for="'action_' + action.id" class="text-sm font-medium text-gray-700 cursor-pointer">{{ action.action_name }} <span class="text-xs font-normal text-gray-500 ml-1">({{ action.to_step ? action.to_step.name : 'Unknown Step' }})</span></label>
                    </div>
                </div>
                
                <button 
                    class="font-semibold px-4 py-2 border rounded-md transition-all shadow-sm text-white bg-blue-600 hover:bg-blue-700 border-blue-600 disabled:opacity-50"
                    :disabled="selectedActions.length === 0"
                    @click="openActionModal(selectedActions)"
                >
                    Proceed with Selected
                </button>
            </template>
            
            <GenericActionModal 
                v-model="showModal"
                :task="task"
                :transitions="selectedTransitionsForModal"
                :users="users"
                @refresh="onRefresh"
            />
        </template>
        <div v-else-if="task.status !== 'completed'" class="flex items-center gap-2 p-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
            <Icon icon="mdi:information-outline" width="20" />
            <p class="text-sm font-medium">No actions available. This task may be pending parallel approvals or conditions.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import axios from 'axios';
import GenericActionModal from '@/components/Modals/Workflows/GenericActionModal.vue';

const props = defineProps({
    task: Object,
    users: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['refresh']);

const actions = ref([]);
const loading = ref(false);
const allowStepSplitting = ref(false);
const selectedActions = ref([]);

const showModal = ref(false);
const selectedTransitionsForModal = ref([]);

onMounted(() => {
    fetchActions();
});

const fetchActions = () => {
    if (!props.task || !props.task.workflow_version_id) return;
    
    loading.value = true;
    axios.get(`/dashboard/generic-workflow/instances/${props.task.id}/actions`)
        .then(response => {
            actions.value = response.data.actions || [];
            allowStepSplitting.value = response.data.allow_step_splitting || false;
        })
        .catch(err => {
            console.error('Error fetching generic workflow actions', err);
        })
        .finally(() => {
            loading.value = false;
        });
};

const openActionModal = (actionOrActions) => {
    selectedTransitionsForModal.value = actionOrActions;
    showModal.value = true;
};

const onRefresh = () => {
    emit('refresh');
};

const getButtonClass = (actionName) => {
    const name = actionName.toLowerCase();
    if (name.includes('approve') || name.includes('accept') || name.includes('complete')) {
        return 'text-white bg-green-600 hover:bg-green-700 border-green-600';
    }
    if (name.includes('reject') || name.includes('return') || name.includes('decline')) {
        return 'text-red-600 border-red-600 hover:bg-red-50';
    }
    return 'text-gray-800 border-gray-300 hover:bg-gray-50';
};
</script>
