<template>
  <div class="flex items-center gap-4 px-7">
    <div @click="togglePackage"
      class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3">
      <i class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"></i>
      <h1>Back</h1>
    </div>
    <h1 class="font-medium border-r border-gray-300 pr-3">
      Accounting Department
    </h1>

    <div class=" flex items-center gap-3">
      <button class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium"
        @click="activeButton('incoming')" :class="{ activeButton: buttonToggle === 1 }">Incoming</button>
      <button class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium"
        @click="activeButton('outgoing')" :class="{ activeButton: buttonToggle === 2 }">Outgoing</button>
    </div>
  </div>
  <Divider />
  <div class="px-7">
    <!-- incoming -->
    <div v-if="clickedButton == 'incoming'">
      <DataTable v-model:expandedRows="expandedRows" :value="incoming" dataKey="id" paginator :rows="10"
        @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem" :pt="{
      root: {
        class: ' border rounded-md overflow-hidden'
      }
    }">
        <Column field="name" header="Name"></Column>
        <Column field="doc_no" header="Number"> </Column>
        <!-- <Column field="file_type" header="Type"> </Column> -->
         <Column field="created_at" header="Date">
                    <template #body="slotProps">
                        <div>
                            {{ formatDate(slotProps.data.created_at) }}
                        </div>
                    </template>
                </Column>
        <Column field="source" header="Source"> </Column>
        <Column header="Status">
          <template #body="slotProps">
            <div class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs" :class="{
      'bg-green-400/10': slotProps.data.status === 'delivered',
      'bg-yellow-400/10': slotProps.data.status === 'pending',
      'bg-red-400/10': slotProps.data.status === 'dejected'
    }" :style="{
      color:
        slotProps.data.status === 'delivered' || slotProps.data.status =='forwarded'
          ? '#12B76A'
          : slotProps.data.status === 'pending'
            ? '#DC6803'
            : slotProps.data.status === 'rejected'
              ? '#F04438'
              : 'black',
      borderColor:
        slotProps.data.status === 'delivered' || slotProps.data.status =='forwarded'
          ? '#12B76A'
          : slotProps.data.status === 'pending'
            ? '#DC6803'
            : slotProps.data.status === 'rejected'
              ? '#F04438'
              : 'black',
      borderWidth: '1px',
      borderStyle: 'solid',
    }">
              <Icon icon="fluent-mdl2:radio-bullet" />
              <p>{{ slotProps.data.status }}</p>
            </div>
          </template>
        </Column>
        <Column expander header="Actions" :pt="{
      rowToggler: {
        class: ' border border-gray-300 px-2.5 py-1.5 rounded-md',
      },
    }">
        </Column>
        <template #expansion="slotProps">
          <div class=" p-5">
            <div class="flex items-center">
              <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
              <div>
                <p class="font-normal">Source</p>
                <div class="mt-5 flex items-center gap-2">
                  <div class="p-3 rounded-full border w-fit">
                    <!-- <img :src="slotProps.data.icon" alt="" v-if="slotProps.data.name">
                    <Icon icon="fa-solid:question" v-else /> -->
                    <img :src="unitIcon" alt="" width="20" v-if="getSourceBranchType(slotProps.data) == 'unit'">
                    <img :src="divisionIcon" alt="" width="20" v-if="getSourceBranchType(slotProps.data) == 'division'">
                    <img :src="departmentIcon" alt="" width="20" v-if="getSourceBranchType(slotProps.data) == 'department'">
                  </div>
                  <div>
                    <h1 class="font-medium" v-if="slotProps.data.name">{{ slotProps.data.name }}
                    </h1>
                    <h1 class="font-medium" v-else>Not set yet</h1>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="slotProps.data.description">
              <h1 class=" mt-2">Description</h1>
              <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{ slotProps.data.description }}</p>
            </div>
          </div>
        </template>
      </DataTable>
    </div>


    <!-- outgoing -->
    <div v-if="clickedButton == 'outgoing'">
      <DataTable v-model:expandedRows="expandedRows" :value="outgoing" dataKey="id" paginator :rows="10"
        @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem" :pt="{
      root: {
        class: ' border rounded-md overflow-hidden'
      }
    }">
        <Column field="name" header="Name"></Column>
        <Column field="doc_no" header="Number"> </Column>
        <!-- <Column field="file_type" header="Type"> </Column> -->
        <Column field="created_at" header="Date">
                    <template #body="slotProps">
                        <div>
                            {{ formatDate(slotProps.data.created_at) }}
                        </div>
                    </template>
          </Column>
        <Column field="source" header="Source"> </Column>
        <Column header="Status">
          <template #body="slotProps">
            <div class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs" :class="{
      'bg-green-400/10': slotProps.data.status === 'delivered',
      'bg-yellow-400/10': slotProps.data.status === 'pending',
      'bg-red-400/10': slotProps.data.status === 'dejected'
    }" :style="{
      color:
        slotProps.data.status === 'delivered'
          ? '#12B76A'
          : slotProps.data.status === 'pending'
            ? '#DC6803'
            : slotProps.data.status === 'rejected'
              ? '#F04438'
              : 'black',
      borderColor:
        slotProps.data.status === 'delivered'
          ? '#12B76A'
          : slotProps.data.status === 'pending'
            ? '#DC6803'
            : slotProps.data.status === 'rejected'
              ? '#F04438'
              : 'black',
      borderWidth: '1px',
      borderStyle: 'solid',
    }">
              <Icon icon="fluent-mdl2:radio-bullet" />
              <p>{{ slotProps.data.status }}</p>
            </div>
          </template>
        </Column>
        <Column expander header="Actions" :pt="{
      rowToggler: {
        class: ' border border-gray-300 px-2.5 py-1.5 rounded-md',
      },
    }">
        </Column>
        <template #expansion="slotProps">
          <div class=" p-5">
            <div class="flex items-center">
              <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
              <div>
                <p class="font-normal">Destination</p>
                <div class="mt-5 flex items-center gap-2">
                  <div class="p-3 rounded-full border w-fit">
                    <!-- <img :src="slotProps.data.icon" alt="" v-if="slotProps.data.name">
                    <Icon icon="fa-solid:question" v-else /> -->
                    <img :src="unitIcon" alt="" width="20" v-if="getDestinationBranchType(slotProps.data) == 'unit'">
                                            <img :src="divisionIcon" alt="" width="20" v-if="getDestinationBranchType(slotProps.data) == 'division'">
                                            <img :src="departmentIcon" alt="" width="20" v-if="getDestinationBranchType(slotProps.data) == 'department'">
                  </div>
                  <div>
                    <!-- <h1 class="font-medium" v-if="slotProps.data.name">{{ slotProps.data.name }}
                    </h1> -->
                    <h1 class="font-medium" v-if="getDestinationBranch(slotProps.data)">{{
                getDestinationBranch(slotProps.data) }}</h1>
                    <h1 class="font-medium" v-else>Not set yet</h1>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="slotProps.data.description">
              <h1 class=" mt-2">Description</h1>
              <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{ slotProps.data.description }}</p>
            </div>
          </div>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, onMounted, defineEmits} from "vue";
import division from "../assets/images/Division-Icon.svg"
import {usePage} from "@inertiajs/vue3"
import unitIcon from "../../../public/images/unit.svg"
import divisionIcon from "../../../public/images/division.svg"
import departmentIcon from "../../../public/images/department.svg"

const props = defineProps(["toggleStateFunction", "data", 'branch', 'department', 'division', 'unit']);
// const props = defineProps(["data"]);
const files = ref([])
const buttonToggle = ref(1);
const clickedButton = ref("incoming");
const page = usePage()
const emit = defineEmits(['close'])


const activeButton = (event) => {
  if (event === "incoming") {
    buttonToggle.value = 1;
  }
  if (event === "outgoing") {
    buttonToggle.value = 2;
  }

  clickedButton.value = event;
};
const togglePackage = () => {
  //toggleStateFunction();
  emit('close')
};
const expandedRows = ref([]);

const onRowExpand = (event) => { };
const onRowCollapse = (event) => { };

const user_branch = computed(() => page.props.userBranch)

const getUserBranchType = () => {
  if (user_branch.value.unit_id) {
    return 'unit'
  }
  if (user_branch.value.division_id) {
    return 'division'
  }
  if (user_branch.value.department_id) {
    return 'department'
  }
}

const getDestinationBranch = (data)=>{
    
    if(data.sent_to_unit){
        let filtered = props.unit.filter((item)=>{
            return item.id == data.sent_to_unit
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null; 
        }
    }

    if(data.sent_to_division){
        let filtered = props.division.filter((item)=>{
            return item.id == data.sent_to_division
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null; 
        }
    }

    if(data.sent_to_department){
        let filtered = props.department.filter((item)=>{
            return item.id == data.sent_to_department
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null; 
        }
    }
}


const outgoing = computed(() => {
  if (props.branch.branch_type == 'unit') {
    let filtered = files.value.filter((item) => item.sent_from_unit === props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
  if (props.branch.branch_type == 'division') {
    let filtered = files.value.filter((item) => item.sent_from_division === props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
  if (props.branch.branch_type == 'department') {
    let filtered = files.value.filter((item) => item.sent_from_department === props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
})

const incoming = computed(() => {
  if (props.branch.branch_type == 'unit') {
    let filtered = files.value.filter((item) => item.sent_to_unit == props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
  if (props.branch.branch_type == 'division') {
    let filtered = files.value.filter((item) => item.sent_to_division == props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
  if (props.branch.branch_type == 'department') {
    let filtered = files.value.filter((item) => item.sent_to_department == props.branch.id)
    return (filtered && filtered.length > 0) ? filtered : []
  }
})

const formatDate = (inputDate) => {
  var date = new Date(inputDate);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  var day = date.getDate();
  var month = months[date.getMonth()];
  var year = date.getFullYear();

  day = day < 10 ? '0' + day : day;

  var formattedDate = day + ' ' + month + ' ' + year.toString();

  return formattedDate;
}

const getBranchFiles = () => {

  axios.get(`/dashboard/registry/branchFiles?branch_id=${props.branch?.id}&branch_type=${props.branch.branch_type}`).then((responses) => {
    files.value = responses.data;
  })
};


const getSourceBranchType = (data)=>{
    
    if(data.sent_from_unit){
       return 'unit'
    }

    if(data.sent_from_division){
       return 'division'
    }

    if(data.sent_from_department){
      return 'department'
    }
}

const getDestinationBranchType = (data)=>{
    
    if(data.sent_to_unit){
       return 'unit'
    }

    if(data.sent_to_division){
       return 'division'
    }

    if(data.sent_to_department){
      return 'department'
    }
}

const data = ref([
  {
    id: 1,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "incoming",
    status: "Delivered",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
  {
    id: 2,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "outgoing",
    status: "Rejected",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
  {
    id: 3,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Going: "incoming",
    Source: "Super User",
    status: "Pending",
    icon: division,
  },
  {
    id: 4,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "outgoing",
    status: "Delivered",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
  {
    id: 5,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "incoming",
    status: "Rejected",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
  {
    id: 6,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "incoming",
    status: "Pending",
    icon: division,
  },
  {
    id: 7,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "outgoing",
    status: "Delivered",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
  {
    id: 8,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    status: "Rejected",
    Going: "incoming",
    Destination: "Audit transaction Repair Division",
    icon: division,

  },
  {
    id: 9,
    name: "Policy_HRGuidelines",
    number: "#A1 -DCA1",
    Date: "12 Mar 2023  ",
    Source: "Super User",
    Going: "outgoing",
    status: "Pending",
    Destination: "Audit transaction Repair Division",
    icon: division,
    subject:
      "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
  },
]);

const displayedItems = computed(() => {
  let data = files.value
  // if (clickedButton.value == "incoming") {
  //   return data = files.value.filter((file) => file.is_incoming == 1)
  // } else {
  //   return data = files.value.filter((file) => file.is_outgoing == 1)
  // }

});

onMounted(() => {
  getBranchFiles()
})
</script>

<style scoped>
.activeButton {
  background-color: white;
  color: black;
  font-weight: bold;
  border: 1px solid #0B6217;
}
</style>
