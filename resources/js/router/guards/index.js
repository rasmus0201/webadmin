import auth from './auth';
import guest from './guest';

export const authGuard = auth;
export const guestGuard = guest;

export default {
    authGuard,
    guestGuard
};
