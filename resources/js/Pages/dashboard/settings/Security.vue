<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
    <div class="flex items-center gap-5">
      <div class=" border rounded-full p-3">
        <Icon icon="lucide:lock" width="30" />
      </div>
      <div>
        <h1 class="text-3xl text-[#1D2939] ">Security & Recovery</h1>
        <p class=" text-gray-500">Edit notification settings for users.</p>
      </div>
    </div>
  </div>
  <Divider />
  <div class="lg:pl-7 ">
    <div class=" pr-7">
      <h1 class=" text-primary text-base">Organisation</h1>
      <div class=" flex items-center justify-between mt-5">
        <div>
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Set-up Password Policies</h1>
          <p class=" text-gray-500">Enhance your organization's security with custom password policies</p>
        </div>
        <div>
          <a href="#" @click="passwordPoliciesBtn">
            <button class=" p-2 rounded-md border border-gray-300 text-base">
              Set Up Security Policies
            </button>
          </a>
        </div>
      </div>
    </div>
    <Divider />


    <div class=" pr-7">
      <div class=" flex items-center justify-between ">
        <div>
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Back-Up Data</h1>
          <p class=" text-gray-500">Add an extra layer of security to your account.</p>
        </div>
        <div>
          <button class=" p-2 rounded-md border border-gray-300 text-base">
            Set-Up Recovery
          </button>
        </div>
      </div>
    </div>
    <Divider />

    <div class=" pr-7">
      <h1 class=" text-primary text-base">Personal</h1>
      <div class=" flex items-center justify-between mt-5">
        <div>
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Change Password</h1>
          <p class=" text-gray-500">Update password for enhanced security.</p>
        </div>
        <div>
          <button class=" p-2 rounded-md border border-gray-300 text-base" @click="password = true">
            Change password
          </button>
        </div>
      </div>
    </div>
    <Divider />

    <div class=" pr-7">

      <div class=" flex items-center justify-between ">
        <div>
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Two-Factor Authentication</h1>
          <p class=" text-gray-500">Add an extra layer of security to your account.</p>
        </div>
        <div>
          <button v-if="!props.user.two_factor_secret && !props.user.two_factor_confirmed_at" class=" p-2 rounded-md border border-gray-300 text-base" @click="twofactors = true">
            Enable 2FA
          </button>
          <button v-if="props.user.two_factor_secret && !props.user.two_factor_confirmed_at" class=" p-2 rounded-md border border-gray-300 text-base" @click="verify2fa_auth = true">
            Verify 2FA
          </button>
          <button v-if="props.user.two_factor_secret && props.user.two_factor_confirmed_at" class=" p-2 rounded-md border border-gray-300 text-red-500 text-base" @click="disableTwofactors = true">
            Disable 2FA
          </button>
          <button v-if="props.user.two_factor_secret && props.user.two_factor_confirmed_at" class=" p-2 rounded-md border border-gray-300 text-black ml-2 text-base" @click="GenerateNewRecoveryCode">
            Generate new Recovery code
          </button>
          <!-- <span v-html="twoFactor?.svg"></span> -->
        </div>
      </div>
      <div class="mt-4" v-if="recoveryCodes && recoveryCodes.length > 0">
          <span class="my-2 w-1/2 block text-gray-600">Please copy the recovery codes shown below and store safely, in the event where you are unable to login with authenticator code, you can login with your recovery codes. Please note that each time you use one of the recovery codes, you will need to generate another recovery codes.</span>
           <ul class="cursor-pointer mt-2" id="copy_to_clipboard" @click="copyToClipboard">
              <li v-for="code in recoveryCodes">{{ code }}</li>
           </ul>
        </div>
    </div>
    <Divider />

    <div class=" pr-7">
      <div class=" flex items-center justify-between ">
        <div>
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Active Sessions</h1>
          <p class=" text-gray-500">Monitor and manage all your active sessions.</p>
        </div>
        <div>
          <button @click="deleteSession = true" class=" p-2 rounded-md border border-gray-300 text-[#D92D20] text-base">
            Log Out All Sessions
          </button>
        </div>
      </div>
    </div>
    <Divider />

    <div class=" pr-7">
      <DataTable paginator :rows="10" :value="sessions" dataKey="id" :pt="{
        table: {
          class: 'w-full border border-border_primary border-solid mb-6',
        },
      }" tableStyle="min-width: 60rem; border-radius: 30px;">

        <Column field="browser" header="Browser" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
          <template #body="slotProps">
            <div class="flex items-center font-medium gap-2">
              <div class="p-2 rounded-full bg-gray-200">
                <!-- <Icon icon="logos:firefox" />
                <Icon icon="logos:chrome" />
                <Icon icon="logos:opera" />
                <Icon icon="logos:safari" /> -->
                <img src="../../../assets/chrome.svg" v-if="slotProps.data.browser.toLowerCase() == 'chrome'" alt="">
                <img src="../../../assets/firefox.svg" v-if="slotProps.data.browser.toLowerCase() == 'firefox'" alt="">
                <img src="../../../assets/opera.svg" v-if="slotProps.data.browser.toLowerCase() == 'opera'" alt="">
              </div>
              {{ slotProps.data.browser }}
            </div>
          </template>
        </Column>
        <Column field="location" header="Location" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
        </Column>
        <Column field="last_activity" header="Session" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
        </Column>
        <Column field="ip_address" header="IP" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
        </Column>

        <Column field="action" header="Action" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
          <template #body="slotProps">
            <!-- <i class="pi pi-times"></i> -->
            <svg @click="deleteUserSession(slotProps.data)" class="cursor-pointer" width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M11.6001 1L1.6001 11M1.6001 1L11.6001 11" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

          </template>
        </Column>
      </DataTable>
    </div>
  </div>
  <TwoFactorsModal v-model="twofactors" @verify_2fa="verify_2fa_handler" :user="props.user" @refresh="refreshHandler" />
  <DisableTwoFactors v-model="disableTwofactors" :user="props.user" @refresh2fa="refreshHandler2fa" @refresh="refreshHandler" />
  <PasswordModal v-model="password" />
  <Verify2FA v-model="verify2fa_auth" @refresh2fa="refreshHandler2fa" :twoFactor="twoFactor"/>
  <DeleteSessionModal v-model="deleteSession" @refresh="refreshHandler" />
  <RegenrateRecoveryCode v-model="regenerateCode" @refresh="refreshHandler"/>
  
<!--   
  <span v-html="twoFactor?.svg"></span> -->
</template>

<script setup>
import { ref, defineEmits, onMounted, defineProps } from 'vue'
import {router} from "@inertiajs/vue3"
import TwoFactorsModal from '../../../components/Modals/TwoFactorsModal.vue'
import DisableTwoFactors from '../../../components/Modals/DisableTwoFactorsModal.vue'
import PasswordModal from '../../../components/Modals/PasswordModal.vue'
import DeleteSessionModal from '../../../components/Modals/DeleteSession.vue'
import Verify2FA from '../../../components/Modals/Verify2FA.vue'
import RegenrateRecoveryCode from '../../../components/Modals/RegenrateRecoveryCode.vue'
import { useToast } from "primevue/usetoast";
import axios from 'axios'

const password = ref(false)
const twofactors = ref(false)
const disableTwofactors = ref(false)
const deleteSession = ref(false)
const emit = defineEmits(['password_policies', 'refreshUser'])
const props = defineProps(['user']);
const twoFactor = ref(null)
const verify2fa_auth = ref(false)
const recoveryCodes = ref([])
const toast = useToast();
const regenerateCode = ref(false)

const passwordPoliciesBtn = () => {
  emit('passwordPolicies', false)
}

const refreshHandler2fa = ()=>{
  emit('refreshUser')
  getUserSessions();
}

const verify_2fa_handler = ()=>{
  verify2fa_auth.value = true;
}

const getUserSessions = () => {
  axios.get('/user-sessions').then((response) => {
    sessions.value = response.data;
  })
}

const deleteUserSession = (data)=>{
  axios.get('/delete-user-sessions/'+data.id).then((response)=>{
    getUserSessions();
  })
}
const getTwoFactorCode = ()=>{
  axios.get('/user/two-factor-qr-code').then((response)=>{
    twoFactor.value = response.data
  })
  
}

const GenerateNewRecoveryCode = ()=>{
  regenerateCode.value = true
  // axios.post('/user/two-factor-recovery-codes').then((response)=>{
  //   recoveryCodes.value = response.data
  // })
}

const getUserRecoveryCode = ()=>{
  axios.get('/user/two-factor-recovery-codes').then((response)=>{
    recoveryCodes.value = response.data
  })
}

const refreshHandler = ()=>{
  getUserSessions();
  getTwoFactorCode()
  getUserRecoveryCode()
}

const copyToClipboard = ()=>{
   // Get the text from the input field
   const ulElement = document.getElementById('copy_to_clipboard');
   const text = ulElement.innerText; // Get the text content of the ul

    // Use the Clipboard API to copy text to the clipboard
    navigator.clipboard.writeText(text).then(() => {
      toast.add({
    severity: "success",
    summary: 'Codes copied!',
    detail: "Recovery codes copied to clipboard.",
    life: 3000,
  });
    }).catch(err => {
      toast.add({
      severity: "error",
      summary: 'Failed',
      detail: "Could not copy recovery codes.",
      life: 3000,
    });
    });
}

const sessions = ref([
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // },
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // },
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // },
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // },
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // },
  // {
  //   browser: 'chrome',
  //   location: 'Lagos Nigeria',
  //   session: 'Current session',
  //   ip: '224.0.1.1',

  // }
])

onMounted(() => {
  getUserSessions();
  if(props.user?.two_factor_secret){
    getTwoFactorCode()
    getUserRecoveryCode()
  }
})
</script>

<style></style>