<template>
  <div class="form-builder p-4 bg-white rounded shadow-sm border border-gray-200">
    <h3 class="text-lg font-semibold mb-4">Dynamic Form Builder</h3>
    
    <div v-for="(field, index) in schema" :key="index" class="p-3 mb-3 border rounded bg-gray-50 flex gap-3 items-start">
      <div class="flex-grow grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Field Name (Key)</label>
          <input v-model="field.name" type="text" class="form-control form-control-sm w-full" placeholder="e.g., first_name" @input="updateSchema">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Label</label>
          <input v-model="field.label" type="text" class="form-control form-control-sm w-full" placeholder="e.g., First Name" @input="updateSchema">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Type</label>
          <select v-model="field.type" class="form-control form-control-sm w-full" @change="updateSchema">
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="textarea">Textarea</option>
            <option value="date">Date</option>
            <option value="select">Dropdown</option>
          </select>
        </div>
        <div class="flex items-center pt-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="field.required" @change="updateSchema">
            <span class="text-sm">Required</span>
          </label>
        </div>
      </div>
      <button @click="removeField(index)" class="btn btn-sm btn-outline-danger mt-6">
        <i class="bi bi-trash"></i>
      </button>
    </div>

    <button @click="addField" class="btn btn-sm btn-primary">
      <i class="bi bi-plus"></i> Add Field
    </button>
  </div>
</template>

<script>
export default {
  name: 'FormBuilder',
  props: {
    modelValue: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      schema: []
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (JSON.stringify(newVal) !== JSON.stringify(this.schema)) {
          this.schema = JSON.parse(JSON.stringify(newVal || []));
        }
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    addField() {
      this.schema.push({
        name: '',
        label: '',
        type: 'text',
        required: false
      });
      this.updateSchema();
    },
    removeField(index) {
      this.schema.splice(index, 1);
      this.updateSchema();
    },
    updateSchema() {
      this.$emit('update:modelValue', this.schema);
    }
  }
}
</script>
