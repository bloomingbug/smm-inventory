<template>
    <Head>
        <title>Permissions - Aplikasi Inventory</title>
    </Head>

    <main class="c-main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div
                        class="card border-0 rounded-3 shadow border-top-purple"
                    >
                        <div class="card-header">
                            <span class="font-weight-bold"
                                ><i class="fa fa-key"></i> PERMISSIONS</span
                            >
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="handleSearch">
                                <div class="input-group mb-3">
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="search"
                                        placeholder="search by permission name..."
                                    />
                                    <button
                                        class="btn btn-primary input-group-text"
                                        type="submit"
                                        @click="handleSearch"
                                    >
                                        <i class="fa fa-search me-2"></i>
                                        SEARCH
                                    </button>
                                </div>
                            </form>
                            <table
                                class="table table-striped table-bordered table-hover"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Permission Name</th>
                                    </tr>
                                </thead>
                                <tbody class="fade-in">
                                    <tr
                                        v-for="(
                                            permission, index
                                        ) in permissions.data"
                                        :key="index"
                                    >
                                        <td>{{ permission.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination
                                :links="permissions.links"
                                align="end"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import Pagination from "../../../Components/Pagination.vue";
import LayoutApp from "../../../Layouts/App.vue";
import { Inertia } from "@inertiajs/inertia";

export default {
    name: "PermissionPage",

    layout: LayoutApp,

    components: {
        Head,
        Link,
        Pagination,
    },

    props: {
        permissions: Object,
    },

    setup() {
        const search = ref(
            "" || new URL(document.location).searchParams.get("q")
        );

        const handleSearch = () => {
            Inertia.get("/apps/permissions", {
                q: search.value,
            });
        };

        return {
            search,
            handleSearch,
        };
    },
};
</script>
