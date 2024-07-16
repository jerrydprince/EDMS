<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: {
        class: 'bg-slate-100  px-5 rounded-md  overflow-y-scroll hide_scroll',
      },
      content: { class: 'px-0 pb-6' },
      header: { class: 'm-5' },
      footer: { class: 'bg-slate-100' },
    }"
    :style="{
      position: 'relative',
      overflow: 'visible',
      width: '40rem',
      padding: '10px 0px',
    }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class="custom-dialog"
  >
    <template #header>
      <div class="bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mdi:speedometer-slow" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">
              Task Completion and Performance Rating
            </h1>
          </div>
        </div>
        <div class="ml-20 flex items-center gap-3">
          <div
            class="h-1.5 w-20 rounded-lg bg-slate-200"
            :style="{ background: activePage > 0 ? '#13A928' : '' }"
          ></div>
          <div
            class="h-1.5 w-20 rounded-lg bg-slate-200"
            :style="{ background: activePage > 1 ? '#13A928' : '' }"
          ></div>
          <div
            class="h-1.5 w-20 rounded-lg bg-slate-200"
            :style="{ background: activePage > 2 ? '#13A928' : '' }"
          ></div>
          <div
            class="h-1.5 w-20 rounded-lg bg-slate-200"
            :style="{ background: activePage > 3 ? '#13A928' : '' }"
          ></div>
          <p>{{ percentage }}%</p>
        </div>
      </div>
    </template>
    <section class="bg-white pb-20 rounded-md mx-5" v-if="activePage === 1">
      <div class="flex items-start w-full justify-between p-7">
        <h1 class="text-gray-700 text-2xl font-semibold">Delivery Time</h1>
        <div>
          <div
            class="bg-slate-50 text-gray-700 p-3 rounded-md flex items-center gap-5"
          >
            <Icon icon="lucide:lock" width="25" />
            <p class="text-2xl font-semibold">1hr 30 mins</p>
          </div>
          <p class="text-gray-400 text-end text-xs mt-2">
            Automatically calculated
          </p>
        </div>
      </div>
      <Divider class="mt-0" />
      <div class="px-7">
        <h1 class="text-gray-700 text-2xl font-semibold">Accuracy</h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How accurately did the employee perform the assigned tasks and data
          entries?
        </p>
        <SingleColorSlide v-model="accuracyValue" :min="1" :max="5" />
      </div>

      <Divider class="mt-20" />
      <div class="mt-5 px-7">
        <h1 class="text-gray-700 text-2xl font-semibold">Productivity</h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How well did the employee complete the task within the assigned time
          frame.
        </p>
        <SingleColorSlide v-model="productivityValue" :min="1" :max="5" />
      </div>
    </section>

    <section class="bg-white pb-20 rounded-md mx-5" v-if="activePage === 2">
      <div class="px-7 pt-5">
        <h1 class="text-gray-700 text-2xl font-semibold">
          Customer satisfaction rating
        </h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How satisfied were customers with the employee's service and
          performance?
        </p>

        <RangeSlider v-model="RatingValue" :min="0" :max="100" />
      </div>

      <Divider class="mt-20" />
      <div class="px-7 pt-3">
        <h1 class="text-gray-700 text-2xl font-semibold">
          Communication skills
        </h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How effectively does the employee convey information and interact with
          others?
        </p>
        <RangeSlider v-model="SkillValue" :min="0" :max="100" />
      </div>

      <Divider class="mt-20" />
      <div class="px-7 pt-3">
        <h1 class="text-gray-700 text-2xl font-semibold">
          Subjective competence assessment
        </h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How proficiently does the employee demonstrate the necessary skills to
          perform their tasks?
        </p>
        <RangeSlider v-model="assessmentValue" :min="0" :max="100" />
      </div>
    </section>

    <section class="bg-white pb-20 rounded-md mx-5" v-if="activePage === 3">
      <div class="mt-5 pt-5 px-7">
        <h1 class="text-gray-700 text-2xl font-semibold">Team Work</h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How actively did the employee participate and contribute to team
          projects?
        </p>
        <SingleColorSlide v-model="TeamValue" :min="1" :max="5" />
      </div>

      <Divider class="mt-20" />
      <div class="px-7 pt-3">
        <h1 class="text-gray-700 text-2xl font-semibold">
          Problem solving skills
        </h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          How effectively does the employee identify issues and implement
          solutions?
        </p>
        <RangeSlider v-model="SolvingValue" :min="0" :max="100" />
      </div>
    </section>

    <section class="bg-white pb-20 mx-5 rounded-md" v-if="activePage === 4">
      <div class="px-7 pt-3">
        <h1 class="text-gray-700 text-2xl font-semibold">Comment</h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          Provide feedback or additional comments on the employee's performance.
        </p>
        <textarea
          name=""
          id=""
          cols="30"
          rows="5"
          class="w-full border rounded-lg border-gray-300 resize-none mt-2 p-3"
          placeholder="Add Remark"
        ></textarea>
      </div>
    </section>
    <template #footer>
      <div class="flex items-center justify-end px-7">
        <Button
          class="mr-4"
          :pt="{
            root: {
              class:
                'py-2 bg-white px-8 border-gray-200 border border-solid rounded-lg',
            },
          }"
          @click="closeModal"
          label="Cancel"
          severity="secondary"
          outlined
        />
        <Button
          label="Next"
          v-if="activePage < 4"
          :pt="{
            root: {
              class:
                'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
            },
          }"
          @click="nextButton"
          autofocus
        />

        <Button
          label="Submit"
          v-if="activePage === 4"
          :pt="{
            root: {
              class:
                'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
            },
          }"
          @click="submit"
          autofocus
        />
      </div>
    </template>
  </Dialog>
</template>
  
  <script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import RangeSlider from "../../../components/MulticolorSlider.vue";
import SingleColorSlide from "../../../components/SingleColorSlide.vue";

const props = defineProps(["modelValue"]);

const toast = useToast();

const emit = defineEmits(["update:modelValue"]);
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});
const activePage = ref(1);
const maxPage = 4;
const percentage = ref(25);
const nextButton = () => {
  if (activePage.value < maxPage) {
    activePage.value++;
    percentage.value += 25;
  }
};

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  emit("update:modelValue", false);
};

const accuracyValue = ref(1);
const productivityValue = ref(1);
const RatingValue = ref(0);
const SkillValue = ref(0);
const assessmentValue = ref(0);
const TeamValue = ref(1);
const SolvingValue = ref(0);
</script>
     <style scoped>
</style>