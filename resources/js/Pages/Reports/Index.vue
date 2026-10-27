<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Layout from '../dashboard/Layout.vue';
import axios from 'axios';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  ArcElement
} from 'chart.js';
import { Bar, Line, Pie } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  ArcElement
);

const props = defineProps({
    auth: Object
});

const loading = ref(true);
const error = ref(null);
const dateFilter = ref('30');
const rawData = ref(null);

// Chart Data Refs
const workflowStatusData = ref({ labels: [], datasets: [] });
const registryTrendsData = ref({ labels: [], datasets: [] });
const fileTrackingData = ref({ labels: [], datasets: [] });
const departmentPerformanceData = ref({ labels: [], datasets: [] });
const slaData = ref({ labels: [], datasets: [] });
const welfareData = ref({});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};

const fetchAnalytics = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(route('reports.analytics'), {
            params: { dateFilter: dateFilter.value }
        });
        const data = response.data;
        rawData.value = data;

        // Map Workflow Status
        workflowStatusData.value = {
            labels: Object.keys(data.workflowStatus),
            datasets: [
                {
                    label: 'Workflow Status',
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#6366F1'],
                    data: Object.values(data.workflowStatus)
                }
            ]
        };

        // Map Registry Trends
        registryTrendsData.value = {
            labels: data.registryTrends.labels,
            datasets: [
                {
                    label: 'Mails Dispatched/Received',
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    data: data.registryTrends.data,
                    fill: true
                }
            ]
        };

        // Map File Tracking
        fileTrackingData.value = {
            labels: data.fileTrackingTrends.labels,
            datasets: [
                {
                    label: 'File Movements',
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    data: data.fileTrackingTrends.data,
                    fill: true
                }
            ]
        };

        // Map Department Performance
        if (data.departmentPerformance && data.departmentPerformance.labels) {
            departmentPerformanceData.value = {
                labels: data.departmentPerformance.labels,
                datasets: [
                    {
                        label: 'Completed Tasks',
                        backgroundColor: '#10B981',
                        data: data.departmentPerformance.data
                    }
                ]
            };
        }

        // Map SLA Data
        slaData.value = {
            labels: data.slaData.labels,
            datasets: [
                {
                    label: 'SLA Status',
                    backgroundColor: ['#10B981', '#EF4444'],
                    data: data.slaData.data
                }
            ]
        };

        // Welfare
        welfareData.value = data.welfare;

    } catch (err) {
        console.error(err);
        error.value = "Failed to load reports data. Please try again.";
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
});
</script>

<template>
    <Head title="Reports & Analytics" />
    
    <Layout>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
            <!-- Header -->
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Reports & Analytics</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Authorized scopes based on your role hierarchy.</p>
                </div>
                <!-- Actions -->
                <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                    <select v-model="dateFilter" @change="fetchAnalytics" class="form-select bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-500 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 font-medium w-full">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 3 Months</option>
                        <option value="365">Last Year</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="flex justify-center items-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-lg shadow-sm border border-red-200">
                {{ error }}
            </div>

            <div v-else class="grid grid-cols-12 gap-6">
                
                <!-- SLA Compliance (Pie) -->
                <div class="col-span-12 md:col-span-4 bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">SLA Compliance</h2>
                    <div class="h-64 flex justify-center">
                        <Pie v-if="slaData.datasets[0].data.length" :data="slaData" :options="chartOptions" />
                        <div v-else class="text-slate-500 mt-10">No SLA data available</div>
                    </div>
                </div>

                <!-- Workflow Status (Pie) -->
                <div class="col-span-12 md:col-span-4 bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Workflow Status</h2>
                    <div class="h-64 flex justify-center">
                        <Pie v-if="workflowStatusData.datasets[0].data.length" :data="workflowStatusData" :options="chartOptions" />
                        <div v-else class="text-slate-500 mt-10">No workflow data available</div>
                    </div>
                </div>

                <!-- Welfare & Pool (Cards) -->
                <div class="col-span-12 md:col-span-4 flex flex-col gap-6">
                    <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex-1 flex flex-col justify-center items-center">
                        <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-1">{{ welfareData.pool_deployments || 0 }}</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400 uppercase font-semibold tracking-wide">Pool Deployments</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex-1 flex flex-col justify-center items-center">
                        <div class="text-3xl font-bold text-emerald-500 mb-1">{{ welfareData.leave_requests || 0 }}</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400 uppercase font-semibold tracking-wide">Leave / Training Requests</div>
                    </div>
                </div>

                <!-- Registry Trends (Line) -->
                <div class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Registry & Mail Trends</h2>
                    <div class="h-80">
                        <Line v-if="registryTrendsData.labels.length" :data="registryTrendsData" :options="chartOptions" />
                        <div v-else class="text-slate-500 mt-10 text-center">No registry data for this period</div>
                    </div>
                </div>

                <!-- File Tracking (Line) -->
                <div class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">File Tracking Movements</h2>
                    <div class="h-80">
                        <Line v-if="fileTrackingData.labels.length" :data="fileTrackingData" :options="chartOptions" />
                        <div v-else class="text-slate-500 mt-10 text-center">No file movement data for this period</div>
                    </div>
                </div>

                <!-- Departmental Performance (Bar) - Only if Global/Dept Scope -->
                <div v-if="departmentPerformanceData.labels && departmentPerformanceData.labels.length > 0" class="col-span-12 bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Departmental Performance (Completed Tasks)</h2>
                    <div class="h-96">
                        <Bar :data="departmentPerformanceData" :options="chartOptions" />
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
