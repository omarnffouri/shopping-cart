<template>
  <li class="tree-node">

    <div class="o-flex gap-10 a-center">
      <slot
              name="checkbox"
              v-bind:id="node.id"
      />


      <span class="node-data">

     <ImageLazy
             :lazy-src="getThumbImageURL(node.image)"
             :alt="node.title"
     />
      {{ node.title }}
    </span>

      <span class="o-flex gap-10">
      <button
              v-if="$can('category', 'edit')"
              class="lite-btn"
              @click.prevent="editNode(node)"
      >
        {{ $t('category.edit') }}
      </button>

      <button
              v-if="$can('category', 'delete')"
              @click.prevent="deleteNode(node)"
              class="delete-btn lite-btn"
      >
        {{ $t('category.delete') }}
      </button>
    </span>

    </div>


    <ul>
    <tree-node
      v-for="childNode in node.child"
        :key="childNode.id"
        :node="childNode"
        @edit="editNode"
        @delete="deleteNode"
    >

      <template
        v-slot:checkbox="{id}"
      >
        <slot
          name="inner-checkbox"
          v-bind:id="childNode.id"
        />
      </template>

    </tree-node>
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
      },
    },
    data() {
      return {
      };
    },
    mixins: [util],
    methods: {
      editNode(node){

        this.$emit('edit', node)
      },
      deleteNode(node){
        this.$emit('delete', node)
      }
    },

  }
</script>

<style>
  .rtl .tree-node {
    margin-left: 0;
    margin-right: 10px;
  }

  .tree-node {
    margin-left: 10px;
    cursor: pointer;
  }

  .rtl .tree-node .node-data{
    padding-left: 0;
    padding-right: 50px;
  }

  .tree-node .node-data{
    padding-left: 50px;
    min-height: 50px;
    position: relative;
  }

  .rtl .tree-node .node-data img{
    right: 0;
    left: auto;
  }


  .tree-node .node-data img{
    position: absolute;
    top: 50%;
    left: 0;
    margin-top: -20px;
    height: 40px;
    width: 40px;
  }
</style>
