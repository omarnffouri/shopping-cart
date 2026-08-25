import {useSettingStore} from "../store/setting";
import {useConstants} from "./useConstants";
import {storeToRefs} from "pinia";
import {useIndexStore} from "../store";

export function useUtils() {

  const getTimeZone = () => {
    const tz = Intl.DateTimeFormat()?.resolvedOptions()?.timeZone;
    if (tz === 'Asia/Calcutta') return 'Asia/Kolkata';
    return tz;
  };

  const settingStore = useSettingStore();
  const {currencyPositionsIn, featuredObj, statusObj, priceTFormat, verifiedObj, booleanObj} = useConstants();
  const {setting} = storeToRefs(settingStore);

  const priceFormatting = (price) => {

    if (parseInt(setting.value?.currency_position) === currencyPositionsIn.PRE) {
      return setting.value?.currency_icon + decimalSeparator(price, setting.value?.decimal_format);
    }
    return decimalSeparator(price, setting.value?.decimal_format) + setting.value?.currency_icon;
  };

  const priceFormat = ({type, price}) => {
    if (priceTFormat.PERCENT === parseInt(type)) {
      return `${price}%`;
    }
    return priceFormatting(price);
  }

  const decimalSeparator = (price, decimalSeparator = 'en-US') => {
    if (!decimalSeparator) {
      decimalSeparator = 'en-US'
    }
    return parseFloat(price).toLocaleString(decimalSeparator, {maximumFractionDigits: 2,})
  };

  const convertToSlug = (text) => {
    return text?.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
  };


  const getFeatured = (index) => {
    return index && featuredObj[index].title
  };

  const getStatus = (index) => {
    return index && statusObj[index].title;
  };

  const getVerificationStatus = (index) => {
    return verifiedObj[index].title;
  };

  const getBoolean = (index) => {
    return booleanObj[index].title
  };

  const {imgSrcUrl, thumbPrefix, defaultImage} = storeToRefs(useIndexStore());

  const getThumbImageURL = (image = null) => {
    //return imgSrcUrl.value + thumbPrefix.value + image || defaultImage.value;
      if (!image) {
          return imgSrcUrl.value + thumbPrefix.value + defaultImage.value
      }

      // If it's already a full URL, return as-is
      if (image.startsWith('http://') || image.startsWith('https://')) {
          return image
      }
      return imgSrcUrl.value + thumbPrefix.value + image
  };

  const getImageURL = (image = null) => {
    //return imgSrcUrl.value + (image || defaultImage.value);

      if (!image) {
          return imgSrcUrl.value + (image || defaultImage.value)
      }

      // If it's already a full URL, return as-is
      if (image.startsWith('http://') || image.startsWith('https://')) {
          return image
      }

      // Otherwise, prepend base + prefix
      return imgSrcUrl.value + (image || defaultImage.value)
  };

  const getDataFromObject = (obj, key, defaultValue = null) => {
    const spit = key.split('.');
    let val = obj;
    spit?.forEach(i => {
      if (!val || val[i] === undefined) {
        val = defaultValue;
        return false;
      }
      val = val[i];
    })
    return val ?? defaultValue;
  };


  const getImageName = (imageLink) => {
    const splitted = imageLink.split('/');
    return splitted[splitted.length - 1];
  };


  return {
    getTimeZone, priceFormatting, convertToSlug, getFeatured, getStatus, getVerificationStatus, getBoolean,
    getThumbImageURL, priceFormat, getDataFromObject, getImageName, getImageURL
  }
}


