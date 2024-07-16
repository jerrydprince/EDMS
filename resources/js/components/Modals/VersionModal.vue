<template>
    <Dialog v-model:visible="visible" modal header="Header" :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '85rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #header>
            <div class="flex items-center gap-3 -mb-6">
                <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
                    <!-- <img :src="page.props.base+'/images/image.svg'" alt=""> -->
                    <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.66666 10.0833L10 14.25L18.3333 10.0833M10 1.75L1.66666 5.91667L10 10.0833L18.3333 5.91667L10 1.75Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="mb-1 font-medium text-lg">File Versions</p>
                    <p class="text-gray-500 text-xs">View all file versions</p>
                </div>
            </div>
        </template>
        <section class="-mb-6">
            <Divider class="" />
            <div class="px-6 overflow-y-auto min-h-[40vh]">
                <table class="w-full text-left">
    <thead class=" bg-gray-50">
      <tr class="border text-[#667085]">
        <th class="p-3 pl-5 w-[3%]">
          #
        </th>
        <th class="w-[27%]">Title</th>
        <th class="w-[35%]">Description</th>
        <th class="w-[10%]">Amount</th>
        <th class="w-[15%]">Date</th>
        <th class="w-[10%]">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr class="border" v-for="(file, key) in files">
        <td class="p-3 px-2 pl-5 py-3"> {{ key + 1 }}</td>
        <td class="py-3 px-2">
          <div class="flex items-center font-medium gap-2">
            <div class="p-2 rounded-full bg-gray-200">
              <!-- <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" /> -->
              <img :src="getFileExtension(file)" alt="" style="width: 20px; max-width: none;">
            </div>
            {{ file.title?.archive }}
          </div>
        </td>

        <td class="px-2">{{ truncateText(file.description?.archive) }}</td>
        <td class="py-3 px-2">{{ file.amount?.archive }}</td>
        <td>{{(file.files && file.files[0])? moment(file.files[0].updated_at).format('lll'): ''}}</td>

        <td class="py-3 px-2">
          <div class="flex items-center gap-4">
            <div @click="toggle2($event, file, key)" aria-haspopup="true" aria-controls="overlay_menu">
              <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                <div>
                  <div class="h-full">
                    <Icon icon="majesticons:more-menu-vertical" width="30" />
                  </div>
                  <TieredMenu ref="menu2" id="overlay_menu" :model="menuItems" popup>
                    <template #item="{ item, props, hasSubmenu }">

                      <a class="flex align-location-center" v-bind="props.action">
                        <!-- <span :class="item.icon" /> -->
                        <span v-html="item.icon"></span>
                        <span class="ml-2">{{ item.label }}</span>
                        <!-- <Badge v-if="item.badge" class="ml-auto" :value="item.badge" /> -->
                        <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto text-primary"></i>
                      </a>
                      <div v-if="hasSubmenu" class="submenu">
                        <TieredMenu :model="item.submenuItems" :popup="true"></TieredMenu>
                      </div>
                    </template>
                  </TieredMenu>
                </div>
              </div>
            </div>

          </div>

        </td>
      </tr>
    </tbody>
  </table>
            </div>
            <Divider class="" />
        </section>
        <template #footer>
            <Button class="mr-4" :pt="{
                root: {
                    class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
                },
            }" @click="closeModal" label="Close" severity="secondary" outlined />
        </template>
    </Dialog>
    
</template>
  
<script setup>
import { ref, defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";
import moment from "moment"


const toast = useToast();
const props = defineProps(["modelValue", "selectedArchive"]);
const emit = defineEmits(["update:modelValue", 'previewFile', 'fileVersionDeleted']);
import { usePage } from '@inertiajs/vue3';
import axios from "axios";

const page = usePage()
const menu2 = ref();
const selectedFile = ref([])

const getFileExtension = (file)=>{
    const f_path = file.path? file.path : file?.name

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

const truncateText = (text)=>{
    let max = 100
    if (text && text.length > max) {
        return text.substring(0, max) + '...';
      }else{
        return text
      }
}

const menuItems = ref([
  {
    label: 'Preview',
    icon: ` <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.833344 8.00065C0.833344 8.00065 4.16668 1.33398 10 1.33398C15.8333 1.33398 19.1667 8.00065 19.1667 8.00065C19.1667 8.00065 15.8333 14.6673 10 14.6673C4.16668 14.6673 0.833344 8.00065 0.833344 8.00065Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10 10.5007C11.3807 10.5007 12.5 9.38136 12.5 8.00065C12.5 6.61994 11.3807 5.50065 10 5.50065C8.6193 5.50065 7.50001 6.61994 7.50001 8.00065C7.50001 9.38136 8.6193 10.5007 10 10.5007Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>` 
    ,
    command: () => {
      //togglePreview.value = true;
      emit('previewFile', selectedFile)
    }
  },

  {
    label: 'Restore',
    icon: `<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M10 16.682l5.69 5.685 1.408-1.407-3.283-3.28h10.131c1.147 0 2.19.467 2.943 1.222a4.157 4.157 0 011.225 2.946 4.18 4.18 0 01-4.168 4.168h-5.628V28h5.522c3.387 0 6.16-2.77 6.16-6.157a6.117 6.117 0 00-1.81-4.343 6.143 6.143 0 00-4.35-1.805H13.815l3.283-3.285L15.69 11 10 16.682z" fill="#000000" fill-rule="nonzero"/></svg>
    `,
    command: ()=>{
      restoreFileVersion()
    }
  },
  {
    label: 'Delete',
    icon: `<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g clip-path="url(#clip0_569_12501)">
        <path d="M12.1001 14.9993H13.7668M13.7668 14.9993H27.1001M13.7668 14.9993V26.666C13.7668 27.108 13.9424 27.5319 14.2549 27.8445C14.5675 28.1571 14.9914 28.3327 15.4334 28.3327H23.7667C24.2088 28.3327 24.6327 28.1571 24.9453 27.8445C25.2578 27.5319 25.4334 27.108 25.4334 26.666V14.9993H13.7668ZM16.2668 14.9993V13.3327C16.2668 12.8907 16.4424 12.4667 16.7549 12.1542C17.0675 11.8416 17.4914 11.666 17.9334 11.666H21.2668C21.7088 11.666 22.1327 11.8416 22.4453 12.1542C22.7578 12.4667 22.9334 12.8907 22.9334 13.3327V14.9993M17.9334 19.166V24.166M21.2668 19.166V24.166" stroke="#344054" stroke-width="1.66666" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
        <defs>
        <clipPath id="clip0_569_12501">
        <rect width="20" height="20" fill="white" transform="translate(9.6001 10)"/>
        </clipPath>
        </defs>
        </svg>
            `,
    command: () => {
        deleteFileVersion()
    }
  },
  
]);

const deleteFileVersion = ()=>{
    router.get('/delete-file-version',{id: selectedFile.value.file_version_id},{onSuccess: page=>{
        emit('fileVersionDeleted')
        toast.add({
              severity: "success",
              summary: "File deleted",
              detail: "File deleted successfully",
              life: 3000,
            });
    }})
    // axios.get('/delete-file-version', {params:{id: selectedFile.value.file_version_id}}).then((response)=>{
    //     if(response.data.success){
    //         emit('fileVersionDeleted');
    //     }
    // })
}

const toggle2 = (event, data, index) => {
//   selectedArchiveId.value = data.id;
   selectedFile.value = data
  menu2.value[index].toggle(event);
  //emit('selectedArchive', selectedArchive);
};

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    },
});

const closeModal = () => {
    emit("update:step", 1);
    emit("update:modelValue", false);
};

const getArchiveDocsVale = (data, search) => {

if (data) {
  for (let archive of data) {

    if (archive.archive_keys.toLowerCase() == search) {
      return [{ archive: archive.archive_values, id: archive.id }]
    }
  }
}
}

const formatDate = (inputDate) => {
  var date = new Date(inputDate);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  var day = date.getDate();
  var month = months[date.getMonth()];
  var year = date.getFullYear();

  day = day < 10 ? '0' + day : day;

  var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

  return formattedDate;
}

const restoreFileVersion = () => {
    router.post('/restore-file-version', selectedFile.value, {onSuccess: page =>{
        closeModal()
        toast.add({
              severity: "success",
              summary: "File restoration",
              detail: "File restored successfully",
              life: 3000,
            });
    },
    onError: err =>{
        toast.add({
              severity: "error",
              summary: "File restoration ",
              detail: "File restoration failed",
              life: 3000,
            });
    }
    })
}


const files = computed(() => {
  let newarr = [];

  if (props.selectedArchive.file_version) {

    for (let version of props.selectedArchive.file_version) {
    
        //for(let file of version.versions){
        let file = JSON.parse(version.versions) 

            let arr = {
            name: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0].archive : file?.file_path[0]?.file_name,
            size: file?.file_path[0]?.file_size,
            date: formatDate(file.updated_at),
            uploaded_by: file?.file_path[0]?.updated_by_name,
            //path: (file.file_path && file.file_path.length > 0)? file?.file_path[0]?.thumbnails : file?.file_path[0]?.path,
            path: (file.file_path && file.file_path.length > 0) ? file?.file_path[0]?.path : '',
            files: file?.file_path,
            id: file.id,
            amount: getArchiveDocsVale(file.archive_docs, 'amount') ? getArchiveDocsVale(file.archive_docs, 'amount')[0] : '',
            title: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : file?.file_path[0]?.file_name,
            description: getArchiveDocsVale(file.archive_docs, 'description') ? getArchiveDocsVale(file.archive_docs, 'description')[0] : '',
            archive_docs_id: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : '',
            archive_docs: file.archive_docs,
            department: file.department ? file.department : [],
            division: file.division ? file.division : [],
            unit: file.unit ? file.unit : [],
            file_activity: file.file_activity,
            file_version_id: version.id
        }
        newarr.push(arr);
       // }
    }
  }
  return newarr;
})


</script>
  