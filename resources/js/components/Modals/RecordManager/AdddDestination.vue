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
            <h1 class=" text-2xl font-medium">Add Destination</h1>
            <p class=" text-sm">Add new file and track movement seamlessly</p>
           </div>
         </div>
       </div>

     </template>
     <Divider class=" -mt-1"/>
     <section class=" w-[100%] px-7">
      <label for="Select Destination" class="">Select Destination</label>
        <Dropdown v-model="formData.to_department" :options="dept" optionLabel="name" placeholder="Select From Dropdown"
        :pt="{
              root: {
                class: 'w-full mt-2 flex border border-solid border-primary shadow-lg rounded-lg'
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
          label="Send"
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

  const dept = ref([])
   const formData = ref({
    to_department:""
   })
  const toast = useToast();
  const props = defineProps(["modelValue", "fileId"]);
// const props = defineProps({
//   destFileId: {
//     type: Number,
//     required: false,
//   },
// });
console.log('in props', props.fileId);
  const emit = defineEmits(["update:modelValue"]);

  const visible = computed({
    get() {
      return props.modelValue;
    },
    set(value) {
      emit("update:modelValue", value);
    },
  });
  const getDept = async () => {
    try {
        const response = await axios.get('/dashboard/registry/dept');
        console.log("dept with divisions and units", response.data.data);
        dept.value = response.data.data
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

onMounted(() => {
    getDept()
})
  const closeModal = () => {
    emit("update:modelValue", false);
  };

  const submit = async () => {
    try {
        formData.value.to_department = formData.value.to_department.id
        console.log(formData.value);
        const response = await axios.post(`/dashboard/registry/transfer/${props.fileId}`, formData.value)
        console.log('transfer:', response.data.data); // Log the response
        formData.value = {}
        toast.add({
            severity: "success",
            summary: "file destination changed",
            detail: "File has been sent to a new destination successfully.",
            life: 3000,
        });
    } catch (error) {
        console.error('Error:', error.message);
        toast.add({
            severity: "danger",
            summary: "an error occured",
            detail: error.message,
            life: 3000,
        });
    }
    emit("update:modelValue", false);
      // toast.add({
      //   severity: "danger",
      //   summary: 'File Rejected',
      //   detail: "File would be redirected back to its source on the EDMS.",
      //   life: 3000,
      // });
  };
  const selectedBranch = ref();
  const branches = ref([
    { name: 'Accounting Department'},
    { name: 'Accounting Department' },
    { name: 'Accounting Department' },
    { name: 'Accounting Department'},
    { name: 'Accounting Department'},
    { name: 'Accounting Department' },
]);
  </script>
   <style scoped>
  .custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
  }
  </style>
