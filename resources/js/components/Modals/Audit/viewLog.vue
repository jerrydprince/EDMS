<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class=" flex items-start justify-between w-full">
        <div class=" flex items-center gap-3">
          <div class=" p-3 border rounded-full">
            <Icon icon="uil:layers" width="30" />
          </div>
          <div>
            <h1 class=" font-semibold text-3xl">Log #{{ audit.id }}</h1>
          </div>
        </div>

        <div class=" bg-[#F2F4F7] p-2 rounded-full cursor-pointer" @click="closeModal">
          <Icon icon="mdi:times" width="20" />
        </div>
      </div>

    </template>
    <Divider class=" -mt-1" />
    <section class=" px-6 font-medium">
      <div class=" flex items-center gap-3 text-[#344054]">
        <div class=" p-3 bg-gray-100 rounded-full">
          <Icon icon="carbon:time" width="20" />
        </div>
        <p>{{ formatDate(audit.created_at) }}</p>
      </div>
      <div class=" flex items-center gap-3 text-[#344054] mt-4">
        <div class=" p-3 bg-gray-100 rounded-full">
          <Icon icon="lucide:user" width="20" />
        </div>
        <p>{{ audit.properties?.user }}</p>
      </div>
      <div class=" flex items-center gap-3 text-[#344054] mt-4">
        <div class=" p-3 bg-gray-100 rounded-full">
          <Icon icon="tdesign:device" width="20" />
        </div>
        <p>{{ audit.properties?.device }} • <span> {{ audit.properties?.ip }}</span></p>
      </div>
      <div class=" flex items-center gap-3 text-[#344054] mt-4">
        <div class=" p-3 bg-gray-100 rounded-full">
          <Icon icon="fluent:document-footer-24-regular" width="20" />
        </div>
        <p>{{ getDocumentTitle(audit.properties) }}</p>
      </div>

      <div class=" flex items-center gap-3 text-[#344054] mt-4">
        <div class=" p-3 bg-gray-100 rounded-full">
          <Icon icon="mingcute:location-2-line" width="20" />
        </div>
        <div v-if="audit.properties.event == 'login' || audit.properties.event =='logout'" class=" flex items-center flex-wrap text-sm font-semibold gap-2 mt-2">
          <h1>{{ audit.properties.department? audit.properties.department?.title:'' }} </h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{  audit.properties.division? audit.properties.division?.title: '' }}</h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{  audit.properties.unit? audit.properties.unit?.title: '' }} </h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{ audit.properties.document_type?.folder?.name }} </h1>

        </div>
        <div v-else class=" flex items-center flex-wrap text-sm font-semibold gap-2 mt-2">
          <h1>{{ audit.properties.document_type?.department?.title }} </h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{ audit.properties.document_type?.division?.title }}</h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{ audit.properties.document_type?.unit?.title }} </h1>
          <span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
          <h1>{{ audit.properties.document_type?.folder?.name }} </h1>

        </div>
      </div>
    </section>
    <Divider />
    <template #footer>
      <div v-if="audit.properties.document_type && audit.properties.document_type.file_path && audit.properties.document_type.file_path.length > 0">
      <div class=" w-full flex items-center justify-between border rounded-md p-3 "
      v-for="audit in audit.properties.document_type.file_path">
        <div class=" flex items-center gap-3">
          <div class="bg-[#EBF9FF] w-fit p-3 rounded-full">
            <!-- <Icon icon="vscode-icons:file-type-word" width="25" /> -->
            <img :src="getFileURL(audit)" width="25" alt="">
          </div>
          <div class=" text-left">
            <h1 class=" font-semibold">{{audit.file_name}}</h1>
            <p class=" opacity-70">{{ audit.file_size }}</p>
          </div>
        </div>
        <div class=" flex items-center gap-3">
          <!-- <Icon icon="solar:eye-outline" class="cursor-pointer" width="25" @click="filePreview" /> -->
          <Icon icon="pajamas:download" class="cursor-pointer" width="25" @click="downloadFile(audit)" />
        </div>
      </div>
      </div>
    </template>
  </Dialog>
</template>
<PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="props.audit.properties.document_type?.file_path" />

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { usePage } from "@inertiajs/vue3";
import moment from "moment";
import PreviewFile from "../../../components/Modals/branches/PreviewFile.vue";

const toast = useToast();
const props = defineProps(["modelValue", 'audit']);
const emit = defineEmits(["update:modelValue"]);
const page = usePage()
const togglePreview = ref(false);

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const filePreview = ()=>{
  togglePreview.value = true;
}

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const formatDate = (date) => {
  if (date) {
    return moment(date).format('YYYY-MM-DD hh:mm A')
  }
}

const getDocumentTitle = (data) => {
  let doctype = data?.document_type?.archive_docs

  if (doctype) {
    let filtered = doctype.filter(item => item.archive_keys == 'title');
    if (filtered && filtered.length > 0) {

      return filtered.map(item => item.archive_values).join(', ');
    } else {
      return ''
    }
  } else {
    return ''
  }
}

const downloadFile = (file)=>{
  const url = '/storage'+file.path;
      const fileName = file.file_name;
      const link = document.createElement('a');
      link.href = url;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
}

const getFileURL = (file) => {
  if(file.path){
    let path = file.path
    const parts = path.split('.');
    const extension = parts.pop();

    if(extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png'){
      return page.props.base+ '/storage' +file.path
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

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  emit("update:modelValue", false);
  //   toast.add({
  //     severity: "success",
  //     summary: '',
  //     detail: "",
  //     life: 3000,
  //   });
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>