<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-slate-100 px-5 rounded-md' },
      content: { class: 'px-0  pb-6' },
      header: { class: ' m-5' },
      footer: {class: 'bg-slate-100'}
    }"
    :style="{ width: '35rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="ph:warning" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Report Misconduct</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="flex items-start gap-1">
        <label class="text-[#344054] font-semibold">Select a Ticket</label>
        <span class="text-red-500">*</span>
      </div>
      <select
        name=""
        id=""
        class="w-full p-2 border rounded-md mt-2 outline-none"
      >
        <option value="Select area">Select area</option>
      </select>

      <div class="my-5">
        <label class="text-[#344054] font-semibold">Describe Complaint</label>
        <textarea
          name=""
          id=""
          cols="30"
          rows="3"
          class="w-full border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"
        ></textarea>
      </div>

      <div>
        <h1 class="text-[#344054] font-semibold mt-3">
          Upload supportive document
        </h1>
        <div  class=" p-1 border rounded-xl mt-3">
        <div
          class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px]"
        >
          <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
          <div class="text-center">
            <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
            <p class="text-sm leading-7 mb-7 text-[#475467]">
                supports PDF & Word docs
            </p>
          </div>

          <button
            class="bg-white border-gray-300 border p-3 flex items-center gap-2  rounded-lg relative"
          >
          <Icon icon="ph:folder-light" width="25" />
            <input type="file" class="opacity-0 absolute w-full" />Browse Computer
          </button>
        </div>
    </div>
      </div>
    </section>
    <template #footer>
        <div  class=" flex items-center  justify-end px-7">
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
        label="Report"
        class=""
        :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }"
        @click="submit"
        autofocus
      />
    </div>
    </template>
  </Dialog>
</template>
  
  <script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
const props = defineProps(["modelValue"]);
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