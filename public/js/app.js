// controla el menú desplegable en pantallas pequeñas.
// actualiza los atributos accesibles para anunciar el estado del botón.
const botonMenu = document.querySelector('.boton-menu');
const navegacion = document.querySelector('.enlaces-navegacion');

botonMenu?.addEventListener('click', () => {
  const estaAbierto = navegacion.classList.toggle('abierto');
  botonMenu.setAttribute('aria-expanded', String(estaAbierto));
  botonMenu.setAttribute('aria-label', estaAbierto ? botonMenu.dataset.labelClose : botonMenu.dataset.labelOpen);
});
