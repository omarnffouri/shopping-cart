<template>
  <ajax-button
    type="button"
    class="clear-cache"
    loading-text=""
    :color="color"
    :text="$t('setting.cClear')"
    :fetching-data="clearingCache"
    @clicked="clearCache"
  />

</template>

<script>
  import { useCommonStore } from '~/store/common';
  import AjaxButton from "~/components/AjaxButton";

  export default {
    setup() {
      const {setRequest} = useCommonStore();
      return {setRequest};
    },
    name: 'ClearCacheBtn',
    data() {
      return {
        clearingCache: false,
      }
    },
    props: {
      color: {
        type: String,
        default: 'white',
      },
    },
    mixins: [],
    components: {AjaxButton},
    computed: {

    },
    methods: {
      async clearCache() {
        this.clearingCache = true
        await this.setRequest({
          params: {},
          api: 'clearCache'
        })
        this.clearingCache = false
      },
    },
  }
</script>

