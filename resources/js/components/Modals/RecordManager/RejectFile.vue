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
      <section class="-mb-6 flex flex-col items-center">
        <div class="bg-white p-7 -mt-12 -mb-5 relative ">
          <img src="../../../assets/images/grid.svg" alt="" />
        </div>
        <div class="border border-red-200 rounded-full p-3 absolute bg-[#F9FAFB] top-20">
          <Icon icon="mdi:cancel-octagon-outline" width="50" class="text-[#F04438]" />
        </div>

        <div>
          <div class="px-7 z-50 relative">
            <h1 class="font-semibold text-2xl text-center">
                Reject File
            </h1>
            <p class="text-[#667085] my-3 text-center">
                Are you sure? This action is irreversible. File would be redirected back to its source on the EDMS.
            </p>
          </div>
        </div>

        <Divider />
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
<!-- {{ props.data }} -->
        <Button
          @click="submit"
          label="Confirm"
          class=""
          :pt="{
            root: {
              class:
                'py-2 bg-[#F04438] px-10 border-0 text-white font-light  rounded-lg',
            },
          }"
        />
      </template>
    </Dialog>
  </template>

  <script setup>
  import { defineEmits, defineProps, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";
  import { router } from "@inertiajs/vue3";

  const toast = useToast();
  const props = defineProps(["modelValue", "data"]);
  console.log('delete', props.data);
  const emit = defineEmits(["update:modelValue"]);

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
  const formData = ref({
    status: "rejected"
});
  const submit = async () => {
      router.post(`/dashboard/registry/updateFileStatus/${props?.data}`, formData.value, {onSuccess: page=>{
        toast.add({
            severity: "success",
            summary: "Reject success",
            detail: "File has been rejected successfully.",
            life: 3000,
        });
        emit("update:modelValue", false);
        emit('refresh')
      }}, {onError: page=>{
        toast.add({
            severity: "success",
            summary: "Reject success",
            detail: "File has been rejected successfully.",
            life: 3000,
        });
      }})

        // try {
        //     const response =  await axios.post(`/dashboard/registry/updateFileStatus/${props?.data}`, formData.value)
        //     toast.add({
        //     severity: "success",
        //     summary: "Reject success",
        //     detail: "File has been rejected successfully.",
        //     life: 3000,
        // });
  

        // } catch (error) {

        // toast.add({
        //     severity: "Error",
        //     summary: 'An error occured',
        //     detail: error.message,
        //     life: 3000,
        // });
        // }
  };

  </script>
   <style scoped>
  .custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
  }
  </style>
