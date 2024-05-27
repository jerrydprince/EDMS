<template>
  <main class="p-4 lg:p-7">
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-2xl md:text-3xl text-gray-800 font-semibold">
        Setup Document Type
      </p>
      <div @click="CreateDocType"
        class="mt-4 md:mt-0 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-1 py-2 px-3 md:px-4 cursor-pointer">
        <Icon icon="ion:add-outline" width="25" />
        <p class="text-sm md:text-base">Add Document Type</p>
      </div>
    </section>
    <section class="mt-4">
      <TabMenu v-model:activeIndex="active" :model="items" :pt="{
        root: { class: 'text-primary' },
        inkbar: { class: 'text-primary' },
        action: ({ props, context, state }) => ({
          class: `${context.index === state.d_activeIndex
              ? 'text-primary border-b-2 border-solid border-primary'
              : ''
            } p-1 pr-0 md:pr-3 md:p-3 pl-1 text-gray-500 -mb-[2px]`,
        }),
      }">
        <template #item="{ item, props }">
          <Link :href="item.link">
          <p v-bind="props.action" class="flex align-items-center gap-1 md:gap-2 text-sm md:text-base">
            <span class="font-normal text-nowrap">{{ item.label }}</span>
            <Icon :icon="item.icon" width="20" />
          </p>
          </Link>
        </template>
      </TabMenu>
      <!-- Document Type -->
      <div class="mt-8 text-sm md:text-base">
        <DataTable v-model:selection="documentName" paginator :rows="10" :value="props.documentTypes" dataKey="id" :pt="{
          table: {
            class: 'w-full border border-border_primary border-solid mb-6',
          },
        }" tableStyle="min-width: 60rem; border-radius: 30px;">
          <Column selectionMode="multiple" headerStyle="width: 3rem; color: #667085;"></Column>
          <Column field="name" header="Document Type" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="metadata_count" header="Number Of Metadata" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="updated_at_formatted" header="Last Updated" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="actions" header="Actions" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="{ data }">
              <div class="flex items-center gap-3">
                <div class="flex cursor-pointer">
                  <div @click="setSingleDocType(data)"
                    class="flex gap-2 items-center pr-4 font-normal h-full border border-gray-300 rounded-lg px-2 py-1">
                    <div>
                      <Icon icon="uiw:setting-o" width="20" />
                    </div>
                    <p class="font-medium">Configure Metadata</p>
                  </div>
                </div>
                <div class="flex">
                  <div @click="toggle($event, data)" aria-haspopup="true" aria-controls="overlay_menu"
                    class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                    <div class="h-full">
                      <Icon icon="majesticons:more-menu-vertical" width="30" />
                    </div>
                    <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true" />
                  </div>
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </section>
    <CreateDocumentTypeDialog v-model="createDocumentType" />
    <ConfigureMetadataModal v-model="configureMetadata" @selectedOptions="SelectedMetadataOptions"
      :doctype="singleDocType" />
    <UpdateDocumentTypeDialog v-model="updateDocumentType"
       :singleDocType="singleDocType" />
  </main>
</template>

<script setup>
import { ref, defineProps, watch, reactive } from "vue";
import CreateDocumentTypeDialog from "../../components/Modals/CreateDocumentTypeDialog.vue";
import UpdateDocumentTypeDialog from "../../components/Modals/UpdateDocumentTypeDialog.vue";
import ConfigureMetadataModal from "../../components/Modals/ConfigureMetadataModal.vue";
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const active = ref(1);
const createDocumentType = ref(false);
const updateDocumentType = ref(false)
const configureMetadata = ref(false);
const selectedMetadata = ref([]);
const props = defineProps(['documentTypes'])
const singleDocType = ref([]);

const menu = ref();
const form = reactive({
  id: ''
})
const menuItems = ref([
  { 
    label: "Edit", 
    icon: "pi pi-pencil" ,
    command: ()=>{
      updateDocumentType.value = true
    }
  },
  { 
    label: "Duplicate", 
    icon: "pi pi-clone",
    command: ()=>{
      router.post('/admin/duplicate-document-type', form)
    }
  },
  { 
    label: "Delete",
     icon: "pi pi-trash", 
     command: ()=>{
      router.get('/admin/delete-document-type', form)
    }
    },
]);

const toggle = (event, data) => {
  menu.value.toggle(event);
  singleDocType.value = data;
  form.id = singleDocType.value.id
};


const documentName = ref([]);

const items = ref([
  {
    label: "Metadata",
    icon: "",
    link: "/admin/metadata-setup",
  },
  {
    label: "Document Type",
    icon: "material-symbols-light:info-outline",
    link: "/admin/setup-document-type",
  },
  {
    label: "File Type",
    icon: "",
    link: "/admin/file-type",
  }
]);

const CreateDocType = () => {
  createDocumentType.value = true;

}

const SelectedMetadataOptions = (val) => {
  selectedMetadata.value = val.value
}

const setSingleDocType = (data) => {
  configureMetadata.value = true
  singleDocType.value = data
}

// watch the selectedmetadata and configure the metadata for the doctype
watch(selectedMetadata, (val) => {
  if (singleDocType.value.id) {
    let data = {
      id: singleDocType.value.id,
      metadata: selectedMetadata.value
    }

    router.post('/admin/configure-meta-data', data, {
      onSuccess: () => {
        //refresh singledocType variable to ensure all is in sync
        let doctype = props.documentTypes.find((val) => {
          return val.id == singleDocType.value.id
        })
        singleDocType.value = doctype
      }
    })
  }
})

//at the point of creating doctype, reset the singledoctype variable
watch(createDocumentType, (val) => {
  singleDocType.value = []
})


const Actions = (data) => {

 console.log(data)
}
</script>
