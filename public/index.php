<?php
$paginaActual = 'inicio';
require_once __DIR__ . '/../app/helpers/funciones.php';
?>
<!doctype html>
<html lang="<?= limpiar(idiomaActual()) ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#073486">
	<title>Logística Delta | <?= limpiar(t('Envíos rápidos y seguros')) ?></title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<?php include __DIR__ . '/partials/nav.php'; ?>
	<main>
		<section class="portada-inicio" aria-labelledby="home-title">
			<div class="contenedor marca-destacada"><div><h1 id="home-title"><span><?= limpiar(t('Logística')) ?></span><span>Delta</span></h1><p><?= limpiar(t('Envíos rápidos y seguros')) ?></p><a class="boton-principal boton-seguimiento-portada" href="seguimiento.php"><?= limpiar(t('Seguí tu envío')) ?></a></div></div>
		</section>
		<section class="contenedor seccion-dividida">
			<div><h2><?= limpiar(t('Sobre nosotros')) ?></h2><p><?= limpiar(t('Somos Logística Delta, una empresa dedicada al transporte y distribución de paquetes. Contamos con un equipo de profesionales y tecnología para brindar un servicio seguro, eficiente y personalizado.')) ?></p></div>
			<img class="foto-recortada inclinada" src="media/photo-1566576721346-d4a3b4eaeb55.jpeg" alt="<?= limpiar(t('Paquetes listos para su distribución')) ?>">
		</section>
		<div class="franja-inicio">
			<section class="contenedor seccion-dividida">
			<img class="foto-recortada foto-redondeada" src="media/photo-1580674285054-bed31e145f59.jpeg" alt="<?= limpiar(t('Camión de carga en ruta')) ?>">
			<div><h2><?= limpiar(t('Nuestro alcance')) ?></h2><p><?= limpiar(t('Conectamos distintas localidades de la provincia de Buenos Aires para que tu paquete llegue de forma segura.')) ?></p></div>
			</section>
		</div>
		<section class="contenedor seccion-dividida">
			<div><h2><?= limpiar(t('Tu envío, siempre a la vista')) ?></h2><p><?= limpiar(t('Consultá el recorrido y el estado actualizado de tu paquete con el código de seguimiento.')) ?></p><p><a class="enlace-texto" href="seguimiento.php"><?= limpiar(t('Seguir un paquete →')) ?></a></p></div>
			<img class="foto-recortada foto-redondeada" src="media/photo-1601584115197-04ecc0da31d7.jpeg" alt="<?= limpiar(t('Operación de preparación de envíos')) ?>">
		</section>
	</main>
	<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
