<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: {
      class: [
        'w-[90vw]',
        currentStep === 1
          ? 'md:w-[80vw] lg:w-[55vw]'
          : 'md:w-[60vw] lg:w-[35vw]',
        'transition-all duration-500 ease-in-out',
        'bg-white rounded-xl p-6 shadow-lg',
      ],
    },
    header: { class: 'hidden' },
    content: {
      class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl rounded-b-2xl',
    },
    footer: { class: 'hidden' },
  }" :style="{ padding: '0px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class=".custom-dialog">
    <Transition name="fade-slide" mode="out-in">
      <!-- Step 1: Voucher form -->
      <section v-if="currentStep === 1" key="step1"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center">
        <div class="items-center justify-start flex border-b border-solid border-[#E6E6E6] w-full">
          <div class="px-5 py-4">
            <p class="text-lg font-semibold text-gray-700">
              Voucher Payment Information
            </p>
          </div>
        </div>
        <!-- THE FORM GOES HERE -->
        <section class="flex flex-col md:flex-row items-start gap-5 px-6 w-full">
          <section class="w-full">
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Deptal No</p>
              </div>
              <div>
                <input v-model="form.deptal_no" type="text" placeholder="Enter Deptal No"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Date</p>
              </div>
              <div>
                <input v-model="form.date" type="datetime-local" placeholder="Add Comments"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
            <div class="self-start mt-9 flex justify-between items-center">
              <div v-if="!previewUrl">
                <p class="text-gray-700 mb-1 text-sm">Upload Signature</p>
                <p class="text-gray-500 text-xs">
                  (JPEG/PNG formats supported).
                </p>
              </div>
              <div v-else>
                <img :src="previewUrl" class="h-[80px]" alt="">
              </div>
              <div>
                <!-- Hidden file input -->
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" />
                <button
                  class="text-gray-700 text-xs bg-white rounded-lg py-2 px-3 border-2 border-solid flex items-center gap-2"
                  @click="triggerFileSelect()">
                  <Icon icon="lucide:upload-cloud" width="18" /><span>Upload Image</span>
                </button>
              </div>
            </div>
            <div class="self-start mt-8">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Amount (Naira)</p>
              </div>
              <div>
                <input v-model="form.amount" type="text" placeholder="₦ Enter Amount"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
            <!-- <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Voucher No</p>
              </div>
              <div>
                <input v-model="form.voucher_number" type="text" disabled placeholder="VOU-53628"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div> -->
            <div class="self-start mt-5 w-full">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Data Type</p>
              </div>
              <div class="w-full">
                <DropdownComp v-model="form.data_type" :items="dataTypes" placeholder="Select Data Type" />
              </div>
            </div>
          </section>
          <section class="w-full">
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Source</p>
              </div>
              <div>
                <input v-model="form.source" type="text" placeholder="Enter Source"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Head</p>
              </div>
              <div>
                <DropdownComp v-model="form.head" :items="headList" placeholder="Select Head" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Checked and passed for payment at</p>
              </div>
              <div>
                <input v-model="form.checked_and_passed" type="text" placeholder="Input Text"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Classification Code</p>
              </div>
              <div>
                <DropdownComp v-model="form.classification_code" :items="classificationCodes"
                  placeholder="Select Classification Code" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">S/Head</p>
              </div>
              <div>
                <DropdownComp v-model="form.s_head" :items="subHeadList" placeholder="Select S/Head" />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Address</p>
              </div>
              <div>
                <input v-model="form.address" type="text" placeholder="Enter Address"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
              </div>
            </div>
          </section>
        </section>
        <div
          class="w-full self-end flex items-center justify-end gap-3 mt-5 py-5 pr-7 border-t border-dashed border-[#E6E6E6]">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-6 md:px-10 bg-white" label="Cancel">
            Cancel
          </button>

          <button @click="createNewVoucherEntry"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-6 md:px-10"
            :disabled="!isFormValid" severity="secondary" outlined>
            Generate Voucher
          </button>
        </div>
      </section>
      <!-- Step 2: Generating Voucher -->
      <section v-else-if="currentStep === 2" key="step2"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center">
        <img :src="'../../../assets/images/votebook.svg'" alt="votebook icon" width="170" height="170" class="drop-shadow-xl" />
        <div class="flex flex-col items-center p-8 gap-y-3 -mt-16">
          <Icon icon="line-md:loading-alt-loop" width="44" />
          <p class="text-2xl text-gray-800 font-semibold">
            Voucher Generating...
          </p>
          <p class="text-sm text-gray-500 text-center font-light">
            Please wait while we prepare your payment voucher. This may take a
            moment. Thank you for your patience!
          </p>
        </div>
      </section>
      <!-- Step 3: Voucher Generated -->
      <section v-else-if="currentStep === 3" key="step3"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center">
        <img :src="'../../../assets/images/voucher_generated.svg'" alt="votebook icon" width="160" height="100"
          class="drop-shadow-xl mt-2 -mb-3" />
        <div class="flex flex-col items-center gap-y-3 px-7">
          <p class="text-2xl font-semibold text-gray-800">Voucher Generated</p>
          <p class="text-gray-500 text-center font-light">
            The voucher have been successfully created and are ready to be
            appended to the main documents. Once appended, all records will be
            consolidated for processing.
          </p>
        </div>
        <div class="w-full px-4 mt-10 mb-3">
          <div v-for="(document, index) in voucherDetails.voucherDocument" :key="index"
            class="rounded-xl p-2 flex items-center justify-between mb-3 bg-white w-full">
            <div
              class="flex items-center gap-3 bg-[#F2F4F7] w-full rounded-xl p-3 border-2 border-transparent hover:border-primary">
              <div class="bg-white w-fit p-2 rounded-full">
                <Icon :icon="document.icon" width="25" />
              </div>
              <div>
                <h1 v-if="file_name">{{ file_name }}</h1>
                <p class="text-xs text-gray-600 font-light mt-1" v-if="file_size">
                  {{ file_size }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="w-full self-end flex items-center justify-end gap-3 py-5 pr-5 border-[#E6E6E6]">
          <button @click="currentStep = 1"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-6 md:px-10 bg-white" label="Cancel">
            Edit Voucher
          </button>

          <button @click="appendNewVoucherEntry"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-6 md:px-10"
            severity="secondary" outlined>
            Append Voucher
          </button>
        </div>
      </section>
    </Transition>
   <Voucher v-if="exporting" id="myFile" />
    <!-- <Voucher
    ref="voucherEl"
    aria-hidden="true"
    class="fixed -left-[99999px] top-0 opacity-0 pointer-events-none"
    id="myFile"
  /> -->
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, nextTick, defineProps, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from "@inertiajs/vue3";
import DropdownComp from "../../DropdownComp.vue";
//import html2pdf from "html2pdf.js";
import Voucher from "../../../components/Voucher.vue"

const voucherEl = ref(null)
const page = usePage();
const emit = defineEmits(["confirmed", "cancelled", "refreshPayment"]);
const toast = useToast();
const props = defineProps(['paymentId', 'payment']);
const exporting = ref(false)
const visible = ref(true);
const currentStep = ref(1); // 1 = form, 2 = generating, 3 = success

const fileInput = ref(null);

const classificationCodes = ref([
  {
    title: "select",
    value: "",
  },
  {
    title: "FGN-34983",
    value: "fgn123",
  },
  {
    title: "STATE-78291",
    value: "state456",
  },
  {
    title: "LGA-56742",
    value: "lga789",
  },
  {
    title: "MIN-19835",
    value: "min012",
  },
  {
    title: "DEPT-92674",
    value: "dept345",
  },
  {
    title: "AGY-41567",
    value: "agy678",
  },
]);

const headList = ref([
  {
    title: "Select",
    value: "",
  },
  {
    title: "Adebayo Ogundimu",
    value: "adebayo",
  },
  {
    title: "Chinelo Okwu",
    value: "chinelo",
  },
  {
    title: "Fatima Abdullahi",
    value: "fatima",
  },
  {
    title: "Emeka Nwankwo",
    value: "emeka",
  },
  {
    title: "Aisha Bello",
    value: "aisha",
  },
  {
    title: "Olumide Adeyemi",
    value: "olumide",
  },
]);

const subHeadList = ref([
  {
    title: "Select",
    value: "",
  },
  {
    title: "Kemi Olatunji",
    value: "kemi",
  },
  {
    title: "Chioma Ikechukwu",
    value: "chioma",
  },
  {
    title: "Ibrahim Musa",
    value: "ibrahim",
  },
  {
    title: "Ngozi Anyanwu",
    value: "ngozi",
  },
  {
    title: "Yakubu Garba",
    value: "yakubu",
  },
  {
    title: "Folake Adebisi",
    value: "folake",
  },
]);

const dataTypes = ref([
  {
    title: "Select",
    value: "",
  },
  {
    title: "String",
    value: "string",
  },
  {
    title: "Boolean",
    value: "boolean",
  },
  {
    title: "Number",
    value: "number",
  },
  {
    title: "Array",
    value: "array",
  },
]);
const base = computed(()=> page.props.base)

const form = useForm({
  deptal_no: "",
  source: "",
  date: "",
  head: "",
  signature: null,
  checked_and_passed: "",
  amount: "",
  classification_code: "",
  s_head: "",
  data_type: "",
  address: "",
  payment_id: null,
  id: null
});

const isFormValid = computed(() => {
  //validate all fields are filled
  if (form.id) {
    return (
      form.deptal_no &&
      form.source &&
      form.date &&
      form.head &&
      form.checked_and_passed &&
      form.amount &&
      form.classification_code &&
      form.s_head &&
      form.data_type &&
      form.address
    );
  } else {
    return (
      form.deptal_no &&
      form.source &&
      form.date &&
      form.head &&
      form.signature &&
      form.checked_and_passed &&
      form.amount &&
      form.classification_code &&
      form.s_head &&
      form.data_type &&
      form.address
    );
  }

});
const file_name = ref(null)
const file_size = ref(null)

//preview image selected
const previewUrl = ref(null);

watch(
  () => form.signature,
  (file) => {
    if (file && file.type.startsWith("image/")) {
      previewUrl.value = URL.createObjectURL(file);
    } else {
      previewUrl.value = null;
    }
  }
);

const triggerFileSelect = () => {
  fileInput.value?.click();
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  form.signature = file;
};

async function getPdfSize(val) {
   if(val){
    let url = base.value+'/'+val
    console.log('this is base', url)
     let response = await fetch(url, { method: 'HEAD' });
      let size = response.headers.get('content-length');
      if (size) {
          let sizeInMB = (size / (1024)).toFixed(2);
          file_size.value = sizeInMB+"KB"
          file_name.value = props.payment.voucher[0].path
        return sizeInMB
      } else {
          return ""
      }
   }
}

const createNewVoucherEntry = async () => {
  // if (!isFormValid.value) {
  //   toast.add({
  //     severity: "warn",
  //     summary: "Invalid Form",
  //     detail: "Please fill in all fields",
  //     life: 3000,
  //   });
  //   return;
  // }
currentStep.value = 2;
  //submit form
  form.payment_id = props.paymentId;
   await form.post("/finance-and-accounts/create-voucher", {
    onSuccess: async(page) => {
       currentStep.value = 3;
    //    exporting.value = true;
    // await nextTick(); // wait for render
    //    generatePDF()
    // exporting.value = false;
    emit('refreshPayment')
      toast.add({
        severity: "success",
        summary: "Voucher Created",
        detail: "The voucher has been successfully created.",
        life: 3000,
      });
    },
    onError: () => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: "Failed to create voucher. Please try again.",
        life: 3000,
      });
      currentStep.value = 1;
    },
  });

  

  // Simulate API delay
  //await new Promise((resolve) => setTimeout(resolve, 6000));

 
};

const appendNewVoucherEntry = () => {
  emit("cancelled");
  visible.value = false;
};

const cancelModal = () => {
  emit("cancelled");
  visible.value = false;
};

// This is after voucher has been created
const voucherDetails = ref({
  voucherDocument: [
    {
      name: "Main Payment Voucher",
      size: "200 KB ",
      icon: "vscode-icons:file-type-pdf2",
    },
  ],
});

watch(()=>props.payment, ()=>{
  if(props.payment && props.payment.voucher && props.payment.voucher.length > 0){
    getPdfSize(props.payment.voucher[0].path);
    let voucher = props.payment.voucher[0]
    form.deptal_no = voucher.deptal_no || "";
    form.source = voucher.source || "";
    form.date = voucher.date || "";
    form.head = headList.value.find((item)=>item.title == voucher.head) || "";
    form.checked_and_passed = voucher.checked_and_passed || "";
    form.amount = voucher.amount || "";
    form.classification_code = classificationCodes.value.find((item)=>item.title == voucher.classification_code) || "";
    form.s_head = subHeadList.value.find((item)=>item.title == voucher.s_head) || "";
    form.data_type = dataTypes.value.find((item)=> item.title == voucher.data_type) || "";
    form.address = voucher.address || "";
    form.payment_id = voucher.payment_id || null;
    form.id = voucher.id || null
  }
},{immediate:true})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
