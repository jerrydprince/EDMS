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
            <Icon icon="mdi:tick" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Return Task</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class="my-5">
        <div class="flex items-center justify-between mb-2">
          <label class="text-[#344054] font-semibold text-sm">Directives / Reason for Return <small class="text-red-500">*</small></label>
          <span class="text-xs text-gray-400">Click preset to populate</span>
        </div>

        <!-- Quick Return Chips -->
        <div class="flex flex-wrap gap-2 mb-3">
          <button 
            type="button" 
            v-for="(preset, i) in returnPresets" 
            :key="i"
            @click="selectReturnPreset(preset)"
            class="text-xs px-2.5 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
            :class="form.description.includes(preset) ? 'bg-red-50 border-red-300 text-red-700 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 hover:border-gray-300'"
          >
            {{ preset }}
          </button>
        </div>

        <textarea name="" v-model="form.description" id="" cols="30" rows="4" placeholder="Enter specific directives for the revision..."
          class="w-full border border-gray-300 rounded-xl overflow-hidden resize-none outline-none p-3 text-sm focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all"></textarea>
        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
      </div>
      
      <div class="my-5">
        <label class="text-[#344054] font-semibold">Digital Signature (PIN) <small class="text-red-500">*</small></label>
        <InputText type="password" v-model="form.approval_pin" placeholder="Enter your signature PIN" class="w-full mt-3 border border-gray-300 rounded-md outline-none p-2" />
        <div v-if="form.errors.approval_pin" class="text-red-500 text-sm mt-1">{{ form.errors.approval_pin }}</div>
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Submit" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>

  <!-- <Progress  v-model="progressModal" /> -->

</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {useForm, router, usePage } from "@inertiajs/vue3";

// import Progress from "../../../components/Modals/Workflows/progress.vue";

const progressModal = ref(false)

const toast = useToast()
const props = defineProps(["modelValue", 'task', 'modifiedPdfBlob']);
const emit = defineEmits(["update:modelValue", "refresh"]);
const form = useForm({
  task_id: null,
  description: "",
  approval_pin: "",
  modified_file: null,
  modified_file_path: null,
})
const page = usePage()

const returnPresets = [
  'Incomplete documentation: Key supporting attachments missing.',
  'Discrepancy detected: Please cross-check ledger figure against voucher.',
  'Revision required: Please adjust recommendations per guidelines.',
  'Unverified vote line: Re-confirm department allocation line.'
];

const selectReturnPreset = (preset) => {
  if (!form.description) {
    form.description = preset;
  } else if (!form.description.includes(preset)) {
    form.description = form.description + '\n' + preset;
  }
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
  form.task_id = props.task.id;

  if (props.modifiedPdfBlob) {
      let fileName = "modified_document.pdf";
      let filePath = "";
      if (props.task && props.task.file && props.task.file.path) {
          filePath = props.task.file.path;
          fileName = "signed_" + filePath.split('/').pop();
      } else if (props.task && props.task.taskdocs && props.task.taskdocs.length > 0) {
          filePath = props.task.taskdocs[0].path;
          fileName = "signed_" + filePath.split('/').pop();
      }
      form.modified_file = new File([props.modifiedPdfBlob], fileName, { type: 'application/pdf' });
      form.modified_file_path = filePath;
  }
  
  if(!form.description || !form.approval_pin){
    toast.add({
      severity: "error",
      summary: 'Validation Error',
      detail: "Please provide a reason and your signature PIN",
      life: 3000,
    });
    return false;
  }
  
  form.post('/dashboard/return-task', {
    onSuccess: page => {
      emit("update:modelValue", false);
      emit("refresh");
      toast.add({
        severity: "success",
        summary: 'Task Returned',
        detail: "The task has been successfully returned to the assigner.",
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
      }
    }
  });
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
