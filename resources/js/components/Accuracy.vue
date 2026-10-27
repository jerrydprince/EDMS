<template>
  <div>
    <div class="flex items-center justify-between w-full p-3 relative">
      <h1 class="font-semibold text-lg">Accuracy</h1>
      <button
        class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
      >
        <Icon icon="pepicons-pop:file" />
        <p>Generate Report</p>
      </button>
    </div>
    <div class="bg-white rounded-md pt-10 px-2 w-full justify-between">
      <div class="relative h-72 flex items-center flex-col justify-center">
        <div class=" relative flex flex-col items-center justify-center">
       <div class=" absolute -left-10 top-10">
        <p class=" text-[#13A928]"> 27%</p>
        <div class=" mt-0.5 relative">
          <div class=" h-[0.5px] w-[70px] absolute bg-[#13A928]"></div>
          <div  class=" absolute  w-[1px] -rotate-45 -mt-2 -right-14 h-[50px] bg-[#13A928] "></div>
        </div>
       </div>
       <div class=" absolute -right-16 top-5">
        <p class=" text-[#12B76A]">27%</p>
        <div class=" mt-0.5">
          <div class=" h-[0.5px] w-[50px] ml-0.5 bg-[#12B76A]"></div>
          <div  class=" w-[1px] rotate-45 -mt-2 -ml-4 h-[50px] bg-[#12B76A] "></div>
        </div>
       </div>
        <div class="inner-circle absolute translate-x-[5%] translate-y-[5%] ">
          <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
            <circle cx="80" cy="80" r="70" stroke-linecap="round" />
            <circle cx="80" cy="80" r="70" stroke-linecap="round" />
          </svg>
        </div>
        <div class="outer-circle  ">
          <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
            <circle cx="120" cy="120" r="110" stroke-linecap="round" />
            <circle cx="120" cy="120" r="110" stroke-linecap="round" />
          </svg>
        </div>
    </div>
        
      </div>

      <div class="flex items-center gap-1 py-2">
        <Icon icon="oui:dot" class="text-[#0B5C16]" />
        <p class="text-sm  text-gray-400">Error rate in financial transactions and reports</p>
      </div>
      <div class="flex items-center gap-1  pb-2">
        <Icon icon="oui:dot" class="text-[#13A928]" />
        <p class="text-sm text-gray-400">Percentage of data entry errors corrected</p>
      </div>
    </div>
  </div>
</template>
    
    <script  setup>
import { ref, computed } from "vue";

const percentage = ref(0);

const cssTransformRotateValue = computed(() => {
  const percentageAsFraction = percentage.value / 100;
  const halfPercentage = percentageAsFraction / 2;

  return `${halfPercentage}turn`;
});
</script>
    
    <style  scoped>
.outer-circle svg {
  width: 260px; /* Adjusted size */
  height: 260px; /* Adjusted size */
}
.outer-circle svg circle {
  width: 260px; /* Adjusted size */
  height: 260px; /* Adjusted size */
  fill: none;
  stroke-width: 25;
  stroke: #12B76A;
  transform: translate(5px, 5px);
  stroke-dasharray: 690.8; /* Adjusted for new radius */
  stroke-dashoffset: 690.8; /* Adjusted for new radius */
  stroke-linecap: round;
}

.outer-circle svg circle:nth-child(1) {
  stroke-dashoffset: 0;
  stroke: #f3f3f3;
}

.outer-circle svg circle:nth-child(2) {
  stroke-dashoffset: calc(
    690.8 - (690.8 * 87) / 100
  ); /* Adjusted for new radius */
  stroke: #12B76A;
}

.inner-circle svg {
  width: 200px;
  height: 200px;
}
.inner-circle svg circle {
  width: 200px;
  height: 200px;
  fill: none;
  stroke-width: 25;
  stroke: #13A928;
  transform: translate(5px, 5px);
  stroke-dasharray: 440;
  stroke-dashoffset: 440;
  stroke-linecap: round;
}

.inner-circle svg circle:nth-child(1) {
  stroke-dashoffset: 0;
  stroke: #f3f3f3;
}

.inner-circle svg circle:nth-child(2) {
  stroke-dashoffset: calc(440 - (440 * 87) / 100);
  stroke: #13a928;
}
</style>
