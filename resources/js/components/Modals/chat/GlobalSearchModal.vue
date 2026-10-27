<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Global Chat Search"
        :style="{ width: '680px', maxWidth: '95vw' }"
        :closable="true"
        class="p-fluid rounded-2xl overflow-hidden"
    >
        <div class="space-y-4">
            <!-- Search Input Box -->
            <div class="relative flex items-center">
                <Icon icon="mdi:magnify" class="absolute left-3.5 text-gray-400 text-xl pointer-events-none" />
                <input
                    ref="searchInputRef"
                    v-model="searchTerm"
                    @input="handleInput"
                    type="text"
                    placeholder="Search across all messages, groups & attachments..."
                    class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder-gray-400"
                />
                <button
                    v-if="searchTerm"
                    @click="clearSearch"
                    type="button"
                    class="absolute right-3 p-1 text-gray-400 hover:text-gray-600 rounded-full"
                >
                    <Icon icon="lucide:x" class="text-sm" />
                </button>
            </div>

            <!-- Scope Filters and Date Filters -->
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                <!-- Scope Pills -->
                <div class="flex items-center gap-1.5 p-1 bg-gray-100/70 rounded-xl">
                    <button
                        v-for="s in scopes"
                        :key="s.value"
                        type="button"
                        @click="setScope(s.value)"
                        class="px-2.5 py-1 rounded-lg font-medium transition-all"
                        :class="selectedScope === s.value ? 'bg-white text-primary shadow-2xs font-semibold' : 'text-gray-600 hover:text-gray-800'"
                    >
                        {{ s.label }}
                    </button>
                </div>

                <!-- Date Range Select -->
                <div class="flex items-center gap-1 text-gray-500">
                    <Icon icon="lucide:calendar" class="text-xs text-gray-400" />
                    <select
                        v-model="selectedDateRange"
                        @change="triggerSearch"
                        class="bg-transparent border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-700 outline-none focus:border-primary cursor-pointer"
                    >
                        <option value="all">All Time</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                    </select>
                </div>
            </div>

            <!-- Results Counter & Status -->
            <div v-if="!loading && searched" class="flex items-center justify-between px-1 text-xs text-gray-500 border-b border-gray-100 pb-2">
                <span>Found {{ results.length }} {{ results.length === 1 ? 'match' : 'matches' }}</span>
                <span class="text-gray-400 italic">Click result to open message</span>
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-12 text-gray-400 gap-2">
                <Icon icon="eos-icons:loading" class="text-3xl text-primary animate-spin" />
                <span class="text-xs">Searching message archives...</span>
            </div>

            <!-- Empty State (No Query) -->
            <div v-else-if="!searched" class="flex flex-col items-center justify-center py-12 text-center text-gray-400 space-y-2">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-primary flex items-center justify-center mb-1">
                    <Icon icon="lucide:message-square-search" class="text-2xl" />
                </div>
                <h4 class="text-sm font-semibold text-gray-700">Search Entire EDMS Chat History</h4>
                <p class="text-xs text-gray-400 max-w-sm">Type keywords, document numbers, or filenames to find messages across all direct and group conversations.</p>
            </div>

            <!-- Empty State (No Matches) -->
            <div v-else-if="results.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-gray-400 space-y-2">
                <Icon icon="lucide:search-x" class="text-3xl text-gray-300" />
                <h4 class="text-sm font-medium text-gray-600">No matching messages found</h4>
                <p class="text-xs text-gray-400">Try adjusting your keyword or date filter.</p>
            </div>

            <!-- Results List -->
            <div v-else class="max-h-[50vh] overflow-y-auto space-y-2 pr-1 hide_scroll">
                <div
                    v-for="item in results"
                    :key="item.type + '-' + item.id"
                    @click="onSelectResult(item)"
                    class="p-3 rounded-xl border border-gray-100 hover:border-primary/40 hover:bg-blue-50/40 cursor-pointer transition-all group shadow-2xs bg-white"
                >
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2">
                            <!-- Type Badge -->
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase"
                                :class="item.type === 'direct' ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700'"
                            >
                                <Icon :icon="item.type === 'direct' ? 'lucide:user' : 'lucide:users'" class="text-[11px]" />
                                {{ item.type === 'direct' ? 'Direct' : item.group_type }}
                            </span>

                            <span class="font-semibold text-xs text-gray-800">
                                {{ item.type === 'direct' ? (item.target_user?.name || item.sender?.name) : item.group_name }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1 text-[11px] text-gray-400 whitespace-nowrap">
                            <span v-if="item.is_edited" class="italic text-[9px] text-gray-400">(edited)</span>
                            <span>{{ item.formatted_date }}</span>
                            <Icon icon="lucide:arrow-right" class="text-xs text-primary opacity-0 group-hover:opacity-100 transition-opacity ml-1" />
                        </div>
                    </div>

                    <!-- Sender name for group messages -->
                    <div v-if="item.type === 'group'" class="text-[11px] text-gray-500 font-medium mb-1">
                        {{ item.sender?.name }}:
                    </div>

                    <!-- Highlighted Message Snippet -->
                    <p class="text-xs text-gray-700 leading-relaxed break-words" v-html="highlightKeyword(item.message)"></p>

                    <!-- Attachments Badge if any -->
                    <div v-if="item.attachments && item.attachments.length > 0" class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span
                            v-for="(att, i) in item.attachments"
                            :key="i"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] border border-gray-200/60"
                        >
                            <Icon icon="lucide:paperclip" class="text-[10px] text-primary" />
                            <span class="truncate max-w-[150px]">{{ att.name }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import Dialog from 'primevue/dialog';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible', 'select-result']);

const searchInputRef = ref(null);
const searchTerm = ref('');
const selectedScope = ref('all');
const selectedDateRange = ref('all');
const loading = ref(false);
const searched = ref(false);
const results = ref([]);
let debounceTimeout = null;

const scopes = [
    { label: 'All', value: 'all' },
    { label: 'Direct', value: 'direct' },
    { label: 'Groups', value: 'group' },
    { label: 'Attachments', value: 'attachments' },
];

watch(() => props.visible, (val) => {
    if (val) {
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
});

const setScope = (scope) => {
    selectedScope.value = scope;
    if (searchTerm.value.trim().length >= 2) {
        triggerSearch();
    }
};

const handleInput = () => {
    clearTimeout(debounceTimeout);
    if (searchTerm.value.trim().length < 2) {
        results.value = [];
        searched.value = false;
        return;
    }
    debounceTimeout = setTimeout(() => {
        triggerSearch();
    }, 350);
};

const clearSearch = () => {
    searchTerm.value = '';
    results.value = [];
    searched.value = false;
    searchInputRef.value?.focus();
};

const triggerSearch = async () => {
    if (searchTerm.value.trim().length < 2) return;
    loading.value = true;
    const token = localStorage.getItem("token");

    // Compute dates if needed
    let dateFrom = null;
    let dateTo = null;
    const now = new Date();
    if (selectedDateRange.value === '7days') {
        const d = new Date();
        d.setDate(d.getDate() - 7);
        dateFrom = d.toISOString().split('T')[0];
    } else if (selectedDateRange.value === '30days') {
        const d = new Date();
        d.setDate(d.getDate() - 30);
        dateFrom = d.toISOString().split('T')[0];
    }

    try {
        const params = new URLSearchParams({
            q: searchTerm.value.trim(),
            scope: selectedScope.value,
        });
        if (dateFrom) params.append('date_from', dateFrom);

        const res = await axios.get(`/dashboard/chat/global-search?${params.toString()}`, {
            headers: { Authorization: `Bearer ${token}` }
        });

        results.value = res.data.results || [];
        searched.value = true;
    } catch (error) {
        console.error('Global search error:', error);
        results.value = [];
        searched.value = true;
    } finally {
        loading.value = false;
    }
};

const highlightKeyword = (text) => {
    if (!text) return '';
    const query = searchTerm.value.trim();
    if (!query) return text;
    const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<mark class="bg-amber-200 text-gray-900 rounded px-0.5 font-medium">$1</mark>');
};

const onSelectResult = (item) => {
    emit('select-result', item);
    emit('update:visible', false);
};
</script>
