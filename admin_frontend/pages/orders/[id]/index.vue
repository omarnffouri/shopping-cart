<template>
  <div class="detail-width">

    <PartialsInvoice
        v-if="showPdf && storeData"
        :order="result"
        @downloaded="pdfDownloaded"
    />

    <div v-if="!showPdf">
      <error-formatter/>

      <div v-if="loading" class="spinner-wrapper">
        <spinner :radius="60" color="primary"/>
      </div>

      <p v-if="orderCancelled"
         class="info-msg danger-msg order-wrapper mb-15"
      >
        {{ $t('fSale.can', { type: $t('fSale.order') }) }}
      </p>
      <p v-if="deliveredCancelError"
         class="info-msg danger-msg order-wrapper mb-15"
      >
        {{ $t('fSale.deliveredCantCancel') }}
      </p>
      <p v-if="backStatusError"
         class="info-msg danger-msg order-wrapper mb-15"
      >
        {{ $t('fSale.backStatusNOtAllow') }}
      </p>

      <p v-if="refunded"
         class="info-msg success-msg order-wrapper mb-15"
      >
        {{ $t('fSale.ref', { type: $t('fSale.order') }) }}
      </p>

      <!-- Cancellation Message Modal -->
      <cancellation
          v-if="openCancellationMessage"
          @close="closeCancellation"
      />

      <!-- Admin Order Cancel Popup -->
      <order-cancel-popup
          v-if="showCancelPopup"
          :order-id="parseInt(id)"
          @close="closeCancelPopup"
          @click="onCancelConfirm"
      />

      <div v-if="result" class="order-wrapper tab-sidebar">
        <div class="title">
          <div class="dply-felx d-block-sm">
            <h4 class="mb-sm-10">
              {{ $t('fSale.oDetail') }}
            </h4>
            <template v-if="!isVendor">
              <!-- Show update status section ONLY when order is NOT cancelled -->
              <div v-if="!orderCancelled" class="dply-felx j-left f-wrap mlr--5 mtb-sm--5">
                <dropdown
                    class="mlr-5 mtb-sm-5"
                    :selected-key="result.status"
                    :options="orderStatusOptions"
                    @clicked="selectedStatus"
                />

                <ajax-button
                    name="save-edit"
                    class="primary-btn mlr-5 mtb-sm-5"
                    :text="$t('profile.us')"
                    :loading-text="$t('profile.updatn')"
                    :fetching-data="formSubmitting"
                    type="button"
                    @clicked="updateStatus"
                />
              </div>

              <!-- Show cancellation section ONLY when order IS cancelled -->
              <div v-if="orderCancelled && cancellationMessage"
                  class="dply-felx mlr--7-5 mtb-sm--5">
                <ajax-button
                    name="cancellation-find"
                    class="outline-btn mlr-7-5 mtb-sm-5"
                    :text="$t('fSale.cReason')"
                    type="button"
                    @clicked="toggleCancellation"
                />
                <ajax-button
                    v-if="!isCashOnDelivery && paymentDone && !refunded"
                    class="primary-btn mlr-7-5 mtb-sm-5"
                    type="button"
                    :loading-text="$t('fSale.refunding')"
                    :disabled="refunded"
                    :fetching-data="refunding"
                    :text="$t('fSale.refund')"
                    @clicked="refund"
                />
                <span
                    v-else-if="refunded"
                    class="success-badge mlr-7-5 mtb-sm-5"
                >
                  <i class="icon check-icon mr-5"></i>
                  {{ $t('fSale.refUp') }}
                </span>
              </div>
            </template>
          </div>
        </div>

        <!-- Rest of the template remains the same -->
        <div class="form-wrapper">
          <div class="dply-felx align-end block-sm mb-30 mb-sm-15 mlr--7-5">
              <p class="mx-w-400x mb-sm-10 mlr-7-5">
                  <span class="block"><b>Delivery Address: </b></span>
                  <b>{{ getDataFromObject(result, 'address.name', $t('prod.na')) }}</b>
                  <span v-if="getDataFromObject(result, 'address')" class="block">{{ generateAddress(getDataFromObject(result, 'address')) }}</span>
                  <span class="block">{{ $t('fSale.email') }}: {{ userEmail }}</span>
                  <span class="block">{{ $t('fSale.phone') }}: {{ getDataFromObject(result, 'address.phone', $t('prod.na')) }}</span>
                  <span class="block"><b>Delivery Date & Time: </b>{{ result.user_delivery_type.delivery_date }} - {{result.user_delivery_type.delivery_time || 'n/a' }}</span>
              </p>
            <ul class="mx-w-400x order-details lh-2 mlr-7-5">
              <li>
                <span>{{ $t('fSale.orderUp') }}</span>
                <span>#{{ result.order }}</span>
              </li>
              <li>
                <span>{{ $t('fSale.sStatus') }}</span>
                <span>{{ getDataFromObject(orderStatus[result.status], 'title') }}</span>
              </li>
              <!--payment method-->
              <li class="mtb-10">
                <span>{{ $t('fSale.pMethod') }}</span>
                <span v-if="isVendor || orderCancelled">{{ paymentTypes[result.order_method] }}</span>
                <span v-else>
                  {{ paymentTypes[result.order_method] }}
<!--                  <dropdown-->
<!--                      :selected-key="result.order_method"-->
<!--                      :options="paymentTypesDrop"-->
<!--                      :default-null="true"-->
<!--                      @clicked="orderMethodChanged"-->
<!--                  />-->
                </span>
              </li>
              <!--payment status-->
              <li class="mtb-10">
                <span>{{ $t('fSale.pStatus') }}</span>
                <span v-if="isVendor || orderCancelled">
                  {{ parseInt(result.payment_done) === status.PUBLIC ? $t('fSale.paid') : $t('fSale.unpaid') }}
                </span>
                <span v-else>
                  <dropdown
                      :selected-key="result.payment_done"
                      :options="paymentStatus"
                      @changed="selectedPaymentStatus"
                  />
                </span>
              </li>
              <li>
                <span>{{ $t('category.date') }}</span>
                <span>{{ result.created }}</span>
              </li>
              <li v-if="!isVendor">
                <span>{{ $t('fSale.amount') }}</span>
                <span>{{ priceFormatting(totalPrice) }}</span>
              </li>

              <template v-if="isBackPayment">
                <li>
                  <span>{{ $t('title.ti') }}</span>
                  <span>{{ result.trans_id }}</span>
                </li>
              </template>
            </ul>
          </div>

          <div class="table-wrapper">
            <table class="mn-w-600x no-bg">
              <thead>
              <tr class="lite-bold">
                <th>{{ $t('index.title') }}</th>
                <th>{{ $t('fSale.shipTo') }}</th>
                <th>{{ $t('fSale.qty') }}</th>
                <th>{{ $t('fSale.bOffer') }}({{ currencyIcon }})</th>
                <th>{{ $t('brand.price') }}({{ currencyIcon }})</th>
                <th>{{ $t('fSale.total') }}({{ currencyIcon }})</th>
              </tr>
              </thead>
              <tbody>
              <PartialsOrderedProduct
                  v-for="(value, index) in result.ordered_products"
                  :key="index"
                  :ordered-product="value"
              />
              </tbody>
            </table>
          </div>

          <div v-if="!isVendor" class="dply-felx j-right mt-20 mt-sm-15">
            <ul class="mx-w-400x order-details order-price">
              <li>
                <span>{{ $t('fSale.sTotal') }}</span>
                <span>{{ priceFormatting(subtotalPrice) }}</span>
              </li>
              <li>
                <span>{{ $t('fSale.sCost') }}</span>
                <span v-if="isFreeShipping">{{ $t('ship.fre') }}</span>
                <span v-else>{{ priceFormatting(shippingPrice) }}</span>
              </li>
              <li v-if="bundleOffer">
                <span>{{ $t('fSale.bOffer') }}</span>
                <span>{{ priceFormatting(bundleOffer) }}</span>
              </li>
              <li v-if="voucherPrice">
                <span>{{ $t('fSale.voucher') }}</span>
                <span>{{ priceFormatting(voucherPrice) }}</span>
              </li>
              <li v-if="taxPrice">
                <span>{{ $t('fSale.tax') }}</span>
                <span>{{ priceFormatting(taxPrice) }}</span>
              </li>
              <li>
                <span>{{ $t('fSale.total') }}</span>
                <span>{{ priceFormatting(totalPrice) }}</span>
              </li>
            </ul>
          </div>

          <div class="dply-felx j-right mt-20 mt-sm-15">
            <button class="plr-20 dply-felx outline-btn" @click="generatePdf">
              <i class="icon print-icon mr-10"/>
              {{ $t('setting.pi') }}
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import {useSettingStore} from '~/store/setting';
import {useResourceStore} from '~/store/resource';
import {useCommonStore} from '~/store/common';
import {useAdminStore} from '~/store/admin';
import {onMounted} from "vue";
import {useUtils} from "~/composables/useUtils";
import {useConstants} from "~/composables/useConstants";
const {t} = useI18n();

import OrderCancelPopup from '~/components/partials/OrderCancelPopup';
import Cancellation from '~/components/partials/Cancellation';
import {useUiStore} from "~/store/ui.js";

definePageMeta({
  middleware: ['common-middleware', 'auth'],
  layout: 'default',
});

const resourceStore = useResourceStore();
const {setCountryList} = resourceStore;
const {countryList} = storeToRefs(resourceStore);

const uiStore = useUiStore();
const commonStore = useCommonStore();
const {setRequest, getById} = commonStore;

const settingStore = useSettingStore();
const {getSetting} = settingStore;
const {setting, storeData} = storeToRefs(settingStore);

const adminStore = useAdminStore();
const {isVendor} = storeToRefs(adminStore);

const showPdf = ref(false);
const showCancelPopup = ref(false);
const statusChanged = ref(false);
const refunding = ref(false);
const formSubmitting = ref(false);
const openCancellationMessage = ref(false);
const result = ref(null);
const loading = ref(false);
const deliveredCancelError = ref(false);
const backStatusError = ref(false);
let Timer = null;

const showDeliveredCancelError = () => {
  deliveredCancelError.value = true;

  if (Timer) clearTimeout(Timer);

  Timer = setTimeout(async () => {
    deliveredCancelError.value = false;
    Timer = null;
    window.location.reload();
  }, 3000);
};

const showBackStatusError = () => {
  backStatusError.value = true;

  if (Timer) clearTimeout(Timer);

  Timer = setTimeout(() => {
    backStatusError.value = false;
    Timer = null;
    window.location.reload();
  }, 3000);

};

const generatePdf = () => {
  showPdf.value = true;
};

const pdfDownloaded = () => {
  showPdf.value = false;
};

const toggleCancellation = () => {
  openCancellationMessage.value = !openCancellationMessage.value;
}

const closeCancellation = () => {
  openCancellationMessage.value = false;
};

const closeCancelPopup = () => {
  showCancelPopup.value = false;
};

const {
  orderStatusIn, status, orderMethodsIn,
  orderStatus, paymentStatus, paymentTypes, paymentTypesDrop
} = useConstants();
const {getTimeZone, getDataFromObject, priceFormatting} = useUtils();
const timeZone = getTimeZone();

const isFreeShipping = computed(() => {
  return !(parseFloat(shippingPrice.value) > 0);
});

const userEmail = computed(() => {
  return result.value?.user?.email ?? result.value?.guest_user?.email ?? t('prod.na');
});

const refunded = computed(() => {
  return parseInt(result.value?.cancellation?.refunded) === status.PUBLIC || false;
});

const paymentDone = computed(() => {
  return parseInt(result.value?.payment_done) === status.PUBLIC;
});

const isBackPayment = computed(() => {
  return parseInt(result.value?.order_method) === orderMethodsIn.BANK;
});

const isCashOnDelivery = computed(() => {
  return parseInt(result.value?.order_method) === orderMethodsIn.CASH_ON_DELIVERY;
});

const cancellationMessage = computed(() => {
  return result.value?.cancellation || null;
});

const orderCancelled = computed(() => {
  if (!result.value) return false;

  const orderStatus = result.value.status;

  if (orderStatus === undefined || orderStatus === null || orderStatus === '') {
    return false;
  }

  const statusValue = parseInt(orderStatus);

  if (isNaN(statusValue)) {
    return false;
  }

  return statusValue === orderStatusIn.CANCELED;
});


const totalPrice = computed(() => {
  return result.value?.calculated.total_price;
});

const voucherPrice = computed(() => {
  return result.value?.calculated.voucher_price;
});

const bundleOffer = computed(() => {
  return result.value?.calculated.bundle_offer;
});

const shippingPrice = computed(() => {
  return result.value?.calculated.shipping_price;
});

const taxPrice = computed(() => {
  return result.value?.calculated.tax;
});

const subtotalPrice = computed(() => {
  return result.value?.calculated.subtotal;
});

const orderStatusOptions = computed(() => {
  const options = {...orderStatus};
  if (!orderCancelled.value && parseInt(result.value?.status) === orderStatusIn.PENDING) {
    options[orderStatusIn.CANCELED] = {title: orderStatus[orderStatusIn.CANCELED].title};
  }
  return options;
});

const route = useRoute();

const id = computed(() => {
  return route?.params?.id;
});

const currencyIcon = computed(() => {
  return setting.value?.currency_icon || '$';
});

const isDelivered = computed(() => {
  return parseInt(result.value?.status) === orderStatusIn.DELIVERED;
});

const orderMethodChanged = async (evt) => {
  loading.value = true;
  const data = await setRequest({
    params: {
      order_method: evt.key,
      id: id.value
    },
    api: 'paymentMethod'
  });

  if (data?.order_method) {
    result.value.order_method = data.order_method;
  }
  loading.value = false;
};

const refund = async () => {
  refunding.value = true;
  const data = await getById({id: result.value?.cancellation?.id, params: {}, api: 'setOrderRefund'});
  if (data.status === 201){
      uiStore.setToastError(data.message, '')
  }
  if (data) {
    result.value = {...result.value, ...{cancellation: {...result.value.cancellation, ...{refunded: data.refunded}}}};
  }
  refunding.value = false;
};

const selectedPaymentStatus = async (evt) => {
  loading.value = true;
  const data = await setRequest({
    params: {
      payment_done: evt.key,
      id: id.value
    },
    api: 'paymentStatus'
  });

  if (data?.payment_done) {
    result.value.payment_done = data.payment_done;
  }
  loading.value = false;
};

const selectedStatus = (evt) => {
  const next = parseInt(evt.key);
  const current = parseInt(result.value?.status);

  if (isDelivered.value && next === orderStatusIn.CANCELED) {
    statusChanged.value = false;
    showDeliveredCancelError();
    return;
  }

  if (next === 6) {
    showCancelPopup.value = true;
    return;
  }
  if (next < current) {
    statusChanged.value = false;
    showBackStatusError();
    return;
  }
  if (next === current) {
    statusChanged.value = false;
    return;
  }

  statusChanged.value = true;
  result.value = {...result.value, ...{status: evt.key}};
};

const cancellationDetails = ref({
  message: '',
  refundable: false
});

const onCancelConfirm = (payload) => {
  if (isDelivered.value) {
    showCancelPopup.value = false;
    statusChanged.value = false;
    return;
  }
  cancellationDetails.value = payload;
  showCancelPopup.value = false;
  statusChanged.value = true;
  result.value = {...result.value, ...{status: orderStatusIn.CANCELED}};
  updateStatus()
};

const updateStatus = async () => {
  if (!statusChanged.value) {
    return false;
  }
  statusChanged.value = false;
  formSubmitting.value = true;

  const params = {
    status: result.value.status,
    id: id.value
  };

  if (parseInt(result.value.status) === orderStatusIn.CANCELED) {
    params.message = cancellationDetails.value.message;
    params.refundable = cancellationDetails.value.refundable ? 1 : 0;
  }

  await setRequest({
    params: params,
    api: 'updateOrderStatus'
  });

  formSubmitting.value = false;

  if (orderStatusIn.DELIVERED === parseInt(result.value.status)) {
    await getById({
      id: id.value,
      params: {},
      api: 'sendStatusUpdateEmail'
    });
  }

  await fetchingData();
};

const generateAddress = (obj) => {
  if (!obj) {
    return t('fSale.noAddr');
  }
  let addArr = []
  addArr.push(obj?.address_1)
  if (obj?.address_2) {
    addArr.push(obj?.address_2)
  }
  addArr.push(obj?.city + '-' + obj?.zip)

  if (countryList.value[obj?.country]) {
    const country = countryList.value[obj?.country]

    if (country.states[obj?.state]) {
      addArr.push(country.states[obj.state]?.name)
    }
    addArr.push(country?.name)
  }
  return addArr.join(', ')
};

const fetchingData = async () => {
  loading.value = true
  result.value = Object.assign({}, await getById({
    id: id.value, params: {time_zone: timeZone}, api: 'getOrder'
  }))
  loading.value = false;
};

onBeforeUnmount(() => {
  if (Timer) clearTimeout(Timer);
});

onMounted(async () => {
  if (!countryList.value) {
    loading.value = true
    const data = await getById({
      params: null,
      id: 'countries',
      api: 'resource'
    });
    setCountryList(data);
    loading.value = false;
  }

  if (!storeData.value) {
    loading.value = true;
    await getSetting();
    loading.value = false;
  }
  await fetchingData();
})
</script>

<style scoped>
.success-badge {
  display: inline-flex;
  align-items: center;
  background: #d4edda;
  color: #155724;
  padding: 8px 15px;
  border-radius: 4px;
  font-weight: 500;
  border: 1px solid #c3e6cb;
}
</style>