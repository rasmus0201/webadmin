import Vue from 'vue';
import Router from 'vue-router';
import { authGuard, guestGuard } from './guards';
import store from '../store/index';
import { LOGOUT } from '../store/actions.type';

Vue.use(Router);

export default new Router({
    mode: 'history',
    routes: [{
            path: '/',
            component: () => import('../views/Dashboard'),
            name: 'dashboard',
            beforeEnter: authGuard
        },
        {
            path: '/password/forgot',
            component: () => import('../views/ForgotPassword'),
            name: 'password.forgot',
            beforeEnter: guestGuard,
        },
        {
            path: '/login',
            component: () => import('../views/Login'),
            name: 'login',
            beforeEnter: guestGuard,
        },
        {
            path: '/logout',
            name: 'logout',
            beforeEnter: async (to, from, next) => {
                authGuard(to, from, next);

                await store.dispatch(`user/${LOGOUT}`);
                await store.dispatch(`auth/${LOGOUT}`);

                return next({
                    name: 'login'
                });
            }
        },
        {
            path: '*',
            component: () => import('../views/404'),
            name: 'not-found'
        }
    ]
});
