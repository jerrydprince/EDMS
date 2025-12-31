<template>
  <main class="p-4">
    <div class="items-center flex justify-between w-full">
      <h1 class="text-[20px]">Your Notifications</h1>
      <div class="flex items-center gap-2 text-[12px] text-green-500">
        <Icon icon="hugeicons:tick-double-02" />
        <p>Mark all as read</p>
      </div>
    </div>
    <div class="flex items-center w-full bg-gray-50 gap-1 p-1 rounded-md justify-between mt-2">
      <div v-for="(item, index) in buttons" :key="index">
        <button class="flex items-center gap-1 px-4 py-1 rounded-md" @click="activeButton(item, index)"
          :class="buttonToggle === index ? 'bg-white' : ''">
          <p class="text-gray-700 text-[10px] font-normal">{{ item }}</p>
          <p
            class="bg-gray-100 text-gray-700 p-1 h-4 w-4 text-[8px] flex flex-col items-center justify-center rounded-sm">
            {{ getTotalNotification(item) }}
          </p>
        </button>
      </div>
    </div>

    <div>
      <div class="text-gray-700">
        <h3 class="text-[10px]">{{ index }}</h3>
        <div>
          <div>
            <div v-for="(nots, index) in chats">
              <div v-for="chat in nots">
                <div v-if="clickedButton == 'Chat'"
                  class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg cursor-pointer hover:bg-slate-50">
                  <div>
                    <!-- <div v-if="notification.isgroup"> -->
                    <div>
                      <div class=" flex items-center gap-16">
                        <div class="relative flex items-center justify-center">
                          <!-- <div
                      v-for="(item, index) in notification.group_member"
                      :key="index"
                      class="absolute w-7 h-7 rounded-full shadow-2xl"
                      :style="{
                        left: `${index * 12}px`,
                        backgroundImage: `url(${item.avatar})`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                      }"
                    ></div> -->

                        </div>
                        <!-- <div class=" text-[12px] leading-[18px]">
                          <p><span>{{ chat.chat.group_type }} </span> <span class=" font-normal text-gray-500">sent a
                              message to </span> <span>{{chat.chat.group_name}}</span></p>
                          <p class=" font-normal text-[10px] text-gray-500">{{ formatRelativeTime(chat.chat.created_at) }} </p>
                        </div>
                      </div>
                    </div>

                    <div class="flex items-start gap-4">
                      <img class="h-7 mt-1" alt="" />
                      <div>
                        <p class="font-semibold text-[12px]">
                          {{ chat.chat.sender_name }}
                          <span class="font-normal text-gray-500">sent you a new message</span>
                        </p>
                        <p class="text-[10px] font-normal leading-[13px] text-gray-500">
                          {{ formatRelativeTime(chat.chat.created_at) }}
                        </p>

                        <p
                          class="text-[10px] font-normal leading-[16px] bg-white border border-gray-100 rounded-lg p-4 mt-2">
                          {{ chat.chat.message }}
                        </p>
                         -->

                        <!-- <div
                      class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                      v-for="(attached, index) in notification.attachments"
                      :key="index"
                    >
                      <Icon :icon="attached.icon" />
                      <p class="text-[10px] font-normal text-gray-500">
                        {{ attached.name }}
                      </p>
                    </div> -->
                      </div>
                    </div>
                  </div>
                  <!-- <div v-if="chat">
                <Icon icon="icon-park-outline:dot" class="text-green-500" />
              </div> -->
                </div>
              </div>
            </div>
          </div>

          <div v-if="clickedButton == 'Task'">
            <div v-for="(task_notifications, ind) in getNotificationType('task')" :key="ind">
              <h3 class="text-[10px] py-3">{{ ind }}</h3>
              <div v-for="notification in task_notifications">
                <Link @click="closeNotification" v-if="notification.type == 'App\\Notifications\\TaskNotification'"
                  :href="'/dashboard/view-task/' + notification.data.task.id" method="post"
                  :data="{ is_assigned: 1, notification_id: notification.id }"
                  class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                <div class="flex items-start gap-4">
                  <img v-if="notification.data && notification.data.user && notification.data.user.profile_image"
                    :src="base + notification.data.user.profile_image" class="h-7 mt-1" alt="" />
                  <img :src="'../assets/images/profile.svg'" class="h-[40px]" v-else alt="">
                  <div>
                    <div class="font-semibold text-[12px]"
                      v-if="notification.data && notification.data.task && notification.data.task.status == 'pending'">
                      {{ notification?.data?.user?.first_name + ' ' + notification?.data?.user?.last_name }}
                      <span class="font-normal text-gray-500">assigned a task to you</span>
                    </div>

                    <p class="text-[10px] font-normal leading-[13px] text-gray-500">
                      {{ formatRelativeTime(notification.created_at) }}
                    </p>

                    <div class=" flex items-center gap-2 text-[10px] mt-1" v-if="notification.statusChange">
                      <div class=" flex items-center gap-1 bg-slate-50 px-3 py-0.5 rounded-md" :class="notification.statusChange.from.name === 'In Progress'
                        ? 'text-yellow-500'
                        : notification.statusChange.from.name === 'Under Review'
                          ? 'text-orange-500'
                          : ''">
                        <Icon :icon='notification.statusChange.from.icon' />
                        <p>{{ notification.statusChange.from.name }}</p>
                      </div>

                      <Icon icon="bi:arrow-right" />
                      <div class=" flex items-center gap-1 bg-slate-50 px-3 py-0.5 rounded-md" :class="notification.statusChange.to.name === 'Completed' ? 'text-green-500'
                        : notification.statusChange.to.name === 'Uncompleted'
                          ? 'text-orange-500'
                          : ''">
                        <Icon :icon='notification.statusChange.to.icon' />
                        <p>{{ notification.statusChange.to.name }}</p>
                      </div>
                    </div>
                    <div class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                      v-for="(attached, index) in notification.attachments" :key="index">
                      <Icon :icon="attached.icon" />
                      <p class="text-[10px] font-normal text-gray-500">
                        {{ attached.name }}
                      </p>
                    </div>
                  </div>
                </div>
                <div v-if="notification.read_at">
                  <Icon icon="icon-park-outline:dot" class="text-green-500" />
                </div>
                </Link>

                <!-- this is for finance and accounts -->
                <Link @click="closeNotification" v-if="notification.type == 'App\\Notifications\\PaymentAssignedNotification'"
                  :href="'/finance-and-accounts/view-payment/'+notification.data.comment[0].payment_id" method="get"
                  :data="{notification: notification.id}"
                  class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                <div class="flex items-start gap-4">
                  <img v-if="notification.data && notification.data.user && notification.data.user.profile_image"
                    :src="base + notification.data.user.profile_image" class="h-7 mt-1" alt="" />
                  <img :src="'../assets/images/profile.svg'" class="h-[40px]" v-else alt="">
                  <div>
                    <div class="font-semibold text-[12px]"
                      v-if="notification.data && notification.data.comment && notification.data.comment.length > 0">
                      <span class="font-normal text-gray-500">{{notification.data?.message}}</span>
                    </div>

                    <p class="text-[10px] font-normal leading-[13px] text-gray-500">
                      {{ formatRelativeTime(notification.created_at) }}
                    </p>

                    
                    <!-- <div class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                      v-for="(attached, index) in notification.attachments" :key="index">
                      <Icon :icon="attached.icon" />
                      <p class="text-[10px] font-normal text-gray-500">
                        {{ attached.name }}
                      </p>
                    </div> -->
                  </div>
                </div>
                <div v-if="notification.read_at">
                  <Icon icon="icon-park-outline:dot" class="text-green-500" />
                </div>
                </Link>
              </div>
            </div>
          </div>

          <!-- <div v-if="notification.type === 'mail'">
            <div
              class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
            >
              <div class="flex items-start gap-4">
                <img :src="notification.avatar" class="h-7 mt-1" alt="" />
                <div>
                  <p class="font-semibold text-[12px]">
                    {{ notification.sender }}
                    <span class="font-normal text-gray-500"
                      >sent you a new mail</span
                    >
                  </p>
                  <p
                    class="text-[10px] font-normal leading-[13px] text-gray-500"
                  >
                    1 hour ago
                  </p>
                  <div
                    class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                    v-for="(attached, index) in notification.attachments"
                    :key="index"
                  >
                    <Icon :icon="attached.icon" />
                    <p class="text-[10px] font-normal text-gray-500">
                      {{ attached.name }}
                    </p>
                  </div>
                </div>
              </div>
              <div v-if="notification.isRead">
                <Icon icon="icon-park-outline:dot" class="text-green-500" />
              </div>
            </div>
          </div> -->

          <div v-if="clickedButton === 'Comments'">
            <div v-for="(comments, ind) in getNotificationType('comments')">
              <h3 class="text-[10px] py-3">{{ ind }}</h3>
              <div
                class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
                v-for="notification in comments">

                <div class="flex items-start gap-4">
                  <img v-if="notification.data && notification.data.user && notification.data.user.profile_image"
                    :src="base + notification.data.user.profile_image" class="h-7 mt-1" alt="" />
                  <img :src="'../assets/images/profile.svg'" class="h-[40px]" v-else alt="">
                  <div>
                    <p class="font-semibold text-[12px]">
                      {{ notification.data.comment.user?.first_name + " " + notification.data.comment.user?.last_name }}
                      <span class="font-normal text-gray-500">mentioned you in
                        <!-- <span v-if="notification.message === 'comment'"> a </span> -->
                        <span>a</span>
                        <span class="text-gray-800 text-[12px] font-semibold"> comment</span></span><span></span>
                    </p>
                    <p class="text-[10px] font-normal leading-[13px] text-gray-500">
                      {{ formatRelativeTime(notification.created_at) }}
                    </p>
                    <p
                      class="text-[10px] font-normal leading-[16px] bg-white border border-gray-100 rounded-lg p-4 mt-2">
                      {{ textTruncate(notification.data?.comment?.comment) }}
                    </p>
                    <!-- <div
                    class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                    v-for="(attached, index) in notification.attachments"
                    :key="index"
                  >
                    <Icon :icon="attached.icon" />
                    <p class="text-[10px] font-normal text-gray-500">
                      {{ attached.name }}
                    </p>
                  </div> -->
                  </div>
                </div>
                <div v-if="comments.read_at">
                  <Icon icon="icon-park-outline:dot" class="text-green-500" />
                </div>
              </div>
            </div>
          </div>

          <!--
          <div v-if="notification.type === 'other'">
            <div
              class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
            >
              <div class="flex items-start gap-4">
                <img :src="notification.avatar" class="h-7 mt-1" alt="" />
                <div>
                  <p class="font-semibold text-[12px]">
                   {{ notification.message }}
                  </p>
                  <p
                    class="text-[10px] font-normal leading-[13px] text-gray-500"
                  >
                    1 hour ago
                  </p>
                  <p
                  v-if="notification.context"
                      class="text-[10px] font-normal leading-[16px] bg-white border border-gray-100 rounded-lg p-4 mt-2"
                    >
                      {{ notification.context}}
                    </p>
                  <div
                    class="bg-gray-50 mb-2 rounded-2xl px-4 py-1 w-fit flex items-center gap-3"
                    v-for="(attached, index) in notification.attachments"
                    :key="index"
                  >
                    <Icon :icon="attached.icon" />
                    <p class="text-[10px] font-normal text-gray-500">
                      {{ attached.name }}
                    </p>
                  </div>
                </div>
              </div>
              <div v-if="notification.isRead">
                <Icon icon="icon-park-outline:dot" class="text-green-500" />
              </div>
            </div>
          </div> -->
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from "vue";
import { usePage, Link } from "@inertiajs/vue3"
import profile from "../../../public/images/profile.svg";
import profile_1 from "../../../public/images/Profile_1.svg";
import profile_2 from "../../../public/images/linda.svg";
import { useStore } from 'vuex';
import moment from "moment"


const clickedButton = ref("Chat");
const buttonToggle = ref(0);
const props = defineProps(['notifications', 'total_task_notifications'])
const activeButton = (item, index) => {
  buttonToggle.value = index;
  clickedButton.value = item;
};
const emit = defineEmits(['close'])
const page = usePage();
const base = computed(() => page.props.base)
const store = useStore()
const global_chat = computed(() => store.getters.getNewChat)
const chats = computed(() => sortChatsByDate());
const taskNotification = ref([])
const commentNotification = ref([])

const getItem = (item) => {

  if (item === 'App\\Notifications\\TaskNotification') {
    return "Task"
  }
  if (item == 'App\\Notifications\\Mail') {
    return "Mail"
  }
  if (item == 'App\\Notifications\\Chat') {
    return "Chat"
  }
  if (item == 'App\\Notifications\\Other') {
    return "Other"
  }
}

const formatRelativeTime = (createdAt) => {
  return moment(createdAt).fromNow(); // "3 minutes ago", "2 hours ago", etc.
};

// Function to format date like "22nd September"
function formatDate(date) {
  const options = { day: 'numeric', month: 'long' };
  return new Date(date).toLocaleDateString('en-US', options);
}

// Helper function to check if a date is today, yesterday, or another day
function classifyDate(dateString) {
  const today = new Date();
  const oneDay = 24 * 60 * 60 * 1000;
  const chatDate = new Date(dateString);
  const timeDifference = today - chatDate;

  if (timeDifference < oneDay && chatDate.getDate() === today.getDate()) {
    return 'Today';
  } else if (timeDifference < 2 * oneDay && chatDate.getDate() === today.getDate() - 1) {
    return 'Yesterday';
  } else {
    return formatDate(chatDate);
  }
}

function textTruncate(text) {
  if(text){
    return text.length > 50 ? text.slice(0, 50) + "..." : text
  }else{
    return ""
  }
}

// Main function to sort chats by date
function sortChatsByDate() {
  const sortedChats = {
    Today: [],
    Yesterday: []
  };

  // Iterate over the chats and classify them
  if (global_chat.value && global_chat.value.length > 0) {
    global_chat.value.forEach(chat => {
      const classifiedDate = classifyDate(chat.date);

      if (classifiedDate === 'Today') {
        // if(chat.group_type){
        //   let key = chat.group_type+"_"+chat.group_id
        //   if (sortedChats.Today[key] && Array.isArray(sortedChats.Today[key])) {
        //     sortedChats.Today[key].push(chat);
        //   } else {
        //     sortedChats.Today.push({key: chat})
        //   }
        // }
        if (chat.group_type) {
          let key = chat.group_type + "_" + chat.group_id;

          // Initialize the key if Today[key] doesn't exist
          if (!sortedChats.Today[`${key}`]) {
            console.log(key)
            sortedChats.Today[`${key}`] = [];
          }
          sortedChats.Today[`${key}`].push(chat);
        }
        else {
          sortedChats.Today.push(chat);
        }
      } else if (classifiedDate === 'Yesterday') {
        sortedChats.Yesterday.push(chat);
      } else {
        // Dynamically create keys for dates like "22nd September"
        if (!sortedChats[classifiedDate]) {
          sortedChats[classifiedDate] = [];
        }
        sortedChats[classifiedDate].push(chat);
      }
    });

    return sortedChats;
  } else {
    return []
  }
}


const closeNotification = () => {
  //store.dispatch("fetchNotifications")
  emit('close')
}

const getTotalNotification = (item) => {
  if (item === 'Task') {
    let count = 0;
    const not = getNotification('task')
    for (let item in not) {
      count += not[item].length
    }
    return count
  }
  if (item == 'Mail') {
    return 0
  }
  if (item == 'Chat') {
    return 0;
  }
  if (item == 'Comments') {
    let count = 0;
    const not = getNotification('comments')
    for (let item in not) {
      count += not[item].length
    }
    return count
  }
  if (item == 'Other') {
    return 0;
  }
}

const getNotification = (type) => {
  if (props.notifications && props.notifications.length > 0) {
    if (type === 'task') {
      const firstNotification = props.notifications[0];

      // Check if the specific notification type key exists
      if ('App\\Notifications\\TaskNotification' in firstNotification) {
        taskNotification.value = firstNotification['App\\Notifications\\TaskNotification']
        return firstNotification['App\\Notifications\\TaskNotification'];
      }

      if ('App\\Notifications\\PaymentAssignedNotification' in firstNotification) {
        taskNotification.value = firstNotification['App\\Notifications\\PaymentAssignedNotification']
        return firstNotification['App\\Notifications\\PaymentAssignedNotification'];
      }
    }

    if (type === 'comments') {
      const firstNotification = props.notifications[0];

      // Check if the specific notification type key exists
      if ('App\\Notifications\\CommentNotification' in firstNotification) {
        commentNotification.value = firstNotification['App\\Notifications\\CommentNotification']
        return firstNotification['App\\Notifications\\CommentNotification'];
      }
    }
  }
}

const getNotificationType = (type) => {
  // for (let item of props.notifications) {
  //   if (item[type] !== undefined) {
  //     console.log(item[type])
  //     return item[type]
  //   } else {
  //     return []
  //   }
  // }

  // if(props.notifications && props.notifications.length > 0){
  //   if(type == 'task'){
  //   for(let not in props.notifications[0]){
  //     console.log(not)
  //     console.log(typeof not)
  //     if(not == 'App\Notifications\TaskNotification'){
  //       console.log('yes')
  //       console.log(props.notifications[0]['App\Notifications\TaskNotification'])
  //       return props.notifications[0]['App\Notifications\TaskNotification']
  //     }else{
  //       console.log(' it is not ')
  //     }
  //   }
  // }
  // }

  const not = getNotification(type)
  return not

}

const buttons = ref(["Chat", "Mail", "Comments", "Task"]);

const filteredItems = computed(() => {
  return notifications.value
    .map((day) => {
      const filteredNotifications = day.notifications.filter((item) => {
        if (clickedButton.value === "Unread") {
          return item.isRead === true;
        } else {
          return (
            item.type &&
            item.type.toLowerCase().includes(clickedButton.value.toLowerCase())
          );
        }
      });

      if (filteredNotifications.length > 0) {
        return {
          ...day,
          notifications: filteredNotifications,
        };
      } else {
        return null;
      }
    })
    .filter((day) => day !== null); // Filter out days that are null
});

</script>

<style></style>