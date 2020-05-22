import Vue from 'vue';
import { sync } from 'vuex-router-sync';
import Toasted from 'vue-toasted';
import vuetify from './vuetify';
import App from './App.vue';
import Page from './components/Page';
import VcIcon from './components/VcIcon';
import router from './router';
import store from './store';
import { CHECK_AUTH } from './store/actions.type';
import ApiService from './common/api.service';

// Font awesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';

library.add(fab);
library.add(fas);
library.add(far);

Vue.component('font-awesome-icon', FontAwesomeIcon);
Vue.component('vc-icon', VcIcon); // Global Vuetify FA Icon

Vue.component('page', Page); // Global page

// Register toast plugin
Vue.use(Toasted);

// Syncs the router with the state, making it possible to return state values with router params in it
sync(store, router);

ApiService.init();

// Ensure we checked auth before each page load.
router.beforeEach((to, from, next) => Promise.all([store.dispatch(`auth/${CHECK_AUTH}`)]).then(next));

new Vue({
    router,
    store,
    vuetify,
    render: h => h(App)
}).$mount('#app');
