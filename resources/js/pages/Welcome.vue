<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const showDonationModal = ref(false);
const donationAmount = ref(50);
const donationType = ref('one-time');
const processing = ref(false);

// Datos del formulario
const firstName = ref('');
const lastName = ref('');
const email = ref('');
const phone = ref('');
const address = ref('');
const city = ref('');
const country = ref('Guatemala');
const state = ref('');
const zip = ref('');
const company = ref('');
const message = ref('');
const paymentMethod = ref('CC');
const needsReceipt = ref(false);
const currency = ref('GTQ');

const openDonationModal = () => {
    showDonationModal.value = true;
};

const closeDonationModal = () => {
    showDonationModal.value = false;
};

const processDonation = async () => {
    processing.value = true;

    try {
        // Generar un número de referencia único (puedes implementar tu propia lógica)
        const referenceNumber = `DON-${Date.now()}`;

        const donationData = {
            x_login: 'visanetgt_qpay',
            x_api_key: '88888888888',
            x_amount: donationAmount.value.toString(),
            x_currency_code: currency.value,
            x_first_name: firstName.value,
            x_last_name: lastName.value,
            x_phone: phone.value,
            x_ship_to_address: address.value,
            x_ship_to_city: city.value,
            x_ship_to_country: country.value,
            x_ship_to_state: state.value,
            x_ship_to_zip: zip.value,
            x_ship_to_phone: phone.value,
            x_description: `Donación ${donationType.value === 'monthly' ? 'mensual' : 'única'} a Fundación IPHONE`,
            x_reference: referenceNumber,
            x_url_cancel: window.location.href,
            x_company: company.value || 'C/F',
            x_address: address.value,
            x_city: city.value,
            x_country: country.value,
            x_state: state.value,
            x_zip: zip.value,
            x_freight: '0',
            x_email: email.value,
            x_type: 'AUTH_ONLY',
            x_method: 'CC',
            x_invoice_num: referenceNumber,
            custom_fields: JSON.stringify({
                donationType: donationType.value,
                message: message.value,
                needsReceipt: needsReceipt.value
            }),
            x_visacuotas: 'no',
            x_relay_url: window.location.href,
            products: JSON.stringify([
                [`Donación a Fundación IPHONE`, 'DON001', '', '1', donationAmount.value, donationAmount.value]
            ]),
            taxes: '0',
            http_origin: window.location.hostname,
            origen: 'PLUGIN',
            store_type: 'hostedpage',
            x_discount: '0'
        };

        const response = await axios.post(
            'https://sandboxpayments.qpaypro.com/checkout/register_transaction_store',
            donationData,
            {
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );

        if (response.data) {
            const resultadoGuardado = await setGuardarInformacion(1);
            Swal.fire({
                title: 'Donación exitosa',
                text: `Muchas gracias por tu donación de ${donationAmount.value} ${currency.value === 'GTQ' ? 'Quetzales' : 'Dólares'} gracias a ti un niño tendra un Iphone`,
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            const resultadoGuardado = await setGuardarInformacion(0);
            await Swal.fire({
                title: 'Error',
                text: 'No se recibió una respuesta válida del servidor de pagos. Por favor intente nuevamente.',
                icon: 'error',
                showConfirmButton: false,
                timer: 1500
            });
        }
    } catch (error) {
        const resultadoGuardado = await setGuardarInformacion(0);
        console.error('Error al procesar donación:', error);

        await Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al procesar tu donación. Por favor intente nuevamente.',
            icon: 'error',
            showConfirmButton: false,
            timer: 1500
        });
    } finally {
        processing.value = false;
    }
};

const setGuardarInformacion = async (estado: number): Promise<{ success: boolean; idTransaccion?: number; error?: string }> => {
    try {
        const transaccionData = {
            nombre: firstName.value,
            apellido: lastName.value,
            pais: country.value,
            direccion: address.value,
            nit: company.value || 'C/F',
            descripcion: message.value,
            moneda: currency.value,
            cantidad: donationAmount.value,
            estado: estado
        };

        const response = await axios.post<{ id: number }>('/transacciones/registrar', transaccionData);
        closeDonationModal();
        return {
            success: true,
            idTransaccion: response.data.id
        };
    } catch (error: any) {
        closeDonationModal();
        console.error('Error al guardar transacción:', error);
        return {
            success: false,
            error: error.message || 'Error desconocido al guardar transacción'
        };
    }
};
</script>

<template>

    <Head title="Fundación IPHONE - Transformando Vidas">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </Head>

    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-b from-[#F0F9FF] to-white p-6 text-[#2D3748] lg:justify-center lg:p-8">
        <!-- Header con navegación -->
        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-6xl">
            <nav class="flex items-center justify-between">
                <Link href="/" class="flex items-center">
                <div
                    class="mr-3 h-10 w-10 rounded-full bg-[#3182CE] flex items-center justify-center text-white font-bold text-xl">
                    FE</div>
                <span class="text-xl font-bold text-[#2C5282]">Fundación IPHONE</span>
                </Link>
                <div class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="inline-block rounded-full border border-[#3182CE] px-5 py-1.5 text-sm leading-normal text-[#3182CE] hover:bg-[#3182CE] hover:text-white transition-colors">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-block rounded-full border border-transparent px-5 py-1.5 text-sm leading-normal text-[#3182CE] hover:border-[#3182CE] transition-colors">
                        Iniciar Sesión
                        </Link>
                        <!-- <Link :href="route('register')"
                            class="inline-block rounded-full bg-[#3182CE] px-5 py-1.5 text-sm leading-normal text-white hover:bg-[#2C5282] transition-colors">
                        Registrarse
                        </Link> -->
                    </template>
                </div>
            </nav>
        </header>

        <!-- Contenido principal -->
        <div
            class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-2xl shadow-xl lg:max-w-6xl lg:flex-row">
                <!-- Sección izquierda (contenido) -->
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 lg:rounded-br-none lg:rounded-tl-lg lg:p-12">
                    <h1 class="mb-4 text-3xl font-bold text-[#2C5282]">Apoyanos para contruir un mundo con más <span
                            class="text-[#3182CE]">IPHONES</span> y menos Androids</h1>
                    <p class="mb-6 text-lg text-[#4A5568]">
                        En Fundación IPHONE trabajamos para un mejor desarrollo socioeconomico, donde los jovenes podran
                        llevar una mejor convivencia con relacionandose con más jovenes asi eliminando el clasismo.
                    </p>

                    <!-- Tarjetas de impacto -->
                    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div
                            class="rounded-xl bg-[#EBF8FF] p-4 text-center shadow-sm transition-transform hover:scale-105">
                            <div class="text-3xl font-bold text-[#3182CE]">15,000+</div>
                            <div class="text-sm font-medium">Iphones Donados</div>
                        </div>
                        <div
                            class="rounded-xl bg-[#EBF8FF] p-4 text-center shadow-sm transition-transform hover:scale-105">
                            <div class="text-3xl font-bold text-[#3182CE]">10</div>
                            <div class="text-sm font-medium">Paises de Latinoamerica</div>
                        </div>
                        <div
                            class="rounded-xl bg-[#EBF8FF] p-4 text-center shadow-sm transition-transform hover:scale-105">
                            <div class="text-3xl font-bold text-[#3182CE]">10 años</div>
                            <div class="text-sm font-medium">De trayectoria</div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="mb-4 text-xl font-semibold text-[#2C5282]">Nuestro Apoyo</h2>
                        <div class="space-y-4">
                            <div
                                class="flex items-start rounded-lg bg-[#F7FAFC] p-4 transition-colors hover:bg-[#EBF8FF]">
                                <div
                                    class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#3182CE] text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Educación</h3>
                                    <p class="text-sm text-[#718096]">Les enseñamos a los jovenes a no discriminar a los
                                        usuarios de Android
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-start rounded-lg bg-[#F7FAFC] p-4 transition-colors hover:bg-[#EBF8FF]">
                                <div
                                    class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#3182CE] text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Apoyo Solidario</h3>
                                    <p class="text-sm text-[#718096]">Brindamos recursos de Ishop a personas necesitadas
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-start rounded-lg bg-[#F7FAFC] p-4 transition-colors hover:bg-[#EBF8FF]">
                                <div
                                    class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#3182CE] text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z"
                                            clip-rule="evenodd" />
                                        <path
                                            d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Capacitación Técnologica</h3>
                                    <p class="text-sm text-[#718096]">Talleres y cursos para jóvenes y adultos para que
                                        sepan usar un IPHONE</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                        <button @click="openDonationModal"
                            class="inline-block rounded-full bg-gradient-to-r from-[#3182CE] to-[#2B6CB0] px-8 py-3 text-center text-sm font-medium text-white shadow-lg hover:from-[#2B6CB0] hover:to-[#3182CE] transition-all hover:shadow-xl">
                            Donar Ahora
                        </button>
                        <Link href="/voluntariado"
                            class="inline-block rounded-full border border-[#3182CE] px-8 py-3 text-center text-sm font-medium text-[#3182CE] hover:bg-[#EBF8FF] transition-colors">
                        Ser Voluntario
                        </Link>
                    </div>
                </div>

                <!-- Sección derecha (imagen) -->
                <div
                    class="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-gradient-to-br from-[#2B6CB0] to-[#4299E1] lg:-ml-px lg:mb-0 lg:aspect-auto lg:w-[500px] lg:rounded-r-lg lg:rounded-t-none">
                    <div class="absolute inset-0 flex items-center justify-center p-8 text-white">
                        <div class="text-center">
                            <h2 class="mb-4 text-4xl font-bold leading-tight">Tu ayuda cambia vidas</h2>
                            <p class="mb-6 text-xl opacity-90">Cada contribución nos acerca a un mundo con menos
                                Androids</p>
                            <div class="animate-bounce">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#3182CE] to-transparent">
                    </div>
                </div>
            </main>
        </div>

        <!-- Footer -->
        <footer class="mt-8 hidden w-full max-w-6xl text-center text-sm text-[#718096] lg:block">
            © 2025 Fundación IPHONE. Todos los derechos reservados.
            <div class="mt-2 flex justify-center space-x-4">
                <a href="#" class="hover:text-[#3182CE]">Términos</a>
                <a href="#" class="hover:text-[#3182CE]">Privacidad</a>
                <a href="#" class="hover:text-[#3182CE]">Contacto</a>
            </div>
        </footer>

        <!-- Modal de Donación (Bootstrap) -->
        <div v-if="showDonationModal" class="modal fade show" tabindex="-1"
            style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-[#3182CE] text-white">
                        <h5 class="modal-title">Haz tu donación</h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeDonationModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="processDonation">
                            <div class="mb-4">
                                <label class="form-label">Tipo de donación</label>
                                <div class="btn-group w-100">
                                    <button type="button" class="btn"
                                        :class="donationType === 'one-time' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationType = 'one-time'">
                                        Única
                                    </button>
                                    <button type="button" class="btn"
                                        :class="donationType === 'monthly' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationType = 'monthly'">
                                        Mensual
                                    </button>
                                </div>
                            </div>
                            <!-- Dentro de tu modal, agrega esta sección donde consideres apropiado -->
                            <div class="mb-4">
                                <label class="form-label">Moneda de la donación</label>
                                <div class="btn-group w-100">
                                    <button type="button" class="btn"
                                        :class="currency === 'GTQ' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="currency = 'GTQ'">
                                        Quetzales (GTQ)
                                    </button>
                                    <button type="button" class="btn"
                                        :class="currency === 'USD' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="currency = 'USD'">
                                        Dólares (USD)
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Cantidad</label>
                                <div class="btn-group w-100 flex-wrap">
                                    <button type="button" class="btn"
                                        :class="donationAmount === 20 ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationAmount = 20">
                                        $20
                                    </button>
                                    <button type="button" class="btn"
                                        :class="donationAmount === 50 ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationAmount = 50">
                                        $50
                                    </button>
                                    <button type="button" class="btn"
                                        :class="donationAmount === 100 ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationAmount = 100">
                                        $100
                                    </button>
                                    <button type="button" class="btn"
                                        :class="donationAmount === 200 ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="donationAmount = 200">
                                        $200
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <label for="customAmount" class="form-label">O escribe otra cantidad:</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="customAmount"
                                            v-model="donationAmount" min="1" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Datos personales</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Primer nombre*"
                                            v-model="firstName" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Apellido*"
                                            v-model="lastName" required>
                                    </div>
                                </div>
                                <input type="email" class="form-control mb-2" placeholder="Correo electrónico*"
                                    v-model="email" required>
                                <input type="tel" class="form-control mb-2" placeholder="Teléfono*" v-model="phone"
                                    required>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Dirección*"
                                            v-model="address" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Ciudad*" v-model="city"
                                            required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <select class="form-control" v-model="country" required>
                                            <option value="">País*</option>
                                            <option value="Guatemala">Guatemala</option>
                                            <!-- Otras opciones de países -->
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Departamento/Estado*"
                                            v-model="state" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Código postal*"
                                            v-model="zip" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" placeholder="NIT (o C/F si no tiene)"
                                            v-model="company">
                                    </div>
                                </div>

                                <textarea class="form-control mt-2" placeholder="Mensaje opcional"
                                    v-model="message"></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Método de pago</label>
                                <div class="border rounded p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            id="creditCard" value="CC" v-model="paymentMethod" checked required>
                                        <label class="form-check-label" for="creditCard">
                                            Tarjeta de crédito/débito
                                        </label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="paypal"
                                            value="PP" v-model="paymentMethod">
                                        <label class="form-check-label" for="paypal">
                                            PayPal
                                        </label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            id="bankTransfer" value="BT" v-model="paymentMethod">
                                        <label class="form-check-label" for="bankTransfer">
                                            Transferencia bancaria
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="receipt" v-model="needsReceipt">
                                <label class="form-check-label" for="receipt">
                                    Necesito recibo deducible de impuestos
                                </label>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    Acepto los términos y condiciones*
                                </label>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    @click="closeDonationModal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" :disabled="processing">
                                    <span v-if="processing">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Procesando...
                                    </span>
                                    <span v-else>
                                        Donar ${{ donationAmount }} {{ donationType === 'monthly' ? 'mensualmente' :
                                            'ahora' }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
