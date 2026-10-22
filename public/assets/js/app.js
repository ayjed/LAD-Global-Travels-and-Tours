/**
 * LAD GLOBAL TRAVELS AND TOURS — PRODUCTION CLIENT JAVASCRIPT
 * Zero dependencies / Vanilla JS / Accessible UI Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
  // Mobile Navigation Toggle
  const mobileToggle = document.getElementById('mobileMenuToggle');
  const navDrawer = document.getElementById('mobileNavDrawer');

  if (mobileToggle && navDrawer) {
    mobileToggle.addEventListener('click', function () {
      const isOpen = navDrawer.classList.toggle('is-open');
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close on Escape key press
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navDrawer.classList.contains('is-open')) {
        navDrawer.classList.remove('is-open');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileToggle.focus();
      }
    });
  }

  // Progressive Enhancement for <details class="accordion-item">
  const detailsItems = document.querySelectorAll('details.accordion-item');
  detailsItems.forEach(item => {
    item.addEventListener('toggle', function () {
      if (this.open) {
        const parentAccordion = this.closest('.accordion');
        if (parentAccordion) {
          parentAccordion.querySelectorAll('details.accordion-item').forEach(sibling => {
            if (sibling !== this && sibling.open) {
              sibling.open = false;
            }
          });
        }
      }
    });
  });

  // Non-details fallback: Accessible Accordion with buttons
  const accordionButtons = document.querySelectorAll('button.accordion-header');
  accordionButtons.forEach(button => {
    button.addEventListener('click', function () {
      const item = this.parentElement;
      const isOpen = item.classList.contains('is-open');

      // Close siblings in same accordion
      const parentAccordion = this.closest('.accordion');
      if (parentAccordion) {
        parentAccordion.querySelectorAll('.accordion-item').forEach(otherItem => {
          if (otherItem !== item) {
            otherItem.classList.remove('is-open');
            const otherHeader = otherItem.querySelector('button.accordion-header');
            if (otherHeader) otherHeader.setAttribute('aria-expanded', 'false');
          }
        });
      }

      if (isOpen) {
        item.classList.remove('is-open');
        this.setAttribute('aria-expanded', 'false');
      } else {
        item.classList.add('is-open');
        this.setAttribute('aria-expanded', 'true');
      }
    });
  });
});

