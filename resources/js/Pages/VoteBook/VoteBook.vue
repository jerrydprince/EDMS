<template>
  <main class="min-h-screen bg-[#F8FAFC] pb-16">
    <!-- TOP EXECUTIVE HEADER -->
    <header class="bg-white border-b border-gray-200/80 px-4 md:px-8 py-6 shadow-xs">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Brand & Title -->
        <div class="flex items-start gap-4">
          <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white flex items-center justify-center shadow-md border border-slate-700/50 shrink-0">
            <Icon icon="solar:book-bookmark-bold-duotone" width="28" class="text-emerald-400" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                TSA & GIFMIS Synchronized
              </span>
              <span class="text-xs text-gray-400">•</span>
              <span class="text-xs text-gray-500 font-medium">FR 415 - 420 Statutory Guard</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight mt-1">
              Budget Vote Book Ledger
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-0.5">
              Federal Republic of Nigeria • Statutory register of approved budgetary allocations, commitments (encumbrances), and actual cash disbursements.
            </p>
          </div>
        </div>

        <!-- Action Toolbar -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button 
            @click="refreshLedger" 
            :disabled="loading"
            class="p-2.5 text-gray-600 hover:text-gray-900 bg-white hover:bg-gray-50 border border-gray-300 rounded-xl transition-all shadow-xs cursor-pointer disabled:opacity-50"
            title="Refresh Ledger"
          >
            <Icon icon="solar:restart-bold" width="18" :class="loading ? 'animate-spin text-emerald-600' : ''" />
          </button>

          <button 
            @click="printTable"
            class="flex items-center gap-2 px-3.5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs md:text-sm border border-gray-300 rounded-xl transition-all shadow-xs cursor-pointer"
          >
            <Icon icon="solar:printer-bold-duotone" width="18" class="text-gray-500" />
            <span class="hidden sm:inline">Print Ledger</span>
          </button>

          <GenerateReportButton size="md" @select="generateReportFunction" />

          <!-- Top-Up Warrant Button -->
          <button 
            v-if="selectedVoteBook && (can('votebook update') || can('votebook create') || hasRole('super admin'))"
            @click="topUpAccountModal = true"
            class="flex items-center gap-2 px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs md:text-sm border border-indigo-200 rounded-xl transition-all shadow-2xs cursor-pointer"
            title="Augment allocation with AIE or Supplementary Warrant"
          >
            <Icon icon="solar:round-transfer-vertical-bold-duotone" width="18" />
            <span>Top-Up Warrant</span>
          </button>

          <!-- Edit Vote Button -->
          <button 
            v-if="selectedVoteBook && (can('votebook update') || can('votebook edit') || hasRole('super admin'))"
            @click="isEditVoteBook = true"
            class="flex items-center gap-2 px-3.5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs md:text-sm border border-gray-300 rounded-xl transition-all shadow-xs cursor-pointer"
            title="Edit Subhead Attributes"
          >
            <Icon icon="solar:pen-new-square-bold-duotone" width="18" class="text-gray-600" />
            <span class="hidden sm:inline">Edit Vote</span>
          </button>

          <!-- Add Vote Button -->
          <button 
            v-if="can('votebook create') || hasRole('super admin')"
            @click="addNewVoteModal = true"
            class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs md:text-sm rounded-xl transition-all shadow-sm shadow-emerald-600/20 cursor-pointer"
          >
            <Icon icon="solar:add-circle-bold" width="20" />
            <span>Add Vote Subhead</span>
          </button>
        </div>
      </div>
    </header>

    <div class="px-4 md:px-8 space-y-6 mt-6">
      <!-- EXECUTIVE TREASURY METRICS CARDS -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Approved Annual Allocation -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Approved Budget Allocation</span>
              <div class="text-2xl font-extrabold text-gray-900 mt-1 tracking-tight flex items-baseline gap-1 font-mono">
                <span class="text-sm font-semibold text-gray-400">₦</span>
                <span>{{ formatCurrency(activeAllocation) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
              <Icon icon="solar:wallet-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Appropriation Line</span>
            <span class="font-bold text-gray-800 font-mono">{{ selectedVoteBook?.ncoa_code ? ('NCOA: ' + selectedVoteBook.ncoa_code) : 'Statutory Subhead' }}</span>
          </div>
        </div>

        <!-- Card 2: Active Commitments (Liabilities) -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Committed (Liabilities)</span>
              <div class="text-2xl font-extrabold text-amber-900 mt-1 tracking-tight flex items-baseline gap-1 font-mono">
                <span class="text-sm font-semibold text-amber-500">₦</span>
                <span>{{ formatCurrency(activeCommitted) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:clock-circle-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Encumbered Vouchers</span>
            <span class="font-bold text-amber-800">{{ liveStats.commitment_count ?? voteLiabilities.length }} Active Mandates</span>
          </div>
        </div>

        <!-- Card 3: Actual Expenditure (Disbursed) -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Actual Expenditure</span>
              <div class="text-2xl font-extrabold text-gray-900 mt-1 tracking-tight flex items-baseline gap-1 font-mono">
                <span class="text-sm font-semibold text-rose-500">₦</span>
                <span>{{ formatCurrency(activeExpenditure) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:arrow-right-down-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Disbursements Passed</span>
            <span class="font-bold text-gray-800">{{ liveStats.expenditure_count ?? voteExpenditures.length }} Liquidated PVs</span>
          </div>
        </div>

        <!-- Card 4: Net Available Balance -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Net Available Balance</span>
              <div class="text-2xl font-extrabold text-emerald-600 mt-1 tracking-tight flex items-baseline gap-1 font-mono">
                <span class="text-sm font-semibold text-emerald-500">₦</span>
                <span>{{ formatCurrency(activeAvailable) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:safe-square-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Legal Spending Headroom</span>
            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px]">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              {{ utilizationRate > 90 ? 'Exhausting' : 'Adequate' }}
            </span>
          </div>
        </div>
      </section>

      <!-- BUDGET COMMITMENT & UTILIZATION PROGRESS GAUGE -->
      <section class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2.5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">
              %
            </div>
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800">
                Budgetary Commitment & Charge Ratio
              </h4>
              <p class="text-xs text-gray-500">
                Percentage of legal allocation encumbered or liquidated under {{ selectedVoteBook?.name || 'Selected Vote' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span 
              :class="[
                'px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5',
                utilizationRate > 90 
                  ? 'bg-rose-50 text-rose-700 border-rose-200' 
                  : utilizationRate > 70 
                    ? 'bg-amber-50 text-amber-700 border-amber-200' 
                    : 'bg-emerald-50 text-emerald-700 border-emerald-200'
              ]"
            >
              <span 
                class="w-2 h-2 rounded-full"
                :class="utilizationRate > 90 ? 'bg-rose-500 animate-ping' : utilizationRate > 70 ? 'bg-amber-500' : 'bg-emerald-500'"
              ></span>
              {{ utilizationRate }}% Committed & Disbursed
            </span>
          </div>
        </div>

        <!-- Meter Bar -->
        <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden p-0.5 border border-gray-200/70">
          <div 
            class="h-full rounded-full transition-all duration-700 shadow-xs"
            :class="utilizationRate > 90 ? 'bg-gradient-to-r from-amber-500 to-rose-600' : utilizationRate > 70 ? 'bg-gradient-to-r from-emerald-500 to-amber-500' : 'bg-gradient-to-r from-emerald-600 to-teal-500'"
            :style="{ width: Math.min(utilizationRate, 100) + '%' }"
          ></div>
        </div>

        <div class="flex justify-between items-center text-[11px] text-gray-500 mt-2 font-mono">
          <span>Charges: ₦{{ formatCurrency(activeExpenditure + activeCommitted) }}</span>
          <span>Appropriation Limit: ₦{{ formatCurrency(activeAllocation) }}</span>
        </div>
      </section>

      <!-- VOTE BOOK ACCOUNTS SELECTOR DECK -->
      <section class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Vote Book Accounts Deck</span>
            <h3 class="text-base font-bold text-gray-900 mt-0.5">
              Select Vote Subhead to Inspect Ledger
            </h3>
          </div>

          <!-- Deck Filters & Search -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center bg-gray-100 p-1 rounded-xl text-xs font-semibold text-gray-600">
              <button 
                @click="accountCategoryFilter = 'all'"
                :class="['px-3 py-1.5 rounded-lg transition-all cursor-pointer', accountCategoryFilter === 'all' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'hover:text-gray-900']"
              >
                All ({{ allAccounts.length }})
              </button>
              <button 
                @click="accountCategoryFilter = 'standard'"
                :class="['px-3 py-1.5 rounded-lg transition-all cursor-pointer', accountCategoryFilter === 'standard' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'hover:text-gray-900']"
              >
                Standard ({{ standardAccountsList.length }})
              </button>
              <button 
                @click="accountCategoryFilter = 'special'"
                :class="['px-3 py-1.5 rounded-lg transition-all cursor-pointer', accountCategoryFilter === 'special' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'hover:text-gray-900']"
              >
                Special Sub-Accounts ({{ specialAccountsList.length }})
              </button>
            </div>

            <div class="relative w-48 sm:w-56">
              <Icon icon="solar:magnifer-linear" width="16" class="absolute left-3 top-2.5 text-gray-400" />
              <input 
                v-model="accountSearch" 
                type="text" 
                placeholder="Find subhead..." 
                class="w-full pl-8 pr-3 py-1.5 bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-xs text-gray-800 outline-none transition-all"
              />
            </div>
          </div>
        </div>

        <!-- Accounts Grid Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 mt-4">
          <div 
            v-for="acc in filteredAccounts" 
            :key="acc.id"
            @click="selectVoteBook(acc)"
            :class="[
              'p-4 rounded-xl border transition-all cursor-pointer relative flex flex-col justify-between group',
              selectedVoteBook?.id === acc.id 
                ? 'bg-emerald-50/40 border-emerald-500 ring-2 ring-emerald-500/20 shadow-xs' 
                : 'bg-gray-50/50 hover:bg-white border-gray-200/80 hover:border-gray-300 hover:shadow-2xs'
            ]"
          >
            <div>
              <div class="flex items-start justify-between gap-2">
                <span 
                  :class="[
                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                    acc.account_type === 'special' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'
                  ]"
                >
                  {{ acc.account_type === 'special' ? 'Special Project' : 'Standard Vote' }}
                </span>

                <span v-if="selectedVoteBook?.id === acc.id" class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                  <Icon icon="solar:check-read-bold" width="12" />
                </span>
              </div>

              <h4 class="text-xs font-bold text-gray-900 mt-2 line-clamp-1 group-hover:text-emerald-700 transition-colors" :title="acc.name">
                {{ acc.name }}
              </h4>

              <p class="text-[11px] text-gray-500 font-mono mt-0.5 truncate">
                {{ acc.ncoa_code ? ('NCOA ' + acc.ncoa_code) : acc.account_number }}
              </p>
            </div>

            <div class="mt-3 pt-2.5 border-t border-gray-200/60 flex items-baseline justify-between">
              <span class="text-[10px] text-gray-500 uppercase font-semibold">Available</span>
              <span class="text-xs font-bold font-mono text-emerald-700">
                ₦{{ formatCurrency(acc.balance || acc.available_balance || 0) }}
              </span>
            </div>
          </div>

          <!-- Add Account Inline Card -->
          <div 
            @click="addNewVoteModal = true"
            class="p-4 rounded-xl border-2 border-dashed border-gray-300 hover:border-emerald-500 bg-white hover:bg-emerald-50/20 transition-all cursor-pointer flex flex-col items-center justify-center text-center group min-h-[110px]"
          >
            <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-emerald-100 text-gray-500 group-hover:text-emerald-600 flex items-center justify-center transition-colors">
              <Icon icon="solar:add-circle-bold" width="20" />
            </div>
            <span class="text-xs font-bold text-gray-700 group-hover:text-emerald-700 mt-1.5 transition-colors">
              Add New Vote Subhead
            </span>
            <span class="text-[10px] text-gray-400">Register statutory line</span>
          </div>
        </div>
      </section>

      <!-- MULTI-TAB STATUTORY LEDGER CONTAINER -->
      <section class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <!-- Tabs Header Bar -->
        <div class="px-6 pt-5 pb-0 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Tab Buttons -->
          <div class="flex items-center gap-2 overflow-x-auto pb-[-1px]">
            <!-- Tab 1: Expenditures -->
            <button 
              @click="activeVoteTab = 'expenditures'"
              :class="[
                'px-4 py-3 text-xs md:text-sm font-bold border-b-2 transition-all cursor-pointer flex items-center gap-2 shrink-0',
                activeVoteTab === 'expenditures' 
                  ? 'border-emerald-600 text-emerald-700' 
                  : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
              ]"
            >
              <Icon icon="solar:arrow-right-down-bold-duotone" width="18" />
              <span>Cash Expenditures (Disbursements)</span>
              <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                {{ voteExpenditures.length }}
              </span>
            </button>

            <!-- Tab 2: Liabilities / Commitments -->
            <button 
              @click="activeVoteTab = 'liabilities'"
              :class="[
                'px-4 py-3 text-xs md:text-sm font-bold border-b-2 transition-all cursor-pointer flex items-center gap-2 shrink-0',
                activeVoteTab === 'liabilities' 
                  ? 'border-amber-600 text-amber-700' 
                  : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
              ]"
            >
              <Icon icon="solar:clock-circle-bold-duotone" width="18" />
              <span>Liabilities & Encumbrances</span>
              <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                {{ voteLiabilities.length }}
              </span>
            </button>

            <!-- Tab 3: Top-Up History -->
            <button 
              @click="activeVoteTab = 'topups'"
              :class="[
                'px-4 py-3 text-xs md:text-sm font-bold border-b-2 transition-all cursor-pointer flex items-center gap-2 shrink-0',
                activeVoteTab === 'topups' 
                  ? 'border-indigo-600 text-indigo-700' 
                  : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
              ]"
            >
              <Icon icon="solar:round-transfer-vertical-bold-duotone" width="18" />
              <span>Warrant Allocations & Top-Ups</span>
              <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800">
                {{ topupHistory.length }}
              </span>
            </button>

            <!-- Tab 4: Velocity Analytics -->
            <button 
              @click="activeVoteTab = 'analytics'"
              :class="[
                'px-4 py-3 text-xs md:text-sm font-bold border-b-2 transition-all cursor-pointer flex items-center gap-2 shrink-0',
                activeVoteTab === 'analytics' 
                  ? 'border-slate-800 text-slate-900' 
                  : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
              ]"
            >
              <Icon icon="solar:chart-square-bold-duotone" width="18" />
              <span>Trend Analytics</span>
            </button>
          </div>

          <!-- Active Subhead Tag -->
          <div class="hidden lg:flex items-center gap-2 pb-2 text-xs text-gray-500 font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="font-bold text-gray-700">{{ selectedVoteBook?.name || 'All Votes' }}</span>
          </div>
        </div>

        <!-- Filter & Search Toolbar (Tabs 1, 2, 3) -->
        <div v-if="activeVoteTab !== 'analytics'" class="p-4 bg-gray-50/60 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
          <div class="flex items-center gap-3 flex-1 max-w-lg">
            <div class="relative w-full">
              <Icon icon="solar:magnifer-linear" width="18" class="absolute left-3.5 top-2.5 text-gray-400" />
              <input 
                v-model="tabSearch" 
                type="text" 
                placeholder="Search voucher, payee, NCOA, description..." 
                class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-xs md:text-sm text-gray-800 outline-none transition-all shadow-2xs"
              />
            </div>
          </div>

          <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Date Filter -->
            <select 
              v-model="selectedDateRange" 
              @change="fetchLedger"
              class="px-3 py-2 bg-white border border-gray-200 text-xs font-semibold rounded-xl text-gray-700 outline-none shadow-2xs"
            >
              <option value="all">All Dates</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>

            <!-- Rows per page -->
            <select 
              v-model="perPage" 
              class="px-3 py-2 bg-white border border-gray-200 text-xs font-semibold rounded-xl text-gray-700 outline-none shadow-2xs"
            >
              <option :value="10">10 Rows</option>
              <option :value="25">25 Rows</option>
              <option :value="50">50 Rows</option>
              <option :value="100">100 Rows</option>
            </select>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1 CONTENT: CASH EXPENDITURES (DISBURSEMENTS) -->
        <!-- ============================================== -->
        <div v-if="activeVoteTab === 'expenditures'" class="overflow-x-auto">
          <table class="min-w-full text-left text-xs md:text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50/90 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
              <tr>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-3">Voucher No</th>
                <th class="py-3 px-3">NCOA Code</th>
                <th class="py-3 px-3">Beneficiary / Payee</th>
                <th class="py-3 px-3">Payment Purpose</th>
                <th class="py-3 px-3">Platform</th>
                <th class="py-3 px-3 text-right">Net Amount</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody v-if="paginatedExpenditures.length > 0" class="divide-y divide-gray-100 bg-white">
              <tr 
                v-for="(item, index) in paginatedExpenditures" 
                :key="item.id || index" 
                class="hover:bg-slate-50/80 transition-colors"
              >
                <td class="py-3.5 px-4 font-medium text-gray-900 whitespace-nowrap">
                  {{ formatDate(item.date) }}
                </td>
                <td class="py-3.5 px-3 font-mono font-bold text-gray-800 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span>{{ item.voucher_no || '---' }}</span>
                    <button 
                      v-if="item.voucher_no"
                      @click="copyToClipboard(item.voucher_no)" 
                      class="text-gray-400 hover:text-gray-700 cursor-pointer"
                      title="Copy Voucher No"
                    >
                      <Icon icon="solar:copy-bold" width="13" />
                    </button>
                  </div>
                </td>
                <td class="py-3.5 px-3 font-mono whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ item.ncoa_code || selectedVoteBook?.ncoa_code || '---' }}
                  </span>
                </td>
                <td class="py-3.5 px-3 font-semibold text-gray-900 whitespace-nowrap">
                  {{ item.to || 'Beneficiary' }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 max-w-xs truncate" :title="item.payment_description || item.receipt_description">
                  {{ item.payment_description || item.receipt_description || 'Disbursement liquidated' }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ item.disbursement_platform || 'GIFMIS' }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-extrabold text-gray-900 whitespace-nowrap">
                  ₦{{ formatCurrency(item.amount) }}
                </td>
                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <Icon icon="solar:check-circle-bold" width="13" />
                    Disbursed
                  </span>
                </td>
                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                  <button 
                    @click="viewRecordDetail(item, 'expenditure')"
                    class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors cursor-pointer"
                    title="View Transaction Detail"
                  >
                    <Icon icon="solar:eye-bold" width="16" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="9" class="py-16 text-center text-gray-400">
                  <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <Icon icon="solar:document-text-bold-duotone" width="24" />
                  </div>
                  <p class="font-bold text-gray-700 text-sm">No Cash Disbursements Found</p>
                  <p class="text-xs text-gray-400 mt-1">
                    No liquidated payment vouchers match this vote subhead or filter criteria.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Bar -->
          <div v-if="filteredExpenditures.length > 0" class="px-6 py-4 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Showing {{ paginationRange(filteredExpenditures.length) }} of {{ filteredExpenditures.length }} Disbursements</span>
            <div class="flex items-center gap-2">
              <button 
                @click="currentPage = Math.max(1, currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-40 cursor-pointer"
              >
                Previous
              </button>
              <span class="font-bold text-gray-800">Page {{ currentPage }} of {{ totalPages(filteredExpenditures.length) }}</span>
              <button 
                @click="currentPage = Math.min(totalPages(filteredExpenditures.length), currentPage + 1)" 
                :disabled="currentPage >= totalPages(filteredExpenditures.length)"
                class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-40 cursor-pointer"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2 CONTENT: LIABILITIES & COMMITMENTS -->
        <!-- ============================================== -->
        <div v-else-if="activeVoteTab === 'liabilities'" class="overflow-x-auto">
          <table class="min-w-full text-left text-xs md:text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50/90 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
              <tr>
                <th class="py-3 px-4">Date Incurred</th>
                <th class="py-3 px-3">Voucher Ref</th>
                <th class="py-3 px-3">Subhead / NCOA</th>
                <th class="py-3 px-3">Payee / Particulars</th>
                <th class="py-3 px-3">Approval Stage</th>
                <th class="py-3 px-3 text-right">Encumbered Amount</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody v-if="paginatedLiabilities.length > 0" class="divide-y divide-gray-100 bg-white">
              <tr 
                v-for="(item, index) in paginatedLiabilities" 
                :key="item.id || index" 
                class="hover:bg-slate-50/80 transition-colors"
              >
                <td class="py-3.5 px-4 font-medium text-gray-900 whitespace-nowrap">
                  {{ formatDate(item.date) }}
                </td>
                <td class="py-3.5 px-3 font-mono font-bold text-amber-900 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span>{{ item.voucher_number || 'ENC-' + (index + 1) }}</span>
                    <button 
                      v-if="item.voucher_number"
                      @click="copyToClipboard(item.voucher_number)" 
                      class="text-gray-400 hover:text-gray-700 cursor-pointer"
                      title="Copy Ref"
                    >
                      <Icon icon="solar:copy-bold" width="13" />
                    </button>
                  </div>
                </td>
                <td class="py-3.5 px-3 font-mono whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ item.s_head || selectedVoteBook?.ncoa_code || '22020501' }}
                  </span>
                </td>
                <td class="py-3.5 px-3 font-semibold text-gray-900 whitespace-nowrap max-w-xs truncate" :title="item.particulars">
                  {{ item.particulars || 'Payee Commitment' }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    <Icon icon="solar:shield-check-bold" width="13" class="text-slate-500" />
                    {{ item.checked_and_passed ? 'Checked & Passed' : 'Verification In-Flight' }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-extrabold text-amber-800 whitespace-nowrap">
                  ₦{{ formatCurrency(item.amount) }}
                </td>
                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <Icon icon="solar:clock-circle-bold" width="13" />
                    Commitment Active
                  </span>
                </td>
                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                  <button 
                    @click="viewRecordDetail(item, 'liability')"
                    class="p-1.5 rounded-lg text-gray-500 hover:text-amber-700 hover:bg-amber-50 transition-colors cursor-pointer"
                    title="View Commitment Detail"
                  >
                    <Icon icon="solar:eye-bold" width="16" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="8" class="py-16 text-center text-gray-400">
                  <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <Icon icon="solar:check-circle-bold-duotone" width="24" class="text-emerald-500" />
                  </div>
                  <p class="font-bold text-gray-700 text-sm">No Pending Liabilities</p>
                  <p class="text-xs text-gray-400 mt-1">
                    All commitments on this Vote Book have been cleared or liquidated through the Cash Book.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Bar -->
          <div v-if="filteredLiabilities.length > 0" class="px-6 py-4 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Showing {{ paginationRange(filteredLiabilities.length) }} of {{ filteredLiabilities.length }} Commitments</span>
            <div class="flex items-center gap-2">
              <button 
                @click="currentPage = Math.max(1, currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-40 cursor-pointer"
              >
                Previous
              </button>
              <span class="font-bold text-gray-800">Page {{ currentPage }} of {{ totalPages(filteredLiabilities.length) }}</span>
              <button 
                @click="currentPage = Math.min(totalPages(filteredLiabilities.length), currentPage + 1)" 
                :disabled="currentPage >= totalPages(filteredLiabilities.length)"
                class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 disabled:opacity-40 cursor-pointer"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3 CONTENT: ALLOCATION & TOP-UP HISTORY -->
        <!-- ============================================== -->
        <div v-else-if="activeVoteTab === 'topups'" class="overflow-x-auto">
          <table class="min-w-full text-left text-xs md:text-sm divide-y divide-gray-100">
            <thead class="bg-gray-50/90 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
              <tr>
                <th class="py-3 px-4">Date Credited</th>
                <th class="py-3 px-3">Transaction Classification</th>
                <th class="py-3 px-3">Authorizing Authority</th>
                <th class="py-3 px-3 text-right">Credit Amount</th>
                <th class="py-3 px-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody v-if="topupHistory.length > 0" class="divide-y divide-gray-100 bg-white">
              <tr 
                v-for="(item, index) in topupHistory" 
                :key="item.id || index" 
                class="hover:bg-slate-50/80 transition-colors"
              >
                <td class="py-3.5 px-4 font-medium text-gray-900 whitespace-nowrap">
                  {{ formatDate(item.created_at) }}
                </td>
                <td class="py-3.5 px-3 font-semibold text-gray-900 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Supplementary Warrant Top-Up</span>
                  </div>
                </td>
                <td class="py-3.5 px-3 font-mono text-gray-600 whitespace-nowrap">
                  GIFMIS / Authority to Incur Expenditure (AIE)
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-extrabold text-emerald-700 whitespace-nowrap">
                  +₦{{ formatCurrency(item.amount) }}
                </td>
                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <Icon icon="solar:check-circle-bold" width="13" />
                    Appropriated
                  </span>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="5" class="py-16 text-center text-gray-400">
                  <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <Icon icon="solar:wallet-bold-duotone" width="24" />
                  </div>
                  <p class="font-bold text-gray-700 text-sm">No Supplementary Top-Ups</p>
                  <p class="text-xs text-gray-400 mt-1">
                    Operating under initial budget allocation. Use "Top-Up Warrant" to augment funds.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ============================================== -->
        <!-- TAB 4 CONTENT: VELOCITY & TREND ANALYTICS -->
        <!-- ============================================== -->
        <div v-else-if="activeVoteTab === 'analytics'" class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Chart -->
            <div class="lg:col-span-2 bg-gray-50/50 p-5 rounded-2xl border border-gray-200/80">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Monthly Expenditure Velocity</h4>
                  <p class="text-xs text-gray-500">Trailing disbursement curve for {{ selectedVoteBook?.name }}</p>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                  FY 2026 Active
                </span>
              </div>
              <VotebookLineChart />
            </div>

            <!-- Right Financial Summary Card -->
            <div class="bg-gray-50/50 p-5 rounded-2xl border border-gray-200/80 flex flex-col justify-between space-y-4">
              <div>
                <h4 class="text-sm font-bold text-gray-900">Statutory Budget Analysis</h4>
                <p class="text-xs text-gray-500 mt-0.5">Summary of fiscal performance</p>

                <div class="mt-4 space-y-3 text-xs">
                  <div class="flex justify-between items-center py-2 border-b border-gray-200/60">
                    <span class="text-gray-500">Appropriation Limit:</span>
                    <span class="font-bold font-mono text-gray-900">₦{{ formatCurrency(activeAllocation) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-200/60">
                    <span class="text-gray-500">Cumulative Outflows:</span>
                    <span class="font-bold font-mono text-rose-600">₦{{ formatCurrency(activeExpenditure) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-200/60">
                    <span class="text-gray-500">Active Commitments:</span>
                    <span class="font-bold font-mono text-amber-600">₦{{ formatCurrency(activeCommitted) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-200/60">
                    <span class="text-gray-500">Net Uncommitted:</span>
                    <span class="font-bold font-mono text-emerald-600">₦{{ formatCurrency(activeAvailable) }}</span>
                  </div>
                </div>
              </div>

              <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs">
                <div class="flex items-center gap-1.5 text-gray-700 font-bold mb-1">
                  <Icon icon="solar:shield-check-bold" class="text-emerald-600" width="16" />
                  <span>Financial Regulations Compliance</span>
                </div>
                <p class="text-[11px] text-gray-500">
                  Under FR 415, no liability may be incurred without uncommitted balance verified in this Vote Book.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- TRANSACTION DETAIL MODAL -->
    <Dialog 
      v-model:visible="detailModalVisible" 
      modal 
      :closable="false"
      :pt="{
        root: { class: 'bg-white rounded-3xl shadow-2xl mx-2 md:mx-0 overflow-hidden border border-gray-100' },
        header: { class: 'hidden' },
        content: { class: 'p-0 bg-white w-full' },
        footer: { class: 'p-0 bg-gray-50 border-t border-gray-100' },
      }"
      :style="{ width: '34rem' }" 
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <section v-if="selectedRecord" class="flex flex-col">
        <!-- HEADER -->
        <div class="px-6 py-5 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
              <Icon icon="solar:document-text-bold-duotone" width="22" class="text-emerald-400" />
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                Vote Book Ledger Transcript
              </span>
              <h3 class="text-base font-bold text-white mt-0.5">
                {{ selectedRecordType === 'expenditure' ? 'Disbursement Detail' : 'Commitment Encumbrance' }}
              </h3>
            </div>
          </div>
          <button 
            @click="detailModalVisible = false"
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-all cursor-pointer"
          >
            <Icon icon="solar:close-circle-bold" width="20" />
          </button>
        </div>

        <!-- BODY -->
        <div class="p-6 space-y-4 text-xs">
          <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200/70 flex items-center justify-between">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Transaction Value</span>
              <div class="text-2xl font-extrabold text-emerald-950 font-mono mt-0.5">
                ₦{{ formatCurrency(selectedRecord.amount || selectedRecord.net_amount) }}
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white text-emerald-800 border border-emerald-200">
              {{ selectedRecordType === 'expenditure' ? 'Disbursed' : 'Encumbered' }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-gray-700">
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Voucher Ref</span>
              <p class="font-bold font-mono text-gray-900 mt-0.5">
                {{ selectedRecord.voucher_no || selectedRecord.voucher_number || '---' }}
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Date</span>
              <p class="font-bold text-gray-900 mt-0.5">
                {{ formatDate(selectedRecord.date || selectedRecord.created_at) }}
              </p>
            </div>
          </div>

          <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Payee / Claimant</span>
            <p class="font-bold text-gray-900 mt-0.5 text-sm">
              {{ selectedRecord.to || selectedRecord.particulars || 'Payee' }}
            </p>
          </div>

          <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Description / Purpose</span>
            <p class="text-gray-700 mt-0.5 leading-relaxed">
              {{ selectedRecord.payment_description || selectedRecord.receipt_description || selectedRecord.particulars || 'Treasury disbursement' }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 text-gray-700">
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Subhead Code</span>
              <p class="font-bold font-mono text-gray-900 mt-0.5">
                {{ selectedRecord.ncoa_code || selectedRecord.s_head || selectedVoteBook?.ncoa_code || '---' }}
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Platform</span>
              <p class="font-bold text-gray-900 mt-0.5">
                {{ selectedRecord.disbursement_platform || 'GIFMIS e-Payment' }}
              </p>
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
          <button 
            @click="detailModalVisible = false"
            class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-2xs"
          >
            Close
          </button>
        </div>
      </section>
    </Dialog>

    <!-- Modals -->
    <AddNewVote 
      v-if="addNewVoteModal" 
      @refreshVoteBook="refreshVoteBookData" 
      @cancelled="addNewVoteModal = false" 
    />
    <editVoteBook 
      v-if="isEditVoteBook" 
      :selectedVoteBook="selectedVoteBook" 
      @refreshVoteBook="refreshVoteBookData" 
      @cancelled="isEditVoteBook = false" 
    />
    <TopUpAccount 
      v-if="topUpAccountModal" 
      :accountSelected="selectedVoteBook" 
      @refreshVoteBook="refreshVoteBookData" 
      @cancelled="topUpAccountModal = false" 
    />
  </main>
</template>

<script setup>
import { ref, defineProps, watch, computed, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";

import AddNewVote from "../../components/Modals/VoteBook/AddNewVote.vue";
import TopUpAccount from "../../components/Modals/VoteBook/TopUpAccount.vue";
import editVoteBook from "../../components/Modals/VoteBook/editVoteBook.vue";
import GenerateReportButton from "../../components/GenerateReport.vue";
import VotebookLineChart from "../../components/VotebookLineChart.vue";
import { usePermissions } from "@/composables/usePermissions";

const toast = useToast();
const { can, hasRole } = usePermissions();
const props = defineProps({
  votebooks: {
    type: Array,
    default: () => []
  },
  systemStats: {
    type: Object,
    default: () => ({})
  }
});

// Modals
const addNewVoteModal = ref(false);
const topUpAccountModal = ref(false);
const isEditVoteBook = ref(false);
const detailModalVisible = ref(false);
const selectedRecord = ref(null);
const selectedRecordType = ref("expenditure");

// State
const loading = ref(false);
const selectedVoteBook = ref(null);
const activeVoteTab = ref("expenditures");
const accountCategoryFilter = ref("all");
const accountSearch = ref("");

// Filters & Pagination
const tabSearch = ref("");
const selectedDateRange = ref("all");
const perPage = ref(10);
const currentPage = ref(1);

// Ledger Records
const voteExpenditures = ref([]);
const voteLiabilities = ref([]);
const topupHistory = ref([]);
const liveStats = ref({});

// Accounts List
const allAccounts = computed(() => props.votebooks || []);
const standardAccountsList = computed(() => allAccounts.value.filter(a => a.account_type === 'standard'));
const specialAccountsList = computed(() => allAccounts.value.filter(a => a.account_type === 'special'));

const filteredAccounts = computed(() => {
  let list = allAccounts.value;
  if (accountCategoryFilter.value === 'standard') {
    list = standardAccountsList.value;
  } else if (accountCategoryFilter.value === 'special') {
    list = specialAccountsList.value;
  }

  if (accountSearch.value.trim()) {
    const s = accountSearch.value.toLowerCase();
    list = list.filter(a => 
      (a.name || '').toLowerCase().includes(s) || 
      (a.account_number || '').toLowerCase().includes(s) ||
      (a.ncoa_code || '').toLowerCase().includes(s) ||
      (a.gifmis_code || '').toLowerCase().includes(s)
    );
  }
  return list;
});

// Metrics
const activeAllocation = computed(() => {
  if (liveStats.value.approved_allocation !== undefined) {
    return Number(liveStats.value.approved_allocation || 0);
  }
  return Number(selectedVoteBook.value?.approved_allocation || selectedVoteBook.value?.balance || 0);
});

const activeCommitted = computed(() => {
  if (liveStats.value.committed_balance !== undefined) {
    return Number(liveStats.value.committed_balance || 0);
  }
  return Number(selectedVoteBook.value?.committed_balance || 0);
});

const activeExpenditure = computed(() => {
  if (liveStats.value.actual_expenditure !== undefined) {
    return Number(liveStats.value.actual_expenditure || 0);
  }
  return Number(selectedVoteBook.value?.actual_expenditure || 0);
});

const activeAvailable = computed(() => {
  if (liveStats.value.available_balance !== undefined) {
    return Number(liveStats.value.available_balance || 0);
  }
  return Number(selectedVoteBook.value?.available_balance || selectedVoteBook.value?.balance || 0);
});

const utilizationRate = computed(() => {
  const alloc = activeAllocation.value;
  if (alloc <= 0) return 0;
  const spentAndCommitted = activeExpenditure.value + activeCommitted.value;
  return Math.min(100, Math.round((spentAndCommitted / alloc) * 100));
});

// Tab Filtering
const filteredExpenditures = computed(() => {
  if (!tabSearch.value.trim()) return voteExpenditures.value;
  const s = tabSearch.value.toLowerCase();
  return voteExpenditures.value.filter(item => 
    (item.voucher_no || '').toLowerCase().includes(s) ||
    (item.to || '').toLowerCase().includes(s) ||
    (item.ncoa_code || '').toLowerCase().includes(s) ||
    (item.payment_description || '').toLowerCase().includes(s)
  );
});

const paginatedExpenditures = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredExpenditures.value.slice(start, start + perPage.value);
});

const filteredLiabilities = computed(() => {
  if (!tabSearch.value.trim()) return voteLiabilities.value;
  const s = tabSearch.value.toLowerCase();
  return voteLiabilities.value.filter(item => 
    (item.voucher_number || '').toLowerCase().includes(s) ||
    (item.particulars || '').toLowerCase().includes(s) ||
    (item.s_head || '').toLowerCase().includes(s)
  );
});

const paginatedLiabilities = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredLiabilities.value.slice(start, start + perPage.value);
});

// Pagination Helpers
const totalPages = (totalCount) => Math.ceil(totalCount / perPage.value) || 1;
const paginationRange = (totalCount) => {
  if (totalCount === 0) return '0';
  const start = (currentPage.value - 1) * perPage.value + 1;
  const end = Math.min(currentPage.value * perPage.value, totalCount);
  return `${start}-${end}`;
};

// Data Fetching
const fetchLedger = async () => {
  if (!selectedVoteBook.value?.id) return;
  loading.value = true;
  currentPage.value = 1;

  try {
    const res = await axios.get('/finance-and-accounts/get-votebook-ledger', {
      params: {
        vote_book_id: selectedVoteBook.value.id,
        date_range: selectedDateRange.value,
      }
    });

    if (res.data) {
      voteExpenditures.value = res.data.expenditures || [];
      voteLiabilities.value = res.data.commitments || [];
      topupHistory.value = res.data.topups || [];
      liveStats.value = res.data.stats || {};
    }
  } catch (e) {
    console.error("Failed to load vote book ledger", e);
    // Fallback to cashbooks endpoint if needed
    try {
      const fallback = await axios.get('/finance-and-accounts/get-cashbooks', {
        params: { vote_book_id: selectedVoteBook.value.id }
      });
      voteExpenditures.value = fallback.data.data || [];
    } catch (err) {}
  } finally {
    loading.value = false;
  }
};

const selectVoteBook = (account) => {
  selectedVoteBook.value = account;
  fetchLedger();
};

const refreshLedger = () => {
  fetchLedger();
  toast.add({
    severity: "info",
    summary: "Ledger Refreshed",
    detail: "Latest commitments and cashbook disbursements synchronized.",
    life: 2500,
  });
};

const refreshVoteBookData = () => {
  router.reload({
    only: ['votebooks', 'systemStats'],
    preserveState: true,
    onSuccess: () => {
      fetchLedger();
    }
  });
};

// Actions
const viewRecordDetail = (record, type) => {
  selectedRecord.value = record;
  selectedRecordType.value = type;
  detailModalVisible.value = true;
};

const copyToClipboard = (text) => {
  if (!text) return;
  navigator.clipboard.writeText(text);
  toast.add({
    severity: "success",
    summary: "Copied to Clipboard",
    detail: text,
    life: 2000,
  });
};

const printTable = () => {
  window.print();
};

const generateReportFunction = (option) => {
  const voteBookId = selectedVoteBook.value?.id || '';
  const filter = typeof option === 'string' ? option : 'all';
  window.open(`/finance-and-accounts/download-report?report=${encodeURIComponent(filter)}&vote_book_id=${encodeURIComponent(voteBookId)}`, '_blank');
};

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  const d = new Date(dateStr);
  return isNaN(d) ? dateStr : d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Lifecycle & Watchers
watch(() => props.votebooks, (newVal) => {
  if (newVal && newVal.length > 0) {
    if (!selectedVoteBook.value || !newVal.find(a => a.id === selectedVoteBook.value.id)) {
      selectedVoteBook.value = newVal[0];
      fetchLedger();
    }
  }
}, { immediate: true });

onMounted(() => {
  if (allAccounts.value.length > 0 && !selectedVoteBook.value) {
    selectedVoteBook.value = allAccounts.value[0];
    fetchLedger();
  }
});
</script>

<style scoped>
@media print {
  header, .action-toolbar, button, select, input, .print-hide {
    display: none !important;
  }
  body, main {
    background: white !important;
    padding: 0 !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  th, td {
    border: 1px solid #ddd !important;
    padding: 6px !important;
    font-size: 10pt !important;
  }
}
</style>
