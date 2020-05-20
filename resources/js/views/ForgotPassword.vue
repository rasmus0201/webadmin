<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset password</div>

                    <div class="card-body">
                        <form class="forgot-password-form" @submit.prevent="submit()">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">
                                    E-mail
                                </label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" required autocomplete="email" autofocus>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Request password reset
                                    </button>
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
export default {
    name: "ForgotPassword",
    methods: {
        submit() {

        }
    }
};
</script>
