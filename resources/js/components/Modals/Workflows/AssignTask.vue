<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6 overflow-y-scroll' },
  }" :style="{ width: '35rem', padding: '10px 0px'}" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=" .custom-dialog">
    <template #header>
      <div class=" w-full">
        <!-- <h1 class=" text-3xl text-[#98A2B3]">Task title...</h1> -->
        <input type="text" v-model="title" id="title" placeholder="Task title..." class="text-3xl w-full border-0 p-2 focus:border-0">

        <div class=" mt-5  flex items-center justify-between w-[100%]">
          <div class=" w-[48%]">
            <label for="Start Date " class=" font-semibold">Start Date</label> <br>
            <input type="date" v-model="startDate" id="start_date" class=" outline-none border rounded-md  w-full p-2">
          </div>
          <div class=" w-[48%]">
            <label for="Start Date" class=" font-semibold">Due Date</label> <br>
            <input type="date" v-model="endDate" id="end_date" class=" outline-none border rounded-md w-full p-2 ">
          </div>
        </div>
      </div>
    </template>


    <section class=" px-6">

      <h1 class=" font-semibold text-[#1D2939] mb-1">Attach Document</h1>
      <div class="  bg-[#FCFCFD] border-dashed flex flex-col items-center rounded-xl border-[3px] py-3">
        <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
        <div class="text-center">
          <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
          <p class="text-sm leading-5 mb-2 text-[#475467]">
            JPEG, PNG, PDF, MP3 & other formats
          </p>
        </div>

        <button class="bg-white border-gray-300 border p-2 rounded-lg relative">
          <input type="file" id="file" @change="handleFileUpload" class="opacity-0 absolute w-full" />Browse File
        </button>
      </div>

      <div class=" mt-4">
        <label for="" class=" font-semibold text-[#1D2939] mb-1">Description</label> <br>
        <textarea v-model="description" id="description" cols="20" rows="3" class=" border rounded-md resize-none w-full"></textarea>

      </div>

      <div class=" mt-3">
        <h1 class="  font-semibold text-[#1D2939] mb-1">Assigned to</h1>
        <div class="border border-gray-300 rounded-md p-2">
          <div class="flex gap-3 flex-wrap items-center">
            <div v-if="Tags.length > 0" class="flex gap-2">
              <div
                class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                v-for="(tag, index) in Tags" :key="index">
                <img v-if="tag.profile_image" :src="page.props.base+tag.img" alt="" class="w-5" />
                <p class="text-xs w-fit">{{ tag.first_name+" "+tag.last_name }}</p>
                <div class=" bg-white border rounded-full p-0.5 text-sm">
                  <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeTag(tag)" />
                </div>
              </div>
            </div>
            <div class="relative w-full">
              <input type="text" id="search" v-model="searchInput" @input="filterRecipients" class="outline-none w-full" />
              <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
                v-if="searchInput && Suggestions.length > 0">
                <div class="flex items-center p-2 px-3 gap-2 hover:bg-primary/10"
                  v-for="(suggestion, index) in Suggestions" :key="index" @click="updateTags(suggestion)">
                  <img v-if="suggestion.profile_image" :src="page.props.base+suggestion.profile_image" alt="" class="w-8" />
                  <p class="text-sm w-fit">{{ suggestion.first_name+" "+suggestion.last_name }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <label for="" class="  font-semibold text-[#1D2939] mb-1">Document Type</label>
        <select v-model="documentType" id="document_type" class=" w-full border border-gray-300 rounded-md p-2 outline-none">
          <option value="">Select Document type</option>
          <option value="PDF">PDF</option>

        </select>
      </div>
    </section>
    <Divider class=" -mb-2" />
    <template #footer>
  
      <div class=" flex justify-center w-full">
        <!-- <div class=" flex flex-col items-start ">
          <label for="" class=" font-semibold text-[#1D2939] mb-1">Status</label>
          <select name="" id=""
            class=" py-2 text-[#344054] font-semibold  px-2  outline-none border-[2px] rounded-md border-gray-300">
            <option value="In Progress">In Progress</option>
            <option value="Under Review">Under Review</option>
            <option value="Completed">Completed</option>
            <option value="Uncompleted">Uncompleted</option>
          </select>
        </div> -->
        <div>
          <Button @click="closeModal" label="Cancel" class="" :pt="{
    root: {
      class:
        'py-3  px-10 border border-gray-300 text-[#344054] font-light  rounded-lg',
    },
  }" />
          <Button @click="submit" label="Assign" class=" ml-3" :pt="{
    root: {
      class:
        'py-3  px-10 border border-gray-300 text-white bg-primary font-light  rounded-lg',
    },
  }" />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import {usePage} from '@inertiajs/vue3'

const props = defineProps(["modelValue", "workflowId"]);
const emit = defineEmits(["update:modelValue"]);
const selectedIndex = ref(null)
const startDate = ref(null);
const endDate = ref(null);
const title = ref('');
const description = ref('');
const documentType = ref('');
const page = usePage()
const file = ref(null)


const searchInput = ref("");
const Tags = ref([]);
const Suggestions = ref([]);
// const recipients = ref([
//   {
//     name: "Jenny Williams",
//     img: image,
//   },
//   {
//     name: "Dave Miye",
//     img: image,
//   },
//   {
//     name: "Obadimu charles",
//     img: image,
//   },
//   {
//     name: "Jefferson Chisom",
//     img: image
//   }
// ]);

const handleFileUpload = (event)=> {
  console.log(event.target.files[0])
      file.value = event.target.files[0];
    
  }

const filterRecipients = () => {
  const searchTerm = searchInput.value.toLowerCase();
  searchUsers()
  // Suggestions.value = recipients.value.filter((recipient) =>
  //   recipient.name.toLowerCase().includes(searchTerm)
  // );

};
const updateTags = (suggestion) => {
  searchInput.value = "";
  Suggestions.value = [];

  const tagExists = Tags.value.some((tag) => tag.first_name+" "+tag.last_name === suggestion.first_name+" "+suggestion.last_name);

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

const searchUsers = ()=>{
  axios.get('/admin/search-users',{params:{search: searchInput.value}}).then((response)=>{
    Suggestions.value = response.data
    console.log(response.data)
  })
}

const submit = () => {
  if(title.value == ""){
    document.getElementById('title').focus();
    return false;
  }
  if(startDate.value == null){
    document.getElementById('start_date').focus();
    return false
  }
  if(endDate.value == null){
    document.getElementById('end_date').focus();
    return false
  }
  if(Tags.value.length == 0){
    console.log(Tags)
    document.getElementById('search').focus();
    return false;
  }

  let userIds = [];
  Tags.value.forEach((val)=>{
    userIds.push(val.id);
  })
 
console.log(userIds)
      let data = new FormData();
      data.append('title', title.value);
      data.append('start', startDate.value);
      data.append('end', endDate.value);
      data.append('description', description.value);
      data.append('file', file.value);
      data.append('documentType', documentType.value);
      data.append('userIds', userIds); 
      data.append('workflow_id', props.workflowId);

  axios.post('/admin/create-task', data).then((response)=>{
    title.value = ''
    startDate.value = null
    endDate.value = null
    description.value = ''
    file.value = null
    documentType.value = '';
    workflowId.value = '';
    userIds = []

  })

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
  border-color: #D0D5DD;
  /* Hide border */
}


</style>