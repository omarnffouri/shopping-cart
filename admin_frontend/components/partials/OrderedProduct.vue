<template>
  <tr>
    <td>
      <div class="dply-felx j-left">
        <router-link :to="`/products/${product.id}`">
          <ImageLazy
              :lazy-src="getThumbImageURL(productImage)"
              :alt="orderedProduct.product.title"
              class="mr-20"
          />
        </router-link>

        <div>
          <router-link :to="`/products/${product.id}`">
            <span class="mn-w-200x">{{ product.title }}</span>
          </router-link>

          <p v-if="orderedProduct.updated_inventory.sku" class="mt-10">
            SKU: {{ orderedProduct.updated_inventory.sku }}
          </p>

          <span class="mt-10">
            <span v-for="i in currentAttribute" :key="i[0] + i[1]" class="mr-15">
              <b class="mr-10">{{ i[0] }}:</b>{{ i[1] }}
            </span>
          </span>

          <div v-if="orderedProduct.note" class="order-note mt-10">
            <p v-if="orderedProduct.note.message" class="note-msg">
              <b>Note:</b> {{ orderedProduct.note.message }}
            </p>

            <ImageLazy
                v-if="orderedProduct.note.image"
                :lazy-src="getImageURL(orderedProduct.note.image)"
                alt="Product note"
                class="note-img mt-5"
            />
          </div>
        </div>
      </div>
    </td>

    <td>{{ shippingType[orderedProduct.shipping_type] }}</td>
    <td>{{ orderedProduct.quantity }}</td>
    <td>{{ priceFormatting((orderedProduct.selling * orderedProduct.bundle_offer) || 0) }}</td>
    <td>{{ priceFormatting(orderedProduct.selling) }}</td>
    <td>{{ priceFormatting(orderedProduct.selling * orderedProduct.quantity) }}</td>
  </tr>
</template>

<script>
import util from "../../mixin/util";
import productImageHelper from "../../mixin/productImageHelper";
import { useSettingStore } from "../../store/setting";
import { storeToRefs } from "pinia";

export default {
  setup() {
    const settingStore = useSettingStore();
    const { setting } = storeToRefs(settingStore);
    return { setting };
  },
  name: "OrderedProduct",
  data() {
    return {};
  },
  props: {
    orderedProduct: {
      type: Object,
    },
  },
  components: {},
  mixins: [util, productImageHelper],
  computed: {
    currentAttribute() {
      return this.inventoryAttributes?.map((i) => {
        return [i?.attribute_value?.attribute?.title, i?.attribute_value?.title];
      });
    },
    inventoryAttributes() {
      return this.orderedProduct.updated_inventory?.inventory_attributes;
    },
    product() {
      return this.orderedProduct?.product;
    },
    currencyPosition() {
      return this.setting?.currency_position;
    },
    currencyIcon() {
      return this.setting?.currency_icon || "$";
    },
  },
  mounted() {},
};
</script>

<style scoped>
.order-note {
  padding: 8px 10px;
  border: 1px dashed #ddd;
  border-radius: 6px;
  max-width: 320px;
}

.note-msg {
  margin: 0;
  font-size: 13px;
}

.note-img {
  max-width: 160px;
  height: auto;
  border-radius: 6px;
  border: 1px solid #eee;
}
</style>
