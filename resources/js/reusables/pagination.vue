<template>
  <div class="w-full flex justify-between px-20 ">
    <button
      class="text-gray-500 flex gap-3 items-center"
      @click="previousPage"
      :disabled="currentPage == 1"
    >
      <i class="pi pi-arrow-left"></i> Previous
    </button>
    <span>
      <button
        v-for="page in visiblePages"
        :key="page"
        @click="goToPage(page)"
        :class="{ active: currentPage  === page, 'ellipsis': page === '...'}"
      >
        {{ page }}
      </button>
    </span>
    <button
      class=" text-gray-500 flex gap-3 items-center"
      @click="nextPage"
        :disabled="currentPage == totalPages"
    >
      Next <i class="pi pi-arrow-right"></i>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { toRefs } from 'vue';

const props = defineProps({
  totalItems: {
    type: Number,
    required: true
  },
  itemsPerPage: {
    type: Number,
    default: 5
  },
  modelValue: {
    type: Number,
    default: 1
  }
});

const emit = defineEmits(['update:modelValue', 'setPage']);

const { totalItems, itemsPerPage, modelValue } = toRefs(props);

const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
const currentPage = computed(() => modelValue.value);

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = modelValue.value;
  
  if (total <= 10) {
    return Array.from({ length: total }, (_, index) => index + 1);
  } else {
    const start = Math.max(1, current - 3);
    const end = Math.min(total, current + 3);
    let pages = [];
    
    if (start > 1) pages.push(1);
    if (start > 2) pages.push("...");
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    if (end < total) pages.push("...");
    if (end < total - 1) pages.push(total);
    return pages;
  }
});

const nextPage = () => {
  if (modelValue.value < totalPages.value) {
    emit('update:modelValue', modelValue.value + 1);
  }
  
};

const previousPage = () => {
  if (modelValue.value > 1) {
    emit('update:modelValue', modelValue.value - 1);
  }
};

const goToPage = (page) => {
  if (page !== '...') {
    emit('setPage', page);
  }

};
</script>

<style scoped>

button.active {
  background-color: #DBEBDD;
  height: 30px;
  width: 30px;
  border-radius: 5px;
  font-size: medium;
  color: #084911;
}

button.ellipsis {
  cursor: default;
  pointer-events: none;
}

button {
  border: none;
  background: none;
  cursor: pointer;
  margin: 0 5px;
}

button:disabled {
  color: #c0c0c0;
}

.paginator span {
  display: flex;
  gap: 5px;
}
</style>
