import Vue from "vue";
import { sync } from "vuex-router-sync";
import Toasted from "vue-toasted";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import { CHECK_AUTH } from "./store/actions.type";
import ApiService from "./common/api.service";

// Bootstrap
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// Register toast plugin
Vue.use(Toasted);

// Syncs the router with the state, making it possible to return state values with router params in it
sync(store, router);

ApiService.init();

// Ensure we checked auth before each page load.
router.beforeEach((to, from, next) => Promise.all([store.dispatch(CHECK_AUTH)]).then(next));

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount("#app");
