<template>
    <main class="p-7">
        <div class="bg-white border border-gray-200 rounded-xl lg:flex items-start p-7 w-full justify-between">
            <div class="flex items-start gap-5">
                <div class="p-3 border rounded-full">
                    <Icon icon="lucide:workflow" width="30" />
                </div>
                <div>
                    <h1 class="text-4xl">Correspondence Dashboard</h1>
                    <div class="text-sm flex items-center gap-5 mt-4">

                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white mt-10 border border-gray-200 rounded-xl p-7 w-full">

            <!-- Correspondence table  -->
            <div class="overflow-x-scroll lg:overflow-hidden mt-10" v-if="roles.includes('super users')">
                <table class="min-w-max w-full border">
                    <thead class="bg-gray-100">
                        <tr class="text-left">
                            <th class="px-5">#</th>
                            <th>Date</th>
                            <th>File Name</th>
                            <th>Document Number</th>
                            <th class="pl-5 py-5">Description</th>
                            <th>Sent By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in filteredCorrespondence" :key="index"
                            class="hover:bg-gray-50 cursor-pointer border-b">
                            <td class="px-5">{{ index + 1 }}</td>

                            <td class="align-middle">
                                <div class="">
                                    <p>{{ formatDate(item.created_at) }}</p>
                                </div>
                            </td>
                             <td class="align-middle">{{ item.file_name }}</td>
                             <td class="align-middle">{{ item.document_number }}</td>
                            <td class="align-middle pl-5">
                                <div class="flex items-center gap-2">
                                    <p> {{
                                        item.description && item.description.length > 140
                                            ? `${item.description.substring(0, 140)}...`
                                        : item.description
                                        }}</p>
                                </div>
                            </td>
                            <td>{{ item.sent_by?.first_name+" "+item.sent_by?.last_name }}</td>

                            <td class="align-top py-3">
                                <div class="flex items-center gap-2">

                                    <Link :href="'/dashboard/correspondence/view-files/' + item.id">
                                    <button
                                        class="flex items-center gap-2 border-[2px]  py-3 px-3 border-gray-300 rounded-md">
                                        <Icon icon="ph:eye" />
                                        <p class="text-sm">View File</p>
                                    </button>
                                    </Link>

                                </div>
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <!-- <paginator
            :totalItems="totalItems"
            :itemsPerPage="itemsPerPage"
            v-model="currentPage"
          /> -->
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
    if (roles.value.includes('super users')) {
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