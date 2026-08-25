<template>
  <div
    v-if="$can('footer_link', 'view')"
    class="tab-sidebar"
  >
    <h4 class="title">{{ $t('admin.footer') }}</h4>

    <div class="dply-felx">
      <ul class="left-area">
        <li
          v-for="(value, index) in tabs"
          :key="index"
          :class="{active : value.tabId === activeTab}"
          @click.prevent="tabSelect(value)"
        >
          {{ value.title }}
        </li>
      </ul>

      <div
        class="right-area pos-rel"
      >
        <div
          v-if="loading"
          class="spinner-wrapper"
        >
          <spinner
            :radius="60"
            color="primary"
          />
        </div>
        <PartialsFooterLink
          v-if="tabId[0] === activeTab"
          :result="result"
          @remove-item="removeItem"
          @update-item="updateItem"
          @add-item="addItem"
          @result="result = Object.assign({}, $event)"
        />

        <PartialsFooterImageLink
          v-if="tabId[1] === activeTab"
          :result="result"
          @delete-item="deleteFooterImageLink"

        />
      </div>

    </div>
  </div>
</template>

<script setup>
  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";
  import {ability} from '~/composables/ability';

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {getAllList, getRequest, deleteData} = commonStore;
  const {allPages} = storeToRefs(commonStore);

  const {t} = useI18n();

  const tabs = ref([
    {
      title: t('admin.service'),
      tabId: 'service-about',
    },
    {
      title: t('admin.payment'),
      tabId: 'payment-social',
    }
  ]);

  const result = ref({
    about_links: [],
    service_links: [],
    payment_links: [],
    social_links: []
  });
  const tabId = ref(['service-about', 'payment-social']);
  const activeTab = ref('service');
  const loading = ref(false);

  const removeItem = ({source, index, deleted}) => {
    const v = {
      ...result.value[source][index],
      ...{deleted: deleted}
    };
    result.value[source].splice(index, 1);
    result.value[source].splice(index, 0, v);
  };

  const updateItem = ({source, index, value}) => {
    if (result.value[source][index]?.id) {
      result.value[source][index] = {...result.value[source][index], ...{updated: true}};
    }
    const v = {...result.value[source][index], ...{page_id: value.key}};
    result.value[source].splice(index, 1);
    result.value[source].splice(index, 0, v);
  };

  const addItem = ({source}) => {
    result.value[source].push({page_id: Object.keys(allPages.value)[0]})
  };

  const router = useRouter();
  const route = useRoute();

  const tabSelect = (val) => {
    if (val.tabId !== route.hash.replace('#', '')) {
      router.push({
        hash: `#${val.tabId}`
      })
    }
    activeTab.value = val.tabId
  };

  const fetchingData = async () => {
    loading.value = true;
    result.value = await getRequest({params: {}, api: 'getFooterLinks'});
    loading.value = false;
  };
  const deleteFooterImageLink = async (id) => {
    if (confirm(t('admin.dltMsg'))) {
      loading.value = true;
      await deleteData({params: id, api: 'deleteFooterImageLink', id: id});
      await fetchingData();
    }
  };

  onMounted(async () => {
    loading.value = true;
    activeTab.value = route.hash ? route.hash.replace('#', '') : tabs.value[0].tabId;
    if (!allPages.value) {
      await getAllList({api: 'getAllPages', action: 'setAllPages'});
    }
    if (ability.can('footer_link', 'view')) {
      await fetchingData();
    } else {
      loading.value = false;
    }
  });

</script>
