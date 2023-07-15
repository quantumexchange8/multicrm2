<script setup>
import InputError from '@/Components/InputError.vue'
import Label from '@/Components/Label.vue'
import Button from '@/Components/Button.vue'
import Input from '@/Components/Input.vue'
import InputSelect from '@/Components/InputSelect.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3'
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {ref} from "vue";


const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    countries: Object,
})
const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const user = usePage().props.auth.user

const form = useForm({
    first_name: user.first_name,
    chinese_name: user.chinese_name,
    email: user.email,
    country: user.country,
    dob: user.dob,
    phone: user.phone,
})

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4"
        >

            <div class="space-y-2">
                <Label for="first_name" value="Name" />

                <Input
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.first_name"
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.first_name" />
            </div>
            <div class="space-y-2">
                <Label for="chinese_name" value="Chinses Name" />

                <Input
                    id="chinese_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.chinese_name"
                    autofocus
                    autocomplete="chinese_name"
                />

                <InputError class="mt-2" :message="form.errors.chinese_name" />
            </div>


            <div class="space-y-2">
                <Label for="dob" value="Date Of Birth" />

               <!-- <Input
                    id="dob"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.dob"
                    autofocus
                    autocomplete="dob"
                /> -->
                <vue-tailwind-datepicker :formatter="formatter" as-single v-model="form.dob" input-classes="py-2 border-gray-400 w-full rounded-full text-sm placeholder:text-sm focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 bg-[#202020] dark:text-gray-300 dark:focus:ring-offset-dark-eval-1" />

                <InputError class="mt-2" :message="form.errors.dob" />
            </div>


            <div class="space-y-2">
                <Label for="country" value="Country" />

                <InputSelect v-model="form.country" class="block w-full text-sm" placeholder="Choose Country">
                        <option v-for="country in props.countries" :value="country.name_en" :key="country.id">{{ country.name_en }}</option>
                    </InputSelect>

                <InputError class="mt-2" :message="form.errors.country" />
            </div>



            <div class="space-y-2">
                <Label for="phone" value="Mobile Phone" />

                <Input
                    id="phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    autofocus
                    autocomplete="phone"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>



            <div class="space-y-2">
                <Label for="email" value="Email" />

                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autocomplete="email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div
                v-if="props.mustVerifyEmail && user.email_verified_at === null"
            >
                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="props.status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="form.processing">Save</Button>

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                    class="transition ease-in-out"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
