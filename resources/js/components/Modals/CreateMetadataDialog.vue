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
          <p class="mb-1 font-medium text-lg">Create New Metadata</p>
          <p class="text-gray-500 text-xs">Define Metadata Attributes</p>
        </div>
      </div>
    </template>
    <section class="-mb-6">
      <Divider class="" />
      <div class="px-6 w-full">
        <div class="mb-5 mt-8">
          <p class="text-sm lg:text-base">
            Metadata Name <span class="text-red-600">*</span>
          </p>
          <InputText
            id="metadata_name"
            class="mt-1 w-full"
            v-model="form.name"
            aria-describedby="metadata_name-help"
            placeholder="Enter Metadata Name"
           
          />
        </div>
        <div class="mb-5 w-full">
          <p class="text-sm lg:text-base">
            String <span class="text-red-600">*</span>
          </p>
          <!-- <Dropdown
            :pt="{
              root: {
                class: 'w-full flex border border-solid border-gray-300 rounded-lg'
              }
            }"
            id="metadata_string"
            v-model="form.string"
            :options="strings"
            optionLabel="label"
            placeholder="Select From Dropdown"
            class="mt-1 w-full"
          /> -->
          <select id="metadata_string" v-model="form.metadata_string" class=" mt-2 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option value="">Select from Dropdown</option>
                <option v-for="(string,ind) in strings" :value="string.label" :key="ind">{{ string.label }}</option>
          </select>
          <div v-if="form.errors.metadata_string"><span class="text-red-500">{{ form.errors.metadata_string }}</span></div>
        </div>
        <div class="flex items-center justify-start gap-3">
          <InputSwitch v-model="form.is_default" />
          <p class="text-gray-700">Set as Default</p>
        </div>
      </div>
      <Divider class="" />
    </section>
    <template #footer>
      <Button
        class="mr-4"
        :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }"
        @click="closeModal"
        label="Cancel"
        severity="secondary"
        outlined
      />
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
import { ref, defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3'

const toast = useToast();
const checked = ref(false);
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);


const selectedCity = ref();
const strings = ref([
  { label: "Description" },
  { label: "Text"},
  { label: "Date" },
  { label: "Number" },
  // { label: "Check List" },
]);

const metadata_strings = [
    {
        Description: `<div class="my-2 w-full">
                <label for="description">Description</label>
                <textarea name="description" @input='updateValue' v-model="description" :value="description" id="description" cols="30" rows="3" class="metadata-inputs border border-gray-200 rounded-lg block mt-2 w-full focus:border-blue-400 p-3"></textarea>
            </div>`
        },
        {
        Text: `<div class="my-2">
                <label for="title">Text</label>
                <input type="text" v-model="title" name="text" id="text" class="metadata-inputs border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
            </div>`
        },
        {
        Date: `<div class="my-2">
                <label for="date">Date</label>
                <input type="date" v-model="date" name="date" id="date" class="metadata-inputs border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
            </div>`
        },
        {
        Number: `<div class="my-2">
                <label for="text">Document Number</label>
                <input type="number" v-model="number" class="metadata-inputs border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full" id="numbers">
            </div>`
        }
]

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

//close modal
const closeModal = () => {
  emit("update:modelValue", false);
};

//metadata form
const form = useForm({
  name:"",
  metadata_string:"",
  is_default:false,
  metadata_value: ''
})

const replaceId = (val)=>{
  if(val){
    let arr = val.split(" ");
    
    Object.values(form.metadata_value)[0].replace(/id="[^"]*"/, `id="${arr[0]}"`);

  }
}

//submit meta form
const submitForm = () => {
  if(form.name ==''){
    document.getElementById('metadata_name').focus()
    return false
  }else
  if(form.metadata_string==''){
    document.getElementById('metadata_string').focus()
    return false
  }

  replaceId(form.name)
  //form.metadata_string.replace(/id="([^"]*)"/g, `id="${newValue}"`);

  form.post('/admin/create-metadata',{onSuccess: (status)=>{
   
    if(status){
      toast.add({
        severity: "success",
        summary: "Metadata Created",
        detail: "You've successfully created a new metadata.",
        life: 3000,
      });
      emit("update:modelValue", false);
      form.reset();
    }
  }})
    
};

const getmetadata = ()=>{
  let data = metadata_strings.filter((field)=>{
    return Object.values(field).some((value) => {
        if(field[form.metadata_string]){
          return field[form.metadata_string]
        }
    });
  })

  return data[0]
}

watch(()=>form.metadata_string, ()=>{
  form.metadata_value = getmetadata()
 
})
</script>
