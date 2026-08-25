<template>
  <div class="product-form">
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

    <div
      class="right-area"
    >
      <div class="sticky">
        <div class="tab-sidebar mb-20 mb-lg mb-md-15">
          <h4 class="title">
            <span class="mr-5">{{ $t('prod.pVideo') }}</span>
            <span class="fw-400 f-8">{{ $t('prod.maxVid') }}</span>
          </h4>

          <div
            v-if="$can('product', 'edit') || $can('product', 'create')"
            class="form-wrapper"
          >

            <error-formatter
              type="video"
            />

            <video-input
              v-if="mediaStorageData.URL === mediaStorage"
              :saving="videoUploading"
              :video="result.video"
              :video-thumb="result.video_thumb"
              @image-change="uploadVideo(null, $event)"
            />

            <video-upload
              v-else
              class="upload-block"
              :video="result.video"
              type="video"
              :file-uploading="videoUploading"
              @file-upload="uploadVideo"
            />
          </div>
          <div v-else-if="$can('product', 'view')"
               class="ptb-15 plr-20"
          >
            <video v-if="result.video"
                   controls autoplay
            >
              <source
                :src="getVideoURL(result.video)"
                type="video/mp4">
            </video>
            <div v-else>
              {{ $t('prod.noVid') }}
            </div>
          </div>
          <video
            v-else-if="result.video && $can('product', 'view')"
            controls autoplay
          >
            <source
              :src="getVideoURL(result.video)"
              type="video/mp4">
          </video>
        </div>

        <div class="tab-sidebar mb-20 mlr-lg-15 mlr-md mb-md-15">
          <h4 class="title">
            <span class="mr-5">{{ $t('prod.pImg') }}</span>
            <span class="fw-400 f-8">{{ $t('prod.suggImg') }}</span>
          </h4>

          <div v-if="!loading">
            <div
              v-if="$can('product', 'edit') || $can('product', 'create')"
              class="form-wrapper upload-block"
            >
              <error-formatter
                type="image"
              />

              <image-input
                v-if="mediaStorageData.URL === mediaStorage"
                :saving="fileUploading"
                :image="result.image"
                @image-change="imageInputChanged"
              />
              <file-upload
                v-else
                class="upload-block"
                :image="result.image"
                :file-uploading="fileUploading"
                @file-upload="uploadFile"
              />
            </div>

            <img
              v-else-if="$can('product', 'view')"
              class="mx-w-300x"
              :src="getImageURL(result.image)"
             alt="">
          </div>

        </div>

        <div
          class="tab-sidebar mb-md-15"
          v-if="!isAdding"
        >
          <h4 class="title">
            <span class="mr-5">{{ $t('prod.pImgs') }}</span>
            <span class="fw-400 f-8">{{ $t('prod.suggImg') }}</span>
          </h4>

          <PartialsProductImages
            :product-images="result.product_images"
            @result="productImages"
          />
        </div>
      </div>
    </div>

    <div
      ref="productFormRef"
      class="left-area">
      <div class="tab-sidebar">

        <div>
          <div class="dply-felx gap-15 title ptb-5 b-0">
            <h4>{{ $t('prod.pForm') }}</h4>

            <button class="btn-clear dply-felx" @click.prevent="productFormOpen = !productFormOpen">
              <i
                class="icon black ignore-click"
                :class="`${productFormOpen? 'arrow-up' : 'arrow-down'}`"
              />
            </button>
          </div>

          <form
            v-if="productFormOpen"
            class="form-wrapper b-t"
            @submit.prevent="checkForm"
            :class="{'has-error': hasError}"
          >
            <error-formatter/>

            <div class="input-wrapper">
              <label>{{ $t('index.title') }}</label>
              <input
                type="text"
                :placeholder="$t('index.title')"
                @change="slugChange"
                v-model="result.title"
                :class="{invalid: !result.title && hasError}"
              >
              <span
                class="error"
                v-if="!result.title && hasError"
              >{{ $t('category.req', { type: $t('index.title')}) }}</span>
            </div>

            <div class="input-wrapper">
              <label>{{ $t('category.slug') }}</label>
              <input
                type="text"
                :placeholder="$t('category.slug')"
                name="slug"
                v-model="result.slug"
                ref="slug"
                :class="{invalid: !!!result.slug && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.slug && hasError"
              >{{ $t('category.req', { type: $t('category.slug')}) }}</span>
            </div>


            <div
              v-for="(flash, index) in result.flash_sale_product"
              :key="index"
              class="info-msg mb-20 mb-sm-15"
            >
              <p>
                {{ $t('prod.flash') }}
                (
                <nuxt-link
                  class="link"
                  :to="`/flash-sales/${flash.flash_sale.id}`"
                >
                  {{ flash.flash_sale.title }}
                </nuxt-link>
                )
              </p>
              <p>{{ $t('prod.flashPrice') }}: <b>{{ priceFormatting(flash.price) }}</b></p>
            </div>

            <div class="dply-felx mlr--7-5 inputs">
              <div class="input-wrapper mlr-7-5">
                <label>{{ $t('prod.purchased') }}({{ currencyIcon }})</label>
                <input
                  type="number"
                  step="any"
                  :placeholder="$t('prod.purchased')"
                  v-model="result.purchased"
                  :class="{invalid: (result.purchased < 1 || !result.purchased) && hasError}"
                >
                <span
                  class="error"
                  v-if="(result.purchased < 1 || !result.purchased) && hasError"
                >{{ $t('category.req', { type: $t('brand.price')}) }}</span>
              </div>

              <div class="input-wrapper mlr-7-5">
                <label>{{ $t('prod.selling') }}({{ currencyIcon }})</label>
                <input
                  type="number"
                  step="any"
                  :placeholder="$t('prod.selling')"
                  v-model="result.selling"
                  :class="{invalid: (result.selling < 1 || !result.selling) && hasError}"
                >
                <span
                  class="error"
                  v-if="(result.selling < 1 || !result.selling) && hasError"
                >{{ $t('category.req', { type: $t('brand.price')}) }}</span>
              </div>

              <div class="input-wrapper mlr-7-5">
                <label>{{ $t('prod.offered') }}({{ currencyIcon }})</label>
                <input
                  type="number"
                  step="any"
                  :placeholder="$t('prod.offered')"
                  v-model="result.offered"
                >
              </div>
            </div><!--dply-felx inputs-->


            <div class="dply-felx inputs pos-rel w-100">
              <div class="input-wrapper ">
                <label class="block">{{ $t('error.cat') }}</label>

                <div
                  data-ignore="multiple-category"
                  @click.prevent="toggleCategories"
                  class="custom-dropdown cp"
                >
                  <span class="ignore-click">{{ $t('title.choose') }} ({{ result.product_categories.length }})
                    <i
                      class="icon black ignore-click"
                      :class="[{'arrow-up': showCategories}, {'arrow-down': !showCategories}]"
                    />
                  </span>
                </div>

                <ul v-if="showCategories" class="multiple-wrap" v-outside-click="closeDrop"
                    id="multiple-category">

                  <input
                    class="mb-10"
                    type="text"
                    :placeholder="$t('list.sh')"
                    v-model="categorySearch"
                    @input="doCategorySearch"
                  >
                  <label
                    v-for="(item, index) in searchedCategories"
                    :key="index"
                    :label-for="`all-cat-${index}`"
                  >
                    <input
                      :id="`all-cat-${index}`"
                      type="checkbox"
                      v-model="result.product_categories"
                      :value="index"
                    >
                    {{ item.title }}
                  </label>
                </ul>
              </div>

              <div v-if="selectedCategories" class="input-wrapper mlr-7-5">
                <label class="block">{{$t('title.prim')}}</label>
                <dropdown
                  class="left-dd"
                  :selectedKey="result.primary_category_id"
                  :options="selectedCategories"
                  @clicked="primaryCategorySelected"
                />
              </div>
            </div>


            <div class="dply-felx inputs mlr--7-5">
              <div class="input-wrapper mlr-7-5">
                <label>{{ $t('prod.unit') }}</label>
                <input
                  type="text"
                  :placeholder="$t('prod.unit')"
                  v-model="result.unit"
                  :class="{invalid: !!!result.unit && hasError}"
                >
                <span
                  class="error"
                  v-if="!!!result.unit && hasError"
                >{{ $t('category.req', { type: $t('prod.unit')}) }}</span>
              </div>

              <div class="input-wrapper mlr-7-5">
                <label>{{ $t('prod.badge') }}</label>
                <input
                  type="text"
                  :placeholder="$t('prod.badge')"
                  v-model="result.badge"
                >
              </div>
            </div>


            <div
              class="input-wrapper"
              :class="{'whysigwyg-error': !!!result.overview && hasError}"
            >
              <WYSIWYGEditor
                :title="$t('prod.overview')"
                :description="result.overview"
                @change="result.overview = $event"
                @file="editorOverviewFile"
              />
              <span
                class="error"
                v-if="!!!result.overview && hasError"
              >{{ $t('category.req', { type: $t('prod.overview')}) }}</span>
            </div>
            <div
              class="input-wrapper"
              :class="{'whysigwyg-error': !!!result.description && hasError}"
            >
              <WYSIWYGEditor
                :description="result.description"
                @change="result.description = $event"
                @file="editorDescriptionFile"
              />
              <span
                class="error"
                v-if="!!!result.description && hasError"
              >{{ $t('category.req', { type: $t('prod.desc')}) }}</span>
            </div>

            <div class="dply-felx inputs mlr--7-5">

              <div class="input-wrapper mlr-7-5">
                <label class="block">{{ $t('category.status') }}</label>
                <dropdown
                  :selectedKey="`${result.status}`"
                  :options="statusObj"
                  @clicked="dropdownSelected"
                />
              </div>

              <div class="input-wrapper mlr-7-5">
                <label class="block">{{ $t('prod.brand') }}</label>
                <dropdown
                  v-if="allBrands"
                  :default-null="true"
                  :selectedKey="result.brand_id"
                  :options="allBrands"
                  @clicked="brandSelected"
                />
              </div>
            </div>

            <div class="input-wrapper">
              <label>{{ $t('prod.tags') }}</label>
              <tag-search
                @add="addTag"
                @delete="deleteTag"
                :tags="result.tags"
              />
            </div>

            <div class="f-wrap dply-felx inputs mlr--7-5">
              <div class="input-wrapper mlr-7-5">
                <label class="block">{{ $t('prod.tRule') }}</label>
                <dropdown
                  v-if="allTaxRules"
                  :selectedKey="result.tax_rule_id"
                  :options="allTaxRules"
                  @clicked="taxRuleSelected"
                />

                <span
                  class="error"
                  v-if="!!!result.tax_rule_id && hasError"
                >{{ $t('category.req', { type: $t('brand.tRule')}) }}</span>
              </div>

              <div class="input-wrapper mlr-7-5">
                <label class="block">{{ $t('dataPage.shipRule') }}</label>
                <dropdown
                  v-if="allShippingRules"
                  :selectedKey="result.shipping_rule_id"
                  :options="allShippingRules"
                  @clicked="shippingRuleSelected"
                />
                <span
                  class="error"
                  v-if="!!!result.shipping_rule_id && hasError"
                >{{ $t('category.req', { type: $t('brand.shipRule')}) }}</span>
              </div>

              <div class="input-wrapper mlr-7-5">
                <label class="block">{{ $t('profile.dleDeal') }}</label>
                <dropdown
                  v-if="allBundleDeals"
                  :default-null="true"
                  :selectedKey="result.bundle_deal_id"
                  :options="allBundleDeals"
                  @clicked="bundleDealSelected"
                />
              </div>
            </div>

            <div class="mb-10 mb-sm-5 cb-wrapper">
              <span class="block mb-10">{{ $t('dataPage.prodCol') }}</span>
              <label
                v-for="(value, index) in allProductCollections"
                :key="index"
                class="mr-15 mb-10"
              >
                <input
                  type="checkbox"
                  :value="value.id"
                  v-model="result.product_collections"
                >
                {{ value.title}}
              </label>
            </div>

            <div class="mb-20 mb-sm-15">
              <span class="section-title">{{ $t('prod.pInfo') }}</span>

              <!-- Refundable -->
              <div class="info-row">
                <div class="row-left">
                  <span class="row-label">{{ $t('prod.refund') }}</span>
                </div>
                <div class="pill-toggle">
                  <div class="pill-option yes">
                    <input type="radio" name="refundable" id="ref-yes" value="1" v-model="result.refundable">
                    <label for="ref-yes">
                      <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5">
                        <polyline points="1.5,6 4.5,9 10.5,3"/>
                      </svg>
                      {{ $t('prod.yes') }}
                    </label>
                  </div>
                  <div class="pill-option no">
                    <input type="radio" name="refundable" id="ref-no" value="2" v-model="result.refundable">
                    <label for="ref-no">
                      <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.5">
                        <line x1="2" y1="2" x2="10" y2="10"/><line x1="10" y1="2" x2="2" y2="10"/>
                      </svg>
                      {{ $t('prod.no') }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Warranty -->
              <div class="info-row">
                <div class="row-left">
                  <span class="row-label">{{ $t('prod.warranty') }}</span>
                </div>
                <div class="pill-toggle">
                  <div class="pill-option yes">
                    <input type="radio" name="warranty" id="war-yes" value="1" v-model="result.warranty">
                    <label for="war-yes">
                      <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5">
                        <polyline points="1.5,6 4.5,9 10.5,3"/>
                      </svg>
                      {{ $t('prod.yes') }}
                    </label>
                  </div>
                  <div class="pill-option no">
                    <input type="radio" name="warranty" id="war-no" value="2" v-model="result.warranty">
                    <label for="war-no">
                      <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.5">
                        <line x1="2" y1="2" x2="10" y2="10"/><line x1="10" y1="2" x2="2" y2="10"/>
                      </svg>
                      {{ $t('prod.no') }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Available for Delivery Today -->
              <div class="info-row">
                <div class="row-left">
                  <span class="row-label">{{ $t('prod.availDelivToday') }}</span>
                </div>
                <label class="toggle" :aria-label="$t('prod.availDelivToday')">
                  <input type="checkbox" v-model="result.available_for_delivery_today">
                  <div class="toggle-track">
                    <div class="toggle-thumb">
                      <svg class="icon-off" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/>
                      </svg>
                      <svg class="icon-on" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,5 4,7.5 8.5,2.5"/>
                      </svg>
                    </div>
                  </div>
                </label>
            </div>

              <span class="section-title" style="margin-top: 16px;">Note Settings</span>
              <!-- Allow Note Image -->
              <div class="info-row">
                <div class="row-left">
                  <span class="row-label">Allow Note Image</span>
                </div>
                <label class="toggle" aria-label="Allow Note Image">
                  <input type="checkbox" v-model="result.allow_note_image">
                  <div class="toggle-track">
                    <div class="toggle-thumb">
                      <svg class="icon-off" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/>
                      </svg>
                      <svg class="icon-on" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,5 4,7.5 8.5,2.5"/>
                      </svg>
                    </div>
                  </div>
                </label>
              </div>

              <!-- Allow Note Message -->
              <div class="info-row">
                <div class="row-left">
                  <span class="row-label">Allow Note Message</span>
                </div>
                <label class="toggle" aria-label="Allow Note Message">
                  <input type="checkbox" v-model="result.allow_note">
                  <div class="toggle-track">
                    <div class="toggle-thumb">
                      <svg class="icon-off" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/>
                      </svg>
                      <svg class="icon-on" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1.5,5 4,7.5 8.5,2.5"/>
                      </svg>
                    </div>
                  </div>
                </label>
              </div>

            <div class="input-wrapper">
              <label>{{ $t('category.mTitle') }}</label>
              <input
                type="text"
                :placeholder="$t('category.mTitle')"
                v-model="result.meta_title"
                :class="{invalid: !!!result.meta_title && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.meta_title && hasError"
              >{{ $t('category.req', { type: $t('category.mTitle')}) }}</span>
            </div>


            <div class="input-wrapper">
              <label>{{ $t('ship.mk') }} ({{ $t('ship.csk') }})</label>
              <textarea
                :placeholder="$t('ship.mk')"
                v-model="result.meta_keywords"
              />
            </div>


            <div class="input-wrapper">
              <label>{{ $t('category.mDesc') }}</label>
              <textarea
                :placeholder="$t('category.mDesc')"
                v-model="result.meta_description"
                :class="{invalid: !!!result.meta_description && hasError}"
              />
              <span
                class="error"
                v-if="!!!result.meta_description && hasError"
              >{{ $t('category.req', { type: $t('category.mDesc')}) }}</span>
            </div>

            <div
              v-if="($can('product', 'edit') || $can('product', 'create')) "
              class="dply-felx j-right gap-15"
            >
              <ajax-button
                name="save-edit"
                class="primary-btn"
                :text="$t('list.svn')"
                :fetching-data="formSubmitting  && !redirect"
              />
              <ajax-button
                name="save"
                class="primary-btn"
                :text="$t('setting.sv')"
                :fetching-data="formSubmitting && redirect"
              />
            </div>
        </div>



      <div class="tab-sidebar mt-15" v-if="!isAdding" ref="productInventoryRef">
        <PartialsProductInventory
          v-if="currentPrice"
          :attributes="allAttributes"
          :product-price="parseFloat(currentPrice)"
          @has-error="scrollToTop('productInventoryRef')"
        />
      </div>
          </form>
        </div>

      </div>
    </div>
  </div>

</template>

<script setup>
  import debounce from "debounce";
  import {storeToRefs} from "pinia";
  import {useCommonStore} from "../../store/common";
  import {useSettingStore} from "../../store/setting";
  import {useIndexStore} from "../../store";
  import {useConstants} from "../../composables/useConstants";
  import {useUtils} from "../../composables/useUtils";
  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore()
  const {getById, setById, setImageById, getDropdownList, setWysiwygImage, deleteData} = commonStore
  const {
    allCategories, allTaxRules, allAttributes,
    allBrands, allProductCollections, allBundleDeals, allShippingRules
  } = storeToRefs(commonStore)

  const indexStore = useIndexStore()
  const {mediaStorage} = storeToRefs(indexStore)

  const settingStore = useSettingStore()
  const {setting} = storeToRefs(settingStore)


  const categorySearch = ref('');

  const productFormOpen = ref(true);
  const showCategories = ref(false);
  const routeName = ref('products');
  const getApi = ref('getProduct');
  const setApi = ref('setProduct');
  const setImageApi = ref('setProductImage');
  const setVideoApi = ref('setProductVideo');
  const fileKeys = ref(['id', 'tax_rule_id', 'shipping_rule_id']);
  const validationKeys = ref(['title', 'slug', 'unit', 'meta_title', 'meta_description',
    'description', 'overview', 'selling', 'purchased']);
  const result = ref({
    id: '',
    title: '',
    tags: ',',
    overview: '',
    description: '',
    status: '2',
    brand_id: '',
    primary_category_id: '',
    category_id: '',
    bundle_deal_id: '',
    unit: '',
    badge: '',
    subcategory_id: '',
    tax_rule_id: '',
    shipping_rule_id: '',
    purchased: '',
    selling: '',
    offered: '',
    refundable: 1,
    warranty: 1,
    meta_description: '',
    meta_keywords: '',
    flash_sale_product: '',
    meta_title: '',
    image: '',
    slug: '',
    video: '',
    product_images: [],
    product_collections: [],
    product_categories: [],
  });
  const hasError = ref(false);
  const loading = ref(false);
  const formSubmitting = ref(false);
  const redirect = ref(false);
  const fileUploading = ref(false);
  const videoUploading = ref(false);
  const searchedCategories = ref({});

  const route = useRoute();

  const toggleCategories = () => {
    showCategories.value = !showCategories.value
  };

  const productCategories = computed(() => {
    return result.value.product_categories
  });

  const selectedCategories = computed(() => {
    let sc = null;
    if (allCategories.value && productCategories.value.length) {

      Object.keys(allCategories.value).forEach(i => {
        if (productCategories.value.includes(i)) {
          sc = {...sc, ...{[i]: allCategories.value[i]}}
        }
      });
    }
    return sc;
  });

  const currencyPosition = computed(() => {
    return setting.value?.currency_position
  });

  const currentPrice = computed(() => {
    return result.value.offered > 0 ? result.value.offered : result.value.selling > 0 ? result.value.selling : 0
  });

  const id = computed(() => {
    return !isAdding.value ? route?.params?.id : ''
  });

  const isAdding = computed(() => {
    return isNaN(route?.params?.id)
  });

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$'
  });


  const doCategorySearch = debounce(function () {
    searchOnCategory()
  }, 500);

  const searchOnCategory = () => {
    searchedCategories.value = {}

    Object.keys(allCategories.value).filter(element =>
      allCategories.value[element].title.toLowerCase().includes(categorySearch.value.toLowerCase())).forEach(i => {
      searchedCategories.value[i] = allCategories.value[i]
    })

    if (!Object.keys(searchedCategories.value)?.length && !categorySearch.value) {
      searchedCategories.value = allCategories.value
    }
  };

  const closeDrop = () => {
    showCategories.value = false
  };

  const productImages = async (evt) => {
    result.value.product_images = []

    await nextTick()

    result.value.product_images = evt
  };

  const imageInputChanged = (evt) => {
    uploadFile(null, evt)
  };

  const addTag = (tag) => {
    if (!result.value.tags) {
      result.value.tags = ','
    }
    result.value.tags = `${result.value.tags}${tag},`
  };

  const deleteTag = (tag) => {
    result.value.tags = result.value.tags.replace(`${tag},`, '')
  };

  const dropdownSelected = (data) => {
    result.value.status = data.key
  };

  const primaryCategorySelected = (data) => {
    result.value.primary_category_id = data.key
  };

  const taxRuleSelected = (data) => {
    result.value.tax_rule_id = data.key
  };

  const shippingRuleSelected = (data) => {
    result.value.shipping_rule_id = data.key
  };

  const brandSelected = (data) => {
    result.value.brand_id = data.key
  };

  const bundleDealSelected = (data) => {
    result.value.bundle_deal_id = data.key
  };

  const redirectingEnable = (buttonType) => {
    redirect.value = buttonType === 'save'
  };

  const checkForm = async () => {
    if (validationKeys.value.findIndex((i) => {
      return (!result.value[i])
    }) > -1) {
      hasError.value = true
      return false
    }
    redirectingEnable(event.submitter.name)
    formSubmitting.value = true


    delete result.value.created_at
    delete result.value.updated_at
    const data = await setById({id: id.value, params: result.value, api: setApi.value})

    if (data) {

      result.value = Object.assign({}, data)

      result.value.product_collections = [...new Set(result.value?.product_collections?.map((o) => {
        return o.product_collection_id
      }))]
      result.value.product_categories = [...new Set(result.value?.product_categories?.map((o) => {
        return o.category_id.toString()
      }))]

      navigateTo({path: `/${routeName.value}${redirect.value ? '' : '/' + result.value.id}`})
    } else {
      scrollToTop()
    }

    formSubmitting.value = false
  };

  const productInventoryRef = ref(null);
  const productFormRef = ref(null);

  const scrollToTop = (ref = "productFormRef") => {
    [ref]?.value?.scrollIntoView({behavior: "smooth"})
  };

  const fetchingData = async () => {

    loading.value = true
    result.value = Object.assign({}, await getById({id: id.value, params: {}, api: getApi.value}))

    result.value.product_collections = [...new Set(result.value?.product_collections?.map((o) => {
      return o.product_collection_id
    }))]
    result.value.product_categories = [...new Set(result.value?.product_categories?.map((o) => {
      return o.category_id.toString()
    }))]

    loading.value = false

  };

  const uploadVideo = async (file, video = null) => {
    videoUploading.value = true

    let params = {}
    if (file) {
      const fd = new FormData()

      fileKeys.value.forEach(i => {
        fd.append(i, result.value[i])
      })
      fd.append('video_file', file.video)
      fd.append('thumb', file.thumb)

      params = fd
    } else {
      fileKeys.value.forEach(i => {
        params[i] = result.value[i]
      })

      params['video_file'] = video.video
      params['thumb'] = video.thumb
    }
    const data = await setImageById({id: id.value, params: params, api: setVideoApi.value})

    if (data) {
      result.value = Object.assign({}, data)
      result.value.product_collections = [...new Set(result.value?.product_collections?.map((o) => {
        return o.product_collection_id
      }))]
      result.value.product_categories = [...new Set(result.value?.product_categories?.map((o) => {
        return o.category_id.toString()
      }))]

      await router.push({path: `/${routeName.value}/${result.value.id}`})
    }

    videoUploading.value = false
  };

  const uploadFile = async (file, name = null) => {
    fileUploading.value = true


    let params = {}
    if (file) {
      const fd = new FormData()
      fileKeys.value.forEach(i => {
        fd.append(i, result.value[i])
      })
      fd.append('photo', file)
      params = fd
    } else {
      fileKeys.value.forEach(i => {
        params[i] = result.value[i]
      })
      params['photo'] = name
    }

    const data = await setImageById({id: id.value, params: params, api: setImageApi.value})

    if (data) {
      result.value = Object.assign({}, data)
      result.value.product_collections = [...new Set(result.value?.product_collections?.map((o) => {
        return o.product_collection_id
      }))]
      result.value.product_categories = [...new Set(result.value?.product_categories?.map((o) => {
        return o.category_id.toString()
      }))]


      await navigateTo({path: `/${routeName.value}/${result.value.id}`})
    }

    fileUploading.value = false
  };

  const {wysiwygType, mediaStorageData, statusObj} = useConstants();

  const editorDescriptionFile = ({deleted, file, Editor, cursorLocation, resetUploader}) => {

    editorFile({deleted, file, Editor, cursorLocation, resetUploader}, wysiwygType.PRODUCT_DESCRIPTION)
  };

  const editorOverviewFile = ({deleted, file, Editor, cursorLocation, resetUploader}) => {
    editorFile({deleted, file, Editor, cursorLocation, resetUploader}, wysiwygType.PRODUCT_OVERVIEW)
  };


  const {getImageName, priceFormatting} = useUtils();

  const editorFile = async ({deleted, file, Editor, cursorLocation, resetUploader}, type) => {
    if (!deleted) {
      loading.value = true

      const fd = new FormData()
      if (!result.value.id) {
        fd.append('product', JSON.stringify(result.value))
      } else {
        fd.append('overview', result.value.overview)
        fd.append('description', result.value.description)
        fd.append('item_id', result.value.id)
      }

      fd.append('type', type)
      fd.append('photo', file)

      const data = await setWysiwygImage(fd)


      if (data) {
        if (!result.value.id) {
          await navigateTo({path: `/${routeName.value}/${data.item_id}`})
        } else {

          Editor.insertEmbed(cursorLocation, "image", data.url);
          Editor.setSelection(cursorLocation + 1);
        }
      }

      loading.value = false

    } else {
      loading.value = true

      await deleteData({params: getImageName(file), api: 'deleteWysiwygImage'})

      loading.value = false
    }
  };


  onMounted(async () => {
      if (!isAdding.value) {
      await fetchingData()
    }
    if (!allCategories.value || !allTaxRules.value || !allAttributes.value ||
      !allBrands.value || !allProductCollections.value || !allBundleDeals.value || !allShippingRules.value) {

      loading.value = true

      await getDropdownList()

      loading.value = false
    }

    searchedCategories.value = allCategories.value
  });


</script>

<style scoped>
.toggle {
  position: relative;
  display: inline-block;
  width: 56px;
  height: 30px;
  flex-shrink: 0;
}
.toggle input { opacity: 0; width: 0; height: 0; position: absolute; }

.toggle-track {
  position: absolute;
  inset: 0;
  border-radius: 30px;
  background: #e2e5ef;
  cursor: pointer;
  overflow: hidden;
  transition: background 0.35s cubic-bezier(0.4,0,0.2,1), box-shadow 0.35s;
}

.toggle-thumb {
  position: absolute;
  top: 4px; left: 4px;
  width: 22px; height: 22px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.18);
  transition: transform 0.38s cubic-bezier(0.34,1.56,0.64,1), width 0.2s ease;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}
.toggle-thumb svg { width: 10px; height: 10px; opacity: 0; transition: opacity 0.2s ease 0.1s; }
.toggle-thumb .icon-off { color: #b0b5cc; }
.toggle-thumb .icon-on  { color: #6366f1; position: absolute; }

.toggle input:checked + .toggle-track {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15), inset 0 1px 3px rgba(0,0,0,0.1);
}
.toggle input:checked + .toggle-track::before {
  background-position: -200% center;
  transition: background-position 0.6s ease;
}
.toggle input:checked + .toggle-track .toggle-thumb {
  transform: translateX(26px);
  box-shadow: 0 2px 12px rgba(99,102,241,0.4);
}
.toggle input:checked + .toggle-track .toggle-thumb .icon-on { opacity: 1; }
.toggle input:active + .toggle-track .toggle-thumb { width: 26px; }
.toggle input:focus-visible + .toggle-track { outline: 2px solid #6366f1; outline-offset: 2px; }

.section-title {
  display: block;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #6b7280;
  margin-bottom: 14px;
}
.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  border-radius: 12px;
  background: #fafafa;
  border: 1.5px solid #f0f0f0;
  margin-bottom: 8px;
  transition: border-color 0.2s, background 0.2s;
}
.info-row:last-child { margin-bottom: 0; }
.info-row:hover { border-color: #e0e0f0; background: #f8f8ff; }
.row-left { display: flex; flex-direction: column; gap: 2px; }
.row-label { font-size: 14px; font-weight: 600; color: #1f2937; }

.pill-toggle {
  display: flex;
  align-items: center;
  background: #f0f2f5;
  border-radius: 10px;
  padding: 3px;
  gap: 2px;
}
.pill-option { position: relative; }
.pill-option input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.pill-option label {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.2s, background 0.2s, box-shadow 0.2s;
  user-select: none;
  white-space: nowrap;
}
.pill-option label svg { width: 12px; height: 12px; }
.pill-option.yes input:checked + label { background: white; color: #059669; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
.pill-option.yes input:checked + label svg { stroke: #059669; }
.pill-option.no  input:checked + label { background: white; color: #ef4444; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
.pill-option.no  input:checked + label svg { stroke: #ef4444; }
.pill-option.yes input:not(:checked) + label:hover { color: #059669; }
.pill-option.no  input:not(:checked) + label:hover { color: #ef4444; }
</style>
