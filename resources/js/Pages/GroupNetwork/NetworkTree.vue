<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import InputError from '@/Components/InputError.vue'
import Label from '@/Components/Label.vue'
import Button from '@/Components/Button.vue'
import Input from '@/Components/Input.vue'
import InputSelect from '@/Components/InputSelect.vue';
import NetwokChild from './Partials/NetworkChild.vue'

defineProps({
    users: Array,
    user_downline: Array,
});

</script>

<template>
    <AuthenticatedLayout title="Network Tree">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Network Tree
                </h2>
            </div>
        </template>

        <form>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <Label for="first_name">View Upline Info</Label>

                    <InputSelect class="block w-full text-sm" placeholder="View Upline">

                    </InputSelect>

                    
                </div>
                <div class="space-y-2">
                    <Label for="search_keyword">Search By Name / Email</Label>

                    <Input
                        id="chinese_name"
                        type="text"
                        class="mt-1 block w-full"
                        autocomplete="chinese_name"
                    />

                    
                </div>
                <div class="space-y-2">
                    
                    <Button @click="submit" >Search</Button>
                </div>
            </div>

            <!-- LOOP ALL USER IF HAVE HIERARCHY -->
            <div id="network-level">
                <div>
                    <template v-for="user in users">
                        <!-- <div>
                            {{ user }}
                        </div> -->
                        <template v-if="user_downline.length > 0">
                            <!-- <li :data-id="user.id"> -->
                                <div>
                                    <p class="text-gray-500 dark:text-white">
                                        <i></i>
                                        {{ user.upline_id + ' | ' + user.first_name + ': ' + user.email + ' | Ranking: ' + user.role }}
                                    </p>
                                </div>
                                <template v-if="user_downline">
                                    <NetwokChild :children="user_downline"></NetwokChild>
                                </template>
                            <!-- </li> -->
                        </template>
                        <template v-else>
                            <!-- <li :data-id="user.id"> -->
                                <div>
                                    <p class="text-gray-500 dark:text-white">
                                        <i></i>
                                        {{ user.upline_id + ' | ' + user.first_name + ': ' + user.email + ' | Ranking: ' + user.role }}
                                    </p>
                                </div>
                            <!-- </li> -->
                        </template>
                    </template>
                </div>
                <hr class="mt-25 mb-25">
            </div>

        </form>
        
    </AuthenticatedLayout>
</template>