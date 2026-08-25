<template>
  <div class="category-wrapper">
      <h4
          class="title">
          {{ $t('searchPopup.categories') }}
      </h4>
    <ul class="table-tree">
      <tree-node
          class="hover-text"
        v-for="value in categories"
        :node="value"
        :key="value.id"
      />
    </ul>

  </div>
</template>

<script>

  import util from '~/mixin/util'
  import TreeNode from "./TreeNode";

    export default {
      name: "FilterCategory",
      props: {
        categories: {
          type: Array,
          default: []
        },
      },
      data() {
        return {

        }
      },
      components: {
        TreeNode

      },
      mixins: [util],
      computed: {
        categorySlug(){
          return this.$route.params?.category || null
        },
      },
      mounted() {
      },
      methods: {

      },
    }
</script>

<style scoped>
.hover-text:hover{
  color: #c8a330;
}
</style>
