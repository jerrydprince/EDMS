<template>
  <main class="p-7">
    <div class="bg-white border border-gray-200 rounded-xl lg:flex items-start p-7 w-full justify-between">
      <div>
        <h1 class="text-4xl">Hello, {{user.first_name}}!</h1>
        <div class="text-sm flex items-center gap-5 mt-4">
          <p class="text-gray-500">welcome to your PMS dashboard</p>
          <!-- <div class="flex items-center gap-2 bg-primary/10 p-1 px-2 rounded-2xl">
            <Icon icon="mynaui:key" />
            <p>Brand lead</p>
          </div> -->
        </div>
      </div>
      <Link :href="'/dashboard/pms-current-user-profile'" class=" mt-3 lg:mt-0 flex items-center gap-2 border p-2 border-gray-300 rounded-md" v-if="roles.includes('directors') 
      || roles.includes('division heads') || roles.includes('unit heads') || roles.includes('regular users')">
        <Icon icon="ph:eye" />
        <p>View Rating</p>
    </Link>
    </div>

     <div  class="p-7 bg-white border o  relative border-gray-200 mt-10 rounded-xl">
      <div v-if="user_roles.includes('super admin') || user_roles.includes('super users') || user_roles.includes('admin') ">
        <h1 class=" text-gray-500">Currently viewing</h1>
        <select @change="changeDepartment" name="" v-model="department"  id=""
          class=" bg-gray-50 border border-gray-300 rounded-3xl p-2 outline-none  mt-2 mb-8 min-w-[300px]  ">
          <option v-for="dept in props.departments" :value="dept.id">{{ dept.title }}</option>

        </select>
      </div>
      <div class=" overflow-x-scroll  lg:overflow-hidden " v-if="!props.roles.includes('regular users')">
        <table class=" min-w-max w-full  border">
          <thead class="bg-gray-100">
            <tr class="text-left">
              <!-- <th class="py-3 pl-5">
                <Checkbox v-model="selectAll" @update:modelValue="toggleAll" class="h-5 w-5" />
              </th> -->
              <th class="pl-5 py-3">#</th>
              <th>Name</th>
              <!-- <th>Branch</th> -->
              <th>Position</th>
              <!-- <th>Performance Status</th> -->
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in departmentUsers" :key="index" class="hover:bg-gray-50 cursor-pointer border-b">
              <!-- <td class="align-middle pl-5">
                <div class="flex items-center gap-2">
                  <Checkbox v-model="checkedItems[index]" class="h-5 w-5" />
                </div>
              </td> -->

              <td class="pl-5">{{ index+1 }}</td>
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <div>
                    <p class="text-base">{{ item.first_name+' '+item.last_name }}</p>
                  </div>
                </div>
              </td>

              <!-- <td class="align-middle">
                <div class="">
                  <p>{{ item.branch }}</p>
                </div>
              </td> -->
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <p>{{ item.roles[0]?.name }}</p>
                </div>
              </td>
              <!-- <td class="align-middle">
                <p :style="{
            'color':
              item.status === 'Performing'
                ? '#0D6E1A'
                : item.status === 'Progressing'
                  ? '#FB6514'
                  : item.status === 'Regressing'
                    ? '#F79009'
                    : item.status === 'Non-Performing' ? '#F04438' : 'black',
          }">• {{ item.status }}</p>
              </td> -->
              <td class="align-top py-3">
                <div class="flex items-center gap-3">

                  <Link :href="'/dashboard/pms-user-profile/'+item.id">
                    <button class="flex items-center gap-2 border p-2 border-gray-300 rounded-md">
                      <Icon icon="ph:eye" />
                      <p class="text-sm">View Profile</p>
                    </button>
                  </Link>
                  <!-- <button class=" border p-2 border-gray-300 rounded-md">
                    <Icon icon="ph:file" />
                  </button> -->
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class=" absolute bottom-0  p-2 rounded-xl flex items-center left-[30%]  bg-[#052B0A] justify-between "
        v-if="checkedCount">
        <div class=" flex items-center gap-2 text-white text-sm ml-3 ">
          <p class=" ">{{ checkedCount }} Profile<span v-if="checkedCount > 1">s</span> selected</p>
          <button class=" cursor-pointer" @click="removeProfile">
            <Icon icon="mdi:times" />
          </button>
        </div>
        <button class=" ml-40 bg-white text-[#052B0A] text-xs p-2 py-3 rounded-md flex items-center gap-1">
          <p>Generate Report</p>
          <Icon icon="mdi:file-outline" />
        </button>
      </div>
    </div>
    <div class=" mt-5">
      <!-- <paginator
        :totalItems="totalItems"
      :itemsPerPage="itemsPerPage"
      v-model="currentPage"
        /> -->
    </div>
  </main>
</template>

<script setup>
import axios from "axios";
import { ref, watch, computed, defineProps, onMounted } from "vue";
import {Link, usePage} from "@inertiajs/vue3"
//import paginator from "@/reusables/paginations.vue";
const currentPage = ref(1);
const itemsPerPage = 10;
const props = defineProps(['users', 'departments', 'roles'])
const department = ref();
const departmentUsers = ref([])
const page = usePage()
const user = computed(()=> page.props.user);
const user_roles = computed(()=> page.props.user_roles);

const changeDepartment = ()=>{
  axios.get('/dashboard/get-department-users',{params:{id: department.value}}).then((response)=>{
    if(response.data){
      departmentUsers.value = response.data
    }
  })
}

const pageStart = ref(1);
const pageEnd = ref(1);
// const displayedItems = computed(() => {
//   const startIndex = (currentPage.value - 1) * itemsPerPage;
//   const endIndex = startIndex + itemsPerPage;

//   pageStart.value = startIndex;
//   pageEnd.value = endIndex;

//   //return items.value.slice(startIndex, endIndex);
// });
// const totalItems = computed(() => items.value.length);

 const checkedItems = ref([]);

// const selectAll = ref(false);

// const toggleAll = () => {
//   const newState = selectAll.value;
//   checkedItems.value = checkedItems.value.map(() => newState);
// };

// // Watch for changes in individual checkboxes to update the "select all" checkbox
// watch(checkedItems, (newCheckedItems) => {
//   const allChecked = newCheckedItems.every(checked => checked);
//   if (selectAll.value !== allChecked) {
//     selectAll.value = allChecked;
//   }
// }, { deep: true });

// const checkedCount = computed(() => {
//   return checkedItems.value.filter(checked => checked).length;
// });

watch(()=>props.departments, ()=>{

  if(props.departments && props.departments.length > 0){
     department.value = props.departments[0].id
  }
}, {immediate:true})

watch(()=>props.users, ()=>{
  departmentUsers.value = props.users
}, {immediate:true})
</script>

<style scoped></style>