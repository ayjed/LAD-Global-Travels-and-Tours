/**
 * LAD GLOBAL TRAVELS AND TOURS — PROTOTYPE VANILLA JAVASCRIPT (P01)
 * Zero frameworks / Accessible interactions / Progressive enhancement
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Navigation Toggle
  const mobileToggle = document.getElementById('mobileMenuToggle');
  const navDrawer = document.getElementById('mobileNavDrawer');

  if (mobileToggle && navDrawer) {
    mobileToggle.addEventListener('click', function () {
      const isOpen = navDrawer.classList.toggle('is-open');
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // 2. Accessible FAQ Accordion
  const accordionHeaders = document.querySelectorAll('.accordion-header');
  accordionHeaders.forEach(header => {
    header.addEventListener('click', function () {
      const item = this.parentElement;
      const isOpen = item.classList.contains('is-open');

      // Close other open items
      document.querySelectorAll('.accordion-item').forEach(otherItem => {
        if (otherItem !== item) {
          otherItem.classList.remove('is-open');
          const otherHeader = otherItem.querySelector('.accordion-header');
          if (otherHeader) otherHeader.setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle current
      if (isOpen) {
        item.classList.remove('is-open');
        this.setAttribute('aria-expanded', 'false');
      } else {
        item.classList.add('is-open');
        this.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // 3. Dynamic Package Pricing Calculator (Per-Person Simulation)
  const travellerInput = document.getElementById('travellerCount');
  const totalPriceDisplay = document.getElementById('totalPriceDisplay');
  const unitPriceAttr = document.getElementById('packageUnitPrice');

  if (travellerInput && totalPriceDisplay && unitPriceAttr) {
    const unitPrice = parseFloat(unitPriceAttr.dataset.price || '0');

    function updatePrice() {
      let count = parseInt(travellerInput.value, 10);
      if (isNaN(count) || count < 1) count = 1;
      const total = count * unitPrice;
      totalPriceDisplay.textContent = '₦' + total.toLocaleString('en-NG');
    }

    travellerInput.addEventListener('input', updatePrice);
    updatePrice();
  }

  // 4. Accessible Form Error Simulation Demo
  const demoForm = document.getElementById('enquiryFormDemo');
  if (demoForm) {
    demoForm.addEventListener('submit', function (e) {
      e.preventDefault();
      let hasError = false;
      const requiredInputs = demoForm.querySelectorAll('[required]');

      requiredInputs.forEach(input => {
        const errorEl = document.getElementById(input.id + '-error');
        if (!input.value.trim()) {
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
          if (errorEl) errorEl.style.display = 'block';
          hasError = true;
        } else {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
          if (errorEl) errorEl.style.display = 'none';
        }
      });

      const alertBox = document.getElementById('formSuccessAlert');
      if (!hasError && alertBox) {
        alertBox.style.display = 'block';
        demoForm.reset();
      }
    });
  }
});
