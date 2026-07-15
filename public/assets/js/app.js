/* =============================================
   HappyBangladesh DMS — Application JavaScript
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {

  // ── 1. Sidebar toggle (mobile) ─────────────────────────────
  const sidebar        = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebar-overlay');
  const menuBtn        = document.getElementById('menu-toggle');

  function openSidebar() {
    sidebar?.classList.add('open');
    sidebarOverlay?.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar?.classList.remove('open');
    sidebarOverlay?.classList.remove('show');
    document.body.style.overflow = '';
  }

  menuBtn?.addEventListener('click', openSidebar);
  sidebarOverlay?.addEventListener('click', closeSidebar);

  // ── 2. Active sidebar link ─────────────────────────────────
  const links = document.querySelectorAll('.sidebar-link');
  const path  = window.location.pathname;
  links.forEach(link => {
    const href = new URL(link.href).pathname;
    if (href === path || (href !== '/' && path.startsWith(href))) {
      link.classList.add('active');
    }
  });

  // ── 3. Dropdown menus (user avatar, action menus) ──────────
  document.querySelectorAll('[data-dropdown]').forEach(trigger => {
    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      const target = document.getElementById(this.dataset.dropdown);
      if (!target) return;
      const isOpen = !target.classList.contains('hidden');
      // close all
      document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
      if (!isOpen) target.classList.remove('hidden');
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
  });

  // ── 4. Table search ────────────────────────────────────────
  document.querySelectorAll('[data-table-search]').forEach(input => {
    const tableId = input.dataset.tableSearch;
    const table   = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  });

  // ── 5. Expandable table rows ───────────────────────────────
  document.querySelectorAll('[data-expand-row]').forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = this.dataset.expandRow;
      const row      = document.getElementById(targetId);
      if (!row) return;
      const isHidden = row.classList.contains('hidden');
      // collapse siblings
      const tbody = this.closest('tbody') || document;
      tbody.querySelectorAll('.expand-detail-row').forEach(r => r.classList.add('hidden'));
      if (isHidden) row.classList.remove('hidden');
      // rotate icon
      this.querySelector('.expand-icon')?.classList.toggle('rotate-180', isHidden);
    });
  });

  // ── 6. Modal open/close ────────────────────────────────────
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById(this.dataset.modalOpen);
      modal?.classList.remove('hidden');
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById(this.dataset.modalClose)
               || this.closest('.modal-overlay');
      modal?.classList.add('hidden');
    });
  });

  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) this.classList.add('hidden');
    });
  });

  // ── 7. Confirm delete dialogs ──────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      const msg = this.dataset.confirm || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // For forms with data-confirm on submit button
  document.querySelectorAll('form[data-confirm-form]').forEach(form => {
    form.addEventListener('submit', function (e) {
      const msg = this.dataset.confirmForm || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ── 8. Auto-dismiss alerts ─────────────────────────────────
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    const delay = parseInt(alert.dataset.autoDismiss) || 4000;
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 500);
    }, delay);
  });

  // ── 9. QR / Barcode Scanner ────────────────────────────────
  const scannerContainer = document.getElementById('scanner-container');
  const startScanBtn     = document.getElementById('start-scan-btn');
  const stopScanBtn      = document.getElementById('stop-scan-btn');
  const scanResult       = document.getElementById('scan-result');
  const scanInput        = document.getElementById('scan-input');
  let videoStream        = null;

  if (startScanBtn) {
    startScanBtn.addEventListener('click', async function () {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment' }
        });
        videoStream = stream;
        const video = document.getElementById('scanner-video');
        if (video) {
          video.srcObject = stream;
          video.play();
        }
        scannerContainer?.classList.remove('hidden');
        startScanBtn.classList.add('hidden');
        stopScanBtn?.classList.remove('hidden');
        if (scanResult) scanResult.textContent = 'Point camera at QR/barcode…';

        // Attempt BarcodeDetector API if available
        if ('BarcodeDetector' in window) {
          const detector = new window.BarcodeDetector();
          const detect   = async () => {
            if (!videoStream) return;
            try {
              const codes = await detector.detect(video);
              if (codes.length > 0) {
                const code = codes[0].rawValue;
                if (scanInput)  scanInput.value = code;
                if (scanResult) {
                  scanResult.textContent = 'Scanned: ' + code;
                  scanResult.className   = 'text-green-600 font-semibold';
                }
                stopScanner();
                return;
              }
            } catch (_) {}
            requestAnimationFrame(detect);
          };
          requestAnimationFrame(detect);
        }
      } catch (err) {
        alert('Camera access denied or not available: ' + err.message);
      }
    });
  }

  function stopScanner() {
    if (videoStream) {
      videoStream.getTracks().forEach(t => t.stop());
      videoStream = null;
    }
    scannerContainer?.classList.add('hidden');
    startScanBtn?.classList.remove('hidden');
    stopScanBtn?.classList.add('hidden');
  }

  stopScanBtn?.addEventListener('click', stopScanner);

  // Manual barcode keyboard input (USB scanner)
  const barcodeBuffer = { value: '', timer: null };
  document.addEventListener('keypress', function (e) {
    if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;
    clearTimeout(barcodeBuffer.timer);
    barcodeBuffer.value += e.key;
    barcodeBuffer.timer  = setTimeout(() => {
      if (barcodeBuffer.value.length >= 4) {
        const val = barcodeBuffer.value;
        if (scanInput) scanInput.value = val;
        if (scanResult) {
          scanResult.textContent = 'Scanned (USB): ' + val;
          scanResult.className   = 'text-green-600 font-semibold';
        }
      }
      barcodeBuffer.value = '';
    }, 100);
  });

  // ── 10. Form validation helpers ────────────────────────────
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function (e) {
      let valid = true;
      this.querySelectorAll('[required]').forEach(field => {
        const err = document.getElementById(field.id + '-error');
        if (!field.value.trim()) {
          valid = false;
          field.classList.add('border-red-500');
          if (err) err.textContent = 'This field is required.';
        } else {
          field.classList.remove('border-red-500');
          if (err) err.textContent = '';
        }
      });
      if (!valid) e.preventDefault();
    });
  });

  // ── 11. Sidebar section collapse ───────────────────────────
  document.querySelectorAll('.sidebar-section-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
      const target = document.getElementById(this.dataset.target);
      target?.classList.toggle('hidden');
      this.querySelector('.toggle-icon')?.classList.toggle('rotate-180');
    });
  });

  // ── 12. Date range picker shortcut ─────────────────────────
  document.querySelectorAll('[data-date-preset]').forEach(btn => {
    btn.addEventListener('click', function () {
      const preset = this.dataset.datePreset;
      const from   = document.getElementById('date-from');
      const to     = document.getElementById('date-to');
      const now    = new Date();
      const pad    = n => String(n).padStart(2, '0');
      const fmt    = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

      if (preset === 'today') {
        if (from) from.value = fmt(now);
        if (to)   to.value   = fmt(now);
      } else if (preset === 'week') {
        const start = new Date(now); start.setDate(now.getDate() - 6);
        if (from) from.value = fmt(start);
        if (to)   to.value   = fmt(now);
      } else if (preset === 'month') {
        if (from) from.value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        if (to)   to.value   = fmt(now);
      }
    });
  });

  // ── 13. Admin Panel AJAX Modals for Add and Edit ────────────
  // Append the reusable ajax modal to body if not already present
  if (!document.getElementById('ajax-modal')) {
    const modalHtml = `
      <div id="ajax-modal" class="modal-overlay hidden">
        <div class="modal-box p-6 w-full max-w-xl relative" id="ajax-modal-content-wrap">
           <button type="button" id="ajax-modal-close-btn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
               <i class="fas fa-times"></i>
           </button>
           <div id="ajax-modal-body">
               <!-- Form content loaded here -->
           </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Close button event
    document.getElementById('ajax-modal-close-btn').addEventListener('click', () => {
      document.getElementById('ajax-modal').classList.add('hidden');
    });
  }

  const ajaxModal = document.getElementById('ajax-modal');
  const ajaxModalBody = document.getElementById('ajax-modal-body');
  const ajaxModalContentWrap = document.getElementById('ajax-modal-content-wrap');

  // Intercept all add/edit link clicks
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('a');
    if (!link) return;
    
    const href = link.getAttribute('href');
    if (!href) return;

    // Check if the link is for create or edit in admin area
    if (href.includes('admin/') && (href.includes('/create') || href.includes('/edit/'))) {
      e.preventDefault();
      
      // Adjust modal width based on page type
      if (href.includes('/dealers')) {
        ajaxModalContentWrap.className = 'modal-box p-6 w-full max-w-3xl relative';
      } else {
        ajaxModalContentWrap.className = 'modal-box p-6 w-full max-w-xl relative';
      }

      // Show loader
      ajaxModalBody.innerHTML = '<div class="flex justify-center items-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-brand"></i></div>';
      ajaxModal.classList.remove('hidden');

      try {
        const res = await fetch(href);
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract card body
        const cardBody = doc.querySelector('.card-body');
        if (cardBody) {
          ajaxModalBody.innerHTML = '';
          ajaxModalBody.appendChild(cardBody);
          
          // Re-execute scripts inside cardBody (e.g. for dealers form)
          const scripts = doc.querySelectorAll('script');
          scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            if (oldScript.src) {
              newScript.src = oldScript.src;
            } else {
              newScript.textContent = oldScript.textContent;
            }
            document.body.appendChild(newScript);
          });

          // Bind submit event to the form
          const form = ajaxModalBody.querySelector('form');
          if (form) {
            bindFormValidation(form);
            bindModalFormSubmit(form);
          }
        } else {
          ajaxModalBody.innerHTML = '<div class="text-red-500 py-4">Error: Form content could not be loaded.</div>';
        }
      } catch (err) {
        ajaxModalBody.innerHTML = '<div class="text-red-500 py-4">Error loading form.</div>';
      }
    }
  });

  function bindFormValidation(form) {
    form.addEventListener('submit', function (e) {
      let valid = true;
      this.querySelectorAll('[required]').forEach(field => {
        const err = document.getElementById(field.id + '-error') || form.querySelector('#' + field.id + '-error');
        if (!field.value.trim()) {
          valid = false;
          field.classList.add('border-red-500');
          if (err) err.textContent = 'This field is required.';
        } else {
          field.classList.remove('border-red-500');
          if (err) err.textContent = '';
        }
      });
      if (!valid) e.preventDefault();
    });
  }

  function bindModalFormSubmit(form) {
    form.addEventListener('submit', async (e) => {
      // If validation failed, let the validation prevent submission
      if (e.defaultPrevented) return;
      
      e.preventDefault();
      
      // Show saving spinner
      const submitBtn = form.querySelector('[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
      }

      try {
        const formData = new FormData(form);
        const res = await fetch(form.getAttribute('action'), {
          method: 'POST',
          body: formData
        });
        
        if (res.redirected) {
          const targetUrl = res.url;
          // Check if redirected to create/edit page (validation error) or index/other page (success)
          if (targetUrl.includes('/create') || targetUrl.includes('/edit/')) {
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const cardBody = doc.querySelector('.card-body');
            const alert = doc.querySelector('.alert');
            
            if (cardBody) {
              ajaxModalBody.innerHTML = '';
              if (alert) {
                ajaxModalBody.appendChild(alert);
              }
              ajaxModalBody.appendChild(cardBody);
              
              // Bind events again
              const newForm = ajaxModalBody.querySelector('form');
              if (newForm) {
                bindFormValidation(newForm);
                bindModalFormSubmit(newForm);
              }
            }
          } else {
            // Success, reload page
            window.location.reload();
          }
        } else {
          // If not redirected, check content for fallback or reload
          const text = await res.text();
          const doc = new DOMParser().parseFromString(text, 'text/html');
          const cardBody = doc.querySelector('.card-body');
          if (cardBody) {
            ajaxModalBody.innerHTML = '';
            const alert = doc.querySelector('.alert');
            if (alert) ajaxModalBody.appendChild(alert);
            ajaxModalBody.appendChild(cardBody);
            const newForm = ajaxModalBody.querySelector('form');
            if (newForm) {
              bindFormValidation(newForm);
              bindModalFormSubmit(newForm);
            }
          } else {
            window.location.reload();
          }
        }
      } catch (err) {
        alert('An error occurred during submission.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });
  }

});
