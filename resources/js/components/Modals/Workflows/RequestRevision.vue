<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: {
        class:
          'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md',
      },
      content: { class: 'px-0  pb-6' },
      header: { class: ' m-5' },
      footer: { class: 'bg-slate-50' },
    }"
    :style="{ width: '35rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <template #header>
      <div class="flex bg-slate-50 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div>
            <h1 class="text-2xl font-medium">Request Revision on Task</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="my-5">
        <label class="text-[#344054] font-semibold">Add Comment </label>
        <textarea
          name=""
          id=""
          cols="30"
          rows="5"
          placeholder="Explain task revision"
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"
        ></textarea>
      </div>

      <div>
        <div class="w-full flex items-center justify-between">
          <div class="flex items-center gap-3">
            <h1 class="text-lg">Exclude from rating</h1>
            <Icon icon="mdi:warning-circle"  class=" rotate-180"/>
          </div>
          <div>
            <InputSwitch
              v-model="checked"
              :pt="{

                input: { class: ' text-yellow-300' },

              }"
            />
          </div>
        </div>
        <div v-if="checked">
           <div class=" flex items-start gap-2 p-3 rounded-lg bg-yellow-50 mt-3 text-yellow-500">
            <Icon icon="mdi:warning-circle"  class=" rotate-180" width="25"/>
            <p class=" w-[90%]">Enable this option if the revisions are due to factors beyond the employee's control and should not impact their performance rating.</p>
           </div>
        </div>
      </div>
    </section>
    <template #footer>
      <div class="flex items-center justify-end px-7">
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
          label="Initiate Revision"
          class=""
          :pt="{
            root: {
              class:
                'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
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
const toast = useToast();
const checked = ref(false);
const props = defineProps(["modelValue"]);
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
  emit("update:modelValue", false);

  toast.add({
    severity: "success",
    summary: "Task Submitted",
    detail: "Your task as been marked as complete and is under review",
    life: 3000,
  });
};
</script>
     <style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>