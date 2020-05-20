import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import { API_URL } from './config';
import store from '../store';
import { RESET_AUTH } from '../store/actions.type';
import router from '../router';
import NotificationService from './notification.service';

const ApiService = {
    init() {
        Vue.use(VueAxios, axios);
        Vue.axios.defaults.baseURL = API_URL;
        Vue.axios.defaults.withCredentials = true;

        this._setInterceptor();
    },

    get(resource, slug = '') {
        return Vue.axios.get(`${resource}/${slug}`).catch(error => {
            throw new Error(`ApiService ${error}`);
        });
    },

    post(resource, params) {
        return Vue.axios.post(`${resource}`, params);
    },

    update(resource, slug, params) {
        return Vue.axios.put(`${resource}/${slug}`, params);
    },

    put(resource, params) {
        return Vue.axios.put(`${resource}`, params);
    },

    delete(resource) {
        return Vue.axios.delete(resource).catch(error => {
            throw new Error(`ApiService ${error}`);
        });
    },

    _setInterceptor() {
        axios.interceptors.response.use(
            response => response,
            error => {
                // User not authenticated
                if (error.response.status === 401 && store.getters['auth/isAuthenticated'] === true) {
                    store.dispatch(`auth/${RESET_AUTH}`);
                    router.push({
                        name: 'login',
                        query: {}
                    });
                }

                NotificationService.error(error.response.data.message);

                return Promise.reject(error);
            }
        );
    }
};

export default ApiService;
