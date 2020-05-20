import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';

import auth from './auth.module';
import user from './user.module';

Vue.use(Vuex);

const vuexLocal = new VuexPersistence({
    storage: window.localStorage,
    modules: ['auth', 'user']
});

export default new Vuex.Store({
    modules: {
        auth,
        user
    },
    plugins: [vuexLocal.plugin]
});
