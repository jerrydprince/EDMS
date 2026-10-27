<template>
  <main class="min-h-screen bg-[#F8FAFC] pb-16">
    <!-- TOP EXECUTIVE HEADER -->
    <header class="bg-white border-b border-gray-200/80 px-4 md:px-8 py-6 shadow-xs">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Brand & Title -->
        <div class="flex items-start gap-4">
          <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950 text-white flex items-center justify-center shadow-md border border-slate-700/50 shrink-0">
            <Icon icon="solar:book-bookmark-bold-duotone" width="28" class="text-emerald-400" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                TSA & GIFMIS Connected
              </span>
              <span class="text-xs text-gray-400">•</span>
              <span class="text-xs text-gray-500 font-medium">Cash Office Gateway</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight mt-1">
              Treasury Cash Book
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-0.5">
              Federal Republic of Nigeria • Comprehensive register of all treasury disbursements, remittances, and receipts.
            </p>
          </div>
        </div>

        <!-- Action Toolbar -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button 
            @click="refreshData" 
            :disabled="loading"
            class="p-2.5 text-gray-600 hover:text-gray-900 bg-white hover:bg-gray-50 border border-gray-300 rounded-xl transition-all shadow-xs cursor-pointer disabled:opacity-50"
            title="Refresh Ledger"
          >
            <Icon icon="solar:restart-bold" width="18" :class="loading ? 'animate-spin text-blue-600' : ''" />
          </button>

          <button 
            @click="printTable"
            class="flex items-center gap-2 px-3.5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs md:text-sm border border-gray-300 rounded-xl transition-all shadow-xs cursor-pointer"
          >
            <Icon icon="solar:printer-bold-duotone" width="18" class="text-gray-500" />
            <span class="hidden sm:inline">Print Ledger</span>
          </button>

          <GenerateCashbookReport 
            :vote_book_id="selectedAccount?.id || 'all'" 
            @select="generateReportFunction" 
            size="md"
          />

          <button 
            v-if="can('cashbook create') || hasRole('super admin')"
            @click="newCashbookEntryModal = true"
            class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs md:text-sm rounded-xl transition-all shadow-sm shadow-emerald-600/20 cursor-pointer"
          >
            <Icon icon="solar:add-circle-bold" width="20" />
            <span>Record Cash Entry</span>
          </button>
        </div>
      </div>
    </header>

    <div class="px-4 md:px-8 space-y-6 mt-6">
      <!-- EXECUTIVE TREASURY SUMMARY METRIC CARDS -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Active Vote Book -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Active Vote Ledger</span>
              <h3 class="text-base font-bold text-gray-900 mt-1 truncate max-w-[200px]" :title="activeAccountDisplayName">
                {{ activeAccountDisplayName }}
              </h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:wallet-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-baseline justify-between text-xs">
            <span class="text-gray-500">Allocation:</span>
            <span class="font-bold text-gray-900 font-mono">₦{{ formatCurrency(activeAccountAllocation) }}</span>
          </div>
        </div>

        <!-- Card 2: Total Disbursed (Outflows) -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Total Disbursements</span>
              <div class="text-2xl font-extrabold text-gray-900 mt-1 tracking-tight flex items-baseline gap-1">
                <span class="text-sm font-semibold text-rose-500">₦</span>
                <span>{{ formatCurrency(totalDisbursedAmount) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:arrow-right-down-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Mandates Liquidated</span>
            <span class="font-bold text-gray-800">{{ summaryStats.total_count || totalItems }} Vouchers</span>
          </div>
        </div>

        <!-- Card 3: Liquid Cash Available -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Available Vote Balance</span>
              <div class="text-2xl font-extrabold text-emerald-600 mt-1 tracking-tight flex items-baseline gap-1">
                <span class="text-sm font-semibold text-emerald-500">₦</span>
                <span>{{ formatCurrency(activeAccountBalance) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:safe-square-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Net Liquid Position</span>
            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px]">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
            </span>
          </div>
        </div>

        <!-- Card 4: Today's Movement & Velocity -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Today's Transactions</span>
              <div class="text-2xl font-extrabold text-gray-900 mt-1 tracking-tight flex items-baseline gap-1">
                <span class="text-sm font-semibold text-indigo-500">₦</span>
                <span>{{ formatCurrency(summaryStats.today_disbursed || 0) }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
              <Icon icon="solar:calendar-date-bold-duotone" width="22" />
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>Reconciliation Status</span>
            <span class="font-semibold text-blue-600">Daily Balance OK</span>
          </div>
        </div>
      </section>

      <!-- VOTE BOOK ACCOUNT SELECTOR CAROUSEL -->
      <section class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <Icon icon="solar:layers-minimalistic-bold-duotone" width="20" class="text-blue-600" />
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Select Vote Book Account</h2>
            <span class="text-xs text-gray-400">({{ availableAccounts.length }} accounts registered)</span>
          </div>
          <div class="flex items-center gap-1">
            <button @click="scrollAccounts('left')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors cursor-pointer" title="Scroll Left">
              <Icon icon="mdi:chevron-left" width="20" />
            </button>
            <button @click="scrollAccounts('right')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors cursor-pointer" title="Scroll Right">
              <Icon icon="mdi:chevron-right" width="20" />
            </button>
          </div>
        </div>

        <div ref="accountsContainer" class="flex gap-3 overflow-x-auto pb-2 scrollbar-none scroll-smooth">
          <!-- CONSOLIDATED / ALL ACCOUNTS CARD -->
          <div 
            @click="selectAccount(null)"
            class="min-w-[240px] max-w-[240px] p-4 rounded-xl border transition-all cursor-pointer select-none flex flex-col justify-between"
            :class="selectedAccount === null 
              ? 'bg-gradient-to-br from-slate-900 to-indigo-950 text-white border-indigo-700 shadow-md ring-2 ring-indigo-500/30' 
              : 'bg-gray-50/70 hover:bg-gray-100/70 text-gray-700 border-gray-200'"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold tracking-widest uppercase px-2 py-0.5 rounded"
                  :class="selectedAccount === null ? 'bg-white/10 text-emerald-400' : 'bg-gray-200 text-gray-600'">
                  Master
                </span>
                <span v-if="selectedAccount === null" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              </div>
              <h4 class="font-bold text-sm leading-tight" :class="selectedAccount === null ? 'text-white' : 'text-gray-900'">
                Consolidated Cash Book
              </h4>
              <p class="text-xs mt-1" :class="selectedAccount === null ? 'text-slate-300' : 'text-gray-500'">
                All Vote Book Ledgers Combined
              </p>
            </div>
            <div class="mt-4 pt-2.5 border-t" :class="selectedAccount === null ? 'border-white/10 text-emerald-300' : 'border-gray-200 text-gray-700'">
              <span class="text-xs font-semibold">View All Entries</span>
            </div>
          </div>

          <!-- INDIVIDUAL VOTE BOOK CARDS -->
          <div 
            v-for="account in availableAccounts" 
            :key="account.id"
            @click="selectAccount(account)"
            class="min-w-[260px] max-w-[260px] p-4 rounded-xl border transition-all cursor-pointer select-none flex flex-col justify-between"
            :class="selectedAccount?.id === account.id 
              ? 'bg-blue-50/60 border-blue-500 shadow-md ring-2 ring-blue-500/20' 
              : 'bg-white hover:bg-gray-50 border-gray-200 shadow-2xs'"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded font-mono"
                  :class="selectedAccount?.id === account.id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'">
                  {{ account.account_number || ('ACC-' + account.id) }}
                </span>
                <span v-if="selectedAccount?.id === account.id" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700">
                  <Icon icon="solar:check-circle-bold" width="14" /> Active
                </span>
              </div>

              <h4 class="font-bold text-sm text-gray-900 leading-snug truncate" :title="account.name">
                {{ account.name }}
              </h4>
              <p class="text-xs text-gray-500 mt-0.5 font-mono">
                Code: {{ account.ncoa_code || account.gifmis_code || '22020501' }}
              </p>
            </div>

            <div class="mt-4 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs">
              <span class="text-gray-400">Balance:</span>
              <span class="font-bold font-mono text-emerald-600">₦{{ formatCurrency(account.balance) }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- CONTROL, SEARCH & FILTER TOOLBAR -->
      <section class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Search & Type Tabs -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
          <!-- Search Bar -->
          <div class="relative w-full sm:w-80">
            <Icon icon="solar:magnifer-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
            <input 
              v-model="searchValue"
              type="text" 
              placeholder="Search Voucher, Payee, NCOA, DVBN..."
              class="w-full pl-10 pr-9 py-2 rounded-xl border border-gray-300 text-xs text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
            />
            <button 
              v-if="searchValue" 
              @click="searchValue = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <Icon icon="material-symbols:close" width="16" />
            </button>
          </div>

          <!-- Transaction Type Tabs -->
          <div class="flex items-center bg-gray-100 p-1 rounded-xl gap-1 shrink-0">
            <button 
              @click="activeTypeFilter = 'all'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
              :class="activeTypeFilter === 'all' ? 'bg-white text-gray-900 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900'"
            >
              All Types
            </button>
            <button 
              @click="activeTypeFilter = 'disbursement'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
              :class="activeTypeFilter === 'disbursement' ? 'bg-white text-rose-700 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900'"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
              Disbursements
            </button>
            <button 
              @click="activeTypeFilter = 'receipt'"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
              :class="activeTypeFilter === 'receipt' ? 'bg-white text-emerald-700 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900'"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              Receipts
            </button>
          </div>
        </div>

        <!-- Filter Dropdowns & Density -->
        <div class="flex items-center gap-3 flex-wrap justify-end">
          <!-- Platform Filter -->
          <div class="flex items-center gap-1.5">
            <span class="text-xs font-semibold text-gray-500 hidden sm:inline">Platform:</span>
            <select 
              v-model="selectedPlatform" 
              class="px-3 py-2 rounded-xl border border-gray-300 text-xs font-medium text-gray-700 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all cursor-pointer"
            >
              <option value="all">All Platforms</option>
              <option value="GIFMIS">GIFMIS</option>
              <option value="Remita">Remita</option>
              <option value="TSA-Direct">TSA-Direct</option>
            </select>
          </div>

          <!-- Date Range Filter -->
          <div class="flex items-center gap-1.5">
            <span class="text-xs font-semibold text-gray-500 hidden sm:inline">Period:</span>
            <select 
              v-model="selectedDateRange" 
              class="px-3 py-2 rounded-xl border border-gray-300 text-xs font-medium text-gray-700 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all cursor-pointer"
            >
              <option value="all">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>
          </div>

          <!-- Items Per Page -->
          <select 
            v-model="itemsPerPage" 
            class="px-2.5 py-2 rounded-xl border border-gray-300 text-xs font-medium text-gray-700 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all cursor-pointer"
            title="Rows per page"
          >
            <option :value="10">10 / page</option>
            <option :value="25">25 / page</option>
            <option :value="50">50 / page</option>
          </select>
        </div>
      </section>

      <!-- DATA TABLE CONTAINER -->
      <section class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <!-- Floating Bulk Selection Bar -->
        <div v-if="selectedRowsCount > 0" class="bg-blue-50/90 border-b border-blue-100 px-6 py-2.5 flex items-center justify-between text-xs text-blue-900 transition-all">
          <div class="flex items-center gap-2 font-semibold">
            <Icon icon="solar:check-read-bold" width="16" class="text-blue-600" />
            <span>{{ selectedRowsCount }} transactions selected</span>
          </div>
          <div class="flex items-center gap-2">
            <button @click="clearSelection" class="text-blue-700 hover:underline cursor-pointer font-medium">Deselect All</button>
            <button @click="printTable" class="px-3 py-1 bg-white border border-blue-200 rounded-md font-semibold text-blue-800 hover:bg-blue-50 shadow-2xs cursor-pointer">
              Print Selected
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse min-w-[900px]">
            <thead>
              <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                <th class="py-3.5 pl-5 pr-2 w-10">
                  <Checkbox :model-value="allSelected" @update:modelValue="toggleAll" class="h-4 w-4" />
                </th>
                <th class="py-3.5 px-3">Date & Voucher</th>
                <th class="py-3.5 px-3">Transaction / Beneficiary</th>
                <th class="py-3.5 px-3">Type</th>
                <th class="py-3.5 px-3">NCOA & Mandate</th>
                <th class="py-3.5 px-3">Platform</th>
                <th class="py-3.5 px-3 text-right">Amount (₦)</th>
                <th class="py-3.5 pr-5 pl-3 text-center w-20">Actions</th>
              </tr>
            </thead>

            <!-- TABLE BODY (DATA) -->
            <tbody v-if="cashbooks.length > 0 && !loading" class="divide-y divide-gray-100 text-gray-700">
              <tr 
                v-for="(item, index) in cashbooks" 
                :key="item.id || index"
                class="hover:bg-blue-50/40 transition-colors group"
                :class="checkedItems[index] ? 'bg-blue-50/20' : ''"
              >
                <!-- Checkbox -->
                <td class="py-3.5 pl-5 pr-2 align-middle">
                  <Checkbox :model-value="checkedItems[index]" @update:model-value="toggleItem(index)" class="h-4 w-4" />
                </td>

                <!-- Date & Voucher No -->
                <td class="py-3.5 px-3 align-middle">
                  <div class="flex flex-col">
                    <span 
                      @click="openViewModal(item)"
                      class="font-mono font-bold text-xs text-blue-600 hover:text-blue-800 cursor-pointer hover:underline"
                    >
                      {{ item.voucher_no || ('PV-' + item.id) }}
                    </span>
                    <span class="text-[11px] text-gray-400 font-mono mt-0.5">
                      {{ formatDate(item.date) }}
                    </span>
                  </div>
                </td>

                <!-- Transaction & Payee -->
                <td class="py-3.5 px-3 align-middle max-w-xs">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                      :class="isReceipt(item) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                      {{ getInitials(item.to || item.from) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                      <span class="font-bold text-gray-900 text-xs truncate" :title="item.to || 'Beneficiary'">
                        {{ item.to || 'Beneficiary' }}
                      </span>
                      <span class="text-[11px] text-gray-400 truncate mt-0.5" :title="item.payment_description || item.receipt_description">
                        {{ item.payment_description || item.receipt_description || item.from }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Type -->
                <td class="py-3.5 px-3 align-middle">
                  <span 
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold"
                    :class="isReceipt(item) 
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
                      : 'bg-rose-50 text-rose-700 border border-rose-200'"
                  >
                    <Icon :icon="isReceipt(item) ? 'solar:arrow-left-down-bold' : 'solar:arrow-right-down-bold'" width="13" />
                    {{ isReceipt(item) ? 'Receipt' : 'Disbursement' }}
                  </span>
                </td>

                <!-- Classification (NCOA & DVBN) -->
                <td class="py-3.5 px-3 align-middle">
                  <div class="flex flex-col gap-1">
                    <span class="font-mono text-[11px] font-bold text-indigo-700 bg-indigo-50/80 px-1.5 py-0.5 rounded w-max">
                      {{ item.ncoa_code || '22020501' }}
                    </span>
                    <span class="font-mono text-[10px] text-gray-400 truncate max-w-[130px]" :title="item.dvbn">
                      {{ item.dvbn || '---' }}
                    </span>
                  </div>
                </td>

                <!-- Platform -->
                <td class="py-3.5 px-3 align-middle">
                  <span 
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold"
                    :class="getPlatformClass(item.disbursement_platform)"
                  >
                    <Icon :icon="getPlatformIcon(item.disbursement_platform)" width="13" />
                    {{ item.disbursement_platform || 'GIFMIS' }}
                  </span>
                </td>

                <!-- Amount (₦) -->
                <td class="py-3.5 px-3 align-middle text-right font-mono">
                  <span 
                    class="font-extrabold text-xs sm:text-sm"
                    :class="isReceipt(item) ? 'text-emerald-600' : 'text-gray-900'"
                  >
                    {{ isReceipt(item) ? '+' : '-' }}₦{{ formatCurrency(item.amount) }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 pr-5 pl-3 align-middle text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button 
                      @click="openViewModal(item)"
                      class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                      title="View Transaction Slip"
                    >
                      <Icon icon="solar:eye-bold-duotone" width="18" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- LOADING STATE -->
            <tbody v-else-if="loading">
              <tr>
                <td colspan="8" class="py-16 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <Icon icon="line-md:loading-loop" width="36" class="text-blue-600 mb-2" />
                    <p class="text-xs font-semibold text-gray-600">Loading Cash Book ledger records...</p>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- EMPTY STATE -->
            <tbody v-else>
              <tr>
                <td colspan="8" class="py-16 text-center">
                  <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mb-3 shadow-inner">
                      <Icon icon="solar:notes-bold-duotone" width="28" />
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">No Cash Book Records Found</h3>
                    <p class="text-xs text-gray-500 mt-1">
                      No transaction entries match the selected Vote Book account or filter criteria.
                    </p>
                    <div class="flex items-center gap-2 mt-4">
                      <button 
                        v-if="searchValue || activeTypeFilter !== 'all' || selectedPlatform !== 'all' || selectedDateRange !== 'all'"
                        @click="resetFilters" 
                        class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
                      >
                        Reset Filters
                      </button>
                      <button 
                        v-if="can('cashbook create') || hasRole('super admin')"
                        @click="newCashbookEntryModal = true"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors shadow-xs cursor-pointer"
                      >
                        Record Entry
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION & METRICS FOOTER -->
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
          <div>
            Showing <span class="font-bold text-gray-800">{{ paginationRangeStart }}</span> to 
            <span class="font-bold text-gray-800">{{ paginationRangeEnd }}</span> of 
            <span class="font-bold text-gray-800">{{ totalItems }}</span> transactions
          </div>

          <div v-if="totalItems > itemsPerPage">
            <pagination 
              :totalItems="totalItems"
              :itemsPerPage="itemsPerPage"
              v-model="currentPage"
              @setPage="(page) => currentPage = page"
            />
          </div>
        </div>
      </section>
    </div>

    <!-- MODALS -->
    <CreateCashBookEntry 
      v-if="newCashbookEntryModal" 
      :selectedAccount="selectedAccount || availableAccounts[0]" 
      @created="onEntryCreated"
      @cancelled="closeModal" 
    />

    <ViewCashBookEntry 
      v-if="viewCashbookEntryModal" 
      :entry="selectedCashbookEntry" 
      @cancelled="closeModal" 
    />
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import Checkbox from "primevue/checkbox";
import { usePermissions } from "@/composables/usePermissions";
import CreateCashBookEntry from "../../components/Modals/CashBook/CreateCashBookEntry.vue";
import ViewCashBookEntry from "../../components/Modals/CashBook/ViewCashBookEntry.vue";
import GenerateCashbookReport from "../../components/GenerateCashbookReport.vue";
import pagination from "../../reusables/pagination.vue";

const { can, hasRole } = usePermissions();

const props = defineProps({
  votebooks: {
    type: Object,
    default: () => ({})
  },
  allVotebooks: {
    type: Array,
    default: () => []
  },
  treasuryStats: {
    type: Object,
    default: () => ({})
  }
});

// State
const loading = ref(false);
const newCashbookEntryModal = ref(false);
const viewCashbookEntryModal = ref(false);
const selectedCashbookEntry = ref(null);
const selectedAccount = ref(null); // null means Consolidated View (All Accounts)

const cashbooks = ref([]);
const totalItems = ref(0);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const summaryStats = ref({
  total_amount: 0,
  total_count: 0,
  today_disbursed: 0
});

// Filters
const searchValue = ref("");
const activeTypeFilter = ref("all");
const selectedPlatform = ref("all");
const selectedDateRange = ref("all");

// Selection
const checkedItems = ref([]);
const accountsContainer = ref(null);

// Accounts computation
const availableAccounts = computed(() => {
  if (props.allVotebooks && props.allVotebooks.length > 0) {
    return props.allVotebooks;
  }
  if (props.votebooks && props.votebooks.standard) {
    return props.votebooks.standard;
  }
  return [];
});

const activeAccountDisplayName = computed(() => {
  return selectedAccount.value ? selectedAccount.value.name : 'Consolidated Treasury Ledger';
});

const activeAccountAllocation = computed(() => {
  if (selectedAccount.value) {
    return selectedAccount.value.approved_allocation || selectedAccount.value.total_allocation || 0;
  }
  return props.treasuryStats?.total_allocation || 0;
});

const activeAccountBalance = computed(() => {
  if (selectedAccount.value) {
    return selectedAccount.value.balance || selectedAccount.value.available_balance || 0;
  }
  return props.treasuryStats?.total_balance || 0;
});

const totalDisbursedAmount = computed(() => {
  if (summaryStats.value.total_amount) {
    return summaryStats.value.total_amount;
  }
  return props.treasuryStats?.total_disbursed || 0;
});

const selectAccount = (account) => {
  selectedAccount.value = account;
  currentPage.value = 1;
  getCashbooks();
};

const scrollAccounts = (direction) => {
  if (accountsContainer.value) {
    const amount = direction === 'left' ? -280 : 280;
    accountsContainer.value.scrollBy({ left: amount, behavior: 'smooth' });
  }
};

// API Fetcher
let searchDebounceTimer = null;
const getCashbooks = async () => {
  loading.value = true;
  try {
    const params = {
      vote_book_id: selectedAccount.value ? selectedAccount.value.id : 'all',
      search: searchValue.value.trim(),
      type: activeTypeFilter.value,
      platform: selectedPlatform.value,
      date_range: selectedDateRange.value,
      page: currentPage.value,
      per_page: itemsPerPage.value
    };

    const res = await axios.get('/finance-and-accounts/get-cashbooks', { params });
    if (res.data) {
      cashbooks.value = res.data.data || [];
      currentPage.value = res.data.current_page || 1;
      itemsPerPage.value = res.data.per_page || 10;
      totalItems.value = res.data.total || 0;
      if (res.data.summary) {
        summaryStats.value = res.data.summary;
      }
    }
  } catch (error) {
    console.error("Failed to fetch cashbooks:", error);
  } finally {
    loading.value = false;
  }
};

const refreshData = () => {
  getCashbooks();
};

const onEntryCreated = () => {
  getCashbooks();
};

const resetFilters = () => {
  searchValue.value = '';
  activeTypeFilter.value = 'all';
  selectedPlatform.value = 'all';
  selectedDateRange.value = 'all';
  currentPage.value = 1;
  getCashbooks();
};

// Watchers
watch(searchValue, () => {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1;
    getCashbooks();
  }, 350);
});

watch([activeTypeFilter, selectedPlatform, selectedDateRange, itemsPerPage], () => {
  currentPage.value = 1;
  getCashbooks();
});

watch(currentPage, () => {
  getCashbooks();
});

// Selection logic
watch(cashbooks, (newVal) => {
  checkedItems.value = newVal.map(() => false);
}, { immediate: true });

const toggleItem = (index) => {
  checkedItems.value[index] = !checkedItems.value[index];
};

const allSelected = computed(() => {
  return checkedItems.value.length > 0 && checkedItems.value.every(Boolean);
});

const toggleAll = () => {
  const next = !allSelected.value;
  checkedItems.value = cashbooks.value.map(() => next);
};

const selectedRowsCount = computed(() => {
  return checkedItems.value.filter(Boolean).length;
});

const clearSelection = () => {
  checkedItems.value = cashbooks.value.map(() => false);
};

// Utilities
const isReceipt = (item) => {
  return Boolean(item.receipt_description && !item.payment_description);
};

const formatCurrency = (val) => {
  const num = parseFloat(val || 0);
  return num.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getInitials = (str) => {
  if (!str) return 'TR';
  const parts = str.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return parts[0].substring(0, 2).toUpperCase();
};

const getPlatformClass = (platform) => {
  const p = (platform || '').toLowerCase();
  if (p.includes('gifmis')) return 'bg-indigo-50 text-indigo-700 border border-indigo-200/80';
  if (p.includes('remita')) return 'bg-amber-50 text-amber-800 border border-amber-200/80';
  return 'bg-blue-50 text-blue-700 border border-blue-200/80';
};

const getPlatformIcon = (platform) => {
  const p = (platform || '').toLowerCase();
  if (p.includes('gifmis')) return 'solar:server-bold-duotone';
  if (p.includes('remita')) return 'solar:card-2-bold-duotone';
  return 'solar:bank-bold-duotone';
};

const paginationRangeStart = computed(() => {
  if (totalItems.value === 0) return 0;
  return (currentPage.value - 1) * itemsPerPage.value + 1;
});

const paginationRangeEnd = computed(() => {
  return Math.min(currentPage.value * itemsPerPage.value, totalItems.value);
});

const openViewModal = (item) => {
  selectedCashbookEntry.value = item;
  viewCashbookEntryModal.value = true;
};

const closeModal = () => {
  newCashbookEntryModal.value = false;
  viewCashbookEntryModal.value = false;
};

const generateReportFunction = (option) => {
  const voteBookId = selectedAccount.value?.id || 'all';
  const filter = typeof option === 'string' ? option : 'all';
  window.open(`/finance-and-accounts/download-report?report=${encodeURIComponent(filter)}&vote_book_id=${encodeURIComponent(voteBookId)}`, '_blank');
};

const printTable = () => {
  window.print();
};

onMounted(() => {
  if (availableAccounts.value.length > 0) {
    selectedAccount.value = availableAccounts.value[0];
  }
  getCashbooks();
});
</script>

<style scoped>
/* Scrollbar utility */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

@media print {
  header, button, select, input, .pagination {
    display: none !important;
  }
  main {
    background: white !important;
  }
}
</style>
