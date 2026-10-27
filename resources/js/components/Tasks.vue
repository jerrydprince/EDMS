<template>
  <main class="bg-white p-7 rounded-2xl mt-4 border border-gray-200 shadow-sm">
    <div class="flex items-center w-full justify-between pb-4 border-b border-gray-100">
      <div class="flex items-center gap-4">
        <h2 class="font-bold text-xl text-gray-900">Task Execution Kanban</h2>
        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-slate-100 px-3 py-1 rounded-full font-medium">
          <Icon icon="lucide:check-square" width="14" />
          <span>{{ totalTasksCount }} Total Tasks</span>
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button
          class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-600 rounded-lg transition-colors cursor-pointer"
          @click="scroll(-200)"
          title="Scroll Left"
        >
          <Icon icon="lucide:chevron-left" width="18" />
        </button>
        <button
          class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-600 rounded-lg transition-colors cursor-pointer"
          @click="scroll(200)"
          title="Scroll Right"
        >
          <Icon icon="lucide:chevron-right" width="18" />
        </button>
      </div>
    </div>

    <!-- Kanban Board -->
    <div class="my-6 overflow-x-auto w-full pb-4" ref="scrollContainer">
      <div class="flex items-start gap-6 min-w-max">
        <div
          v-for="(taskGroup, index) in workflows"
          :key="index"
          class="bg-slate-50 border border-gray-200/80 rounded-2xl p-4 w-[340px] shrink-0"
        >
          <!-- Column Header -->
          <div class="flex items-center justify-between pb-3 border-b border-gray-200/60 mb-3">
            <div class="flex items-center gap-2">
              <span
                class="w-2.5 h-2.5 rounded-full"
                :class="getStatusDotClass(taskGroup.status)"
              ></span>
              <h3 class="font-bold text-sm text-gray-800">{{ taskGroup.status }}</h3>
            </div>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white border border-gray-200 text-gray-600">
              {{ filteredTask(taskGroup.status).length }}
            </span>
          </div>

          <!-- Cards List -->
          <div class="space-y-3">
            <div
              v-for="item in filteredTask(taskGroup.status)"
              :key="item.id"
              class="bg-white rounded-xl border border-gray-200/80 p-4 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5"
            >
              <div class="flex items-start justify-between gap-2">
                <Link
                  :href="'/dashboard/view-task/' + item.id"
                  class="font-bold text-sm text-gray-900 hover:text-blue-600 transition-colors flex items-center gap-2 line-clamp-2"
                >
                  <Icon v-if="item.mail_id" icon="lucide:mail" class="text-blue-500 shrink-0" width="16" title="Mail Assignment" />
                  <Icon v-else-if="item.file_id" icon="lucide:file-text" class="text-purple-500 shrink-0" width="16" title="Document File" />
                  <span>{{ item.title }}</span>
                </Link>
              </div>

              <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                {{ item.description || 'No description provided.' }}
              </p>

              <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 text-xs text-gray-400">
                <div class="flex items-center gap-2">
                  <span class="font-medium text-gray-600">
                    {{ item.assigned_by ? (item.assigned_by.first_name + ' ' + item.assigned_by.last_name) : 'Supervisor' }}
                  </span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="flex items-center gap-1">
                    <Icon icon="lucide:message-square" width="13" />
                    <span>{{ item.comments_count || 0 }}</span>
                  </div>
                  <div class="flex items-center gap-1 font-medium text-gray-500">
                    <Icon icon="lucide:clock" width="13" />
                    <span>{{ getDays(item.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Column State -->
            <div
              v-if="filteredTask(taskGroup.status).length === 0"
              class="p-6 text-center text-xs text-gray-400 bg-white/40 border border-dashed border-gray-200 rounded-xl"
            >
              No {{ taskGroup.status.toLowerCase() }} tasks
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, defineProps, computed } from "vue"
import moment from "moment"
import { usePage, Link } from "@inertiajs/vue3"

const props = defineProps(['task'])
const scrollContainer = ref(null)
const page = usePage()

const totalTasksCount = computed(() => {
  return Array.isArray(props.task) ? props.task.length : 0
})

const scroll = (offset) => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollLeft += offset
  }
}

const filteredTask = (status) => {
  if (!props.task || !Array.isArray(props.task)) return []
  return props.task.filter(
    (item) => (item.status || '').toLowerCase() === status.toLowerCase()
  )
}

const getDays = (date) => {
  if (!date) return ''
  return moment(date).fromNow()
}

const getStatusDotClass = (status) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-emerald-500'
    case 'in progress':
      return 'bg-blue-500'
    case 'under review':
      return 'bg-purple-500'
    case 'uncompleted':
      return 'bg-red-500'
    case 'pending':
    default:
      return 'bg-amber-500'
  }
}

const workflows = ref([
  { status: 'Pending' },
  { status: 'In Progress' },
  { status: 'Under Review' },
  { status: 'Completed' },
  { status: 'Uncompleted' }
])
</script>
