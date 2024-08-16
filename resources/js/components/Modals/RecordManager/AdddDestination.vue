<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '30rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class=" flex items-start justify-between w-full">
        <div class=" flex items-center gap-3">
          <div class=" p-3 border rounded-full">
            <Icon icon="iconamoon:file-add-bold" width="30" />
          </div>
          <div>
            <h1 class=" text-2xl font-medium">Add Destination</h1>
            <p class=" text-sm">Forward file to the selected destination</p>
          </div>
        </div>
      </div>

    </template>
    <Divider class=" -mt-1" />
    <section class=" w-[100%] px-7">
      <label for="Select Destination" class="">Select Destination</label>
      <Dropdown v-model="formData.branch" :options="props.branches" optionLabel="name"
        placeholder="Select From Dropdown" :pt="{
    root: {
      class: 'w-full mt-2 flex border border-solid border-primary shadow-lg rounded-lg'
    }
  }">
        <template #value="slotProps">
          <div v-if="slotProps.value" class="flex  items-center gap-2">
            <div class=" p-2 rounded-full  border border-gray-300">
              <Icon icon="gg:organisation" width="20" />
            </div>
            <div>{{ slotProps.value.title }}</div>
          </div>
          <span v-else>
            {{ slotProps.placeholder }}
          </span>
        </template>
        <template #option="slotProps">
          <div class="flex items-center gap-2">
            <div class=" p-2 rounded-full  border border-gray-300">
              <Icon icon="gg:organisation" width="20" />
            </div>
            <div>{{ slotProps.option.title }}</div>
          </div>
        </template>
      </Dropdown>
    </section>
    <Divider />
    <template #footer>
      <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
      <Button @click="submit" label="Send" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light  rounded-lg',
    },
  }" />
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, onMounted, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const dept = ref([])
const formData = ref({
  branch: null
})
const toast = useToast();
const props = defineProps(["modelValue", "fileId", 'branches', 'branch_type']);
// const props = defineProps({
//   destFileId: {
//     type: Number,
//     required: false,
//   },
// });

const emit = defineEmits(["update:modelValue"]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});
//   const getDept = async () => {
//     try {
//         const response = await axios.get('/dashboard/registry/dept');
//         console.log("dept with divisions and units", response.data.data);
//         dept.value = response.data.data
//     } catch (error) {
//         console.error('Error fetching data:', error);
//     }
// };

// onMounted(() => {
//     getDept()
// })
const closeModal = () => {
  emit("update:modelValue", false);
};

const depts = computed(() => {
  let depts = props.departments.filter((item) => {
    return item.id !== props.department.id
  })
  return depts
})

const submit = () => {
  //formData.value.to_department = formData.value.to_department.id
  if(!formData.value.branch){
    return false
  }

  formData.value.branch_type = props.branch_type
  router.post(`/dashboard/registry/transfer/${props.fileId}`, formData.value, {
    onSuccess: (page) => {
      formData.value = {}
      toast.add({
        severity: "success",
        summary: "file destination changed",
        detail: "File has been sent to a new destination successfully.",
        life: 3000,
      });
      emit("update:modelValue", false);
      emit('refresh')
    }
  }, {
    onError: (page) => {
      toast.add({
        severity: "danger",
        summary: "an error occured",
        detail: error.message,
        life: 3000,
      });
    }
  })


  // try {
  //     formData.value.to_department = formData.value.to_department.id

  //     const response = await axios.post(`/dashboard/registry/transfer/${props.fileId}`, formData.value)

  //     formData.value = {}
  //     toast.add({
  //         severity: "success",
  //         summary: "file destination changed",
  //         detail: "File has been sent to a new destination successfully.",
  //         life: 3000,
  //     });
  // } catch (error) {
  //     console.error('Error:', error.message);
  //     toast.add({
  //         severity: "danger",
  //         summary: "an error occured",
  //         detail: error.message,
  //         life: 3000,
  //     });
  // }
  // emit("update:modelValue", false);
  // toast.add({
  //   severity: "danger",
  //   summary: 'File Rejected',
  //   detail: "File would be redirected back to its source on the EDMS.",
  //   life: 3000,
  // });
};

</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
