<!-- src/components/RangeSlider.vue -->
<template>
    <div class="range-slider">
      <input
        ref="slider"
        type="range"
        :min="min"
        :max="max"
        v-model="internalValue"
        class="slider "
      />
      <div class="slider-thumb" :style="thumbStyle">
        <div class="tooltip text-xs p-2 rounded-2xl shadow-lg">
          <p class="whitespace-nowrap text-[#13A928] z-50">{{ tooltipText }}</p>
        </div>
      </div>
      <div class="progress" :style="{ width: progressWidth + '%' }"></div>
  
      <div
            class="flex items-center absolute mt-2 text-gray-400 w-full justify-between"
          >
            <div class="flex items-center flex-col" v-for="m in max">
              |
              <p>{{ m }}</p>
            </div>
            <!-- <div class="flex items-center flex-col">
              |
              <p>2</p>
            </div>
            <div class="flex items-center flex-col">
              |
              <p>3</p>
            </div>
            <div class="flex items-center flex-col">
              |
              <p>4</p>
            </div>
            <div class="flex items-center flex-col">
              |
              <p>5</p> 
            </div>-->
          </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch, defineProps } from "vue";

  const props = defineProps({
    min: { type: Number, default: 1 },
    max: { type: Number, default: 5 },
    modelValue: { type: Number, default: 1 },
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
  if (progressWidth.value < 50) {
    return "Below Expectation"
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
.range-slider .progress {
  width: 50%;
  height: 0.4em;
  border-radius: 5px;
  background: #13a928;
  position: absolute;
  top: 50%;
  transform: translateY(0%);
  left: 0;
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
