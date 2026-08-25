<template>
  <span>{{formattedPrice}}</span>
</template>

<script>
  import { useSettingStore } from '~/store/setting';

  import util from "~/mixin/util"
  import {storeToRefs} from "pinia";

  export default {
    setup() {
      const settingStore = useSettingStore()
      const {setting} = storeToRefs(settingStore)
      return { setting}
    },
    name: 'PriceFormat',
    props: {
      price: {
        default: 0,
        required: true
      }
    },
    mixins: [util],
    components: {},
    computed: {
      currencyPosition() {
        return this.setting?.currency_position
      },
      currencyIcon() {
        return this.setting?.currency_icon || '$'
      },
      formattedPrice(){
        return this.priceFormat({type: 1, price: this.price, icon: this.setting?.currency_icon})
        //return this.priceFormat(this.setting?.currency_position, this.setting?.currency_icon, this.price, this.setting)
      }
    },
  }
</script>

