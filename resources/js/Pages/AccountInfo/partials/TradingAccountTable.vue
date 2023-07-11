<script setup>
import {DepositIcon, ResetIcon, SettingIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";

defineProps({
    tradingAccounts: Object,
});
</script>
<template>
    <div class="relative overflow-x-auto sm:rounded-lg mt-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-white text-center">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Account No.
                </th>
                <th scope="col" class="px-6 py-3">
                    Account Type
                </th>
                <th scope="col" class="px-6 py-3">
                    Balance
                </th>
                <th scope="col" class="px-6 py-3">
                    Equity
                </th>
                <th scope="col" class="px-6 py-3">
                    Credit
                </th>
                <th scope="col" class="px-6 py-3">
                    Leverage
                </th>
                <th scope="col" class="px-6 py-3">
                    Actions
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(tradingAccount, index) in tradingAccounts" class="bg-white odd:dark:bg-transparent even:dark:bg-dark-eval-0 text-xs font-thin text-gray-900 dark:text-white text-center">
                <th scope="row" class="px-6 py-4 font-thin">
                    {{ tradingAccount.meta_login }}
                </th>
                <td class="px-6 py-4">
                    {{ tradingAccount.account_type.name }}
                </td>
                <td class="px-6 py-4">
                    {{ tradingAccount.balance }}
                </td>
                <td class="px-6 py-4">
                    {{ tradingAccount.equity }}
                </td>
                <td class="px-6 py-4">
                    {{ tradingAccount.credit }}
                </td>
                <td class="px-6 py-4">
                    1:{{ tradingAccount.margin_leverage }}
                </td>
                <td class="px-6 py-4 flex justify-center gap-2">
                    <Button class="justify-center pl-4 pr-3 pt-1 rounded-full w-8 h-8 focus:outline-none" variant="primary-opacity" @click="openDepositModal">
                        <DepositIcon aria-hidden="true" class="w-6 h-6 absolute" />
                        <span class="sr-only">Deposit</span>
                    </Button>
                    <Button class="justify-center px-4 pt-2 rounded-full w-8 h-8 focus:outline-none" variant="primary-opacity" @click="openResetModal">
                        <ResetIcon aria-hidden="true" class="w-6 h-6 absolute" />
                        <span class="sr-only">Reset</span>
                    </Button>
                    <Button class="justify-center px-4 pt-2 rounded-full w-8 h-8 focus:outline-none" variant="primary-opacity" @click="openSettingModal">
                        <SettingIcon aria-hidden="true" class="w-6 h-6 absolute" />
                        <span class="sr-only">Setting</span>
                    </Button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</template>
