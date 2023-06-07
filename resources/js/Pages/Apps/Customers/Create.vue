<template>
    <Head>
        <title>Add New Customer - Aplikasi Inventory</title>
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
                                    ><i class="fa fa-user-circle"></i> ADD NEW
                                    CUSTOMER</span
                                >
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
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
                                                    >No. Telp</label
                                                >
                                                <input
                                                    class="form-control"
                                                    v-model="form.no_telp"
                                                    :class="{
                                                        'is-invalid':
                                                            errors.no_telp,
                                                    }"
                                                    type="number"
                                                    placeholder="No. Telp"
                                                />
                                            </div>
                                            <div
                                                v-if="errors.no_telp"
                                                class="alert alert-danger"
                                            >
                                                {{ errors.no_telp }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Address</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form.address"
                                            :class="{
                                                'is-invalid': errors.address,
                                            }"
                                            type="text"
                                            rows="4"
                                            placeholder="Address"
                                        ></textarea>
                                    </div>
                                    <div
                                        v-if="errors.address"
                                        class="alert alert-danger"
                                    >
                                        {{ errors.address }}
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

<script lang="ts">
import { defineComponent } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head } from "@inertiajs/inertia-vue3";
import Swal from "sweetalert2";
import { reactive } from "vue";
import LayoutApp from "../../../Layouts/App.vue";

export default defineComponent({
    name: "CreateCustomerPage",

    layout: LayoutApp,

    props: {
        errors: Object,
    },

    components: {
        Head,
    },

    setup() {
        const form = reactive({
            name: "",
            no_telp: "",
            address: "",
        });

        const submit = () => {
            Inertia.post(
                "/apps/customers",
                {
                    name: form.name,
                    no_telp: form.no_telp,
                    address: form.address,
                },
                {
                    onSuccess: () => {
                        Swal.fire({
                            title: "Success!",
                            text: "Customer has been added.",
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
});
</script>
