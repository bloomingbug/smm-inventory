<template>
    <Head>
        <title>Edit User - Aplikasi Inventory</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div
                            class="card border-0 rounded-3 shadow border-top-purple"
                        >
                            <div class="card-header">
                                <span class="font-weight-bold"
                                    ><i class="fa fa-user"></i> EDIT USER</span
                                >
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div
                                            class="col-12"
                                            v-if="errors.name_and_email"
                                        >
                                            <div class="alert alert-danger">
                                                {{ errors.name_and_email }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold"
                                                    >Full Name</label
                                                >
                                                <input
                                                    class="form-control"
                                                    v-model="form.name"
                                                    :class="{
                                                        'is-invalid':
                                                            errors.name,
                                                    }"
                                                    type="text"
                                                    placeholder="Full Name"
                                                    required
                                                />
                                            </div>
                                            <div
                                                v-if="errors.name"
                                                class="alert alert-danger"
                                            >
                                                {{ errors.name }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold"
                                                    >Email Address</label
                                                >
                                                <input
                                                    class="form-control"
                                                    v-model="form.email"
                                                    :class="{
                                                        'is-invalid':
                                                            errors.email,
                                                    }"
                                                    type="email"
                                                    placeholder="Email Address"
                                                    required
                                                    autocomplete="email"
                                                />
                                            </div>
                                            <div
                                                v-if="errors.email"
                                                class="alert alert-danger"
                                            >
                                                {{ errors.email }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold"
                                                    >Password</label
                                                >
                                                <input
                                                    class="form-control"
                                                    v-model="form.password"
                                                    :class="{
                                                        'is-invalid':
                                                            errors.password,
                                                    }"
                                                    type="password"
                                                    placeholder="Password"
                                                    autocomplete="old-password"
                                                />
                                            </div>
                                            <div
                                                v-if="errors.password"
                                                class="alert alert-danger"
                                            >
                                                {{ errors.password }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold"
                                                    >Password
                                                    Confirmation</label
                                                >
                                                <input
                                                    class="form-control"
                                                    v-model="
                                                        form.password_confirmation
                                                    "
                                                    type="password"
                                                    placeholder="Password Confirmation"
                                                    autocomplete="old-password"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="fw-bold"
                                                    >Roles</label
                                                >
                                                <br />
                                                <div
                                                    class="form-check form-check-inline badge badge-primary px-2"
                                                    v-for="(
                                                        role, index
                                                    ) in roles"
                                                    :key="index"
                                                >
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        v-model="form.roles"
                                                        :value="role.name"
                                                        :id="`check-${role.id}`"
                                                    />
                                                    <label
                                                        class="form-check-label"
                                                        :for="`check-${role.id}`"
                                                        >{{ role.name }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                v-if="errors.roles"
                                                class="alert alert-danger"
                                            >
                                                {{ errors.roles }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                            >
                                                UPDATE
                                            </button>
                                            <button
                                                class="btn btn-warning shadow-sm rounded-sm ms-3"
                                                type="reset"
                                            >
                                                RESET
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
import { Head, Link } from "@inertiajs/inertia-vue3";
import Swal from "sweetalert2";
import { reactive } from "vue";
import LayoutApp from "../../../Layouts/App.vue";

export default {
    name: "EditUserPage",

    layout: LayoutApp,

    props: {
        errors: Object,
        user: Object,
        roles: Array,
    },

    components: {
        Head,
        Link,
    },

    setup(props) {
        const form = reactive({
            name: props.user.name,
            email: props.user.email,
            password: "",
            password_confirmation: "",
            roles: props.user.roles.map((obj) => obj.name),
        });

        const submit = (id) => {
            Inertia.put(
                `/apps/users/${props.user.id}`,
                {
                    name: form.name,
                    email: form.email,
                    password: form.password,
                    password_confirmation: form.password_confirmation,
                    roles: form.roles,
                },
                {
                    onSuccess: () => {
                        Swal.fire({
                            title: "Success!",
                            text: "User updated successfully.",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                    },
                }
            );
        };

        return {
            form,
            submit,
        };
    },
};
</script>
