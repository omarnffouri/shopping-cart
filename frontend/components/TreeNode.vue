<template>
  <li class="tree-node">
    <span class="node-data">

      <nuxt-link
        :to="categoryLink(node)"
        :title="node.title"
      >
        {{node.title}}
      </nuxt-link>
    </span>

    <ul>
    <tree-node
        class="hover-text"
      v-for="childNode in node.child"
        :key="childNode.id"
        :node="childNode"
      />
    </ul>
  </li>
</template>

<script>
  import util from '~/mixin/util'

  export default {
    name: 'TreeNode',
    components: {},
    props: {
      node: {
        type: Object,
        required: true,
      }
    },
    data() {
      return {
      };
    },
    mixins: [util],
    methods: {
    },
    computed: {
      nodeData(){
        return this.node[this.keyName]
      },
    }

  }
</script>
<style scoped>
.hover-text:hover{
  color: #c8a330;
}
</style>