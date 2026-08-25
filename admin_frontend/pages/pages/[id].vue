<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setPage"
    get-api="getPage"
    route-name="pages"
    gate="page"
    empty-store-variable="allPages"
    :name="$t('dataPage.page')"
    :validation-keys="['title', 'slug',  'meta_title', 'meta_description']"
    :result="result"
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
      <div class="input-wrapper">
        <label>{{ $t('index.title') }}</label>
        <input
          type="text"
          :placeholder="$t('index.title')"
          v-model="result.title"
          @change="slugChange"
          :class="{invalid: !!!result.title && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.title && hasError"
        >
          {{ $t('category.req', { type: $t('index.title')}) }}
        </span>
      </div>

      <div class="input-wrapper">
        <label>{{ $t('category.slug') }}</label>
        <input
          type="text"
          :placeholder="$t('category.slug')"
          v-model="result.slug"
          :class="{invalid: !!!result.slug && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.slug && hasError"
        >
          {{ $t('category.req', { type: $t('category.slug')}) }}
        </span>
      </div>

      <div class="input-wrapper">
        <div class="flex-v-centered mb j-left">
          <span class="mr-15">{{ $t('admin.pageComp') }}</span>
          <dropdown
            :selectedKey="result.page_from_component"
            :options="featuredObj"
            @clicked="pageFromComponentSelected"
          />
        </div>
      </div>

      <div
        v-if="isPageFromComponent"
        class="input-wrapper">
        <div class="flex-v-centered mb j-left">
          <span class="mr-15">{{ $t('admin.comp') }}</span>
          <dropdown
            :selectedKey="result.description"
            :options="pageComponent"
            @clicked="result.description = $event.key"
          />
        </div>
      </div>

      <WYSIWYGEditor
        v-else
        :title="$t('prod.desc')"
        :description="result.description"
        @change="result.description = $event"
        @file="editorDescriptionFile"
      />

      <div class="input-wrapper mt-15">
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
        />
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
  import {storeToRefs} from "pinia";
  import {useLanguageStore} from "~/store/language";
  import {useCommonStore} from "~/store/common";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const languageStore = useLanguageStore();
  const {currentLanguage} = storeToRefs(languageStore);

  const commonStore = useCommonStore();
  const {setRequest, deleteData} = commonStore;

  const loading = ref(false);
  const oldPageDescription = ref('');
  const result = ref({
    id: '',
    title: '',
    slug: '',
    meta_title: '',
    description: '',
    meta_description: '',
    meta_keywords: '',
    page_from_component: ''
  });

  const {status, pageComponent, featuredObj} = useConstants();

  const isPageFromComponent = computed(() => {
    return parseInt(result.value?.page_from_component) !== status.PRIVATE
  });

  const pageFromComponentSelected = (data) => {
    result.value.page_from_component = data.key;

    if (isPageFromComponent.value) {
      oldPageDescription.value = result.value.description;
      result.value.description = Object.keys(pageComponent)[0];
    } else if (oldPageDescription.value) {
      result.value.description = oldPageDescription.value;
    }
  };

  const editorDescriptionFile = ({deleted, file, Editor, cursorLocation, resetUploader}) => {
    editorFile({deleted, file, Editor, cursorLocation, resetUploader});
  };

  const router = useRouter();

  const editorFile = async ({deleted, file, Editor, cursorLocation, resetUploader}) => {
    if (!deleted) {
      loading.value = true;
      const fd = new FormData();
      if (!result.value.id) {
        fd.append('page', JSON.stringify(result.value));
      } else {
        fd.append('description', result.value.description);
        fd.append('page_id', result.value.id);
      }
      fd.append('photo', file);
      const data = await setRequest({params: fd, api: 'setPageWysiwygImage'});

      if (data) {
        if (!result.value.id) {
          await router.push({path: `/pages/${data.page_id}`});
        } else {
          Editor.insertEmbed(cursorLocation, "image", data.url);
          Editor.setSelection(cursorLocation + 1);
        }
      }
      loading.value = false;

    } else {
      loading.value = true;
      await deleteData({params: getImageName(file), api: 'deletePageWysiwygImage'});
      loading.value = false;
    }
  };

</script>
