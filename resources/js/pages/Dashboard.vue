<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt/css/dataTables.dataTables.min.css';
import 'datatables.net-buttons';
import 'datatables.net-buttons-dt/css/buttons.dataTables.min.css';
import 'datatables.net-buttons/js/buttons.html5.min.js';
import 'datatables.net-buttons/js/buttons.print.min.js';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Transaccion {
    id_transaccion: number;
    nombre: string;
    apellido: string;
    pais: string;
    direccion: string;
    nit: string;
    descripcion: string;
    moneda: 'GTQ' | 'USD';
    cantidad: number | string;
    fecha: string;
    estado: 0 | 1;
}

interface TransaccionFormateada extends Omit<Transaccion, 'cantidad'> {
    cantidad: number;
}

const tablaTransacciones = ref<any>(null);
const transaccionesData = ref<TransaccionFormateada[]>([]);
const estadoFiltro = ref(2);

const cargarTablaTransacciones = async () => {
    try {
        const response = await axios.get('/transacciones', {
            params: {
                estado: estadoFiltro.value
            }
        });

        transaccionesData.value = response.data.map((item: Transaccion) => ({
            ...item,
            cantidad: Number(item.cantidad) || 0
        }));

        if ($.fn.DataTable.isDataTable("#transacciones-table")) {
            $("#transacciones-table").DataTable().destroy();
        }

        if (transaccionesData.value.length) {
            tablaTransacciones.value = $("#transacciones-table").DataTable({
                dom: '<"top"<"left-col"l><"center-col"B><"right-col"f>>rt<"bottom"ip>',
                buttons: [
                    {
                        text: '<i class="fas fa-list"></i> Todas',
                        className: 'btn btn-primary btn-sm mx-1',
                        action: function () {
                            estadoFiltro.value = 2;
                            cargarTablaTransacciones();
                        }
                    },
                    {
                        text: '<i class="fas fa-check-circle"></i> Exitosas',
                        className: 'btn btn-success btn-sm mx-1',
                        action: function () {
                            estadoFiltro.value = 1;
                            cargarTablaTransacciones();
                        }
                    },
                    {
                        text: '<i class="fas fa-times-circle"></i> Fallidas',
                        className: 'btn btn-danger btn-sm mx-1',
                        action: function () {
                            estadoFiltro.value = 0;
                            cargarTablaTransacciones();
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm mx-1',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                ordering: false,
                pageLength: 10,
                processing: true,
                lengthChange: true,
                paging: true,
                info: true,
                scrollX: true,
                scrollY: '50vh',
                language: {
                    emptyTable: "No hay transacciones para mostrar",
                    processing: "Procesando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "No se encontraron resultados",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    search: "Buscar:",
                    paginate: {
                        first: "<i class='fas fa-angle-double-left'></i>",
                        last: "<i class='fas fa-angle-double-right'></i>",
                        next: "<i class='fas fa-angle-right'></i>",
                        previous: "<i class='fas fa-angle-left'></i>"
                    }
                },
                columns: [
                    {
                        class: "text-center",
                        data: 'id_transaccion',
                        render: function (data: any, type: any, row: any) {

                            let bgColor;

                            if (row.estado == 1)
                                bgColor = 'btn-success'
                            else
                                bgColor = 'btn-danger'

                            return `<button class="btn btn-sm ${bgColor} detalle" data-id="${data}">
                                    <i class="fa-solid fa-hashtag"></i> ${data}
                                </button>`
                        },
                    },
                    { class: "text-center", mData: 'nombre' },
                    { class: "text-center", mData: 'apellido' },
                    { class: "text-center", mData: 'pais' },
                    { class: "text-center", mData: 'direccion' },
                    { class: "text-center", mData: 'nit' },
                    { class: "text-center", mData: 'descripcion' },
                    {
                        class: "text-center",
                        data: 'cantidad',
                        render: function (data: any, type: any, row: any) {
                            // Convertir a número y manejar casos no numéricos
                            const amount = Number(data) || 0;
                            return row.moneda === 'GTQ'
                                ? `Q${amount.toFixed(2)}`
                                : `$${amount.toFixed(2)}`;
                        }
                    },
                    { class: "text-center", mData: 'fecha' },
                    {
                        "class": "text-center",
                        data: 'estado',
                        render: function (data: any, type: any, row: any) {

                            let bgColor;

                            if (data == 1)
                                bgColor = 'btn-success'
                            else
                                bgColor = 'btn-danger'

                            return `<button class="btn btn-sm ${bgColor}">
                                    <i class="fa-solid fa-hashtag"></i> ${data == 1 ? 'Exitosa' : 'Fallida'}
                                </button>`
                        },
                    },
                ],
                data: transaccionesData.value
            });
        }
        inicializarGraficas();
    } catch (error) {
        console.error(error);
    }
};
const inicializarGraficas = () => {
    if (chartTransacciones.value) {
        chartTransacciones.value.destroy();
    }
    if (chartDonaciones.value) {
        chartDonaciones.value.destroy();
    }

    const totalTransacciones = transaccionesData.value.length;
    const transaccionesExitosas = transaccionesData.value.filter(t => t.estado === 1).length;
    const transaccionesFallidas = totalTransacciones - transaccionesExitosas;

    const totalDonado = transaccionesData.value
        .filter(t => t.estado === 1)
        .reduce((sum, t) => sum + t.cantidad, 0);

    const ctx1 = document.getElementById('chartTransacciones') as HTMLCanvasElement;
    if (ctx1) {
        chartTransacciones.value = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Todas', 'Exitosas', 'Fallidas'],
                datasets: [{
                    data: [totalTransacciones, transaccionesExitosas, transaccionesFallidas],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Transacciones',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const totalDonadoGTQ = transaccionesData.value
        .filter(t => t.estado === 1 && t.moneda === 'GTQ')
        .reduce((sum, t) => sum + t.cantidad, 0);

    const totalDonadoUSD = transaccionesData.value
        .filter(t => t.estado === 1 && t.moneda === 'USD')
        .reduce((sum, t) => sum + t.cantidad, 0);
    console.log(totalDonadoUSD)
    // Gráfica 2: Total donado en transacciones exitosas por moneda
    const ctx2 = document.getElementById('chartDonaciones') as HTMLCanvasElement;
    if (ctx2) {
        chartDonaciones.value = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Total Donado'],
                datasets: [
                    {
                        label: 'Quetzales (GTQ)',
                        data: [totalDonadoGTQ],
                        backgroundColor: '#27ae60', // Verde
                        borderWidth: 1
                    },
                    {
                        label: 'Dólares (USD)',
                        data: [totalDonadoUSD],
                        backgroundColor: '#3498db', // Azul
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Donado en Transacciones Exitosas',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto'
                        }
                    }
                }
            }
        });
    }
};
onMounted(() => {
    cargarTablaTransacciones();
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="chartTransacciones"></canvas>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="chartDonaciones"></canvas>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <table id="transacciones-table" ref="tableRef" class="w-full stripe hover" style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Apellido</th>
                            <th class="px-4 py-2">Pais</th>
                            <th class="px-4 py-2">Direccion</th>
                            <th class="px-4 py-2">Nit</th>
                            <th class="px-4 py-2">Descripción</th>
                            <th class="px-4 py-2">Donado</th>
                            <th class="px-4 py-2">Fecha</th>
                            <th class="px-4 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
