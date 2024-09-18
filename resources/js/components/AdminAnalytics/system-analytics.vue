<template>
    <div class="md:flex items-center gap-2">
      <div class="bg-white rounded-2xl w-full md:w-[30%] p-14">
        <div
          class="flex items-center justify-center flex-col whitespace-nowrap"
          v-if="props.stat.is_online === 'Online'"
        >
          <div
            class="w-fit text-green-500 p-5 rounded-full border-green-500 border-[5px]"
          >
            <Icon icon="eva:flash-fill" width="60" />
          </div>
          <h1 class="font-semibold text-2xl my-3">System operational!</h1>
          <div
            class="w-fit flex items-center gap-1 text-green-500 py-0.5 px-3 rounded-2xl bg-slate-50"
          >
            <Icon icon="oui:dot" />
            <p>Online</p>
          </div>
        </div>

        <div
          class="flex items-center justify-center flex-col whitespace-nowrap"
          v-else
        >
          <div
            class="w-fit text-red-500 p-5 rounded-full border-red-500 border-[5px]"
          >
            <Icon icon="ion:cloud-offline-outline" width="60" />
          </div>
          <h1 class="font-semibold text-2xl my-3">System offline</h1>
          <div
            class="w-fit flex items-center gap-1 text-red-500 py-0.5 px-3 rounded-2xl bg-slate-50"
          >
            <Icon icon="oui:dot" />
            <p>Offline</p>
          </div>
        </div>
      </div>
      <div
        class="overflow-auto mt-5 md:mt-0 w-full md:w-[70%] pb-9 bg-white rounded-2xl p-5"
      >
        <div class="flex items-center w-full justify-between">
          <div>
            <div class="flex items-center justify-between text-sm">
              <div
                class="bg-gray-100 flex items-center rounded-md p-1 whitespace-nowrap"
              >
                <button
                  class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                  @click="buttonToggle = 1"
                  :class="{ activeButton: buttonToggle === 1 }"
                >
                  <p>System status</p>
                </button>
                <button
                  class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
                  @click="buttonToggle = 2"
                  :class="{ activeButton: buttonToggle === 2 }"
                >
                  <p>Response time</p>
                </button>
              </div>
            </div>
          </div>
          <div>
            <select
              name=""
              id=""
              class="w-40 px-2 py-2 outline-none bg-slate-50 rounded-md"
              v-if="buttonToggle === 1"
            >
              <option value="All Time" class="">All Time</option>
              <option value="Today">Today</option>
              <option value="Yesterday">Yesterday</option>
              <option value="This Week">This Week</option>
              <option value="10 days">10days</option>
              <option value="30 days">30days</option>
              <option value="60 days">60days</option>
            </select>

            <div class="flex items-center p-2 gap-2" v-else>
              <h1 class="text-gray-500">Current response time</h1>
              <p class="font-semibold">4.12 ms</p>
            </div>
          </div>
        </div>

        <div>
          <status />
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, defineProps } from "vue";
const props = defineProps(["stat"]);
import status from "../../components/AdminAnalytics/Status.vue";
const buttonToggle = ref(1);
const system_status = ref("offline");
</script>

<style>

</style>
