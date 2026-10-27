<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'px-0 pb-0' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }" :style="{ width: '38rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class=".custom-dialog">
    
    <!-- Custom Header -->
    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="p-3 bg-primary/10 text-primary rounded-xl border border-primary/20">
          <Icon icon="mdi:shield-plus-outline" width="28" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Create Access Level</h1>
          <p class="text-sm text-gray-500 mt-1">Define a new role and its basic properties</p>
        </div>
      </div>
      <button @click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
        <Icon icon="mdi:close" width="24" />
      </button>
    </div>

    <!-- Body -->
    <div class="px-8 py-6 bg-white">
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Access Level Name <span class="text-red-500">*</span>
          </label>
          <input type="text" id="level-name" v-model="name" placeholder="e.g. Content Reviewer" 
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all duration-200 outline-none text-gray-800 placeholder-gray-400">
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Access Level Type <span class="text-red-500">*</span>
          </label>
          <select id="role_type" v-model="type" 
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all duration-200 outline-none text-gray-800 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-no-repeat bg-[position:right_1rem_center]">
            <option value="" disabled selected>Select Role Archetype</option>
            <option value="super admin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="director">Director</option>
            <option value="deputy director">Deputy Director</option>
            <option value="assistant director">Assistant Director</option>
            <option value="record manager">Record Manager</option>
            <option value="super user">Super User</option>
            <option value="regular user">Regular User</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Description <span class="text-gray-400 font-normal">(Optional)</span>
          </label>
          <textarea v-model="description" rows="3" placeholder="Briefly describe the responsibilities of this role..."
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all duration-200 outline-none text-gray-800 placeholder-gray-400 resize-none"></textarea>
        </div>
      </div>
    </div>

    <!-- Custom Footer -->
    <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
      <button @click="closeModal" 
              class="px-6 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-gray-200 bg-gray-100 transition-colors duration-200">
        Cancel
      </button>
      <button @click="submit" 
              class="px-8 py-2.5 rounded-xl font-medium text-white bg-primary hover:bg-primary/90 shadow-sm hover:shadow transition-all duration-200 transform hover:-translate-y-0.5">
        Create Level
      </button>
    </div>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const props = defineProps(["modelValue"]);
const toast = useToast()
const emit = defineEmits(["update:modelValue"]);
const name = ref('');
const type = ref('');
const description = ref('')
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);

};

const submit = () => {
  if(name.value ==''){
    document.getElementById('level-name').focus();
    return false;
  }
  if(type.value ==''){
    document.getElementById('role_type').focus();
    return false;
  }
  router.post('/dashboard/create-role', {name: name.value, type: type.value, description: description.value}, {onSuccess: ()=>{
    emit("update:modelValue", false);
    emit("refresh");
    name.value = ""
    description.value = ""
    toast.add({
      severity: "success",
      summary: 'Access Level Created',
      detail: "The access level has been successfully created.",
      life: 3000,
    });
  }})
  
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
