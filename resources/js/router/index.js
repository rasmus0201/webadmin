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
            path: '/websites',
            component: () => import('../views/Websites'),
            name: 'websites.index',
            beforeEnter: authGuard
        },
        {
            path: '/databases',
            component: () => import('../views/Databases'),
            name: 'databases.index',
            beforeEnter: authGuard
        },
        {
            path: '/backup/databases',
            component: () => import('../views/backup/Databases'),
            name: 'backup.databases.index',
            beforeEnter: authGuard
        },
        {
            path: '/backup/websites',
            component: () => import('../views/backup/Websites'),
            name: 'backup.websites.index',
            beforeEnter: authGuard
        },
        {
            path: '*',
            component: () => import('../views/NotFound'),
            name: 'not-found'
        }
    ]
});
