import {useIndexStore} from "../store";
import {storeToRefs} from "pinia";

export function useValidationHelper() {

    const allowedImageExtensions = /(\.jpg|\.jpeg|\.png|\.svg|\.webp|\.gif)$/i;
    const allowedVideoExtensions = /(\.mp4)$/i;
    const allowedExcelExtensions = /(\.xlsx)$/i;
    const allowedZipExtensions = /(\.zip)$/i;
    const passwordLength = 6;
    const reg = /^(([^<>()\]\\.,;:\s@"]+(\.[^<>()\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/;

    const indexStore = useIndexStore();
    const {media} = storeToRefs(indexStore);
    const {t} = useI18n();

    const isValidExcel = (file, size = media.value.file) => {
        if (file.size > size * 1024) {
            return t('util.fSize', {size: size});
        } else if (!allowedExcelExtensions.exec(file.name)) {
            return t('util.invFile');
        }
        return null
    };

    const isValidZip = (file, size = media.value.file) => {
        if (file.size > size * 1024) {
            return t('util.fSize', {size: size});
        } else if (!allowedZipExtensions.exec(file.name)) {
            return t('util.invFile');
        }
        return null
    };

    const isValidImage = (file, size = media.value.image, isImage = true) => {
        if (file.size > size * 1024) {
            return t('util.fSize', {size: size});
        } else if (isImage && !allowedImageExtensions.exec(file.name)) {
            return t('util.invFile');
        } else if (!isImage && !allowedVideoExtensions.exec(file.name)) {
            return t('util.invFile');
        }
        return null;
    };

    const isValidEmail = (email) => {
        return reg.test(email)
    };

    const isValidLength = (password) => {
        return (password && (password.length >= passwordLength)) || false
    };


    return {
        isValidEmail, isValidLength
    }
}


