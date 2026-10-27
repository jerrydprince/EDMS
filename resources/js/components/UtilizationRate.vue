<template>
  <div>
    <div class="flex items-center justify-between w-full p-3">
      <h1 class="font-semibold text-lg">Utilization Rate</h1>
      <button
        class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
      >
        <Icon icon="pepicons-pop:file" />
        <p>Generate Report</p>
      </button>
    </div>
    <div class="bg-white rounded-md pt-10 px-2 w-full justify-between">

      
      <!-- <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
        <circle cx="80" cy="80" r="70" stroke-linecap="round" />
        <circle cx="80" cy="80" r="70" stroke-linecap="round" />
      </svg> -->

      <div class="my-10 w-full flex items-center justify-center flex-col">
        <div class="gauge__outer">
          <div class="gauge__inner">
            <div
              class="gauge__fill"
              :style="{ transform: `rotate(${cssTransformRotateValue})` }"
            ></div>
            <div class="gauge__cover">{{ percentage.toFixed(0) }}%</div>
          </div>
        </div>
        <div
          class="flex mt-2 text-lg text-gray-500 items-center justify-between w-full max-w-[300px]"
        >
          <p class="pl-3">0</p>
          <p class="pl-7">Percentage</p>
          <p>100%</p>
        </div>
      </div>
      <div class="flex items-center gap-1 py-2">
        <Icon icon="oui:dot" class="text-[#027079]" />
        <p class="text-sm">Percentage of time spent on productive tasks</p>
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
.gauge__outer {
  width: 100%;
  max-width: 300px;
}

.gauge__inner {
  width: 100%;
  height: 0;
  padding-bottom: 50%;
  background: #b4c0be;
  position: relative;
  border-top-left-radius: 100% 200%;
  border-top-right-radius: 100% 200%;
  overflow: hidden;
}

/* svg {
  width: 200px;
  height: 200px;
}
svg circle {
  width: 200px;
  height: 200px;
  fill: none;
  stroke-width: 20;
  stroke: #026e78;
  transform: translate(5px, 5px);
  stroke-dasharray: 440;
  stroke-dashoffset: 440;
  stroke-linecap: round;
}

svg circle:nth-child(1) {
  stroke-dashoffset: 0;
  stroke: #f3f3f3;
}

svg circle:nth-child(2) {
  stroke-dashoffset: calc(440 - (440 * 87) / 100);
  stroke: #13a928;
} */

.gauge__fill {
  position: absolute;
  top: 100%;
  left: 0;
  width: inherit;
  height: 100%;
  background: #026e78;
  transform-origin: center top;
  transform: rotate(0turn);
  transition: transform 0.2s ease-out;
}

.gauge__cover {
  width: 75%;
  height: 150%;
  background: #ffffff;
  position: absolute;
  top: 25%;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 50%;

  /* Text */
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 25%;
  box-sizing: border-box;
  font-family: "Lexend", sans-serif;
  font-weight: bold;
  font-size: 32px;
}
</style>
