document.addEventListener('DOMContentLoaded', () => {
    // 1. Outside Click Listener for Modals
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('modal')) {
            e.target.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

// 2. Farmer Cart Sliding Panel Toggler
window.toggleCart = function() {
    const cartPanel = document.getElementById('cart-panel');
    const overlay = document.getElementById('overlay');
    if (cartPanel && overlay) {
        cartPanel.classList.toggle('open');
        overlay.classList.toggle('open');
        document.body.style.overflow = cartPanel.classList.contains('open') ? 'hidden' : '';
    }
};

// 3. Kiosk Add Product Form Slider
window.toggleCatalogAddForm = function() {
    const form = document.getElementById('add-form');
    const btn = document.getElementById('toggle-btn');
    if (form && btn) {
        const isHidden = form.classList.contains('hidden');
        form.classList.toggle('hidden');
        form.classList.toggle('grid');
        btn.textContent = isHidden ? '− Sembunyikan Form' : '+ Tampilkan Form';
    }
};

// 4. Modal Toggler
window.toggleModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.toggle('open');
        document.body.style.overflow = modal.classList.contains('open') ? 'hidden' : '';
    }
};

// 5. Open Edit Product Modal (Kiosk)
window.openEdit = function(product_id, name, category_id, price, stock, is_subsidized, het, desc) {
    const editId = document.getElementById('edit_product_id');
    const editName = document.getElementById('edit_product_name');
    const editCategory = document.getElementById('edit_category_id');
    const editPrice = document.getElementById('edit_price');
    const editStock = document.getElementById('edit_stock');
    const editSubsidized = document.getElementById('edit_is_subsidized');
    const editHet = document.getElementById('edit_het');
    const editDesc = document.getElementById('edit_desc');

    if (editId) editId.value = product_id;
    if (editName) editName.value = name;
    if (editCategory) editCategory.value = category_id;
    if (editPrice) editPrice.value = price;
    if (editStock) editStock.value = stock;
    if (editSubsidized) editSubsidized.value = is_subsidized;
    if (editHet) editHet.value = het;
    if (editDesc) editDesc.value = desc;

    window.toggleModal('modal-edit');
};

// 6. Open Reject KYC Modal (Admin)
window.openReject = function(kiosk_id) {
    const rejectForm = document.getElementById('reject-form');
    if (rejectForm) {
        rejectForm.action = '?kyc_reject=' + kiosk_id + '&tab=kyc';
    }
    window.toggleModal('modal-reject');
};
