<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        root: { class: 'bg-white px-5 rounded-md' },
        content: { class: 'px-0  pb-6' },
        header: { class: ' m-5' },
        footer: {class: 'bg-slate-100'}
      }"
      :style="{ width: '35rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
      <template #header>
       
      </template>
      <section class="bg-white mx-2 rounded-md p-5 flex flex-col items-center justify-center">
       <img src="../../../assets/images/Scan-Icon.svg" alt="">
       <h1 class="  text-[24px] font-semibold">Upload Physical Documents</h1>
       <p class=" text-[12px] text-gray-400 mt-5">Easily upload or scan documents to automatically send to AGF Mail.</p>

       <div class=" flex items-center gap-5 text-[14px] mt-5">
    <button    class="flex gap-2 my-3 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
              <Icon icon="tabler:line-scan" width="20" />
              <p>Scan Document</p>
            </button>
           
            <button class="flex gap-2 my-3 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"  @click="toggleUploadModal('computer')" >
              <Icon icon="material-symbols:upload" width="20"/>
              
              <p>Upload from Computer</p>
            </button>
           
   </div>
      </section>
      <template #footer>
     
      </template>
    </Dialog>
    <ComputerUpload  v-model="uploadComputerModal"/>
  </template>
    
    <script setup>
  import { defineEmits, defineProps, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";

 import ComputerUpload from "../../../components/Modals/RecordManager/ComputerUpload.vue";
 const uploadComputerModal = ref(false)
  const props = defineProps(["modelValue"]);

  const emit = defineEmits(["update:modelValue"]);
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
const scanModal = ref(false)
  const toggleUploadModal = (event)=>{
    closeModal()

    if (event=== 'computer') {
        uploadComputerModal.value = true  
    }
    else{
        scanModal.value = true
    }
   
  }
  
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