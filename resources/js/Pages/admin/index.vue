<style>
   /* Custom scrollbar styles */
.custom-scrollbar {
  scrollbar-width: thin; /* Firefox */
  scrollbar-color: #a0aec0 #edf2f7; /* Firefox */
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px; /* Set the width of the scrollbar */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #E9E9E9; /* Set the color of the thumb */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background-color: #fff; /* Set the color of the track */
}

.left-line::before {
    content: '';
    width: 30px;
    display: flex;
    border-top: 1px solid #D0D5DD;
    position: relative;
    top: 12px;
}

</style>
<template>
    <main class="p-4 lg:p-7">
        <section class="flex flex-col lg:flex-row items-start lg:items-center justify-between cursor-pointer">
            <p class="text-2xl lg:text-3xl text-gray-800 font-semibold">
                Welcome Back ! 👋🏼
            </p>
            <div
                class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg text-sm lg:text-base py-2 px-3 lg:px-4 cursor-pointer mt-6 lg:mt-0">
                <Icon icon="ion:add-outline" width="25" />
                <p>Invite Members</p>
            </div>
        </section>
        <section class="w-full text-gray-800 bg-primary_bg border border-solid border-gray-300 rounded-xl mt-7 p-3 lg:p-6">
            <div class="flex items-center gap-5">
                <Actions />
                <p class="text-xl lg:text-2xl font-medium">Quick Actions</p>
            </div>
            <div class="grid grid-cols-[270px_270px_270px_270px] lg:grid-cols-4 overflow-x-scroll lg:overflow-hidden mt-4 gap-5 lg:gap-10">
                <Link :href="'admin/metadata-setup'">
                <div class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full">
                    <QuickActions1 class="absolute top-0 left-[6%]" />
                    <QuickActions1_1 class="absolute top-20 z-[999] left-[12%]" />
                    <div class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
                        <p class="text-xl font-semibold mb-2">Metadata Configuration</p>
                        <p class="text-gray-500 text-sm">
                            Configure metadata settings for document categorization
                        </p>
                    </div>
                </div>
                </Link>
                <div class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full">
                    <QuickActions2 class="absolute top-0 left-[6%]" />
                    <div class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
                        <p class="text-xl font-semibold mb-2">Security Settings.</p>
                        <p class="text-gray-500 text-sm">
                            Manage security roles, compliance, and data privacy.
                        </p>
                    </div>
                </div>
                <Link href="/dashboard/manage-users">
                <div class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full">
                    <QuickActions3 class="absolute top-0 left-[6%]" />
                    <div class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
                        <p class="text-xl font-semibold mb-2">Manage Users.</p>
                        <p class="text-gray-500 text-sm">
                            Administer user accounts, roles, and permissions.
                        </p>
                    </div>
                </div>
                </Link>
                <div class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full">
                    <QuickActions4 class="top-0 left-0 w-full object-cover" />
                    <div class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
                        <p class="text-xl font-semibold mb-2">Customize Interface.</p>
                        <p class="text-gray-500 text-sm">
                            Tailor the interface to match the organisation's brand.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="mt-8">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between cursor-pointer p-2">
                <p class="text-xl lg:text-2xl text-gray-800 font-semibold">
                    Organisational Branches
                </p>
                <div @click="departmentalModal = true"
                    class="flex gap-2 text-sm lg:text-base items-center mt-6 lg:mt-0 text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 lg:px-4 cursor-pointer">
                    <Icon icon="ion:add-outline" width="25" />
                    <p>Create Department</p>
                </div>
            </div>
            <div v-if="props.departments.length > 0" class="w-full text-gray-800 border border-solid border-gray-300 rounded-xl mt-1 lg:p-6 p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 lg:gap-6 text-xs lg:text-base">
                        <p class="py-2 px-2 lg:px-3 bg-gray-50 rounded-lg font-semibold">
                            All Departments
                        </p>
                        <p class="hover:bg-gray-50 rounded-lg py-2 px-2 lg:px-3">Pinned</p>
                    </div>
                    <div class="flex items-center text-xs lg:text-base gap-2 cursor-pointer">
                        <p>View All</p>
                        <Icon icon="carbon:arrow-right" width="20" />
                    </div>
                </div>
                <!-- <div class="flex flex-col lg:flex-row gap-4 mt-6"> -->
                    <!-- Departmental Folders -->
                    
                <!-- </div> -->
                <div class=" grid grid-cols-12 gap-4">
                    <div v-for="(dept, index) in props.departments" :key="index" class=" border border-gray-300 rounded-lg mt-5 xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-12 divide-y divide-slate-300">
                            <!-- Head section -->
                        <div class="flex items-center align-middle justify-between m-3">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.375 2.25012V14.6251H10.125V2.25012H3.375ZM2.8125 1.12512H10.6875C10.8367 1.12512 10.9798 1.18439 11.0852 1.28987C11.1907 1.39536 11.25 1.53844 11.25 1.68762V15.1876C11.25 15.3368 11.1907 15.4799 11.0852 15.5854C10.9798 15.6909 10.8367 15.7501 10.6875 15.7501H2.8125C2.66332 15.7501 2.52024 15.6909 2.41475 15.5854C2.30926 15.4799 2.25 15.3368 2.25 15.1876V1.68762C2.25 1.53844 2.30926 1.39536 2.41475 1.28987C2.52024 1.18439 2.66332 1.12512 2.8125 1.12512Z" fill="black"/>
                                    <path d="M4.5 4.50012H9V5.62512H4.5V4.50012ZM4.5 7.87512H9V9.00012H4.5V7.87512ZM4.5 11.2501H9V12.3751H4.5V11.2501ZM11.25 9.00012H13.5V10.1251H11.25V9.00012ZM11.25 11.2501H13.5V12.3751H11.25V11.2501ZM1.125 14.6251H16.875V15.7501H1.125V14.6251Z" fill="black"/>
                                    <path d="M11.25 6.75012V14.6251H14.625V6.75012H11.25ZM10.6875 5.62512H15.1875C15.3367 5.62512 15.4798 5.68439 15.5852 5.78987C15.6907 5.89536 15.75 6.03844 15.75 6.18762V15.1876C15.75 15.3368 15.6907 15.4799 15.5852 15.5854C15.4798 15.6909 15.3367 15.7501 15.1875 15.7501H10.6875C10.5383 15.7501 10.3952 15.6909 10.2898 15.5854C10.1843 15.4799 10.125 15.3368 10.125 15.1876V6.18762C10.125 6.03844 10.1843 5.89536 10.2898 5.78987C10.3952 5.68439 10.5383 5.62512 10.6875 5.62512Z" fill="black"/>
                                </svg>
                            </div>
                            <Link :href="'/dashboard/branches/department/'+dept.id">
                                <div class="ml-2">
                                    <h5 class=" font-semibold text-sm">{{dept.title}}</h5>
                                    <p class=" text-gray-400 text-xs">Last Updated a day ago</p>
                                </div>
                            </Link>
                            <div class="ml-4">
                                <button class=" px-3 py-2 border border-gray-400 rounded-md right">
                                    <svg width="2" height="10" viewBox="0 0 2 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0.99987 5.46672C1.2576 5.46672 1.46654 5.25779 1.46654 5.00006C1.46654 4.74232 1.2576 4.53339 0.99987 4.53339C0.742137 4.53339 0.533203 4.74232 0.533203 5.00006C0.533203 5.25779 0.742137 5.46672 0.99987 5.46672Z" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M0.99987 2.20006C1.2576 2.20006 1.46654 1.99112 1.46654 1.73339C1.46654 1.47566 1.2576 1.26672 0.99987 1.26672C0.742137 1.26672 0.533203 1.47566 0.533203 1.73339C0.533203 1.99112 0.742137 2.20006 0.99987 2.20006Z" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M0.99987 8.73339C1.2576 8.73339 1.46654 8.52446 1.46654 8.26672C1.46654 8.00899 1.2576 7.80006 0.99987 7.80006C0.742137 7.80006 0.533203 8.00899 0.533203 8.26672C0.533203 8.52446 0.742137 8.73339 0.99987 8.73339Z" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                            <!-- body section -->
                        <div class="pb-2">
                            <div class=" overflow-y-scroll h-[242px] custom-scrollbar">
                               <div v-for="(division, index) in dept.division" class="menu" :key="index">
                                    <div class="sub-menu flex my-1 hover:bg-gray-50 cursor-pointer items-center">
                                        <button class="border border-gray-400 px-3 py-2 rounded-md m-3" @click="toggle(dept.title+'_'+division.title+'_'+index)" :id="dept.title+'_'+division.title+'_'+index+'_btn'">
                                            <svg width="4" height="12" viewBox="0 0 4 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.599976 6.80007L3.39998 4.00007L0.599976 1.20007" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            
                                        </button>
                                        <Link :href="'/dashboard/branches/division/'+division.id">
                                            <div class="ml-2">
                                                <h5 class=" font-semibold text-sm">{{division.title}}</h5>
                                                <p class=" text-gray-400 text-xs">Last Updated a day ago</p>
                                            </div>
                                        </Link>
                                    </div>
                                    <div class="inner-menu border-l border-gray-300 ml-6 -mt-3 pb-3 hidden" :id="dept.title+'_'+division.title+'_'+index">
                                        <ul>
                                            <li v-for="(unit, ind) in division.unit" :key="ind" class="pt-2 cursor-pointer hover:bg-gray-50 left-line">
                                                <Link :href="'/dashboard/branches/unit/'+unit.id">
                                                    <div class="ml-10">
                                                        <h5 class=" font-semibold text-sm">{{unit.title}}</h5>
                                                        <p class=" text-gray-400 text-xs">Last Updated a day ago</p>
                                                    </div>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                               </div>
                              
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
            <EmptyState v-else/>
        </section>
        <CreateDepartmentDialog v-model="departmentalModal" :step="step" @update:step="updateModalStep" />
    </main>
</template>
  
<script setup>
import { ref, defineProps } from "vue";
import Actions from "../../assets/Dashboard/Actions.vue"
import QuickActions1 from "../../assets/Dashboard/QuickActions1.vue";
import QuickActions1_1 from "../../assets/Dashboard/QuickActions1_1.vue";
import QuickActions2 from "../../assets/Dashboard/QuickActions2.vue";
import QuickActions3 from "../../assets/Dashboard/QuickActions3.vue";
import QuickActions4 from "../../assets/Dashboard/QuickActions4.vue";
import CreateDepartmentDialog from "../../components/Modals/CreateDepartmentDialog.vue";
import EmptyState from "../../components/EmptyState.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps(['departments']);

const menu = ref();
const departmentalModal = ref(false);
const step = ref(1);
const items = ref([
    { label: "Edit", icon: "pi pi-pencil" },
    { label: "Delete", icon: "pi pi-trash" },
]);


const toggle = (elem) => {
    let element = document.getElementById(elem);
    let btn = document.getElementById(elem+'_btn');
    let svg1 = `<svg width="4" height="12" viewBox="0 0 4 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.599976 6.80007L3.39998 4.00007L0.599976 1.20007" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>`
    let svg2 = `<svg width="4" height="12" viewBox="0 0 8 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.79995 0.599976L3.99995 3.39998L1.19995 0.599976" stroke="#101828" stroke-width="0.933333" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>`
   
    if(element.classList.contains('hidden')){
        element.classList.remove('hidden')
        btn.innerHTML = svg2
    }else{
        element.classList.add('hidden')
        btn.innerHTML = svg1
    }
    
}

const nodes = ref([
    {
        key: "0",
        label: "Cybersecurity",
        timestamp: "3 days",
        children: [
            {
                key: "0-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "1 day",
                type: "timeline",
            },
            {
                key: "0-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "0-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 weeks",
                type: "timeline",
            },
            {
                key: "0-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
    {
        key: "1",
        label: "Business Administration",
        timestamp: "2 days",
        children: [
            {
                key: "1-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "2 days",
                type: "timeline",
            },
            {
                key: "1-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "1-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 minutes",
                type: "timeline",
            },
            {
                key: "1-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
    {
        key: "2",
        label: "Proposals",
        timestamp: "5 days",
        children: [
            {
                key: "2-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "1 day",
                type: "timeline",
            },
            {
                key: "2-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "2-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 weeks",
                type: "timeline",
            },
            {
                key: "2-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
    {
        key: "3",
        label: "Management",
        timestamp: "3 Months",
        children: [
            {
                key: "3-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "1 day",
                type: "timeline",
            },
            {
                key: "3-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "3-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 weeks",
                type: "timeline",
            },
            {
                key: "3-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
    {
        key: "4",
        label: "Budgeting",
        timestamp: "3 Seconds",
        children: [
            {
                key: "4-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "1 day",
                type: "timeline",
            },
            {
                key: "4-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "4-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 weeks",
                type: "timeline",
            },
            {
                key: "4-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
    {
        key: "5",
        label: "Communications",
        timestamp: "3 Weeks",
        children: [
            {
                key: "5-1",
                label: "Public Relations",
                data: "Ship Folder",
                timestamp: "1 day",
                type: "timeline",
            },
            {
                key: "5-2",
                label: "Community Development",
                data: "Ship Folder",
                timestamp: "5 days",
                type: "timeline",
            },
            {
                key: "5-3",
                label: "Secretariat",
                data: "Ship Folder",
                timestamp: "2 weeks",
                type: "timeline",
            },
            {
                key: "5-4",
                label: "Management",
                data: "Ship Folder",
                timestamp: "1 month",
                type: "timeline",
            },
        ],
    },
]);

const updateModalStep = (value) => {
    step.value = value;
};

// const toggle = (event) => {
//     menu.value.toggle(event);
// };
</script>
  