<template>
    <Head>
        <title>Products - Aplikasi Kasir</title>
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
                                ><i class="fa fa-shopping-bag"></i>
                                PRODUCTS</span
                            >
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="handleSearch">
                                <div class="input-group mb-3">
                                    <Link
                                        href="/apps/products/create"
                                        v-if="
                                            hasAnyPermission([
                                                'products.create',
                                            ])
                                        "
                                        class="btn btn-primary input-group-text"
                                    >
                                        <i class="fa fa-plus-circle me-2"></i>
                                        NEW</Link
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="search by product title..."
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
                                        <th scope="col">Barcode</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Buy Price</th>
                                        <th scope="col">Sell Price</th>
                                        <th scope="col">Stock</th>
                                        <th scope="col" style="width: 20%">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="fade-in">
                                    <tr
                                        v-for="(
                                            product, index
                                        ) in products.data"
                                        :key="index"
                                    >
                                        <td class="text-center">
                                            <Barcode
                                                :value="product.barcode"
                                                :format="'CODE39'"
                                                :lineColor="'#000'"
                                                :width="1"
                                                :height="20"
                                            />
                                        </td>
                                        <td class="align-middle">
                                            {{ product.title }}
                                        </td>
                                        <td class="align-middle">
                                            Rp
                                            {{ formatPrice(product.buy_price) }}
                                        </td>
                                        <td class="align-middle">
                                            Rp
                                            {{
                                                formatPrice(product.sell_price)
                                            }}
                                        </td>
                                        <td class="align-middle">
                                            {{ product.stock }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <Link
                                                :href="`/apps/products/${product.id}/edit`"
                                                v-if="
                                                    hasAnyPermission([
                                                        'products.edit',
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
                                                        'products.delete',
                                                    ])
                                                "
                                                class="btn btn-danger btn-sm"
                                                @click="destroy(product.id)"
                                            >
                                                <i class="fa fa-trash"></i>
                                                DELETE
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :links="products.links" align="end" />
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
import { ref } from "vue";
import Pagination from "../../../Components/Pagination.vue";
import LayoutApp from "../../../Layouts/App.vue";
import Barcode from "../../../Components/Barcode.vue";

export default {
    name: "ProductsPage",

    layout: LayoutApp,

    props: {
        products: Object,
        errors: Object,
    },

    components: {
        Head,
        Link,
        Pagination,
        Barcode,
    },

    setup() {
        const search = ref(
            "" || new URL(document.location).searchParams.get("q")
        );

        const handleSearch = () => {
            Inertia.get("/apps/products", {
                q: search.value,
            });
        };

        const destroy = (id) => {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able revert it!",
                icon: "question",
                confirmButtonColor: "#d33",
                cancelButtonColor: "#383838",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    Inertia.delete(`/apps/products/${id}`, {
                        onSuccess: () => {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Product deleted successfully.",
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
