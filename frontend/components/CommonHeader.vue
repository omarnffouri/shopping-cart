<template>
  <header
      :class="{
      'no-banner': (topBannerLoaded && isTopBannerClosed) || !isPublic,
      'mobile-search-open': isMobileView && mobileSearchOpen
    }"
  >

    <banner
        v-if="!isTopBannerClosed"
        class="top-banner"
        :banner="topBanner"
        @close="closeTopBanner"
    />

    <div class="top-wrapper">
      <div class="container-fluid">

        <div ref="headerWrapperRef" class="wrap flex sided">
          <div class="left-side wrap flex gap-10">

            <dropdown
                v-if="Object.keys(languages).length"
                :selected-key="currentLanguage.code"
                :options="languages"
                :positionFixed="false"
                key-name="name"
                class="lang-dropdown"
                @clicked="selectedLanguage"
            />

            <a :href="`mailto:${email}`"
               class="flex gap-5"
            >
              <i class="icon email-icon"/>
              <span>
                {{ email }}
              </span>
            </a>

            <template v-if="phone">
              <span>|</span>
              <a :href="`tel:${phone}`"
                 class="flex gap-5"
              >
                <i class="icon phone-icon"/>
                <span>{{ phone }}</span>
              </a>
            </template>
          </div>

          <div class="flex right-side text-upper">
            <div class="flex gap-5"
                 v-if="!isLoggedIn">
              <nuxt-link
                  to="/login"
                  @click="beforeLogin"
                  class="flex gap-5"
              >
                <i class="icon login-icon"/>
                {{ $t('header.login') }}
              </nuxt-link>
            </div>

            <nuxt-link
                v-else
                to="/user/profile"
                class="flex gap-5"
            >
              <i class="icon user-icon"/>
              {{ $t('header.profile') }}
            </nuxt-link>
          </div>
        </div>
      </div>
    </div>

    <div class="header-sticky" :class="{sticky: headerSticky}">
      <div class="container-fluid flex pos-rel">
        <div class="left-area">
          <nuxt-link to="/" class="logo">
            <img :src="getImageURL(site_setting.header_logo)"
                 :alt="$t('footer.siteLogo')"
                 height="40"
                 width="139"
            >
          </nuxt-link>
        </div>

        <form
            v-if="!isMobileView || mobileSearchOpen"
            class="search-input grow"
            @submit.prevent="search"
        >
          <input
              ref="searchInputRef"
              @focus="openSearchPopup"
              @blur="blurSearchInput"
              type="text"
              :placeholder="$t('header.searchHere')"
              v-model="searchedText"
          >
          <button
              aria-label="submit"
              type="submit"
              class="flex"
          >
            <i class="icon search-icon"/>
          </button>

          <SearchPopup
              v-if="searchPopup"
              :searched-text="searchedText"
              @close="closeSearchPopup"
          />
        </form>

        <div class="right-area flex gap-15 right">
          <dropdown
              v-if="isMobileView && Object.keys(languages).length"
              :selected-key="currentLanguage.code"
              :options="languages"
              :positionFixed="false"
              key-name="name"
              class="lang-dropdown mobile-lang-dropdown"
              @clicked="selectedLanguage"
          />
          <div
            v-if="!isMobileView"
            class="pos-rel account-menu-anchor"
            v-outside-click="closeDropdown"
          >
            <button
              aria-label="submit"
              class="flex gap-10 account-btn"
              :class="{'has-retry-dot': showRetryBadge}"
              @click.prevent="toggleDropdown"
            >
              <span class="account-icon-wrap">
                <i class="icon user-icon black account-icon"/>
              </span>
              <span v-if="showRetryBadge" class="account-retry-dot"></span>
              <span class="account-text">{{ $t('header.account') }}</span>
              <i class="icon arrow-down black account-arrow"/>
            </button>
            <nuxt-link
                v-if="showRetryOrderPopup && !showDropdown"
                :to="retryOrderPath"
                class="retry-order-timer"
            >
              <span class="retry-order-copy">Complete your order:</span>
              <span class="retry-order-time">{{ retryOrderTimeLabel }}</span>
            </nuxt-link>
            <div class="dropdown" :class="{active: showDropdown}">
              <nuxt-link :to="{ path: '/user/orders'}" @click.prevent="closeDropdown">
                {{ $t('header.orders') }}
              </nuxt-link>
              <nuxt-link to="/user/abandoned_orders" @click.prevent="closeDropdown">
                {{ $t('header.abandoned_orders') }}   <span v-if="showRetryBadge" class="account-retry-dot"></span>
              </nuxt-link>
<!--              <nuxt-link to="/user/wishlists" @click.prevent="closeDropdown">-->
<!--                {{ $t('header.wishList') }}-->
<!--              </nuxt-link>-->
              <!-- <nuxt-link to="/user/compared" @click.prevent="closeDropdown">
                {{ $t('header.comparedList') }}
              </nuxt-link> -->
              <nuxt-link to="/user/vouchers" @click.prevent="closeDropdown">
                {{ $t('header.vouchers') }}
              </nuxt-link>
              <button
                aria-label="Logout"
                v-show="isLoggedIn"
                class="clear-btn"
                @click.prevent="userLogOut"
              >
                {{ $t('header.logout') }}
              </button>
            </div>
          </div>
          <button
              aria-label="search"
              class="header-search-btn flex"
              @click.prevent="handleHeaderSearch"
          >
            <i class="icon search-icon black"/>
          </button>
          <nuxt-link to="/cart" class="cart-btn flex pos-rel h-40x">
            <span v-if="cartCount" class="cart-badge">
              {{ cartCount }}
            </span>
            <i class="icon cart-icon black"/>
            <span class="title">{{ $t('header.cart') }}</span>
          </nuxt-link>
          <div
              v-if="isMobileView"
              class="pos-rel mobile-menu"
              v-outside-click="closeMobileMenu"
          >
            <button
                aria-label="menu"
                class="mobile-menu-btn"
                @click.prevent="toggleMobileMenu"
            >
              <span class="menu-dots"></span>
            </button>
            <nuxt-link
                v-if="showRetryOrderPopup && !mobileMenuOpen"
                :to="retryOrderPath"
                class="retry-order-timer"
            >
              <span class="retry-order-copy">Complete your order:</span>
              <span class="retry-order-time">{{ retryOrderTimeLabel }}</span>
            </nuxt-link>
            <div
                class="mobile-menu-dropdown"
                :class="{active: mobileMenuOpen}"
            >
              <nuxt-link
                  v-if="!isLoggedIn"
                  to="/login"
                  @click="beforeLogin"
              >
                {{ $t('header.login') }}
              </nuxt-link>
              <button
                  class="mobile-menu-account-btn"
                  @click.prevent="toggleMobileAccount"
              >
                {{ $t('header.account') }}
              </button>
              <div
                v-if="mobileAccountOpen"
                class="mobile-menu-account-list"
              >
                <nuxt-link to="/user/orders" @click.prevent="closeMobileMenu">
                  {{ $t('header.orders') }}
                </nuxt-link>
                <nuxt-link to="/user/abandoned_orders" @click.prevent="closeMobileMenu">
                  {{ $t('header.abandoned_orders') }}
                </nuxt-link>
<!--                <nuxt-link to="/user/wishlists" @click.prevent="closeMobileMenu">-->
<!--                  {{ $t('header.wishList') }}-->
<!--                </nuxt-link>-->
                <!-- <nuxt-link to="/user/compared" @click.prevent="closeMobileMenu">
                  {{ $t('header.comparedList') }}
                </nuxt-link> -->
                <nuxt-link to="/user/vouchers" @click.prevent="closeMobileMenu">
                  {{ $t('header.vouchers') }}
                </nuxt-link>
                <button
                  v-show="isLoggedIn"
                  class="clear-btn"
                  @click.prevent="userLogOut"
                >
                  {{ $t('header.logout') }}
                </button>
              </div>
              <!-- language moved to main bar; remove from menu -->
            </div>
          </div>
        </div>
      </div>
      <div class="bottom-area text-nowrap">

        <div v-if="!isProductDetailPage"
             class="container-fluid">
          <div class="flex sided">
            <div>
              <nuxt-link
                  v-for="(item, index) in headerLeft"
                  :key="index"
                  :to="getUrl(item)"
              >
                <span>
                  {{ getTitle(item) }}
                </span>
              </nuxt-link>
            </div>
            <div>

              <nuxt-link
                  v-for="(item, index) in headerRight"
                  :key="index"
                  :to="getUrl(item)"
              >
                <span>
                  {{ getTitle(item) }}
                </span>
              </nuxt-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style>
@media (max-width: 768px) {
  .bottom-area {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
  }

  .bottom-area .flex.sided {
    display: inline-flex;
    min-width: max-content;
    gap: 16px;
  }

  .bottom-area a {
    display: inline-block;
  }
}
</style>

<script setup>
import {storeToRefs} from "pinia";
import {computed, onUnmounted} from "vue";
import {navigateTo} from "nuxt/app";
import {useConstants} from "~/composables/useConstants";
import {onMounted} from "vue";
import {useLanguageStore} from "~/store/language";
import {useCartStore} from "~/store/cart";
import {useUserStore} from "~/store/user";
import {useCommonStore} from "~/store/common";
import {useListingStore} from "~/store/listing";
import {useAuthStore} from "~/store/auth";
import {useUtils} from "~/composables/useUtils";

const languageStore = useLanguageStore();
const {setDefaultLanguage, getLangData} = languageStore;
const {currentLanguage, languages, langCode} = storeToRefs(languageStore);

const cartStore = useCartStore();
const {emptyCartProduct, setCartCount} = cartStore;
const {cartCount} = storeToRefs(cartStore);

const userStore = useUserStore();
const {profile} = storeToRefs(userStore);
const {setProfile, getUserToken} = userStore;

const commonStore = useCommonStore();
const {site_setting, setting, topBanner, headerLinks} = storeToRefs(commonStore);
const {bgGetRequest, postRequest, setToastError, setToastMessage} = commonStore;

const listingStore = useListingStore();
const {searched} = storeToRefs(listingStore);
const {updateSearch, setSearchedSuggestion} = listingStore;

const authStore = useAuthStore();
const {authenticated} = storeToRefs(authStore);
const {logUserOut} = authStore;

const isLoggedIn = computed(() => {
  return authenticated.value || false;
});

const route = useRoute();

const isProductDetailPage = computed(() => {
  return !!route.params.id
})
const beforeLogin = () => {
  if (import.meta?.client) {
    const redirectionUrl = route.fullPath;
    localStorage.setItem('redirection_url', redirectionUrl);
  }
};

const showDropdown = ref(false);
const ignoreDropdownClose = ref(false);
const mobileAccountOpen = ref(false);
const mobileMenuOpen = ref(false);

const closeDropdown = () => {
    if (ignoreDropdownClose.value) {
      return;
    }
  showDropdown.value = false;
};

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
    mobileAccountOpen.value = false;
};

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
    if (!mobileMenuOpen.value) {
      mobileAccountOpen.value = false;
    }
  };

const toggleMobileAccount = () => {
    mobileAccountOpen.value = !mobileAccountOpen.value;
};

const userLogOut = async () => {
  closeDropdown();

  await bgGetRequest({
    params: '',
    lang: langCode.value,
    api: 'logout'
  });

  logUserOut();
  emptyCartProduct();

  const route = useRoute();

  const isAuthPage = route.meta.middleware.findIndex(i => {
    return i === 'auth'
  });
  if (isAuthPage > -1) {
    return navigateTo('/');
  }
};

const {setLocale} = useI18n();

const selectedLanguage = (data) => {
  setLocale(data.key);
  document.cookie = 'currentLanguage=' + data.key + '; path=/; max-age=' + 365 * 60 * 60 * 24;
  location.reload();
};

const headerSticky = ref(false);
const topBannerLoaded = ref(false);
const isTopBannerClosed = ref(false);
const searchPopup = ref(false);
const searchFocused = ref(false);
const searchedText = ref('');
const searchInputRef = ref(null);
const isMobileView = ref(false);
const mobileSearchOpen = ref(false);
const retryOrderTimeLeftMs = ref(0);
const retryOrderId = ref('');
const retryOrderPopupDismissed = ref(false);
const RETRY_ORDER_TIMER_KEY = 'retry_order_timer_expires_at';
const RETRY_ORDER_ID_KEY = 'retry_order_id';
let retryOrderTimerInterval = null;

const {status, paymentStatusIn} = useConstants();
const {getImageURL, getUrl, getTitle, getTimeZone} = useUtils();

const isXSmallerDevice = computed(() => window.innerWidth <= 576);
const headerLeft = computed(() => headerLinks.value?.left || []);
const headerRight = computed(() => headerLinks.value?.right || []);
const isPublic = computed(() => parseInt(topBanner.value?.status) === status.PUBLIC);
const cartCountCom = computed(() => profile.value?.cart_count);
const username = computed(() => profile.value?.name?.split(' ')[0]);
const email = computed(() => setting.value?.email);
const phone = computed(() => setting.value?.phone);
const sellerSignUp = computed(() => setting.value?.vendor_registration);
const showRetryBadge = ref(false);
const showRetryOrderTimer = computed(() => retryOrderTimeLeftMs.value > 0 && !!retryOrderId.value);
const showRetryOrderPopup = computed(() => showRetryOrderTimer.value && !retryOrderPopupDismissed.value);

const retryOrderPath = computed(() => {
  if (!retryOrderId.value) return '/cart';
  return `/user/abandoned_order/${retryOrderId.value}?retry_payment=true`;
});

const retryOrderTimeLabel = computed(() => {
  const totalSeconds = Math.max(0, Math.ceil(retryOrderTimeLeftMs.value / 1000));
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

watch(cartCountCom, (value) => {
  setCartCount(value);
})

watch(searchedText, () => {
  if (!searchPopup.value && searchFocused.value) {
    openSearchPopup();
  }
});

watch(retryOrderId, (next, prev) => {
  if (next && next !== prev) {
    retryOrderPopupDismissed.value = false;
  }
});

watch(() => route.fullPath, async () => {
  await hydrateRetryTimerFromOrders();
});


const handleIntersection = (entries) => {
  entries.forEach((entry) => {
    headerSticky.value = !entry.isIntersecting;
  });
};

const closeTopBanner = () => {
  const topBannerClosed = useCookie('topBannerClosed', {
    maxAge: 60 * 60 * 24 * 7 * 30,
    path: '/',
    secure: true
  });

  topBannerClosed.value = true;
  isTopBannerClosed.value = true;
};

const openSearchPopup = () => {
  if (searchedText.value.length > 0) {
    searchPopup.value = true;
  }
  searchFocused.value = true;
};

const blurSearchInput = () => {
  setTimeout(() => {
    searchFocused.value = false;
    closeSearchPopup();
    if (isMobileView.value) {
      mobileSearchOpen.value = false;
    }
  }, 150);
};

const closeSearchPopup = () => {
  setTimeout(() => {
    searchPopup.value = false;
  }, 200);
};

const setQFromRoute = () => {
  searchedText.value = route?.query?.q || '';
};

const search = () => {
  if (searchedText.value && (searchedText.value !== searched.value || route.name !== 'search')) {
    router.push({path: `/search?q=${searchedText.value}`});
    updateSearch(searchedText.value);
  }
};

const handleHeaderSearch = async () => {
  if (isMobileView.value) {
    if (!mobileSearchOpen.value) {
      mobileSearchOpen.value = true;
      await nextTick();
      searchInputRef.value?.focus();
      openSearchPopup();
      return;
    }
  }

  if (!searchedText.value) {
    searchInputRef.value?.focus();
    openSearchPopup();
    return;
  }
  search();
};

const headerWrapperRef = ref(null);
let observer = null;

const topBannerClosed = useCookie('topBannerClosed');

if (topBannerClosed.value !== null) {
  isTopBannerClosed.value = topBannerClosed.value;
  topBannerLoaded.value = true;
} else {
  isTopBannerClosed.value = false;
  topBannerLoaded.value = true;
}

const handleResize = () => {
  isMobileView.value = window.innerWidth <= 768;
  if (!isMobileView.value) {
    mobileSearchOpen.value = false;
  }
};

const syncRetryOrderTimer = () => {
  if (!import.meta.client) return;

  const expiresAtRaw = localStorage.getItem(RETRY_ORDER_TIMER_KEY);
  const orderIdRaw = localStorage.getItem(RETRY_ORDER_ID_KEY);
  retryOrderId.value = orderIdRaw || '';

  if (!expiresAtRaw) {
    retryOrderTimeLeftMs.value = 0;
    return;
  }

  const expiresAt = Number(expiresAtRaw);
  if (!Number.isFinite(expiresAt)) {
    retryOrderTimeLeftMs.value = 0;
    localStorage.removeItem(RETRY_ORDER_TIMER_KEY);
    localStorage.removeItem(RETRY_ORDER_ID_KEY);
    return;
  }

  const remainingMs = Math.max(0, expiresAt - Date.now());
  retryOrderTimeLeftMs.value = remainingMs;

  if (remainingMs === 0) {
    onRetryOrderTimerExpired();
    retryOrderId.value = '';
    localStorage.removeItem(RETRY_ORDER_TIMER_KEY);
    localStorage.removeItem(RETRY_ORDER_ID_KEY);
  }
};

const onRetryOrderTimerExpired = async () => {
    // const params = {
    //     order_id: retryOrderId.value,
    //     message: "Payment failed!",
    //     title: 'System Canceled',
    // };
    // params.user_token = await getUserToken();
    //
    // const data = await postRequest({
    //     api: 'cancelOrder',
    //     params: params,
    //     lang: langCode.value
    // })
    //
    // if (data?.status === 200) {
    //     setToastMessage(data.message)
    // } else if (data?.status === 204) {
    //     setToastError(data.message)
    // } else {
    //     setToastError(data.data?.form.join(', '))
    // }
};

const clearRetryOrderTimer = () => {
  retryOrderTimeLeftMs.value = 0;
  retryOrderId.value = '';
  retryOrderPopupDismissed.value = false;
  if (!import.meta.client) return;
  localStorage.removeItem(RETRY_ORDER_TIMER_KEY);
  localStorage.removeItem(RETRY_ORDER_ID_KEY);
};

const dismissRetryOrderPopup = () => {
  if (!isMobileView.value) return;
  if (!showRetryOrderTimer.value || retryOrderPopupDismissed.value) return;
  retryOrderPopupDismissed.value = true;
};

const restoreRetryOrderPopup = () => {
  if (!isMobileView.value) return;
  if (!showRetryOrderTimer.value) return;
  retryOrderPopupDismissed.value = false;
};

const parseOrderCreatedAt = (order) => {
  const candidates = [order?.created_at, order?.createdAt, order?.created];
  for (const value of candidates) {
    if (!value) continue;
    const parsed = new Date(value).getTime();
    if (Number.isFinite(parsed)) return parsed;
  }
  return 0;
};

const hydrateRetryTimerFromOrders = async () => {
  if (!import.meta.client) return;

  try {
    const data = await postRequest({
      api: 'orderExpiringSoon',
      params: {
        time_zone: getTimeZone(),
        user_token: await getUserToken(),
      },
      lang: langCode.value
    });

    if (data?.status === 200) {
      const order = data.data;
      showRetryBadge.value = order.show_retry_dot;

      if (!order) {
        clearRetryOrderTimer();
        return;
      }

      if (!order.expires_at_ms) {
        clearRetryOrderTimer();
        return;
      }

      if (Number(order.expires_at_ms) <= Date.now()) {
        clearRetryOrderTimer();
        return;
      }

      localStorage.setItem(RETRY_ORDER_TIMER_KEY, String(order.expires_at_ms));
      localStorage.setItem(RETRY_ORDER_ID_KEY, String(order.id));
      syncRetryOrderTimer();
    } else {
      clearRetryOrderTimer();
    }
  } catch (error) {

  }
};

onMounted(async () => {
  handleResize();
  window.addEventListener('resize', handleResize);
  window.addEventListener('pointerdown', dismissRetryOrderPopup, true);
  window.addEventListener('pointerup', restoreRetryOrderPopup, true);
  window.addEventListener('pointercancel', restoreRetryOrderPopup, true);
  window.addEventListener('storage', syncRetryOrderTimer);
  window.addEventListener('retry-order-timer-updated', syncRetryOrderTimer);
  syncRetryOrderTimer();
  await hydrateRetryTimerFromOrders();
  retryOrderTimerInterval = window.setInterval(syncRetryOrderTimer, 1000);

  setQFromRoute();
  updateSearch(searchedText.value);
  if (cartCountCom.value) {
    setCartCount(cartCountCom.value);
  }

  await nextTick();

  let rootMargin = '0px 0px 0px 0px';

  if (isXSmallerDevice.value) {
    rootMargin = '40px 0px 0px 0px';
  }

  observer = new IntersectionObserver(handleIntersection, {
    root: null,
    rootMargin: rootMargin,
    threshold: 0,
  });
  observer.observe(headerWrapperRef.value);
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
  window.removeEventListener('pointerdown', dismissRetryOrderPopup, true);
  window.removeEventListener('pointerup', restoreRetryOrderPopup, true);
  window.removeEventListener('pointercancel', restoreRetryOrderPopup, true);
  window.removeEventListener('storage', syncRetryOrderTimer);
  window.removeEventListener('retry-order-timer-updated', syncRetryOrderTimer);
  if (retryOrderTimerInterval) {
    window.clearInterval(retryOrderTimerInterval);
    retryOrderTimerInterval = null;
  }
  if (observer) {
    observer.disconnect();
  }
});
</script>
