<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-50 max-h-[95vh] overflow-y-scroll hide_scroll px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-50' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
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
          <div v-if=" selectedUsers && selectedUsers.length > 0" class="flex gap-2">
            <div
              class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
              v-for="(user, index) in selectedUsers" :key="index">
              <img v-if="branchType" :src="page.props.base+'/'+user.profile_image" alt="" class="w-5" />
              <p v-if="!branchType" class="text-xs w-fit">{{ user.first_name+" "+user.last_name }}</p>
              <p v-else class="text-xs w-fit">{{ user.title }}</p>
              <div class=" bg-white border rounded-full p-0.5 text-sm">
                <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeUser(user)" />
              </div>
            </div>
          </div>
          <div class="relative">
            <input type="text" v-model="search" @input="filterRecipients" class="outline-none" />
            <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
              v-if="!branchType && users && users.length > 0">
              <div class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                v-for="(user, index) in users" :key="index" @click="updateUsers(user)">
                <img :src="page.props.base+'/'+user.profile_image" alt="" class="w-8" />
                <p class="text-sm w-fit">{{ user.first_name + " " + user.last_name }}</p>
              </div>
            </div>

            <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
              v-if="branchType && branch && branch.length > 0">
              <div class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                v-for="(user, index) in branch" :key="index" @click="updateUsers(user)">
                <!-- <img :src="page.props.base+'/'+user.profile_image" alt="" class="w-8" /> -->
                <p class="text-sm w-fit">{{ user.title }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="my-5">
        <label class="text-[#344054] font-semibold ">Add Description <small class=" text-gray-500"> (optional)
          </small></label>
        <textarea name="" v-model="description" id="" cols="30" rows="5" placeholder="Add description"
          class="w-full mt-3 border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Re-assign" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import image from "../../../assets/images/members/1.svg";
import debounce from "lodash.debounce"
import { usePage, router } from "@inertiajs/vue3"

const toast = useToast()
const props = defineProps(["modelValue", 'users', 'task_id']);
const emit = defineEmits(["update:modelValue"]);
const search = ref('')
const page = usePage()
const description = ref('')
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
const users = ref([])
const selectedUsers = ref([])
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
const branch = ref([])
const branchType = ref('')


const filterRecipients = () => {
  // const searchTerm = searchInput.value.toLowerCase();
  // Suggestions.value = recipients.value.filter((recipient) =>
  //   recipient.name.toLowerCase().includes(searchTerm)
  // );
  searchUsers()
};
const updateUsers = (suggestion) => {
  searchInput.value = "";
  Suggestions.value = [];
  selectedUsers.value.push(suggestion)

  let index = users.value.findIndex((item)=>item.id == suggestion.id)
  users.value.splice(index,1);
};

const removeUser = (user) => {
  console.log(user)
  users.value.push(user)
  let index = selectedUsers.value.findIndex((item)=>item.id == user.id)
  selectedUsers.value.splice(index,1)
};
const closeModal = () => {
  emit("update:modelValue", false);

};

const searchUsers = debounce(() => {
  branchType.value = ''
  axios.get('/dashboard/search-users', { 
    params: { search: search.value } }).then((response) => {
      if(response.data.type){
        branch.value = response.data.data
        branchType.value = response.data.type
      }else{
        users.value = response.data.data
      }
    
  })
}, 500)

const submit = () => {
  router.post('/dashboard/reassign-task',{users: selectedUsers.value 
    , description: description.value, task_id: props.task_id}, {onSuccess: page=>{
    emit("update:modelValue", false);
    emit('refresh')
  toast.add({
    severity: "success",
    summary: 'Task Submitted',
    detail: "Your task as been marked as complete and is under review",
    life: 3000,
  });
  }})
};

watch(()=>props.users, ()=>{
  selectedUsers.value = props.users
}, {immediate: true})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>