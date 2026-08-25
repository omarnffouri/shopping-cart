import json from '~/jsconfig.json'
import { createFetchClient } from "../utils/fetchClient";

export default {
  async convertImage(params, bearer) {
    const fetchClient = createFetchClient({ 'Authorization': `Bearer ${bearer}` });
    const result = await fetchClient(`${json.api.convertImage}/${params}`, {
      method: 'GET',
    });

    return result.json()
  },

  async deleteData(params, bearer, api, lang = null, pathSegment = null) {
    let header = { 'Authorization': `Bearer ${bearer}` }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);

    let queryString = ''
    if (params) {
      queryString = new URLSearchParams(Object?.entries(params)?.filter(([key, value]) => value !== null && value !== undefined))
          ?.toString();
    }

    // Handle dynamic path segments
    let endpoint = json.api[api];
    if (pathSegment) {
      endpoint = `${endpoint}/${pathSegment}`;
    }

    const fullUrl = `${endpoint}?${queryString}`;

    const result = await fetchClient(fullUrl, {
      method: 'DELETE'
    });

    return result.json()
  },
  async setImageById(id = '', params, bearer, api) {
    const fetchClient = createFetchClient({ 'Authorization': `Bearer ${bearer}` }, { Accept: 'application/json' });

    const result = await fetchClient(`${json.api[api]}${id ? '/' + id : ''}`, {
      method: 'post',
      body: params
    });
    return result.json()
  },
  async setById(id, params, bearer, api, lang = null) {
    let header = { 'Authorization': `Bearer ${bearer}` }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }

    let convertedParams = null
    let defaultHeaders = null

    if (params instanceof FormData) {
      convertedParams = params
      defaultHeaders = {
        Accept: 'application/json',
      }
    } else {
      convertedParams = JSON.stringify(params)
      defaultHeaders = {
        Accept: 'application/json',
        "Content-type": "application/json; charset=UTF-8"
      }
    }
    const fetchClient = createFetchClient(header, defaultHeaders);
    const result = await fetchClient(`${json.api[api]}${id ? '/' + id : ''}`, {
      method: 'POST',
      body: params instanceof FormData ? params : JSON.stringify(params)
    });

    return result.json()
  },
  async getById(id, params, bearer, api, lang = null) {
    let header = { 'Authorization': `Bearer ${bearer}` }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(`${json.api[api]}${id ? '/' + id : ''}`, {
      method: 'GET',
      data: params
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },
  // async getRequest(params, bearer, api, lang = null) {
  //
  //   let header = { 'Authorization': `Bearer ${bearer}` }
  //   if (lang) {
  //     header = { ...header, ...{ 'Language': lang } }
  //   }
  //
  //   const fetchClient = createFetchClient(header);
  //
  //   let queryString = ''
  //   if (params) {
  //     queryString = new URLSearchParams(Object?.entries(params)?.filter(([key, value]) => value !== null && value !== undefined))
  //       ?.toString();
  //   }
  //
  //   const fullUrl = `${json.api[api]}?${queryString}`;
  //
  //   const result = await fetchClient(fullUrl, {
  //     method: 'GET'
  //   });
  //
  //   if (result?.status === 401) {
  //     return Promise.reject({ status: 401, message: '' })
  //   }
  //
  //   return result.json()
  // },

  async getRequest(params, bearer, api, lang = null, pathSegment = null) {

    let header = { 'Authorization': `Bearer ${bearer}` }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }

    const fetchClient = createFetchClient(header);

    let queryString = ''
    if (params) {
      queryString = new URLSearchParams(Object?.entries(params)?.filter(([key, value]) => value !== null && value !== undefined))
          ?.toString();
    }

    // Handle dynamic path segments
    let endpoint = json.api[api];
    if (pathSegment) {
      endpoint = `${endpoint}/${pathSegment}`;
    }

    const fullUrl = `${endpoint}?${queryString}`;

    const result = await fetchClient(fullUrl, {
      method: 'GET'
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },

  async setRequest(params, bearer, api, lang = null, customHeaders = {}) {
    let header = { 'Authorization': `Bearer ${bearer}`, ...customHeaders }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }

    let convertedParams = null
    let defaultHeaders = null

    if (params instanceof FormData) {
      convertedParams = params
      defaultHeaders = {
        Accept: 'application/json',
      }
    } else {
      convertedParams = JSON.stringify(params)
      defaultHeaders = {
        Accept: 'application/json',
        "Content-type": "application/json; charset=UTF-8"
      }
    }

    const fetchClient = createFetchClient(header, defaultHeaders);
    const result = await fetchClient(json.api[api], {
      method: 'POST',
      body: params instanceof FormData ? params : JSON.stringify(params)
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },
  async unAuthPost(params, api, lang = null) {
    let header = {}
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(json.api[api], {
      method: 'POST',
      body: JSON.stringify(params)
    });
    return result.json()
  },
  async unAuthGet(params, api, lang = null) {
    let header = {}
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(json.api[api], {
      method: 'GET',
      data: params
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },
  async unAuthSetRequest(params, api, lang = null, method = 'POST') {
    let header = {}
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(json.api[api], {
      method,
      data: params
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },
  async downloadRequest(params, bearer, api, lang = null) {
    let header = { 'Authorization': `Bearer ${bearer}` }

    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(json.api[api], {
      method: 'GET',
      data: params
    });

    const res = await result.blob()

    const contentDispositionHeader = result.headers.get('content-disposition');
    const regex = /filename=([^;]+)/;
    const matches = regex.exec(contentDispositionHeader);

    // Check if the filename was found in the header
    if (matches && matches.length > 1) {
      const filename = matches[1];


      const blob = new Blob([res], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      });

      const url = window.URL.createObjectURL(blob);

      // Create a temporary <a> element and trigger a download with the extracted filename
      const a = document.createElement('a');
      a.href = url;
      a.download = filename; // Use the extracted filename
      document.body.appendChild(a);
      a.click();

      // Clean up by revoking the blob URL
      window.URL.revokeObjectURL(url);
    }
    return res
  },


  async getByIdRequest(id, bearer, api, lang = null) {
    let header = { 'Authorization': `Bearer ${bearer}` }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }
    const fetchClient = createFetchClient(header);
    const result = await fetchClient(`${json.api[api]}/${id}/edit`, {
      method: 'GET'
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },

  async updateRequest(id, payload, bearer, api, lang = null, customHeaders = {}) {
    let header = {
      'Authorization': `Bearer ${bearer}`,
      ...customHeaders
    }
    if (lang) {
      header = { ...header, ...{ 'Language': lang } }
    }

    const defaultHeaders = {
      Accept: 'application/json',
      "Content-type": "application/json; charset=UTF-8"
    }

    const fetchClient = createFetchClient(header, defaultHeaders);

    const result = await fetchClient(`${json.api[api]}/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload)
    });

    if (result?.status === 401) {
      return Promise.reject({ status: 401, message: '' })
    }

    return result.json()
  },
}
