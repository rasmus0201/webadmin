import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';

import auth from './auth.module';

Vue.use(Vuex);

const vuexLocal = new VuexPersistence({
    storage: window.localStorage,
    modules: ['auth']
});

export default new Vuex.Store({
    modules: {
        auth
    },
    plugins: [vuexLocal.plugin]
});
