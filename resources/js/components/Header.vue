<template>
    <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" color="blue darken-3" app dark>
        <v-app-bar-nav-icon v-if="isAuthenticated" @click.stop="toggleNav()">
            <v-icon class="v-app-bar__nav-icon">$vuetify.icons.fasBars</v-icon>
        </v-app-bar-nav-icon>
        <router-link class="toolbar-title-link" :to="{ path: '/' }">
            <v-toolbar-title>
                <span class="hidden-sm-and-down">Webadmin</span>
            </v-toolbar-title>
        </router-link>
        <v-spacer></v-spacer>
        <v-btn v-if="!isAuthenticated" icon :to="{ name: 'login' }">
            <v-icon>$vuetify.icons.fasSignInAlt</v-icon>
        </v-btn>
        <v-btn v-else icon :to="{ name: 'logout' }">
            <v-icon>$vuetify.icons.fasSignOutAlt</v-icon>
        </v-btn>
    </v-app-bar>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Header',
    computed: {
        ...mapGetters({ isAuthenticated: 'auth/isAuthenticated' })
    },
    methods: {
        toggleNav() {
            this.$emit('toggleDrawer');
        },
    }
};
</script>
