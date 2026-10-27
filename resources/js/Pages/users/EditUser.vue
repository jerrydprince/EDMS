<template>
  <main class="min-h-screen bg-[#F9FAFB] pb-16">
    <!-- Top Action Bar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-20 shadow-sm flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Link href="/dashboard/manage-users" class="border border-gray-300 rounded-lg p-2.5 hover:bg-gray-50 transition text-gray-700">
          <Icon icon="formkit:arrowleft" width="22" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Edit Civil Service Officer Profile</h1>
          <p class="text-xs text-gray-500">Update officer hierarchy placement, functional desk, cadre grade level, and active module permissions</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <Link href="/dashboard/manage-users" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
          Back to Users
        </Link>
        <button
          type="button"
          class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!isFormReady || form.processing"
          @click="submitForm">
          <Icon v-if="form.processing" icon="line-md:loading-loop" width="18" />
          <Icon v-else icon="fluent:save-24-regular" width="18" />
          <span>Save Changes</span>
        </button>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
      
      <!-- Section 1: Choose Access Level (Primary System Roles) -->
      <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
          <div>
            <h2 class="text-lg font-bold text-gray-900">1. Primary Access Level & Roles</h2>
            <p class="text-xs text-gray-500">Assign system authority clearance. Selecting roles will also activate standard baseline permissions.</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-200">
            {{ form.role.length }} Role(s) Assigned
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 mt-5">
          <div
            v-for="(item, index) in props.all_roles"
            :key="index"
            class="flex items-start border p-3.5 gap-3 rounded-lg cursor-pointer transition-all hover:border-blue-400"
            :class="form.role.includes(item.name) ? 'border-blue-600 bg-blue-50/50 shadow-sm' : 'border-gray-200 bg-white'"
            @click="toggleRole(item.name)">
            <input
              type="checkbox"
              :checked="form.role.includes(item.name)"
              class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
              @click.stop="toggleRole(item.name)" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-900 leading-snug">{{ capitalize(item.name) }}</p>
              <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">{{ item.description || 'System access role definition.' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 2: Officer Identity & Organizational Placement -->
      <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="pb-4 border-b border-gray-100">
          <h2 class="text-lg font-bold text-gray-900">2. Officer Identity & Organizational Placement</h2>
          <p class="text-xs text-gray-500">Specify personnel particulars and hierarchy placement across Department, Division, and Unit.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
          <!-- Left Column: Personal Data & Avatar -->
          <div class="lg:col-span-7 space-y-5">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input
                  type="text"
                  v-model="form.first_name"
                  placeholder="e.g. Aminu"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.first_name">{{ form.errors.first_name }}</span>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                <input
                  type="text"
                  v-model="form.last_name"
                  placeholder="e.g. Ibrahim"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.last_name">{{ form.errors.last_name }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Official Email Address <span class="text-red-500">*</span></label>
                <input
                  type="email"
                  v-model="form.email"
                  @blur="checkMailExist"
                  placeholder="aminu.ibrahim@treasury.gov.ng"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                <span class="text-red-500 text-xs mt-1 block" v-if="email_msg">{{ email_msg }}</span>
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.email">{{ form.errors.email }}</span>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input
                  type="tel"
                  v-model="form.phone"
                  placeholder="08012345678"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
                <span class="text-red-500 text-xs mt-1 block" v-if="phone_msg">{{ phone_msg }}</span>
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.phone">{{ form.errors.phone }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                <select
                  v-model="form.gender"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                  <option value="" disabled>Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="none">Prefer not to say</option>
                </select>
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.gender">{{ form.errors.gender }}</span>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Civil Service Grade Level <span class="text-red-500">*</span></label>
                <select
                  v-model="form.grade_level"
                  class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                  <option value="" disabled>Select Grade Level</option>
                  <option value="0">Grade Level 0 (Intern / Entry)</option>
                  <option v-for="gl in 20" :key="gl" :value="gl">
                    Grade Level {{ gl }} {{ gl >= 17 ? '(Executive Management)' : gl >= 14 ? '(Senior Directorate)' : gl >= 8 ? '(Officer Cadre)' : '(Junior Cadre)' }}
                  </option>
                </select>
                <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.grade_level">{{ form.errors.grade_level }}</span>
              </div>
            </div>

            <!-- Profile Image Upload Box -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Official Passport Photo / Avatar</label>
              <div class="flex items-center gap-5 p-3.5 border border-dashed border-gray-300 rounded-lg hover:border-blue-400 bg-gray-50/50 transition">
                <div class="w-16 h-16 rounded-full bg-gray-200 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden flex-shrink-0">
                  <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                  <img v-else-if="props.user?.profile_image" :src="'/' + props.user.profile_image" class="w-full h-full object-cover" />
                  <Icon v-else icon="fluent:person-24-filled" width="32" class="text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-3">
                    <label class="cursor-pointer px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-xs">
                      Update Photo
                      <input type="file" accept="image/*" class="hidden" @change="handleFileChange" />
                    </label>
                    <span class="text-xs text-gray-400">JPG, PNG up to 2MB</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Clear passport photo for digital vouchers and workflow sign-off cards.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Dynamic Hierarchy Placement & Functional Designation -->
          <div class="lg:col-span-5 bg-gray-50/70 p-5 rounded-xl border border-gray-200/80 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-200">
              <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Hierarchy & Placement Flow</h3>
              <span class="text-[11px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded font-medium">Dynamic Resolving</span>
            </div>

            <!-- Department -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Department</label>
              <select
                v-model="form.department"
                @change="handleDepartmentChange"
                class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">Select Department (or Global Placement)</option>
                <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
              </select>
              <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.department">{{ form.errors.department }}</span>
            </div>

            <!-- Division -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Division (Optional)</label>
              <select
                v-model="form.division"
                @change="handleDivisionChange"
                :disabled="!availableDivisions.length"
                class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ availableDivisions.length ? 'Select Division' : 'No Divisions for Department' }}</option>
                <option v-for="div in availableDivisions" :key="div.id" :value="div.id">{{ div.title }}</option>
              </select>
            </div>

            <!-- Unit -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Unit (Optional)</label>
              <select
                v-model="form.unit"
                @change="handleUnitChange"
                :disabled="!availableUnits.length"
                class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ availableUnits.length ? 'Select Unit' : 'No Units for Division' }}</option>
                <option v-for="u in availableUnits" :key="u.id" :value="u.id">{{ u.title }}</option>
              </select>
            </div>

            <!-- Section / Desk -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Section / Desk (Optional)</label>
              <select
                v-model="form.section"
                @change="handleSectionChange"
                :disabled="!availableSections.length"
                class="w-full text-sm rounded-lg border border-gray-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ availableSections.length ? 'Select Section / Desk' : 'No Sections for Unit' }}</option>
                <option v-for="s in availableSections" :key="s.id" :value="s.id">{{ s.title }}</option>
              </select>
            </div>

            <!-- Functional Role Title / Designation -->
            <div class="pt-2 border-t border-gray-200">
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-gray-800">Functional Desk / Designation <span class="text-red-500">*</span></label>
                <span v-if="loadingDesignations" class="text-[11px] text-blue-600 flex items-center gap-1">
                  <Icon icon="line-md:loading-loop" width="12" /> Filtering...
                </span>
              </div>
              <select
                v-model="form.designation_id"
                @change="onDesignationSelected"
                class="w-full text-sm rounded-lg border border-blue-300 px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium text-gray-800 shadow-xs">
                <option value="">Select Functional Desk / Role Title</option>
                <option v-for="desig in dynamicDesignations" :key="desig.id" :value="desig.id">
                  {{ desig.title }} (GL {{ desig.grade_level || 'N/A' }}) - {{ desig.cadre || 'General' }}
                </option>
              </select>
              <p class="text-[11px] text-gray-500 mt-1">
                Determines executive stamp authorities, registry clearance scopes, and pre-fills recommended permissions.
              </p>
              <span class="text-red-500 text-xs mt-1 block" v-if="form.errors.designation_id">{{ form.errors.designation_id }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 3: Fine-Grained Permissions & Capability Matrix -->
      <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
          <div>
            <div class="flex items-center gap-3">
              <h2 class="text-lg font-bold text-gray-900">3. Granular Capabilities & Permission Matrix</h2>
              <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ totalEffectiveCount }} Total Active Capabilities
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">
              Permissions govern operational authorities, approval thresholds, stamps, voucher actions, and registry handling.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              class="px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
              @click="applyDesignationPresets">
              Auto-Fill Desk Presets
            </button>
            <button
              type="button"
              class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition"
              @click="resetToRoleBaseline">
              Align with Role Baseline
            </button>
            <button
              type="button"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
              @click="clearAllPermissions">
              Clear Overrides
            </button>
          </div>
        </div>

        <!-- Effective Authority Overview Card -->
        <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-gray-50 via-slate-50 to-blue-50/30 border border-gray-200">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Role Baseline summary -->
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-700">
                  <Icon icon="mdi:shield-check-outline" width="18" />
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-900">{{ inheritedCount }}</span>
                    <span class="text-xs text-gray-600 font-medium">Role Baseline</span>
                  </div>
                  <p class="text-[11px] text-gray-500">
                    From: <span class="font-semibold text-emerald-700">{{ form.role.length ? form.role.join(', ') : 'None' }}</span>
                  </p>
                </div>
              </div>

              <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

              <!-- Desk Overrides summary -->
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700">
                  <Icon icon="mdi:badge-account-outline" width="18" />
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-900">{{ directOverrideCount }}</span>
                    <span class="text-xs text-gray-600 font-medium">Desk Overrides</span>
                  </div>
                  <p class="text-[11px] text-gray-500">Custom capabilities specific to this desk</p>
                </div>
              </div>

              <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

              <!-- Total Effective summary -->
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 border border-purple-200 flex items-center justify-center text-purple-700">
                  <Icon icon="mdi:check-decagram-outline" width="18" />
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-900">{{ totalEffectiveCount }}</span>
                    <span class="text-xs text-gray-600 font-medium">Total Effective</span>
                  </div>
                  <p class="text-[11px] text-gray-500">Combined actionable permissions</p>
                </div>
              </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-3 text-[11px] text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-gray-200">
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Role Inherited
              </span>
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Desk Override
              </span>
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-gray-300"></span> Inactive
              </span>
            </div>
          </div>
        </div>

        <!-- Search / Filter Permissions -->
        <div class="mt-4 mb-6">
          <div class="relative max-w-md">
            <Icon icon="fluent:search-24-regular" class="absolute left-3 top-2.5 text-gray-400" width="18" />
            <input
              type="text"
              v-model="permSearch"
              placeholder="Search capabilities (e.g. stamp, cashbook, audit, dispatch)..."
              class="w-full text-xs rounded-lg border border-gray-300 pl-9 pr-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
          </div>
        </div>

        <!-- Grouped Permissions Grid / Accordion -->
        <div class="space-y-4">
          <div
            v-for="(perms, moduleName) in filteredPermissionGroups"
            :key="moduleName"
            class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50/40">
            <!-- Module Card Header -->
            <div class="px-4 py-3 bg-gray-100/70 border-b border-gray-200 flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <span class="font-bold text-sm text-gray-800">{{ moduleName }}</span>
                <span class="text-[11px] font-semibold text-gray-500 px-2 py-0.5 bg-white border border-gray-200 rounded-full">
                  {{ countModuleSelected(perms) }} / {{ perms.length }} Active
                </span>
              </div>
              <button
                type="button"
                class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition"
                @click="toggleSelectModule(perms)">
                Toggle Module Overrides
              </button>
            </div>

            <!-- Permission Items -->
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              <div
                v-for="p in perms"
                :key="p.id"
                class="flex items-start p-2.5 rounded-lg border transition-all cursor-pointer relative"
                :class="[
                  isRoleInherited(p.name)
                    ? 'border-emerald-300 bg-emerald-50/40 hover:bg-emerald-50/60'
                    : isDirectOverride(p.name)
                      ? 'border-blue-400 bg-blue-50/50 hover:bg-blue-50/70 shadow-xs'
                      : 'border-gray-200 bg-white hover:border-gray-300'
                ]"
                @click="togglePermission(p.name)">
                <input
                  type="checkbox"
                  :checked="isEffectiveActive(p.name)"
                  :disabled="isRoleInherited(p.name)"
                  class="mt-0.5 h-4 w-4 rounded border-gray-300 cursor-pointer"
                  :class="isRoleInherited(p.name) ? 'text-emerald-600 focus:ring-emerald-500' : 'text-blue-600 focus:ring-blue-500'"
                  @click.stop="togglePermission(p.name)" />
                <div class="ml-2.5 flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-1">
                    <p class="text-xs font-bold text-gray-900 leading-snug truncate">{{ p.name }}</p>
                    <span
                      v-if="isRoleInherited(p.name)"
                      class="shrink-0 px-1.5 py-0.2 text-[10px] font-semibold rounded bg-emerald-100 text-emerald-800 border border-emerald-200"
                      :title="'Granted via Role: ' + getInheritedRoleName(p.name)">
                      Role
                    </span>
                    <span
                      v-else-if="isDirectOverride(p.name)"
                      class="shrink-0 px-1.5 py-0.2 text-[10px] font-semibold rounded bg-blue-100 text-blue-800 border border-blue-200"
                      title="Direct desk capability override">
                      Override
                    </span>
                  </div>
                  <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed line-clamp-2">{{ p.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Bottom Submission Footer -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Ensure all mandatory civil service credentials and designations are verified before saving.</p>
        </div>
        <div class="flex items-center gap-3">
          <Link href="/dashboard/manage-users" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Cancel
          </Link>
          <button
            type="button"
            class="flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!isFormReady || form.processing"
            @click="submitForm">
            <Icon v-if="form.processing" icon="line-md:loading-loop" width="18" />
            <Icon v-else icon="fluent:save-24-regular" width="18" />
            <span>Update Officer Profile</span>
          </button>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const props = defineProps({
  officer: { type: Object, default: () => null },
  user: { type: Object, default: () => ({}) },
  all_roles: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  dpt: { type: Object, default: () => null },
  division: { type: Object, default: () => null },
  unit: { type: Object, default: () => null },
  section: { type: Object, default: () => null },
  officer_roles: { type: Array, default: () => null },
  user_roles: { type: Array, default: () => [] },
  designations: { type: Array, default: () => [] },
  user_permissions: { type: Array, default: () => [] },
  direct_permissions: { type: Array, default: () => [] },
  role_permissions: { type: Array, default: () => [] },
  all_permissions: { type: Object, default: () => ({}) },
});

const targetOfficer = computed(() => props.officer || props.user || {});
const targetRoles = computed(() => props.officer_roles || props.user_roles || []);

const toast = useToast();
const previewUrl = ref('');
const isEmailExist = ref(false);
const email_msg = ref(null);
const phone_msg = ref('');
const permSearch = ref('');
const loadingDesignations = ref(false);
const dynamicDesignations = ref(props.designations || []);

const initialOfficer = props.officer || props.user || {};
const initialRoles = props.officer_roles || props.user_roles || [];

const form = useForm({
  id: initialOfficer.id || '',
  first_name: initialOfficer.first_name || '',
  last_name: initialOfficer.last_name || '',
  email: initialOfficer.email || '',
  phone: initialOfficer.phone || '',
  gender: initialOfficer.gender || '',
  grade_level: initialOfficer.grade_level ?? '',
  department: initialOfficer.department_id || '',
  division: initialOfficer.division_id || '',
  unit: initialOfficer.unit_id || '',
  section: initialOfficer.section_id || '',
  designation_id: initialOfficer.designation_id || '',
  role: initialRoles ? [...initialRoles] : [],
  permissions: props.direct_permissions ? [...props.direct_permissions] : [],
  file: null,
  is_approved: 1,
});

// Computed Hierarchy Sub-branches
const availableDivisions = computed(() => {
  if (!form.department) return [];
  const dept = props.departments.find(d => d.id == form.department);
  return dept?.division || [];
});

const availableUnits = computed(() => {
  if (!form.division) return [];
  const div = availableDivisions.value.find(d => d.id == form.division);
  return div?.unit || [];
});

const availableSections = computed(() => {
  if (!form.unit) return [];
  const u = availableUnits.value.find(unit => unit.id == form.unit);
  return u?.sections || [];
});

// Dynamic Available Designations Fetcher
const fetchDesignations = async () => {
  loadingDesignations.value = true;
  try {
    const res = await axios.get('/available-designations', {
      params: {
        department_id: form.department || null,
        division_id: form.division || null,
        unit_id: form.unit || null,
        section_id: form.section || null,
      }
    });
    if (res.data?.data) {
      dynamicDesignations.value = res.data.data;
    } else if (Array.isArray(res.data)) {
      dynamicDesignations.value = res.data;
    }
  } catch (e) {
    console.error("Failed to load available designations:", e);
  } finally {
    loadingDesignations.value = false;
  }
};

const handleDepartmentChange = () => {
  form.division = '';
  form.unit = '';
  form.section = '';
  fetchDesignations();
};

const handleDivisionChange = () => {
  form.unit = '';
  form.section = '';
  fetchDesignations();
};

const handleUnitChange = () => {
  form.section = '';
  fetchDesignations();
};

const handleSectionChange = () => {
  fetchDesignations();
};

// When Designation is selected
const onDesignationSelected = () => {
  const selected = dynamicDesignations.value.find(d => d.id == form.designation_id);
  if (selected) {
    if (selected.grade_level && !form.grade_level) {
      form.grade_level = selected.grade_level;
    }
    applyDesignationPresets();
  }
};

// Role-Inherited Permissions Map (Permission Name -> Role Name)
const roleGrantedPermsMap = computed(() => {
  const map = new Map();
  const rolesList = props.all_roles || [];
  for (const r of rolesList) {
    if (form.role.includes(r.name) && Array.isArray(r.permissions)) {
      r.permissions.forEach(p => {
        const pName = typeof p === 'string' ? p : p.name;
        if (pName && !map.has(pName)) {
          map.set(pName, r.name);
        }
      });
    }
  }
  return map;
});

const isRoleInherited = (permName) => {
  return roleGrantedPermsMap.value.has(permName);
};

const getInheritedRoleName = (permName) => {
  return roleGrantedPermsMap.value.get(permName) || '';
};

const isDirectOverride = (permName) => {
  return form.permissions.includes(permName) && !isRoleInherited(permName);
};

const isEffectiveActive = (permName) => {
  return isRoleInherited(permName) || form.permissions.includes(permName);
};

const inheritedCount = computed(() => {
  return roleGrantedPermsMap.value.size;
});

const directOverrideCount = computed(() => {
  return form.permissions.filter(p => !isRoleInherited(p)).length;
});

const totalEffectiveCount = computed(() => {
  const set = new Set(roleGrantedPermsMap.value.keys());
  form.permissions.forEach(p => set.add(p));
  return set.size;
});

const resetToRoleBaseline = () => {
  form.permissions = [];
  toast.add({
    severity: "success",
    summary: "Aligned to Role Baseline",
    detail: "Direct desk overrides cleared. Officer permissions will strictly track assigned roles.",
    life: 3000,
  });
};

// Automatic Permission Presets based on Designation Title & Roles
const applyDesignationPresets = () => {
  const selected = dynamicDesignations.value.find(d => d.id == form.designation_id);
  const title = (selected?.title || '').toLowerCase();
  const permsToGrant = new Set(form.permissions);

  if (title.includes('checking')) {
    ['checking stamp', 'voucher update', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('audit')) {
    ['audit stamp', 'audit query raise', 'audit query respond', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('cashier')) {
    ['cashbook view', 'cashbook create', 'cashbook update', 'cashbook disburse', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('vote book') || title.includes('votebook')) {
    ['votebook view', 'votebook create', 'votebook update', 'votebook edit', 'voucher create', 'voucher vat generate', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('final accounts') || title.includes('ledger')) {
    ['ledger view', 'ledger create', 'ledger update', 'ledger post', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('advances')) {
    ['advances view', 'advances create', 'advances retire', 'workflow minute'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('central registry')) {
    ['admin registry view', 'registry view', 'registry create', 'registry update', 'registry dispatch', 'mails create', 'mails update', 'mails minute', 'mails dispatch'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('registry')) {
    ['registry view', 'registry create', 'registry update', 'registry dispatch', 'mails create', 'mails update', 'mails minute', 'mails dispatch'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  } else if (title.includes('director') || title.includes('hod')) {
    ['mails minute', 'mails dispatch', 'workflow minute', 'workflow approve', 'workflow return', 'payment approve', 'payment reject', 'voucher create', 'pms review', 'pms approve'].forEach(p => {
      if (!isRoleInherited(p)) permsToGrant.add(p);
    });
  }

  form.permissions = Array.from(permsToGrant);
  toast.add({
    severity: "info",
    summary: "Presets Configured",
    detail: "Recommended operational capabilities mapped for " + (selected?.title || "selected desk"),
    life: 2500,
  });
};

const clearAllPermissions = () => {
  form.permissions = [];
  toast.add({
    severity: "info",
    summary: "Overrides Cleared",
    detail: "All custom desk overrides removed.",
    life: 2000,
  });
};

// Filtered Permission Groups by Search Input
const filteredPermissionGroups = computed(() => {
  const groups = props.all_permissions || {};
  if (!permSearch.value.trim()) return groups;

  const query = permSearch.value.toLowerCase().trim();
  const filtered = {};

  for (const [moduleName, perms] of Object.entries(groups)) {
    const matchedPerms = perms.filter(p =>
      p.name.toLowerCase().includes(query) || (p.description && p.description.toLowerCase().includes(query))
    );
    if (matchedPerms.length > 0) {
      filtered[moduleName] = matchedPerms;
    }
  }

  return filtered;
});

const countModuleSelected = (perms) => {
  return perms.filter(p => isEffectiveActive(p.name)).length;
};

const toggleSelectModule = (perms) => {
  const uninheritedPerms = perms.filter(p => !isRoleInherited(p.name));
  if (!uninheritedPerms.length) {
    toast.add({
      severity: "info",
      summary: "Role Protected",
      detail: "All permissions in this module are already provided by assigned roles.",
      life: 3000,
    });
    return;
  }

  const allDirectGranted = uninheritedPerms.every(p => form.permissions.includes(p.name));
  const permSet = new Set(form.permissions);

  if (allDirectGranted) {
    uninheritedPerms.forEach(p => permSet.delete(p.name));
  } else {
    uninheritedPerms.forEach(p => permSet.add(p.name));
  }
  form.permissions = Array.from(permSet);
};

const togglePermission = (permName) => {
  if (isRoleInherited(permName)) {
    toast.add({
      severity: "info",
      summary: "Role Inherited",
      detail: `"${permName}" is active from the "${getInheritedRoleName(permName)}" role. Manage role baselines in Access Level.`,
      life: 3000,
    });
    return;
  }

  const index = form.permissions.indexOf(permName);
  if (index > -1) {
    form.permissions.splice(index, 1);
  } else {
    form.permissions.push(permName);
  }
};

const toggleRole = (roleName) => {
  const index = form.role.indexOf(roleName);
  if (index > -1) {
    form.role.splice(index, 1);
  } else {
    form.role.push(roleName);
  }
};

const capitalize = (value) => {
  if (!value) return '';
  return value.replace(/\b\w/g, char => char.toUpperCase());
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  form.file = file;
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    previewUrl.value = '';
  }
};

const checkMailExist = () => {
  if (!form.email || form.email === props.user?.email) return;
  axios.get('/auth-email-exist', { params: { email: form.email } }).then((response) => {
    if (response.data.error) {
      email_msg.value = response.data.error_msg;
      isEmailExist.value = true;
    } else {
      email_msg.value = null;
      isEmailExist.value = false;
    }
  });
};

const isFormReady = computed(() => {
  return Boolean(
    form.first_name &&
    form.last_name &&
    form.email &&
    form.grade_level !== '' &&
    form.gender &&
    form.role.length > 0 &&
    !isEmailExist.value
  );
});

const submitForm = () => {
  if (isEmailExist.value) {
    toast.add({ severity: "error", summary: "Validation Error", detail: "Email address already registered.", life: 3000 });
    return;
  }

  form.post('/dashboard/edit-user', {
    onSuccess: () => {
      previewUrl.value = '';
      toast.add({
        severity: "success",
        summary: "Profile Updated",
        detail: "User account and permissions successfully updated.",
        life: 3000,
      });
    },
    onError: (errors) => {
      toast.add({
        severity: "error",
        summary: "Update Failed",
        detail: Object.values(errors)[0] || "Please verify the form fields.",
        life: 4000,
      });
    }
  });
};

onMounted(() => {
  const target = targetOfficer.value;
  if (target && target.id) {
    form.id = target.id;
    form.first_name = target.first_name || '';
    form.last_name = target.last_name || '';
    form.email = target.email || '';
    form.grade_level = target.grade_level ?? '';
    form.gender = target.gender || '';
    form.phone = target.phone || '';
    form.department = target.department_id || '';
    form.division = target.division_id || '';
    form.unit = target.unit_id || '';
    form.section = target.section_id || '';
    form.designation_id = target.designation_id || '';
    form.role = targetRoles.value ? [...targetRoles.value] : [];
    form.permissions = props.direct_permissions ? [...props.direct_permissions] : (props.user_permissions ? [...props.user_permissions] : []);

    if (form.department && dynamicDesignations.value.length === 0) {
      fetchDesignations();
    }
  }
});
</script>

<style scoped>
/* Responsive styling */
</style>
