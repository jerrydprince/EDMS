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
            ? ' px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
            : ' px-3'
          "
          class="py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
        <Icon :icon="item.icon" width="25" />
        <p class=" text-base">{{ item.name }}</p>
      </a>

      </div>
    </section>
    <section class="hide_scroll w-full overflow-y-scroll">

      <!-- <profile v-if="settings[0].path == currentUrl" :user="props.user" />
      <preferences v-if="settings[1].path == currentUrl" />
      <security v-if="settings[2].path == currentUrl" />
      <localisation v-if="settings[3].path == currentUrl" /> -->
      <profile v-if="settings[0].path == currentUrl" :user="props.user" />
      <organisation  v-if="settings[1].path == currentUrl"/>
      <preferences v-if="settings[2].path == currentUrl"/>
      <notification v-if="settings[3].path == currentUrl"/>
      <security :user="props.user" @refreshUser="refreshUserHandler" v-if="settings[4].path == currentUrl && is_passwordPolicies" @passwordPolicies="handlePasswordPolicies" />
      <localisation v-if="settings[6].path == currentUrl" />
      <integrations v-if="settings[5].path == currentUrl" />
      <development v-if="settings[7].path == currentUrl" />

      <passwordPolicies v-if="!is_passwordPolicies && settings[4].path == currentUrl" @security="handleSecurity"/>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3';
import profile from '../../dashboard/settings/profile.vue';
import preferences from '../../dashboard/settings/Preferences.vue';
import security from '../../dashboard/settings/Security.vue';
import localisation from '../../dashboard/settings/Localisation.vue';
import organisation from "../../dashboard/settings/Organisation.vue";
import notification from "../../dashboard/settings/Notifications.vue"
import integrations from "../../dashboard/settings/Integrations.vue";
import development from "../../dashboard/settings/Development.vue";

import passwordPolicies from "../../dashboard/settings/PasswordPolicies.vue"

const { url } = usePage();
const page = usePage();
const currentUrl = ref()
const current_page = computed(()=> page.url)
const props = defineProps(['user'])
const is_passwordPolicies = ref(true)

const refreshUserHandler = ()=>{
  router.reload({only: ['user']})
}

// const settings = ref([
//   {
//     name: "Profile",
//     path: "/dashboard/settings",
//     icon: "ep:user",
//   },
//   {
//     name: "Preferences",
//     path: "/dashboard/preference",
//     icon: "material-symbols:room-preferences-rounded",
//   },

//   {
//     name: "Security & Recovery",
//     path: "/data-entry/security",
//     icon: "lucide:lock",
//   },
//   {
//     name: "Localisation",
//     path: "/data-entry/localisation",
//     icon: "jam:world",
//   },
// ]);

const handlePasswordPolicies = ()=>{
  is_passwordPolicies.value = false
}

const handleSecurity = ()=>{
  is_passwordPolicies.value = true;
}

const settings = ref([
  {
    name: "Profile",
    path: "/settings",
    icon: "ep:user",
  },
  {
    name: "Organisation",
    path: "/settings/organisation",
    icon: "mdi:company",
  },
  {
    name: "Preferences",
    path: "/settings/preferences",
    icon: "material-symbols:room-preferences-rounded",
  },
  {
    name: "Notifications Config",
    path: "/settings/notifications-config",
    icon: "basil:notification-outline",
  },
  {
    name: "Security & Recovery",
    path: "/settings/security-recovery",
    icon: "lucide:lock",
  },
  {
    name: "Integrations",
    path: "/settings/integrations",
    icon: "mi:filter",
  },

  {
    name: "Localisation",
    path: "/settings/localisation",
    icon: "jam:world",
  },
  {
    name: "Development",
    path: "/settings/development-team",
    icon: "mdi:code"
  }
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