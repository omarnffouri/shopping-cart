<template>
  <span>{{formattedPrice}}</span>
</template>

<script>
  import { useSettingStore } from '~/store/setting';

  import util from "~/mixin/util"
  import {storeToRefs} from "pinia";
  export default {
    setup(){
      const settingStore = useSettingStore()
      const {setting} = storeToRefs(settingStore)
      return { setting}
    },
    name: 'PriceFormat',
    props: {
      price: {
        type: Number,
        default: 0,
        required: true
      }
    },
    mixins: [util],
    components: {},
    computed: {
      formattedPrice(){
        if(parseInt(this.setting.currency_position) === this.currencyPositionsIn.PRE) {
          return this.setting.currency_icon + this.price
        }
        return this.price + this.setting.currency_icon
      },
    },
  }
</script>

