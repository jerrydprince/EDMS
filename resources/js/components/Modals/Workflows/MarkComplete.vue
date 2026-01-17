<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-50' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-50 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mdi:tick" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Submit Task</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <div>
        <h1 class="text-[#344054] font-semibold mt-3">
          Upload Associated Document
        </h1>
        <div class=" p-1 border rounded-xl mt-3">
          <div class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px]">
            <i class="pi pi-cloud-upload py-2 px-3 text-2xl" v-if="files && files.length == 0"></i>
            <div v-if="files && files.length > 0">
              <div v-for="file in files">
                <img :src="getFileURL(file)" class=" max-w-[100px] px-2 py-4 ml-3" alt="File Preview"
                        style="max-width: 200px; max-height: 200px;" />
              </div>
            </div>
            <div class="text-center" v-else>
              <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
              <p class="text-sm leading-7 mb-7 text-[#475467]">
                supports PDF & Word docs
              </p>
            </div>

            <button class="bg-white border-gray-300 border p-3 flex items-center gap-2  rounded-lg relative">
              <Icon icon="ph:folder-light" width="25" />
              <input type="file" multiple @change="handleFileUpload" class="opacity-0 absolute w-full" />Browse Computer
            </button>
          </div>
        </div>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Add Description <small class=" text-gray-500"> (optional)
          </small></label>
        <textarea name="" v-model="form.description" id="" cols="30" rows="5" placeholder="Add description"
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Submit" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>

  <!-- <Progress  v-model="progressModal" /> -->

</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {useForm, router, usePage } from "@inertiajs/vue3";

// import Progress from "../../../components/Modals/Workflows/progress.vue";

const progressModal = ref(false)

const toast = useToast()
const props = defineProps(["modelValue", 'task', 'assignedToMe']);
const emit = defineEmits(["update:modelValue"]);
const files = ref([]);
const form = useForm({
  task_id:null,
  description: "",
  is_revision:0,
  files:null,
  assigned_to: (props.assignedToMe)?props.assignedToMe?.assigned_to: '',
  assigned_by: (props.assignedToMe)?props.assignedToMe?.assigned_by: '',
  task_assignment_id: (props.assignedToMe)? props.assignedToMe.id: null
})
const page = usePage()

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);

};

const handleFileUpload = (event) => {
  files.value = Array.from(event.target.files);
  form.files = Array.from(event.target.files);
}

const submit = () => {
  form.files = files.value;
  form.task_id = props.task.id
  if(form.files && form.files.length ==0){
    return false;
  }
  
  router.post('/dashboard/task-submission',form,{onSuccess:page=>{
    emit("update:modelValue", false);
    form.reset();
    files.value = [];
    toast.add({
      severity: "success",
      summary: 'Task Submitted',
      detail: "Your task as been marked as complete and is under review",
      life: 3000,
    });
  }})
};

const getFileURL = (file) => {
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
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>