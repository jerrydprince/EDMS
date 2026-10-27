<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'px-0 pb-0' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }" :style="{ width: '30rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class=".custom-dialog">
    
    <!-- Body -->
    <div class="px-8 py-10 bg-white flex flex-col items-center justify-center text-center">
      <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-6">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
          <Icon icon="icon-park-outline:caution" width="28" />
        </div>
      </div>
      
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Delete Access Level</h1>
      <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
        Are you sure you want to delete this access level? This action cannot be undone and will remove all associated permissions.
      </p>
    </div>

    <!-- Custom Footer -->
    <div class="px-8 py-5 border-t border-gray-100 bg-gray-50 flex justify-between gap-3">
      <button @click="closeModal" 
              class="w-full py-3 rounded-xl font-medium text-gray-700 hover:bg-gray-200 bg-gray-100 transition-colors duration-200">
        Cancel
      </button>
      <button @click="submit" 
              class="w-full py-3 rounded-xl font-medium text-white bg-red-500 hover:bg-red-600 shadow-sm hover:shadow transition-all duration-200 transform hover:-translate-y-0.5">
        Delete Level
      </button>
    </div>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const props = defineProps(["modelValue", "id"]);
const emit = defineEmits(["update:modelValue"]);
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

  router.get('/dashboard/delete-level', {id: props.id}, {onSuccess: ()=>{
    emit("update:modelValue", false);
    emit('refresh')
    toast.add({
      severity: "success",
      summary: '',
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
