<template>
    <div class="container-fluid mtb-20 mtb-sm-15">

        <div class="product-detail checkout-detail">
            <div class="detail-left p-20 p-sm-15 pb-10 area mr-20 mr-sm mb-sm-15 ">
                <h5 class="mb-20">{{ $t('date.dad') }}</h5>
                <div class="mt-30">
                    <h5 class="mb-10">{{ $t('date.os') }}</h5>
                    <cart-list
                        :error-from-api="errorFromApi"
                        :cart-products="checkedProduct"
                        :cart-shipping="cartShipping"
                        :checked="checked"
                        :current-addresses="currentAddresses"
                        :is-shipping="true"
                        :address="selectedCurrentAddress"
                        @shipping-changed="shippingChanged"
                        @cart-changed="cartChanged"
                        @current-shipping="currentShipping"
                    />
                </div>

                <div class="mt-30 checkout-address-layout">
                    <transition name="fade" mode="out-in">
                        <div class="spinner-wrapper flex layer-white" v-if="addressLoading">
                            <spinner :radius="100"/>
                        </div>
                    </transition>
                    <div class="address-wrapper">
                        <user-address
                            ref="shippingAddress"
                            :has-radio="true"
                            @editing="editAddress"
                            @selected-address="setSelected"
                            @add-address="setAddressPopup"
                        />
                    </div>
                </div>
                <form v-if="showAddressForm" class="address-form checkout-address-form mt-15" @submit.prevent="savingAddressData">
                    <div class="flex gap-15">
                        <div v-if="false" class="input-wrap flex-1">
                            <label>
                                {{ $t('addressPopup.country') }}
                            </label>
                            <dropdown
                                :selected-key="addressData.country"
                                :options="countryList"
                                :position-fixed="false"
                                key-name="name"
                                :searching="true"
                                @clicked="selectCountryNew"
                            />
                        </div>
                        <div v-if="Object.keys(states).length" class="input-wrap flex-1">
                            <label>
                                {{ $t('addressPopup.state') }}
                            </label>
                            <dropdown
                                :selected-key="addressData.state"
                                :position-fixed="false"
                                :options="states"
                                key-name="name"
                                :searching="true"
                                @clicked="selectState"
                            />
                        </div>
                    </div>
                    <p v-if="!canFillAddressDetails" class="info-msg mt-10 mb-10">
                        Please select state first.
                    </p>

                    <template v-else>
                        <div class="flex gap-15">
                            <div class="input-wrap flex-1" :class="{invalid: !emailValid && hasAddressErrors}">
                                <label>
                                    {{ $t('addressPopup.email') }}
                                </label>
                                <div class="icon-input">
                                    <i class="icon email-icon"/>
                                    <input type="text" :placeholder="$t('contact.your', { type: $t('contact.email') })"
                                           v-model.trim="addressData.email">
                                </div>
                                <span class="error" v-if="!addressData.email && hasAddressErrors">
                                      {{ $t('addressPopup.isRequired', {type: $t('addressPopup.email')}) }}
                                    </span>
                                <span class="error" v-else-if="invalidEmail && hasAddressErrors">
                                      {{ $t('contact.invalidEmail') }}
                                    </span>
                            </div>
                        </div>

                        <div class="flex block-xxs gap-15">
                            <div class="input-wrap flex-1"
                                 :class="{invalid: !addressData.name && hasAddressErrors}">
                                <label>
                                    {{ $t('addressPopup.name') }}
                                </label>
                                <input type="text" v-model="addressData.name"/>
                                <span class="error" v-if="!addressData.name && hasAddressErrors">
                                      {{ $t('addressPopup.isRequired', {type: $t('addressPopup.name')}) }}
                                    </span>
                            </div>
                            <div v-if="phoneList" class="input-wrap flex-1"
                                 :class="{invalid: !numberValid && hasAddressErrors}">
                                <label>
                                    {{ $t('addressPopup.phone') }}
                                </label>
                                <div class="input-text">
                                      <span>
                                        {{ phoneList[addressData.country] }}
                                      </span>
                                    <input type="text" v-model="addressData.phone"/>
                                </div>
                                <span class="error" v-if="!addressData.phone && hasAddressErrors">
                                      {{ $t('addressPopup.isRequired', {type: $t('addressPopup.phone')}) }}
                                    </span>
                                <span class="error" v-else-if="invalidNumber && hasAddressErrors">
                                      {{ $t('invent.in') }}
                                    </span>
                            </div>
                        </div>

                        <div class="input-wrap" :class="{invalid: !addressData.address_1 && hasAddressErrors}">
                            <label>
                                {{ $t('addressPopup.address') }}
                            </label>
                            <input class="mb-10" type="text" v-model="addressData.address_1"
                                   :placeholder="$t('addressPopup.addressPlaceholder')"/>
                            <input type="text" v-model="addressData.address_2"
                                   :placeholder="$t('addressPopup.address2Placeholder')"/>
                            <span class="error" v-if="!addressData.address_1 && hasAddressErrors">
                                    {{ $t('addressPopup.isRequired', {type: $t('addressPopup.address')}) }}
                                </span>
                        </div>

                        <div class="flex block-xxs gap-15 sided">
                            <div v-if="false" class="input-wrap"
                                 :class="{invalid: !addressData.city && hasAddressErrors}">
                                <label>{{ $t('addressPopup.city') }}</label>
                                <input type="text" v-model="addressData.city"/>
                                <span class="error" v-if="!addressData.city && hasAddressErrors">
                                      {{ $t('addressPopup.isRequired', {type: $t('addressPopup.city')}) }}
                                    </span>
                            </div>

                            <div v-if="false" class="input-wrap"
                                 :class="{invalid: !addressData.zip && hasAddressErrors}">
                                <label>
                                    {{ $t('addressPopup.zipCode') }}
                                </label>
                                <input type="text" v-model="addressData.zip"/>
                                <span class="error" v-if="!addressData.zip && hasAddressErrors">
                                      {{ $t('addressPopup.isRequired', {type: $t('addressPopup.zipCode')}) }}
                                    </span>
                            </div>
                        </div>
                        <div class="input-wrap mb-0">
                            <label>
                                {{ $t('shipping.instruction') }}
                            </label>
                            <textarea v-model="addressData.delivery_instruction"/>
                        </div>
                        <div class="flex sided mlr-0 gap-10 mt-15">
                            <button
                                aria-label="submit"
                                class="outline-btn plr-30 plr-sm-15"
                                @click.prevent="clearData">
                                {{ $t('addressPopup.cancel') }}
                            </button>
                            <ajax-button
                                class="primary-btn plr-30 plr-sm-15"
                                :fetching-data="submittingAddressData"
                                :loading-text="$t('addressPopup.saving')"
                                :text=" $t('addressPopup.thisAddress', {type: editing > 0 ? $t('addressPopup.update') : $t('addressPopup.save')})"
                            />
                        </div>
                    </template>
                </form>

                <div v-if="selectedCurrentAddress" class="delivery-schedule mb-15 mt-15">
                    <button
                        type="button"
                        class="delivery-picker-trigger"
                        @click.prevent="openDatePicker"
                    >
                        <span class="flex align-center gap-10">
                            <i class="icon order-icon"></i>
                            <span>{{ deliveryDateLabel }}</span>
                        </span>
                        <i class="icon arrow-right black dimen-16x"></i>
                    </button>

                    <button
                        type="button"
                        class="delivery-picker-trigger mt-10"
                        :disabled="!deliveryDate"
                        @click.prevent="openTimeSlotPicker"
                    >
                        <span class="flex align-center gap-10">
                            <i class="icon shipping-icon"></i>
                            <span>{{ deliveryTimeSlotLabel }}</span>
                        </span>
                        <i class="icon arrow-right black dimen-16x"></i>
                    </button>

                    <p v-if="selectedDeliverySummary" class="success-msg mt-10">
                        {{ selectedDeliverySummary }}
                    </p>
                </div>
                <p v-else-if="currentAddresses && currentAddresses.length" class="info-msg mt-15 mb-15">
                    Please select an address first to choose delivery date and shipping method.
                </p>

                <pop-over
                    v-if="showDatePicker"
                    :title="$t('checkout.selectDeliveryDate')"
                    elem-id="checkout-delivery-date-pop-over"
                    class="delivery-date-popup"
                    :layer="true"
                    @close="closeDatePicker"
                >
                    <template #content>
                        <div class="delivery-calendar">
                            <div class="calendar-nav">
                                <button type="button" class="calendar-nav-btn" @click.prevent="goToPrevMonth">
                                    <i class="icon arrow-left black"></i>
                                </button>
                                <h5>{{ calendarMonthLabel }}</h5>
                                <button type="button" class="calendar-nav-btn" @click.prevent="goToNextMonth">
                                    <i class="icon arrow-right black"></i>
                                </button>
                            </div>

                            <div class="calendar-grid-head">
                                <span v-for="dayName in weekDayLabels" :key="dayName">{{ dayName }}</span>
                            </div>

                            <div class="calendar-grid-body">
                                <button
                                    v-for="day in calendarDays"
                                    :key="day.iso"
                                    type="button"
                                    class="calendar-day"
                                    :class="{
                                      'empty': !day.currentMonth,
                                      'active': day.iso === deliveryDate,
                                      'disabled': day.disabled
                                    }"
                                    :disabled="!day.currentMonth || day.disabled"
                                    @click.prevent="selectDeliveryDate(day.iso)"
                                >
                                    {{ day.label }}
                                </button>
                            </div>
                        </div>
                    </template>
                </pop-over>

                <pop-over
                    v-if="showTimeSlotPicker"
                    :title="$t('checkout.selectShippingMethod')"
                    elem-id="checkout-delivery-slot-pop-over"
                    class="delivery-slot-popup"
                    :layer="true"
                    @close="closeTimeSlotPicker"
                >
                    <template #content>
                        <div class="slot-list">
                            <p v-if="deliveryTypesLoading" class="p-15">{{ $t('checkout.loadingSlots') }}</p>
                            <p v-else-if="!availableDeliveryMethods.length" class="p-15">
                                {{ $t('checkout.noTimeSlots') }}
                            </p>
                            <div
                                v-for="method in availableDeliveryMethods"
                                :key="method.value"
                                class="slot-method"
                                :class="{'active': selectedDeliveryMethod === method.value}"
                            >
                                <button
                                    type="button"
                                    class="slot-method-head"
                                    @click.prevent="toggleDeliveryMethod(method.value)"
                                >
                                    <span>{{ method.label }}</span>
                                    <span class="slot-price">{{ method.price }}</span>
                                </button>
                                <transition name="slot-expand">
                                    <div v-if="selectedDeliveryMethod === method.value" class="slot-options">
                                        <p class="slot-options-title">{{ $t('checkout.selectTimeSlot') }}</p>
                                        <button
                                            v-for="slot in method.timeSlots"
                                            :key="slot.value"
                                            type="button"
                                            class="slot-option"
                                            :class="{'active': deliveryTimeSlot === slot.value}"
                                            @click.prevent="selectDeliveryTimeSlot(method, slot)"
                                        >
                                            <span class="slot-radio"></span>
                                            <span>{{ slot.label }}</span>
                                        </button>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </template>
                </pop-over>

            </div>
            <div class="area detail-left pt-10 plr-20 plr-sm-15 pb-20 pb-sm-15 mr-20 mr-sm mb-sm-15">
                <h5 class="b-b pb-10 mb-15 bold">
                    {{ $t('checkout.selectPayment') }}
                </h5>
                <payment-gateways
                    v-if="paymentGateway && totalPrice"
                    ref="paymentGateways"
                    :total-price="totalPrice"
                    :voucher="voucherResult"
                    :selected-address-id="selectedCurrentAddress?.id || null"
                    :delivery-date="deliveryDate"
                    :delivery-time-slot="deliveryTimeSlot"
                    :cart-shipping="cartShipping"
                    :user-delivery-type-id="userDeliveryTypeId"
                />
            </div>
            <checkout-right
                route-link="checkout"
                :checked-product="checkedProduct"
                :has-shipping="true"
                :extra-shipping-price="selectedDeliveryCharge"
                :voucher-result="voucherResult"
                :hide-btn="true"
                @calculated-price="calculatedPrice"
            >
                <template v-slot:checkout>
                    <div :class="{invalid: !!voucherError}">
                        <form class="mt-15 btn-input">
                            <input
                                class="pl-15 pr-80"
                                :placeholder="$t('checkout.voucherCode')"
                                type="text"
                                v-model="voucher">

                            <ajax-button
                                class="primary-btn plr-15"
                                type="button"
                                :fetching-data="submitting"
                                loading-text=""
                                :disabled="!voucher || !!voucherError || !!voucherResult"
                                :text="$t('checkout.apply')"
                                @clicked="checkVoucher"
                            />
                        </form>
                    </div>
                    <span v-if="voucherError" class="error">
                        {{ voucherError }}
                    </span>
                </template>
            </checkout-right>
        </div>
    </div>
</template>
<script setup>

import {useUserStore} from "~/store/user";
import {useCommonStore} from "~/store/common";
import {useLanguageStore} from "~/store/language";
import {useCartStore} from "~/store/cart";
import {useResourceStore} from "~/store/resource";
import {storeToRefs} from "pinia";
import {nextTick, onBeforeMount, onMounted} from "vue";
import {useAuthStore} from "~/store/auth";
import {useMetaData} from "~/composables/useMetaData";
import {useValidationHelper} from "../composables/useValidationHelper";
import {useAddressHelper} from "../composables/useAddressHelper";
import {createFetchClient, prepareGetUrl} from "~/utils/fetchClient";
import json from "~/jsConfig.json";

definePageMeta({
    middleware: ['common-middleware'],
    layout: 'default',
});

const languageStore = useLanguageStore()
const {langCode} = storeToRefs(languageStore)

const resourceStore = useResourceStore()
const {countryList, phoneList} = storeToRefs(resourceStore)

const commonStore = useCommonStore()
const {setting, paymentGateway, site_setting, location} = storeToRefs(commonStore)
const {setToastError, getRequest, setPaymentGateway, postRequest, fetchStateDeliveryTypes} = commonStore

const cartStoreStore = useCartStore()
const {
    cartProducts,
    checkoutDeliveryDate,
    checkoutDeliveryTimeSlot,
    checkoutSelectedDeliveryMethod,
    checkoutUserDeliveryTypeId
} = storeToRefs(cartStoreStore)
const {getCartByUser, cartChanged: cartChangedAction} = cartStoreStore

const userStore = useUserStore()
const {getUserToken} = userStore
const {allAddress, profile} = storeToRefs(userStore)

const {t} = useI18n();
const {pageMeta, preloadScript} = useMetaData();

let paymentMeta = '';

const getPaymentMeta = ({paypal_key}) => {
    return {
        link: [
            preloadScript(`https://www.paypal.com/sdk/js?client-id=${paypal_key}&components=buttons,marks&disable-funding=paylater,card`),
            preloadScript('https://checkout.flutterwave.com/v3.js')
        ]
    }
};

if (paymentGateway.value) {
    paymentMeta = getPaymentMeta(paymentGateway.value);
}

useHead({
    ...pageMeta({
        ...site_setting.value,
        ...{meta_title: `${t('header.checkout')} | ${site_setting.value.meta_title}`}
    }),
    ...paymentMeta
});

const authStore = useAuthStore();
const {authenticated, token} = storeToRefs(authStore);

const addressPopup = ref(false);
const cartShipping = ref({});
const checked = ref([]);
const editing = ref(0);
const states = ref({});
const addressLoading = ref(false);
const checkedProduct = ref([]);
const singleShippingCart = ref({});
const hasAddressErrors = ref(false);
const addressData = ref({
    id: '',
    name: '',
    phone: '',
    city: '',
    country: '',
    state: '',
    zip: '',
    address_1: '',
    address_2: '',
    delivery_instruction: ''
});
const submittingAddressData = ref(false);
const selectedCurrentAddress = ref(null);
const errorFromApi = ref(null);
const deliveryDate = checkoutDeliveryDate;
const deliveryTimeSlot = checkoutDeliveryTimeSlot;
const userDeliveryTypeId = checkoutUserDeliveryTypeId;
const selectedDeliveryMethod = checkoutSelectedDeliveryMethod;
const DELIVERY_STORAGE_KEY = 'checkout_delivery_schedule';
const deliveryTypes = ref([]);
const deliveryTypesLoading = ref(false);
const showDatePicker = ref(false);
const showTimeSlotPicker = ref(false);
const weekDayLabels = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
const calendarMonth = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1));

const availableDeliveryMethods = computed(() => {
    return (deliveryTypes.value || []).map((type) => ({
        value: `${type.delivery_type_id}`,
        deliveryTypeId: `${type.delivery_type_id}`,
        label: type.type_name,
        price: `${type.currency} ${type.price}`,
        rawPrice: parseFloat(type.price) || 0,
        timeSlots: (type?.time_slots || []).map((slot) => ({
            value: `${type.delivery_type_id}-${slot.slot_id}`,
            slotId: `${slot.slot_id}`,
            label: slot.slot_name
        }))
    }));
});

const availableTimeSlots = computed(() => {
    return availableDeliveryMethods.value.flatMap((method) =>
        method.timeSlots.map((slot) => ({
            ...slot,
            methodLabel: method.label,
            methodPrice: method.price,
            methodPriceRaw: method.rawPrice
        }))
    );
});

const selectedDeliveryCharge = computed(() => {
    if (!deliveryTimeSlot.value || !deliveryTypes.value?.length) {
        return 0;
    }
    const deliveryTypeId = `${selectedDeliveryMethod.value || ''}`;
    if (!deliveryTypeId) {
        return 0;
    }
    const selectedType = deliveryTypes.value.find(
        (type) => `${type.delivery_type_id}` === deliveryTypeId
    );
    return parseFloat(selectedType?.price || 0) || 0;
});

const getIsoDate = (dateValue) => {
    const year = dateValue.getFullYear();
    const month = `${dateValue.getMonth() + 1}`.padStart(2, '0');
    const day = `${dateValue.getDate()}`.padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const persistDeliverySchedule = (payload) => {
    if (process.server) return;
    try {
        localStorage.setItem(DELIVERY_STORAGE_KEY, JSON.stringify(payload));
    } catch (e) {}
};

const readStoredDeliverySchedule = () => {
    if (process.server) return null;
    try {
        const raw = localStorage.getItem(DELIVERY_STORAGE_KEY);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
};

const clearStoredDeliverySchedule = () => {
    if (process.server) return;
    try {
        localStorage.removeItem(DELIVERY_STORAGE_KEY);
    } catch (e) {}
};

const hasValidTimeSlots = (types = []) => {
    if (!Array.isArray(types) || !types.length) return false;
    return types.some((type) =>
        Array.isArray(type?.time_slots) &&
        type.time_slots.some((slot) => {
            const slotId = `${slot?.slot_id ?? ''}`.trim();
            const slotName = `${slot?.slot_name ?? ''}`.trim();
            return !!slotId && !!slotName;
        })
    );
};

const todayIsoDate = computed(() => {
    return getIsoDate(new Date());
});

const calendarMonthLabel = computed(() => {
    return calendarMonth.value.toLocaleDateString(undefined, {month: 'long'});
});

const deliveryDateLabel = computed(() => {
    if (!deliveryDate.value) {
        return t('checkout.selectDeliveryDate');
    }
    const parsedDate = new Date(`${deliveryDate.value}T00:00:00`);
    if (Number.isNaN(parsedDate.getTime())) {
        return t('checkout.selectDeliveryDate');
    }

    return parsedDate.toLocaleDateString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
});

const deliveryTimeSlotLabel = computed(() => {
    if (!deliveryTimeSlot.value) {
        return t('checkout.selectTimeSlot');
    }
    const found = availableTimeSlots.value.find((slot) => slot.value === deliveryTimeSlot.value);
    return found ? `${found.methodLabel} (${found.label})` : t('checkout.selectTimeSlot');
});

const selectedDeliverySummary = computed(() => {
    if (!deliveryDate.value || !deliveryTimeSlot.value) {
        return '';
    }

    const parsedDate = new Date(`${deliveryDate.value}T00:00:00`);
    const formattedDate = Number.isNaN(parsedDate.getTime())
        ? deliveryDate.value
        : parsedDate.toLocaleDateString(undefined, {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

    const selectedSlot = availableTimeSlots.value.find((slot) => slot.value === deliveryTimeSlot.value);
    const slotText = selectedSlot ? `${selectedSlot.methodLabel} (${selectedSlot.label}) - ${selectedSlot.methodPrice}` : deliveryTimeSlot.value;
    const baseText = t('checkout.deliveryOn', {date: formattedDate, slot: slotText});
    if (slotText && !baseText.includes(slotText)) {
        return `${baseText} | ${slotText}`;
    }
    return baseText;
});

const resolveAddressStateCode = (address) => {
    if (!address) return '';

    const rawState = `${address.state || ''}`.trim();
    const countryCode = `${address.country || ''}`.trim();
    const statesByCode = Object.keys(states.value || {}).length
        ? states.value
        : (countryList.value?.[countryCode]?.states || {});

    if (!rawState) return '';

    if (statesByCode[rawState]?.code) {
        return `${statesByCode[rawState].code}`.trim().toUpperCase();
    }

    const normalizedRawState = rawState.toLowerCase();
    const matchedState = Object.values(statesByCode).find((stateObj) => {
        const code = `${stateObj?.code || ''}`.trim().toLowerCase();
        const name = `${stateObj?.name || ''}`.trim().toLowerCase();
        return code === normalizedRawState || name === normalizedRawState;
    });

    if (matchedState?.code) {
        return `${matchedState.code}`.trim().toUpperCase();
    }

    return rawState.toUpperCase();
};

const selectedAddressStateCode = computed(() => {
    return resolveAddressStateCode(selectedCurrentAddress.value);
});

const canFillAddressDetails = computed(() => {
    return !!addressData.value?.state;
});

const calendarDays = computed(() => {
    const monthDate = calendarMonth.value;
    const year = monthDate.getFullYear();
    const month = monthDate.getMonth();
    const firstDayOfMonth = new Date(year, month, 1);
    const startDay = firstDayOfMonth.getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const visibleWeekCount = Math.max(5, Math.ceil((startDay + daysInMonth) / 7));
    const totalCells = visibleWeekCount * 7;
    const days = [];

    for (let i = 0; i < totalCells; i += 1) {
        const dateNumber = i - startDay + 1;
        const dateObj = new Date(year, month, dateNumber);
        const currentMonth = dateObj.getMonth() === month;
        const iso = getIsoDate(dateObj);
        days.push({
            label: dateObj.getDate(),
            iso,
            currentMonth,
            disabled: currentMonth && iso < todayIsoDate.value
        });
    }

    return days;
});

watch(profile, (newVal) => {
    if (!newVal) return;
    addressData.value.name = newVal.name
    addressData.value.email = newVal.email
})

watch(location, () => {
    settingDeliveryStates()
})

    watch(cartProducts, async (value) => {
        if (!Array.isArray(value) || value.length) return;

        deliveryDate.value = '';
        deliveryTimeSlot.value = '';
        selectedDeliveryMethod.value = '';
        deliveryTypes.value = [];
        userDeliveryTypeId.value = null;
    }, {deep: true});

watch(deliveryDate, (value) => {
    if (!value) {
        deliveryTimeSlot.value = '';
        selectedDeliveryMethod.value = '';
        deliveryTypes.value = [];
        userDeliveryTypeId.value = null;
        clearStoredDeliverySchedule();
    }
});

watch(selectedCurrentAddress, async (value, oldValue) => {
    const selectedAddressChanged = !!oldValue && `${oldValue?.id || ''}` !== `${value?.id || ''}`;
    if (!value || selectedAddressChanged) {
        selectedDeliveryMethod.value = '';
        deliveryTimeSlot.value = '';
        deliveryTypes.value = [];
        userDeliveryTypeId.value = null;
        clearStoredDeliverySchedule();
    }

    if (value && !deliveryDate.value) {
        const stored = readStoredDeliverySchedule();
        const storedDate = stored?.delivery_date || stored?.deliveryDate || '';
        const storedDeliveryTypeId = stored?.delivery_type_id || stored?.selected_delivery_method || '';
        const storedTimeSlotId = stored?.time_slot_id || '';
        if (storedDate) {
            deliveryDate.value = storedDate;
        }
        if (storedDeliveryTypeId) {
            selectedDeliveryMethod.value = `${storedDeliveryTypeId}`;
        }
        if (storedDeliveryTypeId && storedTimeSlotId) {
            deliveryTimeSlot.value = `${storedDeliveryTypeId}-${storedTimeSlotId}`;
        }
    }

    if (value && deliveryDate.value) {
        const slots = await fetchDeliveryTypes(deliveryDate.value, selectedAddressStateCode.value);
        if (!hasValidTimeSlots(slots)) {
            deliveryDate.value = '';
            deliveryTimeSlot.value = '';
            selectedDeliveryMethod.value = '';
            deliveryTypes.value = [];
            userDeliveryTypeId.value = null;
            clearStoredDeliverySchedule();
        }
    }
});

const openDatePicker = () => {
    showDatePicker.value = true;
    if (deliveryDate.value) {
        const current = new Date(`${deliveryDate.value}T00:00:00`);
        if (!Number.isNaN(current.getTime())) {
            calendarMonth.value = new Date(current.getFullYear(), current.getMonth(), 1);
        }
    }
};

const closeDatePicker = () => {
    showDatePicker.value = false;
};

const openTimeSlotPicker = () => {
    if (!deliveryDate.value || !selectedAddressStateCode.value) return;
    if (!deliveryTypes.value.length) {
        fetchDeliveryTypes(deliveryDate.value, selectedAddressStateCode.value);
    }
    showTimeSlotPicker.value = true;
};

const closeTimeSlotPicker = () => {
    showTimeSlotPicker.value = false;
};

const goToPrevMonth = () => {
    const current = calendarMonth.value;
    const previous = new Date(current.getFullYear(), current.getMonth() - 1, 1);
    const minMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
    if (previous < minMonth) return;
    calendarMonth.value = previous;
};

const goToNextMonth = () => {
    const current = calendarMonth.value;
    calendarMonth.value = new Date(current.getFullYear(), current.getMonth() + 1, 1);
};

const selectDeliveryDate = async (isoDate) => {
    deliveryDate.value = isoDate;
    if (deliveryTimeSlot.value) {
        deliveryTimeSlot.value = '';
    }
    selectedDeliveryMethod.value = '';
    clearStoredDeliverySchedule();
    const slots = await fetchDeliveryTypes(isoDate, selectedAddressStateCode.value);
    closeDatePicker();
    if (!slots.length) {
        deliveryDate.value = '';
        deliveryTimeSlot.value = '';
        selectedDeliveryMethod.value = '';
        deliveryTypes.value = [];
        userDeliveryTypeId.value = null;
        clearStoredDeliverySchedule();
        showTimeSlotPicker.value = true;
        return;
    }
    openTimeSlotPicker();
};

const selectDeliveryTimeSlot = async (method, slot) => {
    selectedDeliveryMethod.value = method.value;
    deliveryTimeSlot.value = slot.value;
    const deliveryTypeId = method?.deliveryTypeId || method?.value || null;
    const timeSlotId = slot?.slotId || (slot?.value ? `${slot.value}`.split('-').pop() : null);
    const cartIds = checkedProduct.value?.map(item => item.id) || [];
    const userType = authenticated.value ? 'user' : 'guest';
    const isNumericDeliveryTypeId = /^\d+$/.test(`${deliveryTypeId || ''}`);
    const isNumericTimeSlotId = /^\d+$/.test(`${timeSlotId || ''}`);

    if (isNumericDeliveryTypeId && isNumericTimeSlotId && deliveryDate.value) {
        persistDeliverySchedule({
            delivery_type_id: `${deliveryTypeId}`,
            delivery_date: deliveryDate.value,
            time_slot_id: `${timeSlotId}`,
            selected_delivery_method: `${deliveryTypeId}`,
            delivery_time_slot_value: `${deliveryTypeId}-${timeSlotId}`,
            cart_ids: `${cartIds}`,
            user_type: userType,
            user_id: profile.value?.id ? `${profile.value.id}` : null
        });
    }
    closeTimeSlotPicker();
};

const editDeliveryTimeSlot = () => {
    openDatePicker();
};

const toggleDeliveryMethod = (methodValue) => {
    selectedDeliveryMethod.value = selectedDeliveryMethod.value === methodValue ? '' : methodValue;
};

const fetchDeliveryTypes = async (date, stateCode = selectedAddressStateCode.value) => {
    if (!date || !stateCode) {
        deliveryTypes.value = [];
        selectedDeliveryMethod.value = '';
        deliveryTimeSlot.value = '';
        return [];
    }

    deliveryTypesLoading.value = true;
    try {
        const userToken = await getUserToken();
        const params = prepareGetUrl({
            date,
            ...(stateCode ? {state: stateCode} : {}),
            ...(userToken ? {user_token: userToken} : {})
        });
        const response = await requestDeliveryTypesApi({path: `?${params}`, auth: true});
        deliveryTypes.value = Array.isArray(response?.data) ? response.data : [];
        return deliveryTypes.value;
    } catch (e) {
        deliveryTypes.value = [];
        setToastError(e?.message || 'Unable to fetch delivery slots.');
        return [];
    } finally {
        deliveryTypesLoading.value = false;
    }
};

const requestDeliveryTypesApi = async ({method = 'GET', path = '', body = null, auth = false} = {}) => {
    const headers = {};
    if (langCode.value) {
        headers.Language = langCode.value;
        headers['X-lang'] = langCode.value;
    }
    if (auth && token.value) {
        headers.Authorization = `Bearer ${token.value}`;
    }

    const fetchClient = createFetchClient(headers);
    const response = await fetchClient(`${json.api.deliveryTypes}${path}`, {
        method,
        ...(body ? {body: JSON.stringify(body)} : {})
    });
    return response.json();
};

const {isValidNumber, isValidEmail} = useValidationHelper();

const invalidNumber = computed(() => {
    return !isValidNumber(addressData.value?.phone)
});

const numberValid = computed(() => {
    return addressData.value.phone && !invalidNumber.value
});

const invalidEmail = computed(() => {
    return !isValidEmail(addressData.value.email)
});

const emailValid = computed(() => {
    return addressData.value.email && !invalidEmail.value
});

const currentAddresses = computed(() => {
    return allAddress.value?.data
});

const shippingAddress = ref(null);

const showAddressForm = computed(() => {
    return addressPopup.value || editing.value > 0
});

const initAddress = () => {
    addressData.value = {
        id: '',
        email: '',
        name: '',
        phone: '',
        city: '',
        country: '',
        state: '',
        zip: '',
        address_1: '',
        address_2: '',
        delivery_instruction: ''
    }
};

const setAddressPopup = () => {
    addressPopup.value = true;
};

const setSelected = (event) => {
    selectedCurrentAddress.value = event;
};

const cartChanged = (evt) => {
    singleShippingCart.value = []

    if (evt) {
        getCheckedProducts()
    }
};

const shippingChanged = (evt) => {
    cartShipping.value = evt
};

const currentShipping = ({cart, shipping}) => {
    if (cartShipping.value[cart] && shipping) {

        cartShipping.value[cart].shipping_place = shipping

        const sr = shipping?.shipping_rule

        if (sr?.single_price && (!singleShippingCart.value[sr?.id] || (singleShippingCart.value[sr?.id] === cart))) {

            singleShippingCart.value[sr?.id] = cart
            cartShipping.value[cart].single_shipping = true

        } else if (sr?.single_price && singleShippingCart.value[sr?.id]) {

            cartShipping.value[cart].single_shipping = false
        }
    }
};

const getCheckedProducts = () => {
    checked.value = []
    checkedProduct.value = []

    cartProducts.value.forEach(obj => {
        if (parseInt(obj.selected) === 1) {
            checked.value.push(obj.id)

            checkedProduct.value.push(obj)

            const sp = obj?.shipping_place ?? ''

            cartShipping.value = {
                ...cartShipping.value,
                ...{
                    [obj.id]: {
                        cart: obj.id,
                        shipping_place: sp,
                        single_shipping: true,
                        shipping_type: obj.shipping_type || 1,
                    }
                }
            }
        }
    })
};

const clearData = () => {
    addressPopup.value = false
    initAddress()
    submittingAddressData.value = false
    editing.value = 0
    settingDeliveryStates()
    hasAddressErrors.value = false
};

const {addressAction, fetchingAddressData} = useAddressHelper({submittingAddressData, hasAddressErrors});

const savingAddressData = async () => {
    if (numberValid.value && emailValid.value) {
        await addressAction(addressData)
        if (!hasAddressErrors.value) {
            clearData()
        }
    } else {
        hasAddressErrors.value = true
    }
};

const selectCountry = (evt) => {
    addressData.value = {...addressData.value, ...{country: evt.value?.code2}}
    states.value = evt.value?.states
    addressData.value.state = Object.keys(evt.value?.states).length ? Object.values(evt.value?.states)[0]?.code : ''
};

const setDefaultStateFromLocation = () => {
    const currentState = `${addressData.value?.state || ''}`.trim();
    if (currentState) return;

    const locationState = `${location.value?.region || ''}`.trim().toLowerCase();
    const stateValues = Object.values(states.value || {});
    const matchedState = stateValues.find((stateObj) => {
        const code = `${stateObj?.code || ''}`.trim().toLowerCase();
        const name = `${stateObj?.name || ''}`.trim().toLowerCase();
        return !!locationState && (code === locationState || name === locationState);
    });

    if (matchedState?.code) {
        addressData.value.state = matchedState.code;
        return;
    }

    addressData.value.state = stateValues?.[0]?.code || '';
};

const fetchDeliveryStates = async () => {
    try {
        const mappedStates = await fetchStateDeliveryTypes({lang: langCode.value});
        states.value = mappedStates || {};
        setDefaultStateFromLocation();
    } catch (e) {
        setToastError(e?.message || 'Unable to load states.');
    }
};

const selectCountryNew = (evt) => {
    addressData.value = {...addressData.value, ...{country: evt.value?.code2}}
    setDefaultStateFromLocation();
};

const selectState = (evt) => {
    addressData.value.state = evt.value.code
};

const settingDeliveryStates = async () => {
    if (addressData.value) {
        if (location.value.countryCode && countryList.value[location.value.countryCode]) {
            addressData.value.country = location.value.countryCode
        } else {
            const countryKeys = Object.keys(countryList.value || {});
            addressData.value.country = countryKeys.length ? countryKeys[0] : ''
        }
    }

    if (!Object.keys(states.value || {}).length) {
        await fetchDeliveryStates();
    } else {
        setDefaultStateFromLocation();
    }
};

const settingCountry = () => {
    if (addressData.value) {
        if (location.value.countryCode && countryList.value[location.value.countryCode]) {
            addressData.value.country = location.value.countryCode
        } else {
            addressData.value.country = Object.keys(countryList.value)[0]
        }

        states.value = addressData.value?.country ? countryList.value[addressData.value.country].states : ''
        addressData.value.state = location.value.region
    }
};

const closeAddressPopup = () => {
    addressPopup.value = false
    addressData.value = {}
    addressData.value.country = location.value?.countryCode
    if (!Object.keys(states.value || {}).length) {
        fetchDeliveryStates()
    } else {
        setDefaultStateFromLocation()
    }
    addressData.value.email = profile.value?.email
};

const editAddress = (value) => {
    addressPopup.value = true
    editing.value = value.id
    addressData.value = Object.assign({}, value)
    if (!Object.keys(states.value || {}).length) {
        fetchDeliveryStates()
    }
};
onBeforeMount(async () => {
    if (!setting.value?.guest_checkout) {
        if (!authenticated.value) {
            return navigateTo('/login');
        }
    }
    if (!paymentGateway.value) {
        const data = await getRequest({
            params: '',
            api: 'paymentGateway',
            lang: langCode.value
        });

        setPaymentGateway(data.data);
        useHead(getPaymentMeta(data.data));
    }
});

const voucher = ref('');
const voucherError = ref(null);
const voucherResult = ref(null);
const submitting = ref(false);
const cartPrice = ref(0);

watch(voucher, () => {
    voucherResult.value = null
    voucherError.value = null
});

const productPrice = computed(() => {
    return cartPrice.value.totalPriceWithOffer + cartPrice.value.shippingPrice + cartPrice.value.tax
});

const totalPrice = computed(() => {
    if (productPrice.value) {
        return productPrice.value - cartPrice.value.voucher
    }
    return 0
});


const checkVoucher = async () => {
    submitting.value = true
    const res = await postRequest({
        params: {
            voucher: voucher.value,
            user_token: await getUserToken(),
            price: cartPrice.value?.totalPriceWithOffer
        },
        lang: langCode.value,
        api: 'voucherValidity'
    })
    submitting.value = false
    if (res?.status === 201) {
        voucherError.value = res.data.form[0]
    } else {
        voucherResult.value = res.data
    }
};

const calculatedPrice = (evt) => {
    cartPrice.value = evt
};


onMounted(async () => {
    voucherError.value = null
    voucherResult.value = null


    await getCartByUser({lang: langCode.value})
    getCheckedProducts()

    if (!checkedProduct.value.length && cartProducts.value.length) {
        await cartChangedAction({
            payload: {
                checked: cartProducts.value.map((item) => item.id)
            },
            lang: langCode.value
        })
        await getCartByUser({lang: langCode.value})
        getCheckedProducts()
    }

    initAddress()
    await nextTick()
    if (profile.value) {
        addressData.value.name = profile.value?.name
        addressData.value.email = profile.value?.email
    }
    await settingDeliveryStates()
    const stored = readStoredDeliverySchedule();
    if (stored && !deliveryDate.value) {
        const storedDate = stored?.delivery_date || stored?.deliveryDate || '';
        const storedDeliveryTypeId = stored?.delivery_type_id || stored?.selected_delivery_method || '';
        const storedTimeSlotId = stored?.time_slot_id || '';
        if (storedDate) {
            deliveryDate.value = storedDate;
        }
        if (storedDeliveryTypeId) {
            selectedDeliveryMethod.value = `${storedDeliveryTypeId}`;
        }
        if (storedDeliveryTypeId && storedTimeSlotId) {
            deliveryTimeSlot.value = `${storedDeliveryTypeId}-${storedTimeSlotId}`;
        }
        if (deliveryDate.value && selectedAddressStateCode.value) {
            const slots = await fetchDeliveryTypes(deliveryDate.value, selectedAddressStateCode.value);
            if (!hasValidTimeSlots(slots)) {
                deliveryDate.value = '';
                deliveryTimeSlot.value = '';
                selectedDeliveryMethod.value = '';
                deliveryTypes.value = [];
                userDeliveryTypeId.value = null;
                clearStoredDeliverySchedule();
            }
        }
    }
});


</script>
