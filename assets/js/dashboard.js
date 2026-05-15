/* =============================================
   assets/js/dashboard.js
   Lógica de tabs, tema, idioma, rol y logout
   ============================================= */

'use strict';

(function () {

    // ── Helpers ───────────────────────────────
    const $ = (sel) => document.querySelector(sel);
    const COOKIE_DAYS = 30;

    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/;SameSite=Strict`;
    }

    function showToast() {
        const toast = $('#toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2200);
    }

    // ── Tabs ──────────────────────────────────
    const navItems  = document.querySelectorAll('.nav-item[data-tab]');
    const tabPanels = document.querySelectorAll('.tab-panel');

    navItems.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            navItems.forEach(b => { b.classList.remove('active'); b.removeAttribute('aria-current'); });
            tabPanels.forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            btn.setAttribute('aria-current', 'page');

            const panel = document.getElementById(`tab-${target}`);
            if (panel) panel.classList.add('active');
        });
    });

    // ── Tema ──────────────────────────────────
    const switchTema = $('#switchTema');
    const temaLink   = $('#tema-css');
    const temaLabel  = $('#temaLabel');

    if (switchTema) {
        switchTema.addEventListener('change', () => {
            const nuevoTema = switchTema.checked ? 'oscuro' : 'claro';
            // Cambiar hoja de estilos
            temaLink.href = `assets/css/dashboard_${nuevoTema}.css`;
            document.body.className = `tema-${nuevoTema}`;
            // Guardar en cookie (30 días) — RF-1
            setCookie('tema', nuevoTema, COOKIE_DAYS);
            if (temaLabel) temaLabel.textContent = switchTema.checked ? 'Oscuro' : 'Claro';
            showToast();
        });
    }

    // ── Idioma ────────────────────────────────
    const switchIdioma = $('#switchIdioma');

    if (switchIdioma) {
        switchIdioma.addEventListener('change', () => {
            const nuevoIdioma = switchIdioma.checked ? 'en' : 'es';
            setCookie('idioma', nuevoIdioma, COOKIE_DAYS);
            // Recargar la página para aplicar cambios de idioma desde PHP
            window.location.reload();
        });
    }

    // ── Cambiar Rol ───────────────────────────
    const btnGuardarRol = $('#btnGuardarRol');
    const selectRol     = $('#selectRol');
    const rolActual     = $('#rolActual');

    if (btnGuardarRol && selectRol) {
        btnGuardarRol.addEventListener('click', async () => {
            const nuevoRol = selectRol.value;

            try {
                const formData = new FormData();
                formData.append('accion', 'cambiar_rol');
                formData.append('rol', nuevoRol);

                const res  = await fetch('dashboard.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.ok) {
                    if (rolActual) rolActual.textContent = data.rol;
                    // Actualizar badge en la card de dashboard
                    const badge = document.querySelector('.badge');
                    if (badge) {
                        badge.className = `badge badge-${data.rol}`;
                        badge.textContent = data.rol;
                    }
                    showToast();
                } else {
                    alert(data.msg || 'Error al cambiar el rol.');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('No se pudo conectar al servidor.');
            }
        });
    }

    // ── Logout ────────────────────────────────
    const btnLogout          = $('#btnLogout');
    const modalLogout        = $('#modalLogout');
    const btnCancelarLogout  = $('#btnCancelarLogout');
    const btnConfirmarLogout = $('#btnConfirmarLogout');

    function abrirModal()  { modalLogout.classList.add('show'); btnConfirmarLogout.focus(); }
    function cerrarModal() { modalLogout.classList.remove('show'); }

    if (btnLogout) btnLogout.addEventListener('click', abrirModal);
    if (btnCancelarLogout) btnCancelarLogout.addEventListener('click', cerrarModal);

    // Cerrar al click fuera del modal
    if (modalLogout) {
        modalLogout.addEventListener('click', (e) => {
            if (e.target === modalLogout) cerrarModal();
        });
    }

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalLogout.classList.contains('show')) cerrarModal();
    });

    if (btnConfirmarLogout) {
        btnConfirmarLogout.addEventListener('click', async () => {
            try {
                const formData = new FormData();
                formData.append('accion', 'logout');

                const res  = await fetch('dashboard.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.ok) {
                    window.location.href = 'login.php';
                }
            } catch (err) {
                // Fallback: redirigir de todas formas
                window.location.href = 'login.php';
            }
        });
    }

})();