<template>
  <Dialog v-model:visible="visible" modal header="Header" :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <template #header>
      <div class="flex items-center gap-3 -mb-6">
        <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
          <Icon icon="uiw:setting-o" width="25" />
        </div>
        <div>
          <p class="mb-1 font-medium text-lg">Create New Document Type</p>
          <p class="text-gray-500 text-xs">Define Document Attributes</p>
        </div>
      </div>
    </template>
    <section class="-mb-6">
      <Divider class="" />
      <div class="px-6 w-full">
        <div class="mb-5 mt-8">
          <p class="text-sm lg:text-base">Document Name *</p>
          <InputText id="document_name" class="mt-1 w-full" v-model="form.name" aria-describedby="username-help"
            placeholder="Enter Document Name" />
          <div v-if="form.errors.name"><span class="text-red-500">{{ form.errors.name }}</span></div>
        </div>
        <div class="mb-5">
          <p class="text-sm lg:text-base">Document Description (Optional)</p>
          <Textarea v-model="form.description" rows="5" cols="30" class="mt-1 w-full"
            placeholder="Provide a brief description..." />
        </div>
      </div>
      <Divider class="" />
    </section>
    <template #footer>
      <div class="flex items-center justify-end">
        <Button class="mr-4" @click="configureMetadataModal()" :pt="{
          root: {
            class: 'py-2 px-4 border-gray-200 border border-solid rounded-lg',
          },
        }" severity="secondary" outlined>
          <div class="flex gap-2 font-medium items-center">
            <Icon icon="uiw:setting-o" width="25" />
            <p>Configure Meta-data</p>
          </div>
        </Button>
        <Button label="Save" class="" :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }" @click="submitForm" />
      </div>
    </template>
  </Dialog>
  <ConfigureMetadataModal v-model="configureMetadata" @selectedOptions="SelectedMetadataOptions" :doctype="[]" />
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, onUpdated } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3'
import ConfigureMetadataModal from "../../components/Modals/ConfigureMetadataModal.vue";


const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const selectedMetadata = ref([]);
const configureMetadata = ref(false)

const form = useForm({
  name: "",
  description: "",
  metadata: []
})

const SelectedMetadataOptions = (val) => {
  selectedMetadata.value = val.value
}

const setSingleDocType = (data) => {
  configureMetadata.value = true
 // singleDocType.value = data
}

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const configureMetadataModal = () => {
  //emit("openConfigModal", true);
  configureMetadata.value = true
};

const submitForm = () => {
  form.post('/admin/create-document-type', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Document Created",
        detail: "You've successfully created a new document.",
        life: 3000,
      });
      emit("update:modelValue", false);
    }
  })

};

onUpdated(() => {
  form.metadata = selectedMetadata.value
})

</script>
