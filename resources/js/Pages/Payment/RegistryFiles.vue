<template>
  <section class="">
    <section class="flex md:flex-row flex-col md:items-center gap-x-5 gap-y-3 pt-8 md:pt-10 pb-4">
      <div class="flex items-center border p-2 rounded-lg gap-2 text-sm shadow-sm">
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <input
          type="text"
          v-model="searchValue"
          @input="handleSearch"
          placeholder="Search file"
          class="w-96 h-7 border-0 outline-none text-[#98A2B3]"
        />
      </div>
      <section class="flex items-center gap-x-5">
        <div class="bg-gray-100 flex items-center rounded-lg p-1 shadow-sm">
          <button 
            @click="setFileTypeFilter('all')"
            :class="[
              'rounded-lg py-2 px-8',
              fileTypeFilter === 'all' 
                ? 'bg-white shadow-md border-gray-300' 
                : ''
            ]"
          >
            <p>All</p>
          </button>
          <div class="relative">
            <button
              @click="showTypeDropdown = !showTypeDropdown"
              class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-6"
            >
              <p>Type</p>
              <Icon icon="icon-park-outline:down" width="16" />
            </button>
            <div 
              v-if="showTypeDropdown"
              class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-[120px]"
            >
              <button 
                v-for="type in fileTypes"
                :key="type"
                @click="setFileTypeFilter(type)"
                class="block w-full text-left px-3 py-2 hover:bg-gray-50 first:rounded-t-lg last:rounded-b-lg"
              >
                {{ type.toUpperCase() }}
              </button>
            </div>
          </div>
        </div>
        <button
          @click="toggleSort"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-3 px-4 shadow-sm"
        >
          <Icon 
            :icon="sortOrder === 'asc' ? 'ion:arrow-up' : 'ion:arrow-down'" 
            width="20" 
          />
          <p>Name</p>
        </button>
      </section>
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-20">
      <p class="text-red-600 mb-4">{{ error }}</p>
      <button 
        @click="getRegistryFiles" 
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        Retry
      </button>
    </div>

    <div v-else class="mt-4 md:mt-10 rounded-xl">
      <div class="overflow-x-scroll lg:overflow-hidden">
        <table class="min-w-max w-full border-b border-gray-200 text-gray-500">
          <thead class="bg-gray-50">
            <tr class="text-left">
              <th class="py-3 pl-5">
                <Checkbox
                  v-model="selectAll"
                  @update:modelValue="toggleAll"
                  class="h-5 w-5"
                  value="true"
                />
              </th>
              <th class="font-medium">File name</th>
              <th class="font-medium">Date Uploaded</th>
              <th class="font-medium">File Size</th>
              <th class="font-medium">Uploaded By</th>
              <th class="font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in registryFiles"
              :key="item.id || index"
              class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm"
            >
              <td class="align-middle pl-5">
                <div class="flex items-center gap-2">
                  <Checkbox v-model="checkedItems[index]" value="true" class="h-5 w-5" />
                </div>
              </td>

              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-gray-50">
                      <Icon
                        :icon="getFileIcon(item.file_name)"
                        width="25"
                      />
                    </div>
                    <p class="text-gray-900 text-base">{{ getFileName(item?.file_name) }}</p>
                  </div>
                </div>
              </td>

              <td class="align-middle">
                <p>{{ formatDate(item.created_at) }}</p>
              </td>
              <td class="align-middle">
                <p>{{ formatFileSize(item?.size) }}</p>
              </td>
              <td class="align-middle">
                <p>{{ item.uploaded_by_name }}</p>
              </td>
              <td class="align-middle">
                <div>
                  <Icon icon="ph:eye" width="20" @click="filePreview(item)" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="registryFiles.length === 0 && !loading" class="text-center py-16 px-4 bg-white/70 rounded-2xl border border-dashed border-gray-200 my-4 max-w-xl mx-auto shadow-2xs">
        <div class="w-14 h-14 mx-auto mb-3.5 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-inner">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-1">
          {{ searchValue ? 'No Matching Registry Files' : 'No Payment Requisitions in Queue' }}
        </h3>
        <p class="text-xs md:text-sm text-gray-500 max-w-md mx-auto leading-relaxed">
          {{ searchValue 
            ? 'No registry files match the filter criteria. Clear search or change filters.' 
            : 'All incoming treasury files from central registry have been processed or moved to active payment batches.' }}
        </p>
      </div>

      <div
        class="absolute bottom-[5%] p-2 rounded-xl flex items-center left-[5%] md:left-[45%] bg-[#052B0A] justify-between"
        v-if="checkedCount"
      >
        <div class="flex items-center gap-2 text-white text-sm ml-3">
          <p class="">
            {{ checkedCount }}
            selected
          </p>
        </div>
        <button
          @click="initiatePayment"
          class="ml-16 bg-white text-[#052B0A] text-xs px-4 py-3 rounded-md flex items-center gap-2"
        >
          <p>Initiate Payment</p>
          <Icon icon="ri:exchange-funds-fill" width="18" />
        </button>
        <button class="cursor-pointer text-white px-3" @click="clearSelection">
          <Icon icon="mdi:times" />
        </button>
      </div>
    </div>
    
    <!-- <div class="mt-5">
      
      <div class="flex justify-center items-center gap-4">
        <button 
          @click="currentPage > 1 && (currentPage--)"
          :disabled="currentPage <= 1"
          class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        
        <span class="text-sm text-gray-600">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <button 
          @click="currentPage < totalPages && (currentPage++)"
          :disabled="currentPage >= totalPages"
          class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div> -->
    <pagination class="my-4"
        :totalItems="totalItems"
        :itemsPerPage="itemsPerPage"
        v-model="currentPage"
        @setPage="(page) => currentPage = page"
      />
  </section>
  <InitiatePayment v-model="InitiatePaymentModal" :payment="payment" @removeF="removeF" @removeAllFiles="removeAllFiles" :selectedFiles="getSelectedFiles" />
  <PreviewFinanceFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="selectedFile" />
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, defineProps } from "vue";
import InitiatePayment from "../../components/Modals/Payment/InitiatePayment.vue"
import { router } from "@inertiajs/vue3";
import { registry } from "chart.js";
import PreviewFinanceFile from "../../components/Modals/branches/PreviewFinanceFile.vue";
import pagination from "../../reusables/pagination.vue";

const searchValue = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const InitiatePaymentModal = ref(false);
const registryFiles = ref([])
const togglePreview = ref(false);
const selectedFile = ref()
const loading = ref(false);
const error = ref("");
const sortOrder = ref("desc");
const fileTypeFilter = ref("all");
const showTypeDropdown = ref(false);
const props = defineProps(["payment"]);
const totalItems = ref(0);


const fileTypes = ["pdf", "docx", "ppt", "xlsx", "jpg", "jpeg", "png"];

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const pageStart = ref(1);
const pageEnd = ref(1);

const filePreview = (file) => {
  console.log(file)
  selectedFile.value = file?.path
  togglePreview.value = true;
}

const initiatePayment = ()=>{
  console.log(getSelectedIndexes())
  InitiatePaymentModal.value = true;
}

// Filter and sort files based on search and type
const filteredFiles = computed(() => {
  let filtered = registryFiles.value;

  // Apply search filter
  if (searchValue.value.trim()) {
    const search = searchValue.value.toLowerCase();
    filtered = filtered.filter(file =>
      getFileName(file.file_name).toLowerCase().includes(search) ||
      file.uploaded_by_name?.toLowerCase().includes(search)
    );
  }

  // Apply file type filter
  if (fileTypeFilter.value !== "all") {
    filtered = filtered.filter(file =>
      getExtension(file.file_name).toLowerCase() === fileTypeFilter.value.toLowerCase()
    );
  }

  // Apply sorting
  if (sortOrder.value) {
    // Apply sorting only when filter button is clicked
    filtered = [...filtered].sort((a, b) => {
      const nameA = getFileName(a.file_name).toLowerCase();
      const nameB = getFileName(b.file_name).toLowerCase();
      if (sortOrder.value === "asc") {
        return nameA.localeCompare(nameB);
      } else {
        return nameB.localeCompare(nameA);
      }
    });
  }


  return filtered;
});

// Paginate the filtered files
const paginatedFiles = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  
  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return filteredFiles.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
  return Math.ceil(filteredFiles.value.length / itemsPerPage);
});

//const totalItems = computed(() => filteredFiles.value.length);

const checkedItems = ref([]);
const selectAll = ref(false);

const toggleAll = () => {
  const newState = selectAll.value;
  checkedItems.value = checkedItems.value.map(() => newState);
};

// Checked files Logic
const checkedCount = computed(() => {
  return checkedItems.value.filter((checked) => checked.length).length;
});

const clearSelection = () => {
  checkedItems.value = [];
  selectAll.value = false;
};

const toggleSort = () => {
  if(sortOrder.value === "") {
    sortOrder.value = "asc";
  } else if (sortOrder.value === "asc") {
    sortOrder.value = "desc";
  } else {
    sortOrder.value = "asc";
  }
  //sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
};

const setFileTypeFilter = (type) => {
  fileTypeFilter.value = type;
  showTypeDropdown.value = false;
  currentPage.value = 1; // Reset to first page
};

const handleSearch = () => {
  currentPage.value = 1; // Reset to first page on search
};

const getRegistryFiles = async () => {
  loading.value = false;
  error.value = "";
  
  try {
    const response = await axios.get('/finance-and-accounts/get-registry-files?page=' + currentPage.value+"&search="+searchValue.value);
    registryFiles.value = response.data.data || [];
    currentPage.value = response.data.current_page
    itemsPerPage.value = response.data.per_page
    totalItems.value = response.data.total
   
    // Initialize checked items array
    checkedItems.value = new Array(registryFiles.value.length).fill(false);
  } catch (err) {
    error.value = "Failed to load files. Please try again.";
    console.error("Error fetching registry files:", err);
  } finally {
    loading.value = false;
  }
}

const removeF = (file) => {
 let index = filteredFiles.value.findIndex(f => file.id ==f.id);
  if (index !== -1) {
    checkedItems.value[index] = false;
    selectAll.value = false;
  }
};
const removeAllFiles = () => {
  //remove all selectedIndexes and selectAll
  let indexes = getSelectedIndexes();
  if (indexes.length > 0) {
    indexes.forEach(index => {
      checkedItems.value[index] = false;
    });
    selectAll.value = false;
  }
};


const getExtension = (file_name) => {
  if (!file_name) return "";
  let file_arr = file_name.split(".");
  return file_arr.pop();
}

const getFileName = (file_name) => {
  if (!file_name) return "Unknown file";
  let file_arr = file_name.split('/')
  return file_arr.pop()
}

const getFileIcon = (fileName) => {
  const extension = getExtension(fileName)?.toLowerCase();
  
  switch (extension) {
    case "pdf":
      return "vscode-icons:file-type-pdf2";
    case "docx":
    case "doc":
      return "vscode-icons:file-type-word";
    case "xlsx":
    case "xls":
      return "vscode-icons:file-type-excel";
    case "ppt":
    case "pptx":
      return "vscode-icons:file-type-powerpoint";
    case "jpg":
    case "jpeg":
      return "vscode-icons:file-type-image";
    case "png":
      return "vscode-icons:file-type-image";
    default:
      return "material-icon-theme:pdf";
  }
};

const formatDate = (createdAt) => {
  if (!createdAt) return "Unknown";
  
  try {
    const date = new Date(createdAt);
    const formatted = date.toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "short",
      year: "2-digit"
    });
    return formatted;
  } catch {
    return "Invalid date";
  }
}

const formatFileSize = (bytes) => {
  if (!bytes || bytes === 0) return "0 B";
  if (typeof bytes === 'string') return bytes; // If already formatted
  
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return Math.round((bytes / Math.pow(1024, i)) * 100) / 100 + " " + sizes[i];
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showTypeDropdown.value = false;
  }
};

//get index of all the selected
const getSelectedIndexes = () => {
  return checkedItems.value
    .map((item, index) => item.length > 0 ? index : null)
    .filter(index => index !== null);
};

//get the user selected files
const getSelectedFiles = computed(()=>{
  let indexes = getSelectedIndexes();
  if(indexes.length > 0){
    return filteredFiles.value.filter((_, index)=> indexes.includes(index))
  }else{
    return []
  }
})


onMounted(() => {
  getRegistryFiles()
  loading.value = true;
  document.addEventListener('click', handleClickOutside);
})


// Watch for changes that should reset pagination
watch([searchValue, fileTypeFilter], () => {
  if (searchValue.value.trim()) {
    clearTimeout(window.__searchDebounce__);
    window.__searchDebounce__ = setTimeout(() => {
      getRegistryFiles();
    }, 400);
  } else {
    getRegistryFiles();
  }
  
});

watch(currentPage, (newPage) => {
  getRegistryFiles();
});

// Update checked items array when files change
watch(registryFiles, (newFiles) => {
  checkedItems.value = new Array(newFiles.length).fill(false);
  selectAll.value = false;

  if (!props.payment || !Array.isArray(props.payment.files)) return;

  // Find indexes of files in filteredFiles that match payment.files by id
  const matchedIndexes = filteredFiles.value
      .map((file, idx) => props.payment.files.some(pf => pf.id === file.id) ? idx : null)
      .filter(idx => idx !== null);

    // Set checkedItems for matched indexes
    checkedItems.value = filteredFiles.value.map((_, idx) => matchedIndexes.includes(idx) ? ["true"] : false);
});

watch(checkedItems, (neVal)=>{
  // if(checkedCount == 0){
  //   InitiatePaymentModal.value = false
  // }else{
  //   InitiatePaymentModal.value = true;
  // }
},{
  deep: true
})

</script>
