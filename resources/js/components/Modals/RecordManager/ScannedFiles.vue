<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        root: { class: 'bg-slate-100 px-5  rounded-md ' },
        content: { class: 'px-0  pb-6' },
        header: { class: ' m-5' },
        footer: {class: 'bg-slate-100'}
      }"
      :style="{ width: '32rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
      <template #header>

        <div v-if="activePage ===1">
        <div class="flex  bg-slate-100 items-start justify-between w-full">
          <div class="flex items-center w-full justify-between  gap-3">
            <div>
              <h1 class="text-[24px] font-medium text-gray-900">Scanned Files</h1>
            </div>
          <button>
            <Icon icon="material-symbols:cancel-outline" width="26" class="text-gray-500"  @click="closeModal"/>
          </button>
          </div>
        </div>
      </div>
      <div v-else>
        <div class="flex bg-slate-100 items-start justify-between w-full">
          <div class=" w-full justify-between flex items-center">
          <div class="flex items-center gap-3">
            <div class="p-3 border rounded-md bg-white">
                <Icon icon="fluent-mdl2:chrome-back" width="20" />
            </div>
            <div>
              <h1 class="text-2xl font-medium">PDF File</h1>
            </div>
          </div>

          <div class=" flex items-center gap-5">
            <button>
            <Icon icon="uiw:download" width="20" class="text-gray-500"  @click="closeModal"/>
          </button>

          <button>
            <Icon icon="material-symbols:cancel-outline" width="20" class="text-gray-500"  @click="closeModal"/>
          </button>
          </div>
        </div>
        </div>
      </div>
      </template>
      <section class="bg-white mx-2 rounded-md p-5 max-h-[70vh] hide_scroll">

        <div v-if="activePage === 1">
       <div class=" flex flex-col items-center justify-center my-5" v-if="Professional.length === 0">
         <img :src="Empty_scan" alt="">
        
          <h1 class=" text-[24px] text-gray-900 font-semibold mt-3">Empty List</h1>
          <p class=" my-1 text-[16px] text-gray-800">You haven’t scanned any files yet</p>
          <button
          class="flex gap-2 my-3 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
        >
        <Icon icon="tabler:line-scan" />
          <p>Scan Document</p>
        </button>
       </div>
    <div v-else>
    

      <div
        class="flex items-center bg-slate-50 border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0"
      >
        <i class="pi pi-search text-gray-700" style="font-size: 1rem"></i>
        <input
          v-model="searchQuery"
          type="text"
          class="w-full bg-slate-50 border-0 outline-none text-gray-700"
          placeholder="Search Files"
        />
      </div>
      <div class="mt-5 h-[50vh] overflow-y-scroll hide_scroll">
        <div
          v-for="(item, index) in filteredItems"
          :key="index"
          class="mb-3 flex items-center justify-between w-full"
        >
          <div class="flex gap-4 items-start">
            <div class="p-3 rounded-md border mt-2">
              <Icon :icon="item.icon" width="25" />
            </div>
            <div>
              <h1 class="font-semibold">{{ item.title }}</h1>
              <p
                class="mt-1 bg-slate-50 text-gray-500 text-sm w-fit p-1.5 rounded-md"
              >
                {{ item.date }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button>
              <Icon icon="ph:eye" width="25" @click="toggleModal(2)" />
            </button>
           
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else>
   <div class=" flex flex-col items-center justify-center">
    <Icon icon="vscode-icons:file-type-pdf2" width="80" class=" my-1" />
    <p class=" mb-5 font-semibold">untitledprompt <span class=" text-gray-400">.Pdf </span></p>
    <p class=" text-[12px] text-gray-500 p-3 rounded-xl bg-gray-50">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas suscipit, eros in posuere blandit, odio risus malesuada lacus, ac auctor libero tortor facilisis massa. Nulla in neque at magna mollis malesuada. Quisque vehicula </p>
   <div class=" flex items-center gap-5 text-[14px]">
    <button    class="flex gap-2 my-3 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
              <Icon icon="ph:eye" width="25" @click="toggleModal(2)" />
              <p>Open Doc</p>
            </button>
           
            <button class="flex gap-2 my-3 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
              <Icon icon="tabler:send" width="30" @click="toggleModal(2)" />
              
              <p>Forward to AGF mail</p>
            </button>
           
   </div>
  </div>

  </div>
      </section>
      <template #footer>
       
      </template>
    </Dialog>
  </template>
    
    <script setup>
  import { defineEmits, defineProps, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";
  const props = defineProps(["modelValue"]);
  const emit = defineEmits(["update:modelValue"]);
  import Empty_scan from '../../../assets/images/scan.svg';
  const searchQuery = ref("");
  
  const Professional = ref([
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
  {
    title: "MIT Financial Management course",
    date: "Wed, May, 2024",
    icon: "hugeicons:teaching",
  },
  {
    title: "Caltech 6 months audit training certificate",
    date: "Wed, May, 2024",
    icon: "akar-icons:file",
  },
]);
const filteredItems = computed(() => {
  return Professional.value.filter((item) =>
    item.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

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
const activePage = ref(1)
  const toggleModal = (page)=>{
    activePage.value = page
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