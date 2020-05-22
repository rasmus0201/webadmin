<template>
    <page justify="center" :cols="6">
        <v-card-title>
            Login
        </v-card-title>
        <v-card-text>
            <v-form ref="form" v-model="valid" lazy-validation>
                <v-text-field
                    v-model="form.email"
                    :rules="rules.email"
                    label="E-mail"
                    required
                    autofocus
                    type="email"
                ></v-text-field>
                <v-text-field
                    v-model="form.password"
                    :rules="rules.password"
                    label="Password"
                    required
                    :append-icon="showPassword ? '$vuetify.icons.fasEyeSlash' : '$vuetify.icons.fasEye'"
                    @click:append="togglePasswordVisibility()"
                    :type="showPassword ? 'text' : 'password'"
                ></v-text-field>
                <v-row class="mb-0">
                    <v-col md="8">
                        <v-btn @click="submit">Login</v-btn>

                        <router-link :to="{ name: 'password.forgot' }" class="btn btn-link">
                            Forgot password?
                        </router-link>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </page>
</template>

<script>
import { mapActions } from 'vuex';
import { LOGIN, FETCH_USER } from '../store/actions.type';

export default {
    name: 'Login',
    data() {
        return {
            showPassword: false,
            valid: false,
            form: {
                email: '',
                password: '',
            },
            rules: {
                email: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
                ],
                password: [
                    v => !!v || 'Password is required',
                ],
            }
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
            this.$refs.form.validate();

            if (!this.valid) {
                return Promise.reject('Form is invalid');
            }

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
