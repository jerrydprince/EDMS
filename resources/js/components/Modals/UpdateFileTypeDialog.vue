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
        <div
          class="rounded-full py-4 px-4 border border-border_primary border-solid"
        >
          <Icon icon="uiw:setting-o" width="25" />
        </div>
        <div>
          <p class="mb-1 font-medium text-lg">Update File Type</p>
          <p class="text-gray-500 text-xs">Define File Type Attributes</p>
        </div>
      </div>
    </template>
    <section class="-mb-6">
      <Divider class="" />
      <div class="px-6 w-full">
        <div class="mb-5 mt-8">
          <p class="text-sm lg:text-base">File Type Name *</p>
          <InputText
            id="document_name"
            class="mt-1 w-full"
            v-model="form.name"
            aria-describedby="username-help"
            placeholder="Enter Document Name"
          />
          <div v-if="form.errors.name"><span class="text-red-500">{{ form.errors.name }}</span></div>
        </div>
        <div class="mb-5">
          <p class="text-sm lg:text-base">File Type Description (Optional)</p>
          <Textarea
            v-model="form.description"
            rows="5"
            cols="30"
            class="mt-1 w-full"
            placeholder="Provide a brief description..."
          />
        </div>
      </div>
      <Divider class="" />
    </section>
    <template #footer>
      <div class="flex items-center justify-end">
       
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
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, onUpdated } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3'

const toast = useToast();
const props = defineProps(["modelValue","singleFileType"]);
const emit = defineEmits(["update:modelValue"]);

const form = useForm({
  name: "",
  description: "",
  id:''
})

  
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const submitForm = () => {
  form.post('/admin/file-type',{onSuccess: ()=>{
      toast.add({
        severity: "success",
        summary: "Document Created",
        detail: "You've successfully created a new document.",
        life: 3000,
      });
      emit("update:modelValue", false);
  }})
  
};

onUpdated(()=>{
  // let data = []
  //   if(props.singleFileType && props.singleFileType.metadata){
  //       for(let doc of props.singleFileType.metadata){
  //       data.push(doc.id)
  //   }
  // }
  //data = [...new Set([...data, ...selectedMetadata.value])];
  form.name = props.singleFileType.name
  form.description = props.singleFileType.description
  form.id = props.singleFileType.id
})

</script>
