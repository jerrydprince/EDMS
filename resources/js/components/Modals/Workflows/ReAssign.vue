<template>
  <Dialog :visible="props.modelValue" @update:visible="(v) => emit('update:modelValue', v)" :closable="false" modal :pt="{
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
            <h1 class="text-2xl font-medium">Assign Task</h1>
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
              <img v-if="branchType" :src="getImageForGroup(user)" alt="" class="w-5" />
              <p v-if="!branchType" class="text-xs w-fit">{{ user.first_name+" "+user.last_name }}</p>
              <p v-else class="text-xs w-fit">{{ user.title }}</p>
              <div class=" bg-white border rounded-full p-0.5 text-sm">
                <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeUser(user)" />
              </div>
            </div>
          </div>
          <div class="relative w-full">
            <input 
              type="text" 
              v-model="search" 
              @input="filterRecipients" 
              @focus="onSearchFocus" 
              class="outline-none w-full" 
              placeholder="Search users..." 
            />
            <div 
              ref="pop_up" 
              class="border border-solid py-3 bg-white rounded-md absolute z-50 w-full max-h-60 overflow-y-auto shadow-lg"
              v-if="!branchType && users && users.length > 0 && search.length > 0 && !clickOutside"
            >
              <div 
                class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9] cursor-pointer"
                v-for="(user, index) in users" 
                :key="index" 
                @click="updateUsers(user)"
              >
                <img :src="getImageForGroup(user)" alt="" class="w-8" />
                <p class="text-sm w-fit">{{ user.first_name + " " + user.last_name }}</p>
              </div>
            </div>

            <div 
              ref="pop_up2" 
              class="border border-solid py-3 bg-white rounded-md absolute z-50 w-full max-h-60 overflow-y-auto shadow-lg"
              v-if="branchType && branch && branch.length > 0 && search.length > 0 && !clickOutside2"
            >
              <div 
                class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9] cursor-pointer"
                v-for="(user, index) in branch" 
                :key="index" 
                @click="updateUsers(user)"
              >
                <img :src="getImageForGroup(user)" alt="" class="w-8" />
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
        <Button label="Assign" class="" :pt="{
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
import { onClickOutside } from '@vueuse/core'
import axios from 'axios';

const toast = useToast()
const props = defineProps(["modelValue", 'users', 'task_id', 'userType']);
const emit = defineEmits(["update:modelValue"]);
const search = ref('')
const page = usePage()
const description = ref('')
const pop_up = ref(null)
const pop_up2 = ref(null)
const clickOutside = ref(false)
const clickOutside2 = ref(false)

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

onClickOutside(pop_up, () => {
  clickOutside.value = true;
})

onClickOutside(pop_up2, () => {
  clickOutside2.value = true;
})

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

function getImageForGroup(user) {
    if(branchType.value == 'unit'){
      return page.props.base + "/images/unit_active.svg";
    }else if(branchType.value == 'division'){
      return page.props.base + "/images/division_active.svg";
    }else if(branchType.value == 'department'){
       return page.props.base + "/images/department_active.svg";
    }else{
       if(user.profile_image){
        return page.props.base + "/" + user.profile_image;
       }else{
        return page.props.base + "/images/user.svg";
       }
    }
}

const onSearchFocus = () => {
  clickOutside.value = false;
  clickOutside2.value = false;
}

const filterRecipients = () => {
  // Reset click outside when typing
  clickOutside.value = false;
  clickOutside2.value = false;
  
  // Only search if there's text
  if(search.value.trim().length > 0) {
    searchUsers();
  } else {
    users.value = [];
    branch.value = [];
    branchType.value = '';
  }
};

const updateUsers = (suggestion) => {
  // Check if user is already selected
  const alreadySelected = selectedUsers.value.some(user => user.id === suggestion.id);
  
  if (!alreadySelected) {
    selectedUsers.value.push(suggestion);
    
    // Remove from users list
    if(branchType.value) {
      let index = branch.value.findIndex((item) => item.id == suggestion.id);
      if(index !== -1) {
        branch.value.splice(index, 1);
      }
    } else {
      let index = users.value.findIndex((item) => item.id == suggestion.id);
      if(index !== -1) {
        users.value.splice(index, 1);
      }
    }
  }
  
  // Clear search
  search.value = "";
  users.value = [];
  branch.value = [];
};

const removeUser = (user) => {
  // Add back to appropriate list
  if(user.title) {
    branch.value.push(user);
  } else {
    users.value.push(user);
  }
  
  // Remove from selected
  let index = selectedUsers.value.findIndex((item) => item.id == user.id);
  if(index !== -1) {
    selectedUsers.value.splice(index, 1);
  }
};

const closeModal = () => {
  // Reset state
  search.value = '';
  description.value = '';
  users.value = [];
  branch.value = [];
  branchType.value = '';
  clickOutside.value = false;
  clickOutside2.value = false;
  
  emit("update:modelValue", false);
};

const searchUsers = debounce(() => {
  if(search.value.trim().length === 0) {
    users.value = [];
    branch.value = [];
    branchType.value = '';
    return;
  }
  
  branchType.value = '';
  users.value = [];
  branch.value = [];
  
  axios.get('/dashboard/search-users', { 
    params: { search: search.value, user_type: props.userType } 
  }).then((response) => {
    console.log(response);
    
    if(response.data.type == 'unit' || response.data.type == 'division' || response.data.type == 'department'){
      branch.value = response.data.data.filter(item => 
        !selectedUsers.value.some(selected => selected.id === item.id)
      );
      branchType.value = response.data.type;
      clickOutside2.value = false;
    } else {
      users.value = response.data.data.filter(item => 
        !selectedUsers.value.some(selected => selected.id === item.id)
      );
      clickOutside.value = false;
    }
  }).catch(error => {
    console.error('Search error:', error);
    users.value = [];
    branch.value = [];
  });
}, 500);

const submit = () => {
  if(selectedUsers.value.length === 0) {
    toast.add({
      severity: "warn",
      summary: 'No Users Selected',
      detail: "Please select at least one user to assign the task",
      life: 3000,
    });
    return;
  }
  
  const assignedIds = selectedUsers.value.map(user => user.id);

  router.post(`/dashboard/workflow/delegate/${props.task_id}`, {
    assigned_to: assignedIds, 
    comments: description.value, 
  }, {
    onSuccess: page => {
      emit("update:modelValue", false);
      emit('refresh');
      selectedUsers.value = [];
      description.value = '';
      toast.add({
        severity: "success",
        summary: 'Task Submitted',
        detail: "Your task has been assigned",
        life: 3000,
      });
    }
  });
};

watch(() => props.users, (newUsers) => {
  if (!newUsers) {
    selectedUsers.value = [];
  } else if (Array.isArray(newUsers)) {
    selectedUsers.value = [...newUsers];
  } else {
    selectedUsers.value = [newUsers];
  }
}, { immediate: true });

watch(() => props.modelValue, (newVal) => {
  if(!newVal) {
    // Reset when modal closes
    search.value = '';
    users.value = [];
    branch.value = [];
    branchType.value = '';
    clickOutside.value = false;
    clickOutside2.value = false;
  }
});
</script>

<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
