<template>
  <div class="min-h-screen bg-slate-50/50 pb-16">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200/80 px-5 lg:px-8 py-6 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
            <Icon icon="solar:lifebuoy-bold-duotone" width="28" />
          </div>
          <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Helpdesk & Support Center</h1>
            <p class="text-sm text-slate-500 mt-0.5">Submit technical tickets, browse documentation SOPs, and track issue resolutions.</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
          <button
            @click="helpModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue_primary hover:bg-blue_primary/90 text-white font-medium text-sm shadow-sm transition-all hover:shadow active:scale-[0.98]"
          >
            <Icon icon="solar:ticket-bold-duotone" width="18" />
            <span>Submit Support Ticket</span>
          </button>

          <button
            @click="supportModal = true"
            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-sm shadow-2xs transition-all active:scale-[0.98]"
          >
            <Icon icon="solar:phone-calling-bold-duotone" width="18" class="text-slate-500" />
            <span>Contact Info</span>
          </button>

          <button
            v-if="canManageFaqs"
            @click="openAddFaqModal"
            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-medium text-sm transition-all"
          >
            <Icon icon="solar:question-square-bold-duotone" width="18" class="text-blue-600" />
            <span>+ FAQ</span>
          </button>

          <button
            v-if="canManageDocs"
            @click="openAddDocModal"
            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 border border-slate-200 text-slate-700 font-medium text-sm transition-all"
          >
            <Icon icon="solar:document-add-bold-duotone" width="18" class="text-indigo-600" />
            <span>+ SOP/Doc</span>
          </button>
        </div>
      </div>

      <!-- Telemetry KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-6">
        <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Tickets</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ stats.total }}</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-blue-700 flex items-center justify-center">
            <Icon icon="solar:inbox-line-bold-duotone" width="22" />
          </div>
        </div>

        <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Open & Queued</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ stats.open }}</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-amber-100/70 text-amber-700 flex items-center justify-center">
            <Icon icon="solar:clock-circle-bold-duotone" width="22" />
          </div>
        </div>

        <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">In Progress</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ stats.in_progress }}</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-indigo-100/70 text-indigo-700 flex items-center justify-center">
            <Icon icon="solar:wrench-bold-duotone" width="22" />
          </div>
        </div>

        <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Resolved</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.resolved }}</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-700 flex items-center justify-center">
            <Icon icon="solar:check-circle-bold-duotone" width="22" />
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="px-5 lg:px-8 mt-6">
      <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button
          @click="activeTab = 'faqs'"
          :class="[
            'flex items-center gap-2 px-4 py-3 font-medium text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer',
            activeTab === 'faqs'
              ? 'border-blue_primary text-blue_primary font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          <Icon icon="solar:question-square-bold-duotone" width="18" />
          <span>Knowledge Base & FAQs</span>
          <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-semibold">
            {{ props.faqs?.length || 0 }}
          </span>
        </button>

        <button
          @click="activeTab = 'my_tickets'"
          :class="[
            'flex items-center gap-2 px-4 py-3 font-medium text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer',
            activeTab === 'my_tickets'
              ? 'border-blue_primary text-blue_primary font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          <Icon icon="solar:ticket-bold-duotone" width="18" />
          <span>My Support Tickets</span>
          <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
            {{ props.myTickets?.length || 0 }}
          </span>
        </button>

        <button
          @click="activeTab = 'docs'"
          :class="[
            'flex items-center gap-2 px-4 py-3 font-medium text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer',
            activeTab === 'docs'
              ? 'border-blue_primary text-blue_primary font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          <Icon icon="solar:document-text-bold-duotone" width="18" />
          <span>SOPs & Official Guides</span>
          <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-semibold">
            {{ props.documentations?.length || 0 }}
          </span>
        </button>

        <button
          v-if="canDispatch"
          @click="activeTab = 'admin_triage'"
          :class="[
            'flex items-center gap-2 px-4 py-3 font-medium text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer relative',
            activeTab === 'admin_triage'
              ? 'border-amber-500 text-amber-700 font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          <Icon icon="solar:shield-user-bold-duotone" width="18" class="text-amber-600" />
          <span>IT Helpdesk Dispatcher</span>
          <span
            v-if="stats.open > 0"
            class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-bold animate-pulse"
          >
            {{ stats.open }} Pending
          </span>
          <span v-else class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-semibold">
            {{ rawAdminTickets.length }}
          </span>
        </button>
      </div>
    </div>

    <!-- TAB 1: KNOWLEDGE BASE & FAQS -->
    <div v-if="activeTab === 'faqs'" class="px-5 lg:px-8 mt-6">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main FAQs Column -->
        <div class="lg:col-span-8 space-y-4">
          <!-- Search & Filter Bar -->
          <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
            <div class="relative">
              <Icon icon="solar:magnifer-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="faqSearch"
                type="text"
                placeholder="Search solutions, common errors, or guidelines..."
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary outline-none transition-all"
              />
              <button
                v-if="faqSearch"
                @click="faqSearch = ''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
              >
                <Icon icon="solar:close-circle-bold" width="16" />
              </button>
            </div>

            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
              <button
                v-for="cat in faqCategories"
                :key="cat.value"
                @click="selectedFaqCategory = cat.value"
                :class="[
                  'px-3 py-1.5 rounded-lg font-medium transition-colors whitespace-nowrap cursor-pointer',
                  selectedFaqCategory === cat.value
                    ? 'bg-blue_primary text-white'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'
                ]"
              >
                {{ cat.label }}
              </button>
            </div>
          </div>

          <!-- FAQ Accordion List -->
          <div v-if="filteredFaqs.length > 0" class="space-y-3">
            <div
              v-for="item in filteredFaqs"
              :key="item.id"
              class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-2xs transition-all hover:border-slate-300"
            >
              <div
                class="p-4 flex items-start justify-between gap-4 cursor-pointer select-none"
                @click="toggleFaq(item.id)"
              >
                <div class="flex items-start gap-3">
                  <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <Icon icon="solar:question-circle-bold-duotone" width="16" />
                  </div>
                  <div>
                    <span class="inline-block px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-md bg-slate-100 text-slate-600 mb-1">
                      {{ formatCategory(item.category) }}
                    </span>
                    <h3 class="font-semibold text-slate-800 text-sm leading-snug">{{ item.question }}</h3>
                  </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                  <div v-if="canUpdateSupport || canDeleteSupportItems" class="flex items-center gap-1 mr-1" @click.stop>
                    <button
                      v-if="canUpdateSupport"
                      @click="openEditFaqModal(item)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                      title="Edit FAQ"
                    >
                      <Icon icon="solar:pen-bold-duotone" width="16" />
                    </button>
                    <button
                      v-if="canDeleteSupportItems"
                      @click="confirmDeleteFaq(item)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                      title="Delete FAQ"
                    >
                      <Icon icon="solar:trash-bin-trash-bold-duotone" width="16" />
                    </button>
                  </div>

                  <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                    <Icon
                      :icon="expandedFaqs.includes(item.id) ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold'"
                      width="14"
                    />
                  </div>
                </div>
              </div>

              <!-- Answer body -->
              <div
                v-if="expandedFaqs.includes(item.id)"
                class="px-4 pb-4 pt-1 border-t border-slate-100 text-slate-600 text-sm leading-relaxed bg-slate-50/40"
              >
                <div class="pl-10 whitespace-pre-line">{{ item.answer }}</div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-else
            class="bg-white rounded-2xl border border-slate-200/90 p-12 text-center shadow-2xs"
          >
            <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
              <Icon icon="solar:magnifer-broken" width="28" />
            </div>
            <h3 class="text-base font-semibold text-slate-700">No matching questions found</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Try refining your search terms or submit a direct support ticket to our helpdesk team.</p>
            <button
              @click="helpModal = true"
              class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue_primary text-white text-xs font-medium shadow-sm hover:bg-blue_primary/90"
            >
              <Icon icon="solar:ticket-bold-duotone" width="16" />
              <span>Submit Ticket Now</span>
            </button>
          </div>
        </div>

        <!-- Right Side Contact & Escalation Box -->
        <div class="lg:col-span-4 space-y-4">
          <!-- Live Support Card -->
          <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-blue-500/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white mb-4">
              <Icon icon="solar:headset-bold-duotone" width="22" />
            </div>

            <h3 class="text-base font-bold text-white tracking-tight">Need Immediate IT Support?</h3>
            <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
              Our technical helpdesk operates Monday through Friday to resolve system outages and user escalations.
            </p>

            <div class="mt-4 space-y-2.5 text-xs text-slate-200 border-t border-white/10 pt-4">
              <div class="flex items-center gap-2.5">
                <Icon icon="solar:letter-bold-duotone" width="16" class="text-blue-400 flex-shrink-0" />
                <span class="font-medium truncate">{{ props.supportInfo?.email || 'support@ansel.com' }}</span>
              </div>
              <div class="flex items-center gap-2.5">
                <Icon icon="solar:phone-calling-bold-duotone" width="16" class="text-emerald-400 flex-shrink-0" />
                <span>{{ props.supportInfo?.phone_number || '+234 90 73929401' }}</span>
              </div>
              <div class="flex items-center gap-2.5">
                <Icon icon="solar:clock-circle-bold-duotone" width="16" class="text-amber-400 flex-shrink-0" />
                <span>
                  {{ props.supportInfo?.start_day || 'Mon' }} - {{ props.supportInfo?.end_day || 'Fri' }}
                  ({{ formatTime(props.supportInfo?.start_time) }} - {{ formatTime(props.supportInfo?.end_time) }})
                </span>
              </div>
              <div class="flex items-center gap-2.5">
                <Icon icon="solar:map-point-bold-duotone" width="16" class="text-rose-400 flex-shrink-0" />
                <span class="truncate">{{ props.supportInfo?.location || 'Head Office, Corporate HQ' }}</span>
              </div>
            </div>

            <button
              @click="helpModal = true"
              class="w-full mt-5 py-2.5 px-4 rounded-xl bg-white text-slate-900 font-semibold text-xs hover:bg-slate-100 transition-colors shadow-sm flex items-center justify-center gap-2"
            >
              <Icon icon="solar:ticket-bold-duotone" width="16" class="text-blue_primary" />
              <span>Create New Ticket</span>
            </button>
          </div>

          <!-- Quick Tip Card -->
          <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-5 text-blue-950">
            <div class="flex items-start gap-3">
              <Icon icon="solar:info-circle-bold-duotone" width="22" class="text-blue_primary flex-shrink-0 mt-0.5" />
              <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900">Pro-Tip for Faster Resolution</h4>
                <p class="text-xs text-blue-800/80 mt-1 leading-relaxed">
                  When submitting a ticket, attaching a full-screen screenshot showing the error message and browser URL helps our engineers diagnose issues significantly faster.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: MY SUPPORT TICKETS -->
    <div v-if="activeTab === 'my_tickets'" class="px-5 lg:px-8 mt-6">
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <!-- Controls Header -->
        <div class="p-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/40">
          <div class="flex items-center gap-3">
            <h2 class="text-base font-bold text-slate-800">My Logged Tickets</h2>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
              {{ myFilteredTickets.length }} Records
            </span>
          </div>

          <div class="flex items-center gap-2.5">
            <div class="relative w-full sm:w-64">
              <Icon icon="solar:magnifer-linear" width="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="myTicketSearch"
                type="text"
                placeholder="Search ticket # or subject..."
                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary outline-none"
              />
            </div>

            <select
              v-model="myTicketStatusFilter"
              class="text-xs border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white outline-none focus:ring-2 focus:ring-blue_primary/20"
            >
              <option value="all">All Statuses</option>
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>
        </div>

        <!-- Tickets Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Ticket No</th>
                <th class="py-3 px-4">Category</th>
                <th class="py-3 px-4">Subject & Description</th>
                <th class="py-3 px-4">Priority</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Assigned To</th>
                <th class="py-3 px-4">Submitted</th>
                <th class="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="ticket in myFilteredTickets"
                :key="ticket.id"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <!-- Ticket No -->
                <td class="py-3.5 px-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span>{{ ticket.ticket_no }}</span>
                    <button
                      @click="copyText(ticket.ticket_no)"
                      class="text-slate-400 hover:text-slate-600 p-0.5 rounded"
                      title="Copy Ticket ID"
                    >
                      <Icon icon="solar:copy-bold-duotone" width="14" />
                    </button>
                  </div>
                </td>

                <!-- Category -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                    {{ formatCategory(ticket.category) }}
                  </span>
                </td>

                <!-- Subject & Description -->
                <td class="py-3.5 px-4 max-w-xs">
                  <div class="font-semibold text-slate-800 truncate">{{ ticket.subject }}</div>
                  <div class="text-[11px] text-slate-400 truncate mt-0.5">{{ ticket.description }}</div>
                </td>

                <!-- Priority -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full uppercase',
                      priorityClass(ticket.priority)
                    ]"
                  >
                    {{ ticket.priority }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-semibold rounded-full',
                      statusClass(ticket.status)
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(ticket.status)"></span>
                    {{ formatStatus(ticket.status) }}
                  </span>
                </td>

                <!-- Assigned To -->
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                  <div v-if="ticket.assigned_to" class="flex items-center gap-1.5">
                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px] flex items-center justify-center">
                      {{ (ticket.assigned_to?.name || 'IT').charAt(0) }}
                    </div>
                    <span>{{ ticket.assigned_to?.name || 'Assigned' }}</span>
                  </div>
                  <span v-else class="text-slate-400 italic">Unassigned</span>
                </td>

                <!-- Submitted Date -->
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                  {{ formatDate(ticket.created_at) }}
                </td>

                <!-- Action -->
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <button
                    @click="viewTicketDetails(ticket)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                  >
                    <Icon icon="solar:eye-bold-duotone" width="15" />
                    <span>View</span>
                  </button>
                </td>
              </tr>

              <tr v-if="myFilteredTickets.length === 0">
                <td colspan="8" class="py-12 text-center text-slate-400">
                  <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <Icon icon="solar:ticket-broken" width="24" />
                  </div>
                  <p class="font-medium text-slate-600">No support tickets found</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Submit your first support ticket to track technical assistance.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 3: SOPS & DOCUMENTATION -->
    <div v-if="activeTab === 'docs'" class="px-5 lg:px-8 mt-6">
      <!-- Search & Filter -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <Icon icon="solar:magnifer-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="docSearch"
            type="text"
            placeholder="Search documentation, manual guides, SOPs..."
            class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary outline-none transition-all"
          />
        </div>

        <button
          v-if="canManageDocs"
          @click="openAddDocModal"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue_primary text-white text-xs font-semibold hover:bg-blue_primary/90 shadow-sm"
        >
          <Icon icon="solar:add-circle-bold" width="16" />
          <span>Add New Documentation</span>
        </button>
      </div>

      <!-- Docs Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="doc in filteredDocs"
          :key="doc.id"
          class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-sm hover:border-slate-300 transition-all flex flex-col justify-between group"
        >
          <div>
            <div class="flex items-start justify-between gap-2 mb-3">
              <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                <Icon icon="solar:document-text-bold-duotone" width="22" />
              </div>

              <div v-if="canUpdateSupport || canDeleteSupportItems" class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                <button
                  v-if="canUpdateSupport"
                  @click="openEditDocModal(doc)"
                  class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50"
                  title="Edit Guide Link"
                >
                  <Icon icon="solar:pen-bold-duotone" width="16" />
                </button>
                <button
                  v-if="canDeleteSupportItems"
                  @click="confirmDeleteDoc(doc)"
                  class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                  title="Delete Guide Link"
                >
                  <Icon icon="solar:trash-bin-trash-bold-duotone" width="16" />
                </button>
              </div>
            </div>

            <span class="inline-block px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded bg-slate-100 text-slate-600 mb-1.5">
              {{ formatCategory(doc.category) }}
            </span>
            <h3 class="text-sm font-bold text-slate-800 leading-snug group-hover:text-blue_primary transition-colors">
              {{ doc.name }}
            </h3>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ doc.link }}</span>
            <button
              @click="openLink(doc.link)"
              class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800"
            >
              <span>Open Guide</span>
              <Icon icon="solar:arrow-right-up-bold" width="14" />
            </button>
          </div>
        </div>
      </div>

      <!-- Empty Docs State -->
      <div
        v-if="filteredDocs.length === 0"
        class="bg-white rounded-2xl border border-slate-200/90 p-12 text-center shadow-2xs mt-4"
      >
        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
          <Icon icon="solar:document-broken" width="24" />
        </div>
        <p class="font-semibold text-slate-700">No documentation guides found</p>
        <p class="text-xs text-slate-400 mt-0.5">Contact system administrator or add standard operating guidelines.</p>
      </div>
    </div>

    <!-- TAB 4: IT HELPDESK TRIAGE & DISPATCH CONSOLE (ADMIN ONLY) -->
    <div v-if="canDispatch && activeTab === 'admin_triage'" class="px-5 lg:px-8 mt-6">
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <!-- Dispatcher Header Bar -->
        <div class="p-4 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
              <Icon icon="solar:shield-warning-bold-duotone" width="20" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-800">IT Helpdesk Dispatcher Queue</h2>
              <p class="text-xs text-slate-500">Triage, assign technical staff, and resolve organizational support tickets.</p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative w-full sm:w-64">
              <Icon icon="solar:magnifer-linear" width="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="adminSearch"
                type="text"
                placeholder="Search staff, ticket #, subject..."
                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none"
              />
            </div>

            <!-- Status Filter -->
            <select
              v-model="adminStatusFilter"
              class="text-xs border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white outline-none focus:ring-2 focus:ring-amber-500/20"
            >
              <option value="all">All Statuses</option>
              <option value="open">🟢 Open (Pending Triage)</option>
              <option value="in_progress">🟡 In Progress</option>
              <option value="resolved">🔵 Resolved</option>
              <option value="closed">⚪ Closed</option>
            </select>

            <!-- Priority Filter -->
            <select
              v-model="adminPriorityFilter"
              class="text-xs border border-slate-300 rounded-lg py-1.5 px-2.5 bg-white outline-none focus:ring-2 focus:ring-amber-500/20"
            >
              <option value="all">All Priorities</option>
              <option value="urgent">🔴 Urgent</option>
              <option value="high">🟠 High</option>
              <option value="medium">🟡 Medium</option>
              <option value="low">🟢 Low</option>
            </select>
          </div>
        </div>

        <!-- Admin Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Ticket</th>
                <th class="py-3 px-4">Requester</th>
                <th class="py-3 px-4">Category & Subject</th>
                <th class="py-3 px-4">Priority</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Assigned Tech</th>
                <th class="py-3 px-4">Logged</th>
                <th class="py-3 px-4 text-right">Triage Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="ticket in filteredAdminTickets"
                :key="ticket.id"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <!-- Ticket # -->
                <td class="py-3.5 px-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                    {{ ticket.ticket_no }}
                  </span>
                </td>

                <!-- Requester -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-medium text-slate-800">{{ getUserDisplayName(ticket.user) }}</div>
                  <div class="text-[11px] text-slate-400">{{ ticket.user?.email || 'N/A' }}</div>
                </td>

                <!-- Category & Subject -->
                <td class="py-3.5 px-4 max-w-sm">
                  <div class="flex items-center gap-1.5 mb-0.5">
                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold uppercase rounded bg-slate-100 text-slate-600">
                      {{ formatCategory(ticket.category) }}
                    </span>
                    <span v-if="ticket.attachment_path" class="text-blue-500" title="Contains Attachment">
                      <Icon icon="solar:paperclip-bold-duotone" width="14" />
                    </span>
                  </div>
                  <div class="font-semibold text-slate-800 truncate">{{ ticket.subject }}</div>
                  <div class="text-[11px] text-slate-400 truncate">{{ ticket.description }}</div>
                </td>

                <!-- Priority -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-block px-2 py-0.5 text-[10px] font-bold rounded-full uppercase',
                      priorityClass(ticket.priority)
                    ]"
                  >
                    {{ ticket.priority }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-semibold rounded-full',
                      statusClass(ticket.status)
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(ticket.status)"></span>
                    {{ formatStatus(ticket.status) }}
                  </span>
                </td>

                <!-- Assigned Tech -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span v-if="ticket.assigned_to" class="text-slate-800 font-medium">
                    {{ getUserDisplayName(ticket.assigned_to) }}
                  </span>
                  <span v-else class="text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded text-[10px]">
                    Needs Tech
                  </span>
                </td>

                <!-- Logged -->
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                  {{ formatDate(ticket.created_at) }}
                </td>

                <!-- Action -->
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <button
                    @click="openTriageModal(ticket)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs shadow-2xs transition-all active:scale-[0.98]"
                  >
                    <Icon icon="solar:pen-new-square-bold-duotone" width="14" />
                    <span>Triage</span>
                  </button>
                </td>
              </tr>

              <tr v-if="filteredAdminTickets.length === 0">
                <td colspan="8" class="py-14 text-center text-slate-400">
                  <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <Icon icon="solar:check-read-linear" width="28" />
                  </div>
                  <p class="font-semibold text-slate-700 text-sm">Dispatcher Queue is Clear</p>
                  <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">No support tickets currently pending triage or matching your filters.</p>
                  <button
                    @click="helpModal = true"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue_primary text-white text-xs font-semibold shadow-sm hover:bg-blue_primary/90"
                  >
                    <Icon icon="solar:ticket-bold-duotone" width="16" />
                    <span>Submit Support Ticket</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODALS SECTION -->

    <!-- Submit Ticket Modal -->
    <Help v-model="helpModal" @ticket-created="onTicketCreated" />

    <!-- Support Contact Info Modal -->
    <SupportInfo v-model="supportModal" :supportData="props.supportInfo" :canUpdate="canUpdateSupport" />

    <!-- View Ticket Details Modal (For Users) -->
    <Dialog
      v-model:visible="viewModal"
      modal
      :style="{ width: '36rem' }"
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Icon icon="solar:ticket-bold-duotone" width="22" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800">{{ activeTicket?.ticket_no }}</h3>
            <p class="text-xs text-slate-500">{{ activeTicket?.subject }}</p>
          </div>
        </div>
      </template>

      <div v-if="activeTicket" class="space-y-4 text-xs text-slate-700 py-2">
        <!-- Status & Priority Banner -->
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
          <div>
            <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold">Current Status</span>
            <span
              :class="[
                'inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full mt-1',
                statusClass(activeTicket.status)
              ]"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(activeTicket.status)"></span>
              {{ formatStatus(activeTicket.status) }}
            </span>
          </div>

          <div>
            <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold">Priority</span>
            <span
              :class="[
                'inline-block px-2.5 py-0.5 text-xs font-bold rounded-full uppercase mt-1',
                priorityClass(activeTicket.priority)
              ]"
            >
              {{ activeTicket.priority }}
            </span>
          </div>

          <div>
            <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold">Assigned Tech</span>
            <span class="text-xs font-medium text-slate-800 mt-1 block">
              {{ activeTicket.assigned_to?.name || 'Unassigned' }}
            </span>
          </div>
        </div>

        <!-- Issue Description -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Issue Description</label>
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 whitespace-pre-line text-xs leading-relaxed">
            {{ activeTicket.description }}
          </div>
        </div>

        <!-- Attachment if present -->
        <div v-if="activeTicket.attachment_path">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Attachment</label>
          <a
            :href="activeTicket.attachment_path"
            target="_blank"
            class="flex items-center gap-2 p-2.5 bg-blue-50 hover:bg-blue-100/70 border border-blue-200 rounded-xl text-blue-700 transition-colors"
          >
            <Icon icon="solar:document-bold-duotone" width="18" />
            <span class="font-medium truncate">{{ activeTicket.attachment_name || 'Download Attachment' }}</span>
            <Icon icon="solar:arrow-right-up-bold" width="14" class="ml-auto" />
          </a>
        </div>

        <!-- Resolution Notes -->
        <div v-if="activeTicket.resolution_notes">
          <label class="block text-xs font-bold uppercase tracking-wider text-emerald-700 mb-1">IT Resolution Notes</label>
          <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-200 text-emerald-900 whitespace-pre-line text-xs leading-relaxed">
            {{ activeTicket.resolution_notes }}
          </div>
          <div v-if="activeTicket.resolved_at" class="text-[10px] text-emerald-600 mt-1 italic">
            Resolved on {{ formatDate(activeTicket.resolved_at) }}
          </div>
        </div>
      </div>

      <template #footer>
        <Button
          label="Close"
          severity="secondary"
          outlined
          @click="viewModal = false"
          class="px-5 py-1.5 text-xs border-slate-300 text-slate-700 rounded-lg"
        />
      </template>
    </Dialog>

    <!-- Admin Triage Modal -->
    <Dialog
      v-model:visible="triageModal"
      modal
      :style="{ width: '40rem' }"
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
            <Icon icon="solar:shield-user-bold-duotone" width="22" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800">Triage Ticket: {{ triageForm.ticket_no }}</h3>
            <p class="text-xs text-slate-500">Assign technician, transition status, and enter resolution notes</p>
          </div>
        </div>
      </template>

      <div class="space-y-4 text-xs text-slate-700 py-2">
        <!-- Requester Info -->
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
          <div>
            <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold">Requester</span>
            <span class="font-medium text-slate-800 text-xs">{{ getUserDisplayName(triageTicket?.user) }} ({{ triageTicket?.user?.email }})</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold">Priority</span>
            <span
              :class="[
                'inline-block px-2 py-0.5 text-[10px] font-bold rounded-full uppercase mt-0.5',
                priorityClass(triageTicket?.priority)
              ]"
            >
              {{ triageTicket?.priority }}
            </span>
          </div>
        </div>

        <!-- Issue Description -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Issue Description</label>
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 whitespace-pre-line text-xs max-h-36 overflow-y-auto">
            {{ triageTicket?.description }}
          </div>
        </div>

        <!-- Attachment -->
        <div v-if="triageTicket?.attachment_path">
          <a
            :href="triageTicket.attachment_path"
            target="_blank"
            class="flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-xs font-medium"
          >
            <Icon icon="solar:paperclip-bold-duotone" width="16" />
            <span class="truncate">{{ triageTicket.attachment_name || 'View Attachment' }}</span>
            <Icon icon="solar:arrow-right-up-bold" width="14" class="ml-auto" />
          </a>
        </div>

        <!-- Triage Controls -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
          <!-- Assign Technician -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
              Assign Technician
            </label>
            <select
              v-model="triageForm.assigned_to"
              class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
            >
              <option :value="null">-- Unassigned --</option>
              <option
                v-for="staff in props.itStaff || []"
                :key="staff.id"
                :value="staff.id"
              >
                {{ getUserDisplayName(staff) }} ({{ staff.email }})
              </option>
            </select>
          </div>

          <!-- Update Status -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
              Ticket Status
            </label>
            <select
              v-model="triageForm.status"
              class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
            >
              <option value="open">🟢 Open (Pending Work)</option>
              <option value="in_progress">🟡 In Progress (Under Review)</option>
              <option value="resolved">🔵 Resolved (Fix Applied)</option>
              <option value="closed">⚪ Closed (Completed)</option>
            </select>
          </div>
        </div>

        <!-- Resolution Notes -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
            IT Resolution Notes / Troubleshooting Log
          </label>
          <textarea
            v-model="triageForm.resolution_notes"
            rows="3"
            placeholder="Document what actions were taken, fixes applied, or instructions communicated to the employee..."
            class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 resize-none"
          ></textarea>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2.5 pt-2">
          <Button
            label="Cancel"
            severity="secondary"
            outlined
            :disabled="savingTriage"
            @click="triageModal = false"
            class="px-4 py-2 text-xs border-slate-300 text-slate-700 rounded-lg"
          />
          <Button
            :label="savingTriage ? 'Saving...' : 'Save & Dispatch'"
            :loading="savingTriage"
            :disabled="savingTriage"
            @click="submitTriage"
            class="px-5 py-2 text-xs bg-slate-900 text-white rounded-lg hover:bg-slate-800 shadow-sm"
          />
        </div>
      </template>
    </Dialog>

    <!-- Add/Edit FAQ Dialog -->
    <Dialog
      v-model:visible="faqModal"
      :header="editingFaqId ? 'Edit FAQ Item' : 'Add FAQ Item'"
      :style="{ width: '32rem' }"
      modal
    >
      <div class="flex flex-col gap-3.5 mt-2 text-xs">
        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Category</label>
          <select
            v-model="faqForm.category"
            class="w-full text-xs p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20 bg-white"
          >
            <option value="general">General</option>
            <option value="technical">Technical</option>
            <option value="workflow">Workflow & Approvals</option>
            <option value="permissions">Permissions & Access</option>
            <option value="documents">Document Management</option>
          </select>
        </div>

        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Question</label>
          <input
            v-model="faqForm.question"
            type="text"
            placeholder="e.g. How do I reset my document encryption key?"
            class="w-full p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20"
          />
        </div>

        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Answer</label>
          <textarea
            v-model="faqForm.answer"
            rows="4"
            placeholder="Provide clear, step-by-step instructions..."
            class="w-full p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20 resize-none"
          ></textarea>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-2">
          <Button
            label="Cancel"
            severity="secondary"
            outlined
            @click="faqModal = false"
            class="px-4 py-1.5 text-xs border-slate-300 text-slate-700 rounded-lg"
          />
          <Button
            :label="editingFaqId ? 'Update FAQ' : 'Save FAQ'"
            @click="saveFaq"
            class="bg-blue_primary text-white border-0 px-5 py-1.5 text-xs rounded-lg hover:bg-blue_primary/90"
          />
        </div>
      </template>
    </Dialog>

    <!-- Add/Edit Doc Dialog -->
    <Dialog
      v-model:visible="docModal"
      :header="editingDocId ? 'Edit Documentation Link' : 'Add Documentation Link'"
      :style="{ width: '32rem' }"
      modal
    >
      <div class="flex flex-col gap-3.5 mt-2 text-xs">
        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Category</label>
          <select
            v-model="docForm.category"
            class="w-full text-xs p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20 bg-white"
          >
            <option value="sop">Standard Operating Procedure (SOP)</option>
            <option value="user_guide">User Guide & Manual</option>
            <option value="security">Security & Compliance</option>
            <option value="api">API & Integration</option>
          </select>
        </div>

        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Document Name</label>
          <input
            v-model="docForm.name"
            type="text"
            placeholder="e.g. EDMS Enterprise Administration Guide"
            class="w-full p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20"
          />
        </div>

        <div>
          <label class="font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1 block">Link / URL</label>
          <input
            v-model="docForm.link"
            type="text"
            placeholder="https://docs.enterprise.internal/manual.pdf"
            class="w-full p-2.5 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-2">
          <Button
            label="Cancel"
            severity="secondary"
            outlined
            @click="docModal = false"
            class="px-4 py-1.5 text-xs border-slate-300 text-slate-700 rounded-lg"
          />
          <Button
            :label="editingDocId ? 'Update Doc' : 'Save Doc'"
            @click="saveDoc"
            class="bg-blue_primary text-white border-0 px-5 py-1.5 text-xs rounded-lg hover:bg-blue_primary/90"
          />
        </div>
      </template>
    </Dialog>

    <!-- Delete Confirmation Modal -->
    <Dialog
      v-model:visible="deleteConfirmModal"
      header="Confirm Deletion"
      :style="{ width: '26rem' }"
      modal
    >
      <div class="flex items-center gap-3 py-2 text-xs text-slate-600">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
          <Icon icon="solar:danger-triangle-bold-duotone" width="22" />
        </div>
        <div>
          <p class="font-semibold text-slate-800">Are you sure you want to delete this {{ deleteTargetType }}?</p>
          <p class="text-slate-500 mt-0.5">This item will be removed permanently.</p>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-2">
          <Button
            label="Cancel"
            severity="secondary"
            outlined
            @click="deleteConfirmModal = false"
            class="px-4 py-1.5 text-xs border-slate-300 text-slate-700 rounded-lg"
          />
          <Button
            label="Delete"
            @click="executeDelete"
            class="bg-rose-600 text-white border-0 px-4 py-1.5 text-xs rounded-lg hover:bg-rose-700"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import SupportInfo from "../../components/Modals/SupportInfo.vue";
import Help from "../../components/Modals/Help.vue";

const props = defineProps({
  faqs: { type: Array, default: () => [] },
  documentations: { type: Array, default: () => [] },
  supportInfo: { type: Object, default: () => ({}) },
  myTickets: { type: Array, default: () => [] },
  adminTickets: { type: [Array, Object], default: () => [] },
  ticketStats: { type: Object, default: () => ({ total: 0, open: 0, in_progress: 0, resolved: 0 }) },
  itStaff: { type: Array, default: () => [] },
  isAdmin: { type: Boolean, default: false },
});

const toast = useToast();
const page = usePage();

const userPermissions = computed(() => {
  const p = page.props.permissions || [];
  return Array.isArray(p) ? p.map(x => String(x).toLowerCase().trim()) : [];
});

const isAdmin = computed(() => {
  if (props.isAdmin) return true;
  const rawRoles = page.props.roles || page.props.user_roles || page.props.user?.roles || [];
  const roles = (Array.isArray(rawRoles) ? rawRoles : Object.values(rawRoles)).map(r =>
    (typeof r === "string" ? r : r?.name || "").toLowerCase()
  );
  return roles.some(r =>
    r.includes("admin") ||
    r.includes("super user") ||
    r.includes("accountant-general")
  );
});

const hasSupportPermission = (perm) => {
  if (!perm) return true;
  if (isAdmin.value) return true;
  return userPermissions.value.includes(perm.toLowerCase().trim());
};

const canDispatch = computed(() => isAdmin.value || hasSupportPermission('support dispatch'));
const canUpdateSupport = computed(() => isAdmin.value || hasSupportPermission('support update'));
const canDeleteSupportItems = computed(() => isAdmin.value || hasSupportPermission('support delete'));
const canManageFaqs = computed(() => canUpdateSupport.value);
const canManageDocs = computed(() => canUpdateSupport.value);

// Telemetry Stats
const stats = computed(() => {
  return props.ticketStats || { total: 0, open: 0, in_progress: 0, resolved: 0 };
});

// Normalized Admin Tickets Array (handles both array and paginator object)
const rawAdminTickets = computed(() => {
  if (!props.adminTickets) return [];
  if (Array.isArray(props.adminTickets)) return props.adminTickets;
  if (Array.isArray(props.adminTickets.data)) return props.adminTickets.data;
  return [];
});


// Active Tab
const activeTab = ref("faqs");

// Modal states
const helpModal = ref(false);
const supportModal = ref(false);
const faqModal = ref(false);
const docModal = ref(false);
const viewModal = ref(false);
const triageModal = ref(false);
const deleteConfirmModal = ref(false);

// FAQ State
const faqSearch = ref("");
const selectedFaqCategory = ref("all");
const expandedFaqs = ref([]);
const editingFaqId = ref(null);
const faqForm = ref({ question: "", answer: "", category: "general" });

const faqCategories = [
  { label: "All Questions", value: "all" },
  { label: "General", value: "general" },
  { label: "Technical", value: "technical" },
  { label: "Workflow", value: "workflow" },
  { label: "Permissions", value: "permissions" },
  { label: "Documents", value: "documents" },
];

const filteredFaqs = computed(() => {
  let list = props.faqs || [];
  if (selectedFaqCategory.value !== "all") {
    list = list.filter((f) => (f.category || "general").toLowerCase() === selectedFaqCategory.value.toLowerCase());
  }
  if (faqSearch.value.trim()) {
    const q = faqSearch.value.toLowerCase();
    list = list.filter((f) =>
      (f.question && f.question.toLowerCase().includes(q)) ||
      (f.answer && f.answer.toLowerCase().includes(q))
    );
  }
  return list;
});

const toggleFaq = (id) => {
  const idx = expandedFaqs.value.indexOf(id);
  if (idx === -1) {
    expandedFaqs.value.push(id);
  } else {
    expandedFaqs.value.splice(idx, 1);
  }
};

const openAddFaqModal = () => {
  editingFaqId.value = null;
  faqForm.value = { question: "", answer: "", category: "general" };
  faqModal.value = true;
};

const openEditFaqModal = (item) => {
  editingFaqId.value = item.id;
  faqForm.value = { question: item.question, answer: item.answer, category: item.category || "general" };
  faqModal.value = true;
};

const saveFaq = () => {
  if (!faqForm.value.question.trim() || !faqForm.value.answer.trim()) {
    toast.add({ severity: "warn", summary: "Validation", detail: "Please provide both question and answer.", life: 3000 });
    return;
  }

  if (editingFaqId.value) {
    router.post(`/dashboard/support/faqs/${editingFaqId.value}/update`, faqForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        faqModal.value = false;
        toast.add({ severity: "success", summary: "FAQ Updated", detail: "FAQ item has been saved.", life: 3000 });
      },
    });
  } else {
    router.post("/dashboard/support/faqs", { faqs: [faqForm.value] }, {
      preserveScroll: true,
      onSuccess: () => {
        faqModal.value = false;
        toast.add({ severity: "success", summary: "FAQ Added", detail: "New FAQ published successfully.", life: 3000 });
      },
    });
  }
};

// Documentation State
const docSearch = ref("");
const editingDocId = ref(null);
const docForm = ref({ name: "", link: "", category: "sop" });

const filteredDocs = computed(() => {
  let list = props.documentations || [];
  if (docSearch.value.trim()) {
    const q = docSearch.value.toLowerCase();
    list = list.filter((d) =>
      (d.name && d.name.toLowerCase().includes(q)) ||
      (d.category && d.category.toLowerCase().includes(q))
    );
  }
  return list;
});

const openAddDocModal = () => {
  editingDocId.value = null;
  docForm.value = { name: "", link: "", category: "sop" };
  docModal.value = true;
};

const openEditDocModal = (doc) => {
  editingDocId.value = doc.id;
  docForm.value = { name: doc.name, link: doc.link, category: doc.category || "sop" };
  docModal.value = true;
};

const saveDoc = () => {
  if (!docForm.value.name.trim() || !docForm.value.link.trim()) {
    toast.add({ severity: "warn", summary: "Validation", detail: "Please provide both name and link.", life: 3000 });
    return;
  }

  if (editingDocId.value) {
    router.post(`/dashboard/support/doc/${editingDocId.value}/update`, docForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        docModal.value = false;
        toast.add({ severity: "success", summary: "Doc Updated", detail: "Documentation link saved.", life: 3000 });
      },
    });
  } else {
    router.post("/dashboard/support/doc", { docs: [docForm.value] }, {
      preserveScroll: true,
      onSuccess: () => {
        docModal.value = false;
        toast.add({ severity: "success", summary: "Doc Published", detail: "New guide added.", life: 3000 });
      },
    });
  }
};

// Deletion Logic
const deleteTargetType = ref("faq");
const deleteTargetId = ref(null);

const confirmDeleteFaq = (item) => {
  deleteTargetType.value = "FAQ";
  deleteTargetId.value = item.id;
  deleteConfirmModal.value = true;
};

const confirmDeleteDoc = (doc) => {
  deleteTargetType.value = "Documentation Guide";
  deleteTargetId.value = doc.id;
  deleteConfirmModal.value = true;
};

const executeDelete = () => {
  if (deleteTargetType.value === "FAQ") {
    router.delete(`/dashboard/support/faqs/${deleteTargetId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        deleteConfirmModal.value = false;
        toast.add({ severity: "success", summary: "Deleted", detail: "FAQ item removed.", life: 3000 });
      },
    });
  } else {
    router.delete(`/dashboard/support/doc/${deleteTargetId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        deleteConfirmModal.value = false;
        toast.add({ severity: "success", summary: "Deleted", detail: "Documentation link removed.", life: 3000 });
      },
    });
  }
};

// My Tickets Filters
const myTicketSearch = ref("");
const myTicketStatusFilter = ref("all");

const myFilteredTickets = computed(() => {
  let list = props.myTickets || [];
  if (myTicketStatusFilter.value !== "all") {
    list = list.filter((t) => t.status === myTicketStatusFilter.value);
  }
  if (myTicketSearch.value.trim()) {
    const q = myTicketSearch.value.toLowerCase();
    list = list.filter((t) =>
      (t.ticket_no && t.ticket_no.toLowerCase().includes(q)) ||
      (t.subject && t.subject.toLowerCase().includes(q))
    );
  }
  return list;
});

// View Ticket Details Modal
const activeTicket = ref(null);
const viewTicketDetails = (ticket) => {
  activeTicket.value = ticket;
  viewModal.value = true;
};

// Admin Triage Console State
const adminSearch = ref("");
const adminStatusFilter = ref("all");
const adminPriorityFilter = ref("all");
const triageTicket = ref(null);
const savingTriage = ref(false);

const triageForm = ref({
  id: null,
  ticket_no: "",
  assigned_to: null,
  status: "open",
  resolution_notes: "",
});

const getUserDisplayName = (user) => {
  if (!user) return "Staff User";
  if (user.name) return user.name;
  if (user.first_name || user.last_name) {
    return `${user.first_name || ""} ${user.last_name || ""}`.trim();
  }
  return user.email || "Staff User";
};

const filteredAdminTickets = computed(() => {
  let list = rawAdminTickets.value;
  if (adminStatusFilter.value !== "all") {
    list = list.filter((t) => t.status === adminStatusFilter.value);
  }
  if (adminPriorityFilter.value !== "all") {
    list = list.filter((t) => t.priority === adminPriorityFilter.value);
  }
  if (adminSearch.value.trim()) {
    const q = adminSearch.value.toLowerCase();
    list = list.filter((t) =>
      (t.ticket_no && t.ticket_no.toLowerCase().includes(q)) ||
      (t.subject && t.subject.toLowerCase().includes(q)) ||
      (getUserDisplayName(t.user).toLowerCase().includes(q)) ||
      (t.user?.email && t.user.email.toLowerCase().includes(q))
    );
  }
  return list;
});


const openTriageModal = (ticket) => {
  triageTicket.value = ticket;
  triageForm.value = {
    id: ticket.id,
    ticket_no: ticket.ticket_no,
    assigned_to: ticket.assigned_to_user_id || ticket.assigned_to?.id || null,
    status: ticket.status || "open",
    resolution_notes: ticket.resolution_notes || "",
  };
  triageModal.value = true;
};

const submitTriage = () => {
  savingTriage.value = true;
  router.post(`/dashboard/support/tickets/${triageForm.value.id}/status`, triageForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      savingTriage.value = false;
      triageModal.value = false;
      toast.add({
        severity: "success",
        summary: "Ticket Updated",
        detail: `Ticket ${triageForm.value.ticket_no} status dispatched successfully.`,
        life: 4000,
      });
    },
    onError: () => {
      savingTriage.value = false;
      toast.add({
        severity: "error",
        summary: "Update Failed",
        detail: "Could not save ticket triage status.",
        life: 4000,
      });
    },
  });
};

const onTicketCreated = () => {
  activeTab.value = "my_tickets";
};

// Utilities
const copyText = (text) => {
  if (!text) return;
  navigator.clipboard.writeText(text);
  toast.add({ severity: "info", summary: "Copied", detail: `${text} copied to clipboard`, life: 2000 });
};

const openLink = (link) => {
  if (link) {
    if (!link.startsWith("http://") && !link.startsWith("https://")) {
      link = "https://" + link;
    }
    window.open(link, "_blank");
  }
};

const formatCategory = (cat) => {
  if (!cat) return "General";
  return cat.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const formatStatus = (st) => {
  if (!st) return "Open";
  return st.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const statusClass = (st) => {
  switch (st) {
    case "open":
      return "bg-amber-100 text-amber-800 border border-amber-200";
    case "in_progress":
      return "bg-indigo-100 text-indigo-800 border border-indigo-200";
    case "resolved":
      return "bg-emerald-100 text-emerald-800 border border-emerald-200";
    case "closed":
      return "bg-slate-100 text-slate-700 border border-slate-200";
    default:
      return "bg-slate-100 text-slate-700";
  }
};

const statusDotClass = (st) => {
  switch (st) {
    case "open":
      return "bg-amber-500";
    case "in_progress":
      return "bg-indigo-500";
    case "resolved":
      return "bg-emerald-500";
    case "closed":
      return "bg-slate-500";
    default:
      return "bg-slate-400";
  }
};

const priorityClass = (pr) => {
  switch (pr) {
    case "urgent":
      return "bg-rose-100 text-rose-700 border border-rose-200";
    case "high":
      return "bg-orange-100 text-orange-700 border border-orange-200";
    case "medium":
      return "bg-amber-100 text-amber-700 border border-amber-200";
    case "low":
      return "bg-emerald-100 text-emerald-700 border border-emerald-200";
    default:
      return "bg-slate-100 text-slate-700";
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "N/A";
  const d = new Date(dateStr);
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
};

const formatTime = (timeStr) => {
  if (!timeStr) return "8:00 AM";
  let [h, m] = timeStr.split(":");
  let hour = parseInt(h);
  let ampm = hour >= 12 ? "PM" : "AM";
  hour = hour % 12;
  hour = hour ? hour : 12;
  return `${hour}:${m || "00"} ${ampm}`;
};
</script>

<style scoped>
/* Scoped styles */
</style>
