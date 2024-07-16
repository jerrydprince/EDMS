<!-- src/components/RangeSlider.vue -->
<template>
    <div class="range-slider">
      <input
        ref="slider"
        type="range"
        :min="min"
        :max="max"
        v-model="internalValue"
        class="slider multicolors"
      />
      <div class="slider-thumb" :style="thumbStyle">
        <div class="tooltip text-sm p-2 whitespace-nowrap rounded-2xl shadow-xl flex-row flex items-center gap-1">
          <p class="whitespace-nowrap  text-primary/80"
          :style="{
                    color:
                    progressWidth < 33.33
                        ? '#FF4F26'
                        : progressWidth < 66.66
                        ? '#FEC84B'
                        :  '#13A928'
                  }"
          
          >{{ Math.round(progressWidth)  }}% <span class=" ml-2 bg-slate-50 text-gray-500  py-1 rounded-2xl px-2 text-[12px]"> {{ tooltipText }} </span></p>

        </div>
      </div>
      <div class="multicolor-progress" :style="{ width: progressWidth + '%' }"></div>
  
      <div
            class="flex items-center absolute mt-2 text-gray-400 w-full justify-between"
          >
            <div class="flex items-center flex-col">
              |
              <p>0</p>
            </div>
            <div class="flex items-center flex-col ml-8">
              |
              <p>50%</p>
            </div>
            <div class="flex items-center flex-col">
              |
              <p>100%</p>
            </div>
          </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch } from "vue";
  const props = defineProps({
    min: { type: Number, default: 0 },
    max: { type: Number, default: 100 },
    modelValue: { type: Number, default: 0 },
  });

  const emit = defineEmits(["update:modelValue"]);
  
  const internalValue = ref(props.modelValue);
  
  watch(() => props.modelValue, (newVal) => {
    internalValue.value = newVal;
  });
  
  watch(internalValue, (newVal) => {
    emit("update:modelValue", newVal);
  });
  
  const progressWidth = computed(() => {
    return ((internalValue.value - props.min) / (props.max - props.min)) * 100;
  });


const tooltipText = computed(() => {
  if (progressWidth.value < 33.33) {
    return "Critical";
  } else if (progressWidth.value < 66.66) {
    return "Poor";
  } else {
    return "Meets Expectation";
  }
});
  
  const thumbStyle = computed(() => {
    return {
      left: `calc(${progressWidth.value}% - 0px)`, // Adjust as necessary for your design
    };
  });
  </script>
  
  <style scoped>
  input.slider::before {
  display: none;
}
  .range-slider {
    width: 100%;
    position: relative;
    display: flex;
  }
  .range-slider .slider {
    -webkit-appearance: none;
    width: 100%;
    height: 0.4em;
    outline: none;
    border-radius: 5px;
    background: #e3e3e379;
  }
  
  .range-slider .slider.multicolors {
    background: linear-gradient(
      to right,
      #ff4f26 33.33%,
      #fec84b 33.33% 66.66%,
      #13a928 66.66%
    );
  }
  
  .range-slider .slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 1.5em;
    height: 1.5em;
  
    background-clip: white;
    z-index: 5;
    position: relative;
    cursor: pointer;
  }
  
  .range-slider .slider-thumb {
    position: absolute;
    cursor: pointer;
    width: 1.5em;
    height: 1.5em;
    background: white;
    border: 3px solid #13a928;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    z-index: 2;
    transform: translate(-50%, -45%);
  }
  
  .range-slider:hover .tooltip {
    display: block;
  }
  .range-slider .tooltip {
    display: none;
    position: absolute;
    user-select: none;
    top: -2.5em;
    left: 50%;
    transform: translate(-50%, -45%);
    background-color: white;
  }
  .range-slider .tooltip::before {
    content: "";
    position: absolute;
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 10px solid white;
    bottom: -0.5em;
    left: 50%;
    transform: translate(-50%);
  }
  .range-slider .multicolor-progress {
    width: 50%;
    height: 0.4em;
    border-radius: 5px;
    position: absolute;
    top: 50%;
    transform: translateY(0%);
    left: 0;
  }
  </style>
  