<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=" .custom-dialog">

    <section class="-mb-6  flex flex-col items-center ">
      <div class=" bg-white p-7 -mt-12  relative">
        <img :src="'./../assets/images/grid.svg'" alt="">
      </div>
      <div class=" border rounded-full p-3 absolute bg-[#F9FAFB] top-20">
        <Icon icon="lucide:key" width="50" />
      </div>



      <div>
        <div class=" px-7">
          <h1 class=" font-semibold text-2xl  text-center ">Input email/username</h1>
          <!-- <p class=" text-[#667085] my-3 text-center">  To ensure the security of your account, please input your password to delete login sessions.</p> -->
           <p>A mail will be sent to the email address/username provided.</p>
          <div class=" w-[500px] mt-4">

            <label for="">Username</label> <br>
            <div class="border border-gray-300 rounded-md flex items-center mt-2">
              <div class=" p-2">
                <Icon icon="lucide:lock" width="30" />
              </div>
              <input v-model="form.email" type="text" class="w-full outline-none"
                placeholder="Enter username" />
              <!-- <div class="pr-2">
                <button @click="togglePasswordVisibility">
                  <Icon v-if="passwordFieldType === 'password'" icon="octicon:eye-closed-16" width="30" class="pr-2" />
                  <Icon v-if="passwordFieldType === 'text'" icon="ph:eye-thin" width="30" />
                </button>
              </div> -->
            </div>
            <div v-if="form.errors.email" class=" text-red-500 text-sm">{{ form.errors.email }}</div>
          </div>
        </div>

      </div>


      <Divider />
    </section>

    <template #footer>
      <Button class="mr-4" :pt="{
        root: {
          class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
        },
      }" @click="closeModal" label="Cancel" severity="secondary" outlined />

      <Button :disabled="isLoading" @click="submit" label="Send" class="" :pt="{
        root: {
          class:
            'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
        },
      }" />
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import {useForm} from "@inertiajs/vue3"

const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const isLoading = ref(false)
const form = useForm({
  email:''
})

const showPassword = ref(false);

// Computed property to determine the input type
const passwordFieldType = computed(() => (showPassword.value ? 'text' : 'password'));

// Method to toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
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
  isLoading.value = true;
  form.post('/forgot-password', {onSuccess: page=>{
    emit("update:modelValue", false);
    isLoading.value = false;
    toast.add({
      severity: "success",
      summary: 'Password Changed',
      detail: "Your password has been updated successfully.",
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