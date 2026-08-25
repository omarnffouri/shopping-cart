import Service from '@/services/service.js'

import {useCommonStore} from "~/store/common";
import {useAuthStore} from "./auth";
import json from '~/jsConfig.json'
import {prepareGetUrl} from "../utils/fetchClient";
import {useUserStore} from "./user";
import {showError} from "nuxt/app";
import {storeToRefs} from "pinia";
import {useTracking} from "~/composables/useTracking";

const state = () => ({
  cartCount: 0,
  cartProducts: [],
  checkoutDeliveryDate: '',
  checkoutDeliveryTimeSlot: '',
  checkoutSelectedDeliveryMethod: '',
  checkoutUserDeliveryTypeId: null
});

const actions = {
  getCartProductsSignature(products = []) {
    return (products || [])
      .map((item) => `${item?.id || ''}`)
      .filter((id) => id)
      .sort()
      .join(',');
  },
  emptyCartProduct() {
    this.cartProducts = [];
    this.cartCount = 0;
    this.clearCheckoutDeliverySchedule();
  },
  setCheckoutDeliverySchedule(payload = {}) {
    const {
      deliveryDate,
      deliveryTimeSlot,
      selectedDeliveryMethod,
      userDeliveryTypeId
    } = payload;

    if (deliveryDate !== undefined) {
      this.checkoutDeliveryDate = deliveryDate || '';
    }
    if (deliveryTimeSlot !== undefined) {
      this.checkoutDeliveryTimeSlot = deliveryTimeSlot || '';
    }
    if (selectedDeliveryMethod !== undefined) {
      this.checkoutSelectedDeliveryMethod = selectedDeliveryMethod || '';
    }
    if (userDeliveryTypeId !== undefined) {
      this.checkoutUserDeliveryTypeId = userDeliveryTypeId || null;
    }
  },
  clearCheckoutDeliverySchedule() {
    this.checkoutDeliveryDate = '';
    this.checkoutDeliveryTimeSlot = '';
    this.checkoutSelectedDeliveryMethod = '';
    this.checkoutUserDeliveryTypeId = null;
  },
  subtractCartProductCount(payload) {
    this.cartProducts = this.cartProducts.filter(i => {
      return parseInt(i.selected) !== payload?.status;
    });
    this.cartCount = parseInt(this.cartCount) - parseInt(payload.qty);
  },
  setCartCount(count) {
    this.cartCount = count;
  },
  setCartProducts(cartProducts) {
    const previousSignature = this.getCartProductsSignature(this.cartProducts);
    const nextSignature = this.getCartProductsSignature(cartProducts);

    this.cartProducts = cartProducts;
    this.cartCount = cartProducts.reduce((accum, item) => accum + parseInt(item.quantity), 0);

    if (!cartProducts.length) {
      this.clearCheckoutDeliverySchedule();
      return;
    }

    if (previousSignature && previousSignature !== nextSignature) {
      const previousIds = previousSignature.split(',').filter(Boolean);
      const nextIds = new Set(nextSignature.split(',').filter(Boolean));
      const anyRemoved = previousIds.some((id) => !nextIds.has(id));
      if (anyRemoved) {
        this.clearCheckoutDeliverySchedule();
      }
    }
  },
  cartProductAction(cartProduct) {
    const index = this.cartProducts.findIndex((obj) => {
      return obj.id === cartProduct.id;
    });

    if (index === -1) {
      this.cartProducts.push(cartProduct);
    } else {
      this.cartProducts[index].quantity = 0;
      this.cartProducts[index].quantity = cartProduct.quantity;
    }
  },
  deleteCart(cartProduct) {
    const index = this.cartProducts.findIndex((obj) => {
      return obj.id === cartProduct.id
    });
    this.cartProducts.splice(index, 1);
    this.cartCount -= parseInt(cartProduct.quantity);
  },

  async getCartByUser({lang}) {
    try {
      const {token} = useAuthStore();
      const {getUserToken} = useUserStore();

      const data = await Service.getRequest(`${json.api.cartByUser}?${prepareGetUrl({
        user_token: await getUserToken()
      })}`, token, lang);

      if (data?.status === 200) {
        this.setCartProducts(data.data)
        if (Array.isArray(data?.data) && data.data.length === 0 && this.checkoutUserDeliveryTypeId) {
          this.clearCheckoutDeliverySchedule();
        }
      } else {
        showError({
          statusCode: data?.status,
          message: data?.message
        })
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async buyNow({payload, lang}) {
    try {
      const authStore = useAuthStore();
      const {token} = storeToRefs(authStore);
      const data = await Service.postRequest(json.api.buyNow, payload, token.value)

      if (data?.status === 200) {
        await this.getCartByUser({lang: lang})
      }
      return data

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async cartAction({payload, lang}) {
    try {

      const {trackAddToCart, trackRemoveFromCart} = useTracking()

      const {setToastMessage, setToastError, setting} = useCommonStore()
      const authStore = useAuthStore();
      const {token} = storeToRefs(authStore);

      const hasStoreVal = !!payload?.storeVal?.product;
      const apiQuantity = Number(
        payload?.apiVal instanceof FormData
          ? payload.apiVal.get('quantity')
          : payload?.apiVal?.quantity
      );

      if (hasStoreVal && Number.isFinite(apiQuantity)) {
        if (apiQuantity > 1) {
          trackAddToCart({
            value: payload.storeVal.product.selling,
            currency: setting.currency || 'AED',
            items: [
              {
                item_id: payload.storeVal.product.id,
                item_name: payload.storeVal.product.title,
                price: payload.storeVal.product.selling,
                quantity: payload.storeVal.quantity,
              }
            ]
          })
        } else {
          trackRemoveFromCart({
            value: payload.storeVal.product.selling,
            currency: setting.currency || 'AED',
            items: [
              {
                item_id: payload.storeVal.product.id,
                item_name: payload.storeVal.product.title,
                price: payload.storeVal.product.selling,
                quantity: Math.abs(payload.storeVal.quantity),
              }
            ]
          })
        }
      }

      const apiVal = payload.apiVal
      const storeVal = payload.storeVal

      const data = await Service.postRequest(json.api.cartAction, apiVal, token.value);

      if (data?.status === 200) {
        const {t} = useNuxtApp().$i18n;
        if (hasStoreVal) {
          setToastMessage(t('cart.productAdded'))
        }
        await this.getCartByUser({lang: lang})
      } else if (data?.status === 201) {

        setToastError(data?.data?.form[0])

      } else {
        showError({
          statusCode: data?.status,
          message: data?.message
        })
      }

      return data

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async cartDelete({payload, lang}) {
    try {
      const authStore = useAuthStore();
      const {token} = storeToRefs(authStore);

      const data = await Service.deleteRequest(`${json.api.cartDelete}/${payload.id}`, token.value);
      if (data?.status === 200) {
        const {trackRemoveFromCart} = useTracking()
        trackRemoveFromCart({
          items: [{item_id: data.data.id, quantity: data.data.quantity}],
        })
        await this.getCartByUser({lang: lang});
        // this.deleteCart(data.data)
      } else {
        showError({
          statusCode: data?.status,
          message: data?.message
        });
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      });
    }
  },
  async cartChanged({payload, lang}) {
    try {
      const {setToastError} = useCommonStore();
      const authStore = useAuthStore();
      const {token} = storeToRefs(authStore);

      const normalizedChecked = (payload.checked || [])
        .map((id) => parseInt(id))
        .filter((id) => !Number.isNaN(id));

      const req = {
        checked: normalizedChecked,
        unchecked: [],
        isBundle: false
      };

      this.cartProducts.forEach((obj, key) => {
        if (req.checked.indexOf(parseInt(obj.id)) === -1) {
          req.unchecked.push(obj.id);
          this.cartProducts[key].selected = 2;

        } else {
          this.cartProducts[key].selected = 1;
        }
        if (!req.isBundle && obj.bundle_deal) {
          req.isBundle = true;
        }
      });

      const data = await Service.postRequest(json.api.cartChanged, req, token.value);
      if (data?.status !== 200) {
        setToastError(data?.data?.form[0]);
      } else {
        if (req.isBundle) {
          await this.getCartByUser({lang: lang});
        }
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      });
    }
  },
  async updateCartShipping(payload) {
    try {
      const authStore = useAuthStore();
      const {token} = storeToRefs(authStore);
      const data = await Service.postRequest(json.api.updateCartShipping, payload, token.value);

      if (data?.status === 200) {
        this.cartProducts = data.data;
      }
      return data;

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      });
    }
  },
}

export const useCartStore = defineStore('cart', {
  state,
  actions
});
