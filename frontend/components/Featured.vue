<template>
  <div class="area home-section">
    <template v-if="limitedItems.length">



      <div class="area-content category-grid">
        <div
            v-for="(value, index) in limitedItems"
            :key="index"
            class="center-text"
        >
          <sub-category-tile
              :category="value.category"
              :sub-category="value"
          />
        </div>
      </div>

    </template>
  </div>



</template>

<script>
  import util from '~/mixin/util'
  // import ImageSlider from '~/components/ImageSlider'
  // import ImageLazy from '~/components/ImageLazy'
  import SubCategoryTile from "./SubCategoryTile";
    export default {
        name: 'Featured',
        data() {
            return {
            }
        },
        watch: {
        },
        props: {
          title: {
            type: String,
            default: ''
          },
          itemList: {
            type: Array,
            default(){
              return []
            }
          }
        },
        components: {
          SubCategoryTile,
          // ImageSlider,
          // ImageLazy
        },
        computed: {
          limitedItems() {
            return this.itemList.slice(0, 8)
          }
        },
        mixins: [util],
        methods: {
        },
        created() {
        },
        mounted() {
        }
    }
</script>

<style scoped>

.category-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 15px;
}

/* Responsive */
@media (max-width: 1200px) {
  .category-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (max-width: 768px) {
  .category-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 480px) {
  .category-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

</style>

