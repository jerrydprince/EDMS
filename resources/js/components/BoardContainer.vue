<template>
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 pb-7 gap-5 px-7">
    <!-- ====== task  ====== -->
    <div
      class="bg-[#F2F4F7] rounded-md p-3 h-fit"
      v-for="(task, index) in flowTasks"
:list="flowTasks.Tasks"
      :key="index"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <h1 class="font-semibold text-lg">{{ task.status }}</h1>
          <small
            class="rounded-full flex flex-col items-center justify-center  h-5 w-5 text-white"
            :style="{
              'background-color':
                task.status === 'Completed'
                  ? '#12B76A'
                  : task.status === 'Uncompleted'
                  ? '#F04438'
                  : task.status === 'Under Review'
                  ? '#F79009'
                  : 'black',
            }"
            ><span>{{ task.todo.length }}</span></small
          >
        </div>
        <div class="relative cursor-pointer">
          <div @click="toggleMenu(task.status)">
            <Icon icon="mi:options-horizontal" width="25" />
          </div>
          <div
            id="menu1"
            class="absolute z-50"
            :style="{ right: horizontalPosition ? '0' : 'auto' }"
            v-if="menuVisible && selectedStatus === task.status"
            ref="modalRef"
          >
            <div class="w-[450px] h-[195px] relative" @click.stop>
              <div
                class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer"
                :style="{ right: horizontalPosition ? '0' : 'auto' }"
              >
                <div
                  class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                  v-for="(item, index) in menuItem"
                  :key="index"
                  @click="handleMenuItem(item.label)"
                >
                  <div class="flex items-center gap-2">
                    <Icon :icon="item.icon" />
                    <p>{{ item.label }}</p>
                  </div>
                  <Icon :icon="item.arrow" />
                </div>
              </div>

              <div
                class="w-60 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 right-0 shadow-lg cursor-pointer"
                :style="{ left: horizontalPosition ? '0' : 'auto' }"
                v-if="submenu"
              >
                <div
                  class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                  v-for="(item, index) in submenuItem"
                  :key="index"
                  @click="handleMenuItem(item)"
                >
                  <p>{{ item.label }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <draggable  class=" " :list="task.todo" :item-key="title"
      group="demo"
    >
        <template #item="{ element }">
          <div  class="bg-white rounded-md shadow-md mt-3">
            <div class="p-2 border-b">
              <h1 class="font-semibold text-[#1D2939]">{{ element.title }}</h1>
              <p class="font-semibold text-[#1D2939] opacity-70 text-sm">
                {{ element.task }}
              </p>
            </div>
            <div class="p-2 flex items-center justify-between">
              
              <img src="../assets/images/members/1.svg" alt="" />
              <div class="flex gap-3 items-center">
                <div class="flex items-center gap-1">
                  <Icon icon="iconamoon:comment" width="13" />
                  <span>3</span>
                </div>
                <div class="flex items-center gap-1">
                  <Icon icon="uil:calender" width="13" />
                  <span>3d</span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </draggable>
      <div
        class="bg-[white] hover:bg-[#EBF3FE] text-[#1D2939] hover:border-primary hover: font-semibold border-gray-300 border-dashed border-[2px] px-2 py-3 mt-3 rounded-md relative cursor-pointer flex items-center gap-2"
        @click="addTask = true"
      >
        <Icon icon="ic:outline-plus" width="25" />
        <h1>Add Task</h1>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref,defineProps } from "vue";
import { onClickOutside } from "@vueuse/core";
import draggable from "vuedraggable";
const menuVisible = ref(false);
const modalRef = ref(null);
const submenu = ref(false);

onClickOutside(modalRef, (event) => {
  (menuVisible.value = false), (submenu.value = false);
});

const props = defineProps({
  flowTasks: Object,
});

const menuItem = ref([
  {
    icon: "icon-park-outline:add-one",
    label: "Add Task",
  },
  {
    icon: "ph:arrow-up-bold",
    label: "Sort by",
    arrow: "flowbite:angle-right-outline",
  },
  {
    icon: "icon-park-outline:direction-adjustment-three",
    label: "Move",
  },
]);

const submenuItem = ref([
  {
    label: "Date created (newest first)",
  },
  {
    label: "Date created (oldest first)",
  },
  {
    label: "Task name (alphabetically)",
  },
  {
    label: "Due Date",
  },
]);

const handleMenuItem = (item) => {
  if (item === "Sort by") {
    menuVisible.value = true;
    submenu.value = true;
  }
};
const horizontalPosition = ref(null);
const selectedStatus = ref(null);
const toggleMenu = (status) => {
  selectedStatus.value = status;
  menuVisible.value = !menuVisible.value;
  const { clientX } = event;
  const viewportWidth = window.innerWidth;
  const clientXPercentage = (clientX / viewportWidth) * 100;
  horizontalPosition.value = clientXPercentage >= 65;
};
</script>

<style>
</style>