<template>
     <div class=" rounded-2xl bg-white mt-5 p-5 overflow-x-auto lg:overflow-hidden">
        <h1 class=" bg-slate-50 p-2 rounded-3xl w-fit px-5 mb-3">Access level summary</h1>
      <Chart type="bar" :data="chartData" :options="chartOptions"   class=" w-full min-w-max" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted  } from 'vue';
const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    return {
        labels: ['AGF', 'Super  Admin', 'Admin', 'Records Manager', 'Super Users','Directors','Division Head','Unit Head','Data Entry','Registrars','Users'],
        datasets: [
            {
                label: '',
                data: [10, 325, 702, 620,10, 325, 702, 620,10, 325, 702],
                backgroundColor: ['#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A','#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A','#A6F4C5', '#12B76A', '#A6F4C5'],
                borderRadius:20
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
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary
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

<style>

</style>
