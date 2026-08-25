<template>
  <div class="detail-image shimmer-wrapper">

    <template v-if="mainImgLoaded">
      <div class="hide-md mx-h-100"
           @mouseleave="hideSliderMessage"
           @mouseover="showSliderMessage"
      >
        <div class="detail-image-layout">
          <ul class="left-thumbs-list">
            <li
              v-for="(value, index) in leftThumbList"
              :key="`left-thumb-${index}`"
              @mouseenter="setIndex(index)"
              @click.prevent="setIndex(index)"
            >
              <ImageLazy
                :class="activeIndex === index ? 'choosed-thumb' : ''"
                class="responsive-image"
                :lazy-src="getThumbImageURL(value.image)"
              />
            </li>
          </ul>

          <div class="main-image-holder">
            <ImageZoom
              :image="getImageURL(selectedImage)"
              :zoom-image="getImageURL(selectedImage)"
              @click.prevent="imagePopupOpen()"
            />
          </div>
        </div>
      </div>

      <ImagePopup
        v-if="imagePopup"
        ref="imagePopupRef"
        :title="title"
        :product="product"
        :active-id="activeId"
        :no-scroll="!isSmallerDevice"
        :image-list="productImageList"
        :stacked-on-responsive="true"
        @close-popup="closePopup"
        @add-to-wishlist="$emit('add-to-wishlist')"
      />
      <p class="mt-5 pb-15 center-text lh-30 hide-md">
        <span v-if="!sliderMessage">
          {{ $t('productImage.rollOver') }}
        </span>
        <span v-else>
          {{ $t('productImage.extendedView') }}
        </span>
      </p>

    </template>
    <template v-else>
      <div class="pleceholder-zoomer-base-container center-text">
        <img
             class="preload-img"
             :src="getThumbImageURL(mainImage)"
             :alt="title"
             height="100"
             width="100"
        >
      </div>
      <div class="pleceholder-thumb-list">
        <div class="responsive-image shimmer"/>
        <div class="responsive-image shimmer"/>
        <div class="responsive-image shimmer"/>
      </div>
    </template>


  </div>

</template>

<script setup>
  import {ref} from "vue";
  import {onMounted} from "vue";
  import {useUtils} from "~/composables/useUtils";

  const props = defineProps({
    product: {
      type: Object
    },
    images: {
      type: Array,
      default() {
        return []
      }
    },
    mainImage: {
      type: String,
      default: ''
    },

    title: {
      type: String,
      default: ''
    }
  });

  const {product, images, mainImage, title} = toRefs(props);

  const emit = defineEmits(['image-popup']);

  const imagePopupRef = ref(null)

  const callChildMethod = (index) => {
    if (imagePopupRef.value) {
      imagePopupRef.value.selectSliderImage(index);
    }
  };

  const mainImgLoaded = ref(false);
  const loaded = ref(false);
  const noScroll = ref(true);
  const sliderMessage = ref(false);
  const activeId = ref(0);
  const activeIndex = ref(0);
  const imagePopup = ref(false);

  const selectedImage = computed(() => {
    return productImageList.value[activeIndex.value]?.image || mainImage.value;
  });

  const productImageList = computed(() => {
    return [{image: mainImage.value}, ...images.value];
  });

  const leftThumbList = computed(() => {
    return productImageList.value.slice(0, 3);
  });

  const isSmallerDevice = computed(() => {
    return window.innerWidth <= 992
  });

  const {getThumbImageURL, getImageURL}  = useUtils();

  const setIndex = (index) => {
    activeIndex.value = index
  };

  const showSliderMessage = () => {
    sliderMessage.value = true;
  };

  const hideSliderMessage = () => {
    sliderMessage.value = false;
  };

  const closePopup = () => {
    if (!isSmallerDevice.value) {
      imagePopup.value = false;
    }
    emit('image-popup', imagePopup.value)
  };


  const  zoomActiveChange = (index) => {
    callChildMethod(index);
    activeIndex.value = index;
  };

  defineExpose({ zoomActiveChange });

  const imagePopupOpen = async (index) => {
    activeId.value = index || activeIndex.value;
    imagePopup.value = true

    setTimeout(() => {
      callChildMethod(index || activeIndex.value)
    }, 100)

  };

  const generateImageObj = (id, imageLink) => {
    return {
      id: id,
      url: imageLink
    }
  };

  onMounted(async () => {
    if (isSmallerDevice.value) {
      imagePopup.value = true
    }


    let imageId = 0;
    const thumbPromiseAll = [];

    const mainImg = document.createElement('img')
    mainImg.onload = () => {
      mainImgLoaded.value = true;
    }
    mainImg.onerror = () => {
      mainImgLoaded.value = true;
    }
    mainImg.src = getImageURL(mainImage.value);

    /*
    productImageList.value.forEach((obj) => {
      if (obj) {
        imageId++

        thumbPromiseAll.push(new Promise((resolve, reject) => {
          const thumbImg = document.createElement('img');

          thumbImg.onload = function () {
            resolve(generateImageObj(this.dataset.index, this.src));
          }

          thumbImg.onerror = function () {
            resolve(generateImageObj(this.dataset.index, getThumbImageURL()));
          }

          thumbImg.src = getThumbImageURL(obj.image);
          thumbImg.setAttribute('data-index', imageId);
        }))
      }
    });

    await Promise.all(thumbPromiseAll).then(() => {
      loaded.value = true
    });
    )
     */
  });

</script>
