<template>
    <main
      class="h-[50px] lg:h-[67px] py-1 lg:py-3 border-b border-solid border-gray-200 flex items-center lg:justify-end"
    >
      <div
        class="hidden lg:flex text-xl font-semibold items-center justify-end gap-4 px-7"
      >
        <span class="text-gray-400 relative mr-1">
          <Icon
            icon="iconamoon:search-light"
            width="20"
            class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600"
          />
          <InputText
            v-model="value1"
            placeholder="Search for documents, folders & branches"
            class="pl-10 w-[28rem] h-10 bg-transparent font-light"
            size="small"
          />
        </span>
        <div
          class="flex gap-3 items-center bg-gray-200 h-full py-1 px-2 rounded-full mr-1"
        >
          <Icon
            icon="heroicons:sun-20-solid"
            width="30"
            class="bg-white rounded-full p-1"
          />
          <Icon class="text-gray-500" icon="ion:moon-sharp" width="20" />
        </div>
        <Icon icon="iconamoon:notification" width="25" />
        <div class="ml-1">
          <Userplaceholder />
        </div>
      </div>
      <div
        class="flex lg:hidden text-xl font-semibold items-center justify-between px-4 w-full"
      >
        <div class="flex items-center gap-4">
          <Icon icon="quill:hamburger" width="30" />
          <div class="text-lg font-semibold flex items-center gap-4">
            <p>Ansel LTD</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Icon icon="mynaui:search" width="25" />
          <Icon icon="iconamoon:notification" width="25" />
          <div class="ml-1">
            <User />
          </div>
        </div>
      </div>
    </main>
  </template>
  
  <script setup>
  import { ref } from "vue";
  import User from "../assets/logo.vue";
  import Userplaceholder from "../assets/Userimageholder.vue";
  
  const value1 = ref("");
  </script>
  