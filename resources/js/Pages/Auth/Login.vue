<template>
    <Head>
        <title>Login Account - Aplikasi Inventory</title>
    </Head>

    <div class="col-md-4">
        <div class="fade-in">
            <div class="text-center mb-4">
                <a href="" class="text-dark text-decoration-none">
                    <img src="/images/inventory-management.png" width="70" />
                    <h3 class="mt-2 font-weight-bold">APLIKASI INVENTORY</h3>
                </a>
            </div>
            <div class="card-group">
                <div class="card border-top-purple border-0 shadow-sm rounded-">
                    <div class="card-body">
                        <div class="text-start">
                            <h5>LOGIN ACCOUNT</h5>
                            <p class="text-muted">Sign In to your account</p>
                        </div>
                        <hr />
                        <div
                            v-if="session.status"
                            class="alert alert-success mt-2"
                        >
                            {{ session.status }}
                        </div>
                        <form @submit.prevent="submit">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                </div>
                                <input
                                    class="form-control"
                                    v-model="form.email"
                                    :class="{ 'is-invalid': errors.email }"
                                    type="email"
                                    placeholder="Email Address"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                            </div>
                            <div v-if="errors.email" class="alert alert-danger">
                                {{ errors.email }}
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                </div>
                                <input
                                    class="form-control"
                                    v-model="form.password"
                                    :class="{ 'is-invalid': errors.password }"
                                    type="password"
                                    placeholder="Password"
                                    required
                                    autocomplete="current-password"
                                />
                            </div>
                            <div
                                v-if="errors.password"
                                class="alert alert-danger"
                            >
                                {{ errors.password }}
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <input
                                        type="checkbox"
                                        id="remember"
                                        class="form-check-inline"
                                        v-model="form.remember"
                                    />
                                    <label
                                        for="remember"
                                        class="form-check-label"
                                        >Remember Me</label
                                    >
                                </div>
                                <div class="col-12 mb-3 text-end">
                                    <Link href="/forgot-password"
                                        >Forgot Password?</Link
                                    >
                                </div>
                                <div class="col-12">
                                    <button
                                        class="btn btn-primary shadow-sm rounded-sm px-4 w-100"
                                        type="submit"
                                    >
                                        LOGIN
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
import LayoutAuth from "../../Layouts/Auth.vue";
import { reactive } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head, Link } from "@inertiajs/inertia-vue3";

export default {
    name: "LoginPage",

    layout: LayoutAuth,

    components: {
        Head,
        Link,
    },

    props: {
        errors: Object,
        session: Object,
    },

    // define Composition API
    setup() {
        // define form state
        const form = reactive({
            email: "",
            password: "",
            remember: false,
        });

        // submit method
        const submit = () => {
            // post data to server
            Inertia.post("/login", {
                // data
                email: form.email,
                password: form.password,
                remember: form.remember,
            });
        };

        // return from state and submit method
        return {
            form,
            submit,
        };
    },
};
</script>

<style></style>
