import store from './../../store';

export default (to, from, next) => {
    if (store.getters['auth/isAuthenticated']) {
        return next();
    }

    next({
        name: 'login'
    });
};
