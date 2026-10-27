<template>
    <Dialog v-model:visible="visible" header="Forward Message" :style="{ width: '50vw' }" modal>
        <div class="p-4">
            <div class="mb-4">
                <span class="p-input-icon-left w-full">
                    <i class="pi pi-search" />
                    <InputText v-model="searchQuery" placeholder="Search users..." class="w-full" />
                </span>
            </div>

            <div class="h-64 overflow-y-auto">
                <div v-for="user in filteredUsers" :key="user.id" class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer rounded-lg border-b border-gray-100" @click="toggleUserSelection(user.id)">
                    <Checkbox v-model="selectedUsers" :value="user.id" @click.stop />
                    <img v-if="user.profile_image" :src="'/' + user.profile_image" class="w-10 h-10 rounded-full object-cover" />
                    <div v-else class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">
                        {{ user.first_name.charAt(0) }}
                    </div>
                    <div>
                        <p class="font-medium text-sm text-gray-800">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end gap-2">
                <Button label="Cancel" class="p-button-text p-button-secondary" @click="visible = false" />
                <Button label="Forward" class="bg-primary border-none text-white px-4 py-2 rounded-md" :disabled="selectedUsers.length === 0 || loading" @click="forwardMessage">
                    <span v-if="loading" class="pi pi-spin pi-spinner mr-2"></span> Forward
                </Button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    message: {
        type: Object,
        default: null
    },
    isGroup: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'forwarded']);

const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const toast = useToast();
const users = ref([]);
const searchQuery = ref('');
const selectedUsers = ref([]);
const loading = ref(false);

const fetchUsers = async () => {
    try {
        const token = localStorage.getItem("token");
        const response = await axios.get('/dashboard/chat/users', {
            headers: { Authorization: `Bearer ${token}` }
        });
        users.value = response.data;
    } catch (error) {
        console.error("Error fetching users for forward", error);
    }
};

onMounted(() => {
    fetchUsers();
});

watch(visible, (newVal) => {
    if (newVal) {
        selectedUsers.value = [];
        searchQuery.value = '';
    }
});

const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    const q = searchQuery.value.toLowerCase();
    return users.value.filter(u => 
        (u.first_name && u.first_name.toLowerCase().includes(q)) || 
        (u.last_name && u.last_name.toLowerCase().includes(q))
    );
});

const toggleUserSelection = (id) => {
    const index = selectedUsers.value.indexOf(id);
    if (index === -1) {
        selectedUsers.value.push(id);
    } else {
        selectedUsers.value.splice(index, 1);
    }
};

const forwardMessage = async () => {
    if (selectedUsers.value.length === 0 || !props.message) return;
    
    loading.value = true;
    try {
        const token = localStorage.getItem("token");
        await axios.post('/dashboard/chat/messages/forward', {
            message_id: props.message.id,
            is_group: props.isGroup,
            forward_to_users: selectedUsers.value,
            forward_to_groups: []
        }, {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        toast.add({ severity: 'success', summary: 'Success', detail: 'Message forwarded successfully', life: 3000 });
        emit('forwarded');
        visible.value = false;
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to forward message', life: 3000 });
    } finally {
        loading.value = false;
    }
};
</script>
