<template>
  <div
    class="main-slider"
    :class="{'mn-h-500x': loading}"
  >
    <div
      v-if="loading"
      class="spinner-wrapper"
    >
      <spinner
        :radius="60"
        color="primary"
        class="mr-15"
      />
    </div>

    <transition
      name="fade"
      mode="out-in"
    >
      <div
        v-if="!loading"
      >
        <div class="left">
          <PartialsImageSlider
            v-if="getDataFromObject(result, 'main.length')"
            :image-list="result.main"
            @delete="deleteItem($event)"
          />
          <div
            v-else
            class="btn-wrapper"
          >
            <nuxt-link
              v-if="$can('home_slider', 'create')"
              to="/home-slider/add?type=1"
              class="primary-btn button"
            >
              {{ $t('admin.addImg') }}
            </nuxt-link>
          </div>
        </div>
        <!--left-->
        <div class="right">
          <div class="img-wrap">
            <template v-if="result.right_top">
              <img
                :src="getImageURL(result.right_top.image)"
                alt=""
              >
              <div class="btn-wrapper">
                <nuxt-link
                  v-if="$can('home_slider', 'edit')"
                  class="primary-btn button"
                  :to="`/home-slider/${result.right_top.id}`"
                >
                  {{ $t('category.edit') }}
                </nuxt-link>
                <button
                  v-if="$can('home_slider', 'delete')"
                  class="primary-btn"
                  @click.prevent="deleteItem(result.right_top.id)"
                >
                  {{ $t('category.delete') }}
                </button>
              </div>
            </template>

            <div v-else class="btn-wrapper">
              <nuxt-link
                v-if="$can('home_slider', 'create')"
                to="/home-slider/add?type=2"
                class="primary-btn button"
              >
                {{ $t('admin.addImg') }}
              </nuxt-link>
            </div>
          </div>

          <div class="img-wrap">
            <template v-if="result.right_bottom">
              <img
                :src="getImageURL(result.right_bottom.image)"
                alt=""
              >
              <div class="btn-wrapper">
                <nuxt-link
                  v-if="$can('home_slider', 'edit')"
                  class="primary-btn button"
                  :to="`/home-slider/${result.right_bottom.id}`"
                >
                  {{ $t('category.edit') }}
                </nuxt-link>
                <button
                  v-if="$can('home_slider', 'delete')"
                  class="primary-btn"
                  @click.prevent="deleteItem(result.right_bottom.id)"
                >
                  {{ $t('category.delete') }}
                </button>
              </div>
            </template>
            <div v-else class="btn-wrapper">
              <nuxt-link
                v-if="$can('home_slider', 'create')"
                to="/home-slider/add?type=3"
                class="primary-btn button"
              >
                {{ $t('admin.addImg') }}
              </nuxt-link>
            </div>
          </div>
        </div>
        <!--right-->
      </div>
    </transition>
  </div>

  <!--main-slider-->
</template>

<script setup>
  import {useCommonStore} from '~/store/common';
  import {onMounted} from "vue";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
  const {deleteData, getRequest} = useCommonStore();

  const listApi = ref('getSliderImages');
  const deleteApi = ref('deleteSliderImage');
  const loading = ref(false);
  const result = ref({
    main: null,
    right_top: null,
    right_bottom: null
  });

  const {t} = useI18n();

  const {getImageURL, getDataFromObject } = useUtils();

  const fetchingData = async () => {
    loading.value = true
    result.value = await getRequest({params: {}, api: listApi.value})
    loading.value = false
  };

  const router = useRouter();

  const deleteItem = async (id) => {
    if (confirm(t('admin.dltMsg'))) {
      loading.value = true;
      await deleteData({params: id, api: deleteApi.value, id: id});
      loading.value = false;
      await fetchingData();
    }
  };

  onMounted(async () => {
    loading.value = true;
    await fetchingData();
    loading.value = false;
  });
</script>
