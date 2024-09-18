<template>
    <div class=" rounded-2xl bg-white mt-5 p-5 overflow-x-auto lg:overflow-hidden">

        <div class=" md:flex items-center w-full justify-between">
       <h1 class=" bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">Workflow summary</h1>

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
     <Chart type="bar" :data="chartData" :options="chartOptions"   class=" w-full min-w-max" />
   </div>
</template>

<script setup>
import { ref, computed, onMounted  } from 'vue';
const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
   return {
       labels: ['Workflow created', 'Completed', 'Under Review', 'In Progress', 'Uncompleted'],
       datasets: [
           {
               label: '',
               data: [10, 325, 702, 620,10, 325, 702, 620,10, 325, 702],
               backgroundColor: ['#A6F4C5', '#12B76A', '#A6F4C5', '#12B76A','#A6F4C5'],
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