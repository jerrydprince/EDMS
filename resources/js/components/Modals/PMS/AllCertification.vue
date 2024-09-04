<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md relative' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h1 class="font-semibold text-lg">Profesional Development</h1>
        <button
          class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300">
          <Icon icon="pepicons-pop:file" />
          <p>Generate Report</p>
        </button>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <button class="absolute -right-10 -top-10 bg-white p-2 rounded-full" @click="closeModal">
        <Icon icon="ic:baseline-close" width="25" />
      </button>

      <div class="flex items-center bg-slate-50 border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
        <i class="pi pi-search text-gray-700" style="font-size: 1rem"></i>
        <input v-model="searchQuery" type="text" class="w-full bg-slate-50 border-0 outline-none text-gray-700"
          placeholder="Search Certification" />
      </div>
      <div class="mt-5 h-[50vh] overflow-y-scroll hide_scroll" v-if="props.user && props.user.certificates && props.user.certificates.length > 0">
        <div v-for="(item, index) in filteredItems" :key="index" class="mb-3 flex items-center justify-between w-full">
          <div class="flex gap-4 items-start">
            <div class="p-3 rounded-md border mt-2">
              <Icon :icon="'akar-icons:file'" width="25" />
            </div>
            <div>
              <h1 class="font-semibold">{{ item.title }}</h1>
              <p class="mt-1 bg-slate-50 text-gray-500 text-sm w-fit p-1.5 rounded-md">
                {{ getDate(item.created_at) }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button>
              <Icon icon="ph:eye" width="25" @click="viewCert(item)" />
            </button>
            <button>
              <Icon icon="mingcute:delete-2-line" width="25" @click="deleteCert(item)" />
            </button>
          </div>
        </div>
      </div>
    </section>
    <template #footer>
      <div class="flex items-center justify-start px-5">
        <button
          class="p-2 rounded-md bg-white text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
          @click="toggleModals('add')">
          <Icon icon="material-symbols:add" width="20" />
          <p>Add Certification</p>
        </button>
      </div>
    </template>
  </Dialog>
  <AddCertification v-model="certificationModal" :user="props.user" />
  <DeleteCertificate v-model="deleteModal" :cert="selectedCert" />
  <ViewCertificate v-model="viewCertificateModal" :cert="selectedCert"/>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";

import { useToast } from "primevue/usetoast";

import AddCertification from "../../Modals/PMS/AddCertification.vue";
import DeleteCertificate from "../../Modals/PMS/DeleteCertificate.vue";
import ViewCertificate from "../../Modals/PMS/ViewCertificate.vue";
import moment from "moment";

const viewCertificateModal = ref(false);
const deleteModal = ref(false);
const certificationModal = ref(false);
const props = defineProps(["modelValue", 'user']);
const emit = defineEmits(["update:modelValue"]);
const toast = useToast()
const selectedCert = ref()
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

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

function getDate(date){
  return moment(date).format('ddd, MMM Do, YYYY');
}

const filteredItems = computed(() => {
  return props.user.certificates.filter((item) =>
    item.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const viewCert = (item)=>{
  selectedCert.value = item
  viewCertificateModal.value = true;
}

const deleteCert = (item)=>{
  selectedCert.value = item
  deleteModal.value = true;
}

const toggleModals = (event) => {
  closeModal();
  if (event === "add") {
    certificationModal.value = true;
  }
  // if (event === "view") {
  //   viewCertificateModal.value = true;
  // }
  // if (event === "delete") {
  //   deleteModal.value = true;
  // }
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