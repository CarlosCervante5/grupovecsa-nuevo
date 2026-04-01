  jQuery(document).ready(function () {
    const openModalLink = jQuery(".step-guide__link");
    let modal = jQuery("#stepGuideModal");

    if (!openModalLink || !modal) return;

    jQuery(document).on("click", ".step-guide__link", function(e) {
      e.preventDefault();
      jQuery("#stepGuideModal").addClass("step-guide__modal--active");
    })
    jQuery(document).on("click", "#closeModalBtn", function(e) {
      e.preventDefault();
      jQuery("#stepGuideModal").removeClass('step-guide__modal--active');
    })
    jQuery(document).on("click", ".step-guide__modal-overlay", function(e) {
      e.preventDefault();
      jQuery("#stepGuideModal").removeClass('step-guide__modal--active');
    })
  })
  