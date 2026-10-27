<template>
  <div class="form-renderer">
    <div v-for="(field, index) in schema" :key="index" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ field.label }}
        <span v-if="field.required" class="text-red-500">*</span>
      </label>
      
      <!-- Text / Date / Number -->
      <input 
        v-if="['text', 'number', 'date'].includes(field.type)"
        :type="field.type"
        :required="field.required"
        v-model="formData[field.name]"
        class="form-control w-full"
        @input="updateData"
      >
      
      <!-- Textarea -->
      <textarea 
        v-else-if="field.type === 'textarea'"
        :required="field.required"
        v-model="formData[field.name]"
        class="form-control w-full"
        rows="3"
        @input="updateData"
      ></textarea>
      
      <!-- Select -->
      <select 
        v-else-if="field.type === 'select'"
        :required="field.required"
        v-model="formData[field.name]"
        class="form-control w-full"
        @change="updateData"
      >
        <option value="" disabled>Select an option</option>
        <option v-for="opt in field.options || []" :key="opt" :value="opt">{{ opt }}</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormRenderer',
  props: {
    schema: {
      type: Array,
      required: true
    },
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      formData: {}
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (JSON.stringify(newVal) !== JSON.stringify(this.formData)) {
          this.formData = { ...newVal };
        }
      },
      immediate: true,
      deep: true
    },
    schema: {
      handler(newSchema) {
        // Initialize empty keys for new fields
        newSchema.forEach(field => {
          if (this.formData[field.name] === undefined) {
            this.formData[field.name] = '';
          }
        });
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    updateData() {
      this.$emit('update:modelValue', this.formData);
    }
  }
}
</script>
