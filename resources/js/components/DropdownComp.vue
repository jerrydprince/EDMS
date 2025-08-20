<template>
  <div class="w-full max-w-4xl mx-auto">
    <div class="relative w-full" ref="dropdownWrapper">
      <!-- Main dropdown trigger -->
      <div
        ref="triggerElement"
        class="w-full bg-white border border-gray-300 rounded-lg px-3 py-[9px] flex items-center justify-between cursor-pointer transition-all duration-200 hover:border-gray-300 focus:outline-none text-gray-500"
        :class="{ 'shadow-lg': isOpen }"
        @click="toggleDropdown"
      >
        <span class="text-sm">{{
          selectedItem?.title || selectedItem?.name || placeholder
        }}</span>
        <Icon
          :icon="isOpen ? 'mdi:chevron-up' : 'mdi:chevron-up-down'"
          width="16"
        />
      </div>

      <!-- Dropdown content -->
      <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 transform -translate-y-2 scale-95"
        enter-to-class="opacity-100 transform translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-out"
        leave-from-class="opacity-100 transform translate-y-0 scale-100"
        leave-to-class="opacity-0 transform -translate-y-2 scale-95"
      >
        <div
          v-if="isOpen"
          ref="dropdownContent"
          class="absolute left-0 right-0 bg-white border border-gray-300 rounded-xl shadow-xl z-50 overflow-hidden max-h-80 overflow-y-auto px-1 pt-2 pb-1"
          :class="dropdownPositionClass"
          @click.stop
        >
          <div
            v-for="(item, index) in items"
            :key="index"
            class="px-3 sm:px-4 py-2 sm:py-3 text-sm text-gray-500 cursor-pointer transition-colors duration-150 mb-1 hover:bg-gray-100 rounded-xl"
            :class="{
              'bg-gray-100 text-gray-600': selectedItem === item,
            }"
            @click="selectItem(item)"
          >
            {{ item?.title || item?.name }}
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";

const props = defineProps({
  placeholder: {
    type: String,
    default: "Select an option",
  },
  items: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue", "change"]);

const isOpen = ref(false);
const selectedItem = ref(props.modelValue || props.items[0] || "");
const dropdownPosition = ref("bottom"); // 'top' or 'bottom'

const triggerElement = ref(null);
const dropdownContent = ref(null);
const dropdownWrapper = ref(null);

const dropdownPositionClass = computed(() => {
  return dropdownPosition.value === "bottom"
    ? "top-full mt-2"
    : "bottom-full mb-2";
});

const calculateDropdownPosition = async () => {
  if (!triggerElement.value) return;

  await nextTick();

  const triggerRect = triggerElement.value.getBoundingClientRect();
  const viewportHeight = window.innerHeight;
  const dropdownHeight = 320;

  const spaceBelow = viewportHeight - triggerRect.bottom;
  const spaceAbove = triggerRect.top;

  // If there's not enough space below but enough space above, position on top
  if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
    dropdownPosition.value = "top";
  } else {
    dropdownPosition.value = "bottom";
  }
};

const toggleDropdown = async (event) => {
  event.preventDefault();
  event.stopPropagation();

  if (!isOpen.value) {
    await calculateDropdownPosition();
  }

  isOpen.value = !isOpen.value;
};

const selectItem = (item) => {
  selectedItem.value = item;
  isOpen.value = false;
  emit("update:modelValue", item);
  emit("change", item);
};

const closeDropdown = (event) => {
  if (!dropdownWrapper.value?.contains(event.target)) {
    isOpen.value = false;
  }
};

const handleResize = () => {
  if (isOpen.value) {
    calculateDropdownPosition();
  }
};

const handleScroll = () => {
  if (isOpen.value) {
    calculateDropdownPosition();
  }
};

onMounted(() => {
  document.addEventListener("click", closeDropdown);
  window.addEventListener("resize", handleResize);
  window.addEventListener("scroll", handleScroll, true);

  if (props.modelValue) {
    selectedItem.value = props.modelValue;
  }
});

onUnmounted(() => {
  document.removeEventListener("click", closeDropdown);
  window.removeEventListener("resize", handleResize);
  window.removeEventListener("scroll", handleScroll, true);
});
</script>
