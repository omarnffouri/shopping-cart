<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setSiteFeature"
    get-api="getSiteFeature"
    set-image-api="uploadSiteFeatureImage"
    route-name="site-features"
    :name="$t('title.sf')"
    :validation-keys="['detail']"
    :file-keys="['id', 'detail']"
    :result="result"
    gate="home_slider"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">
      <div
        v-if="loading"
        class="spinner-wrapper"
      >
        <spinner
          :radius="70"
          color="primary"
        />
      </div>


      <WYSIWYGEditor
        class="mb-20"
        :title="$t('prod.desc')"
        :description="result.detail"
        @change="result.detail = $event"
        @file="editorDescriptionFile"
      />

      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('category.status') }}
          </span>

          <dropdown
            :selectedKey="`${result.status}`"
            :options="statusObj"
            @clicked="dropdownSelected"
          />
        </div>
      </div>


    </template>
  </PartialsDataPage>
</template>

<script setup>
  import {useLanguageStore} from '~/store/language';
  import {useCommonStore} from '~/store/common';
  import {storeToRefs} from "pinia";
  import {useConstants} from "~/composables/useConstants";
  import {useIndexStore} from "~/store/index";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const languageStore = useLanguageStore();
  const {currentLanguage} = storeToRefs(languageStore);
  const {setRequest, deleteData} = useCommonStore();
  const {defaultImage} = storeToRefs(useIndexStore());
  const {statusObj, featuredObj} = useConstants();

  const loading = ref(false);
  const result = ref({
    id: '',
    image: defaultImage.value,
    status: '',
    detail: '',
  });

  const editorDescriptionFile = ({deleted, file, Editor, cursorLocation, resetUploader}) => {
    editorFile({deleted, file, Editor, cursorLocation, resetUploader})
  };

  const router = useRouter();

  const editorFile = async ({deleted, file, Editor, cursorLocation, resetUploader}) => {
    if (!deleted) {
      loading.value = true

      const fd = new FormData()
      if (!result.value.id) {
        fd.append('site_feature', JSON.stringify(result.value))
      } else {
        fd.append('detail', result.value.detail)
        fd.append('site_feature_id', result.value.id)
      }
      fd.append('photo', file)
      const data = await setRequest({params: fd, api: 'setFeatureWysiwygImage'})

      if (data) {
        if (!result.value.id) {
          await router.push({path: `/site-features/${data.site_feature_id}`})
        } else {
          Editor.insertEmbed(cursorLocation, "image", data.url);
          Editor.setSelection(cursorLocation + 1);
        }
      }
      loading.value = false

    } else {
      loading.value = true
      await deleteData({params: getImageName(file), api: 'deleteFeatureWysiwygImage'})
      loading.value = false
    }
  };

  const dropdownSelected = (data) => {
    result.value.status = data.key
  };

</script>
