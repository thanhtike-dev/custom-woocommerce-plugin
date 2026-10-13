document.addEventListener('DOMContentLoaded', () => {
  const modal = document.querySelector('#cim-inventory-modal');
  const form = document.querySelector('#cim-inventory-form');

  if (!modal || !form || typeof cimInventory === 'undefined') {
    return;
  }

  const productName = modal.querySelector('.cim-modal__product-name');
  const quantityInput = form.elements.stock_quantity;
  const manageStockInput = form.elements.manage_stock;
  const message = modal.querySelector('.cim-modal__message');
  const saveButton = form.querySelector('[type="submit"]');

  let activeButton = null;
  let previousFocus = null;

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cim-modal-open');

    if (previousFocus) {
      previousFocus.focus();
    }

    activeButton = null;
  };

  const syncStockFields = () => {
    const enabled = manageStockInput.checked;

    quantityInput.disabled = !enabled;
    quantityInput.required = enabled;

    modal
      .querySelector('.cim-stock-fields')
      .classList.toggle('is-disabled', !enabled);
  };

  document.querySelectorAll('.cim-edit-button').forEach((button) => {
    button.addEventListener('click', () => {
      activeButton = button;
      previousFocus = document.activeElement;

      form.reset();
      message.textContent = '';
      message.className = 'cim-modal__message';

      form.elements.product_id.value = button.dataset.productId;
      form.elements.nonce.value = cimInventory.nonce;

      productName.textContent = button.dataset.productName;
      manageStockInput.checked = button.dataset.manageStock === 'yes';

      quantityInput.value = button.dataset.stockQuantity ?? '';

      syncStockFields();

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('cim-modal-open');

      modal.querySelector('.cim-modal__close').focus();
    });
  });

  manageStockInput.addEventListener('change', syncStockFields);

  modal.querySelectorAll('[data-cim-close]').forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!form.reportValidity()) {
      return;
    }

    saveButton.disabled = true;
    message.textContent = 'Saving inventory...';
    message.className = 'cim-modal__message';

    try {
      const formData = new FormData(form);

      if (!manageStockInput.checked) {
        formData.set('manage_stock', 'no');
      }

      const response = await fetch(cimInventory.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.data?.message || 'Unable to update inventory.');
      }

      const data = result.data;
      const row = activeButton.closest('tr');

      if (row) {
        row.cells[3].textContent = data.manage_stock
          ? (data.stock_quantity ?? '—')
          : '—';

        const status = row.cells[4].querySelector('.cim-status');

        if (status) {
          status.textContent = data.stock_label;
        }

        activeButton.dataset.manageStock = data.manage_stock ? 'yes' : 'no';

        activeButton.dataset.stockQuantity = data.stock_quantity ?? '';
      }

      message.textContent = data.message;
      message.classList.add('is-success');

      window.setTimeout(closeModal, 700);
    } catch (error) {
      message.textContent = error.message || 'An unexpected error occurred.';
      message.classList.add('is-error');
    } finally {
      saveButton.disabled = false;
    }
  });
});
