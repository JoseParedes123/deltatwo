<!-- pie reutilizado por las páginas públicas y de cuenta. -->
<footer class="pie-compartido">
  <!-- el pie reúne enlaces institucionales y carga el menú adaptable. -->
  <div class="contenedor grilla-pie">
    <div class="marca-pie">
      <a class="marca" href="index.php" aria-label="Logística Delta, inicio">
      <img src="./media/Logistica_Delta.svg" width="50" height="50" alt="">
        <strong><?= limpiar(t('LOGÍSTICA DELTA')) ?></strong></a>
      <p><?= limpiar(t('Envíos rápidos y seguros en la provincia de Buenos Aires.')) ?></p>
    </div>
    <nav class="navegacion-pie" aria-label="<?= limpiar(t('Enlaces del pie de página')) ?>">
      <a href="index.php"><?= limpiar(t('Inicio')) ?></a><a href="servicios.php"><?= limpiar(t('Servicios')) ?></a><a href="seguimiento.php"><?= limpiar(t('Seguimiento')) ?></a><a href="contacto.php"><?= limpiar(t('Contacto')) ?></a>
    </nav>
    <div class="contacto-pie"><strong><?= limpiar(t('Hablemos')) ?></strong><a href="mailto:logistica.delta@gmail.com">logistica.delta@gmail.com</a><a href="tel:+541145678900">+54 11 4567-8900</a><a href="redes.php"><?= limpiar(t('Redes sociales')) ?></a></div>
  </div>
  <div class="contenedor base-pie"><span>© <?= date('Y') ?> Logística Delta</span><span><?= limpiar(t('Seguimos cada envío, de principio a fin.')) ?></span></div>
</footer>
<script src="js/app.js"></script>
