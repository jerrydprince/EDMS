<template>
  <main class="">
    <div class="flex items-center justify-between pt-5 px-7">
      <div class="flex items-center gap-5">
        <div class="border rounded-full p-3">
          <Icon icon="mynaui:users" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939]">Team Management</h1>
          <p class="text-gray-500">
            Manage your user account permissions here.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <div class="flex">
          <a href="#" class="text-[#2F68C5]" v-if="props.notifications" @click="account_request = true">Account Request
            <span v-if="props.notifications.length > 0"
              class=" bg-red-600 text-white h-[22px] w-[22px] inline-block text-center rounded-full align-middle items-center text-sm">{{
                props.notifications.length }}</span></a>
        </div>
        <button class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
          <Icon icon="material-symbols:download" width="20" />
          <p>Download CSV</p>
        </button>
        <Link href="/dashboard/add-user">
        <button class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4">
          <Icon icon="material-symbols:add" width="20" />
          <p>Add User</p>
        </button>
        </Link>
      </div>
    </div>
    <Divider class=" " />

    <section class="lg:flex items-center justify-between px-7">
      <div class="flex items-center justify-between lg:w-[40%] text-sm">
        <div class="bg-gray-100 flex items-center rounded-md p-1">
          <button class="rounded-lg py-1 px-4" @click="setActive(1)" :class="{ activeButton: buttonToggle === 1 }">
            <p>All</p>
          </button>
          <div>
            <button class="flex gap-2 items-center text-gray-400 rounded-lg py-1 px-4"
              :class="{ activeButton: buttonToggle === 2 }" @click="toggle" aria-haspopup="true"
              aria-controls="overlay_menu">
              <p>Account</p>
              <Icon icon="flowbite:angle-down-outline" class="" />
            </button>
            <TieredMenu ref="menu" id="overlay_tmenu" :model="props.roles" popup style="width: 200px">
              
              <template #item="{ item, props }">
                <a v-if="item.label"  class="flex align-items-center gap-2 px-[15px] py-[8px]" v-bind="props.action">
                  <div v-if="item.icon">
                    <Icon :icon="item.icon" />
                  </div>
                  <span class="">{{ item.label }}</span>
                </a>
                <a v-else  class="flex align-items-center gap-2 px-[15px] py-[8px]" v-bind="props.action"
                  @click="handleFilterOption(item.name)">
                  <div v-if="item.icon">
                    <Icon :icon="item.icon" />
                  </div>
                  <span class="">{{ capitalizeWords(item.name) }}</span>
                </a>
              </template>
            </TieredMenu>
          </div>

          <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4" @click="setActive(3)"
            :class="{ activeButton: buttonToggle === 3 }">
            <p>Sort Branch</p>
          </button>
        </div>
      </div>

      <div class="flex items-center border px-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <input type="text"  placeholder="Search Users" class="w-96 border-0 focus:outline-none outline-none text-[#98A2B3]" />
      </div>
    </section>

    <Divider class=" " />

    <div>
      <div v-for="(item, index) in filteredRoleUsers" :key="index" class="mb-5">
        <div v-if="!item.label"  class="flex items-start justify-between pl-7 pr-14">
          <div class="w-[25%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              {{ capitalizeWords(item.name) }}
            </h1>
            <p class="text-[#667085] mt-2">{{ item.description }}</p>
          </div>
          <div class=" ">
            <DataTable  :rows="4" :value="item.users" dataKey="id" :pt="{
              table: {
                class: ' border border-border_primary border-solid mb-6 mt-2',
              },
            }" tableStyle="min-width: 50rem; border-radius: 100px;" style="width: ">
              <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
              <Column field="name" header="Name" :pt="{
                bodyCell: { class: 'text-gray-700' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 300px">
                <template #body="slotProps">
                  <div class="font-medium">
                    <p>{{ slotProps.data.last_name+" "+slotProps.data.first_name }}</p>

                    <p class="text-[#667085]">{{ slotProps.data.email }}</p>
                  </div>
                </template>
              </Column>
              <Column field="date" header="Date Added" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
                <template #body="slotProps">
                  <div>
                    <p>{{formatDate(slotProps.data.created_at, false)}}</p>
                  </div>
                </template>
              </Column>
              <Column field="updated_at" header="Last Active" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              <template #body="slotProps">
                <div>
                  <p>{{formatDate(slotProps.data.updated_at, true)}}</p>
                </div>
              </template>
              </Column>
              <Column field="" header="Actions" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;">
                <template #body="slotProps">
                  <div class="flex gap-3 items-center">
                    <Link :href="'/dashboard/edit-user?id='+slotProps.data.id" class="p-1 font-semibold border rounded-md border-gray-300 cursor-pointer">
                      <Icon icon="mingcute:pencil-2-line" width="25" />
                    </Link>
                    <div @click="showDeleteDialog(slotProps.data.id)" class=" cursor-pointer">
                      <Icon icon="mingcute:delete-2-line" width="25" />
                    </div>
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
        <div class="flex justify-end px-20" v-if="!item.label">
          <Link :href="'/dashboard/role-users?role_id='+item.id">
          <button class="flex items-center gap-2 text-[#2F68C5]">
            <p>View All</p>
            <Icon icon="flowbite:angle-right-outline" />
          </button>
          </Link>
        </div>
      </div>
    </div>

   
  </main>
  <DeleteUsers v-if="deleteModal" @confirmed="handleDelete" @cancelled="cancelDelete" />
  <AccountRequestModal v-model="account_request" :notifications="props.notifications" />
</template>

<script setup>
import { ref, watch, defineProps, computed, onMounted } from "vue";
import DeleteUsers from "../../components/Modals/DeleteUsers.vue";
import AccountRequestModal from "../../components/Modals/AccountRequestModal.vue";
import { Link } from '@inertiajs/vue3'

const deleteModal = ref(false)
const buttonToggle = ref(1);
const selectedProduct = ref();
const searchQuery = ref('');
const itemIdToDelete = ref(null);
const props = defineProps(['notifications', 'roles']);
const account_request = ref(false);
const newRoles = ref(props.roles);

const setActive = (buttonNumber) => {
  buttonToggle.value = buttonNumber;
  if (buttonToggle.value > 2 || buttonToggle.value < 2) {
    searchQuery.value = ''
  }
};

const formatDate = (data, last_active)=>{
  let newDate =data;

  // Convert the Laravel date string to a JavaScript Date object
  let date = new Date(newDate);

  // Get the day, month, and year from the Date object
  let day = date.getDate();
  let monthIndex = date.getMonth();
  let year = date.getFullYear();

  // Define an array of month names
  let monthNames = [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
  ];

  // Format the date string
  let formattedDate = `${day} ${monthNames[monthIndex]} ${year.toString().slice(-2)}`;
  let formattedDate2 = ` ${monthNames[monthIndex]} ${year.toString().slice(-2)}`;

  let new_date = last_active? formattedDate2: formattedDate

  return new_date;

}

const showDeleteDialog = (itemId) => {
  itemIdToDelete.value = itemId;
  deleteModal.value = !deleteModal.value
};

const handleDelete = (id) => {
  axios.get('/dashboard/delete-user/'+itemIdToDelete.value).then((response)=>{
    itemIdToDelete.value = null;
    deleteModal.value = !deleteModal.value
    router.reload({only:['role']})
  })
};

const cancelDelete = () => {
  deleteModal.value = !deleteModal.value
}
//const filteredItems = ref([...data]);
const handleFilterOption = (label) => {
  searchQuery.value = label;

};

// watch(searchQuery, (newValue) => {
//   filteredItems.value = data.filter(item => {
//     return item.name.toLowerCase().includes(newValue.toLowerCase());
//   });
//   if (buttonToggle.value > 2) {

//     searchQuery.value = ''
//   }

// });
const menu = ref(null);

const capitalizeWords = (str)=> {
  return str.replace(/\b\w/g, function(char) {
    return char.toUpperCase();
  });
}

const toggle = (event) => {
  menu.value.toggle(event);

  console.log(queueMicrotask);
  buttonToggle.value = 2;
};

const items = {
    label: "Filter",
    icon: "octicon:filter-16",
  }

  const filteredRoleUsers = computed(()=>{

    if(searchQuery.value !==''){
      let searched = newRoles.value.filter((val)=>{
        if(!val.label){
          return val.name.toLowerCase() == searchQuery.value.toLocaleLowerCase();
        }
        
      })
      return searched;
    }else{
      return props.roles
    }
  })

  onMounted(() => {
    newRoles.value  = props.roles;
    newRoles.value.unshift(items);
  })

</script>

<style scoped>
.activeButton {
  background-color: white;
  border-radius: 5px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}</style>