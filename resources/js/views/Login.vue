<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Login</div>

                    <div class="card-body">
                        <form class="login-form" @submit.prevent="submit()">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">
                                    E-mail
                                </label>

                                <div class="col-md-6">
                                    <input v-model="form.email" id="email" type="email" class="form-control" required autocomplete="email" autofocus>

                                    <span class="invalid-feedback" role="alert">
                                        <strong>Error message</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">
                                    Password
                                </label>

                                <div class="col-md-6 input-group">
                                    <input v-model="form.password" id="password" :type="showPassword ? 'text' : 'password'" class="form-control" required autocomplete="current-password">
                                    <div class="input-group-append">
                                        <span class="input-group-text" @click="togglePasswordVisibility">
                                            <fa-icon v-if="!showPassword" icon="eye-slash"/>
                                            <fa-icon v-else icon="eye"/>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Login
                                    </button>

                                    <router-link :to="{ name: 'password.forgot' }" class="btn btn-link">
                                        Forgot password?
                                    </router-link>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import { LOGIN, FETCH_USER } from '../store/actions.type';

export default {
    name: 'Login',
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            showPassword: false
        };
    },
    methods: {
        ...mapActions({
            login: `auth/${LOGIN}`,
            fetchUser: `user/${FETCH_USER}`
        }),

        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },

        async submit() {
            try {
                await this.login(this.form);
                await this.fetchUser();
            } catch (error) {
                console.log(error);
            }

            await this.$router.push({ name: 'dashboard' });
        }
    }
};
</script>
