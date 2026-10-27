<template>
  <Sidebar
    :visible="visible"
    @update:visible="$emit('update:visible', $event)"
    position="right"
    class="w-full sm:w-[420px] md:w-[460px] p-sidebar-md"
    :pt="{
      header: { class: 'p-4 border-b border-gray-100' },
      content: { class: 'p-0 overflow-y-auto' }
    }"
  >
    <template #header>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center">
          <Icon icon="material-symbols:star-rounded" class="text-xl" />
        </div>
        <div>
          <h2 class="text-base font-bold text-gray-900">Starred Messages</h2>
          <p class="text-xs text-gray-400">{{ messages.length }} starred {{ messages.length === 1 ? 'item' : 'items' }}</p>
        </div>
      </div>
    </template>

    <div class="p-4">
      <!-- Search inside starred messages -->
      <div v-if="messages.length > 3" class="relative mb-4">
        <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" />
        <input
          v-model="searchFilter"
          type="text"
          placeholder="Filter starred messages..."
          class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
        />
      </div>

      <!-- Empty State -->
      <div v-if="filteredMessages.length === 0" class="flex flex-col items-center justify-center py-16 text-center px-4">
        <div class="w-16 h-16 rounded-full bg-yellow-50/60 text-yellow-400 flex items-center justify-center mb-3">
          <Icon icon="material-symbols:star-outline" class="text-3xl" />
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">No starred messages</h3>
        <p class="text-xs text-gray-400 max-w-xs leading-relaxed">
          Star important messages, decisions, or files from the chat options menu to quickly access them here.
        </p>
      </div>

      <!-- Starred Message Cards -->
      <div v-else class="space-y-3">
        <div
          v-for="msg in filteredMessages"
          :key="msg.id"
          class="p-3.5 rounded-2xl bg-white border border-gray-100 shadow-xs hover:border-yellow-200 hover:shadow-sm transition-all group"
        >
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-gray-900">
                {{ msg.sender_name || msg.sender?.first_name || 'Someone' }}
              </span>
              <span class="text-[11px] text-gray-400">{{ msg.timestamp }}</span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
              <button
                type="button"
                @click="$emit('jump', msg.id)"
                class="p-1 text-gray-400 hover:text-primary hover:bg-blue-50 rounded-md transition-colors text-xs flex items-center gap-1"
                title="Jump to message in conversation"
              >
                <Icon icon="lucide:external-link" class="text-sm" />
                <span class="text-[11px]">Jump</span>
              </button>
              <button
                type="button"
                @click="$emit('unstar', msg)"
                class="p-1 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-md transition-colors"
                title="Remove star"
              >
                <Icon icon="material-symbols:star-rounded" class="text-yellow-500 text-base" />
              </button>
            </div>
          </div>

          <!-- Message text -->
          <p class="text-xs text-gray-700 leading-relaxed break-words whitespace-pre-wrap">
            {{ msg.message }}
          </p>

          <!-- Attachment snippet if any -->
          <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-2 pt-2 border-t border-gray-50 flex items-center gap-2 text-xs text-gray-500">
            <Icon icon="lucide:paperclip" class="text-gray-400" />
            <span>{{ msg.attachments.length }} {{ msg.attachments.length === 1 ? 'attachment' : 'attachments' }}</span>
          </div>
        </div>
      </div>
    </div>
  </Sidebar>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';
import Sidebar from 'primevue/sidebar';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  messages: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['update:visible', 'jump', 'unstar']);

const searchFilter = ref('');

const filteredMessages = computed(() => {
  if (!searchFilter.value || searchFilter.value.trim() === '') {
    return props.messages;
  }
  const query = searchFilter.value.toLowerCase();
  return props.messages.filter(msg => {
    return (
      (msg.message && msg.message.toLowerCase().includes(query)) ||
      (msg.sender_name && msg.sender_name.toLowerCase().includes(query)) ||
      (msg.sender?.first_name && msg.sender.first_name.toLowerCase().includes(query))
    );
  });
});
</script>
