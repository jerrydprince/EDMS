<template>
  <div class="rounded-2xl border border-gray-100 bg-white mt-5 p-5">
    <h1 class="bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">
      Executive Summary
    </h1>
    <div class="grid gap-10 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="(item, index) in data" :key="index">
        <div class="border border-gray-100 rounded-xl p-5">
          <h1 class="text-gray-500 text-[14px]">{{ item.title }}</h1>
          <p class="text-gray-800 text-[30px]">{{ item.count }}</p>
          <div class=" flex items-center gap-3 text-green-500 text-[12px]" v-if="item.percent">
            <div class="flex items-center gap-1 bg-gray-50/50 p-1 px-2 rounded-2xl">
              <Icon icon="material-symbols:trending-up" />
              <p>{{ item.percent }}</p>
            </div>
            <p>vs last month</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script  setup>
const data = [
  {
    title: "Total documents count",
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
  {
    title: "Total Departments",
    count: "45",
 
  },
  {
    title: "Total Divisions",
    count: "120",
  
  },
  {
    title: "Total sections",
    count: "56",
   
  },
];
</script>

<style>
</style>