import {storeToRefs} from "pinia";
import {useUserStore} from "../store/user";
import {useLanguageStore} from "../store/language";
import {useCommonStore} from "../store/common";
import {useAuthStore} from "../store/auth";
import {useCartStore} from "../store/cart";
import {useDetailStore} from "../store/detail";

export function useCartHelper({product, productInventory, emit, notePayload = null}) {

    const ajaxingWishlist = ref(false);
    const ajaxing = ref(false);
    const buyingNow = ref(false)
    const quantity = ref(1);
    const cartError = ref({
        attribute: null,
        quantity: null,
    });
    const router = useRouter();

    const languageStore = useLanguageStore();
    const {langCode} = storeToRefs(languageStore);

    const cartStore = useCartStore();
    const {cartAction, buyNow, getCartByUser, cartChanged} = cartStore;

    const userStore = useUserStore();
    const {getUserToken} = userStore;
    const {profile} = storeToRefs(userStore);

    const commonStore = useCommonStore();
    const {setting} = storeToRefs(commonStore);
    const {setToastError, postRequest, setToastMessage} = commonStore;

    const authStore = useAuthStore();
    const {authenticated} = storeToRefs(authStore);

    const detailStore = useDetailStore();
    const {updateWishlist} = detailStore;
    const {t} = useI18n();

    const wishListed = computed(() => profile.value?.id && product.value?.wishlisted);

    const wishListAction = async () => {
        if(!authenticated.value){
            return navigateTo('/login');
        }
        ajaxingWishlist.value = true;

        const data = await postRequest({
            api: 'userWishlistAction',
            params: {
                product_id: product.value.id
            }
        });

        ajaxingWishlist.value = false;

        if(data?.status === 200){
            setToastMessage(data.message)
            updateWishlist(data)
        }else{
            setToastError(data.data.form.join(', '))
        }
    };

    const emitCartError = () => {
        emit('cart-error', cartError.value)
    };

    const resolveNoteRules = () => {
        const noteObj = product.value?.note || {};
        const requireMessage = typeof noteObj?.message === 'boolean'
            ? noteObj.message
            : !!product.value?.allow_note;
        const requireImage = typeof noteObj?.image === 'boolean'
            ? noteObj.image
            : !!product.value?.allow_note_image;
        return {requireMessage, requireImage, enabled: requireMessage || requireImage};
    };

    const resolveNotePayload = () => {
        const {requireMessage, requireImage, enabled} = resolveNoteRules();
        if (!enabled || !notePayload?.value) return null;

        const image = notePayload.value.image || null;
        const message = (notePayload.value.message || '').trim();

        if (requireMessage && !message) return null;
        if (requireImage && !image) return null;

        return {image, message, requireMessage, requireImage};
    };

    const addToCart = async (isBuyNow = false) => {
        if (!setting.value?.guest_checkout) {
            if (!authenticated.value) {
                return navigateTo('/login');
            }
        }

        cartError.value = {
            attribute: null,
            quantity: null
        };

        if (!product.value?.in_stock) {
            setToastError(t('detailRight.outOfStock'))
            return false
        }
        if (product.value?.attribute?.length > 0 && !productInventory.value?.id) {
            cartError.value.attribute = 'Inventory ID is missing. Please select attributes properly.';
            emitCartError();
            return false;
        }
        if (product.value?.attribute?.length && Object.values(productInventory.value).length === 0) {
            const attr = product.value?.attribute.map(i => {
                return i?.title
            })
            cartError.value.attribute = t('detailRight.requiredAttributes')
            if (attr.length) {
                cartError.value.attribute += `(${attr.join(' / ')})`
            }

            emitCartError();
            return false
        }

        if (productInventory.value.quantity < quantity.value) {
            cartError.value.quantity = t('detailRight.exceedsInventory')
            emitCartError()
            return false
        }

        const noteRules = resolveNoteRules();
        if (noteRules.enabled) {
            const note = resolveNotePayload();
            if (!note) {
                if (noteRules.requireImage && noteRules.requireMessage) {
                    setToastError(t('detailRight.noteRequired') || 'Photo and message are required for this product.');
                } else if (noteRules.requireImage) {
                    setToastError(t('detailRight.noteImageRequired') || 'Photo is required.');
                } else {
                    setToastError(t('detailRight.noteMessageRequired') || 'Message is required.');
                }
                return false;
            }
        }

        if (isBuyNow) {
            const addResponse = await buyNowProduct();
            if (addResponse?.status !== 200) {
                const apiError = addResponse?.data?.form?.[0] || addResponse?.message || t('shipping.noProductSelected')
                setToastError(apiError)
                return addResponse
            }

            await getCartByUser({lang: langCode.value})

            const cartProducts = cartStore.cartProducts || []
            const latestCartItem = [...cartProducts].sort((a, b) => parseInt(b.id) - parseInt(a.id))[0]
            const selectedCartId = addResponse?.data?.id
                || addResponse?.data?.cart_id
                || addResponse?.data?.cart?.id
                || latestCartItem?.id

            if (selectedCartId) {
                await cartChanged({
                    payload: {
                        checked: [selectedCartId]
                    },
                    lang: langCode.value
                })
            }

            await router.push({path: '/checkout'})
            return addResponse
        }  else {
            return await cartAdd()
        }
    };

    const buyNowProduct = async () => {
        const {langCode} = storeToRefs(useLanguageStore());

        const userStore = useUserStore()
        const {getUserToken} = userStore
        buyingNow.value = true;

        const userToken = await getUserToken()

        let payload = {
            user_token: userToken,
            product_id: product.value.id,
            inventory_id: productInventory.value.id,
            quantity: quantity.value,
        }
        const payloadWithNotes = updateNote(userToken)

        if (payloadWithNotes) {
            payload = payloadWithNotes;
        }
        const data = await buyNow({
            payload: payload,
            lang: langCode.value
        })

        buyingNow.value = false;

        return data;
    };

    const cartAdd = async () => {
        ajaxing.value = true;
        const userToken = await getUserToken();

        let apiVal = {
            user_token: userToken,
            product_id: product.value.id,
            inventory_id: productInventory.value?.id,
            quantity: quantity.value,
        };

        const apiValWithNotes = updateNote(userToken)

        if (apiValWithNotes) {
            apiVal = apiValWithNotes;
        }

        const data = await cartAction({
            payload: {
                user_token: userToken,
                apiVal: apiVal,
                isBundle: !!product.value?.bundle_deal,
                storeVal: {
                    product: {
                        id: product.value.id,
                        title: product.value.title,
                        offered: product.value.offered,
                        selling: product.value.selling,
                        image: product.value.image,
                        shipping_rule: product.value.shipping_rule
                    },
                    inventory: productInventory.value,
                    quantity: quantity.value,
                    selected: 1,
                    offered: 0,
                    bundle_deal: product.value?.bundle_deal,
                    shipping_type: 1
                }
            },
            lang: langCode.value
        })
        ajaxing.value = false;
        return data;
    };

    function updateNote(userToken){
        const note = resolveNotePayload();
        if (note) {
            const form = new FormData();
            form.append('user_token', userToken);
            form.append('product_id', product.value.id);
            form.append('inventory_id', productInventory.value?.id || '');
            form.append('quantity', quantity.value);
            if (note.requireMessage) {
                form.append('message', note.message);
            }
            if (note.requireImage) {
                form.append('image', note.image);
            }
            return form;
        }

        return null
    }

    return {wishListAction, cartAdd, cartError, ajaxingWishlist, buyingNow,
        addToCart, quantity, ajaxing, wishListed}
}
