<script setup>
import Button from "@/Components/Button.vue";
import InputSelect from "@/Components/InputSelect.vue";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import {onMounted, ref} from "vue";
import { useForm } from 'laravel-precognition-vue-inertia';
import QrcodeVue from 'qrcode.vue';
import {DuplicateIcon} from "@heroicons/vue/outline";

const submitWithdrawal = ref(false)
const cryptoWallets = ref([]);
const paymentAccounts = ref([]);
const isLoading = ref(false);
const error = ref(null);

onMounted(async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/get_trading_account');

        const withdrawalData = response.data;

        paymentAccounts.value = withdrawalData.paymentAccounts;
        cryptoWallets.value = withdrawalData.cryptoWallets;

    } catch (error) {
        error.value = 'Failed to fetch data';
    } finally {
        isLoading.value = false;
    }
});


const channels = [
    { id: 'channel', src: '/assets/finance/bank.png', value: 'bank', name: 'Bank Account' },
    { id: 'channel', src: '/assets/finance/cryptocurrency.png', value: 'crypto', name: 'Cryptocurrency' },
];


const form = useForm('post', route('payment.requestWithdrawal'), {
    account_no: '',
    account_type: '',
    amount: '',
    channel: '',
});

const openWithdrawalModal = () => {
    submitWithdrawal.value = true
}

const submit = () => {
    form.submit({
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    })
}

const closeModal = () => {
    submitWithdrawal.value = false
    form.reset()
}
</script>

<template>
    <Button class="w-full justify-center" variant="danger-opacity" @click="openWithdrawalModal">
        Withdrawal
    </Button>

    <Modal :show="submitWithdrawal" @close="closeModal">

        <form class="p-6">
            <div v-if="!form.channel">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Withdrawal Method</h2>
                <hr>
                <p class="my-4 text-sm text-gray-600 dark:text-gray-400">
                    <span class="text-red-500">*</span> Select a withdrawal method
                </p>
                <ul class="my-4 grid w-full gap-6" :class="{'md:grid-cols-3': channels.length >= 3, 'md:grid-cols-2': channels.length === 2}">
                    <li v-for="(channel, index) in channels" :key="index">
                        <input
                            type="radio"
                            :id="`channel_${index}`"
                            name="channel"
                            :value="channel.value"
                            class="hidden peer"
                            v-model="form.channel"
                            :required="index === 1"
                        >
                        <label
                            :for="`channel_${index}`"
                            class="inline-flex items-center justify-center w-full p-4 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-[#007BFF] dark:peer-checked:bg-[#007BFF] peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-transparent dark:shadow-lg dark:hover:shadow-blue-600"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <img class="object-cover" :src="channel.src" alt="account_platform">
                                <p class="dark:text-white">{{ channel.name }}</p>
                            </div>
                        </label>
                    </li>
                </ul>
                <InputError :message="form.errors.channel"/>

            </div>
            <!-- Bank -->
            <div v-if="form.channel === 'bank'">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Withdrawal - Bank Account</h2>
                <hr>
                <div class="grid grid-cols-1 my-8 gap-2 w-full text-center">
                    <p class="text-base dark:text-gray-400">Cash Wallet Balance</p>
                    <p class="text-4xl font-bold dark:text-white">$ {{ $page.props.auth.user.cash_wallet }}</p>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <Input id="account_type" type="text" class="block w-full px-4" placeholder="Bank" v-model="form.account_type" @change="form.validate('account_type')" autocomplete="off" />
                        <InputError :message="form.errors.account_type"/>

                    </div>
                    <div class="space-y-2">
                        <Input id="account_no" type="text" class="block w-full px-4" placeholder="Bank Account" v-model="form.account_no" @change="form.validate('account_no')" autocomplete="off" />
                        <InputError :message="form.errors.account_no"/>

                    </div>
                    <div class="space-y-2">
                        <Input id="amount" type="number" min="30" class="block w-full px-4" placeholder="Amount" v-model="form.amount" @change="form.validate('amount')" autocomplete="off" />
                        <InputError :message="form.errors.amount"/>

                    </div>
                </div>

            </div>

            <!-- Crypto -->
            <div v-if="form.channel === 'crypto'">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Withdrawal - Cryptocurrency</h2>
                <hr>
                <div class="grid grid-cols-1 my-8 gap-2 w-full text-center">
                    <p class="text-base dark:text-gray-400">Cash Wallet Balance</p>
                    <p class="text-4xl font-bold dark:text-white">$ {{ $page.props.auth.user.cash_wallet }}</p>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <Input id="account_type" type="text" class="block w-full px-4" placeholder="Cryptocurrency" v-model="form.account_type" @change="form.validate('account_type')" autocomplete="off" />
                        <InputError :message="form.errors.account_type"/>

                    </div>
                    <div class="space-y-2">
                        <Input id="account_no" type="text" class="block w-full px-4" placeholder="Cryptocurrency Address" v-model="form.account_no" @change="form.validate('account_no')" autocomplete="off" />
                        <InputError :message="form.errors.account_no"/>

                    </div>
                    <div class="space-y-2">
                        <Input id="amount" type="number" min="30" class="block w-full px-4" placeholder="Amount" v-model="form.amount" @change="form.validate('amount')" autocomplete="off" />
                        <InputError :message="form.errors.amount"/>

                    </div>
                </div>
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
                    @click="submit"
                >
                    Submit
                </Button>
            </div>
        </form>
    </Modal>
</template>
