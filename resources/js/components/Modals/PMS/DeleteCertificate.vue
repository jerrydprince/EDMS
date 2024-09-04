<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '30rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-center w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white text-red-500">
            <Icon icon="ph:warning" width="30" />
          </div>

        </div>
      </div>
    </template>
    <section class="bg-white  text-center mx-2 rounded-md p-5 flex items-center flex-col justify-center">
      <h1 class=" text-lg font-semibold">Delete Certification</h1>
      <p class=" w-[80%]">Are you sure you want to delete this certification permanently, for this employee, it would
        affect
        their overall performance rating.</p>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Delete" class="" :pt="{
    root: {
      class:
        'py-2 bg-red-500  px-10 border-0 text-white font-light hover:bg-red-600 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {router} from "@inertiajs/vue3";
const props = defineProps(["modelValue", 'cert']);
const emit = defineEmits(["update:modelValue"]);
const toast = useToast()
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
  router.get('/dashboard/delete-certificate', {id: props.cert.id}, {onSuccess:page=>{
    emit("update:modelValue", false);

    toast.add({
      severity: "success",
      summary: 'Success',
      detail: "",
      life: 3000,
    });
  }})
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>