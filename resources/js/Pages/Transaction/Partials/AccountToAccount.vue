<script setup>
import InputError from "@/Components/InputError.vue";
import InputSelect from "@/Components/InputSelect.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import {useForm} from "@inertiajs/vue3";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import Modal from "@/Components/Modal.vue";

defineProps({
    tradingUsers: Object
})

const confirmationModal = ref(false);

const form = useForm({
    transfer_type: 'ATA',
    account_no_1: '',
    account_no_2: '',
    amount: '',
});

const submit = () => {
    form.post(route('account_to_account'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const openConfirmation = () => {
    confirmationModal.value = true
}

const closeModal = () => {
    confirmationModal.value = false
}

</script>

<template>
    <form @submit.prevent="submit">
        <div class="grid gap-6 my-6 md:grid-cols-1">
            <div class="space-y-2">
                <Label for="account_no_1" :value="$t('public.Trading Account To Transfer')" />
                <InputSelect class="w-full" id="account_no_1" v-model="form.account_no_1" :placeholder="$t('public.Select Trading Account No')" >
                    <option v-for="paymentAccount in tradingUsers" :value="paymentAccount.meta_login" :key="paymentAccount.id">{{ paymentAccount.meta_login }} ( $ {{ parseFloat(paymentAccount.balance - paymentAccount.credit).toFixed(2) }} )</option>
                </InputSelect>
                <InputError :message="form.errors.account_no_1"/>
            </div>
            <div class="space-y-2">
                <Label for="account_no_2" :value="$t('public.Trading Account To Receive')" />
                <InputSelect class="w-full" id="account_no_2" v-model="form.account_no_2" :placeholder="$t('public.Select Trading Account No')" >
                    <option v-for="paymentAccount in tradingUsers" :value="paymentAccount.meta_login" :key="paymentAccount.id">{{ paymentAccount.meta_login }} ( $ {{ parseFloat(paymentAccount.balance - paymentAccount.credit).toFixed(2) }} )</option>
                </InputSelect>
                <InputError :message="form.errors.account_no_2"/>
            </div>
            <div class="space-y-2">
                <Label for="amount" :value="$t('public.Amount')+' ($)'" />
                <Input id="amount" type="number" step=".01" class="block w-full px-4" placeholder="0.00" v-model="form.amount" />
                <InputError :message="form.errors.amount"/>
            </div>
        </div>
        <div class="flex justify-end mt-6">
            <Button
                type="button"
                @click="openConfirmation"
            >
                {{ $t('public.Submit') }}
            </Button>
        </div>
    </form>

    <Modal :show="confirmationModal" @close="closeModal" max-width="lg">
        <div class="p-6">
            <h2
                class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100"
            >
                {{ $t('public.Warning') }}
            </h2>
            <hr>
            <div class="text-gray-400 dark:text-white my-5">
                {{ $t('public.account_transfer_fund_confirmation') }}
            </div>
            <div class="mt-6 flex justify-end">
                <Button variant="secondary" @click="closeModal">
                    {{ $t('public.Cancel') }}
                </Button>

                <Button
                    variant="primary"
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="submit"
                >
                    {{ $t('public.Confirm') }}
                </Button>
            </div>
        </div>
    </Modal>
</template>
