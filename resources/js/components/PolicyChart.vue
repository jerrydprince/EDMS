<template>
  <main>
    <div>
      <div class="flex items-center justify-between w-full p-3">
        <h1 class="font-semibold text-lg">Policy & Procedure Adherence</h1>
        <button
          @click="generateReport"
          class="p-2 rounded-md text-xs font-semibold text-gray-700 hover:bg-slate-50 border flex items-center gap-1 border-gray-300 transition-colors"
          title="Download Report PDF"
        >
          <Icon icon="pepicons-pop:file" />
          <p>Generate Report</p>
        </button>
      </div>
      <div class="bg-white rounded-md pt-10 flex items-start w-full justify-between">
        <vue-speedometer :minValue="0" :maxValue="100" :width="400" :needleHeightRatio="0.7" :value="getChartPercentage()" :segments="4"
          :segmentColors="['#FF3932', '#F79009', '#60B527', '#038C8C']" :customSegmentLabels="[
          {
            text: '',
            position: 'INSIDE',
            color: '#555',
          },
          {
            text: '',
            position: 'INSIDE',
            color: '#555',
          },
          {
            text: '',
            position: 'INSIDE',
            color: '#555',
          },
          {
            text: '',
            position: 'INSIDE',
            color: '#555',
          },
        ]"  :ringWidth="27" valueTextFontSize="32px" :needleTransitionDuration="3333"
          needleTransition="easeElastic" needleColor="#D0D5DD" textColor="#000000" />

        <div class=" mt-5 mr-2">
          <p class=" text-[#038C8C] text-lg flex items-center gap-1 ">
            <Icon icon="oui:dot" /><span class=" text-sm text-gray-400">Excellent (61%-100%) </span>
          </p>
          <p class=" text-[#60B527] text-lg my-2 flex items-center gap-1 ">
            <Icon icon="oui:dot" /> <span class=" text-sm text-gray-400">Good (46%-60%) </span>
          </p>
          <p class=" text-[#F79009] text-lg flex items-center gap-1">
            <Icon icon="oui:dot" /> <span class=" text-sm text-gray-400">Fair (26%-45%)</span>
          </p>
          <p class=" text-[#FF3932] text-lg flex items-center gap-1">
            <Icon icon="oui:dot" /> <span class=" text-sm text-gray-400">Needs Improvement (0%-25%)</span>
          </p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import VueSpeedometer from "vue-speedometer/slim";
import {ref, defineProps} from "vue"

const props = defineProps(['chart'])

const getChartPercentage = ()=>{
  if(props.chart){
    const totalValue = props.chart.reduce((sum, item) => {
        return sum + parseInt(item.value, 10); // Convert 'value' to an integer
      }, 0);
    let total = props.chart.length;
    return Math.round(totalValue/total)
  }
  return 0
}

const generateReport = () => {
  const match = window.location.pathname.match(/\/pms-user-profile\/(\d+)/);
  if (match && match[1]) {
    window.open(`/dashboard/pms/report/user/${match[1]}`, '_blank');
  } else {
    window.open('/dashboard/pms/report/department', '_blank');
  }
};
</script>

<style></style>
