<template>
  <div class="detail-right">
    <div class="sticky-right">
      <div class="content">
        <!-- <h2 class="price-wrapper mb-5">
          <span class="color-deep price">
            <price-format :price="productPrice" />
          </span>
          <span class="strike-through f-7" v-if="prevPrice">
            <price-format :price="prevPrice" />
          </span>
        </h2> -->
        <div>
          <span v-if="isFreeShipping" class="mr-5 block color-free">
            {{ $t("invent.fs") }}
          </span>

          <!-- <span v-else class="mr-5 block">
            +
            <price-format :price="parseInt(shippingPrice)" />
            {{ $t("detailRight.shippingFee") }}
          </span> -->
          <div class="pos-rel lh-30 z-7 inline detail-price-toggle">
            <button
              class="semi-bold clear-height mt-10 details-scroll"
              aria-label="details"
              @click.prevent="scrollToDescription"
            >
              <span class="mt-2">{{ $t("detailRight.details") }}</span>
            </button>
            <button
              class="semi-bold clear-height details-popover"
              aria-label="details"
              @click.prevent="scrollToDescription"
            >
              <i class="icon black scale-8 arrow-down" />
            </button>
            <!-- Price details popover disabled for now. -->
            <!--
            <client-only>
              <pop-over
                v-if="pricePopOver"
                :title="$t('detailRight.shippingFeeDetails')"
                @close="closePricePopOver"
                :elem-id="'price-pop-over'"
                :layer="false"
              >
                <template v-slot:content>
                  <div class="flex sided">
                    <div>
                      <p>{{ $t("detailRight.price") }}</p>
                      <p>{{ $t("detailRight.shippingFee") }}</p>
                    </div>
                    <div class="right-text">
                      <p>
                        <price-format :price="toFixed(productPrice)" />
                      </p>
                      <p>
                        <span v-if="isFreeShipping" class="color-free">
                          {{ $t("invent.fre") }}
                        </span>
                        <price-format v-else :price="toFixed(shippingPrice)" />
                      </p>
                    </div>
                  </div>
                </template>
                <template v-slot:pop-footer>
                  <div class="flex sided">
                    <h5 class="semi-bold">{{ $t("checkoutRight.total") }}</h5>
                    <h5 class="semi-bold">
                      <price-format :price="toFixed(totalPrice)" />
                    </h5>
                  </div>
                </template>
              </pop-over>
            </client-only>
            -->
          </div>
        </div>

        <div class="start flex gap-10 mb-10 wrap">
          <span class="mt-5 mn-w-70x">
            {{ $t("detailRight.quantity") }}
          </span>

          <quantity-nav
            class="mt-5"
            :quantity="quantity"
            :product-inventory="productInventory"
            :max="maxQuantity"
            @value-changed="quantityChanged"
          />

          <h4
            class="bold stock-indicator"
            :class="[
              { 'color-success': isInStock },
              { 'color-danger': !isInStock },
            ]"
          >
            {{ inStock }}
          </h4>

          <p v-if="cartError.inventory" class="error mb-10">
            {{ cartError.inventory }}
          </p>
        </div>
        <div class="delivery-today-banner mobile-only-assurance">
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
        <div class="clear-height mtb-10 f-10 semi-bold flex color-deep secure-trans-toggle mobile-only-assurance">
          <i class="no-click icon lock-icon opacity-35 dimen-20x" />
          100% Safe and Secure Payments.
        </div>

        <div class="mobile-detail-tabs">
          <div class="tab-list">
            <button
              class="tab"
              type="button"
              :class="{ active: activeTab === 'overview' }"
              @click="setActiveTab('overview')"
            >
              Overview
            </button>
            <button
              class="tab"
              type="button"
              :class="{ active: activeTab === 'description' }"
              @click="setActiveTab('description')"
            >
              Description
            </button>
            <button
              class="tab"
              type="button"
              :class="{ active: activeTab === 'delivery' }"
              @click="setActiveTab('delivery')"
            >
              Delivery Info
            </button>
          </div>
          <div class="tab-content">
            <div
              v-if="activeTab === 'overview'"
              class="editor"
              v-dompurify-html="overview"
            />
            <div
              v-else-if="activeTab === 'description'"
              class="editor"
              v-dompurify-html="description"
            />
            <div v-else-if="activeTab === 'delivery'" class="tab-pane">
              <p class="f-9">
                {{ $t("detailRight.arrives") }} :
                <span class="color-lite semi-bold">
                  {{ arrivesAt }}
                </span>
              </p>
            </div>
            <div v-else class="tab-pane" />
          </div>
        </div>

        <div v-if="allowNote" class="note-inputs">
          <div v-if="allowNoteImage" class="note-field">
            <label class="note-label">
              {{ $t('detailRight.notePhoto') || 'Select Photo' }}
            </label>
            <div
              class="note-upload"
              :class="{ invalid: noteTouched && allowNoteImage && !noteImageFile }"
            >
              <input
                ref="noteImageInput"
                type="file"
                accept="image/*"
                @change="onNoteImageChange"
              />
              <div v-if="noteImagePreview" class="note-preview">
                <img :src="noteImagePreview" alt="Note preview" />
                <button type="button" class="note-change" @click.prevent="triggerNoteImage">
                  {{ $t('detailRight.changePhoto') || 'Change' }}
                </button>
              </div>
              <div v-else class="note-placeholder">
                <div class="note-placeholder-row" aria-hidden="true">
                  <span class="note-placeholder-icon" />
                </div>
                <div class="note-placeholder-copy">
                  <span class="note-placeholder-title">Upload your image</span>
                  <span class="note-placeholder-sub">
                    {{ $t('detailRight.photoHint') || 'JPG/PNG up to 2MB' }}
                  </span>
                </div>
              </div>
            </div>
            <p v-if="noteTouched && allowNoteImage && !noteImageFile" class="error">
              {{ $t('detailRight.noteImageRequired') || 'Photo is required.' }}
            </p>
          </div>

          <div v-if="allowNoteMessage" class="note-field">
            <label class="note-label">
              {{ $t('detailRight.noteMessage') || 'Write your message' }}
            </label>
            <textarea
              v-model="noteMessage"
              class="note-textarea"
              :maxlength="noteMax"
              :placeholder="$t('detailRight.notePlaceholder') || 'Write your message...'"
              @blur="noteTouched = true"
            />
            <div class="note-meta">
              <span v-if="noteTouched && allowNoteMessage && !noteMessageTrimmed" class="error">
                {{ $t('detailRight.noteMessageRequired') || 'Message is required.' }}
              </span>
              <span class="note-count">{{ noteMessage.length }}/{{ noteMax }}</span>
            </div>
          </div>
        </div>
        <div class="detail-action-bar">
          <div class="detail-action-buttons flex-sm">
            <ajax-button
              id="add-to-cart"
              class="action-btn w-100 w-sm-50 primary-btn mtb-10 mlr-sm-2-5"
              :disabled="disabled || noteBlocked"
              type="button"
              :fetching-data="ajaxing"
              @clicked="handleAddToCart"
              :loading-text="$t('detailRight.adding')"
              :text="$t('detailRight.addToCart')"
            />
            <ajax-button
              class="action-btn w-100 w-sm-50 outline-btn mtb-10 mlr-sm-2-5"
              :disabled="disabled || noteBlocked"
              type="button"
              color="primary"
              :fetching-data="buyingNow"
              @clicked="handleBuyNow"
              :loading-text="$t('detailRight.buyNow')"
              :text="$t('detailRight.buyNow')"
            />
          </div>
        </div>

      </div>

      <client-only>
        <social-share class="hide-sm mb-15" :product="product" />
      </client-only>

      <!--      <store-tile-->
      <!--        class="mt-10"-->
      <!--        :store="product.store"-->
      <!--      />-->
    </div>
    <!-- detail-right -->
    <teleport to="body">
      <CartPopup
        :visible="showCartPopup"
        :base-item-price="popupBasePrice"
        :add-ons-price="popupAddOnsPrice"
        @close="showCartPopup = false"
        @continue="handleCartPopupContinue"
      />
    </teleport>

  </div>
  <!-- detail-right -->
</template>

<script setup>
import moment from "moment";
import { useCartHelper } from "~/composables/useCartHelper";
import { usePriceHelper } from "~/composables/usePriceHelper";
import { toRefs } from "vue";
import { useCommonStore } from "../store/common";
import { useUserStore } from "../store/user";
import { useLanguageStore } from "../store/language";
import { useCartStore } from "../store/cart";
import CartPopup from "~/components/CartPopUp";

const showCartPopup = ref(false)
const popupBasePrice = ref(0); // first item price
const popupAddOnsPrice = ref(0); // addons price, start with 0
const router = useRouter();

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  product: {
    type: Object,
  },
  productInventory: {
    type: Object,
  },
});

const { disabled, product, productInventory } = toRefs(props);
const emit = defineEmits(["cart-error", "scroll-description"]);

const noteImageFile = ref(null);
const noteImagePreview = ref("");
const noteMessage = ref("");
const noteTouched = ref(false);
const noteMax = 200;
const noteImageInput = ref(null);

const notePayload = computed(() => ({
  image: noteImageFile.value,
  message: noteMessage.value
}))

const {
  wishListAction,
  addToCart,
  ajaxingWishlist,
  buyingNow,
  cartError,
  quantity,
  ajaxing,
  wishListed,
} = useCartHelper({ product, productInventory, emit, notePayload: notePayload});

const handleAddToCart = async () => {
  if (noteBlocked.value) {
    noteTouched.value = true;
    if (allowNoteImage.value && !noteImageFile.value) {
      setToastError(t('detailRight.noteImageRequired') || 'Photo is required.');
    } else if (allowNoteMessage.value && !noteMessageTrimmed.value) {
      setToastError(t('detailRight.noteMessageRequired') || 'Message is required.');
    }
    return;
  }
  // 1️⃣ Call the original addToCart so the cart still works
  const result = await addToCart();
  // Only show the popup when cart add succeeds.
  if (!result || result.status !== 200) {
    return;
  }

  // 2️⃣ Show the popup
  popupBasePrice.value = parseFloat(productPrice.value || 0);
  popupAddOnsPrice.value = 0; // for now, no addons yet
  showCartPopup.value = true;
};

const handleBuyNow = async () => {
  if (noteBlocked.value) {
    noteTouched.value = true;
    if (allowNoteImage.value && !noteImageFile.value) {
      setToastError(t('detailRight.noteImageRequired') || 'Photo is required.');
    } else if (allowNoteMessage.value && !noteMessageTrimmed.value) {
      setToastError(t('detailRight.noteMessageRequired') || 'Message is required.');
    }
    return;
  }
  await addToCart(true);
};

defineExpose({ wishListAction, wishListed, ajaxingWishlist });

const { productPrice, prevPrice } = usePriceHelper({
  product,
  productInventory,
});

const { t } = useI18n();


const pricePopOver = ref(false);
const activeTab = ref("overview");

const commonStore = useCommonStore();
const { currencyIcon, setting } = storeToRefs(commonStore);
const { setToastMessage, setToastError } = commonStore;

const userStore = useUserStore();
const { getUserToken } = userStore;

const languageStore = useLanguageStore();
const { langCode } = storeToRefs(languageStore);

const cartStore = useCartStore();

const makeWishListed = async () => {
  await wishListAction();
};

const scrollToDescription = () => {
  emit("scroll-description");
};

const allowNoteMessage = computed(() => {
  const messageFlag = product.value?.note?.message;
  if (typeof messageFlag === 'boolean') return messageFlag;
  return !!product.value?.allow_note;
});

const allowNoteImage = computed(() => {
  const imageFlag = product.value?.note?.image;
  if (typeof imageFlag === 'boolean') return imageFlag;
  return !!product.value?.allow_note_image;
});

const isAvailableForDeliveryToday = computed(() => {
  const rawValue = product.value?.available_for_delivery_today
  if (typeof rawValue === "string") {
    return ["1", "true", "yes"].includes(rawValue.toLowerCase());
  }

  return rawValue === true || rawValue === 1;
});

const allowNote = computed(() => {
  return allowNoteMessage.value || allowNoteImage.value;
});

const noteMessageTrimmed = computed(() => {
  return noteMessage.value.trim();
});

const noteBlocked = computed(() => {
  if (!allowNote.value) return false;
  if (allowNoteImage.value && !noteImageFile.value) return true;
  return !!(allowNoteMessage.value && !noteMessageTrimmed.value);

});

const triggerNoteImage = () => {
  noteImageInput.value?.click();
};

const onNoteImageChange = (evt) => {
  const file = evt.target.files?.[0] || null;
  const maxSize = 2 * 1024 * 1024;
  if (file && file.size > maxSize) {
    noteImageFile.value = null;
    if (noteImagePreview.value) {
      URL.revokeObjectURL(noteImagePreview.value);
      noteImagePreview.value = "";
    }
    evt.target.value = "";
    noteTouched.value = true;
    setToastError(t('detailRight.photoHint') || 'JPG/PNG up to 2MB');
    return;
  }
  noteImageFile.value = file;
  if (noteImagePreview.value) {
    URL.revokeObjectURL(noteImagePreview.value);
  }
  noteImagePreview.value = file ? URL.createObjectURL(file) : "";
  noteTouched.value = true;
};

watch(product, () => {
  noteImageFile.value = null;
  if (noteImagePreview.value) {
    URL.revokeObjectURL(noteImagePreview.value);
  }
  noteImagePreview.value = "";
  noteMessage.value = "";
  noteTouched.value = false;
});

const maxQuantity = computed(() => {
  return parseInt(productInventory.value?.quantity || 0);
});

const isInStock = computed(() => {
  if (productInventory.value?.quantity !== undefined) {
    return productInventory.value.quantity > 0;
  }

  if (product.value?.in_stock !== undefined) {
    return product.value.in_stock;
  }
  return true;
});

const inStock = computed(() =>
  isInStock.value ? t("detail.inStock") : t("detail.outOfStock"),
);

const toFixed = (num) => {
  return parseFloat(num).toFixed(2);
};

const closePricePopOver = () => {
  pricePopOver.value = false;
};

const quantityChanged = (evt) => {
  quantity.value = evt.value;
};

const totalPrice = computed(() => {
  return parseFloat(productPrice.value) + parseFloat(shippingPrice.value);
});

const shippingPlace = computed(() => {
  const all = shippingRule.value?.find((obj) => {
    return obj.country.toUpperCase() === "ALL";
  });
  if (!all) {
    let maxPrice = 0;
    let maxObj = 0;
    shippingRule.value?.forEach((obj) => {
      if (parseFloat(obj.price) > maxPrice) {
        maxPrice = obj.price;
        maxObj = obj;
      }
    });
    return maxObj;
  } else return all;
});

const arrivesAt = computed(() => {
  const momentDate = moment().add(shippingPlace.value?.day_needed, "days");

  const day = momentDate.format("ddd").toLowerCase();
  const mon = momentDate.format("MMM").toLowerCase();
  const date = momentDate.format("D");
  return t("date.ddddMMMD", {
    day: t(`date.${day}`),
    mon: t(`date.${mon}`),
    date: date,
  });
});

const isFreeShipping = computed(() => {
  return !(parseFloat(shippingPrice.value) > 0);
});

const shippingPrice = computed(() => {
  return shippingPlace.value?.price || 0;
});

const shippingRule = computed(() => {
  return product.value?.shipping_rule?.shipping_places;
});

const description = computed(() => {
  return product.value?.description || "";
});

const overview = computed(() => {
  return product.value?.overview || "";
});

const setActiveTab = (tab) => {
  activeTab.value = tab;
};

const handleCartPopupContinue = async (payload) => {
  showCartPopup.value = false;
  try {
    if (payload.withAddons && payload.addons.length > 0) {
      const userToken = await getUserToken();

      for (const addon of payload.addons) {
        const product = addon.product;
        const quantity = addon.quantity;
        const inventory = addon.inventory;
        const selectedAttributes = addon.selectedAttributes;

        if (!inventory || !inventory.id) {
          setToastError(`Cannot add ${product.title}: Missing inventory data`);
          continue; // Skip this product
        }

        await cartStore.cartAction({
          payload: {
            user_token: userToken,
            apiVal: {
              user_token: userToken,
              product_id: product.id,
              inventory_id: inventory.id,
              quantity: quantity
            },
            storeVal: {
              product: {
                id: product.id,
                title: product.title,
                offered: product.offered,
                selling: product.selling,
                image: product.image,
                shipping_rule: product.shipping_rule,
                attribute: product.attribute || []
              },
              inventory: inventory,
              quantity: quantity,
              selected: 1,
              offered: 0,
              bundle_deal: null,
              shipping_type: 1
            },
            isBundle: false
          },
          lang: langCode.value
        });
      }

      // Refresh cart
      await cartStore.getCartByUser({ lang: langCode.value });

      setToastMessage(`${payload.addons.length} add-on(s) added to cart`);

    } else {
      setToastMessage("Continuing without add-ons");
    }

    // REDIRECT TO CART
    await router.push("/cart");

  } catch (err) {
    setToastError("Failed to add add-ons to cart");
  }
};
</script>
