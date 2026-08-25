<template>
  <div class="mx-w-600x mlr-auto">

    <div class="mb-20 card p-20">
      <h4>{{ $t('title.epd') }}</h4>
      <p class="info-msg mtb-15">{{ $t('title.csvDn') }}</p>
      <ajax-button
        class="primary-btn"
        type="button"
        :text="$t('title.ex')"
        loading-text=""
        :fetching-data="exporting"
        @clicked="exportData"
      />
    </div>

    <error-formatter/>

    <div class="mb-20 card p-20 file-wrap"
         :class="{'has-error': uploadMessage}"
    >
      <h4>{{ $t('title.fti') }}</h4>
      <p class="info-msg mtb-15">{{ $t('title.csvUp') }}</p>
      <p class="info-msg mb-15">{{ $t('title.upHelp') }} {{ $t('title.lngHelp') }}</p>

      <input
        class="mb-15 file-input"
        type="file"
        accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        ref="fileInput"
        @change="fileChanged"
      >

      <span
        v-if="uploadMessage"
        class="error mb-15"
      >
        {{ uploadMessage }}
      </span>

      <ajax-button
        class="primary-btn"
        type="button"
        :text="$t('title.im')"
        loading-text=""
        :fetching-data="importing"
        @clicked="importData"
      />
    </div>

    <div class="mb-20 card p-20">
      <div class="dply-felx gap-15 sided">

        <h4>{{ $t('title.bulkImg') }}</h4>

        <nuxt-link to="/images" target="_blank" class="link">{{ $t('title.vi') }}</nuxt-link>
      </div>
      <p class="info-msg mtb-15">{{ $t('title.imgMsg') }}</p>

      <error-formatter
        type="multiple_image"
      />

      <input
        class="mb-15 file-input"
        type="file"
        accept="image/*"
        ref="fileInput"
        multiple
        @change="imageChanged"
      >
      <ajax-button
        class="primary-btn"
        type="button"
        :text="$t('title.bulkImgBtn')"
        loading-text=""
        :fetching-data="uploading"
        @clicked="bulkImage"
      />
    </div>

  </div>
</template>

<script setup>

  import {useUiStore} from "~/store/ui";
  import {useCommonStore} from "~/store/common";
  import {useValidationHelper} from "~/composables/useValidationHelper";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {downloadRequest, setRequest} = useCommonStore();
  const {setErrors} = useUiStore();

  const hasError = ref(null);
  const params = ref(null);
  const uploadMessage = ref(null);
  const file = ref(null);
  const uploading = ref(false);
  const importing = ref(false);
  const exporting = ref(false);

  const {isValidImage, isValidExcel} = useValidationHelper();

  const exportData = async () => {
    exporting.value = true
    await downloadRequest({params: {}, api: 'bulkExport'})
    exporting.value = false
  };

  const imageChanged = async (event) => {
    setErrors()
    const files = event.target.files
    let errors = [];
    const fd = new FormData()
    Object.values(files).forEach((item) => {

      const error = isValidImage(item)
      if (error) {
        errors.push(error)
        return false
      } else {
        fd.append('images[]', item)
      }
    })
    if (!errors.length) {
      params.value = fd
    } else {
      setErrors({multiple_image: errors})
    }
  };

  const fileChanged = (event) => {
    const file = event.target.files[0]
    if (file) {
      uploadMessage.value = isValidExcel(file)
      if (!uploadMessage.value) {
        file.value = file
      }
    }
  };

  const bulkImage = async () => {
    if (params.value) {
      setErrors()
      uploading.value = true

      await setRequest({
        params: params.value,
        api: 'imgUpload'
      })

      params.value = null
      uploading.value = false
    }
  };

  const importData = async () => {
    if (file.value) {
      setErrors()

      const fd = new FormData()
      fd.append('file', file.value)
      importing.value = true
      await setRequest({params: fd, api: 'bulkImport'})
      file.value = null
      importing.value = false
    }
  };

</script>
