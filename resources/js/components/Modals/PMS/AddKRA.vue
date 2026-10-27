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
            <Icon icon="clarity:plus-line" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Add KRA</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class=" mt-3">
        <div class="flex items-start gap-1">
          <label class="text-[#344054] font-semibold">KRA Name</label>
          <span class="text-red-500">*</span>
        </div>
        <input type="text" id="full_name" v-model="form.name" placeholder="Enter Name" class=" w-full mt-2 p-2 border rounded-md">
      </div>





      <div class="my-5">
        <label class="text-[#344054] font-semibold">Description <small class=" text-gray-500">(Optional)</small></label>
        <textarea name="" v-model="form.description" id="" cols="30" rows="3" placeholder="Description"
          class="w-full border mt-2 border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div>

    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Add KPI" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";

const props = defineProps(["modelValue", 'department_id']);
const emit = defineEmits(["update:modelValue", 'refresh']);
const toast = useToast()
const form = useForm({
  name:'',
  description:'',
  department_id: null
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

const submit = () => {
  if(form.name ==''){
    document.getElementById('full_name').focus();
    return false;
  }
  if(props.department_id){
    form.department_id = props.department_id
  }
  form.post('/dashboard/create-kra', {onSuccess: page=>{
    emit("update:modelValue", false);
    emit('refresh')
    form.reset()
    toast.add({
      severity: "success",
      summary: 'KRA created',
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
