<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        content: { class: 'px-0 bg-white pb-6' },
      }"
      :style="{ width: '30rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
    <template #header>
       <div class=" flex items-start justify-between w-full">
         <div class=" flex items-center gap-3">
           <div class=" p-3 border rounded-full">
            <Icon icon="iconamoon:file-add-bold" width="30" />
           </div>
           <div>
            <h1 class=" text-2xl font-medium">New Entry</h1>
            <p class=" text-sm">Add new file and track movement seamlessly</p>
           </div>
         </div>
       </div>

     </template>
     <Divider class=" -mt-1"/>
     <section class=" w-[100%] px-7">
        <div class=" mb-3">
            <label for="name">Name</label>
            <input  type="text" v-model="formData.name" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="Input Name" required>
        </div>
      <label for="Select Destination" class="">Select Entry Type</label>
        <Dropdown v-model="formData.file_type" :options="type" optionLabel="name" placeholder="Select From Dropdown"
        :pt="{
              root: {
                class: 'w-full mt-2 flex border border-solid border-gray-300  rounded-lg'
              }
            }"
        >
            <template #value="slotProps">
                <div v-if="slotProps.value" class="flex  items-center gap-2">
                  <div class=" p-2 rounded-full  border border-gray-300">
                    <Icon :icon="slotProps.value.icon" width="20" />
                  </div>
                    <div>{{ slotProps.value.name }}</div>
                </div>
                <span v-else>
                    {{ slotProps.placeholder }}
                </span>
            </template>
            <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <div class=" p-2 rounded-full  border border-gray-300">
                    <Icon :icon="slotProps.option.icon" width="20" />
                  </div>
                    <div>{{ slotProps.option.name }}</div>
                </div>
            </template>
        </Dropdown>

        <div class=" my-3">
            <label for="name">Document Number</label>
            <input  type="text" v-model="formData.doc_no" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="Input Document Number" required>
        </div>

        <div class=" my-3">
            <label for="name">Subject Matter</label>
            <textarea name="" id="" cols="10" v-model="formData.subject" rows="3" class=' border-solid border-gray-300 mt-1 resize-none p-2 border rounded-md outline-none w-full text-sm' placeholder="Add brief description" ></textarea>
        </div>
        <div>
            <label for="Select Destination" class="">Department</label>
        <Dropdown v-model="formData.sent_to_department_id" :options="branches" optionLabel="name" placeholder="Select From Dropdown"
        :pt="{
              root: {
                class: 'w-full mt-2 flex border border-solid border-gray-300  rounded-lg'
              }
            }"
        >
            <template #value="slotProps">
                <div v-if="slotProps.value" class="flex  items-center gap-2">
                  <div class=" p-2 rounded-full  border border-gray-300">
                    <Icon icon="gg:organisation" width="20" />
                  </div>
                    <div>{{ slotProps.value.name }}</div>
                </div>
                <span v-else>
                    {{ slotProps.placeholder }}
                </span>
            </template>
            <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <div class=" p-2 rounded-full  border border-gray-300">
                    <Icon icon="gg:organisation" width="20"/>
                  </div>
                    <div>{{ slotProps.option.name }}</div>
                </div>
            </template>
        </Dropdown>

        </div>
     </section>
  <Divider/>
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
          @click="submit"
          label="Create Entry"
          class=""
          :pt="{
            root: {
              class:
                'py-2 bg-primary px-10 border-0 text-white font-light  rounded-lg',
            },
          }"
        />
      </template>
    </Dialog>
  </template>

  <script setup>
  import { defineEmits,onMounted, defineProps, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";

  const toast = useToast();
  const props = defineProps(["modelValue"]);
  const emit = defineEmits(["update:modelValue"]);


  const selectedBranch = ref();
  const branches = ref();
const selectedType = ref();
const type = ref([
{
    name: 'File',
    icon: 'ph:file-duotone'
},
{
    name: 'Mail',
    icon: 'ic:outline-mail'
}
])
const department  = ref('')
  const formData = ref({
    name: "",
    file_type: "",
    subject: "",
    doc_no: "",
    sent_to_department_id: ""
});
const getDept = async () => {
    try {
        const response = await axios.get('/dashboard/registry/dept');
        branches.value = response.data.data;
        // console.log('branches', branches.value);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

onMounted(() => {
    getDept()
})
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

  const submit = async () => {
    formData.value.file_type = formData.value.file_type.name
    formData.value.sent_to_department_id = formData.value.sent_to_department_id.id.toString()
    console.log('final', (formData.value));
    const responses = await axios.get('/dashboard/registry/dept');
    department.value = responses.data.data[0]
    try {
        const response = await axios.post(`/dashboard/registry/newfile/${department.value.id}`, formData.value)
        console.log('sent:', response.data.data); // Log the response
        formData.value = {}
        toast.add({
            severity: "success",
            summary: "file Sent",
            detail: "File created successfully.",
            life: 3000,
        });
    } catch (error) {
        console.error('Error:', error.message);
        toast.add({
            severity: "danger",
            summary: "Empty file",
            detail: error.message,
            life: 3000,
        });
    }
    emit("update:modelValue", false);

  };

  </script>
   <style scoped>
  .custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
  }
  </style>
