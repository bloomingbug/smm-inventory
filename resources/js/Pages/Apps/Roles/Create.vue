<template>
    <Head>
        <title>Add New Role - Aplikasi Inventory</title>
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
                                    ><i class="fa fa-shield-alt"></i> ADD
                                    ROLE</span
                                >
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="fw-bold">Role Name</label>
                                        <input
                                            class="form-control"
                                            v-model="form.name"
                                            :class="{
                                                'is-invalid': errors.name,
                                            }"
                                            type="text"
                                            placeholder="Role Name"
                                            required
                                            autofocus
                                        />

                                        <div
                                            v-if="errors.name"
                                            class="alert alert-danger"
                                        >
                                            {{ errors.name }}
                                        </div>
                                    </div>

                                    <hr />
                                    <div class="mb-3">
                                        <label class="fw-bold"
                                            >Permissions</label
                                        >
                                        <br />
                                        <div
                                            class="form-check form-check-inline badge badge-success px-2"
                                            v-for="(
                                                permission, index
                                            ) in permissions"
                                            :key="index"
                                        >
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                v-model="form.permissions"
                                                :value="permission.name"
                                                :id="`check-${permission.id}`"
                                            />
                                            <label
                                                class="form-check-label"
                                                :for="`check-${permission.id}`"
                                                >{{ permission.name }}</label
                                            >
                                        </div>
                                        <div
                                            v-if="errors.permissions"
                                            class="alert alert-danger mt-2"
                                        >
                                            {{ errors.permissions }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                            >
                                                SAVE
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
import { Head } from "@inertiajs/inertia-vue3";
import LayoutApp from "../../../Layouts/App.vue";
import { reactive } from "vue";
import { Inertia } from "@inertiajs/inertia";
import Swal from "sweetalert2";

export default {
    name: "CreateRolesPage",

    layout: LayoutApp,

    components: {
        Head,
    },

    props: {
        permissions: Array,
        errors: Object,
    },

    setup() {
        const form = reactive({
            name: "",
            permissions: [],
        });

        const submit = () => {
            Inertia.post(
                "/apps/roles",
                {
                    name: form.name,
                    permissions: form.permissions,
                },
                {
                    onSuccess: () => {
                        Swal.fire({
                            title: "Success!",
                            text: "Role saved successfully.",
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
