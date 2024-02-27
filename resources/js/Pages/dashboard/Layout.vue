<template>
    <main class="h-screen flex w-full max-w-[1680px] fixed top-0">
      <section
        class="hide_scroll hidden 2xl:block w-1/5 border-r border-solid border-gray-200 sticky top-0 h-screen px-5 py-5 overflow-y-scroll"
        :class="{ 'w-[7%]': shouldShow }"
      >
        <div
          class="text-xl font-semibold flex items-center gap-4 border-b border-dashed border-gray-200 pb-3 px-3"
          :class="{ 'px-0': shouldShow }"
        >
          <logo />
          <p v-if="!shouldShow">Ansel LTD</p>
        </div>
        <div class="border-b border-dashed pb-4">
          <p
            class="text-gray-500 mt-4 mb-2 px-3"
            :class="{ 'text-center': shouldShow }"
          >
            Main
          </p>
          <section>
            <div
              v-for="(item, index) in main"
              :key="index"
              class="mb-1"
              :class="{ ' -ml-4': shouldShow }"
            >
              <router-link
                :to="item.path"
                :class="
                  route.path === item.path
                    ? ' px-3 border bg-gray-25 text-[#3B82F6] bg-[#F9FAFB]'
                    : ' px-3  '
                "
                class="py-3 flex gap-3 items-center text-[#344054] border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-[#3B82F6] font-normal transition-all"
              >
                <Icon
                  :icon="item.icon"
                  width="25"
                  :class="{ 'ml-5': shouldShow }"
                />
                <p class="text-lg" v-if="!shouldShow">{{ item.name }}</p>
              </router-link>
            </div>
          </section>
        </div>
       
        <div class="pb-4 mb-10">
          <div
            class="text-gray-500 mt-4 mb-2 flex items-center justify-between px-3"
            :class="{ ' -ml-3': shouldShow }"
          >
            <div class="flex items-center gap-2">
              <Icon icon="ic:round-greater-than" width="20" v-if="!shouldShow" />
              <p>Branches</p>
            </div>
            <Icon icon="line-md:grid-3-filled" width="20" v-if="!shouldShow" />
          </div>
          <section>
            <div v-for="(item, index) in branches" :key="index" class="mb-1">
              <router-link
                :to="item.path"
                :class="
                  route.path === item.path
                    ? ' px-3 border bg-gray-25 text-[#3B82F6] bg-[#F9FAFB]'
                    : ' px-3  '
                "
                class="py-3 flex justify-between items-center text-[#344054] border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-[#3B82F6] font-normal transition-all"
              >
                <div
                  class="flex items-center gap-3"
                  :class="{ ' ml-2': shouldShow }"
                >
                  <div
                    class="h-4 w-4 rounded-full border border-gray-300"
                    :class="{ 'bg-[#3B82F6]': route.path === item.path }"
                    v-tooltip.right="{
                      value: item.name,
                      pt: {
                        arrow: {
                          style: {
                            borderRightColor: '',
                           
                          },
                        },
                        text: 'bg-[#1D2939] text-xs text-white font-medium px-2 py-1 rounded',
                      },
                    }"
                  ></div>
                  <p class="text-lg" v-if="!shouldShow">{{ item.name }}</p>
                </div>
                <Icon
                  icon="ic:round-greater-than"
                  width="20"
                  v-if="!shouldShow"
                />
              </router-link>
            </div>
            <div class="mb-1">
              <div
                class="px-3 py-3 flex items-center justify-between text-gray-700 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all"
              >
                <div class="flex items-center gap-3" v-if="!shouldShow">
                  <Icon icon="mdi:company" width="20" />
                  <p class="text-lg">Add Department</p>
                </div>
                <Icon
                  icon="material-symbols:add-box-outline"
                  width="25"
                  :class="{ 'ml-1': shouldShow }"
                />
              </div>
            </div>
          </section>
        </div>
        <div class="border-t border-dashed pt-2 pb-4">
          <section>
            <div class="mb-1">
              <router-link
                to="/data-entry/support"
                class="px-3 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all"
              >
                <Icon
                  icon="system-uicons:support"
                  width="30"
                  :class="{ ' ml-1': shouldShow }"
                />
                <p class="text-lg" v-if="!shouldShow">Support</p>
              </router-link>
            </div>
            <div class="mb-1">
              <router-link
                to="/data-entry/settings"
                class="px-3 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all"
              >
                <Icon
                  icon="uiw:setting-o"
                  width="25"
                  :class="{ 'ml-2': shouldShow }"
                />
                <p class="text-lg" v-if="!shouldShow">Settings</p>
              </router-link>
            </div>
          </section>
        </div>
      </section>
      <section class="hide_scroll w-full overflow-y-scroll">
        <TopBar :toggleSearchFunction="toggleSearchState"/>
        <router-view />
      </section>
    </main>
   <div class=" top-0 absolute ">
  <searchResult v-if="toggleSearch"  :toggleSearchFunction="toggleSearchState"/>
  </div>
  </template>
  
  <script setup>
  import { ref, watch, onMounted } from "vue";
  import searchResult from "@/components/Modals/SearchResult.vue";
  import TopBar from "@/layouts/TopBar/Main.vue"
  import { useRoute } from "vue-router";
  import logo from "../../assets/logo.vue";
  const toggleSearch = ref(false);
  
  const toggleSearchState = () => {
    toggleSearch.value = !toggleSearch.value;
  };
  
  const route = useRoute();
  
  const shouldShow = ref(false);
  
  // Watch for changes in the route and update visibility
  watch(
    () => route.path,
    (newPath) => {
      shouldShow.value =
        newPath.startsWith("/data-entry/settings") ;
    }
  );
  
  // Initial setup on component mount
  onMounted(() => {
    shouldShow.value =
      route.path.startsWith("/settings") || route.path.startsWith("/inbox");
  });
  
  const main = ref([
    {
      name: "Dashboard",
      path: "/data-entry",
      icon: "lucide:home",
    },
    {
      name: "Branches",
      path: "/data-entry/branches-overview",
      icon: "material-symbols:database-outline"
    }
  ]);
  
  const branches = ref([
    {
      name: "Communication",
      path: "/data-entry/branches/communication",
      icon: "clarity:circle-line",
    },
    {
      name: "Tech Titans",
      path: "/data-entry/branches/tech-titans",
      icon: "clarity:circle-line",
    },
    {
      name: "Information",
      path: "/data-entry/branches/information",
      icon: "clarity:circle-line",
    },
    {
      name: "Management",
      path: "/data-entry/branches/management",
      icon: "clarity:circle-line",
    },
  ]);
  </script>
  
  <style>
  /* Hide scrollbar for Chrome, Safari and Opera */
  .hide_scroll::-webkit-scrollbar {
    display: none;
  }
  
  /* Hide scrollbar for IE, Edge and Firefox */
  .hide_scroll {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
  }
  </style>
  