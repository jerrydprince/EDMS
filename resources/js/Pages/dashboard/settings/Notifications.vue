<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="basil:notification-outline" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Notification Configuration</h1>
          <p class=" text-gray-500">Edit notification settings for users.</p>
        </div>
      </div> 
    </div>
    <Divider/>
    <div class="lg:pl-7 ">
    <div class=" w-[40vw]">
        <h1 class=" text-[#344054] font-semibold text-lg">Document Activity Alerts</h1>
        <p class=" text-base text-gray-500 font-light">Configure notifications for document related activities.</p>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">New added</h1>
          <select name="" id="" class=" outline-none border border-gray-300 p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Updated</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Deleted</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">New versions added</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Old versions restored</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>
    </div>

    <Divider/>

    <div class=" w-[40vw] mt-10">
        <h1 class=" text-[#344054] font-semibold text-lg">Workflow Alerts</h1>
        <p class=" text-base text-gray-500 font-light">configure notifications for workflow progress.</p>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">New approval request</h1>
          <select name="" id="" class=" outline-none border border-gray-300 p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Workflow assigning</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Response to requests</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Workflow completed</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

       
    </div>

    <Divider/>


    <div class=" w-[40vw] mt-10">
        <h1 class=" text-[#344054] font-semibold text-lg">Collaboration Alerts</h1>
        <p class=" text-base text-gray-500 font-light">configure notifications for collaborative actions.</p>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Access Granted</h1>
          <select name="" id="" class=" outline-none border border-gray-300 p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Comments assigned</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>

        <div class=" flex items-center text-[#475467] justify-between mt-10">
          <h1 class=" font-semibold">Annotations to files</h1>
          <select name="" id="" class=" outline-none border p-2 w-96 rounded-md">
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
            <option value="Sent grouped">Sent grouped</option>
          </select>
        </div>
      </div>
      <Divider/>
      </div>
</template>

<script>
export default {

}
</script>

<style>

</style>