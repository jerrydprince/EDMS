<template>
  <div class="rounded-2xl border border-gray-100 bg-white mt-5 p-5">
    <h1 class="bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">
      Executive Summary
    </h1>
    <div class="grid gap-10 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="(item, index) in data" :key="index">
        <div
          class="border rounded-xl p-5"
          :class="
            clicked_index === index ? 'border-green-500' : 'border-gray-100'
          "
          @click="toggle_select(index)"
        >
          <h1 class="text-gray-500 text-[14px]">{{ item.title }}</h1>
          <p class="text-gray-800 text-[30px]">{{ item.count }}</p>
          <div
            class="flex items-center gap-3 text-green-500 text-[12px]"
            v-if="item.percent"
          >
            <div
              class="flex items-center gap-1 bg-gray-50/50 p-1 px-2 rounded-2xl"
            >
              <Icon icon="material-symbols:trending-up" />
              <p>{{ item.percent }}</p>
            </div>
            <p>vs last month</p>
          </div>
        </div>
      </div>
    </div>

    <section class="px-7">
      <div class="card my-20 overflow-x-scroll lg:overflow-hidden  ">

        <Chart
          type="line"
          :data="chartData"
          :options="chartOptions"
          class=" h-[500px] min-w-max w-full"
        />
      </div>
    </section>
  </div>
</template>
  
  <script  setup>
import { onMounted, ref } from "vue";
const clicked_index = ref(0);

const toggle_select = (index) => {
  clicked_index.value = index;
};
const data = [
  {
    title: "Documents count",
    count: "738,256",
    percent: "25%",
  },
  {
    title: "Active users",
    count: "326",
    percent: "25%",
  },
  {
    title: "Workflow completed",
    count: "56,000",
    percent: "25%",
  },
];

onMounted(() => {
  chartData.value = setChartData();
  chartOptions.value = setChartOptions();
});

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
  return {
    labels: [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    datasets: [
      {
        label: "Users",
        data: [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56, 55, 40],
        fill: false,
        borderColor: "#12B76A",
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
</script>
  
  <style>
</style>
