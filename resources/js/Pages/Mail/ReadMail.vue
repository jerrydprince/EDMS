<template>
    <main class="p-7">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div @click="goBack"
                    class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3">
                    <i class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"></i>
                    <h1>Back</h1>
                </div>
                <Icon icon="bi:archive" width="25" class="ml-4" />
                <Icon icon="material-symbols:delete-outline" width="25" class=" cursor-pointer"
                    @click="DeleteMessage = true" />
                <Icon icon="carbon:printer" width="25" />
                <Icon icon="gg:minimize" width="25" />
            </div>
            <div>
                <div class="flex items-center gap-5">
                    <div class="flex items-center gap-2">
                        <p>1 - 10</p>
                        <p>of 50</p>
                    </div>
                    <div class="flex items-center gap-5">
                        <Icon icon="flowbite:angle-right-outline" class="rotate-180 cursor-pointer" />
                        <Icon icon="flowbite:angle-right-outline" class="cursor-pointer" />
                    </div>
                    <Icon icon="tabler:star" />
                </div>
            </div>
        </div>

        <!-- ======================    MESSAGE BODY   =============== -->
        <div class="w-full border-[1.5px] border-gray-300 rounded-xl p-10 my-10" v-for="(item, key) in mail.data" :key="key">
            <div class="flex items-center w-full justify-between" >
                <div class="flex items-center gap-5">
                    <div>
                        {{ item.sender ? item.sender : 'Blank' }}
                        <div class="flex items-center gap-1 text-xs">
                            <p>{{ item.recipient }}</p>
                            <p>{{ new Date(item.created_at).toDateString() }}<small> (20 minutes ago) </small></p>
                        </div>
                    </div>
                </div>
                <button class="text-sm text-white bg-primary py-2 px-3 rounded-md flex items-center gap-2"
                    v-if="respond === false" @click="handleReply('Reply')">
                    <Icon icon="material-symbols:reply" />
                    <p>Reply Message</p>
                </button>
            </div>
            <div class="mt-10">
                <h1 class="font-semibold text-xl">{{item.subject}}</h1>
                <!-- <p v-html="message"></p> -->
                <p v-html="item.body"></p>
                <div v-for="(doc, index) in item?.attachments" :key="index">
                    <embed :src="'\storage\app/' + doc?.file_path" />
                </div>
                <Divider />
                <!-- Copy\storage\app/public/sNIwuIWBqb1REKCrKhcUKfdBwmE254eyzsCL2mzz.docx -->
                <!-- <div class="border-b mb-5 grid grid-cols-2 gap-10">
                    <div v-for="(document, index) in documents" :key="index"
                        class="border rounded-md p-4 flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                                <Icon :icon="document.icon" width="25" />
                            </div>
                            <div>
                                <h1>{{ document.name }}</h1>
                                <p class="text-sm text-gray-400">{{ document.size }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Icon icon="ph:eye-light" width="25" />
                            <Icon icon="material-symbols-light:download" width="25" />
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-5">
                    <button class="text-sm py-1.5 px-5 rounded-md flex items-center gap-2 border-[2px]"
                        v-if="respond === false" @click="handleReply('Reply')">
                        <Icon icon="material-symbols:reply" width="20" />
                        <p>Reply</p>
                    </button>

                    <button class="text-sm py-1.5 px-5 rounded-md flex items-center gap-2 border-[2px]"
                        v-if="respond === false" @click="handleReply('Forward')">
                        <p>Forward</p>
                        <Icon icon="lucide:forward" width="20" />
                    </button>
                </div> -->
            </div>
        </div>

        <!-- =================     Reply   or forward message     =========== -->
        <div ref="scrollTarget" class="w-full border-[1.5px] pb-5 border-gray-300 rounded-xl overflow-hidden my-10"
            v-if="respond">
            <div class="bg-gray-100 px-3" v-if="respondAction === 'Reply'">
                <button class="text-sm py-2 px-3 rounded-md flex items-center gap-2">
                    <Icon icon="material-symbols:reply" />
                    <p>Reply Message</p>
                </button>
            </div>

            <div class="bg-gray-100 px-3" v-else>
                <button class="text-sm py-2 px-3 rounded-md flex items-center gap-2">
                    <Icon icon="lucide:forward" width="20" />
                    <p>Forward Message</p>
                </button>
            </div>
            <div class="mx-6">
                <div class="my-2 flex items-center justify-between gap-2 border-b pb-2">
                    <div class="flex items-start">
                        <h1 class="mr-2">Recipient:</h1>
                        <div class="flex gap-3 items-center flex-wrap">
                            <div v-if="Tags.length > 0" class="flex gap-2">
                                <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                                    v-for="(tag, index) in Tags" :key="index">
                                    <img :src="tag.img" alt="" class="w-5" />
                                    <p class="text-xs w-fit">{{ tag.name }}</p>
                                    <div class=" bg-white border rounded-full p-0.5 text-sm">
                                        <Icon icon="clarity:times-line" class="cursor-pointer"
                                            @click="removeTag(tag)" />
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="text" v-model="searchInput" @input="filterRecipients"
                                    class="outline-none" />
                                <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
                                    v-if="searchInput && Suggestions.length > 0">
                                    <div class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                                        v-for="(suggestion, index) in Suggestions" :key="index"
                                        @click="updateTags(suggestion)">
                                        <img :src="suggestion.img" alt="" class="w-8" />
                                        <p class="text-sm w-fit">{{ suggestion.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm font-normal" v-if="respondAction === 'Forward'">
                        <button class="border p-2 py-0.5 rounded-md hover:shadow" @click="Bcc = true">
                            Bcc
                        </button>
                        <button class="border p-2 py-0.5 rounded-md hover:shadow" @click="Cc = true">
                            Cc
                        </button>
                    </div>
                </div>
                <div v-if="respondAction === 'Forward'">
                    <div class="flex items-center justify-between border-b pb-2 mt-3" v-if="Bcc">
                        <div class="flex items-center">
                            <div class="flex items-start flex-wrap">
                                <h1 class="mr-2">Bcc:</h1>
                                <div class="flex gap-3 items-center flex-wrap">
                                    <div v-if="BlindCarbonCopy.length > 0" class="flex gap-2">
                                        <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                                            v-for="(tag, index) in BlindCarbonCopy" :key="index">
                                            <img :src="tag.img" alt="" class="w-5" />
                                            <p class="text-xs w-fit">{{ tag.name }}</p>
                                            <div class=" bg-white border rounded-full p-0.5 text-sm">
                                                <Icon icon="clarity:times-line" class="cursor-pointer"
                                                    @click="removeBlindCopy(tag)" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input type="text" v-model="BlindInput" @input="filterBlindCopy"
                                            class="outline-none" />
                                        <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
                                            v-if="BlindInput && Suggestions.length > 0">
                                            <div class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                                                v-for="(suggestion, index) in Suggestions" :key="index"
                                                @click="updateBlindCopy(suggestion)">
                                                <img :src="suggestion.img" alt="" class="w-8" />
                                                <p class="text-sm w-fit">{{ suggestion.name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=" bg-white border rounded-full p-0.5 text-sm">
                            <Icon icon="mdi:times" width="15" @click="closeBlindCarbonCopy" class="cursor-pointer" />
                        </div>
                    </div>
                    <div class="flex items-center justify-between border-b pb-2 mt-3" v-if="Cc">
                        <div class="flex items-center">
                            <div class="flex items-start">
                                <h1 class="mr-2">Cc:</h1>
                                <div class="flex gap-3 items-center flex-wrap">
                                    <div v-if="CarbonCopy.length > 0" class="flex gap-2">
                                        <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                                            v-for="(tag, index) in CarbonCopy" :key="index">
                                            <img :src="tag.img" alt="" class="w-5" />
                                            <p class="text-xs w-fit">{{ tag.name }}</p>
                                            <div class=" bg-white border rounded-full p-0.5 text-sm">
                                                <Icon icon="clarity:times-line" class="cursor-pointer"
                                                    @click="removeCarbonCopy(tag)" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input type="text" v-model="CarbonInput" @input="filterCarbonCopy"
                                            class="outline-none" />
                                        <div class="border border-solid py-3 bg-white rounded-md absolute z-50"
                                            v-if="CarbonInput && Suggestions.length > 0">
                                            <div class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                                                v-for="(suggestion, index) in Suggestions" :key="index"
                                                @click="updateCarbonCopy(suggestion)">
                                                <img :src="suggestion.img" alt="" class="w-8" />
                                                <p class="text-sm w-fit">{{ suggestion.name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=" bg-white border rounded-full p-0.5 text-sm">
                            <Icon icon="mdi:times" width="15" @click="closeCarbonCopy" class="cursor-pointer" />
                        </div>
                    </div>
                </div>
            </div>

            <h1 class="text-[#7A5AF8] ml-6 my-3" v-if="respondAction === 'Forward'">
                ------Forwarded Message------
            </h1>
            <div class="px-6">
                <Editor v-model="SendMessage" editorStyle="height: 40vh" v-if="respondAction === 'Reply'"
                    class=" border-0" placeholder="Type Message here..." />
                <p v-html="message" v-if="respondAction === 'Forward'"></p>
            </div>
            <Divider />
            <div class="flex items-center px-6 w-full justify-between">
                <Icon icon="material-symbols:delete-outline" width="25" class="text-[#F97066] cursor-pointer"
                    @click="closeMessage" />
                <div class="flex items-center gap-2">
                    <Icon icon="akar-icons:attach" width="25" class="cursor-pointer text-gray-400" />

                    <Icon icon="ic:round-link" width="25" class="cursor-pointer text-gray-400"
                        @click="addLinkModal = true" />
                    <button class="flex items-center gap-2 bg-primary text-white px-5 rounded-md py-2 text-sm"
                        @click="submitForm">
                        <p>Send Message</p>
                        <Icon icon="tabler:send" />
                    </button>
                </div>
            </div>
        </div>
    </main>
    <DeleteMailModal v-model="DeleteMessage" />
</template>

<script setup>
import { ref, onMounted, defineProps } from "vue";
import DeleteMailModal from '../../components/modals/Mail/DeleteMail.vue';
import image from '../../assets/images/members/1.svg'
import { usePage } from "@inertiajs/vue3";

const props = defineProps(['id']);
console.log(props);

const mail = ref([]);
const getSingleMail = async () => {
    try {
        const response = await axios.get(`/admin/mails/SingleMail/${props.id}`);
        mail.value = response.data;
        console.log(mail.value)
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
onMounted(() => {
    getSingleMail()
})
const DeleteMessage = ref(false);
const respond = ref(false);
const respondAction = ref("Reply");
const Bcc = ref(false);
const Cc = ref(false);
const SendMessage = ref("");
const page = usePage()

const message = "<h1>Dear [Recipient's Name],</h1> <br>   <p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>";

const scrollTarget = ref(null);
const handleReply = (action) => {
    respondAction.value = action;
    respond.value = true;
    if (scrollTarget.value) {
        const topPosition = scrollTarget.value.offsetTop;
        window.scrollTo({
            top: topPosition,
        });
    }

};
const goBack = () => {
    router.go(-1);
};

const searchInput = ref("");
const BlindInput = ref("");
const CarbonInput = ref("");
const recipients = ref([
    {
        name: "Jenny Williams",
        img: image,
    },
    {
        name: "Dave Miye",
        img: image,
    },
]);
const Suggestions = ref([]);
const Tags = ref([]);
const BlindCarbonCopy = ref([]);
const CarbonCopy = ref([]);

const filterRecipients = () => {
    const searchTerm = searchInput.value.toLowerCase();
    Suggestions.value = recipients.value.filter((recipient) =>
        recipient.name.toLowerCase().includes(searchTerm)
    );
};
const updateTags = (suggestion) => {
    searchInput.value = "";
    Suggestions.value = [];

    const tagExists = Tags.value.some((tag) => tag.name === suggestion.name);

    if (!tagExists) {
        Tags.value.push(suggestion);
    }
};

const removeTag = (tagToRemove) => {
    const index = Tags.value.findIndex((tag) => tag === tagToRemove);
    if (index !== -1) {
        Tags.value.splice(index, 1);
    }
};

const filterBlindCopy = () => {
    const searchTerm = BlindInput.value.toLowerCase();
    Suggestions.value = recipients.value.filter((recipient) =>
        recipient.name.toLowerCase().includes(searchTerm)
    );
};
const updateBlindCopy = (suggestion) => {
    BlindInput.value = "";
    Suggestions.value = [];

    const tagExists = BlindCarbonCopy.value.some(
        (tag) => tag.name === suggestion.name
    );

    if (!tagExists) {
        BlindCarbonCopy.value.push(suggestion);
    }
};

const removeBlindCopy = (tagToRemove) => {
    const index = BlindCarbonCopy.value.findIndex((tag) => tag === tagToRemove);
    if (index !== -1) {
        BlindCarbonCopy.value.splice(index, 1);
    }
};

const filterCarbonCopy = () => {
    const searchTerm = CarbonInput.value.toLowerCase();
    Suggestions.value = recipients.value.filter((recipient) =>
        recipient.name.toLowerCase().includes(searchTerm)
    );
};
const updateCarbonCopy = (suggestion) => {
    CarbonInput.value = "";
    Suggestions.value = [];

    const tagExists = CarbonCopy.value.some(
        (tag) => tag.name === suggestion.name
    );

    if (!tagExists) {
        CarbonCopy.value.push(suggestion);
    }
};

const removeCarbonCopy = (tagToRemove) => {
    const index = CarbonCopy.value.findIndex((tag) => tag === tagToRemove);
    if (index !== -1) {
        CarbonCopy.value.splice(index, 1);
    }
};
const closeBlindCarbonCopy = () => {
    Bcc.value = false;
    BlindCarbonCopy.value = [];
};
const closeCarbonCopy = () => {
    Cc.value = false;
    CarbonCopy.value = [];
};

const documents = ref([
    {
        name: "Tech design requirements.pdf",
        size: "200 KB ",
        icon: "vscode-icons:file-type-word",
    },
    {
        name: "Tech design requirements.pdf",
        size: "200 KB ",
        icon: "vscode-icons:file-type-excel2",
    },
]);
const closeMessage = () => {
    respondAction.value = 'Reply'
    respond.value = false;
    Bcc.value = false;
    Cc.value = false;
};
</script>

<style scoped>
.p-inputtextarea {
    outline: none;
    box-shadow: none !important;
    border: none;
    resize: none;
    margin-top: 10px;
    padding: 0;
    width: 100%;
}

.p-inputtextarea:focus {
    border-color: #d0d5dd;
    /* Hide border */
}
</style>
