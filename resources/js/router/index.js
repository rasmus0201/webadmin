import Vue from "vue";
import Router from "vue-router";
import { authGuard } from "./guards";

Vue.use(Router);

export default new Router({
    mode: "history",
    routes: [{
            path: "/",
            component: () => import("../views/Dashboard"),
            name: "dashboard",
            beforeEnter: authGuard
        },
        {
            path: "/login",
            component: () => import("../views/Login"),
            name: "login"
        },
        {
            path: "/password/forgot",
            component: () => import("../views/ForgotPassword"),
            name: "password.forgot"
        },
        {
            path: "*",
            component: () => import("../views/404"),
            name: "not-found"
        }
    ]
});
