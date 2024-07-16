<template>
    <Dialog
      v-model:visible="visible"
      :closable="false"
      modal
      :pt="{
        root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
        content: { class: 'px-0  pb-6' },
        header: { class: ' m-5' },
        footer: {class: 'bg-slate-50'}
      }"
      :style="{ width: '35rem', padding: '10px 0px' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=".custom-dialog"
    >
      <template #header>
        <div class="flex bg-slate-50 items-start justify-between w-full">
          <div class="flex items-center gap-3">
            <div class="p-3 border rounded-md bg-white">
                <Icon icon="flowbite:refresh-outline" width="30" />
            </div>
            <div>
              <h1 class="text-2xl font-medium">Re-assign Task</h1>
            </div>
          </div>
        </div>
      </template>
      <section class="bg-white mx-2 rounded-md p-5">
        <label class="text-[#344054] font-semibold  ">Assign to </label>
      <div class=" p-3 border-[2px] rounded-md mt-3">
        <div class="flex gap-3 flex-wrap items-center">
              <div v-if="Tags.length > 0" class="flex gap-2">
                <div
                  class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                  v-for="(tag, index) in Tags"
                  :key="index"
                >
                  <img :src="tag.img" alt="" class="w-5" />
                  <p class="text-xs w-fit">{{ tag.name }}</p>
                  <div class=" bg-white border rounded-full p-0.5 text-sm">
                  <Icon
                    icon="clarity:times-line"
                    class="cursor-pointer"
                    @click="removeTag(tag)"
                  />
                </div>
                </div>
              </div>
              <div class="relative">
                <input
                  type="text"
                  v-model="searchInput"
                  @input="filterRecipients"
                  class="outline-none"
                />
                <div
                  class="border border-solid py-3 bg-white rounded-md absolute z-50"
                  v-if="searchInput && Suggestions.length > 0"
                >
                  <div
                    class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                    v-for="(suggestion, index) in Suggestions"
                    :key="index"
                    @click="updateTags(suggestion)"
                  >
                    <img :src="suggestion.img" alt="" class="w-8" />
                    <p class="text-sm w-fit">{{ suggestion.name }}</p>
                  </div>
                </div>
              </div>
            </div>
      </div>

        <div class="my-5">
          <label class="text-[#344054] font-semibold ">Add Description <small class=" text-gray-500"> (optional) </small></label>
          <textarea
            name=""
            id=""
            cols="30"
            rows="5"
            placeholder="Add description"
            class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"
          ></textarea>
        </div>
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
          label="Re-assign"
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
  import image from "../../../assets/images/members/1.svg";

  const toast = useToast()
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
  

  const searchInput = ref("");
const BlindInput = ref("");
const CarbonInput = ref("");
const recipients = ref([
  {
    name: "Jenny Williams",
    img: image,
  },
  {
    name: "Dave Miye",
    img: image,
  },
  {
    name: "Obadimu charles",
    img: image,
  },
  {
    name: "Jefferson Chisom",
    img: image
  }
]);
const Suggestions = ref([]);
const Tags = ref([]);


const filterRecipients = () => {
  const searchTerm = searchInput.value.toLowerCase();
  Suggestions.value = recipients.value.filter((recipient) =>
    recipient.name.toLowerCase().includes(searchTerm)
  );
};
const updateTags = (suggestion) => {
  searchInput.value = "";
  Suggestions.value = [];

  const tagExists = Tags.value.some((tag) => tag.name === suggestion.name);

  if (!tagExists) {
    Tags.value.push(suggestion);
  }
};

const removeTag = (tagToRemove) => {
  const index = Tags.value.findIndex((tag) => tag === tagToRemove);
  if (index !== -1) {
    Tags.value.splice(index, 1);
  }
};
  const closeModal = () => {
    emit("update:modelValue", false);
  
  };
  
  const submit = () => {
    emit("update:modelValue", false);
  
      toast.add({
        severity: "success",
        summary: 'Task Submitted',
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