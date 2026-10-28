<?php require_once __DIR__ . '/../app/helpers/funciones.php'; ?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#073486">
  <title><?= limpiar(t('Contactanos')) ?> | Logística Delta</title>
  <link rel="stylesheet" href="css/style.css">
</head>
  <link rel="icon" type="image/png" href="media/favicon.png">
<body>
  <?php include __DIR__ . '/partials/nav.php'; ?>
  <main class="pagina-redes">
<!-- Reúne los accesos externos y los datos de contacto de la empresa. -->
<section class="contenedor contenido-redes">
      <h1><?= limpiar(t('Contactanos')) ?></h1>
      <nav class="enlaces-redes" aria-label="<?= limpiar(t('Redes y correo')) ?>">
        <a href="https://www.facebook.com/" target="_blank" rel="noreferrer">
          <span class="icono-redes" aria-hidden="true">f</span>
          Logistica.delta
        </a>
        <a href="https://www.instagram.com/" target="_blank" rel="noreferrer">
          <span class="icono-redes" aria-hidden="true">◎</span>
          @Logistica.delta.oficial
        </a>
        <a href="mailto:logistica.delta@gmail.com">
          <span class="icono-redes" aria-hidden="true">✉</span>
          logistica.delta@gmail.com
        </a>
      </nav>
    </section>
    <div class="ilustracion-recorrido" aria-hidden="true">
      <span>▤</span>
      <span>⌖</span>
      <span>⌂</span>
    </div>
  </main>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
