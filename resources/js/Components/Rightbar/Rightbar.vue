<script setup>
import RightbarTitle from "@/Components/Rightbar/RightbarTitle.vue";
import RightbarContent from "@/Components/Rightbar/RightbarContent.vue";
import DepositForm from "@/Components/Rightbar/DepositForm.vue";
import WithdrawalForm from "@/Components/Rightbar/WithdrawalForm.vue";
import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";
import Button from "@/Components/Button.vue";
import { usePermission } from '@/Composables/permissions.js'

const page = usePage()
const monthlyDeposit = page.props.monthlyDeposit;
const monthlyWithdrawal = page.props.monthlyWithdrawal;
const IBAccountTypes = page.props.IBAccountTypes;
const user = computed(() => page.props.auth.user)
const { hasRole } = usePermission();

function formatAmount(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

</script>

<template>
    <aside class="w-full md:w-auto space-y-4">
        <RightbarTitle title="Personal Finances">
            <RightbarContent title="Cash Wallet ($)" :amount="formatAmount(user.cash_wallet)" />
            <RightbarContent v-if="hasRole('ib')" title="Rebate Earned ($)" :amount="formatAmount(IBAccountTypes[0].rebate_wallet)" />
<!--            <RightbarContent title="Reward Point (RP)" amount="999,999" />-->

            <div class="grid grid-cols-2 gap-4 mt-8">
                <DepositForm />
                <WithdrawalForm />
            </div>
            <div v-if="hasRole('ib')">
                <Button variant="secondary-opacity" class="w-full justify-center mt-4">Apply Rebate</Button>
            </div>
            <Button :href="route('transaction')" variant="primary-opacity" class="w-full bg-transparent border border-blue-800 justify-center mt-4">Internal Transfer</Button>

        </RightbarTitle>

        <RightbarTitle title="Monthly Performance">
            <RightbarContent title="Current Month Deposit ($)" :amount="monthlyDeposit" />
            <RightbarContent title="Current Month Withdrawal ($)" :amount="monthlyWithdrawal" />
        </RightbarTitle>
    </aside>
</template>
