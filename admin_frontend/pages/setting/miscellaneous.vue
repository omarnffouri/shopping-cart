<template>
  <PartialsSettingLayout
    active-route="miscellaneous"
    class="mb-5"
  >
    <template v-slot:rightArea>

<!--      <div v-if="activated" class="f-wrap mb-20 mb-sm-15 info-msg dply-felx gap-10">
        <p
          class=""
        >
          {{ $t('dataPage.activated') }}
        </p>

        <ajax-button
          type="button"
          class="primary-btn"
          :disabled="isDemo"
          :activate-btn="true"
          :text="$t('dataPage.deact')"
          @clicked="deactivateScript"
          :fetching-data="deactivating"
        />
      </div>

      <form
        v-else
        :class="{'has-error': hasError}"
        @submit.prevent="activateScript"
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
            {{ $t('dataPage.pkey') }}
          </label>

          <input
            type="text"
            :placeholder="$t('dataPage.pkey')"
            v-model="result.code"
            :class="{invalid: !!!result.code}"
          >
          <span
            class="error"
            v-if="!!!result.code && hasError"
          >
            {{ $t('category.req', { type: $t('dataPage.pkey')}) }}
          </span>
        </div>

        <ajax-button
          class="primary-btn mb-20"
          :activate-btn="true"
          :text="$t('dataPage.act')"
          :fetching-data="updating"
        />
      </form>-->

      <h4 class="mb-20 mb-sm-15">{{ $t('dataPage.mis') }}</h4>
      <form
        :class="{'has-error': hasMisError}"
        @submit.prevent="setMiscellaneous"
      >

        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.guest_checkout" :true-value="1" :false-value="0">
          {{ $t('title.gc') }}
        </label>


        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.cookie_banner" :true-value="1" :false-value="0">
          {{ $t('title.cb') }}
        </label>

        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.vendor_registration" :true-value="1" :false-value="0">
          {{ $t('title.vr') }}
        </label>

        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.attach_pdf" :true-value="1" :false-value="0">
          {{ $t('title.apue') }}
        </label>


        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.translate_pdf" :true-value="1" :false-value="0">
          {{ $t('ship.trans') }}
        </label>


        <label class="input-wrapper block">
          <input type="checkbox" v-model="cbSetting.send_seller_email" :true-value="1" :false-value="0">
          {{ $t('title.sEmail') }}
        </label>


        <div class="input-wrapper location-wrap">
          <div class="dply-felx f-wrap gap-10 start">
            <label>{{ $t('ship.dl') }}</label>

            <dropdown
              :selected-key="cbSetting.default_country"
              :searching="true"
              :options="countryList"
              key-name="name"
              @clicked="selectedCountry"
            />

            <dropdown
              :selected-key="cbSetting.default_state"
              :searching="true"
              :options="stateList"
              key-name="name"
              @clicked="stateSelected"
            />
          </div>
        </div>

        <ajax-button
          class="primary-btn"
          :text="$t('setting.sv')"
          :fetching-data="misUpdating"
        />
      </form>

    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  import {useSettingStore} from "~/store/setting";
  import {useUiStore} from "~/store/ui";
  import {useResourceStore} from "~/store/resource";
  import {useAdminStore} from "~/store/admin";
  import {useCommonStore} from "~/store/common";
  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {setRequest, getRequest, getById} = useCommonStore();

  const {setErrors} = useUiStore();

  const adminStore = useAdminStore();
  const {activated} = storeToRefs(adminStore)
  const {setActivated} = adminStore

  const resourceStore = useResourceStore();
  const {countryList} = storeToRefs(resourceStore)
  const {setCountryList} = resourceStore

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);
  const {getSetting} = settingStore;

  const cbSetting = ref({
    default_country: 'AF',
    default_state: 'BDS',
    attach_pdf: false,
    translate_pdf: false,
    send_seller_email: false,
    vendor_registration: false,
    cookie_banner: false,
    guest_checkout: false
  });

  const result = ref({
    code: null
  });

  const stateList = ref({});
  const misUpdating = ref(false);
  const deactivating = ref(false);
  const loading = ref(false);
  const updating = ref(false);
  const hasMisError = ref(false);
  const hasError = ref(false);

  const isDemo = computed(() => {
    const config = useRuntimeConfig();
    return config.public.isDemo;
  });

  const stateSelected = (evt) => {
    cbSetting.value.default_state = evt.key
  };

  const generateStateList = () => {
    stateList.value = countryList.value[cbSetting.value?.default_country]?.states
  };

  const selectedCountry = (evt) => {
    cbSetting.value.default_country = evt.key
    generateStateList()

    if (Object.keys(stateList.value).length) {
      cbSetting.value.default_state = Object.keys(stateList.value)[0]
    }
  };

  const deactivateScript = async () => {
    if (confirm(t('dataPage.deactMsg'))) {
      setErrors();
      deactivating.value = true;
      await getRequest({params: {}, api: 'deactivate'});
      window.location.reload();
      deactivating.value = false;
    }
  };

  const setMiscellaneous = async () => {
    setErrors();
    misUpdating.value = true;
    await setRequest({params: cbSetting.value, api: 'miscellaneous'});
    misUpdating.value = false;
  };

  const activateScript = async () => {
    setErrors();
    hasError.value = false;

    if (result.value.code) {
      updating.value = true;
      const data = await setRequest({params: result.value, api: 'activate'});
      setActivated({
        activated: data?.valid,
        public_key: data?.public_key
      });
      updating.value = false;
    } else {
      hasError.value = true;
    }
  };

  onMounted(async () => {
    if (!countryList.value) {
      loading.value = true;

      const data = await getById({
        params: null,
        id: 'countries',
        api: 'resource'
      });

      setCountryList(data);
      loading.value = false;
      generateStateList();
    }

    if (!setting.value) {
      gettingStore.value = true;
      await getSetting();
      cbSetting.value = Object.assign({}, setting.value);
      gettingStore.value = false;
    }
    cbSetting.value = Object.assign({}, setting.value);
    generateStateList();
  });

</script>


