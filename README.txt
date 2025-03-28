README - Proyecto Qpaypro
=============================================

1. Instrucciones de Configuración
---------------------------------
- Clonar el repositorio del proyecto
- Instalar dependencias: [Vue, Axios, Datatable, Jquery, Bostrap, Chart, Sweetalert2]
- Configurar variables de entorno:
  * API_URL=https://api-sandboxpayments.qpaypro.com
  * FRONTEND_URL=[http://127.0.0.1:8000/]
- Ejecutar la aplicación: [composer run dev]

2. Estructura del Proyecto
--------------------------
/
├── app/                  # Lógica backend (Laravel)
│   └── Http/Controllers/
│       └── TransaccionController.php
├── resources/
│   ├── js/               # Componentes Vue
│   │   ├── components/
│   │   ├── layouts/
│   │   └── pages/
│   │       ├── auth/
│   │       ├── Welcome.vue  # Vista principal de donaciones
│   │       └── Dashboard.vue # Panel administrativo
│   └── views/
│       └── app.blade.php # Punto de entrada principal
├── routes/
│   └── web.php           # Rutas frontend
└── public/               # Assets compilados

3. Integración con la API de Pagos
----------------------------------
El flujo de integración con la API de QPayPro es el siguiente:

1. El usuario completa el formulario de donación con:
   - Monto
   - Información personal

2. La aplicación envía los datos a:
   POST https://api-sandboxpayments.qpaypro.com/checkout/register_transaction_store

3. La API responde con un token de transacción

4. La aplicación redirige al usuario a:
   https://sandboxpayments.qpaypro.com/checkout/store?token=[TOKEN_RECIBIDO]

5. El usuario completa el pago en la pasarela de QPayPro

6. QPayPro redirige de vuelta a la URL de retorno configurada