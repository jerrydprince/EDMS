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
            <Icon icon="material-symbols:pause-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Add Leave</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class=" mt-3">
        <div class="flex items-start gap-1">
          <label class="text-[#344054] font-semibold">Start Date</label>
          <span class="text-red-500">*</span>
        </div>
        <input type="date" id="start_date" v-model="form.start_date" class=" w-full mt-2 p-2 border rounded-md">
      </div>


      <div class=" mt-5">
        <div class="flex items-start gap-1">
          <label class="text-[#344054] font-semibold">Completion Date</label>
          <span class="text-red-500">*</span>
        </div>
        <input type="date" id="end_date" v-model="form.end_date" class=" w-full mt-2 p-2 border rounded-md">
      </div>


      <div class="my-5">
        <label class="text-[#344054] font-semibold">Reason <small class=" text-gray-500">(Optional)</small></label>
        <textarea name="" v-model="form.reason" id="" cols="30" rows="3" placeholder="Add reason"
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
        <Button label="Add Leave" class="" :pt="{
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
import {useForm, usePage, router} from "@inertiajs/vue3"

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const page = usePage()
const toast = useToast()

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const getUserId = ()=>{
  let url = page.url;
  const urlParts = url.split('/');
  const userId = urlParts[urlParts.length - 1];
  return userId;
}

const form = useForm({
  'start_date': '',
  'end_date': '',
  'reason': '',
  'user_id': getUserId()
})

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if(form.start_date ==''){
    document.getElementById('start_date').focus();
    return false
  }
  if(form.end_date ==''){
    document.getElementById('end_date').focus();
    return false
  }
  if(form.end_date <= form.start_date){
    let end_date = document.getElementById('end_date');
    end_date.value = ""
    end_date.focus();
    return false
  }
  form.post('/dashboard/add-leave', {onSuccess: page=>{
    router.reload({only:['user']})
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