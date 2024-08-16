<template>
    <main class="relative">
        <div class="flex items-center justify-between px-7 py-5">
            <div class="flex items-center gap-3">
                <div class="p-3 border rounded-full">
                    <Icon icon="ic:outline-mail" width="30" />
                </div>
                <div>
                    <h1 class="text-3xl font-semibold text-[#1D2939]">Mail</h1>
                </div>
            </div>
            <button class="flex items-center gap-2 bg-primary p-2 rounded-md text-white" @click="newMessage = true">
                <Icon icon="ic:baseline-plus" width="25" />
                <p>Create New Mail</p>
            </button>
        </div>
        <Divider class="-mt-1" />

        <section class="flex items-center justify-between px-7">
            <div class="flex items-center justify-between text-sm">
                <div class="bg-gray-100 flex items-center rounded-md p-1">
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('All')" :class="{ activeButton: buttonToggle === 1 }">
                        <p>Inbox</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('Archive')" :class="{ activeButton: buttonToggle === 2 }">
                        <p>Archive</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('Unread')" :class="{ activeButton: buttonToggle === 3 }">
                        <p>Unread</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('Starred')" :class="{ activeButton: buttonToggle === 4 }">
                        <p>Starred</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('Drafts')" :class="{ activeButton: buttonToggle === 5 }">
                        <p>Drafts</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                        @click="activeButton('Sent')" :class="{ activeButton: buttonToggle === 6 }">
                        <p>Sent</p>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-5" v-if="arrayMails.length !== 0">
                <div class="flex items-center gap-2">
                    <p>{{ pageStart + 1 }} - {{ pageEnd }}</p>
                    <p>of {{ totalPages }}</p>
                </div>
                <div class="flex items-center gap-5">
                    <Icon icon="flowbite:angle-right-outline" class="rotate-180 cursor-pointer" @click="previousPage"
                        :disabled="currentPage === 1" />
                    <Icon icon="flowbite:angle-right-outline" class="cursor-pointer" @click="nextPage"
                        :disabled="currentPage === totalPages" />
                </div>
            </div>
        </section>

        <div v-if="arrayMails.length !== 0">
            <section class=" mt-7 relative px-7">
                <div>
                    <!-- <DataTable
            v-model:selection="selectedDocument"
            :value="displayedItems"
            dataKey="id"
            :pt="{
              table: {
                class:
                  'w-full border border-border_primary border-solid mb-6 mt-2',
              },
            }"
            tableStyle="min-width: 60rem; border-radius: 30px;"
          >
            <Column
              selectionMode="multiple"
              headerStyle="width: 2rem; color: #667085;"
            ></Column>
            <Column field="" header="">
              <template #body="slotProps">
                <div class="flex items-center gap-3">
                  <button @click="addToFavorite(slotProps.data.id)">
                    <Icon
                      icon="emojione:star"
                      width="25 "
                      class="cursor-pointer"
                      v-if="isFavorite(slotProps.data.id)"
                    />
                    <Icon
                      icon="la:star"
                      width="25"
                      class="cursor-pointer"
                      v-else
                    />
                  </button>
                  <p class="text-[#F04438]" v-if="clickedButton === 'Drafts'">
                    Drafts
                  </p>
                </div>
              </template>
</Column>
<Column field="sender" header="From" v-if="clickedButton !== 'Drafts'">
    <template #body="slotProps">
                <div
                  class="flex items-center gap-2"
                  @click="gotoRoute(slotProps.data.id)"
                >
                  <img src="@/assets/images/linda.svg" alt="" class="w-10" />
                  <div>
                    <p class="text-base">{{ slotProps.data.sender }}</p>
                    <p class="text-sm">{{ slotProps.data.email }}</p>
                  </div>
                </div>
              </template>
</Column>
<Column field="subject" header="Subject" @click="gotoRoute(slotProps.data.id)"></Column>
<Column field="" header="Message">
    <template #body="slotProps">
                <div class="" @click="gotoRoute(slotProps.data.id)">
                  <p v-html="renderMessage(slotProps.data.message)"></p>
                </div>
              </template>
</Column>

<Column field="date" header="Date">
    <template #body="slotProps">
                <div class="flex items-center gap-2">
                  <p>{{ slotProps.data.date }}</p>
                  <div>
                    <div>
                      <button
                        @click="toggle"
                        aria-haspopup="true"
                        aria-controls="overlay_menu"
                      >
                        <Icon
                          class="cursor-pointer"
                          icon="mi:options-vertical"
                          width="25"
                        />
                      </button>
                      <Menu
                        ref="menu"
                        id="overlay_menu"
                        :model="menuItems"
                        :popup="true"
                      />
                    </div>
                  </div>
                </div>
              </template>
</Column>
</DataTable>
-->

                    <table class="w-full mb-7">
                        <thead class=" bg-gray-100 rounded-md overflow-hidden ">
                            <tr class="text-left">
                                <th class="py-6 pl-5">
                                    <!-- <input type="checkbox" v-model="allChecked" @change="toggleAll" class="h-5 w-5" /> -->
                                </th>
                                <th>From</th>
                                <th>Subject</th>
                                <!-- <th>Message</th> -->
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody v-if="clickedButton === 'Drafts'">
                            <tr v-for="(item, index) in draftsmails.data" :key="index" @click="openDraftModal(item)"
                                class=" hover:bg-gray-50 cursor-pointer border-b ">
                                <td class=" align-top pt-5 pl-5 ">
                                    <div class="flex items-center gap-2  ">
                                        <!-- <input @click.stop type="checkbox" v-model="checkedItems[index]"
                                            class="h-5 w-5" /> -->
                                        <button @click="addToFavorite(item.id)" @click.stop>
                                            <Icon icon="emojione:star" width="25 " class="cursor-pointer"
                                                v-if="item.is_starred" />
                                            <Icon icon="la:star" width="25" class="cursor-pointer" v-else />
                                        </button>
                                        <p class="text-[#F04438]" v-if="clickedButton === 'Drafts'">
                                            Drafts
                                        </p>
                                    </div>
                                </td>
                                <td class=" align-top py-5">
                                    <div class=" flex items-center gap-2">
                                        <div>
                                            <p class="text-base">{{ item.sender }}</p>
                                            <p class="text-sm">{{ item.recipient }}</p>
                                        </div>
                                    </div>
                                </td>


                                <td class=" align-top py-5">
                                    {{ item.subject }}
                                    <div class=" flex items-center gap-2 py-2 px-4 rounded-2xl mt-1 bg-slate-100 w-fit "
                                        v-if="item.Attached" @click.stop>
                                        <Icon :icon="item.icon" width="20" />
                                        <!-- <p class=" text-xs">{{ item.Attached }}</p> -->
                                    </div>
                                </td>
                                <td class=" align-top py-5">
                                    <div class="">
                                        <!-- <p v-html="renderMessage(item.body)"></p> -->
                                        <p>{{ new Date(item.created_at).toDateString() }}</p>
                                    </div>
                                </td>
                                <td class=" align-top py-5">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div>
                                                <button @click.stop @click="toggle" aria-haspopup="true"
                                                    aria-controls="overlay_menu">
                                                    <Icon class="cursor-pointer" icon="mi:options-vertical"
                                                        width="25" />
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                        <tbody v-else>
                            <tr v-for="(itemed, index) in filteredMails" :key="index"
                                class=" hover:bg-gray-50 cursor-pointer border-b ">
                                <td class=" align-top pt-5 pl-5 ">
                                    <div class="flex items-center gap-2  ">
                                        <!-- <input @click.stop type="checkbox" v-model="checkedItems[index]"
                                            class="h-5 w-5" /> -->
                                        <button @click="addToFavorite(itemed.id)" @click.stop>
                                            <Icon icon="emojione:star" width="25 " class="cursor-pointer"
                                                v-if="itemed.is_starred" />
                                            <Icon icon="la:star" width="25" class="cursor-pointer" v-else />
                                        </button>
                                        <p class="text-[#F04438]" v-if="clickedButton === 'Drafts'">
                                            Drafts
                                        </p>
                                    </div>
                                </td>
                                <Link :href="`/dashboard/mails/ReadMail/${itemed.id}`">
                                <td class=" align-top py-5">
                                    <div class=" flex items-center gap-2">
                                        <div>
                                            <p class="text-base">{{ itemed.sender }}</p>
                                            <p class="text-sm">{{ itemed.recipient }}</p>
                                        </div>
                                    </div>
                                </td>
                                </Link>

                                <td class=" align-top py-5">
                                    {{ itemed.subject }}
                                    <div class=" flex items-center gap-2 py-2 px-4 rounded-2xl mt-1 bg-slate-100 w-fit "
                                        v-if="itemed.Attached" @click.stop>
                                        <Icon :icon="item.icon" width="20" />
                                        <p class=" text-xs">{{ itemed.Attached }}</p>
                                    </div>
                                </td>
                                <td class=" align-top py-5">
                                    <div class="">
                                        <!-- <p v-html="renderMessage(item.body)"></p> -->
                                        <p>{{ new Date(itemed.created_at).toDateString() }}</p>
                                    </div>
                                </td>
                                <td class=" align-top py-5">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div>
                                                <button @click.stop @click="toggle" aria-haspopup="true"
                                                    aria-controls="overlay_menu">
                                                    <Icon class="cursor-pointer" @click="sendId(itemed?.id)" icon="mi:options-vertical"
                                                        width="25" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <Menu  ref="menu" id="overlay_menu" :model="menuItems" :popup="true">
                            <!-- <template #item="{item}">
                                <button @click='handleSelection(item)'>{{item.label}}</button>
                            </template> -->
                            </Menu>
                        </tbody>
                  </table>
                </div>
                <div></div>
            </section>
            <section class="flex flex-col items-center justify-center mb-10">
                <!-- <Divider /> -->
                <div class="w-full flex justify-between px-20">
                    <button class="text-[#667085] flex gap-3 items-center" @click="previousPage"
                        :disabled="currentPage === 1">
                        <i class="pi pi-arrow-left"></i>Previous
                    </button>
                    <span>
                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                            :class="{ active: currentPage === page }">
                            {{ page }}
                        </button>
                    </span>
                    <button class="text-[#667085] flex gap-3 items-center" @click="nextPage"
                        :disabled="currentPage === totalPages">
                        Next <i class="pi pi-arrow-right"></i>
                    </button>
                </div>
            </section>
        </div>

        <div v-else class="flex items-center h-[60vh] justify-center flex-col">
            <img src="/images/illustration.svg" alt="" />
            <h1 class="font-semibold text-lg">No Mail Yet</h1>
            <p>All Received mail would appear here.</p>
            <div class="text-[#052B0A] font-semibold border-gray-300 border-[1px] px-2 py-3 mt-3 rounded-md relative cursor-pointer flex justify-center items-center gap-2"
                @click="newMessage = true">
                <Icon icon="ic:outline-plus" width="25" />
                <h1>Compose Mail</h1>
            </div>
        </div>
        <div class="fixed right-10 bottom-20 cursor-pointer" @click="newMessage = true">
            <div class="flex flex-col items-center justify-center">
                <img src="/images/Bubble.svg" alt="" />
            </div>
        </div>
    </main>
    <NewMessageModal v-model="newMessage" :data="newSelectedValue" @refresh="refreshData" />
    <DeleteMailMessage v-model="newdelete" :data="toModal" @refresh="refreshData"/>
</template>


<script setup>
import { ref, computed, watch, defineProps, onMounted } from "vue";
import NewMessageModal from '../../components/Modals/Mail/NewMessage.vue';
import DeleteMailMessage from "../../components/Modals/Mail/DeleteMailMessage.vue";
import { Link, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";

const toast = useToast();

const newMessage = ref(false);
const newdelete = ref(false);
const buttonToggle = ref(1);
const isfavourite = ref(false);
const favorites = ref([]);
const draftsmails = ref([]);
const starredMails = ref([]);

const selectedItem = ref({});
const newSelectedValue = computed(()=>{
    return selectedItem.value
})
const pageStart = ref(1);
const pageEnd = ref(1);
const clickedButton = ref("All");
const currentPage = ref(1);
const itemsPerPage = 10;
const toModal = ref('');

const menu = ref();
// console.log("computed", newSelectedValue);

const props = defineProps(['mails'])
// console.log(props.mails)
const arrayMails = Object.keys(props.mails)


const refreshData = ()=>{
    router.reload({only: ['mails']})
}

const fetchdraftsMails = async () => {
    try {
        const response = await axios.get('/dashboard/mails/drafts');
        draftsmails.value = response.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
// console.log('Selected Item before:', selectedItem.value);



const openDraftModal = (item) => {
    newMessage.value = true;
    selectedItem.value = {...item};
    // console.log('Selected Item inside:', selectedItem.value);
};


onMounted(() => {
    fetchdraftsMails()
});



const addToFavorite = async (itemId) => {

    router.post('/dashboard/mails/star/' + itemId, {}, {onSuccess: (page)=>{
        toast.add({
            severity: "success",
            summary: "Mail starred successfully",
            detail: "Your message has been starred successfully.",
            life: 3000,
        });

        router.reload({only: ['mails']})
    }}, {onError: (page)=>{
        toast.add({
            severity: "danger",
            summary: "Mail wasn't starred",
            detail: error.message,
            life: 3000,
        });
    }})


    // if (isFavorite(itemId)) {
    //     favorites.value = favorites.value.filter((id) => id !== itemId);
    // } else {
    //     favorites.value.push(itemId);
    // }
};
const archiveMail = async (itemId) => {
    // try {
    //     const response = await axios.post('/dashboard/mails/archive/' + itemId);
    //     toast.add({
    //         severity: "success",
    //         summary: "Mail archived successfully",
    //         detail: "Your message has been archived successfully.",
    //         life: 3000,
    //     });
    // } catch (error) {
    //     console.error('Error:', error.message);
    //     toast.add({
    //         severity: "danger",
    //         summary: "Mail wasn't archived",
    //         detail: error.message,
    //         life: 3000,
    //     });
    // }

   
};

const isFavorite = (itemId) => {
    //return favorites.value.includes(itemId);

};



const menuItems = ref([
    { 
        label: "Archive",
        command: ()=>{
            router.post('/dashboard/mails/archive/' + toModal.value,{}, {onSuccess: (page)=>{
            toast.add({
                severity: "success",
                summary: "Mail archived successfully",
                detail: "Your message has been archived successfully.",
                life: 3000,
            });
            router.reload({only: ['mails']})
        }}, {onError: (page)=>{
            toast.add({
            severity: "danger",
            summary: "Mail wasn't archived",
            detail: error.message,
            life: 3000,
        });
        }})
        } 
    },
    { 
        label: "Star",
        command: ()=>{
            addToFavorite(toModal.value)
        } 
    },
    { 
        label: "Delete",
        command: ()=>{
            newdelete.value = true;
        }
    },
    { label: "Mark as Unread" },
]);
// console.log(menuItems);
// const handleSelection = (item) => {
//     if(item?.label == 'Delete'){
//         newdelete.value = true;
//     }
//     if(item?.label == 'Star'){
//         addToFavorite(toModal.value)
//     }
//     if(item?.label == 'Archive'){
//         archiveMail(toModal.value)
//     }
// };
const toggle = (event) => {
    menu.value.toggle(event);

};
const sendId = (id) =>{
    toModal.value = id;
    console.log('to props', toModal.value);
}

const activeButton = (event) => {
    if (event === "All") {
        buttonToggle.value = 1;
    }
    if (event === "Archive") {
        buttonToggle.value = 2;
    }
    if (event === "Unread") {
        buttonToggle.value = 3;
    }
    if (event === "Starred") {
        buttonToggle.value = 4;
    }
    if (event === "Drafts") {
        buttonToggle.value = 5;
    }
    if (event === "Sent") {
        buttonToggle.value = 6;
    }
    clickedButton.value = event;
};

const items = ref([
   
]);

const renderMessage = (message) => {
    const maxLength = 100;
    if (message.length > maxLength) {
        return `${message.substring(0, maxLength)}...`;
    } else {
        return message;
    }
};

const allChecked = ref(false);
const checkedItems = ref([]);
const totalItems = computed(() => items.value.length);

const displayedItems = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    pageStart.value = startIndex;
    pageEnd.value = endIndex;

    const filteredItems = items.value.filter((item) => {
        return item.status
            .toLowerCase()
            .includes(clickedButton.value.toLowerCase());
    });

    return filteredItems.slice(startIndex, endIndex);
});
const toggleAll = () => {
    checkedItems.value = Array(displayedItems.value.length).fill(
        allChecked.value
    );
};


// Watch for changes in allChecked to update checkedItems accordingly
watch(allChecked, (newVal) => {
    checkedItems.value = Array(displayedItems.value.length).fill(newVal);
});

// Watch for changes in checkedItems to update allChecked accordingly
watch(checkedItems, (newVal) => {
    allChecked.value = newVal.every(Boolean);
});

const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));

const visiblePages = computed(() => {
    if (totalPages.value <= 10) {
        return Array.from({ length: totalPages.value }, (_, index) => index + 1);
    } else {
        const start = Math.max(1, currentPage.value - 3);
        const end = Math.min(totalPages.value, currentPage.value + 3);
        let pages = [];
        if (start > 1) pages.push(1);
        if (start > 2) pages.push("...");
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        if (end < totalPages.value) pages.push("...");
        if (end < totalPages.value - 1) pages.push(totalPages.value);
        return pages;
    }
});

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

function previousPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function goToPage(page) {
    currentPage.value = page;
}

const filteredMails = computed(() => {
    let data = props.mails
    if (buttonToggle.value == 2) {
        return data = props.mails.filter((val) => val.is_archived == 1)
    } else if (buttonToggle.value == 4) {
        return data = props.mails.filter((val) => val.is_starred == 1)
    } else {
        return data = props.mails.filter((val)=> val.is_archived == 0);
    }
})
</script>

<style scoped>
.activeButton {
    background-color: white;
    border-radius: 5px;
    color: #0d6e1a;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}

.paginator {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

button {
    margin: 0 5px;
}

button.active {
    background-color: #0062ff22;
    height: 30px;
    width: 30px;
    border-radius: 5px;
    font-size: medium;
    color: #234e94;
}

.paginator span {
    margin: 0 10px;
}
</style>
