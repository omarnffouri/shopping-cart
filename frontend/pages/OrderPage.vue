<template>
  <client-only>
    <account-layout
        active-route="orders"
        class="mb-20 mb-sm-15 order-detail-layout"
    >
      <template #rightArea>
        <bank-popup
            v-if="verifyPayment"
            :order="ordered"
            @close="closeBankVerify"
        />

        <div class="spinner-wrapper flex" v-if="fetchingOrderData">
          <spinner :radius="100" />
        </div>

        <p v-if="orderCancelled" class="info-msg danger-msg order-wrapper mb-15">
          {{ $t('order.orderCancelled') }}
          <span v-if="ordered.cancellation" class="block me-2 mt-5 f-9 semi-bold">
             &thinsp; {{ $t('order.reason') }}: {{ ordered.cancellation.message }}
          </span>
        </p>

        <p v-if="refunded" class="info-msg success-msg order-wrapper mb-15">
          {{ $t('order.orderRefunded') }}
        </p>

        <div v-if="Object.keys(ordered).length" class="card">
          <div class="p-20 p-sm-15 pt-20">
            <div class="flex f-reverse sided block-md mb-30 mb-sm-15">
              <ul class="mx-w-400x order-details mb-md-15">
                <li>
                  <span>
                    {{ $t('order.order') }}
                  </span>
                  <span>#{{ ordered.order }}</span>
                </li>
                <li>
                  <span>
                    {{ $t('trackOrder.orderStatus2') }}
                  </span>
                  <span>{{ orderStatus[ordered?.status]?.title }}</span>
                </li>
                <li>
                  <span>
                    {{ $t('order.orderMethod') }}
                  </span>
                  <span>{{ orderMethodsIn[ordered.order_method] }}</span>
                </li>
                <li>
                  <span>
                    {{ $t('order.orderDate') }}
                  </span>
                  <span>{{ ordered.created }}</span>
                </li>
                <li>
                  <span>
                    {{ $t('order.orderAmount') }}
                  </span>
                  <span>
                    <price-format
                        :price="totalPrice"
                    />
                  </span>
                </li>
                <li v-if="parseInt(ordered.order_method) === orderMethods.BANK">
                  <span>
                    {{ $t('date.ti') }}
                  </span>
                  <span>{{ ordered.trans_id }}</span>
                </li>
                <li>
                  <span>
                    {{ $t('order.paymentStatus') }}
                  </span>
                  <span>
                    {{ paymentStatus[ordered.payment_done] }}
                    <pay-button
                        ref="payNowButton"
                        v-if="!orderCancelled && parseInt(ordered.payment_done) === paymentStatusIn.UNPAID
                        && parseInt(ordered.order_method) !== orderMethods.CASH_ON_DELIVERY"
                        class="block mt-10"
                        :order="ordered"
                    />

                    <button
                        v-if="!orderCancelled && parseInt(ordered.payment_done) === paymentStatusIn.UNPAID
                        && parseInt(ordered.order_method) === orderMethods.BANK"
                        @click.prevent="toggleVerify"
                        class="link mt-15 bold f-9"
                    >
                      {{ $t('date.vp') }}
                    </button>
                  </span>
                </li>

              </ul>
              <p class="mx-w-400x lh-2 mr-15">
                <span class="block"><b>Delivery Address: </b></span>
                <span class="block"><b>{{ dataFromObject(ordered.address, 'name') }}</b></span>
                <span class="block">{{ generateAddress(ordered.address) }}</span>
                <span v-if="orderEmail" class="block">
                    {{ $t('addressPopup.email') }}: {{ orderEmail }}
                    </span>
                <span class="block">
                        {{ $t('addressPopup.phone') }}: {{ dataFromObject(ordered.address, 'phone', 'n/a')}}
                    </span>
                <span class="block"><b>Delivery Date & Time:</b> {{ ordered.user_delivery_type.delivery_date }} - {{ordered.user_delivery_type.delivery_time || 'n/a'}}</span>
              </p>
            </div>

            <div class="mb-15">
              <ordered-status
                  :status-of-order="ordered.status"
                  :cancelReason="ordered.cancellation?.message"
              />
            </div>
            <div class="flow-auto mtb-15">
              <table class="mn-w-600x no-bg w-100 mtb-0">
                <thead>
                <tr class="lite-bold">
                  <th>{{ $t('order.image') }}</th>
                  <th>{{ $t('orderCancelPopup.title') }}</th>
                  <th>{{ $t('order.shipTo') }}</th>
                  <th>{{ $t('detailRight.quantity') }}</th>
                  <th>{{ $t('cartProductTile.bundleOffer') }}</th>
                  <th>{{ $t('detailRight.price') }}</th>
                  <th>{{ $t('checkoutRight.total') }}</th>
                </tr>
                </thead>
                <tbody>
                <ordered-product
                    v-for="(value, index) in ordered.ordered_products"
                    :key="index"
                    :ordered="ordered"
                    :cart="value"
                    @rate-now="rateProductId = $event"
                    @show-note="openNoteDialog"
                />
                </tbody>
              </table>
            </div>

            <div class="flex right no-space">
              <ul class="mx-w-400x order-details order-price">
                <li>
                  <span>
                    {{ $t('order.subtotal') }}
                  </span>
                  <span class="semi-bold">
                    <price-format
                        :price="subtotalPrice"
                    />
                  </span>
                </li>
                <li>
                  <span>
                    {{ $t('order.shippingCost') }}
                  </span>
                  <span class="semi-bold">
                    <span
                        v-if="isFreeShipping"
                        class="color-free">
                      {{ $t('invent.fre') }}
                    </span>
                    <price-format
                        v-else
                        :price="shippingPrice"
                    />
                  </span>
                </li>
                <li v-if="bundleOffer">
                  <span>
                    {{ $t('cartProductTile.bundleOffer') }}
                  </span>
                  <span class="semi-bold">
                    <price-format
                        :price="bundleOffer"
                    />
                  </span>
                </li>
                <li v-if="voucherPrice">
                  <span>
                    {{ $t('checkoutRight.voucher') }}
                  </span>
                  <span class="semi-bold">
                    <price-format
                        :price="voucherPrice"
                    />
                  </span>
                </li>
                <li v-if="taxPrice">
                  <span>
                    {{ $t('cart.tax') }}
                  </span>
                  <span class="semi-bold">
                    <price-format
                        :price="taxPrice"
                    />
                  </span>
                </li>
                <li class="mb-0">
                  <span>
                    {{ $t('checkoutRight.total') }}
                  </span>
                  <span class="semi-bold f-11">
                    <price-format
                        :price="totalPrice"
                    />
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>


        <transition name="fade" mode="out-in">
          <rate-popup
              v-if="rateProductId"
              :order-id="orderId"
              :product-id="rateProductId"
              @close="rateProductId = 0"
          />
        </transition>

        <transition name="fade" mode="out-in">
          <div
              v-if="activeNote"
              class="note-dialog-overlay"
              @click.self="closeNoteDialog"
          >
            <div class="note-dialog">
              <button
                  type="button"
                  class="note-dialog-close"
                  aria-label="Close"
                  @click="closeNoteDialog"
              >
                ×
              </button>
              <h5 class="mb-10">Product notes</h5>
              <div v-if="activeNoteImage" class="note-dialog-image mb-10">
                <img :src="activeNoteImage" alt="Product note image" />
              </div>
              <div class="note-dialog-message-wrap">
                <p class="note-dialog-message-label">Message:</p>
                <p v-if="activeNote.message" class="note-dialog-message">
                  {{ activeNote.message }}
                </p>
                <p v-else class="color-lite">No message.</p>
              </div>
            </div>
          </div>
        </transition>
      </template>
    </account-layout>
  </client-only>
</template>

<script setup>
import { useCommonStore } from "~/store/common";
import { useLanguageStore } from "~/store/language";
import { useResourceStore } from "~/store/resource";
import { useUserStore } from "~/store/user";
import { useOrderStore } from "~/store/order";
import { useAuthStore } from "~/store/auth";
import { useMetaData } from "~/composables/useMetaData";
import { useConstants } from "~/composables/useConstants";
import { useUtils } from "~/composables/useUtils";
import { storeToRefs } from "pinia";
import { computed, onBeforeMount, onMounted, ref } from "vue";

const props = defineProps({
  mode: { type: String, default: "order" }
});

const route = useRoute();

const userStore = useUserStore();
const { getUserToken } = userStore;

const orderStore = useOrderStore();
const { ordered } = storeToRefs(orderStore);
const { updateOrderData } = orderStore;

const resourceStore = useResourceStore();
const { countryList, phoneList } = storeToRefs(resourceStore);
const { setCountryList, setPhoneList } = resourceStore;

const commonStore = useCommonStore();
const { setting, paymentGateway, site_setting } = storeToRefs(commonStore);
const { unAuthGet, postRequest, setPaymentGateway, getRequest } = commonStore;

const { pageMeta } = useMetaData();
useHead(pageMeta(site_setting.value));

const languageStore = useLanguageStore();
const { langCode } = storeToRefs(languageStore);

const authStore = useAuthStore();
const { authenticated } = storeToRefs(authStore);

const isLoggedIn = computed(() => authenticated.value || false);

const payNowButton = ref();
const verifyPayment = ref(false);
const fetchingOrderData = ref(false);
const rateProductId = ref(0);
const activeNote = ref(null);

const { t } = useI18n();
const {
  status, orderStatus, orderMethodsIn, orderMethods,
  paymentStatus, paymentStatusIn
} = useConstants();

const { getTimeZone, dataFromObject, getImageURL } = useUtils();

const toggleVerify = () => (verifyPayment.value = !verifyPayment.value);

onBeforeMount(async () => {
  if (!setting.value?.guest_checkout) {
    if (!authenticated.value) return navigateTo("/login");
  }
  if (!paymentGateway.value) {
    const data = await getRequest({ params: "", api: "paymentGateway", lang: langCode.value });
    setPaymentGateway(data.data);
  }
});

const refunded = computed(() => parseInt(ordered.value?.cancellation?.refunded) === status.PUBLIC || false);
const orderCancelled = computed(() => parseInt(ordered.value.cancelled) === status.PUBLIC);
const isFreeShipping = computed(() => !(parseFloat(shippingPrice.value) > 0));
const orderEmail = computed(() => ordered.value.address.email || ordered.value.user?.email || null);
const totalPrice = computed(() => ordered.value.calculated.total_price);
const voucherPrice = computed(() => ordered.value.calculated.voucher_price);
const bundleOffer = computed(() => ordered.value.calculated.bundle_offer);
const shippingPrice = computed(() => ordered.value.calculated.shipping_price);
const taxPrice = computed(() => ordered.value.calculated.tax);
const subtotalPrice = computed(() => ordered.value.calculated.subtotal);

const orderId = computed(() => parseInt(route.params.id));

const closeBankVerify = (evt) => {
  verifyPayment.value = false;
  updateOrderData({ trans_id: evt });
};

const apiName = computed(() => ("orderByUser"));

const fetchingData = async () => {
  fetchingOrderData.value = true;

  const data = await postRequest({
    api: apiName.value,
    params: {
      order_id: orderId.value,
      user_token: await getUserToken(),
      time_zone: getTimeZone()
    },
    lang: langCode.value
  });

  if (data?.status === 403 && !isLoggedIn.value) return navigateTo("/login");
  if (data?.status === 200) updateOrderData(data.data);

  fetchingOrderData.value = false;
};

onMounted(async () => {
  if (!countryList.value || !phoneList.value) {
    fetchingOrderData.value = true;

    const { data } = await unAuthGet({
      params: "",
      lang: langCode.value,
      api: "countriesPhones"
    });

    setCountryList(data?.countries);
    setPhoneList(data?.phones);
    fetchingOrderData.value = false;
  }

  await fetchingData();

    if(route.query?.retry_payment && !orderCancelled.value && parseInt(ordered.value.payment_done) === paymentStatusIn.UNPAID
        && parseInt(ordered.value.order_method) !== orderMethods.CASH_ON_DELIVERY){
        await nextTick()

        if (payNowButton.value) {
            payNowButton.value.openPayNow()
        }
    }
});

const generateAddress = (obj) => {
  if (!obj) {
    return ''
  }
  let addArr = []
  addArr.push(obj?.address_1 || '')
  if (obj?.address_2) {
    addArr.push(obj?.address_2)
  }
  addArr.push(obj?.city + '-' + obj?.zip)

  if (countryList.value[obj?.country]) {
    const country = countryList.value[obj?.country]

    if (country.states[obj?.state]) {
      addArr.push(country?.states[obj?.state]?.name)
    }
    addArr.push(country?.name)
  }
  ordered.value['formatted_address'] = addArr.join(', ')
  return ordered.value['formatted_address']
};

const activeNoteImage = computed(() => {
  const image = activeNote.value?.image || '';
  if (!image) return '';
  if (image.startsWith('http://') || image.startsWith('https://') || image.startsWith('/')) {
    return image;
  }
  return getImageURL(image);
});

const openNoteDialog = (note) => {
  activeNote.value = note || null;
};

const closeNoteDialog = () => {
  activeNote.value = null;
};

</script>
