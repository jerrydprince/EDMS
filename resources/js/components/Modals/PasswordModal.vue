<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=" .custom-dialog">

    <section class="-mb-6  flex flex-col items-center ">
      <div class=" bg-white p-7 -mt-12  relative">
        <img :src="grid" alt="">
      </div>
      <div class=" border rounded-full p-3 absolute bg-[#F9FAFB] top-20">
        <Icon icon="lucide:key" width="50" />
      </div>

      <div class="w-full">
        <div class=" px-7">
          <h1 class=" font-semibold text-2xl  text-center ">Change Password</h1>
          <p class=" text-[#667085] my-3 text-center">Update your password to enhance security.</p>
          <div class=" w-[500px] mx-auto mt-4">

            <label for="">Current Password</label> <br>
            <div class="border border-gray-300 rounded-md flex items-center mt-2 mb-1">
              <div class=" p-2">
                <Icon icon="lucide:lock" width="30" />
              </div>
              <input v-model="form.current_password" :type="passwordFieldType" class="w-full outline-none"
                placeholder="Enter current password" />
              <div class="pr-2">
                <button @click="togglePasswordVisibility">
                  <Icon v-if="passwordFieldType === 'password'" icon="octicon:eye-closed-16" width="30" class="pr-2" />
                  <Icon v-if="passwordFieldType === 'text'" icon="ph:eye-thin" width="30" />
                </button>
              </div>
            </div>
            <div v-if="form.errors.current_password" class="text-red-500 text-sm mb-4">{{ form.errors.current_password }}</div>
            <div v-else class="mb-4"></div>

            <label for="">New Password</label> <br>
            <div class="border border-gray-300 rounded-md flex items-center mt-2 mb-1">
              <div class=" p-2">
                <Icon icon="lucide:lock" width="30" />
              </div>
              <input v-model="form.password" :type="passwordFieldType" class="w-full outline-none"
                placeholder="Enter new password" />
              <div class="pr-2">
                <button @click="togglePasswordVisibility">
                  <Icon v-if="passwordFieldType === 'password'" icon="octicon:eye-closed-16" width="30" class="pr-2" />
                  <Icon v-if="passwordFieldType === 'text'" icon="ph:eye-thin" width="30" />
                </button>
              </div>
            </div>
            <div v-if="form.errors.password" class="text-red-500 text-sm mb-4">{{ form.errors.password }}</div>
            <div v-else class="mb-4"></div>

            <label for="">Confirm New Password</label> <br>
            <div class="border border-gray-300 rounded-md flex items-center mt-2 mb-1">
              <div class=" p-2">
                <Icon icon="lucide:lock" width="30" />
              </div>
              <input v-model="form.password_confirmation" :type="passwordFieldType" class="w-full outline-none"
                placeholder="Confirm new password" />
              <div class="pr-2">
                <button @click="togglePasswordVisibility">
                  <Icon v-if="passwordFieldType === 'password'" icon="octicon:eye-closed-16" width="30" class="pr-2" />
                  <Icon v-if="passwordFieldType === 'text'" icon="ph:eye-thin" width="30" />
                </button>
              </div>
            </div>
            <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mb-4">{{ form.errors.password_confirmation }}</div>
            <div v-else class="mb-4"></div>

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

      <Button :disabled="form.processing" @click="submit" label="Save Changes" class="" :pt="{
        root: {
          class:
            'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg disabled:opacity-50',
        },
      }" />
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3"
import grid from '../../assets/images/grid.svg'

const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const showPassword = ref(false);

const passwordFieldType = computed(() => (showPassword.value ? 'text' : 'password'));

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
  form.reset();
  emit("update:modelValue", false);
};

const submit = () => {
  form.put('/user/password', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.reset();
      emit("update:modelValue", false);
      toast.add({
        severity: "success",
        summary: 'Success',
        detail: "Password has been successfully changed.",
        life: 3000,
      });
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
