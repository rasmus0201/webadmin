<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">404 - Page not found</div>

                    <div class="card-body">
                        <p>Unfortunately we couldn't find the requested URL.</p>
                        <a href="/" @click.prevent="goBack()">Go back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'not-found',
    methods: {
        goBack() {
            window.history.length > 1 ? this.$router.go(-1) : this.$router.push('/');
        }
    }
};
</script>
