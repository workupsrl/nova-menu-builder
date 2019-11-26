<template>
  <vue-nestable
    :value="value"
    @input="val => $emit('input', val)"
    @change="$emit('onChangeMenu')"
    classProp="classProp"
  >
    <vue-nestable-handle slot-scope="{ item }" :item="item" class="handle flex flex-wrap">
      <div :class="`item-data w-2/3 flex ${!hasChildren(item) && 'pl-3'}`">
        <button
          v-if="hasChildren(item)"
          @click="toggleMenuChildrenCascade(item)"
          title="Edit"
          class="appearance-none cursor-pointer text-70 hover:text-primary flex pl-4 pr-4"
        >
          <menu-builder-arrow-icon :wrapperClass="`${isCascadeOpen(item) && 'btn-cascade-open'}`" />
        </button>

        <div class="text-90">{{ item.name }}</div>
        <div class="font-lighter text-80 ml-4 text-sm">{{ item.displayValue }}</div>
      </div>

      <div class="buttons w-1/3 flex justify-end content-center">
        <button
          @click="$emit('editMenu', item)"
          title="Edit"
          class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
        >
          <menu-builder-edit-icon />
        </button>

        <button
          @click="$emit('duplicateMenuItem', item)"
          title="Duplicate"
          class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
        >
          <menu-builder-duplicate-icon />
        </button>

        <button
          v-on:click="$emit('removeMenu', item)"
          title="Delete"
          class="appearance-none cursor-pointer text-70 hover:text-primary mr-1"
        >
          <menu-builder-delete-icon />
        </button>
      </div>
    </vue-nestable-handle>
  </vue-nestable>
</template>
<script>
import { VueNestable, VueNestableHandle } from 'vue-nestable';
import { codemirror } from 'vue-codemirror';

import 'codemirror/addon/display/placeholder.js';
//themes
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';
import 'codemirror/mode/javascript/javascript';

export default {
  props: {
    value: {
      type: Array,
      required: true,
    },
  },

  components: {
    VueNestable,
    VueNestableHandle,
    codemirror,
  },

  data: () => ({
    duplicateItem: null,
  }),

  methods: {
    hasChildren(item) {
      return Array.isArray(item.children) && item.children.length;
    },

    toggleMenuChildrenCascade(item) {
      if (item.classProp.find(className => className === 'hide-cascade'))
        item.classProp.splice(item.classProp.indexOf('hide-cascade'), 1);
      else item.classProp.push('hide-cascade');
      this.$emit('saveMenuLocalState', item);
    },

    isCascadeOpen(item) {
      return !item.classProp.find(className => className === 'hide-cascade');
    },
  },
};
</script>