<template>
    <div>
       <div class="flex items-center justify-between w-full p-3">
          <h1 class="font-semibold text-lg">Team Collaborations</h1>
          <button
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
          >
            <Icon icon="pepicons-pop:file" />
            <p>Generate Report</p>
          </button>
        </div>
        <div class="bg-white rounded-md pt-10 flex items-start px-2 w-full justify-between">
      <Chart
            type="line"
            :data="chartData"
            :options="chartOptions"
            class=" h-[300px] w-full"
          />
      
      </div> 
    </div>
  </template>
  
  <script  setup>
  import {onMounted,ref} from 'vue'
  onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
  });
  
  const chartData = ref();
  const chartOptions = ref();
  
  const setChartData = () => {
  
    return {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug","Sep","Oct","Nov","Dec"],
      datasets: [
        {
          label: "Users",
          data: [125, 10, 80, 81, 56, 55, 40,65, 59, 80, 81, 56, 55, 40],
          fill: true,
          borderColor: "#038C8C",
          backgroundColor: "#EDFDEF",
          tension: 0.5,
        },

        {
          label: "Users",
          data: [65, 49, 80, 11, 36, 55, 20,65, 59, 70, 81, 16, 15, 40],
          fill: true,
          borderColor: "#022859",
          backgroundColor: "#EAFAFE",
          tension: 0.5,
        },
      ],
    };
  };
  const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue("--text-color");
    const textColorSecondary = documentStyle.getPropertyValue(
      "--text-color-secondary"
    );
    const surfaceBorder = documentStyle.getPropertyValue("--surface-border");
  
    return {
      maintainAspectRatio: false,
      aspectRatio: 0.9,
      plugins: {
        legend: {
          labels: {
            color: textColor,
          },
        },
      },
      scales: {
        x: {
          ticks: {
            color: textColorSecondary,
          },
          grid: {
            color: surfaceBorder,
          },
        },
        y: {
          ticks: {
            color: textColorSecondary,
          },
          grid: {
            color: surfaceBorder,
          },
        },
      },
    };
  };
  </script>
  
  <style>
  
  </style>
