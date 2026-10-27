<template>
  <main class="min-h-screen bg-slate-50/60 pb-14">
    <!-- Header Section -->
    <div class="px-7 py-6 bg-white border-b border-slate-200">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-md">
            <Icon icon="carbon:trash-can" width="28" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Recycle Bin & Retention Vault</h1>
              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1">
                <Icon icon="carbon:hourglass" width="13" />
                30-Day Retention Policy
              </span>
            </div>
            <p class="text-slate-500 text-sm mt-0.5">
              Review, restore, or permanently purge soft-deleted documents, workflow tasks, mails, and administrative entities.
            </p>
          </div>
        </div>

        <!-- Executive Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button
            v-if="canDelete"
            @click="EmptyRecycle = true"
            class="px-4 py-2 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors flex items-center gap-1.5 shadow-sm"
          >
            <Icon icon="carbon:trash-can" width="16" />
            Empty Recycle Bin
          </button>
        </div>
      </div>
    </div>

    <div class="px-7 pt-6 space-y-6">
      <!-- 1. Telemetry KPI Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Items -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total in Recycle Bin</span>
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
              <Icon icon="carbon:layers" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ props.counts?.total || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            Across all categories in storage
          </div>
        </div>

        <!-- Deleted Documents -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Archived Documents</span>
            <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
              <Icon icon="carbon:document" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ props.counts?.['Deleted Files'] || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            Soft-deleted vault archives & files
          </div>
        </div>

        <!-- Deleted Tasks -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Workflow Tasks</span>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Icon icon="carbon:flow-stream" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ props.counts?.['Deleted Tasks'] || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            Soft-deleted file movements & tasks
          </div>
        </div>

        <!-- Mails & Entities -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mails & Entities</span>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <Icon icon="carbon:email" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ (props.counts?.['Deleted Mails'] || 0) + (props.counts?.['Deleted Users'] || 0) + (props.counts?.['Deleted Units'] || 0) }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            Mails, personnel & branch units
          </div>
        </div>
      </div>

      <!-- 2. Category Channel Horizontal Tabs -->
      <div class="bg-white rounded-xl border border-slate-200 p-1.5 shadow-sm overflow-x-auto">
        <div class="flex items-center gap-1 min-w-max">
          <button
            v-for="cat in categoryList"
            :key="cat.key"
            @click="switchCategory(cat.key)"
            class="px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2"
            :class="selectedType === cat.key
              ? 'bg-slate-900 text-white shadow-sm'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <Icon :icon="cat.icon" width="16" />
            <span>{{ cat.label }}</span>
            <span
              class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
              :class="selectedType === cat.key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
            >
              {{ props.counts?.[cat.key] || 0 }}
            </span>
          </button>
        </div>
      </div>

      <!-- 3. Advanced Filter & Instant Search Toolbar -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 items-end">
          <!-- Keyword Search -->
          <div class="lg:col-span-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Search Recycled Item</label>
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 focus-within:border-slate-900 focus-within:bg-white transition-colors">
              <Icon icon="iconamoon:search-light" class="text-slate-400 mr-2 shrink-0" width="18" />
              <input
                type="text"
                v-model="searchQuery"
                @keyup.enter="applyFilters"
                placeholder="Search by title, subject, name, or description..."
                class="w-full bg-transparent text-xs text-slate-800 placeholder-slate-400 outline-none"
              />
            </div>
          </div>

          <!-- Date Range Filter -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deleted Date Range</label>
            <div class="grid grid-cols-2 gap-1.5">
              <input
                type="date"
                v-model="dateFrom"
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-xs text-slate-800 outline-none focus:border-slate-900 focus:bg-white"
                title="From Date"
              />
              <input
                type="date"
                v-model="dateTo"
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-xs text-slate-800 outline-none focus:border-slate-900 focus:bg-white"
                title="To Date"
              />
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <button
              @click="applyFilters"
              class="w-full py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-sm"
            >
              <Icon icon="carbon:filter" width="16" />
              Apply Filter
            </button>
            <button
              @click="resetFilters"
              class="p-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors"
              title="Reset Filters"
            >
              <Icon icon="tabler:refresh" width="18" />
            </button>
          </div>
        </div>
      </div>

      <!-- 4. Batch Operations Floating Banner (when items are selected) -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div
          v-if="selectedIds.length > 0"
          class="bg-slate-900 text-white px-5 py-3 rounded-xl shadow-lg flex items-center justify-between gap-4 flex-wrap"
        >
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">
              {{ selectedIds.length }}
            </span>
            <span class="text-xs font-medium">Items selected from {{ selectedType }}</span>
          </div>

          <div class="flex items-center gap-2.5">
            <button
              v-if="canRestore"
              @click="triggerBatchRestore"
              class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5"
            >
              <Icon icon="carbon:reset" width="15" />
              Restore Selected ({{ selectedIds.length }})
            </button>
            <button
              v-if="canDelete"
              @click="triggerBatchDelete"
              class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5"
            >
              <Icon icon="carbon:trash-can" width="15" />
              Purge Selected ({{ selectedIds.length }})
            </button>
            <button
              @click="selectedIds = []"
              class="text-xs text-slate-400 hover:text-white px-2 py-1"
            >
              Deselect
            </button>
          </div>
        </div>
      </transition>

      <!-- 5. Recycled Items Data Table -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <DataTable
          :value="deletedItemsList"
          tableStyle="min-width: 65rem"
          class="p-datatable-sm"
          responsiveLayout="scroll"
        >
          <!-- Empty State -->
          <template #empty>
            <div class="text-center py-16 px-4">
              <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <Icon icon="carbon:trash-can" width="28" />
              </div>
              <h3 class="text-sm font-bold text-slate-800">Recycle Bin Empty</h3>
              <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                No deleted items found in '{{ selectedType }}'. Deleted files and workflows within the 30-day retention grace period will appear here.
              </p>
            </div>
          </template>

          <!-- Checkbox Selection Column -->
          <Column class="w-12 text-center">
            <template #header>
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"
                title="Select All"
              />
            </template>
            <template #body="{ data }">
              <input
                type="checkbox"
                :value="data.id"
                v-model="selectedIds"
                class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"
              />
            </template>
          </Column>

          <!-- 1. Item Name & Details -->
          <Column header="Deleted Item">
            <template #body="{ data }">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 bg-slate-100 text-slate-700">
                  <Icon :icon="getItemIcon(selectedType, data)" width="22" />
                </div>
                <div>
                  <h4 class="text-xs font-bold text-slate-900 line-clamp-1" :title="resolveItemName(data)">
                    {{ resolveItemName(data) }}
                  </h4>
                  <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                    <span class="font-mono">#{{ data.id }}</span>
                    <span>•</span>
                    <span class="capitalize">{{ resolveItemContext(data) }}</span>
                  </div>
                </div>
              </div>
            </template>
          </Column>

          <!-- 2. Deleted Date & Time -->
          <Column header="Deleted Date" class="w-44">
            <template #body="{ data }">
              <div>
                <div class="text-xs font-bold text-slate-800 font-mono">
                  {{ formatDate(data.deleted_at) }}
                </div>
                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                  {{ formatTimeAgo(data.deleted_at) }}
                </div>
              </div>
            </template>
          </Column>

          <!-- 3. Deleted By Officer -->
          <Column header="Deleted By" class="w-48">
            <template #body="{ data }">
              <div>
                <div class="text-xs font-bold text-slate-800 truncate" :title="resolveDeletedBy(data)">
                  {{ resolveDeletedBy(data) }}
                </div>
                <div class="text-[11px] text-slate-500 truncate">
                  {{ data.deleted_by?.department?.title || 'EDMS System' }}
                </div>
              </div>
            </template>
          </Column>

          <!-- 4. Retention Grace Policy -->
          <Column header="Retention Grace" class="w-40">
            <template #body="{ data }">
              <div>
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                  :class="getRetentionBadgeClass(data.retention_days_left)"
                >
                  <Icon icon="carbon:time" width="13" />
                  {{ data.retention_days_left !== undefined ? `${data.retention_days_left} days left` : '30-day grace' }}
                </span>
                <div class="text-[10px] text-slate-400 mt-0.5">
                  {{ data.days_in_bin ? `${data.days_in_bin} days in bin` : 'Recently deleted' }}
                </div>
              </div>
            </template>
          </Column>

          <!-- 5. Actions Column -->
          <Column header="Actions" class="w-32 text-right">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-2">
                <!-- Restore Button -->
                <button
                  v-if="canRestore"
                  @click="openRestoreModal(data.id)"
                  class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                  title="Restore Item"
                >
                  <Icon icon="carbon:reset" width="18" />
                </button>

                <!-- Permanent Delete Button -->
                <button
                  v-if="canDelete"
                  @click="openDeleteModal(data.id)"
                  class="p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                  title="Permanently Destroy"
                >
                  <Icon icon="carbon:trash-can" width="18" />
                </button>
              </div>
            </template>
          </Column>
        </DataTable>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/50">
          <pagination :archives="props.deleted_files" />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <DeleteFileModal v-model="DeleteFileModalOpen" @deletefile="confirmDeleteHandler" />
    <RestoreFileModal v-model="RestoreFileModalOpen" @restore="confirmRestoreHandler" />
    <EmptyRecycleModal v-model="EmptyRecycle" :currentType="selectedType" @emptybin="confirmEmptyBinHandler" />
  </main>
</template>

<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import moment from "moment";
import { useToast } from "primevue/usetoast";
import DeleteFileModal from "../../components/Modals/RecycleBin/DeleteFile.vue";
import RestoreFileModal from "../../components/Modals/RecycleBin/RestoreFile.vue";
import EmptyRecycleModal from "../../components/Modals/RecycleBin/EmptyRecycle.vue";
import pagination from "../dashboard/partials/pagination.vue";

const page = usePage();
const userPermissions = computed(() => {
  const p = page.props.permissions || [];
  return Array.isArray(p) ? p.map(x => String(x).toLowerCase().trim()) : [];
});
const userRoles = computed(() => {
  const r = page.props.roles || page.props.user_roles || [];
  return (Array.isArray(r) ? r : Object.values(r)).map(x => (typeof x === 'string' ? x : x?.name || '').toLowerCase().trim());
});
const hasRecyclePermission = (perm) => {
  if (userRoles.value.some(r => r === 'super admin' || r === 'admin')) return true;
  return userPermissions.value.includes(perm.toLowerCase().trim());
};
const canRestore = computed(() => hasRecyclePermission('recycle bin update'));
const canDelete = computed(() => hasRecyclePermission('recycle bin delete'));

const props = defineProps({
  deleted_files: {
    type: Object,
    default: () => ({ data: [] })
  },
  type: {
    type: String,
    default: 'Deleted Files'
  },
  counts: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const toast = useToast();

// Category Channels Definition
const categoryList = [
  { key: 'Deleted Files', label: 'Archived Documents', icon: 'carbon:document' },
  { key: 'Deleted Tasks', label: 'Workflow Tasks', icon: 'carbon:flow-stream' },
  { key: 'Deleted Mails', label: 'Official Mails', icon: 'carbon:email' },
  { key: 'Deleted Users', label: 'Personnel & Staff', icon: 'carbon:user-avatar' },
  { key: 'Deleted Units', label: 'Branch Units', icon: 'carbon:network-3' },
  { key: 'Deleted Divisions', label: 'Divisions', icon: 'carbon:enterprise' },
  { key: 'Deleted Departments', label: 'Departments', icon: 'carbon:building' },
  { key: 'Deleted Metadata', label: 'Metadata', icon: 'carbon:tag' }
];

// Active State
const selectedType = ref(props.type || 'Deleted Files');
const searchQuery = ref(props.filters?.search || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

// Selection State
const selectedIds = ref([]);
const activeActionId = ref(null);

// Modal Flags
const DeleteFileModalOpen = ref(false);
const RestoreFileModalOpen = ref(false);
const EmptyRecycle = ref(false);

const deletedItemsList = computed(() => props.deleted_files?.data || []);

// Select All Toggle
const isAllSelected = computed(() => {
  if (deletedItemsList.value.length === 0) return false;
  return selectedIds.value.length === deletedItemsList.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = deletedItemsList.value.map(item => item.id);
  }
};

// Switch Category
const switchCategory = (categoryKey) => {
  selectedType.value = categoryKey;
  selectedIds.value = [];
  router.get('/dashboard/recycle-bin', {
    type: categoryKey,
    search: searchQuery.value,
    date_from: dateFrom.value,
    date_to: dateTo.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Apply Search Filters
const applyFilters = () => {
  selectedIds.value = [];
  router.get('/dashboard/recycle-bin', {
    type: selectedType.value,
    search: searchQuery.value,
    date_from: dateFrom.value,
    date_to: dateTo.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Reset Filters
const resetFilters = () => {
  searchQuery.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  selectedIds.value = [];
  router.get('/dashboard/recycle-bin', {
    type: selectedType.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Single Item Restore Flow
const openRestoreModal = (id) => {
  activeActionId.value = id;
  RestoreFileModalOpen.value = true;
};

const confirmRestoreHandler = () => {
  if (!activeActionId.value) return;
  router.post('/dashboard/recycle-bin-restore', {
    id: activeActionId.value,
    type: selectedType.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Item Restored",
        detail: "The item has been restored back to its active operational status.",
        life: 3000
      });
    }
  });
};

// Single Item Permanent Delete Flow
const openDeleteModal = (id) => {
  activeActionId.value = id;
  DeleteFileModalOpen.value = true;
};

const confirmDeleteHandler = () => {
  if (!activeActionId.value) return;
  router.get('/dashboard/recycle-bin-delete', {
    id: activeActionId.value,
    type: selectedType.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: "info",
        summary: "Item Permanently Purged",
        detail: "The item and its physical storage files were permanently removed.",
        life: 3000
      });
    }
  });
};

// Batch Restore Flow
const triggerBatchRestore = () => {
  if (selectedIds.value.length === 0) return;
  router.post('/dashboard/recycle-bin/batch-restore', {
    ids: selectedIds.value,
    type: selectedType.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      toast.add({
        severity: "success",
        summary: "Batch Restored",
        detail: "Selected items have been restored successfully.",
        life: 3000
      });
    }
  });
};

// Batch Delete Flow
const triggerBatchDelete = () => {
  if (selectedIds.value.length === 0) return;
  if (!confirm(`Are you sure you want to permanently purge ${selectedIds.value.length} items? This action cannot be undone.`)) return;

  router.post('/dashboard/recycle-bin/batch-delete', {
    ids: selectedIds.value,
    type: selectedType.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      toast.add({
        severity: "info",
        summary: "Batch Purged",
        detail: "Selected items and disk files were permanently destroyed.",
        life: 3000
      });
    }
  });
};

// Empty Recycle Bin Flow
const confirmEmptyBinHandler = (scope) => {
  router.post('/dashboard/recycle-bin/empty', {
    scope: scope
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      toast.add({
        severity: "success",
        summary: "Recycle Bin Emptied",
        detail: "Recycle bin was permanently cleared.",
        life: 3500
      });
    }
  });
};

// Formatters & Resolvers
const formatDate = (date) => {
  return date ? moment(date).format('YYYY-MM-DD HH:mm') : '—';
};

const formatTimeAgo = (date) => {
  return date ? moment(date).fromNow() : '';
};

const resolveItemName = (item) => {
  if (!item) return 'Item';
  switch (selectedType.value) {
    case 'Deleted Users':
      return `${item.first_name || ''} ${item.last_name || ''}`.trim() || item.email;
    case 'Deleted Tasks':
      return item.title || `Task #${item.id}`;
    case 'Deleted Mails':
      return item.subject || `Mail #${item.id}`;
    case 'Deleted Units':
    case 'Deleted Divisions':
    case 'Deleted Departments':
      return item.title || `Unit #${item.id}`;
    case 'Deleted Metadata':
      return item.name || `Field #${item.id}`;
    default: {
      const titleDoc = item.archive_docs?.find(d => d.archive_keys === 'title');
      return titleDoc?.archive_values || item.description || `Archive #${item.id}`;
    }
  }
};

const resolveItemContext = (item) => {
  if (item.department?.title) return `Dept: ${item.department.title}`;
  if (item.division?.title) return `Div: ${item.division.title}`;
  if (item.unit?.title) return `Unit: ${item.unit.title}`;
  return selectedType.value.replace('Deleted ', '');
};

const resolveDeletedBy = (item) => {
  if (item.deleted_by) {
    return `${item.deleted_by.first_name || ''} ${item.deleted_by.last_name || ''}`.trim() || item.deleted_by.email;
  }
  return 'System / Administrator';
};

const getItemIcon = (type, item) => {
  switch (type) {
    case 'Deleted Users': return 'carbon:user';
    case 'Deleted Tasks': return 'carbon:flow-stream';
    case 'Deleted Mails': return 'carbon:email';
    case 'Deleted Units': return 'carbon:network-3';
    case 'Deleted Divisions': return 'carbon:enterprise';
    case 'Deleted Departments': return 'carbon:building';
    case 'Deleted Metadata': return 'carbon:tag';
    default: return 'vscode-icons:file-type-pdf2';
  }
};

const getRetentionBadgeClass = (days) => {
  if (days === undefined) return 'bg-slate-100 text-slate-700';
  if (days <= 5) return 'bg-rose-100 text-rose-700 border border-rose-200';
  if (days <= 14) return 'bg-amber-100 text-amber-700 border border-amber-200';
  return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};
</script>

<style scoped>
:deep(.p-datatable-thead > tr > th) {
  background-color: #f8fafc;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

:deep(.p-datatable-tbody > tr) {
  transition: background-color 0.15s ease;
  border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable-tbody > tr:hover) {
  background-color: #f8fafc !important;
}

:deep(.p-datatable-tbody > tr > td) {
  padding: 0.85rem 1rem;
  vertical-align: middle;
}
</style>
