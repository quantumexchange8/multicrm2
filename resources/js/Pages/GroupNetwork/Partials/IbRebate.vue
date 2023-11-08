<script setup>

const props = defineProps({
    ib: Object,
});

function formatDate(date) {
    const formattedDate = new Date(date).toISOString().slice(0, 10);
    return formattedDate.replace(/-/g, '/');
}

</script>

<template>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="w-full bg-white rounded-lg shadow dark:bg-dark-eval-1 p-6">
                <div class="flex flex-col items-center mb-4">
                    <img
                        class="h-12 w-12 rounded-full"
                        :src="$page.props.auth.user.picture ? $page.props.auth.user.picture : 'https://img.freepik.com/free-icon/user_318-159711.jpg'"
                        alt="ProfilePic"
                    >
                    <h5 class="my-1 text-xl font-medium text-gray-900 dark:text-white">{{ ib.of_user.first_name }}</h5>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ ib.of_user.ib_id }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center md:text-left">
                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Account Type') }}:</div>
                    <div class="text-black dark:text-white">{{ ib.account_type.name }}</div>

                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Since Date') }} </div>
                    <div class="text-black dark:text-white">{{ formatDate(ib.created_at) }}</div>

                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Direct IB') }} </div>
                    <div class="text-black dark:text-white">{{ ib.of_user.direct_ib }}</div>

                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Direct Clients') }} </div>
                    <div class="text-black dark:text-white">{{ ib.of_user.direct_client }}</div>

                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Total Group IB') }} </div>
                    <div class="text-black dark:text-white">{{ ib.of_user.total_ib }}</div>

                    <div class="text-black dark:text-dark-eval-3">{{ $t('public.Total Group Clients') }} </div>
                    <div class="text-black dark:text-white">{{ ib.of_user.total_client }}</div>
                </div>
            </div>
            <div class="w-full bg-white rounded-lg shadow dark:bg-dark-eval-1 p-6">
                <div class="flex flex-col text-center md:text-left">
                    <h5 class="mb-6 text-xl font-medium text-gray-900 dark:text-white">{{ $t('public.My Rebate Info') }}</h5>
                    <div
                        v-for="account in ib.symbol_groups"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"
                    >
                        <div class="dark:text-dark-eval-3 uppercase">
                            {{ account.symbol_group.name }} (USD) / LOT
                        </div>
                        <div class="text-center">
                            {{ account.amount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>
