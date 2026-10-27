<template>
    <div class="px-5 md:px-7 py-5">
        <div class="flex items-center gap-3">
            <div class="p-5 border rounded-full dark:border-gray-700">
                <Icon icon="carbon:analytics" width="30" class="dark:text-gray-300" />
            </div>
            <div>
                <div class="flex items-center gap-5 mb-2">
                    <h1
                        class="text-2xl md:text-3xl font-semibold text-[#1D2939] dark:text-white"
                    >
                        Analytics
                    </h1>
                    <div class="text-sm flex items-center gap-5 mt-1 md:mt-4">
                        <div
                            class="flex items-center gap-2 bg-primary/10 p-1 px-2 rounded-2xl dark:bg-primary/20"
                        >
                            <Icon icon="mynaui:key" class="dark:text-green-400" />
                            <p class="text-sm md:text-base dark:text-green-400">Admin</p>
                        </div>
                    </div>
                </div>
                <p class="text-[#667085] text-sm md:text-base dark:text-gray-400">
                    This dashboard focuses on strategic metrics and overall
                    system performance.
                </p>
            </div>
        </div>
    </div>

    <section class="bg-slate-50 dark:bg-gray-900 min-h-screen pb-10 px-5 md:px-7 pt-5">
        
        <!-- Admin Analytics -->
        <div v-if="isAdmin">
            <system_analytics v-if="stat" :stat="stat" />
            
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
                <div class="xl:col-span-1 w-full">
                    <AccessLevel v-if="roles" :roles="roles" />
                </div>
                <div class="xl:col-span-2 w-full overflow-hidden">
                    <LoginActivity :logins="logins" />
                </div>
            </div>
        </div>

        <!-- AGF Analytics -->
        <div v-if="isAGF && agfSummaryData">
            <ExecutiveSummary :data="agfSummaryData.summary" />
            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6 items-start">
                <DepartmentTaskStatus :chartData="agfSummaryData.chart_data" />
                <HighPriorityTracker :tasks="agfSummaryData.high_priority_tasks" />
            </div>
            
            <!-- Other AGF components go here -->
        </div>

        <!-- Director Analytics -->
        <div v-if="isDirector && directorSummaryData">
            <ExecutiveSummary :data="directorSummaryData.summary" />
            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6 items-start">
                <DepartmentTaskStatus :chartData="directorSummaryData.chart_data" />
                <HighPriorityTracker :tasks="directorSummaryData.high_priority_tasks" />
            </div>
        </div>

        <!-- Division Analytics -->
        <div v-if="isDivisionHead && divisionSummaryData">
            <ExecutiveSummary :data="divisionSummaryData.summary" />
            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6 items-start">
                <DepartmentTaskStatus :chartData="divisionSummaryData.chart_data" />
                <HighPriorityTracker :tasks="divisionSummaryData.high_priority_tasks" />
            </div>
        </div>

        <!-- Unit Analytics -->
        <div v-if="isUnitHead && unitSummaryData">
            <ExecutiveSummary :data="unitSummaryData.summary" />
            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6 items-start">
                <DepartmentTaskStatus :chartData="unitSummaryData.chart_data" />
                <HighPriorityTracker :tasks="unitSummaryData.high_priority_tasks" />
            </div>
        </div>

    </section>
</template>

<script setup>
import { ref, computed, onMounted, provide } from "vue";
import AccessLevel from "../../components/AdminAnalytics/AccessLevel.vue";
import ResponseTime from "../../components/AdminAnalytics/ResponseTime.vue";
import system_analytics from "../../components/AdminAnalytics/system-analytics.vue";
import Backup from "../../components/AdminAnalytics/BackupStatus.vue";
import ExecutiveSummary from "../../components/ADF-Analytics/ExecutiveSummary.vue";
import LoginActivity from "../../components/AdminAnalytics/LoginActivity.vue";
import SystemUptime from "../../components/ADF-Analytics/SystemUptime.vue";
import DepartmentTaskStatus from "../../components/ADF-Analytics/DepartmentTaskStatus.vue";
import HighPriorityTracker from "../../components/ADF-Analytics/HighPriorityTracker.vue";
import Department_Metrics from "../../components/Director-analysis/Department-metrics.vue";
import workflowSummary from "../../components/Director-analysis/workflow-summary.vue";
import CommunicationMetric from "../../components/Director-analysis/communication-metrics.vue";
import EmployeeProductivity from "../../components/Director-analysis/Employee-Productivity.vue";

import image1 from "../../assets/images/Chart_gain.svg";
import image2 from "../../assets/images/Chart_loss.svg";

const selectedIndex = ref(1);
const stat = ref(null);
const logins = ref([]);
const roles = ref([]);
const handleSelection = (index) => {
    selectedIndex.value = index;
    console.log(selectedIndex.value);
};
const getStat = async () => {
    try {
        const response = await axios.get("/dashboard/analytics/server-status");
        stat.value = response.data;
        console.log("stats here", stat.value);
    } catch (error) {
        console.error("Error fetching data:", error);
    }
};
provide("stat", stat);
provide("roles", roles);
const formatTimestamp = (timestamp) => {
    const date = new Date(timestamp);
    return date.toISOString().slice(0, 19).replace("T", " ");
};
const getLogins = async () => {
    try {
        const response = await axios.get("/dashboard/analytics/user-logins");
        logins.value = response.data.map((login) => ({
            ...login,
            created_at: formatTimestamp(login.created_at),
        }));
        console.log("logins details here", logins.value);
    } catch (error) {
        console.error("Error fetching data:", error);
    }
};
const getRoles = async () => {
    try {
        const response = await axios.get("/dashboard/analytics/roles");
        roles.value = response.data;
        console.log("roles here", roles.value);
    } catch (error) {
        console.error("Error fetching data:", error);
    }
};
// const buttonToggle = ref(1)
// const mapContainer = ref(null);
// const totalUser = ref(0);
// const users = ref();
const totalLevels = ref(0);
const levels = {
    "Super Admin": 500,
    Admin: 192,
    "Records Manager": 64,
    "super user": 11,
    Directors: 8,
    "Division Head": 64,
    "Unit Head": 11,
    "record manager": 8,
    Registrars: 8,
};

for (const level in levels) {
    totalLevels.value += levels[level];
}

const totalWorkflows = ref(0);
const workflows = {
    "Workflow created": 500,
    Completed: 192,
    "Under Review": 64,
    "In Progress": 11,
    Uncompleted: 8,
};

for (const workflow in workflows) {
    totalWorkflows.value += workflows[workflow];
}

const overviews = [
    {
        id: 1,
        numbers: "324K",
        name: "Users",
        image: image1,
        percentage: "25%",
    },
    {
        id: 2,
        numbers: "157K",
        image: image2,
        name: "Records Uploaded",
        percentage: "25%",
    },
    {
        id: 3,
        numbers: "78",
        image: image1,
        name: "Workflow",
        percentage: "25%",
    },
];

import { usePage } from '@inertiajs/vue3'
const page = usePage()
const userRoles = page.props.roles || [];

const isAdmin = computed(() => {
    return userRoles.includes('super admin') || userRoles.includes('admin');
});
const isAGF = computed(() => {
    return userRoles.includes('Accountant-General') || userRoles.includes('super user') || userRoles.includes('agf');
});
const isDirector = computed(() => {
    return userRoles.includes('director') || userRoles.includes('Directors') || userRoles.includes('deputy director') || userRoles.includes('assistant director');
});
const isDivisionHead = computed(() => {
    return userRoles.includes('Division Head') || userRoles.includes('division head');
});
const isUnitHead = computed(() => {
    return userRoles.includes('Unit Head') || userRoles.includes('unit head');
});

const agfSummaryData = ref(null);
const directorSummaryData = ref(null);
const divisionSummaryData = ref(null);
const unitSummaryData = ref(null);

const getAGFSummary = async () => {
    if (!isAGF.value) return;
    try {
        const response = await axios.get("/dashboard/analytics/agf-summary");
        agfSummaryData.value = response.data;
    } catch (error) {
        console.error("Error fetching AGF summary data:", error);
    }
};

const getDirectorSummary = async () => {
    if (!isDirector.value) return;
    try {
        const response = await axios.get("/dashboard/analytics/director-summary");
        directorSummaryData.value = response.data;
    } catch (error) {
        console.error("Error fetching Director summary data:", error);
    }
};

const getDivisionSummary = async () => {
    if (!isDivisionHead.value) return;
    try {
        const response = await axios.get("/dashboard/analytics/division-summary");
        divisionSummaryData.value = response.data;
    } catch (error) {
        console.error("Error fetching Division summary data:", error);
    }
};

const getUnitSummary = async () => {
    if (!isUnitHead.value) return;
    try {
        const response = await axios.get("/dashboard/analytics/unit-summary");
        unitSummaryData.value = response.data;
    } catch (error) {
        console.error("Error fetching Unit summary data:", error);
    }
};

onMounted(() => {
    if (isAdmin.value) {
        getStat();
        getLogins();
        getRoles();
    }
    if (isAGF.value) {
        getAGFSummary();
    }
    if (isDirector.value) {
        getDirectorSummary();
    }
    if (isDivisionHead.value) {
        getDivisionSummary();
    }
    if (isUnitHead.value) {
        getUnitSummary();
    }
});
</script>

<style scoped>
.activeButton {
    background-color: white;
    border-radius: 5px;
    color: #0d6e1a;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>
