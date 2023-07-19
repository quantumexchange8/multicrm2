<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
// import RebateAllocationchild from './Partials/RebateChilder.vue'

// defineProps({
//     account: Object,
//     children: Object,
// });

</script>

<script>
    export default {
        props: {
            account: Object, // Assuming 'account' is passed as a prop
        },
        methods: {
            formatDate(date) {
            return new Date(date).toISOString().slice(0, 10);
            },
        },
    };
</script>

<template>
    <AuthenticatedLayout title="Rebate Allocation">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Rebate Allocation
                </h2>
            </div>
        </template>

        <div class="col-sm-7 col-md-6 col-lg-4 box-bg-color text-left text-white box-padding rounded-l-2xl">
            
            <p class="text-black dark:text-white">IB NAME:
                <span class="text-black dark:text-white">{{ account.of_user.first_name }}</span>
            </p>
            <p class="text-black dark:text-white">IB NUMBER:
                <span class="text-black dark:text-white">{{ account.of_user.ib_id }}</span>
            </p>
            <p class="text-black dark:text-white">account type:
                <span class="text-black dark:text-white">{{ account.account_type.name }}</span>
            </p>
            <p class="text-black dark:text-white">SINCE DATE:
                <span class="text-black dark:text-white">{{ formatDate(account.of_user.created_at)}}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT IB:
                <span class="text-black dark:text-white">{{ account.of_user.direct_ib }}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT CLIENTS:
                <span class="text-black dark:text-white">{{ account.of_user.direct_client }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP IB:
                <span class="text-black dark:text-white">{{ account.of_user.total_ib }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP CLIENTS:
                <span class="text-black dark:text-white">{{ account.of_user.total_client }}</span>
            </p>
        </div>

        
        <div v-for="accounts in account.symbol_groups">
            <p>
                {{ accounts.name }} (USD)/LOT =
                <span>
                    {{ $accounts.amount }}
                </span>
            </p>
        </div>

        <!-- <div v-for="child in children">
            {{ child }}
        </div> -->

    </AuthenticatedLayout>
</template>