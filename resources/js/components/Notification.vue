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
          <p class="bg-gray-100 text-gray-700 p-1 h-4 w-4 text-[8px] flex flex-col items-center justify-center rounded-sm">
            {{ getTotalNotification(item) }}
          </p>
        </button>
      </div>
    </div>

    <div v-for="(item, index) in props.notifications" :key="index">
      <div class="text-gray-700">
        <h3 class="text-[10px]">{{ index }}</h3>
        <div v-for="(notification, indexp) in item" :key="indexp">
          <!-- <div v-if="notification.type === 'chat'">
            <div
              class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg cursor-pointer hover:bg-slate-50"
            >
              <div v-if="notification.type === 'chat'">
                <div v-if="notification.isgroup">
                  <div class=" flex items-center gap-16">
                  <div class="relative flex items-center justify-center">
                    <div
                      v-for="(item, index) in notification.group_member"
                      :key="index"
                      class="absolute w-7 h-7 rounded-full shadow-2xl"
                      :style="{
                        left: `${index * 12}px`,
                        backgroundImage: `url(${item.avatar})`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                      }"
                    ></div>
                  </div>
                  <div class=" text-[12px] leading-[18px]">
                    <p ><span>{{ notification.group_member[0].name }} and {{ notification.group_member.length-1 }} others </span> <span class=" font-normal text-gray-500">sent a message to </span>  <span>Finance Department</span></p>
                    <p class=" font-normal text-[10px] text-gray-500">4 Hour ago </p>
                  </div>
                </div>
                </div>

                <div class="flex items-start gap-4" v-else>
                  <img :src="notification.avatar" class="h-7 mt-1" alt="" />
                  <div>
                    <p class="font-semibold text-[12px]">
                      {{ notification.sender }}
                      <span class="font-normal text-gray-500"
                        >sent you a new message</span
                      >
                    </p>
                    <p
                      class="text-[10px] font-normal leading-[13px] text-gray-500"
                    >
                      1 hour ago
                    </p>

                    <p
                      class="text-[10px] font-normal leading-[16px] bg-white border border-gray-100 rounded-lg p-4 mt-2"
                    >
                      {{ notification.message }}
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
              </div>
              <div v-if="notification.isRead">
                <Icon icon="icon-park-outline:dot" class="text-green-500" />
              </div>
            </div>
          </div> -->

          <div v-if="clickedButton == 'Task' && notification.type === 'App\\Notifications\\TaskNotification'">
            <Link  @click="closeNotification" :href="'/dashboard/view-task/'+notification.data.task.id" method="post" :data="{is_assigned:1, notification_id:notification.id}"
              class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
              <div class="flex items-start gap-4">
                <img v-if="notification.data && notification.data.user && notification.data.user.profile_image"
                  :src="base + notification.data.user.profile_image" class="h-7 mt-1" alt="" />
                <img src="../assets/images/profile.svg" class="h-[40px]" v-else alt="">
                <div>
                  <div class="font-semibold text-[12px]"
                    v-if="notification.data && notification.data.task && notification.data.task.status == 'pending'">
                  {{ notification?.data?.user?.first_name + ' ' + notification?.data?.user?.last_name }}
                  <span class="font-normal text-gray-500">assigned a task to you</span>
                  </div>


                  <p class="text-[10px] font-normal leading-[13px] text-gray-500">
                    1 hour ago
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

          <!-- <div v-if="notification.type === 'mentions'">
            <div
              class="items-start flex justify-between mt-3 gap-3 w-full p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
            >
              <div class="flex items-start gap-4">
                <img :src="notification.avatar" class="h-7 mt-1" alt="" />
                <div>
                  <p class="font-semibold text-[12px]">
                    {{ notification.sender }}
                    <span class="font-normal text-gray-500"
                      >mentioned you in
                      <span v-if="notification.message === 'comment'"> a </span>
                      <span class="text-gray-800 text-[12px] font-semibold">{{
                        notification.message
                      }}</span></span
                    ><span></span>
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
          </div>

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
import profile_1 from "../../../public/images/profile_1.svg";
import profile_2 from "../../../public/images/linda.svg";
import { useStore } from 'vuex';


const clickedButton = ref("chat");
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

const closeNotification = ()=>{
  //store.dispatch("fetchNotifications")
  emit('close')
}

const getTotalNotification = (item)=>{
  if (item === 'Task') {
    return props.total_task_notifications
  }
  if (item == 'Mail') {
    return 0
  }
  if (item == 'Chat') {
    return 0;
  }
  if (item == 'Other') {
    return 0;
  }
}

const buttons = ref(["Chat", "Mail", "Mentions", "Task", "Other", "Unread"]);
const notifications = ref([
  {
    day: "Today",
    notifications: [
      {
        type: "task",
        message: "assigned a task to you",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-29T10:45:00Z",
        isRead: false,
      },
      {
        type: "other",
        message: "Mia Ward archived 10  documents to Accounting Folder",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-29T10:45:00Z",
        isRead: false,
      },
      {
        type: "other",
        message: "Mia Ward archived 16  documents to Sales Division",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-29T10:45:00Z",
        isRead: false,
      },
      {
        type: "other",
        message: "Mia Ward archived 16  documents to Sales Division",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-29T10:45:00Z",
        isRead: false,
      },
      {
        id: 2,
        type: "task",
        message: "approved your task",
        sender: "Rich Wilson",
        avatar: profile,
        statusChange: {
          from: {
            name: "In Progress",
            icon: "nonicons:loading-16"
          },
          to: {
            name: "Completed",
            icon: "teenyicons:tick-circle-solid"
          },
        },
        date: "2024-07-29T12:30:00Z",
        isRead: true,
      },
      {
        id: 2,
        type: "task",
        message: "approved your task",
        sender: "Rich Wilson",
        avatar: profile,
        statusChange: {
          from: {
            name: "Under Review",
            icon: "mdi:timer-sand-complete"
          },
          to: {
            name: "Uncompleted",
            icon: "material-symbols:cancel"
          },
        },
        date: "2024-07-29T12:30:00Z",
        isRead: true,
      },
      {
        id: 3,
        type: "task",
        message: " moved a Append signature task to uncompleted",
        sender: "System",
        avatar: profile,
        statusChange: {
          from: {
            name: "In Progress",
            icon: "nonicons:loading-16"
          },
          to: {
            name: "Completed",
            icon: "teenyicons:tick-circle-solid"
          },
        },
        date: "2024-07-29T14:00:00Z",
        isRead: false,
      },
      {
        id: 4,
        type: "mail",
        message: "James Wilson sent you a new mail",
        sender: "James Wilson",
        avatar: profile,
        attachments: [
          {
            name: "Payment Contract",
            icon: "vscode-icons:file-type-powerpoint",
          },
        ],
        date: "2024-07-29T10:45:00Z",
        isRead: true,
      },
      {
        id: 5,
        type: "mail",
        message: "AGF sent you a new mail",
        sender: "AGF",
        avatar: profile,
        attachments: [],
        date: "2024-07-29T09:30:00Z",
        isRead: false,
      },
      {
        id: 6,
        type: "mail",
        message: "Rich Wilson sent you a new mail",
        sender: "Rich Wilson",
        avatar: profile,
        attachments: [
          {
            name: "Payment Contract",
            icon: "vscode-icons:file-type-powerpoint",
          },
        ],
        date: "2024-07-29T08:00:00Z",
        isRead: true,
      },
      {
        id: 7,
        type: "mentions",
        message: "comment",
        sender: "James Wilson",
        avatar: profile,
        context: "Great job on the budget proposal, it looks thorough and well-researched. I especially appreciate the detailed breakdown of projected expenses and revenue.",
        date: "2024-07-29T11:00:00Z",
        isRead: false,
      },
      {
        id: 8,
        type: "mentions",
        message: "Append signature Task",
        sender: "AGF",
        avatar: profile,
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
      {
        id: 8,
        type: "chat",
        isgroup: false,
        message:
          "It would be helpful to have a summary of completed tasks, any roadblocks encountered, and what the next steps are...",
        sender: "James Wilson",
        avatar: profile,
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
      {
        id: 8,
        type: "chat",
        isgroup: true,
        sender: "James Wilson",
        group_member: [
          {
            name: 'Rich Greg',
            avatar: profile
          },
          {
            name: 'william',
            avatar: profile_1
          },
          {
            name: 'johnson',
            avatar: profile_2
          },
        ],
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
    ],
  },
  {
    day: "Yesterday",
    notifications: [
      {
        id: 9,
        type: "task",
        message: "James Wilson assigned a task to you",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-28T10:45:00Z",
        isRead: true,
      },

      {
        id: 10,
        type: "task",
        message: "James Wilson assigned a task to you",
        sender: "James Wilson",
        avatar: profile,
        status: "assigned",
        date: "2024-07-28T08:30:00Z",
        isRead: false,
      },
      {
        id: 11,
        type: "mail",
        message: "James Wilson sent you a new mail",
        sender: "James Wilson",
        avatar: profile,
        attachments: [
          {
            name: "Payment Contract",
            icon: "vscode-icons:file-type-powerpoint",
          },
        ],
        date: "2024-07-28T14:45:00Z",
        isRead: true,
      },
      {
        id: 8,
        type: "mentions",
        message: "Append signature Task",
        sender: "AGF",
        avatar: profile,
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
      {
        id: 8,
        type: "chat",
        isgroup: false,
        message:
          "It would be helpful to have a summary of completed tasks, any roadblocks encountered, and what the next steps are...",
        sender: "James Wilson",
        avatar: profile,
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
      {
        id: 8,
        type: "chat",
        isgroup: true,
        sender: "James Wilson",
        group_member: [
          {
            name: 'Rich Greg',
            avatar: profile
          },
          {
            name: 'william',
            avatar: profile_1
          },
          {
            name: 'johnson',
            avatar: profile_2
          },
        ],
        date: "2024-07-29T13:00:00Z",
        isRead: true,
      },
      {
        id: 12,
        type: "mail",
        message: "Jasmine Smith sent you a new mail",
        sender: "Jasmine Smith",
        avatar: profile,
        attachments: [],
        date: "2024-07-28T13:30:00Z",
        isRead: false,
      },
      {
        id: 13,
        type: "mentions",
        message: "comment",
        sender: "Rich Wilson",
        avatar: profile,
        context: "Great job on the budget proposal, it looks thorough and well-researched. I especially appreciate the detailed breakdown of projected expenses and revenue.",
        date: "2024-07-28T11:15:00Z",
        isRead: true,
      },
      {
        id: 14,
        type: "mentions",
        message: "Append signature Task",
        sender: "Jasmine Smith",
        avatar: profile,
        date: "2024-07-28T12:45:00Z",
        isRead: false,
      },
    ],
  },
]);
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