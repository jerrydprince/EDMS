<template>

  <main class="bg-white  p-7 rounded-2xl mt-3 border border-gray-300">
    <div class="flex items-center w-full justify-between">
      <div class="flex items-center gap-5">
        <h1 class="font-semibold text-2xl">Tasks Assigned</h1>
        <button class="items-center flex gap-2 text-xs text-gray-500 bg-gray-100/75 p-1 px-2 rounded-xl">
          <Icon icon="uil:padlock" />
          <p>View only</p>
        </button>
      </div>
      <div class="flex items-center gap-5">
        <div class="p-2 bg-slate-100 rounded-full cursor-pointer" @click="scroll(-50)">
          <Icon icon="flowbite:angle-left-outline" />
        </div>
        <div class="p-2 bg-slate-100 rounded-full cursor-pointer" @click="scroll(50)">
          <Icon icon="flowbite:angle-right-outline" />
        </div>
      </div>
    </div>

    <div class=" my-10 overflow-x-scroll   w-full" ref="scrollContainer">
      <div class=" flex mb-10  items-start gap-10">
        <!-- ====== task  ====== -->
        <div class=" bg-gray-100 rounded-md p-3 h-fit " v-for="(task, index) in workflows" :key="index"
          @click="ArchiveDocumentModal = true">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h1 class="font-semibold text-lg">{{ task.status }}</h1>
            </div>
          </div>

          <div v-for="(item, index) in filteredTask(task.status)" :key="index">
            <div class="bg-white rounded-md shadow-md mt-3 w-[400px]">
              <div class="p-2 border-b">
                <h1 class="font-semibold text-[#1D2939]">
                  <!-- <Link :href="'/dashboard/view-task/'+item.id">{{ item.title }}</Link> -->
                  {{ item.title }}
                </h1>
                <p class="font-semibold text-[#1D2939] opacity-70 text-sm">
                  {{ item.description }}
                </p>
              </div>
              <div class="p-2 flex items-center justify-between">
                <img v-if="item.assigned_by && item.assigned_by.profile_image" width="50":src="page.props.base+'/'+item.assigned_by.profile_image" alt="">
                <img v-else src="../assets/images/members/1.svg" alt="" />
                <div class="flex gap-3 items-center">
                  <div class="flex items-center gap-1">
                    <Icon icon="iconamoon:comment" width="13" />
                    <span>{{ item?.comments_count }}</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <Icon icon="uil:calender" width="13" />
                    <span class="capitalize">{{ getDays(item.created_at) }}</span>
                  </div>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, defineProps } from "vue";
import moment from "moment"
import {usePage, Link} from "@inertiajs/vue3"

const props = defineProps(['task'])
const scrollContainer = ref(null);
const page = usePage()
const scroll = (offset) => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollLeft += offset;
  }
};

const filteredTask = (status)=>{
  let filtered = props.task.filter((item)=>item.status.toLowerCase() == status.toLowerCase())
  console.log(filtered)
  return filtered
}

const getDays = (date)=>{
  return moment(date).fromNow();
}

const workflows = ref([
  {
    status: 'Pending'
  },
  {
    status: "In Progress",
    // todo: [
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },




    // ],
  },



  {
    status: "Under Review",
    // todo: [
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    // ],
  },
  {
    status: "Completed",
    // todo: [
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    // ],
  },
  {
    status: "Uncompleted",
    // todo: [
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    //   {
    //     title: "Review and verify",
    //     task: "Provide support and assistance to users regarding document archiving",
    //   },
    // ],
  },
]);
</script>

<style></style>