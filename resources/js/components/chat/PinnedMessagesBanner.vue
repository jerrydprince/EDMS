<template>
  <div v-if="messages && messages.length > 0" class="sticky top-[73px] z-20 mx-4 lg:mx-7 mt-2 transition-all">
    <div class="flex items-center justify-between bg-white/95 backdrop-blur-md border border-blue-100 rounded-2xl shadow-sm px-4 py-2.5 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer" @click="jumpToCurrentPin">
        <!-- Pin Icon badge -->
        <div class="w-8 h-8 rounded-full bg-blue-50 text-primary flex items-center justify-center flex-shrink-0">
          <Icon icon="lucide:pin" class="text-base" />
        </div>

        <!-- Pin Content Preview -->
        <div class="flex flex-col min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-primary uppercase tracking-wide">
              Pinned Message {{ currentPinIndex + 1 }}/{{ messages.length }}
            </span>
            <span class="text-xs text-gray-400">•</span>
            <span class="text-xs font-semibold text-gray-700 truncate">
              {{ currentMessage.sender_name || currentMessage.sender?.first_name || 'Someone' }}
            </span>
          </div>
          <p class="text-xs text-gray-600 truncate mt-0.5">
            {{ currentMessage.message || 'Attachment message' }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1 flex-shrink-0 ml-3">
        <!-- Prev Pin -->
        <button
          v-if="messages.length > 1"
          type="button"
          @click.stop="prevPin"
          class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors"
          title="Previous pinned message"
        >
          <Icon icon="lucide:chevron-left" class="text-lg" />
        </button>

        <!-- Next Pin -->
        <button
          v-if="messages.length > 1"
          type="button"
          @click.stop="nextPin"
          class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors"
          title="Next pinned message"
        >
          <Icon icon="lucide:chevron-right" class="text-lg" />
        </button>

        <!-- Unpin current message button -->
        <button
          type="button"
          @click.stop="$emit('unpin', currentMessage)"
          class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors ml-1"
          title="Unpin this message"
        >
          <Icon icon="lucide:pin-off" class="text-base" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  messages: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['jump', 'unpin']);

const currentPinIndex = ref(0);

const currentMessage = computed(() => {
  if (!props.messages || props.messages.length === 0) return {};
  if (currentPinIndex.value >= props.messages.length) {
    currentPinIndex.value = 0;
  }
  return props.messages[currentPinIndex.value] || {};
});

const nextPin = () => {
  if (props.messages.length === 0) return;
  currentPinIndex.value = (currentPinIndex.value + 1) % props.messages.length;
  jumpToCurrentPin();
};

const prevPin = () => {
  if (props.messages.length === 0) return;
  currentPinIndex.value = (currentPinIndex.value - 1 + props.messages.length) % props.messages.length;
  jumpToCurrentPin();
};

const jumpToCurrentPin = () => {
  if (currentMessage.value && currentMessage.value.id) {
    emit('jump', currentMessage.value.id);
  }
};
</script>
