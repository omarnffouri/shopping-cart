import CryptoJS from 'crypto-js'

export function phpDecryption(encrypted) {
    if (!encrypted) {
        return ''
    }

    const DataKey = CryptoJS.enc.Utf8.parse('70123456891245689013234568090717')
    const DataVector = CryptoJS.enc.Utf8.parse('1124678390523412')

    const decrypted = CryptoJS.AES.decrypt(encrypted, DataKey, { iv: DataVector })
    return CryptoJS.enc.Utf8.stringify(decrypted)
}
