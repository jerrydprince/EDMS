<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 max-h-[97vh] hide_scroll rounded-md overflow-y-scroll' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex  bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="ion:arrow-back-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Upload from Computer</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">




      <div>

        <div class=" p-1 border rounded-xl mt-5">
          <div class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px]">
            <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
            <div class="text-center">
              <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
              <p class="text-sm leading-7 mb-7 text-[#475467]">
                supports PDF, Images & Word docs
              </p>
            </div>

            <button class="bg-white border-gray-300 border p-3 flex items-center gap-2  rounded-lg relative">
              <Icon icon="ph:folder-light" width="25" />
              <input type="file" @change="handleFileUpload" multiple class="opacity-0 absolute w-full" />Browse Computer
            </button>
          </div>
        </div>
      </div>

      <div class="mt-2">
        <label for="" >Send as/to</label>
      <select  name="" v-model="medium"  id=""
          class=" bg-gray-50 border border-gray-300 rounded-xl p-2 my-2 outline-none  mt-2 mb-8 w-full ">
          <option value="correspondence">Correspondence</option>
          <option value="via_mail">Send correspondence and mail file</option>
          <option value="finance">F & A Registry</option>
      </select>
      </div>

      <div v-if="files && files.length > 0">
        <div class=" w-full  mt-3 gap-4 flex items-center" v-for="file in files">
          <div class="border p-3 rounded-md w-full">
            <div class="flex justify-between ">
              <div class="flex items-center gap-1 justify-center">
                <img :src="getFileURL(file)" alt="" class="w-7" />
                <div class="">
                  <h1 class="text-[#1D2939] pl-3">{{file.name}}</h1>
                  <div class="flex items-center gap-5">
                    <p class="text-xs leading-7 text-[#667085]" v-if="form.progress">
                      {{ form.progress.loaded / 1024}} KB of {{ formatSize(file.size) }}
                    </p>
                    <span class="flex items-center text-xs gap-1">
                      <img src="../../../assets/images/loading.svg" alt="" v-if="form.progress" />
                      Uploading...
                    </span>
                  </div>
                </div>
              </div>
              <div>
                <!-- <i class="pi pi-times-circle cursor-pointer text-[#1D2939]"></i> -->
              </div>
            </div>
            <!-- ========== Progress bar  ========== -->

            <div class="w-full h-2 bg-slate-200 mt-2 rounded-md overflow-hidden">
              <!-- <div class="w-[50%] h-[100%] bg-primary"></div> -->
              <svg v-if="form.progress" class=" absolute top-1 right-1 cursor-pointer h-[18px] w-[18px]" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 6.75L6.75 11.25M6.75 6.75L11.25 11.25M16.5 9C16.5 13.1421 13.1421 16.5 9 16.5C4.85786 16.5 1.5 13.1421 1.5 9C1.5 4.85786 4.85786 1.5 9 1.5C13.1421 1.5 16.5 4.85786 16.5 9Z" stroke="#475467" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
            </div>
          </div>


        </div>

        <!-- <div class=" w-full    mt-3 gap-4 flex items-center">
          <div class="border p-3 rounded-md w-full">
            <div class="flex justify-between ">
              <div class="flex items-center gap-1 justify-center">
                <img src="../../../assets/images/file.svg" alt="" class="w-7" />
                <div class="">
                  <h1 class="text-[#1D2939]">Dmiye.pdf</h1>
                  <div class="flex items-center gap-5">
                    <p class="text-xs leading-7 text-[#667085]">
                      60 KB of 120 KB.
                    </p>
                    <span class="flex items-center text-xs gap-1">
                      <img src="../../../assets/images/complete.svg" alt="" />
                      Completed
                    </span>
                  </div>
                </div>
              </div>
              <div>
                <Icon icon="fluent:delete-48-regular" />
              </div>
            </div>
            

          </div>


        </div> -->
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 bg-white px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />

        <Button v-if="!loading" label="Next" class="" :pt="{
    root: {
      class:
        'py-2 bg-[#000002ab]  px-10 border-0 text-white font-light hover:bg-[#000002ab]/70 rounded-lg',
    },
  }" @click="loading = true" autofocus />


        <Button v-else label="Send" class="" :pt="{
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
import { useToast } from "primevue/usetoast";
import {useForm, router,usePage} from '@inertiajs/vue3'

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue",'update']);
const loading = ref(true)
const files = ref()
const page = usePage()
const medium = ref("correspondence")
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const form = useForm({
  file: null
})

const closeModal = () => {
  emit("update:modelValue", false);

};

const handleFileUpload = (event) =>{
    files.value = Array.from(event.target.files);
    //uploadFiles()
}

const getFileURL = (file) =>{
      if (file.type) {
        if(file.type == 'image/jpeg' || file.type == 'image/jpeg' || file.type == 'image/png' || file.type == 'image/gif'){
            return URL.createObjectURL(file); 
        }
       
        if(file.type == 'application/pdf'){
            return page.props.base+'/images/pdf.svg' 
        }

        if(file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
            return page.props.base+'/images/word.svg'
        }

        if(file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            return page.props.base+'/images/excel.svg'
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

const submit = () => {
  if(medium.value == 'correspondence' || medium.value == 'via_mail'){
    router.post('/dashboard/correspondence',{files: files.value, medium: medium.value}, {onSuccess:page=>{
      emit("update:modelValue", false);
      emit('update')
      files.value = null
      medium.value =''
      loading.value = false
      toast.add({
        severity: "success",
        summary: '',
        detail: "",
        life: 3000,
      });
    }})
  
  }else if(medium.value == 'finance'){
    // router.post('/finance-and-accounts/set-registry-files',{files: files.value, medium: medium.value}, {onSuccess:page=>{
    //   emit("update:modelValue", false);
    //   emit('update')
    //   files.value = null
    //   medium.value =''
    //   loading.value = false
    //   toast.add({
    //     severity: "success",
    //     summary: '',
    //     detail: "",
    //     life: 3000,
    //   });
    // }})
  }
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>