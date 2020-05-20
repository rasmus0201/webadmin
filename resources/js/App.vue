<template>
    <div class="app-content d-flex flex-column">
        <Header />
        <main class="container d-flex py-4">
            <router-view :key="$route.path" class="flex-grow-1"></router-view>
        </main>
        <Footer />
    </div>
</template>

<script>
import Header from "./components/Header";
import Footer from "./components/Footer";

export default {
    name: "App",
    components: {
        Header,
        Footer
    }
};
</script>
