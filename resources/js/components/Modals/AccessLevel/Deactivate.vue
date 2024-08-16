<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        root: { class: 'bg-slate-100 px-5 rounded-2xl' },
        content: { class: 'px-0  pb-6' },
        header: { class: ' m-5' },
        footer: {class: 'bg-slate-100'}
      }"
      :style="{ width: '35rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
      <template #header>
        <div class="flex bg-slate-100 items-start justify-center w-full">
          <div class="flex items-center gap-3">
            <div class="p-3 border rounded-md bg-white  text-red-500">
                <label class="switch">
              <input
                type="checkbox"
                disabled
              />
              <span class="slider round"></span>
            </label>
            </div>

          </div>
        </div>
      </template>
      <section class="bg-white  text-center mx-2 rounded-md p-5 flex items-center flex-col justify-center">
         <h1 class=" text-xl font-semibold">Confirm Deactivation</h1>
         <p class=" w-[90%]">Are you sure you want to temporarily deactivate the access level? Users with this access level will not be able to access their permissions until it is reactivated. Please confirm to proceed.</p>
      </section>
      <template #footer>
          <div  class=" flex items-center  justify-end px-7">
        <Button
          class="mr-4"
          :pt="{
            root: {
              class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
            },
          }"
          @click="closeModal"
          label="Cancel"
          severity="secondary"
          outlined
        />
        <Button
          label="Confirm"
          class=""
          :pt="{
            root: {
              class:
                'py-2 bg-primary  px-10 border-0 text-white font-light  rounded-lg',
            },
          }"
          @click="submit"
          autofocus
        />
      </div>
      </template>
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