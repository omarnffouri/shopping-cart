<template>
    <PartialsDataPage
            class="detail-width"
            ref="dataPage"
            set-api="setFlashSale"
            get-api="getFlashSale"
            route-name="flash-sales"
            :name="$t('profile.fSale')"
            :validation-keys="['title', 'start_time', 'end_time']"
            :result="result"
            gate="flash_sale"
            @result="settingResult"
    >
        <template v-slot:form="{hasError}">

            <div class="input-wrapper">
                <label>{{ $t('index.title') }}</label>
                <input
                        type="text"
                        :placeholder="$t('index.title')"
                        v-model="result.title"
                        :class="{invalid: !!!result.title && hasError}"
                >
                <span
                        class="error"
                        v-if="!!!result.title && hasError"
                >
          {{ $t('category.req', { type: $t('index.title')}) }}
                </span>
                    </div>

                    <div class="dply-felx block-xs mlr--7-5 inputs align-start j-left"
                         :class="{'red-border': !!!result.start_time && hasError}"
                    >
                        <div class="input-wrapper mlr-7-5">
                            <label>{{ $t('prod.sTime') }}</label>

                            <FlatPickr width="200px" :config="dpConfig" v-model="result.start_time"/>

                            <span class="error"
                                  v-if="!!!result.start_time && hasError"
                            >
                    {{ $t('category.req', { type: $t('prod.sTime')}) }}
                  </span>
                        </div>

                        <div class="input-wrapper mlr-7-5"
                             :class="{'red-border': !!!result.end_time && hasError}"
                        >
                            <label>{{ $t('prod.eTime') }}</label>


                            <FlatPickr width="200px" :config="dpConfig" v-model="result.end_time"/>

                            <span class="error"
                                  v-if="!!!result.end_time && hasError"
                            >
                    {{ $t('category.req', { type: $t('prod.eTime')}) }}
                  </span>
                            <span class="error"
                                  v-else-if="!dateValidation && hasError"
                            >
                    {{ $t('prod.greater') }}
                  </span>
                </div>

                <div class="input-wrapper mlr-7-5">
                    <label class="block">
                        {{ $t('category.status') }}
                    </label>

                    <dropdown
                            :selectedKey="`${result.status}`"
                            :options="statusObj"
                            @clicked="dropdownSelected"
                    />
                </div>
            </div><!--dply-felx inputs-->

            <PartialsProductSearch
                    ref="productSearchRef"
                    @product-clicked="addFlashProduct"
            />

            <h4>{{ $t('fSale.sProd') }}</h4>
            <div class="table-wrapper mb-20 mb-sm-15">
                <table class="mn-w-600x">

                    <thead>
                    <tr class="lite-bold">
                        <th>{{ $t('index.title') }}</th>
                        <th>{{ $t('brand.price') }}({{ currencyIcon }})</th>
                        <th>{{ $t('prod.offered') }}({{ currencyIcon }})</th>
                        <th>{{ $t('fSale.sPrice') }}({{ currencyIcon }})</th>
                        <th/>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in result.products"
                        :key="index"
                        class="deletable"
                        :class="{deleted: item.deleted}"
                    >
                        <td>
                            <div>
                                <nuxt-link
                                        :to="`/products/${item.product.id}`"
                                        class="dply-felx j-left"
                                >
                                    <ImageLazy
                                            class="mr-20"
                                            :lazy-src="getThumbImageURL(item.product.image)"
                                            :alt="item.product.title"
                                    />

                                    <h5 class="mx-w-400x">{{ item.product.title }}</h5>
                                </nuxt-link>
                            </div>

                        </td>
                        <td>
                            <price-format :price="item.product.selling"/>
                        </td>
                        <td>
                            <price-format :price="item.product.offered"
                            />
                        </td>
                        <td class="mx-w-130x">
                            <input :disabled="item.deleted"
                                   type="number"
                                   step="any"
                                   v-model="item.price"
                                   placeholder="Offered"
                                   @change="valueChanged(index)"
                            />
                        </td>
                        <td class="undo-container">
                            <button
                                    v-if="item.deleted"
                                    @click.prevent="undoDelete(index)"
                                    class="lite-btn"
                            >
                                {{ $t('fSale.undo') }}
                            </button>
                            <button
                                    v-else
                                    @click.prevent="deleteProduct(index)"
                                    class="lite-btn delete-btn"
                            >
                                {{ $t('category.delete') }}
                            </button>
                        </td>
                    </tr>
                    </tbody>

                </table>
            </div>

        </template>
    </PartialsDataPage>
</template>

<script setup>

    import moment from 'moment-timezone'
    import {useCommonStore} from "~/store/common";
    import {storeToRefs} from "pinia";
    import {useSettingStore} from "~/store/setting";
    import {useUtils} from "~/composables/useUtils";
    import {useConstants} from "~/composables/useConstants";

    definePageMeta({
        middleware: ['common-middleware', 'auth'],
        layout: 'default',
    });

    const commonStore = useCommonStore();
    const {getById} = commonStore;

    const settingStore = useSettingStore();
    const {setting} = storeToRefs(settingStore);

    const {dpConfig, statusObj} = useConstants();

    const {getTimeZone, getThumbImageURL} = useUtils();
    const timeZone = getTimeZone();

    const result = ref({
        id: '',
        title: '',
        status: 2,
        start_time: '',
        end_time: '',
        time_zone: timeZone,
        products: []
    });


    const dateValidation = computed(() => {
        return new Date(result.value.end_time) > new Date(result.value.start_time)
    });

    const currencyIcon = computed(() => {
        return setting.value?.currency_icon || '$'
    });


    const settingResult = (evt) => {
        evt['start_time'] = moment(moment.utc(evt['start_time'])).local().format('YYYY-MM-DD HH:mm:ss');
        evt['end_time'] = moment(moment.utc(evt['end_time'])).local().format('YYYY-MM-DD HH:mm:ss')

        result.value = {...evt, time_zone: timeZone}
    };


    const valueChanged = (index) => {
        result.value.products[index] = {
            ...result.value.products[index],
            ...{updated: true}
        }
    };

    const productSearchRef = ref(null);

    const addFlashProduct = (product) => {
        if (result.value.products.findIndex((o) => {
            return o.product.id === product.id
        }) === -1) {
            result.value.products.push({
                price: 0,
                product: {
                    id: product.id,
                    title: product.title,
                    image: product.image,
                    offered: product.offered,
                    selling: product.selling
                }
            })
        }
        productSearchRef.value.autoSuggestionClose()
    };


    const dropdownSelected = (data) => {
        result.value.status = data.key
    };

    const deleteProduct = (index) => {
        result.value.products[index] = {
            ...result.value.products[index],
            ...{deleted: true}
        }
        result.value = {...result.value, ...{products: result.value.products}}
    };

    const undoDelete = (index) => {
        result.value.products[index] = {
            ...result.value.products[index],
            ...{deleted: false}
        }
        result.value = {...result.value, ...{products: result.value.products}}
    };

</script>
