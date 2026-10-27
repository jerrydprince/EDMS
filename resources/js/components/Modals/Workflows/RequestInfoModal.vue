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
            <Icon icon="mdi:message-question-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Request Information</h1>
            <p class="text-sm text-gray-500">Put this task on hold while waiting for information.</p>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Request From <small class="text-red-500">*</small></label>
        <Dropdown v-model="form.requested_from" :options="previousHandlers" optionLabel="name" optionValue="id" placeholder="Select User" class="w-full mt-3" />
        <div v-if="form.errors.requested_from" class="text-red-500 text-sm mt-1">{{ form.errors.requested_from }}</div>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Message <small class="text-red-500">*</small></label>
        <textarea name="" v-model="form.request_message" id="" cols="30" rows="5" placeholder="E.g., Please attach the signed approval memo."
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
          <div v-if="form.errors.request_message" class="text-red-500 text-sm mt-1">{{ form.errors.request_message }}</div>
      </div>

    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Submit Request" class="" :pt="{
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
import { defineEmits, defineProps, computed, ref, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import {useForm, router, usePage } from "@inertiajs/vue3";

const toast = useToast()
const props = defineProps(["modelValue", 'task']);
const emit = defineEmits(["update:modelValue", "refresh"]);
const form = useForm({
  task_id: null,
  requested_from: null,
  request_message: "",
})
const page = usePage()

const previousHandlers = computed(() => {
  if (!props.task) return [];
  const handlers = [];
  const uniqueIds = new Set();
  
  // Exclude current user from being requested
  uniqueIds.add(page.props.user.id);

  const addUser = (userObj) => {
    if (userObj && !uniqueIds.has(userObj.id)) {
      uniqueIds.add(userObj.id);
      handlers.push({
        id: userObj.id,
        name: userObj.first_name + ' ' + userObj.last_name + (userObj.designation ? ' (' + userObj.designation.name + ')' : '')
      });
    }
  };

  // 1. Check immediate assigner
  if (props.task.assigned_to_me && props.task.assigned_to_me.assigned_by) {
    addUser(props.task.assigned_to_me.assigned_by);
  }

  // 2. Check workflow logs
  if (props.task.workflow_logs) {
    props.task.workflow_logs.forEach(log => {
      addUser(log.action_by);
      addUser(log.action_to);
    });
  }

  // 3. Check minutes
  if (props.task.minutes) {
    props.task.minutes.forEach(minute => {
      addUser(minute.user);
    });
  }

  // 4. Check assignments (ones we assigned)
  if (props.task.assignments) {
    props.task.assignments.forEach(assignment => {
      addUser(assignment.assigned_by);
      addUser(assignment.assigned_to);
    });
  }

  // 5. Check task activity
  if (props.task.task_activity) {
    props.task.task_activity.forEach(activity => {
      addUser(activity.user);
    });
  }

  return handlers;
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
  emit("update:modelValue", false);
};

const submit = () => {
  form.task_id = props.task.id;

  if(!form.request_message || !form.requested_from){
    toast.add({
      severity: "error",
      summary: 'Validation Error',
      detail: "Please provide a user and a message",
      life: 3000,
    });
    return false;
  }

  form.post('/dashboard/request-information', {
    onSuccess: page => {
      emit("update:modelValue", false);
      emit("refresh");
      toast.add({
        severity: "success",
        summary: 'Request Sent',
        detail: "The information request has been sent successfully. Task is now on hold.",
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
