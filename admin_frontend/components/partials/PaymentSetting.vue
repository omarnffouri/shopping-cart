<template>
    <form @submit.prevent="updatePayment" class="payment-form" :class="{'has-error': hasError}">
        <div
            v-if="gettingData"
            class="spinner-wrapper"
        >
            <spinner
                :radius="60"
                color="primary"
                class="mr-15"
            />
        </div>

        <div class="input-wrapper">
            <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('admin.default') }}
          </span>
                <dropdown
                    :selectedKey="paymentData.default"
                    :options="paymentTypesDrop"
                    @clicked="paymentTypesSelect"
                />
            </div>
        </div>

        <div class="input-wrapper dply-felx start">
            <label
                for="cod"
                class="mb-0">
                <input
                    type="checkbox"
                    id="cod"
                    :true-value="1" :false-value="0"
                    v-model="paymentData.cash_on_delivery"
                />

                {{ $t('fSale.cod') }}
            </label>
        </div>

        <paypal
            :payment-data="paymentData"
        />

        <stripe
            :payment-data="paymentData"
        />

        <razorpay
            :payment-data="paymentData"
        />

        <flutterwave
            :payment-data="paymentData"
        />

        <iyzico-payment
            :payment-data="paymentData"
        />

        <bank-payment
            :payment-data="paymentData"
        />


        <pay-fast
            :payment-data="paymentData"
        />

        <telr
            :payment-data="paymentData"
        />

        <div class="oflow-hidden">
            <ajax-button
                v-if="$can('setting', 'edit')"
                class="primary-btn"
                :fetching-data="updatingPayment"
            />
        </div>
    </form>
</template>

<script>

import AjaxButton from '~/components/AjaxButton'
import util from "~/mixin/util"
import Spinner from '~/components/Spinner'
import Paypal from "~/components/partials/Paypal";
import Stripe from "~/components/partials/Stripe";
import Razorpay from "~/components/partials/Razorpay";
import Flutterwave from "./Flutterwave";
import IyzicoPayment from "./IyzicoPayment";
import BankPayment from "./BankPayment";
import Dropdown from "../Dropdown";
import PayFast from "./PayFast";
import Telr from "./Telr";
import {storeToRefs} from "pinia";
import {usePaymentStore} from "../../store/payment";
import {showError} from "nuxt/app";


export default {
    setup() {
        const paymentStore = usePaymentStore()
        const {setPayment, getPayment} = paymentStore
        const {payment} = storeToRefs(paymentStore)
        return {setPayment, getPayment, payment}
    },
    name: 'PaymentSetting',
    data() {
        return {
            gettingData: false,
            updatingPayment: false,
            hasError: false,
            paymentData:{
                id: null,
                default: 1,
                paypal: true,
                paypal_key: null,
                paypal_secret: null,
                cash_on_delivery: true,
                stripe: true,
                razorpay: true,
                razorpay_key: null,
                razorpay_secret: null,
                stripe_key: null,
                stripe_secret: null,

                flutterwave: true,
                fw_public_key: null,
                fw_secret_key: null,
                fw_encryption_key: null,
                fw_environment: null,

                iyzico_payment: true,
                ip_base_url: null,
                ip_api_key: null,
                ip_secret_key: null,

                bank: true,
                bank_name: null,
                branch_name: null,
                account_name: null,
                account_number: null,

                payfast_payment: true,
                payfast_base_url: '',
                payfast_merchant_id: '',
                payfast_merchant_key: '',
                payfast_passphrase: '',

                telr: true,
                telr_store_id: '',
                telr_auth_key: '',
                telr_mode: '',
            }
        }
    },
    props: {},
    mixins: [util],
    components: {
        PayFast,
        Dropdown,
        IyzicoPayment,
        Flutterwave,
        Razorpay,
        Stripe,
        Paypal,
        AjaxButton,
        Spinner,
        BankPayment,
        Telr
    },
    computed: {

    },
    methods: {
        paymentTypesSelect(data){
            this.paymentData.default = data.key
        },
        async updatePayment() {
            this.updatingPayment = true
            try {
                await this.setPayment(this.paymentData)
            } catch (e) {
                showError({
                    statusCode: 400,
                    message: e.message
                })
            }
            this.updatingPayment = false
        },

    },
    created() {
    },
    async mounted() {
        if (!this.payment) {
            this.gettingData = true
            try {
                await this.getPayment()
            } catch (e) {
                showError({
                    statusCode: 400,
                    message: e.message
                })
            }
            this.gettingData = false
        }
        this.paymentData = Object.assign({}, this.payment)
    }
}
</script>
