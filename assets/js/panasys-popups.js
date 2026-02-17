(function () {
  'use strict';

  var body = document.body;

  function getStorageKey(popupId) {
    return 'panasys_popup_hidden_until_' + popupId;
  }

  function getHiddenUntil(popupId) {
    try {
      var value = window.localStorage.getItem(getStorageKey(popupId));
      return value ? parseInt(value, 10) : 0;
    } catch (e) {
      return 0;
    }
  }

  function isSuppressed(popup) {
    var hiddenUntil = getHiddenUntil(popup.id);
    return hiddenUntil && hiddenUntil > Date.now();
  }

  function suppressPopup(popup) {
    var days = parseInt(popup.getAttribute('data-hide-days') || '1', 10);
    if (!days || days < 1) {
      days = 1;
    }

    var hiddenUntil = Date.now() + days * 24 * 60 * 60 * 1000;
    try {
      window.localStorage.setItem(getStorageKey(popup.id), String(hiddenUntil));
    } catch (e) {
      // Ignore storage errors.
    }
  }

  function openPopupById(id) {
    var popup = document.getElementById(id);
    if (!popup || isSuppressed(popup)) {
      return;
    }

    popup.classList.add('is-open');
    popup.setAttribute('aria-hidden', 'false');
    body.classList.add('panasys-popup-open');
  }

  function closePopup(popup) {
    popup.classList.remove('is-open');
    popup.setAttribute('aria-hidden', 'true');
    suppressPopup(popup);

    if (!document.querySelector('.panasys-popup.is-open')) {
      body.classList.remove('panasys-popup-open');
    }
  }

  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-panasys-open]');
    if (opener) {
      event.preventDefault();
      openPopupById(opener.getAttribute('data-panasys-open'));
      return;
    }

    var closer = event.target.closest('[data-panasys-close]');
    if (closer) {
      var popup = closer.closest('.panasys-popup');
      if (popup) {
        closePopup(popup);
      }
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    var openPopups = document.querySelectorAll('.panasys-popup.is-open');
    openPopups.forEach(function (popup) {
      closePopup(popup);
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    var autoOpenPopups = document.querySelectorAll('.panasys-popup[data-auto-open="1"]');
    autoOpenPopups.forEach(function (popup) {
      openPopupById(popup.id);
    });
  });
})();
