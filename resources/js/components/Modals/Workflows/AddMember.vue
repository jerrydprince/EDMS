<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
    :style="{ width: '35rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog overflow-y-scroll"
  >
    <template #header>
      <div class="flex items-start justify-between w-full" v-if="addMember">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-full">
            <Icon icon="ep:user" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Add Members</h1>
            <p class="text-sm">Add users and user groups to your workflows.</p>
          </div>
        </div>

        <div
          class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
          @click="closeModal"
        >
          <Icon icon="mdi:times" width="20" />
        </div>
      </div>

      <div class="flex items-start justify-between w-full" v-else>
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-full">
            <Icon icon="ep:user" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Workflow Members</h1>
            <p class="text-sm">List of memebers assigned to this workflow</p>
          </div>
        </div>

        <div
          class="bg-[#F2F4F7] p-2 rounded-full cursor-pointer"
          @click="closeModal"
        >
          <Icon icon="mdi:times" width="20" />
        </div>
      </div>
    </template>
    <Divider class="-mt-2" />
    <section class=" ">
      <div v-if="addMember" class="flex  mx-6 items-start  justify-between">
        <div
          class="flex flex-wrap items-center w-[78%] gap-4 p-3 border border-gray-300 rounded-md"
        >
          <div
            class="flex items-center bg-[#f0f1f4] p-1 rounded-3xl border border-solid border-gray-400"
            v-for="(item, index) in selectedMembers"
            :key="index"
          >
            <img :src="oneIcon" alt="" class="w-5" />
            <p class="text-xs w-fit">{{ item.name }}</p>
            <Icon icon="clarity:times-line" class="cursor-pointer" />
          </div>
        </div>

        <button @click="submit" class=" bg-primary text-white text-xs px-3 py-2 rounded-md ">Add Member</button>
      </div>
      <div class="relative my-4">
        <div class="flex px-7 text-[#667085] gap-10 relative z-50">
          <h1
            class="p-2 border-b-[1px] cursor-pointer"
            @click="activeButton = 1"
            :style="{
              borderColor: activeButton === 1 ? '#0D6E1A' : '#ffffff00',
              color: activeButton === 1 ? '#0D6E1A' : 'inherit',
            }"
          >
            Users
          </h1>
          <h1
            class="p-2 border-b-[1px] cursor-pointer"
            @click="activeButton = 2"
            :style="{
              borderColor: activeButton === 2 ? '#0D6E1A' : '#ffffff00',
              color: activeButton === 2 ? '#0D6E1A' : 'inherit',
            }"
          >
            Member Groups
          </h1>
        </div>
        <div class="absolute z-30 bottom-0 w-full h-[1px] bg-gray-300"></div>
      </div>

      <div
        class="mx-6 flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0 bg-gray-50"
      >
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <Input
          type="text"
          v-model="value"
          :placeholder="
            activeButton === 1
              ? 'Search Users'
              : 'Search member group (access levels) & branches'
          "
          class="w-96 border-0 outline-none bg-gray-50 text-[#98A2B3]"
        />
      </div>
      <div>
        <div class="px-6 mt-3 max-h-[40vh] overflow-y-scroll">
          <div v-if="activeButton === 1">
            <div
              v-for="(member, index) in members"
              :key="index"
              class="flex items-center justify-between mb-3"
            >
              <div class="flex items-center gap-3">
                <input type="checkbox" />
                <img :src="oneIcon" alt="" />
                <div>
                  <h1 class="font-semibold">{{ member.name }}</h1>
                  <p class="text-sm text-[#667085]">{{ member.branch }}</p>
                </div>
              </div>
              <div>
                <div
                  class="flex items-center justify-end w-52 cursor-pointer"
                  @click="toggle"
                  aria-haspopup="true"
                  aria-controls="overlay_menu"
                >
                  <Icon icon="mi:options-vertical" width="25" />
                </div>
              </div>
            </div>
            <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true">
              <template #item="{ item }">
                <div
                  class="flex items-center gap-2 font-semibold p-2"
                  :class="{
                    'text-red-500': item.label === 'Report Contact',
                  }"
                >
                  <Icon :icon="item.icon" width="20" />
                  <p>{{ item.label }}</p>
                </div>
              </template>
            </Menu>
          </div>

          <div v-if="activeButton === 2">
            <div
              class="mb-4 text-[#1D2939] font-medium bg-gray-100 flex items-center w-fit rounded-md p-1"
            >
              <button
                class="rounded-lg py-1 px-4"
                @click="selectButton = 1"
                :class="{ activeButton: selectButton === 1 }"
              >
                <p>Member Groups</p>
              </button>
              <div>
                <button
                  class="flex gap-2 items-center rounded-lg py-1 px-4"
                  @click="selectButton = 2"
                  :class="{ activeButton: selectButton === 2 }"
                >
                  <p>Branches</p>
                </button>
              </div>
            </div>
            <div v-if="selectButton === 1">
              <div
                v-for="(group, index) in groups"
                :key="index"
                class="flex items-center justify-between mb-3"
              >
                <div class="flex items-center gap-3">
                  <input type="checkbox" />
                  <div class="p-3 border rounded-full">
                    <Icon icon="lucide:users" />
                  </div>
                  <div>
                    <h1 class="font-semibold">{{ group.name }}</h1>
                    <p class="text-sm text-[#667085]">
                      {{ group.member }} Members
                    </p>
                  </div>
                </div>
                <div>
                  <div
                    class="flex items-center justify-end w-52 cursor-pointer"
                    @click="toggle"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                  >
                    <Icon icon="mi:options-vertical" width="25" />
                  </div>
                </div>
              </div>
              <Menu
                ref="menu"
                id="overlay_menu"
                :model="groupOption"
                :popup="true"
              >
                <template #item="{ item }">
                  <div
                    class="flex items-center gap-2 font-semibold p-2"
                    :class="{
                      'text-red-500': item.label === 'Report Contact',
                    }"
                  >
                    <Icon :icon="item.icon" width="20" />
                    <p>{{ item.label }}</p>
                  </div>
                </template>
              </Menu>
            </div>

            <div v-else>
              <div
                v-for="(branch, index) in Branches"
                :key="index"
                class="flex items-center justify-between mb-3"
              >
                <div class="flex items-center gap-3">
                  <input type="checkbox" />
                  <div class="p-3 border rounded-full">
                    <Icon icon="lucide:users" />
                  </div>
                  <div>
                    <h1 class="font-semibold">{{ branch.name }}</h1>
                    <p class="text-sm text-[#667085]">
                      {{ branch.member }} Members
                    </p>
                  </div>
                </div>
                <div>
                  <div
                    class="flex items-center justify-end w-52 cursor-pointer"
                    @click="toggle"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                  >
                    <Icon icon="mi:options-vertical" width="25" />
                  </div>
                </div>
              </div>
              <Menu
                ref="menu"
                id="overlay_menu"
                :model="groupOption"
                :popup="true"
              >
                <template #item="{ item }">
                  <div
                    class="flex items-center gap-2 font-semibold p-2"
                    :class="{
                      'text-red-500': item.label === 'Report Contact',
                    }"
                  >
                    <Icon :icon="item.icon" width="20" />
                    <p>{{ item.label }}</p>
                  </div>
                </template>
              </Menu>
            </div>
          </div>
        </div>
      </div>
    </section>
    <Divider class="-mb-2" v-if="!addMember" />
    <template #footer>
      <Button
        v-if="!addMember"
        @click="addMember = true"
        label="Add new Member "
        class=""
        :pt="{
          root: {
            class:
              'py-2  px-10 border border-gray-300 text-[#344054] font-light  rounded-lg',
          },
        }"
      />
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import oneIcon from "../../../../../public/images/members/1.svg";

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const activeButton = ref(1);
const selectButton = ref(1);
const addMember = ref(false);
const members = ref([
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
  {
    name: "Jenny Williams",
    branch: "Communication division",
  },
]);

const groups = ref([
  {
    name: "Archivers Access Level",
    member: "105",
  },
  {
    name: "Archivers Access Level",
    member: "105",
  },
  {
    name: "Archivers Access Level",
    member: "105",
  },
  {
    name: "Archivers Access Level",
    member: "105",
  },
  {
    name: "Archivers Access Level",
    member: "105",
  },
  {
    name: "Archivers Access Level",
    member: "105",
  },
]);

const Branches = ref([
  {
    name: "Communication Department",
    member: "105",
  },
  {
    name: "Finance Unit",
    member: "105",
  },
  {
    name: "Records Division",
    member: "105",
  },
  {
    name: "HR Unit",
    member: "105",
  },
  {
    name: "Public Relations Division",
    member: "105",
  },
  {
    name: "Accounting Department",
    member: "105",
  },
]);

const selectedMembers = ref([
  {
    name: "Jenny Williams",
  },
  {
    name: "Dave Miye",
  },
  {
    name: "James Mitch",
  },
]);
const menu = ref();
const groupOption = ref([
  { label: "Remove user group", icon: "lucide:user-minus" },
]);
const menuItems = ref([
  { label: "Remove Member", icon: "lucide:user-minus" },
  { label: "Report User", icon: "lucide:log-out" },
]);
const toggle = (event) => {
  menu.value.toggle(event);
};
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);
  activeButton.value = 1;
 selectButton.value = 1;
 addMember.value = false;
};

const submit = () => {
  emit("update:modelValue", false);
   activeButton.value = 1;
 selectButton.value = 1;
 addMember.value = false;
  //   toast.add({
  //     severity: "success",
  //     summary: '',
  //     detail: "",
  //     life: 3000,
  //   });
};
</script>
 <style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
.p-inputtextarea {
  outline: none;
  box-shadow: none !important;
  resize: none;
  width: 100%;
}

.activeButton {
  background-color: white;
  border-radius: 5px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
.p-inputtextarea:focus {
  border-color: #d0d5dd; /* Hide border */
}
</style>