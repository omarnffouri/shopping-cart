<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setBanner"
    get-api="getBanner"
    set-image-api="uploadBanner"
    route-name="banners"
    :name="$t('profile.banner')"
    :validation-keys="['title', 'slug']"
    :file-keys="['id', 'type', 'source_type']"
    :result="result"
    gate="banner"
    @result="onSuccess"
  >
    <template v-slot:form="{hasError}">

      <div class="input-wrapper single-line">
        <label>{{ $t('profile.ui') }}: </label><b>{{ bannerUsed[result.type] }}</b>
      </div>

      <div class="input-wrapper">
        <label>{{ $t('index.title') }}</label>
        <input
          type="text"
          :placeholder="$t('index.title')"
          name="title"
          @change="slugChange"
          v-model="result.title"
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
          name="slug"
          v-model="result.slug"
          ref="slug"
          :class="{invalid: !!!result.slug && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.slug && hasError"
        >
          {{ $t('category.req', { type: $t('category.slug')}) }}
        </span>
      </div>

      <div class="input-wrapper single-line">
        <label>{{ $t('admin.sType') }}</label>
        <dropdown
          :selectedKey="result.source_type"
          :options="productSourceObj"
          @clicked="dropdownSelected"
        />
      </div>
      <PartialsProductSource
        v-if="allCategories && parseInt(result.source_type) === productSources.category"
        :title="$t('category.cat')"
        type="category"
        :items="result.source_categories"
        :all-data="allCategories"
        @itemSelected="itemSelected('source_categories', 'category', $event)"
        @removeItem="removeItem('source_categories', $event)"
        @addItem="addItem('source_categories', 'category', allCategories)"
      />
      <PartialsProductSource
        v-if="allSubCategories && parseInt(result.source_type) === productSources.sub_category"
        :title="$t('category.subCat')"
        type="sub_category"
        :items="sourceSubCategories"
        :all-data="allSubCategories"
        @itemSelected="itemSelected('source_sub_categories', 'sub_category', $event)"
        @removeItem="removeItem('source_sub_categories', $event)"
        @addItem="addItem('source_sub_categories', 'sub_category', allSubCategories)"
      />
      <PartialsProductSource
        v-if="allBrands && parseInt(result.source_type) === productSources.brand"
        :title="$t('brand.brand')"
        type="brand"
        :items="result.source_brands"
        :all-data="allBrands"
        @itemSelected="itemSelected('source_brands', 'brand', $event)"
        @removeItem="removeItem('source_brands', $event)"
        @addItem="addItem('source_brands', 'brand', allBrands)"
      />
      <div
        v-if="parseInt(result.source_type) === productSources.tag"
        class="input-wrapper mb-20 mb-sm-15"
      >
        <label>{{ $t('admin.tags') }}</label>
        <tag-search
          @add="addTag"
          @delete="deleteTag"
          :tags="result.tags"
        />
      </div>

      <PartialsSourceTypeProducts
        v-if="parseInt(result.source_type) === productSources.product"
        ref="sourceTypeProductsRef"
        :source-products="sourceProducts"
        @product-clicked="addProduct"
        @delete-product="deleteProduct"
        @undo-delete="undoDelete"
      />

      <div
        v-if="parseInt(result.source_type) === productSources.url"
        class="input-wrapper mb-20 mb-sm-15"
      >

        <p class="info-msg mb-20 mb-sm-15">{{ $t('admin.srcUrl') }} jet-set-hydratiream/product/88630128</p>

        <label>{{ $t('admin.url') }}</label>
        <input
          :placeholder="$t('admin.url')"
          v-model="result.url"
        >
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

      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('admin.closable') }}
          </span>

          <dropdown
            :selectedKey="`${result.closable}`"
            :options="featuredObj"
            @clicked="closableSelected"
          />
        </div>
      </div>


    </template>
  </PartialsDataPage>
</template>

<script setup>

  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {useSettingStore} from "~/store/setting";
  import {useLanguageStore} from "~/store/language";
  import {onMounted} from "vue";
  import {useIndexStore} from "~/store/index";
  import {useConstants} from "~/composables/useConstants";

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

  const {defaultImage} = storeToRefs(useIndexStore());

  const {productSources, productSourceObj, statusObj, bannerUsed, featuredObj} = useConstants();
  const route = useRoute();

  const loading = ref(false);
  const result = ref({
    id: '',
    source_products: [],
    source_brands: [],
    source_categories: [],
    source_sub_categories: [],
    image: defaultImage.value,
    source_type: 1,
    tags: '',
    url: '',
    slug: '',
    title: '',
    status: '',
    closable: '',
    type: parseInt(route?.query?.type) || 1
  });

  const sourceProducts = computed(() => {
    return result.value?.source_products || []
  });

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$';
  });

  const sourceSubCategories = computed(() => {
    return result.value?.source_sub_categories
  });

  const undoDelete = (index) => {
    const v = {
      ...result.value.source_products[index],
      ...{deleted: false}
    }
    result.value.source_products.splice(index, 1)
    result.value.source_products.splice(index, 0, v)
  };

  const deleteProduct = (index) => {
    const v = {
      ...result.value.source_products[index],
      ...{deleted: true}
    }
    result.value.source_products.splice(index, 1)
    result.value.source_products.splice(index, 0, v)
  };

  const sourceTypeProductsRef = ref(null);
  const addProduct = (product) => {
    if (result.value?.source_products?.findIndex((o) => {
      return o.product.id === product.id
    }) || -1 === -1) {

      result.value?.source_products?.push({
        product: {
          id: product.id,
          title: product.title,
          image: product.image,
          offered: product.offered,
          selling: product.selling
        }
      })
    }
    sourceTypeProductsRef.value.autoSuggestionClose()
  };

  const onSuccess = (event) => {
    result.value = Object.assign(result.value, event)
  };

  const itemSelected = (source, type, {index, value}) => {
    if (result.value[source][index]?.id) {
      result.value[source][index] = {...result.value[source][index], ...{updated: true}}
    }
    const v = {...result.value[source][index], ...{[type]: {id: value}}}
    result.value[source].splice(index, 1)
    result.value[source].splice(index, 0, v)
  };

  const removeItem = (source, {index, deleted}) => {
    const v = {
      ...result.value[source][index],
      ...{deleted: deleted}
    }
    result.value[source].splice(index, 1)
    result.value[source].splice(index, 0, v)
  };

  const addItem = (source, type, allData) => {
    result.value[source].push({[type]: {id: Object.keys(allData)[0]}})
  };

  const addTag = (tag) => {
    if (!result.value.tags) {
      result.value.tags = ','
    }
    result.value.tags = `${result.value.tags}${tag},`
  };

  const deleteTag = (tag) => {
    result.value.tags = result.value.tags.replace(`${tag},`, '')
  };

  const closableSelected = (data) => {
    result.value.closable = data.key
  };

  const statusSelected = (data) => {
    result.value.status = data.key
  };

  const dropdownSelected = (data) => {
    result.value.source_type = data.key
  };

  onMounted(async () => {
    loading.value = true
    if (!allCategories.value || !allSubCategories.value || !allBrands.value) {
      await getDropdownList()
    }
    loading.value = false
  });
</script>
