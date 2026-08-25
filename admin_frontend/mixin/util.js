
import { useIndexStore } from '~/store/index';


export default {
  computed: {
    defaultImage() {
      const {defaultImage} = useIndexStore();
      return defaultImage
    },
    timeZone(){
      const tz = Intl.DateTimeFormat()?.resolvedOptions()?.timeZone
      if (tz === 'Asia/Calcutta') return 'Asia/Kolkata'
      return tz
    }
  },
  data() {
    return {
      dpConfig: {
        enableTime: true,
        altFormat: "F j, Y, H:i",
        altInput: true
      },
      withdraw: {
        minAmount: 50
      },
      priceType: {
        FLAT: 1,
        PERCENT: 2
      },
      wysiwygType: {
        PAGE: 1,
        PRODUCT_OVERVIEW: 2,
        PRODUCT_DESCRIPTION: 3
      },
      withdrawalStatus: {
        1: this.$t('util.appr'),
        2: this.$t('util.pend'),
        3: this.$t('dataPage.can')
      },
      mediaStorageData: {
        LOCAL: 'LOCAL',
        GCS: 'GCS',
        URL: 'URL'
      },
      withdrawalStatusIn: {
        APPROVED: 1,
        PENDING: 2,
        CANCELLED: 3
      },

      posPaymentIn: {
        CASH: 1,
        COD: 2,
        OFFLINE: 3,
      },
      posPaymentDrop: {
        1: {title: this.$t('ship.ch')},
        2: {title: this.$t('fSale.cod')},
        3: {title: this.$t('ship.ol')},
      },

      paymentTypesDrop: {
        1: {title: this.$t('title.rp')},
        2: {title: this.$t('fSale.cod')},
        3: {title: this.$t('title.sp')},
        4: {title: this.$t('title.pay')},
        5: {title: this.$t('title.fw')},
        6: {title: this.$t('title.ip')},
        7: {title: this.$t('title.bt')},
        8: {title: this.$t('ship.payfast')},
        9: {title: this.$t('ship.telr')},
      },
      paymentChannelModes: {
        1: {title: this.$t('modes.sandbox')},
        2: {title: this.$t('modes.live')},
      },
      paymentTypes: {
        1: this.$t('title.rp'),
        2: this.$t('fSale.cod'),
        3: this.$t('title.sp'),
        4: this.$t('title.pay'),
        5: this.$t('title.fw'),
        6: this.$t('title.ip'),
        7: this.$t('title.bt'),
        8: this.$t('ship.payfast'),
        9: this.$t('ship.telr'),
      },
      messageReply: {
        1: { title: this.$t('util.replied')},
        2: { title: this.$t('util.nyr')}
      },
      mediaStorages: {
        'LOCAL': {title: this.$t('dataPage.localProject')},
        'GCS': {title: this.$t('dataPage.gcp')},
        'URL': {title: this.$t('admin.url')},
      },
      pageComponent: {
        Contact: {title: 'Contact'},
        Sitemap: {title: 'Sitemap'}
      },
      status: {
        PUBLIC: 1,
        PRIVATE: 2,
      },
      paymentStatus: {
        1: { title: this.$t('fSale.paid')},
        '0': { title: this.$t('fSale.unpaid')}
      },
      orderMethodsIn: {
        CASH_ON_DELIVERY: 2,
        BANK: 7
      },
      orderStatus: {
        1: {title: this.$t('util.pend')},
        2: {title: this.$t('util.con')},
        3: {title: this.$t('util.pu')},
        4: {title: this.$t('util.otw')},
        5: {title: this.$t('index.delivered')}
      },
      orderStatusIn: {
        PENDING: 1,
        CONFIRMED: 2,
        PICKED_UP: 3,
        ON_THE_WAY: 4,
        DELIVERED: 5
      },
      shippingType: {
        1: this.$t('util.loc'),
        2: this.$t('util.pick')
      },
      shippingTypeIn: {
        location: 1,
        pickup: 2
      },
      productSourceObj: {
        1: {title: this.$t('error.cat')},
        3: {title: this.$t('admin.tags')},
        4: {title: this.$t('error.brands')},
        5: {title: this.$t('error.prod')},
        6: {title: 'URL'},
      },
      productSources: {
        category: 1,
        sub_category: 2,
        tag: 3,
        brand: 4,
        product: 5,
        url: 6,
      },
      bannerUsed: {
        1: this.$t('util.bfb'),
        2: this.$t('util.bofs'),
        3: this.$t('util.bofs'),
        4: this.$t('util.bofs'),
        5: this.$t('util.tod'),
        6: this.$t('util.bod'),
        7: this.$t('util.dp'),
        8: this.$t('util.tb'),
        9: this.$t('util.pb')
      },
      featuredObj: {
        1: {title: this.$t('prod.yes')},
        2: {title: this.$t('prod.no')},
      },
      orderTypes: {
        all: {title: this.$t('index.all')},
        website: {title: this.$t('ship.ofw')},
        pos: {title: this.$t('ship.po')},
      },

      booleanObj: {
        1: {title: this.$t('prod.yes')},
        '0': {title: this.$t('prod.no')},
      },
      priceTypeObj: {
        1: {title: this.$t('util.flat')},
        2: {title: this.$t('util.perc')}
      },
      priceTFormat: {
        FLAT: 1,
        PERCENT: 2
      },
      verifiedObj: {
        1: {title: this.$t('user.verified')},
        '0': {title: this.$t('user.uv')}
      },
      environmentObj: {
        'development': {title: this.$t('util.dev')},
        'production': {title: this.$t('util.prod')}
      },
      statusObj: {
        1: {title: this.$t('util.public')},
        2: {title: this.$t('util.private')}
      },
      currencyPositions: {
        1: {title: this.$t('util.left')},
        2: {title: this.$t('util.right')}
      },
      currencyPositionsIn: {
        PRE: 1,
        POST: 2
      },
      orderByAttr: {
        created_at: {title: this.$t('category.date')},
        title: {title: this.$t('index.title')},
      },
      taxRuleObj: {
        created_at: {title: this.$t('category.date')},
        title: {title: this.$t('index.title')},
        price: {title: this.$t('brand.price')}
      },
      orderByFlashSale: {
        title: {title: this.$t('index.title')},
        start_time: {title: this.$t('prod.sTime')},
        end_time: {title: this.$t('prod.eTime')},
        created_at: {title: this.$t('category.date')},
      },
    }
  },
  methods: {
    reloadPage(timer = 100){
      setTimeout(()=>{
        window.location.reload()
      }, timer)
    },
    priceByType(totalPrice, price, type) {
      if (parseInt(type) === this.priceType.FLAT) {
        return price
      } else if (parseInt(type) === this.priceType.PERCENT) {
        return (totalPrice * price) / 100
      }
      return 0
    },

    slugChange(evt, title = 'title'){
      if(this.currentLanguage.code === 'en') {
        this.result.slug = this.convertToSlug(this.result[title])
      }
    },
    priceFormatting(price) {
      if (parseInt(this.currencyPosition) === this.currencyPositionsIn.PRE) {
        return this.currencyIcon + this.decimalSeparator(price, this.setting?.decimal_format)
      }
      return this.decimalSeparator(price, this.setting?.decimal_format) + this.currencyIcon
    },
    decimalSeparator(price, decimalSeparator = 'en-US') {

      if(!decimalSeparator){
        decimalSeparator = 'en-US'
      }

      return parseFloat(price)
        .toLocaleString(
          decimalSeparator,
          {
            maximumFractionDigits: 2,
          })
    },
    getDataFromObject(obj, key, defaultValue = null) {
      const spit = key.split('.')
      let val = obj
      spit?.forEach(i => {
        if (!val || val[i] === undefined) {
          val = defaultValue
          return false
        }
        val = val[i]
      })
      return val ?? defaultValue
    },
    getVideoURL(video) {
      const {imgSrcUrl} = useIndexStore();
      return imgSrcUrl + video
    },
    convertToSlug(text) {
      return text?.toLowerCase()
        .replace(/ /g, '-')
        .replace(/[^\w-]+/g, '')
    },
    getImageURL(image) {
      // if (!image) {
      //   image = this.defaultImage
      // }
      //
      // const {imgSrcUrl} = useIndexStore();
      // return imgSrcUrl + image
        const {imgSrcUrl} = useIndexStore();
        if (!image) return imgSrcUrl + this.defaultImage;
        if (image.startsWith('http')) return image; // already absolute URL
        return imgSrcUrl + image;
    },
    getThumbImageURL(image) {
      // if (!image) {
      //   image = this.defaultImage
      // }
      //
      // const {imgSrcUrl, thumbPrefix} = useIndexStore();
      // return imgSrcUrl + thumbPrefix + image
        const {imgSrcUrl, thumbPrefix} = useIndexStore();
        if (!image) return imgSrcUrl + thumbPrefix + this.defaultImage;
        if (image.startsWith('http')) return image; // already absolute URL
        return imgSrcUrl + thumbPrefix + image;
    },
    getImageName(imageLink) {
      const splitted = imageLink.split('/')
      return splitted[splitted.length - 1]
    },
    getStatus(index) {
      return index && this.statusObj[index].title
    },
    isNewRecord(value) {
      return parseInt(value) !== this.status.PUBLIC
    },
    getFeatured(index) {
      return index && this.featuredObj[index].title
    },
    getBoolean(index) {
      return this.booleanObj[index].title
    },
    getVerificationStatus(index) {
      return this.verifiedObj[index].title
    },
    formatPrice(num) {
      return parseFloat(num)?.toFixed(2)
    },
    priceFormat({type, price}) {
      if (this.priceTFormat.PERCENT === parseInt(type)) {
        return `${price}%`
      }
      return this.priceFormatting(price)
    }
  }
}
