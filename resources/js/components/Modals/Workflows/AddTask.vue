<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '35rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
	 <template #header>
        <div class=" w-full"> 
      <h1 class=" text-3xl text-[#98A2B3]">Task title...</h1>

      <div class=" mt-5  flex items-center justify-between w-[100%]">
         <div class=" w-[48%]">
            <label for="Start Date " class=" font-semibold">Start Date</label> <br>
            <input type="date" class=" outline-none border rounded-md  w-full p-2">
         </div>
         <div class=" w-[48%]">
            <label for="Start Date" class=" font-semibold">Due Date</label> <br>
            <input type="date" class=" outline-none border rounded-md w-full p-2 ">
         </div>
      </div>
    </div>
     </template>
	

        <section class=" px-6">
       
            <h1 class=" font-semibold text-[#1D2939] mb-1">Attach Document</h1>
         <div
      class="  bg-[#FCFCFD] border-dashed flex flex-col items-center rounded-xl border-[3px] py-3"
    >
      <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
      <div class="text-center">
        <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
        <p class="text-sm leading-5 mb-2 text-[#475467]">
          JPEG, PNG, PDF, MP3 & other formats
        </p>
      </div>
<div class=" flex items-center gap-5"> 
      <button class="bg-white border-gray-300 border p-2 rounded-lg relative">
        <input type="file" class="opacity-0 absolute w-full" />Browse File
      </button>
      <button class="bg-white border-gray-300 border p-2 rounded-lg relative">
        <input type="file" class="opacity-0 absolute w-full" />Select from registry
      </button>
    </div>
    </div>

    <div  class=" mt-4">
        <label for="" class=" font-semibold text-[#1D2939] mb-1">Description</label> <br>
        <textarea name="" id="" cols="20" rows="3" class=" border rounded-md resize-none w-full"></textarea>

    </div>
   
    <div class=" mt-3">
        <h1 class="  font-semibold text-[#1D2939] mb-1">Assigned to</h1>
        <div class="border border-gray-300 rounded-md p-2">
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
                    class="flex items-center p-2 px-3 gap-2 hover:bg-primary/10"
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
</div>
<div class="mt-3">
    <label for=""  class="  font-semibold text-[#1D2939] mb-1">Document Type</label>
    <select name="" id="" class=" w-full border border-gray-300 rounded-md p-2 outline-none">
        <option value="">Select Document type</option>
        <option value="PDF">PDF</option>

    </select>
</div>
        </section>
        <Divider class=" -mb-2"/>
		<template #footer>
			<div class=" flex justify-between items-end w-full">
                <div class=" flex flex-col items-start ">
                    <label for="" class=" font-semibold text-[#1D2939] mb-1">Status</label> 
                    <select name="" id="" class=" py-2 text-[#344054] font-semibold  px-2  outline-none border-[2px] rounded-md border-gray-300">
                      <option value="In Progress">In Progress</option>
                      <option value="Under Review">Under Review</option>
                      <option value="Completed">Completed</option>
                      <option value="Uncompleted">Uncompleted</option>
                    </select>
                </div>
                <div>
                <Button
            @click="closeModal"
				label="Cancel"
				class=""
				:pt="{
          root: {
            class:
              'py-3  px-10 border border-gray-300 text-[#344054] font-light  rounded-lg',
          },
        }"
				
			/>
            <Button
            @click="submit"
				label="Assign"
				class=" ml-3"
				:pt="{
          root: {
            class:
              'py-3  px-10 border border-gray-300 text-white bg-primary font-light  rounded-lg',
          },
        }"
				
			/>
        </div>
            </div>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
import image from "../../../assets/images/members/1.svg";

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const selectedIndex = ref(null)

const searchInput = ref("");
const Tags = ref([]);
const Suggestions = ref([]);
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
  selectedIndex.value = null
  Tags.value = []
};

const submit = () => {
  emit("update:modelValue", false);
    selectedIndex.value = null
    Tags.value = []
//   toast.add({
//     severity: "success",
//     summary: '',
//     detail: "",
//     life: 3000,
//   });
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; 
    width: 20%;
  }
  .p-inputtextarea {
    outline: none;
    box-shadow: none !important;
    resize: none;
    width: 100%
}
.p-inputtextarea:focus {
    border-color: #D0D5DD; /* Hide border */
}
</style>
