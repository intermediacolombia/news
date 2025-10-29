(function ($) {
  "use strict";

  // ===============================
  // Dropdown on hover
  // ===============================
  $(document).ready(function () {
    function toggleNavbarMethod() {
      if ($(window).width() > 992) {
        $(".navbar .dropdown")
          .on("mouseover", function () {
            $(".dropdown-toggle", this).trigger("click");
          })
          .on("mouseout", function () {
            $(".dropdown-toggle", this).trigger("click").blur();
          });
      } else {
        $(".navbar .dropdown").off("mouseover").off("mouseout");
      }
    }
    toggleNavbarMethod();
    $(window).resize(toggleNavbarMethod);
  });

  // ===============================
  // Back to top button
  // ===============================
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $(".back-to-top").fadeIn("slow");
    } else {
      $(".back-to-top").fadeOut("slow");
    }
  });
  $(".back-to-top").click(function () {
    $("html, body").animate({ scrollTop: 0 }, 1500, "easeInOutExpo");
    return false;
  });

  // ===============================
  // Función general de inicialización de sliders
  // ===============================
  function initAllCarousels(context = document) {
    // Evita reinicializar sliders ya cargados
    $(context).find(".owl-carousel").each(function () {
      const $this = $(this);
      if ($this.hasClass("owl-loaded")) return;

      // Detectar tipo de carrusel por clase
      if ($this.hasClass("tranding-carousel")) {
        $this.owlCarousel({
          autoplay: true,
          smartSpeed: 2000,
          items: 1,
          dots: false,
          loop: true,
          nav: true,
          navText: [
            '<i class="fa fa-angle-left"></i>',
            '<i class="fa fa-angle-right"></i>',
          ],
        });
      } else if ($this.hasClass("carousel-item-1")) {
        $this.owlCarousel({
          autoplay: true,
          smartSpeed: 1500,
          items: 1,
          dots: false,
          loop: true,
          nav: true,
          navText: [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>',
          ],
        });
      } else if ($this.hasClass("carousel-item-2")) {
        $this.owlCarousel({
          autoplay: false,
          smartSpeed: 1000,
          margin: 30,
          dots: false,
          loop: true,
          nav: true,
          navText: [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>',
          ],
          responsive: {
            0: { items: 1 },
            576: { items: 1 },
            768: { items: 2 },
            1200: { items: 3 },
          },
        });
      } else if ($this.hasClass("carousel-item-3")) {
        $this.owlCarousel({
          autoplay: false,
          smartSpeed: 1000,
          margin: 30,
          dots: false,
          loop: true,
          nav: true,
          navText: [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>',
          ],
          responsive: {
            0: { items: 1 },
            576: { items: 1 },
            768: { items: 2 },
            992: { items: 2 },
            1200: { items: 3 },
          },
        });
      } else if ($this.hasClass("carousel-item-4")) {
        $this.owlCarousel({
          autoplay: false,
          smartSpeed: 1000,
          margin: 10,
          dots: false,
          loop: true,
          nav: true,
          navText: [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>',
          ],
          responsive: {
            0: { items: 1 },
            576: { items: 1 },
            768: { items: 2 },
            992: { items: 3 },
            1200: { items: 5 },
          },
        });
      }
    });
  }

  // Inicialización al cargar la página
  $(document).ready(() => {
    initAllCarousels();
  });

  // ===============================
  // Integración con navegación AJAX
  // ===============================
  const pageContent = document.getElementById("pageContent");
  if (pageContent) {
    const observer = new MutationObserver(() => {
      initAllCarousels(pageContent); // Inicializa solo los nuevos sliders cargados
    });
    observer.observe(pageContent, { childList: true, subtree: true });
  }
})(jQuery);


