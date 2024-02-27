<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="lucide:lock" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Security & Recovery</h1>
          <p class=" text-gray-500">Edit notification settings for users.</p>
        </div>
      </div> 
    </div>
    <Divider/>
    <div class="lg:pl-7 ">
       <div class=" pr-7">
       <h1 class=" text-[#3575DD] text-base">Organisation</h1>
        <div class=" flex items-center justify-between mt-5">
          <div >
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Set-up Password Policies</h1>
          <p class=" text-gray-500">Enhance your organization's security with custom password policies</p>
        </div>
        <div>
          <router-link to="/settings/passwordpolicies">
          <button class=" p-2 rounded-md border border-gray-300 text-base">
            Set Up Security Policies
          </button>
        </router-link>
        </div>
        </div>
       </div>
       <Divider/>


       <div class=" pr-7">
        <div class=" flex items-center justify-between ">
          <div >
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
       <Divider/>

       <div class=" pr-7">
       <h1 class=" text-[#3575DD] text-base">Personal</h1>
        <div class=" flex items-center justify-between mt-5">
          <div >
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Change Password</h1>
          <p class=" text-gray-500">Update password for enhanced security.</p>
        </div>
        <div>
          <button class=" p-2 rounded-md border border-gray-300 text-base" @click="password=true">
            Change password
          </button>
        </div>
        </div>
       </div>
       <Divider/>

       <div class=" pr-7">
       
        <div class=" flex items-center justify-between ">
          <div >
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Two-Factor Authentication</h1>
          <p class=" text-gray-500">Add an extra layer of security to your account.</p>
        </div>
        <div>
          <button class=" p-2 rounded-md border border-gray-300 text-base" @click="twofactors= true">
            Enable 2FA
          </button>
        </div>
        </div>
       </div>
       <Divider/>

       <div class=" pr-7">
        <div class=" flex items-center justify-between ">
          <div >
          <h1 class="text-2xl text-[#1D2939] font-semibold ">Active Sessions</h1>
          <p class=" text-gray-500">Monitor and manage all your active sessions.</p>
        </div>
        <div>
          <button @click="deleteSession=true" class=" p-2 rounded-md border border-gray-300 text-[#D92D20] text-base">
            Log Out All Sessions
          </button>
        </div>
        </div>
       </div>
       <Divider/>

       <div class=" pr-7">
        <DataTable
          paginator
          :rows="10"
          :value="sessions"
          dataKey="id"
          :pt="{
            table: {
              class: 'w-full border border-border_primary border-solid mb-6',
            },
          }"
          tableStyle="min-width: 60rem; border-radius: 30px;"
        >
        
          <Column
            field="browser"
            header="Browser"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon icon="logos:chrome" />
                </div>
                {{ slotProps.data.browser }}
              </div>
            </template>
          </Column>
          <Column
            field="location"
            header="Location"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="session"
            header="Session"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="ip"
            header="Ip"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>

          <Column
            field="action"
            header="Action"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          <template #body={}>
            <i class="pi pi-times"></i>
          </template>
          </Column>
        </DataTable>
       </div>
      </div>
      <TwoFactorsModal v-model="twofactors"/>
      <PasswordModal v-model="password"/>
      <DeleteSessionModal v-model="deleteSession" />
</template>

<script setup>
  import {ref} from 'vue'
  import TwoFactorsModal from '@/components/Modals/TwoFactorsModal.vue'
  import PasswordModal from '@/components/Modals/PasswordModal.vue'
  import DeleteSessionModal from '@/components/Modals/DeleteSession.vue'
  const password = ref(false)
  const twofactors = ref(false)
  const deleteSession = ref(false)
  const sessions = ref([
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      
    }
  ])
</script>

<style>

</style>