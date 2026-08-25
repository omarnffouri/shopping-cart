<template>
  <div>
    <CartStickyBtn
      :product-inventory="selectedInventory"
      :disabled="!statusPublic"
      :product="product"
      @cart-error="hasCartError"
    />

    <div v-if="product">
      <a
        v-if="product.store && product.store.whatsapp_btn"
        class="whatsapp-btn-wrap"
        target="_blank"
        :href="`https://wa.me/${product.store.whatsapp_number}?text=${product.store.whatsapp_default_msg}`"
      >
        <i class="icon whatsapp-icon" />
      </a>

      <!--      <div-->
      <!--        class="detail-menu hide-sm"-->
      <!--      >-->
      <!--        <div class=" container-fluid">-->
      <!--          <div v-if="currentCategories && currentCategories.length" class="mlr&#45;&#45;15">-->
      <!--            <nuxt-link-->
      <!--              v-for="(value, i) in currentCategories"-->
      <!--              :title="value.title"-->
      <!--              :to="categoryLink(value)"-->
      <!--              :key="i"-->
      <!--            >-->
      <!--              {{ value.title }}-->
      <!--            </nuxt-link>-->
      <!--          </div>-->
      <!--          <div class="mlr&#45;&#45;15 mn-h-40x" v-else><a></a></div>-->
      <!--        </div>-->
      <!--      </div>-->
      <div class="container-fluid mtb-15 mt-sm-10 mn-h-700x">
        <div>
          <!--          <breadcrumb-->
          <!--            class="mb-20 mb-sm-15"-->
          <!--            :slugs="preparedSlug"-->
          <!--            :page="productTitle"-->
          <!--          />-->
          <div class="product-detail">
            <div class="detail-left pr-30 pr-sm-0">
              <div class="flex start align-start block-md">
                <div class="product-main">
                  <div class="detail-image-wrapper">
                    <div
                      class="detail-image-inner"
                      :class="{ 'z-2': imagePopup }"
                    >
                      <product-images
                        v-if="productImage || productImageList"
                        ref="productImagesRef"
                        :title="productTitle"
                        :product="product"
                        :main-image="productImage"
                        :images="productImageList"
                        @image-popup="setImagePopup"
                        @add-to-wishlist="addWishList"
                      />
                      <div class="image-assurance hide-sm">
                        <div class="image-delivery-banner">
                          <svg
                            class="delivery-today-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                          >
                            <path d="M10 17h4" />
                            <path d="M3 7h12v8H3z" />
                            <path d="M15 10h3l3 3v2h-6z" />
                            <circle cx="7" cy="17" r="1.5" />
                            <circle cx="17" cy="17" r="1.5" />
                          </svg>
                          <div v-if="isAvailableForDeliveryToday" class="delivery-today-content">
                            <p class="delivery-today-title">
                              {{ t('cartProductTile.earliestDelivery') || 'Earliest Delivery:' }}
                              <span class="delivery-today-highlight">{{ t('cartProductTile.today') || 'Today' }}</span>
                            </p>
                            <p class="delivery-today-note">
                              {{ t('cartProductTile.deliveryDateEditableOnCheckout') || 'Customize delivery date on checkout' }}
                            </p>
                          </div>
                          <div v-else class="delivery-today-content">
                            <p class="delivery-today-title">
                              {{ $t("detailRight.arrives") }} :
                              <span class="delivery-today-highlight">{{ arrivesAt }}</span>
                            </p>
                          </div>
                        </div>
                        <div class="image-secure-note">
                          <i class="no-click icon lock-icon opacity-35 dimen-20x" />
                          100% Safe and Secure Payments.
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="pl-30 pl-md grow product-info-panel">
                    <div class="title-price-row">
                      <h1 class="f-16 product-title">
                        {{ productTitle }}
                      </h1>
                      <div class="product-price">
                        <div class="price-row">
                          <span class="currency">AED</span>
                          <span class="amount">{{ displayPrice }}</span>
                        </div>
                        <div class="price-tax-note">
                          {{ $t("detailRight.inclusiveTaxes") }}
                        </div>
                      </div>
                    </div>
                    <!--                    <div class="mt-10">-->
                    <!--                      <rating-star :rating="parseFloat(productRating)"/>-->
                    <!--                      <span class="f-10 ml-5 semi-bold color-lite">-->
                    <!--                        {{ $t('productReview.reviews', {count: reviewCount}) }}-->
                    <!--                      </span>-->
                    <!--                    </div>-->

                    <div v-if="endTime" class="devider w-md-100 mtb-15">
                      &nbsp;
                    </div>

                    <div
                      v-if="endTime"
                      class="flex sided warning-msg ptb-10 plr-15 mb-15 wrap gap-10"
                    >
                      <h5 class="color-inherit">
                        {{ $t("product.shocking") }}
                      </h5>
                      <div class="gap-10 flex">
                        <h5 class="color-inherit">
                          {{ $t("product.endsIn") }}
                        </h5>
                        <b>
                          <countdown
                            :time-zone="product.time_zone"
                            :end-time="endTime"
                          />
                        </b>
                      </div>
                    </div>

                    <detail-right
                      ref="detailRightEl"
                      id="detail-right"
                      class="detail-right-inline"
                      :product-inventory="selectedInventory"
                      :disabled="!statusPublic"
                      :product="product"
                      @cart-error="hasCartError"
                      @scroll-description="scrollToDescription"
                    />

<!--                    <button-->
<!--                      class="wishlist-action mt-5 mb-15"-->
<!--                      type="button"-->
<!--                      :class="{-->
<!--                        active: mobileWishListed,-->
<!--                        loading: mobileWishlistLoading,-->
<!--                      }"-->
<!--                      :disabled="mobileWishlistLoading"-->
<!--                      @click.prevent="makeWishListedMobile"-->
<!--                    >-->
<!--                      <span class="wishlist-text">-->
<!--                        {{-->
<!--                          !mobileWishListed-->
<!--                            ? $t('detailRight.addToWishlist')-->
<!--                            : $t('detailRight.removeFromWishlist')-->
<!--                        }}-->
<!--                      </span>-->
<!--                      <i-->
<!--                        class="icon"-->
<!--                        :class="mobileWishListed ? 'heart-fill-icon' : 'heart-icon'"-->
<!--                      />-->
<!--                      <span class="wishlist-dot" aria-hidden="true"></span>-->
<!--                    </button>-->

                    <template v-if="description">
                      <div v-if="bundleDeal" class="two-sided mb-15">
                        <h6 class="left">
                          {{ $t("product.bundleDeal") }}
                        </h6>
                        <div class="right bundle-deal">
                          {{ bundleDeal.title }}
                        </div>
                      </div>

                      <div v-if="brand" class="two-sided mb-15">
                        <h6 class="left">
                          {{ $t("product.brand") }}
                        </h6>
                        <div class="right">
                          <nuxt-link
                            class="link"
                            :to="`/${product.brand.slug}/brand?brand=${product.brand.id}`"
                          >
                            {{ brand }}
                          </nuxt-link>
                        </div>
                      </div>

                      <div ref="attrRef"></div>
                    </template>
<!--                    <template v-else>-->
<!--                      <div class="two-sided mb-15">-->
<!--                        <h6 class="left">{{ $t("product.bundleDeal") }}</h6>-->
<!--                        <div class="right bundle-deal opacity-0">xxx</div>-->
<!--                      </div>-->

<!--                      <div class="two-sided mb-15">-->
<!--                        <h6 class="left">{{ $t("product.brand") }}</h6>-->
<!--                        <div class="right opacity-0">xxx</div>-->
<!--                      </div>-->

<!--                      <div class="two-sided mb-15 opacity-0">-->
<!--                        <span class="left">xxx</span>-->
<!--                        <div class="start flex wrap gap-10">-->
<!--                          <label class="rd-container rd-attr">-->
<!--                            <span class="input-content">xxx</span>-->
<!--                          </label>-->
<!--                        </div>-->
<!--                      </div>-->
<!--&lt;!&ndash; -->
<!--                      <div class="wrap two-sided mb-15 align-start">-->
<!--                        <h6 class="left">-->
<!--                          {{ $t("product.refundWarranty") }}-->
<!--                        </h6>-->
<!--                        <div class="right">-->
<!--                          <div class="mb-5">-->
<!--                            <div>{{ $t("productHelper.refundable") }}</div>-->
<!--                            <div class="mb-10 mt-5 block color-lite">-->
<!--                              {{ $t("productHelper.mindChange") }}-->
<!--                            </div>-->
<!--                          </div>-->
<!--                          <div class="mt-5">-->
<!--                            {{ $t("product.authentic") }}-->
<!--                          </div>-->
<!--                        </div>-->
<!--                      </div> &ndash;&gt;-->

<!--                      <div class="two-sided mb-15">-->
<!--                        <h6 class="left">-->
<!--                          {{ $t("accountLayout.vouchers") }}-->
<!--                        </h6>-->
<!--                        <div class="pos-rel">-->
<!--                          <div class="right mlr&#45;&#45;2-5"></div>-->
<!--                        </div>-->
<!--                      </div>-->
<!--                    </template>-->

                    <div
                      v-for="(value, index) in productAttributes"
                      :key="index"
                      class="two-sided mb-15"
                    >
                      <span class="left">{{ value.title }}</span>

                      <div class="start flex wrap gap-10">
                        <label
                          v-for="(av, avIndex) in value.values"
                          :key="`av-${avIndex}`"
                          class="rd-container rd-attr"
                        >
                          <input
                            type="radio"
                            :name="`${value.id}`"
                            v-model="clickedAttributes[value.id]"
                            :value="av.id"
                            @change="
                              selectedAttribute({ key: avIndex, value: av })
                            "
                          />
                          <span class="rd-checkmark"></span>

                          <span class="input-content">{{ av.title }}</span>
                        </label>
                      </div>
                    </div>

                    <div
                      v-if="cartError.attribute"
                      class="two-sided mb-15 align-start"
                    >
                      <h6 class="left"></h6>
                      <div class="right">
                        <p class="error mb-10">
                          {{ cartError.attribute }}
                        </p>
                      </div>
                    </div>
<!-- 
                    <div class="wrap two-sided mb-15 align-start">
                      <h6 class="left">
                        {{ $t("product.refundWarranty") }}
                      </h6>
                      <div class="right">
                        <div class="mb-5">
                          <template v-if="refundable(product)">
                            <div>{{ $t("productHelper.refundable") }}</div>
                            <div class="mb-10 mt-5 block color-lite">
                              {{ $t("productHelper.mindChange") }}
                            </div>
                          </template>
                          <template v-else>
                            {{ $t("productHelper.notRefundable") }}
                          </template>
                        </div>

                        <div v-if="product.warranty">
                          {{ warranty(product) }}
                        </div>
                        <div class="mt-5">
                          {{ $t("product.authentic") }}
                        </div>
                      </div>
                    </div> -->

                    <div
                      v-if="vouchers && vouchers.length"
                      class="two-sided mb-15"
                    >
                      <h6 class="left">
                        {{ $t("accountLayout.vouchers") }}
                      </h6>
                      <div class="pos-rel">
                        <div
                          class="right mlr--2-5 cp"
                          data-ignore="voucher-pop-over"
                          @click.passive="toggleVoucherPopOver"
                        >
                          <span
                            v-for="(value, index) in vouchers"
                            :key="index"
                            class="no-click info-msg ptb-5 mlr-2-5 mb-5 f-9"
                          >
                            {{
                              $t("detailRight.off", {
                                amount: getPriceType(value),
                              })
                            }}
                          </span>
                        </div>
                        <pop-over
                          v-if="voucherPopOver"
                          class="voucher-pop-over-right"
                          :title="$t('filter.shop')"
                          @close="closeVoucherPopOver"
                          elem-id="voucher-pop-over"
                          :layer="false"
                        >
                          <template v-slot:content>
                            <Vouchers
                              ref="voucherPagination"
                              :changing-route="false"
                            />
                          </template>
                        </pop-over>
                      </div>
                    </div>

                    <div class="price-tax-note hide block-sm mb-15">
                      {{ $t("detailRight.inclusiveTaxes") }}
                    </div>

                    <div
                      class="editor mt-30 mt-sm-15 hide-sm"
                      v-dompurify-html="overview"
                    />

                    <div class="hide-sm">
                      <div
                        ref="descriptionRef"
                        class="ellipsis-para editor mt-30 mt-sm-15"
                        :class="{ expanded: descriptionExpand }"
                        v-dompurify-html="description"
                      />
                      <button
                        @click.prevent="descriptionToggle"
                        aria-label="Read less"
                        class="link mt-15 mb-5"
                      >
                        {{
                          descriptionExpand
                            ? $t("product.readLess")
                            : $t("product.readMore")
                        }}
                      </button>
                    </div>
                  </div>
                  <!-- plr-30 grow -->
                </div>
                <!-- flex -->
              </div>
            </div>
            <!-- product-detail -->

          </div>
          <!-- product-detail -->
        </div>
      </div>
      <!-- container-fluid mtb-15 -->

      <client-only>
<!--          <div :class="{ 'mx-h-0': !hasReview, 'review-loaded': !reviewLoaded }"-->
<!--              class="container-fluid suggested-container mn-h-400x">-->
<!--              <LoadSection v-slot="{ renderArea }">-->
<!--                  <product-review-->
<!--                      v-if="renderArea"-->
<!--                      :id="product.id"-->
<!--                      class="b-t pt-20 pt-sm-15  "-->
<!--                      @has-review="fetchedReview"-->
<!--                  />-->
<!--              </LoadSection>-->
<!--          </div>-->

        <div class="container-fluid suggested-container mn-h-400x">
          <LoadSection v-slot="{ renderArea }">
            <suggested-products v-if="renderArea" :product-id="productId" />
          </LoadSection>
        </div>
      </client-only>
    </div>
  </div>
</template>

<script setup>
import moment from "moment";
import { useCommonStore } from "~/store/common";
import { useDetailStore } from "~/store/detail";
import { useUserStore } from "~/store/user";
import { ref, nextTick, onMounted, watch, onUnmounted, computed } from "vue";
import { useAsyncData } from "nuxt/app";
import { useLanguageStore } from "~/store/language";
import { storeToRefs } from "pinia";
import { prepareGetUrl } from "~/utils/fetchClient";
import { useMetaData } from "~/composables/useMetaData";
import { useUtils } from "~/composables/useUtils";
import { useProductHelper } from "~/composables/useProductHelper";
import { usePriceHelper } from "~/composables/usePriceHelper";

definePageMeta({
  middleware: ["common-middleware"],
  layout: "default",
});

const commonStore = useCommonStore();
storeToRefs(commonStore);
const detailStore = useDetailStore();
const { product } = storeToRefs(detailStore);
const { emptySuggestedProducts, setProduct } = detailStore;
const userStore = useUserStore();
const { emptyVoucher } = userStore;
const { t } = useI18n();
const detailRightEl = ref(null);
const languageStore = useLanguageStore();
const { langCode } = storeToRefs(languageStore);
const { pageMeta, route, preloadScript } = useMetaData();
const { getThumbImageURL, getImageURL, categoryLink } = useUtils();
const { unAuthGet } = commonStore;
const { refundable, warranty, getPriceType } = useProductHelper();

const detailRightDoc = ref(null);
const sectionObserver = ref(null);
const clickedAttributes = ref([]);
const cartError = ref({
    attribute: null,
    quantity: null,
});
const selectedInventory = ref({});
const currentAttributes = ref([]);
const descriptionExpand = ref(false);
const descriptionRef = ref(null);
const optionChange = ref(false);
//const productInventory = ref(null);
const imagePopup = ref(false);
const hasReview = ref(true);
const reviewLoaded = ref(true);
const activatedPage = ref(false);
const voucherPopOver = ref(false);
const attrRef = ref(null);
const productImagesRef = ref(null);
const { productPrice } = usePriceHelper({
    product,
    productInventory: selectedInventory,
});

const description = computed(() => product.value?.description || null);
const overview = computed(() => product.value?.overview || "");
const reviewCount = computed(() => product.value?.review_count || 0);
const productRating = computed(() => product.value?.rating || 0);
const productImage = computed(() => product.value?.image || null);
const productImageList = computed(() => product.value?.images || []);
const displayPrice = computed(() => {
  const raw = parseFloat(productPrice.value || 0);
  if (Number.isNaN(raw)) return "0";
  return raw.toFixed(0);
});

/*
  const timeDifference = computed(() => {
      const len = product.value.id.toString()?.length
      let highest = ''
      for (let i = 1; i <= len; i++) {
          highest += '9'
      }
      return ((product.value.id / highest) * 100).toFixed(2)
  });
  */

const endTime = computed(() => product.value?.end_time || null);
const productId = computed(() => route.params.id);
const statusPublic = computed(() => parseInt(product.value?.status) === 1);
const category = computed(() => product.value?.category);
const currentCategories = computed(() => product.value?.current_categories);
const productTitle = computed(() => product.value?.title || "");
const preparedSlug = computed(() => {
  return categoryData.value
    ?.map((i) => {
      return { title: i.title, link: categoryLink(i) };
    })
    ?.reverse();
});
const categoryData = computed(() => product.value?.category_data);
/*
  const productSlug = computed(() => {
      return product.value?.slug
  });
  */
const bundleDeal = computed(() => product.value?.bundle_deal);
const isInStock = computed(() => {
  if (optionChange.value) {
    return productInventory.value?.quantity > 0;
  }

  if (product.value.in_stock !== undefined) {
    return product.value.in_stock;
  }
  return true;
});
const inStock = computed(() => isInStock.value ? t("detail.inStock") : t("detail.outOfStock"));
const mobileWishListed = computed(
  () => detailRightEl.value?.wishListed?.value || false,
);
const mobileWishlistLoading = computed(
  () => detailRightEl.value?.ajaxingWishlist?.value || false,
);
const vouchers = computed(() => product.value?.vouchers);
const brand = computed(() => product.value?.brand?.title || "");
const shippingRule = computed(() => product.value?.shipping_rule?.shipping_places || []);
const shippingPlace = computed(() => {
  const all = shippingRule.value?.find((obj) => obj.country?.toUpperCase() === "ALL");
  if (all) return all;

  let maxPrice = 0;
  let maxObj = null;
  shippingRule.value?.forEach((obj) => {
    if (parseFloat(obj.price) > maxPrice) {
      maxPrice = obj.price;
      maxObj = obj;
    }
  });
  return maxObj;
});
const arrivesAt = computed(() => {
  const dayNeeded = parseInt(shippingPlace.value?.day_needed || 0);
  const momentDate = moment().add(dayNeeded, "days");
  const day = momentDate.format("ddd").toLowerCase();
  const mon = momentDate.format("MMM").toLowerCase();
  const date = momentDate.format("D");

  return t("date.ddddMMMD", {
    day: t(`date.${day}`),
    mon: t(`date.${mon}`),
    date,
  });
});
const isAvailableForDeliveryToday = computed(() => {
  const rawValue = product.value?.available_for_delivery_today;
  if (typeof rawValue === "string") {
    return ["1", "true", "yes"].includes(rawValue.toLowerCase());
  }
  return rawValue === true || rawValue === 1;
});

/*const productAttributeImage = computed(() => {
  const attrImg = []
  product.value.product_image_names.forEach((i, key) => {

      if (i.attributes.length) {
          attrImg[i.attributes[0]?.attribute_value_id] = {value: i, key: key}
      }
  });
  return attrImg;
});*/

const productAttributes = computed(() => {
  product.value?.attribute?.forEach((i) => {
    clickedAttributes.value[i.id] = [];
  });

  return product.value?.attribute?.map((i) => {
    return {
      ...i,
      ...{
        values: i.values.reduce((a, item) => {
          a[`${item.attribute_id}-${item.attribute_value_id}`] = item;
          return a;
        }, {}),
      },
    };
  });
});

const hasCartError = (event) => {
    cartError.value = event;
    attrRef.value.scrollIntoView({
        behavior: "smooth",
        block: "center",
        inline: "center",
    });
};

const setImagePopup = (event) => {
    imagePopup.value = event;
};

const selectedAttribute = (data) => {
    cartError.value.attribute = null;
    //this.attrHover(data.value)

    currentAttributes.value[data.key.split("-")[0]] = data.value;

    const imageMap = [];
    product.value.product_image_names.map((i) => {
        imageMap[
            i.attributes
                .map((j) => {
                    return j.attribute_value_id;
                })
                .sort()
                .join("-")
            ] = i.image;
    });

    const currentSelected = currentAttributes.value
        .map((i) => {
            return i?.attribute_value_id;
        })
        .filter((i) => {
            return i;
        });

    let bestMatch = null;
    let highestScore = -1;

    Object.keys(imageMap).forEach((key) => {
        const splitKey = key.split("-").map(Number);
        const score = splitKey.reduce(
            (acc, value) => acc + (currentSelected.includes(value) ? 1 : 0),
            0,
        );

        if (score > highestScore) {
            highestScore = score;
            bestMatch = imageMap[key];
        }
    });

    const selectedImage = highestScore > 0 ? bestMatch : null;
    const imageIndex = productImageList.value?.findIndex((i) => {
        return i.image === selectedImage;
    });
    productImagesRef.value.zoomActiveChange(imageIndex > -1 ? imageIndex + 1 : 0);

    if (
        Object.values(currentAttributes.value).length ===
        productAttributes.value.length
    ) {
        const selected = Object.values(currentAttributes.value).map((i) => {
            return i.attribute_value_id;
        });

        const selectedAttr = selected.sort().join("-");

        let currentInventory = null;
        const inventoryAttr = [];

        for (var i of product.value?.inventory) {
            const invAttr = [];
            i.inventory_attributes.forEach((j) => {
                invAttr.push(parseInt(j.attribute_value_id));
            });

            inventoryAttr[invAttr.sort().join("-")] = i;
        }

        if (inventoryAttr[selectedAttr]) {
            currentInventory = inventoryAttr[selectedAttr];
        }

        selectedInventory.value = currentInventory;
        optionChanged(currentInventory);
    } else {
        selectedInventory.value = {};
    }
};

const descriptionToggle = () => {
    descriptionExpand.value = !descriptionExpand.value;
};

const scrollToDescription = () => {
  if (!process.client) return;
  if (window.innerWidth <= 768) return;
  if (!descriptionRef.value) return;
  const panel = descriptionRef.value.closest(".product-info-panel");
  if (panel) {
    const panelTop = descriptionRef.value.offsetTop - 20;
    panel.scrollTo({ top: Math.max(0, panelTop), behavior: "smooth" });
    return;
  }
  const header = document.querySelector(".header-sticky");
  const headerHeight = header?.getBoundingClientRect().height || 0;
  const targetTop =
    window.scrollY +
    descriptionRef.value.getBoundingClientRect().top -
    headerHeight -
    140;
  window.scrollTo({ top: Math.max(0, targetTop), behavior: "smooth" });
};

const closeVoucherPopOver = () => {
    voucherPopOver.value = false;
};

const fetchedReview = (evt) => {
    hasReview.value = !!evt;
    reviewLoaded.value = !!!evt;
};

const optionChanged = (evt) => {
    optionChange.value = true;
    productInventory.value = evt;
};

const qty = (direction) => {
    if (quantity.value + direction === 0) {
        return;
    }
    quantity.value += direction;
};

const settingMetaData = () => {
    if(product.value){
        useHead({
            ...pageMeta({
                meta_title: product.value.meta_title,
                meta_description: product.value.meta_description,
                meta_keywords: product.value.meta_keywords,
                image_url: getThumbImageURL(product.value.image),
            }),
            ...{
                link: [preloadScript(getThumbImageURL(product.value.image), "image")],
            },
        });
    }
};
const fetchingData = async () => {
    return useAsyncData(`product-${route.params.id}`, async () => {
        const response = await unAuthGet({
            api: "product",
            params: `/${route.params.id}?${prepareGetUrl({ id: route.params.id, user_id: "" })}`,
            lang: langCode.value,
        });

        setProduct(response.data);
        return response.data;
    });
};

const productInventory = computed(() => {
    // If product has no attributes, return first inventory or empty
    if (!product.value?.attribute || product.value.attribute.length === 0) {
        return product.value?.inventory?.[0] || {};
    }

    // If product has attributes, check selected attributes length
    if (
        Object.keys(currentAttributes.value).length ===
        productAttributes.value.length
    ) {
        const selectedAttr = Object.values(currentAttributes.value)
            .map((i) => i.attribute_value_id)
            .sort()
            .join("-");

        const inventoryMap = {};
        for (const inv of product.value?.inventory || []) {
            const invKey = inv.inventory_attributes
                .map((i) => i.attribute_value_id)
                .sort()
                .join("-");
            inventoryMap[invKey] = inv;
        }

        return inventoryMap[selectedAttr] || {};
    }

    // If attributes not fully selected yet
    return {};
});
watch(productInventory, (newInv) => {
    selectedInventory.value = newInv;
});

if (import.meta.client) {
    fetchingData();
} else {
    await fetchingData();
}

watchEffect(() => {
    settingMetaData();
});

const handleIntersection = (entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            document.body.classList.remove("show-cart");
        } else {
            document.body.classList.add("show-cart");
        }
    });
};

const initIntersection = async () => {
    if (!import.meta.client) return;
    if (detailRightDoc.value) {
        return;
    }
    await nextTick();
    detailRightDoc.value = document.querySelector("#detail-right");
    if (detailRightDoc.value) {
        sectionObserver.value = new IntersectionObserver(handleIntersection, {});
        sectionObserver.value.observe(detailRightDoc.value);
    }
};

watch(product, async () => {
    await initIntersection();
});

const toggleVoucherPopOver = () => {
    voucherPopOver.value = !voucherPopOver.value;
};
const addWishList = () => {
    detailRightEl.value.wishListAction();
};
const makeWishListedMobile = () => {
  detailRightEl.value?.wishListAction?.();
};

onMounted(async () => {
    await initIntersection();

    emptyVoucher();
    emptySuggestedProducts();

    //Checking if the product has no attribute
    if (
        product.value?.inventory?.length === 1 &&
        product.value?.inventory[0]?.inventory_attributes?.length === 0
    ) {
        selectedInventory.value = product.value?.inventory[0];
    }

    document.body.classList.add("detail-page");
});

onUnmounted(() => {
    sectionObserver.value?.disconnect();
    document.body.classList.remove("detail-page");
});
</script>
