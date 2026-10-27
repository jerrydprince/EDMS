<template>
  <main class="w-full h-full max-h-[85vh] flex flex-col bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden font-sans">
    <!-- Header -->
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-md z-20">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
          <Icon icon="lucide:bell" width="18" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-sm font-semibold text-gray-900 tracking-tight">Notifications</h1>
            <span 
              v-if="unreadCount > 0" 
              class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary text-white shadow-sm"
            >
              {{ unreadCount }} new
            </span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-1.5">
        <!-- Sound Mute/Unmute Toggle -->
        <button 
          @click="toggleSound" 
          :title="isMuted ? 'Unmute notification chime' : 'Mute notification chime'"
          class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
        >
          <Icon :icon="isMuted ? 'lucide:volume-x' : 'lucide:volume-2'" width="16" />
        </button>

        <!-- Mark All Read Button -->
        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead" 
          :disabled="isMarkingAll"
          class="flex items-center gap-1.5 text-xs text-primary font-medium hover:bg-primary/10 px-2.5 py-1.5 rounded-lg transition-all disabled:opacity-50"
          title="Mark all notifications as read"
        >
          <Icon :icon="isMarkingAll ? 'line-md:loading-loop' : 'lucide:check-check'" width="14" />
          <span>Mark all read</span>
        </button>

        <!-- Close Dropdown Button -->
        <button 
          @click="closeNotification" 
          class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
          title="Close notifications"
        >
          <Icon icon="lucide:x" width="16" />
        </button>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="px-4 pt-3 pb-2 border-b border-gray-100 bg-gray-50/60 flex flex-col gap-2.5">
      <!-- Tabs -->
      <div class="flex items-center gap-1 overflow-x-auto no-scrollbar">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap"
          :class="activeTab === tab.id ? 'bg-white text-primary shadow-sm font-semibold border border-gray-200/80' : 'text-gray-500 hover:text-gray-800 hover:bg-white/60'"
        >
          <Icon :icon="tab.icon" width="13" />
          <span>{{ tab.label }}</span>
          <span 
            v-if="tab.count > 0" 
            class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold"
            :class="activeTab === tab.id ? 'bg-primary/10 text-primary' : 'bg-gray-200 text-gray-600'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Quick Search Bar -->
      <div class="relative">
        <Icon icon="lucide:search" width="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Filter by sender, tracking ID, or task title..." 
          class="w-full pl-8 pr-7 py-1.5 text-xs bg-white border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
        />
        <button 
          v-if="searchQuery" 
          @click="searchQuery = ''" 
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
          <Icon icon="lucide:x" width="12" />
        </button>
      </div>
    </div>

    <!-- Notifications List Content Area -->
    <div class="flex-1 overflow-y-auto no-scrollbar divide-y divide-gray-100">
      <!-- Empty State -->
      <div v-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-14 px-6 text-center">
        <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-3">
          <Icon icon="lucide:bell-off" width="26" />
        </div>
        <h3 class="text-sm font-semibold text-gray-800">You're all caught up!</h3>
        <p class="text-xs text-gray-500 max-w-xs mt-1">
          {{ searchQuery ? 'No notifications matching your search filter.' : 'No notifications in this category right now.' }}
        </p>
        <button 
          v-if="searchQuery" 
          @click="searchQuery = ''" 
          class="mt-3 text-xs text-primary font-medium hover:underline"
        >
          Clear search filter
        </button>
      </div>

      <!-- Grouped by Date -->
      <div v-else v-for="(group, dateKey) in groupedByDate" :key="dateKey">
        <div class="sticky top-0 bg-gray-50/90 backdrop-blur-xs px-4 py-1.5 text-[11px] font-semibold text-gray-500 tracking-wider uppercase border-y border-gray-100/80 flex items-center justify-between">
          <span>{{ dateKey }}</span>
          <span class="text-[10px] text-gray-400 font-normal">{{ group.length }} items</span>
        </div>

        <div 
          v-for="item in group" 
          :key="item.id"
          class="group relative flex items-start gap-3 p-3.5 transition-all cursor-pointer border-b border-gray-50"
          :class="!item.read_at ? 'bg-blue-50/40 hover:bg-blue-50/70 border-l-[3.5px] border-l-primary' : 'bg-white hover:bg-gray-50/80 border-l-[3.5px] border-l-transparent'"
          @click="handleNotificationClick(item)"
        >
          <!-- User / System Avatar with Initials Fallback -->
          <div class="relative flex-shrink-0 mt-0.5">
            <img 
              v-if="item.actorImage" 
              :src="item.actorImage" 
              class="w-9 h-9 rounded-full object-cover border border-gray-200" 
              alt="Avatar"
              @error="item.actorImage = null"
            />
            <div 
              v-else 
              class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold uppercase shadow-xs"
              :class="getInitialsColor(item.actorName)"
            >
              {{ getInitials(item.actorName) }}
            </div>

            <!-- Mini Event Icon Badge -->
            <div 
              class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-[10px] text-white shadow-xs"
              :class="getEventBadgeBg(item.category)"
            >
              <Icon :icon="getEventIcon(item.category)" width="10" />
            </div>
          </div>

          <!-- Notification Details -->
          <div class="flex-1 min-w-0 pr-12">
            <!-- Header Line: Title & Relative Time -->
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs font-semibold text-gray-900 truncate">
                {{ item.actorName }}
              </span>
              <span class="text-[11px] text-gray-400 whitespace-nowrap flex-shrink-0">
                {{ formatRelativeTime(item.created_at) }}
              </span>
            </div>

            <!-- Main Message Text -->
            <p 
              class="text-xs text-gray-700 mt-0.5 leading-snug line-clamp-2"
              :class="!item.read_at ? 'font-medium text-gray-900' : 'text-gray-600'"
            >
              {{ item.message }}
            </p>

            <!-- Contextual Task / Reference Tag -->
            <div v-if="item.trackingNumber || item.taskTitle" class="flex flex-wrap items-center gap-1.5 mt-1.5">
              <span v-if="item.trackingNumber" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200/60 font-mono">
                <Icon icon="lucide:hash" width="10" />
                {{ item.trackingNumber }}
              </span>
              <span v-if="item.taskTitle" class="text-[11px] text-gray-500 truncate max-w-[220px]">
                {{ item.taskTitle }}
              </span>
            </div>

            <!-- Discussion / Comment Bubble Preview -->
            <div 
              v-if="item.commentText" 
              class="mt-2 text-xs text-gray-700 bg-white/90 border border-gray-200 rounded-lg p-2.5 shadow-xs font-normal leading-relaxed"
            >
              <span class="text-gray-400 mr-1 italic">“</span>{{ item.commentText }}<span class="text-gray-400 ml-1 italic">”</span>
            </div>
          </div>

          <!-- Hover Action Toolbar -->
          <div class="absolute right-3 top-3.5 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 bg-white/90 backdrop-blur-xs px-1 py-0.5 rounded-md shadow-xs border border-gray-200">
            <button 
              @click.stop="toggleReadStatus(item)" 
              class="p-1 rounded text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
              :title="item.read_at ? 'Mark as unread' : 'Mark as read'"
            >
              <Icon :icon="item.read_at ? 'lucide:mail' : 'lucide:check'" width="14" />
            </button>
            <button 
              @click.stop="dismissNotification(item)" 
              class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
              title="Dismiss notification"
            >
              <Icon icon="lucide:trash-2" width="14" />
            </button>
          </div>

          <!-- Unread Dot Indicator (Visible when not hovered) -->
          <div 
            v-if="!item.read_at" 
            class="absolute right-3.5 top-5 w-2 h-2 rounded-full bg-primary shadow-xs group-hover:opacity-0 transition-opacity"
            title="Unread notification"
          ></div>
        </div>
      </div>
    </div>

    <!-- Footer Bar -->
    <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
      <span>{{ allNormalizedList.length }} total notifications</span>
      <div class="flex items-center gap-3">
        <button 
          v-if="hasReadItems"
          @click="clearAllRead" 
          class="text-gray-500 hover:text-gray-800 transition-colors"
        >
          Clear read
        </button>
        <Link 
          href="/dashboard/notifications" 
          @click="closeNotification"
          class="text-primary hover:underline font-medium flex items-center gap-1"
        >
          <span>View all in Hub</span>
          <Icon icon="lucide:arrow-right" width="12" />
        </Link>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, onMounted } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import { useStore } from "vuex";
import moment from "moment";
import axios from "axios";

const props = defineProps(["notifications", "total_task_notifications"]);
const emit = defineEmits(["close"]);
const page = usePage();
const store = useStore();

const activeTab = ref("all");
const searchQuery = ref("");
const isMarkingAll = ref(false);
const isMuted = ref(false);

onMounted(() => {
  isMuted.value = localStorage.getItem("edms_chime_muted") === "true";
});

const toggleSound = () => {
  isMuted.value = !isMuted.value;
  localStorage.setItem("edms_chime_muted", isMuted.value ? "true" : "false");
};

const closeNotification = () => {
  emit("close");
};

// Normalize and flatten all notification sources
const allNormalizedList = computed(() => {
  const storeFlat = store.getters.getAllNotifications;
  if (Array.isArray(storeFlat) && storeFlat.length > 0) {
    return storeFlat.map(normalizeNotification);
  }

  // Fallback to grouped tree if flat array is missing
  let items = [];
  if (props.notifications && props.notifications.length > 0) {
    const rawTree = props.notifications[0];
    for (let notifType in rawTree) {
      const dateGroups = rawTree[notifType];
      if (typeof dateGroups === 'object' && dateGroups !== null) {
        for (let dateKey in dateGroups) {
          if (Array.isArray(dateGroups[dateKey])) {
            items = items.concat(dateGroups[dateKey]);
          }
        }
      }
    }
  }

  return items.map(normalizeNotification);
});

// Helper to normalize different notification shapes into a standard object
function normalizeNotification(notif) {
  const data = notif.data || {};
  let category = "task";
  let actorName = "System";
  let actorImage = null;
  let message = data.message || "Notification received";
  let trackingNumber = data.tracking_number || data.tracking_id || "";
  let taskTitle = data.task_title || "";
  let commentText = "";
  let targetUrl = data.action_url || "";

  // 1. TaskChangedNotification or generic TaskNotification
  if (notif.type?.includes("TaskChangedNotification") || notif.type?.includes("TaskNotification")) {
    category = "task";
    if (data.user) {
      actorName = `${data.user.first_name || ""} ${data.user.last_name || ""}`.trim() || "Staff";
      if (data.user.profile_image) {
        actorImage = (page.props.base || "") + "/" + data.user.profile_image;
      }
    }
    if (data.task_id && !targetUrl) {
      targetUrl = `/dashboard/view-task/${data.task_id}`;
    }
  }

  // 2. WorkflowEventNotification
  else if (notif.type?.includes("WorkflowEventNotification")) {
    category = "workflow";
    if (data.actor) {
      actorName = `${data.actor.first_name || ""} ${data.actor.last_name || ""}`.trim() || "Workflow";
      if (data.actor.profile_image) {
        actorImage = (page.props.base || "") + "/" + data.actor.profile_image;
      }
    }
    if (data.event_type) {
      category = data.event_type;
    }
    if (!targetUrl && data.action_url) {
      targetUrl = data.action_url;
    }
  }

  // 3. PaymentAssignedNotification
  else if (notif.type?.includes("PaymentAssignedNotification")) {
    category = "payment";
    actorName = "Finance Department";
    if (data.comment?.[0]?.payment_id && !targetUrl) {
      targetUrl = `/finance-and-accounts/view-payment/${data.comment[0].payment_id}`;
    }
  }

  // 4. CommentNotification
  else if (notif.type?.includes("CommentNotification")) {
    category = "comment";
    if (data.user) {
      actorName = `${data.user.first_name || ""} ${data.user.last_name || ""}`.trim() || "Staff Member";
      if (data.user.profile_image) {
        actorImage = (page.props.base || "") + "/" + data.user.profile_image;
      }
    }
    commentText = typeof data.comment === "string" ? data.comment : (data.comment?.comment || data.comment_obj?.comments || "");
    const taskId = data.task_id || data.comment_obj?.task_assignment?.task_id || data.comment?.task_id;
    if (taskId && !targetUrl) {
      targetUrl = `/dashboard/view-task/${taskId}`;
    }
  }

  // 5. RegistryNotification
  else if (notif.type?.includes("RegistryNotification")) {
    category = "registry";
    actorName = "Registry Office";
  }

  // 6. ChatMessageNotification
  else if (notif.type?.includes("ChatMessageNotification") || notif.type?.includes("ChatNotification") || data.is_chat) {
    category = "chat";
    if (data.sender) {
      actorName = `${data.sender.first_name || ""} ${data.sender.last_name || ""}`.trim() || "Colleague";
      if (data.sender.profile_image) {
        actorImage = (page.props.base || "") + "/" + data.sender.profile_image;
      }
    }
    commentText = data.message || "Sent you a chat message";
    message = data.group_name ? `Message in ${data.group_name}` : `Direct message from ${actorName}`;
    targetUrl = data.action_url || `/dashboard/chat?user_id=${data.sender_id || (data.sender?.id || '')}`;
  }

  return {
    id: notif.id,
    type: notif.type,
    read_at: notif.read_at,
    created_at: notif.created_at,
    category,
    actorName,
    actorImage,
    message,
    trackingNumber,
    taskTitle,
    commentText,
    targetUrl,
    raw: notif,
  };
}

// Compute counts for tabs
const unreadCount = computed(() => {
  return allNormalizedList.value.filter(n => !n.read_at).length;
});

const workflowCount = computed(() => {
  return allNormalizedList.value.filter(n => !n.read_at && (n.category === 'workflow' || n.category === 'task' || n.category === 'payment')).length;
});

const commentsCount = computed(() => {
  return allNormalizedList.value.filter(n => !n.read_at && n.category === 'comment').length;
});

const chatCount = computed(() => {
  return allNormalizedList.value.filter(n => !n.read_at && n.category === 'chat').length;
});

const hasReadItems = computed(() => {
  return allNormalizedList.value.some(n => !!n.read_at);
});

const tabs = computed(() => [
  { id: "all", label: "All", icon: "lucide:layers", count: unreadCount.value },
  { id: "unread", label: "Unread", icon: "lucide:inbox", count: unreadCount.value },
  { id: "chat", label: "Chat Messages", icon: "solar:chat-round-dots-bold-duotone", count: chatCount.value },
  { id: "workflow", label: "Workflows & Tasks", icon: "lucide:git-pull-request", count: workflowCount.value },
  { id: "comments", label: "Comments", icon: "lucide:message-square", count: commentsCount.value },
]);

// Filtered notifications based on active tab and search query
const filteredNotifications = computed(() => {
  let list = allNormalizedList.value;

  // Filter by tab
  if (activeTab.value === "unread") {
    list = list.filter(item => !item.read_at);
  } else if (activeTab.value === "workflow") {
    list = list.filter(item => ["workflow", "task", "payment", "approved", "completed", "assigned", "delegation", "returned", "breach"].includes(item.category));
  } else if (activeTab.value === "chat") {
    list = list.filter(item => item.category === "chat");
  } else if (activeTab.value === "comments") {
    list = list.filter(item => item.category === "comment");
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(item => {
      return (
        item.actorName.toLowerCase().includes(q) ||
        item.message.toLowerCase().includes(q) ||
        item.trackingNumber.toLowerCase().includes(q) ||
        item.taskTitle.toLowerCase().includes(q) ||
        item.commentText.toLowerCase().includes(q)
      );
    });
  }

  return list;
});

// Group filtered notifications by date classification ("Today", "Yesterday", "Older")
const groupedByDate = computed(() => {
  const groups = {};
  filteredNotifications.value.forEach(item => {
    const dateLabel = classifyDate(item.created_at);
    if (!groups[dateLabel]) {
      groups[dateLabel] = [];
    }
    groups[dateLabel].push(item);
  });
  return groups;
});

function classifyDate(dateString) {
  if (!dateString) return "Earlier";
  const date = moment(dateString);
  if (date.isSame(moment(), "day")) return "Today";
  if (date.isSame(moment().subtract(1, "days"), "day")) return "Yesterday";
  if (date.isAfter(moment().subtract(7, "days"))) return date.format("dddd"); // e.g. "Monday"
  return date.format("DD MMMM YYYY");
}

function formatRelativeTime(dateString) {
  if (!dateString) return "";
  return moment(dateString).fromNow();
}

// User Initials & Color Generation
function getInitials(name) {
  if (!name) return "ED";
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
}

function getInitialsColor(name) {
  const colors = [
    "bg-blue-100 text-blue-700",
    "bg-indigo-100 text-indigo-700",
    "bg-purple-100 text-purple-700",
    "bg-pink-100 text-pink-700",
    "bg-teal-100 text-teal-700",
    "bg-emerald-100 text-emerald-700",
    "bg-amber-100 text-amber-700",
    "bg-cyan-100 text-cyan-700"
  ];
  let hash = 0;
  for (let i = 0; i < (name || "").length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}

// Event Badge Styling
function getEventBadgeBg(category) {
  switch (category) {
    case "approved":
    case "completed":
      return "bg-emerald-500";
    case "returned":
    case "warning":
      return "bg-amber-500";
    case "breach":
    case "escalation":
      return "bg-rose-500";
    case "chat":
      return "bg-indigo-600";
    case "comment":
      return "bg-purple-500";
    case "payment":
      return "bg-teal-500";
    default:
      return "bg-blue-500";
  }
}

function getEventIcon(category) {
  switch (category) {
    case "approved":
    case "completed":
      return "lucide:check-circle";
    case "returned":
    case "warning":
      return "lucide:alert-circle";
    case "breach":
    case "escalation":
      return "lucide:alert-triangle";
    case "chat":
      return "solar:chat-round-dots-bold";
    case "comment":
      return "lucide:message-square";
    case "payment":
      return "lucide:credit-card";
    default:
      return "lucide:arrow-right-left";
  }
}

// User Actions
const handleNotificationClick = async (item) => {
  // Mark as read immediately
  if (!item.read_at) {
    item.read_at = new Date().toISOString();
    axios.post("/mark-notification-as-read", { id: item.id }).catch(() => {});
    store.dispatch("fetchNotifications");
  }

  closeNotification();

  if (item.targetUrl) {
    router.visit(item.targetUrl);
  }
};

const toggleReadStatus = async (item) => {
  if (item.read_at) {
    item.read_at = null;
    await axios.post("/mark-notification-as-unread", { id: item.id });
  } else {
    item.read_at = new Date().toISOString();
    await axios.post("/mark-notification-as-read", { id: item.id });
  }
  store.dispatch("fetchNotifications");
};

const dismissNotification = async (item) => {
  // Optimistically remove from state
  const rawList = store.state.all_notifications;
  const idx = rawList.findIndex(n => n.id === item.id);
  if (idx !== -1) {
    rawList.splice(idx, 1);
  }
  await axios.post("/delete-notification", { id: item.id });
  store.dispatch("fetchNotifications");
};

const markAllAsRead = async () => {
  isMarkingAll.value = true;
  try {
    // Optimistically mark all in state
    allNormalizedList.value.forEach(item => {
      item.read_at = new Date().toISOString();
    });
    await axios.post("/mark-notification-as-read", { all: true });
    await store.dispatch("fetchNotifications");
  } catch (err) {
    console.error("Failed to mark all as read:", err);
  } finally {
    isMarkingAll.value = false;
  }
};

const clearAllRead = async () => {
  try {
    await axios.post("/delete-notification", { clear_read: true });
    await store.dispatch("fetchNotifications");
  } catch (err) {
    console.error("Failed to clear read notifications:", err);
  }
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
