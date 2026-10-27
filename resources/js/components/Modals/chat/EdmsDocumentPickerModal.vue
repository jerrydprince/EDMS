<template>
    <Dialog
        v-model:visible="visible"
        header="Share Document from EDMS Repository"
        :style="{ width: '92vw', maxWidth: '720px' }"
        modal
        :closable="true"
        :dismissableMask="true"
        class="p-dialog-custom"
    >
        <div class="space-y-4 pt-1">
            <!-- Search & Filter Controls -->
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <Icon icon="lucide:search" class="text-base" />
                    </span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by title, folder, doc number, or department..."
                        class="w-full pl-9 pr-8 py-2 text-sm bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                        @input="onSearchInput"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600"
                    >
                        <Icon icon="lucide:x" class="text-xs" />
                    </button>
                </div>

                <div class="flex items-center gap-1.5">
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl flex-1 sm:flex-initial">
                        <button
                            type="button"
                            v-for="cat in categories"
                            :key="cat.value"
                            @click="selectedCategory = cat.value; fetchDocuments()"
                            :class="[
                                'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                                selectedCategory === cat.value
                                    ? 'bg-white text-primary shadow-xs'
                                    : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            {{ cat.label }}
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="fetchDocuments"
                        class="p-2 text-gray-500 hover:text-primary hover:bg-gray-100 rounded-xl transition-colors border border-gray-200 flex-shrink-0"
                        title="Refresh Documents"
                    >
                        <Icon icon="solar:restart-bold" :class="{ 'animate-spin': loading }" class="text-base" />
                    </button>
                </div>
            </div>

            <!-- Access Level & Scope Indicator -->
            <div v-if="accessScope" class="flex items-center justify-between px-3 py-1.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-800">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <Icon icon="solar:shield-check-bold" class="text-blue-600 text-sm" />
                    <span class="font-medium text-blue-600">Access Level:</span>
                    <span class="font-bold text-gray-900">{{ accessTier }}</span>
                    <span class="text-blue-300">•</span>
                    <span class="text-blue-700 font-medium">{{ accessScope }}</span>
                </div>
                <span class="text-[11px] text-blue-600 font-semibold">
                    {{ totalRecordsFormatted ? `${totalRecordsFormatted} records in repository` : `${documents.length} records` }}
                </span>
            </div>

            <!-- Documents List Container -->
            <div class="h-80 overflow-y-auto divide-y divide-gray-100 border border-gray-100 rounded-2xl bg-gray-50/50 p-1">
                <!-- Loading State -->
                <div v-if="loading" class="flex flex-col items-center justify-center h-full py-12 text-gray-400">
                    <Icon icon="eos-icons:loading" class="text-3xl text-primary animate-spin mb-2" />
                    <p class="text-xs font-medium">Loading EDMS documents and records...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="documents.length === 0" class="flex flex-col items-center justify-center h-full py-12 text-gray-400">
                    <Icon icon="solar:document-outline" class="text-4xl text-gray-300 mb-2" />
                    <p class="text-sm font-semibold text-gray-600">No documents found</p>
                    <p class="text-xs text-gray-400 mt-0.5">Try searching with a different title, folder, or keyword</p>
                    <button
                        type="button"
                        @click="clearSearch"
                        class="mt-3 px-3 py-1 bg-white border border-gray-200 text-primary text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors shadow-2xs"
                    >
                        Reset Search
                    </button>
                </div>

                <!-- Document Items -->
                <div
                    v-else
                    v-for="doc in documents"
                    :key="doc.id"
                    @click="toggleSelectDoc(doc)"
                    :class="[
                        'flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-150',
                        isSelected(doc)
                            ? 'bg-blue-50/80 border border-blue-200/80 shadow-2xs'
                            : 'hover:bg-white hover:shadow-2xs border border-transparent'
                    ]"
                >
                    <!-- Checkbox -->
                    <div class="flex-shrink-0">
                        <div
                            class="w-5 h-5 rounded-md flex items-center justify-center transition-colors"
                            :class="isSelected(doc) ? 'bg-primary text-white shadow-2xs' : 'border border-gray-300 bg-white'"
                        >
                            <Icon v-if="isSelected(doc)" icon="lucide:check" class="text-xs font-black" />
                        </div>
                    </div>

                    <!-- File Type Icon -->
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        :class="getFileTypeBadgeClass(doc)"
                    >
                        <Icon :icon="getFileIcon(doc)" class="text-xl" />
                    </div>

                    <!-- Document Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm text-gray-900 truncate max-w-[260px] sm:max-w-[320px]" :title="doc.title || doc.file_name">
                                {{ doc.title || doc.file_name }}
                            </span>
                            <span
                                v-if="doc.doc_no"
                                class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-gray-100 text-gray-700 border border-gray-200 flex-shrink-0"
                            >
                                {{ doc.doc_no }}
                            </span>
                        </div>

                        <!-- Technical filename note if different from Document Management Title -->
                        <div v-if="doc.file_name && doc.file_name !== doc.title" class="text-[11px] text-gray-400 font-mono truncate max-w-[280px]">
                            {{ doc.file_name }}
                        </div>

                        <div class="flex items-center gap-2 mt-1 text-[11px] text-gray-500 flex-wrap">
                            <span class="inline-flex items-center gap-1 text-gray-600 font-medium">
                                <Icon icon="lucide:building" class="text-xs text-gray-400" />
                                {{ doc.department || 'EDMS' }}
                            </span>
                            <span v-if="doc.folder" class="inline-flex items-center gap-1 text-blue-700 font-medium bg-blue-50/80 px-1.5 py-0.5 rounded-md border border-blue-100/60 max-w-[180px] truncate">
                                <Icon icon="solar:folder-bold-duotone" class="text-xs text-blue-500 flex-shrink-0" />
                                <span class="truncate">{{ doc.folder }}</span>
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="text-gray-400">{{ doc.source }}</span>
                            <span v-if="doc.date" class="text-gray-300">•</span>
                            <span v-if="doc.date" class="text-gray-400">{{ doc.date }}</span>
                        </div>
                    </div>

                    <!-- Quick Preview Button -->
                    <div class="flex-shrink-0 flex items-center">
                        <button
                            type="button"
                            @click.stop="openPreview(doc)"
                            class="p-2 text-gray-400 hover:text-primary hover:bg-blue-100/80 rounded-xl transition-all border border-transparent hover:border-blue-200 cursor-pointer shadow-2xs group/prev"
                            title="Preview Document"
                        >
                            <Icon icon="solar:eye-bold-duotone" class="text-lg group-hover/prev:scale-110 transition-transform" />
                        </button>
                    </div>

                    <!-- Classification Tag & Size -->
                    <div class="flex-shrink-0 text-right">
                        <span
                            :class="[
                                'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold',
                                doc.classification === 'Confidential'
                                    ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                    : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            ]"
                        >
                            {{ doc.classification }}
                        </span>
                        <div class="text-[10px] text-gray-400 mt-1">
                            {{ doc.size }}
                        </div>
                    </div>
                </div>

                <!-- Load More Area -->
                <div v-if="hasMore" class="p-3 text-center border-t border-gray-100 bg-white sticky bottom-0 z-10 shadow-sm">
                    <button
                        type="button"
                        @click="loadMoreDocuments"
                        :disabled="loadingMore"
                        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold text-primary bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors disabled:opacity-50 cursor-pointer"
                    >
                        <Icon v-if="loadingMore" icon="eos-icons:loading" class="animate-spin text-sm" />
                        <Icon v-else icon="solar:alt-arrow-down-linear" class="text-sm" />
                        <span>{{ loadingMore ? 'Loading more records...' : `Load More Records (${documents.length} of ${totalRecordsFormatted})` }}</span>
                    </button>
                </div>
                <div v-else-if="documents.length > 0 && totalRecords > 0" class="py-2.5 text-center text-[11px] text-gray-400 font-medium">
                    All {{ totalRecordsFormatted }} records loaded
                </div>
            </div>

            <!-- Footer / Action Area -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="text-xs text-gray-500">
                    <span v-if="selectedDocuments.length > 0" class="font-semibold text-primary">
                        {{ selectedDocuments.length }} document{{ selectedDocuments.length > 1 ? 's' : '' }} selected
                    </span>
                    <span v-else>Select documents to share into chat</span>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        label="Cancel"
                        class="p-button-text p-button-secondary text-xs"
                        @click="visible = false"
                    />
                    <Button
                        class="bg-primary hover:bg-primary/90 border-none text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 cursor-pointer"
                        :disabled="selectedDocuments.length === 0"
                        @click="confirmSelection"
                    >
                        <Icon icon="solar:share-bold" class="text-sm" />
                        <span>Attach to Message ({{ selectedDocuments.length }})</span>
                    </Button>
                </div>
            </div>
        </div>
    </Dialog>

    <!-- Document Preview Modal -->
    <Dialog
        v-model:visible="previewVisible"
        header="Document Preview"
        :style="{ width: '92vw', maxWidth: '820px' }"
        modal
        :closable="true"
        :dismissableMask="true"
        class="p-dialog-custom z-50"
    >
        <div v-if="previewDoc" class="space-y-3 pt-1">
            <!-- Header Metadata Banner -->
            <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex-wrap gap-2">
                <div class="min-w-0 pr-2">
                    <h3 class="font-bold text-sm text-gray-900 truncate max-w-[450px]" :title="previewDoc.title || previewDoc.file_name">
                        {{ previewDoc.title || previewDoc.file_name }}
                    </h3>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-gray-500 flex-wrap">
                        <span class="font-semibold text-primary bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                            {{ previewDoc.doc_no || 'Record' }}
                        </span>
                        <span class="text-gray-300">•</span>
                        <span>{{ previewDoc.department || 'EDMS' }}</span>
                        <span v-if="previewDoc.folder" class="text-gray-300">•</span>
                        <span v-if="previewDoc.folder" class="text-blue-700 font-medium truncate max-w-[180px]">{{ previewDoc.folder }}</span>
                        <span class="text-gray-300">•</span>
                        <span>{{ previewDoc.size }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <a
                        :href="getDocPreviewUrl(previewDoc)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary bg-white hover:bg-blue-50 border border-gray-200 rounded-xl shadow-2xs transition-colors"
                        title="Open in new window"
                    >
                        <Icon icon="solar:arrow-right-up-linear" class="text-sm" />
                        <span>Open Full</span>
                    </a>
                </div>
            </div>

            <!-- Viewer Container -->
            <div class="h-[60vh] min-h-[380px] w-full rounded-2xl border border-gray-200 bg-gray-900/5 overflow-hidden flex items-center justify-center relative">
                <!-- PDF Preview -->
                <iframe
                    v-if="isPdfDoc(previewDoc)"
                    :src="getDocPreviewUrl(previewDoc)"
                    class="w-full h-full border-0 bg-white"
                />

                <!-- Image Preview -->
                <img
                    v-else-if="isImageDoc(previewDoc)"
                    :src="getDocPreviewUrl(previewDoc)"
                    class="max-h-full max-w-full object-contain p-2"
                    :alt="previewDoc.title"
                />

                <!-- Unsupported / Generic Document Fallback -->
                <div v-else class="text-center p-8">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-primary shadow-sm mb-3">
                        <Icon :icon="getFileIcon(previewDoc)" class="text-3xl" />
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm">{{ previewDoc.title || previewDoc.file_name }}</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        Inline browser preview is not supported for this file type. You can open or download the document directly.
                    </p>
                    <a
                        :href="getDocPreviewUrl(previewDoc)"
                        target="_blank"
                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary/90 shadow-sm transition-all"
                    >
                        <Icon icon="solar:arrow-right-up-linear" class="text-sm" />
                        <span>Open Document</span>
                    </a>
                </div>
            </div>

            <!-- Preview Modal Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <span class="text-xs text-gray-400">Document Management Repository</span>
                <div class="flex items-center gap-2">
                    <Button
                        label="Close"
                        class="p-button-text p-button-secondary text-xs"
                        @click="previewVisible = false"
                    />
                    <Button
                        class="bg-primary hover:bg-primary/90 border-none text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 cursor-pointer"
                        @click="selectAndClosePreview(previewDoc)"
                    >
                        <Icon icon="lucide:check" class="text-xs font-black" />
                        <span>{{ isSelected(previewDoc) ? 'Selected (Close)' : 'Select & Attach to Chat' }}</span>
                    </Button>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'attach']);
const pageContext = usePage();

const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const searchQuery = ref('');
const selectedCategory = ref('all');
const documents = ref([]);
const selectedDocuments = ref([]);
const accessTier = ref('');
const accessScope = ref('');
const loading = ref(false);
const loadingMore = ref(false);
const page = ref(1);
const hasMore = ref(false);
const totalRecords = ref(0);
const totalRecordsFormatted = ref('');
let searchDebounce = null;

// Preview Modal state
const previewVisible = ref(false);
const previewDoc = ref(null);

const categories = [
    { label: 'All', value: 'all' },
    { label: 'Repository', value: 'repository' },
    { label: 'Tasks', value: 'tasks' },
];

const fetchDocuments = async () => {
    loading.value = true;
    page.value = 1;
    try {
        const token = localStorage.getItem('token');
        const headers = {};
        if (token && token !== 'undefined' && token !== 'null') {
            headers.Authorization = `Bearer ${token}`;
        }
        const res = await axios.get('/dashboard/chat/edms-documents', {
            params: {
                search: searchQuery.value,
                category: selectedCategory.value,
                page: 1,
            },
            headers,
        });
        documents.value = res.data.documents || [];
        totalRecords.value = res.data.total || 0;
        totalRecordsFormatted.value = res.data.total_formatted || (res.data.total ? res.data.total.toLocaleString() : '');
        hasMore.value = !!res.data.has_more;
        accessTier.value = res.data.tier || '';
        accessScope.value = res.data.scope || '';
    } catch (err) {
        console.error('Error fetching EDMS documents for chat', err);
        documents.value = [];
        totalRecords.value = 0;
        totalRecordsFormatted.value = '';
        hasMore.value = false;
    } finally {
        loading.value = false;
    }
};

const loadMoreDocuments = async () => {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    try {
        const nextPage = page.value + 1;
        const token = localStorage.getItem('token');
        const headers = {};
        if (token && token !== 'undefined' && token !== 'null') {
            headers.Authorization = `Bearer ${token}`;
        }
        const res = await axios.get('/dashboard/chat/edms-documents', {
            params: {
                search: searchQuery.value,
                category: selectedCategory.value,
                page: nextPage,
            },
            headers,
        });
        const newDocs = res.data.documents || [];
        documents.value = [...documents.value, ...newDocs];
        page.value = nextPage;
        hasMore.value = !!res.data.has_more;
    } catch (err) {
        console.error('Error loading more EDMS documents', err);
    } finally {
        loadingMore.value = false;
    }
};

// Automatically fetch documents whenever modal opens
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            searchQuery.value = '';
            selectedDocuments.value = [];
            fetchDocuments();
        }
    },
    { immediate: true }
);

const onSearchInput = () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        fetchDocuments();
    }, 300);
};

const clearSearch = () => {
    searchQuery.value = '';
    fetchDocuments();
};

const isSelected = (doc) => {
    if (!doc) return false;
    return selectedDocuments.value.some((d) => d.id === doc.id);
};

const toggleSelectDoc = (doc) => {
    const idx = selectedDocuments.value.findIndex((d) => d.id === doc.id);
    if (idx > -1) {
        selectedDocuments.value.splice(idx, 1);
    } else {
        selectedDocuments.value.push(doc);
    }
};

const confirmSelection = () => {
    if (selectedDocuments.value.length === 0) return;
    emit('attach', [...selectedDocuments.value]);
    visible.value = false;
    selectedDocuments.value = [];
};

// Document Preview Logic
const openPreview = (doc) => {
    previewDoc.value = doc;
    previewVisible.value = true;
};

const selectAndClosePreview = (doc) => {
    if (!isSelected(doc)) {
        selectedDocuments.value.push(doc);
    }
    previewVisible.value = false;
};

const getDocPreviewUrl = (doc) => {
    if (!doc || !doc.file_path) return '';
    const filePath = doc.file_path;
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) return filePath;
    const base = (pageContext?.props?.base || '').replace(/\/+$/, '');
    if (filePath.startsWith('/download-file') || filePath.startsWith('download-file')) {
        return base + '/' + filePath.replace(/^\/+/, '');
    }
    let clean = filePath.replace(/^\/+/, '').replace(/^public\//, '');
    if (clean.startsWith('storage/')) {
        clean = clean.replace(/^storage\//, '');
    }
    return base + '/storage/' + clean;
};

const isPdfDoc = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return type.includes('pdf') || name.endsWith('.pdf');
};

const isImageDoc = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'].some(ext => type.includes(ext) || name.endsWith('.' + ext));
};

const getFileTypeBadgeClass = (doc) => {
    const ext = (doc.file_type || doc.file_name || '').toLowerCase();
    if (ext.includes('pdf')) return 'bg-red-50 text-red-600 border border-red-100';
    if (ext.includes('doc') || ext.includes('word')) return 'bg-blue-50 text-blue-600 border border-blue-100';
    if (ext.includes('xls') || ext.includes('sheet') || ext.includes('csv')) return 'bg-emerald-50 text-emerald-600 border border-emerald-100';
    if (ext.includes('png') || ext.includes('jpg') || ext.includes('jpeg') || ext.includes('image')) return 'bg-purple-50 text-purple-600 border border-purple-100';
    return 'bg-amber-50 text-amber-600 border border-amber-100';
};

const getFileIcon = (doc) => {
    const ext = (doc.file_type || doc.file_name || '').toLowerCase();
    if (ext.includes('pdf')) return 'vscode-icons:file-type-pdf2';
    if (ext.includes('doc') || ext.includes('word')) return 'vscode-icons:file-type-word';
    if (ext.includes('xls') || ext.includes('sheet') || ext.includes('csv')) return 'vscode-icons:file-type-excel';
    if (ext.includes('png') || ext.includes('jpg') || ext.includes('jpeg') || ext.includes('image')) return 'solar:gallery-bold-duotone';
    return 'solar:document-bold-duotone';
};

watch(visible, (newVal) => {
    if (newVal) {
        selectedDocuments.value = [];
        fetchDocuments();
    }
});

onMounted(() => {
    if (props.modelValue) {
        fetchDocuments();
    }
});
</script>
