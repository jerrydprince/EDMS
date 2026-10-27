<template>
  <main class="mb-20 px-5 lg:px-8 max-w-7xl mx-auto pt-6">
    <!-- Top Executive Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-primary shadow-xs shrink-0">
          <Icon icon="solar:share-circle-bold-duotone" class="w-7 h-7 text-primary" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Shared Files & Documents</h1>
          <p class="text-sm text-gray-500">Cross-departmental files and archives shared across directorates and officers for collaboration and treatment</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <Link href="/dashboard/document-management" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium flex items-center gap-2 transition-colors shadow-2xs">
          <Icon icon="octicon:git-branch-24" width="16" />
          <span>Document Repository</span>
        </Link>
      </div>
    </div>

    <!-- Segmented Tabs Navigation: Shared with Me vs Shared by Me -->
    <div class="border-b border-gray-200 mt-2">
      <nav class="flex space-x-6" aria-label="Tabs">
        <!-- Tab 1: Shared with Me -->
        <button
          type="button"
          @click="switchTab('shared_with_me')"
          :class="[
            currentTab === 'shared_with_me'
              ? 'border-primary text-primary font-semibold'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'
          ]"
          class="flex items-center gap-2.5 py-3 px-1 border-b-2 text-sm transition-colors relative"
        >
          <Icon icon="solar:inbox-in-bold-duotone" class="w-5 h-5" :class="currentTab === 'shared_with_me' ? 'text-primary' : 'text-gray-400'" />
          <span>Shared with Me</span>
          <span
            :class="[
              currentTab === 'shared_with_me'
                ? 'bg-blue-100 text-primary'
                : 'bg-gray-100 text-gray-600'
            ]"
            class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold"
          >
            {{ props.sharedWithMeCount || 0 }}
          </span>
        </button>

        <!-- Tab 2: Shared by Me -->
        <button
          type="button"
          @click="switchTab('shared_by_me')"
          :class="[
            currentTab === 'shared_by_me'
              ? 'border-primary text-primary font-semibold'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'
          ]"
          class="flex items-center gap-2.5 py-3 px-1 border-b-2 text-sm transition-colors relative"
        >
          <Icon icon="solar:plain-bold-duotone" class="w-5 h-5" :class="currentTab === 'shared_by_me' ? 'text-primary' : 'text-gray-400'" />
          <span>Shared by Me</span>
          <span
            :class="[
              currentTab === 'shared_by_me'
                ? 'bg-blue-100 text-primary'
                : 'bg-gray-100 text-gray-600'
            ]"
            class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold"
          >
            {{ props.sharedByMeCount || 0 }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Executive Toolbar: Search, Filters, Sort, and View Switcher -->
    <section class="mt-5 p-3 bg-white border border-gray-200 rounded-xl shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <!-- Left Controls: Search Bar & Format Filter -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 max-w-2xl">
        <!-- Search Input with Clear Button -->
        <div class="relative flex-1">
          <Icon icon="solar:magnifer-outline" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            v-model="search_text"
            @keyup.enter="handleBackendSearch"
            placeholder="Search documents by title, description or officer..."
            class="w-full pl-9 pr-8 py-2 text-sm bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg transition-all text-gray-800 placeholder-gray-400 focus:outline-none"
          />
          <button
            v-if="search_text"
            type="button"
            @click="clearSearch"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 rounded-full"
            title="Clear search"
          >
            <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
          </button>
        </div>

        <!-- Document Type Selector -->
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg border border-gray-200/60 shrink-0 text-xs">
          <button
            v-for="typeOption in docTypeOptions"
            :key="typeOption.value"
            type="button"
            @click="selectedDocType = typeOption.value"
            :class="[
              selectedDocType === typeOption.value
                ? 'bg-white text-gray-900 font-semibold shadow-xs'
                : 'text-gray-500 hover:text-gray-700'
            ]"
            class="px-2.5 py-1.5 rounded-md transition-all whitespace-nowrap"
          >
            {{ typeOption.label }}
          </button>
        </div>
      </div>

      <!-- Right Controls: Sort Order & View Toggle Group -->
      <div class="flex items-center justify-end gap-2.5 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100">
        <!-- Sort Order Button -->
        <button
          type="button"
          @click="toggleSortOrder"
          class="flex items-center gap-2 px-3.5 py-2 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-medium transition-colors shadow-2xs"
          title="Toggle sort direction"
        >
          <Icon
            :icon="sortOrder === 'asc' ? 'solar:sort-from-top-to-bottom-outline' : 'solar:sort-from-bottom-to-top-outline'"
            class="w-4 h-4 text-primary"
          />
          <span>Sort: {{ sortOrder === 'asc' ? 'Name (A → Z)' : 'Name (Z → A)' }}</span>
        </button>

        <!-- View Mode Segmented Switcher (List vs Grid) -->
        <div class="inline-flex items-center p-1 bg-gray-100 rounded-lg border border-gray-200/80">
          <button
            type="button"
            @click="foldersGridView = false"
            :class="[
              !foldersGridView
                ? 'bg-white text-primary font-semibold shadow-xs'
                : 'text-gray-500 hover:text-gray-900 font-medium'
            ]"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs transition-all"
            title="Switch to List View"
          >
            <Icon icon="lucide:list" class="w-4 h-4" />
            <span>List</span>
          </button>

          <button
            type="button"
            @click="foldersGridView = true"
            :class="[
              foldersGridView
                ? 'bg-white text-primary font-semibold shadow-xs'
                : 'text-gray-500 hover:text-gray-900 font-medium'
            ]"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs transition-all"
            title="Switch to Grid View"
          >
            <Icon icon="system-uicons:grid" class="w-4 h-4" />
            <span>Grid</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Contextual Document Header / Summary Bar -->
    <div v-if="displayedFiles.length > 0" class="flex items-center justify-between mt-6 mb-3 px-1">
      <div class="flex items-center gap-2 text-sm text-gray-600">
        <span class="font-semibold text-gray-900">{{ currentTab === 'shared_by_me' ? 'Documents Shared by You' : 'Documents Shared with You' }}</span>
        <span class="text-xs bg-gray-100 text-gray-600 font-mono px-2 py-0.5 rounded-full">{{ displayedFiles.length }} document{{ displayedFiles.length !== 1 ? 's' : '' }}</span>
      </div>
    </div>

    <!-- =================== View 1: List View =================== -->
    <div v-if="displayedFiles.length > 0 && !foldersGridView" class="mt-3">
      <SharedListVue
        :files="displayedFiles"
        :documentsGridView="foldersGridView"
        @docs="SetselectedDocument"
        @changeFormat="setFormatModal"
        @downloadFile="downloadFile"
        @selectedArchive="setSelectedArchive"
        @branchDetails="setBranchDetails"
        :archives="props.archives"
        :shared="props.shared"
        :search="search_text"
        :sort="sortOrder"
        :activeTab="currentTab"
        @fileActivity="toggleFileActivity = true"
        @fileVersion="toggleFileVersion = true"
        @lockDocs="handleLockDocument"
        @share="handleShare"
        :branch_type="props.branch_type"
        :branch_id="props.branch_id"
        :currentPage="props.shared?.current_page || 1"
        :perPage="props.shared?.per_page || 30"
      />
    </div>

    <!-- =================== View 2: Grid View =================== -->
    <div v-if="displayedFiles.length > 0 && foldersGridView" class="mt-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <div
          v-for="file in displayedFiles"
          :key="file.id"
          class="bg-white border border-gray-200 hover:border-blue-300 rounded-xl p-4 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group"
        >
          <!-- Top Row: Icon + More Menu -->
          <div>
            <div class="flex items-start justify-between gap-2 mb-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border border-gray-100" :class="getFileIconMeta(file).bg">
                <Icon :icon="getFileIconMeta(file).icon" class="w-6 h-6" />
              </div>
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  @click="openPreview(file)"
                  class="p-1.5 text-gray-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-colors"
                  title="Preview Document"
                >
                  <Icon icon="solar:eye-outline" class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="downloadDirect(file)"
                  class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                  title="Download File"
                >
                  <Icon icon="solar:download-minimalistic-outline" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Title & Description -->
            <h2
              class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 hover:text-primary cursor-pointer transition-colors mb-1"
              @click="openPreview(file)"
              :title="file.name || file.title"
            >
              {{ file.name || file.title || 'Untitled Document' }}
            </h2>

            <p v-if="file.description?.archive || file.description" class="text-xs text-gray-500 line-clamp-2 mb-3">
              {{ file.description?.archive || file.description }}
            </p>
          </div>

          <!-- Bottom Meta & User Badge -->
          <div class="pt-3 border-t border-gray-100 text-xs text-gray-500 space-y-2 mt-2">
            <!-- Share Counterpart info -->
            <div class="flex items-center justify-between gap-2">
              <span class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">
                {{ currentTab === 'shared_by_me' ? 'Recipient' : 'Shared by' }}
              </span>
              <span class="font-medium text-gray-700 truncate max-w-[130px]" :title="getUserDisplayName(file)">
                {{ getUserDisplayName(file) }}
              </span>
            </div>

            <!-- Directorate & Date -->
            <div class="flex items-center justify-between text-[11px] text-gray-400">
              <span class="truncate max-w-[110px]">
                {{ file.department?.title || file.department?.name || 'General' }}
              </span>
              <span>{{ file.share_info?.shared_at || file.date || 'Recent' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- =================== Rich Empty States =================== -->
    <div v-if="displayedFiles.length === 0" class="flex flex-col items-center justify-center py-20 px-4 text-center my-8 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
      <div
        :class="[
          currentTab === 'shared_by_me'
            ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
            : 'bg-blue-50 text-primary border-blue-100'
        ]"
        class="w-20 h-20 border rounded-2xl flex items-center justify-center mb-5 shadow-xs"
      >
        <Icon
          :icon="currentTab === 'shared_by_me' ? 'solar:plain-bold-duotone' : 'solar:inbox-in-bold-duotone'"
          class="w-10 h-10"
        />
      </div>

      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        {{ currentTab === 'shared_by_me' ? 'You Haven\'t Shared Any Documents Yet' : 'No Documents Shared with You' }}
      </h2>

      <p class="text-gray-500 max-w-md mb-6 text-sm leading-relaxed">
        {{
          currentTab === 'shared_by_me'
            ? 'When you share dockets, memos, or archives with colleagues or directorates from Document Management, they will be tracked here.'
            : 'Files, memos, and dossiers shared with you by other directorates or officers will appear here for collaborative review, annotations, and treatment.'
        }}
      </p>

      <div class="flex items-center gap-3">
        <Link href="/dashboard/document-management" class="px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-sm flex items-center gap-2">
          <Icon icon="octicon:git-branch-24" width="18" />
          <span>Open Document Management</span>
        </Link>
        <Link href="/dashboard/workflow" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
          <Icon icon="ph:clipboard-text" width="18" />
          <span>View Active Tasks</span>
        </Link>
      </div>
    </div>
  </main>

  <!-- Modals -->
  <BranchDetails v-model="branchDetailsModal" :file="state.selectedArchive" :folder="[]" @updateDocument="handleUpdateDocument" />
  <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
  <ChangeFormat v-model="ChangeFormatModal" :archive="state.selectedArchive" />
  <PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="filePreviewToggle" @toggleVersion="toggleFileVersion = true" />
  <share v-model="toggleShare" :archive="state.selectedArchive" />
  <LockDocument v-model="LockDocumentModal" :archive="state.selectedArchive" />
  <UnlockDocument v-model="UnlockDocumentModal" :archive="state.selectedArchive" />
</template>

<script setup>
import { ref, defineProps, computed, reactive, watch } from "vue";
import { Link, usePage, router } from '@inertiajs/vue3';
import BranchDetails from "../../components/Modals/branches/BranchDetails.vue";
import LockDocument from "../../components/Modals/branches/LockDocument.vue";
import UnlockDocument from "../../components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "../../components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "../../components/Modals/branches/ChangeFormatModal.vue";
import PreviewFile from "../../components/Modals/branches/PreviewFile.vue";
import SharedListVue from '../dashboard/partials/SharedListVue.vue';
import share from "../../components/Modals/branches/Share.vue";

const page = usePage();
const props = defineProps([
  'archives',
  'shared',
  'branch_id',
  'is_folder_open',
  'branch_type',
  'folder_name',
  'folder',
  'folder_id',
  'activeTab',
  'sharedWithMeCount',
  'sharedByMeCount',
  'filters'
]);

// Active Tab state: 'shared_with_me' vs 'shared_by_me'
const currentTab = ref(props.activeTab || props.filters?.tab || 'shared_with_me');
const foldersGridView = ref(false);
const search_text = ref(props.filters?.search || '');
const selectedDocType = ref('all');
const sortOrder = ref('asc');

// Modals state
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false);
const UnlockDocumentModal = ref(false);
const GenerateEncriptionModal = ref(false);
const ChangeFormatModal = ref(false);
const toggleFileVersion = ref(false);
const toggleFileActivity = ref(false);
const togglePreview = ref(false);
const filePreviewToggle = ref([]);
const toggleShare = ref(false);

const state = reactive({
  selectedArchive: null
});

const docTypeOptions = [
  { label: 'All Types', value: 'all' },
  { label: 'PDF', value: 'pdf' },
  { label: 'Word', value: 'word' },
  { label: 'Excel', value: 'excel' },
  { label: 'Images', value: 'image' }
];

const switchTab = (tabName) => {
  currentTab.value = tabName;
  router.get('/dashboard/shared-files', {
    tab: tabName,
    search: search_text.value || undefined
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const handleBackendSearch = () => {
  router.get('/dashboard/shared-files', {
    tab: currentTab.value,
    search: search_text.value || undefined
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearSearch = () => {
  search_text.value = '';
  handleBackendSearch();
};

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const formatDate = (inputDate) => {
  if (!inputDate) return '';
  const date = new Date(inputDate);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
  const month = months[date.getMonth()];
  const year = date.getFullYear();
  return `${day} ${month} ${year.toString().substr(-2)}`;
};

const getArchiveDocsVale = (data, search) => {
  if (data) {
    for (let archive of data) {
      if (archive.archive_keys && archive.archive_keys.toLowerCase().includes(search)) {
        return [{ archive: archive.archive_values, id: archive.id }];
      }
    }
  }
  return null;
};

const files = computed(() => {
  const newarr = [];
  if (props.archives) {
    for (let file of props.archives) {
      const arr = {
        name: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0].archive : file?.file_path?.[0]?.file_name,
        size: file?.file_path?.[0]?.file_size,
        date: formatDate(file.updated_at),
        uploaded_by: file?.file_path?.[0]?.updated_by_name,
        path: (file.file_path && file.file_path.length > 0) ? file?.file_path[0]?.path : '',
        files: file?.file_path,
        id: file.id,
        amount: getArchiveDocsVale(file.archive_docs, 'amount') ? getArchiveDocsVale(file.archive_docs, 'amount')[0] : '',
        title: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : file?.file_path?.[0]?.file_name,
        description: getArchiveDocsVale(file.archive_docs, 'description') ? getArchiveDocsVale(file.archive_docs, 'description')[0] : '',
        archive_docs_id: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : '',
        archive_docs: file.archive_docs,
        department: file.department ? file.department : null,
        division: file.division ? file.division : null,
        unit: file.unit ? file.unit : null,
        file_activity: file.file_activity,
        file_version: file.version,
        is_locked: file.is_locked,
        share_info: file.share_info || null
      };
      newarr.push(arr);
    }
  }
  return newarr;
});

const displayedFiles = computed(() => {
  let list = [...files.value];

  // Client-side instant search filtering
  if (search_text.value && search_text.value.trim() !== '') {
    const q = search_text.value.trim().toLowerCase();
    list = list.filter(f => {
      const name = (f.name || f.title || '').toLowerCase();
      const desc = (f.description?.archive || f.description || '').toLowerCase();
      const user = getUserDisplayName(f).toLowerCase();
      return name.includes(q) || desc.includes(q) || user.includes(q);
    });
  }

  // Document Type Filtering
  if (selectedDocType.value !== 'all') {
    list = list.filter(f => {
      const name = (f.name || f.title || '').toLowerCase();
      const ext = name.split('.').pop();
      if (selectedDocType.value === 'pdf') return ext === 'pdf';
      if (selectedDocType.value === 'word') return ['doc', 'docx'].includes(ext);
      if (selectedDocType.value === 'excel') return ['xls', 'xlsx', 'csv'].includes(ext);
      if (selectedDocType.value === 'image') return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
      return true;
    });
  }

  // Sorting
  if (sortOrder.value === 'asc') {
    list.sort((a, b) => (a.name || a.title || '').localeCompare(b.name || b.title || ''));
  } else {
    list.sort((a, b) => (b.name || b.title || '').localeCompare(a.name || a.title || ''));
  }

  return list;
});

const getFileIconMeta = (file) => {
  const name = (file?.name || file?.title || '').toLowerCase();
  const ext = name.split('.').pop();
  if (['pdf'].includes(ext)) {
    return { icon: 'vscode-icons:file-type-pdf2', bg: 'bg-red-50 text-red-600', label: 'PDF' };
  }
  if (['doc', 'docx'].includes(ext)) {
    return { icon: 'vscode-icons:file-type-word', bg: 'bg-blue-50 text-blue-600', label: 'Word' };
  }
  if (['xls', 'xlsx', 'csv'].includes(ext)) {
    return { icon: 'vscode-icons:file-type-excel', bg: 'bg-emerald-50 text-emerald-600', label: 'Excel' };
  }
  if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
    return { icon: 'vscode-icons:file-type-image', bg: 'bg-purple-50 text-purple-600', label: 'Image' };
  }
  return { icon: 'solar:document-outline', bg: 'bg-gray-100 text-gray-600', label: 'Document' };
};

const getUserDisplayName = (file) => {
  if (currentTab.value === 'shared_by_me') {
    const user = file.share_info?.shared_user;
    if (user) return `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.email;
    return 'Recipient';
  }
  const user = file.share_info?.sharing_user;
  if (user) return `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.email;
  return file.uploaded_by || 'Sender';
};

const openPreview = (file) => {
  filePreviewToggle.value = file;
  togglePreview.value = true;
};

const downloadDirect = (file) => {
  window.location.href = (page.props.base || '') + '/download-file?id=' + file.id;
};

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const setBranchDetails = (data) => {
  branchDetailsModal.value = data;
};

const SetselectedDocument = (data) => {
  state.selectedArchive = data.value;
};

const setSelectedArchive = (data) => {
  state.selectedArchive = data.value;
};

const setFormatModal = (data) => {
  ChangeFormatModal.value = data;
};

const handleLockDocument = () => {
  LockDocumentModal.value = true;
};

const handleShare = () => {
  toggleShare.value = true;
};

const handleUpdateDocument = () => {
  router.reload({ only: ['archives', 'shared'] });
  branchDetailsModal.value = false;
};

const downloadFile = async () => {
  if (!state.selectedArchive?.id) return;
  window.location.href = (page.props.base || '') + '/download-file?id=' + state.selectedArchive.id;
};

// Keep currentTab synchronized if props change
watch(() => props.activeTab, (newTab) => {
  if (newTab) currentTab.value = newTab;
});
</script>
