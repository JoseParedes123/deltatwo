const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.nav-links');

const footerMount = document.querySelector('[data-shared-footer]');
if (footerMount) {
  const footerUrl = new URL('Footer.html', document.currentScript.src);
  fetch(footerUrl)
    .then((response) => {
      if (!response.ok) throw new Error(`No se pudo cargar el pie: ${response.status}`);
      return response.text();
    })
    .then((footerMarkup) => {
      footerMount.innerHTML = footerMarkup;
      footerMount.querySelector('[data-current-year]').textContent = new Date().getFullYear();
    })
    .catch((error) => console.error(error));
}

menuButton?.addEventListener('click', () => {
  const isOpen = navigation.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
});

document.querySelector('#tracking-form')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const code = document.querySelector('#tracking-code').value.trim().toUpperCase();
  const message = document.querySelector('#tracking-message');
  const details = document.querySelector('#shipment-card');
  const knownCodes = ['DEL-000245', 'DLT-1042', 'DLT-1043'];

  if (!knownCodes.includes(code)) {
    message.textContent = code ? 'No encontramos ese envío. Revisá el código e intentá de nuevo.' : 'Ingresá el código de tu envío para continuar.';
    details.hidden = true;
    return;
  }

  message.textContent = '';
  details.hidden = false;
  document.querySelector('#shipment-code').textContent = code;
});

document.querySelectorAll('[data-demo-form]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const feedback = form.querySelector('.form-feedback');
    if (form.reportValidity()) {
      feedback.textContent = form.dataset.demoForm === 'contact'
        ? 'Gracias. Tu mensaje quedó listo para enviar.'
        : 'Formulario completado. Esta maqueta no conecta con un servidor.';
      form.reset();
    }
  });
});