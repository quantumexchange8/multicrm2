<script setup>
import Paginator from "@/Components/Paginator.vue";

defineProps({
    payments: Object
})
</script>
<template>
    <div class="p-4 mt-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <div class="relative overflow-x-auto sm:rounded-lg mt-4">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-white text-center">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Date
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Deposit Method
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Account No.
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Amount
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Status
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(payment, index) in payments.data" class="bg-white odd:dark:bg-transparent even:dark:bg-dark-eval-0 text-xs font-thin text-gray-900 dark:text-white text-center">
                    <th scope="row" class="px-6 py-4 font-thin rounded-l-full">
                        {{ new Date(payment.created_at).toLocaleDateString('en-GB') }}
                    </th>
                    <td class="px-6 py-4">
                        {{ payment.gateway ?? payment.channel }}
                    </td>
                    <td class="px-6 py-4">
                        {{ payment.to }}
                    </td>
                    <td class="px-6 py-4">
                        {{ payment.amount }}
                    </td>
                    <td class="p-4 rounded-r-full">
                        <span :class="{
                            'bg-green-500 text-white test-xs font-medium mr-2 px-4 py-1 rounded dark:bg-[#013B20]': payment.status === 'Successful',
                            'bg-yellow-500 text-white test-xs font-medium mr-2 px-4 py-1 rounded dark:bg-[#573A15]': payment.status === 'Submitted',
                            'bg-red-500 text-white test-xs font-medium mr-2 px-5 py-1 rounded dark:bg-[#4C1310]': payment.status === 'Rejected'
                            }">
                            {{ payment.status }}
                        </span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginator -->
        <div class="mt-6 flex justify-end">
            <Paginator :links="payments.links" />
        </div>
    </div>
</template>
