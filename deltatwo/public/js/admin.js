const loginPanel = document.querySelector('#staff-login-panel');
const dashboard = document.querySelector('#staff-dashboard');
const shipmentList = document.querySelector('#admin-shipment-list');
const updateForm = document.querySelector('#shipment-update-form');

if (loginPanel && dashboard) {
  const showLogin = () => {
    loginPanel.hidden = false;
    dashboard.hidden = true;
  };

  const loadShipments = async () => {
    const response = await fetch('api/panel-admin.php?action=list');
    const data = await response.json();
    if (!response.ok) throw new Error(data.mensaje || 'No se pudo cargar el panel.');
    loginPanel.hidden = true;
    dashboard.hidden = false;
    document.querySelector('#staff-greeting').textContent = `Hola, ${data.nombre}. Rol: ${data.rol.replaceAll('_', ' ').toLowerCase()}.`;
    shipmentList.replaceChildren();
    if (!data.envios.length) {
      shipmentList.textContent = 'Todavía no hay envíos activos.';
      return;
    }
    data.envios.forEach((envio) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'admin-shipment';
      const code = document.createElement('strong');
      code.textContent = `${envio.codigo_envio} · ${envio.estado.replaceAll('_', ' ')}`;
      const place = document.createElement('span');
      place.textContent = `Ubicación: ${envio.ubicacion || 'Sin movimientos cargados'}`;
      const destination = document.createElement('span');
      destination.textContent = `Destino: ${envio.destino}`;
      button.append(code, place, destination);
      button.addEventListener('click', () => {
        updateForm.elements.codigo.value = envio.codigo_envio;
        updateForm.elements.estado.value = envio.estado;
        updateForm.elements.codigo.focus();
      });
      shipmentList.append(button);
    });
  };

  document.querySelector('#staff-login-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const feedback = document.querySelector('#staff-login-feedback');
    feedback.textContent = 'Verificando acceso…';
    try {
      const response = await fetch('api/panel-admin.php?action=login', { method: 'POST', body: new FormData(event.currentTarget) });
      const data = await response.json();
      if (!response.ok) throw new Error(data.mensaje || 'No se pudo iniciar sesión.');
      event.currentTarget.reset();
      feedback.textContent = '';
      await loadShipments();
    } catch (error) {
      feedback.textContent = error.message || 'No se pudo conectar con el servidor.';
    }
  });

  updateForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const feedback = document.querySelector('#shipment-update-feedback');
    const button = updateForm.querySelector('button[type="submit"]');
    button.disabled = true;
    feedback.textContent = 'Guardando movimiento…';
    try {
      const response = await fetch('api/panel-admin.php?action=update', { method: 'POST', body: new FormData(updateForm) });
      const data = await response.json();
      if (!response.ok) throw new Error(data.mensaje || 'No se pudo guardar el movimiento.');
      feedback.textContent = data.mensaje;
      updateForm.elements.detalle.value = '';
      await loadShipments();
    } catch (error) {
      feedback.textContent = error.message || 'No se pudo conectar con el servidor.';
    } finally {
      button.disabled = false;
    }
  });

  document.querySelector('#staff-logout').addEventListener('click', async () => {
    await fetch('api/panel-admin.php?action=logout', { method: 'POST' });
    showLogin();
  });

  loadShipments().catch(showLogin);
}
