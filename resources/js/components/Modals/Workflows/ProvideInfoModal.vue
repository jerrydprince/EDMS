<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-50' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-50 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mdi:message-reply-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Provide Information</h1>
            <p class="text-sm text-gray-500">Provide the requested information to resume the task.</p>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class="my-5 p-3 bg-blue-50 border border-blue-200 rounded-md">
        <p class="text-sm text-blue-800 font-medium">Requested by: {{ requestData?.requested_by?.first_name }} {{ requestData?.requested_by?.last_name }}</p>
        <p class="text-sm text-blue-700 italic mt-1">"{{ requestData?.request_message }}"</p>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Your Response <small class="text-red-500">*</small></label>
        <textarea name="" v-model="form.response_message" id="" cols="30" rows="5" placeholder="E.g., I have attached the requested memo."
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
          <div v-if="form.errors.response_message" class="text-red-500 text-sm mt-1">{{ form.errors.response_message }}</div>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold">Attachments (Optional)</label>
        <div class="mt-3">
          <input type="file" ref="fileInput" multiple @change="handleFileUpload" class="w-full border border-gray-300 rounded-md p-2" />
        </div>
        <div v-if="form.errors.files" class="text-red-500 text-sm mt-1">{{ form.errors.files }}</div>
      </div>

    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Submit Response" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" :loading="form.processing" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {useForm, router, usePage } from "@inertiajs/vue3";

const toast = useToast()
const props = defineProps(["modelValue", 'requestData']);
const emit = defineEmits(["update:modelValue", "refresh"]);
const form = useForm({
  request_id: null,
  response_message: "",
  files: []
})

const fileInput = ref(null);

const handleFileUpload = (e) => {
  form.files = Array.from(e.target.files);
};

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
  form.request_id = props.requestData.id;

  if(!form.response_message){
    toast.add({
      severity: "error",
      summary: 'Validation Error',
      detail: "Please provide a response message",
      life: 3000,
    });
    return false;
  }

  form.post('/dashboard/provide-information', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: page => {
      emit("update:modelValue", false);
      emit("refresh");
      toast.add({
        severity: "success",
        summary: 'Response Submitted',
        detail: "The information has been provided successfully. The task is now resumed.",
        life: 3000,
      });
    },
    onError: errors => {
      if (errors.error) {
        toast.add({
          severity: "error",
          summary: 'Error',
          detail: errors.error,
          life: 3000,
        });
      } else if (Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0];
        toast.add({
          severity: "error",
          summary: 'Validation Error',
          detail: firstError,
          life: 3000,
        });
      }
    }
  });
};
</script>
