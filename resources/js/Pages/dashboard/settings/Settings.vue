<template>
  <main class=" flex ">
    <section
      class=" w-1/5 hide_scroll hidden 2xl:block border-r border-solid border-gray-200 sticky top-0 h-screen  py-5 overflow-y-scroll">

      <div class="px-3">
        <h1 class=" text-3xl ">Settings</h1>
        <p class=" text-[#344054]">Choose between categories.</p>
      </div>
      <Divider />
      <div v-for="(item, index) in settings" :key="index" class="mb-2 px-3"  @click="SetURL(item.path)">
        <a :to="item.path" :class="settings[index].path === currentUrl
            ? ' px-3 border bg-gray-25 text-[#3B82F6] bg-[#F9FAFB]'
            : ' px-3'
          "
          class="py-3 flex gap-3 items-center text-[#344054] border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-[#3B82F6] font-normal transition-all">
        <Icon :icon="item.icon" width="25" />
        <p class=" text-base">{{ item.name }}</p>
      </a>

      </div>
    </section>
    <section class="hide_scroll w-full overflow-y-scroll">

      <profile v-if="settings[0].path == currentUrl" :user="props.user" />
      <preferences v-if="settings[1].path == currentUrl" />
      <security v-if="settings[2].path == currentUrl" />
      <localisation v-if="settings[3].path == currentUrl" />

    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue'
import { Link, usePage } from '@inertiajs/vue3';
import profile from '../../dashboard/settings/profile.vue';
import preferences from '../../dashboard/settings/Preferences.vue';
import security from '../../dashboard/settings/Security.vue';
import localisation from '../../dashboard/settings/Localisation.vue';

const { url } = usePage();
const currentUrl = ref()
const props = defineProps(['user'])

const settings = ref([
  {
    name: "Profile",
    path: "/data-entry/settings",
    icon: "ep:user",
  },
  {
    name: "Preferences",
    path: "/data-entry/preference",
    icon: "material-symbols:room-preferences-rounded",
  },

  {
    name: "Security & Recovery",
    path: "/data-entry/security",
    icon: "lucide:lock",
  },
  {
    name: "Localisation",
    path: "/data-entry/localisation",
    icon: "jam:world",
  },
]);

const SetURL = (path)=>{
  currentUrl.value = path
}

onMounted(() => {
  currentUrl.value = url
})
</script>

<style>
  a{
    cursor: pointer;
  }
</style>