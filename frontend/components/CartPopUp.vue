<template>
  <div v-if="visible" class="cart-popup-overlay">
    <div class="cart-popup">
      <!-- Header title -->
      <div class="cart-popup-header">
        <h3>Add on something to make it extra special!</h3>

        <button
            class="close-btn"
            aria-label="Close popup"
            @click="close"
        >
          ✕
        </button>
      </div>

      <!-- ✅ CATEGORY SECTION -->
      <div class="cart-popup-categories">
        <span
            v-for="(cat, index) in addonCategories"
            :key="cat.id || index"
            :class="['category-pill', { active: activeCategory === cat.id }]"
            @click="selectCategory(cat)"
        >
          {{ cat.title }}
        </span>
      </div>

      <!-- Body (SCROLLABLE) -->
      <div class="cart-popup-body">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <p>Loading products...</p>
        </div>

        <!-- Products Grid -->
        <div v-else-if="activeProducts.length > 0" class="products-grid">
          <div v-for="product in paginatedProducts" :key="product.id" class="product-card">
            <div class="product-image">
              <nuxt-link :to="productLink(product)" target="_blank">
                <!-- Instead of regular <img> -->
                <ImageLazy
                    :lazy-src="getThumbImageURL(product.image)"
                    :title="product.title"
                    :alt="product.title"
                />
              </nuxt-link>
            </div>
            <div class="product-details">
              <nuxt-link :to="productLink(product)" target="_blank" class="product-title-link">
                <p class="product-title">{{ product.title }}</p>
              </nuxt-link>
              <!--<p class="product-price">{{ currencyIcon }} {{ formatPrice(product.offered > 0 ? product.offered : product.selling) }}</p>-->

              <!-- ✅ ATTRIBUTE SELECTION -->
              <div v-if="hasAttributes(product)" class="product-attributes">
                <div v-for="attr in product.attribute" :key="attr.id" class="attribute-group">
                  <label class="attribute-label">{{ attr.title }}:</label>
                  <div class="attribute-options">
                    <button
                        v-for="attrValue in attr.values"
                        :key="attrValue.id"
                        :class="['attr-btn', {
                          selected: isAttributeSelected(product, attr.id, attrValue.attribute_value_id)
                        }]"
                        @click="selectAttribute(product, attr.id, attrValue)"
                    >
                      {{ attrValue.title }}
                    </button>
                    <!--<p class="product-price">{{ currencyIcon }} {{ formatPrice(product.offered > 0 ? product.offered : product.selling) }}</p>-->

                  </div>

                </div>
                <!-- ✅ Show error if attributes not selected -->
                <p v-if="getAttributeError(product)" class="error-text">
                  {{ getAttributeError(product) }}
                </p>
              </div>

              <p class="product-price">{{ currencyIcon }} {{ formatPrice(product.offered > 0 ? product.offered : product.selling) }}</p>


              <!-- ✅ Quantity Controls or Add Button -->
              <div v-if="getProductQuantity(product) > 0" class="quantity-controls">
                <button class="qty-btn" @click="decreaseQuantity(product)" aria-label="Decrease quantity">
                  −
                </button>
                <span class="quantity-display">{{ getProductQuantity(product) }}</span>
                <button class="qty-btn" @click="increaseQuantity(product)" aria-label="Increase quantity">
                  +
                </button>
              </div>
              <button
                  v-else
                  class="add-btn"
                  @click="addProduct(product)"
              >
                {{ hasAttributes(product) && !isProductReadyToAdd(product) ? '+ ADD' : '+ ADD' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <!--        <div v-if="totalPages > 1" class="pagination">-->
        <!--          <button-->
        <!--              class="page-btn"-->
        <!--              :disabled="currentPage === 1"-->
        <!--              @click="currentPage&#45;&#45;"-->
        <!--          >-->
        <!--            <i-->
        <!--                class="icon arrow-left black"-->
        <!--            />-->
        <!--          </button>-->


        <!--          <span class="page-info">-->
        <!--    Page {{ currentPage }} of {{ totalPages }}-->
        <!--  </span>-->

        <!--          <button-->
        <!--              class="page-btn"-->
        <!--              :disabled="currentPage === totalPages"-->
        <!--              @click="currentPage++"-->
        <!--          >-->
        <!--            <i-->
        <!--                class="icon arrow-right black"-->
        <!--            />-->
        <!--          </button>-->
        <!--        </div>-->

      </div>

      <!-- Footer -->
      <div class="cart-popup-footer">
        <div class="footer-content">
          <div class="price-details-container">
            <div class="price-label">
              PRICE<br>DETAILS
            </div>
            <div class="price-breakdown">
              <div class="price-item">
                <span class="p-label">Base Item</span>
                <span class="p-value">{{ currencyIcon }} {{ formatPrice(props.baseItemPrice || 0) }}</span>
              </div>
              <div class="operator">+</div>
              <div class="price-item">
                <span class="p-label">{{ totalAddonsCount }} ADD ONS</span>
                <span class="p-value">{{ currencyIcon }} {{ formatPrice(calculatedAddonsPrice) }}</span>
              </div>
              <div class="operator">=</div>
              <div class="price-item total">
                <span class="p-label">Total</span>
                <span class="p-value">{{ currencyIcon }} {{ formatPrice(totalPrice) }}</span>
              </div>
            </div>
          </div>

          <button type="button" class="continue-btn" @click="emitContinue">
            {{ selectedAddons.length > 0 ? 'CONTINUE' : 'CONTINUE WITHOUT ADD ONS' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from "vue";
import { useCommonStore } from "~/store/common";
import { useUtils } from "~/composables/useUtils";

const props = defineProps({
  visible: Boolean,
  baseItemPrice: Number,
  productsData: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(["close", "continue"]);
const commonStore = useCommonStore();
const { getRequest, currencyIcon } = commonStore;
const { productLink, getThumbImageURL  } = useUtils();

const addonCategories = ref([]);
const activeCategory = ref(null);
const selectedAddons = ref([]);
const productAttributes = ref({});
const attributeErrors = ref({});
const loading = ref(false);
const currentPage = ref(1)
const perPage = ref(12)

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2);
};

const fetchAddons = async (categoryId = null) => {
  try {
    loading.value = true;
    const queryParams = categoryId && categoryId !== 'all' ? `?category_id=${categoryId}` : '';
    const response = await getRequest({ params: queryParams, api: 'addons' });

    if(response) {
      let categoriesData = response.result || response.data || (Array.isArray(response) ? response : null);
      if (categoriesData && Array.isArray(categoriesData)) {
        const allProducts = [];
        categoriesData.forEach(cat => { if (cat.products) allProducts.push(...cat.products); });
        const uniqueAllProducts = Array.from(new Map(allProducts.map(item => [item.id, item])).values());

        const allCategory = { id: 'all', title: 'All', products: uniqueAllProducts };

        if (categoryId && categoryId !== 'all') {
          const target = categoriesData.find(c => c.id == categoryId);
          if (target) {
            const local = addonCategories.value.find(c => c.id == categoryId);
            if (local) local.products = target.products || [];
          }
        } else {
          addonCategories.value = [allCategory, ...categoriesData];
          if (!activeCategory.value && addonCategories.value.length) activeCategory.value = addonCategories.value[0].id;
        }
      }
    }
  } catch (e) { console.error("Failed to fetch addons:", e); }
  finally { loading.value = false; }
};

const selectCategory = (cat) => {
  activeCategory.value = cat.id;
  currentPage.value = 1
  const category = addonCategories.value.find(c => c.id === cat.id);
  if (category && (!category.products || category.products.length === 0) && cat.id !== 'all') {
    fetchAddons(cat.id);
  }
};

onMounted(() => fetchAddons());

watch(() => props.visible, (newVal) => {
  if (newVal && addonCategories.value.length === 0) fetchAddons();
});

const activeProducts = computed(() => {
  const category = addonCategories.value.find(c => c.id === activeCategory.value);
  return category ? (category.products || []) : [];
});

const totalPages = computed(() => {
  return Math.ceil(activeProducts.value.length / perPage.value)
})

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return activeProducts.value.slice(start, start + perPage.value)
})

const hasAttributes = (product) => product.attribute && product.attribute.length > 0;

const isAttributeSelected = (product, attrId, attrValueId) => {
  return productAttributes.value[product.id]?.[attrId] === attrValueId;
};

const selectAttribute = (product, attrId, attrValue) => {
  if (!productAttributes.value[product.id]) productAttributes.value[product.id] = {};
  productAttributes.value[product.id][attrId] = attrValue.attribute_value_id;
  delete attributeErrors.value[product.id];
};

const areAllAttributesSelected = (product) => {
  if (!hasAttributes(product)) return true;
  const selected = productAttributes.value[product.id] || {};
  return product.attribute.every(attr => selected[attr.id] !== undefined);
};

const isProductReadyToAdd = (product) => areAllAttributesSelected(product);

const findMatchingInventory = (product) => {
  if (!hasAttributes(product)) return product.inventory?.[0] || null;
  if (!areAllAttributesSelected(product)) return null;

  const selectedStr = Object.values(productAttributes.value[product.id]).sort().join('-');
  return product.inventory?.find(inv => {
    return inv.inventory_attributes.map(ia => ia.attribute_value_id).sort().join('-') === selectedStr;
  });
};

const getAttributeError = (product) => attributeErrors.value[product.id];

const validateProduct = (product) => {
  if (hasAttributes(product) && !areAllAttributesSelected(product)) {
    attributeErrors.value[product.id] = 'Please select all options';
    return false;
  }
  const inventory = findMatchingInventory(product);
  if (hasAttributes(product) && !inventory) {
    attributeErrors.value[product.id] = 'Selected option not available';
    return false;
  }
  delete attributeErrors.value[product.id];
  return true;
};


const getProductQuantity = (product) => {
  return selectedAddons.value
      .filter(a => a.product.id === product.id)
      .reduce((sum, a) => sum + a.quantity, 0);
};

const addProduct = (product) => {
  if (!validateProduct(product)) return;

  const inventory = findMatchingInventory(product);
  const selectedAttrs = hasAttributes(product) ? { ...productAttributes.value[product.id] } : {};

  const existing = selectedAddons.value.find(a =>
      a.product.id === product.id &&
      (hasAttributes(product) ? a.inventory?.id === inventory?.id : true)
  );

  if (existing) {
    existing.quantity++;
  } else {
    selectedAddons.value.push({
      product,
      quantity: 1,
      inventory,
      selectedAttributes: selectedAttrs
    });
  }
};

const increaseQuantity = (product) => {
  if (hasAttributes(product) && areAllAttributesSelected(product)) {
    const inventory = findMatchingInventory(product);
    const existing = selectedAddons.value.find(a => a.product.id === product.id && a.inventory?.id === inventory?.id);
    if (existing) {
      existing.quantity++;
      return;
    }
  }

  const lastAdded = selectedAddons.value.filter(a => a.product.id === product.id).pop();
  if (lastAdded) {
    lastAdded.quantity++;
  } else {
    addProduct(product);
  }
};

const decreaseQuantity = (product) => {
  let targetIndex = -1;

  if (hasAttributes(product) && areAllAttributesSelected(product)) {
    const inventory = findMatchingInventory(product);
    targetIndex = selectedAddons.value.findIndex(a => a.product.id === product.id && a.inventory?.id === inventory?.id);
  }

  if (targetIndex === -1) {
    const productIndices = selectedAddons.value
        .map((a, i) => a.product.id === product.id ? i : -1)
        .filter(i => i !== -1);
    if (productIndices.length > 0) targetIndex = productIndices[productIndices.length - 1];
  }

  if (targetIndex !== -1) {
    if (selectedAddons.value[targetIndex].quantity > 1) {
      selectedAddons.value[targetIndex].quantity--;
    } else {
      selectedAddons.value.splice(targetIndex, 1);
      // Clean up error if removed
      if (getProductQuantity(product) === 0) delete attributeErrors.value[product.id];
    }
  }
};

const totalAddonsCount = computed(() => selectedAddons.value.reduce((total, a) => total + a.quantity, 0));

const calculatedAddonsPrice = computed(() => {
  return selectedAddons.value.reduce((total, addon) => {
    const price = addon.product.offered > 0 ? addon.product.offered : addon.product.selling;
    return total + (parseFloat(price || 0) * addon.quantity);
  }, 0);
});

const totalPrice = computed(() => (props.baseItemPrice || 0) + calculatedAddonsPrice.value);

const close = () => {
  selectedAddons.value = [];
  productAttributes.value = {};
  attributeErrors.value = {};
  emit("close");
};

const emitContinue = () => {
  emit("continue", {
    withAddons: selectedAddons.value.length > 0,
    addons: selectedAddons.value
  });
};

</script>

<style scoped>


/* =========================
   PAGINATION – UI ONLY
========================= */

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  margin-top: 30px;
  padding: 12px 16px;
  background: none;              /* 🔒 solid background */
  border: none;
  border-radius: 8px;
}

/* Buttons */
.page-btn {
  padding: 6px 16px;
  border: 1px solid #ddd;
  background: #c8a330;
  color: #333;
  cursor: pointer;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

/* Hover */
.page-btn:hover:not(:disabled) {
  border-color: #c8a330;
  color: #c8a330;
  background: #faf9f6;
}

/* Disabled */
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: #f5f5f5;
}

/* Page info text */
.page-info {
  font-size: 0.8rem;
  font-weight: 600;
  color: #666;
  white-space: nowrap;
}


.product-attributes {
  margin-bottom: 12px;
  width: 100%;
  text-align: left;
}

.attribute-group {
  margin-bottom: 10px;
}

.attribute-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #666;
  margin-bottom: 4px;
}

.attribute-options {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.attr-btn {
  padding: 4px 10px;
  background: #fcfcfc;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  transition: all 0.2s;
  color: #333;
}

.attr-btn:hover {
  border-color: #aaa;
}

.attr-btn.selected {
  background: #333;
  border-color: #333;
  color: #fff;
}

.error-text {
  color: #c0392b;
  font-size: 0.7rem;
  margin-top: 4px;
}

/* Original Styles */
.cart-popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.cart-popup {
  background: #fff;
  max-width: 1500px;
  width: 95%;
  height: 85vh;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 25px rgba(0,0,0,0.2);
  overflow: hidden;
}

.cart-popup-header {
  background: #fff;
  padding: 15px 20px;
  text-align: center;
  position: relative;
  border-bottom: 1px solid #eee;
}

.cart-popup-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #666;
}

.cart-popup-categories {
  padding: 10px 15px;
  display: flex;
  gap: 20px;
  border-bottom: 1px solid #e5e5e5;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
}

/* Custom scrollbar for categories */
.cart-popup-categories::-webkit-scrollbar {
  height: 4px;
}

.cart-popup-categories::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.cart-popup-categories::-webkit-scrollbar-thumb {
  background: #c8a330;
  border-radius: 10px;
}

.cart-popup-categories::-webkit-scrollbar-thumb:hover {
  background: #c8a330;
}

.category-pill {
  flex: 0 0 auto;
  padding: 8px 15px;
  cursor: pointer;
  white-space: nowrap;
  font-weight: 500;
  color: #666;
  font-size: 0.95rem;
  /*border-bottom: 2px solid transparent;*/
  border: 2px solid transparent;
  transition: all 0.2s;
}

.category-pill.active {
  color: #c8a330;
  border-bottom-color: #c8a330;
  /*color: #fff;*/
}

.cart-popup-body {
  padding: 20px;
  flex: 1;
  overflow-y: auto;
  min-height: 0;
  background: #f9f9f9;
}

.loading-state, .no-products {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
  color: #999;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.product-card {
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
  text-align: center;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-image img {
  width: 100%;
  height: 150px;
  object-fit: contain;
}

.info-icon {
  position: absolute;
  top: 0;
  right: 0;
  background: #f0f0f0;
  color: #999;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 12px;
  line-height: 18px;
  text-align: center;
}

.product-title {
  font-size: 0.85rem;
  color: #333;
  margin-bottom: 8px;
  height: auto;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-price {
  font-size: 1rem;
  font-weight: bold;
  color: #000;
  margin-bottom: 12px;
}

.add-btn {
  background: #fff;
  border: 1px solid #ccc;
  color: #333;
 padding: 0px 12px;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
  font-weight: 600;
}

.add-btn:hover {
  border-color: #c8a330;
  color: #e67e22;
}

.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  border: 1px solid #c8a330;
  border-radius: 4px;
  overflow: hidden;
}

.qty-btn {
  background: #fff;
  border: none;
  color: #c8a330;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 1.2rem;
  font-weight: 600;
}

.quantity-display {
  flex: 1;
  text-align: center;
  font-weight: 600;
  background: #fafafa;
  border-left: 1px solid #c8a330;
  border-right: 1px solid #c8a330;
  padding: 8px 0;
}

.cart-popup-footer {
  background: #f4f4f4;
  padding: 15px 20px;
  border-top: 1px solid #ddd;
  z-index: 10;
}

.footer-content {
  display: flex;
  justify-content: center;
  gap: 20px;
  width: 100%;
}

.price-details-container {
  display: flex;
  align-items: center;
  gap: 20px;

}

.price-label {
  font-size: 0.7rem;
  font-weight: bold;
  text-transform: uppercase;
  text-align: left;
  color: black;
}

.price-breakdown {
  display: flex;
  align-items: center;
  gap: 12px;
}

.price-item {
  display: flex;
  flex-direction: column;
}

.p-label {
  font-size: 0.65rem;
  color: #8a8888;
  font-weight: bold;
  text-transform: uppercase;
}

.p-value {
  font-weight: bold;
  color: #555;
}

.operator {
  color: #ccc;
  font-weight: bold;
}

.total .p-value {
  color: #c8a330;
  font-size: 1.1rem;
}

.continue-btn {
  background: #c8a330;
  color: #fff;
  border: none;
  padding:0px 10px 0px 10px;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}



/* ===============================
   LARGE SCREENS (DESKTOP+)
================================ */
@media (min-width: 1024px) {
  .footer-content {
    justify-content: space-between;
  }

  .price-details-container {
    margin-left: auto; /* push price to right */
  }
}

/* ===============================
   TABLETS
================================ */
@media (max-width: 1023px) {
  .footer-content {
    flex-direction: column;
    align-items: stretch;
  }

  .price-details-container {
    justify-content: center;
  }

  .price-breakdown {
    flex-wrap: wrap;
    justify-content: center;
  }

  .continue-btn {
    width: 100%;
    text-align: center;
  }
}

/* ===============================
   MOBILE (SMALL SCREENS) - FULL SCREEN
================================ */
@media (max-width: 768px) {
  .cart-popup-overlay {
    align-items: flex-start;
    padding: 0;
    background: rgba(0,0,0,0.3);
  }

  .popup-title::before {
    content: "Make it extra special";
  }
  .cart-popup {
    width: 100%;
    height: 100vh;
    max-width: 100%;
    border-radius: 0;
  }

  .cart-popup-header {
    padding: 12px 15px;
  }

  .cart-popup-header h3 {
    font-size: 1.05rem;
  }

  .back-btn {
    left: 10px;
    font-size: 20px;
  }

  .cart-popup-categories {
    padding: 12px 15px;
    gap: 8px;
  }

  .category-pill {
    padding: 6px 16px;
    font-size: 0.85rem;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .cart-popup-body {
    padding: 12px;
    background: #fff;
  }

  .product-card {
    padding: 12px;
  }

  .product-image img {
    height: 120px;
  }

  .product-title {
    font-size: 0.8rem;
  }

  .product-price {
    font-size: 1rem;
    margin-bottom: 10px;
  }

  .cart-popup-footer {
    padding: 12px 15px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
  }

  @supports (-webkit-touch-callout: none) {
    .cart-popup-footer {
      padding-bottom: calc(15px + env(safe-area-inset-bottom));
    }
  }
  .footer-summary {
    margin-bottom: 12px;
  }

  .summary-line {
    font-size: 0.85rem;
    padding: 4px 0;
  }

  .total-line {
    font-size: 0.95rem;
  }

  .continue-btn {
    padding: 0px 10px 0px 10px;
    font-size: 0.9rem;
    z-index: 9;
  }

}

@media (max-width: 480px) {
  .cart-popup-header h3 {
    font-size: 0.95rem;
  }

  .category-pill {
    padding: 5px 14px;
    font-size: 0.8rem;
  }

  .products-grid {
    gap: 10px;
  }

  .product-card {
    padding: 10px;
  }

  .product-image img {
    height: 100px;
  }

  .info-icon {
    width: 18px;
    height: 18px;
    font-size: 10px;
    line-height: 18px;
  }

  .product-title {
    font-size: 0.75rem;
    margin-bottom: 6px;
  }

  .product-price {
    font-size: 0.95rem;
  }

  .add-btn {
    font-size: 0.8rem;
    padding: 0px 10px;
  }

}
</style>