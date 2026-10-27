<template>
  <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-2xs">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500 tracking-wider">
        <tr>
          <th class="py-3.5 px-4 w-12 text-center">#</th>
          <th class="py-3.5 px-4">Document Title</th>
          <th class="py-3.5 px-4">
            {{ props.activeTab === 'shared_by_me' ? 'Recipient (Shared To)' : 'Shared By' }}
          </th>
          <th class="py-3.5 px-4">Department / Directorate</th>
          <th class="py-3.5 px-4">Date Shared</th>
          <th class="py-3.5 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="(file, key) in filteredFiles" :key="file.id || key" class="hover:bg-gray-50/80 transition-colors group">
          <td class="py-3.5 px-4 text-center text-xs text-gray-400 font-mono">
            {{ continuousPageNumbering(key + 1) }}
          </td>

          <!-- Document Title & Icon -->
          <td class="py-3.5 px-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border border-gray-100" :class="getFileIconMeta(file).bg">
                <Icon :icon="getFileIconMeta(file).icon" class="w-5 h-5" />
              </div>
              <div class="min-w-0 max-w-xs xl:max-w-md">
                <p 
                  class="font-semibold text-gray-900 truncate hover:text-primary transition-colors cursor-pointer"
                  @click="handlePreview(file)"
                  :title="file.name || file.title || 'Untitled Document'"
                >
                  {{ file.name || file.title || 'Untitled Document' }}
                </p>
                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                  <span v-if="file.size" class="font-mono text-gray-400">{{ formatFileSize(file.size) }}</span>
                  <span v-if="file.size && (file.description?.archive || file.description)" class="text-gray-300">•</span>
                  <span v-if="file.description?.archive || file.description" class="truncate max-w-[220px]" :title="file.description?.archive || file.description">
                    {{ truncateText(file.description?.archive || file.description) }}
                  </span>
                </div>
              </div>
            </div>
          </td>

          <!-- Recipient or Sender -->
          <td class="py-3.5 px-4">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 flex items-center justify-center text-xs font-bold uppercase shrink-0">
                {{ getUserInitials(file) }}
              </div>
              <div class="min-w-0">
                <p class="font-medium text-gray-800 text-xs truncate max-w-[150px]">{{ getUserDisplayName(file) }}</p>
                <p class="text-gray-400 text-[11px] truncate max-w-[150px]">{{ getUserEmail(file) }}</p>
              </div>
            </div>
          </td>

          <!-- Department / Directorate -->
          <td class="py-3.5 px-4 text-xs">
            <span v-if="file.department?.title || file.department?.name" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200/50">
              {{ file.department?.title || file.department?.name }}
            </span>
            <span v-else class="text-gray-400 italic">Central Registry</span>
          </td>

          <!-- Date Shared -->
          <td class="py-3.5 px-4 text-xs text-gray-600 whitespace-nowrap">
            <div class="flex items-center gap-1.5 text-gray-500">
              <Icon icon="solar:calendar-outline" class="w-3.5 h-3.5 text-gray-400" />
              <span>{{ file.share_info?.shared_at || file.date || 'Recent' }}</span>
            </div>
          </td>

          <!-- Actions -->
          <td class="py-3.5 px-4 text-right">
            <div class="flex items-center justify-end gap-1">
              <button
                type="button"
                @click="handlePreview(file)"
                class="p-2 text-gray-500 hover:text-primary hover:bg-blue-50 rounded-lg transition-colors"
                title="Preview Document"
              >
                <Icon icon="solar:eye-outline" class="w-4 h-4" />
              </button>

              <button
                type="button"
                @click="handleDownload(file)"
                class="p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                title="Download Document"
              >
                <Icon icon="solar:download-minimalistic-outline" class="w-4 h-4" />
              </button>

              <div class="relative">
                <button
                  type="button"
                  @click="toggleRowMenu($event, file, key)"
                  class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                  title="More Options"
                >
                  <Icon icon="majesticons:more-menu-vertical" class="w-4 h-4" />
                </button>
                <TieredMenu ref="menuRefs" id="action_menu" :model="menuItems" popup>
                  <template #item="{ item, props: itemProps, hasSubmenu }">
                    <a v-bind="itemProps.action" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 cursor-pointer" @click="handleItemClick(item)">
                      <span v-if="item.icon" v-html="item.icon"></span>
                      <Icon v-else-if="item.iconName" :icon="item.iconName" class="w-4 h-4 text-gray-500" />
                      <span>{{ item.label }}</span>
                      <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto text-gray-400"></i>
                    </a>
                  </template>
                </TieredMenu>
              </div>

              <button
                type="button"
                @click="deleteArchive(file)"
                class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                title="Remove Share"
              >
                <Icon icon="solar:trash-bin-trash-outline" class="w-4 h-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-4">
    <pagination :archives="props.shared || props.archives" />
  </div>

  <!-- Modal Preview -->
  <PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="selectedArchive" />
</template>

<script setup>
import Swal from 'sweetalert2';
import { ref, defineProps, defineEmits, computed } from "vue";
import { router, usePage } from '@inertiajs/vue3';
import PreviewFile from "../../../components/Modals/branches/PreviewFile.vue";
import pagination from "./pagination.vue";

const props = defineProps([
  'files',
  'documentsGridView',
  'archives',
  'shared',
  'search',
  'sort',
  'activeTab',
  'branch_type',
  'branch_id',
  'currentPage',
  'perPage'
]);

const emit = defineEmits([
  'docs',
  'fileRename',
  'lock',
  'changeFormat',
  'downloadFile',
  'selectedArchive',
  'branchDetails',
  'editMetadata',
  'addMetadata',
  'fileActivity',
  'fileVersion',
  'lockDocs',
  'share',
  'folder_id'
]);

const page = usePage();
const menuRefs = ref([]);
const selectedArchive = ref(null);
const togglePreview = ref(false);

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const handlePreview = (file) => {
  selectedArchive.value = file;
  togglePreview.value = true;
};

const handleDownload = (file) => {
  window.location.href = (page.props.base || '') + '/download-file?id=' + file.id;
};

const continuousPageNumbering = (number) => {
  if (props.currentPage && props.perPage) {
    return (props.currentPage - 1) * props.perPage + number;
  }
  return number;
};

const truncateText = (text) => {
  if (!text) return '';
  const max = 75;
  return text.length > max ? text.substring(0, max) + '...' : text;
};

const formatFileSize = (bytes) => {
  if (!bytes || isNaN(bytes)) return '';
  const num = Number(bytes);
  if (num < 1024) return num + ' B';
  if (num < 1048576) return (num / 1024).toFixed(1) + ' KB';
  return (num / 1048576).toFixed(1) + ' MB';
};

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
  if (props.activeTab === 'shared_by_me') {
    const user = file.share_info?.shared_user;
    if (user) return `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.email;
    return 'Recipient';
  }
  const user = file.share_info?.sharing_user;
  if (user) return `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.email;
  return file.uploaded_by || 'Sender';
};

const getUserEmail = (file) => {
  if (props.activeTab === 'shared_by_me') {
    return file.share_info?.shared_user?.email || 'External';
  }
  return file.share_info?.sharing_user?.email || 'Authorized Officer';
};

const getUserInitials = (file) => {
  const name = getUserDisplayName(file);
  const parts = name.split(' ').filter(Boolean);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase() || 'SH';
};

const menuItems = ref([
  {
    label: 'Preview',
    iconName: 'solar:eye-outline',
    command: () => {
      togglePreview.value = true;
    }
  },
  {
    label: 'Download',
    iconName: 'solar:download-minimalistic-outline',
    command: () => {
      if (selectedArchive.value) {
        handleDownload(selectedArchive.value);
      }
    }
  },
  {
    label: 'Share with Others',
    iconName: 'solar:share-outline',
    command: () => {
      emit('selectedArchive', { value: selectedArchive.value });
      emit('share');
    }
  },
  {
    label: 'File Details',
    iconName: 'solar:info-circle-outline',
    command: () => {
      emit('selectedArchive', { value: selectedArchive.value });
      emit('branchDetails', true);
    }
  },
  {
    label: 'Version History',
    iconName: 'solar:history-outline',
    command: () => {
      emit('selectedArchive', { value: selectedArchive.value });
      emit('fileVersion', true);
    }
  },
  {
    label: 'Activity Log',
    iconName: 'solar:checklist-outline',
    command: () => {
      emit('selectedArchive', { value: selectedArchive.value });
      emit('fileActivity', true);
    }
  }
]);

const toggleRowMenu = (event, file, index) => {
  selectedArchive.value = file;
  emit('selectedArchive', { value: file });
  if (menuRefs.value && menuRefs.value[index]) {
    menuRefs.value[index].toggle(event);
  }
};

const handleItemClick = (item) => {
  if (item.command) {
    item.command();
  }
};

const deleteArchive = (file) => {
  Swal.fire({
    title: 'Remove Share?',
    text: `Are you sure you want to remove "${file.name || 'this document'}" from shared files?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e11d48',
    confirmButtonText: 'Yes, remove',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.get('/delete-archive', { id: file.id }, {
        preserveScroll: true
      });
    }
  });
};

const filteredFiles = computed(() => {
  let list = [...(props.files || [])];
  
  if (props.search && props.search.trim() !== '') {
    const s = props.search.trim().toLowerCase();
    list = list.filter(f => {
      const name = (f.name || f.title || '').toLowerCase();
      const desc = (f.description?.archive || f.description || '').toLowerCase();
      const user = getUserDisplayName(f).toLowerCase();
      return name.includes(s) || desc.includes(s) || user.includes(s);
    });
  }

  if (props.sort === 'asc') {
    list.sort((a, b) => (a.name || a.title || '').localeCompare(b.name || b.title || ''));
  } else if (props.sort === 'desc') {
    list.sort((a, b) => (b.name || b.title || '').localeCompare(a.name || a.title || ''));
  } else if (props.sort === 'date_desc') {
    list.sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));
  } else if (props.sort === 'date_asc') {
    list.sort((a, b) => new Date(a.date || 0) - new Date(b.date || 0));
  }

  return list;
});
</script>
