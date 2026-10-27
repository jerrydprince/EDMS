<template>
    <div class=" rounded-2xl bg-white dark:bg-gray-800 mt-5 p-5 overflow-x-auto lg:overflow-hidden border border-gray-100 dark:border-gray-700">
        <h1 class=" bg-slate-50 dark:bg-gray-900 dark:text-gray-300 p-2 rounded-3xl w-fit px-5 mb-3 border dark:border-gray-700">Access level summary</h1>
        <Bar v-if="chartData && chartData.labels && chartData.labels.length > 0" :data="chartData" :options="chartOptions" class=" w-full min-w-max" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Bar } from 'vue-chartjs';
ChartJS.register(...registerables);
const chartData = ref(null);
const chartOptions = ref(null);
const roles = inject('roles');
console.log("now here:", roles);
watch(roles, () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
const setChartData = () => {
    if (!roles.value) return { labels: [], datasets: [] };
    const labels = roles.value.map(role => role.name);
    const dataCounts = roles.value.map(role => role.users_count);
    return {
        labels: labels,
        datasets: [
            {
                label: 'Access Level Count',
                data: dataCounts,
                backgroundColor: ['#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A', '#A6F4C5'],
                borderRadius: 20
            }
        ]
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
    const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

    return {
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                    display: true,
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    autoSkip: false,
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary,
                    callback: (value) => Math.floor(value),
                    stepSize: 1,
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
}

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>

<style></style>
