<template>
    <Dialog v-model:visible="visible" :closable="false" modal :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '60rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=".custom-dialog">
        <div class="bg-gray-200 w-full flex items-center justify-between -mt-14 rounded-t-xl p-3">
            <h1 class="font-semibold">New Message</h1>
            <div class="flex items-center gap-2">
                <div class="bg-white text-[#101828] p-1 rounded-full">
                    <Icon icon="ic:baseline-minus" width="20" />
                </div>
                <div class="bg-white text-[#101828] p-1 rounded-full">
                    <Icon icon="tabler:arrows-diagonal" width="20" />
                </div>
                <div class="bg-white text-[#101828] p-1 rounded-full" @click="closeModal">
                    <Icon icon="mdi:times" width="20" />
                </div>
            </div>
        </div>
        <section class="px-7">
            <div class="my-2 flex items-center justify-between gap-2 border-b pb-2">
                <div class="flex items-start">
                    <h1 class="mr-2">Recipient:</h1>
                    <!-- <input type="text" v-model="formData.recipient"
                                class="outline-none" /> -->
                    <div class="flex gap-3 flex-wrap items-center">
                        <div v-if="Tags.length > 0" class="flex gap-2">
                            <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                                v-for="(tag, index) in Tags" :key="index">
                                <img :src="tag.img" alt="" class="w-5" />
                                <p class="text-xs w-fit">{{ tag.name }}</p>
                                <div class=" bg-white border rounded-full p-0.5 text-sm">
                                    <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeTag(tag)" />
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="text" v-model="form.recipient" @input="filterRecipients"
                                class="outline-none w-full" required />
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
                <div class="flex items-center gap-2 text-sm font-normal">
                    <button class="border p-2 py-0.5 rounded-md hover:shadow" @click="Bcc = true">
                        Bcc
                    </button>
                    <button class="border p-2 py-0.5 rounded-md hover:shadow" @click="Cc = true">
                        Cc
                    </button>
                </div>
            </div>


            <div class="flex items-center justify-between border-b pb-2 mt-3" v-if="Bcc">
                <div class="flex items-center">
                    <div class="flex items-start flex-wrap">
                        <h1 class="mr-2">Bcc:</h1>
                        <div class="flex gap-3 flex-wrap items-center">
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
                                <input type="text" v-model="form.bcc" @input="filterBlindCopy"
                                    class="outline-none w-full" />
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
                                <input type="text" v-model="form.cc" @input="filterCarbonCopy"
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
            <div class="flex items-center border-b pb-2 mt-3">
                <h1>Subject:</h1>
                <input type="text" v-model="form.subject" class="outline-none border-0 pl-2 w-full" required />
            </div>

            <div>
                <!-- <textarea  v-model="formData.body" name="" id="" cols="30" rows="10"></textarea> -->
                <Editor v-model="form.body" editorStyle="height: 40vh" class=" border-0 mt-10"
                    placeholder="Type Message here..." required />
                <input type="file" style="display: none;" ref="fileInput" @change="handleFileChange" />
            </div>
        </section>
        <Divider />
        <template #footer>
            <div class=" flex items-center w-full justify-between">
                <Icon icon="material-symbols:delete-outline" width="25" class=" text-[#F97066] cursor-pointer"
                    @click="closeModal" />
                <div class=" flex items-center gap-2">

                    <Icon @click="triggerFileInput" icon="akar-icons:attach" width="25"
                        class="cursor-pointer text-gray-400" />

                    <Icon icon="ic:round-link" width="25" class="cursor-pointer text-gray-400"
                        @click="addLinkModal = true" />
                    <button class="flex items-center gap-2 bg-primary text-white px-5 rounded-md py-2 text-sm"
                        @click="submitForm">
                        <p>Send Message</p>
                        <Icon icon="tabler:send" />
                    </button>
                    {{ anchorElement }}
                </div>
            </div>
        </template>
    </Dialog>
    <NewLinkModel v-model="addLinkModal" @data="fromLinkModel" />
</template>

<script setup>
import NewLinkModel from '../chat/AddLink.vue'
import { defineEmits, defineProps, onMounted, computed, ref, watch } from "vue";
import axios from "axios";
import image from '../../../assets/images/members/1.svg'
import { useToast } from "primevue/usetoast";
import { router, useForm } from '@inertiajs/vue3';

const Message = ref('')
const Bcc = ref(false)
const Cc = ref(false)
const fileInput = ref(null);

const addLinkModal = ref(false)

const toast = useToast();
const props = defineProps(["modelValue", "data"]);
// console.log("recipient", props?.data?.recipient);
const emit = defineEmits(["update:modelValue"]);
//const form = new FormData()

const formData = ref({
    recipient: '',
    subject: '',
    body: '',
    bcc: '',
    cc: ''
});

const form = useForm({
    recipient: '',
    subject: '',
    body: '',
    bcc: '',
    cc: ''
})


const functionCalled = ref(false);
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
    {
        name: "Obadimu charles",
        img: image,
    },
    {
        name: "Jefferson Chisom",
        img: image
    }
]);
const Suggestions = ref([]);
const Tags = ref([]);
const BlindCarbonCopy = ref([]);
const CarbonCopy = ref([]);


watch(props, () => {
   
    if (props?.data) {
        const newFormData = {
            recipient: props?.data?.recipient ?? '',
            subject: props?.data?.subject ?? '',
            body: props?.data?.body ?? '',
            bcc: '',
            cc: ''
        }
        formData.value = newFormData;
    }
})

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    } else {
        console.error('fileInput ref is not defined.');
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    console.log(file)
    if (file) {
        formData.value.body += file.name;
        form.append("doc", file)
    }
};
const fromLinkModel = (linkData) => {
    const anchorElement = document.createElement('a');
    anchorElement.href = `https://${linkData?.link}`;
    anchorElement.target = '_blank';
    anchorElement.style.color = 'blue';
    anchorElement.style.textDecoration = 'underline';
    anchorElement.style.cursor = 'pointer';
    anchorElement.textContent = linkData?.text;
    // anchorElement.addEventListener('click', redirectToHref);
    console.log(anchorElement);
    // formData.value.body.parentNode.appendChild(anchorElement);
    formData.value.body += anchorElement
}
// const redirectToHref = (event) => {
//     event.preventDefault(); // Prevents the default behavior of the click event
//     window.location.href = event.target.href; // Redirects to the href address
// };

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

const visible = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    },
});

// const validateForm = () => {
//     if (!formData.recipient || !formData.body || !formData.subject) {
//         toast.add({
//             severity: "danger",
//             summary: "Empty email",
//             detail: "Please provide email details.",
//             life: 3000,
//         });
//         return false
//     }
//     return true;
// };
const submitForm = async () => {
  
    //try {
        // form.append("recipient", formData.value.recipient)
        // form.append("body", formData.value.body)
        // form.append("subject", formData.value.subject)
        // form.append("cc", formData.value.cc)
        // form.append("bcc", formData.value.bcc)

        router.post('/dashboard/mails',form, {onSuccess: page =>{
            toast.add({
                severity: "success",
                summary: "Message Sent",
                detail: "Your message has been sent successfully.",
                life: 3000,
            });
            
            functionCalled.value = true;
            emit("update:modelValue", false);
        }})

    //     const response = await axios.post('/dashboard/mails', form,
    //         {
    //             headers: {
    //                 'Content-Type': 'multipart/form-data'
    //             }
    //         });
        
    //     formData.value = {}
    // } catch (error) {
    //     console.error('Error:', error.message);
    // }
    
};
const closeModal = async () => {
    try {
        if (functionCalled.value === false && formData.value !== null) {
            const response = await axios.post('/dashboard/mails/drafts', formData.value);
            console.log('Response:', response.data); // Log the response
        }
    } catch (error) {
        console.error('Error:', error.message);
    }
    emit("update:modelValue", false);
    Message.value = false
};

</script>
<style scoped>
.custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
}

.p-inputtextarea {
    outline: none;
    box-shadow: none !important;
    border: none;
    resize: none;
    margin-top: 10px;
    padding: 0;
    width: 100%
}

.custom-editor .p-editor-toolbar {
    display: none;
}

.p-inputtextarea:focus {
    border-color: #D0D5DD;
    /* Hide border */
}
</style>
