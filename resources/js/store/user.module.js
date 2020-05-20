import { FETCH_USER, LOGOUT } from './actions.type';
import { SET_USER, PURGE_USER } from './mutations.type';
import ApiService from '../common/api.service';

const state = {
    user: {}
};

const getters = {
    user(state) {
        return state.user;
    }
};

const actions = {
    async [LOGOUT](context) {
        context.commit(PURGE_USER);
    },
    async [FETCH_USER](context) {
        return new Promise((resolve, reject) => {
            ApiService.get('user/me')
                .then(response => response.data)
                .then(result => {
                    if (!result.success || !result.data.user) {
                        reject(result.message);
                        return;
                    }

                    context.commit(SET_USER, result.data.user);
                    resolve(result);
                })
                .catch(error => {
                    reject(error);
                });
        });
    },
};

const mutations = {
    [SET_USER](state, user) {
        state.user = user;
    },
    [PURGE_USER](state) {
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
