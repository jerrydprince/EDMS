<template>
  <main class="px-6 py-6 font-sans min-h-screen bg-gray-50/50">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
          <Link href="/dashboard" class="hover:text-primary transition-colors">Dashboard</Link>
          <Icon icon="lucide:chevron-right" width="12" />
          <span class="text-gray-800 font-medium">Notification Center</span>
        </div>
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notification Center</h1>
          <span 
            v-if="unreadCount > 0" 
            class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary text-white shadow-xs"
          >
            {{ unreadCount }} unread
          </span>
        </div>
        <p class="text-xs text-gray-500 mt-1">
          Review, triage, and manage your workflow alerts, document activities, and discussion notifications.
        </p>
      </div>

      <!-- Header Actions -->
      <div class="flex flex-wrap items-center gap-2">
        <button 
          @click="toggleSound" 
          :title="isMuted ? 'Notification chimes are muted' : 'Notification chimes are active'"
          class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-all shadow-2xs"
        >
          <Icon :icon="isMuted ? 'lucide:volume-x' : 'lucide:volume-2'" width="15" :class="isMuted ? 'text-gray-400' : 'text-primary'" />
          <span>{{ isMuted ? 'Chime Muted' : 'Chime On' }}</span>
        </button>

        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead" 
          :disabled="isMarkingAll"
          class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg bg-primary text-white hover:bg-primary/90 transition-all shadow-xs disabled:opacity-50"
        >
          <Icon :icon="isMarkingAll ? 'line-md:loading-loop' : 'lucide:check-check'" width="15" />
          <span>Mark all as read</span>
        </button>

        <button 
          v-if="hasReadItems"
          @click="clearAllRead" 
          class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-red-600 hover:border-red-200 hover:bg-red-50/50 transition-all shadow-2xs"
        >
          <Icon icon="lucide:trash-2" width="14" />
          <span>Clear read</span>
        </button>
      </div>
    </div>

    <!-- Main Content Container with Sidebar Filters & Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Left Filters Sidebar -->
      <div class="lg:col-span-1 flex flex-col gap-5">
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 gap-3">
          <div class="p-3.5 bg-white border border-gray-200/80 rounded-xl shadow-2xs">
            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Total</span>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ allNormalizedList.length }}</div>
          </div>
          <div class="p-3.5 bg-white border border-gray-200/80 rounded-xl shadow-2xs">
            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Unread</span>
            <div class="text-xl font-bold text-primary mt-0.5">{{ unreadCount }}</div>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-gray-200/80 rounded-xl p-4 shadow-2xs flex flex-col gap-4">
          <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Categories</h2>
          
          <nav class="flex flex-col gap-1">
            <button 
              v-for="cat in categoryTabs" 
              :key="cat.id"
              @click="selectedCategory = cat.id"
              class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all text-left"
              :class="selectedCategory === cat.id ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-100/70 hover:text-gray-900'"
            >
              <div class="flex items-center gap-2.5">
                <Icon :icon="cat.icon" width="16" />
                <span>{{ cat.label }}</span>
              </div>
              <span 
                v-if="cat.count > 0" 
                class="px-2 py-0.2 rounded-full text-[10px] font-semibold"
                :class="selectedCategory === cat.id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'"
              >
                {{ cat.count }}
              </span>
            </button>
          </nav>

          <hr class="border-gray-100" />

          <!-- Read / Unread Status Filter -->
          <div>
            <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">Status</h2>
            <div class="flex flex-col gap-1">
              <button 
                v-for="st in statusOptions" 
                :key="st.id"
                @click="selectedStatus = st.id"
                class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium transition-all text-left"
                :class="selectedStatus === st.id ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
              >
                <span>{{ st.label }}</span>
                <span class="text-[11px] text-gray-400">{{ st.count }}</span>
              </button>
            </div>
          </div>

          <hr class="border-gray-100" />

          <!-- Timeframe Filter -->
          <div>
            <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">Timeframe</h2>
            <div class="flex flex-col gap-1">
              <button 
                v-for="tf in timeframeOptions" 
                :key="tf.id"
                @click="selectedTimeframe = tf.id"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all text-left"
                :class="selectedTimeframe === tf.id ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
              >
                {{ tf.label }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Feed Area -->
      <div class="lg:col-span-3 flex flex-col gap-4">
        <!-- Search & Bulk Actions Bar -->
        <div class="bg-white border border-gray-200/80 rounded-xl p-3.5 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
          <!-- Search Bar -->
          <div class="relative w-full sm:w-80">
            <Icon icon="lucide:search" width="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search sender, task, or keywords..." 
              class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50/80 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary focus:bg-white transition-all"
            />
            <button 
              v-if="searchQuery" 
              @click="searchQuery = ''" 
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <Icon icon="lucide:x" width="13" />
            </button>
          </div>

          <!-- Bulk Selection Toolbar (When items are checked) -->
          <div v-if="selectedIds.length > 0" class="flex items-center gap-2 w-full sm:w-auto justify-end animate-in fade-in duration-150">
            <span class="text-xs font-semibold text-gray-700 mr-1">{{ selectedIds.length }} selected</span>
            <button 
              @click="bulkMarkAsRead" 
              class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
              title="Mark selected as read"
            >
              <Icon icon="lucide:check" width="13" />
              <span>Mark Read</span>
            </button>
            <button 
              @click="bulkDelete" 
              class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
              title="Delete selected"
            >
              <Icon icon="lucide:trash-2" width="13" />
              <span>Delete</span>
            </button>
            <button 
              @click="selectedIds = []" 
              class="text-xs text-gray-400 hover:text-gray-600 ml-1"
            >
              Deselect
            </button>
          </div>

          <!-- Select All Helper (When none selected) -->
          <div v-else class="flex items-center gap-2 self-start sm:self-auto text-xs text-gray-500">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input 
                type="checkbox" 
                :checked="isAllSelected" 
                @change="toggleSelectAll" 
                class="rounded border-gray-300 text-primary focus:ring-primary w-3.5 h-3.5"
              />
              <span>Select all in view</span>
            </label>
          </div>
        </div>

        <!-- Feed List Card -->
        <div class="bg-white border border-gray-200/80 rounded-xl shadow-2xs overflow-hidden divide-y divide-gray-100">
          <!-- Empty State -->
          <div v-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-20 px-6 text-center">
            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-4">
              <Icon icon="lucide:inbox" width="30" />
            </div>
            <h3 class="text-base font-semibold text-gray-900">No notifications found</h3>
            <p class="text-xs text-gray-500 max-w-sm mt-1">
              {{ searchQuery ? 'Try adjusting your search terms or clearing the active filters.' : 'You have no notifications matching this criteria.' }}
            </p>
            <button 
              v-if="searchQuery || selectedCategory !== 'all' || selectedStatus !== 'all' || selectedTimeframe !== 'all'" 
              @click="resetFilters" 
              class="mt-4 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary hover:bg-primary/20 transition-all"
            >
              Reset all filters
            </button>
          </div>

          <!-- Notifications Stream -->
          <div 
            v-else 
            v-for="item in filteredNotifications" 
            :key="item.id"
            class="group relative flex items-start gap-4 p-4 transition-all hover:bg-gray-50/70 border-l-4"
            :class="!item.read_at ? 'bg-blue-50/30 border-l-primary' : 'bg-white border-l-transparent'"
          >
            <!-- Checkbox for multi-select -->
            <div class="mt-1 flex-shrink-0">
              <input 
                type="checkbox" 
                :value="item.id" 
                v-model="selectedIds"
                class="rounded border-gray-300 text-primary focus:ring-primary w-3.5 h-3.5 cursor-pointer"
              />
            </div>

            <!-- Avatar / Initials -->
            <div class="relative flex-shrink-0">
              <img 
                v-if="item.actorImage" 
                :src="item.actorImage" 
                class="w-10 h-10 rounded-full object-cover border border-gray-200" 
                alt="Avatar"
                @error="item.actorImage = null"
              />
              <div 
                v-else 
                class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-semibold uppercase shadow-2xs"
                :class="getInitialsColor(item.actorName)"
              >
                {{ getInitials(item.actorName) }}
              </div>

              <!-- Mini Event Icon Badge -->
              <div 
                class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-[10px] text-white shadow-2xs"
                :class="getEventBadgeBg(item.category)"
              >
                <Icon :icon="getEventIcon(item.category)" width="10" />
              </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 min-w-0 pr-24">
              <!-- Sender & Time -->
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-900">{{ item.actorName }}</span>
                <span class="text-[10px] text-gray-400">•</span>
                <span class="text-[11px] text-gray-400">{{ formatRelativeTime(item.created_at) }}</span>
                <span 
                  v-if="!item.read_at" 
                  class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-primary/10 text-primary"
                >
                  New
                </span>
              </div>

              <!-- Message -->
              <p 
                class="text-xs text-gray-800 mt-1 leading-relaxed"
                :class="!item.read_at ? 'font-medium' : 'text-gray-600'"
              >
                {{ item.message }}
              </p>

              <!-- Context Pill / Tracking ID -->
              <div v-if="item.trackingNumber || item.taskTitle" class="flex flex-wrap items-center gap-2 mt-2">
                <span v-if="item.trackingNumber" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 font-mono border border-gray-200">
                  <Icon icon="lucide:hash" width="11" />
                  {{ item.trackingNumber }}
                </span>
                <span v-if="item.taskTitle" class="text-xs font-medium text-gray-600 truncate max-w-sm">
                  {{ item.taskTitle }}
                </span>
              </div>

              <!-- Discussion Comment Bubble Preview -->
              <div 
                v-if="item.commentText" 
                class="mt-2.5 text-xs text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3 leading-relaxed max-w-2xl"
              >
                <span class="text-gray-400 mr-1 italic">“</span>{{ item.commentText }}<span class="text-gray-400 ml-1 italic">”</span>
              </div>
            </div>

            <!-- Direct Action & Toolbars -->
            <div class="absolute right-4 top-4 flex items-center gap-2">
              <!-- Action Button: Open Task / View -->
              <button 
                v-if="item.targetUrl" 
                @click="navigateAction(item)"
                class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-primary hover:text-white transition-all shadow-2xs"
              >
                <span>View</span>
                <Icon icon="lucide:arrow-right" width="12" />
              </button>

              <!-- Quick Read Toggle -->
              <button 
                @click.stop="toggleReadStatus(item)" 
                class="p-1.5 rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors"
                :title="item.read_at ? 'Mark as unread' : 'Mark as read'"
              >
                <Icon :icon="item.read_at ? 'lucide:mail' : 'lucide:check'" width="15" />
              </button>

              <!-- Delete Button -->
              <button 
                @click.stop="dismissNotification(item)" 
                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                title="Dismiss notification"
              >
                <Icon icon="lucide:trash-2" width="15" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { useStore } from "vuex";
import moment from "moment";
import axios from "axios";

const page = usePage();
const store = useStore();

const selectedCategory = ref("all");
const selectedStatus = ref("all");
const selectedTimeframe = ref("all");
const searchQuery = ref("");
const selectedIds = ref([]);
const isMarkingAll = ref(false);
const isMuted = ref(false);

onMounted(() => {
  isMuted.value = localStorage.getItem("edms_chime_muted") === "true";
  store.dispatch("fetchNotifications");
});

const toggleSound = () => {
  isMuted.value = !isMuted.value;
  localStorage.setItem("edms_chime_muted", isMuted.value ? "true" : "false");
};

// Normalize notifications into standard shape
const allNormalizedList = computed(() => {
  const storeFlat = store.getters.getAllNotifications;
  if (Array.isArray(storeFlat) && storeFlat.length > 0) {
    return storeFlat.map(normalizeNotification);
  }
  return [];
});

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
  } else if (notif.type?.includes("WorkflowEventNotification")) {
    category = data.event_type || "workflow";
    if (data.actor) {
      actorName = `${data.actor.first_name || ""} ${data.actor.last_name || ""}`.trim() || "Workflow";
      if (data.actor.profile_image) {
        actorImage = (page.props.base || "") + "/" + data.actor.profile_image;
      }
    }
    if (!targetUrl && data.action_url) {
      targetUrl = data.action_url;
    }
  } else if (notif.type?.includes("PaymentAssignedNotification")) {
    category = "payment";
    actorName = "Finance Department";
    if (data.comment?.[0]?.payment_id && !targetUrl) {
      targetUrl = `/finance-and-accounts/view-payment/${data.comment[0].payment_id}`;
    }
  } else if (notif.type?.includes("CommentNotification")) {
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
  } else if (notif.type?.includes("RegistryNotification")) {
    category = "registry";
    actorName = "Registry Office";
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
  };
}

const unreadCount = computed(() => {
  return allNormalizedList.value.filter(n => !n.read_at).length;
});

const hasReadItems = computed(() => {
  return allNormalizedList.value.some(n => !!n.read_at);
});

// Category Tabs Definition
const categoryTabs = computed(() => [
  { id: "all", label: "All Notifications", icon: "lucide:layers", count: unreadCount.value },
  { id: "workflows", label: "Workflows & Approvals", icon: "lucide:git-pull-request", count: allNormalizedList.value.filter(n => !n.read_at && ["workflow", "approved", "completed", "returned", "breach"].includes(n.category)).length },
  { id: "tasks", label: "Task Assignments", icon: "lucide:check-square", count: allNormalizedList.value.filter(n => !n.read_at && n.category === "task").length },
  { id: "comments", label: "Discussion Comments", icon: "lucide:message-square", count: allNormalizedList.value.filter(n => !n.read_at && n.category === "comment").length },
  { id: "payments", label: "Finance & Accounts", icon: "lucide:credit-card", count: allNormalizedList.value.filter(n => !n.read_at && n.category === "payment").length },
]);

// Status Options
const statusOptions = computed(() => [
  { id: "all", label: "All Items", count: allNormalizedList.value.length },
  { id: "unread", label: "Unread Only", count: unreadCount.value },
  { id: "read", label: "Read Only", count: allNormalizedList.value.length - unreadCount.value },
]);

// Timeframe Options
const timeframeOptions = [
  { id: "all", label: "All Time" },
  { id: "today", label: "Today" },
  { id: "week", label: "Past 7 Days" },
  { id: "older", label: "Older" },
];

// Main Filtering Logic
const filteredNotifications = computed(() => {
  let list = allNormalizedList.value;

  // Category filter
  if (selectedCategory.value === "workflows") {
    list = list.filter(n => ["workflow", "approved", "completed", "returned", "breach"].includes(n.category));
  } else if (selectedCategory.value === "tasks") {
    list = list.filter(n => n.category === "task");
  } else if (selectedCategory.value === "comments") {
    list = list.filter(n => n.category === "comment");
  } else if (selectedCategory.value === "payments") {
    list = list.filter(n => n.category === "payment");
  }

  // Status filter
  if (selectedStatus.value === "unread") {
    list = list.filter(n => !n.read_at);
  } else if (selectedStatus.value === "read") {
    list = list.filter(n => !!n.read_at);
  }

  // Timeframe filter
  if (selectedTimeframe.value === "today") {
    list = list.filter(n => moment(n.created_at).isSame(moment(), "day"));
  } else if (selectedTimeframe.value === "week") {
    list = list.filter(n => moment(n.created_at).isAfter(moment().subtract(7, "days")));
  } else if (selectedTimeframe.value === "older") {
    list = list.filter(n => moment(n.created_at).isBefore(moment().subtract(7, "days")));
  }

  // Search filter
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

// Selection helpers
const isAllSelected = computed(() => {
  return filteredNotifications.value.length > 0 && selectedIds.value.length === filteredNotifications.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = filteredNotifications.value.map(n => n.id);
  }
};

const resetFilters = () => {
  selectedCategory.value = "all";
  selectedStatus.value = "all";
  selectedTimeframe.value = "all";
  searchQuery.value = "";
};

function formatRelativeTime(dateString) {
  if (!dateString) return "";
  return moment(dateString).fromNow();
}

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
    case "comment":
      return "lucide:message-square";
    case "payment":
      return "lucide:credit-card";
    default:
      return "lucide:arrow-right-left";
  }
}

// User Actions
const navigateAction = (item) => {
  if (!item.read_at) {
    item.read_at = new Date().toISOString();
    axios.post("/mark-notification-as-read", { id: item.id }).catch(() => {});
    store.dispatch("fetchNotifications");
  }
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
  await axios.post("/delete-notification", { id: item.id });
  store.dispatch("fetchNotifications");
};

const bulkMarkAsRead = async () => {
  for (const id of selectedIds.value) {
    await axios.post("/mark-notification-as-read", { id });
  }
  selectedIds.value = [];
  store.dispatch("fetchNotifications");
};

const bulkDelete = async () => {
  for (const id of selectedIds.value) {
    await axios.post("/delete-notification", { id });
  }
  selectedIds.value = [];
  store.dispatch("fetchNotifications");
};

const markAllAsRead = async () => {
  isMarkingAll.value = true;
  try {
    await axios.post("/mark-notification-as-read", { all: true });
    await store.dispatch("fetchNotifications");
  } finally {
    isMarkingAll.value = false;
  }
};

const clearAllRead = async () => {
  await axios.post("/delete-notification", { clear_read: true });
  await store.dispatch("fetchNotifications");
};
</script>
