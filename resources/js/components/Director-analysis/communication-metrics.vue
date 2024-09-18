
<template>
     <div class=" rounded-2xl bg-white mt-5 p-5 overflow-x-auto lg:overflow-hidden">

        <div class=" md:flex items-center w-full justify-between">
       <h1 class=" bg-slate-50 p-2 whitespace-nowrap rounded-3xl w-fit px-5 mb-3">Communication Metrics</h1>

       <select
              name=""
              id=""
              class="w-40 px-2 py-2 outline-none bg-slate-50 rounded-md"
             
            >
              <option value="All Time" class="">All Time</option>
              <option value="Today">Today</option>
              <option value="Yesterday">Yesterday</option>
              <option value="This Week">This Week</option>
              <option value="10 days">10days</option>
              <option value="30 days">30days</option>
              <option value="60 days">60days</option>
            </select>

    </div>
    <div class=" flex items-center gap-2">
        <div>
        <div>
            <div class=" flex items-center gap-1">
               <div class=" h-4 rounded-lg w-1 bg-green-500"></div>
               <p class=" text-[14px] text-gray-500">Email Volume</p>
            </div>
            <h1 class=" text-[32px] font-semibold">52,010 </h1>
            <div class=" items-center flex gap-1 text-[12px]">
                <Icon icon="heroicons-outline:arrow-sm-up" />
                <p class=" text-gray-800 font-semibold">3200 <span class=" font-normal text-gray-500">sent</span></p>
            </div>
            <div class=" items-center flex gap-1 text-[12px]">
                <Icon icon="heroicons-outline:arrow-sm-down" />
                <p class=" text-gray-800 font-semibold">9200 <span class=" font-normal text-gray-500">recieved</span></p>
            </div>
        </div>

        <div class=" mt-5">
            <div class=" flex items-center gap-1">
               <div class=" h-4 rounded-lg w-1 bg-green-200"></div>
               <p class=" text-[14px] text-gray-500">Chat Volume</p>
            </div>
            <h1 class=" text-[32px] font-semibold">122,010  </h1>
            <div class=" items-center flex gap-1 text-[12px]">
                <Icon icon="heroicons-outline:arrow-sm-up" />
                <p class=" text-gray-800 font-semibold">3200 <span class=" font-normal text-gray-500">sent</span></p>
            </div>
            <div class=" items-center flex gap-1 text-[12px]">
                <Icon icon="heroicons-outline:arrow-sm-down" />
                <p class=" text-gray-800 font-semibold">9200 <span class=" font-normal text-gray-500">recieved</span></p>
            </div>
        </div>
    </div>
        <Chart type="line" :data="chartData" :options="chartOptions" class="h-[18rem] w-full" />
    </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

const chartData = ref();
const chartOptions = ref();
        
const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    return {
        labels: [2015, 2016, 2017, 2018, 2019],
        datasets: [
            {
                label: 'Dataset 1',
                fill: false,
                borderColor: '#12B76A',
                yAxisID: 'y',
                tension: 0.4,
                data: [65, 59, 80, 81, 56, 55, 10]
            },
            {
                label: 'Dataset 2',
                fill: false,
                borderColor: '#A6F4C5',
                yAxisID: 'y1',
                tension: 0.4,
                data: [28, 48, 40, 19, 86, 27, 90]
            }
        ]
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        stacked: false,
        maintainAspectRatio: false,
        aspectRatio: 0.6,
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
                type: 'linear',
                display: false,
                position: 'left',
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    drawOnChartArea: false,
                    color: surfaceBorder
                }
            }
        }
    };
}
</script>
