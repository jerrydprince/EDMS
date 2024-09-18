<template>
  <div>
    <Line v-if="datacollection" :chart-data="datacollection" :options="chartOptions"></Line>
    <div v-else>Loading...</div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement)

export default defineComponent({
  name: 'LineChart',
  components: {
    Line
  },
  setup() {
    const datacollection = ref(null)
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: 10
        }
      }
    }

    onMounted(() => {
      setTimeout(() => {
        datacollection.value = {
          labels: Array.from({ length: 30 }, (_, i) => `Day ${i + 1}`),
          datasets: [
            {
              label: 'Latency',
              backgroundColor: 'rgba(0, 128, 0, 0.2)',
              borderColor: 'rgba(0, 128, 0, 1)',
              data: Array.from({ length: 30 }, () => Math.floor(Math.random() * 10)),
              fill: true
            }
          ]
        }
        console.log('Data collection initialized:', datacollection.value)
      }, 1000)
    })

    return {
      datacollection,
      chartOptions
    }
  }
})
</script>

<style scoped>
/* Add any styles if needed */
</style>
