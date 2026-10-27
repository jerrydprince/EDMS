<template>
  <!-- <main class=" flex w-full hide_scroll"> -->
  <main class="h-screen flex w-full fixed top-0 dark:bg-gray-900 dark:text-white transition-colors duration-200">
    <Navigation />
    <section class="hide_scroll w-full overflow-y-scroll bg-gray-50 dark:bg-gray-900">
      <TopBar :toggleSearchFunction="toggleSearchState" :total_task_notifications="total_task_notifications" :notifications="notifications"/>
      <slot />
    </section>

    <Toast :pt="{
      root: { class: 'bg-transparent mr-5 mt-3' },
      container: {
        class: 'bg-white border border-[#0E7A1D] border-solid rounded-xl drop-shadow-xl',
      },
      buttonContainer: {
        class:
          'border border-solid border-gray-500 rounded-full px-0 py-0 -mt-10 -mr-4',
      },
      closeButton: { class: 'p-0 px-2 pb-1' },
      summary: { class: 'text-gray-800 font-semibold text-lg' },
      detail: { class: 'font-light text-gray-500' },
    }">
      <template #icon="{ }">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
          <path
            d="M22 11.5799V12.4999C21.9988 14.6563 21.3005 16.7545 20.0093 18.4817C18.7182 20.2088 16.9033 21.4723 14.8354 22.0838C12.7674 22.6952 10.5573 22.6218 8.53447 21.8744C6.51168 21.1271 4.78465 19.746 3.61096 17.9369C2.43727 16.1279 1.87979 13.9879 2.02168 11.8362C2.16356 9.68443 2.99721 7.63619 4.39828 5.99694C5.79935 4.35768 7.69279 3.21525 9.79619 2.74001C11.8996 2.26477 14.1003 2.4822 16.07 3.35986M22 4.49986L12 14.5099L9.00001 11.5099"
            stroke="#0E7A1D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </template>
    </Toast>
  </main>
  <div class="top-0 absolute">
      <searchResult
        v-if="toggleSearch"
        :toggleSearchFunction="toggleSearchState"
        @closeModal="closeModal"
      />
    </div>
</template>

  
<script setup>
  import { ref,onMounted, computed, provide, watch } from "vue";
import TopBar from "./TopBar.vue";
import Navigation from "./Navigation.vue";
import searchResult from "../components/Modals/SearchResult.vue";
import { usePage, router } from "@inertiajs/vue3";
import { useStore } from 'vuex';
import { useToast } from "primevue/usetoast";
import { playNotificationChime } from "../utils/audio";

const toast = useToast();
const toggleSearch = ref(false);
const page = usePage()
const user = computed(()=>page.props.user);
const roles = computed(()=>page.props.user_roles)
const notifications = computed(()=> store.getters.getNotifications)
const total_task_notifications = computed(()=>store.getters.getTotalTaskNotifications)
const store = useStore()
const current_page = computed(()=> page.url)
const comment = ref([])



  const toggleSearchState = () => {
    toggleSearch.value = !toggleSearch.value;
  };

  const closeModal = ()=> {
    toggleSearch.value = false
  }

  const getRole = (user)=>{
    if(roles.value.includes('unit_heads')){
      return 'unit_heads'
    }
    if(roles.value.includes('division_heads')){
      return 'division_heads'
    }
    if(roles.value.includes('director')){
      return 'director'
    }
    if(roles.value.includes('super user')){
      return 'super_users'
    }
    if(roles.value.includes('admin')){
      return 'admin'
    }
    if(roles.value.includes('super admin')){
      return 'super_admin'
    }
    return 'users'
  }

  watch(current_page, ()=>{
    store.dispatch("fetchNotifications")
  })

  const getUserNotifications = ()=>{
    axios.get('/user-notifications').then((response)=>{
      //notifications.value = response.data.notifications
     // total_task_notifications.value = response.data.count
      store.commit('setNotifications', response.data)
    })
  }
  provide('notifications', notifications)
  provide('comments', comment)
  onMounted(()=>{
    
    window.addEventListener('axios-error', (e) => {
      toast.add({ severity: 'error', summary: e.detail.summary, detail: e.detail.message, life: 5000 });
    });

    //getUserNotifications()
     store.dispatch("fetchNotifications")
    // notifications.value = store.state.notifications
    // total_task_notifications.value = store.state.total_task_notifications



    if (user.value && user.value.id) {
        window.Echo.private(`App.Models.User.${user.value.id}`)
            .notification((notification) => {
                console.log("Global Real-time Notification Received:", notification);
                
                // Play audio chime
                playNotificationChime();

                // Show in-app toast popup for discussion message
                if (notification.type?.includes('CommentNotification') || notification.comment_obj || notification.comment) {
                    const senderName = notification.user ? `${notification.user.first_name} ${notification.user.last_name}` : 'New Message';
                    const taskTitle = notification.task_title || 'Task Discussion';
                    const messageText = notification.comment || notification.comment_obj?.comments || 'Sent you a message';
                    
                    toast.add({
                        severity: 'info',
                        summary: `${senderName} • ${taskTitle}`,
                        detail: messageText,
                        life: 6000
                    });

                    // Dispatch specific event for real-time discussion components
                    window.dispatchEvent(new CustomEvent('live-comment', { detail: notification }));
                } else if (notification.type?.includes('ChatMessageNotification') || notification.is_chat) {
                    const senderName = notification.sender ? `${notification.sender.first_name} ${notification.sender.last_name}` : 'Chat';
                    const title = notification.group_name ? `💬 ${notification.group_name}` : `💬 ${senderName}`;
                    const messageText = notification.message || 'Sent you a chat message';

                    toast.add({
                        severity: 'info',
                        summary: title,
                        detail: messageText,
                        life: 6000
                    });

                    // Dispatch specific event for real-time chat components
                    window.dispatchEvent(new CustomEvent('live-chat-message', { detail: notification }));
                }

                // Fetch latest global notifications for top bar
                store.dispatch("fetchNotifications"); 
            });
    }


  })
</script>
 
  
