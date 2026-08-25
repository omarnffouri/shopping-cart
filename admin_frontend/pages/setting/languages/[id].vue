<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setLanguage"
    get-api="getLanguage"
    route-name="setting/languages"
    :name="$t('dataPage.lang')"
    :validation-keys="['name']"
    :result="result"
    gate="language"
    @result="result=$event"
    @on-success="onSuccess"
  >
    <template v-slot:form="{hasError}">

      <div
        class="input-wrapper single-line"
      >
        <label class="mb-0">
          {{ $t('profile.lang') }}
        </label>

        <dropdown
          :selected-key="result.code"
          :options="languageList"
          key-name="name"
          :searching="true"
          @clicked="selectedLanguage"
        />
      </div>


      <div class="input-wrapper dply-felx start gap-10">
        <label class="mb-0">{{ $t('setting.code') }}</label>
        <input
          type="text"
          :placeholder="$t('setting.code')"
          v-model="result.code"
          disabled
          :class="{invalid: !!!result.code && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.code && hasError"
        >
          {{ $t('category.req', { type: $t('setting.code')}) }}
        </span>
      </div>


      <div class="input-wrapper dply-felx start gap-10">
        <label class="mb-0">{{ $t('setting.dir') }}</label>
        <dropdown
          :selectedKey="`${result.direction}`"
          :options="directionObj"
          @clicked="directionSelected"
        />
      </div>

      <div class="input-wrapper dply-felx start gap-10">
        <label
          for="default"
          class="mb-0 dply-felx start gap-10">
          <span class="label">{{ $t('admin.default') }}</span>
          <input
            type="checkbox"
            id="default"
            v-model="result.default"
          />

        </label>
      </div>

      <div class="input-wrapper dply-felx start gap-10">
        <label class="mb-0">{{ $t('category.status') }}</label>
        <dropdown
          :selectedKey="`${result.status}`"
          :options="statusObj"
          @clicked="dropdownSelected"
        />
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
  import {useResourceStore} from '~/store/resource';
  import {useCommonStore} from '~/store/common';
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {getById} = useCommonStore();

  const resourceStore = useResourceStore();
  const {setLanguageList} = resourceStore;
  const {languageList} = storeToRefs(resourceStore);

  const {statusObj, featuredObj} = useConstants();

  const directionObj = ref({
    'ltr': {title: 'LTR'},
    'rtl': {title: 'RTL'},
  });

  const result = ref({
    id: '',
    name: 'English',
    code: 'en',
    direction: '',
    default: false,
    status: 2,
  });

  const onSuccess = () => {
    setTimeout(() => {
      window.location.reload();
    }, 500);
  };

  const selectedLanguage = (data) => {
    result.value.code = data.key;
    result.value.name = data.value?.name;
  };

  const directionSelected = (data) => {
    result.value.direction = data.key;
  };

  const dropdownSelected = (data) => {
    result.value.status = data.key;
  };


  onMounted(async () => {
    if (!languageList.value) {
      const data = await getById({
        params: null,
        id: 'languages',
        api: 'resource'
      });
      setLanguageList(data);
    }
  });
</script>
