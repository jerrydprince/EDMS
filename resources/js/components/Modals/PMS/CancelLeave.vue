<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' },
  }" :style="{ width: '35rem', padding: '30px 15px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <section class="flex flex-col items-center">
      <div class="relative items-center justify-center flex -mt-5">
        <div class="border rounded-full p-3 text-gray-500">
          <Icon icon="iconoir:cancel" width="50" />
        </div>
      </div>
      <div class="text-center w-[85%]">
        <h1 class="text-2xl mb-4 font-semibold mt-5 text-gray-700">
          Cancel Leave
        </h1>
        <p class="text-sm text-center text-gray-500">
          Are you sure you want to cancel the leave duration. This action would
          reactivate the performance tracking KPIs of this employee.
        </p>
      </div>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-5">
        <div class="flex justify-between w-2/3">
          <button @click="cancelDelete" class="py-2 px-14 border-gray-200 border border-solid rounded-lg"
            label="Cancel">
            Cancel
          </button>

          <button @click="confirmDelete" class="bg-[#F04438] py-2 px-14 text-white rounded-lg" severity="secondary"
            outlined>
            Delete
          </button>
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const toast = useToast();
const props = defineProps(["modelValue", "user"]);
const emit = defineEmits(["update:modelValue"]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const cancelDelete = () => {
  emit("update:modelValue", false);
};

const confirmDelete = () => {
  router.get('/dashboard/cancel-leave',{user_id: props.user.id, leave_id: props.user.leave[0].id},
    {onSuccess: page=>{
      emit("update:modelValue", false);
      router.reload({only: ['user']})
      toast.add({
        severity: "success",
        summary: "Leave Cancelled",
        detail: "Employee Leave Cancelled successfully ",
        life: 3000,
      });
    }})
  
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>
