<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MI CRUD DE CLIENTES</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; }
    h1 { margin-bottom: 8px; }
    form, table { margin-top: 16px; }
    label { display: block; margin: 6px 0 2px; }
    input { padding: 6px 8px; width: 100%; max-width: 360px; }
    button { padding: 8px 12px; margin-top: 10px; cursor: pointer; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f5f5f5; text-align: left; }
    .row-actions button { margin-right: 6px; }
    .muted { color: #666; font-size: 12px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
    .card { border: 1px solid #e5e5e5; padding: 16px; border-radius: 8px; }
    .success { color: #0a7a46; }
    .error { color: #a11111; white-space: pre-wrap; }
  </style>
</head>
<body>
  <h1>Clientes</h1>
  <p class="muted">CRUD DE CLIENTES <code>/api/clients</code>.</p>

  <div class="grid">
    <div class="card">
      <h3>CREAR O ACTUALIZAR CLIENTE</h3>
      <form id="client-form">
        <input type="hidden" id="client-id" />
        <label for="name">Nombre</label>
        <input id="name" placeholder="Nombre" required />

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" placeholder="edras@gmail.com" required />

        <label for="phone">Teléfono</label>
        <input id="phone" placeholder="Número de teléfono" />

        <label for="address">Dirección</label>
        <input id="address" placeholder="Dirección" />

        <button type="submit">Guardar</button>
        <button type="button" id="reset-btn">Limpiar</button>
        <div id="form-msg"></div>
      </form>
    </div>

    <div class="card" style="overflow:auto">
      <h3>LISTADO DE CLIENTES</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo electrónico</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="clients-body"></tbody>
      </table>
      <div id="list-msg"></div>
    </div>
  </div>

  <script>
    const api = '/api/clients'; 
    let currentListUrl = api;

    const q = (s) => document.querySelector(s);
    const bodyEl = q('#clients-body');
    const form = q('#client-form');
    const resetBtn = q('#reset-btn');
    const formMsg = q('#form-msg');
    const listMsg = q('#list-msg');
    async function fetchClients(url = api) {
      listMsg.textContent = '';
      try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('Error al cargar lista: ' + res.status);
        const items = await res.json();
        renderTable(items);
      } catch (err) {
        listMsg.innerHTML = `<span class="error">${err.message}</span>`;
      }
    }

    function renderTable(items) {
      bodyEl.innerHTML = items.map(c => `
        <tr>
          <td>${c.id}</td>
          <td>${c.name ?? ''}</td>
          <td>${c.email ?? ''}</td>
          <td>${c.phone ?? ''}</td>
          <td>${c.address ?? ''}</td>
          <td class="row-actions">
            <button onclick=\"editClient(${c.id})\">Editar</button>
            <button onclick=\"deleteClient(${c.id})\">Eliminar</button>
          </td>
        </tr>
      `).join('');
    }

    window.editClient = async function(id) {
      try {
        const res = await fetch(`${api}/${id}`);
        if (!res.ok) throw new Error('No se pudo cargar el cliente.');
        const c = await res.json();
        q('#client-id').value = c.id;
        q('#name').value = c.name || '';
        q('#email').value = c.email || '';
        q('#phone').value = c.phone || '';
        q('#address').value = c.address || '';
        formMsg.innerHTML = '<span class="muted">EDITANDO EL CLIENTE #' + c.id + '</span>';
      } catch (e) {
        formMsg.innerHTML = `<span class="error">${e.message}</span>`;
      }
    }

    window.deleteClient = async function(id) {
      if (!confirm('¿Eliminar cliente #' + id + '?')) return;
      formMsg.textContent = '';
      try {
        const res = await fetch(`${api}/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('No se pudo eliminar');
        formMsg.innerHTML = '<span class="success">Eliminado</span>';
        await fetchClients(currentListUrl);
      } catch (e) {
        formMsg.innerHTML = `<span class="error">${e.message}</span>`;
      }
    }

    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      formMsg.textContent = '';

      const payload = {
        name: q('#name').value.trim(),
        email: q('#email').value.trim(),
        phone: q('#phone').value.trim() || null,
        address: q('#address').value.trim() || null,
      };

      const id = q('#client-id').value;
      const method = id ? 'PUT' : 'POST';
      const url = id ? `${api}/${id}` : api;

      try {
        const res = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) {
          const msg = await res.text();
          throw new Error('Error al guardar: ' + msg);
        }
        formMsg.innerHTML = '<span class="success">Guardado</span>';
        resetForm();
        await fetchClients(currentListUrl);
      } catch (e) {
        formMsg.innerHTML = `<span class="error">${e.message}</span>`;
      }
    });

    function resetForm() {
      q('#client-id').value = '';
      q('#name').value = '';
      q('#email').value = '';
      q('#phone').value = '';
      q('#address').value = '';
    }

    resetBtn.addEventListener('click', () => {
      resetForm();
      formMsg.textContent = '';
    });

    fetchClients();
  </script>
</body>
</html>
