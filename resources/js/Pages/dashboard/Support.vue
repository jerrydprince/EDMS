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

  

  <div class="justify-between flex items-center px-7 mt-10">
    <div class="p-5 border border-solid rounded-md w-96">
      <div class="p-2 border rounded-full w-fit">
        <Icon icon="majesticons:chat-line" width="30" />
      </div>
      <h1 class="text-[#1D2939] font-semibold text-lg mt-2">
        Chat with support
      </h1>
      <p class="my-1">We’re here to help</p>
      <h1 class="text-[#1D2939] font-semibold text-lg">Support@ansel.com</h1>
    </div>

    <div class="p-5 border border-solid rounded-md w-96">
      <div class="p-2 border rounded-full w-fit">
        <Icon icon="carbon:location" width="30" />
      </div>
      <h1 class="text-[#1D2939] font-semibold text-lg mt-2">Visit Us</h1>
      <p class="my-1">Visit out Head office</p>
      <h1 class="text-[#1D2939] font-semibold text-lg">View on Google maps</h1>
    </div>
    <div class="p-5 border border-solid rounded-md w-96">
      <div class="p-2 border rounded-full w-fit">
        <Icon icon="ph:phone" width="30" />
      </div>
      <h1 class="text-[#1D2939] font-semibold text-lg mt-2">Call Us</h1>
      <p class="my-1">Mon - Fri 8am to 5pm</p>
      <h1 class="text-[#1D2939] font-semibold text-lg">+234 90 73929401</h1>
    </div>
  </div>

  <div
    class="px-7 mt-10 flex justify-between items-start text-[#344054] font-medium"
  >
  <div class=" w-[25%]">
    <button
      class="flex items-center gap-2 border border-solid rounded-3xl border-gray-300 p-3"
      
    >
      <Icon icon="radix-icons:question-mark-circled" width="25" />
      <p>Frequenly Asked Questions</p>
    </button>

    <p class=" text-sm py-5 opacity-50">Explore our Frequently Asked Questions to find quick answers and gain insights into common queries about the platform. </p>

    <div class=" w-full p-5 gradient rounded-xl text-white">
      <h1>Still have a question? </h1>
      <p class=" text-xs text-gray-50 py-2 ">More Questions? Contact our helpful support team anytime.</p>
      <button class=" p-2 mt-5 rounded-md w-full bg-white text-primary font-normal text-sm" @click="helpModal=true">Message Us</button>
    </div>
  </div>

    
 <!-- ==============  faqs ============ -->
 <div class=" w-[67%]">
    <div
      v-for="(item, index) in Faq"
      :key="index"
      class="border mb-3 border-solid rounded-md p-3"
    >
      <div class="flex items-center pr-5 justify-between">
        <h1 class="text-[#344054] font-medium">{{ item.title }}</h1>
        <div @click="toggleExpandable(index)">
          <button>
            <Icon
              v-if="expandedIndexes.includes(index)"
              icon="ic:baseline-minus"
              width="30"
            />
            <Icon v-else icon="ic:baseline-plus" width="30" />
          </button>
        </div>
      </div>
      <div
        class="text-sm opacity-50 w-[55%]"
        v-if="expandedIndexes.includes(index)"
      >
        <p>{{ item.body }}</p>
      </div>
    </div>
  </div>
  </div>

 

  <!-- ===================  Documentations ======== -->

  <div
    class="px-7  mt-10 flex justify-between items-center text-[#344054] font-medium"
  >
    <button
      class="flex items-center gap-2 border border-solid rounded-3xl border-gray-300 p-3"
    >
      <Icon icon="mingcute:document-line" width="25" />
      <p>Documentation</p>
    </button>
  </div>
<div class=" mb-14">
  <div
    v-for="(item, index) in Documentation"
    :key="index"
    class="mb-3 mt-5 mx-7 border border-solid p-3 rounded-md"
  >
    <div class="flex justify-between items-center text-[#344054] font-medium">
      <h1>{{ item.label }}</h1>
      <p><Icon icon="subway:right-up-corner-arrow-1" /></p>
    </div>
  </div>
</div>
  <SupportInfo v-model="supportModal" />
  <Help  v-model="helpModal"/>
</template>

<script setup>
import { ref } from "vue";
import SupportInfo from "@/components/Modals/SupportInfo.vue";
import Help from "@/components/Modals/Help.vue";
const helpModal = ref(false)
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

<style scoped>


.gradient{

background: linear-gradient(90deg, #3575DD, #6294E56B 60%),

linear-gradient(300deg, #3575DD, rgba(0,0,255,0) 40%);
}
</style>
