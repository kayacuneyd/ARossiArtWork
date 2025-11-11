const modalSelectors = {
  contact: '[data-modal="contact"]',
  artwork: '[data-modal="artwork"]'
};

function toggleModal(modal, open = false) {
  if (!modal) return;
  modal.classList.toggle('hidden', !open);
  modal.classList.toggle('flex', open);
  if (open) {
    document.body.classList.add('overflow-hidden');
  } else if (![...document.querySelectorAll('[data-modal]')].some((m) => !m.classList.contains('hidden'))) {
    document.body.classList.remove('overflow-hidden');
  }
}

function setupContactModal() {
  const contactModal = document.querySelector(modalSelectors.contact);
  const openButtons = document.querySelectorAll('[data-open-contact]');
  const closeButtons = contactModal ? contactModal.querySelectorAll('[data-close-modal]') : [];
  const titleInput = contactModal ? contactModal.querySelector('[data-contact-artwork-title]') : null;
  const slugInput = contactModal ? contactModal.querySelector('[data-contact-artwork-slug]') : null;

  openButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      toggleModal(contactModal, true);
    });
  });

  closeButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      if (titleInput) titleInput.value = '';
      if (slugInput) slugInput.value = '';
      toggleModal(contactModal, false);
    });
  });

  const resetModal = () => {
    if (titleInput) titleInput.value = '';
    if (slugInput) slugInput.value = '';
    toggleModal(contactModal, false);
  };

  return {
    modal: contactModal,
    setArtwork(artwork) {
      if (titleInput) titleInput.value = artwork?.title ?? '';
      if (slugInput) slugInput.value = artwork?.slug ?? '';
      toggleModal(contactModal, true);
    },
    reset: resetModal
  };
}

function setupArtworkModal(contactModal) {
  const artworkModal = document.querySelector(modalSelectors.artwork);
  if (!artworkModal) return;

  const titleEl = artworkModal.querySelector('[data-artwork-title]');
  const descEl = artworkModal.querySelector('[data-artwork-description]');
  const metaEl = artworkModal.querySelector('[data-artwork-meta]');
  const imgEl = artworkModal.querySelector('[data-artwork-image]');
  const webpSource = artworkModal.querySelector('[data-artwork-webp]');
  const closeButtons = artworkModal.querySelectorAll('[data-close-modal]');
  const cards = document.querySelectorAll('[data-artwork]');
  const contactButtons = artworkModal.querySelectorAll('[data-open-contact]');
  let currentArtwork = null;

  const closeModal = () => {
    toggleModal(artworkModal, false);
  };

  closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));

  cards.forEach((card) => {
    card.addEventListener('click', (event) => {
      const isModifier = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
      if (isModifier) return;

      event.preventDefault();
      const dataAttr = card.getAttribute('data-artwork');
      if (!dataAttr) return;

      try {
        const data = JSON.parse(dataAttr);
        currentArtwork = data;
        if (titleEl) titleEl.textContent = data.title ?? '';
        if (descEl) {
          descEl.textContent = data.description || 'Description coming soon.';
        }
        if (metaEl) {
          metaEl.innerHTML = '';
          if (data.meta) {
            if (data.meta.technique) {
              metaEl.innerHTML += `<p><strong>Technique:</strong> ${data.meta.technique}</p>`;
            }
            if (data.meta.dimensions) {
              metaEl.innerHTML += `<p><strong>Dimensions:</strong> ${data.meta.dimensions}</p>`;
            }
            if (data.meta.price) {
              metaEl.innerHTML += `<p><strong>Price:</strong> £${Number(data.meta.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</p>`;
            }
          }
        }
        if (webpSource) {
          if (data.webp) {
            webpSource.srcset = data.webp;
          } else {
            webpSource.removeAttribute('srcset');
          }
        }
        if (imgEl && data.image) {
          imgEl.src = data.image;
          imgEl.alt = data.title ?? '';
        }

        toggleModal(artworkModal, true);
      } catch (error) {
        console.error('Failed to parse artwork data', error);
      }
    });
  });

  contactButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (currentArtwork) {
        contactModal?.setArtwork(currentArtwork);
      } else {
        contactModal?.modal && toggleModal(contactModal.modal, true);
      }
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeModal();
      contactModal?.reset?.();
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const contactModal = setupContactModal();
  setupArtworkModal(contactModal);

  document.querySelectorAll('[data-close-modal]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('[data-modal]');
      if (!modal) return;
      toggleModal(modal, false);
      if (contactModal?.modal === modal) {
        contactModal.reset();
      }
    });
  });
});
