<template>
    <main class="mb-20">
        <!-- Header -->
        <div class="flex items-center justify-between pt-5 px-7">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-xs">
                    <Icon icon="lets-icons:file" width="28" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-[#1D2939]">File & Docket Tracking</h1>
                    <p class="text-sm text-gray-500">
                        Monitor chain of custody, desk dwell times, and physical/digital docket movement across all directorates.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="flex gap-2 items-center text-white bg-primary rounded-lg py-2.5 px-5 font-medium hover:bg-primary/90 transition-all shadow-sm"
                    @click="TrackPackageModel = true">
                    <Icon icon="lucide:search" width="18" />
                    <span>Track Specific Docket / Task</span>
                </button>
            </div>
        </div>

        <Divider class="my-4" />

        <!-- Main Tracking View -->
        <div class="px-7">
            <!-- Search & Branch Discovery (When no branch dossier is open) -->
            <div class="min-h-[60vh] flex flex-col justify-center items-center py-10" v-if="!packages">
                <section class="flex flex-col items-center justify-center max-w-2xl w-full">
                    <div class="flex flex-col items-center justify-center px-4 mb-6">
                        <img :src="registryIcon" alt="Tracking Icon" class="w-20 mb-3" />
                        <div class="text-center px-7">
                            <h2 class="font-bold text-xl text-gray-900">Institutional Docket & File Tracker</h2>
                            <p class="text-gray-500 text-sm mt-1 max-w-lg">
                                Search by File Number, Subject, or Task ID (e.g. TSK-...) for instant custody audit trail, or select a directorate below to inspect its inward/outward dossiers.
                            </p>
                        </div>

                        <!-- Direct Docket Search Input -->
                        <div class="relative mt-6 w-full max-w-xl">
                            <div class="flex items-center border border-gray-300 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 bg-white p-3 rounded-xl gap-3 shadow-xs transition-all">
                                <Icon icon="lucide:search" class="text-gray-400" width="20" />
                                <input v-model="directSearch" type="text" class="w-full border-0 outline-none text-gray-800 placeholder-gray-400 text-sm"
                                    placeholder="Enter File Name, Doc No, or Task Tracking ID..." @input="onDocketSearch" />
                                <span v-if="isSearching" class="text-xs text-gray-400 animate-pulse">Searching...</span>
                            </div>

                            <!-- Docket Search Results Dropdown -->
                            <div class="border border-gray-200 py-2 bg-white max-h-[300px] overflow-y-auto rounded-xl absolute z-50 w-full shadow-xl mt-2"
                                v-if="searchResults && searchResults.length > 0">
                                <div class="flex items-center justify-between p-3 px-4 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors"
                                    v-for="(item, index) in searchResults" :key="index"
                                    @click="selectDocketForTracking(item)">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg" :class="item.result_type === 'task' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'">
                                            <Icon :icon="item.result_type === 'task' ? 'ph:clipboard-text' : 'ph:folder'" width="20" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ item.result_type === 'task' ? item.title : item.name }}</p>
                                            <p class="text-xs text-gray-500 font-mono">
                                                {{ item.result_type === 'task' ? '#' + item.tracking_id : '#' + (item.doc_no || 'No Ref') }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full font-medium capitalize">
                                        {{ item.result_type || 'file' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Divider / Alternative Search -->
                        <div class="flex items-center gap-3 w-full max-w-xl my-6">
                            <div class="h-[1px] bg-gray-200 flex-1"></div>
                            <span class="text-xs text-gray-400 uppercase font-semibold">Or browse by branch</span>
                            <div class="h-[1px] bg-gray-200 flex-1"></div>
                        </div>

                        <!-- Branch Filter Input -->
                        <div class="relative w-full max-w-xl">
                            <div class="flex items-center border border-gray-300 focus-within:border-primary bg-white p-2.5 rounded-xl gap-2.5 shadow-xs transition-all">
                                <Icon icon="octicon:git-branch-24" class="text-gray-400" width="18" />
                                <input v-model="Users" type="text" class="w-full border-0 outline-none text-gray-700 placeholder-gray-400 text-sm"
                                    placeholder="Search Directorate, Division, or Unit..." @input="filterBranch" id="search"/>
                            </div>

                            <!-- Branch Suggestions Dropdown -->
                            <div class="border border-gray-200 py-2 bg-white max-h-[260px] overflow-y-auto rounded-xl absolute z-40 w-full shadow-xl mt-2"
                                v-if="branches && branches.length > 0">
                                <div class="flex items-center p-3 px-4 gap-3 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors"
                                    v-for="(branch, index) in branches" :key="index"
                                    @click="updateBranch(branch)">
                                    <div class="p-2 border border-gray-200 rounded-lg bg-gray-50">
                                        <img :src="unitIcon" alt="" width="18" v-if="branch.branch_type == 'unit'">
                                        <img :src="divisionIcon" alt="" width="18" v-if="branch.branch_type == 'division'">
                                        <img :src="departmentIcon" alt="" width="18" v-if="branch.branch_type == 'department' || branch.branch_type == 'agf'">
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ branch.title }}</p>
                                        <p class="text-xs text-gray-400 capitalize">{{ branch.branch_type }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Branch Inward/Outward Dossier Viewer -->
            <div v-else>
                <RegistryPackage :toggleStateFunction="toggleState" :branch="selectedBranch" :department="props.department"
                :division="props.division" :unit="props.unit" @close="closeRegistryPackageHandler"/>
            </div>
        </div>
    </main>

    <!-- Modals (Tracking Only) -->
    <TrackPackage v-model="TrackPackageModel" :department="props.department" :division="props.division" :unit="props.unit" />
    <ViewPackage v-model="viewpackageModel" :fileMovement="selectedTrackedMovement" :file="selectedTrackedFile" :task="selectedTrackedTask" :taskMinutes="selectedTrackedMinutes" :department="props.department" :division="props.division" :unit="props.unit" />
</template>

<script setup>
import { ref, defineProps, onMounted, computed } from "vue";
import AssignRole from "../../components/Modals/Registry/AssignRole.vue";
import RegistryPackage from "../../components/RegistryPackage.vue";
import TrackPackage from "../../components/Modals/Registry/TrackPackage.vue";
import EditPermission from "../../components/Modals/Registry/EditPermission.vue"
import debounce from 'lodash.debounce'
import {usePage} from '@inertiajs/vue3'
//import registry from '/assets/images/registry.svg';
import registryIcon from "../../assets/images/registry.svg";
import unitIcon from '../../../../public/images/unit.svg';
// import divisionIcon from '../../assets/images/Division-icon.svg';
// import departmentIcon from '../../assets/images/department.svg';

import divisionIcon from "../../../../public/images/division.svg";
import departmentIcon from "../../../../public/images/department.svg";

import ViewPackage from "../../components/Modals/Registry/ViewPackage.vue";

const AssignRoleModel = ref(false);
const TrackPackageModel = ref(false);
const EditPermissionModal = ref(false);
const buttonToggle = ref(1);
const Recipients = ref([]);
const files = ref([]);
const page = usePage();
const permissions = computed(() => page.props.permissions);
const role = computed(() => page.props.user_roles || []);
const props = defineProps(['department', 'division', 'unit']);
const branches = ref([]);
const selectedBranch = ref();

const directSearch = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const viewpackageModel = ref(false);
const selectedTrackedFile = ref(null);
const selectedTrackedTask = ref(null);
const selectedTrackedMinutes = ref([]);
const selectedTrackedMovement = ref(null);

const onDocketSearch = debounce((e) => {
    const query = e.target.value;
    if (query && query.trim() !== '') {
        isSearching.value = true;
        axios.get('/dashboard/registry/track-files', { params: { search: query } })
            .then(res => {
                searchResults.value = res.data || [];
            })
            .finally(() => {
                isSearching.value = false;
            });
    } else {
        searchResults.value = [];
    }
}, 400);

const selectDocketForTracking = (item) => {
    axios.get('/dashboard/registry/tracked', {
        params: {
            file_id: item.id,
            file_movement_id: item.result_type === 'task' ? null : (item.file_movement && item.file_movement.length > 0 ? item.file_movement[0].file_movement_id : null),
            type: item.result_type
        }
    }).then(res => {
        if (item.result_type === 'task') {
            selectedTrackedTask.value = res.data.task;
            selectedTrackedMinutes.value = res.data.minutes || [];
            selectedTrackedFile.value = null;
            selectedTrackedMovement.value = null;
        } else {
            selectedTrackedFile.value = res.data.file;
            selectedTrackedMovement.value = res.data.file_movement;
            selectedTrackedTask.value = null;
            selectedTrackedMinutes.value = [];
        }
        viewpackageModel.value = true;
        searchResults.value = [];
        directSearch.value = '';
    });
};

const closeRegistryPackageHandler = ()=>{
   // document.getElementById('search').focus()
    toggleState();
}

const getDept = async () => {
    try {
        const response = await axios.get('/dashboard/registry/dept');
        let newKey = 'icon';
        let newValue = 'gg:organisation'
        response.data.data.forEach(obj => {
            return obj[newKey] = newValue;
        })
        Recipients.value = response.data.data
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};


const menu = ref();
const items = ref([
    {
        label: "Edit Permissions",
        icon: "ant-design:edit-outlined"
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const Users = ref("");


const Suggestions = ref([]);
// const filterRecipients = () => {
//     const searchTerm = Users.value.toLowerCase();
//     Suggestions.value = Recipients.value.filter((recipient) =>
//     recipient.name.toLowerCase().includes(searchTerm)
// );
// };

const filterBranch = debounce((event)=>{
    let search = event.target.value
   if(search !== ''){
    axios.get('/dashboard/admin-registry-branch', {params:{search: search}}).then((response)=>{
        if(response.data){
            branches.value = response.data
        }
    })
   }else{
    branches.value = []
   }
}, 500)

const updateBranch = (branch)=>{
    selectedBranch.value = branch
    toggleState();
}

const packages = ref(false);

const toggleState = () => {
    packages.value = !packages.value;
};
const updateUsers = (suggestion) => {
    toggleState();
    Users.value = "";
    Suggestions.value = [];
};
</script>

<style scoped>
.activeButton {
    background-color: white;
    border-radius: 5px;
    color: black;
    font-weight: 500;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>
