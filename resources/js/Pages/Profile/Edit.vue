<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    countries: Object,
    avatar: String,
    frontIdentity: String,
    backIdentity: String
})

</script>

<template>
    <AuthenticatedLayout title="Profile">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Profile
            </h2>
        </template>

        <div class="space-y-6">
            <div
                class="p-4 sm:p-8 bg-white dark:bg-dark-eval-1 shadow sm:rounded-lg"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :countries="countries"
                    :avatar="avatar"
                    :frontIdentity="frontIdentity"
                    :backIdentity="backIdentity"
                    class="w-full"
                />
            </div>

            <div
                class="p-4 sm:p-8 bg-white dark:bg-dark-eval-1 shadow sm:rounded-lg"
            >
                <UpdatePasswordForm class="max-w-xl" />
            </div>

<!--            <div-->
<!--                class="p-4 sm:p-8 bg-white dark:bg-dark-eval-1 shadow sm:rounded-lg"-->
<!--            >-->
<!--                <DeleteUserForm class="max-w-xl" />-->
<!--            </div>-->
        </div>
    </AuthenticatedLayout>
</template>
