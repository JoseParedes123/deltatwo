<?php $paginaActual = 'servicios'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486">
  <title>Servicios | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main>
    <div class="titulo-cinta">
      <h1>Servicios</h1>
    </div>
    <p class="introduccion-pagina">En Logística Delta ofrecemos soluciones completas para que tus envíos lleguen de forma segura, rápida y confiable.</p>
    <section class="contenedor grilla-servicios" aria-label="Nuestros servicios">
      <article class="tarjeta-servicio">
        <h2>
          <svg class="icono-servicio" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="3" aria-hidden="true">
            <path d="M4 12h25v22H4zM29 19h8l7 8v7H29z" />
            <circle cx="13" cy="37" r="4" />
            <circle cx="36" cy="37" r="4" />
          </svg>
          Envíos regionales
        </h2>
        <p>Llevamos tus paquetes a todo Buenos Aires.</p>
      </article>
      <article class="tarjeta-servicio">
        <h2>
          <svg class="icono-servicio" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="3" aria-hidden="true">
            <circle cx="24" cy="24" r="19" />
            <path d="M24 11v14l10 6" />
          </svg>
          Envíos express
        </h2>
        <p>Vamos con la mejor cobertura y tiempos de entrega.</p>
      </article>
      <article class="tarjeta-servicio">
        <h2>
          <svg class="icono-servicio" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="3" aria-hidden="true">
            <path d="m4 14 20-9 20 9-20 9zM4 14v21l20 9 20-9V14M24 23v21M14 10l20 9" />
          </svg>
          Mercadería general
        </h2>
        <p>Repuestos, electrodomésticos, ropa y más.</p>
      </article>
      <article class="tarjeta-servicio">
        <h2>
          <svg class="icono-servicio" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2.5" aria-hidden="true">
            <circle cx="24" cy="24" r="20" />
            <path d="m30 15-7 15 10-8zM18 33l7-15-10 7z" />
          </svg>
          Seguimiento
        </h2>
        <p>Consultá el estado de tu paquete en cualquier momento.</p>
      </article>
      <article class="tarjeta-servicio">
        <h2>
          <svg class="icono-servicio" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2.5" aria-hidden="true">
            <path d="M10 27v-5a14 14 0 0 1 28 0v5M10 25H7v11h7V25h-4zm28 0h3v11h-7V25h4zM34 37a10 10 0 0 1-10 6h-5" />
            <circle cx="17" cy="43" r="2" />
          </svg>
          Atención al cliente
        </h2>
        <p>Estamos para ayudarte en lo que necesites.</p>
      </article>
    </section>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
