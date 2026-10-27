<template>
  <main class="bg-slate-50 min-h-[100vh]">
    <div class="flex items-center px-4 sm:px-6 pt-5 pb-3 gap-3 bg-white border-b border-gray-200">
      <div v-for="(item, index) in Routes" :key="index">
        <a
          @click="setLink(item.path)"
          v-if="!roles.includes('regular user')"
          href="#"
          :class="
            currentLink === item.path
              ? 'bg-blue-50 text-blue-700 shadow-xs border-blue-200 font-bold'
              : 'text-gray-600 hover:bg-gray-50 border-gray-200 font-medium'
          "
          class="px-4 py-2 flex gap-2 items-center rounded-xl border transition-all duration-200 text-sm"
        >
          <Icon :icon="item.icon" width="20" />
          <p>{{ item.name }}</p>
        </a>
      </div>
    </div>
    <div>
      <overview :users="props.users" :roles="roles" :departments="props.departments" v-if="currentLink == '/dashboard/pms/overview'"/>
      <settings :departments="props.departments" v-if="currentLink == '/dashboard/pms/settings' && !roles.includes('regular user')"/>
    </div>
  </main>
</template>

<script setup>
import {ref, defineProps, computed} from 'vue';
import {Link, usePage} from "@inertiajs/vue3"
import overview from "../PMS/Overview.vue"
import settings from "../PMS/Settings.vue"
//import { useRoute } from "vue-router";
//const route = useRoute();

const props = defineProps(['users', 'departments'])
const page = usePage();
const roles = computed(()=>page.props.roles)
const Routes = [
    {
        name: 'Overview',
        path: '/dashboard/pms/overview',
        icon: "lucide:home",
    },
    {
        name: 'KRA config',
        path: '/dashboard/pms/settings',
        icon:  "uiw:setting-o"
    }
]

const currentLink = ref('/dashboard/pms/overview')
const setLink = (link)=>{
  currentLink.value = link
}
</script>

<style>

</style>
