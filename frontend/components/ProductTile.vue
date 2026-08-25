<template>
  <div class="p-tile">
    <nuxt-link
      :title="product.title"
      :to="productLink(product)"
      draggable="false"
      class="page-link"
      @click="goToProduct"
    >
      <span class="block img-wrapper">
        <span
          v-if="badge"
          class="badge"
        >
          {{ badge }}
        </span>

        <ImageLazy
          v-if="isLazyImage"
          :lazy-src="getThumbImageURL(product.image)"
          :title="product.title"
          :alt="product.title"
        />
        <img
          v-else
          :src="getThumbImageURL(product.image)"
          :title="product.title"
          :alt="product.title"
          height="50"
          width="50"
        >
      </span>

      <div class="item-title">
        <h5
          class="ellipsis"
          :class="`ellipsis-${titleEllipsis}`"
        >
          {{product.title}}
        </h5>
<!--        <span class="block mtb-5">-->
<!--          <rating-star-->
<!--            :rating="parseFloat(product.rating)"-->
<!--          />-->
<!--          <span class="f-10 ml-5 semi-bold color-lite">-->
<!--            {{ $t('productReview.reviews', {count: product.review_count}) }}</span>-->
<!--        </span>-->
        <span
          class="flex wrap start"
          :class="{'has-delivery': isAvailableForDeliveryToday}"
        >
          <h4 class="price-wrapper">
            <span
              class="strike-through"
              v-if="prevPrice"
            >
              <price-format
                :price="prevPrice"
              />
            </span>
            <span class="price">
              <price-format
                :price="currentPricing"
              />
            </span>
          </h4>
          <span
            v-if="reducedPercent"
            class="discount ml-10"
          >
            -{{reducedPercent}}%</span>
        </span>
        <div class="tile-delivery" v-if="isAvailableForDeliveryToday">
          <span style="font-weight: bolder">{{ t('cartProductTile.earliestDelivery') || 'Earliest Delivery:' }}</span>
          <span class="delivery-date">{{ t('cartProductTile.today') || 'Today' }}</span>
        </div>
      </div>
    </nuxt-link>
  </div>
</template>

<script setup>
  import {useCompareHelper} from "~/composables/useCompareHelper";
  import {toRefs} from "vue";
  import {usePriceHelper} from "~/composables/usePriceHelper";
  import {useUtils} from "~/composables/useUtils";
  import {useDetailStore} from "../store/detail";

  const props = defineProps({
    product: {
      type: Object,
      default() {
        return null
      },
    },
    isLazyImage: {
      type: Boolean,
      default: true
    },
    compared: {
      type: Boolean,
      default: false
    },
    titleEllipsis: {
      type: Number,
      default: 2
    },
  });

  const {product, isLazyImage, compared, titleEllipsis} = toRefs(props);

  const emit = defineEmits(['removed']);

  const {ajaxingCompare} = useCompareHelper({product, emit});
  const { t } = useI18n();

  const { prevPrice, currentPricing, reducedPercent } = usePriceHelper({product});

  const {getThumbImageURL, productLink} = useUtils();

  const badge = computed(() => {
    return product.value?.badge;
  });

  const detailStore = useDetailStore();
  const {setProduct} = detailStore;

  const goToProduct = () => {
    setProduct(product.value);
  };

  const ratingValue = computed(() => {
    const value = parseFloat(product.value?.rating || 5);
    if (Number.isNaN(value)) {
      return 5;
    }
    return Math.round(value * 10) / 10;
  });

  const reviewCount = computed(() => {
    return parseInt(product.value?.review_count || 0);
  });

  const isAvailableForDeliveryToday = computed(() => {
    const rawValue = product.value?.available_for_delivery_today
    if (typeof rawValue === "string") {
      return ["1", "true", "yes"].includes(rawValue.toLowerCase());
    }

    return rawValue === true || rawValue === 1;
  });

</script>
