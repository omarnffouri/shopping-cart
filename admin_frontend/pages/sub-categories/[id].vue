<template>
  <data-page
    v-if="allCategories && $can('subcategory', 'view')"
    ref="dataPage"
    set-api="setSubCategory"
    get-api="getSubCategory"
    set-image-api="setSubCategoryImage"
    empty-store-variable="allSubCategories"
    route-name="sub-categories"
    :name="$t('prod.subCat')"
    gate="subcategory"
    :validation-keys="['title', 'slug', 'meta_title', 'meta_description']"
    :file-keys="['id', 'status', 'featured', 'category_id']"
    :result="result"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">
      <div class="input-wrapper">

        <label>{{ $t('index.title') }}</label>
        <input
          type="text"
          :placeholder="$t('index.title')"
          v-model="result.title"
          ref="title"
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
          ref="title"
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
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">{{$t('category.catUp')}}</span>
          <dropdown
            :selectedKey="`${result.category_id}`"
            :options="allCategories"
            @clicked="categorySelected"/>
        </div>
      </div>

      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
            <span class="mr-15">
              {{$t('category.featured')}}
            </span>

          <dropdown
            :selectedKey="`${result.featured}`"
            :options="featuredObj"
            @clicked="featuredSelected"
          />
        </div>
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
        <div class="dply-felx j-left mb-20 mb-sm-15">
            <span class="mr-15">
              {{$t('category.status')}}
            </span>

          <dropdown
            :selectedKey="`${result.status}`"
            :options="statusObj"
            @clicked="dropdownSelected"
          />
        </div>
      </div>

    </template>
  </data-page>
</template>
<script>

  import DataPage from '~/components/partials/DataPage'
  import util from '~/mixin/util'
  import Dropdown from '~/components/Dropdown'
  import {useCommonStore} from "../../store/common";
  import {useLanguageStore} from "../../store/language";
  import {showError} from "nuxt/app";



  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  })

  export default {
    setup(){
      const commonStore = useCommonStore()
      const {getAllList} = commonStore
      const {allCategories} = storeToRefs(commonStore)

      const languageStore = useLanguageStore()
      const {currentLanguage} = storeToRefs(languageStore)

      return {currentLanguage, getAllList, allCategories}
    },
    name: "sub-categories",
    data() {
      return {
        result: {
          id: '',
          title: '',
          featured: 2,
          category_id: '',
          status: 2,
          slug: '',
          meta_description: '',
          meta_title: '',
          image: ''
        }
      }
    },
    mixins: [util],
    components: {
      DataPage,
      Dropdown
    },
    computed: {
    },
    methods: {
      titleChanged() {
        this.result.slug = this.convertToSlug(this.result.title)
      },
      categorySelected(data) {
        this.result.category_id = data.key
      },
      featuredSelected(data) {
        this.result.featured = data.key
      },
      dropdownSelected(data) {
        this.result.status = data.key
      },
    },
    async mounted() {
      if (!this.allCategories) {
        try {
          await this.getAllList({api: 'getAllCategories', action: 'setAllCategories'})
        } catch (e) {
          showError({
            statusCode: 400,
            message: e.message
          })
        }
      }
    }
  }
</script>

<style scoped>

</style>
