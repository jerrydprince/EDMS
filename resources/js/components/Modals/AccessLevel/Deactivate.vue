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
      <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-6">
        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
          <Icon icon="mdi:pause-octagon-outline" width="28" />
        </div>
      </div>
      
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Deactivate Access Level</h1>
      <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
        Are you sure you want to deactivate this access level? Users with this access level will temporarily lose their permissions until reactivated.
      </p>
    </div>

    <!-- Custom Footer -->
    <div class="px-8 py-5 border-t border-gray-100 bg-gray-50 flex justify-between gap-3">
      <button @click="closeModal" 
              class="w-full py-3 rounded-xl font-medium text-gray-700 hover:bg-gray-200 bg-gray-100 transition-colors duration-200">
        Cancel
      </button>
      <button @click="submit" 
              class="w-full py-3 rounded-xl font-medium text-white bg-amber-500 hover:bg-amber-600 shadow-sm hover:shadow transition-all duration-200 transform hover:-translate-y-0.5">
        Deactivate
      </button>
    </div>
  </Dialog>
</template>
    
    <script setup>
  import { defineEmits, defineProps, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";
  import { router

   } from "@inertiajs/vue3";
  const props = defineProps(["modelValue", 'id']);
  const checked = false
  const toast = useToast()
  const emit = defineEmits(["update:modelValue", "id"]);
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
    
    router.post('/dashboard/change-accessLevel-status', {status: 0, id: props.id}, {onSuccess: ()=>{
      emit("update:modelValue", false);
      emit('refresh')
      toast.add({
          severity: "success",
          summary: 'Access Level Deactivated',
          detail: "The access level been temporarily deactivated",
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
  .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked + .slider {
  background-color: #12b76a;
}

input:focus + .slider {
  box-shadow: 0 0 1px #12b76a;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
  </style>
