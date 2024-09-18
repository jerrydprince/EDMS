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
                        <div v-for="(item, index) in userGroups" :key="index" class="" @click="handleGroups(item, index)">
                            <div
                                class="flex items-center justify-center flex-col p-3 border border-solid cursor-pointer rounded-md border-gray-300">
                                <div class="text-primary border bg-white p-3 w-fit rounded-full">
                                    <!-- <Icon :icon="item.icon" width="30" /> -->
                                    <img :src="getImageForGroup(index)" alt="" />
                                   
                                </div>
                                <p class="mt-2 font-semibold">{{ index }}</p>
                                <small v-if="item && item.length > 0" class="capitalize">({{item.length}} <span class="capitalize">{{ index }}</span>)</small>
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
                    <div v-for="(item, index) in newbox" :key="index" class="cursor-pointer"
                        @click="toggleBackground(item)">
                        <div class="flex items-center py-3 px-3 justify-between w-full"
                            :class="{ ' bg-[#F5F9FF]': open && clickedIndex === item.id }">
                            <div class="flex items-center gap-3 w-full">
                                <img v-if="message.profile_image" :src="page.props.base+message.sender+'/'+message.profile_image" alt="">
                                    <img v-else src="/images/display_picture.svg" alt="" class="flex-none" />
                                <!-- <img src="/images/display_picture.svg" alt=""  /> -->
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <h1 class="font-semibold text-base">{{ item.user.first_name }}</h1>
                                        <p>{{ item.last_message == null?'No messages yet':item.last_message.message
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
                        ({{ groups.length }})
                    </p>
                </div>
            </div>

            <div class="flex items-center border mx-3 my-5 border-solid rounded-3xl px-3 bg-gray-100">
                <Icon icon="lucide:search" class="text-lg text-gray-500" />
                <input type="text" :placeholder="groups?.name" class="outline-none h-full p-2 bg-gray-100" />
            </div>

            <div v-for="(item, index) in groups" :key="index" class="cursor-pointer"
                @click="toggleBackground(item)">
                <!-- {{ item }} -->
                <div class="flex items-center py-3 px-3 justify-between w-full"
                    :class="{ ' bg-[#F5F9FF]': open && item.id == groupIndex  }">
                    <div class="flex items-center gap-3 w-full">
                        <div
                            class="text-primary border border-gray-300 bg-white p-2 rounded-full overflow-hidden flex-none">
                            <!-- <Icon :icon="item.icon" width="25" /> -->
                             <img :src="current_img" alt="">
                        </div>
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h1 class="font-semibold text-base">{{ item.title }}</h1>
                                <p class="text-[#98A2B3] text-sm">
                                    {{ item.users?item.users.length:'' }} Members
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
                        :allMessagess="allMessagess" :groupType="groupType" />
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
import { ref, onMounted, watch, computed } from "vue";
import {usePage} from "@inertiajs/vue3"
import EmptyChatRoom from "../../components/EmptyChatRoom.vue";
import ReportDialog from "../../components/Modals/chat/ReportDialog.vue";
import AddLink from "../../components/Modals/chat/AddLink.vue";
import Messages from "../../components/Messages.vue";



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
const Branch_group = ref([])
const userGroups = ref([])
const inbox = ref([])
const Suggestions = ref([]);
const Users = ref("");
const page = usePage()
const current_img = ref(null)
const groupType = ref(null)
const groupIndex = ref(null)
const user = computed(()=>page.props.user)
const userBranch = computed(()=>page.props.userBranch)
function toggleBackground(item) {
    groupIndex.value = item.id
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

const getUserBranch = ()=>{
    if(userBranch.value.unit){
        return 'unit'
    }else if(userBranch.value.division){
        return 'division'
    }else if(userBranch.value.department){
        return 'department'
    }
}

function getImageForGroup(item) {
    
    switch (item) {
      case 'department':
        return page.props.base+'/images/department_active.svg';
      case 'division':
        return page.props.base+'/images/division_active.svg';
      case 'unit':
      return page.props.base+'/images/unit_active.svg';
      default:
        return ''; // Fallback or default image
    }
  }

document.addEventListener("keydown", handleKeyDown);
const message = ref("");

const items = ref([{ label: "Branch Groups" }, { label: "Direct Messages" }]);
const handleGroups = (item, index) => {
    activeButton.value = 1;
    groups.value = item;
    current_img.value = getImageForGroup(index)

    if(index == 'department'){
        groupType.value = 'department'
    }else if(index == 'division'){
        groupType.value = 'division'
    }else{
        groupType.value = 'unit'
    }

    // const newValues = ["gg:organisation", "icon-park-outline:fork", "tabler:box"]
    // groups.value.forEach((obj, index) => {
    //     obj.icon = newValues[0]
    // })

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
watch(() => inbox.value, (checkbox) => {
    newbox.value = checkbox
    console.log("normal mesage", newbox.value);
    console.log("listened mesage", allMessagess.value);
})

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
        userGroups.value = response.data;

    } catch (error) {
        console.error('Error fetching user groups:', error);
    }
}
function listenToGroupMessages(groups) {
    //this line to be reviewed
    let total_length = Object.keys(groups).length
   
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

    for(let grp in groups){
        if(groups.hasOwnProperty(grp)){
            let grouptype = getUserBranch();
            let branch = null
            if(grouptype == 'unit'){
                branch = userBranch.value['unit'];
            }
            if(grouptype == 'division'){
                branch = userBranch.value['division']
            }
            if(grouptype == 'department'){
                branch = userBranch.value['department']
            }

            window.Echo.private(`group.${branch.id}.${grouptype}`)
            .listen('GroupMessageSent', (event) => {
               
                allMessagess.value = event.message;
               
                allMessagess.value.is_sent_by_logged_in_user = allMessagess.value.sender_id === user.value.id;
            });
           
        }
           
    }
   
}
onMounted(async () => {
    try {
        await fetchUserGroups();
        listenToGroupMessages(userGroups.value);

        getAllUserAndMessage()
        getAllCategories()
        let userId = user.value.id;
       
        window.Echo.private(`chat.${userId}`)
            .listen('MessageSent', (event) => {
                allMessagess.value = event.message;
                allMessagess.value.is_sent_by_logged_in_user = allMessagess.value.sender_id === $userId;
            });
        
    } catch (error) {
      
    }

})



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
