<template>
  <main class="w-full px-4 sm:px-6 py-6 space-y-6">
    <!-- Welcome Banner & Quick Stats -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <!-- Decorative background blur -->
      <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold uppercase tracking-wider mb-3 border border-blue-400/20">
            <Icon icon="lucide:sparkles" width="14" />
            <span>Performance Management Hub</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Welcome, {{ user?.first_name || 'Staff' }}!
          </h1>
          <p class="text-slate-300 text-sm mt-1 max-w-xl leading-relaxed">
            Monitor organizational performance indicators, review staff accountability metrics, and track attendance benchmarks.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            href="/dashboard/pms-current-user-profile"
            class="inline-flex items-center gap-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm px-5 py-3 rounded-2xl shadow-lg shadow-blue-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]"
          >
            <Icon icon="lucide:user-check" width="18" />
            <span>My Personal Scorecard</span>
          </Link>
        </div>
      </div>

      <!-- Quick Executive Metrics (if supervisor / admin) -->
      <div v-if="!isRegularUserOnly" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-white/10">
        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Supervised Staff</p>
          <p class="text-2xl font-black text-white mt-1">{{ departmentUsers.length }}</p>
        </div>
        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Active Search Matches</p>
          <p class="text-2xl font-black text-blue-300 mt-1">{{ filteredUsers.length }}</p>
        </div>
        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Department Scope</p>
          <p class="text-sm font-bold text-slate-200 mt-2 truncate">{{ currentDepartmentName }}</p>
        </div>
        <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Active Role</p>
          <p class="text-sm font-bold text-emerald-400 mt-2 capitalize truncate">{{ userRolesDisplay }}</p>
        </div>
      </div>
    </div>

    <!-- Regular User Self-Service View -->
    <div v-if="isRegularUserOnly" class="bg-white rounded-3xl p-8 border border-gray-200 shadow-sm text-center">
      <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <Icon icon="lucide:award" width="32" />
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Your Performance Scorecard is Ready</h3>
      <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
        View your evaluated Key Result Areas, completed tasks, turnaround time, leave balances, and logged attendance sessions.
      </p>
      <Link
        href="/dashboard/pms-current-user-profile"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-6 py-3 rounded-xl shadow-sm transition-all"
      >
        <Icon icon="lucide:gauge" width="18" />
        <span>Open My Scorecard</span>
      </Link>
    </div>

    <!-- Supervisor & Management Staff Directory -->
    <div v-else class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
      <!-- Filter Bar -->
      <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
          <Icon icon="lucide:search" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search staff by name, email, or role..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-800 placeholder-gray-400"
          />
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Department Selector (for Admins / Execs) -->
          <div v-if="isElevatedAdmin" class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dept:</span>
            <select
              v-model="department"
              @change="changeDepartment"
              class="bg-slate-50 border border-gray-200 text-sm font-semibold text-gray-800 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">
                {{ dept.title || dept.name }}
              </option>
            </select>
          </div>

          <!-- Role Filter -->
          <select
            v-model="roleFilter"
            class="bg-slate-50 border border-gray-200 text-sm font-semibold text-gray-800 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Cadres / Roles</option>
            <option value="director">Directors</option>
            <option value="deputy director">Deputy Directors</option>
            <option value="assistant director">Assistant Directors</option>
            <option value="regular user">Staff / Officers</option>
          </select>

          <!-- Generate Report Button -->
          <button
            @click="generateDepartmentReport"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-700 bg-blue-50/80 hover:bg-blue-100 border border-blue-200 rounded-xl shadow-sm transition-all"
            title="Download Department Performance Summary PDF"
          >
            <Icon icon="lucide:file-text" width="15" />
            <span>Generate Report</span>
          </button>
        </div>
      </div>

      <!-- Table Section -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
          <thead class="bg-slate-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
            <tr>
              <th class="py-4 px-6 w-12 text-center">#</th>
              <th class="py-4 px-6">Personnel</th>
              <th class="py-4 px-6">Cadre / Role</th>
              <th class="py-4 px-6">Branch / Unit</th>
              <th class="py-4 px-6 text-right">Performance Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr
              v-for="(staff, index) in paginatedUsers"
              :key="staff.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <td class="py-4 px-6 text-center text-xs font-semibold text-gray-400">
                {{ (currentPage - 1) * itemsPerPage + index + 1 }}
              </td>

              <!-- Staff Info -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0 uppercase">
                    {{ (staff.first_name?.[0] || '') + (staff.last_name?.[0] || '') }}
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 leading-tight">
                      {{ staff.first_name }} {{ staff.last_name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ staff.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Cadre / Role -->
              <td class="py-4 px-6">
                <span
                  :class="getRoleBadgeClass(staff.roles?.[0]?.name)"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide"
                >
                  <Icon icon="lucide:shield" width="12" />
                  {{ staff.roles?.[0]?.name || 'Officer' }}
                </span>
              </td>

              <!-- Branch / Unit -->
              <td class="py-4 px-6 text-sm text-gray-600">
                <p class="font-medium text-gray-800">
                  {{ staff.unit?.title || staff.unit?.name || staff.division?.title || staff.division?.name || 'General Branch' }}
                </p>
                <p class="text-xs text-gray-400">
                  {{ staff.department?.title || staff.department?.name || 'Assigned' }}
                </p>
              </td>

              <!-- Action Button -->
              <td class="py-4 px-6 text-right">
                <Link
                  :href="'/dashboard/pms-user-profile/' + staff.id"
                  class="inline-flex items-center gap-1.5 bg-white hover:bg-blue-50 text-blue-700 border border-blue-200 hover:border-blue-300 font-semibold text-xs px-3.5 py-2 rounded-xl shadow-sm transition-all hover:shadow"
                >
                  <Icon icon="lucide:bar-chart-3" width="15" />
                  <span>View Scorecard</span>
                </Link>
              </td>
            </tr>

            <!-- Empty Filter State -->
            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="py-12 text-center">
                <div class="w-12 h-12 bg-slate-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
                  <Icon icon="lucide:users-round" width="24" />
                </div>
                <p class="text-base font-semibold text-gray-700">No personnel found</p>
                <p class="text-xs text-gray-400 mt-1">No staff members match the current filter or search criteria.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="filteredUsers.length > itemsPerPage" class="p-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500 font-medium">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} of {{ filteredUsers.length }} personnel
        </p>
        <div class="flex items-center gap-2">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1.5 text-xs font-semibold border rounded-lg border-gray-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors"
          >
            Previous
          </button>
          <span class="text-xs font-bold text-gray-700 px-2">Page {{ currentPage }} of {{ totalPages }}</span>
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1.5 text-xs font-semibold border rounded-lg border-gray-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import axios from "axios"
import { ref, watch, computed, defineProps } from "vue"
import { Link, usePage } from "@inertiajs/vue3"

const props = defineProps(['users', 'departments', 'roles'])

const page = usePage()
const user = computed(() => page.props.user)
const user_roles = computed(() => page.props.user_roles || [])

const department = ref(null)
const departmentUsers = ref([])
const searchQuery = ref("")
const roleFilter = ref("")
const currentPage = ref(1)
const itemsPerPage = 10

const isElevatedAdmin = computed(() => {
  return user_roles.value.some(r => ['super admin', 'super user', 'admin'].includes(r?.toLowerCase()))
})

const isRegularUserOnly = computed(() => {
  if (user_roles.value.length === 0) return true
  return user_roles.value.every(r => r?.toLowerCase() === 'regular user')
})

const userRolesDisplay = computed(() => {
  return user_roles.value.join(', ') || 'Staff'
})

const currentDepartmentName = computed(() => {
  const currentDept = props.departments?.find(d => d.id === department.value)
  return currentDept ? (currentDept.title || currentDept.name) : 'All Departments'
})

const changeDepartment = () => {
  if (!department.value) return
  axios.get('/dashboard/get-department-users', { params: { id: department.value } }).then((response) => {
    if (response.data) {
      departmentUsers.value = response.data
      currentPage.value = 1
    }
  }).catch(err => {
    console.error("Error loading department users", err)
  })
}

const filteredUsers = computed(() => {
  return departmentUsers.value.filter(u => {
    // Role filter
    if (roleFilter.value) {
      const matchRole = u.roles?.some(r => r.name?.toLowerCase().includes(roleFilter.value.toLowerCase()))
      if (!matchRole) return false
    }

    // Search query
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim()
      const fullName = `${u.first_name || ''} ${u.last_name || ''}`.toLowerCase()
      const email = (u.email || '').toLowerCase()
      const roleName = (u.roles?.[0]?.name || '').toLowerCase()
      return fullName.includes(query) || email.includes(query) || roleName.includes(query)
    }

    return true
  })
})

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage) || 1
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredUsers.value.slice(start, start + itemsPerPage)
})

const getRoleBadgeClass = (roleName) => {
  const name = roleName?.toLowerCase() || ''
  if (name.includes('director')) {
    return 'bg-purple-50 text-purple-700 border border-purple-100'
  }
  if (name.includes('admin')) {
    return 'bg-amber-50 text-amber-700 border border-amber-100'
  }
  return 'bg-blue-50 text-blue-700 border border-blue-100'
}

watch(() => props.departments, () => {
  if (props.departments && props.departments.length > 0 && !department.value) {
    department.value = props.departments[0].id
  }
}, { immediate: true })

watch(() => props.users, () => {
  if (props.users) {
    departmentUsers.value = props.users
  }
}, { immediate: true })

const generateDepartmentReport = () => {
  const deptId = department.value || ''
  window.open(`/dashboard/pms/report/department/${deptId}`, '_blank')
}
</script>
