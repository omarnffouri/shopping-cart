<template>
    <div class="pos-rel">
        <stripe-payment
            v-if="showStripe"
            :stripe-key="paymentGateway.stripe_key"
            :order-id="orderId"
            :amount="parseFloat(amount)"
            :currency="currencyData"
            :name="userName"
            :site-name="site_setting.siteName"
            :email="userEmail"
            @success="orderPlaced('success', $event)"
            @closed="orderPlaced('closed', $event)"
        />

        <razorpay-payment
            v-else-if="showRazorpay"
            :order-id="orderId"
            :razorpay-key="paymentGateway.razorpay_key"
            :razorpay-payment-token="razorpayPaymentToken"
            :amount="parseFloat(amount)"
            :site-name="site_setting.siteName"
            :currency="currencyData"
            :name="userName"
            :email="userEmail"
            @success="orderPlaced('success', $event)"
            @closed="orderPlaced('closed', $event)"
            @error="orderPlaced('error', $event)"
        />

        <teleport to="body">
            <telr-payment
                v-if="showTelr && telrPaymentRef"
                :paymentRef="telrPaymentRef"
                :amount="parseFloat(amount)"
                :site-name="site_setting.site_name"
                @success="orderPlaced('success', $event)"
                @error="orderPlaced('error', $event)"
                @closed="orderPlaced('closed', $event)"
            />
        </teleport>

        <transition name="fade" mode="out-in">
            <div class="spinner-wrapper flex layer-white" v-if="loading || payFastLoader">
                <spinner :radius="100"/>
            </div>
        </transition>

        <p v-if="orderError"
           class="f-13 error mb-15">
            <span v-for="i in orderError" class="block">{{ i }}</span>
        </p>
        <p v-if="showMissingDeliveryDateError" class="f-13 error mb-15">
            Please select a delivery date first.
        </p>
        <p v-if="showMissingDeliveryTimeSlotError" class="f-13 error mb-15">
            Please select a delivery time slot first.
        </p>
        <form v-if="paymentGateway">
            <p v-if="noPaymentMethod" class="info mt-15">
                {{ $t('checkout.noPayment') }}
            </p>

            <div class="heading-tab-wrapper">
                <div class="tab-heading">

                    <label v-if="parseInt(paymentGateway.stripe) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.STRIPE}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.STRIPE"
                            v-model="paymentType"
                        >
                        <i class="icon stripe-icon"/>
                        <span>{{ $t('payment.stripe') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.flutterwave) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.FLUTTERWAVE}">
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.FLUTTERWAVE"
                            v-model="paymentType"
                        >
                        <i class="icon flutterwave-icon"/>
                        <span>{{ $t('payment.flutterwave') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.razorpay) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.RAZORPAY}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.RAZORPAY"
                            v-model="paymentType"
                        >
                        <i class="icon razorpay-icon"/>
                        <span>{{ $t('payment.razorpay') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.paypal) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.PAYPAL}"
                    >
                        <span
                            class="spinner-wrapper flex layer-white"
                            v-if="!paypaLoaded && paymentType === orderMethods.PAYPAL"
                        >
                            <spinner :radius="50"/>
                        </span>
                        <input type="radio"
                               name="payment"
                               :value="orderMethods.PAYPAL"
                               v-model="paymentType"
                        >
                        <i class="icon paypal-icon"/>
                        <span>{{ $t('payment.paypal') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.iyzico_payment) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.IYZICO_PAYMENT}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.IYZICO_PAYMENT"
                            v-model="paymentType"
                        >
                        <i class="icon iyzico-icon"/>
                        <span>{{ $t('filter.payIyzico') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.payfast_payment) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.PAYFAST}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.PAYFAST"
                            v-model="paymentType"
                        >
                        <i class="icon payfast-icon"/>
                        <span>{{ $t('invent.pf') }}</span>
                    </label>

                    <label v-if="parseInt(paymentGateway.telr) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.TELR}">
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.TELR"
                            v-model="paymentType"
                        >

                      <img
                          src="/assets/payments/atm-card.png"
                          alt="Card"
                          style="height: 32px; width: auto; margin-bottom: 10px"
                      />

                        <span>{{ $t('util.creditDebit') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.cash_on_delivery) === status.PUBLIC"
                           :class="{active: paymentType === orderMethods.CASH_ON_DELIVERY}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.CASH_ON_DELIVERY"
                            v-model="paymentType"
                        >
                        <i class="icon cod-icon"/>
                        <span>{{ $t('orderTabbing.cod') }}</span>
                    </label>


                    <label v-if="parseInt(paymentGateway.bank) === status.PUBLIC && orderMethod !== orderMethods.BANK"
                           :class="{active: paymentType === orderMethods.BANK}"
                    >
                        <input
                            type="radio"
                            name="payment"
                            :value="orderMethods.BANK"
                            v-model="paymentType"
                        >
                        <i class="icon bank-icon mb-5"/>
                        <span>{{ $t('date.bt') }}</span>
                    </label>

                </div>

                <div class="tab-content">
                    <ajax-button v-if="paymentType === orderMethods.STRIPE"
                                 class="primary-btn plr-30 plr-sm-15"
                                 type="button"
                                 :fetching-data="placingOrder"
                                 :disabled="!totalPrice || noPaymentMethod"
                                 :text="paymentBtnText"
                                 @clicked="initStripe"
                    />

                    <ajax-button v-else-if="paymentType === orderMethods.RAZORPAY"
                                 class="primary-btn plr-30 plr-sm-15"
                                 type="button"
                                 :fetching-data="placingOrder"
                                 :disabled="!totalPrice || noPaymentMethod"
                                 :text="paymentBtnText"
                                 @clicked="initRazorpay"
                    />

                    <div v-else-if="paymentType === orderMethods.PAYFAST">
                        <ajax-button
                            class="primary-btn plr-30 plr-sm-15"
                            type="button"
                            :fetching-data="placingOrder"
                            :disabled="!totalPrice || noPaymentMethod"
                            :text="paymentBtnText"
                            @clicked="initPayFast"
                        />

                        <div v-if="payFastData" ref="payFastContainer" v-html="payFastForm"></div>
                    </div>

                    <div v-else-if="paymentType === orderMethods.CASH_ON_DELIVERY">
                        <ajax-button
                            class="primary-btn plr-30 plr-sm-15"
                            type="button"
                            :fetching-data="placingOrder"
                            :disabled="!totalPrice || noPaymentMethod"
                            :text="$t('checkout.confirmOrder')"
                            @clicked="confirmOrder"
                        />
                    </div>

                    <ajax-button v-else-if="paymentType === orderMethods.BANK"
                                 class="primary-btn  plr-30 plr-sm-15"
                                 type="button"
                                 :fetching-data="placingOrder"
                                 :disabled="!totalPrice || noPaymentMethod"
                                 :text="$t('checkout.confirmOrder')"
                                 @clicked="confirmOrder"
                    />

                    <div v-else-if="paymentType === orderMethods.IYZICO_PAYMENT">
                        <iyzico-payment
                            ref="iyzicoPayment"
                            :order="orderData"
                            :btn-text="paymentBtnText"
                            @clicked="payWithIyzicoPayment"
                            @success="izcoOrderPlaces"
                            @closed="orderPlaced('closed', $event)"
                            @error="orderPlaced('error', $event)"
                        />
                    </div>

                    <div v-else-if="paymentType === orderMethods.FLUTTERWAVE">
                        <flutterwave-pay-btn
                            ref="flutterWave"
                            :order="orderData"
                            :public-key="paymentGateway.fw_public_key"
                            :amount="parseFloat(amount)"
                            :currency="setting.currency"
                            :btn-text="paymentBtnText"
                            :name="userName"
                            :loading="!flutterwaveLoaded"
                            :user-id="`${userId}`"
                            :email="userEmail"
                            :site-name="siteName"
                            :header-logo="headerLogo"
                            @clicked="payWithFlutterWave"
                            @success="orderPlaced('success', $event)"
                            @closed="orderPlaced('closed', $event)"
                            @error="orderPlaced('error', $event)"
                        />
                    </div>

                    <div v-else-if="paymentType === orderMethods.TELR">
                        <ajax-button
                            class="primary-btn plr-30 plr-sm-15"
                            type="button"
                            :fetching-data="placingOrder"
                            :disabled="!totalPrice || noPaymentMethod"
                            :text="paymentBtnText"
                            @clicked="initTelr"
                        />
                    </div>

                    <div v-if="parseInt(paymentGateway.paypal) === status.PUBLIC"
                         class="paypal-tab" :class="{'paypal-active': paymentType === orderMethods.PAYPAL}">
                        <div ref="paypal"/>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<script>
import StripePayment from '~/components/StripePayment'
import RazorpayPayment from '~/components/RazorpayPayment'
import util from '~/mixin/util'
import productHelper from "~/mixin/productHelper"
import productPriceHelper from "~/mixin/productPriceHelper"
import paymentHelper from '~/mixin/paymentHelper'
import Spinner from "./Spinner";
import AjaxButton from "./AjaxButton";
import FlutterwavePayBtn from "./FlutterwavePayBtn";
import IyzicoPayment from "./IyzicoPayment";
import {useLanguageStore} from "~/store/language";
import {useCommonStore} from "~/store/common";
import {useCartStore} from "~/store/cart";
import {useUserStore} from "~/store/user";
import TelrPayment from '~/components/TelrPayment'
import {storeToRefs} from "pinia";
import {prepareGetUrl} from "../utils/fetchClient";
import {useTracking} from "~/composables/useTracking";

export default {
    setup() {
        const languageStore = useLanguageStore()
        const {langCode} = storeToRefs(languageStore)

        const userStore = useUserStore()
        const {profile} = storeToRefs(userStore)
        const {getUserToken} = userStore

        const { trackPurchase } = useTracking();
        const commonStore = useCommonStore()
        const {currencyIcon, setting, currency, paymentGateway, site_setting} = storeToRefs(commonStore)
        const {setToastMessage, setToastError, postRequest, getRequest, bgGetRequest, unAuthPost, setPaymentGateway} = commonStore

        const currencyPosition = computed(() => {
            return setting.value.currency_position;
        })
        const cartStoreStore = useCartStore()
        const {cartProducts} = storeToRefs(cartStoreStore)
        const {
            getCartByUser,
            subtractCartProductCount,
            emptyCartProduct,
            updateCartShipping,
            cartChanged
        } = cartStoreStore

        return {
            getUserToken, profile, currencyPosition, cartProducts, langCode, postRequest, getRequest,
            currency, paymentGateway, site_setting, bgGetRequest, unAuthPost,
            currencyIcon, setting, setToastMessage, setToastError,
            getCartByUser, subtractCartProductCount, emptyCartProduct, updateCartShipping, cartChanged, trackPurchase, setPaymentGateway
        }
    },
    middleware: ['auth'],
    data() {
        return {
            deliveryStorageKey: 'checkout_delivery_schedule',
            loading: false,
            payFastLoader: false,
            payFastData: null,
            flutterwaveLoaded: false,
            paypaLoaded: false,
            showRazorpay: false,
            showTelr: false,
            showStripe: false,
            paymentType: 1,
            orderData: null,
            orderError: null,
            submitting: false,
            placingOrder: false,
            checkedProductQty: 0,
            attemptedSubmitWithoutDate: false,
            attemptedSubmitWithoutTimeSlot: false,
            conversionSent: false,
        }
    },
    props: {
        voucher: {
            type: Object,
            default() {
                return null
            }
        },
        order: {
            type: Object,
            default() {
                return null
            }
        },
        page: {
            type: String,
            default: 'checkout'
        },
        totalPrice: {
            type: Number,
            default: 0
        },
        selectedAddressId: {
            type: [Number, String],
            default: null
        },
        deliveryDate: {
            type: String,
            default: ''
        },
        deliveryTimeSlot: {
            type: String,
            default: ''
        },
        cartShipping: {
            type: Object,
            default() {
                return {}
            }
        },
        userDeliveryTypeId: {
            type: [Number, String],
            default: null
        }
    },
    watch: {
        payFastForm() {
            this.$nextTick(() => {
                this.$refs.payFastContainer?.querySelector('#frmPayment')?.submit()
                //this.payFastLoader = false
            })
        }
    },
    components: {
        IyzicoPayment,
        FlutterwavePayBtn,
        AjaxButton,
        Spinner,
        RazorpayPayment,
        StripePayment,
        TelrPayment
    },
    mixins: [util, productHelper, paymentHelper, productPriceHelper],
    computed: {
        telrPaymentRef() {
            return this.orderData?.telr?.ref || this.orderData.payment_token || null
        },
        payFastForm() {
            return this.payFastData?.payfast
        },
        orderMethod() {
            return this.order?.order_method
        },
        paymentBtnText() {
            return this.$t('checkout.confirmOrderAnd', {amount: this.formattedPrice})
        },
        formattedPrice() {
            return this.priceFormat(this.currencyPosition, this.currencyIcon, this.totalPrice, this.setting)
        },
        voucherResult() {
            return this.voucher
        },
        isCheckout() {
            return this.page === 'checkout'
        },
        userEmail() {
            return this.orderData?.email || this.profile?.email
        },
        headerLogo() {
            return this.imageURL({'image': this.site_setting.header_logo})
        },
        siteName() {
            return this.site_setting?.site_name
        },
        currencyData() {
            return this.orderData?.currency || this.currency
        },
        userId() {
            return this.profile?.id
        },
        userName() {
            return this.orderData?.userName || this.profile?.name
        },
        razorpayPaymentToken() {
            return this.orderData?.payment_token || null
        },
        amount() {
            return parseFloat(this.orderData?.total_amount).toFixed(2) || 0
        },
        orderId() {
            return this.orderData?.id || null
        },
        noPaymentMethod() {
            return parseInt(this.paymentGateway?.stripe) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.razorpay) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.paypal) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.iyzico_payment) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.flutterwave) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.bank) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.cash_on_delivery) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.payfast_payment) !== this.status.PUBLIC &&
                parseInt(this.paymentGateway?.telr) !== this.status.PUBLIC
        },
        checkedProduct() {
            return this.cartProducts.filter(obj => {
                return parseInt(obj.selected) === 1
            })
        },
        missingDeliveryDate() {
            return this.isCheckout && !this.deliveryDate
        },
        showMissingDeliveryDateError() {
            return this.attemptedSubmitWithoutDate && this.missingDeliveryDate
        },
        missingDeliveryTimeSlot() {
            return this.isCheckout && !!this.deliveryDate && !this.deliveryTimeSlot
        },
        showMissingDeliveryTimeSlotError() {
            return this.attemptedSubmitWithoutTimeSlot && this.missingDeliveryTimeSlot
        },
        resolvedUserDeliveryTypeId() {
            if (this.userDeliveryTypeId) {
                return this.userDeliveryTypeId
            }
            return null
        },
    },
    methods: {
        startRetryOrderTimer(orderId, minutes = 10) {
            if (!import.meta.client || !orderId) return
            const expiresAt = Date.now() + (minutes * 60 * 1000)
            localStorage.setItem('retry_order_timer_expires_at', String(expiresAt))
            localStorage.setItem('retry_order_id', String(orderId))
            window.dispatchEvent(new CustomEvent('retry-order-timer-updated'))
        },
        getStoredDeliverySchedule() {
            if (typeof window === 'undefined') return null
            try {
                const raw = localStorage.getItem(this.deliveryStorageKey)
                if (!raw) return null
                return JSON.parse(raw)
            } catch (e) {
                return null
            }
        },
        resolveStoredDeliveryFields() {
            const stored = this.getStoredDeliverySchedule()
            if (!stored) return null

            const deliveryDate = stored?.delivery_date || stored?.deliveryDate || ''
            const deliveryTypeId = stored?.delivery_type_id || stored?.deliveryTypeId || stored?.selected_delivery_method || ''
            const timeSlotId = stored?.time_slot_id || stored?.timeSlotId || ''

            if (!deliveryDate || !deliveryTypeId || !timeSlotId) return null

            return {
                delivery_type_id: `${deliveryTypeId}`,
                delivery_date: deliveryDate,
                time_slot_id: `${timeSlotId}`
            }
        },
        async payWithIyzicoPayment() {
            const order = await this.confirmOrder()
            if (!order) return
            this.$refs.iyzicoPayment.makePayment(!!this.isCheckout)
        },
        async payWithFlutterWave() {
            const order = await this.confirmOrder()
            if (!order) return
            this.$refs.flutterWave.makePayment()
        },
        setLoaded() {
            window.paypal.Buttons({
                    style: {
                        label: 'pay'
                    },
                    createOrder: async (data, actions) => {
                        if (this.isCheckout) {
                            return this.confirmOrder()
                                .then((orderData) => {
                                    if (!orderData) {
                                        throw new Error('Delivery date is required')
                                    }
                                    return actions.order.create({
                                        purchase_units: [
                                            {
                                                description: '',
                                                amount: {
                                                    currency_code: this.currency,
                                                    value: this.amount
                                                }
                                            }
                                        ]
                                    });
                                })
                        } else {
                            this.orderData = this.order
                            return actions.order.create({
                                purchase_units: [
                                    {
                                        description: '',
                                        amount: {
                                            currency_code: this.currency,
                                            value: this.amount
                                        }
                                    }
                                ]
                            });
                        }
                    },
                    onApprove: async (data, actions) => {
                        return actions?.order?.capture()?.then(async function (details) {
                            // console.log('Transaction completed by ' + details.payer.name.given_name);
                            await this.paymentDoneFn(this.orderId, this.orderId, this.orderMethods.PAYPAL)
                            this.orderPlaced('success', this.orderId)
                        });
                    },
                    onError: err => {
                        this.orderPlaced('closed', err)
                    }
                })
                .render(this.$refs.paypal)
        },
        async initIyzico() {
            try {
                await this.confirmOrder()
            } catch (e) {
                console.log(e)
            }
        },
        async initPayFast() {
            try {
                const order = await this.confirmOrder()
                if (!order) return
                this.payFastData = order

                this.payFastLoader = true
            } catch (e) {
                console.log(e)
            }
        },
        async initRazorpay() {
            try {
                const order = await this.confirmOrder()
                if (!order) return
                this.showRazorpay = true
            } catch (e) {
                console.log(e)
            }
        },
        async initStripe() {
            try {
                const order = await this.confirmOrder()
                if (!order) return
                this.showStripe = true
            } catch (e) {
                console.log(e)
            }
        },
        async initTelr() {
            try {
                const order = await this.confirmOrder()
                if(!order) return
                this.showTelr = this.telrPaymentRef
                if (this.isCheckout && !this.showTelr) {
                    this.orderPlaced('success', order.id)
                }
            } catch (e) {
                console.log(e)
            }
        },
        confirmOrder() {
            return new Promise(async resolve => {
                if (this.missingDeliveryDate) {
                    this.attemptedSubmitWithoutDate = true
                    const msg = 'Please select a delivery date first.'
                    this.orderError = null
                    this.setToastError(msg)
                    resolve(null)
                    return
                }
                this.attemptedSubmitWithoutDate = false
                if (this.missingDeliveryTimeSlot) {
                    this.attemptedSubmitWithoutTimeSlot = true
                    const msg = 'Please select a delivery time slot first.'
                    this.orderError = null
                    this.setToastError(msg)
                    resolve(null)
                    return
                }
                this.attemptedSubmitWithoutTimeSlot = false
                if (this.isCheckout) {
                    if (this.orderData?.id && parseInt(this.orderData?.order_method) === parseInt(this.paymentType)) {
                        resolve(this.orderData)
                        return
                    }
                    this.orderError = '';
                    this.placeOrder()
                        .then(result => {
                            const data = result?.data
                            if (parseInt(data.order_method) !== this.orderMethods.CASH_ON_DELIVERY ||
                                parseInt(data.order_method) !== this.orderMethods.BANK
                            ) {
                                data['total_amount'] = data.amount
                            }
                            this.orderData = data
                            resolve(data)
                        })
                } else {
                    this.orderData = this.order
                    let payDone = null
                    if (parseInt(this.paymentType) === this.orderMethods.CASH_ON_DELIVERY) {
                        this.placingOrder = true
                        await this.paymentDoneFn(this.order.id, this.order.id, this.orderMethods.CASH_ON_DELIVERY)
                        this.placingOrder = false
                        this.orderPlaced('success', this.order.id)
                    }
                    if (parseInt(this.paymentType) === this.orderMethods.BANK) {
                        this.placingOrder = true
                        await this.paymentDoneFn(this.order.id, this.order.id, this.orderMethods.BANK)
                        this.placingOrder = false
                        this.orderPlaced('success', this.order.id)
                    }
                    if (parseInt(this.paymentType) === this.orderMethods.IYZICO_PAYMENT) {
                        const {data} = await this.paymentDoneFn(this.order.id, this.order.id, this.orderMethods.IYZICO_PAYMENT)
                        this.orderData = {...this.orderData, ...data}
                    }
                    if (parseInt(this.paymentType) === this.orderMethods.PAYFAST) {
                        const {data} = await this.paymentDoneFn(this.order.id, this.order.id, this.orderMethods.PAYFAST)
                        payDone = data
                    }
                    if (parseInt(this.paymentType) === this.orderMethods.TELR) {
                        if (this.telrPaymentRef) {
                            const response = await this.paymentResponseTelr({OrderRef: this.telrPaymentRef})
                            if (response.status === 200) {
                                if (response.data?.order?.payment_done) {
                                    this.setToastMessage(response.data.message)
                                }
                                if (response.data?.ref) {
                                    this.orderData.payment_token = response.data.ref
                                    payDone = this.orderData
                                }
                            } else {
                                this.setToastError(response.data.form.join(', '))
                            }
                        }else{
                            this.loading = true
                            const response = await this.postRequest({
                                lang: this.langCode,
                                api: 'createTelrPaymentRef',
                                params: {
                                    data: this.phpEncryption({
                                        user_token: this.getUserToken(),
                                        order_id: this.orderData.id,
                                        order_method: this.paymentType,
                                        time_zone: this.timeZone,
                                    })
                                }
                            })
                            this.loading = false
                            if (response?.status === 200) {
                                this.orderData.telr = response.data
                                payDone = this.orderData
                            } else {
                                this.setToastError(response.data.form.join(', '))
                            }
                        }
                    }
                    resolve(payDone)
                }
            })
        },
        izcoOrderPlaces(evt) {
            this.orderPlaced('success', evt?.id, evt?.redirect, false)
        },
        orderPlaced(type = 'success', event, redirect = true, showToast = true) {
            const orderId = this.orderId || event?.id || event
            if (type === 'success') {
                if (!this.conversionSent && this.isCheckout) {
                    this.conversionSent = true;
                    const orderValue = parseFloat(this.amount || 1.00);
                    this.trackPurchase({
                        orderId: orderId,
                        value: orderValue,
                        currency: this.setting.currency || 'AED'
                    })
                }
                if (showToast) this.setToastMessage(this.$t('payButton.placedSuccess'));
                if (redirect) this.$router.push({ path: `/user/order/${orderId}` });
                this.$emit('order-status', true);
            } else {
                if (type === 'error') this.setToastError(event)
                if (redirect) this.$router.push({path: `/user/abandoned_order/${orderId}`});
                this.$emit('order-status', false)
            }
        },
        async placeOrder() {
            return new Promise((async (resolve, reject) => {
                if (this.checkedProduct.length) {
                    const params = []
                    this.checkedProduct.forEach((obj) => {
                        let shippingPrice = 0
                        if (parseInt(obj.shipping_type) === 1) {
                            shippingPrice = parseInt(obj?.shipping_place?.price)
                        } else if (parseInt(obj.shipping_type) === 2) {
                            shippingPrice = parseInt(obj?.shipping_place?.pickup_price)
                        }
                        const currentInventoryPrice = this.currentInventoryPriceCalc(obj.inventory, obj.flash_product)
                        const currentPrice = parseInt(obj?.quantity) * currentInventoryPrice
                        const currentOffer = currentInventoryPrice * parseInt(obj?.offered)
                        this.checkedProductQty += parseInt(obj?.quantity)

                        params.push({
                            cart: obj.id,
                            bundle_offer: currentOffer,
                            shipping_price: shippingPrice,
                            selling: currentPrice,
                        })
                    })
                    this.loading = true
                    const userToken = this.getUserToken();

                    if (this.isCheckout) {
                        const missingShipping = this.checkedProduct.some((obj) => {
                            return !this.cartShipping?.[obj.id]?.shipping_place
                        })

                        if (missingShipping) {
                            this.loading = false
                            this.setToastError(this.$t('shipping.unableShipped'))
                            reject()
                            return
                        }
                    }
                    if (this.isCheckout && this.missingDeliveryTimeSlot) {
                        this.loading = false
                        this.attemptedSubmitWithoutTimeSlot = true
                        this.setToastError('Please select a delivery time slot first.')
                        reject()
                        return
                    }

                    if (this.isCheckout) {
                        const shippingUpdate = await this.updateCartShipping({
                            cart: this.cartShipping,
                            user_token: userToken,
                            selected_address: this.selectedAddressId,
                            lang: this.langCode
                        })
                        if (shippingUpdate?.status !== 200) {
                            this.loading = false
                            if (shippingUpdate?.data?.form?.length) {
                                this.orderError = shippingUpdate.data.form
                                this.setToastError(shippingUpdate.data.form[0])
                            } else if (shippingUpdate?.data?.product) {
                                const productError = []
                                Object.values(shippingUpdate?.data?.product[0]).forEach((obj) => {
                                    obj.forEach((o) => productError.push(o))
                                })
                                this.orderError = productError
                                if (productError.length) {
                                    this.setToastError(productError[0])
                                }
                            }
                            reject()
                            return
                        }
                    }

                    let res = await this.postRequest({
                        lang: this.langCode,
                        api: 'orderAction',
                        params: {
                            data: this.phpEncryption({
                                user_token: userToken,
                                order_method: this.paymentType,
                                voucher: this.voucherResult?.voucher || '',
                                time_zone: this.timeZone,
                                selected_address: this.selectedAddressId,
                                ...(this.isCheckout ? (this.resolveStoredDeliveryFields() || {}) : {})
                            })
                        }
                    })

                    this.loading = false
                    if (res?.status === 200) {
                        if (
                            parseInt(res.data.order_method) !== this.orderMethods.CASH_ON_DELIVERY &&
                            parseInt(res.data.order_method) !== this.orderMethods.BANK
                        ) {
                            this.startRetryOrderTimer(res.data.id)
                        }
                        setTimeout(async () => {
                            await this.bgGetRequest({
                                params: `/${res.data.id}?${prepareGetUrl({
                                    id: res.data.id,
                                    time_zone: this.timeZone,
                                    user_token: await this.getUserToken()
                                })}`,
                                lang: this.langCode,
                                api: 'sendOrderEmail'
                            });
                        }, 100);
                        this.subtractCartProductCount({
                            qty: this.checkedProductQty,
                            status: this.status
                        });
                        if (parseInt(res.data.order_method) === this.orderMethods.CASH_ON_DELIVERY ||
                            parseInt(res.data.order_method) === this.orderMethods.BANK
                        ) {
                            this.orderPlaced('success', res.data.id)
                        }
                        resolve(res)
                    } else if (res?.status === 201) {
                        if (res?.data?.form) {
                            this.orderError = res?.data?.form
                        } else if (res?.data?.product) {
                            const productError = []
                            Object.values(res?.data?.product[0]).forEach((obj) => {
                                obj.forEach(o => {
                                    productError.push(o)
                                })
                            })
                            this.orderError = productError
                        }
                        reject()
                    } else {
                        reject()
                    }
                } else {
                    this.setToastError(this.$t('listingLayout.noProductFound'))
                    this.$router.push('cart')
                }
            }))
        },
    },
    async mounted() {
        if (this.paymentGateway?.default) {
            this.paymentType = this.paymentGateway?.default
        } else if (parseInt(this.paymentGateway?.stripe) === this.status.PUBLIC) {
            this.paymentType = this.orderMethods.STRIPE
        }

        if (parseInt(this.paymentGateway?.paypal) === this.status.PUBLIC) {
            const recaptchaScript = document.createElement('script')
            recaptchaScript.setAttribute('src',
                `https://www.paypal.com/sdk/js?client-id=${this.paymentGateway?.paypal_key}&components=buttons,marks&disable-funding=paylater,card`
            )
            recaptchaScript.setAttribute('async', true)
            document.head.appendChild(recaptchaScript)
            recaptchaScript.addEventListener("load", () => {
                this.setLoaded()
                this.paypaLoaded = true
            });
        } else {
            this.paypaLoaded = true
        }

        if (parseInt(this.paymentGateway?.flutterwave) === this.status.PUBLIC) {

            const recaptchaScript = document.createElement('script')
            recaptchaScript.setAttribute('src',
                `https://checkout.flutterwave.com/v3.js`
            )
            recaptchaScript.setAttribute('async', true)
            document.head.appendChild(recaptchaScript)
            recaptchaScript.addEventListener("load", () => {
                this.flutterwaveLoaded = true
            });

        } else {
            this.flutterwaveLoaded = true
        }
    },
    async beforeMount() {
        if (!this.paymentGateway) {
            const data = await getRequest({params: "", api: "paymentGateway", lang: this.langCode});
            this.setPaymentGateway(data.data);
        }
    }
}
</script>
