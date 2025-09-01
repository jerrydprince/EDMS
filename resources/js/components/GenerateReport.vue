<template>
  <section class="relative">
    <button @click="toggleDropdown"
      class="flex items-center gap-2 rounded-lg border border-solid border-gray-300 px-3 md:px-4 py-2 text-gray-700 text-sm md:text-base">
      <Icon icon="material-symbols:download" width="20" />
      <template v-if="props.size === 'md' || props.size === 'lg'">
        <span> <span v-if="props.size === 'lg'">Generate </span>Report</span>
      </template>
    </button>
    <div v-if="isOpen"
      class="absolute md:right-0 left-0 md:left-auto z-10 mt-5 w-64 lg:w-[17rem] rounded-xl bg-white pt-3 pb-2 shadow-lg border-2"
      @click.stop>
      <p class="mb-4 ml-6 text-sm px-1 font-[450] text-gray-400">
        Select Timeframe
      </p>
      <ul class="space-y-2 px-1 pb-2">
        <li v-for="option in options" :key="option.value" @click="selectOption(option)"
          class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-3 hover:bg-gray-100">
          <Checkbox  v-model="selectedDate" :value="option.value" :modelValue="isSelected(option)" />
          <!-- <input
            type="checkbox"
            :checked="selectedOption.value === option.value"
            class="accent-green-600 w-4 h-4 border-gray-300 rounded-xl"
          /> -->
          <span class="text-sm text-gray-500 font-[450]">{{
            option.label
            }}</span>
        </li>
        <!-- Custom date Block -->
        <div v-if="selectedOption.value === 'custom'" class="mr-3 flex flex-col gap-y-5 items-end pb-5">
          <div class="text-gray-500 flex items-center gap-x-4">
            <p class="text-xs font-[450]">Start Date</p>
            <input type="date" v-model="customSelectionForm.startDate"
              class="border-2 w-32 rounded-md outline-none px-2 py-1 text-xs" />
          </div>
          <div class="text-gray-500 flex items-center gap-x-4">
            <p class="text-xs font-[450]">End Date</p>
            <input type="date" v-model="customSelectionForm.endDate"
              class="border-2 w-32 rounded-md outline-none px-2 py-1 text-xs" />
          </div>
        </div>
      </ul>
      <hr class="border" />
      <button
        class="mt-2 mx-1 mr-3 w-auto float-right rounded-lg border-2 px-7 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        @click="emitSelected">
        Download
      </button>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { defineEmits } from "vue";

const emit = defineEmits(["select"]);

const isOpen = ref(false);
const selectedDate = ref(null)

const props = defineProps({
  size: {
    type: String,
    default: "lg",
    validator: (value) => ["sm", "md", "lg"].includes(value),
  },
});

const customSelectionForm = ref({
  startDate: "",
  endDate: "",
});

// SELECT OPTION LOGICS HERE
const selectedOption = ref({ label: "", value: "" });

// Return Boolean for Checkbox component
const isSelected = computed(() => {
  return (option) => selectedOption.value?.value === option.value;
});

const options = [
  { label: "Today", value: "today" },
  { label: "Last 7 days", value: "last7" },
  { label: "Last 30 Days", value: "last30" },
  { label: "This Month", value: "thisMonth" },
  { label: "Last Month", value: "lastMonth" },
  { label: "Custom", value: "custom" },
];

// Setting the option here
function selectOption(option) {
  if (option.value == selectedOption.value?.value) {
    selectedOption.value = { label: "", value: "" };
  } else {
    selectedOption.value = option;
  }
}
// SELECT LOGICS ENDS HERE

function toggleDropdown() {
  isOpen.value = !isOpen.value;
}

function emitSelected() {
  if (selectedOption.value?.value === "custom") {
    emit("select", customSelectionForm.value);
  } else {
    emit("select", selectedOption.value);
  }
  isOpen.value = false;
}

function handleClickOutside(event) {
  if (!event.target.closest(".relative")) {
    isOpen.value = false;
  }
}

watch(selectedDate, (newVal)=>{
  selectedDate.value = null
  selectedDate.value = newVal
})

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>
