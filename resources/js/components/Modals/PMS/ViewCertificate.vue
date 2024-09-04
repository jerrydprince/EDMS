<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="pepicons-pencil:file" width="30" />
          </div>
          <div>
            <h1 class="text-xl font-medium">{{ cert.title }}</h1>
            <p class=" mt-1 bg-slate-50 text-gray-500 text-sm w-fit p-1.5 rounded-md">{{certDate(cert.created_at)}}</p>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <img :src="page.props.base + props.cert.file_path" alt="">
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">

        <Button label="Close" class="" :pt="{
    root: {
      class:
        'py-2  px-10 border font-semibold  rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {usePage} from "@inertiajs/vue3"
import moment from "moment"

const props = defineProps(["modelValue", 'cert']);
const emit = defineEmits(["update:modelValue"]);
const page = usePage()
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

function certDate(date){
  return moment(date).format('ddd, MMM Do, YYYY');
}
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>