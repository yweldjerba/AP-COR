document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('cartQuantityModal');

    if (!modal) {
        return;
    }

    const productId = document.getElementById('cartModalProductId');
    const productBrand = document.getElementById('cartModalBrand');
    const productName = document.getElementById('cartModalTitle');
    const productPrice = document.getElementById('cartModalPrice');
    const productStock = document.getElementById('cartModalStock');
    const productCurrent = document.getElementById('cartModalCurrent');
    const quantityInput = document.getElementById('cartModalQuantity');
    const minusButton = document.getElementById('quantityMinus');
    const plusButton = document.getElementById('quantityPlus');

    const openButtons = document.querySelectorAll('.js-open-cart-modal');
    const closeButtons = document.querySelectorAll('.js-close-cart-modal');

    function openModal(button) {
        const stock = Math.max(1, parseInt(button.dataset.stock || '1', 10));
        const inCart = Math.max(0, parseInt(button.dataset.inCart || '0', 10));

        productId.value = button.dataset.id;
        productBrand.textContent = button.dataset.brand || 'GSB';
        productName.textContent = button.dataset.name || 'Ajouter au panier';
        productPrice.textContent = (button.dataset.price || '0,00') + ' €';
        productStock.textContent = stock + ' unité' + (stock > 1 ? 's' : '');

        quantityInput.value = 1;
        quantityInput.min = 1;
        quantityInput.max = stock;

        if (inCart > 0) {
            productCurrent.textContent =
                inCart + ' unité' + (inCart > 1 ? 's' : '') + ' déjà dans votre panier';
        } else {
            productCurrent.textContent = '';
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        window.setTimeout(function () {
            quantityInput.focus();
            quantityInput.select();
        }, 80);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function clampQuantity() {
        const min = parseInt(quantityInput.min || '1', 10);
        const max = parseInt(quantityInput.max || '1', 10);
        let value = parseInt(quantityInput.value || '1', 10);

        if (Number.isNaN(value)) {
            value = 1;
        }

        value = Math.max(min, Math.min(max, value));
        quantityInput.value = value;
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(button);
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    minusButton.addEventListener('click', function () {
        clampQuantity();

        const value = parseInt(quantityInput.value, 10);

        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });

    plusButton.addEventListener('click', function () {
        clampQuantity();

        const value = parseInt(quantityInput.value, 10);
        const max = parseInt(quantityInput.max || '1', 10);

        if (value < max) {
            quantityInput.value = value + 1;
        }
    });

    quantityInput.addEventListener('change', clampQuantity);
    quantityInput.addEventListener('blur', clampQuantity);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
