<template>

    <main class="flex h-[91vh] overflow-hidden bg-white">

        <!-- Left Sidebar - Branch Groups/Direct Messages -->

        <section

            class="w-full lg:w-[25%] border-r border-solid border-gray-100 top-0 h-[100%] py-4 flex flex-col transition-all duration-300 bg-white"

            :class="{ 'hidden lg:block': open }"

            v-if="activeButton === 0"

        >

            <div class="px-3">

                <div

                    class="flex items-center justify-between text-[#1D2939] text-2xl lg:text-3xl font-semibold"

                >

                    <h1 class="">Chats</h1>

                </div>



                <div

                    class="flex items-center justify-between mt-5 w-full gap-2"

                >

                    <div

                        class="flex items-center flex-1 lg:w-[85%] rounded-full px-4 py-1 bg-gray-50 border border-gray-200 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all shadow-sm"

                    >

                        <Icon

                            icon="lucide:search"

                            class="text-lg text-gray-400"

                        />

                        <input

                            type="text"

                            v-model="filter_users"

                            @input="filterRecipients"

                            placeholder="Search Users"

                            class="outline-none h-full p-2 bg-transparent w-full text-sm placeholder-gray-400"

                        />

                    </div>

                    <div class="mr-1 flex-none flex items-center gap-1">

                        <button

                            type="button"

                            @click="showGlobalSearchModal = true"

                            class="p-2 hover:bg-blue-50 rounded-full text-primary transition-all border border-transparent hover:border-blue-200"

                            title="Global Chat Search (Ctrl+K)"

                        >

                            <Icon icon="solar:minimalistic-magnifer-zoom-in-bold-duotone" class="text-xl" />

                        </button>

                        <Icon

                            icon="ion:filter-sharp"

                            width="28"

                            class="lg:w-[32px] text-gray-500 cursor-pointer"

                        />

                    </div>

                </div>

            </div>



            <!-- Modern 4-Tab Navigation Strip -->

            <div class="mt-4 px-2.5">

                <div class="grid grid-cols-4 gap-1 p-1 bg-gray-100/90 rounded-2xl border border-gray-200/60 shadow-2xs">

                    <button

                        v-for="(tab, tIndex) in tabItems"

                        :key="tIndex"

                        type="button"

                        @click="active = tIndex"

                        class="relative flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 py-2 px-1 rounded-xl text-xs font-semibold transition-all duration-200"

                        :class="active === tIndex

                            ? 'bg-white text-primary shadow-xs font-bold ring-1 ring-black/5'

                            : 'text-gray-500 hover:text-gray-900 hover:bg-white/50'"

                        :title="tab.title"

                    >

                        <Icon :icon="tab.icon" class="text-base sm:text-lg flex-shrink-0" />

                        <span class="truncate hidden sm:inline text-xs">{{ tab.label }}</span>

                        <!-- Live Counter Badge -->

                        <span

                            v-if="tab.badge && tab.badge > 0"

                            class="absolute -top-1 -right-1 sm:static sm:top-auto sm:right-auto bg-primary text-white text-[10px] font-extrabold px-1.5 py-0.2 min-w-[18px] text-center rounded-full shadow-2xs"

                        >

                            {{ tab.badge > 99 ? '99+' : tab.badge }}

                        </span>

                    </button>

                </div>

            </div>



            <!-- =============    Direct Messages (Tab 0)   =========== -->

            <div class="" v-if="active === 0">

                <!-- Quick Filter Pills -->

                <div class="flex items-center gap-1.5 px-3 mt-3 mb-2 overflow-x-auto hide_scroll py-0.5">
                    <button
                        type="button"
                        @click="activeDirectFilter = 'all'"
                        class="text-xs px-2.5 py-1 rounded-full transition-all duration-150 flex items-center gap-1 font-medium flex-shrink-0 cursor-pointer"
                        :class="activeDirectFilter === 'all'
                            ? 'bg-primary text-white shadow-xs font-semibold'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        <span>All</span>
                        <span class="text-[10px] opacity-80">({{ users.length }})</span>
                    </button>


                    <button
                        type="button"
                        @click="activeDirectFilter = 'unread'"
                        class="text-xs px-2.5 py-1 rounded-full transition-all duration-150 flex items-center gap-1 font-medium flex-shrink-0 cursor-pointer"
                        :class="activeDirectFilter === 'unread'
                            ? 'bg-primary text-white shadow-xs font-semibold'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        <span>Unread</span>
                        <span v-if="unreadDirectUsersCount > 0" class="text-[10px] bg-white text-primary font-bold px-1 rounded-full">
                            {{ unreadDirectUsersCount }}
                        </span>
                        <span v-else class="text-[10px] opacity-80">(0)</span>
                    </button>

                    <button
                        type="button"
                        @click="activeDirectFilter = 'online'"
                        class="text-xs px-2.5 py-1 rounded-full transition-all duration-150 flex items-center gap-1 font-medium flex-shrink-0 cursor-pointer"
                        :class="activeDirectFilter === 'online'
                            ? 'bg-emerald-600 text-white shadow-xs font-semibold'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        <span>Online</span>
                        <span class="text-[10px] opacity-80">({{ onlineDirectUsersCount }})</span>
                    </button>
                </div>



                <div class="overflow-scroll h-[56vh] lg:h-[62vh] pb-10 hide_scroll">

                    <div

                        v-for="(item, index) in displayedDirectUsers"

                        :key="item.id || index"

                        class="cursor-pointer"

                        @click="toggleBackgroundUser(item)"

                    >

                        <div

                            class="flex items-center py-2.5 px-3 mx-2 my-1 rounded-2xl transition-all duration-200 hover:bg-gray-50/80"

                            :class="{

                                ' bg-blue-50/90 shadow-xs border border-blue-100': clickedIndex === item.id,

                            }"

                        >

                            <div class="flex items-center gap-3 w-full">

                                <div class="relative flex-none">

                                    <img :src="getUserImage(item)" alt="" class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-xs" />

                                    <span v-if="isUserOnline(item)" class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full ring-1 ring-emerald-500/20"></span>

                                </div>

                                <div class="flex flex-col flex-1 min-w-0 pr-1">

                                    <div class="flex items-center justify-between">
                                        <h1 class="font-semibold text-sm text-gray-900 truncate">
                                            {{ item.first_name }} {{ item.last_name || '' }}
                                        </h1>
                                        <span v-if="item.last_message_time" class="text-[11px] text-gray-400 font-normal ml-1 flex-shrink-0">
                                            {{ item.last_message_time }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between mt-0.5">

                                        <p class="text-xs text-gray-500 truncate max-w-[170px]" :class="{'font-semibold text-gray-900': item.unread_count > 0}">

                                            <span v-if="typingUsersMap[item.id]" class="text-primary font-medium italic animate-pulse flex items-center gap-1">

                                                <Icon icon="solar:pen-new-square-bold-duotone" class="text-xs" />

                                                typing...

                                            </span>

                                            <template v-else>

                                                <span v-if="item.last_message_sender_id === user.id" class="text-gray-400">You: </span>

                                                {{ item.last_message || 'Start conversation' }}

                                            </template>

                                        </p>

                                        <span v-if="item.unread_count > 0" class="ml-1.5 flex-none bg-primary text-white text-[11px] font-bold px-1.5 py-0.5 min-w-[18px] text-center rounded-full shadow-xs animate-pulse">

                                            {{ item.unread_count }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div v-if="displayedDirectUsers.length === 0" class="py-12 text-center text-xs text-gray-400 px-4">
                        <Icon icon="solar:user-cross-linear" class="text-3xl text-gray-300 mx-auto mb-2" />
                        <span v-if="filter_users">No contacts matching "{{ filter_users }}"</span>
                        <span v-else-if="activeDirectFilter === 'unread'">No unread messages</span>
                        <span v-else-if="activeDirectFilter === 'online'">No colleagues currently online</span>
                        <span v-else>No direct conversations</span>
                    </div>

                </div>

            </div>



            <!-- ======================  Branch Group (Tab 1) ============="" -->

            <div v-else-if="active === 1">
                <div class="px-3 pt-3 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Organizational Channels</span>
                    <span class="text-[11px] text-primary bg-blue-50 px-2 py-0.5 rounded-full font-semibold">Directorates & Units</span>
                </div>

                <div
                    class="overflow-scroll h-[52vh] lg:h-[58vh] pb-20 hide_scroll px-3 pr-4 mt-2"
                >
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-3 justify-between"
                    >
                        <div
                            v-for="(item, index) in userGroups"
                            :key="index"
                            class=""
                            @click="handleGroups(item, index)"
                        >
                            <div
                                class="flex items-center justify-center flex-col p-4 cursor-pointer rounded-2xl bg-white border border-gray-100 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 group relative"
                            >
                                <div
                                    class="text-primary bg-blue-50 group-hover:bg-primary transition-all duration-300 p-3.5 w-fit rounded-2xl shadow-2xs group-hover:scale-105 flex items-center justify-center cursor-pointer"
                                >
                                    <img
                                        :src="getImageForGroup(index)"
                                        alt=""
                                        class="w-6 h-6 transition-all duration-300 group-hover:brightness-0 group-hover:invert"
                                    />
                                </div>
                                <p
                                    class="mt-2 font-bold text-sm capitalize text-gray-800 group-hover:text-primary transition-colors text-center"
                                >
                                    {{ index }}
                                </p>
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <span class="text-xs text-gray-500 font-medium">
                                        {{ (item && item.length > 0) ? item.length : 0 }} Channels
                                    </span>
                                </div>


                                <span class="mt-2 text-[10px] font-semibold text-primary bg-blue-50/80 group-hover:bg-primary group-hover:text-white px-2.5 py-0.5 rounded-full border border-blue-100/60 transition-colors">
                                    Browse Rooms &rarr;
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!userGroups || Object.keys(userGroups).length === 0" class="py-12 text-center text-xs text-gray-400">
                        <Icon icon="solar:users-group-two-rounded-linear" class="text-3xl text-gray-300 mx-auto mb-2" />
                        <span>No channels found.</span>
                    </div>
                </div>
            </div>



            <!-- =============    Contacts & Requests (Tab 2)    =========== -->

            <div class="" v-else-if="active === 2">

                <div class="px-3 pt-3 mb-2 flex justify-between items-center">

                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Directory Discovery</span>

                    <span v-if="chatRequests.length > 0" class="text-[10px] font-bold bg-amber-500 text-white px-2 py-0.5 rounded-full">

                        {{ chatRequests.length }} Pending

                    </span>

                </div>

                

                <div class="px-3 mb-3 relative">

                    <div class="flex items-center rounded-full px-4 py-1.5 bg-gray-50 border border-gray-200 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all">

                        <Icon icon="lucide:search" class="text-lg text-gray-400 flex-shrink-0" />

                        <input

                            type="text"

                            v-model="personalSearch"

                            @input="searchPersonalUsers"

                            placeholder="Search colleagues to connect..."

                            class="outline-none h-full p-1.5 bg-transparent w-full text-xs text-gray-800 placeholder-gray-400"

                        />

                        <button v-if="personalSearch" @click="personalSearch = ''; searchResults = []" class="p-0.5 text-gray-400 hover:text-gray-600">

                            <Icon icon="lucide:x" class="text-xs" />

                        </button>

                    </div>

                </div>



                <!-- Live Search Results Dropdown/Box -->

                <div v-if="personalSearch && searchResults.length > 0" class="mx-3 mb-3 p-2 bg-white rounded-2xl border border-blue-100 shadow-md max-h-[28vh] overflow-y-scroll hide_scroll">

                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 py-1">Matched Colleagues</div>

                    <div v-for="res in searchResults" :key="res.id" class="px-2.5 py-2 flex items-center justify-between hover:bg-gray-50 rounded-xl transition-colors">

                        <div class="flex items-center gap-2.5 min-w-0 pr-2">

                            <img :src="getUserImage(res)" class="w-8 h-8 rounded-full object-cover flex-none border border-gray-100" />

                            <div class="truncate">

                                <h4 class="text-xs font-bold text-gray-900 truncate">{{ res.first_name }} {{ res.last_name }}</h4>

                                <p class="text-[10px] text-gray-400 truncate">{{ res.department?.name || res.email || 'Colleague' }}</p>

                            </div>

                        </div>

                        <button

                            @click="sendRequest(res.id)"

                            class="text-xs bg-primary hover:bg-blue-700 text-white px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1 shadow-2xs transition-colors flex-shrink-0"

                        >

                            <Icon icon="solar:user-plus-rounded-bold" class="text-xs" />

                            Connect

                        </button>

                    </div>

                </div>



                <!-- Pending Requests Cards (Redesigned) -->

                <div v-if="chatRequests.length > 0" class="mx-3 mb-3 p-3 bg-amber-50/90 rounded-2xl border border-amber-200/80 shadow-2xs">

                    <div class="flex items-center justify-between mb-2">

                        <div class="flex items-center gap-1.5 text-amber-900 font-bold text-xs">

                            <Icon icon="solar:bell-bing-bold-duotone" class="text-amber-600 text-sm" />

                            <span>Pending Invitations ({{ chatRequests.length }})</span>

                        </div>

                    </div>

                    <div class="space-y-2">

                        <div

                            v-for="req in chatRequests"

                            :key="req.id"

                            class="bg-white p-2.5 rounded-xl border border-amber-100 shadow-2xs flex items-center justify-between gap-2"

                        >

                            <div class="flex items-center gap-2.5 min-w-0">

                                <img :src="getUserImage(req.sender)" class="w-8 h-8 rounded-full object-cover border border-gray-100 flex-shrink-0" />

                                <div class="truncate">

                                    <h4 class="text-xs font-bold text-gray-900 truncate">

                                        {{ req.sender?.first_name }} {{ req.sender?.last_name }}

                                    </h4>

                                    <p class="text-[10px] text-gray-400 truncate">

                                        {{ req.sender?.department?.name || req.sender?.roles?.[0]?.name || 'Colleague' }}

                                    </p>

                                </div>

                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">

                                <button

                                    @click="acceptRequest(req.id)"

                                    class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-2.5 py-1 rounded-lg shadow-2xs flex items-center gap-1 transition-colors"

                                    title="Accept Request"

                                >

                                    <Icon icon="lucide:check" class="text-xs" />

                                    Accept

                                </button>

                                <button

                                    @click="declineRequest(req.id)"

                                    class="text-xs bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-600 border border-gray-200 font-semibold px-2 py-1 rounded-lg transition-colors"

                                    title="Decline Request"

                                >

                                    <Icon icon="lucide:x" class="text-xs" />

                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Personal Contacts List -->

                <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between">

                    <span>Connected Contacts</span>

                    <span class="text-[10px] font-normal text-gray-400">({{ personalContacts.length }})</span>

                </div>

                <div class="overflow-scroll h-[40vh] lg:h-[48vh] pb-10 hide_scroll">

                    <div v-for="(contact, index) in personalContacts" :key="index" class="cursor-pointer" @click="toggleBackgroundUser(contact)">

                        <div class="flex items-center py-2.5 px-3 mx-2 my-1 rounded-2xl transition-all duration-200 hover:bg-gray-50/80"

                             :class="{' bg-blue-50/90 shadow-xs border border-blue-100': clickedIndex === contact.id}">

                            <div class="flex items-center gap-3 w-full">

                                <div class="relative flex-none">

                                    <img :src="getUserImage(contact)" class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-xs" />

                                    <span v-if="isUserOnline(contact)" class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full ring-1 ring-emerald-500/20"></span>

                                </div>

                                <div class="flex flex-col flex-1 min-w-0 pr-1">

                                    <div class="flex items-center justify-between">

                                        <h1 class="font-semibold text-sm text-gray-900 truncate">

                                            {{ contact.first_name }} {{ contact.last_name || '' }}

                                        </h1>

                                        <span v-if="contact.last_message_time" class="text-[11px] text-gray-400 font-normal ml-1 flex-shrink-0">

                                            {{ contact.last_message_time }}

                                        </span>

                                    </div>

                                    <div class="flex items-center justify-between mt-0.5">

                                        <p class="text-xs text-gray-500 truncate max-w-[170px]" :class="{'font-semibold text-gray-900': contact.unread_count > 0}">

                                            <span v-if="typingUsersMap[contact.id]" class="text-primary font-medium italic animate-pulse flex items-center gap-1">

                                                <Icon icon="solar:pen-new-square-bold-duotone" class="text-xs" />

                                                typing...

                                            </span>

                                            <template v-else>

                                                <span v-if="contact.last_message_sender_id === user.id" class="text-gray-400">You: </span>

                                                {{ contact.last_message || 'Start conversation' }}

                                            </template>

                                        </p>

                                        <span v-if="contact.unread_count > 0" class="ml-1.5 flex-none bg-primary text-white text-[11px] font-bold px-1.5 py-0.5 min-w-[18px] text-center rounded-full shadow-xs animate-pulse">

                                            {{ contact.unread_count }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div v-if="personalContacts.length === 0" class="py-10 text-center text-xs text-gray-400 px-4">

                        <Icon icon="solar:users-group-rounded-linear" class="text-3xl text-gray-300 mx-auto mb-2" />

                        <span>No personal contacts yet. Search for colleagues above to connect!</span>

                    </div>

                </div>

            </div>



            <!-- =============    Archived Chats (Tab 3)   =========== -->

            <div class="" v-else-if="active === 3">

                <div class="px-3 pt-3 mb-2 flex justify-between items-center">

                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Archived Conversations</span>

                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">

                        {{ filteredArchivedChats.length + filteredArchivedGroups.length }} Archived

                    </span>

                </div>



                <!-- In-Archive Search Filter -->

                <div class="px-3 mb-3 relative">

                    <div class="flex items-center rounded-full px-4 py-1.5 bg-gray-50 border border-gray-200 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all">

                        <Icon icon="lucide:search" class="text-lg text-gray-400 flex-shrink-0" />

                        <input

                            type="text"

                            v-model="archiveSearchQuery"

                            placeholder="Filter archived chats..."

                            class="outline-none h-full p-1.5 bg-transparent w-full text-xs text-gray-800 placeholder-gray-400"

                        />

                        <button v-if="archiveSearchQuery" @click="archiveSearchQuery = ''" class="p-0.5 text-gray-400 hover:text-gray-600">

                            <Icon icon="lucide:x" class="text-xs" />

                        </button>

                    </div>

                </div>

                

                <div class="overflow-scroll h-[48vh] lg:h-[62vh] pb-10 hide_scroll">

                    <div v-if="filteredArchivedChats.length === 0 && filteredArchivedGroups.length === 0" class="text-center py-12 px-4">

                        <Icon icon="solar:archive-linear" class="text-4xl text-gray-300 mx-auto mb-2" />

                        <p class="text-xs text-gray-400">

                            {{ archiveSearchQuery ? `No archived chats match "${archiveSearchQuery}"` : 'No archived conversations found.' }}

                        </p>

                    </div>



                    <!-- Archived Direct Chats -->

                    <div v-if="filteredArchivedChats.length > 0" class="mb-4">

                        <div class="px-4 py-1 text-[11px] font-semibold text-gray-400 uppercase tracking-wider flex items-center justify-between">

                            <span>Direct Chats</span>

                            <span class="text-[10px]">({{ filteredArchivedChats.length }})</span>

                        </div>

                        <div v-for="(contact, index) in filteredArchivedChats" :key="'archived-user-' + contact.id" class="cursor-pointer" @click="toggleBackgroundUser(contact)">

                            <div class="flex items-center py-2.5 px-3 mx-2 my-1 rounded-2xl transition-all duration-200 hover:bg-gray-50"

                                 :class="{' bg-blue-50 shadow-sm border border-blue-100': clickedIndex === contact.id}">

                                <div class="flex items-center gap-3 w-full">

                                    <img :src="getUserImage(contact)" class="flex-none w-9 h-9 rounded-full object-cover border border-gray-100" />

                                    <div class="flex items-center justify-between w-full min-w-0">

                                        <div class="truncate mr-2">

                                            <h1 class="font-semibold text-sm truncate">{{ contact.first_name }} {{ contact.last_name }}</h1>

                                            <p class="text-xs text-gray-400">Archived &bull; Read-only</p>

                                        </div>

                                        <button @click.stop="unarchiveChat(contact.id)" class="text-xs text-primary font-semibold hover:underline flex-shrink-0 px-2 py-1 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg">Unarchive</button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- Archived Groups -->

                    <div v-if="filteredArchivedGroups.length > 0">

                        <div class="px-4 py-1 text-[11px] font-semibold text-gray-400 uppercase tracking-wider flex items-center justify-between">

                            <span>Group Channels</span>

                            <span class="text-[10px]">({{ filteredArchivedGroups.length }})</span>

                        </div>

                        <div v-for="(grp, index) in filteredArchivedGroups" :key="'archived-grp-' + grp.id" class="cursor-pointer" @click="openArchivedGroup(grp)">

                            <div class="flex items-center py-2.5 px-3 mx-2 my-1 rounded-2xl transition-all duration-200 hover:bg-gray-50"

                                 :class="{' bg-blue-50 shadow-sm border border-blue-100': clickedIndexGroup === grp.id}">

                                <div class="flex items-center gap-3 w-full">

                                    <div class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center text-primary flex-none">

                                        <Icon icon="solar:users-group-two-rounded-bold-duotone" class="text-lg" />

                                    </div>

                                    <div class="flex items-center justify-between w-full min-w-0">

                                        <div class="truncate mr-2">

                                            <h1 class="font-semibold text-sm truncate">{{ grp.name }}</h1>

                                            <p class="text-xs text-gray-400 capitalize">{{ grp.group_type }} &bull; Archived</p>

                                        </div>

                                        <button @click.stop="unarchiveGroup(grp)" class="text-xs text-primary font-semibold hover:underline flex-shrink-0 px-2 py-1 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg">Unarchive</button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        <!-- Middle Sidebar - Group List -->

        <section

            class="w-full lg:w-[25%] border-r border-solid border-gray-200 top-0 h-[100%] py-3 transition-all duration-300"

            :class="{ 'hidden lg:block': open }"

            v-if="activeButton === 1"

        >

            <div class="flex items-center gap-3 px-3">

                <div

                    class="p-2 border rounded-xl hover:bg-gray-100 cursor-pointer transition-colors"

                    @click="activeButton = 0"

                >

                    <Icon

                        icon="material-symbols:arrow-back"

                        width="20"

                        class="text-gray-700"

                    />

                </div>

                <div class="flex items-center gap-2">

                    <h1 class="font-bold text-xl lg:text-2xl capitalize text-gray-900">

                        {{ groupType }}

                    </h1>

                    <span

                        class="text-xs font-semibold bg-blue-50 text-primary border border-blue-100 px-2 py-0.5 rounded-full"

                        v-if="selectedBranchGroup"

                    >

                        {{ selectedBranchGroup?.length }} rooms

                    </span>

                </div>

            </div>



            <div

                class="flex items-center border mx-3 my-4 border-gray-200 rounded-full px-3.5 py-1 bg-gray-50 focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all"

            >

                <Icon icon="lucide:search" class="text-base text-gray-400 flex-shrink-0" />

                <input

                    type="text"

                    v-model="filter_groups"

                    :placeholder="'Search ' + groupType + ' channels...'"

                    class="outline-none h-full p-1.5 bg-transparent w-full text-xs text-gray-800 placeholder-gray-400"

                />

                <button v-if="filter_groups" @click="filter_groups = ''" class="p-0.5 text-gray-400 hover:text-gray-600">

                    <Icon icon="lucide:x" class="text-xs" />

                </button>

            </div>



            <div

                v-if="groups && groups.length > 0"

                class="overflow-scroll h-[70vh] pb-10 hide_scroll"

            >

                <div

                    v-for="(item, index) in filterGroups"

                    :key="index"

                    class="my-1 mx-2"

                >

                    <div

                        class="flex items-center py-2.5 px-3 justify-between w-full rounded-2xl transition-all duration-200 cursor-pointer hover:bg-blue-50/60 group/channel border border-transparent"

                        :class="{

                            'bg-blue-50/90 border-blue-200 shadow-2xs': open && item.id == groupIndex,

                        }"

                    >

                        <div

                            class="flex items-center gap-3 w-full min-w-0"

                            @click="toggleGroup(item)"

                        >

                            <div

                                class="text-primary border border-gray-200 bg-blue-50/60 group-hover/channel:bg-primary group-hover/channel:border-primary p-2 rounded-xl overflow-hidden flex-none transition-all duration-200 shadow-2xs group-hover/channel:scale-105"

                            >

                                <img

                                    :src="current_img"

                                    alt=""

                                    class="w-5 h-5 transition-all duration-200 group-hover/channel:brightness-0 group-hover/channel:invert"

                                />

                            </div>

                            <div

                                class="flex items-center justify-between w-full min-w-0 pr-1"

                            >

                                <div class="truncate">
                                    <h1
                                        class="font-semibold text-sm lg:text-base text-gray-900 truncate"
                                    >
                                        {{ item.title }}
                                    </h1>

                                    <button
                                        type="button"
                                        @click.stop="toggleGroupUsers(item)"
                                        class="text-xs text-gray-500 hover:text-primary transition-colors flex items-center gap-1 cursor-pointer mt-0.5"
                                        :title="toggleUsers && groupIndex == item.id ? 'Collapse members' : 'Expand members'"
                                    >
                                        <Icon icon="solar:users-group-two-rounded-linear" class="text-xs text-gray-400" />
                                        <span>{{ getChannelMembers(item).length }} Members</span>
                                        <Icon :icon="toggleUsers && groupIndex == item.id ? 'lucide:chevron-up' : 'lucide:chevron-down'" class="text-[10px]" />
                                    </button>
                                </div>

                            </div>

                        </div>



                        <button

                            type="button"

                            class="p-1.5 border border-gray-200 rounded-xl hover:bg-primary hover:text-white hover:border-primary transition-all duration-200 cursor-pointer flex items-center justify-center flex-shrink-0"

                            :class="{ 'bg-primary text-white border-primary shadow-xs': toggleUsers && groupIndex == item.id, 'text-gray-500 bg-white': !(toggleUsers && groupIndex == item.id) }"

                            @click.stop="toggleGroupUsers(item)"

                            :title="toggleUsers && groupIndex == item.id ? 'Collapse members' : 'View members'"

                        >

                            <Icon

                                :icon="toggleUsers && groupIndex == item.id ? 'solar:alt-arrow-down-linear' : 'solar:alt-arrow-right-linear'"

                                class="text-base transition-transform"

                            />

                        </button>

                    </div>



                    <!-- Members Expand Area -->

                    <div v-if="toggleUsers && groupIndex == item.id" class="my-2 mx-1 bg-gray-50/80 border border-gray-200/70 rounded-2xl p-2.5 space-y-1">

                        <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">

                            <span>Channel Members</span>

                            <span class="text-primary font-bold">{{ getChannelMembers(item).length }}</span>

                        </div>



                        <div v-if="getChannelMembers(item).length === 0" class="py-3 px-3 text-center text-xs text-gray-400 italic">

                            No colleagues currently assigned to this {{ groupType }}.

                        </div>



                        <div

                            v-for="(user, uIdx) in getChannelMembers(item)"

                            :key="user.id || uIdx"

                            class="cursor-pointer p-2 rounded-xl hover:bg-white transition-all flex items-center justify-between border border-transparent hover:border-gray-200/70 hover:shadow-2xs"

                            :class="{ 'bg-white shadow-2xs border-blue-200': open && clickedIndex === user.id }"

                            @click="toggleBackgroundUser(user)"

                        >

                            <div class="flex items-center gap-2.5 min-w-0 pr-2">

                                <img

                                    v-if="user.profile_image"

                                    :src="page.props.base + '/' + user.profile_image"

                                    class="w-8 h-8 rounded-full object-cover flex-none border border-gray-200"

                                />

                                <div

                                    v-else

                                    class="w-8 h-8 rounded-full bg-blue-100 border border-blue-200 text-primary flex items-center justify-center font-bold text-xs flex-none"

                                >

                                    {{ (user.first_name || 'U').charAt(0).toUpperCase() }}

                                </div>

                                <div class="truncate min-w-0">
                                    <h4 class="font-semibold text-xs text-gray-900 truncate">
                                        {{ user.first_name }} {{ user.last_name }}
                                    </h4>
                                    <p class="text-[10px] text-gray-400 truncate">{{ user.designation || user.email }}</p>
                                </div>

                            </div>



                            <button

                                type="button"

                                class="text-[10px] font-semibold text-primary bg-blue-50 px-2 py-0.5 rounded-md hover:bg-primary hover:text-white transition-colors flex-shrink-0"

                            >

                                Chat

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        <!-- Messages Section -->

        <section

            class="w-full lg:w-[75%] transition-all duration-300"

            :class="{ 'hidden lg:block': !open }"

        >

            <div v-if="open">

                <div v-if="active === 0 || active === 1 || active === 2 || active === 3">

                    <Messages

                        :selectedUser="selectedUser"

                        :selectedMessage="selectedMessage"

                        :clickedIndexGroup="clickedIndexGroup"

                        :clickedIndex="clickedIndex"

                        :selectedMessageGroup="selectedMessageGroup"

                        :allMessagess="allMessagess"

                        :groupType="groupType"

                        :clickedIndexGroupName="clickedIndexGroupName"

                        :is_single="is_single"

                        :is_group="is_group"

                        :selectedGroup="selectedGroup"

                        @back="handleBackToList"

                        @refresh-archives="getArchivedChats"

                    />

                </div>

            </div>

            <div v-else>

                <EmptyChatRoom />

            </div>

        </section>

    </main>



    <AddLink v-model="addLinkModal" />

    <ReportDialog v-model="reportModal" />

    <GlobalSearchModal

        v-model:visible="showGlobalSearchModal"

        @select-result="onGlobalSearchResult"

    />

</template>



<script setup>

import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";

import { usePage } from "@inertiajs/vue3";

import EmptyChatRoom from "../../components/EmptyChatRoom.vue";

import ReportDialog from "../../components/Modals/chat/ReportDialog.vue";

import AddLink from "../../components/Modals/chat/AddLink.vue";

import Messages from "../../components/Messages.vue";

import GlobalSearchModal from "../../components/Modals/chat/GlobalSearchModal.vue";



import { useStore } from "vuex";

import { useToast } from "primevue/usetoast";



const toast = useToast();

const clickedIndex = ref(null);

const clickedIndexGroup = ref(null);

const groups = ref([]);

const activeButton = ref(0);

const active = ref(0);

const addLinkModal = ref(false);

const reportModal = ref(false);

const open = ref(false);

const selectedMessage = ref();

const selectedMessageGroup = ref();

const allMessagess = ref([]);

const newbox = ref([]);

const Branch_group = ref([]);

const userGroups = ref([]);

const inbox = ref([]);

const Suggestions = ref([]);

const filter_users = ref("");

const page = usePage();

const current_img = ref(null);

const groupType = ref(null);

const groupIndex = ref(null);

const user = computed(() => page.props.user);

const users = ref([]);

const userBranch = computed(() => page.props.userBranch);

const store = useStore();

const global_chat = computed(() => store.getters.getNewChat);

const clickedIndexGroupName = ref("");

const selectedUser = ref();

const is_group = ref(false);

const is_single = ref(false);

const toggleUsers = ref(false);

const selectedGroup = ref([]);

const selectedBranchGroup = ref();

const filter_groups = ref("");

const filtered_groups = ref([]);



const notifications = computed(() => store.getters.getNotifications);



const directUnreadCount = computed(() => {

    return (users.value || []).reduce((acc, u) => acc + (u.unread_count || 0), 0);

});



const personalUnreadCount = computed(() => {

    const unread = (personalContacts.value || []).reduce((acc, u) => acc + (u.unread_count || 0), 0);

    return unread + (chatRequests.value?.length || 0);

});



const tabItems = computed(() => [

    { label: "Direct", title: "Direct Messages", icon: "solar:chat-round-line-bold-duotone", badge: directUnreadCount.value },

    { label: "Groups", title: "Branch Groups", icon: "solar:buildings-2-bold-duotone", badge: 0 },

    { label: "Contacts", title: "Contacts & Requests", icon: "solar:users-group-rounded-bold-duotone", badge: chatRequests.value?.length || 0 },

    { label: "Archived", title: "Archived Chats", icon: "solar:archive-bold-duotone", badge: (archivedChats.value?.length || 0) + (archivedGroups.value?.length || 0) }

]);



const items = computed(() => [

    { label: "Direct Messages", badge: directUnreadCount.value },

    { label: "Branch Groups" },

    { label: "Personal Messages", badge: personalUnreadCount.value },

    { label: "Archived Chats" }

]);



// Quick Filter Pills & Live Typing State for Direct Tab
const activeDirectFilter = ref('all');
const typingUsersMap = ref({});
const typingTimeouts = {};

const unreadDirectUsersCount = computed(() => {
    return (users.value || []).filter(u => (u.unread_count || 0) > 0).length;
});

const onlineDirectUsersCount = computed(() => {
    return (users.value || []).filter(u => isUserOnline(u)).length;
});

const displayedDirectUsers = computed(() => {
    let list = filter_users.value ? Suggestions.value : users.value;
    if (!list) return [];
    if (activeDirectFilter.value === 'unread') {
        return list.filter(u => (u.unread_count || 0) > 0);
    }
    if (activeDirectFilter.value === 'online') {
        return list.filter(u => isUserOnline(u));
    }
    return list;
});



const onlineUserIds = ref(new Set());
const presenceJoined = ref(false);

const isUserOnline = (userOrId) => {
    if (!userOrId) return false;

    const id = typeof userOrId === 'object' ? Number(userOrId.id) : Number(userOrId);
    if (!id) return false;

    const currentUserId = Number(page?.props?.user?.id || 0);

    // Current user is always online
    if (currentUserId && id === currentUserId) return true;

    // When Echo presence channel has connected, presence channel is the real-time truth
    if (presenceJoined.value) {
        return onlineUserIds.value.has(id);
    }

    // Fallback: check server-provided is_online (backed by Cache active session)
    if (typeof userOrId === 'object') {
        return userOrId.is_online === 1 || userOrId.is_online === '1' || userOrId.is_online === true;
    }

    const found = (users.value || []).find(u => Number(u.id) === id) || (personalContacts.value || []).find(u => Number(u.id) === id);
    if (found) {
        return found.is_online === 1 || found.is_online === '1' || found.is_online === true;
    }

    return false;
};



import _, { debounce } from 'lodash';



const personalSearch = ref("");

const searchResults = ref([]);

const chatRequests = ref([]);

const personalContacts = ref([]);



const searchPersonalUsers = debounce(async () => {

    if (!personalSearch.value) {

        searchResults.value = [];

        return;

    }

    const token = localStorage.getItem("token");

    try {

        const res = await axios.get(`/dashboard/chat/search-personal?query=${personalSearch.value}`, {

            headers: { Authorization: `Bearer ${token}` }

        });

        searchResults.value = res.data;

    } catch (error) {

        console.error(error);

    }

}, 300);



const sendRequest = async (userId) => {

    const token = localStorage.getItem("token");

    try {

        const response = await axios.post("/dashboard/chat/request", { receiver_id: userId }, {

            headers: { Authorization: `Bearer ${token}` }

        });

        

        if (response.data.status === 'exists') {

            toast.add({ severity: 'info', summary: 'Info', detail: 'Request already sent!', life: 3000 });

        } else {

            toast.add({ severity: 'success', summary: 'Success', detail: 'Request sent!', life: 3000 });

        }

        

        personalSearch.value = "";

        searchResults.value = [];

    } catch (error) {

        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to send request.', life: 3000 });

        console.error(error);

    }

};



const acceptRequest = async (id) => {

    const token = localStorage.getItem("token");

    try {

        await axios.post(`/dashboard/chat/request/${id}/accept`, {}, {

            headers: { Authorization: `Bearer ${token}` }

        });

        toast.add({ severity: 'success', summary: 'Connected', detail: 'Chat request accepted!', life: 3000 });

        getPersonalData();

    } catch (error) {

        console.error(error);

        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to accept request.', life: 3000 });

    }

};



const declineRequest = async (id) => {

    const token = localStorage.getItem("token");

    try {

        await axios.post(`/dashboard/chat/request/${id}/decline`, {}, {

            headers: { Authorization: `Bearer ${token}` }

        });

        toast.add({ severity: 'info', summary: 'Declined', detail: 'Chat request declined', life: 3000 });

        getPersonalData();

    } catch (error) {

        console.error(error);

        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to decline request.', life: 3000 });

    }

};



const archivedChats = ref([]);

const archivedGroups = ref([]);

const showGlobalSearchModal = ref(false);

const archiveSearchQuery = ref("");



const filteredArchivedChats = computed(() => {

    if (!archiveSearchQuery.value || !archiveSearchQuery.value.trim()) {

        return archivedChats.value || [];

    }

    const q = archiveSearchQuery.value.toLowerCase().trim();

    return (archivedChats.value || []).filter(c => {

        const name = `${c.first_name || ''} ${c.last_name || ''}`.toLowerCase();

        const email = (c.email || '').toLowerCase();

        return name.includes(q) || email.includes(q);

    });

});



const filteredArchivedGroups = computed(() => {

    if (!archiveSearchQuery.value || !archiveSearchQuery.value.trim()) {

        return archivedGroups.value || [];

    }

    const q = archiveSearchQuery.value.toLowerCase().trim();

    return (archivedGroups.value || []).filter(g => {

        const name = (g.name || '').toLowerCase();

        const type = (g.group_type || '').toLowerCase();

        return name.includes(q) || type.includes(q);

    });

});



const getPersonalData = async () => {

    const token = localStorage.getItem("token");

    try {

        const [reqs, contacts] = await Promise.all([

            axios.get("/dashboard/chat/requests", { headers: { Authorization: `Bearer ${token}` } }),

            axios.get("/dashboard/chat/personal-contacts", { headers: { Authorization: `Bearer ${token}` } })

        ]);

        chatRequests.value = reqs.data;

        personalContacts.value = contacts.data;

    } catch (error) {

        console.error(error);

    }

};



const getArchivedChats = async () => {

    const token = localStorage.getItem("token");

    try {

        const res = await axios.get("/dashboard/chat/users/archived", { headers: { Authorization: `Bearer ${token}` } });

        if (res.data && res.data.users !== undefined) {

            archivedChats.value = res.data.users || [];

            archivedGroups.value = res.data.groups || [];

        } else {

            archivedChats.value = Array.isArray(res.data) ? res.data : [];

            archivedGroups.value = [];

        }

    } catch (error) {

        console.error(error);

    }

};



const unarchiveChat = async (id) => {

    const token = localStorage.getItem("token");

    try {

        await axios.post("/dashboard/chat/users/unarchive", { archived_user_id: id }, { headers: { Authorization: `Bearer ${token}` } });

        toast.add({ severity: 'success', summary: 'Success', detail: 'Chat unarchived successfully', life: 3000 });

        getArchivedChats();

        getPersonalData();

    } catch (error) {

        console.error(error);

        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to unarchive chat', life: 3000 });

    }

};



const unarchiveGroup = async (group) => {

    const token = localStorage.getItem("token");

    try {

        await axios.post("/dashboard/chat/users/unarchive", { group_id: group.id, group_type: group.group_type }, { headers: { Authorization: `Bearer ${token}` } });

        toast.add({ severity: 'success', summary: 'Success', detail: 'Group unarchived successfully', life: 3000 });

        getArchivedChats();

    } catch (error) {

        console.error(error);

        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to unarchive group', life: 3000 });

    }

};



const openArchivedGroup = (grp) => {

    groupType.value = grp.group_type;

    toggleGroup({

        id: grp.id,

        name: grp.name,

        user: { id: grp.id }

    });

};



const onGlobalSearchResult = (item) => {

    if (item.type === 'direct') {

        const target = item.target_user || item.sender;

        if (target) {

            const contact = {

                id: target.id,

                first_name: target.first_name || target.name?.split(' ')[0] || 'User',

                last_name: target.last_name || target.name?.split(' ').slice(1).join(' ') || '',

                profile_image: target.profile_image,

                user: target

            };

            toggleBackgroundUser(contact);

        }

    } else if (item.type === 'group') {

        const groupItem = {

            id: item.group_id,

            name: item.group_name,

            group_type: item.group_type,

            user: { id: item.group_id }

        };

        groupType.value = item.group_type;

        toggleGroup(groupItem);

    }

};



watch(active, (newVal) => {

    if (newVal === 2) {

        if (personalContacts.value.length === 0 && chatRequests.value.length === 0) {

            getPersonalData();

        }

    } else if (newVal === 3) {

        getArchivedChats();

    }

});



function toggleGroup(item) {

    groupIndex.value = item.id;

    filter_users.value = "";

    Suggestions.value = [];

    selectedMessage.value = item;

    selectedMessageGroup.value = item;

    selectedMessage.value = [];

    // selectedMessage.value['user'] = item

    open.value = true;

    clickedIndex.value = item?.user?.id;

    clickedIndexGroup.value = item?.id;

    clickedIndexGroupName.value = item?.title;

    //selectedUser.value['type'] = 'group'

    is_group.value = true;

    is_single.value = false;

    selectedGroup.value = item;

}



const filterGroups = computed(() => {

    if (filter_groups.value == "") {

        return groups.value;

    } else {

        return groups.value.filter((item) => {

            return item.title

                .toLowerCase()

                .includes(filter_groups.value.toLowerCase());

        });

    }

});



function toggleBackgroundUser(item) {

    if (item.unread_count) {

        item.unread_count = 0;

    }

    filter_users.value = "";

    Suggestions.value = [];

    selectedUser.value = {

        ...item,

        is_online: isUserOnline(item),

        type: "single"

    };

    open.value = true;

    is_group.value = false;

    is_single.value = true;

    clickedIndex.value = item.id;

}



function handleKeyDown(event) {

    if (event.key === "Escape") {

        open.value = false;

    }

}



const getUserBranch = () => {

    if (userBranch.value.unit) {

        return "unit";

    } else if (userBranch.value.division) {

        return "division";

    } else if (userBranch.value.department) {

        return "department";

    } else {

        return "others";

    }

};



const handleBackToList = () => {

    open.value = false;

    clickedIndex.value = null;

    clickedIndexGroup.value = null;

};



const getItemCount = (itm) => {

    let filtered = global_chat.value.filter((item) => {

        return item.group_id == itm.id && item.group_type == groupType.value;

    });



    console.log("this is the filtered list ", filtered);

    return filtered ? filtered.length : 0;

};



const getUserBranchId = () => {

    if (userBranch.value.unit) {

        return userBranch.value.unit.id;

    } else if (userBranch.value.division) {

        return userBranch.value.division.id;

    } else if (userBranch.value.department) {

        return userBranch.value.department.id;

    }

};



const getChannelMembers = (item) => {

    if (!item) return [];

    if (Array.isArray(item.users) && item.users.length > 0) return item.users;

    if (Array.isArray(item.user) && item.user.length > 0) return item.user;

    return [];

};



const toggleGroupUsers = (item) => {

    if (groupIndex.value === item.id) {

        toggleUsers.value = !toggleUsers.value;

    } else {

        groupIndex.value = item.id;

        toggleUsers.value = true;

    }

};



function getImageForGroup(item) {

    switch (item) {

        case "department":

            return page.props.base + "/images/department_active.svg";

        case "division":

            return page.props.base + "/images/division_active.svg";

        case "unit":

            return page.props.base + "/images/unit_active.svg";

        default:

            return ""; // Fallback or default image

    }

}



const getUserImage = (user) => {

    if (user && user.profile_image) {

        return page.props.base + "/" + user.profile_image;

    }

    return page.props.base + '/images/user.png';

};



document.addEventListener("keydown", handleKeyDown);

const message = ref("");

const handleGroups = (item, index) => {

    activeButton.value = 1;

    groups.value = item;

    current_img.value = getImageForGroup(index);

    is_group.value = true;

    is_single.value = false;

    selectedBranchGroup.value = item;



    if (index == "department") {

        groupType.value = "department";

    } else if (index == "division") {

        groupType.value = "division";

    } else {

        groupType.value = "unit";

    }



    // const newValues = ["gg:organisation", "icon-park-outline:fork", "tabler:box"]

    // groups.value.forEach((obj, index) => {

    //     obj.icon = newValues[0]

    // })

};

const getAllCategories = async () => {

    const token = localStorage.getItem("token");

    try {

        const response = await axios.get("/dashboard/chat/categories", {

            headers: {

                Authorization: `Bearer ${token}`,

            },

        });



        Branch_group.value = response.data;

        const newValues = [

            "gg:organisation",

            "icon-park-outline:fork",

            "tabler:box",

        ];

        Branch_group.value.forEach((obj, index) => {

            obj.icon = newValues[index];

        });

        Branch_group.value.forEach((obj, index) => {

            obj.icon = newValues[index];

        });

        Branch_group.value.forEach((obj, index) => {

            obj.icon = newValues[index];

        });

    } catch (error) {}

};

const getAllUserAndMessage = async () => {

    const token = localStorage.getItem("token");

    try {

        const response = await axios.get("/dashboard/chat/users", {

            headers: {

                Authorization: `Bearer ${token}`,

            },

        });



        //inbox.value = response.data;

        users.value = response.data;

    } catch (error) {}

};

watch(

    () => inbox.value,

    (checkbox) => {

        newbox.value = checkbox;

    }

);



const filterRecipients = () => {

    const searchTerm = (filter_users.value || '').trim().toLowerCase();

    if (!searchTerm) {

        Suggestions.value = [];

        return;

    }



    Suggestions.value = (users.value || []).filter((item) => {

        const fullName = `${item.first_name || ''} ${item.last_name || ''}`.toLowerCase();

        const snippet = (item.last_message || '').toLowerCase();

        return fullName.includes(searchTerm) || snippet.includes(searchTerm);

    });

};



async function fetchUserGroups() {

    const token = localStorage.getItem("token");

    try {

        const response = await axios.get("/dashboard/chat/all-groups", {

            headers: {

                Authorization: `Bearer ${token}`,

            },

        });



        userGroups.value = response.data;

    } catch (error) {

        console.log(error);

    }

}



function listenToGroupMessages(groups) {

    //this line to be reviewed

    let total_length = Object.keys(groups).length;



    // if(groups && total_length > 0){

    //     groups.forEach(group => {

    //     let userId = window.authUserId;



    //     // window.Echo.private(`group.${group.id}`)

    //     //     .listen('GroupMessageSent', (event) => {

    //     //         console.log('from group:', group.id, event);

    //     //         allMessagess.value = event.message;

    //     //         console.log("listened", allMessagess.value.sender_id)

    //     //         allMessagess.value.is_sent_by_logged_in_user = allMessagess.value.sender_id === userId;

    //     //     });

    //     let groupType = getUserBranch()



    // });



    // }

    let grouptype = getUserBranch();

    for (let grp in groups) {

        if (groups.hasOwnProperty(grp)) {

            let branch = null;

            if (grouptype == "unit") {

                branch = userBranch.value["unit"];

            }

            if (grouptype == "division") {

                branch = userBranch.value["division"];

            }

            if (grouptype == "department") {

                branch = userBranch.value["department"];

            }

        }

    }



    let branch_id = getUserBranchId();

    if (branch_id && grouptype && grouptype !== "others") {

        window.Echo.private(`group.${branch_id}.${grouptype}`)

            .listen("GroupMessageSent", (event) => {

                allMessagess.value = event.message;



                allMessagess.value.is_sent_by_logged_in_user =

                    allMessagess.value.sender_id === user.value.id;

                //if(event.message.group_id == clickedIndexGroup.value && event.message.group_type === grouptype){

                let data = {

                    group_type: grouptype,

                    group_id: event.message.group_id,

                    type: "chat",

                    chat: event.message,

                    date: new Date(),

                };

                store.commit("setNewChat", data);

                // store.commit('setNotifications', values)

                // }

            })

            .listen("MessageEdited", (event) => {

                window.dispatchEvent(new CustomEvent('chat-message-edited', { detail: event.message }));

            });

    }

}

onMounted(async () => {

    try {

        getAllUserAndMessage();

        getAllCategories();

        getPersonalData();



        await fetchUserGroups();

        listenToGroupMessages(userGroups.value);



        let userId = user.value.id;



        window.Echo.private(`chat.${userId}`).listen("MessageSent", (event) => {

            allMessagess.value = event.message;

            allMessagess.value.is_sent_by_logged_in_user =

                allMessagess.value.sender_id === userId;



            // Live update contact list preview and unread counters

            const senderId = event.message.sender_id;

            const targetUser = users.value.find(u => u.id === senderId);

            if (targetUser) {

                targetUser.last_message = event.message.message || 'Sent an attachment';

                targetUser.last_message_sender_id = senderId;

                targetUser.last_message_time = 'Just now';

                if (clickedIndex.value !== senderId) {

                    targetUser.unread_count = (targetUser.unread_count || 0) + 1;

                }

                users.value = [targetUser, ...users.value.filter(u => u.id !== senderId)];

            } else {

                getAllUserAndMessage();

            }

            const targetPersonal = personalContacts.value.find(u => u.id === senderId);

            if (targetPersonal) {

                targetPersonal.last_message = event.message.message || 'Sent an attachment';

                targetPersonal.last_message_sender_id = senderId;

                targetPersonal.last_message_time = 'Just now';

                if (clickedIndex.value !== senderId) {

                    targetPersonal.unread_count = (targetPersonal.unread_count || 0) + 1;

                }

            }



            if (document.hidden) {

                // Play notification sound

                const audio = new Audio('/sounds/notification.mp3');

                audio.play().catch(e => console.log('Audio playback prevented by browser'));



                if (Notification.permission === "granted") {

                    new Notification("New Message", {

                        body: "You have received a new message.",

                        icon: "/images/logo.png"

                    });

                } else if (Notification.permission !== "denied") {

                    Notification.requestPermission().then(permission => {

                        if (permission === "granted") {

                            new Notification("New Message", {

                                body: "You have received a new message.",

                                icon: "/images/logo.png"

                            });

                        }

                    });

                }

            }

        });



        window.Echo.private(`chat.${userId}`).listen("MessagesRead", (event) => {

            window.dispatchEvent(new CustomEvent('chat-messages-read', { detail: event }));

            const readerUser = users.value.find(u => Number(u.id) === Number(event.reader_id));

            if (readerUser) {

                readerUser.unread_count = 0;

            }

        });



        window.Echo.private(`chat.${userId}`).listen("MessageEdited", (event) => {

            window.dispatchEvent(new CustomEvent('chat-message-edited', { detail: event.message }));

            const senderId = event.message.sender_id;

            const targetUser = users.value.find(u => u.id === senderId || u.id === event.message.recipient_id);

            if (targetUser) {

                targetUser.last_message = event.message.message;

            }

        });



        window.Echo.private(`chat-requests.${userId}`).listen("ChatRequestEvent", (event) => {

            if (active.value === 2) {

                chatRequests.value.push(event.chatRequest);

            } else {

                toast.add({ severity: 'info', summary: 'New Request', detail: `New chat request from ${event.chatRequest.sender.first_name}`, life: 5000 });

            }

        });



        // Live typing whisper listener

        window.Echo.private(`chat.${userId}`).listenForWhisper("typing", (event) => {

            if (event && event.sender_id) {

                typingUsersMap.value = {

                    ...typingUsersMap.value,

                    [event.sender_id]: true

                };

                if (typingTimeouts[event.sender_id]) {

                    clearTimeout(typingTimeouts[event.sender_id]);

                }

                typingTimeouts[event.sender_id] = setTimeout(() => {

                    const copy = { ...typingUsersMap.value };

                    delete copy[event.sender_id];

                    typingUsersMap.value = copy;

                }, 2500);

            }

        });



        // Global chat presence tracking and heartbeat
        startHeartbeat();

        if (typeof window !== 'undefined' && window.Echo) {
            window.Echo.join('chat.presence')
                .here((onlineUsersList) => {
                    presenceJoined.value = true;
                    const onlineSet = new Set(onlineUsersList.map(u => Number(u.id)));
                    onlineUserIds.value = onlineSet;

                    // Sync all loaded users with authoritative online presence
                    (users.value || []).forEach(u => {
                        u.is_online = onlineSet.has(Number(u.id)) ? 1 : 0;
                    });
                    (personalContacts.value || []).forEach(p => {
                        p.is_online = onlineSet.has(Number(p.id)) ? 1 : 0;
                    });

                    if (selectedUser.value && selectedUser.value.id) {
                        selectedUser.value.is_online = isUserOnline(selectedUser.value);
                    }
                })
                .joining((joiningUser) => {
                    const jId = Number(joiningUser.id);
                    onlineUserIds.value.add(jId);

                    const targetU = (users.value || []).find(u => Number(u.id) === jId);
                    if (targetU) targetU.is_online = 1;

                    const targetP = (personalContacts.value || []).find(u => Number(u.id) === jId);
                    if (targetP) targetP.is_online = 1;

                    if (selectedUser.value && Number(selectedUser.value.id) === jId) {
                        selectedUser.value.is_online = true;
                    }
                })
                .leaving((leavingUser) => {
                    const lId = Number(leavingUser.id);
                    onlineUserIds.value.delete(lId);

                    const targetU = (users.value || []).find(u => Number(u.id) === lId);
                    if (targetU) targetU.is_online = 0;

                    const targetP = (personalContacts.value || []).find(u => Number(u.id) === lId);
                    if (targetP) targetP.is_online = 0;

                    if (selectedUser.value && Number(selectedUser.value.id) === lId) {
                        selectedUser.value.is_online = false;
                    }
                });
        }

        window.addEventListener("beforeunload", handleBeforeUnload);

        window.addEventListener("keydown", (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
                e.preventDefault();
                showGlobalSearchModal.value = true;
            }
        });

    } catch (error) {}
});

let heartbeatInterval = null;

const startHeartbeat = () => {
    axios.post('/dashboard/chat/heartbeat').catch(() => {});
    heartbeatInterval = setInterval(() => {
        axios.post('/dashboard/chat/heartbeat').catch(() => {});
    }, 45000);
};

const handleBeforeUnload = () => {
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/dashboard/chat/leave');
    }
};

onBeforeUnmount(() => {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
    }
    if (typeof window !== 'undefined') {
        window.removeEventListener("beforeunload", handleBeforeUnload);
        if (window.Echo) {
            try {
                window.Echo.leave('chat.presence');
            } catch (e) {}
        }
    }
});

</script>



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

    /* Hide border */

    outline: none;

    /* Remove outline */

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



/* Rounded sliders */

.slider.round {

    border-radius: 34px;

}



.slider.round:before {

    border-radius: 50%;

}

</style>

