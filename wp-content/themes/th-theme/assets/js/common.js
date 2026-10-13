jQuery(document).ready(function ($) {
  'use strict';

  // Hamburger Event
  const navButton = document.querySelector('.js-hamburger-btn');
  const disclosure = document.getElementById('hamburger-menu');
  const listItems = disclosure.querySelectorAll('li a');

  function openNavigation() {
    navButton.setAttribute('aria-expanded', 'true');
    document.body.classList.add('is-hamburger-open');
    $('.js-hamburger-menu').addClass('is-open');
  }

  function closeNavigation() {
    navButton.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('is-hamburger-open');
    $('.js-hamburger-menu').removeClass('is-open');
  }

  function toggleNavigation() {
    const isOpen = navButton.getAttribute('aria-expanded') === 'true';
    isOpen ? closeNavigation() : openNavigation();
  }

  function handleBlur(event) {
    const navList = event.currentTarget.closest('.hamburger-menu-list');
    if (!event.relatedTarget || !navList.contains(event.relatedTarget)) {
      closeNavigation();
    }
  }

  navButton.addEventListener('click', toggleNavigation);

  if (listItems.length > 0) {
    listItems[listItems.length - 1].addEventListener('blur', handleBlur);
  }

  window.addEventListener('keyup', (e) => {
    if (e.key === 'Escape') {
      navButton.focus();
      closeNavigation();
    }
  });

  // Scroll to anchor animation
  let scrollPadding = 100;

  // Detect if a link's href goes to the current page
  function getSamePageAnchor(link) {
    if (
      link.protocol !== window.location.protocol ||
      link.host !== window.location.host ||
      link.pathname !== window.location.pathname ||
      link.search !== window.location.search
    ) {
      return false;
    }
    return link.hash;
  }

  // Scroll to a given hash, preventing the event given if there is one
  function scrollToHash(hash, e) {
    const elem = hash ? document.querySelector(hash) : false;
    if (elem) {
      if (e) e.preventDefault();
      const offsetTop =
        elem.getBoundingClientRect().top + window.pageYOffset - scrollPadding;
      gsap.to(window, {
        scrollTo: offsetTop,
      });
    }
  }

  // If a link's href is within the current page(except tab-link), scroll to it instead
  document.querySelectorAll('a[href]:not(.tab-link)').forEach((a) => {
    a.addEventListener('click', (e) => {
      scrollToHash(getSamePageAnchor(a), e);
    });
  });

  // Scroll to the element in the URL's hash on load
  scrollToHash(window.location.hash);
});
