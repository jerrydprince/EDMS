<template>
    <div class=" rounded-2xl bg-white mt-5 p-5 overflow-x-auto lg:overflow-hidden">
        <h1 class=" bg-slate-50 p-2 rounded-3xl w-fit px-5 mb-3">Access level summary</h1>
        <Chart type="bar" :data="chartData" :options="chartOptions" class=" w-full min-w-max" />
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
    // Create a map to count occurrences of each role
    const roleCounts = roles.value.reduce((acc, role) => {
        acc[role.name] = (acc[role.name] || 0) + 1; // Increment count
        return acc;
    }, {});
    // Extract labels and counts from the aggregated data
    const labels = Object.keys(roleCounts);
    const dataCounts = Object.values(roleCounts);
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
