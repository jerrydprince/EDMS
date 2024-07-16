<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5  rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ position: 'relative', width: '40rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="material-symbols:share-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Share Document</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="flex bg-slate-50 items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
        <i class="pi pi-search text-gray-700" style="font-size: 1rem"></i>
        <input v-model="searchText" type="text" class=" w-full bg-slate-50 p-2 border-0 outline-none text-gray-700"
          placeholder="Search Name" />
      </div>


      <div class=" flex items-center w-full bg-slate-50 p-1 text-gray-700 text-base mt-3">
        <button @click="togglebutton = 1" class=" w-[50%]  py-2 rounded-md" :class="{ 'activebutton': togglebutton === 1 }">
          Search by user
        </button>

        <button class=" w-[50%] py-2 rounded-md" @click="togglebutton = 2" :class="{ 'activebutton': togglebutton === 2 }">
          Search by designation
        </button>
      </div>
      <Divider />
      <div v-if="users && users.length > 0" class="rounded-md max-h-[35vh] overflow-y-scroll hide_scroll">
        <div v-for="(item, index) in users" :key="index">
          <div class=" flex items-center gap-5 my-5 ">
            <input type="checkbox" class=" w-5 h-5" v-model="selectedUsers" :value="item.id">
            <div class=" flex items-center gap-5">
              <div>
                <img :src="item.profile_image" alt="" class=" w-12">
              </div>
              <div>
                <h1 class=" text-gray-800 font-semibold ">{{ item.first_name+" "+item.last_name }}</h1>
                <p class=" text-gray-500 text-sm">{{ item.email }}</p>
              </div>

              <button v-if="togglebutton === 2" class=" text-sm text-primary bg-primary/5 px-3 py-1 rounded-xl">{{
    item.designation }}</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="users.length === 0" class=" w-full h-[35vh] flex items-center flex-col justify-center">
        <img src="/images/search.svg" alt="" class=" w-16">
        <h1 class=" text-gray-800 text-lg font-semibold mt-3">No Result Found</h1>
        <div class=" flex items-start gap-2 p-3 rounded-lg bg-yellow-50 mt-5 text-[#DC6803] ">
          <Icon icon="mdi:warning-circle" class=" rotate-180" width="25" />
          <p class=" w-[90%]">Your search results are limited based on your access level and designation. For
            assistance,
            please contact <span class=" underline cursor-pointer"> Help & Support. </span></p>
        </div>
      </div>

      <div v-if="send_option" class=" bg-white right-5 bottom-14 absolute py-2 flex flex-col border rounded-lg ">
        <button class=" px-5   py-2 hover:bg-slate-50" @click="submit">Send via mail</button>
        <button class=" px-5  py-2 hover:bg-slate-50" @click="submit">Send via chat</button>
      </div>
    </section>
    <template #footer>
      <div class=" flex items-center  justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 bg-white px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Send" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="send_option = true" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import debounce from 'lodash.debounce';
import {usePage, router} from "@inertiajs/vue3";
import axios from "axios";


const props = defineProps(["modelValue", "archive"]);
const togglebutton = ref(1)
const searchText = ref('')
const users = ref([])
const page = usePage();
const selectedUsers = ref([])

const send_option = ref(false)


const searchUsers = debounce(() => {
  axios.get('/share-file-search', { params: { search: searchText.value } }).then((response) => {
    users.value = response.data
  })
}, 500)

const emit = defineEmits(["update:modelValue"]);
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

};

const submit = () => {
 
  // router.post('/share-file', {ids: selectedUsers.value, archive_id: props.archive.id}, {onSuccess: page=>{
  //   emit("update:modelValue", false);
  //   send_option.value = false
  //     toast.add({
  //       severity: "success",
  //       summary: '',
  //       detail: "",
  //       life: 3000,
  //     });
  // }})
  
};

watch(searchText, () => {
  searchUsers()
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}

.activebutton {
  background-color: white;
  font-weight: 600;
}
</style>