<script setup>
import RightbarTitle from "@/Components/Rightbar/RightbarTitle.vue";
import RightbarContent from "@/Components/Rightbar/RightbarContent.vue";
import DepositForm from "@/Components/Rightbar/DepositForm.vue";
import WithdrawalForm from "@/Components/Rightbar/WithdrawalForm.vue";
import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";

const page = usePage()
const monthlyDeposit = page.props.monthlyDeposit;
const user = computed(() => page.props.auth.user)

function formatAmount(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

</script>

<template>
    <aside class="w-full md:w-auto space-y-4">
        <RightbarTitle title="Personal Finances">
            <RightbarContent title="Cash Wallet ($)" :amount="formatAmount(user.cash_wallet)" />
<!--            <RightbarContent title="Reward Point (RP)" amount="999,999" />-->

            <div class="flex justify-between gap-4 mt-8">
                <DepositForm />
                <WithdrawalForm />
            </div>

        </RightbarTitle>

        <RightbarTitle title="Monthly Performance">
            <RightbarContent title="Current Month Deposit ($)" :amount="monthlyDeposit" />
            <RightbarContent title="Current Month Withdrawal ($)" :amount="user.total_withdrawal" />
        </RightbarTitle>
    </aside>
</template>
