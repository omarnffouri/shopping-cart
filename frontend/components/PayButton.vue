<template>
  <div class="">
    <button
      class="outline-btn plr-30"
      aria-label="submit"
      @click.prevent="openPayNow"
    >
      {{ $t('checkout.payNow') }}
    </button>
    <payment-popup
      v-if="payNow"
      :order="order"
      @close="closePayNow"
    />
  </div>

</template>
<script>

  import PaymentPopup from "./PaymentPopup";
  export default {
    name: 'PayButton',
    data() {
      return {
        payNow: false
      }
    },
    watch: {},
    props: {
      order: {
        type: Object,
        default(){
          return null
        }
      }
    },
    components: {
      PaymentPopup
    },
    computed: {

    },
    mixins: [],
    methods: {
        openPayNow() {
            this.payNow = true
        },
        closePayNow() {
            this.payNow = false
        }
    },
    created() {
    },
    async mounted() {
    }
  }
</script>

