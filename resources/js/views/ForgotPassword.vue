<template>
    <page justify="center" :cols="6">
        <v-card-title>
            Reset password
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
                <v-btn class="mt-4" @click="submit">Request reset</v-btn>
            </v-form>
        </v-card-text>
    </page>
</template>

<script>
export default {
    name: 'ForgotPassword',
};
</script>

<script>
export default {
    name: 'ForgotPassword',
    data() {
        return {
            valid: false,
            form: {
                email: '',
            },
            rules: {
                email: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
                ]
            }
        };
    },
    methods: {
        async submit() {
            this.$refs.form.validate();

            if (!this.valid) {
                return Promise.reject('Form is invalid');
            }
        }
    }
};
</script>
