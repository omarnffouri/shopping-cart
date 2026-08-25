<template>
  <div>

    <div class="dply-felx gap-15 title ptb-5 b-0">
      <h4 class="">{{ $t('prod.pInv') }}</h4>

      <button class="btn-clear dply-felx" @click.prevent="inventoryOpen = !inventoryOpen">
        <i
          class="icon black ignore-click"
          :class="[{'arrow-up': inventoryOpen}, {'arrow-down': !inventoryOpen}]"
        />
      </button>
    </div>

    <div v-if="inventoryOpen" class="form-wrapper attr-inventory b-t">
      <error-formatter
        type="inventory"
      />
      <form
        @submit.prevent="saveInventory"
      >
        <div
          class="mb-20 mb-sm-15 atr-wrapper"
          v-if="attributes && attributes.length"
        >
          <div
            v-for="(attr, index) in attributes"
            :key="index"
            class="single-atr"
          >
            <template
              v-if="!!attr.values.length"
            >
              <label
                class="cb atr-title f-1-2 bold"
              >
                <input
                  v-model="selectedAttributes"
                  type="checkbox"
                  :value="attr.id"
                  @change="attributeChanged(attr, $event)"
                >
                <span>{{attr.title}}</span>
              </label>

              <label
                v-for="(val, i) in attr.values"
                :key="i"
                class="cb"
              >
                <input
                  v-model="selectedAttributeValues"
                  type="checkbox"
                  :value="val.id"
                  @change="attributeValueChanged(val, $event)"
                >
                <span>{{ val.title }}</span>
              </label>
            </template>
          </div>
        </div>
        <div
          class="inventory-wrap mb-20 mb-sm-15"
        >
          <div class="dply-felx f-column gap-15 ">
            <div class="f-wrap tr dply-felx gap-10 ">

              <span class="title">{{ $t('list.attr') }}</span>

              <div class="right dply-felx gap-10 f-wrap">
                <div class="inputs dply-felx gap-10 f-wrap">
                  <span class="w-120x">{{ $t('title.sku') }}</span>
                  <span class="w-120x">{{ $t('brand.price') }}({{currencyIcon}})</span>
                  <span class="w-120x">{{ $t('fSale.qty') }}</span>
                  <span class="w-120x"></span>
                </div>

              </div>

            </div>

            <div
              v-for="(item, index) in combinations"
              :key="index"
              class="tr dply-felx gap-10"
            >
              <h5
                v-if="item.values.length"
                class="title">{{ renderLabel(item.values) }}</h5>
              <h5
                v-else
                class="title">{{ $t('list.aq') }}</h5>


              <div class="right dply-felx gap-10 f-wrap">
                <div class="inputs dply-felx gap-10 f-wrap">

                  <div class="w-120x">
                    <input
                      type="text"
                      v-model="item.sku"
                      :placeholder="$t('title.sku')"
                    />
                  </div>
                  <div class="w-120x">
                    <input
                      :disabled="!item.values.length"
                      type="number"
                      step="any"
                      v-model="item.price"
                      :placeholder="$t('brand.price')"
                    />
                  </div>
                  <div class="w-120x">
                    <input
                      type="number"
                      step="any"
                      v-model="item.quantity"
                      :placeholder="$t('fSale.qty')"
                    />
                  </div>

                </div>

              </div>


            </div>
          </div>
        </div>

        <div
          v-if="$can('product', 'edit') || $can('product', 'create')"
          class="dply-felx j-right"
        >
          <ajax-button
            name="save-edit"
            class="primary-btn mr-10"
            :text="$t('list.svn')"
            :fetching-data="formSubmitting  && !redirect"
          />
          <ajax-button
            name="save"
            class="primary-btn"
            :text="$t('setting.sv')"
            :fetching-data="formSubmitting && redirect"
          />
        </div>
      </form>
    </div>


  </div>
</template>

<script>
  import AjaxButton from '~/components/AjaxButton'
  import ErrorFormatter from '~/components/ErrorFormatter'
  import util from "~/mixin/util"
  import validation from "~/mixin/validation"
  import {useCommonStore} from "../../store/common";
  import {storeToRefs} from "pinia";
  import {useSettingStore} from "../../store/setting";

  export default {
    setup(){
      const commonStore = useCommonStore()
      const {getById, setById} = commonStore

      const settingStore = useSettingStore()
      const {setting} = storeToRefs(settingStore)

      return {setting,getById, setById}
    },
    name: 'ProductInventory',
    data() {
      return {
        inventoryOpen: false,
        result: [],
        selectedAttributes: [],
        selectedAttributeValues: [],
        selected: {},
        combinations: [],
        formSubmitting: false,
        redirect: false,
        existing: {},
      }
    },
    props: {
      attributes: {
        type: Array,
        default: []
      },
      productPrice: {
        type: Number,
        default: 0
      }
    },
    mixins: [util, validation],
    components: {
      AjaxButton,
      ErrorFormatter
    },
    computed: {
      currencyIcon() {
        return this.setting?.currency_icon || '$'
      },
      productId() {
        return this.$route.params.id
      },
    },
    methods: {
      async imageDelete(mainIndex, imgIndex) {
        let comb = this.combinations[mainIndex]
        comb.images.splice(imgIndex, 1);

        const temp = this.combinations

        temp[mainIndex] = {...temp[mainIndex], ...comb}

        this.combinations = []

        setTimeout(() => {

          this.combinations = temp

        }, 10)
      },
      fileClicked(refStr) {
        if (this.$refs[refStr] && this.$refs[refStr]?.length) {
          this.$refs[refStr][0]?.click()
        }
      },
      fileChanged(index, evt) {
        let comb = this.combinations[index]


        Object.values(evt?.target?.files)?.forEach((i, key) => {

          if(!this.isValidImage(i)){

            if (!comb?.images) {
              comb = {...comb, ...{images: []}}
            }

            comb.images.push({
              blob: URL.createObjectURL(i),
              file: evt?.target?.files[key]
            })

          }

        })

        const temp = this.combinations

        temp[index] = {...temp[index], ...comb}

        this.combinations = []

        setTimeout(() => {

          this.combinations = temp

        }, 20)
      },
      redirectingEnable(buttonType) {
        this.redirect = buttonType === 'save'
      },
      async fetchingData() {
        try {
          this.loading = true
          this.result = await this.getById({id: this.productId, params: {}, api: 'getInventory'})

          this.loading = false
        } catch (e) {
           showError({
            statusCode: 400,
            message: e.message
          })
        }
      },
      async saveInventory() {
        this.redirectingEnable(event.submitter.name)
        this.formSubmitting = true
        try {

          let formData = new FormData();

          this.combinations.forEach((item, index) => {
            for (const key in item) {
              if (item.hasOwnProperty(key)) {

                if (key === 'images' || key === 'values') {
                  item[key].forEach((nestedItem, nestedIndex) => {
                    for (const nestedKey in nestedItem) {
                      if (nestedItem.hasOwnProperty(nestedKey)) {
                        formData.append(`inventories[${index}][${key}][${nestedIndex}][${nestedKey}]`, nestedItem[nestedKey] ?? "");
                      }
                    }
                  });
                } else {
                  formData.append(`inventories[${index}][${key}]`, item[key] ?? "");
                }
              }
            }
          })

          const data = await this.setById({
            id: this.productId,
            params: formData,
            api: 'setInventory'
          })

          if (data) {
            this.result = data
            await this.mappingData()
            this.$router.push({path: `/products${this.redirect ? '' : '/' + this.productId}`})
          } else {

            this.$emit('has-error');

          }
        } catch (e) {
           showError({
            statusCode: 400,
            message: e.message
          })
        }
        this.formSubmitting = false
      },
      renderLabel(attribute) {
        return attribute.map(i => {
          return i.title
        }).join(' + ')
      },
      combos(list, n = 0, result = [], current = []) {
        if (n === list.length) {
          let inventory = {
            id: '',
            sku: '',
            price: this.productPrice,
            quantity: 0,
          }
          const existingId = current.map(k => {
            return k.id
          }).join('-')
          if (this.existing[existingId] !== undefined) {

            inventory = {
              id: this.existing[existingId].id,
              sku: this.existing[existingId].sku,
              price: this.existing[existingId].price,
              quantity: this.existing[existingId].quantity
            }
          }
          result.push({...inventory, ...{values: current}})
        } else {
          list[n].forEach(item => this.combos(list, n + 1, result, [...current, item]))
        }
        return result
      },
      attributeValueChanged(attributeValue, event) {
        if (event.target.checked) {
          // Adding checked in parent if child is checked
          if (this.selectedAttributes?.findIndex((i) => {
            return i === attributeValue?.attribute_id
          }) === -1) {
            this.selectedAttributes.push(attributeValue.attribute_id)
            this.selected = {...this.selected, ...{[attributeValue.attribute_id]: []}}
          }

          // Preparing the final object
          if (this.selected[attributeValue.attribute_id]?.findIndex(i => {
            return i === attributeValue.id
          }) === -1) {
            this.selected[attributeValue.attribute_id].push(attributeValue)
          }

        } else {
          this.selected[attributeValue.attribute_id]
            = this.selected[attributeValue?.attribute_id].filter((i) => {
            return i.id !== attributeValue.id
          })

          // Removing the parent array if no attribute selected
          if (!this.selected[attributeValue.attribute_id]?.length) {
            delete this.selected[attributeValue.attribute_id]

            // Unchecked the parent if  no attribute selected
            const attributeIndex = this.selectedAttributes.findIndex(i => {
              return attributeValue.attribute_id === i
            })
            this.selectedAttributes.splice(attributeIndex, 1)
          }
        }

        this.combinations = this.combos(Object.values(this.selected))
      },
      attributeChanged(attribute, event) {
        if (event.target.checked) {
          // Adding all attribute values
          const selectedAttributeValues = this.attributes.find(i => {
            return i.id === attribute.id
          })?.values
          this.selected = {...this.selected, ...{[attribute.id]: selectedAttributeValues}}
          const selectedAttributeValueIds = selectedAttributeValues?.map(i => {
            return i.id
          })
          this.selectedAttributeValues = [...new Set(this.selectedAttributeValues.concat(selectedAttributeValueIds))]
        } else {
          // Getting the selected attribute values
          const currentSelectedValues = this.selected[attribute.id]?.map(i => {
            return i.id
          })
          delete this.selected[attribute.id]
          this.selectedAttributeValues =
            this.selectedAttributeValues.filter((value) => {
              return currentSelectedValues.indexOf(value) === -1
            })
        }
        this.combinations = this.combos(Object.values(this.selected))
      },
      async mappingData() {


        if (this.result && this.result?.length) {
          this.selected = {}
          this.combinations = []
          const selectedAttr = []
          const selectedAttrValues = []
          await Object.values(this.result)?.forEach((i) => {
            // Setting the selected attribute and values
            const attributes = []
            i.inventory_attributes.forEach((j) => {
              selectedAttr.push(j.attribute_value.attribute_id)
              selectedAttrValues.push(j.attribute_value.id)
              attributes.push(j.attribute_value)
              // Preparing the selected object
              if (this.selected[j.attribute_value.attribute_id] === undefined) {
                this.selected = {...this.selected, ...{[j.attribute_value.attribute_id]: []}}
              }

              if (this.selected[j.attribute_value.attribute_id].findIndex(i => {
                return j.attribute_value.id === i.id
              }) === -1) {
                this.selected[j.attribute_value.attribute_id].push(j.attribute_value)
              }
            })
            // Making an object with existing values to save the inventory values
            this.existing[attributes.map(k => {
              return k.id
            }).join('-')] = {
              id: i.id,
              price: parseFloat(i.price) > 0 ? i.price : this.productPrice,
              sku: i.sku,
              quantity: i.quantity,
            }
            // Making combination
            this.combinations.push({
              id: i.id,
              sku: i.sku,
              price: parseFloat(i.price) > 0 ? i.price : this.productPrice,
              quantity: i.quantity,
              values: attributes
            })
          })
          this.selectedAttributes = [...new Set(selectedAttr)]
          this.selectedAttributeValues = [...new Set(selectedAttrValues)]
        } else {
          // Default combination
          this.combinations = [{
            price: this.productPrice,
            quantity: 0,
            sku: '',
            images: [],
            values: []
          }]
        }
      },

    },
    beforeCreate() {
    },
    destroyed() {
    },
    created() {
    },
    async mounted() {
      await this.fetchingData()
      await this.mappingData()
    }
  }
</script>
<style lang="stylus">
  .atr-wrapper
    border-radius 5px
    border 1px solid #ddd
    background #f7f7f5

    .single-atr
      display flex
      flex-wrap wrap
      align-items center
      border-bottom 1px solid #ddd

      label
        padding 15px

      .atr-title
        width 120px
        max-width 120px

      &:last-child
        border-bottom none

</style>
