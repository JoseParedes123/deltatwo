<?php
$paginaActual = 'inicio';
require_once __DIR__ . '/../app/helpers/funciones.php';
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#073486">
	<title>Logística Delta | Envíos rápidos y seguros</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<?php include __DIR__ . '/partials/nav.php'; ?>
	<main>
		<section class="portada-inicio" aria-labelledby="home-title">
			<div class="contenedor marca-destacada"><div><h1 id="home-title"><span>Logística</span><span>Delta</span></h1><p>Envíos rápidos y seguros</p></div></div>
		</section>
		<section class="contenedor seccion-dividida">
			<div><h2>Sobre nosotros</h2><p>Somos Logística Delta, una empresa dedicada al transporte y distribución de paquetes. Contamos con un equipo de profesionales y tecnología para brindar un servicio seguro, eficiente y personalizado.</p></div>
			<img class="foto-recortada inclinada" src="media/photo-1566576721346-d4a3b4eaeb55.jpeg" alt="Paquetes listos para su distribución">
		</section>
		<div class="franja-inicio">
			<section class="contenedor seccion-dividida">
				<img class="foto-recortada foto-redondeada" src="media/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1100&q=85" alt="Camión de carga en ruta">
			<div><h2>Nuestro alcance</h2><p>Conectamos sucursales y centros de distribución en distintas provincias para que tu paquete recorra Argentina de forma segura.</p></div>
			</section>
		</div>
		<section class="contenedor seccion-dividida">
			<div><h2>Tu envío, siempre a la vista</h2><p>Consultá el recorrido y el estado actualizado de tu paquete con el código de seguimiento.</p><p><a class="enlace-texto" href="seguimiento.php">Seguir un paquete →</a></p></div>
			<img class="foto-recortada foto-redondeada" src="media/photo-1580674285054-bed31e145f59?auto=format&fit=crop&w=1100&q=85" alt="Operación de preparación de envíos">
		</section>
	</main>
	<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
