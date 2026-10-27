<template>
  <div>
     <div class="flex items-center justify-between w-full p-3">
        <h1 class="font-semibold text-lg">Productivity Chart</h1>
        <button
          @click="generateReport"
          class="p-2 rounded-md text-xs font-semibold text-gray-700 hover:bg-slate-50 border flex items-center gap-1 border-gray-300 transition-colors"
          title="Download Report PDF"
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
import {onMounted,ref, defineProps} from 'vue'

const props = defineProps(['chart'])
const labels = ref([])
const values = ref([])

onMounted(() => {
  chartData.value = setChartData();
  chartOptions.value = setChartOptions();
  groupedData()
  getMonthData()
});

const chartData = ref();
const chartOptions = ref();

const formatDateToMonth = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
  return `${year}-${month}`;
};

const groupedData =()=>{
  props.chart.reduce((acc, item) => {
  const month = formatDateToMonth(item.created_at);
  
    let index = labels.value.findIndex((item)=>{
      return item == month
    })
    if(index == -1){
      labels.value.push(month)
    }
}, {})
} 

const getMonthData = ()=>{
  const sumByMonth = props.chart.reduce((acc, item) => {
  const date = new Date(item.created_at);
  const yearMonth = `${date.getFullYear()}-${date.getMonth() + 1}`; // Format as YYYY-MM
  const value = parseFloat(item.value);

  if (!acc[yearMonth]) {
    acc[yearMonth] = 0;
  }
  
  acc[yearMonth] += value;
  return acc;
}, {});

  if(sumByMonth){
    values.value.push(sumByMonth)
  }
}


const setChartData = () => {

  return {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug","Sep","Oct","Nov","Dec"],
    datasets: [
      {
        label: "Users",
        data: [65, 59, 80, 81, 56, 55, 40,65, 59, 80, 81, 56, 55, 40],
        fill: true,
        borderColor: "#0B5C16",
        backgroundColor: "#0e7a1c1f",
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

const generateReport = () => {
  const match = window.location.pathname.match(/\/pms-user-profile\/(\d+)/);
  if (match && match[1]) {
    window.open(`/dashboard/pms/report/user/${match[1]}`, '_blank');
  } else {
    window.open('/dashboard/pms/report/department', '_blank');
  }
};
</script>

<style>

</style>
