(function () {
  'use strict';

  var body = document.body;

  function openPopupById(id) {
    var popup = document.getElementById(id);
    if (!popup) {
      return;
    }

    popup.classList.add('is-open');
    popup.setAttribute('aria-hidden', 'false');
    body.classList.add('panasys-popup-open');
  }

  function closePopup(popup) {
    popup.classList.remove('is-open');
    popup.setAttribute('aria-hidden', 'true');

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
