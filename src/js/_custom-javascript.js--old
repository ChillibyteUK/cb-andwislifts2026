// Add your custom JS here.

// Add background to navbar on scroll
(function () {
  var navbar = document.getElementById("wrapper-navbar");

  var forceScrolled =
    document.body.classList.contains("single-post") ||
    document.body.classList.contains("page-template-text-page");

  if (forceScrolled) {
    navbar.classList.add("scrolled");
  }

  var addNavbarBackground = function () {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      if (!forceScrolled) {
        navbar.classList.remove("scrolled");
      }
    }
  };

  window.addEventListener("scroll", addNavbarBackground);
})();

// (function() {
//   // Hide header on scroll
//   var doc = document.documentElement;
//   var w = window;

//   var prevScroll = w.scrollY || doc.scrollTop;
//   var curScroll;
//   var direction = 0;
//   var prevDirection = 0;

//   var header = document.getElementById('wrapper-navbar');

//   var checkScroll = function() {
//       // Find the direction of scroll (0 - initial, 1 - up, 2 - down)
//       curScroll = w.scrollY || doc.scrollTop;
//       if (curScroll > prevScroll) {
//           // Scrolled down
//           direction = 2;

// Equalize image heights per multi-module row in content grid.
(function () {
  function syncRow(row) {
    const covers = row.querySelectorAll(".img-cover");
    if (!covers || covers.length < 2) return;

    row.classList.remove("content-grid-row-sync");

    let maxHeight = 0;
    covers.forEach((cover) => {
      cover.style.height = "auto";
    });

    covers.forEach((cover) => {
      const rect = cover.getBoundingClientRect();
      if (rect.height > maxHeight) maxHeight = rect.height;
    });

    if (maxHeight > 0) {
      covers.forEach((cover) => {
        cover.style.height = `${Math.ceil(maxHeight)}px`;
      });
      row.classList.add("content-grid-row-sync");
    } else {
      covers.forEach((cover) => {
        cover.style.height = "";
      });
      row.classList.remove("content-grid-row-sync");
    }
  }

  function syncAll() {
    const rows = document.querySelectorAll(".content-grid .row");
    rows.forEach(syncRow);
  }

  function init() {
    syncAll();
    const imgs = document.querySelectorAll(".content-grid .img-cover img");
    imgs.forEach((img) => {
      if (img.complete) return;
      img.addEventListener("load", syncAll, { once: true });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  let resizeTimer = null;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(syncAll, 150);
  });
})();
//       } else if (curScroll < prevScroll) {
//           // Scrolled up
//           direction = 1;
//       }

//       if (direction !== prevDirection) {
//           toggleHeader(direction, curScroll);
//       }

//       prevScroll = curScroll;
//   };

//   var toggleHeader = function(direction, curScroll) {
//       if (direction === 2 && curScroll > 125) {
//           // Replace 52 with the height of your header in px
//           if (!document.getElementById('navbar').classList.contains('show')) {
//               header.classList.add('hide');
//               prevDirection = direction;
//           }
//       } else if (direction === 1) {
//           header.classList.remove('hide');
//           prevDirection = direction;
//       }
//   };

//   window.addEventListener('scroll', checkScroll);
// }
// )();

// Count up stat hero values when they enter view.
(function () {
  const statHeroes = document.querySelectorAll(
    ".stat-hero, .cb-stats, .cb-ticker",
  );

  if (!statHeroes.length) return;

  const prefersReducedMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const animateValue = (element) => {
    const target = Number(element.dataset.statTarget || 0);

    if (!Number.isFinite(target)) {
      return;
    }

    if (prefersReducedMotion || target === 0) {
      element.textContent = target.toLocaleString();
      return;
    }

    const duration = 1200;
    const startTime = performance.now();

    const tick = (now) => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      element.textContent = Math.round(target * eased).toLocaleString();

      if (progress < 1) {
        window.requestAnimationFrame(tick);
      }
    };

    window.requestAnimationFrame(tick);
  };

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target
          .querySelectorAll(
            ".stat-hero__stat-value, .cb-stats__stat-value, .cb-ticker__stat-value",
          )
          .forEach(animateValue);

        obs.unobserve(entry.target);
      });
    },
    {
      threshold: 0.35,
    },
  );

  statHeroes.forEach((hero) => observer.observe(hero));
})();

// Contact-person modal: update title and set the hidden recipient_pid field
// when Bootstrap fires show.bs.modal. Uses event.relatedTarget (the trigger
// link) to read the person's post ID, first name, and full name.
(function () {
  const modal = document.getElementById("modal-contact-person");
  if (!modal) return;

  const formId = modal.dataset.gfFormId;
  const recipientField = modal.dataset.gfRecipientField;

  modal.addEventListener("show.bs.modal", function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const pid = trigger.dataset.personId || "";
    const firstName = trigger.dataset.personFirstname || "";
    const fullName = trigger.dataset.personFullname || "";

    // Update the modal title.
    const title = modal.querySelector("#modal-contact-person-title");
    if (title) {
      title.textContent = firstName ? "Contact " + firstName : "Contact";
    }

    // Set the GF hidden recipient_pid field value (two targets as GF renders
    // both an id-based and a name-based input for hidden fields).
    if (formId && recipientField) {
      const byId = modal.querySelector(
        "#input_" + formId + "_" + recipientField,
      );
      const byName = modal.querySelector(
        "input[name='input_" + recipientField + "']",
      );
      if (byId) byId.value = pid;
      if (byName) byName.value = pid;

      // Also update gform_field_values so GF's AJAX submission path picks up
      // the value (mirrors the pluto cb-team implementation).
      const fieldValues = modal.querySelector(
        "input[name='gform_field_values']",
      );
      if (fieldValues) {
        const current = fieldValues.value || "";
        const updated = current
          .split("&")
          .filter((p) => !p.startsWith("recipient_pid="))
          .concat("recipient_pid=" + encodeURIComponent(pid))
          .join("&");
        fieldValues.value = updated;
      }
    }
  });
})();

/*

  // Header background
  document.addEventListener('scroll', function() {
      var nav = document.getElementById('navbar');
    //   var primaryNav = document.getElementById('primaryNav');
    //   if (!primaryNav.classList.contains('show')) {
    //       nav.classList.toggle('scrolled', window.scrollY > nav.offsetHeight);
    //   }
      document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
          dropdown.classList.remove('show');
      });
      document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
          toggle.classList.remove('show');
          toggle.blur();
      });
  });

*/
