<template>
    <div class="flex relative">
        <div v-if="activeMessageDropdown" class="fixed inset-0 z-40" @click="closeMessageDropdown"></div>
        <div
            class="w-[100%] h-[91vh] bg-slate-50 relative border-l border-gray-100 flex flex-col"
            :class="{ 'w-[5%]': profile }"
            style="transition: width 0.1s linear"
            @dragenter.prevent="onDragEnter"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="onDropFiles"
        >
            <!-- Drag-and-Drop Visual Overlay -->
            <div
                v-if="isDraggingOver"
                class="absolute inset-0 z-50 bg-blue-600/10 backdrop-blur-xs flex flex-col items-center justify-center border-4 border-dashed border-primary rounded-3xl m-4 pointer-events-none transition-all duration-200"
            >
                <div class="w-20 h-20 rounded-full bg-white shadow-xl flex items-center justify-center text-primary mb-3 animate-bounce">
                    <Icon icon="solar:cloud-upload-bold-duotone" class="text-4xl text-primary" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Drop files here to send</h3>
                <p class="text-xs text-gray-500 mt-1 font-medium">Images, voice notes, documents, or videos</p>
            </div>
            <!-- ================= header ====================== -->

            <!-- <div class="flex items-center justify-between bg-white px-7 py-3">
                <div class="flex items-center gap-2">
                    <div class="" @click="profile = true">
                        <div v-if="selectedMessage.type === 'group'">
                            <div class="">
                                <div
                                    class="text-primary border border-primary bg-white p-2 rounded-full overflow-hidden flex-none">
                                    <Icon :icon="selectedMessage.icon" width="25" />
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <img :src="getUserProfileImage(selectedUser)" alt="" class="w-8 h-8 rounded-full object-cover" />
                        </div>
                    </div>
                    <div>
                        <p v-if="selectedMessage.users" class="text-sm font-semibold">{{ selectedMessage.name }}</p>
                        <p v-else class="text-sm font-semibold">{{ selectedMessage.user.first_name }}</p>

                        <div class="flex items-center text-xs mt-1" v-if="selectedMessage.users">
                            <p class="text-[#98A2B3]">
                                {{ selectedMessage.users.length }} Members,
                            </p>
                            <p class="text-green-500 pl-2"> {{getTotalOnlineUsers(selectedMessage.users)}} Online</p>
                        </div>
                        <p class="text-xs text-[#98A2B3]" v-else>Offline</p>
                    </div>
                </div>
                <div class="p-2 border cursor-pointer rounded-md">
                    <Icon icon="mi:options-vertical" width="25" />
                </div>
            </div> -->

            <div
                class="flex items-center justify-between bg-white/80 backdrop-blur-md px-4 lg:px-7 py-4 border-b border-gray-100 shadow-sm z-10 sticky top-0"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center gap-2"
                        @click="profile = true"
                    >
                        <button
                            @click="$emit('back')"
                            class="lg:hidden border p-2 rounded-md"
                        >
                            <Icon
                                icon="material-symbols:arrow-back"
                                width="25"
                            />
                        </button>
                        <div v-if="is_group">
                            <div class="">
                                <div
                                    class="text-primary border border-primary bg-white p-2 rounded-full overflow-hidden flex-none"
                                >
                                    <!-- <Icon :icon="selectedMessage.icon" width="25" /> -->
                                </div>
                            </div>
                        </div>
                        <div v-else class="relative">
                            <img :src="getUserProfileImage(selectedUser)" alt="" class="w-10 h-10 rounded-full object-cover" />
                            <span v-if="selectedUser?.is_online == 1 || selectedUser?.is_online === true" class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full ring-1 ring-emerald-500/20"></span>
                        </div>
                    </div>
                    <div>
                        <div v-if="is_single">
                            <p class="text-sm font-semibold text-gray-900">
                                {{
                                    selectedUser.first_name +
                                    " " +
                                    selectedUser.last_name
                                }}
                            </p>
                            <div class="flex items-center gap-1.5 text-xs mt-0.5">
                                <span class="w-2 h-2 rounded-full" :class="(selectedUser?.is_online == 1 || selectedUser?.is_online === true) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                                <span v-if="selectedUser?.is_online == 1 || selectedUser?.is_online === true" class="text-emerald-600 font-medium">Online</span>
                                <span v-else class="text-[#98A2B3]">Offline</span>
                            </div>
                        </div>

                        <div
                            class="flex flex-col text-xs mt-1"
                            v-if="is_group"
                        >
                            <p class="text-sm font-semibold text-gray-900">
                                {{ selectedMessageGroup?.name || selectedGroup?.name || clickedIndexGroupName || 'Group' }}
                            </p>
                            <div class="flex items-center text-xs mt-0.5">
                                <p class="text-[#98A2B3] pr-2">
                                    {{ totalUsers() }} Members,
                                </p>
                                <p class="text-green-500">
                                    {{ getTotalOnlineUsers(selectedUser) }} Online
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative hidden lg:flex items-center mr-2">
                        <div class="flex items-center rounded-full bg-gray-50 border border-gray-200 px-3 py-1.5 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all shadow-xs">
                            <Icon icon="mdi:magnify" class="text-gray-400 mr-2 flex-shrink-0" />
                            <input
                                v-model="searchQuery"
                                @input="onSearchInput"
                                @keydown.enter="jumpNextSearch"
                                type="text"
                                placeholder="Search in chat..."
                                class="bg-transparent border-none outline-none text-xs w-32 lg:w-44 placeholder-gray-400"
                            />
                            <div v-if="searchMatches.length > 0" class="flex items-center gap-1 pl-2 border-l border-gray-200 text-xs">
                                <span class="text-[11px] text-gray-500 font-semibold whitespace-nowrap">
                                    {{ currentSearchIndex + 1 }}/{{ searchMatches.length }}
                                </span>
                                <button type="button" @click="jumpPrevSearch" class="p-0.5 hover:bg-gray-200 rounded text-gray-600 transition-colors" title="Previous match">
                                    <Icon icon="lucide:chevron-up" class="text-sm" />
                                </button>
                                <button type="button" @click="jumpNextSearch" class="p-0.5 hover:bg-gray-200 rounded text-gray-600 transition-colors" title="Next match">
                                    <Icon icon="lucide:chevron-down" class="text-sm" />
                                </button>
                            </div>
                            <button v-if="searchQuery" type="button" @click="clearSearch" class="p-0.5 text-gray-400 hover:text-gray-600 ml-1">
                                <Icon icon="lucide:x" class="text-xs" />
                            </button>
                        </div>
                    </div>
                    <div class="p-2 border cursor-pointer rounded-md hover:bg-gray-50 transition-colors" @click="toggleOptionsMenu" aria-haspopup="true" aria-controls="overlay_options_menu">
                        <Icon icon="mi:options-vertical" width="22" />
                    </div>
                </div>
                <Menu ref="optionsMenu" id="overlay_options_menu" :model="optionsMenuItems" :popup="true" />
            </div>
            <!-- ================= Archived Chat Banner ================= -->
            <div v-if="isArchived" class="bg-amber-50/95 border-b border-amber-200 px-4 py-2 flex items-center justify-between text-xs text-amber-800 shadow-2xs z-10 sticky top-[73px]">
                <div class="flex items-center gap-2">
                    <Icon icon="solar:archive-bold" class="text-amber-600 text-base flex-shrink-0" />
                    <span><strong>This conversation is archived.</strong> Viewing messages in read-only mode.</span>
                </div>
                <button @click="toggleArchiveStatus" type="button" class="px-2.5 py-1 bg-white hover:bg-amber-100 border border-amber-300 rounded-lg text-amber-900 font-semibold transition-colors flex items-center gap-1 shadow-2xs">
                    <Icon icon="solar:archive-up-linear" class="text-xs" />
                    Unarchive
                </button>
            </div>
            <!-- ================= Pinned Messages Top Banner ================= -->
            <PinnedMessagesBanner
                :messages="pinnedMessages"
                @jump="jumpToMessage"
                @unpin="togglePinMessage"
            />
            <!-- ===============  message body ================= -->
            <div
                @scroll="onScroll"
                ref="chatContainer"
                class="flex-1 h-[74vh] overflow-y-scroll hide_scroll px-4 lg:px-7 pt-5 transition-all pb-32"
                id="chat-container"
            >
                <div
                    v-for="(item, index) in sortedMessages"
                    :key="item.date"
                    class="mt-5"
                >
                    <div class="flex items-center w-full justify-center my-3">
                        <div class="h-px flex-1 bg-gray-200/60 max-w-[100px]"></div>
                        <span class="text-[#667085] text-[11px] font-medium px-3 py-0.5 rounded-full bg-white shadow-2xs border border-gray-100 mx-2">
                            {{ item.date }}
                        </span>
                        <div class="h-px flex-1 bg-gray-200/60 max-w-[100px]"></div>
                    </div>
                    <div
                        v-for="(message, index) in item.messages"
                        :key="message.id"
                        :id="'msg-' + message.id"
                        class="max-w-[85%] sm:max-w-[75%] md:max-w-[65%] transition-all duration-200 mb-1.5"
                        :class="{
                            'w-fit mr-auto': message.sender_id !== page.props.user.id,
                            'w-fit ml-auto': message.sender_id == page.props.user.id,
                            'ring-2 ring-primary ring-offset-2 rounded-2xl bg-blue-50/40 p-1': highlightedMessageId === message.id,
                        }"
                    >
                        <div
                            v-if="
                                message.group_type === props.groupType &&
                                message.group_id == clickedIndexGroup
                            "
                        >
                            <div v-if="message.sender_id !== page.props.user.id" class="flex items-center gap-1.5 mb-1 pl-1">
                                <img :src="getUserProfileImage(message)" alt="" class="w-4 h-4 rounded-full object-cover" />
                                <span class="font-semibold text-xs text-gray-700">{{ message.sender_name }}</span>
                            </div>
                            <div class="relative group flex items-end gap-1.5" :class="{ 'flex-row-reverse': message.sender_id == page.props.user.id }">
                                <div class="flex flex-col max-w-full">
                                    <div
                                        v-if="message.message && message.message.trim()"
                                        @dblclick="handleDoubleClickMessage(message)"
                                        title="Double-click to react ❤️"
                                        :class="{
                                            'py-1.5 px-3 text-sm bg-white text-gray-800 rounded-2xl rounded-tl-xs shadow-xs border border-gray-100 w-fit leading-relaxed cursor-pointer select-text':
                                                message.sender_id !== page.props.user.id,
                                            'w-fit bg-gradient-to-r from-blue-600 to-indigo-600 py-1.5 px-3 text-sm text-white rounded-2xl rounded-tr-xs shadow-xs leading-relaxed cursor-pointer select-text':
                                                message.sender_id == page.props.user.id,
                                        }"
                                    >
                                        <div v-if="message.reply_to" class="text-xs p-1.5 mb-1.5 rounded border-l-4 truncate bg-black/10" :class="message.sender_id == page.props.user.id ? 'border-white/50 text-blue-50' : 'border-primary text-gray-700'">
                                            <span class="font-bold block">{{ message.reply_to.sender?.first_name || message.reply_to.user?.first_name || 'Someone' }}</span>
                                            {{ message.reply_to.message || 'Media message' }}
                                        </div>
                                        <span>{{ message.message }}</span>
                                        <div class="flex items-center justify-end gap-1 mt-0.5 text-[10px]" :class="message.sender_id == page.props.user.id ? 'text-blue-100' : 'text-gray-400'">
                                            <span v-if="message.is_edited" class="italic text-[9px] opacity-80">(edited)</span>
                                            <span>{{ message.timestamp }}</span>
                                            <span v-if="message.sender_id == page.props.user.id" class="inline-flex items-center ml-0.5">
                                                <Icon v-if="message.is_read == 1 || message.is_read === true || message.is_read === '1'" icon="lucide:check-check" class="text-xs text-[#38bdf8] font-black drop-shadow-xs" title="Read" />
                                                <Icon v-else icon="lucide:check-check" class="text-xs text-white/50" title="Delivered" />
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="!message.message || !message.message.trim()" class="flex items-center justify-end gap-1 mt-0.5 text-[10px] text-gray-400">
                                        <span v-if="message.is_edited" class="italic text-[9px] text-gray-400">(edited)</span>
                                        <span>{{ message.timestamp }}</span>
                                        <span v-if="message.sender_id == page.props.user.id" class="inline-flex items-center ml-0.5">
                                            <Icon v-if="message.is_read == 1 || message.is_read === true || message.is_read === '1'" icon="lucide:check-check" class="text-xs text-blue-600 font-bold" title="Read" />
                                            <Icon v-else icon="lucide:check-check" class="text-xs text-gray-400" title="Delivered" />
                                        </span>
                                    </div>
                            <div v-for="(doc, index) in message?.attachments" :key="index" class="mt-1">
                                <template v-if="isEdmsAttachment(doc)">
                                    <div
                                        class="my-1 p-2.5 rounded-xl bg-white/95 border border-blue-200/90 shadow-2xs hover:shadow-xs hover:border-blue-300 transition-all max-w-[280px] sm:max-w-[340px] text-gray-800 cursor-pointer"
                                        @click.stop="openAttachmentPreview(doc)"
                                    >
                                        <div class="flex items-start gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                                <Icon :icon="getEdmsDocIcon(doc)" class="text-xl" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1 mb-0.5">
                                                    <span class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">EDMS</span>
                                                    <span v-if="getEdmsDocNo(doc)" class="text-[10px] font-mono text-gray-500 font-bold truncate">{{ getEdmsDocNo(doc) }}</span>
                                                </div>
                                                <h4 class="text-xs font-bold text-gray-900 truncate leading-tight" :title="doc.file_name">
                                                    {{ getEdmsDocTitle(doc) }}
                                                </h4>
                                                <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-500">
                                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-semibold" :class="getEdmsDocClassification(doc) === 'Confidential' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'">
                                                        {{ getEdmsDocClassification(doc) }}
                                                    </span>
                                                    <span class="truncate max-w-[100px]">{{ getEdmsDocDepartment(doc) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between">
                                            <span class="text-[10px] text-gray-400 font-medium">{{ doc.metadata?.size || 'Document' }}</span>
                                            <button
                                                type="button"
                                                @click.stop="openAttachmentPreview(doc)"
                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:text-blue-700 hover:underline cursor-pointer"
                                            >
                                                <span>Quick Preview</span>
                                                <Icon icon="solar:eye-bold" class="text-xs" />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="isImageAttachment(doc)">
                                    <img :src="getFileUrl(doc.file_path)" class="max-w-[200px] md:max-w-[300px] rounded-lg cursor-pointer hover:opacity-90 shadow-xs" />
                                </template>
                                <template v-else-if="isVideoAttachment(doc)">
                                    <video :src="getFileUrl(doc.file_path)" controls class="max-w-[200px] md:max-w-[300px] rounded-lg shadow-xs"></video>
                                </template>
                                <template v-else-if="isAudioAttachment(doc)">
                                    <VoiceWaveformPlayer :src="getFileUrl(doc.file_path)" :is-sent="message.sender_id == page.props.user.id" />
                                </template>
                                <template v-else>
                                    <a :href="getFileUrl(doc.file_path)" target="_blank" class="flex items-center gap-2 p-2 bg-white/20 rounded-lg text-sm hover:underline">
                                        <Icon icon="mdi:file-document-outline" class="text-xl" />
                                        <span class="truncate max-w-[150px]">{{ doc.file_name }}</span>
                                    </a>
                                </template>
                            </div>
                            <!-- Reactions Display -->
                            <div v-if="message.reactions && message.reactions.length > 0" class="flex items-center gap-1 mt-1 bg-white shadow-sm border rounded-full px-2 py-0.5 w-max">
                                <span v-for="reaction in getUniqueReactions(message.reactions)" :key="reaction" class="text-xs">{{ reaction }}</span>
                                <span v-if="message.reactions.length > 1" class="text-xs text-gray-500 font-medium ml-1">{{ message.reactions.length }}</span>
                            </div>
                            </div>
                                <!-- Action Buttons (WhatsApp Style Dropdown) -->
                                <div class="relative flex items-center flex-shrink-0 transition-opacity ml-1"
                                     :class="activeMessageDropdown === message.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                    <button v-if="!message.is_deleted" @click.stop="toggleMessageDropdown(message.id)" class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                        <Icon icon="mdi:chevron-down" class="text-xl" />
                                    </button>
                                    
                                    <div v-if="activeMessageDropdown === message.id" class="absolute top-8 right-0 z-50 bg-white shadow-xl rounded-xl border border-gray-200/90 w-52 py-2">
                                        <!-- Emojis row -->
                                        <div class="flex items-center justify-between px-2.5 pb-2 border-b border-gray-100 mb-1">
                                            <button v-for="emoji in ['👍','❤️','😂','😮','😢','🙏']" :key="emoji" @click="reactToMessage(message, emoji)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 rounded-md text-base transition-transform hover:scale-125">{{ emoji }}</button>
                                        </div>
                                        
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="toggleStarMessage(message); closeMessageDropdown()">
                                            <Icon :icon="message.is_starred ? 'mdi:star' : 'mdi:star-outline'" :class="{'text-yellow-500': message.is_starred}" class="text-sm" /> Star
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="initiateReply(message); closeMessageDropdown()">
                                            <Icon icon="mdi:reply" class="text-sm" /> Reply
                                        </button>
                                        <button v-if="canEditMessage(message)" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-blue-600 flex items-center gap-2.5 transition-colors font-medium" @click="startEditMessage(message); closeMessageDropdown()">
                                            <Icon icon="mdi:pencil-outline" class="text-sm" /> Edit (15m)
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-indigo-600 flex items-center gap-2.5 transition-colors font-medium" @click="openCreateTaskModal(message); closeMessageDropdown()">
                                            <Icon icon="solar:clipboard-check-bold-duotone" class="text-sm" /> Convert to Task
                                        </button>
                                        <button v-if="message.message" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="copyText(message.message); closeMessageDropdown()">
                                            <Icon icon="mdi:content-copy" class="text-sm" /> Copy
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="openForwardModal(message); closeMessageDropdown()">
                                            <Icon icon="mdi:share" class="text-sm" /> Forward
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="togglePinMessage(message); closeMessageDropdown()">
                                            <Icon :icon="message.is_pinned ? 'mdi:pin' : 'mdi:pin-outline'" :class="{'text-blue-500': message.is_pinned}" class="text-sm" /> Pin
                                        </button>
                                        <button v-if="message.sender_id == page.props.user.id" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-red-500 flex items-center gap-2.5 transition-colors" @click="deleteMessage(message); closeMessageDropdown()">
                                            <Icon icon="mdi:trash-can-outline" class="text-sm" /> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                        </div>

                        <div v-if="!message.group_type">
                            <div class="relative group flex items-end gap-1.5" :class="{ 'flex-row-reverse': message.sender_id == page.props.user.id }">
                                <div class="flex flex-col max-w-full">
                                    <div
                                        v-if="message.message && message.message.trim()"
                                        @dblclick="handleDoubleClickMessage(message)"
                                        title="Double-click to react ❤️"
                                        :class="{
                                            'py-1.5 px-3 text-sm bg-white text-gray-800 rounded-2xl rounded-tl-xs shadow-xs border border-gray-100 w-fit leading-relaxed cursor-pointer select-text':
                                                message.sender_id !== page.props.user.id,
                                            'w-fit bg-gradient-to-r from-blue-600 to-indigo-600 py-1.5 px-3 text-sm text-white rounded-2xl rounded-tr-xs shadow-xs leading-relaxed cursor-pointer select-text':
                                                message.sender_id == page.props.user.id,
                                        }"
                                    >
                                        <div v-if="message.reply_to" class="text-xs p-1.5 mb-1.5 rounded border-l-4 truncate bg-black/10" :class="message.sender_id == page.props.user.id ? 'border-white/50 text-blue-50' : 'border-primary text-gray-700'">
                                            <span class="font-bold block">{{ message.reply_to.sender?.first_name || message.reply_to.user?.first_name || 'Someone' }}</span>
                                            {{ message.reply_to.message || 'Media message' }}
                                        </div>
                                        <span>{{ message.message }}</span>
                                        <div class="flex items-center justify-end gap-1 mt-0.5 text-[10px]" :class="message.sender_id == page.props.user.id ? 'text-blue-100' : 'text-gray-400'">
                                            <span v-if="message.is_edited" class="italic text-[9px] opacity-80">(edited)</span>
                                            <span>{{ message.timestamp }}</span>
                                            <span v-if="message.sender_id == page.props.user.id" class="inline-flex items-center ml-0.5">
                                                <Icon v-if="message.is_read == 1 || message.is_read === true || message.is_read === '1'" icon="lucide:check-check" class="text-xs text-[#38bdf8] font-black drop-shadow-xs" title="Read" />
                                                <Icon v-else icon="lucide:check-check" class="text-xs text-white/50" title="Delivered" />
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="!message.message || !message.message.trim()" class="flex items-center justify-end gap-1 mt-0.5 text-[10px] text-gray-400">
                                        <span v-if="message.is_edited" class="italic text-[9px] text-gray-400">(edited)</span>
                                        <span>{{ message.timestamp }}</span>
                                        <span v-if="message.sender_id == page.props.user.id" class="inline-flex items-center ml-0.5">
                                            <Icon v-if="message.is_read == 1 || message.is_read === true || message.is_read === '1'" icon="lucide:check-check" class="text-xs text-blue-600 font-bold" title="Read" />
                                            <Icon v-else icon="lucide:check-check" class="text-xs text-gray-400" title="Delivered" />
                                        </span>
                                    </div>
                            <div v-for="(doc, index) in message?.attachments" :key="index" class="mt-1">
                                <template v-if="isEdmsAttachment(doc)">
                                    <div
                                        class="my-1 p-2.5 rounded-xl bg-white/95 border border-blue-200/90 shadow-2xs hover:shadow-xs hover:border-blue-300 transition-all max-w-[280px] sm:max-w-[340px] text-gray-800 cursor-pointer"
                                        @click.stop="openAttachmentPreview(doc)"
                                    >
                                        <div class="flex items-start gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                                <Icon :icon="getEdmsDocIcon(doc)" class="text-xl" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1 mb-0.5">
                                                    <span class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">EDMS</span>
                                                    <span v-if="getEdmsDocNo(doc)" class="text-[10px] font-mono text-gray-500 font-bold truncate">{{ getEdmsDocNo(doc) }}</span>
                                                </div>
                                                <h4 class="text-xs font-bold text-gray-900 truncate leading-tight" :title="doc.file_name">
                                                    {{ getEdmsDocTitle(doc) }}
                                                </h4>
                                                <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-500">
                                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-semibold" :class="getEdmsDocClassification(doc) === 'Confidential' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'">
                                                        {{ getEdmsDocClassification(doc) }}
                                                    </span>
                                                    <span class="truncate max-w-[100px]">{{ getEdmsDocDepartment(doc) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between">
                                            <span class="text-[10px] text-gray-400 font-medium">{{ doc.metadata?.size || 'Document' }}</span>
                                            <button
                                                type="button"
                                                @click.stop="openAttachmentPreview(doc)"
                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:text-blue-700 hover:underline cursor-pointer"
                                            >
                                                <span>Quick Preview</span>
                                                <Icon icon="solar:eye-bold" class="text-xs" />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="isImageAttachment(doc)">
                                    <img :src="getFileUrl(doc.file_path)" class="max-w-[200px] md:max-w-[300px] rounded-lg cursor-pointer hover:opacity-90 shadow-xs" />
                                </template>
                                <template v-else-if="isVideoAttachment(doc)">
                                    <video :src="getFileUrl(doc.file_path)" controls class="max-w-[200px] md:max-w-[300px] rounded-lg shadow-xs"></video>
                                </template>
                                <template v-else-if="isAudioAttachment(doc)">
                                    <VoiceWaveformPlayer :src="getFileUrl(doc.file_path)" :is-sent="message.sender_id == page.props.user.id" />
                                </template>
                                <template v-else>
                                    <a :href="getFileUrl(doc.file_path)" target="_blank" class="flex items-center gap-2 p-2 bg-white/20 rounded-lg text-sm hover:underline">
                                        <Icon icon="mdi:file-document-outline" class="text-xl" />
                                        <span class="truncate max-w-[150px]">{{ doc.file_name }}</span>
                                    </a>
                                </template>
                            </div>
                            <!-- Reactions Display -->
                            <div v-if="message.reactions && message.reactions.length > 0" class="flex items-center gap-1 mt-1 bg-white shadow-sm border rounded-full px-2 py-0.5 w-max">
                                <span v-for="reaction in getUniqueReactions(message.reactions)" :key="reaction" class="text-xs">{{ reaction }}</span>
                                <span v-if="message.reactions.length > 1" class="text-xs text-gray-500 font-medium ml-1">{{ message.reactions.length }}</span>
                            </div>
                            </div>
                                <!-- Action Buttons (WhatsApp Style Dropdown) -->
                                <div class="relative flex items-center flex-shrink-0 transition-opacity ml-1"
                                     :class="activeMessageDropdown === message.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                    <button v-if="!message.is_deleted" @click.stop="toggleMessageDropdown(message.id)" class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                        <Icon icon="mdi:chevron-down" class="text-xl" />
                                    </button>
                                    
                                    <div v-if="activeMessageDropdown === message.id" class="absolute top-8 right-0 z-50 bg-white shadow-xl rounded-xl border border-gray-200/90 w-52 py-2">
                                        <!-- Emojis row -->
                                        <div class="flex items-center justify-between px-2.5 pb-2 border-b border-gray-100 mb-1">
                                            <button v-for="emoji in ['👍','❤️','😂','😮','😢','🙏']" :key="emoji" @click="reactToMessage(message, emoji)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 rounded-md text-base transition-transform hover:scale-125">{{ emoji }}</button>
                                        </div>
                                        
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="toggleStarMessage(message); closeMessageDropdown()">
                                            <Icon :icon="message.is_starred ? 'mdi:star' : 'mdi:star-outline'" :class="{'text-yellow-500': message.is_starred}" class="text-sm" /> Star
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="initiateReply(message); closeMessageDropdown()">
                                            <Icon icon="mdi:reply" class="text-sm" /> Reply
                                        </button>
                                        <button v-if="canEditMessage(message)" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-blue-600 flex items-center gap-2.5 transition-colors font-medium" @click="startEditMessage(message); closeMessageDropdown()">
                                            <Icon icon="mdi:pencil-outline" class="text-sm" /> Edit (15m)
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-indigo-600 flex items-center gap-2.5 transition-colors font-medium" @click="openCreateTaskModal(message); closeMessageDropdown()">
                                            <Icon icon="solar:clipboard-check-bold-duotone" class="text-sm" /> Convert to Task
                                        </button>
                                        <button v-if="message.message" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="copyText(message.message); closeMessageDropdown()">
                                            <Icon icon="mdi:content-copy" class="text-sm" /> Copy
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="openForwardModal(message); closeMessageDropdown()">
                                            <Icon icon="mdi:share" class="text-sm" /> Forward
                                        </button>
                                        <button class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 flex items-center gap-2.5 text-gray-700 transition-colors" @click="togglePinMessage(message); closeMessageDropdown()">
                                            <Icon :icon="message.is_pinned ? 'mdi:pin' : 'mdi:pin-outline'" :class="{'text-blue-500': message.is_pinned}" class="text-sm" /> Pin
                                        </button>
                                        <button v-if="message.sender_id == page.props.user.id" class="w-full text-left px-3.5 py-1.5 text-xs hover:bg-gray-50 text-red-500 flex items-center gap-2.5 transition-colors" @click="deleteMessage(message); closeMessageDropdown()">
                                            <Icon icon="mdi:trash-can-outline" class="text-sm" /> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Scroll to Bottom Button -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-3 scale-90"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-3 scale-90"
            >
                <button
                    v-if="showScrollBottom"
                    type="button"
                    @click="scrollToBottomSmooth"
                    class="absolute right-6 md:right-10 bottom-28 z-30 w-10 h-10 rounded-full bg-white/95 hover:bg-white text-gray-700 hover:text-primary shadow-lg border border-gray-200/80 backdrop-blur-md flex items-center justify-center transition-transform hover:scale-110 active:scale-95"
                    title="Scroll to bottom"
                >
                    <Icon icon="solar:alt-arrow-down-bold" class="text-lg" />
                    <span
                        v-if="unreadSinceScroll > 0"
                        class="absolute -top-1.5 -right-1.5 bg-primary text-white text-[10px] font-bold px-1.5 py-0.2 min-w-[18px] text-center rounded-full shadow-md animate-pulse"
                    >
                        {{ unreadSinceScroll > 99 ? '99+' : unreadSinceScroll }}
                    </span>
                </button>
            </transition>

            <!-- =============== send message  ================= -->
            <div class="absolute w-full bottom-0 left-0 px-4 lg:px-7 pb-6 bg-gradient-to-t from-slate-50 via-slate-50 to-transparent pt-10">
                <div
                    class="bg-white/80 backdrop-blur-xl border border-gray-200 shadow-lg rounded-3xl py-2 px-2 transition-all focus-within:ring-2 focus-within:ring-primary/30"
                >
                    <div v-if="isArchived" class="py-3 px-4 text-center flex items-center justify-center gap-2 text-xs text-gray-500">
                        <Icon icon="solar:lock-bold-duotone" class="text-amber-500 text-base" />
                        <span>This conversation is archived and in read-only mode.</span>
                        <button @click="toggleArchiveStatus" type="button" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Unarchive to send messages
                        </button>
                    </div>
                    <div v-else>
                        <div
                            v-if="isTyping"
                            class="px-2 pb-2 text-sm text-gray-500"
                        >
                            {{ typingUser.name }} is typing...
                        </div>
                        <!-- Editing Message Banner -->
                        <div v-if="editingMessage" class="relative bg-amber-50 rounded-xl p-2.5 mb-2 border-l-4 border-amber-500 shadow-inner flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <Icon icon="mdi:pencil" class="text-amber-600 text-base flex-shrink-0" />
                                <div class="truncate text-xs">
                                    <span class="font-bold text-amber-800">Editing message</span>
                                    <span class="text-amber-600 ml-1 truncate max-w-[220px]">{{ editingMessage.message }}</span>
                                </div>
                            </div>
                            <button type="button" @click="cancelEditMessage" class="text-gray-400 hover:text-red-500 transition-colors p-1" title="Cancel edit">
                                <Icon icon="lucide:x" class="text-sm" />
                            </button>
                        </div>
                        <div v-if="replyingTo" class="relative bg-gray-100 rounded-lg p-3 mb-2 border-l-4 border-primary shadow-inner">
                            <button type="button" @click="cancelReply" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 transition-colors">
                                <Icon icon="mdi:close-circle" class="text-xl" />
                            </button>
                            <span class="text-primary font-semibold block text-sm mb-1">Replying to {{ replyingTo.sender?.first_name || replyingTo.user?.first_name || 'Someone' }}</span>
                            <span class="text-gray-600 text-sm line-clamp-2">{{ replyingTo.message || 'Media message' }}</span>
                        </div>
                        <!-- Attached Files Previews -->
                        <div v-if="files && files.length > 0" class="flex flex-wrap gap-2 px-3 pt-2 pb-1">
                            <div
                                v-for="(f, fIndex) in files"
                                :key="fIndex"
                                class="flex items-center gap-1.5 bg-blue-50 text-blue-900 border border-blue-200 text-xs py-1 px-2.5 rounded-full shadow-2xs"
                            >
                                <Icon icon="mdi:attachment" class="text-sm text-blue-600" />
                                <span class="max-w-[140px] truncate font-medium">{{ f.name }}</span>
                                <button type="button" @click="removeFile(fIndex)" class="p-0.5 hover:text-red-500 rounded-full transition-colors">
                                    <Icon icon="lucide:x" class="text-xs" />
                                </button>
                            </div>
                        </div>
                        <!-- EDMS Attached Documents Previews -->
                        <div v-if="edmsAttachments && edmsAttachments.length > 0" class="flex flex-wrap gap-2 px-3 pt-2 pb-1">
                            <div
                                v-for="(doc, dIndex) in edmsAttachments"
                                :key="dIndex"
                                class="flex items-center gap-1.5 bg-indigo-50 text-indigo-900 border border-indigo-200 text-xs py-1 px-2.5 rounded-full shadow-2xs"
                            >
                                <Icon :icon="getFileIcon(doc)" class="text-sm text-indigo-600" />
                                <span class="font-bold text-[10px] text-indigo-600 uppercase">EDMS</span>
                                <span class="max-w-[150px] truncate font-medium">{{ doc.title || doc.file_name }}</span>
                                <button type="button" @click="removeEdmsAttachment(dIndex)" class="p-0.5 hover:text-red-500 rounded-full transition-colors" title="Remove">
                                    <Icon icon="lucide:x" class="text-xs" />
                                </button>
                            </div>
                        </div>
                        <Textarea
                            v-model="formData.message"
                            @input="handleTyping"
                            @keyup.enter="sendMessage"
                            autoResize
                            rows="1"
                            cols="30"
                            placeholder="Type a message..."
                            class="p-inputtextarea bg-transparent text-gray-700 px-4 pt-3 pb-1"
                            required
                        />
                        <input
                            type="file"
                            multiple
                            style="display: none"
                            ref="fileInput"
                            @change="handleFileChange"
                        />
                    </div>

                    <div
                        class="flex items-center justify-between px-4 pb-2 text-gray-500"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-2 hover:bg-gray-100 rounded-full cursor-pointer transition-colors" @click="triggerFileInput" title="Attach Files">
                                <Icon
                                    icon="akar-icons:attach"
                                    width="22"
                                />
                            </div>
                            <!-- Share from EDMS Button -->
                            <div class="p-2 hover:bg-gray-100 rounded-full cursor-pointer transition-colors text-primary" @click="showEdmsPickerModal = true" title="Share from EDMS Repository">
                                <Icon
                                    icon="solar:folder-with-files-bold-duotone"
                                    width="22"
                                />
                            </div>
                            <div class="p-2 hover:bg-gray-100 rounded-full cursor-pointer transition-colors" @click="addLinkModal = true" title="Add Link">
                                <Icon
                                    icon="ic:round-link"
                                    width="22"
                                />
                            </div>
                            <div class="p-2 hover:bg-gray-100 rounded-full cursor-pointer transition-colors relative" @click="showEmojiPicker = !showEmojiPicker">
                                <Icon
                                    icon="mdi:emoticon-outline"
                                    width="22"
                                />
                                <div v-if="showEmojiPicker" class="absolute bottom-12 right-0 z-50 shadow-xl rounded-lg bg-white border border-gray-200 p-2 w-[280px] h-[320px] overflow-y-auto" @click.stop>
                                    <div class="grid grid-cols-8 gap-1">
                                        <button v-for="(emoji, index) in EMOJI_LIST" :key="index" @click.stop="onSelectEmoji(emoji)" class="text-xl hover:bg-gray-100 p-1 rounded transition-colors" type="button">
                                            {{ emoji }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2 rounded-full cursor-pointer transition-colors relative" :class="isRecording ? 'bg-red-100 text-red-600 animate-pulse' : 'hover:bg-gray-100 text-gray-500'" @click="toggleRecording">
                                <Icon
                                    :icon="isRecording ? 'mdi:stop-circle-outline' : 'mdi:microphone-outline'"
                                    width="22"
                                />
                                <span v-if="isRecording" class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-semibold bg-red-500 text-white px-2 py-1 rounded shadow-lg whitespace-nowrap">
                                    {{ formattedRecordingTime }}
                                </span>
                            </div>
                        </div>
                        <button
                            @click="sendMessage()"
                            class="flex items-center justify-center bg-primary text-white w-10 h-10 rounded-full hover:bg-indigo-600 hover:shadow-lg transition-all flex-shrink-0"
                        >
                            <Icon icon="tabler:send" width="20" class="ml-1" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ===================   sidebar ================= -->

        <section
            v-if="profile && selectedUser.type !== 'group'"
            class="w-[35%] border-l border-gray-100 bg-white"
            ref="sidebarRef"
        >
            <div v-if="activePage === 1">
                <div
                    class="flex items-center justify-between bg-white px-7 border-b bord py-3"
                >
                    <h1 class="font-semibold text-lg">Profile</h1>
                    <div
                        class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
                        @click="profile = false"
                    >
                        <Icon icon="prime:times" width="25" />
                    </div>
                </div>

                <div
                    class="border border-solid bg-white overflow-scroll h-[90vh] pb-32 hide_scroll"
                >
                    <div
                        class="w-full flex flex-col items-center justify-center mt-10"
                    >
                        <div class="relative w-36 h-36">
                            <img :src="getUserProfileImage(selectedUser)" alt="" class="w-36 h-36 rounded-full object-cover" />
                            <div
                                class="h-6 w-6 bg-[#12B76A] rounded-full absolute bottom-0 right-5 border-[3px] border-white"
                            ></div>
                        </div>
                        <h1 class="font-semibold text-xl mt-2">
                            {{ selectedUser?.first_name }} {{ selectedUser?.last_name }}
                        </h1>
                        <p
                            class="text-sm text-[#667085] mt-2 bg-gray-50 px-4 py-1.5 rounded-2xl capitalize"
                        >
                            {{ selectedUser?.roles?.[0]?.name || 'User' }}
                        </p>
                    </div>

                    <div class="mx-3 bg-[#F2F4F7] rounded-md p-3 mt-5">
                        <div class="flex justify-between items-center">
                            <h1 class="font-semibold text-lg">Share Files</h1>
                            <button
                                class="text-base text-primary"
                                @click="activePage = 2"
                            >
                                See All
                            </button>
                        </div>
                        <div
                            v-for="(item, index) in files"
                            :key="index"
                            class="mt-3"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                <div
                                    class="p-2 bg-gray-100 border rounded-full"
                                >
                                    <Icon :icon="item.icon" width="30" />
                                </div>
                                <h1>{{ item.name }}</h1>
                            </div>
                        </div>
                    </div>
                    <Divider />
                    <div class="flex px-7 items-center justify-between">
                        <div class="flex items-center gap-2 text-base">
                            <Icon
                                icon="grommet-icons:notification"
                                width="20"
                            />
                            <h1 class="">Notification</h1>
                        </div>
                        <label class="switch">
                            <input type="checkbox" :v-model="false" />
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <Divider />
                    <div
                        class="flex items-center gap-3 py-3 px-7 cursor-pointer"
                        @click="reportModal = true"
                    >
                        <img
                            :src="reportIcon"
                            alt=""
                            class="w-7"
                        />
                        <p class="text-[#F04438] font-semibold text-base">
                            Report Contact
                        </p>
                    </div>
                </div>
            </div>

            <div class="" v-else>
                <div
                    class="flex items-center gap-5 bg-white px-7 border-b bord py-3"
                >
                    <div
                        class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
                        @click="activePage = 1"
                    >
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>
                    <h1 class="font-semibold text-lg">
                        Shared Files
                        <span class="text-xs text-gray-500">(24 Files)</span>
                    </h1>
                </div>
                <div
                    class="flex items-center border m-7 border-solid rounded-3xl px-3 bg-gray-100"
                >
                    <Icon icon="lucide:search" class="text-lg text-gray-500" />
                    <input
                        type="text"
                        placeholder="Search Files "
                        class="outline-none h-full p-2 bg-gray-100"
                    />
                </div>

                <div class="mx-7 flex gap-5 flex-wrap pb-5">
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl cursor-pointer font-normal border border-solid border-gray-300"
                        :class="{
                            'bg-[#eaf2ff] border-primary  ': activeButton === 1,
                        }"
                    >
                        All Files
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Documents
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Images
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Video
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Audio
                    </div>
                </div>

                <!-- =================   All Files =============== -->
                <div class="mx-7">
                    <div
                        class="pt-10 h-[65vh] pb-20 overflow-scroll hide_scroll"
                        v-if="allFiles.length !== 0"
                    >
                        <div
                            v-for="(item, index) in allFiles"
                            :key="index"
                            class="mb-7"
                        >
                            <h1 class="text-[#667085]">{{ item.day }}</h1>
                            <div
                                v-for="(files, index) in item.files"
                                :key="index"
                                class="mt-3"
                            >
                                <div class="flex items-center gap-2 mb-3">
                                    <div
                                        class="p-2 bg-gray-100 border rounded-full"
                                    >
                                        <Icon :icon="files.icon" width="30" />
                                    </div>
                                    <h1>{{ files.name }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center"
                    >
                        <img
                            :src="noFile"
                            alt=""
                            class="w-40 my-5"
                        />
                        <h1 class="font-semibold text-2xl">No Files Found</h1>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="profile && selectedUser.type === 'group'"
            class="w-[35%] border-l border-gray-100 bg-white"
            ref="sidebarRef"
        >
            <div v-if="activePage === 1">
                <div
                    class="flex items-center gap-3 bg-white px-7 border-b bord py-3"
                >
                    <div
                        class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
                        @click="profile = false"
                    >
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>

                    <h1 class="font-semibold text-lg">Branch Info</h1>
                </div>

                <div
                    class="border border-solid bg-white overflow-scroll h-[90vh] pb-32 hide_scroll"
                >
                    <div
                        class="w-full flex flex-col items-center justify-center mt-10"
                    >
                        <div
                            class="text-primary border border-primary bg-white p-5 rounded-full overflow-hidden flex-none"
                        >
                            <Icon :icon="selectedMessage.icon" width="80" />
                        </div>
                        <h1 class="font-semibold text-xl mt-2">
                            {{ clickedIndexGroupName }}
                        </h1>
                        <div class="flex items-center text-xs mt-1">
                            <p class="text-[#98A2B3]">
                                {{ totalUsers() }} Members,
                            </p>
                            <p class="text-green-500 pl-2">{{ getTotalOnlineUsers(selectedGroup) }} Online</p>
                        </div>
                    </div>

                    <Divider />
                    <div class="flex px-7 items-center justify-between">
                        <div class="flex items-center gap-2 text-base">
                            <Icon
                                icon="grommet-icons:notification"
                                width="20"
                            />
                            <h1 class="">Notification</h1>
                        </div>
                        <!-- <label class="switch">
                            <input type="checkbox" v-model="toggle" />
                            <span class="slider round"></span>
                        </label> -->
                    </div>
                    <Divider />
                    <!-- =================  Members ================ -->
                    <div class="mx-3 mt-5">
                        <div
                            class="flex items-center justify-between font-semibold"
                        >
                            <h1>Members</h1>
                            <div
                                class="p-2 cursor-pointer transition-all"
                                @click="toggleMembers"
                            >
                                <Icon
                                    icon="flowbite:angle-right-outline"
                                    :class="{ 'rotate-90': member_switch }"
                                    style="transition: all 0.3s linear"
                                />
                            </div>
                        </div>
                        <div
                            class="h-0 overflow-hidden"
                            :class="{ ' h-auto': member_switch }"
                            style="transition: all 1s linear"
                        >
                            <div>
                                <div
                                    v-for="(item, index) in selectedGroup?.users || []"
                                    :key="index"
                                >
                                    <div
                                        class="flex items-center gap-5 font-semibold mb-3"
                                    >
                                        <img :src="getUserProfileImage(item)" alt="" class="w-9 h-9 rounded-full object-cover" />

                                        <h1
                                            class="cursor-pointer"
                                            @click="toggle"
                                            aria-haspopup="true"
                                            aria-controls="overlay_menu"
                                        >
                                            {{ item.first_name }} {{ item.last_name }}
                                        </h1>
                                    </div>
                                </div>
                                <Menu
                                    ref="menu"
                                    id="overlay_menu"
                                    :model="menuItems"
                                    :popup="true"
                                >
                                    <template #item="{ item }">
                                        <div
                                            class="flex items-center gap-2 font-semibold p-2"
                                            :class="{
                                                'text-red-500':
                                                    item.label ===
                                                    'Report Contact',
                                            }"
                                            @click="
                                                handleMemberOption(item.label)
                                            "
                                        >
                                            <Icon
                                                :icon="item.icon"
                                                width="20"
                                            />
                                            <p>{{ item.label }}</p>
                                        </div>
                                    </template>
                                </Menu>
                            </div>
                            <div class=" "></div>
                            <div>
                                <button class="text-sm text-primary">
                                    See More...
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mx-3 bg-[#F2F4F7] rounded-md p-3 mt-5">
                        <div class="flex justify-between items-center">
                            <h1 class="font-semibold text-lg">Share Files</h1>
                            <button
                                class="text-base text-primary"
                                @click="activePage = 2"
                            >
                                See All
                            </button>
                        </div>
                        <div
                            v-for="(item, index) in files"
                            :key="index"
                            class="mt-3"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                <div
                                    class="p-2 bg-gray-100 border rounded-full"
                                >
                                    <Icon :icon="item.icon" width="30" />
                                </div>
                                <h1>{{ item.name }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="" v-else>
                <div
                    class="flex items-center gap-5 bg-white px-7 border-b bord py-3"
                >
                    <div
                        class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
                        @click="activePage = 1"
                    >
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>
                    <h1 class="font-semibold text-lg">
                        Shared Files
                        <span class="text-xs text-gray-500">(24 Files)</span>
                    </h1>
                </div>
                <div
                    class="flex items-center border m-7 border-solid rounded-3xl px-3 bg-gray-100"
                >
                    <Icon icon="lucide:search" class="text-lg text-gray-500" />
                    <input
                        type="text"
                        placeholder="Search Files "
                        class="outline-none h-full p-2 bg-gray-100"
                    />
                </div>

                <div class="mx-7 flex gap-5 flex-wrap pb-5">
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl cursor-pointer font-normal border border-solid border-gray-300"
                        :class="{
                            'bg-[#eaf2ff] border-primary  ': activeButton === 1,
                        }"
                    >
                        All Files
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Documents
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Images
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Video
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300"
                    >
                        Audio
                    </div>
                </div>

                <!-- =================   All Files =============== -->
                <div class="mx-7">
                    <div
                        class="pt-10 h-[65vh] pb-20 overflow-scroll hide_scroll"
                        v-if="allFiles.length !== 0"
                    >
                        <div
                            v-for="(item, index) in allFiles"
                            :key="index"
                            class="mb-7"
                        >
                            <h1 class="text-[#667085]">{{ item.day }}</h1>
                            <div
                                v-for="(files, index) in item.files"
                                :key="index"
                                class="mt-3"
                            >
                                <div class="flex items-center gap-2 mb-3">
                                    <div
                                        class="p-2 bg-gray-100 border rounded-full"
                                    >
                                        <Icon :icon="files.icon" width="30" />
                                    </div>
                                    <h1>{{ files.name }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center"
                    >
                        <img
                            :src="noFile"
                            alt=""
                            class="w-40 my-5"
                        />
                        <h1 class="font-semibold text-2xl">No Files Found</h1>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <AddLink v-model="addLinkModal" @data="fromLinkModel" />
    <ReportDialog v-model="reportModal" />
    <ForwardMessageModal v-model="forwardModalVisible" :message="forwardMessageData" :isGroup="is_group" @forwarded="fetchMessages" />
    <EdmsDocumentPickerModal
        v-model="showEdmsPickerModal"
        @attach="onAttachEdmsDocuments"
    />
    <CreateTaskFromMessageModal
        v-model="showCreateTaskModal"
        :message="taskSourceMessage"
        :isGroup="is_group"
        :selectedUser="selectedUser"
    />
    <StarredMessagesDrawer
        :visible="showStarredDrawer"
        @update:visible="showStarredDrawer = $event"
        :messages="starredMessages"
        @jump="jumpToMessage"
        @unstar="toggleStarMessage"
    />
    <ExportChatModal
        v-model:visible="showExportModal"
        :conversationType="is_group ? 'group' : 'direct'"
        :targetId="is_group ? props.clickedIndexGroup : props.selectedUser?.id"
        :groupType="props.groupType"
        :conversationTitle="is_group ? (props.selectedMessageGroup?.name || 'Group') : ((props.selectedUser?.first_name || '') + ' ' + (props.selectedUser?.last_name || ''))"
    />

    <!-- Quick Preview Modal for Shared EDMS & Chat Documents -->
    <Dialog
        v-model:visible="quickPreviewModal"
        modal
        :closable="true"
        :dismissableMask="true"
        :style="{ width: '92vw', maxWidth: '1000px' }"
        class="p-dialog-custom"
    >
        <template #header>
            <div class="flex items-center justify-between w-full pr-4 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <Icon :icon="getEdmsDocIcon(activePreviewDoc)" class="text-xl" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-[10px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">EDMS</span>
                            <span v-if="getEdmsDocNo(activePreviewDoc)" class="text-[11px] font-mono font-bold text-gray-600">{{ getEdmsDocNo(activePreviewDoc) }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" :class="getEdmsDocClassification(activePreviewDoc) === 'Confidential' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                                {{ getEdmsDocClassification(activePreviewDoc) }}
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 truncate max-w-[320px] sm:max-w-[500px]" :title="getEdmsDocTitle(activePreviewDoc)">
                            {{ getEdmsDocTitle(activePreviewDoc) }}
                        </h3>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a
                        :href="getFileUrl(activePreviewDoc?.file_path)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors"
                        title="Open directly in a new browser tab"
                    >
                        <Icon icon="solar:arrow-right-up-linear" class="text-sm" />
                        <span class="hidden sm:inline">Open in New Tab</span>
                    </a>
                    <a
                        :href="getDownloadUrl(activePreviewDoc)"
                        download
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary hover:bg-blue-700 text-white transition-colors"
                        title="Download document"
                    >
                        <Icon icon="solar:download-minimalistic-bold" class="text-sm" />
                        <span class="hidden sm:inline">Download</span>
                    </a>
                </div>
            </div>
        </template>

        <div class="pt-2">
            <!-- PDF Viewer -->
            <div v-if="isPdfAttachment(activePreviewDoc)" class="w-full bg-gray-900 rounded-xl overflow-hidden border border-gray-200 shadow-inner">
                <iframe
                    :src="getFileUrl(activePreviewDoc?.file_path) + '#toolbar=1&navpanes=0'"
                    class="w-full h-[70vh] border-0"
                    title="Document Preview"
                >
                    <div class="p-8 text-center text-white">
                        <p class="mb-3">Inline preview is not supported by your browser.</p>
                        <a :href="getFileUrl(activePreviewDoc?.file_path)" target="_blank" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold">
                            Open in New Tab
                        </a>
                    </div>
                </iframe>
            </div>

            <!-- Image Viewer -->
            <div v-else-if="isImageAttachment(activePreviewDoc)" class="w-full flex items-center justify-center p-4 bg-gray-50 rounded-xl border border-gray-200 min-h-[300px] max-h-[70vh] overflow-auto">
                <img :src="getFileUrl(activePreviewDoc?.file_path)" class="max-w-full max-h-[65vh] object-contain rounded-lg shadow-sm" />
            </div>

            <!-- Video Viewer -->
            <div v-else-if="isVideoAttachment(activePreviewDoc)" class="w-full flex items-center justify-center p-4 bg-black rounded-xl">
                <video :src="getFileUrl(activePreviewDoc?.file_path)" controls class="max-w-full max-h-[65vh] rounded-lg"></video>
            </div>

            <!-- Fallback for other formats (docx, xlsx, etc.) -->
            <div v-else class="py-12 px-6 flex flex-col items-center justify-center text-center bg-gray-50/70 rounded-xl border border-gray-200">
                <div class="w-20 h-20 rounded-2xl bg-white border border-gray-200 shadow-sm flex items-center justify-center mb-4 text-primary">
                    <Icon :icon="getEdmsDocIcon(activePreviewDoc)" class="text-5xl" />
                </div>
                <h4 class="text-base font-bold text-gray-900 mb-1">
                    {{ getEdmsDocTitle(activePreviewDoc) }}
                </h4>
                <p class="text-xs text-gray-500 max-w-md mb-6">
                    This file format ({{ activePreviewDoc?.file_type || 'Document' }}) can be viewed by downloading or opening in external viewer.
                </p>
                <div class="flex items-center gap-3">
                    <a
                        :href="getFileUrl(activePreviewDoc?.file_path)"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-primary hover:bg-blue-700 text-white shadow-sm transition-all"
                    >
                        <Icon icon="solar:eye-bold" class="text-lg" />
                        <span>Open Document</span>
                    </a>
                    <a
                        :href="getDownloadUrl(activePreviewDoc)"
                        download
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 shadow-2xs transition-all"
                    >
                        <Icon icon="solar:download-minimalistic-bold" class="text-lg" />
                        <span>Download Copy</span>
                    </a>
                </div>
            </div>

            <!-- Document Details Footer -->
            <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between text-xs text-gray-500 gap-2">
                <div class="flex items-center gap-4">
                    <span><strong>Department:</strong> {{ getEdmsDocDepartment(activePreviewDoc) }}</span>
                    <span v-if="activePreviewDoc?.metadata?.size"><strong>Size:</strong> {{ activePreviewDoc.metadata.size }}</span>
                </div>
                <span class="text-gray-400 font-mono text-[11px] truncate max-w-[300px]">
                    {{ activePreviewDoc?.file_name }}
                </span>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import {
    ref,
    watch,
    nextTick,
    onMounted,
    onBeforeUnmount,
    computed,
    onUpdated,
} from "vue";
import { useInfiniteScroll } from "@vueuse/core";

import noFile from "../../../public/images/no_file.svg";
import reportIcon from "../../../public/images/report.svg";

import { defineProps } from "vue";
import { useToast } from "primevue/usetoast";
import { onClickOutside } from "@vueuse/core";
import AddLink from "./Modals/chat/AddLink.vue";
import ReportDialog from "./Modals/chat/ReportDialog.vue";
import ForwardMessageModal from "./Modals/chat/ForwardMessageModal.vue";
import EdmsDocumentPickerModal from "./Modals/chat/EdmsDocumentPickerModal.vue";
import CreateTaskFromMessageModal from "./Modals/chat/CreateTaskFromMessageModal.vue";
import ExportChatModal from "./Modals/chat/ExportChatModal.vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Menu from "primevue/menu";
import VoiceWaveformPlayer from "./chat/VoiceWaveformPlayer.vue";
import PinnedMessagesBanner from "./chat/PinnedMessagesBanner.vue";
import StarredMessagesDrawer from "./chat/StarredMessagesDrawer.vue";

const EMOJI_LIST = ["😀","😃","😄","😁","😆","😅","😂","🤣","🥲","🥹","😊","😇","🙂","🙃","😉","😌","😍","🥰","😘","😗","😙","😚","😋","😛","😝","😜","🤪","🤨","🧐","🤓","😎","🥸","🤩","🥳","😏","😒","😞","😔","😟","😕","🙁","☹️","😣","😖","😫","😩","🥺","😢","😭","😮‍💨","😤","😠","😡","🤬","🤯","😳","🥵","🥶","😱","😨","😰","😥","😓","🫣","🤗","🫡","🤔","🫢","🤭","🤫","🤥","😶","😶‍🌫️","😐","😑","😬","🫨","🫠","🙄","😯","😦","😧","😮","😲","🥱","😴","🤤","😪","😵","😵‍💫","🫥","🤐","🥴","🤢","🤮","🤧","😷","🤒","🤕","🤑","🤠","😈","👿","👹","👺","🤡","💩","👻","💀","☠️","👽","👾","🤖","🎃","😺","😸","😹","😻","😼","😽","🙀","😿","😾","🫶","🤲","👐","🙌","👏","🤝","👍","👎","👊","✊","🤛","🤜","🫙","🤞","✌️","🫰","🤟","🤘","👌","🤌","🤏","🫳","🫴","👈","👉","👆","👇","☝️","✋","🤚","🖐","🖖","👋","🤙","🫲","🫱","💪","🦾","🖕","✍️","🙏","🫵","🦶","🦵","🦿","💄","💋","👄","🫦","🦷","👅","👂","🦻","👃","👣","👁","👀","🫀","🫁","🧠","🗣","👤","👥","🫂"];

const quickPreviewModal = ref(false);
const activePreviewDoc = ref(null);

const getFileUrl = (filePath) => {
    if (!filePath) return '';
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) return filePath;
    const base = (page?.props?.base || '').replace(/\/+$/, '');
    if (filePath.startsWith('/download-file') || filePath.startsWith('download-file')) {
        return base + '/' + filePath.replace(/^\/+/, '');
    }
    let clean = filePath.replace(/^\/+/, '').replace(/^public\//, '');
    if (clean.startsWith('storage/')) {
        clean = clean.replace(/^storage\//, '');
    }
    return base + '/storage/' + clean;
};

const getDownloadUrl = (doc) => {
    if (!doc) return '#';
    const base = (page?.props?.base || '').replace(/\/+$/, '');
    if (doc.edms_file_id) {
        return `${base}/download-file?id=${doc.edms_file_id}`;
    }
    return getFileUrl(doc.file_path);
};

const openAttachmentPreview = (doc) => {
    if (!doc) return;
    activePreviewDoc.value = doc;
    quickPreviewModal.value = true;
};

const isPdfAttachment = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return type.includes('pdf') || name.endsWith('.pdf');
};

const isAudioAttachment = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return type.startsWith('audio/') || /\.(webm|mp3|wav|ogg|m4a|aac|flac)$/i.test(name);
};

const isImageAttachment = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return type.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg)$/i.test(name);
};

const isVideoAttachment = (doc) => {
    if (!doc) return false;
    const type = (doc.file_type || '').toLowerCase();
    const name = (doc.file_name || doc.file_path || '').toLowerCase();
    return type.startsWith('video/') || /\.(mp4|mov|avi|mkv)$/i.test(name);
};

// EDMS Document Helpers
const isEdmsAttachment = (doc) => {
    if (!doc) return false;
    if (doc.edms_file_id) return true;
    if (doc.file_path && (doc.file_path.includes('Archives') || doc.file_path.includes('/Archives/'))) return true;
    if (doc.metadata) {
        if (typeof doc.metadata === 'string') {
            try {
                const parsed = JSON.parse(doc.metadata);
                return Boolean(parsed.is_edms);
            } catch (e) {
                return false;
            }
        }
        return Boolean(doc.metadata.is_edms);
    }
    return false;
};

const getEdmsDocTitle = (doc) => {
    let meta = doc.metadata;
    if (typeof meta === 'string') {
        try { meta = JSON.parse(meta); } catch (e) { meta = {}; }
    }
    return meta?.title || doc.file_name || 'Repository Document';
};

const getEdmsDocNo = (doc) => {
    let meta = doc.metadata;
    if (typeof meta === 'string') {
        try { meta = JSON.parse(meta); } catch (e) { meta = {}; }
    }
    return meta?.doc_no || '';
};

const getEdmsDocClassification = (doc) => {
    let meta = doc.metadata;
    if (typeof meta === 'string') {
        try { meta = JSON.parse(meta); } catch (e) { meta = {}; }
    }
    return meta?.classification || 'Standard';
};

const getEdmsDocDepartment = (doc) => {
    let meta = doc.metadata;
    if (typeof meta === 'string') {
        try { meta = JSON.parse(meta); } catch (e) { meta = {}; }
    }
    return meta?.department || 'EDMS Repository';
};

const getEdmsDocIcon = (doc) => {
    const ext = (doc.file_type || doc.file_name || '').toLowerCase();
    if (ext.includes('pdf')) return 'vscode-icons:file-type-pdf2';
    if (ext.includes('doc') || ext.includes('word')) return 'vscode-icons:file-type-word';
    if (ext.includes('xls') || ext.includes('sheet') || ext.includes('csv')) return 'vscode-icons:file-type-excel';
    if (ext.includes('png') || ext.includes('jpg') || ext.includes('jpeg') || ext.includes('image')) return 'solar:gallery-bold-duotone';
    return 'solar:document-bold-duotone';
};

const getFileIcon = (doc) => {
    return getEdmsDocIcon(doc);
};

const toast = useToast();
const emit = defineEmits(["back", "refresh-archives"]);
const addLinkModal = ref(false);
const reportModal = ref(false);
const forwardModalVisible = ref(false);
const activeMessageDropdown = ref(null);
const showStarredDrawer = ref(false);
const highlightedMessageId = ref(null);
const showScrollBottom = ref(false);
const unreadSinceScroll = ref(0);
let typingTimer = null;

const handleDoubleClickMessage = (msg) => {
    if (!msg || msg.is_deleted) return;
    reactToMessage(msg, '❤️');
};

// Phase 3 State
const showEdmsPickerModal = ref(false);
const edmsAttachments = ref([]);
const showCreateTaskModal = ref(false);
const taskSourceMessage = ref(null);
const editingMessage = ref(null);

const canEditMessage = (message) => {
    if (!message || message.is_deleted) return false;
    const isSender = (message.sender_id === page.props.user.id) || (message.user?.id === page.props.user.id);
    if (!isSender) return false;
    if (!message.created_at) return true;
    const createdTime = new Date(message.created_at).getTime();
    const now = new Date().getTime();
    const diffMinutes = (now - createdTime) / (1000 * 60);
    return diffMinutes <= 15;
};

const startEditMessage = (message) => {
    editingMessage.value = message;
    formData.value.message = message.message || '';
    nextTick(() => {
        const textarea = document.querySelector('.p-inputtextarea');
        if (textarea) textarea.focus();
    });
};

const cancelEditMessage = () => {
    editingMessage.value = null;
    formData.value.message = '';
};

const openCreateTaskModal = (message) => {
    taskSourceMessage.value = message;
    showCreateTaskModal.value = true;
};

const onAttachEdmsDocuments = (docs) => {
    if (!docs || docs.length === 0) return;
    edmsAttachments.value = [...edmsAttachments.value, ...docs];
    toast.add({
        severity: 'info',
        summary: 'EDMS Document Added',
        detail: `${docs.length} document(s) ready to send`,
        life: 2500
    });
};

const removeEdmsAttachment = (index) => {
    edmsAttachments.value.splice(index, 1);
};

const updateEditedMessageLocally = (editedData) => {
    if (!editedData || !editedData.id) return;
    for (const dateKey in allMessages.value) {
        const day = allMessages.value[dateKey];
        if (day && day.messages) {
            const found = day.messages.find(m => Number(m.id) === Number(editedData.id));
            if (found) {
                found.message = editedData.message;
                found.is_edited = true;
                found.edited_at = editedData.edited_at;
                break;
            }
        }
    }
};

const getUniqueReactions = (reactions) => {
    if (!reactions) return [];
    return [...new Set(reactions.map(r => r.emoji))];
};

const toggleMessageDropdown = (msgId) => {
    if (activeMessageDropdown.value === msgId) {
        activeMessageDropdown.value = null;
    } else {
        activeMessageDropdown.value = msgId;
    }
};

const closeMessageDropdown = () => {
    activeMessageDropdown.value = null;
};

const reactToMessage = async (msg, emoji) => {
    try {
        const token = localStorage.getItem("token");
        const response = await axios.post(`/dashboard/chat/messages/${msg.id}/react`, 
            { is_group: props.is_group, emoji: emoji },
            { headers: { Authorization: `Bearer ${token}` } }
        );
        msg.reactions = response.data.reactions;
        closeMessageDropdown();
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to react', life: 3000 });
    }
};

const openForwardModal = (msg) => {
    forwardMessageData.value = msg;
    forwardModalVisible.value = true;
};

const optionsMenu = ref();
const showEmojiPicker = ref(false);

const toggleOptionsMenu = (event) => {
    optionsMenu.value.toggle(event);
};

const showExportModal = ref(false);
const isArchived = ref(false);

const checkArchiveStatus = async () => {
    const token = localStorage.getItem("token");
    try {
        let url = '';
        if (props.is_single && props.selectedUser) {
            url = `/dashboard/chat/archive-status?archived_user_id=${props.selectedUser.id}`;
        } else if (props.is_group && props.clickedIndexGroup) {
            url = `/dashboard/chat/archive-status?group_id=${props.clickedIndexGroup}&group_type=${props.groupType}`;
        } else {
            isArchived.value = false;
            return;
        }
        const res = await axios.get(url, { headers: { Authorization: `Bearer ${token}` } });
        isArchived.value = !!res.data.is_archived;
    } catch (e) {
        console.error('Failed to check archive status:', e);
    }
};

const toggleArchiveStatus = async () => {
    const token = localStorage.getItem("token");
    const willUnarchive = isArchived.value;
    const endpoint = willUnarchive ? "/dashboard/chat/users/unarchive" : "/dashboard/chat/users/archive";

    let payload = {};
    if (props.is_single && props.selectedUser) {
        payload.archived_user_id = props.selectedUser.id;
    } else if (props.is_group && props.clickedIndexGroup) {
        payload.group_id = props.clickedIndexGroup;
        payload.group_type = props.groupType;
    } else {
        return;
    }

    try {
        await axios.post(endpoint, payload, { headers: { Authorization: `Bearer ${token}` } });
        isArchived.value = !willUnarchive;
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: willUnarchive ? 'Conversation unarchived' : 'Conversation archived (read-only)',
            life: 3000
        });
        emit('refresh-archives');
    } catch (error) {
        console.error('Failed to toggle archive status:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to update archive status',
            life: 3000
        });
    }
};

const optionsMenuItems = computed(() => {
    const items = [
        {
            label: 'Starred Messages',
            icon: 'pi pi-star',
            command: () => {
                showStarredDrawer.value = true;
            }
        },
        {
            label: 'Export Transcript',
            icon: 'pi pi-download',
            command: () => {
                showExportModal.value = true;
            }
        }
    ];

    if (props.is_single) {
        items.splice(1, 0, {
            label: 'View Profile',
            icon: 'pi pi-user',
            command: () => {
                profile.value = true;
            }
        });

        items.push({
            label: isArchived.value ? 'Unarchive Chat' : 'Archive Chat',
            icon: isArchived.value ? 'pi pi-box' : 'pi pi-folder',
            command: toggleArchiveStatus
        });

        items.push({
            label: 'Block User',
            icon: 'pi pi-ban',
            command: async () => {
                if (!props.selectedUser) return;
                const token = localStorage.getItem("token");
                try {
                    await axios.post("/dashboard/chat/users/block", { blocked_user_id: props.selectedUser.id }, {
                        headers: { Authorization: `Bearer ${token}` }
                    });
                    toast.add({ severity: 'success', summary: 'Success', detail: 'User blocked successfully', life: 3000 });
                } catch (error) {
                    console.error(error);
                    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to block user', life: 3000 });
                }
            }
        });
    } else {
        items.push({
            label: isArchived.value ? 'Unarchive Group' : 'Archive Group',
            icon: isArchived.value ? 'pi pi-box' : 'pi pi-folder',
            command: toggleArchiveStatus
        });
    }

    return items;
});

const onSelectEmoji = (emojiStr) => {
    formData.value.message += emojiStr;
    showEmojiPicker.value = false;
};

const allMessages = ref([]);
const messagesContainer = ref(null);
const fileInput = ref(null);
const typingUser = ref(null);
const isTyping = ref(false);
const page = usePage();

const getUserProfileImage = (userOrMsg) => {
    if (userOrMsg && userOrMsg.profile_image) {
        return page.props.base + '/' + userOrMsg.profile_image;
    }
    if (userOrMsg && userOrMsg.sender && userOrMsg.sender.profile_image) {
        return page.props.base + '/' + userOrMsg.sender.profile_image;
    }
    return page.props.base + '/images/user.png';
};
const user = computed(() => page.props.user);
const files = ref([]);

const chatContainer = ref(null);
const currentPage = ref(1);
const totalPages = ref(null);
const isLoading = ref(false);
const isScrollTop = ref(false);
const lastPage = ref();
const is_content_loaded = ref(false);

const props = defineProps([
    "selectedMessage",
    "selectedUser",
    "clickedIndex",
    "clickedIndexGroup",
    "allMessagess",
    "groupType",
    "clickedIndexGroupName",
    "is_group",
    "is_single",
    "selectedGroup",
    "selectedMessageGroup",
]);
const currentClickedIndex = ref(props.clickedIndex);
const clickedIndexGroup = ref(props.clickedIndexGroup);
// const form = new FormData();
const default_scrollheight = ref(0);

import merge from "lodash/merge";

const formData = ref({
    message: "",
    recipient_id: props.clickedIndex,
    group_id: props.clickedIndexGroup,
});
const replyingTo = ref(null);
const searchQuery = ref('');
const currentSearchIndex = ref(0);

const pinnedMessages = computed(() => {
    const list = [];
    Object.values(allMessages.value || {}).forEach(dateObj => {
        (dateObj.messages || []).forEach(msg => {
            if (msg.is_pinned && !msg.is_deleted) {
                list.push(msg);
            }
        });
    });
    return list;
});

const starredMessages = computed(() => {
    const list = [];
    Object.values(allMessages.value || {}).forEach(dateObj => {
        (dateObj.messages || []).forEach(msg => {
            if (msg.is_starred && !msg.is_deleted) {
                list.push(msg);
            }
        });
    });
    return list;
});

const searchMatches = computed(() => {
    if (!searchQuery.value || searchQuery.value.trim() === '') return [];
    const q = searchQuery.value.toLowerCase();
    const matches = [];
    Object.values(allMessages.value || {}).forEach(dateObj => {
        (dateObj.messages || []).forEach(msg => {
            if (msg.message && msg.message.toLowerCase().includes(q) && !msg.is_deleted) {
                matches.push(msg.id);
            }
        });
    });
    return matches;
});

const jumpToMessage = (msgId) => {
    nextTick(() => {
        const el = document.getElementById(`msg-${msgId}`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            highlightedMessageId.value = msgId;
            setTimeout(() => {
                if (highlightedMessageId.value === msgId) {
                    highlightedMessageId.value = null;
                }
            }, 2500);
        } else {
            toast.add({ severity: 'info', summary: 'Info', detail: 'Message may be further up in history.', life: 3000 });
        }
    });
};

const onSearchInput = () => {
    currentSearchIndex.value = 0;
    if (searchMatches.value.length > 0) {
        jumpToMessage(searchMatches.value[0]);
    }
};

const jumpNextSearch = () => {
    if (searchMatches.value.length === 0) return;
    currentSearchIndex.value = (currentSearchIndex.value + 1) % searchMatches.value.length;
    jumpToMessage(searchMatches.value[currentSearchIndex.value]);
};

const jumpPrevSearch = () => {
    if (searchMatches.value.length === 0) return;
    currentSearchIndex.value = (currentSearchIndex.value - 1 + searchMatches.value.length) % searchMatches.value.length;
    jumpToMessage(searchMatches.value[currentSearchIndex.value]);
};

const clearSearch = () => {
    searchQuery.value = '';
    currentSearchIndex.value = 0;
};

const initiateReply = (message) => {
    replyingTo.value = message;
    document.querySelector('.p-inputtextarea').focus();
};

const deleteMessage = async (msg) => {
    try {
        const token = localStorage.getItem("token");
        await axios.delete(`/dashboard/chat/messages/${msg.id}`, {
            data: { is_group: props.is_group },
            headers: { Authorization: `Bearer ${token}` }
        });
        msg.is_deleted = true;
        msg.message = "This message was deleted";
        msg.attachments = [];
        toast.add({ severity: 'success', summary: 'Success', detail: 'Message deleted', life: 2000 });
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete message', life: 3000 });
    }
};
const cancelReply = () => {
    replyingTo.value = null;
};

const togglePinMessage = async (msg) => {
    try {
        const token = localStorage.getItem("token");
        const response = await axios.post(`/dashboard/chat/messages/${msg.id}/pin`, 
            { is_group: props.is_group },
            { headers: { Authorization: `Bearer ${token}` } }
        );
        msg.is_pinned = response.data.is_pinned;
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to pin message', life: 3000 });
    }
};

const toggleStarMessage = async (msg) => {
    try {
        const token = localStorage.getItem("token");
        const response = await axios.post(`/dashboard/chat/messages/${msg.id}/star`, 
            { is_group: props.is_group },
            { headers: { Authorization: `Bearer ${token}` } }
        );
        msg.is_starred = response.data.is_starred;
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to star message', life: 3000 });
    }
};

const copyText = (text) => {
    navigator.clipboard.writeText(text);
    toast.add({ severity: 'success', summary: 'Copied', detail: 'Message copied to clipboard', life: 2000 });
};
// watch(()=>props.allMessagess, (newMessages) => {
//     allMessages.value.push(newMessages);
// })
const sidebarRef = ref(null);
let lastTypingWhisper = 0;
const handleTyping = () => {
    const now = Date.now();
    if (now - lastTypingWhisper > 1500) {
        lastTypingWhisper = now;
        try {
            if (props.is_single && props.selectedUser?.id) {
                window.Echo?.private(`chat.${props.selectedUser.id}`)?.whisper('typing', {
                    sender_id: page.props.user.id,
                    name: page.props.user.first_name,
                });
            } else if (props.is_group && props.clickedIndexGroup && props.groupType) {
                window.Echo?.private(`group.${props.clickedIndexGroup}.${props.groupType}`)?.whisper('typing', {
                    sender_id: page.props.user.id,
                    name: page.props.user.first_name,
                });
            }
        } catch (err) {}
    }
};
// const listenForTyping = () => {
//     window.Echo.private(`chat.${currentClickedIndex.value}`)
//         .listen('typing', (event) => {
//             console.log("e dey type:", event);
//             // typingUser.value = event.user;
//             // isTyping.value = event.isTyping;
//         });
// };
onClickOutside(sidebarRef, (event) => {
    profile.value = false;
});
const triggerFileInput = () => {
    fileInput.value.click();
};

const profile = ref(false);
const activePage = ref(1);
const activeButton = ref(1);
// const files = [
//     {
//         icon: "vscode-icons:file-type-powerpoint",
//         name: "Auditing Report.ppt",
//     },
//     {
//         icon: "vscode-icons:file-type-word",
//         name: "Communication.docx",
//     },
//     {
//         icon: "vscode-icons:file-type-excel2",
//         name: "Analysis Report.xls",
//     },
//     {
//         icon: "vscode-icons:file-type-powerpoint",
//         name: "Auditing Report.ppt",
//     },
// ];

const getTotalOnlineUsers = (users) => {
    if (props.is_group) {
        if (!props.selectedGroup?.users) return 0;
        let found_users = props.selectedGroup.users.filter((item) => {
            return item.is_online == 1 || item.is_online === true;
        });

        return found_users ? found_users.length : 0;
    } else {
        if (users && (users.is_online == 1 || users.is_online === true)) {
            return true;
        } else {
            return false;
        }
    }
};

const totalUsers = (users) => {
    if (props.is_group) {
        return props.selectedGroup?.users?.length || 0;
    }
    return 0;
};

const allFiles = [];
const toggleMembers = () => {
    member_switch.value = !member_switch.value;
};
const member_switch = ref(false);
const members = [];

const menu = ref();
const menuItems = ref([
    { label: "Send Message", icon: "mdi:message-outline" },
    { label: "Make branch admin", icon: "material-symbols:shield-outline" },
    { label: "Remove Member", icon: "eva:slash-fill" },
    { label: "Report Contact", icon: "uiw:delete" },
]);
const returnLink = (linkData) => {
    if (
        !linkData?.link.startsWith("http://") &&
        !linkData?.link.startsWith("https://")
    ) {
        return `https://${linkData?.link}`;
    }
    return linkData?.link;
};
const fromLinkModel = (linkData) => {
    const linkHTML = `<a href="${returnLink(linkData)}" target="_blank">${
        linkData?.text
    }</a>`;

    formData.value.message += linkHTML;
};
const scrollToBottom = () => {
    nextTick(() => {
        const chatContainer = document.getElementById("chat-container");
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
};

const scrollToBottomSmooth = () => {
    nextTick(() => {
        const chatContainer = document.getElementById("chat-container");
        if (chatContainer) {
            chatContainer.scrollTo({
                top: chatContainer.scrollHeight,
                behavior: 'smooth'
            });
            showScrollBottom.value = false;
            unreadSinceScroll.value = 0;
        }
    });
};

const isRecording = ref(false);
const recordingDuration = ref(0);
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const recordingTimer = ref(null);

const formattedRecordingTime = computed(() => {
    const mins = Math.floor(recordingDuration.value / 60);
    const secs = recordingDuration.value % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
});

const toggleRecording = async () => {
    if (isRecording.value) {
        stopRecording();
    } else {
        await startRecording();
    }
};

let recordStartTime = 0;

const startRecording = async () => {
    try {
        // Stop any currently playing audio on the page to prevent acoustic feedback / echo
        document.querySelectorAll('audio, video').forEach(el => el.pause());

        const stream = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: { ideal: true },
                noiseSuppression: { ideal: true },
                autoGainControl: { ideal: true },
                channelCount: { ideal: 1 }, // Mono channel eliminates comb-filter hollow echo
                sampleRate: { ideal: 48000 },
            }
        });

        let mimeType = 'audio/webm;codecs=opus';
        if (!MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = 'audio/webm';
            if (!MediaRecorder.isTypeSupported(mimeType)) {
                mimeType = 'audio/mp4';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = '';
                }
            }
        }

        const options = mimeType ? { mimeType, audioBitsPerSecond: 128000 } : {};
        mediaRecorder.value = new MediaRecorder(stream, options);
        audioChunks.value = [];
        recordStartTime = Date.now();
        
        mediaRecorder.value.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                audioChunks.value.push(event.data);
            }
        };

        mediaRecorder.value.onstop = () => {
            const actualMime = mediaRecorder.value.mimeType || mimeType || 'audio/webm';
            const audioBlob = new Blob(audioChunks.value, { type: actualMime });
            const extension = actualMime.includes('mp4') ? 'mp4' : (actualMime.includes('ogg') ? 'ogg' : 'webm');
            const durationMs = Math.max(500, Date.now() - recordStartTime);
            const audioFile = new File([audioBlob], `audio_${Date.now()}_dur${durationMs}.${extension}`, { type: actualMime });
            
            const currentFiles = Array.isArray(files.value) ? files.value : Array.from(files.value || []);
            files.value = [...currentFiles, audioFile];
        };

        mediaRecorder.value.start(250);
        isRecording.value = true;
        recordingDuration.value = 0;
        recordingTimer.value = setInterval(() => {
            recordingDuration.value++;
        }, 1000);
    } catch (error) {
        console.error("Error accessing microphone:", error);
        toast.add({ severity: 'error', summary: 'Microphone Error', detail: 'Could not access microphone with clear audio settings.', life: 3000 });
    }
};

const stopRecording = () => {
    if (mediaRecorder.value && isRecording.value) {
        mediaRecorder.value.stop();
        if (mediaRecorder.value.stream) {
            mediaRecorder.value.stream.getTracks().forEach(track => track.stop());
        }
        isRecording.value = false;
        clearInterval(recordingTimer.value);
    }
};

const isDraggingOver = ref(false);
let dragCounter = 0;

const onDragEnter = (e) => {
    dragCounter++;
    if (e.dataTransfer && e.dataTransfer.types && Array.from(e.dataTransfer.types).includes('Files')) {
        isDraggingOver.value = true;
    }
};

const onDragOver = (e) => {
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'copy';
    }
};

const onDragLeave = (e) => {
    dragCounter--;
    if (dragCounter <= 0) {
        dragCounter = 0;
        isDraggingOver.value = false;
    }
};

const onDropFiles = (e) => {
    dragCounter = 0;
    isDraggingOver.value = false;
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const dropped = Array.from(e.dataTransfer.files);
        const current = Array.isArray(files.value) ? files.value : Array.from(files.value || []);
        files.value = [...current, ...dropped];
        toast.add({ severity: 'info', summary: 'Attached', detail: `${dropped.length} file(s) attached`, life: 2500 });
    }
};

const removeFile = (index) => {
    const current = Array.isArray(files.value) ? [...files.value] : Array.from(files.value || []);
    current.splice(index, 1);
    files.value = current;
};

const handleFileChange = (event) => {
    if (event.target && event.target.files) {
        const selected = Array.from(event.target.files);
        const current = Array.isArray(files.value) ? files.value : Array.from(files.value || []);
        files.value = [...current, ...selected];
    }
};

const formattedDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();

    // Compare the date with today's date
    if (date.toDateString() === today.toDateString()) {
        //return 'Today';
        return formattedDate(today);
    }

    // Check if the date is yesterday
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        //return 'Yesterday';
        return formattedDate(yesterday);
    }

    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}
watch(
    () => props.allMessagess,
    (newMessage) => {
        if (newMessage) {
            // Check if the message belongs to the currently selected user or group
            const isRelevant = props.is_group 
                ? (newMessage.group_id == props.clickedIndexGroup)
                : (newMessage.sender_id == props.clickedIndex || newMessage.recipient_id == props.clickedIndex);

            if (!isRelevant) return;

            const messageDate = formatDate(newMessage.created_at);

            if (allMessages.value[messageDate]) {
                allMessages.value[messageDate].messages.push(newMessage);

                allMessages.value[messageDate].messages = [
                    ...allMessages.value[messageDate].messages,
                ];
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [messageDate]: {
                        date: "",
                        messages: [newMessage],
                    },
                };
            }

            if (showScrollBottom.value) {
                unreadSinceScroll.value++;
            } else {
                scrollToBottom();
            }
        }
    },
    { deep: true }
);

const userConversations = () => {
    is_content_loaded.value = false;
    allMessages.value = {};
    checkArchiveStatus();

    if (props.selectedUser && props.is_single) {
        axios
            .get(`/dashboard/chat/usersConversation/${props.selectedUser.id}`)
            .then((response) => {
                allMessages.value = response.data.data;
                currentPage.value = response.data.pagination.current_page;
                totalPages.value = response.data.pagination.total;
                lastPage.value = response.data.pagination.last_page;
                scrollToBottom();
            });
    } else {
        axios
            .get(
                `/dashboard/chat/group/${props.clickedIndexGroup}/${props.groupType}`
            )
            .then((response) => {
                allMessages.value = response.data.data;
                // allMessages.value = response.data.data
                currentPage.value = response.data.pagination.current_page;
                totalPages.value = response.data.pagination.total;
                lastPage.value = response.data.pagination.last_page;
                scrollToBottom();
            });
    }
};

// const fetchMessages = async () => {
//     // const previousScrollHeight = chatContainer.value.scrollHeight;
//     // if (isLoading.value || currentPage.value > totalPages.value) return; // Don't load if already loading or pages are finished
//     // isLoading.value = true;

//     try {
//         currentPage.value += 1;
//         // is_content_loaded.value = true;

//         if (currentPage.value <= lastPage.value) {
//             if (props.selectedUser && props.is_single) {
//                 const response = await axios.get(`/dashboard/chat/usersConversation/${props.selectedUser.id}?page=${currentPage.value}`);

//                 //allMessages.value.push(response.data.data);
//                 //allMessages.value = allMessages.value, response.data.data};
//                 for (let item in response.data.data) {
//                     if (Object.keys(allMessages.value).includes(item)) {
//                         allMessages.value[item].messages = [...response.data.data[item].messages, ...allMessages.value[item].messages].sort((b, a) => new Date(a.created_at) - new Date(b.created_at))
//                     } else {
//                         allMessages.value = merge({}, allMessages.value, response.data.data)
//                     }
//                 }

//                 //allMessages.value = merge({}, allMessages.value, response.data.data)
//                 // currentPage.value = response.data.pagination.current_page
//                 // totalPages.value = response.data.pagination.total
//                 // lastPage.value = response.data.pagination.last_page

//             } else {
//                 axios.get(`/dashboard/chat/group/${props.clickedIndexGroup}/${props.groupType}?page=${currentPage.value}`).then((response) => {

//                     //allMessages.value = response.data.data
//                     // allMessages.value = response.data.data
//                     // for (let item in response.data.data) {
//                     //     if (Object.keys(allMessages.value).includes(item)) {
//                     //         allMessages.value[item].messages = [...response.data.data[item].messages, ...allMessages.value[item].messages].sort((b, a) => new Date(a.created_at) - new Date(b.created_at))
//                     //     } else {
//                     //         allMessages.value = merge({}, allMessages.value, response.data.data)
//                     //     }
//                     // }
//                     for (let item in response.data.data) {
//                         if (Object.keys(allMessages.value).includes(item)) {
//                             // If the date key already exists, add the new messages at the end and sort by created_at
//                             allMessages.value[item].messages = [
//                                 ...allMessages.value[item].messages,
//                                 ...response.data.data[item].messages
//                             ].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
//                         } else {
//                             // If the date key does not exist, append the new data at the end
//                             allMessages.value = {
//                                 ...allMessages.value, // Retain the existing data
//                                 [item]: response.data.data[item] // Append the new data with the new key at the end
//                             };
//                         }
//                     }

//                 })
//             }

//         }
//     } catch (error) {
//         console.error("Error fetching messages:", error);
//     } finally {
//         isLoading.value = false;
//     }
// };

const fetchMessages = async () => {
    if (isLoading.value || currentPage.value >= lastPage.value) return;

    isLoading.value = true;
    const container = chatContainer.value;
    const oldScrollHeight = container ? container.scrollHeight : 0;
    const oldScrollTop = container ? container.scrollTop : 0;

    try {
        const nextPage = currentPage.value + 1;
        let response;

        if (props.selectedUser && props.is_single) {
            response = await axios.get(
                `/dashboard/chat/usersConversation/${props.selectedUser.id}?page=${nextPage}`
            );
        } else {
            response = await axios.get(
                `/dashboard/chat/group/${props.clickedIndexGroup}/${props.groupType}?page=${nextPage}`
            );
        }

        currentPage.value = nextPage;
        lastPage.value = response.data.pagination.last_page;

        for (let item in response.data.data) {
            if (Object.keys(allMessages.value).includes(item)) {
                allMessages.value[item].messages = [
                    ...response.data.data[item].messages,
                    ...allMessages.value[item].messages,
                ].sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [item]: response.data.data[item],
                };
            }
        }

        // Maintain scroll anchor position
        nextTick(() => {
            if (container) {
                const heightDiff = container.scrollHeight - oldScrollHeight;
                container.scrollTop = oldScrollTop + heightDiff;
            }
        });
    } catch (error) {
        console.error("Error fetching messages:", error);
    } finally {
        isLoading.value = false;
    }
};

const onScroll = (event) => {
    const el = event.target;
    const scrollTop = el.scrollTop;
    const scrollHeight = el.scrollHeight;
    const clientHeight = el.clientHeight;

    if (scrollTop <= 30 && currentPage.value < lastPage.value && !isLoading.value) {
        fetchMessages();
    }

    const isNearBottom = scrollHeight - (scrollTop + clientHeight) < 180;
    showScrollBottom.value = !isNearBottom;
    if (isNearBottom) {
        unreadSinceScroll.value = 0;
    }
};

watch(
    () => props.selectedUser,
    () => {
        userConversations();
    },
    { immediate: true }
);

watch(
    [
        () => props.clickedIndex,
        () => props.clickedIndexGroup,
        () => props.is_group,
    ],
    ([newIndex, newGroup]) => {
        currentClickedIndex.value = newIndex;
        clickedIndexGroup.value = newGroup;
        // getMessagesInGroup();
        userConversations();
    }
);
const sendMessage = async () => {
    // If we are currently editing a message
    if (editingMessage.value) {
        if (!formData.value.message.trim()) return;
        const token = localStorage.getItem("token");
        try {
            const res = await axios.put(`/dashboard/chat/messages/${editingMessage.value.id}`, {
                message: formData.value.message,
                is_group: props.is_group
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            updateEditedMessageLocally(res.data.data);
            toast.add({ severity: 'success', summary: 'Updated', detail: 'Message edited successfully', life: 2500 });
            editingMessage.value = null;
            formData.value.message = "";
        } catch (err) {
            console.error(err);
            toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message || 'Failed to edit message', life: 4000 });
        }
        return;
    }

    if (formData.value.message.trim() === "" && files.value.length == 0 && edmsAttachments.value.length == 0) {
        return; // Do not send an empty message
    }
    let toCheck = "user";
    //const hasKey = toCheck in props.selectedMessage;

    const token = localStorage.getItem("token");
    is_content_loaded.value = false;
    const form = new FormData();

    if (props.is_single) {
        try {
            form.append("message", formData.value.message);
            form.append("recipient_id", props.selectedUser.id);
            if (replyingTo.value) {
                form.append("reply_to_id", replyingTo.value.id);
            }
            if (files.value && files.value.length > 0) {
                for (let i = 0; i < files.value.length; i++) {
                    form.append("files[]", files.value[i]);
                }
            }
            if (edmsAttachments.value && edmsAttachments.value.length > 0) {
                form.append("edms_attachments", JSON.stringify(edmsAttachments.value));
            }

            const responses = await axios.post(
                `/dashboard/chat/users/messages`,
                form,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${token}`,
                    },
                }
            );

            const newMessage = responses.data.sentMessage;
            const messageDate = formatDate(newMessage.created_at);
            if (allMessages.value[messageDate]) {
                allMessages.value[messageDate].messages.push(newMessage);
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [messageDate]: {
                        date: messageDate,
                        messages: [newMessage],
                    },
                };
            }
            scrollToBottom();
            formData.value.message = ""; files.value = []; edmsAttachments.value = [];
            replyingTo.value = null;
        } catch (error) {
            console.error(error);
            toast.add({
                severity: "error",
                summary: "Error",
                detail: error.response?.data?.message || "Failed to send message",
                life: 3000,
            });
        }
    } else {
        try {
            form.append("message", formData.value.message);
            form.append("group_id", clickedIndexGroup.value);
            form.append("groupType", props.groupType);
            form.append("groupName", props.clickedIndexGroupName);
            if (replyingTo.value) {
                form.append("reply_to_id", replyingTo.value.id);
            }
            if (files.value && files.value.length > 0) {
                for (let i = 0; i < files.value.length; i++) {
                    form.append("files[]", files.value[i]);
                }
            }
            if (edmsAttachments.value && edmsAttachments.value.length > 0) {
                form.append("edms_attachments", JSON.stringify(edmsAttachments.value));
            }

            const responses = await axios.post(
                `/dashboard/chat/group/send-message`,
                form,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${token}`,
                    },
                }
            );

            const newMessage = responses.data.sentMessage;
            const messageDate = formatDate(newMessage.created_at);
            if (allMessages.value[messageDate]) {
                allMessages.value[messageDate].messages.push(newMessage);
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [messageDate]: {
                        date: messageDate,
                        messages: [newMessage],
                    },
                };
            }
            scrollToBottom();
            formData.value.message = ""; files.value = []; edmsAttachments.value = [];
            replyingTo.value = null;
        } catch (error) {
            console.error(error);
            toast.add({
                severity: "error",
                summary: "Error",
                detail: error.response?.data?.message || "Failed to send message",
                life: 3000,
            });
        }
    }
};

const sortedMessages = computed(() => {
    const messageDates = Object.keys(allMessages.value);

    const sortedDates = messageDates.sort((a, b) => new Date(a) - new Date(b));
    let result = sortedDates.map((date) => {
        return {
            date: allMessages.value[date].date,
            messages: allMessages.value[date].messages.sort(
                (a, b) => new Date(a.created_at) - new Date(b.created_at)
            ),
        };
    });

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.map(day => ({
            ...day,
            messages: day.messages.filter(msg => 
                msg.message && msg.message.toLowerCase().includes(query)
            )
        })).filter(day => day.messages.length > 0);
    }

    return result;
});

const handleScroll = (event) => {
    const scrollTop = event.target.scrollTop;
    const scrollHeight = event.target.scrollHeight;
    const clientHeight = event.target.clientHeight;

    // Check if user scrolled to the bottom
    if (scrollTop + clientHeight >= scrollHeight) {
        console.log("Reached the bottom");
    }
};

const handleMessagesReadEvent = (event) => {
    const detail = event?.detail;
    if (!detail) return;
    if (props.selectedUser && Number(props.selectedUser.id) === Number(detail.reader_id)) {
        Object.values(allMessages.value || {}).forEach(dateGroup => {
            if (dateGroup && Array.isArray(dateGroup.messages)) {
                dateGroup.messages.forEach(msg => {
                    if (Number(msg.sender_id) === Number(page.props.user.id)) {
                        msg.is_read = true;
                    }
                });
            }
        });
    }
};

const handleMessageEditedEvent = (event) => {
    const detail = event?.detail;
    if (detail) {
        updateEditedMessageLocally(detail);
    }
};

onMounted(() => {
    scrollToBottom();
    window.addEventListener("chat-messages-read", handleMessagesReadEvent);
    window.addEventListener("chat-message-edited", handleMessageEditedEvent);

    // Whisper typing listener
    try {
        window.Echo.private(`chat.${page.props.user.id}`).listenForWhisper('typing', (event) => {
            if (props.is_single && Number(event?.sender_id) === Number(props.clickedIndex)) {
                isTyping.value = true;
                typingUser.value = { name: event.name || 'User' };
                if (typingTimer) clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    isTyping.value = false;
                }, 2500);
            }
        });
    } catch (e) {}
});
onBeforeUnmount(() => {
    window.Echo.leave(`chat.${currentClickedIndex}`);
    window.removeEventListener("chat-messages-read", handleMessagesReadEvent);
    window.removeEventListener("chat-message-edited", handleMessageEditedEvent);
    if (typingTimer) clearTimeout(typingTimer);
});
// onUpdated(() => {
//     if (!isScrollTop.value) {
//         //scrollToBottom();
//     }
//     // onScroll()
// });
watch(sortedMessages, (newMessages, oldMessages) => {
    if (newMessages.length !== oldMessages.length) {
        if (!isScrollTop.value && !is_content_loaded.value) {
            // scrollToBottom();
        }
    }
});
const toggle = (event) => {
    menu.value.toggle(event);
};
const handleMemberOption = (item) => {
    if (item === "Report Contact") {
        reportModal.value = true;
    }
};

// const { reset } = useInfiniteScroll(
//     chatContainer,
//     () => {
//         fetchMessages()
//     },
//     { distance: 10, direction: 'top' },
// )

// function resetList() {
//     data.value = []
//     reset()
// }
</script>

<style>
@import 'vue3-emoji-picker/css';
</style>

<style scoped>
.p-inputtextarea {
    border: none;
    outline: none;
    box-shadow: none !important;
    resize: none;
    width: 100%;
}

.p-inputtextarea:focus {
    border-color: transparent;
    outline: none;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: 0.4s;
    transition: 0.4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: 0.4s;
    transition: 0.4s;
}

input:checked + .slider {
    background-color: #2196f3;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196f3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>
