<template>
  <main class=" bg-slate-50  min-h-[100vh] ">
    <div class=" flex p-7 gap-5">
    <div v-for="(item,index) in Routes"  :key="index" class=" ">
        <a @click="setLink(item.path)" v-if="!roles.includes('regular users')"
              href="#"
              :class="
                (currentLink === item.path
                  ? 'px-3 border bg-gray-25  bg-[#ffffff]'

                  : '')

              "
              class="py-2 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-3xl hover:bg-gray-25 hover:text-primary font-normal transition-all"
            >
              <Icon :icon="item.icon" width="25" />
              <p class="text-lg" >{{ item.name }}</p>
    </a>
    </div>
</div>
<Divider class=" -mt-1"/>
<div class="">
    <overview :users="props.users" :roles="roles" :departments="props.departments" v-if="currentLink == '/dashboard/pms/overview'"/>
    <settings :departments="props.departments"  v-if="currentLink == '/dashboard/pms/settings' && !roles.includes('regular users')"/>
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