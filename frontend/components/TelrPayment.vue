<template>
    <div>
        <transition name="fade" mode="out-in">
            <pop-over
                v-if="showPopup"
                :elem-id="'telr-popup'"
                class="popup-top-auto telr-pop-over"
                :layer="true"
                :outside-click-on="false"
                @close="closePopOver"
            >
                <template v-slot:heading>
                    <div class="p-3">
                        <h3 class="color-inherit semi-bold">{{ siteName }}</h3>
                        <h6 class="color-inherit opacity-8 mb-5">{{ $t('telrPayment.securePayment') }}</h6>
                        <h3 class="color-inherit">
                            <price-format :price="amount" />
                        </h3>
                    </div>
                </template>

                <template v-slot:content>
                    <div v-if="!paymentRef" class="spinner-wrapper flex layer-white" style="height: 400px;">
                        <spinner :radius="100" />
                    </div>
                    <div v-else class="iframe-container">
                        <iframe
                            id="givvo"
                            ref="telrIframe"
                            :src="iframeUrl"
                            frameborder="0"
                            width="100%"
                            height="270"
                            style="border: 0; width: 100%;"
                            sandbox="allow-forms allow-scripts allow-same-origin"
                        ></iframe>
                    </div>
                </template>
            </pop-over>
        </transition>
    </div>
</template>

<script>
import PopOver from './PopOver';
import AjaxButton from './AjaxButton';
import PriceFormat from './PriceFormat';
import Spinner from './Spinner';

export default {
    name: 'TelrPaymentIframe',
    components: { PopOver, AjaxButton, PriceFormat, Spinner },
    props: {
        siteName: { type: String, default: '' },
        amount: { type: Number, default: 0 },
        paymentRef: {type: String, required: true },
    },
    data() {
        return {
            showPopup: true,
        };
    },
    computed: {
        iframeUrl() {
            return `https://secure.telr.com/gateway/process_framed.html?o=${this.paymentRef}&mode=3`;
        }
    },
    methods: {
        closePopOver(eventName = 'closed', $event) {
            this.showPopup = false;
            this.$emit(eventName, $event);
        },
        adjustIframeHeight() {
            this.$nextTick(() => {
                const iframe = this.$refs.telrIframe;
                if (iframe) {
                    iframe.style.height = `${Math.max(window.innerHeight * 0.7, 300)}px`;
                }
            });
        },
    },
    mounted() {
        const iframe = this.$refs.telrIframe;
        let handled = false;
        iframe.addEventListener("load", () => {
            if (handled) return;
            try {
                const url = iframe.contentWindow.location.href;
                if (!url.includes("/payment/telr")) return;
                handled = true;

                const params = new URL(url).searchParams;
                const status = params.get("STATUS");       // '9' | '5' | '1' | '2'
                const orderRef = params.get("OrderRef") || this.paymentRef;

                if (status === "9") {
                    this.closePopOver("success", params);
                } else if (status === "1") {
                    this.closePopOver("closed", params);
                } else if (status === "2") {
                    this.closePopOver("error", params);
                } else if (status === "5") {
                    this.closePopOver("success", params);
                } else {
                    this.closePopOver("error", params); // unknown status -> error
                }

                window.location.assign(`${window.location.origin}/payment/telr?OrderRef=${encodeURIComponent(orderRef)}`
                );
            } catch (e) {
                console.log(e);
            }
        });

        window.addEventListener('resize', this.adjustIframeHeight);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.adjustIframeHeight);
    },
};
</script>

<style scoped>
.iframe-container {
    width: 100%;
    overflow: hidden;
    -webkit-overflow-scrolling: touch;
}

@media (max-width: 600px) {
    /* Prevent horizontal scroll on small screens */
    iframe {
        min-width: 100% !important;
    }
}
</style>