<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="p-5 border-b flex justify-between items-center bg-slate-50">
      <h1 class="font-semibold text-gray-800 text-2xl flex items-center gap-2">
        <Icon icon="lucide:message-circle" width="25" />
        Task Discussion
      </h1>
      <button @click="$emit('close')" class="flex items-center gap-2 text-gray-500 hover:text-gray-800">
        <Icon icon="flowbite:angle-left-outline" /> Back
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex justify-center items-center">
      <Icon icon="line-md:loading-twotone-loop" width="40" class="text-primary" />
    </div>

    <!-- Chat Area -->
    <div v-else class="flex-1 flex flex-col h-[700px] bg-white">
      <!-- Messages List -->
      <div class="flex-1 overflow-y-auto p-5 space-y-4" ref="messagesContainer">
        <div v-if="messages.length === 0" class="text-center text-gray-500 mt-10">
          No messages yet. Start the discussion!
        </div>
        
        <div v-for="msg in messages" :key="msg.id" class="flex w-full" :class="{'justify-end': isMine(msg)}">
          <!-- Others Message -->
          <div v-if="!isMine(msg)" class="flex gap-3 max-w-[80%]">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
               <img :src="getProfileImage(msg)" alt="User" class="w-full h-full object-cover"/>
            </div>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-medium text-gray-700">{{ msg.sender_name }}</span>
                <span class="text-xs text-gray-400">{{ msg.timestamp }}</span>
              </div>
              <div class="bg-gray-100 text-gray-800 p-3 rounded-2xl rounded-tl-none">
                {{ msg.message }}
              </div>
            </div>
          </div>
          
          <!-- My Message -->
          <div v-else class="flex gap-3 max-w-[80%] justify-end">
            <div>
              <div class="flex items-center justify-end gap-2 mb-1">
                <span class="text-xs text-gray-400">{{ msg.timestamp }}</span>
              </div>
              <div class="bg-primary text-white p-3 rounded-2xl rounded-tr-none">
                {{ msg.message }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 bg-slate-50 border-t">
        <form @submit.prevent="sendMessage" class="flex gap-2">
          <input 
            v-model="newMessage" 
            type="text" 
            placeholder="Type a message..." 
            class="flex-1 bg-white border border-gray-200 rounded-full px-4 py-2 outline-none focus:ring-2 focus:ring-primary/20"
            :disabled="sending"
          />
          <button 
            type="submit" 
            class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-primary/90 transition-colors"
            :disabled="sending || !newMessage.trim()"
          >
            <Icon v-if="!sending" icon="lucide:send" width="18" />
            <Icon v-else icon="line-md:loading-twotone-loop" width="18" />
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  task: Object
});

const emit = defineEmits(['close']);

const page = usePage();
const currentUser = page.props.user;

const loading = ref(true);
const sending = ref(false);
const chatGroup = ref(null);
const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);

const initChat = async () => {
  loading.value = true;
  try {
    // 1. Get or create the chat group for this task
    const res = await axios.post(`/dashboard/task/${props.task.id}/chat-group`);
    chatGroup.value = res.data.group;

    // 2. Load messages
    await loadMessages();

    // 3. Setup WebSocket listener
    setupEcho();
  } catch (error) {
    console.error("Error initializing task chat:", error);
  } finally {
    loading.value = false;
  }
};

const loadMessages = async () => {
  if (!chatGroup.value) return;
  try {
    // Re-use the existing endpoint for group messages (simulating "department" group structure internally)
    const res = await axios.get(`/dashboard/get-group-messages/${chatGroup.value.id}/task`);
    // Format expects an array of date objects, we'll flatten it for the task view
    if (res.data) {
      let flatMessages = [];
      Object.values(res.data).forEach(day => {
        flatMessages = [...flatMessages, ...day.messages];
      });
      messages.value = flatMessages;
      scrollToBottom();
    }
  } catch (error) {
    console.error("Error loading messages:", error);
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value || !chatGroup.value) return;
  
  sending.value = true;
  try {
    // Reuse existing chat sending endpoint
    await axios.post('/dashboard/send-group-message', {
      group_id: chatGroup.value.id,
      message: newMessage.value,
      group_type: 'task'
    });
    
    newMessage.value = '';
    await loadMessages();
  } catch (error) {
    console.error("Error sending message:", error);
  } finally {
    sending.value = false;
  }
};

const setupEcho = () => {
  if (window.Echo && chatGroup.value) {
    window.Echo.private(`chat.group.${chatGroup.value.id}`)
      .listen('GroupMessageSent', (e) => {
        // Only load if it's not our own message (which is handled instantly in real apps, but we reload here)
        if (e.message.user_id !== currentUser.id) {
          loadMessages();
        }
      });
  }
};

const isMine = (msg) => {
  return msg.sender_id === currentUser.id;
};

const getProfileImage = (msg) => {
  if (msg && msg.profile_image) {
    return `${page.props.base}/${msg.profile_image}`;
  }
  return `${page.props.base}/images/user.png`;
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

onMounted(() => {
  initChat();
});

watch(() => props.task.id, () => {
  initChat();
});
</script>
