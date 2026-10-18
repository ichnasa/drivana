/* Drivana UI behaviours — small, dependency-free, driven by data-* attributes. */
(() => {
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const rupiah = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  const reduced = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

  /* ───────────── Form validation (client side; the server validates again) ───────────── */
  const ALERT_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="icon"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';
  const SKIP = ['hidden', 'submit', 'button', 'reset'];
  const inputsOf = (root) => $$('input, select, textarea', root).filter((i) => !SKIP.includes(i.type) && !i.disabled);
  const fieldOf = (i) => i.closest('.field') || i.parentElement;
  const boxOf = (i) => i.closest('.input, .upload, .selfie, .file-row');
  const errOf = (i) => { const f = fieldOf(i); return f && f.querySelector('.field-error'); };
  const labelOf = (i) => {
    if (i.dataset.label) return i.dataset.label;
    if (i.getAttribute('aria-label')) return i.getAttribute('aria-label');
    const row = i.closest('.file-row');
    if (row && row.querySelector('b')) return row.querySelector('b').textContent.trim();
    const f = fieldOf(i);
    const l = f && f.querySelector('label:not(.upload):not(.selfie):not(.file-row):not(.checkline)');
    return l ? l.textContent.trim() : 'Kolom ini';
  };

  /** Returns an Indonesian error message, or '' when the value is fine. */
  const check = (i) => {
    const label = labelOf(i);
    if (i.type === 'checkbox') return i.required && !i.checked ? 'Centang untuk melanjutkan.' : '';
    if (i.type === 'radio') {
      const group = $$('input[type=radio][name="' + i.name + '"]', i.form || document);
      return i.required && !group.some((r) => r.checked) ? 'Pilih ' + (i.closest('[data-label]') ? i.closest('[data-label]').dataset.label.toLowerCase() : 'salah satu') + ' dulu.' : '';
    }
    if (i.type === 'file') {
      if (i.dataset.busy) return label + ' sedang diproses, tunggu sebentar.';
      if (!i.files || !i.files.length) return i.required ? label + ' wajib diunggah.' : '';
      const f = i.files[0];
      const mb = (n) => (n / 1048576).toFixed(1).replace('.', ',');
      const allowed = (i.dataset.types || 'jpeg,png').split(',');
      const okType = allowed.some((n) => f.type === 'image/' + n || (n === 'pdf' && f.type === 'application/pdf'));
      if (!okType) return label + ' harus berformat ' + allowed.map((n) => (n === 'jpeg' ? 'JPG' : n.toUpperCase())).join(', ') + '.';
      const max = Number(i.dataset.maxMb || 0);
      if (max && f.size > max * 1048576) return label + ' maksimal ' + max + ' MB (file kamu ' + mb(f.size) + ' MB).';
      return '';
    }
    const v = (i.value || '').trim();
    if (i.required && v === '') return label + ' wajib diisi.';
    if (v === '') return '';
    if (i.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return i.dataset.error || 'Format email tidak valid.';
    if (i.minLength > 0 && v.length < i.minLength) return label + ' minimal ' + i.minLength + ' karakter.';
    if (i.pattern && !new RegExp('^(?:' + i.pattern + ')$').test(v)) return i.dataset.error || 'Format tidak sesuai.';
    if (i.type === 'date' && i.min && v < i.min) return i.dataset.error || 'Tanggal tidak valid.';
    if (i.dataset.match) {
      const other = document.querySelector(i.dataset.match);
      if (other && v !== other.value) return i.dataset.matchError || 'Tidak sama.';
    }
    if (i.dataset.min && Number(v) < Number(i.dataset.min)) return i.dataset.minError || 'Nilai terlalu kecil.';
    if (i.dataset.gte) {
      const other = document.querySelector(i.dataset.gte);
      if (other && other.value.trim() !== '' && Number(v) < Number(other.value)) return i.dataset.gteError || 'Nilai terlalu kecil.';
    }
    return '';
  };

  const show = (i, msg) => {
    const box = boxOf(i), err = errOf(i);
    if (box) box.classList.toggle('has-error', !!msg);
    if (msg) i.setAttribute('aria-invalid', 'true'); else i.removeAttribute('aria-invalid');
    if (err) {
      err.replaceChildren();
      if (msg) {
        const tpl = document.createElement('template');
        tpl.innerHTML = ALERT_ICON;
        err.append(tpl.content.firstChild, document.createTextNode(' ' + msg));
      }
    }
  };

  /** Validate every field inside root; returns the first invalid input (or null). */
  const validateScope = (root) => {
    let first = null;
    inputsOf(root).forEach((i) => {
      const m = check(i);
      show(i, m);
      if (m && !first) first = i;
    });
    return first;
  };

  // Multi-step flows keep their own show() here so validation can reveal the right step
  $$('[data-steps]').forEach((flow) => {
    const panes = $$('[data-step-pane]', flow);
    const dots = $$('.stepper .s', flow);
    const bars = $$('.stepper .bar', flow);
    let current = 0;
    const showStep = (i) => {
      current = Math.max(0, Math.min(panes.length - 1, i));
      panes.forEach((p, n) => p.classList.toggle('is-active', n === current));
      dots.forEach((d, n) => {
        d.classList.toggle('is-current', n === current);
        d.classList.toggle('is-done', n < current);
        d.innerHTML = n < current ? '✓' : String(n + 1);
      });
      bars.forEach((b, n) => b.classList.toggle('is-done', n < current));
      window.scrollTo({ top: 0 });
    };
    flow.showStep = showStep;
    flow.paneIndexOf = (el) => panes.findIndex((p) => p.contains(el));
    // "Lanjut" only advances when the current step is valid
    $$('[data-next]', flow).forEach((b) => b.addEventListener('click', () => {
      const bad = validateScope(panes[current]);
      if (bad) { focusInvalid(bad); return; }
      showStep(current + 1);
    }));
    $$('[data-prev]', flow).forEach((b) => b.addEventListener('click', () => showStep(current - 1)));
    // After a server-side failure, open the first step that has an error
    const failed = panes.findIndex((p) => $$('.field-error', p).some((e) => e.textContent.trim() !== ''));
    showStep(failed > -1 ? failed : 0);
  });

  const focusInvalid = (el) => {
    const flow = el.closest('[data-steps]');
    if (flow && flow.showStep) flow.showStep(flow.paneIndexOf(el));
    const target = el.type === 'file' || el.type === 'checkbox' ? boxOf(el) || el : el;
    if (el.type === 'file' && target) target.scrollIntoView({ block: 'center' });
    else if (el.focus) el.focus();
  };

  $$('form').forEach((form) => {
    if (form.matches('[data-chat-form]')) return;
    const ruled = inputsOf(form).some((i) => i.required || i.pattern || i.minLength > 0 || i.dataset.gte || i.dataset.min || i.type === 'email' || i.type === 'date');
    if (!ruled) return;
    form.noValidate = true;   // our messages replace the browser bubbles

    const live = (e) => {
      const i = e.target;
      if (!inputsOf(form).includes(i)) return;
      if (e.type === 'focusout') i.dataset.touched = '1';
      if (i.dataset.touched || (boxOf(i) && boxOf(i).classList.contains('has-error'))) show(i, check(i));
      // a changed "min" re-checks the "max" that depends on it, whether or not "min" was touched
      if (i.id) $$('[data-gte="#' + i.id + '"]', form).forEach((o) => { if (o.dataset.touched || (boxOf(o) && boxOf(o).classList.contains('has-error'))) show(o, check(o)); });
    };
    ['focusout', 'input', 'change'].forEach((t) => form.addEventListener(t, live));

    form.addEventListener('submit', (e) => {
      const bad = validateScope(form);
      if (bad) { e.preventDefault(); e.stopImmediatePropagation(); focusInvalid(bad); return; }
      const btn = form.querySelector('.btn[type=submit]');
      if (btn) { btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true'); }
    });
  });
  window.addEventListener('pageshow', () => $$('.btn.is-loading').forEach((b) => { b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); }));

  // After a server-side validation failure, bring the first error into view and focus it
  const firstServerError = document.querySelector('.field-error:not(:empty)');
  if (firstServerError && !document.querySelector('[data-steps]')) {
    const f = firstServerError.closest('.field');
    const target = f && f.querySelector('input:not([type=hidden]):not([type=file])');
    if (firstServerError.scrollIntoView) firstServerError.scrollIntoView({ block: 'center' });
    if (target && target.focus) target.focus({ preventScroll: true });
  }

  /* ───────────── Interactions ───────────── */

  // Show/hide password
  $$('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = document.querySelector(btn.dataset.togglePassword);
      if (!input) return;
      const reveal = input.type === 'password';
      input.type = reveal ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(reveal));
      btn.setAttribute('aria-label', reveal ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  // Demo credentials: fill the form
  $$('[data-fill]').forEach((btn) => btn.addEventListener('click', () => {
    const data = JSON.parse(btn.dataset.fill);
    const form = btn.closest('.app').querySelector('form');
    Object.entries(data).forEach(([name, value]) => {
      const el = form && form.querySelector('[name="' + name + '"]');
      if (el) { el.value = value; show(el, ''); }
    });
  }));

  // Toast: disappears by itself
  $$('[data-toast]').forEach((t) => {
    setTimeout(() => { t.classList.add('is-gone'); setTimeout(() => t.remove(), 300); }, 4500);
  });

  // Filter tabs: [data-filter-group] > [data-filter="x"] hides/shows [data-status] items
  $$('[data-filter-group]').forEach((group) => {
    const target = document.querySelector(group.dataset.filterGroup);
    const empty = target && target.querySelector('[data-empty]');
    $$('[data-filter]', group).forEach((chip) => {
      chip.addEventListener('click', () => {
        $$('[data-filter]', group).forEach((c) => c.classList.toggle('is-active', c === chip));
        const key = chip.dataset.filter;
        let shown = 0;
        $$('[data-status]', target).forEach((el) => {
          const ok = key === 'all' || el.dataset.status === key;
          el.classList.toggle('hide', !ok);
          if (ok) shown++;
        });
        if (empty) empty.classList.toggle('hide', shown > 0);
      });
    });
  });

  // Single-select groups (booking driver option, payment method, filter chips, etc.)
  $$('[data-choice-group]').forEach((group) => {
    const input = group.dataset.choiceInput ? document.querySelector(group.dataset.choiceInput) : null;
    $$('[data-value]', group).forEach((btn) => {
      btn.addEventListener('click', () => {
        if (group.hasAttribute('data-toggle') && btn.classList.contains('is-active')) {   // click again to clear
          btn.classList.remove('is-active');
          if (input) input.value = '';
          return;
        }
        $$('[data-value]', group).forEach((b) => b.classList.toggle('is-active', b === btn));
        if (input) input.value = btn.dataset.value;
        group.dispatchEvent(new CustomEvent('choice', { bubbles: true, detail: btn.dataset.value }));
      });
    });
  });

  // Modals
  $$('[data-modal-open]').forEach((btn) =>
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const modal = document.getElementById(btn.dataset.modalOpen);
      if (!modal) return;
      Object.entries(btn.dataset).forEach(([k, v]) => {
        if (!k.startsWith('fill')) return;
        const slot = k.slice(4).toLowerCase();
        if (slot === 'action') { const f = modal.querySelector('form[data-form-target]'); if (f) f.action = v; return; }
        modal.querySelectorAll('[data-slot="' + slot + '"]').forEach((el) => { el.textContent = v; });
      });
      modal.classList.add('is-open');
    })
  );
  // a modal whose form came back with a server error opens straight away
  $$('.modal').forEach((m) => { if ($$('.field-error', m).some((e) => e.textContent.trim() !== '')) m.classList.add('is-open'); });
  $$('[data-modal-close]').forEach((btn) =>
    btn.addEventListener('click', () => btn.closest('.modal').classList.remove('is-open'))
  );
  $$('.modal').forEach((m) =>
    m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('is-open'); })
  );

  // Photos are shrunk in the browser (max 1800px JPEG) so phone-camera pictures never hit the size limit
  const shrink = (file) => new Promise((resolve) => {
    if (!file.type.startsWith('image/') || file.size < 1.2 * 1048576) return resolve(file);
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
      const k = Math.min(1, 1800 / Math.max(img.naturalWidth, img.naturalHeight));
      const c = document.createElement('canvas');
      c.width = Math.round(img.naturalWidth * k);
      c.height = Math.round(img.naturalHeight * k);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      c.toBlob((blob) => {
        URL.revokeObjectURL(url);
        resolve(blob && blob.size < file.size ? new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
      }, 'image/jpeg', 0.85);
    };
    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };   // e.g. a format this browser cannot decode
    img.src = url;
  });

  // File tiles: show the chosen file name and re-validate
  $$('[data-upload]').forEach((tile) => {
    const input = tile.querySelector('input[type=file]');
    if (!input) return;
    const label = tile.querySelector('[data-upload-label]');
    input.addEventListener('change', async () => {
      if (!input.files.length) return;
      if (label) label.textContent = 'Memproses foto…';
      input.dataset.busy = '1';
      const original = input.files[0];
      const file = await shrink(original);
      delete input.dataset.busy;
      if (file !== original && window.DataTransfer) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
      }
      const msg = check(input);
      show(input, msg);
      tile.classList.toggle('is-done', !msg && input.files.length > 0);
      if (label) label.textContent = msg ? 'Pilih ulang file' : input.files[0].name + ' · ' + (input.files[0].size / 1048576).toFixed(1).replace('.', ',') + ' MB';
    });
  });

  // Booking form: duration + cost summary
  const booking = document.querySelector('[data-booking]');
  if (booking) {
    const price = Number(booking.dataset.price);
    const driverDay = Number(booking.dataset.driver);
    const fee = Number(booking.dataset.fee);
    const start = booking.querySelector('[name=start]');
    const end = booking.querySelector('[name=end]');
    const driver = booking.querySelector('[name=driver]');
    const set = (key, text) => { const el = booking.querySelector('[data-out="' + key + '"]'); if (el) el.textContent = text; };
    const calc = () => {
      const raw = Math.round((new Date(end.value) - new Date(start.value)) / 86400000);
      const days = Number.isFinite(raw) ? Math.max(1, raw) : 1;
      const withDriver = booking.dataset.driverOnly === '1' || driver.value === '1';
      const rent = price * days;
      const drv = withDriver ? driverDay * days : 0;
      set('days', 'Durasi: ' + days + ' hari');
      set('rent-label', 'Sewa mobil (' + days + ' hari)');
      set('rent', rupiah(rent));
      set('driver-label', 'Pengemudi (' + days + ' hari)');
      set('driver', rupiah(drv));
      booking.querySelector('[data-row="driver"]').classList.toggle('hide', !withDriver);
      set('fee', rupiah(fee));
      set('total', rupiah(rent + drv + fee));
    };
    [start, end].forEach((i) => i.addEventListener('change', calc));
    booking.addEventListener('choice', calc);
    calc();
  }

  // Date ranges: the end date can never be on or before the start date
  $$('form').forEach((form) => {
    const start = form.querySelector('input[type=date][name=start]');
    const end = form.querySelector('input[type=date][name=end]');
    if (!start || !end) return;
    const sync = () => {
      if (!start.value) return;
      const next = new Date(start.value + 'T00:00:00Z');
      next.setUTCDate(next.getUTCDate() + 1);
      end.min = next.toISOString().slice(0, 10);
      if (end.dataset.touched || end.value) show(end, check(end));
    };
    start.addEventListener('change', sync);
    sync();
  });

  // Payment countdown: [data-countdown="YYYY-MM-DD HH:MM:SS"]
  $$('[data-countdown]').forEach((el) => {
    const target = new Date(el.dataset.countdown).getTime();
    const tick = () => {
      const left = Math.max(0, Math.round((target - Date.now()) / 1000));
      el.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
      if (left === 0) { clearInterval(timer); location.reload(); }
    };
    const timer = setInterval(tick, 1000);
    tick();
  });

  // Promo code on payment page
  const promoBtn = document.querySelector('[data-promo-apply]');
  if (promoBtn) {
    promoBtn.addEventListener('click', () => {
      const code = document.querySelector('[data-promo-input]').value.trim().toUpperCase();
      const msg = document.querySelector('[data-promo-msg]');
      const total = document.querySelector('[data-pay-total]');
      const cta = document.querySelector('[data-cta-total]');
      const base = Number(total.dataset.base);
      if (code === promoBtn.dataset.code) {
        const pct = Number(promoBtn.dataset.percent);
        total.textContent = rupiah(base * (1 - pct / 100));
        msg.textContent = 'Kode promo dipakai: diskon ' + pct + '%';
        msg.className = 'badge badge-green';
      } else {
        total.textContent = rupiah(base);
        msg.textContent = code ? 'Kode promo tidak valid' : 'Masukkan kode promo dulu';
        msg.className = 'badge badge-red';
      }
      if (cta) cta.textContent = total.textContent;
      msg.classList.remove('hide');
    });
  }

  // Chat: send with fetch(), poll for new messages
  const chat = document.querySelector('[data-chat-form]');
  if (chat) {
    const body = document.querySelector('[data-chat-body]');
    const field = chat.querySelector('input[name=body]');
    const token = chat.querySelector('input[name=csrf_test_name]');
    let lastId = Number(body.dataset.lastId || 0);
    const bottom = () => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    const add = (m) => {
      if (body.querySelector('[data-id="' + m.id + '"]')) return;
      body.querySelector('.muted.caption')?.remove();
      const el = document.createElement('div');
      el.className = 'bubble ' + m.from + ' is-new';
      el.dataset.id = m.id;
      el.textContent = m.body;
      const t = document.createElement('small');
      t.textContent = m.time;
      el.appendChild(t);
      body.appendChild(el);
      lastId = Math.max(lastId, m.id);
    };
    bottom();
    chat.addEventListener('submit', async (e) => {
      e.preventDefault();
      const text = field.value.trim();
      if (!text) return;
      field.value = '';
      try {
        const res = await fetch(chat.action, {
          method: 'POST', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ body: text, csrf_test_name: token ? token.value : '' }),
        });
        const data = await res.json();
        if (data.ok) { add(data.message); bottom(); } else { field.value = text; }
      } catch (err) { field.value = text; }
    });
    if (body.dataset.poll) {
      setInterval(async () => {
        if (document.hidden) return;
        try {
          const res = await fetch(body.dataset.poll + '?after=' + lastId, { headers: { Accept: 'application/json' } });
          const data = await res.json();
          if (data.messages.length) { data.messages.forEach(add); bottom(); }
        } catch (err) { /* offline: try again next tick */ }
      }, 4000);
    }
  }

  // Admin document search (name / id / document number)
  const docSearch = document.querySelector('[data-doc-search]');
  if (docSearch) {
    docSearch.addEventListener('input', () => {
      const q = docSearch.value.trim().toLowerCase();
      $$('#docs .u-card').forEach((card) => card.classList.toggle('hide-search', q && !card.textContent.toLowerCase().includes(q)));
    });
  }

  // A total that just changed flashes once
  const bump = (el) => {
    if (!el || reduced) return;
    el.classList.remove('bump');
    void el.offsetWidth; // restart the animation
    el.classList.add('bump');
  };
  $$('[data-out="total"], [data-pay-total]').forEach((el) => {
    new MutationObserver(() => bump(el)).observe(el, { childList: true, characterData: true, subtree: true });
  });

  // Logo: tap to make the keychain swing again
  $$('.logo-box:not(.icon-box)').forEach((box) => {
    box.addEventListener('click', () => {
      box.classList.remove('is-swinging');
      void box.offsetWidth;
      box.classList.add('is-swinging');
    });
  });
})();
