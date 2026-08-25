<template>
  <button
          @click.passive="btnClicked" class="ajax-btn"
          :disabled="disable || disabled || !isActive"
          :type="propType"
  >
    <Spinner v-if="fetchingData" :color="color" />

    <i v-if="onlyIcon && !fetchingData"
       class="icon"
       :class="onlyIcon"
    />
    <span v-if="!onlyIcon && propLoadingText && fetchingData">
      {{ propLoadingText }}
    </span>
    <span v-else-if="!onlyIcon && !fetchingData">
      {{ propText }}
    </span>
  </button>
</template>

<script>
  import { storeToRefs } from 'pinia'
  import { useAdminStore } from '~/store/admin'
  import { useI18n } from 'vue-i18n'
  import Spinner from '~/components/Spinner.vue'

  export default {
    name: 'AjaxButton',
    components: {
      Spinner,
    },
    props: {
      color: {
        type: String,
        default: 'white',
      },
      text: {
        type: String,
        default: null,
      },

      loadingText: {
        type: String,
        default: null,
      },
      fetchingData: {
        type: Boolean,
        default: false,
      },
        disabled: {
            type: Boolean,
            default: false,
        },
        onlyIcon: {
            type: String,
            default: null,
        },
      activateBtn: {
        type: Boolean,
        default: false,
      },
      type: {
        type: String,
        default: '',
      },
    },
    setup(props, { emit }) {
      const adminStore = useAdminStore()
      const { activated } = storeToRefs(adminStore)

      const { t } = useI18n()


      // Resolve dynamic default
      const propText = props.text || t('profile.submit')
      const propLoadingText = props.loadingText || t('profile.gr')
      const propType = props.type || t('profile.submit')

      // Computed properties
      const fetchingData = computed(() => props.fetchingData)
      const disable = computed(() => props.fetchingData)
      const isActive = computed(() => props.activateBtn || activated.value)

      // Button click handler
      const btnClicked = () => {
        if (props.type !== 'Submit') {
          emit('clicked')
        }
      }
      return {
        propText,
        propLoadingText,
        propType,
        disable,
        isActive,
        btnClicked,
        fetchingData
      }
    },
  }
</script>

<style lang="stylus">
  .ajax-btn
    gap 10px
    display flex
    justify-content center
    align-items center

    span
      white-space nowrap
      overflow hidden
      text-overflow ellipsis

  button:disabled,
  button[disabled]
    opacity .6
    cursor no-drop
</style>
