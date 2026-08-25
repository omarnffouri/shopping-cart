<template>
  <div>
    <product-list
      v-if="category"
      :result-title="categoryTitle"
      :has-breadcrumb="true"
      :categories="allCategories"
      :fetching-product-data="fetchingProductData"
      @fetch-data="fetchingData"
    />
  </div>
</template>

<script setup>
  import {useCommonStore} from "~/store/common";
  import {useListingStore} from "~/store/listing";
  import {useLanguageStore} from "~/store/language";
  import {storeToRefs} from "pinia";
  import {prepareGetUrl} from "~/utils/fetchClient";
  import {useAsyncData} from "nuxt/app";
  import {computed} from "vue";
  import {useUtils} from "~/composables/useUtils";
  import {useMetaData} from "~/composables/useMetaData";

  definePageMeta({
    middleware: ['common-middleware'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {customScripts} = storeToRefs(commonStore);
  const {unAuthGet} = commonStore;

  const listingStore = useListingStore();
  const {brands, shippingRules, collections, categoryData, allCategories} = storeToRefs(listingStore);
  const {emptyProducts, setProducts} = listingStore;

  const languageStore = useLanguageStore();
  const {langCode} = storeToRefs(languageStore);
  const route = useRoute();

  const categoryKey = `category-${route.params.category}}`;
  const pageNumber = ref(1)
  const appendData = ref(false)

  const fetchCategoryData = async () => {
    return await unAuthGet({
      api: 'all',
      params: `?${prepareGetUrl({
        category: route?.params?.category,
        sortby: route.query.sortby || '',
        shipping: route.query.shipping || '',
        brand: route.query.brand || '',
        collection: route.query.collection || '',
        rating: route.query.rating || 0,
        max: route?.query?.max || 0,
        min: route?.query?.min || 0,
        page: pageNumber.value,
        sidebar_data: !brands.value || !shippingRules.value || !collections.value || !allCategories.value
      })}`,
      lang: langCode.value
    });
  };

  const {data: category, pending, error} = await useAsyncData(categoryKey, async () => {
    const result = await fetchCategoryData();
    setProducts(result)
    return result.data.category;
  });

  const {pageMeta} = useMetaData();
  const {getImageURL} = useUtils();

  watch(() => route.query, () => {
    fetchingData();
  });

  useHead(pageMeta({
    meta_title: category.value?.meta_title,
    meta_description: category.value?.meta_description,
    meta_keywords: category.value?.meta_keywords,
    image_url: getImageURL(category.value?.image)
  }));

  const categoryTitle = computed(() => {
    return category.value.title;
  });

  const fetchingProductData = ref(false);

  const fetchingData = async ({page = 1, append = false, done} = {}) => {
    pageNumber.value = page;
    appendData.value = append;

    fetchingProductData.value = true;

    if (route.params.category !== category.value.slug) {
      fetchingProductData.value = false;
      return false;
    }

    if (!append) {
      emptyProducts();
    }

    const result = await fetchCategoryData();
    setProducts(result, appendData.value);

    fetchingProductData.value = false;
    done?.();
  };

</script>
