<template>
  <div class="absolute w-[100vw] h-[100vh] bg-[#00000026] z-50 flex flex-col items-center">
    <div class="w-[50em] mt-[10vh]">
      <div class="bg-white rounded-lg overflow-hidden mb-5">
        <div class="flex items-center px-5">
          <div class="flex items-center w-full text-gray-400">
            <Icon icon="iconamoon:search-light" width="20" class="pi pi-search" />
            <input type="text" id="search_text" v-model="search_text" class="outline-none w-full p-3 border-0"
              placeholder="Search for documents,folder and branches" />
          </div>
          <button>
            <Icon icon="mdi:times" @click="toggleSearch" />
          </button>
        </div>
        <div class="bg-[#F9FAFB] px-5 border-t py-2 border-solid flex items-center justify-between">
          <div class="flex items-center gap-2">
            <h1>Navigate</h1>
            <button class="p-1 rounded-md" :class="searchActive == 'ASC'? 'bg-slate-200': 'bg-white'">
              <Icon icon="tabler:arrow-up" @click="sortAsc"/>
            </button>
            <button class="p-1 rounded-md" :class="searchActive == 'DESC'? 'bg-slate-200': 'bg-white'">
              <Icon @click="sortDesc" icon="tabler:arrow-down" />
            </button>
          </div>

          <div class="flex items-center gap-2">
            <button @click="toggleSearch">Close</button>
            <button class="bg-white rounded-md px-2 py-0.5 shadow border">
              esc
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white relative h-[70vh] rounded-lg overflow-hidden">
        <div class="flex items-center justify-between w-full border-b px-5 py-3">
          <div class="flex items-center gap-2">
            <div class="relative ">
              <button :class="{ 'bg-primary text-white': filterTypes !== 'Document Types' }"
                class="flex items-center gap-1 rounded-lg px-4 py-1 border"
                @click="(activeButton = 1), (openMenu = !openMenu)">
                <p>{{ filterTypes }}</p>
                <Icon v-if="filterTypes === 'Document Types'" icon="flowbite:angle-down-outline"
                  :class="{ 'rotate-180': activeButton === 1 && openMenu }" />
                <Icon icon="prime:times" width="20" @click="cancelFilter('type')" v-else />

              </button>


              <div class="bg-white left-0 py-2 absolute top-12 w-[200px] shadow-md rounded-md"
                v-if="activeButton === 1 && openMenu" ref="modalRef">

                <div v-for="(item, index) in documentType" :key="index"
                  class="flex items-center gap-3 py-1 hover:bg-gray-50 px-2" @click="SelectOption(item.name)">
                  <!-- <Icon :icon="item.icon" width="25"
                    :class="{ 'text-red-600': item.label === 'PDFs', 'text-purple-600': item.label === 'Photos & Images', 'text-yellow-500': item.label === 'Audio', 'text-purple-700': item.label === 'Video' }" /> -->
                  <h1 class="text-base">{{ item.name }}</h1>
                </div>
              </div>
            </div>

            <div class=" relative">
              <button class="flex  items-center gap-1 rounded-lg px-4 py-1 border"
                :class="{ 'bg-primary text-white': modifiedDate !== 'Date Modified' }"
                @click="(activeButton = 2), (openMenu = !openMenu)">
                <p>{{ modifiedDate }}</p>
                <Icon v-if="modifiedDate === 'Date Modified'" icon="flowbite:angle-down-outline"
                  :class="{ 'rotate-180': activeButton === 2 && openMenu }" />
                <Icon icon="prime:times" width="20" @click="cancelFilter('date')" v-else />

              </button>

              <div class="bg-white py-2 left-0  absolute top-12 w-[200px] shadow-md rounded-md"
                v-if="activeButton === 2 && openMenu" ref="modalRef">
                <div v-for="(item, index) in dateRanges" :key="index" class="hover:bg-gray-50 px-2"
                  @click="SelectOption(item.label)">
                  <div class="flex items-center gap-3 py-2">
                    <h1 class="text-base">{{ item.label }}</h1>
                  </div>
                </div>
                <div class="flex items-center px-2 cursor-pointer hover:bg-gray-50 gap-3 py-2">
                  <h1 class="text-base">Custom</h1>
                </div>
              </div>
            </div>
            <!-- <button class="flex items-center gap-1 rounded-lg px-4 py-1 border">
              <Icon icon="material-symbols-light:list" width="25" />
              <p>List</p>
            </button> -->
          </div>
          <div>
            <Icon icon="tabler:refresh" @click='resetSearch' class=" cursor-pointer" width="30" />
          </div>
        </div>

        <div class="px-5 pt-5 h-[60vh] pb-5 overflow-y-scroll hide_scroll">


          <!-- ================   Recent Searches    ========== -->
          <!-- <div v-if="search_result.recent_searches && search_result.recent_searches.length > 0">
            <div class="flex items-center gap-2">
              <h1>Recent Searches</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ search_result.recent_searches.length }}</p>
            </div>

            <div v-for="(RecentSearch, index) in search_result.recent_searches" :key="index">
              <div class="flex items-center justify-between hover:bg-gray-100 my-2">
                <div class="p-2  ">
                  <h1><Link :href="'/search?search_text='+RecentSearch.query">{{ RecentSearch.query }} </Link></h1>
                </div>
                <Icon icon="mi:delete-alt" width="25" class=" cursor-pointer" @click="deleteFromRecentSearch(RecentSearch.id, index)"/>
              </div>
            </div>

            <div>
              <button class=" text-sm text-primary flex items-center gap-1" v-if="search_result.recent_searches.length > 3">
                Show More
                <Icon icon="flowbite:angle-down-outline"  />
              </button>
            </div>
          </div> -->


          <!-- ==============     Documents    ============= -->
          <div v-if="search_result.archive && search_result.archive.length > 0" class=" mt-5">
            <div class="flex items-center gap-2">
              <h1>Documents</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ search_result.archive.length }}</p>
            </div>

            <div v-for="(document, index) in search_result.archive" :key="index">
              <Link @click="toggleSearch" :href="'/'+getLandingPage()+'/folder-view/'+getUserBranch(document)+'/'+document.folder_id+'?archive_id='+document.id+'&branch_id='+getUserBranchID(document)">
                <div class="flex items-center gap-3 my-2 group">
                  <div class="p-2 rounded-full bg-gray-100">
                    <!-- <Icon :icon="document.icon" width="15 " /> -->
                    <img :src="getFileExtension(document)" width="15" alt="document">
                  </div>
                  <p class="text-sm font-medium">{{getFileName(document.archive_docs)? getFileName(document.archive_docs): (document.file_path[0]?.file_name)? document.file_path[0].file_name:''}}</p>
                 
                  <!-- preview icon -->
                  <svg width="20" @click.prevent="previewFile($event, document)" class=" opacity-0 group-hover:opacity-100" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_1110_19716)">
                    <path d="M0.833496 9.9987C0.833496 9.9987 4.16683 3.33203 10.0002 3.33203C15.8335 3.33203 19.1668 9.9987 19.1668 9.9987C19.1668 9.9987 15.8335 16.6654 10.0002 16.6654C4.16683 16.6654 0.833496 9.9987 0.833496 9.9987Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10.0002 12.4987C11.3809 12.4987 12.5002 11.3794 12.5002 9.9987C12.5002 8.61799 11.3809 7.4987 10.0002 7.4987C8.61945 7.4987 7.50016 8.61799 7.50016 9.9987C7.50016 11.3794 8.61945 12.4987 10.0002 12.4987Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_1110_19716">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                  </svg>

                </div>
              </Link>
            </div>

            <div>
              <button class=" text-sm text-primary flex items-center gap-1" v-if="search_result.archive.length > 3"
                @click="toggleShowAll">
                {{ showAll ? "Show Less " : "Show More" }}
                <Icon icon="flowbite:angle-up-outline" v-if="showAll" />
                <Icon icon="flowbite:angle-down-outline" v-else />
              </button>
            </div>
          </div>


          <!-- ==============     Folders    ============= -->
          <div class=" mt-5" v-if="search_result.folder && search_result.folder.length > 0">
            <div class="flex items-center gap-2">
              <h1>Folders</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ search_result.folder.length }}</p>
            </div>
            <div class="grid grid-cols-3   gap-2">
              <div v-for="folder in search_result.folder" class="rounded-md flex items-center gap-3 hover:bg-gray-100 p-3" :key="i">
                <Link :href="'/'+getLandingPage()+'/folder-view/'+getUserBranch(folder)+'/'+folder.id" @click="toggleSearch">
                <div class="border rounded-md p-2 bg-gray-100 cursor-pointer">
                  <img :src="page.props.base+'/images/folder.svg'" alt="" />
                </div>
              </Link>
                <div>
                  <h1 class="text-base font-medium">{{folder.name}}</h1>
                  <div class="flex items-center gap-3 mt-1">
                    <p>{{ folder.file_count }} Document</p>
                    <div>
                      <!-- <p class=" text-xs"> FLD2103 </p> -->
                    </div>
                  </div>
                </div>
             
              </div>
            </div>
          </div>


          <!-- ==============     Branches    ============= -->
          <div class=" mt-5">
            <div class="flex items-center gap-2" v-if="branches_total > 0">
              <h1>Branches</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ branches_total }}</p>
            </div>

            <div v-if="search_result.department && search_result.department.length > 0">
              <div v-for="(department, index) in search_result.department" :key="index">
              <div class="flex items-center gap-3 my-2">
                <div class="p-2 rounded-full border ">
                  <!-- <Icon :icon="Branche.icon" width="15 " /> -->
                </div>
                <Link :href="'/'+getLandingPage()+'/branches/'+department.id">
                <p class="text-sm font-medium">{{ department.title }} Department</p>
                </Link>
              </div>
            </div>
            </div>

            <div v-if="search_result.division && search_result.division.length > 0">
              <div v-for="(division, index) in search_result.division" :key="index">
              <div class="flex items-center gap-3 my-2">
                <div class="p-2 rounded-full border ">
                  <!-- <Icon :icon="Branche.icon" width="15 " /> -->
                </div>
                <p class="text-sm font-medium">{{ division.title }} Division</p>
              </div>
            </div>
            </div>

           <div v-if="search_result.unit && search_result.unit.length > 0">
            <div v-for="(unit, index) in search_result.unit" :key="index">
              <div class="flex items-center gap-3 my-2">
                <div class="p-2 rounded-full border ">
                  <Icon :icon="Branche.icon" width="15 " />
                </div>
                <p class="text-sm font-medium">{{ unit.title }} Unit</p>
              </div>
            </div>
           </div>

            <div>
              <button class=" text-sm text-primary" v-if="branches_total > 3">
                Show More
              </button>
            </div>
          </div>


        </div>

        <div class=" absolute bottom-0 w-full px-5 py-1 bg-gray-200 text-gray-500">
          <div class=" flex items-center justify-between">
            <!-- <p>Type "/" for tags</p> -->
            <p>{{ total_search }} Results</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <PreviewFile v-if="togglePreview"  :togglePreviewFunction="togglePreviewState" :file="files"/>
</template>

<script setup>
import { ref, computed, defineProps, onMounted, defineEmits, watch, onBeforeMount } from "vue";
import { onClickOutside } from "@vueuse/core";
import  debounce  from 'lodash.debounce';
import { Link, usePage } from "@inertiajs/vue3"
import PreviewFile from "../../components/Modals/branches/PreviewFile.vue";
import axios from "axios";

const activeButton = ref(1);
const date = ref(false);
//const { toggleSearchFunction } = defineProps(["toggleSearchFunction", "archive"]);
const props = defineProps(["toggleSearchFunction"])
const toggleSearch = () => {
  //toggleSearchFunction();
  emit('closeModal')
};

const page = usePage();
const togglePreview = ref(false)

const selectedArchive = ref()

const user_branch = ref();

const query_string = ref(new Date().getTime());
const branches_total = ref(0)

const search_text = ref('');

const emit = defineEmits(['closeModal', 'toggleSearchFunction']);

const openMenu = ref(false);
const modalRef = ref(null);
onClickOutside(modalRef, (event) => {
  openMenu.value = false;
});
const filterTypes = ref("Document Types")
const modifiedDate = ref("Date Modified")
const search_result = ref([])
const total_search = ref(0);
const documentType = ref([])
const searchActive = ref('')

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const getLandingPage = ()=>{
  if(page.props.roles.includes('data entry')){
    return 'edms';
  }else{
    return 'dashboard';
  }
}


const Types = [
  {
    icon: 'circum:filter',
    label: 'All'
  },
  {
    icon: 'mdi:file-pdf-box',
    label: 'PDFs'
  },
  {
    icon: 'ic:baseline-photo',
    label: 'Photos & Images'
  },
  {
    icon: 'vscode-icons:file-type-word',
    label: 'Word Document'
  },
  {
    icon: 'vscode-icons:file-type-excel',
    label: 'Excel'
  },
  {
    icon: 'vscode-icons:file-type-powerpoint',
    label: 'Powerpoint'
  },
  // {
  //   icon: 'ic:baseline-audio-file',
  //   label: 'Audio'
  // },
  // {
  //   icon: 'entypo:video',
  //   label: 'Video'
  // },
  {
    icon: 'ion:folder',
    label: 'Folders'
  },
  {
    icon: 'mdi:database-outline',
    label: 'Branches'
  }
]

const SelectOption = (label) => {
  if (activeButton.value === 1) {

    filterTypes.value = label
    search()
  }
  if (activeButton.value === 2) {
    modifiedDate.value = label
    search()
  }
  openMenu.value = false
}

const deleteFromRecentSearch = (id, index)=>{
  axios.get('/delete-from-recent-search', {params:{id:id}}).then((response)=>{
    if(response.success){
      search_result.value.recent_searches.splice(index, 1);
    }
  })
}

const previewFile = (event, document)=>{

  selectedArchive.value = document
  togglePreview.value = true;
  event.stopPropagation();
  
}

const cancelFilter = (filter) => {
  if (filter === 'type') {

    filterTypes.value = 'Document Types';
  }
  if (filter === 'date') {
    modifiedDate.value = 'Date Modified'
  }
  openMenu.value = false
}

const resetSearch = ()=>{
  Object.keys(search_result.value).forEach(key => {
    delete search_result.value[key];
  });

  total_search.value = 0;
  search_text.value = ''
  document.getElementById('search_text').focus()
}

const getFileName = (file)=>{
  for(let data of file){
    if(data.archive_keys == 'title'){
      return data.archive_values
    }
  }
}

const dateRanges = [
  {
    label: "Today",
  },
  {
    label: "Yesterday",
  },
  {
    label: "Last 7 days",
  },
  {
    label: "Last 30 days",
  },
  {
    label: "Last 90 days",
  }
];

const showAll = ref(false);



const toggleShowAll = () => {
  showAll.value = !showAll.value;
};

const escClose = (event)=>{
  window.addEventListener('keyup', handleKeyUpEvent)
} 

const handleKeyUpEvent = (event)=>{
  if(event.key == 'Escape'){
      toggleSearch()
    }
}

const getFileExtension = (file)=>{
  if(file.file_path && file.file_path.length > 0){
    
    const f_path = file.file_path[0]?.file_name
    if(f_path){
      const parts = f_path.split('.');

    const extension = parts.pop();

    if(extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png'){
      return page.props.base+'/images/image.svg'
    }
    
    if(extension == 'pdf'){
        return page.props.base+'/images/pdf.svg'
    }

    if(extension == 'doc' || extension == 'doctx'){
        return page.props.base+'/images/word.svg'
    }

    if(extension == 'xlx' || extension == 'xlsx'){
        return page.props.base+'/images/excel.svg'
    }
    }
    
  }

}

const getDocumentType = ()=>{
  axios.get('/get-document-type').then((response)=>{
    documentType.value = response.data
  })
}

const getUserBranch = (folder)=>{
  if(folder.unit ){
    return 'unit';
  }
  if(folder.division ){
    return 'division';
  }
  if(folder.department){
    return 'department';
  }
}

const getUserBranchID = (folder)=>{
  if(folder.unit ){
    return folder.unit.id;
  }
  if(folder.division ){
    return folder.division.id;
  }
  if(folder.department){
    return folder.department.id;
  }
}

const search = debounce(()=>{
     axios.get('/search', {params:{file_name: search_text.value, doctype: filterTypes.value, modified:modifiedDate.value, query_string:query_string.value}}).then((response)=>{
      branches_total.value = 0;
      total_search.value = 0;
      search_result.value = response.data;
      //user_branch.value = response.data.user_branch
    })
},500)

watch(search_text,(newval)=>{
  if(newval !== ""){
    search()
    
  }else{
    query_string.value = new Date().getTime();
  }
  
})

watch(search_result, (newVal)=>{
  if(newVal.department && newVal.department.length > 0){
    branches_total.value += newVal.department.length
    total_search.value +=newVal.department.length
  }
  if(newVal.division){
    branches_total.value += newVal.division.length
    total_search.value +=newVal.division.length
  }
  if(newVal.unit){
    branches_total.value += newVal.unit.length
    total_search.value +=newVal.unit.length
  }
  if(newVal.archive){
    total_search.value +=newVal.archive.length
  }
  if(newVal.folder){
    total_search.value +=newVal.folder.length
  }


})

const sortAsc = ()=>{
  if(search_result.value){
      search_result.value.archive.sort((a, b)=>{
        searchActive.value = 'ASC'
        return a.title.localeCompare(b.title, undefined, { numeric: true });
      })
  }
}

const sortDesc = ()=>{
  if(search_result.value){
      search_result.value.archive.sort((a, b)=>{
        searchActive.value = 'DESC';
        return b.title.localeCompare(a.title, undefined, { numeric: true });
      })
  }
}

const formatDate =(inputDate)=> {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

    return formattedDate;
}

const getArchiveDocsVale = (data, search)=>{
   
   if(data){
    for(let archive of data){
       
        if(archive.archive_keys.toLowerCase() == search){
            return [{archive: archive.archive_values, id: archive.id}]
        }
    }
   }
}

const files = computed(()=>{
  let newarr = [];

  if(selectedArchive.value){
    
      let arr = {
        name: selectedArchive.value?.file_path[0]?.file_name,
        size: selectedArchive.value?.file_path[0]?.file_size,
        date: formatDate(selectedArchive.value.updated_at),
        uploaded_by: selectedArchive.value?.file_path[0]?.updated_by_name,
        //path: (file.file_path && file.file_path.length > 0)? file?.file_path[0]?.thumbnails : file?.file_path[0]?.path,
        path: (selectedArchive.value.file_path && selectedArchive.value.file_path.length > 0)? selectedArchive.value?.file_path[0]?.path: '',
        files: selectedArchive.value?.file_path,
        id: selectedArchive.value.id,
        amount: getArchiveDocsVale(selectedArchive.value.archive_docs, 'amount')? getArchiveDocsVale(selectedArchive.value.archive_docs, 'amount')[0]:'',
        title: getArchiveDocsVale(selectedArchive.value.archive_docs, 'title')?getArchiveDocsVale(selectedArchive.value.archive_docs, 'title')[0]: selectedArchive.value?.file_path[0]?.file_name,
        description:getArchiveDocsVale(selectedArchive.value.archive_docs, 'description')? getArchiveDocsVale(selectedArchive.value.archive_docs, 'description')[0]: '',
        archive_docs_id: getArchiveDocsVale(selectedArchive.value.archive_docs, 'title')? getArchiveDocsVale(selectedArchive.value.archive_docs, 'title')[0]:'',
        archive_docs: selectedArchive.value.archive_docs
      }
      //newarr.push(arr);
      return arr
    
  }
  return newarr;
})

// watch(search_text, (newval)=>{
//   if(newval == ""){
//     query_string.value = new Date().getTime();
//   }
// })

onMounted(()=>{
  escClose();
  document.getElementById('search_text').focus();
  getDocumentType()
})

onBeforeMount(()=>{
  window.removeEventListener('keyup', handleKeyUpEvent)
})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>