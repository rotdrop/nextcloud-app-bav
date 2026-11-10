import { defineComponent } from 'vue';

// Preserve the UI contracts used by the app without loading Nextcloud's runtime.
export const NcTextField = defineComponent({
  inheritAttrs: false,
  props: ['modelValue', 'label'],
  emits: ['update:modelValue', 'blur'],
  template: `<label>{{ label }}
    <input v-bind="$attrs" :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)">
  </label>`,
});

export const NcButton = defineComponent({
  template: '<button type="button"><slot /></button>',
});

export const NcDialog = defineComponent({
  props: ['open', 'name'],
  emits: ['update:open'],
  template: '<section v-if="open" role="dialog" :aria-label="name"><slot /><slot name="actions" /></section>',
});

export const NcPopover = defineComponent({
  props: ['shown'],
  emits: ['update:shown'],
  template: '<div><slot name="trigger" /><div v-if="shown"><slot /></div></div>',
});
