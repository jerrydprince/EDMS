<template>
    <div class="h-full min-h-[80vh] flex bg-white overflow-hidden text-gray-800 border rounded-xl shadow-sm">
        
        <!-- Left Sidebar -->
        <aside class="w-64 border-r border-gray-200 flex flex-col bg-gray-50/30">
            <!-- Compose Button -->
            <div class="p-4">
                <button 
                    @click="newMessage = true"
                    class="flex items-center gap-3 bg-[#c2e7ff] hover:bg-[#b3d3e8] text-[#001d35] px-6 py-4 rounded-2xl shadow-sm transition-all font-medium w-fit">
                    <Icon icon="lucide:pencil" width="20" />
                    Compose
                </button>
            </div>
            
            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-2">
                <nav class="space-y-1 pr-3">
                    <button @click="activeButton('All')" 
                        :class="buttonToggle === 1 ? 'bg-[#d3e3fd] text-[#0b57d0] font-semibold rounded-r-full' : 'text-gray-700 hover:bg-gray-100 rounded-r-full'"
                        class="w-full flex items-center gap-4 px-6 py-2 transition-colors">
                        <Icon :icon="buttonToggle === 1 ? 'material-symbols:inbox' : 'material-symbols:inbox-outline'" width="20" />
                        Inbox
                        <!-- Optional Badge here -->
                    </button>
                    <button @click="activeButton('Starred')" 
                        :class="buttonToggle === 4 ? 'bg-[#d3e3fd] text-[#0b57d0] font-semibold rounded-r-full' : 'text-gray-700 hover:bg-gray-100 rounded-r-full'"
                        class="w-full flex items-center gap-4 px-6 py-2 transition-colors">
                        <Icon :icon="buttonToggle === 4 ? 'material-symbols:star' : 'material-symbols:star-outline'" width="20" />
                        Starred
                    </button>
                    <button @click="activeButton('Sent')" 
                        :class="buttonToggle === 6 ? 'bg-[#d3e3fd] text-[#0b57d0] font-semibold rounded-r-full' : 'text-gray-700 hover:bg-gray-100 rounded-r-full'"
                        class="w-full flex items-center gap-4 px-6 py-2 transition-colors">
                        <Icon :icon="buttonToggle === 6 ? 'material-symbols:send' : 'material-symbols:send-outline'" width="20" />
                        Sent
                    </button>
                    <button @click="activeButton('Drafts')" 
                        :class="buttonToggle === 5 ? 'bg-[#d3e3fd] text-[#0b57d0] font-semibold rounded-r-full' : 'text-gray-700 hover:bg-gray-100 rounded-r-full'"
                        class="w-full flex items-center gap-4 px-6 py-2 transition-colors">
                        <Icon :icon="buttonToggle === 5 ? 'material-symbols:drafts' : 'material-symbols:drafts-outline'" width="20" />
                        Drafts
                    </button>
                    <button @click="activeButton('Archive')" 
                        :class="buttonToggle === 2 ? 'bg-[#d3e3fd] text-[#0b57d0] font-semibold rounded-r-full' : 'text-gray-700 hover:bg-gray-100 rounded-r-full'"
                        class="w-full flex items-center gap-4 px-6 py-2 transition-colors">
                        <Icon :icon="buttonToggle === 2 ? 'material-symbols:archive' : 'material-symbols:archive-outline'" width="20" />
                        Archive
                    </button>
                </nav>
            </div>
        </aside>

        <!-- Main Mail List Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-white">
            
            <!-- Toolbar -->
            <div class="h-14 border-b border-gray-100 flex items-center justify-between px-4">
                <div class="flex items-center gap-4">
                    <!-- Checkbox -->
                    <div class="p-2 rounded-full hover:bg-gray-100 cursor-pointer text-gray-500" @click="toggleSelectAll">
                        <Icon :icon="allSelected ? 'material-symbols:check-box' : (selectedMails.length > 0 ? 'material-symbols:indeterminate-check-box' : 'material-symbols:check-box-outline-blank')" width="22" :class="{'text-primary': selectedMails.length > 0}" />
                    </div>
                    <!-- Refresh -->
                    <button @click="refreshData" class="p-2 rounded-full hover:bg-gray-100 text-gray-500" title="Refresh">
                        <Icon icon="material-symbols:refresh" width="22" />
                    </button>
                    
                    <!-- Bulk Actions (shown when items are selected) -->
                    <div v-if="selectedMails.length > 0" class="flex items-center gap-1 border-l pl-3 ml-1 border-gray-200">
                        <button @click="bulkArchive" class="p-2 rounded-full hover:bg-gray-100 text-gray-500" title="Archive Selected">
                            <Icon icon="material-symbols:archive-outline" width="22" />
                        </button>
                        <button @click="bulkDelete" class="p-2 rounded-full hover:bg-gray-100 text-gray-500" title="Delete Selected">
                            <Icon icon="material-symbols:delete-outline" width="22" />
                        </button>
                        <button @click="bulkMarkUnread" class="p-2 rounded-full hover:bg-gray-100 text-gray-500" title="Mark Unread">
                            <Icon icon="material-symbols:mark-email-unread-outline" width="22" />
                        </button>
                    </div>
                    
                    <!-- More Options (shown when NO items are selected) -->
                    <div v-else class="p-2 rounded-full hover:bg-gray-100 cursor-pointer text-gray-500">
                        <Icon icon="material-symbols:more-vert" width="22" />
                    </div>
                </div>
                
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span v-if="arrayMails.length !== 0 && mails">
                        {{ mails.from }}-{{ mails.to }} of {{ mails.total }}
                    </span>
                    <div class="flex gap-1" v-if="arrayMails.length !== 0 && mails && mails.links">
                        <Link v-if="mails.links[0].url" :href="mails.links[0].url" preserve-state preserve-scroll class="p-2 rounded-full hover:bg-gray-100 text-gray-500">
                            <Icon icon="material-symbols:chevron-left" width="22" />
                        </Link>
                        <Link v-if="mails.links[mails.links.length - 1].url" :href="mails.links[mails.links.length - 1].url" preserve-state preserve-scroll class="p-2 rounded-full hover:bg-gray-100 text-gray-500">
                            <Icon icon="material-symbols:chevron-right" width="22" />
                        </Link>
                    </div>
                    
                    <!-- Split Screen Toggle -->
                    <button @click="splitScreen = !splitScreen" class="ml-4 p-2 rounded-full hover:bg-gray-100 text-gray-500" :title="splitScreen ? 'Disable Split Screen' : 'Enable Split Screen'">
                        <Icon :icon="splitScreen ? 'material-symbols:splitscreen-right-outline' : 'material-symbols:splitscreen-right'" width="22" />
                    </button>
                </div>
            </div>

            <!-- Mail List -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="arrayMails.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500">
                    <img :src="illustration" alt="" class="w-48 mb-4 opacity-70" />
                    <p class="text-lg">Your inbox is empty</p>
                </div>
                
                <div v-else class="flex flex-col divide-y divide-gray-100">
                    <div v-for="(item, index) in (buttonToggle === 5 ? draftsmails.data : individual_mails)" :key="item.id" 
                        @click="buttonToggle === 5 ? openDraftModal(item) : openEmail(item)"
                        class="group flex items-center px-4 py-2 hover:shadow-[inset_1px_0_0_#dadce0,inset_-1px_0_0_#dadce0,0_1px_2px_0_rgba(60,64,67,.3),0_1px_3px_1px_rgba(60,64,67,.15)] hover:z-10 bg-white cursor-pointer transition-shadow"
                        :class="{'bg-[#f2f6fc] font-bold': (!item.is_read && buttonToggle === 1) }">
                        
                        <!-- Left Actions -->
                        <div class="flex items-center gap-3 w-48 shrink-0">
                            <div class="text-gray-300 hover:text-gray-600">
                                <Icon icon="material-symbols:drag-indicator" width="20" class="opacity-0 group-hover:opacity-100 cursor-grab" />
                            </div>
                            <div class="text-gray-400 cursor-pointer" @click.stop="toggleSelection(item.id)">
                                <Icon :icon="selectedMails.includes(item.id) ? 'material-symbols:check-box' : 'material-symbols:check-box-outline-blank'" width="20" :class="{'text-primary': selectedMails.includes(item.id)}" />
                            </div>
                            <button @click.stop="addToFavorite(item.id)" class="text-gray-400">
                                <Icon :icon="item.is_starred ? 'material-symbols:star' : 'material-symbols:star-outline'" width="20" :class="item.is_starred ? 'text-yellow-400' : 'hover:text-gray-600'" />
                            </button>
                            <span class="truncate pr-4" :class="{'text-red-500 font-medium': buttonToggle===5}">
                                {{ buttonToggle === 5 ? 'Draft' : (buttonToggle === 6 ? item.recipient : item.sender) }}
                            </span>
                        </div>
                        
                        <!-- Middle Snippet -->
                        <div class="flex-1 truncate pr-4 text-sm flex items-center gap-2">
                            <span>{{ item.subject || '(no subject)' }}</span>
                            <span class="text-gray-500 font-normal truncate">- {{ item.body_snippet || 'Click to view email contents...' }}</span>
                            
                            <div v-if="item.Attached" class="flex items-center gap-1 border border-gray-200 rounded-full px-2 py-0.5 ml-2 shrink-0">
                                <Icon :icon="item.icon || 'material-symbols:attach-file'" width="14" class="text-gray-500" />
                            </div>
                        </div>
                        
                        <!-- Right Info -->
                        <div class="shrink-0 text-xs flex items-center gap-4 justify-end min-w-[120px]">
                            <!-- Quick Actions (Hidden by default, shown on hover) -->
                            <div class="hidden group-hover:flex items-center gap-2 text-gray-500 pr-2 bg-white z-20">
                                <button @click.stop="archiveMail(item.id)" class="p-2 hover:bg-gray-100 rounded-full" title="Archive">
                                    <Icon icon="material-symbols:archive-outline" width="20" />
                                </button>
                                <button @click.stop="sendId(item.id); newdelete = true" class="p-2 hover:bg-gray-100 rounded-full" title="Delete">
                                    <Icon icon="material-symbols:delete-outline" width="20" />
                                </button>
                                <button @click.stop="markUnread(item.id)" class="p-2 hover:bg-gray-100 rounded-full" title="Mark as unread">
                                    <Icon icon="material-symbols:mark-email-unread-outline" width="20" />
                                </button>
                            </div>
                            <!-- Date (Hidden on hover) -->
                            <span class="group-hover:hidden font-medium" :class="(!item.is_read && buttonToggle === 1) ? 'text-gray-900' : 'text-gray-500'">
                                {{ formatMailDate(item.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Reading Pane (if splitScreen is enabled and mail is selected) -->
        <aside v-if="splitScreen && selectedEmailId" class="w-1/2 border-l border-gray-200 bg-white overflow-y-auto flex flex-col shadow-lg z-10 transition-all">
            <!-- MailPreview handles its own scrolling and header -->
            <MailPreview :id="selectedEmailId" :key="selectedEmailId" @close="selectedEmailId = null" />
        </aside>
        
    </div>

    <!-- Modals -->
    <NewMessageModal v-model="newMessage" :data="newSelectedValue" @refresh="refreshData" />
    <DeleteMailMessage v-model="newdelete" :data="toModal" @refresh="refreshData" />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import NewMessageModal from "../../components/Modals/Mail/NewMessage.vue";
import DeleteMailMessage from "../../components/Modals/Mail/DeleteMailMessage.vue";
import MailPreview from "../../components/MailPreview.vue";
import { Link, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import illustration from "../../assets/images/Illustration.svg";
import axios from 'axios';
import { Icon } from '@iconify/vue';

const toast = useToast();
const props = defineProps(["mails", "buttonToggle"]);

const newMessage = ref(false);
const newdelete = ref(false);
const splitScreen = ref(true); // Default to on for a robust webmail feel
const selectedEmailId = ref(null);
const selectedMails = ref([]);
const buttonToggle = computed(() => props.buttonToggle || 1);
const draftsmails = ref({ data: [] });

const selectedItem = ref({});
const newSelectedValue = computed(() => selectedItem.value);
const toModal = ref("");

const individual_mails = computed(() => props.mails?.data || []);
const arrayMails = computed(() => {
    return buttonToggle.value === 5 ? (draftsmails.value?.data || []) : individual_mails.value;
});

const allSelected = computed(() => {
    if (arrayMails.value.length === 0) return false;
    return selectedMails.value.length === arrayMails.value.length;
});

const toggleSelection = (id) => {
    const index = selectedMails.value.indexOf(id);
    if (index === -1) {
        selectedMails.value.push(id);
    } else {
        selectedMails.value.splice(index, 1);
    }
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedMails.value = [];
    } else {
        selectedMails.value = arrayMails.value.map(mail => mail.id);
    }
};

const bulkArchive = () => {
    selectedMails.value.forEach(id => archiveMail(id));
    selectedMails.value = [];
};
const bulkDelete = () => {
    // For bulk delete, we'd need a bulk API, but for now we'll just open the modal for the first one or alert
    if (selectedMails.value.length > 0) {
        sendId(selectedMails.value[0]);
        newdelete.value = true;
    }
};
const bulkMarkUnread = () => {
    selectedMails.value.forEach(id => markUnread(id));
    selectedMails.value = [];
};

const refreshData = () => {
    router.reload({ only: ["mails"] });
};

const fetchdraftsMails = async () => {
    try {
        const response = await axios.get("/dashboard/mails/drafts");
        draftsmails.value = response.data;
    } catch (error) {
        console.error("Error fetching drafts:", error);
    }
};

const openDraftModal = (item) => {
    newMessage.value = true;
    selectedItem.value = { ...item };
};

const openEmail = (item) => {
    if (!splitScreen.value) {
        router.visit(`/dashboard/mails/ReadMail/${item.id}`);
        return;
    }
    selectedEmailId.value = item.id;
    // Mark as read immediately on frontend for visual feedback
    if(!item.is_read && buttonToggle.value === 1) {
        item.is_read = 1;
    }
};

const formatMailDate = (dateString) => {
    if(!dateString) return '';
    const date = new Date(dateString);
    const today = new Date();
    if(date.toDateString() === today.toDateString()) {
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    return date.toLocaleDateString([], {month: 'short', day: 'numeric'});
};

onMounted(() => {
    fetchdraftsMails();
});

const addToFavorite = (itemId) => {
    router.post("/dashboard/mails/star/" + itemId, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: "success", summary: "Updated", detail: "Star status updated.", life: 3000 });
        }
    });
};

const markUnread = (itemId) => {
    router.post("/dashboard/mails/unread/" + itemId, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: "success", summary: "Marked unread", detail: "Mail marked as unread.", life: 3000 });
            selectedEmailId.value = null;
        }
    });
};

const archiveMail = (itemId) => {
    router.post("/dashboard/mails/archive/" + itemId, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: "success", summary: "Archived", detail: "Mail has been archived.", life: 3000 });
            if(selectedEmailId.value === itemId) selectedEmailId.value = null;
        }
    });
};

const sendId = (id) => {
    toModal.value = id;
};

const activeButton = (event) => {
    selectedEmailId.value = null; // reset selection when changing tabs
    if (event === "All") router.get('/dashboard/mails');
    if (event === "Archive") router.get('/dashboard/mails', { is_archive: 'true' });
    if (event === "Unread") router.get('/dashboard/mails', { is_read: 'false' });
    if (event === "Starred") router.get('/dashboard/mails', { is_starred: 'true' });
    if (event === "Drafts") router.get('/dashboard/mails', { is_draft: 'true' });
    if (event === "Sent") router.get('/dashboard/mails', { is_sent: 'true' });
};
</script>
