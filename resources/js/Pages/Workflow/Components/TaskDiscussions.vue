<template>
  <div class="flex h-full min-h-0 bg-white w-full border-t overflow-hidden">
    <!-- Left Sidebar: List of Conversations -->
    <div class="w-[32%] min-w-[220px] max-w-[340px] border-r border-gray-200 bg-gray-50 flex flex-col h-full overflow-hidden shrink-0">
      <div class="p-4 border-b border-gray-200 bg-white shrink-0 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:messages-square" class="text-primary w-5 h-5" />
          <h2 class="font-bold text-gray-800 text-base md:text-lg">Discussions</h2>
        </div>
        <div v-if="loading" class="text-xs text-primary flex items-center gap-1 font-semibold" title="Refreshing discussions...">
          <Icon icon="line-md:loading-twotone-loop" width="16" />
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-2.5 space-y-4 custom-scrollbar">
        <!-- Upline Discussions (Supervisors / Assigners) -->
        <div v-if="uplineThreads.length > 0">
          <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <Icon icon="lucide:arrow-up-right" width="13" />
            <span>Upline (Supervisor{{ uplineThreads.length > 1 ? 's' : '' }})</span>
          </div>
          
          <div class="space-y-1 mt-1">
            <div 
              v-for="thread in uplineThreads" 
              :key="thread.id"
              class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer transition-all relative border"
              :class="activeThreadId === thread.id 
                ? 'bg-primary text-white border-primary shadow-sm' 
                : 'bg-white hover:bg-gray-100/80 text-gray-700 border-gray-200/70'"
              @click="selectThread(thread)"
            >
              <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 bg-white p-0.5 border border-gray-200 shadow-2xs">
                 <img 
                   :src="getUserProfileImage(thread.user)" 
                   @error="handleImgError"
                   alt="User" 
                   class="w-full h-full object-cover rounded-full" 
                 />
              </div>
              <div class="flex flex-col flex-1 min-w-0 text-left">
                <h3 class="font-bold text-xs truncate" :class="activeThreadId === thread.id ? 'text-white' : 'text-gray-900'">
                  {{ thread.user?.first_name }} {{ thread.user?.last_name }}
                </h3>
                <p class="text-[10.5px] truncate mt-0.5" :class="activeThreadId === thread.id ? 'text-blue-100' : 'text-gray-400'">
                  {{ thread.user?.department?.title || 'Supervisor' }}
                </p>
              </div>
              
              <!-- Badge -->
              <div v-if="getCommentCountForUser(thread.user?.id) > 0" 
                   class="min-w-[18px] h-[18px] px-1 shrink-0 rounded-full flex items-center justify-center text-[10px] font-bold shadow-2xs"
                   :class="activeThreadId === thread.id ? 'bg-white text-primary' : 'bg-blue-100 text-blue-700'">
                {{ getCommentCountForUser(thread.user?.id) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Downline Discussions (Subordinates / Assignees) -->
        <div v-if="downlineThreads.length > 0">
          <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <Icon icon="lucide:arrow-down-right" width="13" />
            <span>{{ isAgf ? 'Task Assignment Threads' : 'Downline (Assignees)' }}</span>
          </div>
          
          <div class="space-y-1 mt-1">
            <div 
              v-for="thread in downlineThreads" 
              :key="thread.id"
              class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer transition-all relative border"
              :class="activeThreadId === thread.id 
                ? 'bg-primary text-white border-primary shadow-sm' 
                : 'bg-white hover:bg-gray-100/80 text-gray-700 border-gray-200/70'"
              @click="selectThread(thread)"
            >
              <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 bg-white p-0.5 border border-gray-200 shadow-2xs">
                 <img 
                   :src="getUserProfileImage(thread.user)" 
                   @error="handleImgError"
                   alt="User" 
                   class="w-full h-full object-cover rounded-full" 
                 />
              </div>
              <div class="flex flex-col flex-1 min-w-0 text-left">
                <div class="flex items-center gap-1 min-w-0">
                  <h3 class="font-bold text-xs truncate" :class="activeThreadId === thread.id ? 'text-white' : 'text-gray-900'" :title="thread.user?.first_name + ' ' + thread.user?.last_name">
                    {{ thread.user?.first_name }} {{ thread.user?.last_name }}
                  </h3>
                  <span v-if="thread.status === 'under review'" class="text-[8.5px] px-1 py-0.2 rounded font-bold uppercase shrink-0" :class="activeThreadId === thread.id ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                    Review
                  </span>
                </div>
                <p class="text-[10.5px] truncate mt-0.5" :class="activeThreadId === thread.id ? 'text-blue-100' : 'text-gray-400'" :title="thread.user?.department?.title">
                  {{ thread.user?.department?.title || 'Assignee' }}
                </p>
              </div>

              <!-- Badge -->
              <div v-if="getCommentCountForUser(thread.user?.id) > 0" 
                   class="min-w-[18px] h-[18px] px-1 shrink-0 rounded-full flex items-center justify-center text-[10px] font-bold shadow-2xs"
                   :class="activeThreadId === thread.id ? 'bg-white text-primary' : 'bg-blue-100 text-blue-700'">
                {{ getCommentCountForUser(thread.user?.id) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="uplineThreads.length === 0 && downlineThreads.length === 0" class="text-center py-12 text-gray-400">
          <Icon icon="lucide:message-square-dashed" width="32" class="mx-auto mb-2 opacity-50" />
          <p class="text-xs">No active discussion channels found for this task.</p>
        </div>
      </div>
    </div>

    <!-- Right Side: Chat Window -->
    <div class="flex-1 flex flex-col h-full min-h-0 bg-white overflow-hidden">
      <div v-if="!activeThreadId" class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-300 mb-3 border border-gray-100">
          <Icon icon="lucide:messages-square" width="32" />
        </div>
        <p class="text-base font-bold text-gray-700">Select a discussion channel</p>
        <p class="text-xs text-gray-400 max-w-xs mt-1">Choose a conversation from the sidebar to view comments or send real-time feedback.</p>
      </div>

      <template v-else>
        <!-- Chat Header -->
        <div class="p-3.5 px-5 border-b border-gray-200 flex items-center justify-between shrink-0 bg-white">
          <div class="flex items-center gap-3 min-w-0">
             <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 border border-gray-200 shadow-2xs p-0.5 shrink-0">
                 <img 
                   :src="getUserProfileImage(activeUser)" 
                   @error="handleImgError"
                   alt="User" 
                   class="w-full h-full object-cover rounded-full" 
                 />
             </div>
             <div class="min-w-0">
                <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight truncate">
                  {{ activeUser?.first_name }} {{ activeUser?.last_name }}
                </h3>
                <p class="text-xs text-gray-400 truncate">{{ activeUser?.department?.title || 'Task Collaborator' }}</p>
             </div>
          </div>
          <button 
            @click="fetchDiscussions(false)" 
            class="text-xs font-semibold text-gray-500 hover:text-primary px-3 py-1.5 rounded-xl border border-gray-200 hover:border-primary/40 flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer shrink-0"
            title="Refresh comments thread"
          >
            <Icon icon="lucide:refresh-cw" :class="{ 'animate-spin': loading }" width="13" />
            <span class="hidden sm:inline">Refresh</span>
          </button>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 min-h-0 overflow-y-auto p-4 md:p-6 space-y-4 custom-scrollbar" ref="chatScrollContainer">
           <div v-if="activeComments.length === 0" class="text-center text-gray-400 my-12">
              <Icon icon="lucide:message-circle" width="36" class="mx-auto text-gray-200 mb-2" />
              <p class="text-sm font-medium text-gray-500">No comments in this thread yet.</p>
              <p class="text-xs text-gray-400 mt-0.5">Start the conversation below!</p>
           </div>
           
           <div v-for="comment in activeComments" :key="comment.id" class="flex w-full" :class="{'justify-end': isMyComment(comment)}">
              <!-- Others Message -->
              <div v-if="!isMyComment(comment)" class="flex gap-2.5 max-w-[85%]">
                <div class="w-7 h-7 rounded-full overflow-hidden shrink-0 bg-white border border-gray-200 shadow-2xs p-0.5 mt-0.5">
                   <img 
                     :src="getUserProfileImage(getCommentSender(comment))" 
                     @error="handleImgError"
                     alt="User" 
                     class="w-full h-full object-cover rounded-full"
                   />
                </div>
                <div class="flex flex-col">
                  <div class="flex items-center gap-2 mb-1 pl-1">
                    <span class="text-xs font-bold text-gray-700">{{ getCommentSender(comment)?.first_name }} {{ getCommentSender(comment)?.last_name }}</span>
                    <span class="text-[10px] text-gray-400">{{ moment(comment.created_at).format('MMM D, HH:mm') }}</span>
                  </div>
                  <div class="bg-gray-100 text-gray-800 p-3 px-4 rounded-2xl rounded-tl-xs shadow-2xs text-xs md:text-sm leading-relaxed break-words whitespace-pre-wrap">
                    <template v-for="(token, tIdx) in parseCommentTokens(comment.comments)" :key="tIdx">
                      <span v-if="token.type === 'text'">{{ token.value }}</span>
                      <button 
                        v-else-if="token.type === 'citation'" 
                        type="button" 
                        @click="jumpToCitation(token.page)" 
                        class="inline-flex items-center gap-1 px-2 py-0.5 mx-0.5 rounded-md bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold text-xs transition-colors cursor-pointer border border-blue-200 align-middle shadow-2xs"
                        title="Click to jump to this page in document viewer"
                      >
                        <Icon icon="lucide:external-link" width="11" />
                        <span>{{ token.value }}</span>
                      </button>
                    </template>
                    <!-- Attachment Display -->
                    <div v-if="comment.attachment" class="mt-2.5 pt-2 border-t border-gray-200/70 flex items-center gap-2">
                       <Icon icon="lucide:file-text" class="text-gray-500 shrink-0" width="15" />
                       <a :href="getAttachmentUrl(comment.attachment)" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold truncate flex-1 block max-w-[200px]" :title="comment.attachment_name">
                         {{ comment.attachment_name || 'Attached File' }}
                       </a>
                       <a :href="getAttachmentUrl(comment.attachment)" download class="text-gray-400 hover:text-gray-600 shrink-0">
                         <Icon icon="lucide:download" width="13" />
                       </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- My Message -->
              <div v-else class="flex flex-col items-end max-w-[85%]">
                 <div class="flex items-center gap-2 mb-1 pr-1">
                    <span class="text-[10px] text-gray-400">{{ moment(comment.created_at).format('MMM D, HH:mm') }}</span>
                    <span class="text-xs font-bold text-gray-700">You</span>
                 </div>
                 <div class="bg-primary text-white p-3 px-4 rounded-2xl rounded-tr-xs shadow-2xs text-xs md:text-sm leading-relaxed break-words whitespace-pre-wrap">
                   <template v-for="(token, tIdx) in parseCommentTokens(comment.comments)" :key="tIdx">
                     <span v-if="token.type === 'text'">{{ token.value }}</span>
                     <button 
                       v-else-if="token.type === 'citation'" 
                       type="button" 
                       @click="jumpToCitation(token.page)" 
                       class="inline-flex items-center gap-1 px-2 py-0.5 mx-0.5 rounded-md bg-white/20 hover:bg-white/30 text-white font-bold text-xs transition-colors cursor-pointer border border-white/30 align-middle shadow-2xs"
                       title="Click to jump to this page in document viewer"
                     >
                       <Icon icon="lucide:external-link" width="11" />
                       <span>{{ token.value }}</span>
                     </button>
                   </template>
                   <!-- Attachment Display -->
                   <div v-if="comment.attachment" class="mt-2.5 pt-2 border-t border-white/20 flex items-center gap-2">
                      <Icon icon="lucide:file-text" class="text-white/80 shrink-0" width="15" />
                      <a :href="getAttachmentUrl(comment.attachment)" target="_blank" class="text-white hover:underline text-xs font-semibold truncate flex-1 block max-w-[200px]" :title="comment.attachment_name">
                        {{ comment.attachment_name || 'Attached File' }}
                      </a>
                      <a :href="getAttachmentUrl(comment.attachment)" download class="text-white/80 hover:text-white shrink-0">
                        <Icon icon="lucide:download" width="13" />
                      </a>
                   </div>
                 </div>
              </div>
           </div>
        </div>

        <!-- Chat Input Form -->
        <div class="p-3 md:p-4 border-t border-gray-200 bg-gray-50/70 shrink-0">
           <!-- Page Citation Quick Chips -->
           <div class="flex items-center gap-1.5 mb-2.5 overflow-x-auto pb-1 text-xs custom-scrollbar">
             <span class="text-[11px] font-semibold text-gray-400 flex items-center gap-1 shrink-0">
               <Icon icon="lucide:bookmark" width="12" class="text-blue-500" />
               <span>Cite Page:</span>
             </span>
             <button
               type="button"
               v-for="p in [1, 2, 3, 4, 5]"
               :key="p"
               @click="insertCitation(p)"
               class="px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 font-medium transition-colors shrink-0 cursor-pointer shadow-2xs"
             >
               + Page {{ p }}
             </button>
             <button
               type="button"
               @click="promptCitation"
               class="px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-500 hover:bg-gray-100 font-medium transition-colors shrink-0 cursor-pointer shadow-2xs"
             >
               + Other...
             </button>
           </div>

           <!-- File Attachment Preview -->
           <div v-if="selectedFile" class="mb-2 p-2 px-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs text-blue-800">
             <div class="flex items-center gap-2 truncate">
               <Icon icon="lucide:paperclip" width="14" class="text-blue-500 shrink-0" />
               <span class="font-medium truncate">{{ selectedFile.name }}</span>
             </div>
             <button @click="removeFile" class="text-gray-400 hover:text-rose-500 ml-2 cursor-pointer">
               <Icon icon="lucide:x" width="14" />
             </button>
           </div>
           
           <div class="flex items-end gap-2 bg-white p-2 rounded-2xl border border-gray-300 shadow-2xs focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all">
              <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect" />
              <button 
                type="button"
                @click="$refs.fileInput.click()" 
                class="w-9 h-9 rounded-xl text-gray-400 hover:text-primary hover:bg-gray-50 flex items-center justify-center transition-colors shrink-0 cursor-pointer"
                title="Attach Document"
              >
                <Icon icon="lucide:paperclip" width="18" />
              </button>
              
              <textarea 
                v-model="newMessage" 
                rows="1"
                placeholder="Type your comment..." 
                class="flex-1 bg-transparent border-0 outline-none resize-none py-2 px-1 text-xs md:text-sm min-h-[38px] max-h-[120px] custom-scrollbar focus:ring-0 leading-normal"
                @keydown.enter.prevent="sendMessage"
                ref="chatInput"
              ></textarea>
              
              <button 
                type="button"
                @click="sendMessage"
                class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center hover:bg-primary/90 transition-all shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shadow-2xs"
                :disabled="(!newMessage.trim() && !selectedFile) || sending"
              >
                <Icon v-if="!sending" icon="lucide:send" width="16" />
                <Icon v-else icon="line-md:loading-twotone-loop" width="16" />
              </button>
           </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';
import { playNotificationChime } from '../../../utils/audio';

const props = defineProps({
  task: Object
});

const page = usePage();
const currentUser = computed(() => page.props.user || {});

const activeThreadId = ref(null);
const activeUser = ref(null);
const activeAssignment = ref(null);

const newMessage = ref('');
const localComments = ref([]);
const loading = ref(false);
const sending = ref(false);

const liveDiscussionsData = ref(null);
const chatScrollContainer = ref(null);
const chatInput = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);

// Get base URL for attachments
const getAttachmentUrl = (path) => {
  if (!path) return '#';
  const base = page.props.base || '';
  return path.startsWith('/') ? `${base}${path}` : `${base}/${path}`;
};

// Page Citation Helpers
const insertCitation = (pageNum) => {
  const citation = `[Page ${pageNum}] `;
  if (!newMessage.value.includes(citation.trim())) {
    newMessage.value = citation + newMessage.value;
  }
  if (chatInput.value) {
    chatInput.value.focus();
  }
};

const promptCitation = async () => {
  const { value: pageNum } = await Swal.fire({
    title: 'Cite Document Page',
    input: 'number',
    inputLabel: 'Enter the page number to cite in this discussion',
    inputPlaceholder: 'e.g. 4',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    inputValidator: (val) => {
      if (!val || parseInt(val) < 1) {
        return 'Please enter a valid page number';
      }
    }
  });

  if (pageNum) {
    insertCitation(parseInt(pageNum));
  }
};

const parseCommentTokens = (text) => {
  if (!text) return [];
  const regex = /(\[Page\s+\d+\]|Page\s+\d+)/gi;
  const parts = [];
  let lastIndex = 0;
  let match;

  while ((match = regex.exec(text)) !== null) {
    if (match.index > lastIndex) {
      parts.push({ type: 'text', value: text.substring(lastIndex, match.index) });
    }
    const pageMatch = match[0].match(/\d+/);
    const pageNum = pageMatch ? parseInt(pageMatch[0]) : null;
    parts.push({
      type: 'citation',
      value: match[0],
      page: pageNum
    });
    lastIndex = regex.lastIndex;
  }

  if (lastIndex < text.length) {
    parts.push({ type: 'text', value: text.substring(lastIndex) });
  }

  return parts;
};

const jumpToCitation = (pageNum) => {
  if (!pageNum) return;
  window.dispatchEvent(new CustomEvent('jump-to-pdf-page', { detail: { page: pageNum } }));
};

// Effective assignments merging live fetched data with props
const effectiveAssignedToMe = computed(() => {
  return liveDiscussionsData.value?.assigned_to_me || props.task?.assigned_to_me || null;
});

const effectiveAssignments = computed(() => {
  return liveDiscussionsData.value?.assignments || props.task?.assignments || [];
});

const isAgf = computed(() => {
  const roles = page.props.roles || page.props.user_roles || [];
  return roles.some(r => {
    const s = String(r).toLowerCase();
    return s.includes('super user') || s.includes('accountant-general') || s.includes('super admin');
  });
});

// Upline threads: all distinct supervisors who assigned the task to me
const uplineThreads = computed(() => {
  const myId = parseInt(currentUser.value?.id);
  const list = [];
  const seenUserIds = new Set();

  // 1. Direct assignedToMe
  const me = effectiveAssignedToMe.value;
  if (me && me.assigned_by && me.assigned_by.id) {
    const assignerId = parseInt(me.assigned_by.id);
    if (assignerId !== myId) {
      seenUserIds.add(assignerId);
      list.push({
        id: 'up_' + assignerId,
        user: me.assigned_by,
        assignment: me,
        type: 'up'
      });
    }
  }

  // 2. Any assignment where assigned_to is me
  for (const a of effectiveAssignments.value) {
    const toId = parseInt(a.assigned_to?.id || a.assigned_to);
    const byUser = a.assignedBy || a.assigned_by;
    const byId = parseInt(byUser?.id || byUser);

    if (toId === myId && byId && byId !== myId && !seenUserIds.has(byId) && typeof byUser === 'object') {
      seenUserIds.add(byId);
      list.push({
        id: 'up_' + byId,
        user: byUser,
        assignment: a,
        type: 'up'
      });
    }
  }

  return list;
});

// Downline threads: all distinct subordinates assigned by me (or all if AGF)
const downlineThreads = computed(() => {
  const myId = parseInt(currentUser.value?.id);
  const uplineUserIds = new Set(uplineThreads.value.map(t => parseInt(t.user?.id)));
  const list = [];
  const seenUserIds = new Set();

  for (const a of effectiveAssignments.value) {
    const byId = parseInt(a.assigned_by?.id || a.assigned_by);
    const toUser = a.assignedTo || a.assigned_to;
    const toId = parseInt(toUser?.id || toUser);

    if (!toId || toId === myId) continue;

    // Filter by my downlines unless AGF
    if (!isAgf.value && byId !== myId) continue;

    // Exclude supervisors already in upline to prevent duplication
    if (uplineUserIds.has(toId)) continue;

    if (!seenUserIds.has(toId) && typeof toUser === 'object') {
      seenUserIds.add(toId);
      list.push({
        id: 'down_' + toId,
        user: toUser,
        assignment: a,
        status: a.status,
        type: 'down'
      });
    }
  }

  return list;
});

// Calculate total comment count between currentUser and a given user ID
const getCommentCountForUser = (userId) => {
  if (!userId || !currentUser.value?.id) return 0;
  const myId = parseInt(currentUser.value.id);
  const targetId = parseInt(userId);

  return localComments.value.filter(c => {
    const sentBy = parseInt(c.sent_by || c.comment_by);
    const sentTo = parseInt(c.sent_to);
    return (sentBy === myId && sentTo === targetId) || (sentBy === targetId && sentTo === myId);
  }).length;
};

// Filter comments for active conversation in strict chronological order
const activeComments = computed(() => {
  if (!activeUser.value || !currentUser.value?.id) return [];
  const myId = parseInt(currentUser.value.id);
  const otherId = parseInt(activeUser.value.id);
  const currentAssignmentId = activeAssignment.value?.id ? parseInt(activeAssignment.value.id) : null;

  return localComments.value.filter(comment => {
    const sentBy = parseInt(comment.sent_by || comment.comment_by);
    const sentTo = parseInt(comment.sent_to);
    const assignId = parseInt(comment.task_assignment_id);

    // Match conversation pair
    if ((sentBy === myId && sentTo === otherId) || (sentBy === otherId && sentTo === myId)) {
      return true;
    }
    // Or match active assignment
    if (currentAssignmentId && assignId === currentAssignmentId) {
      return true;
    }
    return false;
  }).sort((a, b) => {
    const timeA = a.created_at ? new Date(a.created_at).getTime() : 0;
    const timeB = b.created_at ? new Date(b.created_at).getTime() : 0;
    if (timeA !== timeB) return timeA - timeB;
    return (parseInt(a.id) || 0) - (parseInt(b.id) || 0);
  });
});

const selectThread = (thread) => {
  if (!thread || !thread.user) return;
  activeThreadId.value = thread.id;
  activeUser.value = thread.user;
  activeAssignment.value = thread.assignment;

  scrollToBottom(false);
  nextTick(() => {
    if (chatInput.value) chatInput.value.focus();
  });
};

const autoSelectThread = () => {
  // If already selected and valid, keep it
  if (activeThreadId.value) {
    const existsInUp = uplineThreads.value.some(t => t.id === activeThreadId.value);
    const existsInDown = downlineThreads.value.some(t => t.id === activeThreadId.value);
    if (existsInUp || existsInDown) return;
  }

  // 1. Prioritize subordinate under review
  const underReviewSub = downlineThreads.value.find(t => t.status === 'under review');
  if (underReviewSub) {
    selectThread(underReviewSub);
    return;
  }

  // 2. Prioritize thread with existing comments
  const subWithComments = downlineThreads.value.find(t => getCommentCountForUser(t.user?.id) > 0);
  if (subWithComments) {
    selectThread(subWithComments);
    return;
  }

  const uplineWithComments = uplineThreads.value.find(t => getCommentCountForUser(t.user?.id) > 0);
  if (uplineWithComments) {
    selectThread(uplineWithComments);
    return;
  }

  // 3. Default to upline
  if (uplineThreads.value.length > 0) {
    selectThread(uplineThreads.value[0]);
    return;
  }

  // 4. Default to downline
  if (downlineThreads.value.length > 0) {
    selectThread(downlineThreads.value[0]);
    return;
  }
};

const fetchDiscussions = async (forceAutoSelect = false) => {
  const taskId = props.task?.id;
  if (!taskId) return;

  loading.value = true;
  try {
    const res = await axios.get(`/dashboard/get-task-discussions/${taskId}`);
    if (res.data?.status === 'success') {
      liveDiscussionsData.value = {
        assigned_to_me: res.data.assigned_to_me,
        assignments: res.data.assignments
      };

      // Load comments chronologically
      if (Array.isArray(res.data.comments)) {
        // Merge without duplicating existing local temp comments
        const serverComments = res.data.comments;
        const tempComments = localComments.value.filter(c => String(c.id).startsWith('temp_'));
        
        // Filter out any temp comments that have been saved
        const pendingTemp = tempComments.filter(t => !serverComments.some(sc => sc.comments === t.comments && sc.sent_by === t.sent_by));
        
        localComments.value = [...serverComments, ...pendingTemp].sort((a, b) => {
          const timeA = a.created_at ? new Date(a.created_at).getTime() : 0;
          const timeB = b.created_at ? new Date(b.created_at).getTime() : 0;
          if (timeA !== timeB) return timeA - timeB;
          return (parseInt(a.id) || 0) - (parseInt(b.id) || 0);
        });
      }

      if (!activeThreadId.value || forceAutoSelect) {
        autoSelectThread();
      }
    }
  } catch (err) {
    console.error('Failed to load discussions:', err);
  } finally {
    loading.value = false;
  }
};

watch(() => props.task?.id, (newId) => {
  if (newId) {
    activeThreadId.value = null;
    activeUser.value = null;
    activeAssignment.value = null;
    liveDiscussionsData.value = null;
    localComments.value = [];
    fetchDiscussions(true);
  }
}, { immediate: true });

const scrollToBottom = (smooth = true) => {
  nextTick(() => {
    if (chatScrollContainer.value) {
      chatScrollContainer.value.scrollTo({
        top: chatScrollContainer.value.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto'
      });
      // Safety timeout for image rendering and DOM layout settling
      setTimeout(() => {
        if (chatScrollContainer.value) {
          chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight;
        }
      }, 60);
    }
  });
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
  }
};

const removeFile = () => {
  selectedFile.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const isMyComment = (comment) => {
  const myId = currentUser.value?.id;
  if (!myId) return false;
  return parseInt(comment.sent_by) === parseInt(myId) || parseInt(comment.comment_by) === parseInt(myId);
};

const getCommentSender = (comment) => {
  return comment.sent_by_user 
    || comment.sentByUser 
    || comment.sender 
    || comment.user 
    || (comment.sent_by === activeUser.value?.id ? activeUser.value : null) 
    || activeUser.value;
};

const getUserProfileImage = (user) => {
  if (user && user.profile_image) {
    let cleanImg = user.profile_image.startsWith('/') ? user.profile_image.substring(1) : user.profile_image;
    cleanImg = encodeURI(cleanImg);
    return `${page.props.base || ''}/${cleanImg}`;
  }
  return `${page.props.base || ''}/images/user.png`;
};

const handleImgError = (event) => {
  event.target.src = `${page.props.base || ''}/images/user.png`;
};

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedFile.value) || sending.value) return;
  if (!activeUser.value?.id) return;
  
  sending.value = true;
  
  const comment_to = activeUser.value.id;
  const assignmentId = activeAssignment.value?.id || null;
  const messageToSend = newMessage.value.trim();
  const fileToSend = selectedFile.value;

  // Optimistic preview comment appended chronologically to the end
  const tempId = 'temp_' + Date.now();
  const tempComment = {
    id: tempId,
    task_assignment_id: assignmentId,
    sent_by: currentUser.value.id,
    comment_by: currentUser.value.id,
    sent_to: comment_to,
    comments: messageToSend,
    attachment: fileToSend ? URL.createObjectURL(fileToSend) : null,
    attachment_name: fileToSend ? fileToSend.name : null,
    created_at: new Date().toISOString(),
    sent_by_user: currentUser.value
  };

  localComments.value.push(tempComment);
  scrollToBottom(true);
  
  // Clear inputs immediately for smooth UX
  newMessage.value = '';
  removeFile();

  try {
    const formData = new FormData();
    formData.append('task_id', props.task.id);
    formData.append('comment', messageToSend);
    formData.append('comment_to', comment_to);
    if (assignmentId) {
      formData.append('task_assignment_id', assignmentId);
    }
    
    if (fileToSend) {
      formData.append('attachment', fileToSend);
    }

    const res = await axios.post('/dashboard/task-comment', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (res.data?.comment) {
      // Replace optimistic temp comment with real saved comment
      const idx = localComments.value.findIndex(c => c.id === tempId);
      if (idx !== -1) {
        localComments.value[idx] = res.data.comment;
      }
    }

    scrollToBottom(true);
  } catch (error) {
    console.error('Error sending comment:', error);
    localComments.value = localComments.value.filter(c => c.id !== tempId);
    alert('Failed to send comment. Please try again.');
  } finally {
    sending.value = false;
  }
};

const handleIncomingComment = (notification) => {
  const commentObj = notification.comment_obj;
  if (!commentObj || !commentObj.id) return;

  // Strict task isolation
  if (notification.task_id && props.task?.id && parseInt(notification.task_id) !== parseInt(props.task.id)) {
    return;
  }

  // Deduplicate
  if (localComments.value.some(c => c.id === commentObj.id)) {
    return;
  }

  // If matches an optimistic temp comment by same content, replace it
  const tempIdx = localComments.value.findIndex(c => String(c.id).startsWith('temp_') && c.comments === commentObj.comments && parseInt(c.sent_by) === parseInt(commentObj.sent_by));
  if (tempIdx !== -1) {
    localComments.value[tempIdx] = commentObj;
  } else {
    localComments.value.push(commentObj);
  }

  // Sort chronologically
  localComments.value.sort((a, b) => {
    const timeA = a.created_at ? new Date(a.created_at).getTime() : 0;
    const timeB = b.created_at ? new Date(b.created_at).getTime() : 0;
    if (timeA !== timeB) return timeA - timeB;
    return (parseInt(a.id) || 0) - (parseInt(b.id) || 0);
  });

  // Check if incoming comment belongs to currently open conversation
  const myId = parseInt(currentUser.value?.id);
  const otherId = activeUser.value ? parseInt(activeUser.value.id) : null;
  const sentBy = parseInt(commentObj.sent_by || commentObj.comment_by);
  const sentTo = parseInt(commentObj.sent_to);

  const isCurrentConversation = otherId && (
    (sentBy === otherId && sentTo === myId) || 
    (sentBy === myId && sentTo === otherId)
  );

  if (isCurrentConversation) {
    scrollToBottom(true);
  }
};

let pollInterval = null;
const liveCommentListener = (e) => {
  if (e.detail) {
    handleIncomingComment(e.detail);
  }
};

onMounted(() => {
  // Listen to live-comment dispatched by Layout.vue
  window.addEventListener('live-comment', liveCommentListener);

  // Background fallback safety poll every 15 seconds silently
  pollInterval = setInterval(() => {
    if (props.task?.id) {
      fetchDiscussions(false);
    }
  }, 15000);
});

onUnmounted(() => {
  window.removeEventListener('live-comment', liveCommentListener);
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});

watch(() => activeComments.value.length, () => {
  scrollToBottom(true);
});
</script>
