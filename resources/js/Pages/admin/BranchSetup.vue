<style scoped>
  tr.submenu td:first-child::before {
    content: '';
    display: block;
    border-top: 1px solid #DFDFDF;
    position: relative;
    left: -64px;
    width: 11%;
    top: 13px;
}

tr.submenu td:first-child {
    border-left: 1px solid #DFDFDF;
}

div.table{
  display: table;
  width: 100%;
}

div.thead{
  display: table-header-group;
  background: #F9FAFB;
  height: 44px;
}

div.table-row{
  display: table-row;
  cursor: pointer;
}

div.table-row:hover{
  background: #EAECF0;
}

/* .card{
  padding: 1px;
} */

div.td{
  display: table-cell;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 1rem;
  padding-right: 1rem;
  border-bottom: 1px solid #EAECF0;
}

div.table-row:last-child div.td{
  border-bottom: none;
}

div.tbody{
  display: table-row-group;
}

div.td:first-child {
    padding-left: 5%;
}
</style>
<template>
  <main class="p-4 lg:p-7">
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-3xl text-gray-800 font-semibold">
        Organisational Branches
      </p>
      <div class="mt-4 md:mt-0 flex items-center gap-3">
        <Link href="/dashboard/governance-hub"
          class="flex gap-2 items-center bg-indigo-50 border border-indigo-200 text-indigo-700 font-semibold rounded-lg py-2 px-4 shadow-2xs hover:bg-indigo-100 transition-all cursor-pointer">
          <Icon icon="lucide:network" width="20" class="text-indigo-600" />
          <p>Governance Hub</p>
        </Link>
        <div @click="departmentalModal = true"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
          <Icon icon="ion:add-outline" width="25" />
          <p>Create Department</p>
        </div>
      </div>
    </section>
    <section class="mt-10">
      <div class="grid xl:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6">
        <div
          class="flex items-start gap-3 border border-solid border-border_primary pl-3 pr-20 pt-3 pb-6 rounded-lg w-full">
          <div class="border border-solid border-border_primary rounded-full px-3 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path
                d="M14.1668 17.5V15.8333C14.1668 14.9493 13.8156 14.1014 13.1905 13.4763C12.5654 12.8512 11.7176 12.5 10.8335 12.5H4.16683C3.28277 12.5 2.43493 12.8512 1.80981 13.4763C1.18469 14.1014 0.833496 14.9493 0.833496 15.8333V17.5M19.1668 17.5V15.8333C19.1663 15.0948 18.9205 14.3773 18.468 13.7936C18.0155 13.2099 17.3819 12.793 16.6668 12.6083M13.3335 2.60833C14.0505 2.79192 14.686 3.20892 15.1399 3.79359C15.5937 4.37827 15.84 5.09736 15.84 5.8375C15.84 6.57764 15.5937 7.29673 15.1399 7.88141C14.686 8.46608 14.0505 8.88308 13.3335 9.06667M10.8335 5.83333C10.8335 7.67428 9.34111 9.16667 7.50016 9.16667C5.65921 9.16667 4.16683 7.67428 4.16683 5.83333C4.16683 3.99238 5.65921 2.5 7.50016 2.5C9.34111 2.5 10.8335 3.99238 10.8335 5.83333Z"
                stroke="#101828" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div>
            <p class="text-lg text-gray-800 mb-3 mt-2">Total Users</p>
            <p class="text-2xl font-medium">{{ users_data }}</p>
          </div>
        </div>
        <div
          class="flex items-start gap-3 border border-solid border-border_primary pl-3 pr-20 pt-3 pb-6 rounded-lg w-full">
          <div class="border border-solid border-border_primary rounded-full px-3 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path
                d="M3.75 2.5V16.25H11.25V2.5H3.75ZM3.125 1.25H11.875C12.0408 1.25 12.1997 1.31585 12.3169 1.43306C12.4342 1.55027 12.5 1.70924 12.5 1.875V16.875C12.5 17.0408 12.4342 17.1997 12.3169 17.3169C12.1997 17.4342 12.0408 17.5 11.875 17.5H3.125C2.95924 17.5 2.80027 17.4342 2.68306 17.3169C2.56585 17.1997 2.5 17.0408 2.5 16.875V1.875C2.5 1.70924 2.56585 1.55027 2.68306 1.43306C2.80027 1.31585 2.95924 1.25 3.125 1.25Z"
                fill="black" />
              <path
                d="M5 5H10V6.25H5V5ZM5 8.75H10V10H5V8.75ZM5 12.5H10V13.75H5V12.5ZM12.5 10H15V11.25H12.5V10ZM12.5 12.5H15V13.75H12.5V12.5ZM1.25 16.25H18.75V17.5H1.25V16.25Z"
                fill="black" />
              <path
                d="M12.5 7.5V16.25H16.25V7.5H12.5ZM11.875 6.25H16.875C17.0408 6.25 17.1997 6.31585 17.3169 6.43306C17.4342 6.55027 17.5 6.70924 17.5 6.875V16.875C17.5 17.0408 17.4342 17.1997 17.3169 17.3169C17.1997 17.4342 17.0408 17.5 16.875 17.5H11.875C11.7092 17.5 11.5503 17.4342 11.4331 17.3169C11.3158 17.1997 11.25 17.0408 11.25 16.875V6.875C11.25 6.70924 11.3158 6.55027 11.4331 6.43306C11.5503 6.31585 11.7092 6.25 11.875 6.25Z"
                fill="black" />
            </svg>
          </div>
          <div>
            <p class="text-lg text-gray-800 mb-3 mt-2">Total Departments</p>
            <p class="text-2xl font-medium">{{ department }}</p>
          </div>
        </div>
        <div
          class="flex items-start gap-3 border border-solid border-border_primary pl-3 pr-20 pt-3 pb-6 rounded-lg w-full">
          <div class="border border-solid border-border_primary rounded-full px-3 py-3">
            <Icon icon="ph:arrows-split-fill" width="20" :verticalFlip="true" />
          </div>
          <div>
            <p class="text-lg text-gray-800 mb-3 mt-2">Total Division</p>
            <p class="text-2xl font-medium">{{ division }}</p>
          </div>
        </div>
        <div
          class="flex items-start gap-3 border border-solid border-border_primary pl-3 pr-20 pt-3 pb-6 rounded-lg w-full">
          <div class="border border-solid border-border_primary rounded-full px-3 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path
                d="M2.725 5.80008L10 10.0084L17.275 5.80008M10 18.4001V10.0001M17.5 13.3334V6.66675C17.4997 6.37448 17.4225 6.08742 17.2763 5.83438C17.13 5.58134 16.9198 5.37122 16.6667 5.22508L10.8333 1.89175C10.58 1.74547 10.2926 1.66846 10 1.66846C9.70744 1.66846 9.42003 1.74547 9.16667 1.89175L3.33333 5.22508C3.08022 5.37122 2.86998 5.58134 2.72372 5.83438C2.57745 6.08742 2.5003 6.37448 2.5 6.66675V13.3334C2.5003 13.6257 2.57745 13.9127 2.72372 14.1658C2.86998 14.4188 3.08022 14.6289 3.33333 14.7751L9.16667 18.1084C9.42003 18.2547 9.70744 18.3317 10 18.3317C10.2926 18.3317 10.58 18.2547 10.8333 18.1084L16.6667 14.7751C16.9198 14.6289 17.13 14.4188 17.2763 14.1658C17.4225 13.9127 17.4997 13.6257 17.5 13.3334Z"
                stroke="#101828" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div>
            <p class="text-lg text-gray-800 mb-3 mt-2">Total Unit</p>
            <p class="text-2xl font-medium">{{ unit }}</p>
          </div>
        </div>
      </div>
    </section>
    <section class="mt-8">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer p-2">
        <p class="text-2xl text-gray-800 font-semibold">Branches</p>
        <div class="mt-3 md:mt-0 text-sm md:text-base flex items-center gap-6">
          <p @click="activeTab = 'all'" :class="{'bg-gray-50 font-semibold': activeTab === 'all', 'hover:bg-gray-50': activeTab !== 'all'}" class="rounded-lg py-2 px-3 cursor-pointer transition-colors">
            All Departments
          </p>
          <p @click="activeTab = 'pinned'" :class="{'bg-gray-50 font-semibold': activeTab === 'pinned', 'hover:bg-gray-50': activeTab !== 'pinned'}" class="rounded-lg py-2 px-3 cursor-pointer transition-colors">Pinned</p>
          <p @click="activeTab = 'departments_only'" :class="{'bg-gray-50 font-semibold': activeTab === 'departments_only', 'hover:bg-gray-50': activeTab !== 'departments_only'}" class="rounded-lg py-2 px-3 cursor-pointer transition-colors">Departments Only</p>
        </div>
      </div>
      <div class="border border-solid border-gray-300 rounded-lg mt-3">
        <div v-if="props.department != 0">

          <div class="card">

            <div class="relative overflow-x-auto">

              <DataTable v-model:expandedRows="expandedRows" :value="filteredDepartments" dataKey="id" tableStyle="min-width: 60rem">

              <Column expander style="width: 3rem" v-if="activeTab !== 'departments_only'" />
              <Column field="title" header="Department">
                <template #body="slotProps">
                  <div class="flex items-center gap-2">
                    <span>{{ slotProps.data.title }}</span>
                    <Icon v-if="pinnedDepartments.includes(slotProps.data.id)" icon="mdi:pin" class="text-blue-500" width="16" />
                  </div>
                </template>
              </Column>
              <Column header="Last updated" field="updated_at_formatted"></Column>
              <Column field="division_count" header="Divisions">
                <template #body="slotProps">
                  {{ slotProps.data.division_count }}
                </template>
              </Column>
              <Column field="archive_count" header="No. of Documents">
                <template #body="slotProps">
                  {{ slotProps.data.archive_count || 0 }}
                </template>
              </Column>
              <Column field="user_count" header="No. of Users">
                <template #body="slotProps">
                  <div class="flex items-center gap-3">
                    <p>{{ slotProps.data.user_count || 0 }} users</p>
                    <div class="flex -space-x-2 overflow-hidden ml-2" v-if="slotProps.data.user && slotProps.data.user.length > 0">
                      <img v-for="usr in slotProps.data.user.slice(0, 3)" :key="usr.id"
                           :src="usr.profile_image ? ($page.props.base + '/' + usr.profile_image) : ($page.props.base + '/images/user.svg')"
                           alt=""
                           class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover bg-white" />
                      <div v-if="slotProps.data.user_count > 3"
                           class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
                        +{{ slotProps.data.user_count - 3 }}
                      </div>
                    </div>
                  </div>
                </template>
              </Column>
              <Column field="" header="">
                <template #body="{data}">
                  <div class="flex items-center gap-3">
                    <div class="flex cursor-pointer">
                      <div @click="addDocTypes(data)"
                        class="flex gap-2 items-center pr-4 font-normal h-full border border-gray-300 rounded-lg px-2 py-1">
                        <div>
                          <Icon icon="ion:add-outline" width="25" />
                        </div>
                        <p class="font-medium">Add document type</p>
                      </div>
                    </div>
                    <div class="flex">
                      <div @click="toggle($event, data)" aria-haspopup="true" aria-controls="overlay_menu"
                        class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                        <div class="h-full">
                          <Icon icon="majesticons:more-menu-vertical" width="30" />
                        </div>
                        <Menu ref="menu"  id="overlay_menu" :model="menuItems" :popup="true" />
                      </div>

                      </div>
                    </div>
                  </template>
                </Column>
                <template #expansion="slotProps">
                  <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg m-2">
                    <h5 class="text-lg font-semibold mb-3 ml-2">Divisions for {{ slotProps.data.title }}</h5>
                    <DataTable v-model:expandedRows="expandedDivisionRows" :value="slotProps.data.division" dataKey="id" class="p-datatable-sm">
                      <Column expander style="width: 3rem" />
                      <Column field="title" header="Division"></Column>
                      <Column header="Last updated" field="updated_at_formatted"></Column>
                      <Column field="unit_count" header="Units">
                        <template #body="divSlot">
                          {{ divSlot.data.unit_count || (divSlot.data.unit ? divSlot.data.unit.length : 0) }}
                        </template>
                      </Column>
                      <Column field="archive_count" header="No. of Documents">
                        <template #body="divSlot">
                          {{ divSlot.data.archive_count || 0 }}
                        </template>
                      </Column>
                      <Column field="users_count" header="No. of Users">
                        <template #body="divSlot">
                          <div class="flex items-center gap-3">
                            <p>{{ divSlot.data.users_count || 0 }} users</p>
                            <div class="flex -space-x-2 overflow-hidden ml-2" v-if="divSlot.data.users && divSlot.data.users.length > 0">
                              <img v-for="usr in divSlot.data.users.slice(0, 3)" :key="usr.id"
                                   :src="usr.profile_image ? ($page.props.base + '/' + usr.profile_image) : ($page.props.base + '/images/user.svg')"
                                   alt=""
                                   class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover bg-white" />
                              <div v-if="divSlot.data.users_count > 3"
                                   class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
                                +{{ divSlot.data.users_count - 3 }}
                              </div>
                            </div>
                          </div>
                        </template>
                      </Column>
                      <template #expansion="divSlotProps">
                        <div class="p-3 bg-white border border-gray-100 rounded-lg m-2">
                          <h6 class="text-md font-semibold mb-3 ml-2">Units for {{ divSlotProps.data.title }}</h6>
                                                    <DataTable v-model:expandedRows="expandedUnitRows" :value="divSlotProps.data.unit" dataKey="id" class="p-datatable-sm">
                            <Column expander style="width: 3rem" />
                            <Column field="title" header="Unit"></Column>
                            <Column header="Last updated" field="updated_at_formatted"></Column>
                            <Column field="archive_count" header="No. of Documents">
                              <template #body="unitSlot">
                                {{ unitSlot.data.archive_count || 0 }}
                              </template>
                            </Column>
                            <Column field="users_count" header="No. of Users">
                              <template #body="unitSlot">
                                <div class="flex items-center gap-3">
                                  <p>{{ unitSlot.data.users_count || 0 }} users</p>
                                  <div class="flex -space-x-2 overflow-hidden ml-2" v-if="unitSlot.data.users && unitSlot.data.users.length > 0">
                                    <img v-for="usr in unitSlot.data.users.slice(0, 3)" :key="usr.id"
                                         :src="usr.profile_image ? ($page.props.base + '/' + usr.profile_image) : ($page.props.base + '/images/user.svg')"
                                         alt=""
                                         class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover bg-white" />
                                    <div v-if="unitSlot.data.users_count > 3"
                                         class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
                                      +{{ unitSlot.data.users_count - 3 }}
                                    </div>
                                  </div>
                                </div>
                              </template>
                            </Column>
                            <template #expansion="unitSlotProps">
                              <div class="p-3 bg-gray-50 border border-gray-100 rounded-lg m-2">
                                <h6 class="text-sm font-semibold mb-3 ml-2">Sections for {{ unitSlotProps.data.title }}</h6>
                                <DataTable :value="unitSlotProps.data.sections" dataKey="id" class="p-datatable-sm">
                                  <Column field="title" header="Section"></Column>
                                  <Column header="Last updated" field="updated_at_formatted"></Column>
                                  <Column field="archive_count" header="No. of Documents">
                                    <template #body="secSlot">
                                      {{ secSlot.data.archive_count || 0 }}
                                    </template>
                                  </Column>
                                  <Column field="users_count" header="No. of Users">
                                    <template #body="secSlot">
                                      <div class="flex items-center gap-3">
                                        <p>{{ secSlot.data.users_count || 0 }} users</p>
                                        <div class="flex -space-x-2 overflow-hidden ml-2" v-if="secSlot.data.users && secSlot.data.users.length > 0">
                                          <img v-for="usr in secSlot.data.users.slice(0, 3)" :key="usr.id"
                                               :src="usr.profile_image ? ($page.props.base + '/' + usr.profile_image) : ($page.props.base + '/images/user.svg')"
                                               alt=""
                                               class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover bg-white" />
                                          <div v-if="secSlot.data.users_count > 3"
                                               class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
                                            +{{ secSlot.data.users_count - 3 }}
                                          </div>
                                        </div>
                                      </div>
                                    </template>
                            </Column>
                            <Column field="" header="">
                              <template #body="{data}">
                                <div class="flex items-center gap-3">
                                  <div class="flex cursor-pointer">
                                    <div @click="openAddSectionModal(data)"
                                      class="flex gap-2 items-center pr-4 font-normal h-full border border-gray-300 rounded-lg px-2 py-1">
                                      <div>
                                        <Icon icon="ion:add-outline" width="25" />
                                      </div>
                                      <p class="font-medium">Add Section</p>
                                    </div>
                                  </div>
                                </div>
                              </template>
                            </Column>
                          </DataTable>
                        </div>
                      </template>
                    </DataTable>
                        </div>
                      </template>
                    </DataTable>
                  </div>
                </template>
              </DataTable>
            <!-- <Toast /> -->

            </div>

          </div>
        </div>
        <EmptyState v-else>
          <div @click="departmentalModal = true"
            class="mt-3 m-auto w-56 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
            <Icon icon="ion:add-outline" width="25" />
            <p>Create Department</p>
          </div>
        </EmptyState>
      </div>
    </section>
    <ConfigureMetadataModal v-model="configureMetadata" @selectedOptions="SelectedMetadataOptions"
      :doctype="singleDocType" />
    <CreateDepartmentDialog v-model="departmentalModal" :step="step" @update:step="updateModalStep" />
    <DocumentTypesModal v-model="documentTypes" />
    <AddDocumentTypesModal v-model="addDocType" :doctypes="props.documentTypes" :branch="currentDepartment" :url="'/dashboard/add-document-type-to-department'" />
    <AddDivisionModal v-model="addDivision" :department ="singleDepartment" @close="addDivision = false"/>
    <EditDepartmentModal v-model="editDepartment" :department="singleDepartment" @close="editDepartment=false"/>
    <AddFileTypeModal v-model="addFileType"  :selectedFileType="selectedFileType" :branch="'department'" :branch_id="singleDepartment.id"/>
  <ChangeLogModal v-model="changeLogModal" :data="currentChangeLogData" :type="currentChangeLogType" />
  </main>
</template>

<script setup>
import { ref, defineProps, watch, computed, onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import EmptyState from "../../components/EmptyState.vue";
import CreateDepartmentDialog from "../../components/Modals/CreateDepartmentDialog.vue";
import ConfigureMetadataModal from "../../components/Modals/ConfigureMetadataModal.vue";
import DocumentTypesModal from "../../components/Modals/DocumentTypesModal.vue";
import AddDocumentTypesModal from "../../components/Modals/AddDocumentTypesModal.vue";
import AddFileTypeModal from "../../components/Modals/AddFileTypeModal.vue";
import AddDivisionModal from "../../components/Modals/AddDivisionModal.vue";
import EditDepartmentModal from "../../components/Modals/EditDepartmentModal.vue";
import ChangeLogModal from "../../components/Modals/ChangeLogModal.vue";
import { router } from '@inertiajs/vue3'

const departmentalModal = ref(false);
const step = ref(1);

const props = defineProps(['department', 'division', 'unit', 'users', 'departments','documentTypes'])

const users_data = ref(props.users);
const activeTab = ref('all');
const pinnedDepartments = ref([]);
const expandedDivisionRows = ref([]);
const expandedUnitRows = ref([]);

onMounted(() => {
  pinnedDepartments.value = JSON.parse(localStorage.getItem('pinned_departments') || '[]');
});

const togglePin = (deptId) => {
  let index = pinnedDepartments.value.indexOf(deptId);
  if(index > -1) {
    pinnedDepartments.value.splice(index, 1);
  } else {
    pinnedDepartments.value.push(deptId);
  }
  localStorage.setItem('pinned_departments', JSON.stringify(pinnedDepartments.value));
}

const filteredDepartments = computed(() => {
  if (activeTab.value === 'pinned') {
    return props.departments.filter(dept => pinnedDepartments.value.includes(dept.id));
  }
  return props.departments;
});

const selectedMetadata = ref([]);
const singleDocType = ref([]);
const singleDepartment = ref([])
const documentTypes = ref(false);
const addDocType = ref(false);
const addFileType = ref(false);
const currentDepartment = ref([])
const addDivision = ref(false)
const editDepartment = ref(false);
const selectedFileType = ref([]);


const updateModalStep = (value) => {
  step.value = value;
};

// const RefreshValues = (value) => {
//  // router.reload({ only: ['department','division','unit'] })
// }

// const nodes = ref([
//   {
//     key: "0",
//     label: "Cybersecurity",
//     timestamp: "3 days",
//     children: [
//       {
//         key: "0-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "1 day",
//         type: "timeline",
//       },
//       {
//         key: "0-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "0-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 weeks",
//         type: "timeline",
//       },
//       {
//         key: "0-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
//   {
//     key: "1",
//     label: "Business Administration",
//     timestamp: "2 days",
//     children: [
//       {
//         key: "1-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "2 days",
//         type: "timeline",
//       },
//       {
//         key: "1-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "1-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 minutes",
//         type: "timeline",
//       },
//       {
//         key: "1-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
//   {
//     key: "2",
//     label: "Proposals",
//     timestamp: "5 days",
//     children: [
//       {
//         key: "2-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "1 day",
//         type: "timeline",
//       },
//       {
//         key: "2-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "2-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 weeks",
//         type: "timeline",
//       },
//       {
//         key: "2-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
//   {
//     key: "3",
//     label: "Management",
//     timestamp: "3 Months",
//     children: [
//       {
//         key: "3-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "1 day",
//         type: "timeline",
//       },
//       {
//         key: "3-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "3-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 weeks",
//         type: "timeline",
//       },
//       {
//         key: "3-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
//   {
//     key: "4",
//     label: "Budgeting",
//     timestamp: "3 Seconds",
//     children: [
//       {
//         key: "4-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "1 day",
//         type: "timeline",
//       },
//       {
//         key: "4-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "4-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 weeks",
//         type: "timeline",
//       },
//       {
//         key: "4-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
//   {
//     key: "5",
//     label: "Communications",
//     timestamp: "3 Weeks",
//     children: [
//       {
//         key: "5-1",
//         label: "Public Relations",
//         data: "Ship Folder",
//         timestamp: "1 day",
//         type: "timeline",
//       },
//       {
//         key: "5-2",
//         label: "Community Development",
//         data: "Ship Folder",
//         timestamp: "5 days",
//         type: "timeline",
//       },
//       {
//         key: "5-3",
//         label: "Secretariat",
//         data: "Ship Folder",
//         timestamp: "2 weeks",
//         type: "timeline",
//       },
//       {
//         key: "5-4",
//         label: "Management",
//         data: "Ship Folder",
//         timestamp: "1 month",
//         type: "timeline",
//       },
//     ],
//   },
// ]);
const menu = ref();
const menuItems = ref([]);

const changeLogModal = ref(false);
const currentChangeLogData = ref(null);
const currentChangeLogType = ref('');

const openChangeLog = (data, type) => {
  currentChangeLogData.value = data;
  currentChangeLogType.value = type;
  changeLogModal.value = true;
};

const toggle = (event, data) => {
  singleDepartment.value = data;
  const isPinned = pinnedDepartments.value.includes(data.id);
  
  menuItems.value = [
    {label: 'Add Division', icon: 'pi pi-plus', command: ()=>{ addDivision.value = true }},
    { label: "Divisions", icon: 'pi pi-eye', command: (event)=>{ window.open('/dashboard/get-divisions?id='+data.id, "_blank"); }},
    { label: 'Add File Type', icon: 'pi pi-file', command: ()=>{ addFileType.value = true; }},
    { label: "Edit", icon: "pi pi-pencil", command: ()=>{ editDepartment.value = true; }},
    { label: isPinned ? "Unpin" : "Pin", icon: isPinned ? "pi pi-bookmark-fill" : "pi pi-bookmark", command: () => { togglePin(data.id); }},
    { label: "Delete", icon: "pi pi-trash", command: ()=>{ let reqData = {id: data.id}; router.post('/dashboard/delete-department', reqData) }},
    { label: "Change Log", icon: "pi pi-chart-pie", command: () => { openChangeLog(data, 'department'); } },
  ];
  
  menu.value.toggle(event);
};

const addSectionModal = ref(false);
const sectionForm = useForm({
  title: '',
  description: '',
  unit_id: ''
});

const openAddSectionModal = (unit) => {
  sectionForm.unit_id = unit.id;
  sectionForm.title = '';
  sectionForm.description = '';
  addSectionModal.value = true;
};

const submitSection = () => {
  sectionForm.post('/dashboard/add-section', {
    preserveScroll: true,
    onSuccess: () => {
      addSectionModal.value = false;
    }
  });
};
const configureMetadata = ref(false);

const SelectedMetadataOptions = (val) => {
  selectedMetadata.value = val.value
}

const expandedRows = ref([]);

// const onRowExpand = (event) => { };
// const onRowCollapse = (event) => { };

const formatCurrency = (value) => {
  return value;
};
// const getSeverity = (product) => {
//   switch (product.inventoryStatus) {
//     case "INSTOCK":
//       return "success";

//     case "LOWSTOCK":
//       return "warning";

//     case "OUTOFSTOCK":
//       return "danger";

//     default:
//       return null;
//   }
// };
// const getOrderSeverity = (order) => {
//   switch (order.status) {
//     case "DELIVERED":
//       return "success";

//     case "CANCELLED":
//       return "danger";

//     case "PENDING":
//       return "warning";

//     case "RETURNED":
//       return "info";

//     default:
//       return null;
//   }
// };

const addDocTypes = (val) =>{
  addDocType.value = true
  currentDepartment.value = val
}

// const toggle = (elem) => {
//     let element = document.getElementById(elem);
//     let btn = document.getElementById(elem+'_btn');
//     let svg1 = `<svg width="4" height="12" viewBox="0 0 4 8" fill="none" xmlns="http://www.w3.org/2000/svg">
//                                                 <path d="M0.599976 6.80007L3.39998 4.00007L0.599976 1.20007" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
//                                             </svg>`
//     let svg2 = `<svg width="4" height="12" viewBox="0 0 8 4" fill="none" xmlns="http://www.w3.org/2000/svg">
//                                                 <path d="M6.79995 0.599976L3.99995 3.39998L1.19995 0.599976" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
//                                             </svg>`

//     if(element.classList.contains('hidden')){
//         element.classList.remove('hidden')
//         btn.innerHTML = svg2
//     }else{
//         element.classList.add('hidden')
//         btn.innerHTML = svg1
//     }

// }

// watch the selectedmetadata and configure the metadata for the doctype
watch(selectedMetadata, (val) => {
  if (singleDocType.value.id) {
    let data = {
      id: singleDocType.value.id,
      metadata: selectedMetadata.value
    }

    router.post('/dashboard/configure-meta-data', data, {
      onSuccess: () => {
        //refresh singledocType variable to ensure all is in sync
        let doctype = props.documentTypes.find((val) => {
          return val.id == singleDocType.value.id
        })
        singleDocType.value = doctype
      }
    })
  }
})

watch(singleDepartment, ()=>{
  if(singleDepartment.value.file_type && singleDepartment.value.file_type.length > 0){
    let arr= []
    for(let filetype of singleDepartment.value.file_type){
      arr.push(filetype.id);
    }
    selectedFileType.value = arr
  }
})
</script>








