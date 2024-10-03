<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="ph:warning" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Report Misconduct</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="flex items-start gap-1">
        <label class="text-[#344054] font-semibold">Select a Ticket</label>
        <span class="text-red-500">*</span>
      </div>
      <select v-model="form.ticket" name="" id="" class="w-full p-2 border rounded-md mt-2 outline-none">
        <option value="Select area">Select area</option>
      </select>

      <div class="my-5">
        <label class="text-[#344054] font-semibold">Describe Complaint</label>
        <textarea v-model="form.complaint" name="" id="" cols="30" rows="3"
          class="w-full border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div>

      <div>
        <h1 class="text-[#344054] font-semibold mt-3">
          Upload supportive document
        </h1>
        <div class=" p-1 border rounded-xl mt-3">
          <div class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px]">
            <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
            <div class="text-center">
              <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
              <p class="text-sm leading-7 mb-7 text-[#475467]">
                supports PDF & Word docs
              </p>
            </div>
            <div>
              <img :src="getFileURL()" width="100" alt="" class="py-3">
            </div>

            <button class="bg-white border-gray-300 border p-3 flex items-center gap-2  rounded-lg relative">
              <Icon icon="ph:folder-light" width="25" />
              <input type="file" @change="handleFileUpload" class="opacity-0 absolute w-full" />Browse Computer
            </button>
          </div>
        </div>
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Report" class="" :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import {useForm, usePage} from "@inertiajs/vue3"
import { useToast } from "primevue/usetoast";

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const page = usePage()
const toast = useToast()
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const getUserId = ()=>{
  let url = page.url;
  const urlParts = url.split('/');
  const userId = urlParts[urlParts.length - 1];
  return userId;
}

const form = useForm({
  'ticket': '',
  'complaint': '',
  'file': null,
  'user_id': getUserId()
})

const closeModal = () => {
  emit("update:modelValue", false);

};

const handleFileUpload = (event) => {
  form.file = event.target.files[0];
}

const getFileURL = () => {
  let file = form.file
  if(form.file){
    console.log(file.type)
    if (file.type) {
    if (file.type == 'image/jpeg' || file.type == 'image/jpeg' || file.type == 'image/png' || file.type == 'image/gif') {
      return URL.createObjectURL(file);
    }

    if (file.type == 'application/pdf') {
      return page.props.base + '/images/pdf.svg'
    }

    if (file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
      return page.props.base + '/images/word.svg'
    }

    if (file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      return page.props.base + '/images/excel.svg'
    }
  } else {
    // If type is not available, manually determine file type based on extension
    const extension = file.name.split('.').pop();
    const mimeTypes = {
      'jpg': 'image/jpeg',
      'jpeg': 'image/jpeg',
      'png': 'image/png',
      'gif': 'image/gif',
      'pdf': 'application/pdf',
      'doc': 'application/msword',
      'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xls': 'application/vnd.ms-excel',
      'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    };

    return URL.createObjectURL(new Blob([file], { type: mimeTypes[extension] || 'application/octet-stream' }));
  }
  }
}

const submit = () => {
  form.post('/dashboard/report-misconduct', {onSuccess: page=>{
    emit("update:modelValue", false);

    toast.add({
      severity: "success",
      summary: 'Success',
      detail: "",
      life: 3000,
    });
  }})
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>