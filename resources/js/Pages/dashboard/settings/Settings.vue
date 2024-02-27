<template>
  <main class=" flex ">
      <section class=" w-1/5 hide_scroll hidden 2xl:block border-r border-solid border-gray-200 sticky top-0 h-screen  py-5 overflow-y-scroll">

       <div class="px-3">
        <h1 class=" text-3xl ">Settings</h1>
        <p class=" text-[#344054]">Choose between categories.</p>
       </div>
<Divider/>
        <div v-for="(item, index) in settings" :key="index" class="mb-2 px-3">
						<router-link
							:to="item.path"
							:class="
                route.path === item.path
                  ? ' px-3 border bg-gray-25 text-[#3B82F6] bg-[#F9FAFB]'
                  : ' px-3'
              "
							class="py-3 flex gap-3 items-center text-[#344054] border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-[#3B82F6] font-normal transition-all"
						>
							<Icon :icon="item.icon" width="25" />
							<p class=" text-base" >{{ item.name }}</p>
						</router-link>
					</div>
      </section>
        <section class="hide_scroll w-full overflow-y-scroll">
		
			<router-view />

      </section>
  </main>
</template>

<script setup>
import {ref} from 'vue'
import { useRoute } from "vue-router";

const route = useRoute()

       const settings = ref([
  {
    name: "Profile",
    path: "/data-entry/settings",
    icon: "ep:user",
  },
  {
    name: "Preferences",
    path: "/data-entry/settings/preference",
    icon: "material-symbols:room-preferences-rounded",
  },
  
  {
    name: "Security & Recovery",
    path: "/data-entry/settings/security",
    icon: "lucide:lock",
  },
  {
    name: "Localisation",
    path: "/data-entry/settings/localisation",
    icon: "jam:world",
  },
]);
</script>

<style>

</style>