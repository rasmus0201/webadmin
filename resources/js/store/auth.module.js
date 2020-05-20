import {
    LOGIN,
    LOGOUT,
    CHECK_AUTH,
    RESET_AUTH
} from './actions.type';
import { SET_AUTH, PURGE_AUTH } from './mutations.type';
import ApiService from '../common/api.service';

const state = {
    isAuthenticated: false
};

const getters = {
    isAuthenticated(state) {
        return state.isAuthenticated;
    }
};

const actions = {
    async [LOGIN](context, credentials) {
        return new Promise((resolve, reject) => {
            ApiService.post('auth/login', credentials)
                .then(response => response.data)
                .then(result => {
                    if (!result.success) {
                        reject(result.message);
                        return;
                    }

                    context.commit(SET_AUTH);
                    resolve(result);
                })
                .catch(error => {
                    reject(error);
                });
        });
    },
    async [LOGOUT](context) {
        return new Promise((resolve, reject) => {
            ApiService.post('auth/logout')
                .then(response => response.data)
                .then(result => {
                    resolve(result);
                })
                .catch(error => {
                    reject(error);
                })
                .finally(() => {
                    // We always need to reset frontend
                    context.commit(PURGE_AUTH);
                });
        });
    },
    [CHECK_AUTH]({ commit, state }) {
        if (state.isAuthenticated !== true) {
            commit(PURGE_AUTH);
        }
    },
    [RESET_AUTH]({ commit }) {
        commit(PURGE_AUTH);
    }
};

const mutations = {
    [SET_AUTH](state) {
        state.isAuthenticated = true;
    },
    [PURGE_AUTH](state) {
        state.isAuthenticated = false;
    }
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters
};
