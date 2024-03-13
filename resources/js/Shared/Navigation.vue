<template>
  <!-- <section class="hidden lg:block w-1/5 border-r border-solid border-gray-200 sticky top-0  px-5 py-5"> -->
    <section class="hide_scroll hidden 2xl:block w-1/5 border-r border-solid border-gray-200 sticky top-0 h-screen px-5 py-5 overflow-y-scroll">
    <div class="text-xl font-semibold flex items-center gap-4 border-b border-dashed border-gray-200 pb-3 ">
      <logo />
      <p>Ansel LTD</p>
    </div>
    <div class="border-b border-dashed pb-4">
      <p class="text-gray-500 pl-4 mt-4 mb-2">Main</p>
      <section>
        <div v-for="(item, index) in main" :key="index" class="mb-1">
          <Link :href="item.path"
            class="px-4 py-3 flex gap-3 items-center text-gray-700 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <Icon :icon="item.icon" width="25" />
          <p class="text-lg">{{ item.name }}</p>
          </Link>
        </div>
      </section>
    </div>
    <div class="border-b border-dashed pb-4">
      <p class="text-gray-500 pl-4 mt-4 mb-2">Admin</p>
      <section>
        <div v-for="(item, index) in admin" :key="index" class="mb-1">
          <Link :href="item.path"
            class="px-4 py-3 flex gap-3 items-center text-gray-700 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <Icon :icon="item.icon" width="25" />
          <p class="text-lg">{{ item.name }}</p>
          </Link>
        </div>
      </section>
    </div>
    <div class="pb-4 mb-12">
      <div class="text-gray-500 pl-4 mt-4 mb-2 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="ic:round-greater-than" width="20" />
          <p>Branches</p>
        </div>
        <Icon icon="line-md:grid-3-filled" width="20" />
      </div>
      <section>
        <div v-for="(item, index) in branches" :key="index" class="mb-1">
          <Link :href="item.path"
            class="pl-4 pr-1 py-3 flex items-center justify-between text-gray-700 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <div class="flex items-center gap-3">
            <Icon :icon="item.icon" width="15" />
            <p class="text-lg">{{ item.name }}</p>
          </div>
          <Icon icon="ic:round-greater-than" width="20" />
          </Link>
        </div>
        <div class="mb-1">
          <div
            class="pl-4 pr-1 py-3 flex items-center justify-between text-gray-700 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
            <div class="flex items-center gap-3">
              <Icon icon="mdi:company" width="20" />
              <p class="text-lg">Add Department</p>
            </div>
            <Icon icon="material-symbols:add-box-outline" width="25" />
          </div>
        </div>
      </section>
    </div>
    <div class="border-t border-dashed pt-2 pb-4">
      <section>
        <div class="mb-1">
          <Link href="/"
            class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <Icon icon="system-uicons:support" width="30" />
          <p class="text-lg">Support</p>
          </Link>
        </div>
        <div class="mb-1">
          <Link href="/"
            class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <Icon icon="uiw:setting-o" width="25" />
          <p class="text-lg">Settings</p>
          </Link>
        </div>
      </section>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import logo from "../assets/logo.vue";
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const departments = computed(() => page.props.shared_departments)
const branches = ref([]);

const setDepartments = () => {
  for (let dept of departments.value) {
    let data = {
      name: dept.title,
      path: "/admin/branches/" + dept.id,
      icon: "clarity:circle-line",
    }
    branches.value.push(data)
  }
}

const main = ref([
  {
    name: "Admin",
    path: "/admin",
    icon: "lucide:home",
  },
  {
    name: "Analytics",
    path: "/analytics",
    icon: "carbon:analytics",
  },
  {
    name: "Inbox",
    path: "/inbox",
    icon: "octicon:inbox-24",
  },
]);

const admin = ref([
  {
    name: "Manage Users",
    path: "/admin/manage-users",
    icon: "mynaui:users",
  },
  {
    name: "Branch Setup",
    path: "/admin/branch-setup",
    icon: "solar:database-outline",
  },
  {
    name: "Access Config",
    path: "/access-config",
    icon: "mdi:cloud-key-outline",
  },
  {
    name: "Metadata Setup",
    path: "/admin/meta-data-setup",
    icon: "uiw:setting-o",
  },
  {
    name: "Audit Log",
    path: "/audit-log",
    icon: "material-symbols-light:layers-outline",
  },
  {
    name: "Recycle Bin",
    path: "/recycle-bin",
    icon: "ep:delete",
  },
]);

onMounted(() => {
  setDepartments()
})
</script>