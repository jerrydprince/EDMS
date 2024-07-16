<template>
  <main class="p-7 bg-slate-50">
    <div class="mb-5 flex items-center w-full justify-between">
      <div class="flex items-center gap-2 cursor-pointer" @click="goBack">
        <Icon icon="flowbite:angle-left-outline" />
        <p>Back</p>
      </div>
      <div class="flex items-center gap-10 font-semibold text-gray-600">
        <button
          class="bg-white border rounded-md px-4 py-2"

          v-if="data.status === 'In Progress' || data.status === 'Under Review'"

          @click="Re_Assign_Modal = true"
        >
          Reassign Task
        </button>
        <button
          class="flex items-center bg-white border rounded-md gap-2 px-4 py-2"

          v-if=" data.status === 'Under Review'"

          @click="completeModal = true"
        >
          <Icon icon="mdi:tick" width="25" />
          <p>Mark as complete</p>
        </button>



        <button
          class="flex items-center bg-white border rounded-md gap-2 px-4 py-2"
          v-if=" data.status === 'In Progress'"
          @click="editModal = true"
        >
          <Icon icon="icon-park-outline:edit" width="25" />
          <p>Edit Task</p>
        </button>



        <button
          class="flex items-center bg-white border rounded-md gap-2 px-4 py-2"
          @click="reOpenModal = true"
          v-if="data.status === 'Completed'"
        >
          <Icon icon="teenyicons:unlock-outline" width="25" />
          <p>Reopen Task</p>
        </button>

      </div>
      <div
        class="absolute top-12 cursor-pointer z-50 right-7 block md:hidden"
        @click="subNavVisible = true"
      >
        <Icon icon="mdi:hamburger-menu-back" width="30" />
      </div>
    </div>

    <section class="lg:flex items-center gap-10 w-full">
      <div class="bg-white w-full lg:w-[60%] rounded-lg p-5">
        <div class="pb-5 flex items-center gap-10">
          <div class="p-4 border rounded-md">
            <Icon icon="lucide:file-text" width="25" />
          </div>
          <p class="text-2xl">Caltech 6 months audit training certificate</p>
        </div>

        <div class="h-[1000px] overflow-y-auto hide_scroll">
          <div v-for="i in 4" :key="i">
            <div class="bg-slate-50 p-5 w-full rounded-md">
              <img
                src="/assets/images/document_2.svg"
                alt=""
                class="overflow-hidden rounded-md w-full"
              />
            </div>
          </div>
        </div>
      </div>

      <div

        class="bg-white hidden lg:block w-[40%] h-[1120px] overflow-y-scroll hide_scroll rounded-lg"

      >
        <div v-if="!sidemenu">
          <h1 class="p-8 font-semibold text-gray-800 text-3xl">
            Append Signature
          </h1>
          <Divider class="mt-0" />

          <section class="p-5 text-gray-600 max-h-[1000px]">
            <div>
              <div class="flex items-center gap-14">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:users" />
                  <p>Assign To</p>
                </div>
                <div
                  class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border-gray-300"
                >

                  <img :src="data.img" alt="" class="w-7" />
                  <p class="text-base w-fit">{{ data.name }}</p>

                </div>
              </div>

              <div class="flex items-center gap-14 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:calendar" />
                  <p>Due date</p>
                </div>
                <div>

                  <p class="text-base w-fit">{{ data.date }}</p>
                </div>
              </div>

              <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="fluent:document-16-regular" />
                <p>Document Type</p>
              </div>
              <div class="flex items-center gap-3">
              <div  v-for="(item,index) in data.document_type"  :key="index">
                <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4">{{ item }}</p>
              </div>
            </div>
            </div>


              <div class="flex items-center gap-24 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:calendar" />
                  <p>Tags</p>
                </div>

                <div class="flex items-center gap-3">
                  <div
                    class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer"
                  >

                    <p class="text-xs w-fit text-[#F67E5A]">
                      {{ data.severity }}
                    </p>

                  </div>

                  <div
                    class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer"
                  >

                    <p
                      class="text-xs w-fit font-semibold"
                      :style="{
                        color:
                          data.status === 'Completed'
                            ? '#0D6E1A'
                            : data.status === 'Under Review'
                            ? '#FB6514'
                            : data.status === 'In Progress'
                            ? '#F79009'
                            : data.status === 'Uncompleted'
                            ? '#F04438'
                            : 'black',
                      }"
                    >
                      {{ data.status }}
                    </p>

                  </div>
                </div>
              </div>

              <div class="mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:file-text" />
                  <p>Task Description</p>
                </div>

                <p
                  class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full"
                >

                 {{ data.desc }}

                </p>
              </div>

              <div class="mt-5">
                <div class="flex mb-5 items-center gap-3">
                  <Icon icon="grommet-icons:attachment" width="20" />
                  <p>Attached Document</p>
                </div>
                <div

                  v-for="(document, index) in data.documents"
                  :key="index"
                  class="border border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"

                >
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
            </div>
          </section>
          <Divider class="mt-0" />

          <div
            class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"

            @click="toggleSidemenu('comment')"
          >
            <div class="flex items-center gap-5">
              <Icon icon="lucide:message-square" width="25" />
              <p class="text-xl">Comments</p>
              <p
                class="w-7 h-7 rounded-md text-gray-800 font-semibold bg-primary/10 flex items-center justify-center flex-col p-3"
              >
                3
              </p>
            </div>
            <Icon icon="flowbite:angle-right-outline" width="25" />
          </div>
          <Divider class="mt-0" />
          <div
            class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"


            @click="toggleSidemenu('submission')"
          >
            <div class="flex items-center gap-5">
              <Icon icon="lucide:activity" width="25" />
              <p class="text-xl">Task Submissions</p>
              <p
                class="w-7 h-7 rounded-md text-red-500 font-semibold bg-red-500/10 flex items-center justify-center flex-col p-3"
              >
                !
              </p>
            </div>
            <Icon icon="flowbite:angle-right-outline" width="25" />
          </div>
          <Divider class="mt-0" />
        </div>

        <!-- /////   comments   ///// -->

        <div v-if="comment && sidemenu">

          <div class="p-8">
            <div
              class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')"
            >
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">
            <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

            <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
              <textarea
                name=""
                id=""
                cols="30"
                placeholder="Enter Message"
                rows="5"
                class="bg-slate-50 outline-none resize-none"
              ></textarea>
              <div class="w-full flex items-center justify-end">
                <button
                  class="bg-white px-3 py-1.5 border border-gray-300 rounded-md"
                >
                  Send
                </button>
              </div>
            </div>
            <div class="mt-5 px-5">
              <div v-for="(item, index) in comments" :key="index">
                <Divider class="mt-0" />
                <div class="flex items-start w-full">
                  <div class="w-[15%]">
                    <div class="w-14 h-14 rounded-full overflow-hidden">
                      <img :src="item.img" alt="" class="w-full h-full" />
                    </div>
                  </div>
                  <div class="w-[80%]">
                    <div class="flex items-center gap-2">
                      <h1 class="text-gray-800 text-xl">{{ item.name }}</h1>
                      <small class="text-gray-500">• {{ item.time }}</small>
                    </div>
                    <p class="text-base text-gray-500 my-4">
                      {{ item.comment }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- //////   submissions   ///// -->
        <div v-if="submission && sidemenu">
          <div class="p-8">
            <div
              class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')"
            >
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">

            <h1 class="px-5 font-semibold text-gray-800 text-3xl">
              Task Submissions
            </h1>

            <div class="px-5 mt-3">
              <div class="bg-slate-50 p-3 rounded-md">
                <div
                  v-for="(document, index) in documents"
                  :key="index"
                  class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"
                >
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

            </div>

            <div class="mt-7 px-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:file-text" />
                <p>Task Description</p>
              </div>

              <p
                class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full"
              >
                {{ data.desc }}
              </p>
            </div>

            <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end">
              <button class="text-red-600" @click="RequestRevisionModal = true">
                Request Revision
              </button>

              <button
                class="font-semibold text-gray-800 px-3 py-2 border rounded-md"
                @click="completeModal = true"
              >
                Mark as complete
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>
  <MarkComplete v-model="completeModal" />
  <ReAssign v-model="Re_Assign_Modal" />
  <RequestRevision v-model="RequestRevisionModal" />
  <DeleteRevision v-model="deleteRevisionModal" />

  <EditTask  v-model="editModal"/>

  <Reopen v-model="reOpenModal" />
  <Sidebar
    v-model:visible="subNavVisible"
    header=" Left Sidebar"
    position="right"
    :style="{ width: '32rem', padding: '10px 0px' }"
  >
    <div
      class="bg-white hide_scroll sticky top-0 h-screen pr-5 overflow-y-scroll"
    >
      <div v-if="!sidemenu">
        <h1 class="p-8 font-semibold text-gray-800 text-3xl">
          Append Signature
        </h1>
        <Divider class="mt-0" />

        <section class="p-5 text-gray-600 max-h-[1000px]">
          <div>
            <div class="flex items-center gap-14">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:users" />
                <p>Assign To</p>
              </div>
              <div
                class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border-gray-300"
              >
                <img src="/assets/images/user.png" alt="" class="w-7" />
                <p class="text-base w-fit">Dave Miye</p>
              </div>
            </div>

            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="fluent:document-16-regular" />
                <p>Document Type</p>
              </div>
              <div class="flex items-center gap-3">
              <div  v-for="(item,index) in data.document_type"  :key="index">
                <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4">{{ item }}</p>
              </div>
            </div>
            </div>
            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Due date</p>
              </div>
              <div>
                <p class="text-base w-fit">2023-05-15</p>
              </div>
            </div>

            <div class="flex items-center gap-24 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Tags</p>
              </div>

              <div class="flex items-center gap-3">
                <div
                  class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer"
                >
                  <p class="text-xs w-fit text-[#F67E5A]">Medium</p>
                </div>

                <div
                  class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer"
                >
                  <p class="text-xs w-fit text-[#F67E5A]">In Progress</p>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:file-text" />
                <p>Task Description</p>
              </div>

              <p
                class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full"
              >
               {{ data.desc }}
              </p>
            </div>

            <div class="mt-5">
              <div class="flex mb-5 items-center gap-3">
                <Icon icon="grommet-icons:attachment" width="20" />
                <p>Attached Document</p>
              </div>
              <div
                v-for="(document, index) in documents"
                :key="index"
                class="border border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"
              >
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
          </div>
        </section>
        <Divider class="mt-0" />

        <div
          class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('comment')"
        >
          <div class="flex items-center gap-5">
            <Icon icon="lucide:message-square" width="25" />
            <p class="text-xl">Comments</p>
            <p
              class="w-7 h-7 rounded-md text-gray-800 font-semibold bg-primary/10 flex items-center justify-center flex-col p-3"
            >
              3
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />
        <div
          class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('submission')"
        >
          <div class="flex items-center gap-5">
            <Icon icon="lucide:activity" width="25" />
            <p class="text-xl">Task History</p>
            <p
              class="w-7 h-7 rounded-md text-red-500 font-semibold bg-red-500/10 flex items-center justify-center flex-col p-3"
            >
              !
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />
      </div>

      <!-- /////   comments   ///// -->

      <div v-if="comment && sidemenu">
        <div class="p-8">
          <div
            class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')"
          >
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

          <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
            <textarea
              name=""
              id=""
              cols="30"
              placeholder="Enter Message"
              rows="5"
              class="bg-slate-50 outline-none resize-none"
            ></textarea>
            <div class="w-full flex items-center justify-end">
              <button
                class="bg-white px-3 py-1.5 border border-gray-300 rounded-md"
              >
                Send
              </button>
            </div>
          </div>
          <div class="mt-5 px-5">
            <div v-for="(item, index) in comments" :key="index">
              <Divider class="mt-0" />
              <div class="flex items-start w-full gap-4">
                <div class="w-[15%]">
                  <div class="w-14 h-14 rounded-full overflow-hidden">
                    <img :src="item.img" alt="" class="w-full h-full" />
                  </div>
                </div>
                <div class="w-[80%]">
                  <div class="flex items-center gap-2">
                    <h1 class="text-gray-800 text-xl">{{ item.name }}</h1>
                    <small class="text-gray-500">• {{ item.time }}</small>
                  </div>
                  <p class="text-base text-gray-500 my-4">
                    {{ item.comment }}
                  </p>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- //////   submissions   ///// -->
      <div v-if="submission && sidemenu">
        <div class="p-8">
          <div
            class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')"
          >
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">
            Task Submissions
          </h1>

          <div class="px-5 mt-3">
            <div class="bg-slate-50 p-3 rounded-md">
              <div
                v-for="(document, index) in documents"
                :key="index"
                class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"
              >
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
          </div>

          <div class="mt-7 px-5">
            <div class="flex items-center gap-3">
              <Icon icon="lucide:file-text" />
              <p>Task Description</p>
            </div>

            <p
              class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full"
            >
         {{ data.desc }}
            </p>
          </div>

          <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end">
            <button class="text-red-600" @click="RequestRevisionModal = true">
              Request Revision
            </button>

            <button
              class="font-semibold text-gray-800 px-3 py-2 border rounded-md"
              @click="completeModal = true"
            >
              Mark as complete
            </button>
          </div>
        </div>
      </div>
    </div>

  </Sidebar>
</template>

<script  setup>
import { ref } from "vue";
//import { useRouter } from "vue-router";
import MarkComplete from "../../components/Modals/Workflows/MarkComplete.vue";
import ReAssign from "../../components/Modals/Workflows/ReAssign.vue";
import RequestRevision from "../../components/Modals/Workflows/RequestRevision.vue";
import DeleteRevision from "../../components/Modals/Workflows/DeleteRevision.vue";
import EditTask from "../../components/Modals/Workflows/EditTask.vue";
import Reopen from "../../components/Modals/Workflows/Reopen.vue";
import jenna from "../../assets/images/jenna.svg";
import user from "../../assets/images/user.svg";

const editModal = ref(false)
const subNavVisible = ref(false);
const reOpenModal = ref(false);
const sidemenu = ref(false);
const submission = ref(false);

const comment = ref(false);
const RequestRevisionModal = ref(false);
const completeModal = ref(false);
const deleteRevisionModal = ref(false);
const Re_Assign_Modal = ref(false);
//const router = useRouter();




const goBack = () => {
 // router.go(-1);
};


const data = ref({
  date: '2023-05-15',
  name: 'Dave Miye',
  img: jenna,
  status: "In Progress",
  desc: 'Review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archive.',
  documents: [
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
  ],
  document_type: ["Invoice", "Reciept"],
  severity: "Medium",
});


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

const comments = ref([
  {
    img: jenna,
    name: "Jenna Ryan obadimu",
    time: "6m ago",
    comment:
      "Task Received i would begin work immediately & review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. ",
  },
  {
    img: user,
    name: "You",
    time: "6m ago",
    comment: "Okay understood, i would work on the changes in due time boss ",
  },
]);

const toggleSidemenu = (action) => {

  if (action === "comment") {
    comment.value = !comment.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "submission") {
    submission.value = !submission.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "back") {
    comment.value = false;
    sidemenu.value = false;
    submission.value = false;
  }
};

</script>

<style>
</style>