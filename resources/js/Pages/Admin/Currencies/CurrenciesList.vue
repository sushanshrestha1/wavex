<script setup>
import { usePage, Link, useForm, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import {ref, computed, watch} from "vue";

defineProps({
    currencies: {
        type:Object,
        required:true
    }
});

let search = ref(usePage().props.search),
 pageNumber =ref(1);


 let currenciesUrl = computed(()=> {
    let url = new URL(route("currencies.index"));
    url.searchParams.append("page", pageNumber.value);
    // if(search.value){
    //     url.searchParams.append("search",search.value);
    // }
    return url;
});

watch(()=> currenciesUrl.value,
    (updatedcurrenciesUrl) => {
        router.visit(updatedcurrenciesUrl,{
            preserveScroll: true,
            preserveState: true,
            replace:true,
        });
    }
);

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split("=")[1];
}


const deleteForm=useForm({});
const deletecurrency = (currencyId) =>{
    if (confirm('Are you sure you want to delete?')) {
        deleteForm.delete(route('exchangerate.destroy',currencyId));
    }
}
</script>

<template>
    <div class="bg-gray-100 py-10">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Currencies
                        </h1>

                    </div>

                    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <Link :href="route('currencies.create')"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        Add Currency
                        </Link>

                    </div>
                </div>

                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                ID</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                CODE</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                Name</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                Symbol</th>
                                            
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="currency in currencies.data" :key="currency.id">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ currency.id }}</td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ currency.code }}</td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ currency.name }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                currency.symbol }}</td>
                                           
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-left text-sm font-medium sm:pr-6">
                                                <Link :href="route('currencies.edit', currency.id)"
                                                    class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                                    <Link 
                                                        @click="deletecurrency(currency.id)"
                                                        class="ml-2 text-indigo-600 hover:text-indigo-900">
                                                        Delete
                                                    </Link>

                                            </td>
                                        </tr>
                                        <!-- Handle empty state if no data is present -->

                                    </tbody>
                                </table>
                            </div>
                            <Pagination :data="currencies" :updatedPageNumber="updatedPageNumber" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
