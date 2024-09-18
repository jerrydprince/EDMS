<template>
    <div class=" px-5 md:px-7 py-5">
        <div class="flex items-center gap-3">
            <div class="p-5 border rounded-full">
                <Icon icon="carbon:analytics" width="30" />
            </div>
            <div>
                <div class="flex items-center gap-5 mb-2">
                    <h1 class=" text-2xl md:text-3xl font-semibold text-[#1D2939]">Analytics</h1>
                    <div class="text-sm flex items-center gap-5 mt-4">
                        <div class="flex items-center gap-2 bg-primary/10 p-1 px-2 rounded-2xl">
                            <Icon icon="mynaui:key" />
                            <p>Admin</p>
                        </div>
                    </div>
                </div>
                <p class="text-[#667085]  text-sm md:text-base">
                    This dashboard focuses on strategic metrics and overall system
                    performance.
                </p>
            </div>
        </div>
    </div>

    <section class="bg-slate-50 pb-10 px-5 md:px-7 pt-5">

        <system_analytics v-if="stat" :stat="stat" />
        <!-- <Department_Metrics/>
<div class=" md:flex items-center gap-5">
  <div class=" md:w-[49%]">
<workflowSummary/>
</div>
<div class=" md:w-[49%]">
<CommunicationMetric/>
</div>
</div>
<EmployeeProductivity/> -->
        <AccessLevel />
        <LoginActivity v-if="logins.length > 0" :logins="logins" />
        <!-- <Backup /> -->
        <!-- <ExecutiveSummary /> -->
        <!-- <SystemUptime /> -->
        <!-- <ResponseTime/> -->
    </section>


</template>

<script setup>
import { ref, computed, onMounted, provide } from "vue";
import AccessLevel from "../../components/AdminAnalytics/AccessLevel.vue";
import ResponseTime from "../../components/AdminAnalytics/ResponseTime.vue";
import system_analytics from "../../components/AdminAnalytics/system-analytics.vue"
import Backup from "../../components/AdminAnalytics/BackupStatus.vue";
import ExecutiveSummary from "../../components/ADF-Analytics/ExecutiveSummary.vue";
import LoginActivity from "../../components/AdminAnalytics/LoginActivity.vue";
import SystemUptime from "../../components/ADF-Analytics/SystemUptime.vue";
import Department_Metrics from '../../components/Director-analysis/Department-metrics.vue';
import workflowSummary from "../../components/Director-analysis/workflow-summary.vue";
import CommunicationMetric from "../../components/Director-analysis/communication-metrics.vue";
import EmployeeProductivity from "../../components/Director-analysis/Employee-Productivity.vue"


import image1 from "../../assets/images/Chart_gain.svg";
import image2 from "../../assets/images/Chart_loss.svg";

const selectedIndex = ref(1);
const stat = ref(null);
const logins = ref([]);
const handleSelection = (index) => {
    selectedIndex.value = index;
    console.log(selectedIndex.value);
};
const getStat = async () => {
    try {
        const response = await axios.get('/admin/analytics/server-status');
        stat.value = response.data;
        console.log('stats here', stat.value);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
provide('stat', stat)
const formatTimestamp = (timestamp) => {
    const date = new Date(timestamp);
    return date.toISOString().slice(0, 19).replace('T', ' ');
};
const getLogins = async () => {
    try {
        const response = await axios.get('/admin/analytics/user-logins');
        logins.value = response.data.map(login => ({
            ...login,
            created_at: formatTimestamp(login.created_at)
        }));
        console.log('logins details here', logins.value);
    } catch (error) {
        console.error('Error fetching data:', error);
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
    "Super Users": 11,
    Directors: 8,
    "Division Head": 64,
    "Unit Head": 11,
    "Data Entry": 8,
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
onMounted(() => {
    getStat()
    getLogins()
})
</script>

<style scoped>
.activeButton {
    background-color: white;
    border-radius: 5px;
    color: #0d6e1a;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>
