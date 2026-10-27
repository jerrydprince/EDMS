<template>
  <main class="w-full px-4 sm:px-6 py-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-200">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1">
          <Icon icon="lucide:settings-2" width="16" />
          <span>Performance Management System</span>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">KRA & KPI Configuration</h1>
        <p class="text-gray-500 text-sm mt-1 max-w-2xl">
          Define Key Result Areas (KRAs) and measurable Key Performance Indicators (KPIs) tailored for each organizational department.
        </p>
      </div>

      <!-- Action Button -->
      <div class="flex items-center gap-3">
        <button
          v-if="can('pms create') || can('pms update') || hasRole('super admin')"
          @click="openAddKra"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all shadow-blue-500/20 active:scale-95"
        >
          <Icon icon="lucide:plus-circle" width="18" />
          <span>Add New KRA</span>
        </button>
      </div>
    </div>

    <!-- Department Selector & Summary Bar -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="md:col-span-2 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-center">
        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
          <Icon icon="lucide:building-2" width="14" />
          <span>Target Department</span>
        </label>
        <select
          v-model="selectedDeptId"
          @change="fetchKRAs"
          class="w-full bg-slate-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">
            {{ dept.title || dept.name }}
          </option>
        </select>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
          <Icon icon="lucide:target" width="24" />
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Active KRAs</p>
          <h2 class="text-2xl font-bold text-gray-900 mt-0.5">{{ krasList.length }}</h2>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <Icon icon="lucide:gauge" width="24" />
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Total Indicators</p>
          <h2 class="text-2xl font-bold text-gray-900 mt-0.5">{{ totalKPIsCount }}</h2>
        </div>
      </div>
    </div>

    <!-- KRAs Accordion List -->
    <div class="mt-8 space-y-5" v-if="loading">
      <div class="bg-white rounded-2xl p-8 border border-gray-200 text-center animate-pulse">
        <div class="h-6 bg-slate-200 rounded w-1/4 mx-auto mb-4"></div>
        <div class="h-4 bg-slate-100 rounded w-1/2 mx-auto"></div>
      </div>
    </div>

    <div class="mt-8 space-y-5" v-else-if="krasList.length > 0">
      <div
        v-for="(kraItem, index) in krasList"
        :key="kraItem.id"
        class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-200 hover:border-gray-300"
      >
        <!-- Card Header -->
        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
          <div class="flex items-start gap-4 flex-1">
            <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-blue-700 flex items-center justify-center shrink-0 mt-0.5 font-bold text-sm">
              #{{ index + 1 }}
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-3 flex-wrap">
                <h3 class="text-lg font-bold text-gray-900">{{ kraItem.name }}</h3>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                  <Icon icon="lucide:check-circle-2" width="12" />
                  {{ kraItem.kpi?.length || 0 }} KPIs
                </span>
                <span class="text-xs text-gray-400">
                  Added {{ formatDate(kraItem.created_at) }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-1 max-w-3xl">
                {{ kraItem.description || 'No description provided for this Key Result Area.' }}
              </p>
            </div>
          </div>

          <!-- Header Controls -->
          <div class="flex items-center gap-2 self-end md:self-center shrink-0">
            <button
              v-if="can('pms update') || hasRole('super admin')"
              @click="startEditKRA(kraItem)"
              class="p-2 text-gray-500 hover:text-blue-600 hover:bg-white rounded-lg border border-transparent hover:border-gray-200 transition-colors"
              title="Edit KRA"
            >
              <Icon icon="lucide:edit-3" width="18" />
            </button>
            <button
              v-if="can('pms delete') || hasRole('super admin')"
              @click="confirmDeleteKRA(kraItem)"
              class="p-2 text-gray-500 hover:text-red-600 hover:bg-white rounded-lg border border-transparent hover:border-gray-200 transition-colors"
              title="Delete KRA"
            >
              <Icon icon="lucide:trash-2" width="18" />
            </button>
            <button
              @click="toggleAccordion(kraItem.id)"
              class="p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg border border-transparent hover:border-gray-200 transition-all ml-1"
            >
              <Icon
                icon="lucide:chevron-down"
                width="20"
                class="transition-transform duration-200"
                :class="{ 'rotate-180': openKRAIds[kraItem.id] }"
              />
            </button>
          </div>
        </div>

        <!-- Inline Edit Section (if editing) -->
        <div v-if="editingKraId === kraItem.id" class="p-5 bg-amber-50/50 border-t border-b border-amber-100">
          <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-3">Edit Key Result Area Details</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-700">KRA Name</label>
              <input
                v-model="editKraForm.name"
                type="text"
                class="w-full mt-1 bg-white border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="text-xs font-medium text-gray-700">Description</label>
              <input
                v-model="editKraForm.description"
                type="text"
                class="w-full mt-1 bg-white border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 mt-4">
            <button
              @click="editingKraId = null"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button
              @click="saveEditKRA(kraItem)"
              class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all"
            >
              Save Changes
            </button>
          </div>
        </div>

        <!-- Expanded KPI Table -->
        <div v-show="openKRAIds[kraItem.id]" class="p-5 border-t border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
              <Icon icon="lucide:list-checks" width="14" />
              <span>Assigned Performance Indicators</span>
            </h4>
            <button
              v-if="can('pms create') || can('pms update') || hasRole('super admin')"
              @click="openAddKPI(kraItem)"
              class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100/70 px-3 py-1.5 rounded-lg transition-colors"
            >
              <Icon icon="lucide:plus" width="14" />
              <span>Add KPI</span>
            </button>
          </div>

          <div v-if="kraItem.kpi && kraItem.kpi.length > 0" class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-left text-sm text-gray-600">
              <thead class="bg-slate-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                <tr>
                  <th class="py-3 px-4">Indicator / Description</th>
                  <th class="py-3 px-4">Metric Model</th>
                  <th class="py-3 px-4">Scale / Target</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="kpiItem in kraItem.kpi" :key="kpiItem.id" class="hover:bg-slate-50/60 transition-colors">
                  <td class="py-3.5 px-4 font-medium text-gray-900">
                    <div class="capitalize text-sm font-semibold">{{ kpiItem.key }}</div>
                    <div v-if="kpiItem.description" class="text-xs text-gray-400 font-normal mt-0.5 max-w-xl">
                      {{ kpiItem.description }}
                    </div>
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      :class="getStringBadgeClass(kpiItem.string)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wide"
                    >
                      <Icon :icon="getStringIcon(kpiItem.string)" width="13" />
                      {{ getStringLabel(kpiItem.string) }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-gray-700">
                    <span v-if="kpiItem.string === 'point'">{{ kpiItem.value }} Points</span>
                    <span v-else-if="kpiItem.string === 'percentage'">100% Target</span>
                    <span v-else>Turnaround Time</span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="inline-flex items-center gap-2">
                      <button
                        v-if="can('pms update') || hasRole('super admin')"
                        @click="openEditKPI(kpiItem, kraItem)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                        title="Edit KPI"
                      >
                        <Icon icon="lucide:edit-2" width="16" />
                      </button>
                      <button
                        v-if="can('pms delete') || hasRole('super admin')"
                        @click="confirmDeleteKPI(kpiItem)"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                        title="Delete KPI"
                      >
                        <Icon icon="lucide:trash-2" width="16" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty KPI State -->
          <div v-else class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-gray-200">
            <Icon icon="lucide:clipboard-list" width="28" class="text-gray-400 mx-auto mb-2" />
            <p class="text-sm font-medium text-gray-700">No KPIs configured for this KRA yet</p>
            <p class="text-xs text-gray-400 mt-0.5">Add quantifiable indicators to measure employee performance.</p>
            <button
              @click="openAddKPI(kraItem)"
              class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:underline"
            >
              <Icon icon="lucide:plus" width="14" />
              <span>Create first KPI</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty KRAs State -->
    <div v-else class="mt-8 bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
      <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <Icon icon="lucide:target" width="32" />
      </div>
      <h3 class="text-lg font-bold text-gray-900 mb-1">No KRAs Configured for Selected Department</h3>
      <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
        Set up Key Result Areas to establish objective scorecards and evaluation benchmarks for staff in this department.
      </p>
      <button
        @click="openAddKra"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-5 py-2.5 rounded-xl shadow-sm transition-all"
      >
        <Icon icon="lucide:plus-circle" width="18" />
        <span>Configure First KRA</span>
      </button>
    </div>

    <!-- Modals -->
    <AddKRA v-model="add_kra" :department_id="selectedDeptId" @refresh="fetchKRAs" />
    <AddKPI v-model="add_kpi" :kraId="activeKra?.id" @refresh="fetchKRAs" />
    <EditKPI v-model="edit_kpi" :kpi="selectedKpi" :kraId="activeKra?.id" @refresh="fetchKRAs" />
    <DeleteKRA v-model="deletekra_modal" :kra_id="kraToDelete?.id" @refresh="fetchKRAs" />
    <DeleteKPI v-model="deletekpi_modal" :kpi_id="kpiToDelete?.id" @refresh="fetchKRAs" />
  </main>
</template>

<script setup>
import { ref, onMounted, defineProps, computed, reactive } from "vue"
import { usePage } from "@inertiajs/vue3"
import axios from "axios"
import moment from "moment"
import { useToast } from "primevue/usetoast"
import { usePermissions } from "@/composables/usePermissions"
import AddKPI from "../../components/Modals/PMS/AddKPI.vue"
import EditKPI from "../../components/Modals/PMS/EditKPI.vue"
import AddKRA from "../../components/Modals/PMS/AddKRA.vue"
import DeleteKRA from "../../components/Modals/PMS/DeleteKRA.vue"
import DeleteKPI from "../../components/Modals/PMS/DeleteKPI.vue"

const props = defineProps(['departments'])
const page = usePage()
const toast = useToast()
const { can, hasRole } = usePermissions()

const selectedDeptId = ref(null)
const krasList = ref([])
const loading = ref(false)

const openKRAIds = reactive({})
const editingKraId = ref(null)
const editKraForm = reactive({ name: '', description: '' })

// Modal control states
const add_kra = ref(false)
const add_kpi = ref(false)
const edit_kpi = ref(false)
const deletekra_modal = ref(false)
const deletekpi_modal = ref(false)

const activeKra = ref(null)
const selectedKpi = ref({})
const kraToDelete = ref(null)
const kpiToDelete = ref(null)

const user_roles = computed(() => page.props.user_roles || [])

const totalKPIsCount = computed(() => {
  return krasList.value.reduce((total, kra) => {
    return total + (kra.kpi?.length || 0)
  }, 0)
})

const fetchKRAs = async () => {
  if (!selectedDeptId.value && props.departments && props.departments.length > 0) {
    selectedDeptId.value = props.departments[0].id
  }

  if (!selectedDeptId.value) return

  loading.value = true
  try {
    const response = await axios.get('/dashboard/get-kra', {
      params: { id: selectedDeptId.value }
    })
    
    // Response can be an array of KRAs or a single KRA object (for fallback)
    if (Array.isArray(response.data)) {
      krasList.value = response.data
    } else if (response.data && typeof response.data === 'object') {
      krasList.value = [response.data]
    } else {
      krasList.value = []
    }

    // Default expand all KRAs
    krasList.value.forEach(kra => {
      if (openKRAIds[kra.id] === undefined) {
        openKRAIds[kra.id] = true
      }
    })
  } catch (err) {
    console.error('Error fetching KRAs:', err)
  } finally {
    loading.value = false
  }
}

const toggleAccordion = (kraId) => {
  openKRAIds[kraId] = !openKRAIds[kraId]
}

const openAddKra = () => {
  add_kra.value = true
}

const startEditKRA = (kra) => {
  editingKraId.value = kra.id
  editKraForm.name = kra.name
  editKraForm.description = kra.description || ''
}

const saveEditKRA = async (kra) => {
  if (!editKraForm.name.trim()) return

  try {
    await axios.post('/dashboard/update-kra', {
      id: kra.id,
      name: editKraForm.name,
      description: editKraForm.description
    })

    kra.name = editKraForm.name
    kra.description = editKraForm.description
    editingKraId.value = null

    toast.add({
      severity: "success",
      summary: "KRA Updated",
      detail: "Key Result Area has been updated successfully.",
      life: 3000
    })
  } catch (err) {
    console.error('Failed to update KRA', err)
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to update KRA. Please try again.",
      life: 3000
    })
  }
}

const confirmDeleteKRA = (kra) => {
  kraToDelete.value = kra
  deletekra_modal.value = true
}

const openAddKPI = (kra) => {
  activeKra.value = kra
  add_kpi.value = true
}

const openEditKPI = (kpi, kra) => {
  activeKra.value = kra
  selectedKpi.value = kpi
  edit_kpi.value = true
}

const confirmDeleteKPI = (kpi) => {
  kpiToDelete.value = kpi
  deletekpi_modal.value = true
}

const formatDate = (date) => {
  if (!date) return ''
  return moment(date).format('MMM D, YYYY')
}

const getStringBadgeClass = (str) => {
  switch (str?.toLowerCase()) {
    case 'point':
      return 'bg-purple-50 text-purple-700 border border-purple-100'
    case 'percentage':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-100'
    case 'time':
    default:
      return 'bg-blue-50 text-blue-700 border border-blue-100'
  }
}

const getStringIcon = (str) => {
  switch (str?.toLowerCase()) {
    case 'point':
      return 'lucide:star'
    case 'percentage':
      return 'lucide:percent'
    case 'time':
    default:
      return 'lucide:clock'
  }
}

const getStringLabel = (str) => {
  switch (str?.toLowerCase()) {
    case 'point':
      return 'Point Rating'
    case 'percentage':
      return 'Percentage'
    case 'time':
    default:
      return 'Timeliness'
  }
}

onMounted(() => {
  if (props.departments && props.departments.length > 0) {
    selectedDeptId.value = props.departments[0].id
  }
  fetchKRAs()
})
</script>
