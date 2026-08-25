<template>
  <div
    class="gap-20 flex sided align-start b-b pb-20 pt-10 mb-10 cart-product-tile"
    v-if="product"
  >
    <div class="flex gap-15">

      <label class="cb-container">
        <input
          type="checkbox"
          :value="cartId"
          v-model="cbChecked"
          class="cp"
          @change="$emit('cb-changed', {id: cart.id, checked: $event})"
        >
        <span class="checkmark"></span>
      </label>

      <nuxt-link
        class="w-100x img-wrapper"
        :to="productLink(product)"
        :title="title"
        @click="goToProduct(product)"
      >
        <ImageLazy
          :lazy-src="getThumbImageURL(productImage)"
          :title="title"
          :alt="title"
        />
      </nuxt-link>
    </div>
    <div class="content-wrap flex align-start grow block-sm gap-15">
      <div class="grow">

        <div>
          <h5 class="semi-bold mb-5">
            <nuxt-link
              class="ellipsis-1"
              :to="productLink(product)"
              :title="title"
              @click="goToProduct(product)"
            >
              {{ title }}
            </nuxt-link>
          </h5>
          <p
            v-if="isShipping && addressLine"
            class="color-lite f-9 mb-5"
          >
            {{ addressLine }}
          </p>
          <h6 class="color-lite mb-15 mt-10">
            <span class="mr-15" v-for="i in currentAttr">
              <b class="mr-5">{{i[0]}}</b> : {{i[1]}}
            </span>

            <span
              v-if="hasBundleDeal"
              class="ellipsis-1 mr-10"
            >
              <span class="bold mr-5">{{ $t('cartProductTile.bundleOffer') }}: </span>
              {{ bundleDeal.title }}
            </span>

            <span
              v-if="prevPrice"
            >
              <price-format
                class="color-reduced strike-through"
                :price="prevPrice"
              />

              <span class="bold color-offer">{{ $t('date.offer', {amount: reducedPercent }) }}</span>
            </span>
          </h6>
          <div class="price-row">
            <price-format
              :price="productPrice"
            />
            <span class="qty">x {{ productQuantity }}</span>
          </div>

        </div>

        <div
          v-if="!isShipping"
          class="flex gap-10 start wrap mt-10"
        >
          <quantity-nav
            class="mtb-5"
            :quantity="parseInt(productQuantity)"
            :product-inventory="cart.updated_inventory"
            :max="maxQuantity"
            @value-changed="valueChanged"
          />
          <ajax-button
            class="outline-btn plr-20 mtb-5"
            type="button"
            :text="$t('userAddress.delete')"
            color="primary"
            @clicked="deleting"
          />
        </div>

        <div v-if="showNoteSection" class="note-block">
          <p class="note-title">
            {{ noteSectionTitle }}
          </p>
          <input
            v-if="allowNoteImage"
            ref="noteImageInput"
            class="note-hidden-input"
            type="file"
            accept="image/*"
            @change="onNoteImageSelected"
          />
          <div class="note-row">
            <div v-if="allowNoteImage || noteImageSrc" class="note-col note-col-image">
              <div class="note-image">
                <img
                  v-if="noteImageSrc"
                  :src="noteImageSrc"
                  alt="Note image"
                />
                <div v-else class="note-image-placeholder">
                  {{ $t('detailRight.selectPhoto') || 'Select Photo' }}
                </div>
              </div>
              <button
                type="button"
                class="note-action note-action-btn"
                :disabled="updatingNote || !allowNoteImage"
                @click="triggerChangeImage"
              >
                {{ updatingNote ? 'Saving...' : 'CHANGE' }}
              </button>
            </div>
            <div v-if="allowNoteMessage || noteMessage" class="note-col note-col-message">
              <div class="note-message-box">
                <textarea
                  v-if="isEditingNote && allowNoteMessage"
                  v-model="draftNoteMessage"
                  class="note-edit-input"
                  maxlength="200"
                />
                <p v-else>{{ noteMessage || ($t('detailRight.notePlaceholder') || 'Write your message...') }}</p>
                <span class="note-count">{{ currentNoteLength }}</span>
              </div>
              <button
                type="button"
                class="note-action note-action-btn"
                :disabled="updatingNote || !allowNoteMessage"
                @click="onEditClick"
              >
                {{ isEditingNote ? 'Save' : 'Edit' }}
              </button>
            </div>
          </div>
        </div>

      </div>

      <div
        v-if="isShipping"
        class="shipping-select"
      >
        <form v-if="currentAddresses.length && isSingleShipping">
          <p v-if="!currentShipRule" class="error">{{ noShipMessage }}</p>
          <p v-else-if="error && error.length" class="error">
            <span class="block" v-for="e in error">{{ e }}</span>
          </p>
          <div v-else-if="cartShipping[cart.id]"></div>
        </form>
      </div>

      <div class="mt-sm-10 mn-w-90x right-text">
        <p class="inl-b-sm" v-if="hasBundleDeal">(-) x {{ bundleDeal.free }}</p>

      </div>
    </div>
  </div>
</template>

<script setup>
  import {useUserStore} from "~/store/user";
  import {useCommonStore} from "~/store/common";
  import {useLanguageStore} from "~/store/language";
  import {useCartStore} from "~/store/cart";
  import {useDetailStore} from "~/store/detail";
  import {useProductHelper} from "~/composables/useProductHelper";
  import {useUtils} from "~/composables/useUtils";
  import {useConstants} from "~/composables/useConstants";
  import {usePriceHelper} from "../composables/usePriceHelper";

  const props = defineProps({
    checked: {
      type: Array
    },
    cart: {
      type: Object
    },
    isShipping: {
      type: Boolean,
      default: false
    },
    cartShipping: {
      type: Object,
      default() {
        return null
      }
    },
    error: {
      type: Array,
      default() {
        return []
      }
    },
    currentAddresses: {
      type: Array,
      default() {
        return []
      }
    },
    address: {
      type: Object,
      default() {
        return null
      }
    },
  });

  const emit = defineEmits(['current-shipping', 'shipping-changed', 'deleting', 'quantity']);
  const {checked, cart, cartShipping, address} = toRefs(props);

  const userStore = useUserStore();
  const {getUserToken} = userStore;

  const commonStore = useCommonStore();
  const {setToastError} = commonStore;
  const {t} = useI18n();

  const languageStore = useLanguageStore();
  const {langCode} = storeToRefs(languageStore);

  const cartStore = useCartStore();
  const {cartAction} = cartStore;

  const detailStore = useDetailStore();
  const {setProduct} = detailStore;

  const goToProduct = (product) => {
    setProduct(product);
  };

  const cbChecked = ref(checked.value);

  watch(checked, (value) => {
    cbChecked.value = value;
  });

  const isSingleShipping = computed(() => {
    return cartShipping.value[cart.value?.id]?.single_shipping;
  });

  const hasBundleDeal = computed(() => {
    return (productQuantity.value >= bundleDeal.value?.buy);
  });

  const bundleDeal = computed(() => {
    return product.value?.bundle_deal;
  });

  const cartId = computed(() => {
    return cart.value?.id;
  });

  const product = computed(() => {
    return cart.value?.flash_product;
  });

  const noteImage = computed(() => {
    return cart.value?.note?.image || '';
  });

  const noteMessage = computed(() => {
    return cart.value?.note?.message || '';
  });

  const noteImageInput = ref(null);
  const updatingNote = ref(false);
  const isEditingNote = ref(false);
  const draftNoteMessage = ref('');
  const lastSelectedNoteImageFile = ref(null);
  const pendingMessageAfterImagePick = ref('');

  const hasNoteData = computed(() => {
    return !!(noteImage.value || noteMessage.value);
  });

  const allowNoteMessage = computed(() => {
    const messageFlag = product.value?.note?.message;
    if (typeof messageFlag === 'boolean') return messageFlag;
    if (product.value?.allow_note !== undefined) return !!product.value?.allow_note;
    return !!noteMessage.value;
  });

  const allowNoteImage = computed(() => {
    const imageFlag = product.value?.note?.image;
    if (typeof imageFlag === 'boolean') return imageFlag;
    if (product.value?.allow_note_image !== undefined) return !!product.value?.allow_note_image;
    return !!noteImage.value;
  });

  const showNoteSection = computed(() => {
    return allowNoteMessage.value || allowNoteImage.value || hasNoteData.value;
  });

  const noteSectionTitle = computed(() => {
    const hasImage = allowNoteImage.value || !!noteImage.value;
    const hasMessage = allowNoteMessage.value || !!noteMessage.value;

    if (hasImage && hasMessage) {
      return t('cartProductTile.noteTitlePhotoMessage') || 'Your Photo and Message on product';
    }
    if (hasImage) {
      return t('cartProductTile.noteTitlePhoto') || 'Your Photo on product';
    }
    return t('cartProductTile.noteTitleMessage') || 'Your Message on product';
  });

  const noteImageSrc = computed(() => {
    if (!noteImage.value) return '';
    if (noteImage.value.startsWith('http://') || noteImage.value.startsWith('https://')  || noteImage.value.startsWith('blob:')) {
      return noteImage.value;
    }
    if (noteImage.value.startsWith('/')) {
      return noteImage.value;
    }
    return getImageURL(noteImage.value);
  });

  const currentNoteLength = computed(() => {
    return isEditingNote.value ? draftNoteMessage.value.length : noteMessage.value.length;
  });

  watch(noteMessage, (value) => {
    if (!isEditingNote.value) {
      draftNoteMessage.value = value || '';
    }
  }, {immediate: true});

  const productInventory = computed(() => {
    return cart.value?.updated_inventory;
  });

  const currentShipRule = computed(() => {
    let matched = null;
    if (address.value) {
      product.value?.shipping_rule?.shipping_places.forEach((obj) => {
        if (obj.country === address.value.country) {
          if (obj.state === address.value.state) {
            matched = obj;
            return;
          } else if (obj.state === 'ALL') {
            matched = obj;
          }
        } else if (obj.country === 'ALL') {
          if (!matched) {
            matched = obj;
          }
        }
      })
    }

    if (matched && !matched?.shipping_rule) {
      matched = {...matched, ...{shipping_rule: product.value?.shipping_rule}};
    }

    if (matched && cartShipping.value[cart.value?.id]) {
      cartShipping.value[cart.value?.id].shipping_type = shippingTypeIn.location;
      cartShipping.value[cart.value?.id].shipping_place = matched;
      updateCartShipping();
    }

    emit('current-shipping', {cart: cart.value?.id, shipping: matched});
    return matched;
  });


  const {getProductImage} = useProductHelper();
  const {productLink, getThumbImageURL, getImageURL} = useUtils();
  const {shippingTypeIn} = useConstants();

  const productImage = computed(() => {
    return getProductImage(product.value, inventoryAttributes.value);
  });

  const inventoryAttributes = computed(() => {
    return productInventory.value?.inventory_attributes
  });


  const {productPrice, prevPrice, reducedPercent} = usePriceHelper({product, productInventory});

  const currentAttr = computed(() => {
    return inventoryAttributes.value?.map(i => {
      return [i?.attribute_value?.attribute?.title, i?.attribute_value?.title]
    })
  });

  const title = computed(() => {
    return product.value?.title || ''
  });

  const maxQuantity = computed(() => {
    return parseInt(productInventory.value?.quantity)
  });

  const productQuantity = computed(() => {
    return parseInt(cart.value?.quantity)
  });

  const noShipMessage = computed(() => {
    const state = address.value?.stateTitle ? `${address.value?.stateTitle},` : ''
    return t('cartProductTile.noShipMessage', {state: state, country: address.value?.countryTitle})
  });

  const addressLine = computed(() => {
    if (!address.value) return '';
    const parts = [
      address.value.address_1,
      address.value.address_2,
      address.value.cityTitle || address.value.city,
      address.value.stateTitle,
      address.value.countryTitle
    ].filter(Boolean);
    return parts.join(', ');
  });


  const updateCartShipping = () => {
    emit('shipping-changed', cartShipping.value)
  };

  const deleting = async () => {
      emit('deleting', {id: cartId.value, isBundle: !!bundleDeal.value, user_token: await getUserToken()})
  };

  const valueChanged = (evt) => {
    const notePayload = {};
    const hasAnyNoteRequirement = allowNoteMessage.value || allowNoteImage.value;

    if (allowNoteMessage.value) {
      const message = (noteMessage.value || '').trim();
      if (!message) {
        setToastError('Message is required.');
        return;
      }
      notePayload.message = message;
    }

    if (allowNoteImage.value) {
      const image = lastSelectedNoteImageFile.value || noteImage.value || noteImageSrc.value || '';
      if (!image) {
        setToastError('Photo is required.');
        return;
      }
      notePayload.image = image;
    }

    emit('quantity', {
        bundleDeal: bundleDeal.value,
        product: product.value,
        inventory: productInventory.value,
        direction: evt.direction,
        note: hasAnyNoteRequirement ? notePayload : null
      }
    )
  };

  const saveNoteByActionApi = async ({message = null, imageFile = null}) => {
    if (!product.value?.id || !productInventory.value?.id) return {ok: false};

    const messageToSend = (message ?? noteMessage.value ?? '').trim();
    if (allowNoteMessage.value && !messageToSend) {
      setToastError('Message is required.');
      return {ok: false};
    }

    let imageToSend = imageFile || lastSelectedNoteImageFile.value;
    if (allowNoteImage.value && !imageToSend) {
      imageToSend = noteImage.value || noteImageSrc.value || '';
    }
    if (allowNoteImage.value && !imageToSend) {
      return {ok: false, needsImage: true};
    }

    const form = new FormData();
    form.append('user_token', await getUserToken());
    form.append('product_id', product.value.id);
    form.append('inventory_id', productInventory.value.id);
    form.append('quantity', 0);
    if (allowNoteMessage.value) {
      form.append('message', messageToSend);
    }
    if (allowNoteImage.value) {
      form.append('image', imageToSend);
    }

    updatingNote.value = true;
    try {
      const response = await cartAction({
        payload: {
          apiVal: form
        },
        lang: langCode.value
      });

      if (!response || response.status !== 200) {
        throw new Error(response?.data?.form?.[0] || response?.message || 'Unable to update note.');
      }
      lastSelectedNoteImageFile.value = imageToSend;
      return {ok: true};
    } catch (e) {
      setToastError(e?.message || 'Unable to update note.');
      return {ok: false};
    } finally {
      updatingNote.value = false;
    }
  };

  const triggerChangeImage = () => {
    if (!allowNoteImage.value) return;
    noteImageInput.value?.click();
  };

  const onNoteImageSelected = async (evt) => {
    const selectedFile = evt.target.files?.[0] || null;
    if (!selectedFile) return;
    const maxSize = 2 * 1024 * 1024;
    if (selectedFile.size > maxSize) {
      evt.target.value = '';
      setToastError(t('detailRight.photoHint') || 'JPG/PNG up to 2MB');
      return;
    }

    lastSelectedNoteImageFile.value = selectedFile;
    cart.value.note.image = selectedFile ? URL.createObjectURL(selectedFile) : null;

    const messageToSave = pendingMessageAfterImagePick.value || draftNoteMessage.value || noteMessage.value;
    const result = await saveNoteByActionApi({
      imageFile: selectedFile,
      message: messageToSave
    });
    if (result?.ok) {
      isEditingNote.value = false;
    }
    pendingMessageAfterImagePick.value = '';

    evt.target.value = '';
  };

  const onEditClick = async () => {
    if (!allowNoteMessage.value) return;
    if (!isEditingNote.value) {
      isEditingNote.value = true;
      draftNoteMessage.value = noteMessage.value || '';
      return;
    }
    cart.value.note.message = draftNoteMessage.value
    const result = await saveNoteByActionApi({
      message: draftNoteMessage.value
    });

    if (result?.ok) {
      isEditingNote.value = false;
      return;
    }

    if (result?.needsImage) {
      pendingMessageAfterImagePick.value = draftNoteMessage.value;
      setToastError('Select image once to save this text.');
      noteImageInput.value?.click();
    }
  };

</script>
