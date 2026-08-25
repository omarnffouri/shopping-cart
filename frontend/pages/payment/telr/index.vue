<template>
    <client-only>
        <div class="payment-status-page">
            <section class="status-shell">
                <!-- Loading State -->
                <div v-if="loading" class="status-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                    </div>
                    <div class="status-body">
                        <h3 class="status-title"> {{ $t('razorpayCallback.wait') }}... <spinner class="ml-10"/></h3>
                        <p class="status-subtitle">Please do not close this page while we process your payment.</p>
                    </div>
                </div>

                <!-- Success State -->
                <div v-else-if="status == 'completed'" class="status-card success-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                        <div class="status-badge success-badge">Payment Success</div>
                    </div>
                    <div class="status-body">
                        <div class="status-hero">
                            <div class="hero-icon success-icon">
                                <svg viewBox="0 0 140 140" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="successGreen" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#a6e6b3"/>
                                            <stop offset="1" stop-color="#4fb46f"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="70" cy="70" r="64" fill="rgba(79,180,111,0.18)"/>
                                    <rect x="28" y="54" width="84" height="50" rx="8" fill="#f7fff9" stroke="#cfe8d6" stroke-width="2"/>
                                    <rect x="28" y="64" width="84" height="10" fill="#e7f6ec"/>
                                    <circle cx="56" cy="92" r="6" fill="#cfe8d6"/>
                                    <circle cx="72" cy="92" r="6" fill="#cfe8d6"/>
                                    <circle cx="88" cy="92" r="6" fill="#cfe8d6"/>
                                    <circle cx="112" cy="28" r="22" fill="url(#successGreen)"/>
                                    <circle cx="112" cy="28" r="14" fill="#ecfff1"/>
                                    <path d="M104 28l6 6 14-14" stroke="#2e7d4f" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="hero-copy">
                                <h3 class="status-title">Payment Successful!</h3>
                                <p class="status-subtitle">{{ message || 'Your payment went through. We are preparing your order now.' }}</p>
                            </div>
                        </div>
                        <div class="status-actions">
                            <button class="cta-btn" v-if="order" @click="goToOrder">Go to order</button>
                            <button class="ghost-btn" @click="viewOrders">Back to Orders</button>
                        </div>
                        <div class="status-note">
                            We’ll send you updates as your flowers are arranged and dispatched.
                        </div>
                    </div>
                </div>

                <!-- Pending State -->
                <div v-else-if="status == 'pending'" class="status-card pending-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                        <div class="status-badge pending-badge">Payment Pending</div>
                    </div>
                    <div class="status-body">
                        <div class="status-hero">
                            <div class="hero-icon pending-icon">
                                <svg viewBox="0 0 140 140" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="pendingGray" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#d7d9dd"/>
                                            <stop offset="1" stop-color="#8f949c"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="70" cy="70" r="64" fill="rgba(143,148,156,0.18)"/>
                                    <circle cx="70" cy="70" r="36" fill="url(#pendingGray)"/>
                                    <circle cx="70" cy="70" r="26" fill="#f5f6f8"/>
                                    <path d="M70 54v18l12 8" stroke="#585e66" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="70" cy="70" r="22" fill="none" stroke="#6a7078" stroke-width="3"/>
                                </svg>
                            </div>
                            <div class="hero-copy">
                                <h3 class="status-title">Payment Pending</h3>
                                <p class="status-subtitle">{{ message || 'Your payment is still pending. This can take a few minutes.' }}</p>
                            </div>
                        </div>
                        <div class="status-actions">
                            <button class="cta-btn" v-if="order" @click="goToOrder">Go to order</button>
                            <button class="ghost-btn" @click="backToHome">Back to Home</button>
                        </div>
                        <div class="status-note">
                            We’ll update your order once the payment is confirmed.
                        </div>
                    </div>
                </div>

                <div v-else-if="status == 'expired'" class="status-card expired-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                        <div class="status-badge expired-badge">Payment Expired</div>
                    </div>
                    <div class="status-body">
                        <div class="status-hero">
                            <div class="hero-icon expired-icon">
                                <svg viewBox="0 0 140 140" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="receiptGray" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#e3e6eb"/>
                                            <stop offset="1" stop-color="#b4bcc8"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="70" cy="70" r="62" fill="rgba(148,156,168,0.18)"/>
                                    <g transform="translate(-8 0)">
                                        <path d="M46 26h48l6 8v70l-6-6-6 6-6-6-6 6-6-6-6 6-6-6-6 6V34z" fill="#ffffff" stroke="#c9d0da" stroke-width="3" stroke-linejoin="round"/>
                                        <rect x="56" y="42" width="32" height="6" rx="3" fill="url(#receiptGray)"/>
                                        <rect x="56" y="54" width="24" height="6" rx="3" fill="url(#receiptGray)"/>
                                        <rect x="56" y="66" width="30" height="6" rx="3" fill="url(#receiptGray)"/>
                                        <rect x="56" y="78" width="20" height="6" rx="3" fill="url(#receiptGray)"/>
                                        <circle cx="100" cy="46" r="18" fill="#f0f2f6" stroke="#c9d0da" stroke-width="3"/>
                                        <circle cx="100" cy="46" r="12" fill="#ffffff"/>
                                        <path d="M100 38v8l6 4" stroke="#6f7682" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="95" y="26" width="10" height="8" rx="4" fill="#c9d0da"/>
                                    </g>
                                </svg>
                            </div>
                            <div class="hero-copy">
                                <h3 class="status-title">Payment Session Expired</h3>
                                <p class="status-subtitle">{{ message || 'Your payment session timed out. Please try again.' }}</p>
                            </div>
                        </div>
                        <div class="status-actions">
                            <button class="cta-btn" v-if="order" @click="tryAgain">Try Again</button>
                            <button class="ghost-btn" @click="backToHome">Back to Home</button>
                        </div>
                        <div class="status-note">
                            If this keeps happening, contact support and mention your order number.
                        </div>
                    </div>
                </div>

                <!-- Failed State (Declined/Cancelled) -->
                <div v-else-if="['declined', 'cancelled'].includes(status)" class="status-card failed-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                        <div class="status-badge">Payment Failed</div>
                    </div>
                    <div class="status-body">
                        <div class="status-hero">
                            <div class="hero-icon">
                                <svg viewBox="0 0 140 140" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="failRed" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#f07a7a"/>
                                            <stop offset="1" stop-color="#c14a4a"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="70" cy="70" r="64" fill="rgba(193,74,74,0.16)"/>
                                    <rect x="28" y="54" width="84" height="50" rx="8" fill="#fff7f2" stroke="#e7c8c8" stroke-width="2"/>
                                    <rect x="28" y="64" width="84" height="10" fill="#f3d1d1"/>
                                    <circle cx="56" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="72" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="88" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="112" cy="28" r="22" fill="url(#failRed)"/>
                                    <circle cx="112" cy="28" r="14" fill="#ffe9e9"/>
                                    <path d="M104 20l16 16M120 20l-16 16" stroke="#7b1f1f" stroke-width="6" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="hero-copy">
                                <h3 class="status-title">Oops! Something went wrong.</h3>
                                <p class="status-subtitle">{{ message || 'We couldn’t process your payment. Please try again.' }}</p>
                            </div>
                        </div>
                        <div class="status-actions">
                            <button class="cta-btn" v-if="order" @click="tryAgain">Try Again</button>
                            <button class="ghost-btn" @click="viewOrders">View Orders</button>
                        </div>
                        <div class="status-note">
                            Need help? Contact support and mention your order number.
                        </div>
                    </div>
                </div>

                <!-- Fallback -->
                <div v-else class="status-card failed-card">
                    <div class="status-header">
                        <div class="status-logo"></div>
                        <div class="status-badge">Payment Failed</div>
                    </div>
                    <div class="status-body">
                        <div class="status-hero">
                            <div class="hero-icon">
                                <svg viewBox="0 0 140 140" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="failRed" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#f07a7a"/>
                                            <stop offset="1" stop-color="#c14a4a"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="70" cy="70" r="64" fill="rgba(193,74,74,0.16)"/>
                                    <rect x="28" y="54" width="84" height="50" rx="8" fill="#fff7f2" stroke="#e7c8c8" stroke-width="2"/>
                                    <rect x="28" y="64" width="84" height="10" fill="#f3d1d1"/>
                                    <circle cx="56" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="72" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="88" cy="92" r="6" fill="#e2b2b2"/>
                                    <circle cx="112" cy="28" r="22" fill="url(#failRed)"/>
                                    <circle cx="112" cy="28" r="14" fill="#ffe9e9"/>
                                    <path d="M104 20l16 16M120 20l-16 16" stroke="#7b1f1f" stroke-width="6" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="hero-copy">
                                <h3 class="status-title">Payment Failed</h3>
                                <p class="status-subtitle">{{ message || 'We couldn’t process your payment. Please try again.' }}</p>
                            </div>
                        </div>

                        <div class="status-actions">
                            <button class="cta-btn" v-if="order" @click="tryAgain">Try Again</button>
                            <button class="ghost-btn" @click="backToHome">Back to Home</button>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </client-only>
</template>

<script>
import global from '~/mixin/global.js'
import { useCommonStore } from "~/store/common.js";
import { storeToRefs } from "pinia";
import { useMetaData } from "~/composables/useMetaData.js";
import paymentHelper from '~/mixin/paymentHelper'
import { useUserStore } from "~/store/user";

definePageMeta({
    middleware: ['common-middleware'],
    layout: 'default',
})

export default {
    setup() {
        const commonStore = useCommonStore()
        const { setToastMessage, setToastError, getRequest, postRequest } = commonStore
        const { customScripts, site_setting } = storeToRefs(commonStore)

        const { pageMeta } = useMetaData();
        useHead(pageMeta(site_setting.value));
        const userStore = useUserStore()
        const { getUserToken } = userStore

        return { setToastMessage, setToastError, getRequest, customScripts, getUserToken, postRequest, site_setting }
    },
    mixins: [global, paymentHelper],
    data() {
        return {
            loading: true,
            message: '',
            order: null,
            status: null,
            payNow: false,
        }
    },
    async mounted() {
        try {
            this.status = this.$route.query.status
            const response = await this.paymentResponseTelr(this.$route.query);
            if (response?.status === 200) {
                const paymentState = response.data?.payment_state || 'expired'
                this.status = paymentState
                this.message = response.data.message
                this.order = response.data?.order

                if (paymentState === 'completed') {
                    this.setToastMessage(this.message)
                    setTimeout(() => {this.goToOrder()}, 5000)
                }

                if (['expired', 'pending', 'declined', 'cancelled'].includes(paymentState)) {
                    this.setToastError(this.message);
                }
            }
            if (response?.status === 201) {
                this.message = response.data.form.join(', ')
                this.setToastError(this.message)
            }
        } catch (error) {
            this.message = 'Something went wrong. Please try again.'
            this.setToastError(this.message)
        } finally {
            this.loading = false
        }
    },

    methods: {
        goToOrder() {
            if (this.order.id) this.$router.push(`/user/order/${this.order.id}`)
        },
        viewOrders() {
            this.$router.push('/user/orders')
        },
        tryAgain(){
            if (this.order.id) this.$router.push(`/user/abandoned_order/${this.order.id}?retry_payment=true`)
        },
        backToHome(){
            this.$router.push('/')
        }
    }
}
</script>

<style scoped>

.payment-status-page {
    /*min-height: 100vh;*/
    padding: 48px 20px 72px;
    background: #f8f9fb;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-shell {
    width: min(980px, 100%);
    position: relative;
}

.status-card {
    position: relative;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 22px 60px rgba(44, 30, 20, 0.18);
    padding: 32px clamp(20px, 3vw, 40px);
    overflow: hidden;
}

.status-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.status-logo {
    font-size: 20px;
    letter-spacing: 0.6px;
    color: #8c6f17;
}

.status-badge {
    background: #fce1df;
    color: #c14a4a;
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.status-body {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.status-title {
    font-size: clamp(22px, 3vw, 30px);
    color: #3b2f12;
    margin: 0;
}

.status-subtitle {
    font-size: 15px;
    color: #6b5a4f;
    line-height: 1.6;
    margin: 0;
}

.status-hero {
    display: grid;
    grid-template-columns: 1fr;
    gap: 22px;
    align-items: center;
    text-align: center;
}

.hero-icon {
    width: 140px;
    height: 140px;
    display: grid;
    place-items: center;
    margin: 0 auto;
    animation: float 4.5s ease-in-out infinite;
}

.hero-icon svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 8px 18px rgba(140, 111, 23, 0.25));
}

.status-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.status-actions button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 150px;
}

.cta-btn {
    background: #c8a330;
    color: #fff;
    border: none;
    padding: 12px 22px;
    border-radius: 999px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 10px 24px rgba(200, 163, 48, 0.35);
    text-align: center;
}

.cta-btn:hover {
    transform: translateY(-1px);
}

.ghost-btn {
    background: transparent;
    color: #c8a330;
    border: 1px solid #e6d59a;
    padding: 12px 20px;
    border-radius: 999px;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
}

.status-note {
    font-size: 13px;
    color: #9b6b6b;
}

.status-decor {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.petal {
    position: absolute;
    width: 30px;
    height: 14px;
    background: rgba(200, 163, 48, 0.3);
    border-radius: 999px;
    filter: blur(0.2px);
    animation: drift 6s ease-in-out infinite;
}

.petal-1 {
    top: 10%;
    right: 12%;
    transform: rotate(12deg);
}

.petal-2 {
    bottom: 18%;
    left: 8%;
    transform: rotate(-18deg);
    animation-delay: 1.4s;
}

.petal-3 {
    top: 48%;
    right: 35%;
    transform: rotate(28deg);
    animation-delay: 2.2s;
}

.success-card {
    border: 1px solid rgba(200, 163, 48, 0.35);
}

.failed-card {
    border: 1px solid rgba(193, 74, 74, 0.28);
}

.pending-card {
    border: 1px solid rgba(143, 148, 156, 0.4);
}

.pending-badge {
    background: #eef0f3;
    color: #59606a;
}

.pending-icon svg {
    filter: drop-shadow(0 8px 18px rgba(89, 96, 106, 0.25));
}

.expired-card {
    border: 1px solid rgba(185, 169, 138, 0.45);
}


.expired-badge {
    background: #f2eadc;
    color: #7a6a4b;
}

.expired-icon svg {
    filter: drop-shadow(0 8px 18px rgba(122, 106, 75, 0.25));
}

.success-badge {
    background: #e6f6ec;
    color: #2e7d4f;
}

.success-icon svg {
    filter: drop-shadow(0 8px 18px rgba(79, 180, 111, 0.25));
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes drift {
    0%, 100% {
        transform: translateY(0) rotate(12deg);
        opacity: 0.6;
    }
    50% {
        transform: translateY(-10px) rotate(-6deg);
        opacity: 1;
    }
}

@media (max-width: 720px) {
    .status-hero {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .status-actions {
        justify-content: center;
    }

    .status-header {
        flex-direction: column;
    }
}
</style>
