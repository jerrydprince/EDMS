<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        content: { class: 'px-0 bg-white pb-6' },
      }"
      :style="{ width: '35rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
      <section class="-mb-6 flex flex-col items-center">
        <div class="bg-white p-7 -mt-12 relative">
          <img :src="page.props.base+'/images/grid.svg'" alt="" />
        </div>
        <div class="border rounded-full p-3 absolute bg-[#F9FAFB] top-20">
          <Icon icon="mi:delete" width="50" class="text-[#F04438]" />
        </div>
  
        <div>
          <div class="px-7">
            <h1 class="font-semibold text-2xl text-center">
                Confirm Deletion
            </h1>
            <p class="text-[#667085] my-3 text-center">
                Are you sure you want to delete this mail? This action cannot be undone.
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
  import { usePage } from '@inertiajs/vue3'
  
  const toast = useToast();
  const props = defineProps(["modelValue"]);
  const emit = defineEmits(["update:modelValue"]);

  const page = usePage();
  
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