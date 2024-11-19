<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: {
      class:
        'bg-white px-5 max-h-[97vh] hide_scroll overflow-y-auto  rounded-md overflow-hidden',
    },
    content: { class: 'px-0  pb-6' },
    header: { class: ' bg-slate-100 -mt-2 ' },

    footer: { class: 'bg-white' },
  }" :style="{ width: '40rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
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
          <div v-for="(page, index) in page_count " class="h-1.5 w-20 rounded-lg bg-slate-200"
            :style="{ background: activePage > index ? '#13A928' : '' }">
          </div>
          <!-- <div class="h-1.5 w-20 rounded-lg bg-slate-200" :style="{ background: activePage > 1 ? '#13A928' : '' }">
          </div>
          <div class="h-1.5 w-20 rounded-lg bg-slate-200" :style="{ background: activePage > 2 ? '#13A928' : '' }">
          </div>
          <div class="h-1.5 w-20 rounded-lg bg-slate-200" :style="{ background: activePage > 3 ? '#13A928' : '' }">
          </div> -->
          <p>{{ Math.round(percentage) }}%</p>
        </div>
      </div>
    </template>
    <!-- <section class="bg-white pb-20 rounded-md mx-5" v-if="activePage === 1">
      <div class="flex items-start w-full justify-between p-7">
        <h1 class="text-gray-700 text-2xl font-semibold">Delivery Time</h1>
        <div>
          <div class="bg-slate-50 text-gray-700 p-3 rounded-md flex items-center gap-5">
            <Icon icon="lucide:lock" width="25" />
            <p class="text-2xl font-semibold">{{getTimeDifference()}}</p>
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
    </section> -->

    <section class="bg-white rounded-md mx-5">
      <div v-for="(item, index) in kra">
        <div v-show="activePage === 1">
          <div class="flex items-start w-full justify-between p-7">
            <h1 class="text-gray-700 text-2xl font-semibold">Delivery Time</h1>
            <div>
              <div class="bg-slate-50 text-gray-700 p-3 rounded-md flex items-center gap-5">
                <Icon icon="lucide:lock" width="25" />
                <p class="text-2xl font-semibold">{{ getTimeDifference() }}</p>
              </div>
              <p class="text-gray-400 text-end text-xs mt-2">
                Automatically calculated
              </p>
            </div>
          </div>
          <Divider class="mt-0" v-if="activePage == 1" />
        </div>
        <div v-show="activePage === index + 1">
          <div v-for="itm in item">
            <div class="mt-5 pt-5 px-7" v-if="itm.string =='point'">
              <h1 class="text-gray-700 text-2xl font-semibold">{{ itm.key }}</h1>
              <p class="text-gray-400 text-sm mt-2 mb-5">
               {{itm.description}}
              </p>
              <SingleColorSlide @click="singleSlider(itm, $event)"  :min="1" :max="parseInt(itm.value)" />
            </div>

            <Divider class="mt-20" />
            <div class="px-7 pt-3" v-if="itm.string == 'percentage'">
              <h1 class="text-gray-700 text-2xl font-semibold">
                {{itm.key}}
              </h1>
              <p class="text-gray-400 text-sm mt-2 mb-5">
                {{ itm.description }}
              </p>
              <RangeSlider v-model="SolvingValue" @click="Slider(itm, $event)" :min="0" :max="100" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white pb-20 mx-5 rounded-md" v-show="activePage === page_count">
      <div class="px-7 pt-3">
        <h1 class="text-gray-700 text-2xl font-semibold">Comment</h1>
        <p class="text-gray-400 text-sm mt-2 mb-5">
          Provide feedback or additional comments on the employee's performance.
        </p>
        <textarea name="" v-model="description"cols="30" rows="5" class="w-full border rounded-lg border-gray-300 resize-none mt-2 p-3"
          placeholder="Add Remark"></textarea>
      </div>
    </section>
    <template #footer>
      <div class="flex items-center justify-end px-7">

        <Button class="mr-4" v-if="activePage == 1" :pt="{
    root: {
      class:
        'py-2 bg-white px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />

<Button label="Previous" v-if="activePage > 1 && activePage <= page_count" :pt="{
    root: {
      class:
       'py-2 px-8 bg-white mr-5 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="previousButton"  />

        <Button label="Next" v-if="activePage < page_count" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="nextButton"  />
  

        <Button label="Submit" v-if="activePage === page_count" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit"  />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import RangeSlider from "../../../components/MulticolorSlider.vue";
import SingleColorSlide from "../../../components/SingleColorSlide.vue";
import {router} from "@inertiajs/vue3"

const props = defineProps(["modelValue", 'task']);

const toast = useToast();
const form = ref([])
const page_count = ref()
const activePage = ref(1);
const maxPage = 4;
const percentage = ref();
const kra = ref()
const percent_increment = ref(0)
const description = ref()

const emit = defineEmits(["update:modelValue"]);
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const singleSlider = (item, event)=>{
  let obj = {
    user_id: props.task.task_submission[0]?.user_id,
    key: item.key,
    value: event.target.value?event.target.value: 4,
    kra_value: item.value,
    string: item.string,
    task_id: props.task.id,
    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
  }

  let index = form.value.findIndex((item)=>{
    return item.user_id == obj.user_id && item.key == obj.key
  })
  console.log(index)
  if(index >= 0){
    form.value[index].value = event.target.value
  }else{
    form.value.push(obj)
  }
  console.log(item)
  console.log(event.target.value)
}

const Slider = (item, event)=>{
  let obj = {
    user_id: props.task.task_submission[0]?.user_id,
    key: item.key,
    value: event.target.value?event.target.value: 4,
    kra_value: item.value,
    string: item.string,
    task_id: props.task.id,
    created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
  }

  let index = form.value.findIndex((item)=>{
    return item.user_id == obj.user_id && item.key == obj.key
  })

  if(index >= 0){
    form.value[index].value = event.target.value
  }else{
    form.value.push(obj)
  }
}

const nextButton = () => {
  if (activePage.value < maxPage) {
    activePage.value++;
    percentage.value += percent_increment.value;
  }
};

const previousButton = () => {
  if (activePage.value < maxPage) {
    activePage.value--;
    percentage.value -= percent_increment.value;
  }
};


const closeModal = () => {
  form.value = []
  emit("update:modelValue", false);
};

const submit = () => {
  //form.value.description = description.value
  router.post('/dashboard/rate-user',{form:form.value, user_id: form.value[0].user_id, description: description.value}, {onSuccess: page=>{
    emit("update:modelValue", false);
    activePage.value = 1;
    percentage.value = 0;

    toast.add({
    severity: "success",
    summary: 'User Rated',
    detail: "User ratings saved successfully",
    life: 3000,
  });
  }})
};

const getTimeDifference = () => {
  let time1 = props.task.created_at;
  let time2 = props.task?.task_submission[props.task?.task_submission.length - 1]?.created_at

  if (time1 && time2) {
    time1 = new Date(time1);
    time2 = new Date(time2);

    // Calculate the difference in milliseconds
    const diffInMs = Math.abs(time1 - time2);

    // Convert milliseconds to total minutes
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));

    // Calculate days, hours, and minutes
    const days = Math.floor(diffInMinutes / (60 * 24)); // 1 day = 60 mins * 24 hrs
    const hours = Math.floor((diffInMinutes % (60 * 24)) / 60);
    const minutes = diffInMinutes % 60;

    // Format the output
    let formattedDifference = '';
    if (days > 0) {
      formattedDifference += `${days} day${days !== 1 ? 's' : ''} `;
    }
    if (hours > 0) {
      formattedDifference += `${hours} hr${hours !== 1 ? 's' : ''} `;
    }
    if (minutes > 0) {
      formattedDifference += `${minutes} min${minutes !== 1 ? 's' : ''}`;
    }

    console.log(formattedDifference.trim())

    return formattedDifference.trim()
  } else {
    return ''
  }
}

const getDepartmentKRA = () => {
  let user_id = props.task.task_submission[0]?.user_id;
  if (user_id) {
    axios.get('/dashboard/get-department-kra', { params: { user_id: user_id } }).then((response) => {
      kra.value = response.data
      if (response.data && response.data.length > 0) {
        page_count.value = parseInt(response.data.length + 1)
        percentage.value = parseFloat(100 / page_count.value)
        percent_increment.value = parseFloat(100 / page_count.value)
      } else {
        page_count.value = 0;
        percentage.value = 0;
      }
    })
  }
}

const accuracyValue = ref(1);
const productivityValue = ref(1);
const RatingValue = ref(0);
const SkillValue = ref(0);
const assessmentValue = ref(0);
const TeamValue = ref([]);
const SolvingValue = ref(0);

onMounted(() => {
  getDepartmentKRA();
})
</script>
<style scoped></style>