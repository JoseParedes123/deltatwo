const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.nav-links');

const footerMount = document.querySelector('[data-shared-footer]');
if (footerMount) {
  const footerUrl = new URL('../partials/Footer.html', document.currentScript.src);
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

document.querySelector('#tracking-form')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const code = document.querySelector('#tracking-code').value.trim().toUpperCase();
  const message = document.querySelector('#tracking-message');
  const details = document.querySelector('#shipment-card');
  const button = event.currentTarget.querySelector('button[type="submit"]');

  if (!code) {
    message.textContent = 'Ingresá el código de tu envío para continuar.';
    details.hidden = true;
    return;
  }

  message.textContent = 'Consultando envío...';
  details.hidden = true;
  button.disabled = true;

  try {
    const data = new FormData();
    data.append('codigo', code);
    const endpoint = new URL('consultar_seguimiento.php', window.location.href);
    const response = await fetch(endpoint, { method: 'POST', body: data });
    const resultado = await response.json();

    if (!response.ok) {
      message.textContent = resultado.mensaje || 'No encontramos ese envío.';
      return;
    }

    const envio = resultado.envio;
    document.querySelector('#shipment-code').textContent = envio.codigo;
    document.querySelector('#shipment-state').textContent = envio.estado_legible;
    document.querySelector('#shipment-origin').textContent = envio.origen;
    document.querySelector('#shipment-destination').textContent = envio.destino;
    document.querySelector('#shipment-date').textContent = envio.fecha_solicitud;
    document.querySelector('#shipment-estimated').textContent = envio.fecha_entrega_estimada || 'A confirmar';
    document.querySelector('#shipment-package').textContent = envio.descripcion_paquete || 'Sin descripción';
    document.querySelector('#shipment-weight').textContent = envio.peso_kg ? `${envio.peso_kg} kg` : 'No informado';

    const etapasPorEstado = {
      REGISTRADO: 0,
      RECIBIDO_DEPOSITO: 1,
      PREPARADO: 2,
      EN_TRANSITO: 3,
      EN_REPARTO: 3,
      ENTREGADO: 4,
      NO_ENTREGADO: 3,
      DEVUELTO: 3,
      EXTRAVIADO: 3,
    };
    const etapaActual = etapasPorEstado[envio.estado] ?? -1;

    document.querySelectorAll('.progress-step').forEach((step, index) => {
      step.classList.toggle('current', index <= etapaActual);
      if (index === etapaActual) {
        step.setAttribute('aria-current', 'step');
      } else {
        step.removeAttribute('aria-current');
      }
    });

    message.textContent = '';
    details.hidden = false;
  } catch (error) {
    message.textContent = 'No se pudo conectar con el servidor. Verificá que Apache y MySQL estén activos.';
  } finally {
    button.disabled = false;
  }
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