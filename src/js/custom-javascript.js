/**
 * File custom-javascript.js.
 *
 * Handles same-page anchor navigation with smooth scrolling offsets that
 * account for the fixed header and mobile Bootstrap navigation state.
 */
document.addEventListener("DOMContentLoaded", function () {
  var navbar = document.getElementById("primary-navbar");
  var navWrapper = document.getElementById("wrapper-navbar");
  var collapse = null;
  var logoSlidersVisible = true;

  document.addEventListener("visibilitychange", function () {
    logoSlidersVisible = !document.hidden;
  });

  function updateHeaderState() {
    if (!navWrapper) {
      return;
    }

    navWrapper.classList.toggle("scrolled", window.scrollY > 20);
  }

  window.addEventListener("scroll", updateHeaderState, { passive: true });
  window.addEventListener("resize", updateHeaderState);
  updateHeaderState();

  if (navbar && typeof bootstrap !== "undefined") {
    collapse = bootstrap.Collapse.getOrCreateInstance(navbar, {
      toggle: false,
    });
  }

  /**
   * Adjust the scroll position so hash targets stay visible below the header.
   */
  function getScrollOffset() {
    var navHeight = navWrapper ? navWrapper.offsetHeight : 0;

    if (window.innerWidth < 992) {
      return Math.max(navHeight - 28, 24);
    }

    return navHeight + 12;
  }

  /**
   * Scroll to a hash target using Lenis when available, or native smooth scroll.
   */
  function scrollToHash(hash) {
    if (!hash || hash === "#") {
      return;
    }

    var target = document.querySelector(decodeURI(hash));

    if (!target) {
      return;
    }

    var top =
      target.getBoundingClientRect().top +
      window.pageYOffset -
      getScrollOffset();

    if (window.lenis && typeof window.lenis.scrollTo === "function") {
      window.lenis.scrollTo(top, {
        duration: 1,
      });
      return;
    }

    window.scrollTo({
      top: top,
      behavior: "smooth",
    });
  }

  document.querySelectorAll('a[href*="#"]').forEach(function (link) {
    link.addEventListener("click", function (event) {
      var href = link.getAttribute("href");

      if (!href || href === "#") {
        return;
      }

      var url = new URL(href, window.location.href);

      // Only intercept in-page anchor links that resolve on the current URL.
      if (
        url.origin !== window.location.origin ||
        url.pathname !== window.location.pathname ||
        !url.hash
      ) {
        return;
      }

      var target = document.querySelector(decodeURI(url.hash));

      if (!target) {
        return;
      }

      event.preventDefault();
      window.history.pushState(null, "", url.hash);

      if (
        collapse &&
        window.innerWidth < 992 &&
        navbar.contains(link) &&
        navbar.classList.contains("show")
      ) {
        collapse.hide();
        // Wait for the mobile nav to finish collapsing before measuring scroll.
        window.setTimeout(function () {
          scrollToHash(url.hash);
        }, 250);
        return;
      }

      scrollToHash(url.hash);
    });
  });

  if (window.location.hash) {
    window.setTimeout(function () {
      scrollToHash(window.location.hash);
    }, 50);
  }

  document
    .querySelectorAll(".cb-customer-grid.logo-slider-block")
    .forEach(function (block) {
      if (typeof Flickity === "undefined") {
        return;
      }

      block.querySelectorAll(".logo-slider").forEach(function (slider, index) {
        var flickity = new Flickity(slider, {
          cellAlign: "left",
          pageDots: false,
          prevNextButtons: false,
          imagesLoaded: true,
          wrapAround: true,
          selectedAttraction: 0.01,
          friction: 0.15,
        });

        window.setTimeout(function () {
          window.setInterval(function () {
            if (logoSlidersVisible) {
              flickity.next();
            }
          }, 4000);
        }, 750 * index);
      });
    });
});
