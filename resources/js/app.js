import Vue from 'vue';
import { sync } from 'vuex-router-sync';
import Toasted from 'vue-toasted';
import App from './App.vue';
import router from './router';
import store from './store';
import { CHECK_AUTH } from './store/actions.type';
import ApiService from './common/api.service';

// Bootstrap
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// Font awesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Register toast plugin
Vue.use(Toasted);

library.add([faEye, faEyeSlash]);
Vue.component('fa-icon', FontAwesomeIcon);

// Syncs the router with the state, making it possible to return state values with router params in it
sync(store, router);

ApiService.init();

// Ensure we checked auth before each page load.
router.beforeEach((to, from, next) => Promise.all([store.dispatch(`auth/${CHECK_AUTH}`)]).then(next));

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app');
