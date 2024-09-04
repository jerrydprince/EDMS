<template>
    <main class="flex border h-[91vh] fixed 2xl:w-[93.3%] w-[100%] overflow-hidden">
        <section class="w-[25%] border-r border-solid border-gray-200 top-0 h-[100%] py-3" v-if="activeButton === 0">
            <div class="px-3">
                <div class="flex items-center justify-between text-[#1D2939] text-3xl font-semibold">
                    <h1 class="">Chats</h1>
                </div>

                <div class="flex items-center justify-between mt-5 w-full">
                    <div class="flex items-center border w-[85%] border-solid rounded-3xl px-3 bg-gray-100">
                        <Icon icon="lucide:search" class="text-lg text-gray-500" />
                        <input type="text" v-model="Users" @input="filterRecipients" placeholder="Search Messages "
                            class="outline-none h-full p-2 bg-gray-100" />
                    </div>
                    <div class="mr-1">
                        <Icon icon="ion:filter-sharp" width="35" />
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <TabMenu v-model:activeIndex="active" :model="items" :pt="{
            root: { class: 'text-primary' },
            inkbar: { class: 'text-primary' },
            action: ({ props, context, state }) => ({
                class: `${context.index === state.d_activeIndex
                    ? 'text-primary border-b-2 border-solid border-primary'
                    : ''
                    } p-1 pr-0 md:pr-1 md:p-3 pl-1 text-gray-500 -mb-[2px]`,
            }),
        }">
                    <template #item="{ item, props }">
                        <p v-bind="props.action" class="flex align-items-center gap-1 md:gap-2 text-sm md:text-sm">
                            <span class="font-semibold text-sm cursor-pointer">{{
            item.label
        }}</span>
                        </p>
                    </template>
                </TabMenu>
            </div>
            <!-- ======================  Branch Group ============="" -->
            <div v-if="active === 0">

                <div class="overflow-scroll h-[50vh] pb-20 hide_scroll px-3 pr-7 mt-5">
                    <div class="grid grid-cols-2 gap-5 justify-between">
                        <div v-for="(item, index) in Branch_group" :key="index" class="" @click="handleGroups(item)">
                            <div
                                class="flex items-center justify-center flex-col p-3 border border-solid cursor-pointer rounded-md border-gray-300">
                                <div class="text-primary border bg-white p-3 w-fit rounded-full">
                                    <Icon :icon="item.icon" width="30" />
                                </div>
                                <p class="mt-2 font-semibold">{{ item.name }}</p>
                                <small>({{ item.groups.length }} groups)</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- =============    Direct Messages    =========== -->
            <div class="" v-else>
                <div v-if="Users && Suggestions.length > 0" class="overflow-scroll h-[65vh] pb-10 hide_scroll">
                    <div v-for="(suggestion, index) in Suggestions" :key="index" class="cursor-pointer"
                        @click="toggleBackground(suggestion)">
                        <div class="flex items-center py-3 px-3 justify-between w-full"
                            :class="{ ' bg-[#F5F9FF]': open && clickedIndex === suggestion.id }">
                            <div class="flex items-center gap-3 w-full">
                                <img src="/images/display_picture.svg" alt="" class="flex-none" />
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <h1 class="font-semibold text-base">{{ suggestion.name }}</h1>
                                        <!-- <p>{{ suggestion.messages[0]?.message ? suggestion.messages[0]?.message : 'No
                                            messages yet'
                                            }}</p> -->
                                    </div>
                                    <div class="p-1 border rounded-full">
                                        <Icon icon="uil:angle-right-b" width="20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="overflow-scroll h-[65vh] pb-10 hide_scroll">
                    <div v-for="(item, index) in inbox" :key="index" class="cursor-pointer"
                        @click="toggleBackground(item)">
                        <div class="flex items-center py-3 px-3 justify-between w-full"
                            :class="{ ' bg-[#F5F9FF]': open && clickedIndex === item.user.id }">
                            <div class="flex items-center gap-3 w-full">
                                <img src="/images/display_picture.svg" alt="" class="flex-none" />
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <h1 class="font-semibold text-base">{{ item.user.first_name }}</h1>
                                        <p>{{ item.conversations && item.conversations.length > 0
            ? item.conversations[item.conversations.length - 1].message
            : 'No messages yet'
                                            }}</p>
                                    </div>
                                    <div class="p-1 border rounded-full">
                                        <Icon icon="uil:angle-right-b" width="20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="w-[25%] border-r border-solid border-gray-200 top-0 h-[100%] py-3" v-if="activeButton === 1">
            <div class="flex items-center gap-3 px-3">
                <div class="p-2 border rounded-md cursor-pointer" @click="activeButton = 0">
                    <Icon icon="material-symbols:arrow-back" width="30" />
                </div>
                <div>
                    <h1 class="font-semibold text-2xl">{{ groups?.name }}</h1>
                    <p class="opacity-70">
                        ({{ groups?.groups.length }})
                    </p>
                </div>
            </div>

            <div class="flex items-center border mx-3 my-5 border-solid rounded-3xl px-3 bg-gray-100">
                <Icon icon="lucide:search" class="text-lg text-gray-500" />
                <input type="text" :placeholder="groups?.name" class="outline-none h-full p-2 bg-gray-100" />
            </div>

            <div v-for="(item, index) in groups.groups" :key="index" class="cursor-pointer"
                @click="toggleBackground(item)">
                <!-- {{ item }} -->
                <div class="flex items-center py-3 px-3 justify-between w-full"
                    :class="{ ' bg-[#F5F9FF]': open && clickedIndex === item.id }">
                    <div class="flex items-center gap-3 w-full">
                        <div
                            class="text-primary border border-gray-300 bg-white p-2 rounded-full overflow-hidden flex-none">
                            <Icon :icon="item.icon" width="25" />
                        </div>
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h1 class="font-semibold text-base">{{ item.name }}</h1>
                                <p class="text-[#98A2B3] text-sm">
                                    {{ item?.users.length }} Members
                                </p>
                            </div>
                            <div class="p-1 border rounded-full">
                                <Icon icon="uil:angle-right-b" width="20" />
                            </div>
                        </div>
                    </div>
                    <div></div>
                </div>
            </div>
        </section>
        <section class="w-[75%]">
            <div v-if="open">
                <div v-if="active === 0 || active === 1">
                    <Messages :selectedMessage="selectedMessage" :clickedIndexGroup="clickedIndexGroup"
                        :clickedIndex="clickedIndex" :selectedMessageGroup="selectedMessageGroup"
                        :allMessagess="allMessagess" />
                </div>
            </div>
            <div v-else>
                <EmptyChatRoom />
            </div>
        </section>
    </main>

    <AddLink v-model="addLinkModal" />
    <ReportDialog v-model="reportModal" />
</template>

<script setup>
import { ref, onMounted } from "vue";
import EmptyChatRoom from "../../components/EmptyChatRoom.vue";
import ReportDialog from "../../components/Modals/chat/ReportDialog.vue";
import AddLink from "../../components/Modals/chat/AddLink.vue";
import Messages from "../../components/Messages.vue";
import { usePage } from "@inertiajs/vue3"

const clickedIndex = ref(null);
const clickedIndexGroup = ref(null);
const groups = ref(null);
const activeButton = ref(0);
const active = ref(0);
const addLinkModal = ref(false);
const reportModal = ref(false);
const open = ref(false);
const selectedMessage = ref();
const selectedMessageGroup = ref();
const allMessagess = ref([]);
const Branch_group = ref([])
const userGroups = ref([])
const inbox = ref([])
const Suggestions = ref([]);
const Users = ref("");
const page = usePage()

function toggleBackground(item) {
    console.log('such as', item);
    Users.value = "";
    Suggestions.value = [];
    selectedMessage.value = item;
    selectedMessageGroup.value = item;
    open.value = true;
    clickedIndex.value = item?.user?.id;
    clickedIndexGroup.value = item?.id;
}
function handleKeyDown(event) {
    if (event.key === "Escape") {
        open.value = false;
    }
}

document.addEventListener("keydown", handleKeyDown);
const message = ref("");

const items = ref([{ label: "Branch Groups" }, { label: "Direct Messages" }]);
const handleGroups = (item) => {
    activeButton.value = 1;
    groups.value = item;
    const newValues = ["gg:organisation", "icon-park-outline:fork", "tabler:box"]
    groups.value.groups.forEach((obj, index) => {
        obj.icon = newValues[0]
    })
    console.log("check am", groups.value)
    console.log(groups.value.groups);
};
const getAllCategories = async () => {
    const token = localStorage.getItem('token');
    try {
        const response = await axios.get('/dashboard/chat/categories', {
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
        console.log('display', response.data);
        Branch_group.value = response.data;
        const newValues = ["gg:organisation", "icon-park-outline:fork", "tabler:box"]
        Branch_group.value.forEach((obj, index) => {
            obj.icon = newValues[index]
        })
        Branch_group.value.forEach((obj, index) => {
            obj.icon = newValues[index]
        })
        Branch_group.value.forEach((obj, index) => {
            obj.icon = newValues[index]
        })
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
const getAllUserAndMessage = async () => {
    const token = localStorage.getItem('token');
    try {
        const response = await axios.get('/dashboard/chat/users', {
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
        console.log('display here again', response.data);
        inbox.value = response.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};


const filterRecipients = () => {
    const searchTerm = Users.value.toLowerCase();
    console.log(searchTerm);
    const combinedArray = inbox.value.concat(Branch_group.value);
    Suggestions.value = combinedArray.filter((item) => {
        if (item?.name) {
            return item?.name.toLowerCase().includes(searchTerm)
        }
        if (Array.isArray(item.groups)) {
            return item.groups.some(subItem => subItem?.name?.toLowerCase().includes(searchTerm.toLowerCase()));
        }
        return false;
    });
};
async function fetchUserGroups() {
    const token = localStorage.getItem('token');
    try {
        const response = await axios.get('/dashboard/chat/all-groups', {
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
        userGroups.value = response.data.groups;
        console.log("all user groups:", userGroups.value);
    } catch (error) {
        console.error('Error fetching user groups:', error);
    }
}
function listenToGroupMessages(groups) {
    groups.forEach(group => {
        let userId = window.authUserId;
        // window.Echo.private(`group.${group.id}`)
        //     .listen('GroupMessageSent', (event) => {
        //         console.log('from group:', group.id, event);
        //         allMessagess.value = event.message;
        //         console.log("listened", allMessagess.value.sender_id)
        //         allMessagess.value.is_sent_by_logged_in_user = allMessagess.value.sender_id === userId;
        //     });
    });
}
onMounted(async () => {
    try {
        await fetchUserGroups();
        listenToGroupMessages(userGroups.value);

        getAllUserAndMessage()
        getAllCategories()
        let userId = page.props.user.id;

        window.Echo.private(`chat.${userId}`)
            .listen('MessageSent', (event) => {
                allMessagess.value = event.message;
                allMessagess.value.is_sent_by_logged_in_user = allMessagess.value.sender_id === window.authUserId;
            });
    } catch (error) {
        console.error('Error during onMounted:', error);
    }

})
// const inbox = [
//     {
//         id: "1001",
//         Image: "/images/display_picture.svg",
//         name: "Jide Adeshina",
//         message: [
//             {
//                 date: "Yesterday",
//                 body: [
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },
//                 ],
//             },
//             {
//                 date: "Today",
//                 body: [
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },
//                 ],
//             },
//         ],
//     },
//     {
//         id: "1002",
//         Image: "/images/display_picture.svg",
//         name: "Lana Stainer",
//         message: [
//             {
//                 date: "Yesterday",
//                 body: [
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },
//                 ],
//             },
//             {
//                 date: "Today",
//                 body: [
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "User",
//                         content: "Hello, how are you?",
//                         timestamp: "Thursday 11:40am",
//                     },
//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },

//                     {
//                         sender: "Sola",
//                         content:
//                             "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                         timestamp: "Thursday 11:40am",
//                     },
//                 ],
//             },
//         ],
//     },
// ];

//   const Branch_group = [
//     {
//       icon: "gg:organisation",
//       branch: "Departments",
//       placeholder: "Search Department",
//       groups: [
//         {
//           id: "1003",
//           icon: "gg:organisation",
//           name: "Communication Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1004",
//           icon: "gg:organisation",
//           name: "Auditing Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1005",
//           icon: "gg:organisation",
//           name: "Sales Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1006",
//           icon: "gg:organisation",
//           name: "Budgeting Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1007",
//           icon: "gg:organisation",
//           name: "Outreach Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//       ],
//     },
//     {
//       icon: "icon-park-outline:fork",
//       branch: "Divisions",
//       placeholder: "Search Division",
//       groups: [
//         {
//           id: "10010",
//           icon: "icon-park-outline:fork",
//           name: "Communication Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1011",
//           icon: "icon-park-outline:fork",
//           name: "Auditing Division",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1013",
//           icon: "icon-park-outline:fork",
//           name: "Sales Unit",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1014",
//           icon: "icon-park-outline:fork",
//           name: "Budgeting Division",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1015",
//           icon: "icon-park-outline:fork",
//           name: "Outreach Unit",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//       ],
//     },
//     {
//       icon: "prime:box",
//       branch: "Units",
//       placeholder: "Search Unit",
//       groups: [
//         {
//           id: "1020",
//           icon: "prime:box",
//           name: "Communication Department",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1021",
//           icon: "prime:box",
//           name: "Auditing Division",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1023",
//           icon: "prime:box",
//           name: "Sales Unit",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1024",
//           icon: "prime:box",
//           name: "Budgeting Division",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//         {
//           id: "1025",
//           icon: "prime:box",
//           name: "Outreach Unit",
//           member: "12",
//           type: "group",
//           message: [
//             {
//               date: "Yesterday",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//             {
//               date: "Today",
//               body: [
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "User",
//                   content: "Hello, how are you?",
//                   timestamp: "Thursday 11:40am",
//                 },
//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },

//                 {
//                   sender: "Sola",
//                   content:
//                     "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum",
//                   timestamp: "Thursday 11:40am",
//                 },
//               ],
//             },
//           ],
//         },
//       ],
//     },
//   ];


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

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>
