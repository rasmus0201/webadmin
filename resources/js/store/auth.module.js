import {
    LOGIN,
    LOGOUT,
    CHECK_AUTH,
    RESET_AUTH
} from "./actions.type";
import { SET_AUTH, PURGE_AUTH } from "./mutations.type";
import ApiService from "../common/api.service";

const state = {
    user: {},
    isAuthenticated: false
};

const getters = {
    user(state) {
        return state.user;
    },
    isAuthenticated(state) {
        return state.isAuthenticated;
    }
};

const actions = {
    async [LOGIN](context, credentials) {
        return new Promise((resolve, reject) => {
            ApiService.post("auth/login", credentials)
                .then(response => response.data)
                .then(result => {
                    if (!result.success || !result.data.user) {
                        reject(result.message);
                        return;
                    }

                    context.commit(SET_AUTH, result.data.user);
                    resolve(result);
                })
                .catch(({
                    response
                }) => {
                    reject(response);
                });
        });
    },
    async [LOGOUT](context) {
        return new Promise((resolve, reject) => {
            ApiService.post("auth/logout")
                .then(({
                    response
                }) => {
                    resolve(response.data);
                })
                .catch(({
                    response
                }) => {
                    reject(response);
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
    [SET_AUTH](state, user) {
        state.isAuthenticated = true;
        state.user = user;
    },
    [PURGE_AUTH](state) {
        state.isAuthenticated = false;
        state.user = {};
    }
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters
};
