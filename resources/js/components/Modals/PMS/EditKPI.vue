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
            <h1 class="text-2xl font-medium">Add KPI</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class=" mt-3">
        <div class="flex items-start gap-1">
          <label class="text-[#344054] font-semibold">KPI Name</label>
          <span class="text-red-500">*</span>
        </div>
        <input v-model="form.name" type="text" placeholder="Enter Name" class=" w-full mt-2 p-2 border rounded-md">
      </div>

      <!-- <div class="my-5">
        <label class="text-[#344054] font-semibold">Description <small class=" text-gray-500">(Optional)</small></label>
        <textarea name="" id="" cols="30" rows="3" placeholder="Add reason"
          class="w-full border mt-2 border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div> -->

      <div class=" mt-5">
        <div class="flex items-start gap-1">
          <label class="text-[#344054] font-semibold">Select String</label>
          <span class="text-red-500">*</span>
        </div>
        <select v-model="form.string" class="w-full mt-2 p-2 border rounded-md outline-none">
          <option value="" disabled selected>Select String</option>
          <option value="time">Time</option>
          <option value="point">point scale</option>
          <option value="Percentage">Percentage (100%)</option>
        </select>
      </div>

      <div class="mt-5" v-if="form.string == 'point'">
        <label for="">Points</label>
        <input v-model="form.points" type="text" placeholder="Enter the points" class=" w-full mt-2 p-2 border rounded-md">
      </div>

    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Edit KPI" class="" :pt="{
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
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
   name: '',
   string: '',
   points:null,
   kra_id:null, 
   kpi_id:null
})
const props = defineProps(["modelValue", 'kpi', 'kraId']);
const emit = defineEmits(["update:modelValue", 'refresh']);
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
  form.kra_id = props.kraId;
  form.post('/dashboard/add-kpi', {onSuccess:page=>{
    emit("update:modelValue", false);
    form.reset();
    emit('refresh')
       toast.add({
        severity: "success",
        summary: '',
        detail: "KPI edited",
        life: 3000,
      });
  }})

};

watch(()=>props.kpi, ()=>{
  form.name = props.kpi.key,
  form.string = props.kpi.string,
  form.points = props.kpi.value,
  form.kra_id = props.kraId,
  form.kpi_id = props.kpi.id
}, {immediate:true})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
