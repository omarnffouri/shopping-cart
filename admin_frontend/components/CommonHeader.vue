<template>
    <header :class="{'dots-open': dotsOpen}">
        <div class="dply-felx logo-wrapper">
            <button
                    @click.prevent="leftMenuToggle"
                    class="dply-felx j-center toggle-menu-btn"
            >
                <i class="ignore-click icon menu-icon mr-0"/>
            </button>
            <nuxt-link
                    to="/"
                    class="logo"
            >
                <img v-if="storeData"
                     :src="getImageURL(siteLogo)"
                     alt=""
                >
            </nuxt-link>
        </div>

        <div class="dply-felx grow right-wrap">

            <PartialsClearCacheBtn
                    color="primary"
                    class="outline-btn"
            />

            <div class="dply-felx j-left pos-rel">

                <dropdown
                        v-if="$can('language', 'view') && Object.keys(languages).length > 1"
                        :selected-key="currentLanguage.code"
                        :options="languages"
                        class="lang-down"
                        key-name="name"
                        @clicked="selectedLanguage"
                />

                <PartialsUserMessages
                        v-if="$can('message', 'view')"
                />
                <button
                        data-ignore="right-menu"
                        @click.prevent="rightMenuToggle"
                        class="dots-btn"
                >
                    <i class="ignore-click icon more-dots"/>
                </button>
                <div class="right-area"
                        id="right-menu"
                        v-outside-click="closeRightMenu"
                >
                    <button
                            v-if="$can('profile', 'view')"
                            @click.prevent="goProfile"
                    >
                        <i class="icon profile"/>
                        {{ $t('error.pro') }}
                    </button>
                    <button @click.prevent="loggingOut">
                        <i class="icon logout"/>
                        {{ $t('error.log') }}
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>

  import {useSiteSettingStore} from "~/store/site-setting";
  import {useSettingStore} from "~/store/setting";
  import {useLanguageStore} from "~/store/language";
  import {useUiStore} from "~/store/ui";
  import {storeToRefs} from "pinia";
  import {useCommonStore} from "~/store/common";
  import {useAuthStore} from "~/store/auth";
  import {useIndexStore} from "~/store/index";
  import {useUtils} from "../composables/useUtils";

  const authStore = useAuthStore();
  const {logUserOut} = authStore;

  const {getRequest} = useCommonStore();

  const siteSettingStore = useSiteSettingStore();
  const {siteSetting} = storeToRefs(siteSettingStore);

  const settingStore = useSettingStore();

  const {setting, storeData} = storeToRefs(settingStore);

  const {clearSetting} = settingStore;

  const languageStore = useLanguageStore();
  const {languages, currentLanguage} = storeToRefs(languageStore);

  const {hideSidebar, toggleSidebar} = useUiStore();

  const {defaultImage} = storeToRefs(useIndexStore());

  const {setLocale} = useI18n()
  const selectedLanguage = (data) => {
    setLocale(data.key);
    document.cookie = 'currentLanguage=' + data.key + '; path=/; max-age=' + 365 * 60 * 60 * 24;
    location.reload();
  };

  const siteLogo = computed(() => {
    return storeData.value?.image ?? siteSetting.value?.header_logo ?? defaultImage.value
  });

  const loggingOut = async () => {
    clearSetting();
    await getRequest({params: {}, api: 'logout'});
    logUserOut();
    return navigateTo('/login');
  };

  const leftMenuToggle = () => {
    toggleSidebar()
  };

    const {getImageURL } = useUtils();

  const dotsOpen = ref(false);

  const rightMenuToggle = () => {
    hideSidebar()
    dotsOpen.value = !dotsOpen.value
  };
  const closeRightMenu = () => {
    dotsOpen.value = false
  };

  const route = useRoute();
  const router = useRouter();

  const goProfile = () => {
    route.name !== 'profile' ? router.push({path: '/profile'}) : false
    dotsOpen.value = false
  };


</script>
