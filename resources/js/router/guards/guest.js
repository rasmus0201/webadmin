import store from './../../store';

export default (to, from, next) => {
    if (!store.getters['auth/isAuthenticated']) {
        return next();
    }

    return next({
        name: 'dashboard'
    });
};
