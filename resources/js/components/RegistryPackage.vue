<template>
  <div class="flex items-center gap-4 px-7">
    <div
      @click="togglePackage"
      class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3"
    >
      <i
        class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"
      ></i>
      <h1>Back</h1>
    </div>
    <h1 class="font-medium border-r border-gray-300 pr-3">
      Accounting Department
    </h1>

    <div class=" flex items-center gap-3">
       <button class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium" @click="activeButton('incoming')"  :class="{ activeButton: buttonToggle === 1 }">Incoming</button>
       <button  class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium"  @click="activeButton('outgoing')"  :class="{ activeButton: buttonToggle === 2 }">Outgoing</button>
    </div>
  </div>
  <Divider />
  <div class="px-7">
    <div

    >
      <DataTable
        v-model:expandedRows="expandedRows"
        :value="displayedItems"
        dataKey="id"
        paginator
          :rows="10"
        @rowExpand="onRowExpand"
        @rowCollapse="onRowCollapse"
        tableStyle="min-width: 60rem"
        :pt="{
           root: {
            class: ' border rounded-md overflow-hidden'
           }
        }"
      >
        <Column field="name" header="Name"></Column>
        <Column field="id" header="Number"> </Column>
        <!-- <Column field="file_type" header="Type"> </Column> -->
        <Column field="date" header="Date"></Column>
        <Column field="source" header="Source"> </Column>
        <Column header="Status">
          <template #body="slotProps">
            <div
              class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs"
              :class="{
                'bg-green-400/10': slotProps.data.status === 'delivered',
                'bg-yellow-400/10':  slotProps.data.status === 'pending',
                'bg-red-400/10' :  slotProps.data.status === 'dejected'
              }"
              :style="{
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
              }"
            >
              <Icon icon="fluent-mdl2:radio-bullet" />
              <p>{{ slotProps.data.status }}</p>
            </div>
          </template>
        </Column>
        <Column
          expander
          header="Actions"
          :pt="{
            rowToggler: {
              class: ' border border-gray-300 px-2.5 py-1.5 rounded-md',
            },
          }"
        >
        </Column>
        <template #expansion="slotProps">
          <div  class=" p-5">
          <div class="flex items-center">
            <div
              class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"
            ></div>
            <div>
              <p class="font-normal">Destination</p>
              <div class="mt-5 flex items-center gap-2">
                <div class="p-3 rounded-full border w-fit">
                  <img :src="slotProps.data.icon" alt="" v-if="slotProps.data.department.name">
                  <Icon icon="fa-solid:question" v-else />
                </div>
                <div>
                  <h1 class="font-medium"  v-if="slotProps.data.department.name">{{ slotProps.data.department.name }}</h1>
                  <h1 class="font-medium" v-else>Not set yet</h1>
                </div>
              </div>
            </div>
          </div>
          <div v-if="slotProps.data.subject">
            <h1 class=" mt-2">Subject Matter</h1>
            <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{ slotProps.data.subject }}</p>
          </div>
        </div>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref,computed, defineProps } from "vue";
import division from "../assets/images/Division-Icon.svg"
const props = defineProps(["toggleStateFunction", "data"]);
// const props = defineProps(["data"]);
const files =  ref([])
const buttonToggle = ref(1);
const clickedButton = ref("incoming");

files.value = props?.data
console.log(files.value);
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
  toggleStateFunction();
};
const expandedRows = ref([]);

const onRowExpand = (event) => {};
const onRowCollapse = (event) => {};
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
    if (clickedButton.value == "incoming") {
        return data = files.value.filter((file) => file.is_incoming == 1)
    }else{
        return data = files.value.filter((file) => file.is_outgoing == 1)
    }
//   const filteredItems = files.value.filter((item) => {
//     console.log('item', item);
//     return item.is_outgoing
//       .toLowerCase()
//       .includes(clickedButton.value.toLowerCase());
//   });

//   return filteredItems;
});
</script>

<style  scoped>
.activeButton {
  background-color: white;
  color: black;
  font-weight: bold;
 border:1px solid #0B6217;
}
</style>
