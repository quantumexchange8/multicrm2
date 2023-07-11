<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from "@/Components/Button.vue";
import {PlusCircleIcon} from "@heroicons/vue/solid";
import TradingAccountTable from "@/Pages/AccountInfo/partials/TradingAccountTable.vue";
import {nextTick, ref} from "vue";
import InputSelect from "@/Components/InputSelect.vue";
import InputError from "@/Components/InputError.vue";
import Input from "@/Components/Input.vue";
import Label from "@/Components/Label.vue";
import Modal from "@/Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";
import ToastList from "@/Components/ToastList.vue";

defineProps({
    tradingAccounts: Object,
    accountTypes: Object,
    leverages: Object,
});

const platforms = [
    { id: 'account_platform_2', src: '/assets/platform/icon/metatrader5.png', value: 2 },
    { id: 'account_platform_3', src: '/assets/platform/icon/ctrader.png', value: 3 },
    { id: 'account_platform_4', src: '/assets/platform/icon/match_trade.png', value: 4 },
];

const acc_types = [
    // { id: 'account_type_2', src: '/assets/account_type/ecn.png', value: 2 },
    { id: 'account_type_3', src: '/assets/account_type/standard.png', value: 1 },
    // { id: 'account_type_4', src: '/assets/account_type/standard_islam.png', value: 4 },
];

const addingTradingAccount = ref(false)
const passwordInput = ref(null)

const form = useForm({
    group: '',
    // account_platform: '',
    leverage: '',
    currency: '',
})

const addTradingAccount = () => {
    addingTradingAccount.value = true
}

const addNewAccount = () => {
    form.post(route('add_trading_account'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    })
}

const closeModal = () => {
    addingTradingAccount.value = false

    form.reset()
}
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <ToastList />

        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Account Info
                </h2>
                <Button
                    external
                    variant="primary-opacity"
                    target="_blank"
                    class="items-center gap-2 max-w-xs"
                    v-slot="{ iconSizeClasses }"
                    @click="addTradingAccount"
                >
                    <PlusCircleIcon aria-hidden="true" :class="iconSizeClasses" />
                    <span>Add Trading Account</span>
                </Button>

                <Modal :show="addingTradingAccount" @close="closeModal">
                    <div class="p-6">
                        <h2
                            class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100"
                        >
                            Add Trading Account
                        </h2>
                        <hr>
                        <div class="mt-6 space-y-2">
                            <Label for="group" value="Select Account Type" />
                            <ul class="grid w-full gap-6 md:grid-cols-3">
                                <li v-for="(type, index) in acc_types" :key="index">
                                    <input type="radio" :id="type.id" name="group" :value="accountTypes.id" class="hidden peer" v-model="form.group" :required="type.required">
                                    <label :for="type.id" class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-[#007BFF] dark:peer-checked:bg-[#007BFF] peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-transparent dark:shadow-lg dark:hover:shadow-blue-600">
                                        <div class="block">
                                            <img class="object-cover" :src="type.src" alt="account_type">
                                        </div>
                                    </label>
                                </li>
                            </ul>
                            <InputError :message="form.errors.account_type"/>
                        </div>

<!--                        <div class="mt-6 space-y-2">-->
<!--                            <Label for="account_platform" value="Select Account Platform" />-->
<!--                            <ul class="grid w-full gap-6 md:grid-cols-3">-->
<!--                                <li v-for="(platform, index) in platforms" :key="index">-->
<!--                                    <input type="radio" :id="platform.id" name="account_platform" :value="platform.value" class="hidden peer" v-model="form.account_platform" :required="platform.required">-->
<!--                                    <label :for="platform.id" class="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-[#007BFF] dark:peer-checked:bg-[#007BFF] peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-transparent dark:shadow-lg dark:hover:shadow-blue-600">-->
<!--                                        <div class="block">-->
<!--                                            <img class="object-cover" :src="platform.src" alt="account_platform">-->
<!--                                        </div>-->
<!--                                    </label>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                            <InputError :message="form.errors.account_platform"/>-->
<!--                        </div>-->

                        <div class="mt-6 space-y-2">
                            <Label for="leverage" value="Trading Account Leverage" />
                            <InputSelect v-model="form.leverage" class="block w-full text-sm" placeholder="Choose Leverages">
                                <option v-for="leverage in leverages" :value="leverage.value" :key="leverage.id">{{ leverage.leverage }}</option>
                            </InputSelect>
                            <InputError :message="form.errors.leverage"/>
                        </div>

                        <div class="mt-6 space-y-2">
                            <Label for="currency" value="Currency" />
                            <InputSelect v-model="form.currency" class="block w-full text-sm" placeholder="Choose Currency">
                                <option value="USD">USD</option>
                            </InputSelect>
                            <InputError :message="form.errors.leverage"/>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <Button variant="secondary" @click="closeModal">
                                Cancel
                            </Button>

                            <Button
                                variant="primary"
                                class="ml-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="addNewAccount"
                            >
                                Process
                            </Button>
                        </div>
                    </div>
                </Modal>
            </div>
        </template>

        <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
            Ctrader Table
            <TradingAccountTable :tradingAccounts="tradingAccounts" />
        </div>

    </AuthenticatedLayout>
</template>
