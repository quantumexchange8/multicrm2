<script setup>
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import { ref } from "vue";
import { useForm } from '@inertiajs/vue3';

    defineProps({
        childrens: Array,
        accounts: Object,
        submitAllocation: Boolean,
    });

    const submitAllocation = ref(false);
    const showForm = ref(false);

    const symbolGroupRate = ref({});

    const form = useForm({
        symbolGroupRate: [],
    });
    
    const openRebateAllocationModal = (userId) => {
        submitAllocation.value = true;
        // console.log("User ID:", userId);
    }

    const closeModal = () => {
        submitAllocation.value = false
        
    }

    const cancel = () => {
        showForm.value = false;
        symbolGroupRate.value = {};
    }

    const submitForm  = () => {
        
        form.patch(route('updateRebate.update', form.data));
    }

    function formatDate(date) {
        return new Date(date).toISOString().slice(0, 10);
    }


</script>

<template>
        <div v-for="children in childrens" :key="children.account.of_user.id" class="mt-10">
            <p class="text-black dark:text-white">IB NAME:
                <span class="text-black dark:text-white">{{ children.account.of_user.first_name }}</span>
            </p>
            <p class="text-black dark:text-white">IB ID:
                <span class="text-black dark:text-white">{{ children.account.of_user.ib_id }}</span>
            </p>
            <p class="text-black dark:text-white">account type:
                <span class="text-black dark:text-white">{{ children.account.account_type.name }}</span>
            </p>
            <p class="text-black dark:text-white">SINCE DATE:
                <span class="text-black dark:text-white">{{ formatDate(children.account.of_user.created_at)}}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT IB:
                <span class="text-black dark:text-white">{{ children.account.of_user.direct_ib }}</span>
            </p>
            <p class="text-black dark:text-white">DIRECT CLIENTS:
                <span class="text-black dark:text-white">{{ children.account.of_user.direct_client }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP IB:
                <span class="text-black dark:text-white">{{ children.account.of_user.total_ib }}</span>
            </p>
            <p class="text-black dark:text-white">TOTAL GROUP CLIENTS:
                <span class="text-black dark:text-white">{{ children.account.of_user.total_client }}</span>
            </p>
            
            <!-- {{ children.account.user_id }} -->
            <button ref="buttonRef" class="w-full justify-center" @click="openRebateAllocationModal(children.account.user_id)">
                View More Details
            </button>

            <template v-if="children.children && children.children.length > 0">
                <!-- Recursive component call to display downlines -->
                <rebate-child :childrens="children.children" :accounts="accounts" :submitAllocation="submitAllocation"/>
            </template>
            
        </div>

        <Modal :show="submitAllocation" @close="closeModal">

                <h2 class="text-lg mb-2 font-medium text-gray-900 dark:text-gray-100">View More Details</h2>
                
                <p class="text-black dark:text-white mb-5">My Rebate Info</p>
                <div v-for="account in accounts.symbol_groups">
                    <p class="text-black dark:text-white mb-5">
                        {{ account.symbol_group.name }} (USD)/LOT =
                        <span class="text-black dark:text-white mb-5">
                            {{ account.amount }}
                        </span>
                    </p>
                </div>
            
                <div v-if="showForm">
                    <form @submit.prevent="submitForm">
                        <div v-for="children in childrens" :key="children.account.of_user.id" class="mt-10">
                            <div v-for="childrenUSD in children.account.symbol_groups">
                                <p class="text-black dark:text-white mb-5">
                                    {{ childrenUSD.symbol_group.name }} (USD)/LOT =
                                    ID: {{ childrenUSD.id }}
                                    <Input
                                        :id="'symbol_group_' + childrenUSD.symbol_group.id"
                                        :name="'symbolGroupRate[' + childrenUSD.symbol_group.id + ']'"
                                        :value="childrenUSD.symbol_group.rate"
                                        type="number"
                                        class="block"
                                        />
                                </p>
                            </div>
                        </div>
                        
                        <button class="bg-red-600" @click="cancel">Cancel</button>
                        <button class="bg-blue-600">Save</button>
                    </form>
                </div>

                <div v-else>
                    <div v-for="children in childrens" :key="children.account.of_user.id" class="mt-10">
                        <p class="text-black dark:text-white">IB NAME:
                            <span class="text-black dark:text-white">{{ children.account.of_user.first_name }}</span>
                            
                        </p>

                        <div v-for="childrenUSD in children.account.symbol_groups" >
                            <p class="text-black dark:text-white mb-5">
                                ID: {{ childrenUSD.symbol_group.id }}
                                {{ childrenUSD.symbol_group.name }} (USD)/LOT =
                                <span class="text-black dark:text-white mb-5">
                                    {{ childrenUSD.amount }}
                                </span>
                            </p>
                        </div>
                        
                    </div>

                    <button @click="showForm = !showForm" class="bg-white">Edit</button>
                </div>
                
        </Modal>
       
</template>