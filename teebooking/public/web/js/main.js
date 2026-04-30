(function($) {
    "use strict";

    $(document).ready(function() {

    /* ======================================== Navbar Toggler ======================================== */
        $(document).on('click', '.navbar-toggler', function() {
            $(".navbar-toggler").toggleClass("active");
        });

        $(document).on('click', '.click-nav-right-icon', function() {
            $(".show-nav-content").toggleClass("show");
        });
     
    /* ======================================== Click Active Class ======================================== */

        $(document).on('click', '.active-list .item', function() {
            $(this).siblings().removeClass('active');
            $(this).toggleClass('active');
        });

    /* ======================================== Click Slide Open Close ======================================== */
        $(document).on('click', '.single-shop-left-title .title', function(e) {
            var shopTitle = $(this).parent('.single-shop-left-title');
            if (shopTitle.hasClass('open')) {
                shopTitle.removeClass('open');
                shopTitle.find('.single-shop-left-inner').removeClass('open');
                shopTitle.find('.single-shop-left-inner').slideUp(300, "swing");
            } else {
                shopTitle.addClass('open');
                shopTitle.children('.single-shop-left-inner').slideDown(300, "swing");
                shopTitle.siblings('.single-shop-left-title').children('.single-shop-left-inner').slideUp(300, "swing");
                shopTitle.siblings('.single-shop-left-title').removeClass('open');
            }
        });

    /* ======================================== back to top ========================================*/

        $(document).on('click', '.back-to-top', function() {
            $("html,body").animate({
                scrollTop: 0
            }, 700);
        });

    });

    /*  ======================================== back to top  ======================================== */

    $(window).on('scroll', function() {
        //back to top show/hide
        var ScrollTop = $('.back-to-top');
        if ($(window).scrollTop() > 200) {
            ScrollTop.fadeIn(10);
        } else {
            ScrollTop.fadeOut(10);
        }
    });
    
     /*  ======================================== Model to top  ======================================== */

      const buttonClose = document.querySelectorAll('[data-dismiss="modal"]');
      const modal = document.querySelector(".modal");
      const trigger = document.querySelector('[data-toggle="modal"]');

      function getStaticClass(modal) {
        modal.classList.add("astroui-modal-static");
        document.body.style.overflow = "hidden";
        document.body.classList.add("astroui-modal-open");
        setTimeout(() => {
          modal.classList.remove("astroui-modal-static");
        }, 100);
      }
      function showModal(modal) {
        modal.style.display = "flex";
        setTimeout(() => {
          modal.classList.add("show");
        }, 100);
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
        document.body.classList.add("astroui-modal-open");
      }
      function dismissModal(modal) {
        modal.classList.remove("show");
        setTimeout(() => {
          modal.style.display = "none";
        }, 200);
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        document.body.classList.remove("astroui-modal-open");
      }
      dismissModal(modal);
      const getDismiss = (buttonClose, modal) => {
        buttonClose.addEventListener("click", () => {
          dismissModal(modal);
        });
      };
      buttonClose.forEach((buttonClose) => {
        getDismiss(buttonClose, modal);
      });
      trigger.addEventListener("click", () => {
        showModal(modal);
      });
      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("show")) {
          dismissModal(modal);
        }
      });
    

      



    

})(jQuery);






