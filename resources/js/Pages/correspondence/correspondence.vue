<template>
    <main class="p-7 min-h-screen bg-gray-50/50">
        <!-- Header Section -->
        <div class="bg-white border border-gray-200/60 shadow-sm rounded-2xl flex flex-col lg:flex-row items-start lg:items-center p-8 w-full justify-between gap-6 transition-all hover:shadow-md">
            <div class="flex items-center gap-6">
                <div class="p-4 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl shadow-inner">
                    <Icon icon="lucide:mail-open" width="36" class="text-indigo-600" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Correspondence</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Manage and track all forwarded and incoming mails.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="relative w-full lg:w-72">
                    <Icon icon="ph:magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="20" />
                    <input type="text" placeholder="Search correspondence..." v-model="search" @input="getSearchResult" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm outline-none">
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white mt-8 border border-gray-200/60 shadow-sm rounded-2xl w-full flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                <h2 class="text-lg font-semibold text-gray-700">Recent Correspondences</h2>
                <div class="text-xs font-semibold px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full">{{ filteredCorrespondence?.length || 0 }} Total</div>
            </div>

            <!-- Correspondence table  -->
            <div class="overflow-x-auto w-full" v-if="roles.includes('super user') || roles.includes('super admin') || roles.includes('super users') || roles.includes('Accountant-General')">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="px-8 py-5 border-b border-gray-200/80">#</th>
                            <th class="px-6 py-5 border-b border-gray-200/80">Date</th>
                            <th class="px-6 py-5 border-b border-gray-200/80">File Name</th>
                            <th class="px-6 py-5 border-b border-gray-200/80">Doc Number</th>
                            <th class="px-6 py-5 border-b border-gray-200/80 w-1/3">Description</th>
                            <th class="px-6 py-5 border-b border-gray-200/80">Sent By</th>
                            <th class="px-8 py-5 border-b border-gray-200/80 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                        <tr v-for="(item, index) in filteredCorrespondence" :key="index"
                            class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                            <td class="px-8 py-4 font-medium text-gray-900">{{ index + 1 }}</td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <Icon icon="ph:calendar-blank" class="text-gray-400" />
                                    <span>{{ formatDate(item.created_at) }}</span>
                                </div>
                            </td>
                            
                             <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ item.file_name || 'N/A' }}</div>
                             </td>
                             
                             <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="item.document_number" class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-md text-xs font-medium border border-gray-200/60">{{ item.document_number }}</span>
                                <span v-else class="text-gray-400 italic text-xs">None</span>
                             </td>
                             
                            <td class="px-6 py-4 text-gray-500">
                                <p class="line-clamp-2 leading-relaxed">
                                    {{ item.description && item.description.length > 140 ? `${item.description.substring(0, 140)}...` : item.description || 'No description provided.' }}
                                </p>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3" v-if="item.sent_by">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs uppercase shadow-sm">
                                        {{ item.sent_by?.first_name?.charAt(0) }}{{ item.sent_by?.last_name?.charAt(0) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ item.sent_by?.first_name+" "+item.sent_by?.last_name }}</span>
                                </div>
                                <span v-else class="text-gray-400 italic">System</span>
                            </td>

                            <td class="px-8 py-4 text-right whitespace-nowrap">
                                <Link :href="'/dashboard/correspondence/view-files/' + item.id">
                                    <button class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all shadow-sm group-hover:shadow focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <Icon icon="ph:eye-duotone" width="18" class="text-gray-400 group-hover:text-indigo-500 transition-colors" />
                                        <span class="font-medium text-sm">View File</span>
                                    </button>
                                </Link>
                            </td>
                        </tr>
                        
                        <tr v-if="!filteredCorrespondence || filteredCorrespondence.length === 0">
                            <td colspan="7" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-gray-50 border border-gray-100 shadow-inner rounded-full flex items-center justify-center mb-4">
                                        <Icon icon="ph:folder-open-duotone" width="32" class="text-gray-400" />
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">No Correspondence Found</h3>
                                    <p class="text-gray-500 mt-1">There are no records to display at this time.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/30 flex items-center justify-between" v-if="roles.includes('super user') || roles.includes('super admin') || roles.includes('super users') || roles.includes('Accountant-General')">
                <p class="text-sm text-gray-500">Showing {{ filteredCorrespondence?.length || 0 }} entries</p>
                <!-- Pagination can go here -->
            </div>
        </div>
    </main>
</template>

<script setup>
import { ref, computed, defineProps, watch, inject, onMounted } from "vue";
//import paginator from "@/reusables/paginations.vue";

import { Link, router, usePage } from "@inertiajs/vue3";
import debounce from "lodash.debounce"
import { create } from "lodash";

const assignmentButton = ref(1);
const buttonToggle = ref(1);
const currentPage = ref(1);
const itemsPerPage = 10;
const tasks = ref([])
const page = usePage()
const search = ref('')
const props = defineProps(['created_task', 'assigned_task', 'correspondences'])

const roles = computed(() => page.props.roles)

const notifications = inject('notifications')
const created_task = ref([])
const assigned_task = ref([])

const buttonSwitch = ref(1);
const clickedButton = ref("");


const isRole = (role) => {
    if (roles.value.includes(role)) {
        return true
    } else {
        return false
    }
}

const taskByStatus = (status) => {
    let type = assignmentButton.value == 1 ? 'assigned_to_me' : 'assigned_by_me';
    axios.get('/dashboard/task-by-status', { params: { status: status, type: type } }).then((response) => {
        tasks.value = response.data.data;
    })
}

const formatDate = (inputDate) => {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

    return formattedDate;
}

const filteredItems = computed(() => {
    if (clickedButton.value === '') {
        return items.value;
    }
    return items.value.filter(item =>
        item.status.toLowerCase() === clickedButton.value.toLowerCase()
    );
});

const filteredTask = computed(() => {
    if (buttonSwitch.value == 1) {
        return created_task.value?.data
    } else
        if (buttonSwitch.value == 2) {
            let data = created_task.value.data.filter((item) => item.status === 'pending');
            return data
        } else
            if (buttonSwitch.value == 3) {
                let data = created_task.value.data.filter((item) => item.status == 'in progress');
                return data
            } else
                if (buttonSwitch.value == 4) {
                    let data = created_task.value.data.filter((item) => item.status == 'under review');
                    return data
                } else
                    if (buttonSwitch.value == 5) {
                        let data = created_task.value.data.filter((item) => item.status == 'completed');
                        return data
                    } else
                        if (buttonSwitch.value == 6) {
                            let data = created_task.value.data.filter((item) => item.status == 'uncompleted');
                            return data
                        }
    // else
    // {
    //   return props.created_task?.data
    // }
})

const filteredCorrespondence = computed(() => {
    return props.correspondences
})
const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;

    pageStart.value = startIndex;
    pageEnd.value = endIndex;

    return filteredItems.value.slice(startIndex, endIndex);
});

watch(() => props.created_task, () => {

}, { deep: true })


watch(assignmentButton, (newVal) => {
    buttonSwitch.value = 1;
    // if(newVal == 1){
    //   router.reload({only: ['created_at']})
    // }else{
    //   router.reload({only:['assigned_task']})
    // }
})

watch(notifications, (newVal) => {
    if (newVal.Today && newVal.Today[0].data.task) {
        router.reload({ only: ['assigned_task'] })
    }
})

const getSearchResult = debounce((event) => {
    let search_value = event.target.value;
    axios.get('/dashboard/search-workflow', { params: { search: search_value } }).then((response) => {
        created_task.value = response.data.created_task
        assigned_task.value = response.data.assigned_task
    })
}, 500)

watch(() => props.assigned_task, () => {
    assigned_task.value = props.assigned_task
}, { immediate: true })

watch(() => props.created_task, () => {
    created_task.value = props.created_task
}, { immediate: true })

onMounted(() => {
    if (roles.value.includes('super user') || roles.value.includes('super admin') || roles.value.includes('super users')) {
        assignmentButton.value = 2;
    }
})

</script>

<style scoped>
.activeButton {
    background-color: white;
    border-radius: 5px;
    color: black;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>
