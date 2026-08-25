<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setCustomScript"
    get-api="getCustomScript"
    route-name="custom-scripts"
    :name="$t('title.cs')"
    :validation-keys="['route_pattern']"
    :result="result"
    gate="header_link"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">

      <p class="info-msg mb-20 mb-sm-15" v-html="$t('title.up')"/>

      <div class="input-wrapper">
        <label>{{ $t('title.rParam') }}</label>
        <input
          type="text"
          :placeholder="$t('title.rParam')"
          name="title"
          v-model="result.route_pattern"
          :class="{invalid: !!!result.route_pattern && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.route_pattern && hasError"
        >
          {{ $t('category.req', { type: $t('title.rParam')}) }}
        </span>
      </div>

      <p class="info-msg mb-20 mb-sm-15" v-html="$t('title.wj')"/>

      <div class="input-wrapper dply-felx start">
        <label for="headerCode" class="mb-0">
          <input
            type="checkbox"
            :true-value="1" :false-value="0"
            id="headerCode"
            v-model="result.header_script"
            @change="cbHeaderChanged"
          />
          {{ $t('title.hsc') }}
        </label>
        <button
          type="button"
          class="toggle-arrow"
          @click="toggleHeaderForm"
        >
          <i class="icon black arrow-down"/>
        </button>
      </div>

      <div v-if="showHeaderForm" class="input-wrapper">
        <label>{{ $t('title.hsc') }}</label>

        <textarea
          :placeholder="$t('title.hsc')"
          v-model="result.header_script_code"
        />
      </div>

      <div class="input-wrapper dply-felx start">
        <label for="bodyCode" class="mb-0">
          <input
            type="checkbox"
            id="bodyCode"
            :true-value="1" :false-value="0"
            v-model="result.body_script"
            @change="cbBodyChanged"
          />
          {{ $t('title.bs') }}
        </label>
        <button
          type="button"
          class="toggle-arrow"
          @click="toggleBodyForm"
        >
          <i class="icon black arrow-down"/>
        </button>
      </div>

      <div v-if="showBodyForm" class="input-wrapper">
        <label>{{ $t('title.bsc') }}</label>

        <textarea
          :placeholder="$t('title.bsc')"
          v-model="result.body_script_code"
        />
      </div>

      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('category.status') }}
          </span>

          <dropdown
            :selectedKey="`${result.status}`"
            :options="statusObj"
            @clicked="statusSelected"
          />
        </div>
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>

  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {useLanguageStore} from "~/store/language";
  import {useSettingStore} from "~/store/setting";
  import {useConstants} from "../../composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {getDropdownList} = commonStore;
  const {allCategories, allSubCategories, allBrands} = storeToRefs(commonStore);

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);

  const languageStore = useLanguageStore();
  const {currentLanguage} = storeToRefs(languageStore);

  const {statusObj} = useConstants();

  const showHeaderForm = ref(false);
  const showBodyForm = ref(false);
  const result = ref({
    id: '',
    route_pattern: '',
    header_script: '',
    header_script_code: '',
    body_script: '',
    body_script_code: '',
    status: '',
  });

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$';
  });

  const toggleHeaderForm = () => {
    showHeaderForm.value = !showHeaderForm.value;
  };
  const toggleBodyForm = () => {
    showBodyForm.value = !showBodyForm.value;
  };

  const cbHeaderChanged = (evt) => {
    showHeaderForm.value = evt.target.checked
  };

  const cbBodyChanged = (evt) => {
    showBodyForm.value = evt.target.checked
  };

  const statusSelected = (data) => {
    result.value.status = data.key
  };
</script>
