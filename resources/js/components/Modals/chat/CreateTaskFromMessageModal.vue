<template>
    <Dialog
        v-model:visible="visible"
        header="Create Task from Message"
        :style="{ width: '92vw', maxWidth: '580px' }"
        modal
        :closable="true"
        :dismissableMask="true"
        class="p-dialog-custom"
    >
        <form @submit.prevent="submitTask" class="space-y-4 pt-1">
            <!-- Source Notice Banner -->
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900">
                <Icon icon="solar:info-circle-bold-duotone" class="text-lg text-primary flex-shrink-0 mt-0.5" />
                <div>
                    <span class="font-bold">Converting chat message to workflow task.</span>
                    <p class="text-blue-700 mt-0.5">This task will be logged in the EDMS Workflow system and assigned to the selected colleagues.</p>
                </div>
            </div>

            <!-- Task Title -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Task Title <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.title"
                    type="text"
                    required
                    placeholder="Brief title for this task..."
                    class="w-full px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white border border-gray-200 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                />
            </div>

            <!-- Task Description -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    required
                    placeholder="Detailed task instructions..."
                    class="w-full px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white border border-gray-200 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-none"
                ></textarea>
            </div>

            <!-- Priority & Due Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Priority
                    </label>
                    <select
                        v-model="form.priority"
                        class="w-full px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white border border-gray-200 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Due Date
                    </label>
                    <input
                        v-model="form.due_date"
                        type="date"
                        class="w-full px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white border border-gray-200 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                    />
                </div>
            </div>

            <!-- Assignee Selection -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Assign To Colleague(s)
                </label>
                <div class="relative mb-2">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <Icon icon="lucide:search" class="text-xs" />
                    </span>
                    <input
                        v-model="userSearch"
                        type="text"
                        placeholder="Search users to assign..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 focus:border-primary rounded-lg outline-none"
                    />
                </div>

                <div class="max-h-36 overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded-xl bg-gray-50/40 p-1">
                    <div
                        v-for="u in filteredUsers"
                        :key="u.id"
                        @click="toggleUser(u.id)"
                        class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-white transition-colors"
                        :class="form.users.includes(u.id) ? 'bg-blue-50/80 font-medium' : ''"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0"
                            >
                                {{ (u.first_name || 'U').charAt(0) }}
                            </div>
                            <span class="text-xs text-gray-800 truncate">
                                {{ u.first_name }} {{ u.last_name }}
                            </span>
                        </div>
                        <div
                            class="w-4 h-4 rounded flex items-center justify-center flex-shrink-0"
                            :class="form.users.includes(u.id) ? 'bg-primary text-white' : 'border border-gray-300'"
                        >
                            <Icon v-if="form.users.includes(u.id)" icon="lucide:check" class="text-[10px]" />
                        </div>
                    </div>
                </div>
                <p v-if="form.users.length > 0" class="text-[11px] text-primary font-medium mt-1">
                    {{ form.users.length }} assignee{{ form.users.length > 1 ? 's' : '' }} selected
                </p>
            </div>

            <!-- Linked Message Attachments Warning -->
            <div v-if="message?.attachments && message.attachments.length > 0" class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-800">
                <Icon icon="solar:paperclip-2-bold-duotone" class="text-base text-emerald-600 flex-shrink-0" />
                <span>
                    <strong>{{ message.attachments.length }} attachment{{ message.attachments.length > 1 ? 's' : '' }}</strong> from this message will be automatically linked to the task.
                </span>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <Button
                    type="button"
                    label="Cancel"
                    class="p-button-text p-button-secondary text-xs"
                    @click="visible = false"
                />
                <Button
                    type="submit"
                    class="bg-primary hover:bg-primary/90 border-none text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-xs flex items-center gap-2"
                    :disabled="loading || !form.title.trim()"
                >
                    <Icon v-if="loading" icon="eos-icons:loading" class="animate-spin text-sm" />
                    <Icon v-else icon="solar:check-circle-bold" class="text-sm" />
                    <span>Create Workflow Task</span>
                </Button>
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    message: {
        type: Object,
        default: null,
    },
    isGroup: {
        type: Boolean,
        default: false,
    },
    selectedUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'task-created']);

const toast = useToast();
const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const loading = ref(false);
const usersList = ref([]);
const userSearch = ref('');

const form = ref({
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
    users: [],
});

const fetchUsers = async () => {
    try {
        const token = localStorage.getItem('token');
        const res = await axios.get('/dashboard/chat/users', {
            headers: token ? { Authorization: `Bearer ${token}` } : {},
        });
        usersList.value = res.data || [];
    } catch (err) {
        console.error('Error fetching users for task assignment', err);
    }
};

const filteredUsers = computed(() => {
    if (!userSearch.value.trim()) return usersList.value;
    const q = userSearch.value.toLowerCase();
    return usersList.value.filter(
        (u) =>
            (u.first_name && u.first_name.toLowerCase().includes(q)) ||
            (u.last_name && u.last_name.toLowerCase().includes(q))
    );
});

const toggleUser = (userId) => {
    const idx = form.value.users.indexOf(userId);
    if (idx > -1) {
        form.value.users.splice(idx, 1);
    } else {
        form.value.users.push(userId);
    }
};

const populateFromMessage = () => {
    if (!props.message) return;

    const rawText = props.message.message || '';
    const cleanSnippet = rawText.replace(/\n+/g, ' ').trim();
    const titleSnippet = cleanSnippet.length > 50 ? cleanSnippet.substring(0, 47) + '...' : cleanSnippet;

    form.value.title = titleSnippet ? `Task: ${titleSnippet}` : 'Task from Chat Message';
    form.value.description = rawText || 'Follow up on discussion from chat.';
    form.value.priority = 'medium';

    // Default due date to 3 days in future
    const d = new Date();
    d.setDate(d.getDate() + 3);
    form.value.due_date = d.toISOString().split('T')[0];

    // Auto-select chat partner if 1-on-1
    form.value.users = [];
    if (!props.isGroup && props.selectedUser?.id) {
        form.value.users = [props.selectedUser.id];
    } else if (props.message.sender_id) {
        form.value.users = [props.message.sender_id];
    }
};

const submitTask = async () => {
    if (!form.value.title.trim()) return;

    loading.value = true;
    try {
        const token = localStorage.getItem('token');
        const res = await axios.post(
            '/dashboard/chat/create-task',
            {
                title: form.value.title,
                description: form.value.description,
                priority: form.value.priority,
                due_date: form.value.due_date,
                users: form.value.users,
                message_id: props.message?.id || null,
                is_group: props.isGroup,
            },
            {
                headers: token ? { Authorization: `Bearer ${token}` } : {},
            }
        );

        toast.add({
            severity: 'success',
            summary: 'Task Created',
            detail: `Task #${res.data.task.id} created successfully from chat message!`,
            life: 5000,
        });

        emit('task-created', res.data.task);
        visible.value = false;
    } catch (err) {
        console.error('Error creating task from message', err);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: err.response?.data?.message || 'Failed to create task.',
            life: 5000,
        });
    } finally {
        loading.value = false;
    }
};

watch(visible, (newVal) => {
    if (newVal) {
        populateFromMessage();
        if (usersList.value.length === 0) {
            fetchUsers();
        }
    }
});

onMounted(() => {
    fetchUsers();
});
</script>
