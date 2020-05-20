<template>
    <header class="header">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <router-link :to="{ path: '/' }" class="navbar-brand">Webadmin</router-link>
                <button class="navbar-toggler" data-toggle="collapse" data-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <template v-if="!isAuthenticated">
                            <li class="nav-item">
                                <router-link :to="{ name: 'login' }" class="nav-link">Log ind</router-link>
                            </li>
                        </template>
                        <template v-else>
                            <li class="nav-item dropdown">
                                <router-link to="/#" id="navbarDropdown" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ user.name }} <span class="caret"></span>
                                </router-link>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <router-link :to="{ name: 'logout' }" class="dropdown-item">
                                        Logout
                                    </router-link>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Header',
    computed: {
        ...mapGetters({
            isAuthenticated: 'auth/isAuthenticated',
            user: 'user/user'
        })
    }
};
</script>
