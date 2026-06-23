// ============================================================
// TU - Main JavaScript
// ============================================================

const TU = {

  // ---- Toast Notifications ----
  toast(msg, type = 'info', duration = 3500) {
    const container = document.getElementById('toast-container') || (() => {
      const c = document.createElement('div');
      c.id = 'toast-container';
      document.body.appendChild(c);
      return c;
    })();

    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <span class="toast-msg">${msg}</span>
      <span class="toast-close" onclick="this.parentElement.remove()">✕</span>`;
    container.appendChild(t);
    setTimeout(() => t.style.opacity = '0', duration);
    setTimeout(() => t.remove(), duration + 400);
  },

  // ---- Modal ----
  modal: {
    open(id) {
      const el = document.getElementById(id);
      if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
    },
    close(id) {
      const el = id ? document.getElementById(id) : document.querySelector('.modal-overlay.open');
      if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
    },
    closeAll() {
      document.querySelectorAll('.modal-overlay.open').forEach(m => {
        m.classList.remove('open');
      });
      document.body.style.overflow = '';
    }
  },

  // ---- Tabs ----
  initTabs(containerSel) {
    const containers = document.querySelectorAll(containerSel || '.tabs-container');
    containers.forEach(container => {
      const buttons = container.querySelectorAll('.tab-btn');
      buttons.forEach(btn => {
        btn.addEventListener('click', () => {
          buttons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const target = btn.dataset.tab;
          container.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
          const tc = container.querySelector(`[data-tab-content="${target}"]`);
          if (tc) tc.classList.add('active');
        });
      });
    });
  },

  // ---- Sidebar toggle ----
  initSidebar() {
    const btn = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (!btn || !sidebar) return;
    btn.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
      if (sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) && !btn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  },

  // ---- Search & Filter Table ----
  initSearch(inputSel, tableSel) {
    const input = document.querySelector(inputSel);
    const table = document.querySelector(tableSel);
    if (!input || !table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  },

  // ---- Confirm Dialog ----
  confirm(msg, onYes, onNo = null) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay open';
    overlay.innerHTML = `
      <div class="modal" style="max-width:420px">
        <div class="modal-body" style="text-align:center;padding:30px 24px">
          <div style="font-size:2.5rem;margin-bottom:16px">⚠️</div>
          <h4 style="font-size:1rem;margin-bottom:8px">Konfirmasi</h4>
          <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:24px">${msg}</p>
          <div style="display:flex;gap:10px;justify-content:center">
            <button class="btn btn-light" id="confirm-no">Batal</button>
            <button class="btn btn-danger" id="confirm-yes">Ya, Lanjutkan</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    overlay.querySelector('#confirm-yes').onclick = () => { overlay.remove(); onYes(); };
    overlay.querySelector('#confirm-no').onclick  = () => { overlay.remove(); if (onNo) onNo(); };
  },

  // ---- Loading button ----
  btnLoading(btn, loading = true) {
    if (loading) {
      btn._orig = btn.innerHTML;
      btn.innerHTML = '<span class="spinner"></span> Memproses...';
      btn.disabled = true;
    } else {
      btn.innerHTML = btn._orig || btn.innerHTML;
      btn.disabled = false;
    }
  },

  // ---- API Request ----
  async api(endpoint, data = {}, method = 'POST') {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (method !== 'GET') opts.body = JSON.stringify(data);
    const res = await fetch(endpoint, opts);
    return res.json();
  },

  // ---- File to Base64 ----
  fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result.split(',')[1]);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  },

  // ---- Avatar Preview ----
  initAvatarPreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;
    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 2 * 1024 * 1024) { TU.toast('Ukuran foto max 2MB', 'error'); return; }
      const reader = new FileReader();
      reader.onload = ev => { preview.src = ev.target.result; };
      reader.readAsDataURL(file);
    });
  },

  // ---- Format date ----
  formatDate(str) {
    if (!str) return '-';
    const d = new Date(str);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
  },

  // ---- Pagination ----
  paginate(data, page, perPage = 15) {
    const total = data.length;
    const pages = Math.ceil(total / perPage);
    const start = (page - 1) * perPage;
    return {
      items:   data.slice(start, start + perPage),
      page, perPage, total, pages,
      hasPrev: page > 1,
      hasNext: page < pages,
    };
  },

  renderPagination(container, current, total, onClick) {
    if (!container) return;
    let html = '';
    const add = (p, label = p, disabled = false, active = false) => {
      html += `<button class="page-btn ${active ? 'active' : ''}" ${disabled ? 'disabled' : ''}
               onclick="(${onClick})(${p})">${label}</button>`;
    };
    add(current - 1, '‹', current <= 1);
    const range = 2;
    for (let i = 1; i <= total; i++) {
      if (i === 1 || i === total || (i >= current - range && i <= current + range)) {
        add(i, i, false, i === current);
      } else if (i === current - range - 1 || i === current + range + 1) {
        html += `<span class="page-btn" style="border:none;background:none;cursor:default">…</span>`;
      }
    }
    add(current + 1, '›', current >= total);
    container.innerHTML = html;
  },

  // ---- Export CSV ----
  exportCSV(data, filename = 'data.csv') {
    if (!data.length) return;
    const headers = Object.keys(data[0]);
    const rows = data.map(r => headers.map(h => `"${(r[h] || '').toString().replace(/"/g, '""')}"`).join(','));
    const csv = [headers.join(','), ...rows].join('\n');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
  },

  // ---- Print ----
  printElement(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const w = window.open('', '_blank', 'width=900,height=700');
    w.document.write(`<html><head><title>Print</title>
      <link rel="stylesheet" href="assets/css/style.css">
      <style>body{padding:20px;background:#fff} .no-print{display:none}</style>
      </head><body>${el.innerHTML}</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); w.close(); }, 500);
  },

  // ---- Init all ----
  init() {
    this.initSidebar();
    this.initTabs();
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', e => {
        if (e.target === overlay) this.modal.close();
      });
    });
    // ESC to close modal
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') this.modal.closeAll();
    });
    // Active menu
    const path = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.menu-item').forEach(item => {
      const href = item.getAttribute('href') || '';
      if (href && href.includes(path)) item.classList.add('active');
    });
  }
};

document.addEventListener('DOMContentLoaded', () => TU.init());

// ============================================================
// MySQL CRUD API Client
// ============================================================
const GS = {
  async call(action, payload = {}) {
    payload.action = action;
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch('api/crud.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify(payload)
    });
    return res.json();
  },

  async getData(table, filters = {}) {
    return this.call('getData', { table, filters, page: filters.page || 1, per_page: filters.per_page || 9999 });
  },

  async addRow(table, data) {
    return this.call('addRow', { table, data });
  },

  async updateRow(table, rowId, data) {
    return this.call('updateRow', { table, rowId, data });
  },

  async deleteRow(table, rowId) {
    return this.call('deleteRow', { table, rowId });
  },

  async findRow(table, key, value) {
    return this.call('findRow', { table, key, value });
  },

  // ---- Client-side Table Sorting ----
  _sortState: {},

  sortData(arr, key, dir) {
    if (!arr || !arr.length) return arr;
    return [...arr].sort((a, b) => {
      let va = (a[key] ?? '').toString().toLowerCase();
      let vb = (b[key] ?? '').toString().toLowerCase();
      // Try numeric comparison if both look numeric
      if (!isNaN(parseFloat(va)) && !isNaN(parseFloat(vb))) {
        va = parseFloat(va); vb = parseFloat(vb);
        return dir === 'asc' ? va - vb : vb - va;
      }
      if (va < vb) return dir === 'asc' ? -1 : 1;
      if (va > vb) return dir === 'asc' ? 1 : -1;
      return 0;
    });
  },

  toggleSort(tableId, key, callback) {
    const stateKey = tableId + '_' + key;
    const current = this._sortState[stateKey] || 'none';
    // Reset all sorts for this table
    Object.keys(this._sortState).forEach(k => {
      if (k.startsWith(tableId + '_')) delete this._sortState[k];
    });
    const next = current === 'asc' ? 'desc' : 'asc';
    this._sortState[stateKey] = next;
    this.renderSortIndicator(tableId, key, next);
    if (callback) callback(key, next);
  },

  renderSortIndicator(tableId, activeKey, dir) {
    const table = document.getElementById(tableId);
    if (!table) return;
    table.querySelectorAll('th .sort-arrow').forEach(el => el.remove());
    const ths = table.querySelectorAll('th[data-sort]');
    ths.forEach(th => {
      const arrow = document.createElement('span');
      arrow.className = 'sort-arrow';
      if (th.dataset.sort === activeKey) {
        arrow.textContent = dir === 'asc' ? ' ▲' : ' ▼';
        th.classList.add('sorted');
      } else {
        arrow.textContent = ' ⇅';
        th.classList.remove('sorted');
      }
      th.appendChild(arrow);
    });
  }
};

// ============================================================
// Theme Toggle (Light / Dark)
// ============================================================
function initTheme() {
  const saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  updateThemeIcon(saved);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  updateThemeIcon(next);
}

function updateThemeIcon(theme) {
  const btn = document.getElementById('theme-toggle');
  if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}

// Apply theme immediately on script load to prevent flash
initTheme();
