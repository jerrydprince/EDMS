<template>
  <main class="flex pb-20  items-center justify-center px-7 flex-col  2xl:w-[80vw]">
    <div class="text-center ">
      <h1 class="text-3xl text-gray-800 font-semibold mt-10 mb-3">
        KRA Configuration
      </h1>
      <p class="text-gray-500">
        Configure the metadata to be used for the creation of KRA for
        performance tracking for tasks across branches
      </p>
    </div>

    <div class="flex items-start gap-5">
      <div>
        <div  class="flex items-start gap-5 mt-16 relative" v-if="kpi && kpi.length >0">
          <p class=" absolute right-0 -top-8  text-sm bg-white p-1 rounded-2xl px-2">{{ formatDate(kra.created_at) }}</p>
          <img src="../../assets/images/kra.svg" alt="" class="py-2 w-4 mt-4" />
          <div class="bg-white p-2 rounded-xl px-4">
            <div class="flex gap-5 items-center">
              <h1 class="text-gray-500">{{ kra.name }}</h1>
              <div class="lg:w-[40vw]  rounded-lg p-2">
                <input :value="kra.description" class=" bg-slate-50 p-2 py-3 w-full outline-none rounded-lg " />
              </div>
              <div @click="toggleKPI(0)" class="cursor-pointer ml-2 lg:ml-2 duration-300 transition-all"
                :class="[openKPI[index] ? ' -rotate-180 ' : ' rotate-0']">
                <Icon icon="icons8:angle-down" class="text-gray-500" />
              </div>

              <Icon icon="fluent:delete-48-regular" class="text-gray-500 cursor-pointer"
                @click="deletekra_modal = true" />
            </div>
            <div class="rounded-md px-5 overflow-hidden duration-500 transition-all"
              :class="[openKPI[0] ? 'max-h-[500px] py-3 mt-3 ' : 'h-0 mt-0 max-h-0 py-0']">
              <table class="min-w-max w-full bg-slate-50">
                <thead class=" ">
                  <tr class="text-left  text-gray-500">
                    <td class=" pl-5 pt-3">KPI</td>
                    <td>String</td>
                    <th></th>
                  </tr>
                </thead>
                <draggable tag="tbody" :list="kpi" :item-key="index" group="demo">
                  <template #item="{ element }">
                    <tr class="border-b  mb-3">
                      <td class="py-4 pl-5 capitalize">{{ element.key }}</td>
                      <td class="py-4">{{ element.value }} 
                        <span v-if="element.string=='point'"> point scale</span>
                        <span v-if="element.string=='percentage'"> percentage</span>
                      </td>
                      <td class="py-4 pr-5 text-gray-500">
                        <div class="flex justify-end items-center gap-3">
                          <Icon icon="uil:edit" class="cursor-pointer" @click="editKPI(element)"/>
                          <div>
                            <!-- <Icon icon="fluent:delete-16-regular" class=" cursor-pointer"
                              @click="deletekpi_modal = true" /> -->
                              <span v-html="delete_icon" class="cursor-pointer" @click="deleteKPI(element)"></span>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>
              </table>
              <button @click="add_kpi = true"
                class=" font-semibold text-gray-800 border-gray-300 flex items-center gap-3 w-full  justify-center  border p-2 rounded-lg mt-2">
                <Icon icon="clarity:plus-line" />
                <p>Add KPI </p>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class=" relative mt-16 add-kpi">
        <div class="tooltip rounded-sm shadow-lg">
          <p class=" text-white whitespace-nowrap text-xs p-2"> Add New KRA </p>
        </div>
        <div class="bg-white p-5 rounded-md  " @click="add_kra = true" v-if="kpi && kpi.length == 0">
          <!-- <Icon icon="clarity:plus-line" width="40" /> -->
           <span v-html="plus_icon" class="cursor-pointer"></span>
        </div>
      </div>
    </div>


  </main>
  <AddKPI v-model="add_kpi" :kraId="kra?.id" @refresh="refresh"/>
  <EditKPI v-model="edit_kpi" :kpi="selected_kpi" :kraId ='kra?.id' @refresh="refresh"/>
  <AddKRA v-model="add_kra" @refresh="refresh" />
  <DeleteKRA v-model="deletekra_modal" :kra_id="kra.id" @refresh="refresh"  />
  <DeleteKPI v-model="deletekpi_modal" :kpi_id="selected_kpi.id" @refresh="refresh" />
</template>

<script setup>
import { ref, onMounted } from "vue";
import draggable from "vuedraggable";
import AddKPI from "../../components/Modals/PMS/AddKPI.vue";
import EditKPI from "../../components/Modals/PMS/EditKPI.vue";
import AddKRA from "../../components/Modals/PMS/AddKRA.vue";
import DeleteKRA from "../../components/Modals/PMS/DeleteKRA.vue";
import DeleteKPI from "../../components/Modals/PMS/DeleteKPI.vue";

const deletekra_modal = ref(false);
const deletekpi_modal = ref(false)
const add_kpi = ref(false)
const add_kra = ref(false)
const kra = ref([])
const selected_kpi = ref([])
const edit_kpi = ref(false)
const delete_icon = `<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.85645 3.56696H2.9993M2.9993 3.56696H12.1422M2.9993 3.56696V11.567C2.9993 11.8701 3.11971 12.1608 3.33404 12.3751C3.54836 12.5894 3.83905 12.7098 4.14216 12.7098H9.85644C10.1595 12.7098 10.4502 12.5894 10.6646 12.3751C10.8789 12.1608 10.9993 11.8701 10.9993 11.567V3.56696H2.9993ZM4.71359 3.56696V2.42411C4.71359 2.121 4.834 1.83031 5.04832 1.61599C5.26265 1.40166 5.55334 1.28125 5.85645 1.28125H8.14216C8.44526 1.28125 8.73595 1.40166 8.95028 1.61599C9.16461 1.83031 9.28502 2.121 9.28502 2.42411V3.56696M5.85645 6.42411V9.85268M8.14216 6.42411V9.85268" stroke="#667085" stroke-width="1.10636" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`
const editKPI = (kpi)=>{
  edit_kpi.value = true;
  selected_kpi.value = kpi
}

const deleteKPI = (kpi)=>{
  deletekpi_modal.value = true;
  selected_kpi.value = kpi
}

const kpi = ref([
  // {
  //   name: "KRA 2",
  //   title: "Operational Efficiency",
  //   list: [
  //     {
  //       id: 5,
  //       kpi: "Timeliness",
  //       string: "Time",
  //     },
  //     {
  //       id: 6,
  //       kpi: "Accuracy",
  //       string: "5 point scale",
  //     },
  //     {
  //       id: 7,
  //       kpi: "Productivity",
  //       string: "5 point scale",
  //     },
  //     {
  //       id: 8,
  //       kpi: "Communication skills",
  //       string: "Percentage",
  //     },
  //   ],
  // },
 
]);

const getKRA = ()=>{
  axios.get('/dashboard/get-kra').then((response)=>{
   if(response.data){
    if(response.data && response.data.kpi){
      kpi.value = response.data.kpi;
    }else{
      kpi.value = []
    }
   
    kra.value = response.data
   }
  })
}

const formatDate = (inputDate) => {
  var date = new Date(inputDate);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  var day = date.getDate();
  var month = months[date.getMonth()];
  var year = date.getFullYear();

  day = day < 10 ? '0' + day : day;

  var formattedDate = day + ' ' + month + ' ' + year.toString();

  return formattedDate;
}

const refresh = ()=>{
  getKRA()
}

const plus_icon = ref(`<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6 12H18" stroke="#1C1132" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 18V6" stroke="#1C1132" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`)

const openKPI = ref(kpi.value.map(() => false));

const toggleKPI = (index) => {
  openKPI.value[index] = !openKPI.value[index];
};

onMounted(()=>{
  getKRA()
})
</script>

<style scoped>
.tooltip {
  display: none;
  position: absolute;
  user-select: none;
  top: -2.5em;
  left: 50%;

  transform: translate(-50%, -45%);
  background-color: #000000ed;
}

.tooltip::before {
  content: "";
  position: absolute;
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 10px solid black;
  bottom: -0.5em;
  left: 50%;
  transform: translate(-50%);
}

.add-kpi:hover .tooltip {
  display: block;
}
</style>