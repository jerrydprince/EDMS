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
            <p class="mb-1 font-medium text-lg">Create New File Type</p>
            <p class="text-gray-500 text-xs">Define File Attributes</p>
          </div>
        </div>
      </template>
      <section class="-mb-6">
        <Divider class="" />
        <div class="px-6 w-full">
          <div class="mb-5 mt-8">
            <p class="text-sm lg:text-base">File Type Name *</p>
            <InputText id="document_name" class="mt-1 w-full" v-model="form.name" aria-describedby="username-help"
              placeholder="Enter Document Name" />
            <div v-if="form.errors.name"><span class="text-red-500">{{ form.errors.name }}</span></div>
          </div>
          <div class="mb-5">
            <p class="text-sm lg:text-base">File Type Description (Optional)</p>
            <Textarea v-model="form.description" rows="3" cols="30" class="mt-1 w-full"
              placeholder="Provide a brief description..." />
          </div>
          
          <div class="mb-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm lg:text-base font-medium">Custom Columns</p>
                <div @click="addColumn" class="cursor-pointer text-primary border border-primary px-2 py-1 rounded text-xs flex items-center gap-1">
                    <Icon icon="ion:add-outline" width="16" /> Add Column
                </div>
            </div>
            
            <div v-for="(heading, index) in form.headings" :key="index" class="flex items-center gap-2 mb-3">
                <InputText class="w-full" v-model="form.headings[index]" placeholder="e.g. Vendor Name" />
                <div @click="removeColumn(index)" class="cursor-pointer text-red-500 p-2 border border-red-200 rounded hover:bg-red-50">
                    <Icon icon="uiw:delete" width="16" />
                </div>
            </div>
          </div>
        </div>
        <Divider class="" />
      </section>
      <template #footer>
        <div class="flex items-center justify-end">
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
    headings: ["Title", "Description", "Amount"],
  })

  const addColumn = () => {
    form.headings.push("");
  };

  const removeColumn = (index) => {
    form.headings.splice(index, 1);
  };

  
  const visible = computed({
    get() {
      return props.modelValue;
    },
    set(value) {
      emit("update:modelValue", value);
    },
  });

  
  const submitForm = () => {
    form.post('/dashboard/file-type', {
      onSuccess: () => {
        form.reset()
        toast.add({
          severity: "success",
          summary: "File type Created",
          detail: "You've successfully created a new file type.",
          life: 3000,
        });
        emit("update:modelValue", false);
      }
    })
  
  };
  
  </script>
  
