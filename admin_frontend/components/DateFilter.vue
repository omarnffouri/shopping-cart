<template>
  <div
    class="dply-felx inputs align-end j-left"
  >
    <div
      class="input-wrapper"
    >
      <label class="mb-10">{{ $t('prod.sTime') }}</label>

      <flat-pickr width="180px" :config="dpConfig" v-model="filter.start_time"/>
    </div>

    <div
      class="input-wrapper"
    >
      <label class="mb-10">{{ $t('prod.eTime') }}</label>

      <flat-pickr width="180px" :config="dpConfig" v-model="filter.end_time"/>
    </div>

    <ajax-button
      name="save-edit"
      class="primary-btn mlr-5 mtb-sm-5"
      :text="$t('ship.fil')"
      type="button"
      @clicked="filterChanged"
    />

    <button class="outline-btn" @click="clearTime">
      {{ $t('ship.cl') }}
    </button>
  </div>
</template>

<script>
  import flatPickr from 'vue-flatpickr-component';
  import util from '~/mixin/util'
  import AjaxButton from "./AjaxButton";

  export default {
    name: 'DateFilter',
    components: {
      AjaxButton,
      flatPickr
    },
    directives: {},
    props: {
    },
    mixins: [util],
    computed: {

    },
    data() {
      return {
        filter: {
          start_time: '',
          end_time: ''
        },
      }
    },
    methods: {
      filterChanged(){
        this.$emit('date-changed', this.filter)
      },
      clearTime(){
        this.filter.start_time = ''
        this.filter.end_time = ''
        this.filterChanged()
      },
    },
    mounted() {
      this.filter.start_time = this.$route?.query?.start_time || ''
      this.filter.end_time = this.$route?.query?.end_time || ''
    },
  }
</script>
