<template>
    <Head>
        <title>User - Aplikasi Inventory</title>
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
                                ><i class="fa fa-users"></i> Users</span
                            >
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="handleSearch">
                                <div class="input-group mb-3">
                                    <Link
                                        href="/apps/users/create"
                                        v-if="
                                            hasAnyPermission(['users.create'])
                                        "
                                        class="btn btn-primary input-group-text"
                                    >
                                        <i class="fa fa-plus-circle me-2"></i>
                                        NEW</Link
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="search by user name..."
                                        v-model="search"
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
                                        <th scope="col">Full Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Roles</th>
                                        <th scope="col" style="width: 20%">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="fade-in">
                                    <tr
                                        v-for="(user, index) in users.data"
                                        :key="index"
                                    >
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span
                                                v-for="(
                                                    role, index
                                                ) in user.roles"
                                                :key="index"
                                                class="badge badge-primary shadow border-0 ms-2 mb-2"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <Link
                                                :href="`/apps/users/${user.id}/edit`"
                                                v-if="
                                                    hasAnyPermission([
                                                        'users.edit',
                                                    ])
                                                "
                                                class="btn btn-success btn-sm me-2"
                                                ><i
                                                    class="fa fa-pencil-alt me-1"
                                                ></i>
                                                EDIT</Link
                                            >
                                            <button
                                                v-if="
                                                    hasAnyPermission([
                                                        'users.delete',
                                                    ])
                                                "
                                                class="btn btn-danger btn-sm"
                                                @click.prevent="
                                                    destroy(user.id)
                                                "
                                            >
                                                <i class="fa fa-trash"></i>
                                                DELETE
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="users.links" align="end" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head, Link } from "@inertiajs/inertia-vue3";
import Swal from "sweetalert2";
import LayoutApp from "../../../Layouts/App.vue";
import Pagination from "../../../Components/Pagination.vue";

export default {
    name: "UserPage",

    layout: LayoutApp,

    props: {
        users: Object,
    },

    components: {
        Head,
        Link,
        Pagination,
    },

    setup() {
        const search = ref(
            "" || new URL(document.location).searchParams.get("q")
        );

        const handleSearch = () => {
            Inertia.get("/apps/users", {
                q: search.value,
            });
        };

        const destroy = (id) => {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able revert this!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#383838",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    Inertia.delete(`/apps/users/${id}`, {
                        onSuccess: () => {
                            Swal.fire({
                                title: "Deleted!",
                                text: "User deleted successfully.",
                                icon: "success",
                                showConfirmButton: false,
                                timer: 2000,
                            });
                        },
                    });
                }
            });
        };

        return {
            search,
            handleSearch,
            destroy,
        };
    },
};
</script>
