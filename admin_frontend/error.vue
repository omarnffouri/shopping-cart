<script setup>
  const props = defineProps({
    error: Object
  })

  const reload = () => {
    window.location.reload()
  }
</script>

<template>
  <div class="container-fluid">

    <div class="flex error-wrapper reverse">

      <div class="img-wrap mb-sm-20 mlr-xs-auto">
        <img src="~/assets/images/error.png"
             height="50"
             width="50"
        >
      </div>

      <div class="mr-50 mr-sm">

        <h1 class="big-title">{{ $t('category.sor') }}</h1>
        <h2 class=" mb-15 word-break-all">{{ error.message }}</h2>
        <div class="mb-30 mb-sm-15 centered">
          <button class="outline-btn"
                  @click.prevent="reload"
          >
            {{ $t('category.tryLoad') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
