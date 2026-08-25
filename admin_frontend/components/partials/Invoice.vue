<template>
    <div>

        <div class="order-wrapper bg-transparent">

            <div class="dply-felx j-right mb-20 mb-sm-15">
                <button class="plr-20 dply-felx outline-btn" @click="downloadPdf()">
                    <i class="icon print-icon mr-10"/>
                    {{ $t('setting.pi') }}
                </button>
            </div>
        </div>

        <div ref="pdfContentRef" id="pdf-content">
            <div class="order-wrapper invoice-wrapper f-9 pt-30 pb-30">

                <div class="sided p-30 align-start">
                    <div class="mx-w-350x">
                        <img v-if="base64SiteLogo"
                             class="h-25x w-auto mb-10"
                             :src="`data:image/png;base64,${base64SiteLogo}`"
                        >
                        <h4 class="fw-600 mtb-5">
                            {{ getDataFromObject(storeData, 'name') }}
                        </h4>
                        <p> {{generateAddress(setting)}}</p>
                        <p>Phone: {{ getDataFromObject(setting, 'phone', $t('prod.na')) }}</p>
                    </div>

                    <div>
                        <h3 class="mb-5 bold">{{ $t('setting.inv') }}</h3>
                        <ul class="mx-w-400x order-details lh-2">
                            <li>
                                <span>{{ $t('fSale.orderUp') }}</span>
                                <span>#{{ order.order }}</span>
                            </li>
                            <li>
                                <span>{{ $t('setting.od') }}</span>
                                <span>{{ order.created }}</span>
                            </li>
                            <li v-if="!isVendor">
                                <span>{{ $t('setting.oa') }}</span>
                                <span>{{ priceFormatting(totalPrice) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="plr-30">
                    <table class="mn-w-600x no-bg mt-0 shipping-table">
                        <thead>
                        <tr class="lite-bold">
                            <th>{{ $t('fSale.shipTo') }}</th>
                            <th>{{ $t('setting.om') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td class="w-50">
                                <div class="mx-w-300x lh-2">
                                    <b>{{ getDataFromObject(order, 'address.name', $t('prod.na')) }}</b>
                                    <span
                                            v-if="getDataFromObject(order, 'address')"
                                            class="block"
                                    >
                                        {{ generateAddress(getDataFromObject(order, 'address')) }}</span>
                                    <span class="block">Email: {{ userEmail }}</span>
                                    <span class="block">Phone: {{ getDataFromObject(order, 'address.phone',  $t('prod.na')) }}</span>
                                </div>
                            </td>
                            <td class="w-50">{{ paymentTypes[order.order_method] }}</td>
                        </tr>
                        </tbody>

                    </table><!--table-->

                    <table class="mn-w-600x no-bg">
                        <thead>
                        <tr class="lite-bold">
                            <th>{{ $t('index.title') }}</th>
                            <th>{{ $t('fSale.dFee') }}</th>
                            <th>{{ $t('fSale.qty') }}</th>
                            <th>{{ $t('brand.price') }}</th>
                            <th>{{ $t('fSale.total') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(value, index) in order.ordered_products" :key="index">

                            <td style="min-width: 250px">
                                {{ value.product.title }}
                                <span style="display: block"
                                >
                                <span
                                        v-for="i in generatingAttribute(value)"
                                        class="mr-15"
                                >
                                  <b class="mr-10">{{ i[0] }}:</b> {{ i[1] }}
                                </span>

                                <span
                                        v-if="value.updated_inventory.sku" class="block mt-5"
                                >
                                  SKU: {{ value.updated_inventory.sku }}
                                </span>

                              </span>
                            </td>
                            <!--<td>{{ shippingType[value.shipping_type] }}</td>-->
                            <td>{{ priceFormatting(value.shipping_price) }}</td>
                            <td>{{ value.quantity }}</td>

                            <td>{{ priceFormatting(value.selling) }}</td>
                            <td>{{ priceFormatting(value.selling * value.quantity) }}</td>
                        </tr>
                        </tbody>
                    </table><!--table-->
                    <div v-if="!isVendor"
                            class="dply-felx j-right">
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

                    <table class="w-50 no-bg mt-0 shipping-table single-table">
                        <thead>
                        <tr class="lite-bold">
                            <th>{{ $t('setting.notes') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td class="w-50">
                                <p class="semi-bold italic mb-10">
                                    {{ $t('setting.query') }}
                                </p>
                                <p>
                                    {{ $t('setting.query') }}
                                    {{ $t('setting.regard') }}: {{ getDataFromObject(setting, 'phone', $t('prod.na')) }}
                                </p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!--table-->
                </div>
            </div>
        </div>

        <div>
        </div>
    </div>
</template>

<script setup>
    import {useSettingStore} from '~/store/setting';
    import {useResourceStore} from '~/store/resource';
    import {useCommonStore} from '~/store/common';
    import {useAdminStore} from '~/store/admin';
    import {useSiteSettingStore} from '~/store/site-setting';
    import {storeToRefs} from "pinia";
    import html2pdf from 'html2pdf.js';
    import {useI18n} from "vue-i18n";
    import {onMounted} from "vue";
    import {useUtils} from "~/composables/useUtils";
    import {useConstants} from "~/composables/useConstants";


    const props = defineProps({
        order: {
            type: Object
        }
    });

    const {order} = toRefs(props);

    const emit = defineEmits(['downloaded']);

    const resourceStore = useResourceStore()
    const {countryList} = storeToRefs(resourceStore)
    const {setCountryList} = resourceStore

    const {getById} = useCommonStore()

    const settingStore = useSettingStore()
    const {setting, base64SiteLogo, storeData} = storeToRefs(settingStore)
    const {setConvertImage} = settingStore

    const adminStore = useAdminStore()
    const {isVendor} = storeToRefs(adminStore)

    const siteSettingStore = useSiteSettingStore()
    const {siteSetting} = storeToRefs(siteSettingStore)

    const {paymentTypes  } = useConstants();
    const {getDataFromObject, priceFormatting  } = useUtils();

    const {t} = useI18n();
    const pdfContentRef = ref(null);

    const downloadPdf = () => {
        const options = {
            margin: 0,
            filename: `${t('setting.inv')}-${props.order.order}`,
            image: {type: 'jpeg', quality: 0.98},
            html2canvas: {scale: 2},
            jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'},
        };
        html2pdf().set(options).from(pdfContentRef.value).save().finally(() => {
            // Hide the element again after generating the PDF
            triggerDownload()
        });
    }

    const triggerDownload = () => {
        emit('downloaded', true);
    };


    const isFreeShipping = computed(() => {
        return !(parseFloat(shippingPrice.value) > 0);
    });

    const userEmail = computed(() => {
        return order.value?.user?.email ?? order.value?.guest_user?.email ?? t('prod.na')
    });

    const currencyPosition = computed(() => {
        return setting.value?.currency_position
    });

    const totalPrice = computed(() => {
        return order.value?.calculated.total_price
    });

    const voucherPrice = computed(() => {
        return order.value?.calculated.voucher_price
    });

    const bundleOffer = computed(() => {
        return order.value?.calculated.bundle_offer
    });

    const shippingPrice = computed(() => {
        return order.value?.calculated.shipping_price
    });

    const taxPrice = computed(() => {
        return order.value?.calculated.tax
    });

    const subtotalPrice = computed(() => {
        return order.value?.calculated.subtotal
    });

    const currencyIcon = computed(() => {
        return setting.value?.currency_icon || '$'
    });


    const generateAddress = (obj) => {
        if (!obj) {
            return t('prod.na')
        }
        let addArr = []
        addArr.push(obj?.address_1)
        if (obj?.address_2) {
            addArr.push(obj?.address_2)
        }
        addArr.push(obj?.city + '-' + obj?.zip)
        if (countryList.value && countryList.value[obj?.country]) {
            const country = countryList.value[obj?.country]
            if (country.states[obj?.state]) {
                addArr.push(country.states[obj.state]?.name)
            }
            addArr.push(country?.name)
        }
        return addArr.join(', ')
    };

    const generatingAttribute = (attr) => {
        return attr?.updated_inventory?.inventory_attributes?.map(i => {
            return [i?.attribute_value?.attribute?.title, i?.attribute_value?.title]
        })
    };

    defineExpose({
        downloadPdf
    });

    onMounted(async () => {
        if (!countryList.value) {
            const data = await getById({
                params: null,
                id: 'countries',
                api: 'resource'
            });
            setCountryList(data);
        }
        if (!base64SiteLogo.value) {
            await setConvertImage(siteSetting.value?.email_logo);
        }
    });

</script>
