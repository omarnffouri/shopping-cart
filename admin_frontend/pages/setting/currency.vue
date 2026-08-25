<template>
  <PartialsSettingLayout
    active-route="currency"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <form
        :class="{'has-error': hasError}"
        @submit.prevent="updateCurrency"
      >
        <div
          v-if="loading"
          class="spinner-wrapper"
        >
          <spinner
            :radius="60"
            color="primary"
            class="mr-15"
          />
        </div>

        <div class="input-wrapper single-line">
          <label>
            {{ $t('setting.cur') }}
          </label>

          <dropdown
            v-if="currency"
            :selected-key="currency"
            :options="currencyList"
            key-name="name"
            :searching="true"
            @clicked="selectedCurrency"
          />
        </div>

        <div class="input-wrapper single-line">
          <label>
            {{ $t('setting.curPos') }}
          </label>

          <dropdown
            :selectedKey="`${currencyPosition}`"
            :options="currencyPositions"
            @clicked="selectedCurrencyPosition"
          />
        </div>

        <div class="input-wrapper single-line">
          <label>
            {{ $t('dataPage.df') }}
          </label>

          <dropdown
            v-if="decimalFormat"
            :selected-key="decimalFormat"
            :options="decimalFormatList"
            key-name="name"
            :searching="true"
            @clicked="selectedDecimalFormat"
          />
        </div>

        <ajax-button
          v-if="$can('setting', 'edit')"
          class="primary-btn"
          :text="$t('dataPage.uc')"
          :fetching-data="updatingCurrency"
        />
      </form>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  import {useCommonStore} from '~/store/common';
  import {useResourceStore} from '~/store/resource';
  import {useSettingStore} from "~/store/setting";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {getById} = useCommonStore();

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);

  const {setCurrency} = settingStore;

  const resourceStore = useResourceStore();
  const {currencyList, decimalFormatList} = storeToRefs(resourceStore);
  const {setCurrencyList, setDecimalFormatList} = resourceStore;

  const updatedCurrency = ref(null);
  const decimalFormat = ref('en-US');
  const currencyPosition = ref(1);
  const loading = ref(false);
  const updatingCurrency = ref(false);
  const hasError = ref(false);

  const {currencyPositions} = useConstants();

  const currency = computed(() => {
    return setting.value?.currency || 'USD'
  });

  const currencyIcon = ref('$');

  const selectedDecimalFormat = (data) => {
    decimalFormat.value = data.key
  };

  const selectedCurrencyPosition = (data) => {
    currencyPosition.value = data.key
  };

  const selectedCurrency = (data) => {
    updatedCurrency.value = data.key
    currencyIcon.value = data.value.symbolNative
  };

  const updateCurrency = async () => {
    if (updatedCurrency.value && currencyIcon.value && currencyPosition.value) {
      updatingCurrency.value = true

      await setCurrency({
        currency: updatedCurrency.value || currency.value,
        currency_icon: currencyIcon.value,
        decimal_format: decimalFormat.value,
        currency_position: currencyPosition.value
      })

      updatingCurrency.value = false
    }
  };


  onMounted(async () => {
    if (!currencyList.value) {
      loading.value = true

      const data = await getById({
        params: null,
        id: 'currencies',
        api: 'resource'
      })
      setCurrencyList(data)
      loading.value = false
    }
    if (!decimalFormatList.value) {
      loading.value = true

      const data = await getById({
        params: null,
        id: 'decimalFormats',
        api: 'resource'
      })
      setDecimalFormatList(data)
      loading.value = false
    }
    updatedCurrency.value = setting.value?.currency
    currencyIcon.value = setting.value?.currency_icon
    currencyPosition.value = setting.value?.currency_position

    if (setting.value?.decimal_format) {
      decimalFormat.value = setting.value?.decimal_format
    }
  });
</script>


