<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import NetworkChild from './Partials/NetworkChild.vue'

defineProps({
    users: Object
});

function nodeWasClicked(users) {
    alert(users.parent.first_name);
}

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

        <NetworkChild
            :users="users"
            @onClick="nodeWasClicked"
        />

    </AuthenticatedLayout>
</template>
