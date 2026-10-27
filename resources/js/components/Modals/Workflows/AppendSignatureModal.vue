<template>
  <Dialog :visible="props.modelValue" @update:visible="(v) => emit('update:modelValue', v)" :closable="false" modal :pt="{
    root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-5' },
    footer: { class: 'bg-slate-50' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <template #header>
      <div class="flex bg-slate-50 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mdi:draw-pen" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Append Digital Signature</h1>
          </div>
        </div>
      </div>
    </template>
    
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="my-3">
        <p class="text-sm text-gray-500 mb-4">
          Please enter your digital signature PIN to sign the document(s) attached to this task.
        </p>
        <label class="text-[#344054] font-semibold">Digital Signature PIN</label>
        <input type="password" v-model="form.approval_pin" required
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden outline-none p-2 focus:border-primary focus:ring-primary"
          placeholder="Enter PIN">
      </div>
    </section>

    <template #footer>
      <div class="flex items-center justify-end px-7 mt-4">
        <Button class="mr-4" :pt="{
          root: { class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg' },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        
        <Button :label="form.processing ? 'Signing...' : 'Sign Document'" :disabled="form.processing" :pt="{
          root: { class: 'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg' },
        }" @click="submitSignature" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, router } from "@inertiajs/vue3";

const toast = useToast();
const props = defineProps({
  modelValue: Boolean,
  taskId: Number,
});
const emit = defineEmits(["update:modelValue", "refresh"]);

const form = useForm({
  task_id: props.taskId,
  approval_pin: '',
});

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  form.reset();
  emit("update:modelValue", false);
};

const submitSignature = () => {
  form.task_id = props.taskId;
  if (!form.approval_pin) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "PIN is required.",
      life: 3000,
    });
    return;
  }

  form.post('/dashboard/task-append-signature', {
    onSuccess: () => {
      emit("update:modelValue", false);
      form.reset();
      emit("refresh");
      toast.add({
        severity: "success",
        summary: "Success",
        detail: "Signature appended successfully!",
        life: 3000,
      });
    },
    onError: (errors) => {
      let msg = "Failed to append signature.";
      if (errors && Object.keys(errors).length > 0) {
        msg = errors[Object.keys(errors)[0]];
      }
      toast.add({
        severity: "error",
        summary: "Error",
        detail: msg,
        life: 3000,
      });
    }
  });
};
</script>
