<template>
  <div>
    <div class="detail-menu">
      <div class="container-fluid">
        <div class="list-heading flex sided">
          <div class="flex gap-5 list-heading-filter">
            <span class="filter-hover-text" @click="openFilter">
                  {{ $t('listingLayout.filter') }}
                  <span v-if="filteredCount">({{ filteredCount }})</span>
                  <i class="icon arrow-down"></i>
            </span>
          </div>
          <div class="flex gap-5 list-heading-sort">
            <span class="hide-sm">
              {{ $t('listingLayout.sortBy') }}
            </span>
            <client-only>
              <dropdown
                class="sort-dropdown"
                :options="sortingOptions"
                :selected-key="sortBy"
                @clicked="selectedSorting"
              />
            </client-only>
          </div>
        </div>
      </div>
    </div>
    <!-- TOP FILTER DROPDOWN -->
    <div v-show="filterPopup" class="top-filter-wrapper">
      <div class="filter-overlay" @click="closeFilter"></div>

      <div class="top-filter-panel">
        <div class="filter-header flex sided">
          <h4>{{ $t('listingLayout.filter') }}</h4>
          <button class="clear-btn" @click="closeFilter">✕</button>
        </div>

        <div class="filter-body-horizontal">

          <div class="filter-section full-width">
            <filter-price ref="filterPriceRef" @reset-route="changeRoute"/>
          </div>

          <!-- Horizontal filters -->
          <div class="filter-row">
            <!--<div class="filter-section" v-if="brands && brands.length">-->
            <!--  <filter-brand ref="filterBrand" :brands="brands" @reset-route="changeRoute"/>-->
            <!--</div>-->

            <div class="filter-section" v-if="categories && categories.length">
              <filter-category ref="filterCategory" :categories="categories" @going-next="goingNext"/>
            </div>

            <!--<div class="filter-section">-->
            <!--  <filter-shipping ref="filterShippingRef" :shipping-rules="shippingRules" @reset-route="changeRoute"/>-->
            <!--</div>-->

            <div class="filter-section" v-if="collections && collections.length">
              <filter-collection ref="filterCollection" :collections="collections" @reset-route="changeRoute"/>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid mtb-20 mtb-sm-15">
      <div class="product-list">
        <div class="main-content">
          <breadcrumb
            v-if="hasBreadcrumb"
            class="mb-15 mt-0"
            :page="resultTitle"
            :slugs="slugs"
          />
          <div class="pos-rel">
            <div class="tile-container">
              <template v-if="currentItems.length">
                <product-tile
                  v-for="(value, index) in currentItems"
                  :key="value?.id ?? index"
                  :product="value"
                />

                <div ref="infiniteSentinel" class="infinite-sentinel"></div>

                <div class="mt-20" v-if="isLoadingMore">Loading more...</div>
              </template>
              <div class="shimmer-wrapper">
                <tile-shimmer v-if="fetchingProductData" v-for="index in shimmerTotal" :key="index"/>
              </div>
            </div>
            <div v-if="(currentItems && !currentItems.length)" class="info-msg">
              {{ $t('listingLayout.noProductFound') }}
            </div>

            <!--<pagination class="mt-30" ref="productPaginationRef" :total-page="totalPage"/>-->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useListingStore} from "~/store/listing";
import {storeToRefs} from "pinia";
import {computed, nextTick, onMounted, onUnmounted} from "vue";

const props = defineProps({
  categories: {
    type: Array,
    default: []
  },
  backBtn: {
    type: Boolean,
    default: true
  },

  hasBreadcrumb: {
    type: Boolean,
    default: false
  },
  slugs: {
    type: Array,
    default() {
      return []
    }
  },
  fetchingProductData: {
    type: Boolean,
    default: false
  },
  productParams: {
    type: Object,
    default() {
      return {}
    }
  },
  resultTitle: {
    type: String,
    default: ''
  },
});

const emit = defineEmits(['fetch-data']);

const listingStore = useListingStore();
const {products, brands, shippingRules, collections, sortByType, sortBy, search} = storeToRefs(listingStore);

const route = useRoute();
const router = useRouter();
const {t} = useI18n();

const loaded = ref(false);
const filterPopup = ref(false);
const sortingOptions = ref({
  featured: {title: t('featured.featured')},
  price_low_to_high: {title: t('listingLayout.priceLowToHigh')},
  price_high_to_low: {title: t('listingLayout.priceHighToLow')},
  //avg_customer_review: {title: t('listingLayout.avgCustomerReview')},
});

const filteredCount = computed(() => {
  let count = 0
  if (route.query?.shipping) {
    count++
  }
  if (route.query?.rating) {
    count++
  }
  if (route.query?.min) {
    count++
  }
  if (route.query?.max) {
    count++
  }
  return count
});

const isXsDevice = computed(() => window && window.innerWidth <= 576);

const pageHeading = computed(() => {
  if (products.value) {
    if (products.value?.total > 0) {
      return t('listingLayout.paginationResult', {
        from: products.value?.from,
        to: products.value?.to,
        total: products.value?.total
      })
    }

    //return t('listingLayout.noProductFound')
  }
  return t('listingLayout.showingResult')
  //return `${t('listingLayout.loading')}...`
});

const currentItems = computed(() => products.value?.data || []);
const totalPage = computed(() => products.value?.last_page || 1);
const shimmerTotal = computed(() => {
  const total = Number(products.value?.total ?? 0);
  const to = Number(products.value?.to ?? 0);
  const remain = total - to;
  return remain > 0 ? Math.min(remain, 20) : 0; // cap to 20 shimmers
});
const goingBack = () => {
  router.go(-1)
};

const openFilter = () => {
  filterPopup.value = true
  document.body.classList.add('no-scroll')
}
const closeFilter = () => {
  filterPopup.value = false
  document.body.classList.remove('no-scroll')
};

const productPaginationRef = ref(null);

const selectedSorting = (data) => {
  if (sortBy.value) {
    let filtered = Object.assign({}, route.query);
    filtered = {...filtered, ...{sortby: data.key}};

    //productPaginationRef.value?.resettingRoute(filtered);
    changeRoute(filtered)
  }
  sortBy.value = data.key;
};

const clearSortby = () => {
  sortBy.value = 'featured'
};

const filterPriceRef = ref(null);
const filterShippingRef = ref(null);

const clearQuery = () => {
  filterPriceRef.value?.clearPrice()
  filterShippingRef.value?.clearShipping()
  clearSortby()
  //filterRatingRef.value?.clearRating()
  if (isXsDevice.value) {
    closeFilter();
  }
};

const changeRoute = (evt) => {
  router.push({
    query: {
      ...evt,
      ...{
        orderBy: route.query.orderBy || sortBy.value,
        orderByType: route.query.orderByType || sortByType.value ,
        q: route.query.q || search.value
      }
    }
  })
  fetchingData();
};

const goingNext = (url) => {
  clearQuery()
  if (url === route.path) {
    router.push({query: {}})
    resetInfinite();
    fetchingData();
  } else {
    router.push({path: url})
  }
};

const fetchingData = () => {
  // this.settingRouteParam()
  window.scrollTo(0, 0)
  //emit('fetch-data')
};

onUnmounted(() => {
  io?.disconnect();
  if (isXsDevice.value && filterPopup.value) closeFilter();
});

onMounted(async () => {
  document.body.classList.remove('no-scroll')
  await nextTick();
  initObserver();

  if (isXsDevice.value && filterPopup.value) closeFilter();
});

const infiniteSentinel = ref(null);
const page = ref(1);

const isLoadingMore = ref(false);
const isEndReached = ref(false);

let io = null;
const initObserver = () => {
  if (!infiniteSentinel.value) return;

  io?.disconnect();
  io = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) loadNextPage();
    },
    // smaller margin to reduce eager retriggers
    { root: null, rootMargin: "0px 0px 150px 0px", threshold: 0 }
  );

  io.observe(infiniteSentinel.value);
};

const loadNextPage = async () => {
  if (isLoadingMore.value || isEndReached.value) return;

  const next = page.value + 1;
  if (next > totalPage.value) {
    isEndReached.value = true;
    return;
  }

  isLoadingMore.value = true;

  // stop retrigger while loading
  if (io && infiniteSentinel.value) io.unobserve(infiniteSentinel.value);

  emit("fetch-data", {
    page: next,
    append: true,
    done: async () => {
      page.value = next;
      if (page.value >= totalPage.value) isEndReached.value = true;

      await nextTick();

      // re-observe after DOM updated
      if (io && infiniteSentinel.value) io.observe(infiniteSentinel.value);

      isLoadingMore.value = false;
    },
  });
};

const resetInfinite = async () => {
  page.value = 1;
  isEndReached.value = false;

  io?.disconnect();

  isLoadingMore.value = true;

  emit("fetch-data", {
    page: 1,
    append: false,
    done: async () => {
      await nextTick();
      initObserver();
      isLoadingMore.value = false;
    },
  });
};

watch(
  () => infiniteSentinel.value,
  async (el) => {
    if (!el) return;
    if (isLoadingMore.value) return; // don't reconnect mid-load
    await nextTick();
    initObserver();
  },
  { flush: "post" }
);
</script>

<style scoped>
.infinite-sentinel {
  height: 1px;
}
.filter-body-horizontal {
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

/* Categories full width */
.full-width {
  width: 100%;
}

/* Horizontal row */
.filter-row {
  display: flex;
  gap: 20px;
  padding-bottom: 10px;
}

/* Each filter card */
.filter-section {
  min-width: 240px;
  background: #fafafa;
  border-radius: 10px;
  padding: 12px;
  flex-shrink: 0;
}

/* Smooth horizontal scroll */
.filter-row::-webkit-scrollbar {
  height: 6px;
}
.filter-row::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 10px;
}

.filter-hover-text {
  cursor: pointer;
  display: inline-flex;
  gap: 6px;
  color: #c8a330;
  font-weight: bold;
}

.top-filter-wrapper {
  position: fixed;
  inset: 0;
  z-index: 999;
}

.filter-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
}

/* For the default styling of child components */
.filter-inline > * {
  min-width: auto !important;
  flex-shrink: 0;
}

.top-filter-panel {
  position: absolute;
  top: 70px;
  left: 50%;
  transform: translateX(-50%);
  width: 90%;
  max-width: 1200px;
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.filter-block {
  margin-bottom: 24px;
}

.filter-title {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
}

.filter-inline {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.filter-pill {
  padding: 8px 14px;
  background: #f5f5f5;
  border-radius: 20px;
  font-size: 13px;
  cursor: pointer;
  transition: 0.2s;
}

.filter-pill:hover {
  background: var(--primary-color);
  color: #fff;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  .top-filter-panel {
    top: 0;
    left: 0;
    transform: none;
    width: 100%;
    max-width: 100%;
    height: 100%;
    border-radius: 0;
    padding: 16px;
    overflow-y: auto;
  }

  .filter-body-horizontal {
    max-height: calc(100vh - 80px);
    gap: 16px;
  }

  .filter-row {
    flex-direction: column;
    gap: 12px;
    padding-bottom: 0;
  }

  .filter-section {
    min-width: 100%;
    width: 100%;
  }

  .filter-header {
    margin-bottom: 16px;
  }

  .clear-btn {
    font-size: 24px;
  }
}

@media (max-width: 576px) {
    .list-heading {
        display: flex;
        align-items: center;
    }

    .list-heading > .flex {
        width: auto !important;
        justify-content: flex-start !important;
    }

    .list-heading-sort {
        order: 1;
    }

    .list-heading-filter {
        order: 2;
        margin-left: auto;
        justify-content: flex-end !important;
    }

    .top-filter-panel {
        padding: 12px;
    }

  .filter-body-horizontal {
    gap: 12px;
  }

  .filter-section {
    padding: 10px;
  }
}
</style>
