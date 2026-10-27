<template>
  <main class="p-7">
    <!-- Executive Welcome Deck (Phase 1) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 md:p-8 text-white shadow-xl border border-slate-700/50">
      <!-- Ambient Glow Behind -->
      <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-primary/25 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-indigo-600/20 blur-3xl"></div>

      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Left: Greetings & Context -->
        <div class="flex items-start md:items-center gap-5">
          <div class="p-4 bg-white/10 backdrop-blur-md text-blue-300 rounded-2xl shadow-inner border border-white/10 shrink-0">
            <Icon icon="lucide:workflow" width="36" />
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="text-xs font-semibold tracking-wider uppercase text-blue-300/90 flex items-center gap-1.5">
                <Icon icon="lucide:calendar" width="13" />
                {{ formattedCurrentDate }}
              </span>
              <span class="text-slate-500">•</span>
              <span v-if="user?.department?.title || userBranch?.department?.title" class="text-xs text-slate-300 font-medium flex items-center gap-1">
                <Icon icon="lucide:building-2" width="13" class="text-slate-400" />
                {{ user?.department?.title || userBranch?.department?.title }}
              </span>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
              <span>{{ greetingText }}, {{ user?.first_name || 'Officer' }}</span>
              <span class="inline-block text-xl">👋</span>
            </h1>

            <div class="flex flex-wrap items-center gap-3 mt-2.5">
              <div v-if="roles && roles.length > 0" class="flex items-center gap-1.5 bg-blue-500/20 text-blue-200 border border-blue-400/30 font-medium px-3 py-1 rounded-full text-xs backdrop-blur-sm shadow-sm">
                <Icon icon="mynaui:key" width="14"/>
                <span class="capitalize">{{ roles[0] }}</span>
              </div>
              <div v-if="user?.branchName || branchName" class="text-xs text-slate-300 bg-white/5 border border-white/10 px-3 py-1 rounded-full font-mono uppercase tracking-wider">
                Jurisdiction: {{ user?.branchName || branchName }}
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Telemetry & SLA Compliance Health -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 shrink-0">
          <!-- Operational Health Alert Badge -->
          <div 
            :class="slaStatusBadge.class" 
            class="flex items-center gap-3 px-4 py-3 rounded-2xl border backdrop-blur-md transition-all shadow-sm"
          >
            <div class="relative flex h-3 w-3 items-center justify-center">
              <span :class="slaStatusBadge.dotClass" class="absolute inline-flex h-full w-full rounded-full opacity-75"></span>
              <span class="relative inline-flex h-2 w-2 rounded-full bg-current"></span>
            </div>
            <div class="flex flex-col">
              <span class="text-[11px] uppercase tracking-wider font-semibold opacity-75">Operational Health</span>
              <span class="text-xs font-bold leading-tight flex items-center gap-1">
                <Icon :icon="slaStatusBadge.icon" width="14" />
                {{ slaStatusBadge.text }}
              </span>
            </div>
          </div>

          <!-- SLA Compliance Rate Radar Card -->
          <div class="flex items-center gap-3 bg-white/10 border border-white/15 backdrop-blur-md px-4 py-3 rounded-2xl shadow-sm">
            <div class="relative flex items-center justify-center">
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black shadow-inner border-2"
                :class="{
                  'border-emerald-400 text-emerald-300 bg-emerald-500/10': complianceRate >= 85,
                  'border-amber-400 text-amber-300 bg-amber-500/10': complianceRate >= 70 && complianceRate < 85,
                  'border-rose-400 text-rose-300 bg-rose-500/10': complianceRate < 70
                }"
              >
                {{ complianceRate }}%
              </div>
            </div>
            <div class="flex flex-col">
              <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-300">SLA Compliance</span>
              <span class="text-xs font-bold text-white">On-Time Clearance</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Interactive Stats Cards (Phase 1 Click-to-Filter) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mt-6" v-if="activeTaskStats">
      <!-- 1. Total Tasks -->
      <div 
        @click="handleKpiCardClick('total')"
        class="group relative bg-white p-5 rounded-2xl shadow-sm border transition-all duration-200 cursor-pointer overflow-hidden flex flex-col justify-between"
        :class="buttonSwitch === 1 ? 'ring-2 ring-primary border-primary shadow-md bg-blue-50/20' : 'border-gray-100 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5'"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase tracking-wider font-bold text-gray-500">Total Dockets</p>
          <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:scale-110 transition-transform">
            <Icon icon="lucide:layers" width="20" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <h3 class="text-3xl font-extrabold text-gray-800 tracking-tight">{{ activeTaskStats.total || 0 }}</h3>
          <span v-if="buttonSwitch === 1" class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Active
          </span>
          <span v-else class="text-[11px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
            Click to view all
          </span>
        </div>
      </div>

      <!-- 2. Pending / Action Needed -->
      <div 
        @click="handleKpiCardClick('pending')"
        class="group relative bg-white p-5 rounded-2xl shadow-sm border transition-all duration-200 cursor-pointer overflow-hidden flex flex-col justify-between"
        :class="buttonSwitch === 2 ? 'ring-2 ring-indigo-500 border-indigo-500 shadow-md bg-indigo-50/20' : 'border-gray-100 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5'"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase tracking-wider font-bold text-indigo-700">Awaiting Action</p>
          <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl group-hover:scale-110 transition-transform">
            <Icon icon="lucide:inbox" width="20" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <h3 class="text-3xl font-extrabold text-gray-800 tracking-tight">{{ activeTaskStats.pending || 0 }}</h3>
          <span v-if="buttonSwitch === 2" class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Filtering
          </span>
          <span v-else class="text-[11px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
            Click to filter
          </span>
        </div>
      </div>

      <!-- 3. Due Soon (<48h) -->
      <div 
        @click="handleKpiCardClick('due_soon')"
        class="group relative bg-white p-5 rounded-2xl shadow-sm border transition-all duration-200 cursor-pointer overflow-hidden flex flex-col justify-between"
        :class="buttonSwitch === 8 ? 'ring-2 ring-amber-500 border-amber-500 shadow-md bg-amber-50/20' : 'border-gray-100 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5'"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase tracking-wider font-bold text-amber-700">Due Soon (&lt;48h)</p>
          <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl group-hover:scale-110 transition-transform">
            <Icon icon="lucide:clock" width="20" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <h3 class="text-3xl font-extrabold text-amber-600 tracking-tight">{{ activeTaskStats.due_soon || 0 }}</h3>
          <span v-if="buttonSwitch === 8" class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Filtering
          </span>
          <span v-else class="text-[11px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
            Click to filter
          </span>
        </div>
      </div>

      <!-- 4. Overdue -->
      <div 
        @click="handleKpiCardClick('overdue')"
        class="group relative bg-white p-5 rounded-2xl shadow-sm border transition-all duration-200 cursor-pointer overflow-hidden flex flex-col justify-between"
        :class="buttonSwitch === 9 ? 'ring-2 ring-rose-500 border-rose-500 shadow-md bg-rose-50/20' : 'border-gray-100 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5'"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase tracking-wider font-bold text-rose-700">Overdue SLA</p>
          <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl group-hover:scale-110 transition-transform">
            <Icon icon="lucide:alert-circle" width="20" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <h3 class="text-3xl font-extrabold text-rose-600 tracking-tight">{{ activeTaskStats.overdue || 0 }}</h3>
          <span v-if="buttonSwitch === 9" class="text-[10px] font-bold text-rose-800 bg-rose-100 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Filtering
          </span>
          <span v-else class="text-[11px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
            Click to filter
          </span>
        </div>
      </div>

      <!-- 5. Completed -->
      <div 
        @click="handleKpiCardClick('completed')"
        class="group relative bg-white p-5 rounded-2xl shadow-sm border transition-all duration-200 cursor-pointer overflow-hidden flex flex-col justify-between"
        :class="buttonSwitch === 5 ? 'ring-2 ring-emerald-500 border-emerald-500 shadow-md bg-emerald-50/20' : 'border-gray-100 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5'"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs uppercase tracking-wider font-bold text-emerald-700">Completed</p>
          <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-110 transition-transform">
            <Icon icon="lucide:check-circle-2" width="20" />
          </div>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <h3 class="text-3xl font-extrabold text-emerald-600 tracking-tight">{{ activeTaskStats.completed || 0 }}</h3>
          <span v-if="buttonSwitch === 5" class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Filtering
          </span>
          <span v-else class="text-[11px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
            Click to filter
          </span>
        </div>
      </div>
    </div>
    <div class="bg-white mt-8 shadow-sm border border-gray-100 rounded-2xl p-5 md:p-8 w-full">
      
      <!-- Primary Navigation Deck & Search/Controls (Phase 2) -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-6 border-b border-gray-100">
        <!-- Segmented Assignment Queue Switcher -->
        <div class="inline-flex p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80 shadow-inner flex-wrap md:flex-nowrap gap-1">
          <!-- Tab 1: Incoming Tasks -->
          <button 
            @click="assignedByMeButton" 
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
            :class="assignmentButton == 1 ? 'bg-white text-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
            <Icon icon="lucide:inbox" width="17" :class="assignmentButton == 1 ? 'text-primary' : 'text-slate-400'" />
            <span>Incoming My Desk</span>
            <span v-if="assignedTaskStats?.total" class="text-[10px] font-mono px-2 py-0.5 rounded-full"
              :class="assignmentButton == 1 ? 'bg-primary text-white' : 'bg-slate-200 text-slate-700'">
              {{ assignedTaskStats.total }}
            </span>
          </button>
          
          <!-- Tab 2: Dispatched Tasks -->
          <button 
            v-if="!isRole('regular user') && !isRole('regular users')" 
            @click="assignedToMeButton" 
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
            :class="assignmentButton == 2 ? 'bg-white text-primary shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
            <Icon icon="lucide:send" width="17" :class="assignmentButton == 2 ? 'text-primary' : 'text-slate-400'" />
            <span>Delegated Tasks</span>
            <span v-if="createdTaskStats?.total" class="text-[10px] font-mono px-2 py-0.5 rounded-full"
              :class="assignmentButton == 2 ? 'bg-primary text-white' : 'bg-slate-200 text-slate-700'">
              {{ createdTaskStats.total }}
            </span>
          </button>

          <!-- Tab 3: Submissions & Review -->
          <button 
            v-if="!isRole('regular user') && !isRole('regular users')" 
            @click="submissionsButton" 
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
            :class="assignmentButton == 3 ? 'bg-emerald-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
            <Icon icon="lucide:file-check" width="17" :class="assignmentButton == 3 ? 'text-white' : 'text-emerald-600'" />
            <span>Submissions & Review</span>
            <span v-if="submissionTaskStats?.total" class="text-[10px] font-mono px-2 py-0.5 rounded-full font-bold"
              :class="assignmentButton == 3 ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 animate-pulse'">
              {{ submissionTaskStats.total }}
            </span>
          </button>

          <!-- Tab 4: Awaiting My Action -->
          <button 
            @click="assignmentButton = 4" 
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
            :class="assignmentButton == 4 ? 'bg-amber-500 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
            <Icon icon="lucide:hourglass" width="17" :class="assignmentButton == 4 ? 'text-white' : 'text-amber-500'" />
            <span>Awaiting My Action</span>
          </button>
        </div>
        
        <!-- View Toggle & Search -->
        <div class="flex items-center gap-3">
          <button 
            v-if="assignmentButton == 1 && (can('workflow create') || isRole('super admin') || isRole('super user'))" 
            @click="AssignModal = true" 
            class="flex items-center gap-2 text-white bg-primary px-3.5 py-2 rounded-xl hover:bg-blue-700 transition-colors shadow-sm text-xs sm:text-sm font-semibold shrink-0">
            <Icon icon="lucide:plus" width="18" />
            <span>{{ (isRole('regular user') || isRole('regular users')) ? 'New Application / Docket' : 'New Docket / Directive' }}</span>
          </button>
          
          <div class="flex items-center border p-2 rounded-xl gap-2 text-sm bg-gray-50 border-gray-200 focus-within:bg-white focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all w-48 md:w-60">
            <Icon icon="lucide:search" width="16" class="text-gray-400 shrink-0" />
            <input 
              type="text" 
              v-model="search" 
              @input="getSearchResult" 
              placeholder="Search Tasks..."
              class="border-0 bg-transparent outline-none text-gray-700 text-sm w-full placeholder:text-gray-400" 
            />
            <button v-if="search" @click="search = ''; getSearchResult()" class="text-gray-400 hover:text-gray-600 text-xs">✕</button>
          </div>

          <button 
            @click="exportScheduleModalOpen = true" 
            class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all border border-slate-200 shadow-2xs shrink-0 cursor-pointer"
            title="Executive Docket Briefing Schedule (Print / PDF / CSV)"
          >
            <Icon icon="lucide:printer" width="16" class="text-slate-500" />
            <span class="hidden md:inline">Schedule Sheet</span>
          </button>
          
          <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 shadow-inner shrink-0">
            <button 
              @click="viewMode = 'table'" 
              class="p-2 rounded-lg transition-all duration-200" 
              :class="viewMode === 'table' ? 'bg-white shadow-sm text-primary' : 'text-gray-500 hover:text-gray-700'"
              title="Table View">
              <Icon icon="lucide:list" width="18" />
            </button>
            <button 
              @click="viewMode = 'kanban'" 
              class="p-2 rounded-lg transition-all duration-200" 
              :class="viewMode === 'kanban' ? 'bg-white shadow-sm text-primary' : 'text-gray-500 hover:text-gray-700'"
              title="Kanban Board View">
              <Icon icon="lucide:layout-grid" width="18" />
            </button>
          </div>
        </div>
      </div>

      <!-- Semantic Consolidated Sub-Filter Deck (Phase 2) -->
      <div v-if="assignmentButton !== 4" class="space-y-3 pb-6 mb-4 border-b border-gray-100">
        <!-- 4 Primary Semantic Lifecycle Buckets -->
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="inline-flex p-1 bg-gray-100 rounded-2xl border border-gray-200/80 flex-wrap gap-1">
            <!-- 1. All Dockets -->
            <button 
              @click="setBucket('all')"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
              :class="activeBucket === 'all' ? 'bg-white text-primary shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900'">
              <span>All Dockets</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                :class="activeBucket === 'all' ? 'bg-primary/10 text-primary font-bold' : 'bg-gray-200 text-gray-600'">
                {{ bucketCounts.all }}
              </span>
            </button>

            <!-- 2. Active Processing -->
            <button 
              @click="setBucket('active')"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
              :class="activeBucket === 'active' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900'">
              <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
              <span>Active Processing</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                :class="activeBucket === 'active' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'bg-gray-200 text-gray-600'">
                {{ bucketCounts.active }}
              </span>
            </button>

            <!-- 3. Review & Clearance -->
            <button 
              @click="setBucket('review')"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
              :class="activeBucket === 'review' ? 'bg-white text-purple-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900'">
              <span class="w-2 h-2 rounded-full bg-purple-500"></span>
              <span>Review & Clearance</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                :class="activeBucket === 'review' ? 'bg-purple-100 text-purple-700 font-bold' : 'bg-gray-200 text-gray-600'">
                {{ bucketCounts.review }}
              </span>
            </button>

            <!-- 4. Time-Critical & Risks -->
            <button 
              @click="setBucket('critical')"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
              :class="activeBucket === 'critical' ? 'bg-white text-rose-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900'">
              <span class="w-2 h-2 rounded-full bg-rose-500"></span>
              <span>Time-Critical & Risks</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
                :class="activeBucket === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-rose-50 text-rose-600 border border-rose-200'">
                {{ bucketCounts.critical }}
              </span>
            </button>

            <!-- 5. Cleared & Closed -->
            <button 
              @click="setBucket('completed')"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer"
              :class="activeBucket === 'completed' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900'">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>Closed & Archived</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                :class="activeBucket === 'completed' ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-gray-200 text-gray-600'">
                {{ bucketCounts.completed }}
              </span>
            </button>
          </div>

          <!-- Quick Action & Contextual Chips -->
          <div class="flex items-center gap-2">
            <button 
              @click="hasDiscussionsOnly = !hasDiscussionsOnly"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 border cursor-pointer"
              :class="hasDiscussionsOnly 
                ? 'bg-blue-50 text-blue-700 border-blue-300 shadow-sm ring-1 ring-blue-400' 
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
              <Icon icon="lucide:message-square" width="14" :class="hasDiscussionsOnly ? 'text-blue-600' : 'text-gray-400'" />
              <span>With Discussions</span>
            </button>

            <button 
              v-if="buttonSwitch !== 1 || hasDiscussionsOnly" 
              @click="activeButton(''); hasDiscussionsOnly = false" 
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-medium text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors">
              <Icon icon="lucide:x" width="14" />
              <span>Reset Filters</span>
            </button>
          </div>
        </div>

        <!-- Granular Sub-Chips when an operational bucket is active -->
        <div v-if="activeBucket !== 'all' && activeBucket !== 'completed'" class="flex flex-wrap items-center gap-2 pt-1">
          <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mr-1 flex items-center gap-1">
            <Icon icon="lucide:filter" width="12" />
            Filter by:
          </span>

          <!-- Active Bucket Granular Options -->
          <template v-if="activeBucket === 'active'">
            <button 
              @click="activeButton('Pending')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 2 ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
              Pending / Unopened ({{ activeTaskStats?.pending || 0 }})
            </button>
            <button 
              @click="activeButton('In Progress')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 3 ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
              In Progress / Working ({{ activeTaskStats?.in_progress || 0 }})
            </button>
          </template>

          <!-- Review Bucket Granular Options -->
          <template v-if="activeBucket === 'review'">
            <button 
              @click="activeButton('Under Review')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 4 ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
              Under Review ({{ activeTaskStats?.review || 0 }})
            </button>
            <button 
              @click="activeButton('Returned')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 7 ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
              Returned for Correction ({{ activeTaskStats?.returned || 0 }})
            </button>
            <button 
              @click="activeButton('Uncompleted')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 6 ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
              Uncompleted ({{ activeTaskStats?.uncompleted || 0 }})
            </button>
          </template>

          <!-- Critical Bucket Granular Options -->
          <template v-if="activeBucket === 'critical'">
            <button 
              @click="activeButton('Overdue')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 9 ? 'bg-rose-600 text-white font-bold shadow-xs' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'">
              Overdue SLA ({{ activeTaskStats?.overdue || 0 }})
            </button>
            <button 
              @click="activeButton('Due_Soon')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 8 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'">
              Due Soon &lt;48h ({{ activeTaskStats?.due_soon || 0 }})
            </button>
            <button 
              @click="activeButton('On_Hold')" 
              class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all"
              :class="buttonSwitch === 10 ? 'bg-yellow-600 text-white font-bold shadow-xs' : 'bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100'">
              On Hold / Inquiries ({{ activeTaskStats?.on_hold || 0 }})
            </button>
          </template>
        </div>
      </div>

      <!-- True Multi-Stage Administrative Kanban Pipeline (Phase 3) -->
      <div v-if="viewMode === 'kanban'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6 items-start">
        <div 
          v-for="lane in kanbanPipeline" 
          :key="lane.id" 
          class="rounded-2xl border border-slate-200/90 shadow-xs flex flex-col min-h-[500px]"
          :class="lane.bgClass"
        >
          <!-- Lane Header -->
          <div class="p-4 border-b border-slate-200/80 bg-white/80 rounded-t-2xl backdrop-blur-sm flex items-center justify-between" :class="lane.headerBorder">
            <div class="flex items-center gap-2.5">
              <div class="p-1.5 rounded-lg" :class="lane.badgeClass">
                <Icon :icon="lane.icon" width="16" />
              </div>
              <div>
                <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <span>{{ lane.title }}</span>
                  <span class="text-xs px-2 py-0.5 rounded-full font-mono font-bold" :class="lane.badgeClass">
                    {{ lane.tasks.length }}
                  </span>
                </h4>
                <p class="text-[11px] text-slate-500 font-medium">{{ lane.subtitle }}</p>
              </div>
            </div>

            <!-- Overdue Warning Alert Badge in Lane Header -->
            <span 
              v-if="lane.overdueCount > 0" 
              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1 animate-pulse"
              title="Overdue Dockets in this stage"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
              {{ lane.overdueCount }} Overdue
            </span>
          </div>

          <!-- Lane Cards List -->
          <div class="p-3.5 space-y-3.5 flex-1 overflow-y-auto max-h-[750px] custom-scrollbar">
            <!-- Empty State for Lane -->
            <div 
              v-if="lane.tasks.length === 0" 
              class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center flex flex-col items-center justify-center text-slate-400 min-h-[220px]"
            >
              <Icon :icon="lane.icon" width="28" class="text-slate-300 mb-2 opacity-60" />
              <p class="text-xs font-semibold text-slate-500">No dockets in this stage</p>
              <p class="text-[11px] text-slate-400 mt-0.5">Tasks will appear here when moved</p>
            </div>

            <!-- Pipeline Docket Card -->
            <div 
              v-for="item in lane.tasks" 
              :key="item.id" 
              @click="openTriageDrawer(item)"
              class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs hover:shadow-lg hover:border-primary/40 transition-all duration-200 cursor-pointer group flex flex-col justify-between relative overflow-hidden"
            >
              <!-- Top Card Meta -->
              <div class="flex justify-between items-start gap-2 mb-2.5">
                <div class="flex items-center gap-2">
                  <Icon v-if="item.mail_id" icon="lucide:mail" class="text-blue-500 bg-blue-50 p-1 rounded-lg" width="24" title="Inbound Mail Docket" />
                  <Icon v-else-if="item.file_id" icon="lucide:file-text" class="text-purple-500 bg-purple-50 p-1 rounded-lg" width="24" title="Registry File Docket" />
                  <div v-else class="text-primary bg-primary/10 p-1 rounded-lg"><Icon icon="lucide:check-square" width="14" /></div>

                  <span v-if="item.tracking_id" class="text-[10px] font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-semibold truncate max-w-[110px]">
                    {{ item.tracking_id }}
                  </span>
                  <span v-if="item.folder_id" class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-semibold flex items-center gap-1" title="Archived to Department Document Manager">
                    <Icon icon="fluent:folder-open-20-filled" width="11" />
                    <span>Archived</span>
                  </span>
                </div>

                <!-- Priority Badge -->
                <p v-if="item.priority" class="text-[9px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full" 
                  :class="{
                    'bg-rose-50 text-rose-700 border border-rose-200': item.priority == 2 || item.priority === 'High',
                    'bg-amber-50 text-amber-700 border border-amber-200': item.priority == 3 || item.priority === 'Medium',
                    'bg-slate-100 text-slate-600 border border-slate-200': item.priority == 1 || item.priority === 'Low'
                  }">
                  {{ item.priority == 1 ? 'Low' : item.priority == 2 ? 'High' : (item.priority == 3 ? 'Med' : item.priority) }}
                </p>
              </div>

              <!-- Docket Title -->
              <h3 class="font-bold text-slate-800 text-sm leading-snug mb-3 group-hover:text-primary transition-colors line-clamp-2">
                {{ item.title }}
              </h3>

              <!-- Desk Officer & Tenancy Info -->
              <div class="pt-2.5 border-t border-slate-100 mb-3 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-[10px] shrink-0">
                      {{ item.assignments && item.assignments.length > 0 ? (item.assignments[0].assigned_to?.first_name?.[0] || 'O') : 'U' }}
                    </div>
                    <span class="font-semibold text-slate-700 truncate max-w-[120px]">
                      {{ item.assignments && item.assignments.length > 0 ? (item.assignments[0].assigned_to?.first_name + ' ' + (item.assignments[0].assigned_to?.last_name?.[0] || '')) : 'Unassigned' }}
                    </span>
                  </div>

                  <!-- Tenancy Dwell Badge (Phase 3 & 4) -->
                  <span 
                    v-if="getTenancyDuration(item)" 
                    class="text-[10px] font-medium px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0"
                    :class="getTenancyDuration(item).isStagnant ? 'bg-amber-100 text-amber-800 font-bold border border-amber-300' : 'bg-slate-100 text-slate-500'"
                    :title="getTenancyDuration(item).isStagnant ? 'Docket dwell time exceeds standard' : 'Time at current desk'"
                  >
                    <Icon v-if="getTenancyDuration(item).isStagnant" icon="lucide:alert-circle" width="11" class="text-amber-600" />
                    <span>{{ getTenancyDuration(item).label }}</span>
                  </span>
                </div>
              </div>

              <!-- Footer Strip: SLA & Action Buttons -->
              <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                <div class="flex items-center gap-1.5">
                  <!-- SLA Countdown / Due status -->
                  <span 
                    v-if="item.due_date" 
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="getSlaBadge(item.due_date, item.status).class"
                  >
                    <span v-if="getSlaBadge(item.due_date, item.status).dot" :class="getSlaBadge(item.due_date, item.status).dot" class="w-1.5 h-1.5 rounded-full"></span>
                    <span>{{ getSlaBadge(item.due_date, item.status).label }}</span>
                  </span>

                  <!-- Discussion Count -->
                  <div 
                    v-if="item.comments_count > 0" 
                    class="flex items-center gap-1 bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded-full text-[10px] font-bold border border-blue-200" 
                    title="Discussion Messages"
                  >
                    <Icon icon="lucide:message-square" width="11" />
                    <span>{{ item.comments_count }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                  <button 
                    @click.stop="openTriageDrawer(item)" 
                    title="Quick Triage Preview" 
                    class="flex items-center justify-center w-7 h-7 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all rounded-full shadow-2xs"
                  >
                    <Icon icon="lucide:panel-right-open" width="13" />
                  </button>

                  <Link 
                    as="button" 
                    :href="'/dashboard/view-task/'+item.id" 
                    :data="{is_assigned: item.is_assigned}" 
                    @click.stop
                    title="Open Full Task Details"
                    class="flex items-center justify-center w-7 h-7 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all rounded-full shadow-2xs"
                  >
                    <Icon icon="ph:eye" width="13" />
                  </Link>

                  <button 
                    v-if="canReassign(item)" 
                    :title="getReassignTitle(item)" 
                    @click.stop="openAssignModal(item)" 
                    class="flex items-center justify-center w-7 h-7 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all rounded-full shadow-2xs cursor-pointer"
                  >
                    <Icon v-if="item.assignments && item.assignments.length > 0" icon="lucide:refresh-cw" width="13" />
                    <Icon v-else icon="lucide:corner-up-right" width="13" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Assigned by me table -->
      <div class="overflow-x-auto mt-6" v-if="assignmentButton == 2 && viewMode === 'table'">
        <table class="min-w-max w-full">
          <thead class="bg-gray-100">
            <tr class="text-left ">
              <th class="w-10 pl-5 py-3">
                <input 
                  type="checkbox" 
                  v-model="isAllSelected" 
                  class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" 
                  title="Select all tasks on page"
                />
              </th>
              <th class="py-3 font-medium w-28 md:w-auto text-sm">Task Title</th>
              <th class="font-medium w-28 md:w-auto text-sm">Date Assigned</th>
              <th class="font-medium w-28 md:w-auto text-sm">Deadline</th>
              <th class="font-medium w-28 md:w-auto text-sm">Assigned To</th>
              <th class="font-medium w-28 md:w-auto text-sm">Priority</th>
              <th class="font-medium w-28 md:w-auto text-sm">Status</th>
              <th class="font-medium w-28 md:w-auto text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(item, index) in filteredTask" 
              :key="index" 
              @click="openTriageDrawer(item)"
              class="hover:bg-blue-50/40 cursor-pointer border-b transition-colors"
              :class="{ 'bg-blue-50/60': selectedTaskIds.includes(item.id) }"
            >
              <td class="w-10 pl-5 align-middle" @click.stop>
                <input 
                  type="checkbox" 
                  :value="item.id" 
                  v-model="selectedTaskIds" 
                  class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" 
                />
              </td>

              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-2">
                    <Icon v-if="item.mail_id" icon="lucide:mail" class="text-blue-500" width="16" title="From Correspondence Mail" />
                    <Icon v-else-if="item.file_id" icon="lucide:file-text" class="text-purple-500" width="16" title="From Registry Document" />
                    <p class="font-medium text-gray-800 hover:text-primary transition-colors" v-if="item.title && item.title.length > 0">
                      {{ item.title }}
                    </p>
                    <Icon v-if="item.is_signed" icon="lucide:pen-tool" class="text-green-600" width="16" title="Digitally Signed" />
                    <div v-if="item.comments_count > 0" class="flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full text-xs font-semibold border border-blue-200" title="Discussion Messages">
                      <Icon icon="lucide:message-square" width="12" />
                      <span>{{ item.comments_count }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <td class="align-middle">
                <div class="">
                  <p class="text-sm text-gray-600">{{ formatDate(item.created_at) }}</p>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex items-center gap-2">
                  <span 
                    v-if="item.due_date"
                    :class="getSlaBadge(item.due_date, item.status).class"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs"
                  >
                    <span 
                      v-if="getSlaBadge(item.due_date, item.status).dot" 
                      :class="getSlaBadge(item.due_date, item.status).dot" 
                      class="w-1.5 h-1.5 rounded-full"
                    ></span>
                    {{ getSlaBadge(item.due_date, item.status).label }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">-</span>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex flex-col justify-center">
                  <template v-if="item.assignments && item.assignments.length > 0">
                    <template v-if="checkUserRole(item.assignments[0].assigned_to, 'director') && item.assignments[0].assigned_to.department">
                      <p class="text-sm">Director, {{ item.assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else-if="checkUserRole(item.assignments[0].assigned_to, 'deputy director') && item.assignments[0].assigned_to.department">
                      <p class="text-sm">Deputy Director, {{ item.assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else-if="checkUserRole(item.assignments[0].assigned_to, 'assistant director') && item.assignments[0].assigned_to.department">
                      <p class="text-sm">Assistant Director, {{ item.assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else>
                      <p class="text-sm">{{ item.assignments[0].assigned_to.first_name + " " + item.assignments[0].assigned_to.last_name }}</p>
                      <p class="text-[10px] text-gray-500 font-medium" v-if="item.assignments[0].assigned_to.department">{{ item.assignments[0].assigned_to.department.title }}</p>
                    </template>
                  </template>
                  <p v-else class="text-xs w-fit bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md border border-gray-200 mt-1">Unassigned</p>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex items-center gap-2">
                  <p v-if="item.priority" class="bg-[#FEFBF6] px-5 py-1 rounded-3xl" :style="{
              color:
                item.priority == 1
                  ? '#F67E5A'
                  : item.priority == 2
                    ? '#FF3737'
                    : item.priority == 3
                      ? '#FFBC53'
                      : 'black',
            }">
                    {{ item.priority == 1 ? 'High' : (item.priority == 2 ? 'Medium' : (item.priority == 3 ? 'Low' : item.priority)) }}
                  </p>
                  <p v-else class="text-gray-400 font-medium">-</p>
                </div>
              </td>
              <td class="align-middle">
                <p class="bg-slate-50 rounded-3xl w-fit px-3 py-1 capitalize" :style="{
              color:
                (item.status || '').toLowerCase() === 'completed'
                  ? '#0D6E1A'
                  : (item.status || '').toLowerCase() === 'under review'
                    ? '#FB6514'
                    : (item.status || '').toLowerCase() === 'in progress'
                      ? '#F79009'
                      : (item.status || '').toLowerCase() === 'pending'
                        ? '#F79009'
                        : '#F04438',
            }">
                  {{ item.status }}
                </p>
                <!-- Tenancy Duration Badge (Phase 4) -->
                <div v-if="getTenancyDuration(item)" class="mt-1">
                  <span 
                    class="inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full"
                    :class="getTenancyDuration(item).isStagnant ? 'bg-amber-100 text-amber-800 font-bold border border-amber-300' : 'bg-slate-100 text-slate-500'"
                    :title="getTenancyDuration(item).isStagnant ? 'Dwell time exceeds 48 hours' : 'Time at current desk'"
                  >
                    <Icon v-if="getTenancyDuration(item).isStagnant" icon="lucide:alert-circle" width="10" class="text-amber-600" />
                    <span>{{ getTenancyDuration(item).label }}</span>
                  </span>
                </div>
              </td>
              <td class="align-middle py-3 pr-4">
                <div class="flex items-center gap-2">
                  <!-- Nudge Officer Action (Phase 4) -->
                  <button 
                    v-if="getTenancyDuration(item)?.isStagnant && (item.status || '').toLowerCase() !== 'completed'"
                    @click.stop="nudgeOfficer(item)" 
                    title="Send Priority Nudge Reminder to Desk Officer"
                    class="flex items-center justify-center w-9 h-9 border border-amber-300 text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white transition-all duration-200 rounded-full shadow-sm cursor-pointer"
                  >
                    <Icon icon="lucide:zap" width="15" />
                  </button>
                  <button @click.stop="openTriageDrawer(item)" title="Quick Triage Preview" class="flex items-center justify-center w-9 h-9 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-200 rounded-full shadow-sm">
                    <Icon icon="lucide:panel-right-open" width="16" />
                  </button>
                  <Link as="button" :href="'/dashboard/view-task/'+item.id" :data="{is_assigned: item.is_assigned}" @click.stop>
                    <div role="button" title="View Full Task Page" class="flex items-center justify-center w-9 h-9 border border-primary text-primary hover:bg-primary hover:text-white transition-all duration-200 rounded-full shadow-sm">
                      <Icon icon="ph:eye" width="18" />
                    </div>
                  </Link>
                  <button v-if="!isRole('regular user') && !isRole('regular users')" :disabled="!canReassign(item)" :title="getReassignTitle(item)" @click.stop="openAssignModal(item)" :class="['flex items-center justify-center w-9 h-9 rounded-full shadow-sm transition-all duration-200', !canReassign(item) ? 'border border-gray-200 text-gray-300 bg-gray-50 cursor-not-allowed opacity-60' : 'border border-[#0D6E1A] text-[#0D6E1A] hover:bg-[#0D6E1A] hover:text-white cursor-pointer']">
                      <Icon v-if="item.assignments && item.assignments.length > 0" icon="lucide:refresh-cw" width="16" />
                      <Icon v-else icon="lucide:corner-up-right" width="16" />
                  </button>
                  <button 
                    :title="isTaskApproved(item) ? 'Approved' : 'Review & Approve'" 
                    v-if="(item.status || '').toLowerCase() === 'under review' || isTaskApproved(item)" 
                    :disabled="isTaskApproved(item) || (item.status || '').toLowerCase() !== 'under review'"
                    @click.stop="isTaskApproved(item) ? null : approveTask(item)" 
                    :class="[
                      'flex items-center justify-center w-9 h-9 rounded-full shadow-sm transition-all duration-200',
                      (isTaskApproved(item) || (item.status || '').toLowerCase() !== 'under review')
                        ? 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed opacity-60 shadow-none'
                        : 'bg-green-500 text-white hover:bg-green-600 cursor-pointer'
                    ]"
                  >
                      <Icon :icon="isTaskApproved(item) ? 'lucide:check-check' : 'lucide:check-circle'" width="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
       
        <!-- pagination -->
        <div class="w-full pt-8 pb-4">
        <div class="flex">
          <div class="flex pl-6" v-if="created_task && created_task.first_page_url && created_task.links[0].url">
            <Link :data="{assigned_btn: assignmentButton}" preserve-state :href="created_task.prev_page_url">
              <svg width="86" class=" cursor-pointer" height="20" viewBox="0 0 86 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.8333 9.99935H4.16663M4.16663 9.99935L9.99996 15.8327M4.16663 9.99935L9.99996 4.16602" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M29.1186 15V4.81818H32.7479C33.54 4.81818 34.1963 4.96236 34.7166 5.25071C35.237 5.53906 35.6264 5.93347 35.8849 6.43395C36.1435 6.93111 36.2727 7.49124 36.2727 8.11435C36.2727 8.74077 36.1418 9.30421 35.88 9.80469C35.6214 10.3018 35.2304 10.6963 34.7067 10.9879C34.1863 11.2763 33.5317 11.4205 32.7429 11.4205H30.2472V10.1179H32.6037C33.1042 10.1179 33.5102 10.0317 33.8217 9.85938C34.1333 9.68371 34.362 9.44508 34.5078 9.14347C34.6536 8.84186 34.7266 8.49882 34.7266 8.11435C34.7266 7.72988 34.6536 7.38849 34.5078 7.0902C34.362 6.7919 34.1316 6.55824 33.8168 6.3892C33.5052 6.22017 33.0942 6.13565 32.5838 6.13565H30.6548V15H29.1186ZM37.9544 15V7.36364H39.3912V8.5767H39.4707C39.6099 8.16572 39.8552 7.84257 40.2065 7.60724C40.5611 7.36861 40.9622 7.24929 41.4096 7.24929C41.5024 7.24929 41.6118 7.2526 41.7377 7.25923C41.867 7.26586 41.9681 7.27415 42.041 7.28409V8.70597C41.9814 8.68939 41.8753 8.67116 41.7228 8.65128C41.5704 8.62808 41.4179 8.61648 41.2654 8.61648C40.9141 8.61648 40.6009 8.69105 40.3258 8.8402C40.054 8.98603 39.8386 9.18987 39.6795 9.4517C39.5204 9.71023 39.4409 10.0052 39.4409 10.3366V15H37.9544ZM46.3452 15.1541C45.5928 15.1541 44.9448 14.9934 44.4013 14.6719C43.861 14.3471 43.4434 13.8913 43.1484 13.3047C42.8568 12.7147 42.7109 12.0237 42.7109 11.2315C42.7109 10.4493 42.8568 9.75994 43.1484 9.16335C43.4434 8.56676 43.8544 8.10109 44.3814 7.76633C44.9117 7.43158 45.5315 7.2642 46.2408 7.2642C46.6716 7.2642 47.0893 7.33546 47.4936 7.47798C47.898 7.6205 48.2609 7.84422 48.5824 8.14915C48.9039 8.45407 49.1574 8.85014 49.343 9.33736C49.5286 9.82126 49.6214 10.4096 49.6214 11.1023V11.6293H43.5511V10.5156H48.1648C48.1648 10.1245 48.0852 9.77817 47.9261 9.47656C47.767 9.17164 47.5433 8.93134 47.255 8.75568C46.9699 8.58002 46.6352 8.49219 46.2507 8.49219C45.8331 8.49219 45.4685 8.59493 45.157 8.80043C44.8487 9.0026 44.6101 9.26776 44.4411 9.59588C44.2753 9.92069 44.1925 10.2737 44.1925 10.6548V11.5249C44.1925 12.0353 44.282 12.4695 44.4609 12.8274C44.6432 13.1854 44.8968 13.4588 45.2216 13.6477C45.5464 13.8333 45.9259 13.9261 46.3601 13.9261C46.6418 13.9261 46.8987 13.8864 47.1307 13.8068C47.3627 13.724 47.5632 13.6013 47.7322 13.4389C47.9013 13.2765 48.0305 13.076 48.12 12.8374L49.527 13.0909C49.4143 13.5052 49.2121 13.8681 48.9205 14.1797C48.6321 14.4879 48.2692 14.7282 47.8317 14.9006C47.3975 15.0696 46.902 15.1541 46.3452 15.1541ZM57.5847 7.36364L54.8155 15H53.2246L50.4505 7.36364H52.0463L53.9803 13.2401H54.0598L55.9888 7.36364H57.5847ZM58.9817 15V7.36364H60.4682V15H58.9817ZM59.7324 6.18537C59.4739 6.18537 59.2518 6.09919 59.0662 5.92685C58.8839 5.75118 58.7928 5.54238 58.7928 5.30043C58.7928 5.05516 58.8839 4.84635 59.0662 4.67401C59.2518 4.49834 59.4739 4.41051 59.7324 4.41051C59.9909 4.41051 60.2114 4.49834 60.3936 4.67401C60.5792 4.84635 60.6721 5.05516 60.6721 5.30043C60.6721 5.54238 60.5792 5.75118 60.3936 5.92685C60.2114 6.09919 59.9909 6.18537 59.7324 6.18537ZM65.6847 15.1541C64.9688 15.1541 64.344 14.9901 63.8104 14.6619C63.2768 14.3338 62.8625 13.8748 62.5675 13.2848C62.2725 12.6948 62.125 12.0054 62.125 11.2166C62.125 10.4245 62.2725 9.73177 62.5675 9.13849C62.8625 8.54522 63.2768 8.08452 63.8104 7.75639C64.344 7.42827 64.9688 7.2642 65.6847 7.2642C66.4006 7.2642 67.0253 7.42827 67.5589 7.75639C68.0926 8.08452 68.5069 8.54522 68.8018 9.13849C69.0968 9.73177 69.2443 10.4245 69.2443 11.2166C69.2443 12.0054 69.0968 12.6948 68.8018 13.2848C68.5069 13.8748 68.0926 14.3338 67.5589 14.6619C67.0253 14.9901 66.4006 15.1541 65.6847 15.1541ZM65.6896 13.9062C66.1536 13.9062 66.5381 13.7836 66.843 13.5384C67.148 13.2931 67.3733 12.9666 67.5192 12.5589C67.6683 12.1513 67.7429 11.7022 67.7429 11.2116C67.7429 10.7244 67.6683 10.277 67.5192 9.86932C67.3733 9.45833 67.148 9.12855 66.843 8.87997C66.5381 8.63139 66.1536 8.5071 65.6896 8.5071C65.2223 8.5071 64.8345 8.63139 64.5263 8.87997C64.2214 9.12855 63.9943 9.45833 63.8452 9.86932C63.6993 10.277 63.6264 10.7244 63.6264 11.2116C63.6264 11.7022 63.6993 12.1513 63.8452 12.5589C63.9943 12.9666 64.2214 13.2931 64.5263 13.5384C64.8345 13.7836 65.2223 13.9062 65.6896 13.9062ZM75.7409 11.8331V7.36364H77.2324V15H75.7708V13.6776H75.6912C75.5156 14.0852 75.2338 14.425 74.8461 14.6967C74.4616 14.9652 73.9827 15.0994 73.4093 15.0994C72.9187 15.0994 72.4846 14.9917 72.1067 14.7763C71.7322 14.5575 71.4372 14.2344 71.2218 13.8068C71.0096 13.3793 70.9036 12.8506 70.9036 12.2209V7.36364H72.3901V12.0419C72.3901 12.5623 72.5343 12.9766 72.8226 13.2848C73.111 13.593 73.4855 13.7472 73.9462 13.7472C74.2246 13.7472 74.5014 13.6776 74.7765 13.5384C75.0549 13.3991 75.2852 13.1887 75.4675 12.907C75.6531 12.6252 75.7443 12.2673 75.7409 11.8331ZM84.9521 9.22798L83.6048 9.46662C83.5484 9.29427 83.4589 9.13021 83.3363 8.97443C83.217 8.81865 83.0546 8.69105 82.8491 8.59162C82.6436 8.49219 82.3867 8.44247 82.0785 8.44247C81.6576 8.44247 81.3062 8.53693 81.0245 8.72585C80.7428 8.91146 80.6019 9.15175 80.6019 9.44673C80.6019 9.70194 80.6964 9.90743 80.8853 10.0632C81.0742 10.219 81.3791 10.3466 81.8001 10.446L83.0131 10.7244C83.7158 10.8868 84.2395 11.1371 84.5842 11.4751C84.9289 11.8132 85.1012 12.2524 85.1012 12.7926C85.1012 13.25 84.9686 13.6577 84.7035 14.0156C84.4416 14.3703 84.0754 14.6487 83.6048 14.8509C83.1374 15.053 82.5955 15.1541 81.979 15.1541C81.1239 15.1541 80.4263 14.9718 79.886 14.6072C79.3458 14.2393 79.0143 13.7173 78.8917 13.0412L80.3285 12.8224C80.418 13.197 80.6019 13.4804 80.8803 13.6726C81.1587 13.8615 81.5217 13.956 81.9691 13.956C82.4563 13.956 82.8458 13.8549 83.1374 13.6527C83.4291 13.4472 83.5749 13.197 83.5749 12.902C83.5749 12.6634 83.4854 12.4628 83.3065 12.3004C83.1308 12.138 82.8607 12.0154 82.4961 11.9325L81.2035 11.6491C80.4909 11.4867 79.9639 11.2282 79.6225 10.8736C79.2844 10.5189 79.1154 10.0698 79.1154 9.52628C79.1154 9.07552 79.2414 8.68111 79.4933 8.34304C79.7451 8.00497 80.0932 7.74148 80.5373 7.55256C80.9814 7.36032 81.4902 7.2642 82.0636 7.2642C82.8888 7.2642 83.5385 7.44318 84.0124 7.80114C84.4864 8.15578 84.7996 8.63139 84.9521 9.22798Z" fill="#667085"/>
              </svg>
            </Link>

          </div>

          <div class="mx-auto flex" v-if="created_task && created_task.links">
            <template v-for="link in created_task.links">
                <Link :data="{assigned_btn: assignmentButton}" preserve-state  :class="{'hidden': link.label.includes('Previous') || link.label.includes('Next')}" :href="link.url"><span class="w-[30px] h-[30px] rounded-full text-[#0D6E1A]  flex items-end justify-center p-1 cursor-pointer mx-1" :class="link.active? 'bg-[#b5d1b0]':'bg-[#E7F2E8]'">{{ link.label }}</span></Link>
            </template>
          </div>

          <div class="flex pr-10" v-if="created_task && created_task.next_page_url && created_task.links[created_task.links.length -1].url">
            <Link :data="{assigned_btn: assignmentButton}" preserve-state :href="created_task.next_page_url" >
              <svg width="60" height="20" class=" cursor-pointer" viewBox="0 0 60 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9.33665 4.81818V15H7.92472L2.74929 7.53267H2.65483V15H1.11861V4.81818H2.54048L7.72088 12.2955H7.81534V4.81818H9.33665ZM14.7495 15.1541C13.9971 15.1541 13.3491 14.9934 12.8056 14.6719C12.2653 14.3471 11.8477 13.8913 11.5527 13.3047C11.2611 12.7147 11.1152 12.0237 11.1152 11.2315C11.1152 10.4493 11.2611 9.75994 11.5527 9.16335C11.8477 8.56676 12.2587 8.10109 12.7857 7.76633C13.316 7.43158 13.9358 7.2642 14.6451 7.2642C15.0759 7.2642 15.4935 7.33546 15.8979 7.47798C16.3023 7.6205 16.6652 7.84422 16.9867 8.14915C17.3082 8.45407 17.5617 8.85014 17.7473 9.33736C17.9329 9.82126 18.0257 10.4096 18.0257 11.1023V11.6293H11.9554V10.5156H16.5691C16.5691 10.1245 16.4895 9.77817 16.3304 9.47656C16.1713 9.17164 15.9476 8.93134 15.6593 8.75568C15.3742 8.58002 15.0395 8.49219 14.655 8.49219C14.2374 8.49219 13.8728 8.59493 13.5613 8.80043C13.253 9.0026 13.0144 9.26776 12.8453 9.59588C12.6796 9.92069 12.5968 10.2737 12.5968 10.6548V11.5249C12.5968 12.0353 12.6863 12.4695 12.8652 12.8274C13.0475 13.1854 13.3011 13.4588 13.6259 13.6477C13.9507 13.8333 14.3302 13.9261 14.7644 13.9261C15.0461 13.9261 15.303 13.8864 15.535 13.8068C15.767 13.724 15.9675 13.6013 16.1365 13.4389C16.3056 13.2765 16.4348 13.076 16.5243 12.8374L17.9313 13.0909C17.8186 13.5052 17.6164 13.8681 17.3248 14.1797C17.0364 14.4879 16.6735 14.7282 16.236 14.9006C15.8018 15.0696 15.3063 15.1541 14.7495 15.1541ZM20.6706 7.36364L22.356 10.3366L24.0563 7.36364H25.682L23.3006 11.1818L25.7019 15H24.0762L22.356 12.1463L20.6408 15H19.0101L21.3865 11.1818L19.04 7.36364H20.6706ZM30.7729 7.36364V8.55682H26.6017V7.36364H30.7729ZM27.7203 5.53409H29.2069V12.7578C29.2069 13.0462 29.2499 13.2633 29.3361 13.4091C29.4223 13.5516 29.5333 13.6494 29.6692 13.7024C29.8084 13.7521 29.9592 13.777 30.1216 13.777C30.2409 13.777 30.3453 13.7687 30.4348 13.7521C30.5243 13.7356 30.5939 13.7223 30.6436 13.7124L30.9121 14.9403C30.8259 14.9735 30.7033 15.0066 30.5442 15.0398C30.3851 15.0762 30.1863 15.0961 29.9476 15.0994C29.5565 15.1061 29.1919 15.0365 28.8539 14.8906C28.5158 14.7448 28.2424 14.5194 28.0336 14.2145C27.8248 13.9096 27.7203 13.5268 27.7203 13.0661V5.53409Z" fill="#667085"/>
              <path d="M44.1667 9.99935H55.8334M55.8334 9.99935L50.0001 4.16602M55.8334 9.99935L50.0001 15.8327" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </Link>
          </div>
        </div>
      </div>
      </div>

      <!-- Assigned to me table  -->
      <div class="overflow-x-scroll lg:overflow-hidden mt-10" v-if="assignmentButton == 1 && viewMode === 'table'">
        <table class="min-w-max w-full">
          <thead class="bg-gray-100">
            <tr class="text-left">
              <th class="w-10 pl-5 py-3">
                <input 
                  type="checkbox" 
                  v-model="isAllSelected" 
                  class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" 
                  title="Select all tasks on page"
                />
              </th>
              <th class="py-3 font-medium w-28 md:w-auto text-sm">Task Title</th>
              <th class="font-medium w-28 md:w-auto text-sm">Date Assigned</th>
              <th class="font-medium w-28 md:w-auto text-sm">Deadline</th>
              <th class="font-medium w-28 md:w-auto text-sm">Assigned By</th>
              <th class="font-medium w-28 md:w-auto text-sm">Assigned To</th>
              <th class="font-medium w-28 md:w-auto text-sm">Priority</th>
              <th class="font-medium w-28 md:w-auto text-sm">Status</th>
              <th class="font-medium w-28 md:w-auto text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(item, index) in filteredAssignedTask" 
              :key="index" 
              @click="openTriageDrawer(item)"
              class="hover:bg-blue-50/40 cursor-pointer border-b transition-colors"
              :class="{ 'bg-blue-50/60': selectedTaskIds.includes(item.id) }"
            >
              <td class="w-10 pl-5 align-middle" @click.stop>
                <input 
                  type="checkbox" 
                  :value="item.id" 
                  v-model="selectedTaskIds" 
                  class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" 
                />
              </td>

              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-2">
                    <Icon v-if="item.mail_id" icon="lucide:mail" class="text-blue-500" width="16" title="From Correspondence Mail" />
                    <Icon v-else-if="item.file_id" icon="lucide:file-text" class="text-purple-500" width="16" title="From Registry Document" />
                    <p class="font-medium text-gray-800 hover:text-primary transition-colors" v-if="item.title && item.title.length > 0">
                      {{ item.title }}
                    </p>
                    <Icon v-if="item.is_signed" icon="lucide:pen-tool" class="text-green-600" width="16" title="Digitally Signed" />
                    <div v-if="item.comments_count > 0" class="flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full text-xs font-semibold border border-blue-200" title="Discussion Messages">
                      <Icon icon="lucide:message-square" width="12" />
                      <span>{{ item.comments_count }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <td class="align-middle">
                <div class="">
                  <p class="text-sm text-gray-600">{{ formatDate(item.created_at) }}</p>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex items-center gap-2">
                  <span 
                    v-if="item.due_date"
                    :class="getSlaBadge(item.due_date, item.status).class"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs"
                  >
                    <span 
                      v-if="getSlaBadge(item.due_date, item.status).dot" 
                      :class="getSlaBadge(item.due_date, item.status).dot" 
                      class="w-1.5 h-1.5 rounded-full"
                    ></span>
                    {{ getSlaBadge(item.due_date, item.status).label }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">-</span>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex flex-col justify-center">
                  <template v-if="item.assignments && item.assignments.length > 0">
                    <p class="text-sm">{{ item.assignments[0].assigned_by.first_name + " " + item.assignments[0].assigned_by.last_name }}</p>
                    <p class="text-[10px] text-gray-500 font-medium" v-if="item.assignments[0].assigned_by.department">{{ item.assignments[0].assigned_by.department.title }}</p>
                  </template>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex flex-col justify-center">
                  <template v-if="item.assigned_out_assignments && item.assigned_out_assignments.length > 0">
                    <template v-if="checkUserRole(item.assigned_out_assignments[0].assigned_to, 'director') && item.assigned_out_assignments[0].assigned_to.department">
                      <p class="text-sm">Director, {{ item.assigned_out_assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else-if="checkUserRole(item.assigned_out_assignments[0].assigned_to, 'deputy director') && item.assigned_out_assignments[0].assigned_to.department">
                      <p class="text-sm">Deputy Director, {{ item.assigned_out_assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else-if="checkUserRole(item.assigned_out_assignments[0].assigned_to, 'assistant director') && item.assigned_out_assignments[0].assigned_to.department">
                      <p class="text-sm">Assistant Director, {{ item.assigned_out_assignments[0].assigned_to.department.title }}</p>
                    </template>
                    <template v-else>
                      <p class="text-sm">{{ item.assigned_out_assignments[0].assigned_to.first_name + " " + item.assigned_out_assignments[0].assigned_to.last_name }}</p>
                      <p class="text-[10px] text-gray-500 font-medium" v-if="item.assigned_out_assignments[0].assigned_to.department">{{ item.assigned_out_assignments[0].assigned_to.department.title }}</p>
                    </template>
                  </template>
                  <p v-else class="text-xs w-fit bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md border border-gray-200 mt-1">Not Assigned Out</p>
                </div>
              </td>
              <td class="align-middle py-1.5">
                <div class="flex items-center gap-2">
                  <p v-if="item.priority" class="bg-[#FEFBF6] px-5 py-1 rounded-3xl capitalize" :style="{
              color:
                item.priority == 1
                  ? '#F67E5A'
                  : item.priority == 2
                    ? '#FF3737'
                    : item.priority == 3
                      ? '#FFBC53'
                      : 'black',
            }">
                    {{ item.priority == 1 ? 'High' : (item.priority == 2 ? 'Medium' : (item.priority == 3 ? 'Low' : item.priority)) }}
                  </p>
                  <p v-else class="text-gray-400 font-medium">-</p>
                </div>
              </td>
              <td class="align-middle">
                <p class="bg-slate-50 rounded-3xl w-fit px-3 py-1 capitalize" :style="{
              color:
                (item.status || '').toLowerCase() === 'completed'
                  ? '#0D6E1A'
                  : (item.status || '').toLowerCase() === 'review'
                    ? '#FB6514'
                    : (item.status || '').toLowerCase() === 'pending'
                      ? '#F79009'
                      : (item.status || '').toLowerCase() === 'in progress'
                        ? '#F79009'
                        : '#F04438',
            }">
                  {{ item.status }}
                </p>
                <!-- Tenancy Duration Badge (Phase 4) -->
                <div v-if="getTenancyDuration(item)" class="mt-1">
                  <span 
                    class="inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full"
                    :class="getTenancyDuration(item).isStagnant ? 'bg-amber-100 text-amber-800 font-bold border border-amber-300' : 'bg-slate-100 text-slate-500'"
                    :title="getTenancyDuration(item).isStagnant ? 'Dwell time exceeds 48 hours' : 'Time at current desk'"
                  >
                    <Icon v-if="getTenancyDuration(item).isStagnant" icon="lucide:alert-circle" width="10" class="text-amber-600" />
                    <span>{{ getTenancyDuration(item).label }}</span>
                  </span>
                </div>
              </td>
              <td class="align-middle py-3 pr-4">
                <div class="flex items-center gap-2">
                  <!-- Nudge Officer Action (Phase 4) -->
                  <button 
                    v-if="item.assigned_out_assignments && item.assigned_out_assignments.length > 0 && getTenancyDuration(item)?.isStagnant && (item.status || '').toLowerCase() !== 'completed'"
                    @click.stop="nudgeOfficer(item)" 
                    title="Send Priority Nudge Reminder to Desk Officer"
                    class="flex items-center justify-center w-9 h-9 border border-amber-300 text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white transition-all duration-200 rounded-full shadow-sm cursor-pointer"
                  >
                    <Icon icon="lucide:zap" width="15" />
                  </button>
                  <button @click.stop="openTriageDrawer(item)" title="Quick Triage Preview" class="flex items-center justify-center w-9 h-9 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-200 rounded-full shadow-sm">
                    <Icon icon="lucide:panel-right-open" width="16" />
                  </button>
                  <Link as="button" :href="'/dashboard/view-task/'+item.id" :data="{is_assigned: item.is_assigned}" @click.stop>
                    <div role="button" title="View Full Task Page" class="flex items-center justify-center w-9 h-9 border border-primary text-primary hover:bg-primary hover:text-white transition-all duration-200 rounded-full shadow-sm">
                      <Icon icon="ph:eye" width="18" />
                    </div>
                  </Link>
                  <button v-if="!isRole('regular user') && !isRole('regular users')" :disabled="!canReassign(item)" :title="getReassignTitle(item)" @click.stop="openAssignModal(item)" :class="['flex items-center justify-center w-9 h-9 rounded-full shadow-sm transition-all duration-200', !canReassign(item) ? 'border border-gray-200 text-gray-300 bg-gray-50 cursor-not-allowed opacity-60' : 'border border-[#0D6E1A] text-[#0D6E1A] hover:bg-[#0D6E1A] hover:text-white cursor-pointer']">
                      <Icon v-if="item.assigned_out_assignments && item.assigned_out_assignments.length > 0" icon="lucide:refresh-cw" width="16" />
                      <Icon v-else icon="lucide:corner-up-right" width="16" />
                  </button>
                  <button title="Archive Task" v-if="(item.status || '').toLowerCase() === 'completed'" @click.stop="archiveTask(item)" class="flex items-center justify-center w-9 h-9 bg-gray-700 text-white hover:bg-gray-800 transition-all duration-200 rounded-full shadow-sm">
                      <Icon icon="lucide:archive" width="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- <pagination :archives="assigned_task" /> -->

        <!-- pagination -->
        <div class="w-full pt-8 pb-4" v-if="assigned_task && assigned_task.links && assigned_task.links.length > 0">
        <div class="flex">
          <div class="flex pl-6" v-if="assigned_task && assigned_task.first_page_url && assigned_task.links[0].url">
            <Link :data="{assigned_btn: assignmentButton}" preserve-state :href="assigned_task.prev_page_url">
              <svg width="86" class=" cursor-pointer" height="20" viewBox="0 0 86 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.8333 9.99935H4.16663M4.16663 9.99935L9.99996 15.8327M4.16663 9.99935L9.99996 4.16602" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M29.1186 15V4.81818H32.7479C33.54 4.81818 34.1963 4.96236 34.7166 5.25071C35.237 5.53906 35.6264 5.93347 35.8849 6.43395C36.1435 6.93111 36.2727 7.49124 36.2727 8.11435C36.2727 8.74077 36.1418 9.30421 35.88 9.80469C35.6214 10.3018 35.2304 10.6963 34.7067 10.9879C34.1863 11.2763 33.5317 11.4205 32.7429 11.4205H30.2472V10.1179H32.6037C33.1042 10.1179 33.5102 10.0317 33.8217 9.85938C34.1333 9.68371 34.362 9.44508 34.5078 9.14347C34.6536 8.84186 34.7266 8.49882 34.7266 8.11435C34.7266 7.72988 34.6536 7.38849 34.5078 7.0902C34.362 6.7919 34.1316 6.55824 33.8168 6.3892C33.5052 6.22017 33.0942 6.13565 32.5838 6.13565H30.6548V15H29.1186ZM37.9544 15V7.36364H39.3912V8.5767H39.4707C39.6099 8.16572 39.8552 7.84257 40.2065 7.60724C40.5611 7.36861 40.9622 7.24929 41.4096 7.24929C41.5024 7.24929 41.6118 7.2526 41.7377 7.25923C41.867 7.26586 41.9681 7.27415 42.041 7.28409V8.70597C41.9814 8.68939 41.8753 8.67116 41.7228 8.65128C41.5704 8.62808 41.4179 8.61648 41.2654 8.61648C40.9141 8.61648 40.6009 8.69105 40.3258 8.8402C40.054 8.98603 39.8386 9.18987 39.6795 9.4517C39.5204 9.71023 39.4409 10.0052 39.4409 10.3366V15H37.9544ZM46.3452 15.1541C45.5928 15.1541 44.9448 14.9934 44.4013 14.6719C43.861 14.3471 43.4434 13.8913 43.1484 13.3047C42.8568 12.7147 42.7109 12.0237 42.7109 11.2315C42.7109 10.4493 42.8568 9.75994 43.1484 9.16335C43.4434 8.56676 43.8544 8.10109 44.3814 7.76633C44.9117 7.43158 45.5315 7.2642 46.2408 7.2642C46.6716 7.2642 47.0893 7.33546 47.4936 7.47798C47.898 7.6205 48.2609 7.84422 48.5824 8.14915C48.9039 8.45407 49.1574 8.85014 49.343 9.33736C49.5286 9.82126 49.6214 10.4096 49.6214 11.1023V11.6293H43.5511V10.5156H48.1648C48.1648 10.1245 48.0852 9.77817 47.9261 9.47656C47.767 9.17164 47.5433 8.93134 47.255 8.75568C46.9699 8.58002 46.6352 8.49219 46.2507 8.49219C45.8331 8.49219 45.4685 8.59493 45.157 8.80043C44.8487 9.0026 44.6101 9.26776 44.4411 9.59588C44.2753 9.92069 44.1925 10.2737 44.1925 10.6548V11.5249C44.1925 12.0353 44.282 12.4695 44.4609 12.8274C44.6432 13.1854 44.8968 13.4588 45.2216 13.6477C45.5464 13.8333 45.9259 13.9261 46.3601 13.9261C46.6418 13.9261 46.8987 13.8864 47.1307 13.8068C47.3627 13.724 47.5632 13.6013 47.7322 13.4389C47.9013 13.2765 48.0305 13.076 48.12 12.8374L49.527 13.0909C49.4143 13.5052 49.2121 13.8681 48.9205 14.1797C48.6321 14.4879 48.2692 14.7282 47.8317 14.9006C47.3975 15.0696 46.902 15.1541 46.3452 15.1541ZM57.5847 7.36364L54.8155 15H53.2246L50.4505 7.36364H52.0463L53.9803 13.2401H54.0598L55.9888 7.36364H57.5847ZM58.9817 15V7.36364H60.4682V15H58.9817ZM59.7324 6.18537C59.4739 6.18537 59.2518 6.09919 59.0662 5.92685C58.8839 5.75118 58.7928 5.54238 58.7928 5.30043C58.7928 5.05516 58.8839 4.84635 59.0662 4.67401C59.2518 4.49834 59.4739 4.41051 59.7324 4.41051C59.9909 4.41051 60.2114 4.49834 60.3936 4.67401C60.5792 4.84635 60.6721 5.05516 60.6721 5.30043C60.6721 5.54238 60.5792 5.75118 60.3936 5.92685C60.2114 6.09919 59.9909 6.18537 59.7324 6.18537ZM65.6847 15.1541C64.9688 15.1541 64.344 14.9901 63.8104 14.6619C63.2768 14.3338 62.8625 13.8748 62.5675 13.2848C62.2725 12.6948 62.125 12.0054 62.125 11.2166C62.125 10.4245 62.2725 9.73177 62.5675 9.13849C62.8625 8.54522 63.2768 8.08452 63.8104 7.75639C64.344 7.42827 64.9688 7.2642 65.6847 7.2642C66.4006 7.2642 67.0253 7.42827 67.5589 7.75639C68.0926 8.08452 68.5069 8.54522 68.8018 9.13849C69.0968 9.73177 69.2443 10.4245 69.2443 11.2166C69.2443 12.0054 69.0968 12.6948 68.8018 13.2848C68.5069 13.8748 68.0926 14.3338 67.5589 14.6619C67.0253 14.9901 66.4006 15.1541 65.6847 15.1541ZM65.6896 13.9062C66.1536 13.9062 66.5381 13.7836 66.843 13.5384C67.148 13.2931 67.3733 12.9666 67.5192 12.5589C67.6683 12.1513 67.7429 11.7022 67.7429 11.2116C67.7429 10.7244 67.6683 10.277 67.5192 9.86932C67.3733 9.45833 67.148 9.12855 66.843 8.87997C66.5381 8.63139 66.1536 8.5071 65.6896 8.5071C65.2223 8.5071 64.8345 8.63139 64.5263 8.87997C64.2214 9.12855 63.9943 9.45833 63.8452 9.86932C63.6993 10.277 63.6264 10.7244 63.6264 11.2116C63.6264 11.7022 63.6993 12.1513 63.8452 12.5589C63.9943 12.9666 64.2214 13.2931 64.5263 13.5384C64.8345 13.7836 65.2223 13.9062 65.6896 13.9062ZM75.7409 11.8331V7.36364H77.2324V15H75.7708V13.6776H75.6912C75.5156 14.0852 75.2338 14.425 74.8461 14.6967C74.4616 14.9652 73.9827 15.0994 73.4093 15.0994C72.9187 15.0994 72.4846 14.9917 72.1067 14.7763C71.7322 14.5575 71.4372 14.2344 71.2218 13.8068C71.0096 13.3793 70.9036 12.8506 70.9036 12.2209V7.36364H72.3901V12.0419C72.3901 12.5623 72.5343 12.9766 72.8226 13.2848C73.111 13.593 73.4855 13.7472 73.9462 13.7472C74.2246 13.7472 74.5014 13.6776 74.7765 13.5384C75.0549 13.3991 75.2852 13.1887 75.4675 12.907C75.6531 12.6252 75.7443 12.2673 75.7409 11.8331ZM84.9521 9.22798L83.6048 9.46662C83.5484 9.29427 83.4589 9.13021 83.3363 8.97443C83.217 8.81865 83.0546 8.69105 82.8491 8.59162C82.6436 8.49219 82.3867 8.44247 82.0785 8.44247C81.6576 8.44247 81.3062 8.53693 81.0245 8.72585C80.7428 8.91146 80.6019 9.15175 80.6019 9.44673C80.6019 9.70194 80.6964 9.90743 80.8853 10.0632C81.0742 10.219 81.3791 10.3466 81.8001 10.446L83.0131 10.7244C83.7158 10.8868 84.2395 11.1371 84.5842 11.4751C84.9289 11.8132 85.1012 12.2524 85.1012 12.7926C85.1012 13.25 84.9686 13.6577 84.7035 14.0156C84.4416 14.3703 84.0754 14.6487 83.6048 14.8509C83.1374 15.053 82.5955 15.1541 81.979 15.1541C81.1239 15.1541 80.4263 14.9718 79.886 14.6072C79.3458 14.2393 79.0143 13.7173 78.8917 13.0412L80.3285 12.8224C80.418 13.197 80.6019 13.4804 80.8803 13.6726C81.1587 13.8615 81.5217 13.956 81.9691 13.956C82.4563 13.956 82.8458 13.8549 83.1374 13.6527C83.4291 13.4472 83.5749 13.197 83.5749 12.902C83.5749 12.6634 83.4854 12.4628 83.3065 12.3004C83.1308 12.138 82.8607 12.0154 82.4961 11.9325L81.2035 11.6491C80.4909 11.4867 79.9639 11.2282 79.6225 10.8736C79.2844 10.5189 79.1154 10.0698 79.1154 9.52628C79.1154 9.07552 79.2414 8.68111 79.4933 8.34304C79.7451 8.00497 80.0932 7.74148 80.5373 7.55256C80.9814 7.36032 81.4902 7.2642 82.0636 7.2642C82.8888 7.2642 83.5385 7.44318 84.0124 7.80114C84.4864 8.15578 84.7996 8.63139 84.9521 9.22798Z" fill="#667085"/>
              </svg>
            </Link>

          </div>

          <div class="mx-auto flex" v-if="assigned_task && assigned_task.links">
            <template v-for="link in assigned_task.links">
                <Link :data="{assigned_btn: assignmentButton}" preserve-state  :class="{'hidden': link.label.includes('Previous') || link.label.includes('Next')}" :href="link.url"><span class="w-[30px] h-[30px] rounded-full text-[#0D6E1A]  flex items-end justify-center p-1 cursor-pointer mx-1" :class="link.active? 'bg-[#b5d1b0]':'bg-[#E7F2E8]'">{{ link.label }}</span></Link>
            </template>
          </div>

          <div class="flex pr-10" v-if="assigned_task && assigned_task.next_page_url && assigned_task.links[assigned_task.links.length -1].url">
            <Link :data="{assigned_btn: assignmentButton}" preserve-state :href="assigned_task.next_page_url" >
              <svg width="60" height="20" class=" cursor-pointer" viewBox="0 0 60 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9.33665 4.81818V15H7.92472L2.74929 7.53267H2.65483V15H1.11861V4.81818H2.54048L7.72088 12.2955H7.81534V4.81818H9.33665ZM14.7495 15.1541C13.9971 15.1541 13.3491 14.9934 12.8056 14.6719C12.2653 14.3471 11.8477 13.8913 11.5527 13.3047C11.2611 12.7147 11.1152 12.0237 11.1152 11.2315C11.1152 10.4493 11.2611 9.75994 11.5527 9.16335C11.8477 8.56676 12.2587 8.10109 12.7857 7.76633C13.316 7.43158 13.9358 7.2642 14.6451 7.2642C15.0759 7.2642 15.4935 7.33546 15.8979 7.47798C16.3023 7.6205 16.6652 7.84422 16.9867 8.14915C17.3082 8.45407 17.5617 8.85014 17.7473 9.33736C17.9329 9.82126 18.0257 10.4096 18.0257 11.1023V11.6293H11.9554V10.5156H16.5691C16.5691 10.1245 16.4895 9.77817 16.3304 9.47656C16.1713 9.17164 15.9476 8.93134 15.6593 8.75568C15.3742 8.58002 15.0395 8.49219 14.655 8.49219C14.2374 8.49219 13.8728 8.59493 13.5613 8.80043C13.253 9.0026 13.0144 9.26776 12.8453 9.59588C12.6796 9.92069 12.5968 10.2737 12.5968 10.6548V11.5249C12.5968 12.0353 12.6863 12.4695 12.8652 12.8274C13.0475 13.1854 13.3011 13.4588 13.6259 13.6477C13.9507 13.8333 14.3302 13.9261 14.7644 13.9261C15.0461 13.9261 15.303 13.8864 15.535 13.8068C15.767 13.724 15.9675 13.6013 16.1365 13.4389C16.3056 13.2765 16.4348 13.076 16.5243 12.8374L17.9313 13.0909C17.8186 13.5052 17.6164 13.8681 17.3248 14.1797C17.0364 14.4879 16.6735 14.7282 16.236 14.9006C15.8018 15.0696 15.3063 15.1541 14.7495 15.1541ZM20.6706 7.36364L22.356 10.3366L24.0563 7.36364H25.682L23.3006 11.1818L25.7019 15H24.0762L22.356 12.1463L20.6408 15H19.0101L21.3865 11.1818L19.04 7.36364H20.6706ZM30.7729 7.36364V8.55682H26.6017V7.36364H30.7729ZM27.7203 5.53409H29.2069V12.7578C29.2069 13.0462 29.2499 13.2633 29.3361 13.4091C29.4223 13.5516 29.5333 13.6494 29.6692 13.7024C29.8084 13.7521 29.9592 13.777 30.1216 13.777C30.2409 13.777 30.3453 13.7687 30.4348 13.7521C30.5243 13.7356 30.5939 13.7223 30.6436 13.7124L30.9121 14.9403C30.8259 14.9735 30.7033 15.0066 30.5442 15.0398C30.3851 15.0762 30.1863 15.0961 29.9476 15.0994C29.5565 15.1061 29.1919 15.0365 28.8539 14.8906C28.5158 14.7448 28.2424 14.5194 28.0336 14.2145C27.8248 13.9096 27.7203 13.5268 27.7203 13.0661V5.53409Z" fill="#667085"/>
              <path d="M44.1667 9.99935H55.8334M55.8334 9.99935L50.0001 4.16602M55.8334 9.99935L50.0001 15.8327" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </Link>
          </div>
        </div>
      </div>
      </div>

      <!-- Submissions & Review table (Tab 3) -->
      <div class="overflow-x-auto mt-6" v-if="assignmentButton == 3 && viewMode === 'table'">
        <table class="min-w-max w-full">
          <thead class="bg-emerald-50/80 border-b border-emerald-100">
            <tr class="text-left">
              <th class="w-10 pl-5 py-3">
                <input 
                  type="checkbox" 
                  v-model="isAllSelected" 
                  class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" 
                  title="Select all tasks on page"
                />
              </th>
              <th class="py-3 font-semibold text-emerald-950 text-sm">Task & Tracking ID</th>
              <th class="font-semibold text-emerald-950 text-sm">Submitted By</th>
              <th class="font-semibold text-emerald-950 text-sm">Submission Date</th>
              <th class="font-semibold text-emerald-950 text-sm">Deadline</th>
              <th class="font-semibold text-emerald-950 text-sm">Attached Output</th>
              <th class="font-semibold text-emerald-950 text-sm">Review Status</th>
              <th class="font-semibold text-emerald-950 text-sm pr-4">Review Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(item, index) in filteredSubmissionsTask" 
              :key="index" 
              @click="openTriageDrawer(item)"
              class="hover:bg-emerald-50/40 cursor-pointer border-b transition-colors"
              :class="{ 'bg-emerald-50/60': selectedTaskIds.includes(item.id) }"
            >
              <td class="w-10 pl-5 align-middle" @click.stop>
                <input 
                  type="checkbox" 
                  :value="item.id" 
                  v-model="selectedTaskIds" 
                  class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" 
                />
              </td>

              <td class="align-middle py-3">
                <div class="flex items-center gap-2">
                  <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                    <Icon icon="lucide:file-check" width="16" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-bold text-gray-800 hover:text-emerald-700 transition-colors">
                        {{ item.title }}
                      </p>
                      <div v-if="item.comments_count > 0" class="flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full text-xs font-semibold border border-blue-200" title="Discussion Messages">
                        <Icon icon="lucide:message-square" width="12" />
                        <span>{{ item.comments_count }}</span>
                      </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span v-if="item.tracking_id" class="text-[11px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                        {{ item.tracking_id }}
                      </span>
                      <span v-if="item.folder_id" class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-semibold flex items-center gap-1" title="Archived to Department Document Manager">
                        <Icon icon="fluent:folder-open-20-filled" width="11" />
                        <span>Archived</span>
                      </span>
                    </div>
                  </div>
                </div>
              </td>

              <td class="align-middle py-3">
                <div class="flex flex-col" v-if="getReviewSubmitter(item)">
                  <span class="font-semibold text-sm text-gray-800">
                    {{ getReviewSubmitter(item).first_name }} {{ getReviewSubmitter(item).last_name }}
                  </span>
                  <span class="text-xs text-gray-500" v-if="getReviewSubmitter(item).department">
                    {{ getReviewSubmitter(item).department?.title }}
                  </span>
                </div>
                <span v-else class="text-gray-400 text-sm">-</span>
              </td>

              <td class="align-middle py-3">
                <span class="text-sm text-gray-600">{{ formatDate(item.updated_at || item.created_at) }}</span>
              </td>

              <td class="align-middle py-3">
                <div class="flex items-center gap-2">
                  <span 
                    v-if="item.due_date"
                    :class="getSlaBadge(item.due_date, item.status).class"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  >
                    <span 
                      v-if="getSlaBadge(item.due_date, item.status).dot" 
                      :class="getSlaBadge(item.due_date, item.status).dot" 
                      class="w-1.5 h-1.5 rounded-full"
                    ></span>
                    {{ getSlaBadge(item.due_date, item.status).label }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">-</span>
                </div>
              </td>

              <td class="align-middle py-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg border border-gray-200">
                    <Icon icon="lucide:paperclip" width="14" />
                    <span>{{ item.taskdocs_count || (item.taskdocs ? item.taskdocs.length : 0) }} files</span>
                  </span>
                </div>
              </td>

              <td class="align-middle py-3">
                <span class="bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs px-3 py-1 font-semibold capitalize inline-flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                  Under Review
                </span>
              </td>

              <td class="align-middle py-3 pr-4">
                <div class="flex items-center gap-2">
                  <button @click.stop="openTriageDrawer(item)" title="Quick Triage Preview" class="flex items-center justify-center w-9 h-9 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-200 rounded-full shadow-sm">
                    <Icon icon="lucide:panel-right-open" width="16" />
                  </button>
                  <Link as="button" :href="'/dashboard/view-task/'+item.id" :data="{is_assigned: item.is_assigned}" @click.stop>
                    <div role="button" title="Open Full Review & Approval Screen" class="flex items-center justify-center w-9 h-9 border border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all duration-200 rounded-full shadow-sm">
                      <Icon icon="lucide:file-check-2" width="18" />
                    </div>
                  </Link>
                </div>
              </td>
            </tr>
            <tr v-if="filteredSubmissionsTask.length === 0">
              <td colspan="8" class="text-center py-12 text-gray-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <Icon icon="lucide:inbox" width="40" class="text-gray-300" />
                  <p class="font-medium text-gray-500">No submissions currently awaiting your review.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- pagination for submissions -->
        <div class="w-full pt-8 pb-4" v-if="submissions_task && submissions_task.links && submissions_task.links.length > 0">
          <div class="flex justify-between items-center px-4">
            <Link v-if="submissions_task.prev_page_url" :data="{assigned_btn: assignmentButton}" preserve-state :href="submissions_task.prev_page_url" class="text-xs font-semibold px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">Previous</Link>
            <div class="flex gap-1">
              <template v-for="link in submissions_task.links">
                <Link v-if="!link.label.includes('Previous') && !link.label.includes('Next')" :data="{assigned_btn: assignmentButton}" preserve-state :href="link.url">
                  <span class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors" :class="link.active ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">{{ link.label }}</span>
                </Link>
              </template>
            </div>
            <Link v-if="submissions_task.next_page_url" :data="{assigned_btn: assignmentButton}" preserve-state :href="submissions_task.next_page_url" class="text-xs font-semibold px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">Next</Link>
          </div>
        </div>
      </div>

      <!-- Tab 4: Awaiting My Action Queue -->
      <div class="mt-6" v-if="assignmentButton == 4">
        <AwaitingMyAction />
      </div>

      <div class="mt-5">
        <!-- <paginator
          :totalItems="totalItems"
          :itemsPerPage="itemsPerPage"
          v-model="currentPage"
        /> -->
      </div>
    </div>

    <!-- Floating Bulk Action Toolbar (Pillar 2) -->
    <Transition name="fade-slide">
      <div 
        v-if="selectedTaskIds.length > 0" 
        class="fixed bottom-8 left-1/2 -translate-x-1/2 z-40 bg-slate-900/95 text-white rounded-2xl shadow-2xl px-6 py-4 flex items-center gap-6 border border-slate-700/80 backdrop-blur-md"
      >
        <div class="flex items-center gap-3">
          <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs shadow-sm">
            {{ selectedTaskIds.length }}
          </span>
          <span class="text-sm font-semibold text-gray-200 whitespace-nowrap">Tasks Selected</span>
        </div>

        <div class="h-6 w-[1px] bg-slate-700"></div>

        <div class="flex items-center gap-3">
          <button 
            @click="openBatchAssignModal(null)" 
            class="px-4 py-2 bg-primary hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-sm"
          >
            <Icon icon="lucide:send" width="16" />
            <span>{{ (isRole('regular user') || isRole('regular users')) ? 'Submit to Departmental Director' : 'Batch Route / Assign' }}</span>
          </button>

          <button 
            v-if="isAnyCompletedSelected" 
            @click="handleBatchArchive" 
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-gray-200 rounded-xl text-xs font-semibold transition-all flex items-center gap-2 border border-slate-700"
          >
            <Icon icon="lucide:archive" width="16" />
            <span>Batch Archive</span>
          </button>

          <button 
            @click="exportSelectedToCsv" 
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-gray-200 rounded-xl text-xs font-semibold transition-all flex items-center gap-2 border border-slate-700"
            title="Export Selected Dockets to CSV"
          >
            <Icon icon="lucide:download" width="16" />
            <span>Export CSV</span>
          </button>

          <button 
            @click="selectedTaskIds = []" 
            class="text-xs text-gray-400 hover:text-white px-2 py-1 transition-colors"
          >
            Clear
          </button>
        </div>
      </div>
    </Transition>
  </main>

  <!-- Executive Docket Schedule Printable Modal (Phase 5) -->
  <Transition name="fade-slide">
    <div 
      v-if="exportScheduleModalOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
      @click.self="exportScheduleModalOpen = false"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Toolbar -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/10 rounded-xl text-blue-300">
              <Icon icon="lucide:printer" width="20" />
            </div>
            <div>
              <h3 class="font-bold text-base text-white">Executive Docket Briefing Schedule</h3>
              <p class="text-xs text-slate-400">Departmental standup & clearance report</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button 
              @click="exportSelectedToCsv" 
              class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold flex items-center gap-1.5 border border-slate-700 transition-colors"
            >
              <Icon icon="lucide:download" width="14" />
              <span>CSV</span>
            </button>
            <button 
              @click="printDocketSchedule" 
              class="px-4 py-1.5 bg-primary hover:bg-blue-600 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer"
            >
              <Icon icon="lucide:printer" width="14" />
              <span>Print Briefing</span>
            </button>
            <button 
              @click="exportScheduleModalOpen = false" 
              class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors ml-2"
            >
              <Icon icon="lucide:x" width="20" />
            </button>
          </div>
        </div>

        <!-- Printable Schedule Sheet Body -->
        <div id="printable-docket-sheet" class="p-8 overflow-y-auto flex-1 bg-white text-slate-900 space-y-6">
          <!-- Institutional Header -->
          <div class="border-b-2 border-slate-900 pb-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Government of Edo State • Electronic Document Management System</span>
              <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase mt-0.5">Workflow Docket Schedule</h1>
              <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-3">
                <span>Department: <strong class="text-slate-900">{{ user?.department?.title || userBranch?.department?.title || 'Edo State Civil Service' }}</strong></span>
                <span>•</span>
                <span>Generated: <strong class="text-slate-900">{{ formattedCurrentDate }}</strong></span>
                <span>•</span>
                <span>Reporting Officer: <strong class="text-slate-900">{{ user?.first_name }} {{ user?.last_name }}</strong></span>
              </div>
            </div>

            <div class="flex items-center sm:flex-col sm:items-end gap-3 shrink-0">
              <div class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono text-right">
                <span class="text-slate-500 text-[11px] block">SLA Compliance</span>
                <strong class="text-base" :class="complianceRate >= 85 ? 'text-emerald-700' : 'text-amber-700'">{{ complianceRate }}%</strong>
              </div>
              <span class="text-xs font-semibold text-slate-600">Total: <strong>{{ currentTaskList.length }}</strong> Dockets</span>
            </div>
          </div>

          <!-- Schedule Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-300 text-slate-800 font-bold uppercase tracking-wider text-[11px]">
                  <th class="py-2.5 px-3">#</th>
                  <th class="py-2.5 px-3">Docket ID</th>
                  <th class="py-2.5 px-3">Subject / Title</th>
                  <th class="py-2.5 px-3">Current Desk</th>
                  <th class="py-2.5 px-3">Priority</th>
                  <th class="py-2.5 px-3">Deadline</th>
                  <th class="py-2.5 px-3">Status</th>
                  <th class="py-2.5 px-3">Tenancy</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 text-slate-800">
                <tr v-for="(item, idx) in currentTaskList" :key="item.id" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-mono text-slate-400">{{ idx + 1 }}</td>
                  <td class="py-2.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ item.tracking_id || item.id }}</td>
                  <td class="py-2.5 px-3 font-semibold text-slate-900 max-w-xs">{{ item.title }}</td>
                  <td class="py-2.5 px-3">
                    <span v-if="item.assignments && item.assignments.length > 0" class="font-medium">
                      {{ item.assignments[0].assigned_to?.first_name }} {{ item.assignments[0].assigned_to?.last_name }}
                    </span>
                    <span v-else class="text-slate-400 italic">Unassigned</span>
                  </td>
                  <td class="py-2.5 px-3 font-bold uppercase text-[10px]" :class="item.priority == 2 ? 'text-rose-600' : 'text-slate-700'">
                    {{ item.priority == 1 ? 'Low' : item.priority == 2 ? 'High' : 'Medium' }}
                  </td>
                  <td class="py-2.5 px-3 whitespace-nowrap" :class="getDueDateStatus(item.due_date, item.status) === 'overdue' ? 'text-rose-600 font-bold' : ''">
                    {{ item.due_date ? formatDate(item.due_date) : '-' }}
                  </td>
                  <td class="py-2.5 px-3 font-semibold capitalize">{{ item.status }}</td>
                  <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[11px]">{{ getTenancyDuration(item)?.label || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Executive Sign-off Block -->
          <div class="pt-8 border-t border-slate-300 grid grid-cols-2 gap-12 text-xs text-slate-700">
            <div>
              <p class="font-bold text-slate-900 mb-6">Prepared by Desk Officer:</p>
              <div class="border-b border-slate-400 w-56 mb-1"></div>
              <p class="font-mono text-[10px] text-slate-500">{{ user?.first_name }} {{ user?.last_name }} • Date: {{ formattedCurrentDate }}</p>
            </div>
            <div class="text-right">
              <p class="font-bold text-slate-900 mb-6">Director / Permanent Secretary Endorsement:</p>
              <div class="border-b border-slate-400 w-56 ml-auto mb-1"></div>
              <p class="font-mono text-[10px] text-slate-500">Executive Signature & Official Stamp</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <AssignTask v-model="AssignModal" />
  <AssignExistingTask v-if="activeAssignTask" v-model="assignModalOpen" :task="activeAssignTask" @assigned="handleTaskAssigned" />
  
  <!-- Hybrid Triage Drawer (Pillar 1) -->
  <TaskTriageDrawer 
    :isOpen="triageDrawerOpen" 
    :task="activeTriageTask || {}" 
    :hasPrev="triageTaskIndex > 0" 
    :hasNext="triageTaskIndex < currentTaskList.length - 1" 
    @close="triageDrawerOpen = false" 
    @prev="navigateTriage(-1)" 
    @next="navigateTriage(1)" 
    @assign="handleDrawerAssign" 
    @quick-route="handleQuickRoute" 
    @submit="handleDrawerSubmit"
    @hold="handleDrawerHold"
    @return="handleDrawerReturn"
    @approve="handleDrawerApprove"
    @request-revision="handleDrawerRequestRevision"
    @refresh="router.reload()"
  />


  <!-- Submit Task Modal from Triage Drawer -->
  <MarkComplete 
    v-if="activeTriageTask && drawerSubmitModalOpen" 
    v-model="drawerSubmitModalOpen" 
    :task="activeTriageTask" 
    :assignedToMe="activeTriageTask.assigned_to_me" 
    :activeDocPath="activeTriageTask.file?.path || (activeTriageTask.taskdocs && activeTriageTask.taskdocs[0]?.path) || ''"
    @refresh="handleDrawerActionDone"
  />

  <!-- Hold / Request Info Modal from Triage Drawer -->
  <RequestInfoModal 
    v-if="activeTriageTask && drawerHoldModalOpen" 
    v-model="drawerHoldModalOpen" 
    :task="activeTriageTask" 
    @refresh="handleDrawerActionDone"
  />

  <!-- Return Task Modal from Triage Drawer -->
  <ReturnTask 
    v-if="activeTriageTask && drawerReturnModalOpen" 
    v-model="drawerReturnModalOpen" 
    :task="activeTriageTask" 
    @refresh="handleDrawerActionDone"
  />

  <!-- Approve Review Modal from Triage Drawer -->
  <ApproveReviewModal 
    v-if="activeTriageTask && drawerApproveModalOpen && subordinateAssignmentUnderReview" 
    v-model="drawerApproveModalOpen" 
    :task="activeTriageTask" 
    :assignment="subordinateAssignmentUnderReview" 
    @confirmed="handleDrawerApproveConfirmed" 
  />

  <!-- Request Revision Modal from Triage Drawer -->
  <RequestRevision 
    v-if="activeTriageTask && drawerRevisionModalOpen" 
    v-model="drawerRevisionModalOpen" 
    :task="activeTriageTask" 
    @refresh="handleDrawerActionDone"
  />

  <!-- Batch Assign Modal (Pillar 2) -->
  <BatchAssignModal 
    v-model="batchAssignModalOpen" 
    :selectedTasks="selectedTasksObjects" 
    :prefillUser="batchPrefillUser" 
    @assigned="handleBatchAssigned" 
  />
</template>

<script setup>
import { ref, computed, defineProps, watch, inject, onMounted, onUpdated } from "vue";
//import paginator from "@/reusables/paginations.vue";
import AssignTask from "../../components/Modals/Workflows/AssignTask.vue";
import AssignExistingTask from "../../components/Modals/Workflows/AssignExistingTask.vue";
import TaskTriageDrawer from "../../components/Workflow/TaskTriageDrawer.vue";
import BatchAssignModal from "../../components/Modals/Workflows/BatchAssignModal.vue";
import MarkComplete from "../../Components/Modals/Workflows/MarkComplete.vue";
import RequestInfoModal from "../../Components/Modals/Workflows/RequestInfoModal.vue";
import AwaitingMyAction from "../../components/Workflow/AwaitingMyAction.vue";
import ReturnTask from "../../Components/Modals/Workflows/ReturnTask.vue";
import ApproveReviewModal from "../../components/Modals/Workflows/ApproveReviewModal.vue";
import RequestRevision from "../../Components/Modals/Workflows/RequestRevision.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import debounce from "lodash.debounce"
import { create } from "lodash";
import axios from "axios";
import Swal from "sweetalert2";
import { usePermissions } from "@/composables/usePermissions";

const { can, hasRole } = usePermissions();

const activeAssignTask = ref(null);
const assignModalOpen = ref(false);

const canReassign = (task) => {
  if (!task) return true;
  const assignments = task.assigned_out_assignments || task.assignments || [];
  if (!assignments || assignments.length === 0) return true; // Initial assignment is allowed
  const latest = assignments[0];
  return (latest.status || '').toLowerCase() === 'completed';
};

const getReassignTitle = (task) => {
  if (!task) return 'Assign Task';
  const assignments = task.assigned_out_assignments || task.assignments || [];
  if (!assignments || assignments.length === 0) return 'Assign Task';
  const latest = assignments[0];
  if ((latest.status || '').toLowerCase() !== 'completed') {
    return 'Task must be approved before reassigning';
  }
  return 'Reassign Task';
};

const openAssignModal = (task) => {
  if (!canReassign(task)) return;
  activeAssignTask.value = task;
  assignModalOpen.value = true;
};

// Batch & Triage State
const selectedTaskIds = ref([]);
const batchAssignModalOpen = ref(false);
const batchPrefillUser = ref(null);

const triageDrawerOpen = ref(false);
const activeTriageTask = ref(null);

const currentTaskList = computed(() => {
  if (assignmentButton.value === 1) return filteredAssignedTask.value;
  if (assignmentButton.value === 2) return filteredTask.value;
  if (assignmentButton.value === 3) return filteredSubmissionsTask.value;
  return filteredAssignedTask.value;
});

const isAllSelected = computed({
  get: () => {
    const list = currentTaskList.value;
    return list.length > 0 && selectedTaskIds.value.length === list.length;
  },
  set: (val) => {
    if (val) {
      selectedTaskIds.value = currentTaskList.value.map(item => item.id);
    } else {
      selectedTaskIds.value = [];
    }
  }
});

const selectedTasksObjects = computed(() => {
  return currentTaskList.value.filter(item => selectedTaskIds.value.includes(item.id));
});

const isAnyCompletedSelected = computed(() => {
  return selectedTasksObjects.value.some(t => (t.status || '').toLowerCase() === 'completed');
});

const triageTaskIndex = computed(() => {
  if (!activeTriageTask.value) return -1;
  return currentTaskList.value.findIndex(t => t.id === activeTriageTask.value.id);
});

const openTriageDrawer = (task) => {
  activeTriageTask.value = task;
  triageDrawerOpen.value = true;
};

const navigateTriage = (direction) => {
  const newIdx = triageTaskIndex.value + direction;
  if (newIdx >= 0 && newIdx < currentTaskList.value.length) {
    activeTriageTask.value = currentTaskList.value[newIdx];
  }
};

const handleDrawerAssign = (task) => {
  openAssignModal(task);
};

const drawerSubmitModalOpen = ref(false);
const drawerHoldModalOpen = ref(false);
const drawerReturnModalOpen = ref(false);
const drawerApproveModalOpen = ref(false);
const drawerRevisionModalOpen = ref(false);

const currentUser = computed(() => page.props.user || {});

const subordinateAssignmentUnderReview = computed(() => {
  if (!activeTriageTask.value || !activeTriageTask.value.assignments) return null;
  const isAgf = (roles.value || []).some(r => r.toLowerCase().includes('super user') || r.toLowerCase().includes('accountant-general'));
  if (isAgf) {
    return activeTriageTask.value.assignments.find(a => a.status === 'under review') || activeTriageTask.value.assignments[0];
  }
  return activeTriageTask.value.assignments.find(a => {
    const isAssigner = a.assigned_by?.id === currentUser.value?.id || a.assigned_by === currentUser.value?.id;
    return isAssigner && (a.status === 'under review' || a.status === 'review');
  }) || activeTriageTask.value.assignments[0];
});

const handleDrawerSubmit = (task) => {
  activeTriageTask.value = task;
  drawerSubmitModalOpen.value = true;
};

const handleDrawerHold = (task) => {
  activeTriageTask.value = task;
  drawerHoldModalOpen.value = true;
};

const handleDrawerReturn = (task) => {
  activeTriageTask.value = task;
  drawerReturnModalOpen.value = true;
};

const handleDrawerApprove = (task) => {
  activeTriageTask.value = task;
  drawerApproveModalOpen.value = true;
};

const isTaskApproved = (item) => {
  if (!item) return false;
  if (item.is_approved) return true;
  const status = (item.status || '').toLowerCase();
  if (status === 'completed') return true;
  const assignments = item.assigned_out_assignments || item.assignments || [];
  if (assignments.length > 0) {
    const latest = assignments[0];
    if ((latest.status || '').toLowerCase() === 'completed') return true;
  }
  return false;
};

const approveTask = (task) => {
  if (isTaskApproved(task)) return;
  handleDrawerApprove(task);
};

const handleDrawerRequestRevision = (task) => {
  activeTriageTask.value = task;
  drawerRevisionModalOpen.value = true;
};

const handleDrawerApproveConfirmed = ({ directive, pin, onDone }) => {
  const assignment = subordinateAssignmentUnderReview.value;
  if (!assignment || !activeTriageTask.value) return;

  router.post('/dashboard/approve-task', {
    task_id: activeTriageTask.value.id,
    task_assignment_id: assignment.id,
    directive: directive,
    approval_pin: pin,
  }, {
    onSuccess: () => {
      if (activeTriageTask.value) {
        activeTriageTask.value.is_approved = true;
      }
      if (onDone) onDone(null);
      drawerApproveModalOpen.value = false;
      triageDrawerOpen.value = false;
      router.reload();
    },
    onError: (errors) => {
      const errorMsg = errors.approval_pin || Object.values(errors)[0] || 'Approval failed. Please verify your PIN.';
      if (onDone) onDone(errorMsg);
    }
  });
};

const handleDrawerActionDone = () => {
  drawerSubmitModalOpen.value = false;
  drawerHoldModalOpen.value = false;
  drawerReturnModalOpen.value = false;
  drawerApproveModalOpen.value = false;
  drawerRevisionModalOpen.value = false;
  triageDrawerOpen.value = false;
  router.reload();
};

const openBatchAssignModal = (prefill = null) => {
  batchPrefillUser.value = prefill;
  batchAssignModalOpen.value = true;
};

const handleQuickRoute = ({ task, suggested }) => {
  batchPrefillUser.value = suggested;
  selectedTaskIds.value = [task.id];
  batchAssignModalOpen.value = true;
};

const handleBatchAssigned = () => {
  selectedTaskIds.value = [];
  batchAssignModalOpen.value = false;
  router.reload();
};

const handleTaskAssigned = () => {
  assignModalOpen.value = false;
  router.reload();
};

const handleBatchArchive = async () => {
  if (!selectedTaskIds.value || selectedTaskIds.value.length === 0) return;

  const result = await Swal.fire({
    title: 'Archive Selected Tasks?',
    text: `Are you sure you want to archive ${selectedTaskIds.value.length} selected tasks?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d97706',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, Archive Tasks',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    router.post('/dashboard/batch-archive-tasks', {
      task_ids: selectedTaskIds.value
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedTaskIds.value = [];
        Swal.fire({
          icon: 'success',
          title: 'Tasks Archived',
          text: 'Selected tasks have been archived successfully.',
          timer: 2000,
          showConfirmButton: false
        });
      }
    });
  }
};

const AssignModal = ref(false)
const assignmentButton = ref(1);
const viewMode = ref("table");
const buttonToggle = ref(1);
const currentPage = ref(1);
const itemsPerPage = 10;
const tasks = ref([])
const page = usePage()
const search = ref('')
const props = defineProps(['created_task', 'assigned_task', 'submissions_task', 'branchType', 'assignedTaskStats', 'createdTaskStats', 'submissionTaskStats'])

const activeTaskStats = computed(() => {
  if (assignmentButton.value === 1) return props.assignedTaskStats;
  if (assignmentButton.value === 2) return props.createdTaskStats;
  if (assignmentButton.value === 3) return props.submissionTaskStats;
  return props.assignedTaskStats;
});

const roles = computed(()=> page.props.roles)
const user = computed(()=> page.props.user)
const userBranch = computed(() => page.props.userBranch)
const branchName = computed(() => page.props.branchName)

const checkUserRole = (userObj, roleName) => {
    return userObj && userObj.roles && userObj.roles.some(r => r.name === roleName);
}

// Phase 1: Executive Welcome Deck & KPI Computed Properties
const greetingText = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good morning';
  if (hour < 17) return 'Good afternoon';
  return 'Good evening';
});

const formattedCurrentDate = computed(() => {
  return new Intl.DateTimeFormat('en-GB', { 
    weekday: 'short', 
    day: 'numeric', 
    month: 'short', 
    year: 'numeric' 
  }).format(new Date());
});

const complianceRate = computed(() => {
  if (!activeTaskStats.value) return 100;
  const completed = Number(activeTaskStats.value.completed || 0);
  const overdue = Number(activeTaskStats.value.overdue || 0);
  const totalRelevant = completed + overdue;
  if (totalRelevant === 0) return 100;
  return Math.min(100, Math.round((completed / totalRelevant) * 100));
});

const slaStatusBadge = computed(() => {
  const overdue = Number(activeTaskStats.value?.overdue || 0);
  const dueSoon = Number(activeTaskStats.value?.due_soon || 0);
  if (overdue > 0) {
    return {
      text: `${overdue} Overdue Docket${overdue > 1 ? 's' : ''} Require Escalation`,
      class: 'bg-rose-500/15 text-rose-300 border-rose-500/30',
      dotClass: 'bg-rose-400 animate-ping',
      icon: 'lucide:alert-triangle'
    };
  }
  if (dueSoon > 0) {
    return {
      text: `${dueSoon} Docket${dueSoon > 1 ? 's' : ''} Approaching SLA Deadline`,
      class: 'bg-amber-500/15 text-amber-300 border-amber-500/30',
      dotClass: 'bg-amber-400',
      icon: 'lucide:clock'
    };
  }
  return {
    text: 'Operations Healthy & On Track',
    class: 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
    dotClass: 'bg-emerald-400',
    icon: 'lucide:check-circle-2'
  };
});

const handleKpiCardClick = (statKey) => {
  if (statKey === 'total') {
    activeButton('');
  } else if (statKey === 'pending') {
    if (buttonSwitch.value === 2) {
      activeButton('');
    } else {
      activeButton('Pending');
    }
  } else if (statKey === 'due_soon') {
    if (buttonSwitch.value === 8) {
      activeButton('');
    } else {
      activeButton('Due_Soon');
    }
  } else if (statKey === 'overdue') {
    if (buttonSwitch.value === 9) {
      activeButton('');
    } else {
      activeButton('Overdue');
    }
  } else if (statKey === 'completed') {
    if (buttonSwitch.value === 5) {
      activeButton('');
    } else {
      activeButton('Completed');
    }
  }
};

// Phase 2: Semantic Sub-Filter Deck & Contextual Discussions Toggle
const hasDiscussionsOnly = ref(false);

const activeBucket = computed(() => {
  if (buttonSwitch.value === 1) return 'all';
  if (buttonSwitch.value === 2 || buttonSwitch.value === 3) return 'active';
  if (buttonSwitch.value === 4 || buttonSwitch.value === 6 || buttonSwitch.value === 7) return 'review';
  if (buttonSwitch.value === 8 || buttonSwitch.value === 9 || buttonSwitch.value === 10) return 'critical';
  if (buttonSwitch.value === 5) return 'completed';
  return 'all';
});

const bucketCounts = computed(() => {
  const stats = activeTaskStats.value || {};
  return {
    all: stats.total || 0,
    active: (stats.pending || 0) + (stats.in_progress || 0),
    review: (stats.review || 0) + (stats.returned || 0) + (stats.uncompleted || 0),
    critical: (stats.overdue || 0) + (stats.due_soon || 0) + (stats.on_hold || 0),
    completed: stats.completed || 0
  };
});

const setBucket = (bucket) => {
  if (bucket === 'all') {
    activeButton('');
  } else if (bucket === 'active') {
    activeButton('Pending');
  } else if (bucket === 'review') {
    activeButton('Under Review');
  } else if (bucket === 'critical') {
    if ((activeTaskStats.value?.overdue || 0) > 0) {
      activeButton('Overdue');
    } else {
      activeButton('Due_Soon');
    }
  } else if (bucket === 'completed') {
    activeButton('Completed');
  }
};

// Phase 3 & 4: Administrative Multi-Stage Kanban Pipeline & Tenancy Tracking
const getTenancyDuration = (task) => {
  if (!task) return null;
  const targetDate = task.updated_at || task.created_at;
  if (!targetDate) return null;
  const then = new Date(targetDate);
  const now = new Date();
  const diffMs = now - then;
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  
  if (diffDays > 0) {
    return {
      label: `${diffDays}d at desk`,
      isStagnant: diffDays >= 2
    };
  }
  if (diffHours > 0) {
    return {
      label: `${diffHours}h at desk`,
      isStagnant: false
    };
  }
  return {
    label: 'Just now',
    isStagnant: false
  };
};

const kanbanPipeline = computed(() => {
  const all = currentTaskList.value || [];
  
  const intake = all.filter(t => (t.status || '').toLowerCase() === 'pending');
  const processing = all.filter(t => (t.status || '').toLowerCase() === 'in progress');
  const review = all.filter(t => ['under review', 'returned', 'uncompleted'].includes((t.status || '').toLowerCase()));
  const cleared = all.filter(t => (t.status || '').toLowerCase() === 'completed');

  return [
    {
      id: 'intake',
      title: 'Intake / New',
      subtitle: 'Awaiting initial action',
      icon: 'lucide:inbox',
      badgeClass: 'bg-slate-200 text-slate-800',
      headerBorder: 'border-slate-300',
      bgClass: 'bg-slate-50/70',
      tasks: intake,
      overdueCount: intake.filter(t => getDueDateStatus(t.due_date, t.status) === 'overdue').length
    },
    {
      id: 'processing',
      title: 'In Processing',
      subtitle: 'Active drafting & treatment',
      icon: 'lucide:play-circle',
      badgeClass: 'bg-blue-100 text-blue-800',
      headerBorder: 'border-blue-400',
      bgClass: 'bg-blue-50/40',
      tasks: processing,
      overdueCount: processing.filter(t => getDueDateStatus(t.due_date, t.status) === 'overdue').length
    },
    {
      id: 'review',
      title: 'Review & Clearance',
      subtitle: 'Awaiting endorsement',
      icon: 'lucide:file-check',
      badgeClass: 'bg-purple-100 text-purple-800',
      headerBorder: 'border-purple-400',
      bgClass: 'bg-purple-50/40',
      tasks: review,
      overdueCount: review.filter(t => getDueDateStatus(t.due_date, t.status) === 'overdue').length
    },
    {
      id: 'cleared',
      title: 'Cleared & Dispatched',
      subtitle: 'Finished & archived',
      icon: 'lucide:check-circle-2',
      badgeClass: 'bg-emerald-100 text-emerald-800',
      headerBorder: 'border-emerald-400',
      bgClass: 'bg-emerald-50/40',
      tasks: cleared,
      overdueCount: 0
    }
  ];
});

// Phase 4 & 5: Priority Nudge & Executive Docket Schedule Export
const exportScheduleModalOpen = ref(false);

const printDocketSchedule = () => {
  window.print();
};

const exportSelectedToCsv = () => {
  const tasksToExport = selectedTasksObjects.value.length > 0 ? selectedTasksObjects.value : currentTaskList.value;
  if (!tasksToExport || tasksToExport.length === 0) {
    Swal.fire({
      icon: 'info',
      title: 'No Dockets Available',
      text: 'There are no dockets in the current view to export.',
      confirmButtonColor: '#3b82f6',
    });
    return;
  }

  const headers = ['Tracking ID', 'Title', 'Origin', 'Assigned Officer', 'Department', 'Status', 'Priority', 'Due Date', 'Tenancy'];
  const rows = tasksToExport.map(t => {
    const officer = t.assignments?.[0]?.assigned_to?.first_name 
      ? `${t.assignments[0].assigned_to.first_name} ${t.assignments[0].assigned_to.last_name || ''}`
      : 'Unassigned';
    const dept = t.assignments?.[0]?.assigned_to?.department?.title || '-';
    const origin = t.mail_id ? 'Correspondence Mail' : (t.file_id ? 'Registry File' : 'Standard Task');
    const priority = t.priority == 2 ? 'High' : (t.priority == 3 ? 'Medium' : 'Low');
    const tenancy = getTenancyDuration(t)?.label || '-';
    
    return [
      `"${t.tracking_id || t.id}"`,
      `"${(t.title || '').replace(/"/g, '""')}"`,
      `"${origin}"`,
      `"${officer}"`,
      `"${dept}"`,
      `"${t.status || '-'}"`,
      `"${priority}"`,
      `"${t.due_date ? formatDate(t.due_date) : '-'}"`,
      `"${tenancy}"`
    ];
  });

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `docket_schedule_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const nudgeOfficer = async (task) => {
  if (!task) return;
  const targetOfficer = task.assignments?.[0]?.assigned_to || task.assigned_out_assignments?.[0]?.assigned_to;
  if (!targetOfficer) {
    Swal.fire({
      icon: 'warning',
      title: 'No Assigned Officer',
      text: 'No assigned officer was found to nudge for this docket.',
      confirmButtonColor: '#3b82f6',
    });
    return;
  }
  
  const officerName = `${targetOfficer.first_name || ''} ${targetOfficer.last_name || ''}`.trim() || 'Desk Officer';
  const dwell = getTenancyDuration(task)?.label || 'several days';
  
  const result = await Swal.fire({
    title: 'Send Priority Nudge?',
    html: `
      <div class="text-left text-sm text-slate-600 space-y-3">
        <p>Send an official executive priority reminder to <b>${officerName}</b> for:</p>
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 text-xs">
          ${task.title}
        </div>
        <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200 font-medium">
          <span>⚡ Dwell duration at desk: <b>${dwell}</b></span>
        </div>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#d97706',
    cancelButtonColor: '#64748b',
    confirmButtonText: '⚡ Send Priority Nudge',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    try {
      const res = await axios.post('/dashboard/task-comment', {
        task_id: task.id,
        comment_to: targetOfficer.id,
        comment: `⚡ PRIORITY NUDGE: Docket "${task.title}" (${task.tracking_id || 'ID: ' + task.id}) has been at your desk for ${dwell}. Please attend to this matter for expedited clearance.`
      });

      if (res.data?.status === 'success' || res.status === 200) {
        Swal.fire({
          icon: 'success',
          title: 'Priority Nudge Delivered',
          text: `Official priority reminder successfully sent to ${officerName}.`,
          timer: 2500,
          showConfirmButton: false
        });
      } else {
        throw new Error(res.data?.message || 'Failed to deliver reminder.');
      }
    } catch (err) {
      console.error('Nudge delivery error:', err);
      Swal.fire({
        icon: 'error',
        title: 'Delivery Failed',
        text: err.response?.data?.message || err.message || 'Could not deliver priority nudge.',
        confirmButtonColor: '#ef4444'
      });
    }
  }
};

const notifications = inject('notifications')
const created_task = ref([])
const assigned_task = ref([])
const submissions_task = ref([])

const buttonSwitch = ref(1);
const clickedButton = ref("");

const getReviewSubmitter = (task) => {
  if (!task || !task.assignments || task.assignments.length === 0) return null;
  const mySub = task.assignments.find(a => (a.assigned_by === currentUser.value?.id || a.assigned_by?.id === currentUser.value?.id) && a.status === 'under review');
  if (mySub && mySub.assigned_to) return mySub.assigned_to;
  const anyUnderReview = task.assignments.find(a => a.status === 'under review');
  if (anyUnderReview && anyUnderReview.assigned_to) return anyUnderReview.assigned_to;
  return task.assignments[0]?.assigned_to || null;
};

const setAssignmentTab = (tabNumber) => {
  assignmentButton.value = tabNumber;
  buttonSwitch.value = 1;
  clickedButton.value = "";
  try {
    const url = new URL(window.location.href);
    url.searchParams.set('assigned_btn', tabNumber);
    url.searchParams.delete('status');
    window.history.replaceState({}, '', url.toString());
  } catch (e) {}
  router.get('/dashboard/workflow', { 
    assigned_btn: tabNumber 
  }, { 
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['created_task', 'assigned_task', 'submissions_task', 'assignedTaskStats', 'createdTaskStats', 'submissionTaskStats']
  });
};

const assignedByMeButton = () => setAssignmentTab(1);
const assignedToMeButton = () => setAssignmentTab(2);
const submissionsButton = () => setAssignmentTab(3);

const activeButton = (event) => {
  if (event == "") {
    buttonSwitch.value = 1;
    taskByStatus("")
  }
  if (event == "Pending") {
    buttonSwitch.value = 2;
    taskByStatus('pending');
  }
  if (event == "In Progress") {
    buttonSwitch.value = 3;
    taskByStatus('in progress')
  }
  if (event == "Under Review") {
    buttonSwitch.value = 4;
    taskByStatus('under review')
  }
  if (event == "Completed") {
    buttonSwitch.value = 5;
    taskByStatus('completed')
  }
  if (event == "Uncompleted") {
    buttonSwitch.value = 6;
    taskByStatus('uncompleted')
  }
  if (event == "Returned") {
    buttonSwitch.value = 7;
    taskByStatus('returned')
  }
  if (event == "Due_Soon") {
    buttonSwitch.value = 8;
    taskByStatus('due_soon');
  }
  if (event == "Overdue") {
    buttonSwitch.value = 9;
    taskByStatus('overdue');
  }
  if (event == "On_Hold") {
    buttonSwitch.value = 10;
    taskByStatus('on_hold');
  }
  clickedButton.value = event;
};

const isRole = (role)=>{
  if(roles.value.includes(role)){
    return true
  }else{
    return false
  }
}

const taskByStatus = (status)=>{
  let type = assignmentButton.value == 1 ? 'assigned_to_me' : (assignmentButton.value == 3 ? 'submissions' : 'assigned_by_me');
  // axios.get('/dashboard/task-by-status',{params:{status: status, type: type}}).then((response)=>{
  //   tasks.value = response.data.data;
  // })
  router.get('/dashboard/workflow', {status: status.toLowerCase(), assigned_btn: assignmentButton.value}, {
    preserveState: true,
    replace: true,
    only: ['created_task', 'assigned_task', 'submissions_task']
  })
}

const formatDate =(inputDate)=> {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

    return formattedDate;
}

const getDueDateStatus = (dueDate, status) => {
  if (status && status.toLowerCase() === 'completed') return 'completed';
  if (!dueDate) return 'none';
  const due = new Date(dueDate);
  due.setHours(0,0,0,0);
  const today = new Date();
  today.setHours(0,0,0,0);
  const diffTime = due - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) return 'overdue';
  if (diffDays <= 2) return 'soon';
  return 'ok';
}

const getSlaBadge = (dueDate, status) => {
  if (status && status.toLowerCase() === 'completed') {
    return {
      type: 'completed',
      label: 'Completed',
      class: 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold',
      dot: 'bg-emerald-500'
    };
  }
  if (!dueDate) {
    return {
      type: 'none',
      label: '-',
      class: 'text-gray-400 font-medium',
      dot: ''
    };
  }

  const due = new Date(dueDate);
  due.setHours(23, 59, 59, 999);
  const now = new Date();
  const diffMs = due - now;
  const diffHours = Math.round(diffMs / (1000 * 60 * 60));
  const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

  if (diffMs < 0) {
    const overdueDays = Math.abs(diffDays);
    return {
      type: 'overdue',
      label: overdueDays === 0 ? 'Overdue Today' : `Overdue (${overdueDays}d ago)`,
      class: 'bg-red-50 text-red-600 border border-red-200 font-bold animate-pulse shadow-2xs',
      dot: 'bg-red-600'
    };
  }
  if (diffHours <= 24) {
    return {
      type: 'urgent',
      label: `Due in ${Math.max(1, diffHours)}h`,
      class: 'bg-amber-50 text-amber-700 border border-amber-200 font-semibold shadow-2xs',
      dot: 'bg-amber-500'
    };
  }
  if (diffDays <= 3) {
    return {
      type: 'soon',
      label: `Due in ${diffDays}d`,
      class: 'bg-blue-50 text-blue-700 border border-blue-200 font-medium',
      dot: 'bg-blue-500'
    };
  }
  return {
    type: 'ok',
    label: formatDate(dueDate),
    class: 'text-gray-700 font-normal',
    dot: 'bg-gray-400'
  };
};

const filteredItems = computed(() => {
  if (clickedButton.value === '') {
    return items.value;
  }
  return items.value.filter(item =>
    item.status.toLowerCase() === clickedButton.value.toLowerCase()
  );
});

const filteredTask = computed(() => {
  const base = Array.isArray(created_task.value) ? created_task.value : (created_task.value?.data || []);
  if (!hasDiscussionsOnly.value) return base;
  return base.filter(item => (item.comments_count || 0) > 0);
});

const filteredAssignedTask = computed(() => {
  const base = Array.isArray(assigned_task.value) ? assigned_task.value : (assigned_task.value?.data || []);
  if (!hasDiscussionsOnly.value) return base;
  return base.filter(item => (item.comments_count || 0) > 0);
});

const filteredSubmissionsTask = computed(() => {
  const base = Array.isArray(submissions_task.value) ? submissions_task.value : (submissions_task.value?.data || []);
  if (!hasDiscussionsOnly.value) return base;
  return base.filter(item => (item.comments_count || 0) > 0);
});
const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return filteredItems.value.slice(startIndex, endIndex);
});

watch(()=>props.created_task, ()=>{

},{deep: true})


// watch(assignmentButton, (newVal)=>{
//   buttonSwitch.value = 1;
//   // if(newVal == 1){
//   //   router.reload({only: ['created_at']})
//   // }else{
//   //   router.reload({only:['assigned_task']})
//   // }
// })

watch(notifications, (newVal)=>{
  if(newVal.Today && newVal.Today[0].data.task){
      router.reload({ only: ['assigned_task', 'submissions_task'] })
    }
})

const getSearchResult = debounce((event)=>{
   const params = new URLSearchParams(window.location.search)
  const val = params.get('assigned_btn');
  let search_value = event.target.value;
  // axios.get('/dashboard/workflow',{params:{search:search_value, assigned_btn: val}}).then((response)=>{
  //   created_task.value = response.data.created_task
  //   assigned_task.value = response.data.assigned_task
  // })
  router.get('/dashboard/workflow',{search: search_value, assigned_btn: val}, {preserveState: true, replace: true})
}, 500)

const addToURLParam = ()=>{
  const params = new URLSearchParams(window.location.search)
  const val = params.get('assigned_btn');
  assignmentButton.value = val ? parseInt(val) : 1;
}

watch(()=>props.assigned_task, ()=>{
  assigned_task.value = props.assigned_task
},{immediate:true})

watch(()=>props.created_task, ()=>{
  created_task.value = props.created_task
},{immediate:true})

watch(()=>props.submissions_task, ()=>{
  submissions_task.value = props.submissions_task
},{immediate:true})

onMounted(()=>{
  const params = new URLSearchParams(window.location.search);
  const val = params.get('assigned_btn');
  if(val){
    assignmentButton.value = parseInt(val);
  }else{
    assignmentButton.value = 1; // Default to Assigned to me
  }

  const status = params.get('status');
  if(status == 'in progress'){
    //activeButton(status.charAt(0).toUpperCase() + status.slice(1))
    buttonSwitch.value = 3;
  }
  if(status == 'under review'){
   // activeButton(status.charAt(0).toUpperCase() + status.slice(1))
    buttonSwitch.value = 4;
  }
  if(status == 'completed'){
   // activeButton(status.charAt(0).toUpperCase() + status.slice(1))
    buttonSwitch.value = 5;
  }
  if(status == 'uncompleted'){
    //activeButton(status.charAt(0).toUpperCase() + status.slice(1))
    buttonSwitch.value = 6;
  }
  if(status == 'pending'){
   // activeButton(status.charAt(0).toUpperCase() + status.slice(1))
    buttonSwitch.value = 2;
  }
  if(!status){
    buttonSwitch.value = 1;
   // taskByStatus('')
  }
  window.Echo.private('App.Models.User.' + user.value.id)
      .notification((notification) => {
          router.reload({ only: ['assigned_task', 'submissions_task'] })
      });
})

</script>

<style scoped>
.activeButton {
  background-color: white;
  border-radius: 5px;
  color: black;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translate(-50%, 20px);
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-docket-sheet,
  #printable-docket-sheet * {
    visibility: visible;
  }
  #printable-docket-sheet {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 1.5rem !important;
    background: white !important;
    color: black !important;
  }
}
</style>
