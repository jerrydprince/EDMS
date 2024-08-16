<template>
  <div>
    <div class="lg:flex items-center relative justify-between pt-5 px-7">
      <div class="flex items-center gap-5">
        <div class="border rounded-full p-3">
          <Icon icon="mdi:cloud-key-outline" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939]">Access Level Configuration</h1>
          <p class="text-gray-500">Manage access level permissions</p>
        </div>
      </div>
      <div class="mt-3 lg:mt-0 flex items-center gap-7">
        <button @click="create_levels = true"
          class="flex gap-2 items-center border font-semibold border-gray-500 text-gray-700 rounded-lg py-2 px-3">
          <Icon icon="ei:plus" width="25" />
          <p>Create Access Level</p>
        </button>
      </div>
    </div>
    <Divider class=" " />
  </div>
  <main class="px-7">
    <DataTable :rows="4" :value="props.roles" dataKey="id" :pt="{
          table: {
            class: ' border border-border_primary w-full border-solid mb-6 mt-2',
          },
        }" tableStyle="min-width: 50rem; border-radius: 100px;" style="width: ">
      <Column field="id" header="S/N"> </Column>
      <Column field="name" header="Access Level"> </Column>
      <Column field="users_count" header="No Of Users"> </Column>

      <Column :body="formatDateColumn" header="Created Date">
        <template #body="slotProps">
          <div>
            {{ formatDate(slotProps.data.created_at) }}
          </div>
        </template>
       </Column>

      <Column field="updated_at" header="Last Modified">
        <template #body="slotProps">
          <div>
            {{ formatDate(slotProps.data.updated_at) }}
          </div>
        </template>
       </Column>
      <Column field="status" header="Status">
        <template #body="slotProps">
          <div class="flex gap-3 items-center">
            <!-- <p>{{ slotProps.data.status }}</p> -->
            <label class="switch">
              <input type="checkbox" :checked="slotProps.data.status === 1"
                @change="toggleStatus(slotProps.data)" />
              <span class="slider round"></span>
            </label>
          </div>
        </template>
      </Column>
      <Column field="" header="Actions" :pt="{
          bodyCell: { class: 'text-gray-500' },
        }" headerStyle="color: #667085; font-weight: 500;">
        <template #body="slotProps">
          <div class="flex gap-3 items-center">
            <div class="p-1 font-semibold border rounded-md cursor-pointer border-gray-300"
              @click="editPermission(slotProps.data)">
              <Icon icon="mingcute:pencil-2-line" width="25" />
            </div>
            <div @click="deleteLevel(slotProps.data)" class="cursor-pointer">
              <Icon icon="mingcute:delete-2-line" width="25" />
            </div>
          </div>
        </template>
      </Column>
    </DataTable>
  </main>
  <CreateLevels v-model="create_levels" />
  <EditPermission v-model="edit_permission" :selectedPermission="selectedLevel.permissions" :permissions="props.permissions" :role_id="selectedLevel.id"/>
  <DeleteLevel v-model="delete_level" :id="selectedLevel.id" />
  <Deactivate v-model="deactivate_modal" @refresh="refreshPage" :id="selectedLevel.id" />
</template>

<script setup>

import { ref, defineProps } from "vue";
import CreateLevels from "../../components/Modals/AccessLevel/CreateLevel.vue";
import EditPermission from "../../components/Modals/AccessLevel/EditPermission.vue";
import DeleteLevel from "../../components/Modals/AccessLevel/DeleteLevel.vue";
import Deactivate from "../../components/Modals/AccessLevel/Deactivate.vue";
import { router } from "@inertiajs/vue3";

const deactivate_modal = ref(false);
const delete_level = ref(false);
const create_levels = ref(false);
const edit_permission = ref(false);
const props = defineProps(['roles', 'permissions'])
const selectedLevel = ref([])

const accessLevelData = ref([
  {
    SNo: 1,
    AccessLevel: "Super Admin",
    NoOfUsers: 3,
    CreatedDate: "2024-01-15",
    LastModified: "2024-01-15",
    Status: "Active",
    id: 1,
  },
  {
    SNo: 2,
    AccessLevel: "Administrator",
    NoOfUsers: 10,
    CreatedDate: "2024-02-10",
    LastModified: "2024-02-10",
    Status: "Active",
    id: 2,
  },
  {
    SNo: 3,
    AccessLevel: "Records Manager",
    NoOfUsers: 3,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 3,
  },
  {
    SNo: 4,
    AccessLevel: "Super Users",
    NoOfUsers: 10,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 4,
  },
  {
    SNo: 5,
    AccessLevel: "Directors",
    NoOfUsers: 50,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 5,
  },
  {
    SNo: 6,
    AccessLevel: "Division Heads",
    NoOfUsers: 5,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 6,
  },
  {
    SNo: 7,
    AccessLevel: "Unit Head",
    NoOfUsers: 50,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 7,
  },
  {
    SNo: 8,
    AccessLevel: "Data Entry Personnel",
    NoOfUsers: 5,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Inactive",
    id: 8,
  },
]);

const deleteLevel = (data)=>{
  selectedLevel.value = data
  delete_level.value = true
  
}

const toggleStatus = (item) => {
  selectedLevel.value = item
 
 let status = item.status === 1 ? 0 : 1;

  if (status === 0) {
    deactivate_modal.value = true;
   
  }else{
    router.post('/dashboard/change-accessLevel-status', {status: 1, id: item.id}, {onSuccess: ()=>{
    router.reload({only: ['roles']})
  }})
  }
};

const refreshPage = ()=>{
  router.reload({only: ['roles']})
}

function editPermission(level){
  selectedLevel.value = level
  edit_permission.value = true
}

const formatDate = (date) => {
  if (!date) return '';
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
}

</script>

<style scoped>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked+.slider {
  background-color: #12b76a;
}

input:focus+.slider {
  box-shadow: 0 0 1px #12b76a;
}

input:checked+.slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>