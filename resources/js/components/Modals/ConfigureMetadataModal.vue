<template>
  <Dialog
    v-model:visible="visible"
    modal
    header="Header"
    :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
    :style="{ width: '35rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
  >
    <template #header>
      <div class="flex items-center gap-3 -mb-6">
        <Icon icon="solar:arrow-left-outline" width="25" @click="closeModal" />
        <div
          class="ml-2 rounded-full py-4 px-4 border border-border_primary border-solid"
        >
          <Icon icon="uiw:setting-o" width="25" />
        </div>
        <div>
          <p class="mb-1 font-medium text-lg">Configure Metadata</p>
          <p class="text-gray-500 text-xs">Define Document Metadata Attributes</p>
        </div>
      </div>
    </template>
    <section class="-mb-6">
      <Divider class="" />
      <div class="px-6 w-full">
        <div class="mb-5 mt-8">
          <p class="text-sm lg:text-base text-gray-700">
            Search Metadata / File Type
          </p>
          <span class="text-gray-400 relative mr-1">
            <Icon
              icon="iconamoon:search-light"
              width="20"
              class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600"
            />
            <InputText
              v-model="search"
              placeholder="Search"
              class="pl-10 w-full mt-1 h-10 bg-transparent font-light"
              aria-describedby="username-help"
            />
          </span>
        </div>
        <div class=" h-72 overflow-y-scroll">
          <div class="mt-4">
          <div
            v-for="mtdata of metadata"
            :key="mtdata.string"
            class="flex gap-3 mb-7 items-center"
          >
            <Checkbox
              v-model="selectedCategories"
              :inputId="mtdata.string"
              :disabled = isDisabled(mtdata)
              :pt="{
               
              }"
              name="mtdata"
              :value="mtdata.id"
            />
            <label :for="mtdata.string" class="text-gray-700">{{
              mtdata.name
            }}</label>
          </div>
        </div>
        </div>
      </div>
      <Divider class="" />
    </section>
    <template #footer>
      <Button
        label="Save"
        class=""
        :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }"
        @click="submitForm"
      />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, onMounted, onUpdated } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const toast = useToast();
const props = defineProps(["modelValue","doctype"]);
const emit = defineEmits(["update:modelValue"]);
const search = ref("");
const selectedCategories = ref([]);
const metadata = ref();

const documentType = ref(props.doctype);

// const categories = ref([
//   { name: "Policy Documents", key: "policy" },
//   { name: "Administrative Documents", key: "admin" },
//   { name: "Financial Documents", key: "fin" },
//   { name: "Reports", key: "reports" },
//   { name: "Policy Documents", key: "policy2" },
//   { name: "Financial Documents", key: "fin2" },
//   { name: "Administrative Documents", key: "admin2" },
// ]);

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

const submitForm = () => {
  toast.add({
    severity: "success",
    summary: "Metadata Updated",
    detail: "You've successfully configured metadata",
    life: 3000,
  });
  emit("update:modelValue", false);
  emit("selectedOptions",selectedCategories);
};

const isDisabled = (data) => {
  
  if(data.is_default == 1){
    selectedCategories.value.push(data.id)
    return true
  }else{
    return false
  }
} 

onMounted(()=>{
  axios.get('/admin/metadata-part').then((response)=>{
    metadata.value = response.data
  })
})

onUpdated(()=>{
  documentType.value = props.doctype
  let data = []
  if(documentType && documentType.value.metadata){
    for(let doc of documentType.value.metadata){
   data.push(doc.id)
  }
}
  selectedCategories.value = data
})

</script>
