<template>
    <page>
        <v-card-title>
            404 - Page not found
        </v-card-title>
        <v-card-text>
            Unfortunately we couldn't find the requested URL.
        </v-card-text>
        <v-card-actions>
            <v-btn to="/" @click.prevent="goBack()">Go back</v-btn>
        </v-card-actions>
    </page>
</template>

<script>
export default {
    name: 'NotFound',
    methods: {
        goBack() {
            window.history.length > 1 ? this.$router.go(-1) : this.$router.push('/');
        }
    }
};
</script>
