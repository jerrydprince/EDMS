<template>
    <DataTable v-if="props.files.length > 0 && !props.documentsGridView" v-model:selection="selectedDocument" paginator
    :rows="10" :value="props.files" dataKey="id" :pt="{
      table: {
        class:
          'w-full border border-border_primary border-solid mb-6 mt-2',
      },
    }" tableStyle="min-width: 60rem; border-radius: 30px;">
    <Column selectionMode="multiple" headerStyle="width: 2rem; color: #667085;"></Column>
    <Column field="name" header="File Name" :pt="{
      bodyCell: { class: 'text-gray-700' },
    }" headerStyle="color: #667085; font-weight: 500;">
      <template #body="slotProps">
        <div class="flex items-center font-medium gap-2">
          <div class="p-2 rounded-full bg-gray-200">
            <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
          </div>
          {{ slotProps.data.name }}
        </div>
      </template>
    </Column>
    <Column field="date" header="Date Uploaded" :pt="{
      bodyCell: { class: 'text-gray-500' },
    }" headerStyle="color: #667085; font-weight: 500;">
    </Column>
    <Column field="size" header="File Size" :pt="{
      bodyCell: { class: 'text-gray-500' },
    }" headerStyle="color: #667085; font-weight: 500;">
    </Column>
    <Column field="uploaded_by" header="Uploaded By" :pt="{
      bodyCell: { class: 'text-gray-500' },
    }" headerStyle="color: #667085; font-weight: 500;">
    </Column>

    <Column field="actions" header="Actions" :pt="{
      bodyCell: { class: 'text-gray-700' },
    }" headerStyle="color: #667085; font-weight: 500;">
      <template #body="{ data }">
        <div class="flex items-center gap-4">
          <div @click="toggle2($event, data)" aria-haspopup="true" aria-controls="overlay_menu">
            <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
              <div>
                <div class="h-full">
                  <Icon icon="majesticons:more-menu-vertical" width="30" />
                </div>
                <TieredMenu ref="menu2" id="overlay_menu" :model="menuItems" :popup="true">
                  <template #item="{ item, props, hasSubmenu }">

                    <a class="flex align-location-center" v-bind="props.action">
                      <span :class="item.icon" />
                      <span class="ml-2" @click="handleItemClick(item)">{{ item.label }}</span>
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

          <div class="cursor-pointer" @click="deleteArchive(data)">
            <Icon icon="uiw:delete" width="16" />
          </div>
        </div>
      </template>

      <template>
        <div class="flex items-center gap-4  border border-[red]">
          <div class="flex border ">
            <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg ">



            </div>
          </div>

          <div class="cursor-pointer">
            <Icon icon="uiw:delete" width="16" />
          </div>
        </div>
      </template>
    </Column>
  </DataTable>

  <PreviewFile v-if="togglePreview"  :togglePreviewFunction="togglePreviewState" :file="selectedArchive"/>
</template>

<script setup>
     import { ref, defineProps, watch, defineEmits } from "vue";
     import { router } from '@inertiajs/vue3';
     import PreviewFile from "@/components/Modals/branches/PreviewFile.vue";

    const props = defineProps(['files', 'documentsGridView']);
    const emit = defineEmits(['docs', 'fileRename', 'lock', 'changeFormat','downloadFile', 'selectedArchive', 'branchDetails']);
    const selectedDocument = ref()
    const menu2 = ref();
    const selectedArchiveId = ref();
    const selectedArchive = ref();
    const togglePreview = ref(false);

    const togglePreviewState = () => {
    togglePreview.value = !togglePreview.value;
    };

    const menuItems = ref([
  {
    label: 'Preview',
    icon: 'pi pi-eye',
    command: ()=>{
      togglePreview.value = true;
    }
  },
  {
    label: 'Rename',
    icon: 'pi pi-pencil',
    command: () =>{
     // FileRenameModal.value = true;
     emit('fileRename', true);
    }
  },
  {
    label: 'Download',
    icon: 'pi pi-download',
    command: ()=>{
       downloadFile()
    }
  },
  {
    label: 'Duplicate',
    icon: 'pi pi-clone',
    command: () => {
      router.post('/duplicate-file',{id: selectedArchiveId.value},{onSuccess: page =>{
        console.log(page)
      }})
    }
  },
  {
    label: 'Share',
    icon: 'pi pi-share-alt',
  },
  {
    label: 'Change Format',
  },
  {
    label: 'File Information',
    icon: 'pi pi-info-circle',
    items: [
      {
        label: "Details",
        icon: 'pi pi-info-circle',
        command: ()=>{
          //branchDetailsModal.value = true;
          emit('branchDetails', true);
        }
      },
      {
        label: "Activity",
      },
      {
        label: "File Versions",
      },
      {
        label: "Lock",
        icon: 'pi pi-lock'
      }
    ]
  },
]);

const toggle2 = (event,data) => {
  selectedArchiveId.value = data.id;
  selectedArchive.value = data
  menu2.value.toggle(event);
  emit('selectedArchive',selectedArchive);
};

const handleItemClick = (item) => {

if (item.label === 'Lock') {
  //LockDocumentModal.value = true
  emit('lock',true);
}
if (item.label === 'Change Format') {
  //ChangeFormatModal.value = true
  emit('changeFormat',true);
}
}


const downloadFile = async () => {
  try {
    const response = await axios.get('/download-file', {
      params: { id: selectedArchiveId.value },
      responseType: 'blob'
    });

    // Extract the filename from the Content-Disposition header
    const contentDisposition = response.headers['content-disposition'];
    const fileName = contentDisposition.split('filename=')[1];

    // Determine the content type based on the file extension
    const contentType = fileName.endsWith('.pdf') ? 'application/pdf' :
                        fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') ? 'image/jpeg' :
                        fileName.endsWith('.png') ? 'image/png' :
                        'application/octet-stream'; // Default to binary data

    // Create a blob object from the binary data
    const blob = new Blob([response.data], { type: contentType });

    // Create a URL for the blob object
    const url = window.URL.createObjectURL(blob);

    // Create a link element and simulate a click to trigger the file download
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName); // Set the filename for the downloaded file
    document.body.appendChild(link);
    link.click();

    // Cleanup by revoking the object URL
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading file:', error);
  }
};

const deleteArchive = (data)=>{
  let id = data.id
  router.get('/delete-archive',{id:id, only: ['department']}, {onSuccess:page=>{
    //console.log(page)
  }, preserveScroll: (page) => console.log(page)})
}


    watch(selectedDocument, ()=>{
        emit('docs',selectedDocument);
    })
</script>