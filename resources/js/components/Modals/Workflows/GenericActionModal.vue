<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-50' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <template #header>
      <div class="flex bg-slate-50 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mdi:check-circle-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">{{ transitions.length === 1 ? transitions[0].action_name : 'Execute Parallel Actions' }}</h1>
          </div>
        </div>
      </div>
    </template>
    
    <section class="bg-white mx-2 rounded-md p-5">
      <!-- Users Assignment (If Required) -->
      <div v-if="requiresManualAssignment">
         <div class="my-5">
          <label class="text-[#344054] font-semibold ">Assign To <small class=" text-gray-500"> (Next Handler) </small></label>
           <!-- For simplicity, rendering a basic dropdown or multi-select here. -->
           <!-- Assuming we can pass users list as prop for now, or just let them type IDs for the MVP -->
           <p class="text-sm text-gray-500 mt-2">Please select the next handler(s).</p>
           
           <MultiSelect
             v-model="form.assigned_to"
             :options="users"
             optionLabel="first_name"
             optionValue="id"
             placeholder="Select Users"
             class="w-full mt-2"
             display="chip"
           />
         </div>
      </div>
      
      <!-- Auto Assignment Info -->
      <div v-else-if="requiresAutoAssignment">
         <div class="my-5 p-4 bg-blue-50 text-blue-800 rounded-md">
            <h4 class="font-semibold mb-1">Automatic Organizational Routing</h4>
            <p class="text-sm">This action will automatically be routed to users matching the required role configuration(s).</p>
         </div>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Comments / Minutes <small class=" text-gray-500"> (optional)
          </small></label>
        <textarea name="" v-model="form.comments" cols="30" rows="5" placeholder="Add minutes or comments for this action"
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div>
    </section>

    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
          root: { class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg' },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Execute" class="" :loading="loading" :pt="{
          root: { class: 'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg' },
        }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import axios from 'axios';
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
    modelValue: Boolean,
    task: Object,
    transitions: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(["update:modelValue", "refresh"]);
const toast = useToast();
const loading = ref(false);

const form = ref({
  assigned_to: [],
  comments: ""
});

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  },
});

const requiresManualAssignment = computed(() => {
    return props.transitions.some(t => t.to_step && !t.to_step.is_end_step && !t.to_step.role_required);
});

const requiresAutoAssignment = computed(() => {
    return props.transitions.some(t => t.to_step && !t.to_step.is_end_step && t.to_step.role_required);
});

const closeModal = () => {
  form.value = { assigned_to: [], comments: "" };
  visible.value = false;
};

const submit = () => {
    if (!props.transitions || props.transitions.length === 0) return;
    
    // Basic validation
    if (requiresManualAssignment.value && form.value.assigned_to.length === 0) {
        toast.add({
            severity: "error",
            detail: "You must select at least one user to assign the next step.",
            life: 3000,
        });
        return;
    }

    loading.value = true;

    axios.post(`/dashboard/generic-workflow/instances/${props.task.id}/execute`, {
        transition_ids: props.transitions.map(t => t.id),
        assigned_to: form.value.assigned_to,
        comments: form.value.comments
    }).then(response => {
        toast.add({
            severity: "success",
            detail: response.data.message || "Action executed successfully.",
            life: 3000,
        });
        emit("refresh");
        closeModal();
    }).catch(error => {
        let msg = "An error occurred";
        if (error.response && error.response.data && error.response.data.error) {
            msg = error.response.data.error;
        }
        toast.add({
            severity: "error",
            detail: msg,
            life: 3000,
        });
    }).finally(() => {
        loading.value = false;
    });
};
</script>
