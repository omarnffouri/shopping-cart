<template>
  <div
    v-if="$can('header_link', 'view')"
    class="tab-sidebar"
  >
    <h4 class="title">{{ $t('dataPage.head') }}</h4>
    <div
      ref="formWrapper"
      class=""
    >
      <div class="form-wrapper">
        <error-formatter/>

        <p class="info-msg mb-15 mb-sm-15">
          {{ $t('setting.linkMsg') }}
        </p>

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

        <PartialsHeaderLink
          v-if="result"
          :title="$t('util.left')"
          :result-data="result"
          @changed="result = $event"
        />

        <PartialsHeaderLink
          v-if="result"
          :title="$t('util.right')"
          type="right"
          :result-data="result"
          @changed="result = $event"
        />

        <div
          v-if="!gate || $can(gate, 'edit') || $can(gate, 'create')"
          class="dply-felx j-right single-btn"
        >
          <ajax-button
            type="button"
            class="primary-btn"
            :fetching-data="formSubmitting"
            :text="$t('setting.sv')"
            @clicked="saveHeader"
          />
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
  import {useCommonStore} from "../../store/common";
  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {getRequest, setRequest} = useCommonStore();

  const gate = ref('header_link');
  const result = ref(null);
  const formSubmitting = ref(false);
  const loading = ref(false);

  const saveHeader = async () => {
    formSubmitting.value = true
    const data = await setRequest({params: result.value, api: 'setHeaderLink'})
    result.value = Object.assign({}, data)
    formSubmitting.value = false
  };

  const fetchingData = async () => {
    loading.value = true
    const data = await getRequest({params: {}, api: 'getHeaderLinks'})
    result.value = Object.assign({}, data)
    loading.value = false
  };

  onMounted(async () => {
    await fetchingData()
  });

</script>
