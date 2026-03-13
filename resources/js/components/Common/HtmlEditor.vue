<template>
  <div class="wysiwyg">
    <QuillEditor
      v-model:content="localValue"
      contentType="html"
      theme="snow"
      :toolbar="toolbar"
      @update:content="emitValue"
    />
  </div>
</template>

<script>
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

export default {
  name: 'HtmlEditor',
  components: { QuillEditor },
  props: {
    modelValue: { type: String, default: '' },
  },
  data() {
    return {
      localValue: this.modelValue,
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ header: [1, 2, 3, false] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['link'],
        ['clean'],
      ],
    }
  },
  watch: {
    modelValue(val) {
      if (val !== this.localValue) this.localValue = val
    },
  },
  methods: {
    emitValue(val) {
      this.$emit('update:modelValue', val)
    },
  },
}
</script>

<style scoped>
.wysiwyg :deep(.ql-container) {
  min-height: 140px;
  border-radius: 10px;
}
.wysiwyg :deep(.ql-toolbar) {
  border-radius: 10px 10px 0 0;
}
</style>
