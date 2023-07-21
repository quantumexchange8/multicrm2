<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import downlineHasDownline from './Partials/RebateChild.vue'
import Modal from "@/Components/Modal.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import Label from "@/Components/Label.vue";
import {ref} from "vue";

const submitAllocation = ref(false)

const buttonRef = ref(null);

</script>

<script>
    export default {
        props: {
            accounts: Object,
            childrens: Object,
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
                <span class="text-black dark:text-white">{{ accounts.of_user.first_name }}</span>
            </p>
            <p class="text-black dark:text-white">IB NUMBER:
                <span class="text-black dark:text-white">{{ accounts.of_user.ib_id }}</span>
            </p>
            <p class="text-black dark:text-white">account type:
                <span class="text-black dark:text-white">{{ accounts.account_type.name }}</span>
            </p>
            <p class="text-black dark:text-white">SINCE DATE:
                <span class="text-black dark:text-white">{{ formatDate(accounts.of_user.created_at)}}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT IB:
                <span class="text-black dark:text-white">{{ accounts.of_user.direct_ib }}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT CLIENTS:
                <span class="text-black dark:text-white">{{ accounts.of_user.direct_client }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP IB:
                <span class="text-black dark:text-white">{{ accounts.of_user.total_ib }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP CLIENTS:
                <span class="text-black dark:text-white">{{ accounts.of_user.total_client }}</span>
            </p>
        </div>
        
        <div class="mt-10">
            <div v-for="account in accounts.symbol_groups">
                <p>
                    {{ account.symbol_group.name }} (USD)/LOT =
                    <span>
                        {{ account.amount }}
                    </span>
                </p>
            </div>
        </div>

        <downlineHasDownline :childrens="childrens" :submitAllocation="submitAllocation" :accounts="accounts"/>
        
    </AuthenticatedLayout>
</template>