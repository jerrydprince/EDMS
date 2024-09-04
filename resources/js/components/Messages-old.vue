<template>
    <div class="flex">
        <div class="w-[100%] h-[91vh] bg-[#F5F9FF] relative border" :class="{ 'w-[65%]': profile }"
            style="transition: width 0.1s linear">
            <!-- ================= header ====================== -->

            <div class="flex items-center justify-between bg-white px-7 py-3">
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
                            <img src="../assets/images/display_picture.svg" alt="" />
                        </div>
                    </div>
                    <div>
                        <p v-if="selectedMessage.users" class="text-sm font-semibold">{{ selectedMessage.name }}</p>
                        <p v-else class="text-sm font-semibold">{{ selectedMessage.user.first_name }}</p>

                        <div class="flex items-center text-xs mt-1" v-if="selectedMessage.users">
                            <p class="text-[#98A2B3]">
                                {{ selectedMessage.users.length }} Members,
                            </p>
                            <p class="text-green-500">1 Online</p>
                        </div>
                        <p class="text-xs text-[#98A2B3]" v-else>Offline</p>
                    </div>
                </div>
                <div class="p-2 border cursor-pointer rounded-md">
                    <Icon icon="mi:options-vertical" width="25" />
                </div>
            </div>

            <!-- ===============  message body ================= -->
            <div class="flex-1 h-[70vh] overflow-y-scroll hide_scroll mx-7 pt-5 transition-all" id="chat-container">
                <div v-for="(item, index) in sortedMessages" :key="item.date" class="mt-5">
                    <div class="flex items-center w-full justify-center">
                        <div class="h-0.5 w-[30%] bg-gray-100"></div>
                        <p class="text-[#667085] text-xs px-20">{{ item.date }}</p>
                        <div class="h-0.5 w-[30%] bg-gray-100"></div>
                    </div>
                    <div v-for="(message, index) in item.messages" class="max-w-[40%] " :class="{
            'mb-8 w-fit   ': message.is_sent_by_logged_in_user === false,
            ' w-fit ml-auto   mb-5  p-3 text-base ':
                message.is_sent_by_logged_in_user == true,
            'max-w-[50%]': profile,
        }">
                        <div>
                            <div class="flex items-center w-full text-xs justify-between mb-2">
                                <h1 v-if="message.is_sent_by_logged_in_user == true"> You</h1>
                                <div v-else class="py-2 flex items-center gap-2">
                                    <img src="../assets/images/display_picture.svg" alt="" />
                                    <div v-if="message.is_sent_by_logged_in_user === false">
                                        <h1 class="font-semibold" v-if="message.is_sent_by_logged_in_user === false">
                                            {{ message.sender_name }}
                                        </h1>
                                    </div>
                                </div>
                                <p class="text-[#667085]">{{ message.timestamp }}</p>
                            </div>
                            <p :class="{
            'p-3 text-base ml-10 -mt-5 bg-white text-[#101828] rounded-3xl rounded-tl-none pl-5 shadow-md  w-fit  ':
                message.is_sent_by_logged_in_user === false,
            ' w-fit   bg-primary p-3 pr-5 text-base text-white rounded-3xl rounded-tr-none shadow-md ':
                message.is_sent_by_logged_in_user == true,
        }">
                                {{ message.message }}
                            </p>
                            <!-- <div v-for="(doc, index) in message?.attachments" :key="index"> -->
                            <div v-if='message?.attachment'>
                                <embed :src="'/storage/' + message?.attachment?.file_path" />
                            </div>
                            <!-- </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- =============== send message  ================= -->
            <div class="absolute w-full bottom-5">
                <div class="mx-7 border border-gray-300 bg-white rounded-md py-3 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-300 px-2 pb-2">
                        <div class="flex gap-3 items-center">
                            <h1 class="text-[#98A2B3]">To:</h1>
                            <p v-if="selectedMessage.users" class="bg-[#EAECF0] text-base px-4 py-1 rounded-2xl">
                                {{ selectedMessage.name }}
                            </p>
                            <p v-else class="bg-[#EAECF0] text-base px-4 py-1 rounded-2xl">
                                {{ selectedMessage.user.first_name }}
                            </p>
                        </div>
                        <div>
                            <Icon icon="tabler:arrows-diagonal" />
                        </div>
                    </div>
                    <div>
                        <div v-if="isTyping">
                            {{ typingUser.name }} is typing...
                        </div>
                        <Textarea v-model="formData.message" @input="handleTyping" autoResize rows="1" cols="30"
                            placeholder="Start a new message" class="p-inputtextarea" required />
                        <input type="file" style="display: none;" ref="fileInput" @change="handleFileChange" />
                        <!-- {{ clickedIndex }} -->
                        <!-- {{selectedMessage}} -->
                    </div>

                    <div class="flex items-center justify-between px-2 text-[#98A2B3]">
                        <div class="flex items-center gap-5">
                            <Icon icon="akar-icons:attach" width="25" class="cursor-pointer"
                                @click="triggerFileInput" />
                            <Icon icon="ic:round-link" width="25" class="cursor-pointer" @click="addLinkModal = true" />
                        </div>
                        <button @click="sendMessage()"
                            class="flex items-center gap-2 bg-primary text-white px-5 rounded-md py-2">
                            <p>Send</p>
                            <Icon icon="tabler:send" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ===================   sidebar ================= -->

        <section v-if="profile && selectedMessage.type !== 'group'" class="w-[35%] border-l" ref="sidebarRef">
            <div v-if="activePage === 1">
                <div class="flex items-center justify-between bg-white px-7 border-b bord py-3">
                    <h1 class="font-semibold text-lg">Profile</h1>
                    <div class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer" @click="profile = false">
                        <Icon icon="prime:times" width="25" />
                    </div>
                </div>

                <div class="border border-solid bg-white overflow-scroll h-[90vh] pb-32 hide_scroll">
                    <div class="w-full flex flex-col items-center justify-center mt-10">
                        <div class="relative w-36 h-36">
                            <img src="../assets/images/display_picture.svg" alt="" class="w-36" />
                            <div
                                class="h-6 w-6 bg-[#12B76A] rounded-full absolute bottom-0 right-5 border-[3px] border-white">
                            </div>
                        </div>
                        <h1 class="font-semibold text-xl mt-2">
                            {{ selectedMessage.name }}
                        </h1>
                        <p class="text-sm text-[#667085] mt-2 bg-gray-50 px-4 py-1.5 rounded-2xl">
                            Product Manager
                        </p>
                    </div>

                    <div class="mx-3 bg-[#F2F4F7] rounded-md p-3 mt-5">
                        <div class="flex justify-between items-center">
                            <h1 class="font-semibold text-lg">Share Files</h1>
                            <button class="text-base text-primary" @click="activePage = 2">
                                See All
                            </button>
                        </div>
                        <div v-for="(item, index) in files" :key="index" class="mt-3">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="p-2 bg-gray-100 border rounded-full">
                                    <Icon :icon="item.icon" width="30" />
                                </div>
                                <h1>{{ item.name }}</h1>
                            </div>
                        </div>
                    </div>
                    <Divider />
                    <div class="flex px-7 items-center justify-between">
                        <div class="flex items-center gap-2 text-base">
                            <Icon icon="grommet-icons:notification" width="20" />
                            <h1 class="">Notification</h1>
                        </div>
                        <label class="switch">
                            <input type="checkbox" v-model="toggle" />
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <Divider />
                    <div class="flex items-center gap-3 py-3 px-7 cursor-pointer" @click="reportModal = true">
                        <img src="../assets/images/report.svg" alt="" class="w-7" />
                        <p class="text-[#F04438] font-semibold text-base">Report Contact</p>
                    </div>
                </div>
            </div>

            <div class="" v-else>
                <div class="flex items-center gap-5 bg-white px-7 border-b bord py-3">
                    <div class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer" @click="activePage = 1">
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>
                    <h1 class="font-semibold text-lg">
                        Shared Files
                        <span class="text-xs text-gray-500">(24 Files)</span>
                    </h1>
                </div>
                <div class="flex items-center border m-7 border-solid rounded-3xl px-3 bg-gray-100">
                    <Icon icon="lucide:search" class="text-lg text-gray-500" />
                    <input type="text" placeholder="Search Files " class="outline-none h-full p-2 bg-gray-100" />
                </div>

                <div class="mx-7 flex gap-5 flex-wrap pb-5">
                    <div class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl cursor-pointer font-normal border border-solid border-gray-300"
                        :class="{ 'bg-[#eaf2ff] border-primary  ': activeButton === 1 }">
                        All Files
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Documents
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Images
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Video
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Audio
                    </div>
                </div>

                <!-- =================   All Files =============== -->
                <div class="mx-7">
                    <div class="pt-10 h-[65vh] pb-20 overflow-scroll hide_scroll" v-if="allFiles.length !== 0">
                        <div v-for="(item, index) in allFiles" :key="index" class="mb-7">
                            <h1 class="text-[#667085]">{{ item.day }}</h1>
                            <div v-for="(files, index) in item.files" :key="index" class="mt-3">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="p-2 bg-gray-100 border rounded-full">
                                        <Icon :icon="files.icon" width="30" />
                                    </div>
                                    <h1>{{ files.name }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center">
                        <img src="../assets/images/no_file.svg" alt="" class="w-40 my-5" />
                        <h1 class="font-semibold text-2xl">No Files Found</h1>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="profile && selectedMessage.type === 'group'" class="w-[35%] border-l" ref="sidebarRef">
            <div v-if="activePage === 1">
                <div class="flex items-center gap-3 bg-white px-7 border-b bord py-3">
                    <div class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer" @click="profile = false">
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>

                    <h1 class="font-semibold text-lg">Branch Info</h1>
                </div>

                <div class="border border-solid bg-white overflow-scroll h-[90vh] pb-32 hide_scroll">
                    <div class="w-full flex flex-col items-center justify-center mt-10">
                        <div
                            class="text-primary border border-primary bg-white p-5 rounded-full overflow-hidden flex-none">
                            <Icon :icon="selectedMessage.icon" width="80" />
                        </div>
                        <h1 class="font-semibold text-xl mt-2">
                            {{ selectedMessage.name }}
                        </h1>
                        <div class="flex items-center text-xs mt-1">
                            <p class="text-[#98A2B3]">
                                {{ selectedMessage.member }} Members,
                            </p>
                            <p class="text-green-500">15 Online</p>
                        </div>
                    </div>

                    <Divider />
                    <div class="flex px-7 items-center justify-between">
                        <div class="flex items-center gap-2 text-base">
                            <Icon icon="grommet-icons:notification" width="20" />
                            <h1 class="">Notification</h1>
                        </div>
                        <label class="switch">
                            <input type="checkbox" v-model="toggle" />
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <Divider />
                    <!-- =================  Members ================ -->
                    <div class="mx-3 mt-5">
                        <div class="flex items-center justify-between font-semibold">
                            <h1>Members</h1>
                            <div class="p-2 cursor-pointer transition-all" @click="toggleMembers">
                                <Icon icon="flowbite:angle-right-outline" :class="{ 'rotate-90': member_switch }"
                                    style="transition: all 0.3s linear" />
                            </div>
                        </div>
                        <div class="h-0 overflow-hidden" :class="{ ' h-auto': member_switch }"
                            style="transition: all 1s linear">
                            <div>
                                <div v-for="(item, index) in members" :key="index">
                                    <div class="flex items-center gap-5 font-semibold mb-3">
                                        <img src="../assets/images/display_picture.svg" alt="" class="w-9" />

                                        <h1 class="cursor-pointer" @click="toggle" aria-haspopup="true"
                                            aria-controls="overlay_menu">
                                            {{ item.name }}
                                        </h1>
                                    </div>
                                </div>
                                <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true">
                                    <template #item="{ item }">
                                        <div class="flex items-center gap-2 font-semibold p-2" :class="{
            'text-red-500': item.label === 'Report Contact',
        }" @click="handleMemberOption(item.label)">
                                            <Icon :icon="item.icon" width="20" />
                                            <p>{{ item.label }}</p>
                                        </div>
                                    </template>
                                </Menu>
                            </div>
                            <div class=" "></div>
                            <div>
                                <button class="text-sm text-primary">See More...</button>
                            </div>
                        </div>
                    </div>

                    <div class="mx-3 bg-[#F2F4F7] rounded-md p-3 mt-5">
                        <div class="flex justify-between items-center">
                            <h1 class="font-semibold text-lg">Share Files</h1>
                            <button class="text-base text-primary" @click="activePage = 2">
                                See All
                            </button>
                        </div>
                        <div v-for="(item, index) in files" :key="index" class="mt-3">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="p-2 bg-gray-100 border rounded-full">
                                    <Icon :icon="item.icon" width="30" />
                                </div>
                                <h1>{{ item.name }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="" v-else>
                <div class="flex items-center gap-5 bg-white px-7 border-b bord py-3">
                    <div class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer" @click="activePage = 1">
                        <Icon icon="ic:sharp-arrow-back" width="25" />
                    </div>
                    <h1 class="font-semibold text-lg">
                        Shared Files
                        <span class="text-xs text-gray-500">(24 Files)</span>
                    </h1>
                </div>
                <div class="flex items-center border m-7 border-solid rounded-3xl px-3 bg-gray-100">
                    <Icon icon="lucide:search" class="text-lg text-gray-500" />
                    <input type="text" placeholder="Search Files " class="outline-none h-full p-2 bg-gray-100" />
                </div>

                <div class="mx-7 flex gap-5 flex-wrap pb-5">
                    <div class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl cursor-pointer font-normal border border-solid border-gray-300"
                        :class="{ 'bg-[#eaf2ff] border-primary  ': activeButton === 1 }">
                        All Files
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Documents
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Images
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Video
                    </div>
                    <div
                        class="bg-[#F2F4F7] text-base px-3 py-1 rounded-2xl font-normal cursor-pointer border border-solid border-gray-300">
                        Audio
                    </div>
                </div>

                <!-- =================   All Files =============== -->
                <div class="mx-7">
                    <div class="pt-10 h-[65vh] pb-20 overflow-scroll hide_scroll" v-if="allFiles.length !== 0">
                        <div v-for="(item, index) in allFiles" :key="index" class="mb-7">
                            <h1 class="text-[#667085]">{{ item.day }}</h1>
                            <div v-for="(files, index) in item.files" :key="index" class="mt-3">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="p-2 bg-gray-100 border rounded-full">
                                        <Icon :icon="files.icon" width="30" />
                                    </div>
                                    <h1>{{ files.name }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center">
                        <img src="../assets/images/no_file.svg" alt="" class="w-40 my-5" />
                        <h1 class="font-semibold text-2xl">No Files Found</h1>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <AddLink v-model="addLinkModal" @data="fromLinkModel" />
    <ReportDialog v-model="reportModal" />
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount, computed, onUpdated } from "vue";
import { defineProps } from "vue";
import { useToast } from "primevue/usetoast";
import { onClickOutside } from "@vueuse/core";
import AddLink from "./Modals/chat/AddLink.vue";
import ReportDialog from "./Modals/chat/ReportDialog.vue";
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
const toast = useToast();
const addLinkModal = ref(false);
const reportModal = ref(false);
const allMessages = ref([]);
const messagesContainer = ref(null);
const fileInput = ref(null);
const typingUser = ref(null);
const isTyping = ref(false);




const props = defineProps(["selectedMessage", "clickedIndex", "clickedIndexGroup", "allMessagess"]);
const currentClickedIndex = ref(props.clickedIndex);
const clickedIndexGroup = ref(props.clickedIndexGroup);
const form = new FormData()
const formData = ref({
    message: "",
    recipient_id: props.clickedIndex,
    group_id: props.clickedIndexGroup
});
// watch(()=>props.allMessagess, (newMessages) => {
//     allMessages.value.push(newMessages);
// })
const sidebarRef = ref(null);
const handleTyping = async (event) => {
    const token = localStorage.getItem('token');
    if (event.target.value) {
        await axios.post('/dashboard/chat/typing', {
            recipient_id: currentClickedIndex.value,
            is_typing: true
        }, {
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
    } else {
        await axios.post('/api/typing', {
            recipient_id: currentClickedIndex.value,
            is_typing: false
        }, {
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
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
    if (fileInput.value) {
        fileInput.value.click();
    } else {
        console.error('fileInput ref is not defined.');
    }
};
const profile = ref(false);
const activePage = ref(1);
const activeButton = ref(1);
const files = [
    {
        icon: "vscode-icons:file-type-powerpoint",
        name: "Auditing Report.ppt",
    },
    {
        icon: "vscode-icons:file-type-word",
        name: "Communication.docx",
    },
    {
        icon: "vscode-icons:file-type-excel2",
        name: "Analysis Report.xls",
    },
    {
        icon: "vscode-icons:file-type-powerpoint",
        name: "Auditing Report.ppt",
    },
];

const allFiles = [
    {
        day: "Today",
        files: [
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
            {
                icon: "vscode-icons:file-type-word",
                name: "Communication.docx",
            },
            {
                icon: "vscode-icons:file-type-excel2",
                name: "Analysis Report.xls",
            },
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
        ],
    },

    {
        day: "Yesterday",
        files: [
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
            {
                icon: "vscode-icons:file-type-word",
                name: "Communication.docx",
            },
            {
                icon: "vscode-icons:file-type-excel2",
                name: "Analysis Report.xls",
            },
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
        ],
    },
    {
        day: "1/04/23",
        files: [
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
            {
                icon: "vscode-icons:file-type-word",
                name: "Communication.docx",
            },
            {
                icon: "vscode-icons:file-type-excel2",
                name: "Analysis Report.xls",
            },
            {
                icon: "vscode-icons:file-type-powerpoint",
                name: "Auditing Report.ppt",
            },
        ],
    },
];
const toggleMembers = () => {
    member_switch.value = !member_switch.value;
};
const member_switch = ref(false);
const members = [
    {
        id: "1",
        name: "David Akinson",
    },
    {
        id: "2",
        name: "David Akinson",
    },
    {
        id: "3",
        name: "David Akinson",
    },
    {
        id: "4",
        name: "David Akinson",
    },
    {
        id: "5",
        name: "David Akinson",
    },
];

const menu = ref();
const menuItems = ref([
    { label: "Send Message", icon: "mdi:message-outline" },
    { label: "Make branch admin", icon: "material-symbols:shield-outline" },
    { label: "Remove Member", icon: "eva:slash-fill" },
    { label: "Report Contact", icon: "uiw:delete" },
]);
const returnLink = (linkData) => {
    if (!linkData?.link.startsWith('http://') && !linkData?.link.startsWith('https://')) {
        return `https://${linkData?.link}`;
    }
    return linkData?.link
}
const fromLinkModel = (linkData) => {
    const linkHTML = `<a href="${returnLink(linkData)}" target="_blank">${linkData?.text}</a>`
    console.log("emitted", linkData);
    formData.value.message += linkHTML;
}
const scrollToBottom = () => {
    nextTick(() => {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
};
const handleFileChange = (event) => {
    const file = event.target.files[0];
    console.log(file)
    if (file) {
        formData.value.message += file.name;
        form.append("doc", file)
    }
};
function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();

    // Compare the date with today's date
    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    }

    // Check if the date is yesterday
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    }

    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}
watch(() => props.allMessagess, (newMessage) => {
    if (newMessage) {
        console.log('watching...', newMessage)
        const messageDate = formatDate(newMessage.created_at);

        if (allMessages.value[messageDate]) {
            allMessages.value[messageDate].messages.push(newMessage);

            allMessages.value[messageDate].messages = [...allMessages.value[messageDate].messages];
        } else {
            allMessages.value = {
                ...allMessages.value,
                [messageDate]: {
                    date: '',
                    messages: [newMessage]
                }
            };
        }
        console.log("Messages updated:", allMessages.value);
    }
}, { deep: true })
const userConversations = async () => {
    let toCheck = "user"
    const hasKey = toCheck in props.selectedMessage;
    // console.log("inside", props.selectedMessage);
    console.log(hasKey);
    const token = localStorage.getItem('token');
   // if (currentClickedIndex.value) {
        try {
            const response = await axios.get(`/dashboard/chat/usersConversation/${currentClickedIndex.value}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                }
            });
            allMessages.value = response.data;
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    //} 
    // else {
    //     try {
    //         const responses = await axios.get(`/dashboard/chat/group/${clickedIndexGroup.value}/messages`, {
    //             headers: {
    //                 'Authorization': `Bearer ${token}`,
    //             }
    //         });
    //         allMessages.value = responses.data
    //         console.log("messages in group", allMessages.value);
    //     } catch (error) {
    //         console.error('Error fetching data:', error);
    //     }
    // }

};

watch([() => props.clickedIndex, () => props.clickedIndexGroup], ([newIndex, newGroup]) => {
    currentClickedIndex.value = newIndex;
    clickedIndexGroup.value = newGroup;
    // getMessagesInGroup();
    //userConversations()
});
const sendMessage = async () => {
    let toCheck = "user"
    const hasKey = toCheck in props.selectedMessage;
    console.log("inside send", props.selectedMessage);
    const token = localStorage.getItem('token');
    if (hasKey === true) {
        try {
            form.append("message", formData.value.message)
            form.append("recipient_id", currentClickedIndex.value)
            console.log("new data", form);
            console.log("recipient", formData.value.recipient_id);
            const responses = await axios.post(`/dashboard/chat/users/messages/`, form, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Authorization': `Bearer ${token}`,
                }
            });
            console.log("on send", responses.data);
            const newMessage = responses.data.sentMessage;
            const messageDate = formatDate(newMessage.created_at);
            if (allMessages.value[messageDate]) {
                allMessages.value[messageDate].messages.push(newMessage);
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [messageDate]: {
                        date: messageDate,
                        messages: [newMessage]
                    }
                };
            }

            formData.value = {}
        } catch (error) {
            console.error('Error fetching data:', error.message);
        }
    } 
    else {
        try {
            form.append("message", formData.value.message)
            form.append("group_id", clickedIndexGroup.value)
            console.log("new data", form);
            const responses = await axios.post(`/dashboard/chat/group/send-message`, form, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Authorization': `Bearer ${token}`,
                }
            });
            console.log("on send", responses.data);
            const newMessage = responses.data.sentMessage;
            const messageDate = formatDate(newMessage.created_at);
            if (allMessages.value[messageDate]) {
                allMessages.value[messageDate].messages.push(newMessage);
            } else {
                allMessages.value = {
                    ...allMessages.value,
                    [messageDate]: {
                        date: messageDate,
                        messages: [newMessage]
                    }
                };
            }
            formData.value = {}
        } catch (error) {
            console.error('Error fetching data:', error.message);
        }
    }

}

const sortedMessages = computed(() => {
    const messageDates = Object.keys(allMessages.value);
    const sortedDates = messageDates.sort((a, b) => new Date(a) - new Date(b));
    return sortedDates.map(date => {
        return {
            date: allMessages.value[date].date,
            messages: allMessages.value[date].messages
        };
    });
});

onMounted(() => {
  userConversations()
    console.log("group id:", clickedIndexGroup.value)
    //listenForTyping();
    scrollToBottom();
   
});
onBeforeUnmount(() => {
    window.Echo.leave(`chat.${currentClickedIndex}`);
});
onUpdated(() => {
    scrollToBottom();
});
watch(sortedMessages, (newMessages, oldMessages) => {
    console.log('sorted:', sortedMessages);
    if (newMessages.length !== oldMessages.length) {
        scrollToBottom();
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

input:checked+.slider {
    background-color: #2196f3;
}

input:focus+.slider {
    box-shadow: 0 0 1px #2196f3;
}

input:checked+.slider:before {
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
