<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: {
      class:
        'bg-white px-5 max-h-[97vh] hide_scroll overflow-y-auto  rounded-md overflow-hidden',
    },
    content: { class: 'px-0  pb-6' },
    header: { class: ' bg-slate-100 -mt-2 ' },

    footer: { class: 'bg-white' },
  }" :style="{ width: '45rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 p-3 justify-end w-full">
        <div class="flex gap-5 bg-slate-100 items-center px-5">
          <Icon icon="grommet-icons:attachment" width="25" />
          <Icon icon="mingcute:delete-2-line" width="25" @click="closeModal" />
          <button class="p-2 border-[2px] border-solid rounded-md bg-white" @click="submit">
            Edit Task
          </button>
        </div>
      </div>
    </template>
    <section class="bg-white p-5">
      <input v-model="title" type="text" class="w-full text-3xl outline-none" placeholder="Write a Task Name" />
    </section>
    <Divider class="-mt-0" />
    <section class="p-5 text-gray-600">
      <div>
        <div class="flex items-center gap-14">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:users" />
            <p>Assign To</p>
          </div>

          <div
            class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300">
            <img :src="data.img" alt="" class="w-5" />
            <p class="text-xs w-fit">{{ props.task.title }}</p>

          </div>
        </div>

        <div class="flex items-center gap-14 mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:calendar" />
            <p>Due date</p>
          </div>
          <div class="relative" v-if="!date">
            <div class="flex bg-slate-50 py-1.5 px-3 rounded-2xl items-center gap-2">
              <p>Set deadline</p>
              <Icon icon="lucide:plus" />
            </div>
            <div class="absolute top-0 opacity-0">
              <Calendar v-model="date" />
            </div>
          </div>

          <div
            class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
            v-else>
            <p class="text-xs w-fit">{{ formattedDate }}</p>
            <div class="bg-white border rounded-full p-0.5 text-sm">
              <Icon icon="clarity:times-line" class="cursor-pointer" @click="date = null" />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-24 mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:calendar" />
            <p>Tags</p>
          </div>
          <div class="relative" v-if="!priority">
            <div class="flex bg-slate-50 py-1.5 px-3 rounded-2xl items-center gap-2" @click="select_Priority = true">
              <Icon icon="lucide:plus" />
              <p>Set Priority</p>
            </div>

            <div class="z-50 absolute top-12 bg-white border flex items-center gap-5 p-5 rounded-xl shadow-lg"
              v-if="select_Priority">
              <div v-for="(priority, index) in Priorities" :key="index" class=" " @click="updatePriority(priority)">
                <p class="bg-[#FEFBF6] px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
    color:
      priority === 'Low'
        ? '#FFBC53'
        : priority === 'High'
          ? '#FF3737'
          : priority === 'Medium'
            ? '#F67E5A'
            : 'black',
  }">
                  {{ priority }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
    color:
      priority === 'Low'
        ? '#FFBC53'
        : priority === 'High'
          ? '#FF3737'
          : priority === 'Medium'
            ? '#F67E5A'
            : 'black',
  }" v-else>
            <p class="text-xs w-fit">{{ priority }}</p>
            <div class="">
              <Icon icon="clarity:times-line" class="cursor-pointer" @click="priority = ''" />
            </div>
          </div>
        </div>

        <div class="mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:file-text" />
            <p>Task Description</p>
          </div>

          <textarea name="" id="" v-model="description" cols="30" rows="5"
            class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
      </textarea>
        </div>

        <div class="mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="grommet-icons:attachment" width="20" />
            <p>Attached Document</p>
          </div>

          <div class="w-[70%] lg:w-[100%] mt-3 gap-4 flex items-center">
            <div class="border p-3 rounded-md">
              <div class="flex justify-between">
                <div class="flex items-center gap-1 justify-center">
                  <img src="/assets/images/file.svg" alt="" class="w-7" />
                  <div class="">
                    <h1 class="text-[#1D2939]">Dmiye.pdf</h1>
                    <div class="flex items-center gap-5">
                      <p class="text-xs leading-7 text-[#667085]">
                        60 KB of 120 KB.
                      </p>
                      <span class="flex items-center text-xs gap-1">
                        <img src="/assets/images/loading.svg" alt="" />
                        Uploading...
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <i class="pi pi-times-circle cursor-pointer text-[#1D2939]"></i>
                </div>
              </div>
              <!-- ========== Progress bar  ========== -->

              <div class="w-full h-2 bg-slate-200 mt-2 rounded-md overflow-hidden">
                <div class="w-[50%] h-[100%] bg-primary"></div>
              </div>
            </div>

            <div class="p-7 border-dashed border-[3px] rounded-md">
              <Icon icon="lucide:plus" width="30" />
            </div>
          </div>
        </div>
        <div class="mt-5">
          <p>Submission Type</p>

          <div v-for="(item, index) in type" :key="index" class="flex mt-3 items-center gap-5">
            <input type="radio" v-model="selectedType" :value="index" />
            <p class="text-black font-semibold">{{ item }}</p>
          </div>
        </div>
      </div>
    </section>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, onMounted } from "vue";
import image from "../../../assets/images/members/1.svg";
import { useToast } from "primevue/usetoast";
const selectedType = ref(null);
const select_Priority = ref(false);
const toggleRecipient = ref(false);
const date = computed(()=>props.task.due_date);
const description = ref('')
const Users = ref("");
const title = ref('')
const Assigned_by = ref("");

const data = ref({
  img: image,
  name: "Dave Miye",
  date: "2023-05-15",
  priority: "Medium",
  title: 'Append Signature',
  desc: "Review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archive.",
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const formattedDate = computed(() => formatDate(date.value));
const priority = ref("");
const Priorities = ref(["Low", "Medium", "High"]);

const updatePriority = (event) => {
  priority.value = event;
  select_Priority.value = false;
};

const type = ref([
  "Document Upload + Text comment",
  "Document Only",
  "Text Only",
]);
const props = defineProps(["modelValue", 'task']);
const emit = defineEmits(["update:modelValue"]);
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
};

onMounted(() => {
  date.value = data.value.date;
  priority.value = data.value.priority;
  description.value = data.value.desc;
  title.value = data.value.title
});

const submit = () => {
  emit("update:modelValue", false);

  //   toast.add({
  //     severity: "success",
  //     summary: '',
  //     detail: "",
  //     life: 3000,
  //   });
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>