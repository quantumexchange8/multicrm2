<script setup>
import DataTable from "datatables.net-vue3";
import 'datatables.net-dt/css/jquery.dataTables.css';
import ButtonsHtml5 from 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';
import 'datatables.net-responsive-dt';
import 'datatables.net-responsive-dt/css/responsive.dataTables.min.css';
import JsZip from "jszip";
import pdfmake from "pdfmake/build/pdfmake";
import * as pdfFonts from "pdfmake/build/vfs_fonts";
import {ref} from "vue";
pdfmake.vfs = pdfFonts.pdfMake ? pdfFonts.pdfMake.vfs : pdfmake.vfs;
window.JSZip = JsZip;
DataTable.use(ButtonsHtml5);

defineProps({
    withdrawals: Object
})

const columns = ref([]);
const buttons = ref([]);

columns.value = [
    {
        data: 'created_at',
        render: function(data, type, row) {
            if (type === 'display') {
                const date = new Date(data);
                return `${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear()}`;
            }
            return data;
        }
    },
    {
        data: 'created_at',
        render: function (data, type, row) {
            if (type === 'display') {
                const date = new Date(data);
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                const seconds = date.getSeconds().toString().padStart(2, '0');

                return `${hours}:${minutes}:${seconds}`;
            }
            return data;
        }
    },
    {data: 'channel'},
    {data: 'amount'},
    {
        data: 'status',
        render: function(data, type, row) {
            const statusClass = getStatusClass(data); // Function to determine the class based on status
            return `<span class="${statusClass}">${data}</span>`;
        }
    }
]

buttons.value = [
    {
        title: 'Withdrawal From Wallet',
        extend: 'excelHtml5',
        className: 'text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700'
    },
    {
        title: 'Withdrawal From Wallet',
        extend: 'pdfHtml5',
        className: 'text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700'
    },
    {
        title: 'Withdrawal From Wallet',
        extend: 'print',
        className: 'text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700'
    },
]

function getStatusClass(status) {
    switch (status) {
        case 'Successful':
            return 'bg-green-500 text-white test-xs font-medium mr-2 px-4 py-1 rounded dark:bg-[#013B20]';
        case 'Submitted':
            return 'bg-yellow-500 text-white test-xs font-medium mr-2 px-4 py-1 rounded dark:bg-[#573A15]';
        case 'Rejected':
            return 'bg-red-500 text-white test-xs font-medium mr-2 px-6 py-1 rounded dark:bg-[#4C1310]';
        default:
            return '';
    }
}
</script>
<template>
    <div class="p-4 mt-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <div class="relative overflow-x-auto sm:rounded-lg mt-4">
            <DataTable
                :data="withdrawals"
                :columns="columns"
                class="w-full text-sm text-center text-gray-500 dark:text-gray-400"
                :options="{responsive:true, autoWidth:false, dom:'Bfrtip', buttons:buttons}"
            >
                <thead class="text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-white text-center">
                <tr>
                    <th class="py-2" style="text-align: center">Date</th>
                    <th class="py-2" style="text-align: center">Time</th>
                    <th class="py-2" style="text-align: center">Withdrawal Methods</th>
                    <th class="py-2" style="text-align: center">Amount ($)</th>
                    <th class="py-2" style="text-align: center">Status</th>
                </tr>
                </thead>

            </DataTable>
        </div>

    </div>
</template>
