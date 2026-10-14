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

    const timeline = document.querySelector('#shipment-timeline');
    timeline.replaceChildren();
    const movimientos = envio.movimientos || [];
    if (movimientos.length === 0) {
      const item = document.createElement('li');
      item.className = 'timeline-item';
      item.textContent = `Estado actual: ${envio.estado_legible}. El recorrido se actualizará cuando el paquete pase por otra sucursal.`;
      timeline.append(item);
    } else {
      movimientos.forEach((movimiento) => {
        const item = document.createElement('li');
        item.className = 'timeline-item';
        const heading = document.createElement('strong');
        heading.textContent = movimiento.sucursal;
        const detail = document.createElement('span');
        detail.textContent = `${movimiento.detalle} · ${movimiento.provincia}`;
        const date = document.createElement('time');
        date.dateTime = movimiento.fecha_hora;
        date.textContent = new Date(movimiento.fecha_hora.replace(' ', 'T')).toLocaleString('es-AR', { dateStyle: 'medium', timeStyle: 'short' });
        item.append(heading, detail, date);
        timeline.append(item);
      });
    }
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

const trackingForm = document.querySelector('#tracking-form');
const trackingCodeFromUrl = new URLSearchParams(window.location.search).get('codigo');
if (trackingForm && trackingCodeFromUrl) {
  document.querySelector('#tracking-code').value = trackingCodeFromUrl;
  trackingForm.requestSubmit();
}
document.querySelectorAll('[data-demo-form]').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const feedback = form.querySelector('.form-feedback');
    const action = form.dataset.demoForm;
    if (action === 'contact') {
      const button = form.querySelector('button[type="submit"]');
      button.disabled = true;
      feedback.textContent = 'Enviando consulta…';
      try {
        const response = await fetch('api/contacto.php', { method: 'POST', body: new FormData(form) });
        const result = await response.json();
        if (!response.ok) throw new Error(result.mensaje || 'No pudimos enviar tu consulta.');
        feedback.textContent = result.mensaje;
        form.reset();
      } catch (error) {
        feedback.textContent = error.message || 'No se pudo conectar con el servidor.';
      } finally {
        button.disabled = false;
      }
      return;
    }
    feedback.textContent = 'Procesando…';
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;
    try {
      const response = await fetch(`api/auth.php?action=${action}`, { method: 'POST', body: new FormData(form) });
      const result = await response.json();
      if (!response.ok) throw new Error(result.mensaje || 'No pudimos procesar tus datos.');
      window.location.href = result.redirect;
    } catch (error) {
      feedback.textContent = error.message || 'No se pudo conectar con el servidor.';
    } finally {
      submit.disabled = false;
    }
  });
});

document.querySelector('#request-shipment-form')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.reportValidity()) return;
  const feedback = document.querySelector('#request-feedback');
  const button = form.querySelector('button[type="submit"]');
  button.disabled = true;
  feedback.textContent = 'Registrando la solicitud…';
  try {
    const response = await fetch('api/solicitar-envio.php', { method: 'POST', body: new FormData(form) });
    const result = await response.json();
    if (!response.ok) throw new Error(result.mensaje || 'No pudimos registrar la solicitud.');
    const precio = result.precio ? ` · Estimación: $${Number(result.precio).toLocaleString('es-AR')}` : ' · Precio a confirmar';
    feedback.textContent = `Solicitud creada. Código ${result.codigo}${precio}. Podés seguirla sin registrarte.`;
    form.reset();
    window.setTimeout(() => window.location.reload(), 1800);
  } catch (error) {
    feedback.textContent = error.message || 'No se pudo conectar con el servidor.';
  } finally {
    button.disabled = false;
  }
});
const shipmentMount = document.querySelector('#customer-shipments');
if (shipmentMount) {
  fetch('api/mis-envios.php')
    .then(async (response) => {
      const data = await response.json();
      if (!response.ok) throw new Error(data.mensaje || 'No pudimos cargar la cuenta.');
      const zoneSelect = document.querySelector('#request-zone');
      if (zoneSelect) {
        zoneSelect.replaceChildren(new Option('Elegí una zona', ''));
        (data.zonas || []).forEach((zone) => zoneSelect.add(new Option(zone.nombre, zone.id_zona)));
        if (!data.zonas?.length) zoneSelect.replaceChildren(new Option('No hay zonas habilitadas', ''));
      }
      document.querySelector('#account-greeting').textContent = `Hola, ${data.nombre}. Acá podés consultar el estado de tus envíos.`;
      if (!data.envios.length) {
        shipmentMount.textContent = 'Todavía no tenés envíos asociados a tu cuenta.';
        return;
      }
      data.envios.forEach((envio) => {
        const card = document.createElement('article');
        card.className = 'customer-shipment-card';
        const title = document.createElement('h2');
        title.textContent = envio.codigo_envio;
        const state = document.createElement('p');
        state.textContent = `Estado: ${envio.estado.replaceAll('_', ' ').toLowerCase()}`;
        const destination = document.createElement('p');
        destination.textContent = `Destino: ${envio.destino} · ${envio.destinatario}`;
        const estimated = document.createElement('p');
        estimated.textContent = `Entrega estimada: ${envio.fecha_entrega_estimada || 'A confirmar'}`;
        const link = document.createElement('a');
        link.className = 'text-link';
        link.href = `seguimiento.html?codigo=${encodeURIComponent(envio.codigo_envio)}`;
        link.textContent = 'Ver seguimiento →';
        card.append(title, state, destination, estimated, link);
        shipmentMount.append(card);
      });
    })
    .catch((error) => {
      document.querySelector('#account-greeting').textContent = error.message;
      if (error.message.includes('sesión')) window.setTimeout(() => { window.location.href = 'iniciar-sesion.html'; }, 1200);
    });
}

document.querySelector('[data-logout]')?.addEventListener('click', async () => {
  await fetch('api/auth.php?action=logout', { method: 'POST' });
  window.location.href = 'index.html';
});




