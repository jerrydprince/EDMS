<template>
  <div>
    <div class=" mt-7 flex items-center gap-16">
     <div >
      <h1 class=" text-3xl font-semibold">{{stat.uptime_percentage}}% </h1>
      <div class=" mt-1 flex items-center gap-1  text-gray-500">
        <p>Uptime</p>
        <Icon icon="mingcute:warning-fill" class=" rotate-180"/>
      </div>
     </div>
     <div >
      <h1 class=" text-3xl font-semibold whitespace-nowrap">{{stat.downtime}} </h1>
      <div class=" mt-1 flex items-center gap-1  text-gray-500">
        <p>Downtime</p>
        <Icon icon="mingcute:warning-fill" class=" rotate-180"/>
      </div>
     </div>
     <div >
      <h1 class=" text-3xl font-semibold">3 </h1>
      <div class=" mt-1 flex items-center gap-1  text-gray-500">
        <p>Incidents</p>
        <Icon icon="mingcute:warning-fill" class=" rotate-180"/>
      </div>
     </div>

     </div>

    <div class="chart-container min-w-[450px]  overflow-auto hide_scroll     ">
      <div class=" w-fit ">
      <div class=" flex items-center gap-[7px]">
      <div v-for="(i) in 55" :key="i">
         <div class=" w-2 h-16 rounded-lg"   :class="i === 20 || i === 7 || i === 10 ? 'bg-red-500' : 'bg-green-500'"> </div>
        </div>

      </div>
      <div class="labels text-gray-500 w-full mt-2">
        <div class=" flex items-center gap-1 text-left"  >
      <Icon icon="flowbite:angle-left-outline"  />
      <span  class="cursor-pointer ">Last 30 Days</span>
    </div>
        <span class="middle-label">June 15</span>
        <span class=" text-right"> Today</span>
      </div>
    </div>
      <!-- <ApexCharts :options="chartOptions" :series="chartSeries" type="line" height="150"></ApexCharts> -->
      <!-- <bar-chart :chart-data="chartData" :options="chartOptions"></bar-chart> -->

    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue';
import ApexCharts from 'vue3-apexcharts';
const stat = inject('stat');
console.log("injected", stat);

const dates = [
  ['2024-01-01', 2300000],
  ['2024-01-02', 2200000],
  ['2024-01-03', 2500000],
  ['2024-01-04', 2700000],
  ['2024-01-05', 2100000],
  ['2024-01-06', 2600000],
  ['2024-01-07', 2800000],
  ['2024-01-08', 3000000],
  ['2024-01-09', 2900000],
  ['2024-01-10', 3100000],
];
const chartSeries = [
  {
    name: 'XYZ MOTORS',
    data: dates,
  },
];

const chartOptions = {
  chart: {
    type: 'area',
    stacked: false,
    height: 350,
    zoom: {
      type: 'x',
      enabled: true,
      autoScaleYaxis: true,
    },
    toolbar: {
      autoSelected: 'zoom',
    },
  },
  dataLabels: {
    enabled: false,
  },
  markers: {
    size: 0,
  },
  title: {
    text: 'Stock Price Movement',
    align: 'left',
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      inverseColors: false,
      opacityFrom: 0.5,
      opacityTo: 0,
      stops: [0, 90, 100],
    },
  },
  yaxis: {
    labels: {
      formatter: (val) => (val / 1000000).toFixed(0),
    },
    title: {
      text: 'Price',
    },
  },
  xaxis: {
    type: 'datetime',
  },
  tooltip: {
    shared: false,
    y: {
      formatter: (val) => (val / 1000000).toFixed(0),
    },
  },
};
</script>

<style scoped>
.summary {
  display: flex;
  justify-content: space-between;
  padding: 0 20px;
}

.chart-container {
  position: relative;
  height: 100px; /* Adjust the height as needed */
  margin-top: 20px;
}

.labels {
  display: flex;
  justify-content: space-between;
  /* position: absolute;
  top: -20px; Adjust position as needed */
  /* width: 100%; */
  font-size: 14px;
}

.middle-label {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

h3 {
  margin: 0;
  padding: 0;
}
</style>
