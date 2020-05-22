<template>
    <v-app>
        <v-navigation-drawer  v-if="isAuthenticated" v-model="drawer" :clipped="$vuetify.breakpoint.lgAndUp" app>
            <SidebarItems />
        </v-navigation-drawer>

        <Header @toggleDrawer="drawer = !drawer" />

        <v-content>
            <v-container class="fill-height align-items-start" fluid>
                <router-view :key="$route.path"></router-view>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
import { mapGetters } from 'vuex';
import Header from './components/Header';
import SidebarItems from './components/SidebarItems';

export default {
    components: {
        Header,
        SidebarItems
    },
    computed: {
        ...mapGetters({ isAuthenticated: 'auth/isAuthenticated' })
    },
    data() {
        return {
            drawer: true,
        };
    }
}
</script>
