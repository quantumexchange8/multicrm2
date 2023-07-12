<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import {computed, defineComponent} from 'vue'
import { Carousel, Pagination, Slide } from 'vue3-carousel'
import { TickerTape } from "vue-tradingview-widgets";

import 'vue3-carousel/dist/carousel.css'
import {usePage} from "@inertiajs/vue3";

const page = usePage()
const user = computed(() => page.props.auth.user)

defineComponent({
    name: 'Highlight',
    components: {
        Carousel,
        Slide,
        Pagination,
        TickerTape
    },
});

const highlightCarousels = [
    { src: '/assets/dashboard/highlight-1.png' },
    { src: '/assets/dashboard/highlight-2.png' },
    { src: '/assets/dashboard/highlight-3.png' },
    { src: '/assets/dashboard/highlight-4.png' },
];
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Dashboard
                </h2>
            </div>
        </template>

        <div class="mb-6">
            <Carousel :autoplay="2000" :items-to-show="2.5" :wrap-around="true" class="my-carousel">
                <Slide v-for="(highlight, index) in highlightCarousels" :key="index" class="carousel-slide">
                    <img class="object-cover w-full" :src="highlight.src" alt="">
                </Slide>

                <template #addons>
                    <Pagination class="my-carousel-pagination" />
                </template>
            </Carousel>
        </div>
        <div class="mb-6">
            <iframe scrolling="no" allowtransparency="true" frameborder="0" src="https://s.tradingview.com/embed-widget/ticker-tape/?locale=en#%7B%22symbols%22%3A%5B%7B%22proName%22%3A%22FOREXCOM%3ASPXUSD%22%2C%22title%22%3A%22S%26P%20500%22%7D%2C%7B%22proName%22%3A%22FOREXCOM%3ANSXUSD%22%2C%22title%22%3A%22US%20100%22%7D%2C%7B%22proName%22%3A%22FX_IDC%3AEURUSD%22%2C%22title%22%3A%22EUR%2FUSD%22%7D%2C%7B%22proName%22%3A%22BITSTAMP%3ABTCUSD%22%2C%22title%22%3A%22Bitcoin%22%7D%2C%7B%22proName%22%3A%22BITSTAMP%3AETHUSD%22%2C%22title%22%3A%22Ethereum%22%7D%5D%2C%22showSymbolLogo%22%3Atrue%2C%22colorTheme%22%3A%22dark%22%2C%22isTransparent%22%3Atrue%2C%22displayMode%22%3A%22adaptive%22%2C%22width%22%3A%22100%25%22%2C%22height%22%3A44%2C%22utm_source%22%3A%22currenttech.pro%22%2C%22utm_medium%22%3A%22widget%22%2C%22utm_campaign%22%3A%22ticker-tape%22%2C%22page-uri%22%3A%22currenttech.pro%2Fdemocrm%2Fpublic%2Fmember%2Fdashboard%22%7D" style="box-sizing: border-box; display: block; height: 50px; width: 100%;"></iframe>
        </div>

        <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
            Welcome back! {{ user.name }}
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.my-carousel {
    gap: 20px;
}

.carousel-slide {
    margin-right: 20px;
}

.my-carousel-pagination {
    --vc-pgn-width: 5px;
    --vc-pgn-height: 5px;
    --vc-pgn-margin: 4px;
    --vc-pgn-border-radius: 50%;
    --vc-pgn-background-color: #ffffff61;
    --vc-pgn-active-color: #fff;
}
</style>
