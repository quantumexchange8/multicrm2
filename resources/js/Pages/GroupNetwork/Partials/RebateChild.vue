<script setup>
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import {computed, ref} from "vue";
import {useForm} from '@inertiajs/vue3';
import Button from "@/Components/Button.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    children: Object,
    ib: Object,
});

const submitAllocation = ref(false);
const showForm = ref(false);
const childDetail = ref(null);
const children = props.children;
const ib = props.ib;
const errors = ref([]);
const groupRateItems = ref({});

const form = useForm({
    user_id: '',
});

const updateGroupRate = (symbolGroupId, value) => {
    groupRateItems.value[symbolGroupId] = value;
};

const submitForm  = () => {
    for (const symbolGroupId in groupRateItems.value) {
        const amount = groupRateItems.value[symbolGroupId];
        const symbolGroup = ib.symbol_groups.find((group) => group.id === Number(symbolGroupId));

        if (isNaN(amount)) {
            errors.value[symbolGroupId] = 'Please enter a valid amount';
        } else if (symbolGroup && parseFloat(amount) > parseFloat(symbolGroup.amount)) {
            const symbolGroupName = symbolGroup?.symbol_group?.name ?? '';
            errors.value[symbolGroupId] = `Invalid Amount for ${symbolGroupName}`;
        } else {
            errors.value[symbolGroupId] = '';
        }
    }

    // If there are any errors, prevent form submission
    if (Object.values(errors.value).some((error) => error !== '')) {
        return;
    }

    // Pass groupRateItems directly to the form submission
    form.post(route('updateRebate.update', { symbolGroupItems: groupRateItems.value }), {
        onSuccess: () => {
            showForm.value = false;
            closeModal();
        },
    });
}

const openRebateAllocationModal = (childId) => {
    const child = children.find((child) => child.id === childId);
    if (child) {
        childDetail.value = child;
        submitAllocation.value = true;
        form.user_id = childId;
    }
};

const closeModal = () => {
    childDetail.value = null;
    submitAllocation.value = false

}

const cancel = () => {
    showForm.value = false;
}

const getAmount = (childRebateInfo) => {
    const amount = groupRateItems.value[childRebateInfo.symbol_group.id] || childRebateInfo.amount;
    return parseFloat(amount).toFixed(2);
};

function formatDate(date) {
    const formattedDate = new Date(date).toISOString().slice(0, 10);
    return formattedDate.replace(/-/g, '/');
}

</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div
            v-for="child in children"
            :key="children.id"
        >
            <div class="w-full bg-white rounded-lg shadow dark:bg-dark-eval-1 p-6">
                <div class="flex flex-col items-center mb-4">
                    <img
                        class="h-12 w-12 rounded-full"
                        :src="child.profile_pic ? child.profile_pic : 'https://img.freepik.com/free-icon/user_318-159711.jpg'"
                        alt="ProfilePic"
                    >
                    <h5 class="my-1 text-xl font-medium text-gray-900 dark:text-white">{{ child.of_user.first_name }}</h5>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ child.of_user.ib_id }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center md:text-left">
                    <div class="text-black dark:text-dark-eval-3">Account type:</div>
                    <div class="text-black dark:text-white">{{ child.account_type.name }}</div>

                    <div class="text-black dark:text-dark-eval-3">Since Date </div>
                    <div class="text-black dark:text-white">{{ formatDate(child.created_at) }}</div>

                    <div class="text-black dark:text-dark-eval-3">Direct IB </div>
                    <div class="text-black dark:text-white">{{ child.of_user.direct_ib }}</div>

                    <div class="text-black dark:text-dark-eval-3">Direct Clients </div>
                    <div class="text-black dark:text-white">{{ child.of_user.direct_client }}</div>

                    <div class="text-black dark:text-dark-eval-3">Total Group IB </div>
                    <div class="text-black dark:text-white">{{ child.of_user.total_ib }}</div>

                    <div class="text-black dark:text-dark-eval-3">Total Group Clients </div>
                    <div class="text-black dark:text-white">{{ child.of_user.total_client }}</div>
                </div>
                <Button
                    ref="buttonRef"
                    class="w-full justify-center mt-6"
                    @click="openRebateAllocationModal(child.id)"
                >
                    View More Details
                </Button>
            </div>

        </div>
    </div>

    <Modal :show="childDetail !== null" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg mb-2 font-medium text-gray-900 dark:text-gray-100">View More Details</h2>
                <hr>
                <div class="grid grid-cols-1 md:grid-cols-2 mt-6">
                    <div>
                        <p class="text-black dark:text-white mb-5">My Rebate Info</p>
                        <div v-for="account in ib.symbol_groups">
                            <div class="grid grid-cols-2 text-black dark:text-white pr-4 mb-5 items-center">
                                <span class="text-black dark:text-dark-eval-3 uppercase">{{ account.symbol_group.name }} (USD)/LOT</span>
                                <span class="text-black dark:text-white text-right md:text-center py-2">{{ account.amount }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="showForm">
                        <p class="text-black dark:text-white mb-5">{{ childDetail.of_user.first_name }}</p>

                        <form @submit.prevent="submitForm">
                            <input v-model="form.user_id" type="hidden" />
                            <div v-for="childRebateInfo in childDetail.symbol_groups" >
                                <div class="grid grid-cols-2 text-black dark:text-white md:pr-4 mb-5 items-center">
                                    <span class="text-black dark:text-dark-eval-3 uppercase">{{ childRebateInfo.symbol_group.name }} (USD)/LOT</span>
                                    <Input
                                        :id="'symbol_group_' + childRebateInfo.symbol_group.id"
                                        :name="'groupRateItems[' + childRebateInfo.symbol_group.id + ']'"
                                        :model-value="getAmount(childRebateInfo)"
                                        @input="updateGroupRate(childRebateInfo.symbol_group.id, $event.target.value)"
                                        type="number"
                                        step="0.01"
                                        min="0.00"
                                        class="block w-full"
                                    />
                                    <InputError :message="errors[childRebateInfo.symbol_group.id]" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 w-full md:w-1/2 md:float-right">
                                <Button class="px-6 justify-center" variant="danger" @click="cancel">Cancel</Button>
                                <Button class="px-6 justify-center">Save</Button>
                            </div>
                        </form>
                    </div>

                    <div v-else>
                        <p class="text-black dark:text-white mb-5">{{ childDetail.of_user.first_name }}</p>
                        <div v-for="childRebateInfo in childDetail.symbol_groups" >
                            <div class="grid grid-cols-2 text-black dark:text-white pr-4 mb-5 items-center">
                                <span class="text-black dark:text-dark-eval-3 uppercase">{{ childRebateInfo.symbol_group.name }} (USD)/LOT</span>
                                <span class="text-black dark:text-white text-right md:text-center py-2">{{ getAmount(childRebateInfo) }}</span>
                            </div>

                        </div>

                        <Button @click="showForm = !showForm" class="px-6 float-right">Edit</Button>
                    </div>
                </div>

            </div>
        </Modal>

</template>
