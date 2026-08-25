<template>
  <div class="align-start dply-felx reverse block-md gap-10">
    <div v-if="enableSearch" class="grow dply-felx block-xs">
      <form class="search-input" @submit.prevent="makeSearch">
        <input type="text" :placeholder="$t('list.sh')" v-model="search">
        <button class="primary-btn">{{ $t('list.srch') }}</button>
      </form>

      <slot name="add-button">
        <nuxt-link v-if="title && addButton && (gate && $can(gate, 'create'))"
            :to="addRoute" class="button primary-btn">
          <span v-html="$t('profile.add', { title: title})"></span>
        </nuxt-link>
      </slot>

    </div>

    <div class="dply-felx gap-10 j-left f-wrap mt-md-15">
      <table-sort class="mt-0" :order-by-options="orderByOptions"/>

      <slot name="filter"/>

      <inline-pop-over :arrow="true" class="bulk-action" ref="bulkDelete">
        <template v-slot:button>
          {{ $t('title.act') }}
        </template>
        <template v-slot:content>
          <slot name="bulk-action"/>
          <button @click.prevent="deleteBulk" class="outline-btn">
            {{ $t('category.delete') }}
          </button>
        </template>
      </inline-pop-over>
    </div>

  </div>
</template>

<script>
  import tableHelper from '~/mixin/tableHelper'
  import Dropdown from "~/components/Dropdown"
  import TableSort from "~/components/partials/TableSort"
  import InlinePopOver from "../InlinePopOver";
  import {useCommonStore} from "~/store/common.js";
  import {useUiStore} from "~/store/ui.js";
  import Auth from "~/middleware/auth.js";

  export default {
    setup(props) {
      const {setErrors, setToastMessage } = useUiStore();
      const {getRequest} = useCommonStore()
      return {getRequest, setToastMessage, setErrors}
    },

    name: 'TableTop',
    data() {
      return {
        loading: false,
      }
    },
    props: {
      addButton: {
        type: Boolean,
        default: true
      },
      enableSearch: {
        type: Boolean,
        default: true
      },
      resetRoute: {
        type: Boolean,
        default: true
      },
      title: {
        type: String,
        default: null
      },
      gate: {
        type: String,
        default: null
      },
      orderByOptions: {
        type: Object,
        default() {
          return null
        }
      }
    },
    mixins: [tableHelper],
    components: {
      InlinePopOver,
      Dropdown,
      TableSort
    },
    computed: {
      addRoute(){
        // Removing the trailing slash
        return `${this.$route.path.replace(/\/$/, "")}/add`
      },
    },
    methods: {
      Auth,
      deleteBulk(){
        this.$refs.bulkDelete.closePop()
        this.$emit('delete-bulk')
      },
      async fetchProducts() {
        this.$emit('fetch-external')
      }
    },
    beforeCreate() {
    },
    destroyed() {
    },
    created() {
    }
  }
</script>

