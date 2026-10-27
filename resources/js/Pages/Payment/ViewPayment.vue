<template>
  <main class="p-5 md:p-7 bg-slate-50">
    <!-- <iframe
      src="/src/templates/voucherTemplate.html"
      class="w-full h-[800px]"
    ></iframe> -->
    <!-- EBEWE Action Panel replacing hardcoded role logic -->
    <EbeweActionPanel 
      :payment="payment" 
      :roles="roles" 
      :user="user"
      @openParallelChecklist="() => {}"
      @openDigitalStamp="digitalStampModal = true"
      @reject="rejectPaymentModal = true"
      @assign="setAssignTaskModal"
      @accept="acceptTask"
    />
    <!-- Federal Treasury 7-Stage Milestone Progression Tracker -->
    <PaymentMilestoneTracker :payment="payment" :auditQueries="auditQueries" />
    <section class="flex lg:flex-row flex-col-reverse items-start gap-10 w-full">
      <div class="bg-white w-full lg:w-[60%] rounded-lg p-5">
        <div class="pb-5 flex items-center justify-between">
          <div class="flex items-center gap-6">
            <Link :href="'/finance-and-accounts'">
            <Icon icon="flowbite:angle-left-outline" class="cursor-pointer" />
            </Link>

            <div class="p-2 md:p-4 border rounded-lg">
              <Icon icon="lucide:file-text" width="25" />
            </div>
            <p class="text-base md:text-2xl text-gray-900">{{ payment.title }}</p>
          </div>
          <button @click="downloadCurrentFile" v-if="!previewVoucher"
            class="hidden md:flex items-center gap-2 py-2 w-auto border-gray-200 border border-solid rounded-lg px-4 bg-white text-sm"
            label="Cancel">
            <Icon class="cursor-pointer" icon="material-symbols-light:download" width="20" />
            Download
          </button>
        </div>

        <div class="h-[800px] overflow-y-auto hide_scroll">
          <div>
            <div class="bg-slate-50 p-2 md:p-3 w-full rounded-xl">
              <!-- DIGITAL STAMPS SECTION -->
              <div v-if="ebewe_stamps && ebewe_stamps.length > 0" class="w-full flex gap-4 overflow-x-auto p-4 bg-white border border-slate-200 rounded-lg mb-4">
                <div v-for="stamp in ebewe_stamps" :key="stamp.id" 
                     :class="['relative flex-shrink-0 flex items-center justify-center p-4 border-2 rounded-lg rotate-[-2deg] opacity-90', stamp.color, stamp.color.replace('border-', 'text-')]">
                  <div :class="['absolute inset-1 border border-dashed rounded', stamp.color]"></div>
                  <div class="text-center relative z-10 bg-white/60 px-2 py-1 rounded">
                    <h2 class="text-sm font-black uppercase tracking-wider mb-1">{{ stamp.label }}</h2>
                    <p class="text-[8px] font-mono">ID: {{ stamp.applied_by }}-{{ String(stamp.id).padStart(4, '0') }}</p>
                    <p class="text-[8px] font-mono mt-1">{{ new Date(stamp.created_at).toLocaleDateString() }}</p>
                    <p class="text-[8px] font-bold mt-1 uppercase">{{ stamp.stamp_type }}</p>
                  </div>
                </div>
              </div>

              <!-- UNIFIED PDF EDITOR (FOR VOUCHERS AND ATTACHED PDF DOCUMENTS) -->
              <div v-if="activePreviewPdfUrl" class="w-full h-[760px] min-h-[700px] rounded-xl overflow-hidden border border-gray-200 shadow-sm bg-white">
                <PDFEditor ref="pdfEditorRef" :url="activePreviewPdfUrl" @save-pdf="handleSavePdf" />
              </div>

              <!-- IMAGE DOCUMENT PREVIEW -->
              <div v-else-if="activePreviewImageUrl" class="w-full h-[760px] flex items-center justify-center bg-gray-50 rounded-xl p-4 border border-gray-200">
                <img :src="activePreviewImageUrl" alt="Document Preview" class="rounded-lg max-w-full max-h-[720px] object-contain shadow-sm" />
              </div>

              <!-- FALLBACK EMPTY STATE -->
              <div v-else class="w-full h-[600px] flex flex-col items-center justify-center bg-white rounded-xl border-2 border-dashed border-gray-200 p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-4">
                  <Icon icon="lucide:file-text" class="w-8 h-8" />
                </div>
                <p class="text-gray-800 font-bold text-lg">No Document or Voucher Selected</p>
                <p class="text-sm text-gray-500 mt-1 max-w-sm">Select an attached document from the list or click any appended voucher in the sidebar to preview.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white lg:block w-full lg:w-[40%] h-[920px] rounded-xl">
        <div class="flex lg:hidden items-center gap-6 p-4">
          <Icon @click="goBack" icon="flowbite:angle-left-outline" class="cursor-pointer" />
          <div class="p-2 md:p-4 border rounded-lg">
            <Icon icon="lucide:file-text" width="25" />
          </div>
          <p class="text-base md:text-2xl text-gray-900">Vendor Contracts</p>
        </div>
        <div v-if="!sidemenu && !getActiveCustomSideMenu">
          <!-- Universal Directorate Action Desk (F&A, Procurement, Administration) -->
          <div class="p-3 border-b border-gray-100">
            <UniversalDirectorateActionDesk 
              :payment="payment" 
              :user="user" 
              :roles="roles" 
              :permissions="permissions"
              :activeQueries="auditQueries" 
              @refresh="reloadPayment" 
              @open-create-voucher="handleOpenCreateVoucher"
              @open-charge-account="chargeAccountModal = true"
            />
          </div>
          <section>
            <!-- ACCESS LEVELS: ADVANCE SECTION -->
            <div class="px-2 pt-2">
              <!-- VIEW LEDGER ENTRY STARTS -->
              <div  v-if="(roles.includes('advance section(HOS)') || roles.includes('advance section') || user?.section_id === 82) && payment.ledger_count > 0" @click="viewLedgerEntryModal = true">
                <div class="flex justify-between bg-[#F3F5F7] bg-linear-to-r px-5 py-5 rounded-xl">
                  <div class="flex items-center gap-x-3 text-gray-800 font-medium">
                    <Icon icon="icon-park-outline:bookshelf" width="20" />
                    <p class="">View Ledger Entry</p>
                  </div>
                  <div class="flex items-center gap-x-1 text-gray-400 hover:underline cursor-pointer">
                    <p>Go</p>
                    <Icon icon="tdesign:chevron-right" width="20" class="mt-[2px]" />
                  </div>
                </div>
              </div>
              <!-- VIEW LEDGER ENTRY ENDS -->

              <!-- CREATE LEDGER ENTRY STARTS -->
              <div v-if="(roles.includes('advance section(HOS)') || roles.includes('advance section') || user?.section_id === 82) && payment.ledger_count == 0 && (!roles.includes('other charges(HOS)') && user?.section_id !== 81)"  class="px-2 pt-2 cursor-pointer">
                <div class="flex justify-between bg-[#FFF9F5] bg-linear-to-r px-5 py-5 rounded-xl">
                  <div class="flex items-center gap-x-3 text-[#DC6803] font-medium">
                    <Icon icon="icon-park-outline:bookshelf" width="20" />
                    <p class="">Create Ledger Entry</p>
                  </div>
                  <div @click="CreateNewLedgerEntry" class="flex items-center gap-x-1 text-gray-400 hover:underline cursor-pointer">
                    <p>Go</p>
                    <Icon icon="tdesign:chevron-right" width="20" class="mt-[2px]" />
                  </div>
                </div>
              </div>
              <!-- CREATE LEDGER ENTRY ENDS -->
            </div>

            <!-- ACCESS LEVELS: OTHER CHARGES -->
            <div v-if="roles.includes('other charges(HOS)') || roles.includes('other charges') || user?.section_id === 81">
              <!-- CREATE LEDGER ENTRY STARTS -->
              <div v-if="payment.vat_voucher.length == 0" class="px-2 pt-2 cursor-pointer" @click="chargeAccountModal = true">
                <div class="flex justify-between bg-[#FFF9F5] bg-linear-to-r px-5 py-5 rounded-xl">
                  <div class="flex items-center gap-x-3 text-[#DC6803] font-medium">
                    <Icon icon="ri:coupon-2-line" width="20" />
                    <p class="">Charge Account</p>
                  </div>
                  <div class="flex items-center gap-x-1 text-gray-400 hover:underline">
                    <p>Go</p>
                    <Icon icon="tdesign:chevron-right" width="20" class="mt-[2px]" />
                  </div>
                </div>
              </div>
              <!-- CREATE LEDGER ENTRY ENDS -->
            </div>
          </section>

          <div class="flex flex-col items-start gap-y-3 p-7">
            <p class="text-sm text-gray-500">Task Title</p>
            <p class="text-gray-800 text-xl md:text-2xl">{{ payment.title }}</p>
            <div class="bg-gray-100 rounded-2xl py-1 px-4 text-center text-sm" v-if="roles.includes('director')">
              <!-- STATUS VARIATIONS -->
              <p class="font-medium" :style="{
                color:
                  payment.status === 'due'
                    ? '#0D6E1A'
                    : payment.status === 'overdue'
                      ? '#F04438'
                      : payment.status === 'resolved'
                        ? '#101828'
                        : 'black',
              }">
                <span class="font-black">•</span> {{ payment.status }}
              </p>
            </div>
            <div v-else class="bg-gray-100 rounded-2xl py-1 px-4 text-center text-sm">
              <!-- STATUS VARIATIONS -->
              <p class="font-medium" :style="{
                color:
                  payment?.assigned_by?.status === 'due'
                    ? '#0D6E1A'
                    :payment?.assigned_by?.status === 'done'
                    ? '#0D6E1A'
                    : payment?.assigned_by?.status === 'overdue'
                      ? '#F04438'
                      : payment?.assigned_by?.status === 'pending'
                        ? '#101828'
                        : 'black',
              }">
                <span class="font-black">•</span> {{ payment?.assigned_by?.status }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-12 text-gray-900 bg-gray-50 px-6 mb-7 font-light" v-if="roles.includes('director')">
            <p @click="toggleSidemenu('back')" :class="!progressMonitor
              ? 'border-primary'
              : 'text-gray-500 border-transparent'
              " class="py-3 border-b-2 cursor-pointer">
              Payments Details
            </p>
            <p @click="toggleSidemenu('progressMonitor')" :class="progressMonitor
              ? 'border-primary'
              : 'text-gray-500 border-transparent'
              " class="py-3 border-b-2 cursor-pointer">
              Progress Monitor
            </p>
          </div>

          <section class="h-[665px] overflow-y-scroll hide_scroll pb-20" v-if="!progressMonitor">
            <section class="p-2 text-gray-600">
              <div v-if="roles.includes('CPO') || roles.includes('CPO(HOS)')|| roles.includes('final account(HOS)') || roles.includes('final account')" 
                class="bg-gray-100 rounded-xl mb-3 py-2 px-3 cursor-pointer">
                <div class="flex items-center justify-between" @click="paymentReceiptModal = true">
                  <div class="flex items-center gap-x-2">
                    <img :src="VoteBookImg" alt="votebook icon" width="80" height="80"
                      class="-ml-4 -mr-3 -mb-3 drop-shadow-md" />
                    <div>
                      <p class="text-gray-700 font-[450] mb-1">
                        Asset Acquisitions Account
                      </p>
                      <p class="text-gray-400 font-light text-xs">
                        View payment details
                        <span v-if="!data.appendedPaymentReceipt?.length">& Upload Receipt.</span>
                      </p>
                    </div>
                  </div>
                  <Icon icon="mdi:chevron-right" width="25" class="text-gray-400" />
                </div>
                <!-- APPENDED PAYMENT RECEIPTS HERE -->
                <div v-if="payment.receipt.length > 0">
                  <div v-for="(document, index) in payment.receipt" :key="index"
                    class="rounded-xl p-4 flex items-center justify-between mb-3 bg-white">
                    <div class="flex items-center gap-3" @click="setCurrentFile(document, index)">
                      <div class="bg-white w-fit p-2 rounded-full">
                        <Icon :icon="getIcon(document)" width="25" />
                      </div>
                      <div>
                        <h1>{{ document.path.split('/').pop() }}</h1>
                        <p class="text-sm text-gray-400">
                          {{ document.size }}
                        </p>
                      </div>
                    </div>

                    <div class="bg-white rounded-full p-1 drop-shadow-md text-gray-400 cursor-pointer">
                      <Icon class="cursor-pointer" icon="uil:times" width="16" @click="removeReceipt(document)" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-100 px-3 py-1 rounded-xl">
                <div class="mt-3">
                  <div class="flex justify-between mb-5">
                    <div class="flex items-center gap-3 text-gray-500">
                      <Icon icon="grommet-icons:attachment" width="15" />
                      <p class="font-light text-sm">Attached Document</p>
                    </div>
                    <!-- <div @click="router.push(`/payment/edit-selection/${data.id}`)"

                    </div> -->
                    <a :href="`/finance-and-accounts?id=${payment.id}`" v-if="roles.includes('director')"
                      class="flex items-center gap-3 text-gray-700 cursor-pointer">
                      <Icon :icon="'lucide:edit-3'" width="15" />
                      <p class="text-sm">Edit Selection</p>
                    </a>
                  </div>
                  <div v-for="(document, index) in payment.files" :key="index" :class="[
                    'border-2  hover:border-primary rounded-xl cursor-pointer p-4 flex items-center justify-between mb-3 bg-white',
                    currentIndex === index ? 'border-primary' : 'border-transparent'
                  ]" @click="setCurrentFile(document, index)">
                    <div class="flex items-center gap-3">
                      <div class="bg-white w-fit p-2 rounded-full">
                        <Icon :icon="getFileIcon(document.path)" width="25" />
                      </div>
                      <div>
                        <h1>{{ getFileName(document) }}</h1>
                        <p class="text-sm text-gray-400">{{ document.size }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3">
                      <Icon class="cursor-pointer" icon="ph:eye-light" width="25" />
                    </div>
                  </div>
                </div>
                <!-- GENERATE VOUCHER AND APPENDED VOUCHER BLOCK START -->
                <section>
                  <!-- Append Voucher Block -->
                  <div v-if="(roles.includes('advance section') || roles.includes('advance section(HOS)')) && payment.voucher.length ==0 && payment.ledger_count > 0" class="mt-6">
                    <div @click="
                      () => {
                        if (data.ledgerCreated) createVoucherModal = true;
                      }
                    " v-tooltip.bottom="{
                      value: data.ledgerCreated
                        ? ''
                        : 'Ledger entry have to be made before vouchers can be generated.',
                      pt: {
                        arrow: {
                          style: {
                            borderRightColor: '',
                          },
                        },
                        text: 'bg-[#1D2939] text-xs text-white px-4 py-2 rounded-xl',
                      },
                    }" class="flex items-center ml-3 pb-2 text-base" :class="data.ledgerCreated
                      ? 'text-[#0B5C16] cursor-pointer'
                      : 'text-gray-400 cursor-not-allowed'
                      ">
                      <p>Generate Vouchers</p>
                      <Icon icon="tdesign:chevron-right" width="18" class="mt-[2px]" />
                    </div>
                  </div>
                  <!-- View Appended Voucher Block -->
                  <div v-if="payment.voucher.length > 0 || payment.vat_voucher.length > 0" class="mt-6">
                    <div class="flex justify-between mb-5">
                      <div class="flex items-center gap-3 text-gray-500">
                        <Icon icon="ri:coupon-2-line" width="17" />
                        <p class="font-light text-sm">Appended Voucher(s)</p>
                      </div>
                      <div v-if="(roles.includes('advance section(HOS)') || roles.includes('advanced section')) && payment.voucher.length > 0" class="flex items-center gap-2 text-[#0B5C16] cursor-pointer">
                        <p class="text-sm" @click="editVoucher">Edit Voucher info</p>
                        <Icon
                          icon="tdesign:chevron-right"
                          width="18"
                          class="mt-[2px]"
                        />
                      </div>
                    </div>
                    <!-- MAIN PAYMENT VOUCHERS -->
                    <div v-for="vou in vouchers" :key="'main-vou-' + (vou.id || vou.path)" 
                         @click="viewVoucher(vou)"
                         :class="[
                           'border-2 rounded-xl p-3 flex items-center justify-between mb-3 bg-white cursor-pointer transition-all hover:shadow-sm',
                           (previewVoucher && selectedVoucher && selectedVoucher.id === vou.id && !selectedVoucher.percentage) 
                             ? 'border-emerald-600 bg-emerald-50/30 ring-1 ring-emerald-500' 
                             : 'border-gray-200 hover:border-emerald-400'
                         ]">
                      <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex-shrink-0">
                          <Icon icon="lucide:file-check" width="22" />
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                              Main Voucher (TF1)
                            </span>
                            <span v-if="vou.voucher_number" class="text-xs font-mono text-gray-500 font-semibold">{{ vou.voucher_number }}</span>
                          </div>
                          <p class="text-xs font-semibold text-gray-800 mt-1">
                            Payee: {{ payment.title || 'Contractor' }}
                          </p>
                          <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                            Dept No: {{ vou.deptal_no || 'PV/CAP/' + payment.id }} &bull; Gross: &#8358;{{ Number(vou.amount || payment.amount || 0).toLocaleString('en-NG', {minimumFractionDigits: 2}) }}
                          </p>
                        </div>
                      </div>

                      <div class="flex items-center gap-2">
                        <button @click.stop="downloadVoucherPdf(vou)" class="p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" title="Download Voucher">
                          <Icon icon="material-symbols-light:download" width="20" />
                        </button>
                        <button @click.stop="viewVoucher(vou)" :class="['p-1.5 rounded-lg transition', (previewVoucher && selectedVoucher?.id === vou.id && !selectedVoucher.percentage) ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-emerald-700 hover:bg-emerald-50']" title="Preview in PDF Viewer">
                          <Icon icon="ph:eye-bold" width="18" />
                        </button>
                      </div>
                    </div>

                    <!-- DEDUCTION VOUCHERS (VAT, WHT, STAMP DUTY) -->
                    <div v-for="(vou, index) in payment.vat_voucher" :key="'vat-' + (vou.id || index)"
                         @click="viewVoucher(vou)"
                         :class="[
                           'border-2 rounded-xl p-3 flex items-center justify-between mb-3 bg-white cursor-pointer transition-all hover:shadow-sm',
                           (previewVoucher && selectedVoucher && selectedVoucher.id === vou.id && selectedVoucher.percentage) 
                             ? 'border-blue-600 bg-blue-50/30 ring-1 ring-blue-500' 
                             : 'border-gray-200 hover:border-blue-400'
                         ]">
                      <div class="flex items-center gap-3">
                        <div :class="['p-2 rounded-xl border flex-shrink-0', 
                                      vou.percentage === '7.5%' || (vou.path && vou.path.includes('vat')) ? 'bg-purple-50 text-purple-700 border-purple-100' : 
                                      vou.percentage === '10%' || (vou.path && vou.path.includes('wht')) ? 'bg-amber-50 text-amber-700 border-amber-100' : 
                                      'bg-teal-50 text-teal-700 border-teal-100']">
                          <Icon icon="lucide:receipt" width="22" />
                        </div>
                        <div>
                          <div class="flex items-center gap-2 flex-wrap">
                            <span :class="['text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border',
                                           vou.percentage === '7.5%' || (vou.path && vou.path.includes('vat')) ? 'bg-purple-100 text-purple-800 border-purple-200' : 
                                           vou.percentage === '10%' || (vou.path && vou.path.includes('wht')) ? 'bg-amber-100 text-amber-800 border-amber-200' : 
                                           'bg-teal-100 text-teal-800 border-teal-200']">
                              {{ vou.percentage === '7.5%' || (vou.path && vou.path.includes('vat')) ? 'VAT Voucher (7.5%)' :
                                 vou.percentage === '10%' || (vou.path && vou.path.includes('wht')) ? 'WHT Voucher (10%)' :
                                 'Stamp Duty Voucher (1%)' }}
                            </span>
                            <span class="text-xs font-mono text-gray-500 font-semibold">{{ vou.voucher_number || 'DEDUCTION-VOU' }}</span>
                          </div>
                          <p class="text-xs font-semibold text-gray-800 mt-1">
                            Payable to: {{ vou.percentage === '7.5%' || (vou.path && vou.path.includes('vat')) ? 'Federal Inland Revenue Service (FIRS)' :
                                           vou.percentage === '10%' || (vou.path && vou.path.includes('wht')) ? 'Federal / State Tax Authority' :
                                           'FIRS Stamp Duty Pool Account' }}
                          </p>
                          <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                            Deduction: &#8358;{{ Number(vou.deduction_amount || 0).toLocaleString('en-NG', {minimumFractionDigits: 2}) }}
                          </p>
                        </div>
                      </div>

                      <div class="flex items-center gap-2">
                        <button @click.stop="downloadVoucherPdf(vou)" class="p-1.5 text-gray-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition" title="Download Deduction Voucher">
                          <Icon icon="material-symbols-light:download" width="20" />
                        </button>
                        <button @click.stop="viewVoucher(vou)" :class="['p-1.5 rounded-lg transition', (previewVoucher && selectedVoucher?.id === vou.id && selectedVoucher.percentage) ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-blue-700 hover:bg-blue-50']" title="Preview in PDF Viewer">
                          <Icon icon="ph:eye-bold" width="18" />
                        </button>
                      </div>
                    </div>
                  </div>
                </section>
                <!-- GENERATE VOUCHER AND APPENDED VOUCHER BLOCK END -->
              </div>
              <div class="mt-6">
                <div class="flex mb-3 items-center gap-3 text-gray-500">
                  <Icon icon="bx:file" width="20" />
                  <p class="font-light text-sm">General Comments</p>
                </div>
                <div class="bg-gray-100 rounded-xl p-5">
                  <p class="text-sm text-gray-500">
                    {{ data.desc }}
                  </p>
                </div>
              </div>
              <!-- User Details -->
              <section class="p-5 text-gray-600 mt-5">
                <div>
                  <div class="flex items-center gap-14 font-light">
                    <div class="flex items-center gap-3 text-gray-500">
                      <Icon icon="lucide:users" />
                      <p class="whitespace-nowrap">Assigned To</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <div v-for="(item, index) in data.tags" :key="index">
                        <div
                          class="w-full flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border-gray-300">
                          <img :src="item.img" alt="" class="w-7" />
                          <p class="text-sm lg:text-base text-gray-800 w-fit">
                            {{ getAssignedTo() }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-20 mt-5 font-light">
                    <div class="flex items-center gap-3 text-gray-500">
                      <Icon icon="lucide:calendar" />
                      <p>Due date</p>
                    </div>
                    <div>
                      <p class="text-gray-800 w-fit">{{ formatDate(payment?.assigned_by?.due_date) }}</p>
                    </div>
                  </div>
                </div>
              </section>
            </section>
            <Divider class="mt-10" v-if="payment.assigned_by && payment.assigned_by.comments && payment.assigned_by.comments.length > 0" />

            <div v-if="payment.assigned_by && payment.assigned_by.comments && payment.assigned_by.comments.length > 0" class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
              @click="toggleCommentSidemenu(payment.assigned_by.recipient_id)">
              <div class="flex items-center gap-5 text-gray-700">
                <Icon icon="lucide:message-square" width="25" />
                <p class="text-xl">Task Assigned To Me Comments</p>
                <p
                  class="w-7 h-7 rounded-md text-[#0E7A1D] font-semibold bg-[#F8FBF9] flex items-center justify-center flex-col p-3">
                  {{ payment.assigned_by.comments.length }}
                </p>
              </div>
              <Icon icon="flowbite:angle-right-outline" class="text-gray-500" width="25" />
            </div>
            <Divider class="mt-0" />

            <div v-if="payment.assigned_to && payment.assigned_to.length > 0">
              <div v-for="(assigned_to, index) in payment.assigned_to" :key="index" class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
                @click="toggleCustomSidemenu(index, assigned_to.assigned_by)">
                <div class="flex items-center gap-5 text-gray-700">
                  <Icon icon="lucide:message-square" width="25" />
                  <p class="text-xl">Assigned By Me Comments</p>
                  <p
                    class="w-7 h-7 rounded-md text-[#0E7A1D] font-semibold bg-[#F8FBF9] flex items-center justify-center flex-col p-3">
                    {{ assigned_to.comments.length }}
                  </p>
                </div>
                <Icon icon="flowbite:angle-right-outline" class="text-gray-500" width="25" />
              </div>
            </div>
            <Divider class="mt-0" />
          </section>
          <!-- PROGRESS MONITOR STARTS -->
          <div v-if="progressMonitor" class="h-[665px] overflow-y-auto hide_scroll pb-14">
            <div class="px-6 py-5 flex flex-col gap-y-8">
              <div v-for="(activity, index) in payment_histories" :key="index" class="flex items-start gap-x-4">
                <div class="flex flex-col items-center">
                  <p :class="activity.status === 'completed'
                    ? 'bg-primary'
                    : 'bg-gray-300'
                    " class="h-6 w-[5px] rounded-full"></p>
                  <span v-if="index + 1 !== payment_histories.length" v-for="(item, count) in 4" :key="count"
                    :class="activity.status === 'completed'
                      ? 'text-primary'
                      : 'text-gray-300'
                      " class="font-black text-xl -mb-4">•</span>
                </div>
                <div class="-mt-3">
                  <p class="text-gray-700 mb-1">{{ activity.office }}</p>
                  <p class="font-light text-sm text-gray-500">
                    {{
                      activity.date_completed
                        ? activity.date_completed
                        : "Due time not set"
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- COMMENTS -->
        <div v-if=" sidemenu">
          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">
            <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

            <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
              <textarea name="" id="" cols="30" placeholder="Enter Message" v-model="message" rows="5"
                class="bg-slate-50 outline-none resize-none"></textarea>
              <div class="w-full flex items-center justify-end">
                <button @click="sendComment(payment.assigned_by.assigned_by, '' , payment.assigned_by.id)" class="bg-white px-3 py-1.5 border border-gray-300 rounded-md">
                  Send
                </button>
              </div>
            </div>
            <div class="mt-5 px-5 overflow-y-scroll max-h-[500px]">
              <div v-for="(item, index) in payment.assigned_by.comments" :key="index">
                <Divider class="mt-0" />
                <div class="flex items-start w-full">
                  <div class="w-[15%]">
                    <div class="w-14 h-14 rounded-full overflow-hidden">
                      <img v-if="item.sender && item.sender.profile_image" :src="base+'/'+item.sender?.profile_image" alt="" class="w-full h-full" />
                      <img v-else :src="user_img" alt="" class="w-full h-full" />
                    </div>
                  </div>
                  <div class="w-[80%]">
                    <div class="flex items-center gap-2">
                      <h1 class="text-gray-800 text-xl">{{user.id == item.sender?.id? "You": item.sender?.last_name+" "+item.sender?.first_name }}</h1>
                      <small class="text-gray-500">• {{ formatRelativeTime(item.created_at) }}</small>
                    </div>
                    <p class="text-base text-gray-500 my-4">
                      {{ item.comment }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-for="(pay, index) in payment.assigned_to" :key="index">
          <div v-if="customSideMenu[index]">
            <div class="p-8">
              <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
                @click="toggleSidemenu('back')">
                <Icon icon="flowbite:angle-left-outline" />
                <p>Back</p>
              </div>
            </div>
            <Divider class="mt-0" />
            <div class="">
              <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

              <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
                <textarea name="" id="" cols="30" placeholder="Enter Message" v-model="message" rows="5"
                  class="bg-slate-50 outline-none resize-none"></textarea>
                <div class="w-full flex items-center justify-end">
                  <button @click="sendComment(pay?.recipient_id, '', pay?.id)" class="bg-white px-3 py-1.5 border border-gray-300 rounded-md">
                    Send
                  </button>
                </div>
              </div>
              <div class="mt-5 px-5 overflow-y-scroll max-h-[500px]">
                <div v-for="(item, index) in pay.comments" :key="index">
                  <Divider class="mt-0" />
                  <div class="flex items-start w-full">
                    <div class="w-[15%]">
                      <div class="w-14 h-14 rounded-full overflow-hidden">
                        <img v-if="item.sender && item.sender.profile_image" :src="base+'/'+item.sender?.profile_image" alt="" class="w-full h-full" />
                        <img v-else :src="user_img" alt="" class="w-full h-full" />
                      </div>
                    </div>
                    <div class="w-[80%]">
                      <div class="flex items-center gap-2">
                        <h1 class="text-gray-800 text-xl">{{user.id == item.sender?.id? "You": item.sender?.last_name+" "+item.sender?.first_name }}</h1>
                        <small class="text-gray-500">• {{ formatRelativeTime(item.created_at) }}</small>
                      </div>
                      <p class="text-base text-gray-500 my-4">
                        {{ item.comment }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <AcceptPayment v-if="acceptPaymentModal" :assignTask="assignTask" :payment="payment" :paymentId="payment.id" @cancelled="closeModal('acceptPayment')" />
  <RejectPayment v-if="rejectPaymentModal" :assigned_by="payment?.assigned_by?.assigned_by" :recipient_id="payment?.assigned_by?.recipient_id" :paymentId="payment.id" @cancelled="closeModal('rejectPayment')" />
  <FinalisePayment v-if="finalisePaymentModal" :paymentId="payment.id" @cancelled="closeModal('finalisePayment')" />
  <DigitalStampPad 
    :show="digitalStampModal" 
    :paymentId="payment.id" 
    :user="user" 
    :roles="roles" 
    @close="digitalStampModal = false" 
    @stamped="() => { digitalStampModal = false; router.reload(); }" 
  />
  <ViewLedgerEntry :paymentId="payment.id" @editLedgerEntry="editLedgerEntry" v-if="viewLedgerEntryModal" @cancelled="closeModal('viewLedger')" />
  <CreateVoucher v-if="createVoucherModal" :payment="payment" @refreshPayment="refreshPayment" :paymentId="payment.id" @cancelled="closeModal('createVoucher')" />
  <ChargeAccount v-if="chargeAccountModal" @refreshPayment="refreshPayment" :payment="payment" @cancelled="closeModal('chargeAccount')" />
  <PaymentReceipt v-if="paymentReceiptModal" :payment="payment" @confirmed="confirmPaymentReceipt" @cancelled="closeModal('paymentReceipt')" />
  <NewLedgerEntry v-if="isCreateNewLedgerEntry" :ledger="ledger" @confirmed="confirmNewLedgerEntry" @cancelled="closeModal()"/>

</template>

<script setup>
import { computed, ref, watch, onMounted, inject } from "vue";
import { useToast } from "primevue/usetoast";
import { Link, usePage, router } from "@inertiajs/vue3";
import {useStore} from "vuex";

import AcceptPayment from "../../components/Modals/Payment/AcceptPayment.vue";
import RejectPayment from "../../components/Modals/Payment/RejectPayment.vue";
import FinalisePayment from "../../components/Modals/Payment/FinalisePayment.vue";
import ViewLedgerEntry from "../../components/Modals/Ledger/ViewLedgerEntry.vue";
import CreateVoucher from "../../components/Modals/Voucher/CreateVoucher.vue";
import EbeweActionPanel from "../../components/EbeweActionPanel.vue";
import DigitalStampPad from "../../components/DigitalStampPad.vue";
import ChargeAccount from "../../components/Modals/Payment/ChargeAccount.vue";
import PaymentReceipt from "../../components/Modals/VoteBook/PaymentReceipt.vue";
import VoteBookImg from "../../assets/images/votebook.svg";
import jenna from "../../assets/images/jenna.svg";
import user_img from "../../assets/images/user.svg";
import Voucher from "../../components/Voucher.vue";
import NewLedgerEntry from "../../components/Modals/Ledger/NewLedgerEntry.vue";
import PaymentMilestoneTracker from "../../components/Payment/PaymentMilestoneTracker.vue";
import ChiefAccountantActionDesk from "../../components/Payment/ChiefAccountantActionDesk.vue";
import UniversalDirectorateActionDesk from "../../components/UniversalDirectorateActionDesk.vue";
import PDFEditor from "../../components/PDFViewer/PDFEditor.vue";
import axios from "axios";

const page = usePage();
const roles = computed(() => page.props.roles);
const permissions = computed(() => page.props.permissions || []);
const base = computed(()=> page.props.base)
const user = computed(()=> page.props.user)
const sidemenu = ref(false);

const pdfTimestamp = ref(Date.now());
const isSavingPdf = ref(false);

const getFullUrl = (rawPath, isStorage = false) => {
  if (!rawPath || typeof rawPath !== 'string') return '';
  if (rawPath.startsWith('http://') || rawPath.startsWith('https://') || rawPath.startsWith('blob:') || rawPath.startsWith('data:')) {
    return rawPath;
  }
  const cleanBase = (base.value || '').replace(/\/+$/, '');
  let cleanPath = rawPath.replace(/^\/+/, '');
  
  if (isStorage) {
    if (!cleanPath.startsWith('storage/')) {
      cleanPath = 'storage/' + cleanPath;
    }
  }
  
  return cleanBase ? `${cleanBase}/${cleanPath}` : `/${cleanPath}`;
};

const activePreviewPdfUrl = computed(() => {
  if (previewVoucher.value && selectedVoucher.value?.path) {
    const raw = selectedVoucher.value.path;
    const url = getFullUrl(raw, false);
    return url ? `${url}?t=${pdfTimestamp.value}` : null;
  }
  
  if (!previewVoucher.value && currentDocument.value) {
    let rawPath = '';
    let isStorage = false;
    
    if (currentDocument.value.file_name) {
      if (typeof currentDocument.value.path === 'string') {
        rawPath = currentDocument.value.path;
      } else if (Array.isArray(currentDocument.value.path) && currentDocument.value.path.length > 0) {
        rawPath = currentDocument.value.path[0].path || currentDocument.value.path[0];
      }
      isStorage = false;
    } else if (currentDocument.value.path) {
      if (typeof currentDocument.value.path === 'string') {
        rawPath = currentDocument.value.path;
      } else if (Array.isArray(currentDocument.value.path) && currentDocument.value.path.length > 0) {
        rawPath = currentDocument.value.path[0].path || currentDocument.value.path[0];
      }
      isStorage = true;
    }
    
    if (rawPath && /\.pdf$/i.test(rawPath)) {
      const url = getFullUrl(rawPath, isStorage);
      return url ? `${url}?t=${pdfTimestamp.value}` : null;
    }
  }
  
  return null;
});

const activePreviewImageUrl = computed(() => {
  if (previewVoucher.value) return null;
  if (!currentDocument.value) return null;
  
  let rawPath = '';
  let isStorage = false;
  if (currentDocument.value.file_name) {
    if (typeof currentDocument.value.path === 'string') {
      rawPath = currentDocument.value.path;
    } else if (Array.isArray(currentDocument.value.path) && currentDocument.value.path.length > 0) {
      rawPath = currentDocument.value.path[0].path || currentDocument.value.path[0];
    }
    isStorage = false;
  } else if (currentDocument.value.path) {
    if (typeof currentDocument.value.path === 'string') {
      rawPath = currentDocument.value.path;
    } else if (Array.isArray(currentDocument.value.path) && currentDocument.value.path.length > 0) {
      rawPath = currentDocument.value.path[0].path || currentDocument.value.path[0];
    }
    isStorage = true;
  }
  
  if (rawPath && /\.(jpg|jpeg|png|gif|bmp|svg|webp)$/i.test(rawPath)) {
    return getFullUrl(rawPath, isStorage);
  }
  return null;
});

const handleSavePdf = async ({ blob }) => {
  if (!blob) return;
  isSavingPdf.value = true;
  try {
    const target = previewVoucher.value 
      ? selectedVoucher.value?.path 
      : (typeof currentDocument.value?.path === 'string' ? currentDocument.value?.path : currentDocument.value?.path?.[0]?.path);
      
    const formData = new FormData();
    formData.append('payment_id', props.payment.id);
    formData.append('target_path', target || '');
    const rawName = target ? target.split('/').pop().split('?')[0] : 'document.pdf';
    formData.append('file', blob, 'modified_' + rawName);

    const res = await axios.post('/finance-and-accounts/payment-save-modified-pdf', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data.status === 'success') {
      toast.add({
        severity: "success",
        summary: "Saved",
        detail: "Document annotations and signatures saved successfully.",
        life: 3000
      });
      pdfTimestamp.value = Date.now();
      reloadPayment();
    } else {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: res.data.message || "Failed to save PDF",
        life: 3000
      });
    }
  } catch (e) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: e.response?.data?.message || "Error saving PDF",
      life: 3000
    });
  } finally {
    isSavingPdf.value = false;
  }
};

const props = defineProps({
  payment: Object,
  ebewe_instance: Object,
  ebewe_stamps: {
    type: Array,
    default: () => []
  }
});
const assignTask = ref(false)
const comment = ref(false);
const progressMonitor = ref(false);
const acceptPaymentModal = ref(false);
const rejectPaymentModal = ref(false);
const finalisePaymentModal = ref(false);
const viewLedgerEntryModal = ref(false);
const createVoucherModal = ref(false);
const chargeAccountModal = ref(false);
const paymentReceiptModal = ref(false);
const digitalStampModal = ref(false);
const isCreateNewLedgerEntry = ref(false);
const toast = useToast();
const currentIndex = ref(0);
const currentDocument = ref(props.payment.files && props.payment.files.length > 0 ? props.payment.files[0] : null);
const currentRole = ref("Advance section");
const voucher_name = ref('')
const voucher_size = ref('')
const previewVoucher = ref(false)
const ledger = ref(null);
const vouchers = ref(null)
const file_sizes = ref([])
const selectedVoucher = ref(null)
const message = ref('')
const payment_assignments = ref([])
const payment_histories = ref([])
const comment1 = ref(false)
const sidemenu1 = ref(false)
const comment_recipient = ref(null)
const customComment = ref([])
const customSideMenu = ref([])
const store = useStore()
const auditQueries = ref([]);

const fetchAuditQueries = async () => {
  if (!props.payment?.id) return;
  try {
    const res = await axios.get(`/finance-and-accounts/get-payment-audit-queries/${props.payment.id}`);
    auditQueries.value = res.data;
  } catch (e) {
    console.error('Failed to load audit queries', e);
  }
};

const reloadPayment = () => {
  router.reload({
    preserveScroll: true,
    preserveState: false
  });
  fetchAuditQueries();
};

const handleOpenCreateVoucher = (payload) => {
  if (payload && payload.type) {
    if (!props.payment?.payment_type) {
      props.payment.payment_type = payload.type;
    }
  }
  createVoucherModal.value = true;
};
const new_comment =  computed(()=> store.getters.getNotifications)
// DIFFERENT ROLES
// Finance and Accounting
// Deputy Director
// Assistant Director
// Advance section
// Other charges
// Checkings
// Internal Audit
// CPO
// CPO Cashbook
// Final Account

const user_roles = {
  //"director": "Director of Finance",
  "deputy director": "Deputy Director Payment",
  "assistant director": "Assistant Director Payment",
  "advance section(HOS)": "Advance section",
  "other charges(HOS)": "Other charges",
  "checking(HOS)": "Checkings",
  "internal audit(HOS)": "Internal Audit",
  "CPO(HOS)": "CPO",
  "final account": "Final Account"};

const editLedgerEntry = (val) => {
  console.log("Edit Ledger Entry", val);
  // Function to handle editing of ledger entry
  if(val){
    ledger.value = val;
    isCreateNewLedgerEntry.value = true;
    viewLedgerEntryModal.value = false;
  }
//viewLedgerEntryModal.value = false;
};

const confirmPaymentReceipt = ()=>{
  router.reload({only: ['payment']})
}

const editVoucher = (voucher)=>{
  createVoucherModal.value = true
}

function formatRelativeTime(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString.replace(" ", "T"));
  const now = new Date();
  const diffMs = now - date;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);

  if (diffSec < 60) return "Just now";
  if (diffMin < 60) return diffMin === 1 ? "A minute ago" : `${diffMin} mins ago`;
  if (diffHour < 24) return diffHour === 1 ? "An hour ago" : `${diffHour} hours ago`;
  if (diffDay < 7) return diffDay === 1 ? "Yesterday" : `${diffDay} days ago`;
  return date.toLocaleDateString();
}

const removeReceipt = async(receipt)=>{
  
  axios.get('/finance-and-accounts/remove-payment-receipt/', {params: {payment_id: props.payment.id, id: receipt.id}}).then((response)=>{
    toast.add({severity:'success', summary: 'Success', detail: 'Receipt removed successfully', life: 3000});
    router.reload({only: ['payment']})
  }).catch((error)=>{
    toast.add({severity:'error', summary: 'Error', detail: 'Failed to remove receipt', life: 3000});
  })
}


const getCommentRecipient = ()=>{
  /*if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0 && props.payment.assigned_to[0].id !== user.value.id){
      return props.payment.assigned_to[0].id
  }
  return props.payment.assigned_by.id
  */
  // if(props.payment && props.assigned_by && props.assigned_by.recipient_id == user.value.id){
  //   return props.payment.assigned_by.recipient_id;
  // }else{
  //   return props.payment.assigned_by.assigned_by;
  // }
  if(props.payment && props.payment.assigned_by && props.payment.assigned_by.recipient_id == user.value.id){
    return props.payment.assigned_by.assigned_by;
  }else{
    return props.payment.assigned_by.recipient_id;
  }
}

const sendComment = (id, unique_id, payment_assignment_id)=>{
  if(message.value && message.value.length > 0){
    router.post('/finance-and-accounts/make-comment', 
    {payment_id: props.payment.id, comment: message.value, recipient: id, unique_id: unique_id, payment_assignment_id: payment_assignment_id}, {
      onSuccess: (response)=>{
        toast.add({severity:'success', summary: 'Success', detail: 'Comment added successfully', life: 3000});
        message.value = ''
        //router.reload({only:['payment']})
      },
      onError: (error)=>{
        toast.add({severity:'error', summary: 'Error', detail: 'Failed to add comment', life: 3000});
      }
    })
  }else{
    toast.add({severity:'warn', summary: 'Warning', detail: 'Comment cannot be empty', life: 3000});
  }
}

const setAssignTaskModal = ()=>{
  acceptPaymentModal.value = true;
  assignTask.value = true;
}

const acceptTask = ()=>{
  acceptPaymentModal.value = true
  assignTask.value = false
}

const isAssignedToOtherCharges = computed(()=>{
  let filtered = props.payment.assigned_to.filter((item)=>item.role =='other charges(HOS)');
  if(filtered && filtered.length > 0){
    return true
  }else{
    return false
  }
})

const isAssignedToCheckings = computed(()=>{
  let filtered = props.payment.assigned_to.filter((item)=>item.role =='other charges(HOS)');
  if(filtered && filtered.length > 0){
    return true
  }else{
    return false
  }
})

const getReSubmitUser = ()=>{
  let found = []
  if( props.payment.assigned_to && props.payment.assigned_to.length == 1){
    found  = props.payment.assigned_to.find((item)=>item.assigned_by == user.value.id);
  }else if(props.payment.assigned_to && props.payment.assigned_to.length > 1){
    found  = props.payment.assigned_to.find((item)=>{
      return item.assigned_by == user.value.id && item.role.includes('(HOS)')
    });
  }

  if(found){
    return found
  }else{
    return found
  }
}

const ReSubmitRejectedPayment = ()=>{
  const user = getReSubmitUser()
  if(user){
    router.post('/finance-and-accounts/re-submit-payment',
    {recipient: user.recipient_id, assigned_by: user.assigned_by, parent_assigned_by: props.payment.assigned_by.assigned_by}, {onSuccess:()=>{
      toast.add({severity:'success', summary: 'Success', detail: 'Payment resubmitted successfully', life: 3000});
    }})
  }
}

async function getPdfSize(val) {
   if(val){
    let url = base.value+'/'+val
    console.log('this is base', url)
     let response = await fetch(url, { method: 'HEAD' });
      let size = response.headers.get('content-length');
      if (size) {
          let sizeInMB = (size / (1024)).toFixed(2);
          voucher_size.value = sizeInMB+" KB"
          voucher_name.value = props.payment.voucher.path
        return sizeInMB+" KB"
      } else {
          return ""
      }
   }
}

async function loadVoucherSizes() {
  if(props.payment && vouchers.value){
    for (let v of props.payment.voucher) {
      v.size = await getPdfSize(v.path) // 👈 attach size directly to object
    }
  }
}

async function getVatPdfSize(val) {
   if(val){
    if(props.payment.vat_voucher.length > 0){
      //iterate over the vat_voucher array to get the size of each pdf
      for(let i = 0; i < props.payment.vat_voucher.length; i++){
        const response = await fetch(base.value + '/' + props.payment.vat_voucher[i].path);
        const blob = await response.blob();
        const sizeInKB = (blob.size / 1024).toFixed(2) + ' KB';
        file_sizes.value.push(sizeInKB)
    }
   }
}
}

const refreshPayment = ()=>{
  router.reload({only:['payment']})
}
// Appending query to the route
const routeEditedQuery = computed(() => route.query.isFileEdited);

const CreateNewLedgerEntry = () => {
  // Function to create a new ledger entry
  isCreateNewLedgerEntry.value = true;
};

const getAssignedTo = () => {
   if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0) {
      return props.payment.assigned_to.map(assignment => assignment.user.first_name + " " + assignment.user.last_name).join(", ");
    }
}
const data = ref({
  id: "lag-49430",
  title: "Lagos Rail Expansion Payment",
  date: "2023-05-15",
  tags: [
    {
      img: jenna,
      title: "Deputy Director of payment",
    },
  ],
  ledgerCreated: true,
  voucherAppended: false,
  status: "Due",

  desc: "Review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archive.",
  documents: [
    {
      name: "Vendor Contracts",
      size: "200 KB ",
      icon: "vscode-icons:file-type-word",
    },
    {
      name: "Purchase Orders",
      size: "700 KB ",
      icon: "vscode-icons:file-type-pdf2",
    },
    {
      name: "Expense Reports",
      size: "200 KB ",
      icon: "vscode-icons:file-type-excel2",
    },
  ],
  appendedVouchers: [
    {
      name: "Main Payment Voucher",
      size: "700 KB ",
      icon: "vscode-icons:file-type-pdf2",
    },
    {
      name: "Contigency Voucher",
      size: "100 KB ",
      icon: "vscode-icons:file-type-pdf2",
    },
  ],
  appendedPaymentReceipt: [
    {
      name: "Receipt",
      size: "7.2 MB ",
      icon: "vscode-icons:file-type-pdf2",
    },
  ],

  // payment_timeline: [
  //   {
  //     title: "Payment Timeline",
  //     status: "completed",
  //     office: "Director of Finance",
  //     date_completed: "6 Jun 2025 9:24 PM",
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "completed",
  //     office: "Deputy Director Payment",
  //     date_completed: "7 Jun 2025 9:24 PM",
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "completed",
  //     office: "Assistant Director Payment",
  //     date_completed: "8 Jun 2025 9:24 PM",
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "Advance Section",
  //     date_completed: null,
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "Other Charges",
  //     date_completed: null,
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "Checkings",
  //     date_completed: null,
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "Internal Audit",
  //     date_completed: null,
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "CPO",
  //     date_completed: null,
  //   },
  //   {
  //     title: "Payment Timeline",
  //     status: "pending",
  //     office: "Final Account",
  //     date_completed: null,
  //   },
  // ],
});


const setCurrentFile = (document, index) => {
  // Function to set the current index of the file being viewed
  currentIndex.value = index;
  currentDocument.value = document;
  selectedVoucher.value = null;
  previewVoucher.value = false;
  pdfTimestamp.value = Date.now();
};

const getFileName = (document) => {
  console.log('document', document)
  // Function to extract file name from the file path
  if (document.file_name) {
    const parts = document.file_name.split('/');
    const lastPart = parts[parts.length - 1];
    const nameWithoutExt = lastPart.substring(0, lastPart.lastIndexOf('.')) || lastPart;

    return nameWithoutExt;

  }
};

//view voucher
const viewVoucher = (voucher)=>{
  selectedVoucher.value = voucher;
  currentDocument.value = null;
  previewVoucher.value = true;
  pdfTimestamp.value = Date.now();
}

// Comments
const comments = ref([
  {
    img: jenna,
    name: "Jenna Ryan obadimu",
    time: "6m ago",
    comment:
      "Task Received i would begin work immediately & review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. ",
  },
  {
    img: user,
    name: "You",
    time: "6m ago",
    comment: "Okay understood, i would work on the changes in due time boss ",
  },
]);

const toggleCommentSidemenu = (recipient) => {
  comment_recipient.value = recipient
  comment.value = !comment.value;
  sidemenu.value = !sidemenu.value;
}

const toggleSidemenu = (action) => {
  // if (action === "comment") {
  //   comment.value = !comment.value;
  //   sidemenu.value = !sidemenu.value;
  //   comment_recipient.value = props.payment?.assigned_by?.recipient_id
  // }

  // if (action === "comment1") {
  //   comment1.value = !comment1.value;
  //   sidemenu1.value = !sidemenu1.value;
  //   comment_recipient.value = props.payment.assigned_by.assigned_by
  // }

  if (action === "progressMonitor") {
    progressMonitor.value = !progressMonitor.value;
  }

  if (action === "back") {
    comment.value = false;
    sidemenu.value = false;
    progressMonitor.value = false;
     let total = props.payment.assigned_to.length;
    if(total > 0){
      customComment.value = Array.from({length: total},()=>false)
      customSideMenu.value = Array.from({length: total}, ()=>false)
    }
  }

  // if (action === "back1") {
  //   comment1.value = false;
  //   sidemenu1.value = false;
  //   progressMonitor.value = false;
  // }
};

const getActiveCustomSideMenu = computed(()=>{
  let filtered = customSideMenu.value.filter((item)=>item == true);
  if(filtered && filtered.length > 0){
    return true;
  }else{
    return false
  }
})

const toggleCustomSidemenu = (index, recipient)=>{
    customComment.value[index] = true;
    customSideMenu.value[index] = true;
    comment_recipient.value = recipient
}

const isAcceptActive = ()=>{
  if(roles.value.includes('deputy director')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'assistant director')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else if(roles.value.includes('assistant director')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'advance section(HOS)' || item.role == 'other charges(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else if(roles.value.includes('advance section(HOS)')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'other charges(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else  if(roles.value.includes('other charges(HOS)')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'checking(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else if(roles.value.includes('checking(HOS)')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'internal audit(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else if(roles.value.includes('internal audit(HOS)')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'CPO(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }else if(roles.value.includes('CPO(HOS)')){
    if(props.payment && props.payment.assigned_to && props.payment.assigned_to.length > 0){
      let found = props.payment.assigned_to.find((item)=>item.role == 'final account(HOS)')
      if(found){
        return true
      }else{
        return false
      }
    }
  }
}

const resubmitTask = () => {
  // Task resubmission logics here
  // Resetting route query
  router.push({
    name: "Payment Details",
    params: { paymentId: route.params.paymentId },
    query: { isFileEdited: false },
  });
  // Toast for file edit selection
  toast.add({
    severity: "success",
    summary: "Task Resubmitted",
    detail: "This task has been assigned to Deputy Director (Payment)",
    life: 3000,
  });
};

const confirmNewLedgerEntry = () => {
  // Logic to confirm new ledger entry creation
  isCreateNewLedgerEntry.value = false;
};

const closeModal = (modal) => {
  isCreateNewLedgerEntry.value = false;
  if (modal == "acceptPayment") {
    acceptPaymentModal.value = !acceptPaymentModal.value;
  } else if (modal == "rejectPayment") {
    rejectPaymentModal.value = !rejectPaymentModal.value;
  } else if (modal == "finalisePayment") {
    finalisePaymentModal.value = !finalisePaymentModal.value;
  } else if (modal == "viewLedger") {
    viewLedgerEntryModal.value = !viewLedgerEntryModal.value;
  } else if (modal == "createVoucher") {
    createVoucherModal.value = !createVoucherModal.value;
  } else if (modal == "chargeAccount") {
    chargeAccountModal.value = !chargeAccountModal.value;
  } else if (modal === "paymentReceipt") {
    paymentReceiptModal.value = !paymentReceiptModal.value;
  } else {
    return;
  }
};

const payment_history = ()=>{
    const history = Object.keys(user_roles).map(role => {
    const found = payment_assignments.value.find(a => a.role.toLowerCase() === role.toLocaleLowerCase());
    return {
      title: "Payment Timeline",
      status: found? "completed": "pending",
      office: user_roles[role],
      date_completed: found ? found.due_date || null : null,
    };
  })
  payment_histories.value = history;
}

const getPaymentAssignments = async ()=>{
  await axios.get('/finance-and-accounts/get-payment-assignments?payment_id='+props.payment.id).then((response)=>{
    if(response && response.data){
      payment_assignments.value = response.data
      payment_history()
    }
  }).catch((error)=>{
    console.log('Error fetching payment assignments', error)
  })
}

const downloadCurrentFile = () => {
  if (!currentDocument.value || !currentDocument.value.file_name) return;
  if (Array.isArray(currentDocument.value.path)) {
    // Download all files in the array
    currentDocument.value.path.forEach(doc => {
      const url = doc.path.startsWith('http') ? doc.path : base.value + doc.path;
      const link = document.createElement('a');
      link.href = url;
      link.download = doc.path.split('/').pop();
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
    return;
  } else if (currentDocument.value.path) {
    // Download single file (when path is a string)
    const url = currentDocument.value.path.startsWith('http') ? currentDocument.value.path : base.value + '/storage/' + currentDocument.value.path;
    const link = document.createElement('a');
    link.href = url;
    link.download = currentDocument.value.path.split('/').pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    return;
  }
  // const link = document.createElement('a');
  // link.href = base.value+"/"+currentDocument.value.file_name;
  // link.download = getFileName(currentDocument.value);
  // document.body.appendChild(link);
  // link.click();
  // document.body.removeChild(link);
};

//format date to 2023-05-15
const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  if(isNaN(year) || isNaN(month) || isNaN(day)) {
    return "No due date assigned";
  }
  return `${year}-${month}-${day}`;
};

const getExtension = (file) => {
  console.log('this is the file', file)
  if (!file) return "";
  if(file && file.length > 0){
    let file_name = file[0]
    let file_arr = file_name.split(".");
    return file_arr.pop();
  }
}

const getFileIcon = (file) => {
  if(file && file.length > 0){
    let fileName = file[0]
      const extension = getExtension(fileName)?.toLowerCase();

    switch (extension) {
      case "pdf":
        return "vscode-icons:file-type-pdf2";
      case "docx":
      case "doc":
        return "vscode-icons:file-type-word";
      case "xlsx":
      case "xls":
        return "vscode-icons:file-type-excel";
      case "ppt":
      case "pptx":
        return "vscode-icons:file-type-powerpoint";
      case "jpg":
      case "jpeg":
        return "vscode-icons:file-type-image";
      case "png":
        return "vscode-icons:file-type-image";
      default:
        return "material-icon-theme:pdf";
    }
  }
};

const getIcon = (file) => {
  if(file && file.path){
    let fileName = file.path
      const extension = fileName.split('.').pop().toLowerCase();

    switch (extension) {
      case "pdf":
        return "vscode-icons:file-type-pdf2";
      case "docx":
      case "doc":
        return "vscode-icons:file-type-word";
      case "xlsx":
      case "xls":
        return "vscode-icons:file-type-excel";
      case "ppt":
      case "pptx":
        return "vscode-icons:file-type-powerpoint";
      case "jpg":
      case "jpeg":
        return "vscode-icons:file-type-image";
      case "png":
        return "vscode-icons:file-type-image";
      default:
        return "material-icon-theme:pdf";
    }
  }
};

const downloadVoucherPdf = (voucher) => {
  selectedVoucher.value = voucher
  if (!selectedVoucher.value.path) return;
  const url = base.value + '/' + selectedVoucher.value.path;
  console.log('url',url)
  const link = document.createElement('a');
  link.href = url;
  link.download = voucher_name.value || 'voucher.pdf';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

watch(()=>props.payment, (newVal)=>{
  if(newVal && newVal.voucher){
    vouchers.value = newVal.voucher
    loadVoucherSizes()
    //getPdfSize(newVal.voucher.path)
  }
  if(newVal.vat_voucher.length > 0){
    getVatPdfSize(newVal.vat_voucher)
  }

    let total = newVal.assigned_to.length;
    if(total > 0){
      customComment.value = Array.from({length: total},()=>false)
      //customSideMenu.value = Array.from({length: total}, ()=>false)
    }
}, {immediate: true})

let isFirstRun = true;

watch(new_comment, ()=>{
 if(!isFirstRun){
   router.reload({
    only: ['payment'],
    preserveScroll: true,     // 🚫 prevent scrolling to top
    preserveState: true, 
  })
 }else{
  isFirstRun = false;
 }
}, {immediate: false})

onMounted(()=>{
  fetchAuditQueries();
  if(roles.value.includes('director')){
    getPaymentAssignments()
  }
})
</script>

<style></style>
