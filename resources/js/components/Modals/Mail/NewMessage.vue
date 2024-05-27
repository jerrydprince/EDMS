<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
    :style="{ width: '60rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <div
      class="bg-gray-200 w-full flex items-center justify-between -mt-14 rounded-t-xl p-3"
    >
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
            <div class="flex gap-3 flex-wrap items-center">
              <div v-if="Tags.length > 0" class="flex gap-2">
                <div
                  class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                  v-for="(tag, index) in Tags"
                  :key="index"
                >
                  <img :src="tag.img" alt="" class="w-5" />
                  <p class="text-xs w-fit">{{ tag.name }}</p>
                  <div class=" bg-white border rounded-full p-0.5 text-sm">
                  <Icon
                    icon="clarity:times-line"
                    class="cursor-pointer"
                    @click="removeTag(tag)"
                  />
                </div>
                </div>
              </div>
              <div class="relative">
                <input
                  type="text"
                  v-model="searchInput"
                  @input="filterRecipients"
                  class="outline-none"
                />
                <div
                  class="border border-solid py-3 bg-white rounded-md absolute z-50"
                  v-if="searchInput && Suggestions.length > 0"
                >
                  <div
                    class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                    v-for="(suggestion, index) in Suggestions"
                    :key="index"
                    @click="updateTags(suggestion)"
                  >
                    <img :src="suggestion.img" alt="" class="w-8" />
                    <p class="text-sm w-fit">{{ suggestion.name }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="flex items-center gap-2 text-sm font-normal"
            
          >
            <button
              class="border p-2 py-0.5 rounded-md hover:shadow"
              @click="Bcc = true"
            >
              Bcc
            </button>
            <button
              class="border p-2 py-0.5 rounded-md hover:shadow"
              @click="Cc = true"
            >
              Cc
            </button>
          </div>
        </div>

      
        <div
            class="flex items-center justify-between border-b pb-2 mt-3"
            v-if="Bcc"
          >
            <div class="flex items-center">
              <div class="flex items-start flex-wrap">
                <h1 class="mr-2">Bcc:</h1>
                <div class="flex gap-3 flex-wrap items-center">
                  <div v-if="BlindCarbonCopy.length > 0" class="flex gap-2">
                    <div
                      class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                      v-for="(tag, index) in BlindCarbonCopy"
                      :key="index"
                    >
                      <img :src="tag.img" alt="" class="w-5" />
                      <p class="text-xs w-fit">{{ tag.name }}</p>
                      <div class=" bg-white border rounded-full p-0.5 text-sm">
                      <Icon
                        icon="clarity:times-line"
                        class="cursor-pointer"
                        @click="removeBlindCopy(tag)"
                      />
                      </div>
                    </div>
                  </div>
                  <div class="relative">
                    <input
                      type="text"
                      v-model="BlindInput"
                      @input="filterBlindCopy"
                      class="outline-none"
                    />
                    <div
                      class="border border-solid py-3 bg-white rounded-md absolute z-50"
                      v-if="BlindInput && Suggestions.length > 0"
                    >
                      <div
                        class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                        v-for="(suggestion, index) in Suggestions"
                        :key="index"
                        @click="updateBlindCopy(suggestion)"
                      >
                        <img :src="suggestion.img" alt="" class="w-8" />
                        <p class="text-sm w-fit">{{ suggestion.name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class=" bg-white border rounded-full p-0.5 text-sm">
            <Icon
              icon="mdi:times"
              width="15"
              @click="closeBlindCarbonCopy"
              class="cursor-pointer"
            />
            </div>
          </div>

          <div
            class="flex items-center justify-between border-b pb-2 mt-3"
            v-if="Cc"
          >
            <div class="flex items-center">
              <div class="flex items-start">
                <h1 class="mr-2">Cc:</h1>
                <div class="flex gap-3 items-center flex-wrap">
                  <div v-if="CarbonCopy.length > 0" class="flex gap-2">
                    <div
                      class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                      v-for="(tag, index) in CarbonCopy"
                      :key="index"
                    >
                      <img :src="tag.img" alt="" class="w-5" />
                      <p class="text-xs w-fit">{{ tag.name }}</p>
                      <div class=" bg-white border rounded-full p-0.5 text-sm">
                      <Icon
                        icon="clarity:times-line"
                        class="cursor-pointer"
                        @click="removeCarbonCopy(tag)"
                      />
                      </div>
                    </div>
                  </div>
                  <div class="relative">
                    <input
                      type="text"
                      v-model="CarbonInput"
                      @input="filterCarbonCopy"
                      class="outline-none"
                    />
                    <div
                      class="border border-solid py-3 bg-white rounded-md absolute z-50"
                      v-if="CarbonInput && Suggestions.length > 0"
                    >
                      <div
                        class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                        v-for="(suggestion, index) in Suggestions"
                        :key="index"
                        @click="updateCarbonCopy(suggestion)"
                      >
                        <img :src="suggestion.img" alt="" class="w-8" />
                        <p class="text-sm w-fit">{{ suggestion.name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <div class=" bg-white border rounded-full p-0.5 text-sm">
            <Icon
              icon="mdi:times"
              width="15"
              @click="closeCarbonCopy"
              class="cursor-pointer"
            />
          </div>
          </div>
      <div class="flex items-center border-b pb-2 mt-3">
        <h1>Subject:</h1>
        <input type="text" class="outline-none border-0 pl-2 w-full" />
      </div>

      <div>
        <Editor v-model="Message" editorStyle="height: 40vh"
        class=" border-0 mt-10"
        placeholder="Type Message here..."
        />
      </div>
    </section>
<Divider/>
    <template  #footer>
            <div class=" flex items-center w-full justify-between">
                <Icon icon="material-symbols:delete-outline" width="25"  class=" text-[#F97066] cursor-pointer"  @click="closeModal"/>
              <div class=" flex items-center gap-2">

                <Icon
                icon="akar-icons:attach"
                width="25"
                class="cursor-pointer text-gray-400"

              />

              <Icon
                icon="ic:round-link"
                width="25"
                class="cursor-pointer text-gray-400"
                @click="addLinkModal = true"
              />
                <button
              class="flex items-center gap-2 bg-primary text-white px-5 rounded-md py-2 text-sm"
              @click="submitForm"
            >
              <p>Send Message</p>
              <Icon icon="tabler:send" />
            </button>
              </div>
            </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed,ref } from "vue";
//import image from "@/assets/images/members/1.svg";
import image from '../../../assets/images/members/1.svg'
import { useToast } from "primevue/usetoast";
const Message = ref('')
const Bcc = ref(false)
const Cc = ref(false)
const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

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

const closeModal = () => {
  emit("update:modelValue", false);
  Message.value = false
};

const submitForm = () => {
	toast.add({
    severity: "success",
    summary: "Message Sent",
    detail: "Your message has been sent successfully.",
    life: 3000,
  });
  emit("update:modelValue", false);
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
.custom-editor .p-editor-toolbar{
  display: none;
}
.p-inputtextarea:focus {
    border-color: #D0D5DD; /* Hide border */
}
</style>