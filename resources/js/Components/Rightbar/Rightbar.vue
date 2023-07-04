<script setup>
import RightbarTitle from "@/Components/Rightbar/RightbarTitle.vue";
import RightbarContent from "@/Components/Rightbar/RightbarContent.vue";
import DepositForm from "@/Components/Rightbar/DepositForm.vue";
</script>

<template>
    <aside class="w-full md:w-auto space-y-4">
        <RightbarTitle title="Personal Finances">
            <RightbarContent title="Cash Wallet" amount="99,999" />
            <RightbarContent title="Reward Point (RP)" amount="999,999" />

            <DepositForm />

        </RightbarTitle>

        <RightbarTitle title="Monthly Performance">
            <p class="font-normal text-gray-700 dark:text-gray-400">This is the content of the second right content card.</p>
        </RightbarTitle>
    </aside>
</template>
