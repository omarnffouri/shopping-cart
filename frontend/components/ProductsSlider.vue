<template>

  <div
    v-if="productCollectionsCount"
    class="area home-section"
  >

    <div class="flex sided title">
      <h4>{{ title }}</h4>
      <nuxt-link
        class="link"
        :to="collectionLink(linkObj)"
      >
        {{ $t('featured.showAll') }}
      </nuxt-link>
    </div>

    <div class="area-content shimmer-wrapper product-grid">
      <div
          v-for="(value, index) in limitedProducts"
          :key="index"
      >
        <product-tile :product="value" />
      </div>
    </div>




    <!--    <div class="area-content shimmer-wrapper">-->
<!--      <image-slider>-->
<!--        <template v-slot:content>-->

<!--          <li-->
<!--            v-for="(value, index) in itemList.products"-->
<!--            :key="index"-->
<!--          >-->
<!--            <product-tile-->
<!--              :product="value"-->
<!--            />-->
<!--          </li>-->
<!--        </template>-->

<!--      </image-slider>-->
<!--    </div>-->
  </div>
</template>

<script setup>
  import {toRefs} from "vue";
  import {useUtils} from "~/composables/useUtils";

  const props = defineProps({
    collection: {
      type: Object
    },
  });

  const {collectionLink} = useUtils();

  const {collection} = toRefs(props);
  const limitedProducts = computed(() => {
    return itemList.value?.products?.slice(0, 6) || []
  });

  const productCollectionsCount = computed(() => {
    return limitedProducts.value.length;
  });


  const itemList = computed(() => {
    return collection.value;
  });

  const  title = computed(() => {
    return collection.value?.title;
  });

  const  slug = computed(() => {
    return collection.value?.slug;
  });

  const  linkObj = computed(() => {
    return {
      slug: slug.value,
      title: title.value,
      id: collection.value?.id
    }
  });


</script>

<style scoped>
.product-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 20px;
}

/* Tablet */
@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Mobile */
@media (max-width: 768px) {
  .product-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

</style>
