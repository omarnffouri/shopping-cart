<template>
  <div class="tab-sidebar">
    <h4 class="title">{{ $t('setting.store') }}</h4>
    <div class="form-wrapper">
      <form
        @submit.prevent="updateStore"
      >
        <error-formatter/>
        <error-formatter
          type="image"
        />
        <div
          v-if="gettingStore"
          class="spinner-wrapper"
        >
          <spinner
            :radius="60"
            color="primary"
            class="mr-15"
          />
        </div>

        <div v-if="!gettingStore" class="input-wrapper b-b pb-15">
          <label class="mb-15">
            {{ $t('setting.sLogo') }}
          </label>

          <div
            v-if="!gate || (gate && $can(gate, 'edit'))"
            class=""
          >
            <image-input
              v-if="mediaStorageData.URL === mediaStorage"
              :saving="fileUploading"
              :image="result.image"
              @image-change="uploadFile(null, $event)"
            />

            <file-upload
              v-else
              class="logo-upload upload-block mx-w-300x"
              :image="result.image"
              :file-uploading="fileUploading"
              :btn-text="$t('setting.cLogo')"
              @file-upload="uploadFile"
            />
          </div>

          <img
            v-else
            :src="getImageURL(result.image)"
          >
        </div>

        <div :class="{'has-error': hasError}">
          <div class="input-wrapper">
            <label>
              {{ $t('setting.sName') }}
            </label>
            <input
              type="text"
              @change="slugChange($event, 'name')"
              :placeholder="$t('setting.sName')"
              v-model="result.name"
              :class="{invalid: !result.name && hasError}"
            >
            <span
              class="error"
              v-if="!result.name && hasError"
            >
              {{ $t('category.req', { type: $t('setting.sName')}) }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>
              {{ $t('category.slug') }}
            </label>
            <input
              type="text"
              :placeholder="$t('category.slug')"
              v-model="result.slug"
              :class="{invalid: !result.slug && hasError}"
            >
            <span
              class="error"
              v-if="!result.slug && hasError"
            >
              {{ $t('category.req', { type: $t('category.slug')}) }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>{{ $t('category.mTitle') }}</label>
            <input
              type="text"
              :placeholder="$t('category.mTitle')"
              v-model="result.meta_title"
              :class="{invalid: !!!result.meta_title && hasError}"
            >
            <span
              class="error"
              v-if="!!!result.meta_title && hasError"
            >
              {{ $t('category.req', { type: $t('category.mTitle')}) }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>{{ $t('category.mDesc') }}</label>
            <textarea
              :placeholder="$t('category.mDesc')"
              v-model="result.meta_description"
              :class="{invalid: !!!result.meta_description && hasError}"
            />
            <span
              class="error"
              v-if="!!!result.meta_description && hasError"
            >
              {{ $t('category.req', { type: $t('category.mDesc')}) }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>{{ $t('ship.mk') }} ({{ $t('ship.csk') }})</label>
            <textarea
              :placeholder="$t('ship.mk')"
              v-model="result.meta_keywords"
              :class="{invalid: !!!result.meta_keywords && hasError}"
            />
            <span
              class="error"
              v-if="!!!result.meta_keywords && hasError"
            >
              {{ $t('category.req', { type: $t('ship.mk')}) }}
            </span>
          </div>

          <PartialsWhatsAppSetting
            :store-data="result"
          />
        </div>

        <div class="dply-felx j-right">
          <ajax-button
            v-if="!gate || (gate && $can(gate, 'edit'))"
            class="primary-btn"
            :text="$t('setting.sv')"
            :fetching-data="updatingStore"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
  import {useSettingStore} from "~/store/setting";
  import {useLanguageStore} from "~/store/language";
  import {useIndexStore} from "~/store/index";
  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";
  import {useConstants} from "~/composables/useConstants";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const indexStore = useIndexStore();
  const {mediaStorage} = storeToRefs(indexStore)

  const {updateStoreData} = useSettingStore();
  const languageStore = useLanguageStore();
  const {currentLanguage} = storeToRefs(languageStore)

  const {getRequest, setRequest} = useCommonStore();

  const {mediaStorageData} = useConstants();
  const {convertToSlug} = useUtils();

  const gate = ref('store');
  const hasError = ref(false);
  const gettingStore = ref(false);
  const updatingStore = ref(false);
  const fileUploading = ref(false);
  const result = ref({
    id: '',
    image: '',
    name: '',
    slug: '',
    meta_description: '',
    meta_title: '',
  });

  const slugChange = (evt, title = 'title') => {
    if (currentLanguage.value.code === 'en') {
      result.value.slug = convertToSlug(result.value[title])
    }
  };

  const uploadFile = async (file, name = null) => {
    fileUploading.value = true;
    let params = {};
    if (file) {
      const fd = new FormData();
      fd.append('photo', file);
      params = fd;
    } else {
      params['photo'] = name;
    }
    const data = await setRequest({params: params, api: 'setStoreLogo'});

    if (data) {
      result.value = data;
      updateStoreData(data);
    }
    fileUploading.value = false;
  };

  const updateStore = async () => {
    if (result.value.name && result.value.slug) {
      hasError.value = false;
      updatingStore.value = true;

      const data = await setRequest({params: result.value, api: 'setStore'});
      if (data) {
        result.value = data;
      }
      updatingStore.value = false;
    } else {
      hasError.value = true;
    }
  };

  onMounted(async () => {
    gettingStore.value = true
    const data = await getRequest({params: {}, api: 'getStore'});
    if (data) {
      result.value = data;
    }
    gettingStore.value = false;
  });
</script>
