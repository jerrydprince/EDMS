<template>

    <div class=" block md:flex items-center justify-between px-7">
        <div>
            <h1 class="text-3xl text-[#1D2939] font-semibold">File Registry</h1>
            <h2>{{ department?.name }}</h2>
            <p class=" opacity-80 pt-3">Efficiently monitor and manage branch document movement.</p>
        </div>

        <div class="flex items-center gap-4 mt-5 md:mt-0">
            <button @click="TrackPackageModel = true"
                class="flex gap-2 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
                <p>Track File</p>
            </button>

            <button class="flex gap-2 items-center text-white bg-primary rounded-lg py-2 px-4"
                @click="createEntry = true">
                <Icon icon="material-symbols:add" width="20" />
                <p>Create Entry</p>
            </button>
        </div>
    </div>

    <Divider class="" />
    <div class="flex justify-between items-center gap-4 px-7">
        <div class=" flex items-center gap-3">
            <button class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium"
                @click="activeButton('incoming')" :class="{ activeButton: buttonToggle === 1 }">Incoming</button>
            <button class="  bg-gray-100 py-2 border border-gray-300 px-3 rounded-2xl text-xs text-gray-500 font-medium"
                @click="activeButton('outgoing')" :class="{ activeButton: buttonToggle === 2 }">Outgoing</button>
        </div>
        <button class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon icon="raphael:barchart" width="20" />
            <p>Generate Report</p>
        </button>
    </div>
    <Divider />
    <div class="px-7">
        <div class=" mb-52">
            <DataTable v-model:expandedRows="expandedRows" :value="displayedItems" dataKey="id" paginator :rows="10"
                @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem" :pt="{
                root: {
                    class: ' border rounded-md pb-10 overflow-hidden relative'
                },
                paginator: {
                    class: 'pt-10'
                }
            }">
                <Column field="name" header="Name"></Column>
                <Column field="doc_no" header="Number"> </Column>
                <Column field="created_at" header="Date"></Column>
                <!-- <Column field="Source" header="Source"> </Column> -->
                <Column header="Status">
                    <template #body="slotProps">
                        <div class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs" :class="{
                'bg-green-400/10': slotProps.data.status === 'delivered',
                'bg-yellow-400/10': slotProps.data.status === 'pending',
                'bg-red-400/10': slotProps.data.status === 'rejected'
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
                        <div v-if="checkData" class=" flex items-center gap-5">
                            <div v-if="slotProps.data.status !== 'rejected' && slotProps.data.status !== 'delivered'">
                                <button @click="rejectFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-semibold text-red-700 border-red-300 border-solid border rounded-lg py-2 px-3">
                                    <Icon icon="iconoir:cancel" />
                                    <p class=" text-xs ">Reject File</p>
                                </button>

                                <button @click="acceptFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3">
                                    <Icon icon="charm:tick" />
                                    <p class=" text-xs ">Accept File</p>
                                </button>
                                <button @click="forwardFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3">
                                    <Icon icon="charm:move" />
                                    <p class=" text-xs ">Forward File</p>
                                </button>
                            </div>
                            <div v-else>
                                <p class="font-normal">Destination</p>
                                <div class="mt-5 flex items-center gap-2">
                                <div class="p-3 rounded-full border w-fit">
                                        <img :src="slotProps.data.icon" alt=""
                                            v-if="slotProps.data.sent_to_department_id">
                                        <Icon icon="fa-solid:question" v-else />
                                    </div>
                                <div>
                                        <h1 class="font-medium" v-if="slotProps.data.sent_to_department_id">{{
                slotProps.data.sent_to_department_id }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet</h1>
                                    </div>
                            </div>
                            </div>
                        </div>
                        <!-- <div v-if="checkDest">
                            <button class="flex gap-2 items-center border ml-4  rounded-lg py-2 px-4 absolute bottom-5"
                        @click="destination(slotProps.data.id)">
                        <Icon icon="gridicons:add-outline" width="20" />
                        <p>Add New Destination</p>
                    </button>
                        </div> -->
                        <div class="flex items-center" v-if='hide'>
                            <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
                            <div>
                                <p class="font-normal">Destination</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <div class="p-3 rounded-full border w-fit">
                                        <img :src="slotProps.data.icon" alt=""
                                            v-if="slotProps.data.sent_to_department_id">
                                        <Icon icon="fa-solid:question" v-else />
                                    </div>
                                    <div>
                                        <h1 class="font-medium" v-if="slotProps.data.sent_to_department_id">{{
                slotProps.data.sent_to_department_id }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="slotProps.data.subject">
                            <h1 class=" mt-2">Subject Matter</h1>
                            <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{ slotProps.data.subject
                                }}</p>
                        </div>
                    </div>
                </template>

            </DataTable>
        </div>
    </div>
    <RejectFileModal v-model="rejectFile" :data="fileId" />
    <AddDestinationModal v-model="addDestination" :fileId="destFileId" />
    <CreatEntryModal v-model="createEntry" />
    <TrackPackage v-model="TrackPackageModel" />
</template>
<script setup>
import { ref, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import RejectFileModal from '../../components/Modals/RecordManager/RejectFile.vue';
import AddDestinationModal from '../../components/Modals/RecordManager/AdddDestination.vue';
import TrackPackage from "../../components/Modals/Registry/TrackPackage.vue";
import CreatEntryModal from '../../components/Modals/RecordManager/CreateEntry.vue'

const TrackPackageModel = ref(false);
const addDestination = ref(false);
const createEntry = ref(false)
const buttonToggle = ref(1);
const clickedButton = ref("incoming");
const files = ref([])
const filteredFiles = ref([])
const rejectFile = ref(false)
const fileId = ref('')
const destFileId = ref(null)
const department = ref({})
const formData = ref({
    status: "delivered",
});
const checkData = ref(true)
const checkDest = ref(false)
const hide = ref(false)



const getFilesInDept = async () => {
    try {
        const response = await axios.get('/dashboard/registry/dept');
        department.value = response.data.data[0]
        const responses = await axios.get(`/dashboard/registry/deptFiles/${department.value.id}`);
        console.log('get data here', responses.data);
        files.value = responses.data;
        // console.log('here', files.value);

    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
// const destination = async (id) =>{
//     destFileId.value = id;
// }
const forwardFileHandler = async (fileId) => {
    addDestination.value = true
    destFileId.value = fileId;
    // try {
    //     const response = await axios.post(`/dashboard/registry/forward/${fileId}`);
    //     console.log('updated', response.data.data);
    //     toast.add({
    //         severity: "success",
    //         summary: 'File forwrded',
    //         detail: "file has been forwarded to outgoing files.",
    //         life: 3000,
    //     });
    // } catch (error) {
    //     console.error('Error fetching data:', error.message);
    // }
}
onMounted(() => {
    getFilesInDept()
})
console.log('out', files.value);

const displayedItems = computed(() => {
    console.log('from computed', files.value);
    if (files.value.received_files && Array.isArray(files.value.received_files)) {
        files.value.received_files.forEach(obj => {
            const date = new Date(obj.created_at);
            obj.created_at = date.toDateString()
        })
    }
    if (files.value.sent_files && Array.isArray(files.value.sent_files)) {
        files.value.sent_files.forEach(obj => {
            const date = new Date(obj.created_at);
            obj.created_at = date.toDateString()
        })
    }
    if (clickedButton.value == "incoming") {
        checkData.value = true;
        hide.value = false;
        // return files.value.received_files || files.value.received_files.filter((file) => file.is_incoming == 1)
        return files.value.received_files
    }
    else if (clickedButton.value == "outgoing") {
        checkData.value = false;
        checkDest.value = true;
        hide.value = true;
        const filtered = files.value.received_files.filter((file) => file.is_outgoing == 1)
        return [...filtered, ...(files.value.sent_files || [])]
    }
    // files.value.sent_files.filter((file) => file.created_at = new Date(file.created_at).toDateString())
    // if (!files.value) return [];
    // let data = files.value
    // if (clickedButton.value == "incoming") {
    //     return data = files.value.sent_files.filter((file) => file.is_outgoing == 1)
    // } else {
    //     return data = files.value.sent_files.filter((file) => file.is_incoming == 1)
    // }
});


console.log('out', files.value);
const activeButton = (event) => {
    if (event === "incoming") {
        buttonToggle.value = 1;
    }
    if (event === "outgoing") {
        buttonToggle.value = 2;
    }

    clickedButton.value = event;
};

const toast = useToast();
const expandedRows = ref([]);
const onRowExpand = (event) => { };
const onRowCollapse = (event) => { };

const acceptFileHandler = async (fileId) => {
    try {
        console.log(formData.value.status);
        const response = await axios.post(`/dashboard/registry/updateFileStatus/${fileId}`, formData.value);
        console.log('updated', response.data.data);
        toast.add({
            severity: "success",
            summary: 'File Accepted',
            detail: "You can view and add new destinations for outgoing files.",
            life: 3000,
        });
    } catch (error) {
        console.error('Error fetching data:', error.message);
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
        icon: "gg:organisation",
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
        icon: "icon-park-outline:fork",
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
        Destination: "Audit transaction Repair Division",
        icon: "icon-park-outline:fork",
        subject:
            "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
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
        icon: "gg:organisation",
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
        icon: "icon-park-outline:fork",
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
        Destination: "Audit transaction Repair Division",
        icon: "icon-park-outline:fork",
        subject:
            "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
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
        icon: "gg:organisation",
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
        icon: "icon-park-outline:fork",
        subject:
            "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
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
        icon: "icon-park-outline:fork",
        subject:
            "Management System (EDMS) within our organization. As part of our continuous efforts to streamline our document management processes and enhance efficiency, we have decided to transition to a digital platform for managing our documents. The EDMS will provide us with a centralized repository for storing, organizing, and retrieving documents, which will significantly improve collaboration and accessib",
    },
]);

const rejectFileHandler = (id) => {
    rejectFile.value = true;
    fileId.value = id
}


// // const displayedItems = computed(() => {
// //   const filteredItems = data.value.filter((item) => {
// //     return item.Going
// //       .toLowerCase()
// //       .includes(clickedButton.value.toLowerCase());
// //   });

//   return filteredItems;
// });
</script>

<style scoped>
.activeButton {
    background-color: white;
    color: black;
    font-weight: bold;
    border: 1px solid #0B6217;
}
</style>
