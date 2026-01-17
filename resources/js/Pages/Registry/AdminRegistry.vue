<template>
    <main class="mb-20">
        <div class="flex items-center justify-between pt-5 px-7">
            <div class="flex items-center gap-5">
                <div class="border rounded-full p-3">
                    <Icon icon="lets-icons:file" width="30" />
                </div>
                <div>
                    <h1 class="text-3xl text-[#1D2939]">File Registry</h1>
                    <p class="text-gray-500">
                        Manage registry access level and track files within organization.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button v-if="packages"
                    class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
                    <Icon icon="raphael:barchart" width="20" />
                    <p>Generate Report</p>
                </button>

                <button class="flex gap-2 items-center text-white bg-primary rounded-lg py-2 px-4"
                    @click="TrackPackageModel = true">
                    <p>Track Package</p>
                </button>
            </div>
        </div>

        <Divider class=" " />


        <div class=" ">
            <div class=" h-[60vh] flex flex-col justify-center items-center" v-if="!packages">
                <section class="flex flex-col items-center justify-center">
                    <div class="flex flex-col items-center justify-center px-4 mb-5">
                        <img :src="registryIcon" alt="" class="w-20" />
                        <div class="text-center px-7 mt-1">
                            <h1 class="font-semibold text-lg">Registry Search</h1>
                            <p class="opacity-80 text-base">
                                Track the flow of documents across the organization <br />
                                for enhanced oversight and management.
                            </p>
                        </div>
                        <div class="relative mt-4">
                            <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
                                <i class="pi pi-search text-gray-700" style="font-size: 1rem"></i>
                                <input v-model="Users" type="text" class="w-96 border-0 outline-none text-gray-700"
                                    placeholder="Search Branch" @input="filterBranch" id="search"/>
                            </div>

                            <div class="border border-solid py-3 bg-white h-[300px] overflow-y-scroll rounded-md absolute z-50 w-full shadow-xl mt-2"
                                v-if="branches && branches.length > 0">
                                <div class="flex items-center p-3 px-3 gap-2 cursor-pointer hover:bg-[#f8f8f9]"
                                    v-for="(branch, index) in branches" :key="index"
                                    @click="updateBranch(branch)">
                                    <div class="p-2 border border-gray-600 rounded-full">
                                        <!-- <Icon :icon="suggestion.icon" width="20" /> -->
                                        <img :src="unitIcon" alt="" width="20" v-if="branch.branch_type == 'unit'">
                                        <img :src="divisionIcon" alt="" width="20" v-if="branch.branch_type == 'division'">
                                        <img :src="departmentIcon" alt="" width="20" v-if="branch.branch_type == 'department'">
                                    </div>
                                    <p class="text-sm w-fit font-normal">{{ branch.title }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div v-else class=" ">
                <RegistryPackage :toggleStateFunction="toggleState" :branch="selectedBranch" :department="props.department"
                :division="props.division" :unit="props.unit" @close="closeRegistryPackageHandler"/>
            </div>
        </div>
    </main>
    <AssignRole v-model="AssignRoleModel" />
    <TrackPackage v-model="TrackPackageModel" :department="props.department" :division="props.division" :unit="props.unit" />
    <EditPermission v-model="EditPermissionModal" />
</template>

<script setup>
import { ref, defineProps, onMounted } from "vue";
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

const AssignRoleModel = ref(false);
const TrackPackageModel = ref(false);
const EditPermissionModal = ref(false)
const buttonToggle = ref(1);
const Recipients = ref([])
const files = ref([])
const props = defineProps(['department', 'division', 'unit'])
const branches = ref([])
//files.value = props.files
const page = usePage()
const selectedBranch = ref()

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
