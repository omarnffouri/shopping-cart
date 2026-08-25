<template>
  <PartialsSettingLayout
    active-route="analytics"
    class="mb-5"
  >
    <template v-slot:rightArea>

      <h4 class="mb-20 mb-sm-15">{{ $t('title.ana') }}</h4>
      <form
        :class="{'has-error': hasError}"
        @submit.prevent="setAnalytics"
      >

        <label class="input-wrapper block">
          <input type="checkbox" v-model="result.enable_ga" :true-value="1" :false-value="0">
          {{ $t('title.ega') }}!!
        </label>

        <div class="input-wrapper single-line">
          <label>
            {{ $t('title.gaId') }}
          </label>

          <input
            type="text"
            :placeholder="$t('title.eGa')"
            v-model="result.ga_id"
          >
        </div>

        <label class="input-wrapper block">
          <input type="checkbox" v-model="result.enable_pixel" :true-value="1" :false-value="0">
          {{ $t('title.pixel') }}
        </label>

        <div class="input-wrapper single-line">
          <label>
            {{ $t('title.pixelId') }}
          </label>

          <input
            type="text"
            :placeholder="$t('title.ePixel')"
            v-model="result.pixel_id"
          >
        </div>

        <ajax-button
          class="primary-btn"
          :text="$t('setting.sv')"
          :fetching-data="loading"
        />
      </form>

    </template>
  </PartialsSettingLayout>
</template>

<script setup>

  import {useSettingStore} from '~/store/setting';
  import {useUiStore} from '~/store/ui';
  import {useCommonStore} from '~/store/common';

  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {setRequest, getRequest} = useCommonStore();

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);
  const {getSetting} = settingStore;

  const {setErrors} = useUiStore();

  const result = ref({
    enable_ga: false,
    enable_pixel: false,
    ga_id: "",
    pixel_id: ""
  });

  const loading = ref(false);
  const hasError = ref(false);

  const setAnalytics = async () => {
    setErrors()
    loading.value = true
    await setRequest({params: result.value, api: 'analytics'})
    loading.value = false
  };

  onMounted(async () => {
    if (!setting.value) {
      gettingStore.value = true
      await getSetting()
      result.value = Object.assign({}, setting.value)
      gettingStore.value = false
    }
    result.value = Object.assign({}, setting.value)
  });
</script>


