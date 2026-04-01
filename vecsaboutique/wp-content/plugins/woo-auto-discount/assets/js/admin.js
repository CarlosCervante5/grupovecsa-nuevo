/**
 * JavaScript para el panel de administración
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Inicializar color pickers
        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker({
                change: function(event, ui) {
                    updateBadgePreview();
                }
            });
        }
        
        // Toggle entre categorías y etiquetas
        $('#apply_to').on('change', function() {
            var value = $(this).val();
            
            if (value === 'category') {
                $('.apply-category').show();
                $('.apply-tag').hide();
            } else {
                $('.apply-category').hide();
                $('.apply-tag').show();
            }
        });
        
        // Actualizar preview del badge en tiempo real
        $('#badge_text, #discount_value, #show_discount_amount, #discount_type, #badge_style').on('change input', function() {
            updateBadgePreview();
        });
        
        function updateBadgePreview() {
            var badgeText = $('#badge_text').val();
            var discountValue = $('#discount_value').val();
            var showAmount = $('#show_discount_amount').is(':checked');
            var discountType = $('#discount_type').val();
            var badgeStyle = $('#badge_style').val();
            var badgeColor = $('#badge_color').val();
            var textColor = $('#badge_text_color').val();
            
            var previewText = badgeText;
            
            if (showAmount && discountValue) {
                if (discountType === 'percentage') {
                    previewText += ' -' + discountValue + '%';
                } else {
                    previewText += ' -$' + parseFloat(discountValue).toFixed(2);
                }
            }
            
            $('#badge-preview')
                .text(previewText)
                .attr('class', 'woo-auto-discount-badge woo-auto-discount-badge-' + badgeStyle)
                .css({
                    'background-color': badgeColor,
                    'color': textColor
                });
        }
        
        // Validación del formulario
        $('form').on('submit', function(e) {
            var enabled = $('#enabled').is(':checked');
            
            if (!enabled) {
                return true;
            }
            
            var discountValue = parseFloat($('#discount_value').val());
            
            if (isNaN(discountValue) || discountValue <= 0) {
                e.preventDefault();
                alert('Por favor, ingresa un valor de descuento válido mayor a 0.');
                $('#discount_value').focus();
                return false;
            }
            
            var applyTo = $('#apply_to').val();
            
            if (applyTo === 'category') {
                var selectedCategories = $('#selected_categories').val();
                if (!selectedCategories || selectedCategories.length === 0) {
                    e.preventDefault();
                    alert('Por favor, selecciona al menos una categoría.');
                    $('#selected_categories').focus();
                    return false;
                }
            } else {
                var selectedTags = $('#selected_tags').val();
                if (!selectedTags || selectedTags.length === 0) {
                    e.preventDefault();
                    alert('Por favor, selecciona al menos una etiqueta.');
                    $('#selected_tags').focus();
                    return false;
                }
            }
            
            return true;
        });
        
        // Mostrar mensaje de éxito
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('settings-updated') === 'true') {
            var $notice = $('<div class="notice notice-success is-dismissible"><p><strong>¡Configuración guardada exitosamente!</strong></p></div>');
            $('.woo-auto-discount-wrap h1').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
    
})(jQuery);


