<template>
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
        <div class="flex items-center gap-5">
            <div class="border p-3 rounded-full">
                <Icon icon="fa:life-bouy" width="35" />
            </div>
            <div>
                <h1 class="text-3xl text-[#1D2939] font-semibold">
                    Help & Support Configuration
                </h1>
                <p class="text-gray-500">Customize help and support information.</p>
            </div>
        </div>
    </div>
    <Divider />

    <div class="px-7 flex justify-between items-center text-[#344054] font-medium">
        <button class="flex items-center gap-2 border border-solid rounded-3xl border-gray-300 p-3"
            @click="supportModal = true">
            <Icon icon="simple-line-icons:earphones-alt" />
            <p>Support Info</p>
        </button>

        <button class="flex items-center gap-2 border border-solid rounded-lg px-5 border-gray-300 p-3"
            @click="supportModal = true">
            <Icon icon="ri:edit-line" />
            <p>Configure</p>
        </button>
    </div>

    <div class="justify-between flex items-center px-7 mt-10">
        <div class="p-5 border border-solid rounded-md w-96">
            <div class="p-2 border rounded-full w-fit">
                <Icon icon="majesticons:chat-line" width="30" />
            </div>
            <h1 class="text-[#1D2939] font-semibold text-lg mt-2">
                Chat with support
            </h1>
            <p class="my-1">We’re here to help</p>
            <h1 class="text-[#1D2939] font-semibold text-lg">{{ support.email }}</h1>
        </div>

        <div class="p-5 border border-solid rounded-md w-96">
            <div class="p-2 border rounded-full w-fit">
                <Icon icon="carbon:location" width="30" />
            </div>
            <h1 class="text-[#1D2939] font-semibold text-lg mt-2">Visit Us</h1>
            <p class="my-1">Visit out Head office</p>
            <h1 class="text-[#1D2939] font-semibold text-lg">View on Google maps, {{ support.location }}</h1>
        </div>
        <div class="p-5 border border-solid rounded-md w-96">
            <div class="p-2 border rounded-full w-fit">
                <Icon icon="ph:phone" width="30" />
            </div>
            <h1 class="text-[#1D2939] font-semibold text-lg mt-2">Call Us</h1>
            <p class="my-1">{{ abbreviateDay(support.start_day) }} - {{ abbreviateDay(support.end_day) }} {{ formatTime(support.start_time) }} to {{
                formatTime(support.end_time) }}</p>
            <h1 class="text-[#1D2939] font-semibold text-lg">{{ support.phone_number }}</h1>
        </div>
    </div>

    <div class="px-7 mt-10 flex justify-between items-center text-[#344054] font-medium">
        <button class="flex items-center gap-2 border border-solid rounded-3xl border-gray-300 p-3">
            <Icon icon="radix-icons:question-mark-circled" width="25" />
            <p>Frequenly Asked Questions</p>
        </button>

        <button class="flex items-center gap-2 border border-solid rounded-lg px-5 border-gray-300 p-3"
            @click="faqModel = true">
            <Icon icon="ri:edit-line" />
            <p>Configure</p>
        </button>
    </div>

    <!-- ==============  faqs ============ -->
    <div class="m-7">
        <div v-for="(item, index) in faqs" :key="index" class="border mb-3 border-solid rounded-md p-3">
            <div class="flex items-center pr-5 justify-between">
                <h1 class="text-[#344054] font-medium">{{ item.question }}</h1>
                <div @click="toggleExpandable(index)">
                    <button>
                        <Icon v-if="expandedIndexes.includes(index)" icon="ic:baseline-minus" width="30" />
                        <Icon v-else icon="ic:baseline-plus" width="30" />
                    </button>
                </div>
            </div>
            <div class="text-sm opacity-50 w-[55%]" v-if="expandedIndexes.includes(index)">
                <p>{{ item.answer }}</p>
                <img v-if="item.image" :src="`/storage/${item.image}`" alt="FAQ Image" class="faq-image" />
            </div>
        </div>
    </div>

    <div class="px-7  mt-10 flex justify-between items-center text-[#344054] font-medium">
        <button class="flex items-center gap-2 border border-solid rounded-3xl border-gray-300 p-3">
            <Icon icon="mingcute:document-line" width="25" />
            <p>Documentation</p>
        </button>

        <button class="flex items-center gap-2 border border-solid rounded-lg px-5 border-gray-300 p-3"
            @click="documentationPop = true">
            <Icon icon="ri:edit-line" />
            <p>Configure</p>
        </button>
    </div>
    <div class=" mb-14">
        <div v-for="(item, index) in docs" :key="index"
            class="mb-3 mt-5 mx-7 border border-solid p-3 rounded-md">
            <div class="flex justify-between items-center text-[#344054] font-medium">
                <a :href="item.link">{{ item.name }}</a>
                <p>
                    <Icon icon="subway:right-up-corner-arrow-1" />
                </p>
            </div>
        </div>
    </div>
    <SupportInfo v-model="supportModal" />
    <FaqModal v-model="faqModel" />
    <DocumentationModal v-model="documentationPop" />
</template>

<script setup>
import { ref, onMounted } from "vue";
import SupportInfo from "../../components/Modals/SupportInfo.vue";
import FaqModal from "../../components/Modals/FaqModal.vue";
import DocumentationModal from "../../components/Modals/Documentation.vue"
const support = ref({})
const faqs = ref([])
const docs = ref([])
const faqModel = ref(false)
const documentationPop = ref(false)
const supportModal = ref(false);
const expandedIndexes = ref([]);
const toggleExpandable = (index) => {
    const indexPosition = expandedIndexes.value.indexOf(index);
    if (indexPosition === -1) {
        expandedIndexes.value.push(index);
    } else {
        expandedIndexes.value.splice(indexPosition, 1);
    }
};
const getSupport = async () => {
    try {
        const response = await axios.get('/dashboard/support/support-details');
        support.value = response.data.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
const getFaqs = async () => {
    try {
        const response = await axios.get('/dashboard/support/get-faqs');
        faqs.value = response.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
const getDocs = async () => {
    try {
        const response = await axios.get('/dashboard/support/get-docs');
        docs.value = response.data;
        console.log('docs here:', docs.value);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
function abbreviateDay(day) {
    const dayMap = {
        Monday: 'Mon',
        Tuesday: 'Tue',
        Wednesday: 'Wed',
        Thursday: 'Thu',
        Friday: 'Fri',
        Saturday: 'Sat',
        Sunday: 'Sun'
      };
      return dayMap[day] || day;
}
function formatTime(time) {
    if (!time || typeof time !== 'string') {
      return ''; // Return an empty string or a default value
    }
    const [hours] = time.split(':');
    const date = new Date();
    date.setHours(hours);

    const options = { hour: 'numeric', hour12: true };
    return date.toLocaleString('en-US', options);
}
onMounted(() => {
    getSupport()
    getFaqs()
    getDocs()
})
const Faq = [
    {
        title: "How do I upload a document?",
        body: " Departments are major organizational segments, divisions are sub-groups within departments, and units are further divisions within divisions. This hierarchical structure aids in efficient document management.",
    },
    {
        title: "How do I upload a document?",
        body: " Departments are major organizational segments, divisions are sub-groups within departments, and units are further divisions within divisions. This hierarchical structure aids in efficient document management.",
    },
    {
        title: "How do I upload a document?",
        body: " Departments are major organizational segments, divisions are sub-groups within departments, and units are further divisions within divisions. This hierarchical structure aids in efficient document management.",
    },
    {
        title: "How do I upload a document?",
        body: " Departments are major organizational segments, divisions are sub-groups within departments, and units are further divisions within divisions. This hierarchical structure aids in efficient document management.",
    },
];

const Documentation = [
    { label: "What is an EDMS application" },
    { label: "Account Security: Tips for a Secure Experience" },
    { label: "Account Security: Tips for a Secure Experience" },
    { label: "Understanding Document Types and Metadata" },
    { label: "Collaboration Features: An In-Depth Overview" },
];
</script>

<style></style>
