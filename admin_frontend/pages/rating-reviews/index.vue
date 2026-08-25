<template>
    <PartialsListPage
            ref="listPageRef"
            list-api="getRatingReviews"
            delete-api="deleteRatingReview"
            route-name="rating-reviews"
            :name="$t('fSale.rr')"
            gate="rating_review"
            class="rating-review-wrapper"
            :add-button="false"
            :order-options="ratingReviewObj"
            @delete-bulk="deleteBulk"
            @list="setItemList"
    >
        <template v-slot:table="{list}">
            <tr class="lite-bold">
                <th>
                    <input type="checkbox" @change="checkAll">
                </th>
                <th>{{ $t('fSale.user') }}</th>
                <th>{{ $t('fSale.rating') }}</th>
                <th>{{ $t('fSale.review') }}</th>
                <th class="mn-w-130x">{{ $t('prod.pImgs') }}</th>
                <th>{{ $t('fSale.product') }}</th>
                <th>{{ $t('category.created') }}</th>
                <th>&nbsp;</th>
            </tr>

            <tr v-for="(value, index) in list"
                :key="index"
            >
                <td>
                    <input type="checkbox" :value="value.id" v-model="cbList">
                </td>
                <td><span class="mn-w-80x">{{ userName(value) }}</span></td>
                <td><span>{{ value.rating }}</span></td>
                <td><span>{{ value.review }}</span></td>
                <td>
                    <span class="mx-w-400x dply-felx j-left f-wrap">
                        <ImageLazy
                                v-for="(img, index) in value.review_images"
                                :key="index"
                                :lazy-src="getThumbImageURL(img.image)"
                                class="m-5 img-80x"
                        />
                    </span>
                </td>
                <td>
                    <nuxt-link
                            class="ellipsis mx-w-200x link"
                            :to="`products/${value.product.id}`"
                    >
                        {{ value.product.title }}
                    </nuxt-link>
                </td>
                <td>{{ value.created }}</td>
                <td>
                    <button
                            v-if="$can('rating_review', 'delete')"
                            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{
                        $t('category.delete') }}
                    </button>
                </td>
            </tr>
        </template>
    </PartialsListPage>
</template>

<script setup>

  import {useUtils} from "~/composables/useUtils";
  import {useListHelper} from "~/composables/useListHelper";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();

  const ratingReviewObj = ref({
    user_id: {title: t('fSale.user')},
    created_at: {title: t('category.date')},
    rating: {title: t('fSale.rating')},
    product_id: {title: t('fSale.product')},
  });

  const userName = (review) => {
    if (review?.user) {
      return review?.user?.name
    } else if (review?.guest_user) {
      return review?.guest_user?.name
    }
    return ""
  };


  const {getThumbImageURL} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

</script>
