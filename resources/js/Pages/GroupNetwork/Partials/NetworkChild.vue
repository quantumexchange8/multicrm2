<script>
import {PlusCircleIcon, MinusCircleIcon} from "@heroicons/vue/outline";
export default {
    name: 'NetworkChild',
    components: {PlusCircleIcon, MinusCircleIcon},
    props: {
        users: Object,
        depth: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            expanded: false,
        }
    },
    methods: {
        nodeClicked() {
            this.expanded = !this.expanded;
            if (!this.hasChildren) {
                this.$emit('onClick', this.users);
            }
        }
    },
    computed: {
        hasChildren() {
            return this.users.children;
        },
        iconSizeClasses() {
            return 'w-6 h-6 cursor-pointer hover:text-[#FF9E23]';
        }
    },
    emits: ['onClick']
}
</script>

<template>
    <div
        @click="nodeClicked"
        class="dark:text-white flex items-center mb-6"
        :style="{ 'margin-left': `${depth * 20}px` }"
    >
        <div v-if="hasChildren" class="mr-2">
            <template v-if="expanded">
            <!-- Show the MinusCircleIcon if expanded -->
            <MinusCircleIcon aria-hidden="true" :class="iconSizeClasses" />
            </template>
            <template v-else>
                <!-- Show the PlusCircleIcon if not expanded -->
                <PlusCircleIcon aria-hidden="true" :class="iconSizeClasses" />
            </template>
        </div>
        <div v-else class="mr-2 w-5 h-5 bg-dark-eval-2 dark:bg-white hover:bg-[#FF9E23] rounded-full"></div>
        <div class="flex items-center">
            <a href="#" class="flex items-center p-3 space-x-4 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 dark:text-white">
                <img
                    class="object-cover w-10 h-10 rounded-full"
                    :src="users.profile_photo ? users.profile_photo : 'https://img.freepik.com/free-icon/user_318-159711.jpg'"
                    alt="userPic"
                >
                <div class="flex-col ml-3 whitespace-nowrap w-28">
                    <span>{{ users.parent.first_name }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">{{ users.parent.email }}</span>
                </div>
                <div class="flex-1 ml-3 text-center whitespace-nowrap">
                    <span>{{ users.level }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">Level</span>
                </div>
                <div class="flex-1 ml-3 text-center whitespace-nowrap">
                    <span>{{ users.parent.total_group_deposit }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">Total Group Deposit</span>
                </div>
                <div class="flex-1 ml-3 text-center whitespace-nowrap">
                    <span>{{ users.parent.total_group_withdrawal }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">Total Group Withdrawal</span>
                </div>
                <div class="flex-1 ml-3 text-center whitespace-nowrap">
                    <span>{{ users.parent.total_ib }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">Total IB</span>
                </div>
                <div class="flex-1 ml-3 text-center whitespace-nowrap">
                    <span>{{ users.parent.total_client }}</span> <br>
                    <span class="text-xs dark:text-dark-eval-4">Total Client</span>
                </div>
            </a>
        </div>
    </div>
    <NetworkChild
        v-if="expanded"
        v-for="(child, index) in users.children"
        :key="child.id"
        :users="child"
        :depth="depth + 1"
        @onClick="nodeClicked"
    />
</template>
