// Controla el menú desplegable en pantallas pequeñas.
const botonMenu = document.querySelector('.boton-menu');
const navegacion = document.querySelector('.enlaces-navegacion');

botonMenu?.addEventListener('click', () => {
  const estaAbierto = navegacion.classList.toggle('abierto');
  botonMenu.setAttribute('aria-expanded', String(estaAbierto));
});
