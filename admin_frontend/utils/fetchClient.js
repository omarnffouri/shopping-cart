import {useRuntimeConfig} from "nuxt/app";
import json from '~/jsconfig.json'

export const createFetchClient = (token, defaultHeaders = {
    Accept: 'application/json',
    "Content-type": "application/json; charset=UTF-8"
}) => {
    return async (url, options = {}) => {

        const mergedOptions = {
            ...options,
            headers: {
                ...token,
                ...defaultHeaders,
                ...options.headers,
            },
        };

        const config = useRuntimeConfig();
        const baseUrl = config.public.apiBase + json.api.url;
        return await fetch(`${baseUrl}${url}`, mergedOptions);
    };
};
