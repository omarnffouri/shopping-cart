<template>
  <ul class="error-list mb-15" v-if="errorData">
    <li class="mb-10">{{ $t('error.feo') }}</li>
    <li
      v-for="(value, index) in errorData"
      :key="index"
    >
      {{ value }}
    </li>
  </ul>
</template>

<script>
  import { useUiStore } from '~/store/ui';

  export default {
    setup() {
      const uiStore = useUiStore();
      const {errors} = storeToRefs(uiStore)

      return { errors }
    },
    name: 'ErrorFormatter',
    props: {
      type: {
        type: String,
        default: 'form'
      }
    },
    computed: {
      errorData(){
        return this.errors && this.errors[this.type] !== undefined ? this.errors[this.type] : null
      },
    }
  }
</script>

<style lang="stylus">
</style>
