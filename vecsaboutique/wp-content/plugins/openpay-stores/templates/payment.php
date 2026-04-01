<?php
/**
 * Estructura para el Checkout Clásico (Vista Tradicional)
 */

$country = $this->country;
$images_url = $this->images_dir . '/newcheckout/';

$modal_stores = [
	'MX' => [
		'cols' => [
			['Walmart', 'Walmart Express', 'Bodega Aurrerá', "Sam's Club", "Waldo's", 'Farmacias del Ahorro', 'Farmacias Guadalajara'],
			['7 Eleven', 'Circle K', 'Extra', 'Kiosko', 'SYS Tienda', 'Otras']
		],
		'link' => 'https://www.paynet.com.mx/mapa-tiendas/index.html',
		'link_text' => 'consulta la tienda más cercana'
	],
	'PE' => [
		'cols' => [
			['BBVA', 'Caja Arequipa', 'Interbank', 'Yape'],
			['BCP', 'Caja Huancayo', 'Kasnet']
		],
		'link' => '#',
		'link_text' => 'consulta tu punto de pago más cercano'
	]
];
?>

<div id="openpay-classic-container" class="openpay-store-checkout-container" style="max-width: 100%; overflow: hidden;">
	<?php if ($country === 'MX'): ?>
		<div class="openpay-logos" style="max-width: 100%; text-align: center;">
			<img src="<?= $images_url ?>openpay-stores-icons.svg" alt="Metodos de pago Openpay"
				style="max-width: 100%; height: auto;">
		</div>
	<?php endif; ?>

	<?php if ($country === 'PE'): ?>
		<div class="openpay-tabs">
			<button type="button" class="openpay-tab-button active" data-tab="webapp">Pago en web app</button>
			<button type="button" class="openpay-tab-button" data-tab="agencias">Pago en agencias</button>
		</div>
		<input type="hidden" name="openpay_pe_type" id="openpay_pe_type" value="webapp">
	<?php endif; ?>

	<div class="openpay-store-checkout-style">
		<div class="step-guide">
			<div class="step-guide__header">
				<div class="step-guide__logo-left">
					<img src="<?= $images_url ?>icon-lock.svg" alt="Lock" style="width: 15px; height: auto;">
					<img src="<?= $images_url ?>logo-openpay-small.svg" alt="Openpay"
						style="height: 20px; width: auto;">
					<span class="step-guide__security-text">by BBVA asegura y protege tu pago.</span>
				</div>

				<div class="step-guide__logo-right">
					<?php if ($country === 'MX'): ?>
						<img src="<?= $images_url ?>paynet-logo.png" alt="Paynet" style="max-width: 100px; height: auto;">
					<?php elseif ($country === 'CO'): ?>
						<img src="<?= $images_url ?>efecty-logo.png" alt="Efecty" style="max-width: 100px; height: auto;">
					<?php endif; ?>
				</div>
			</div>

			<div class="step-guide__step step-guide__vertical-line">
				<div class="step-guide__icon">
					<img src="<?= $images_url ?>logo-step-1.svg" alt="Paso 1" style="max-width: 100%; height: auto;">
				</div>
				<div class="step-guide__content">
					<p class="step-guide__title">Confirma y reserva la compra</p>
					<p class="step-guide__description">Selecciona “Realizar el pedido”, tu compra quedará reservada
						hasta que completes el pago.</p>
				</div>
			</div>

			<div class="step-guide__step step-guide__vertical-line">
				<div class="step-guide__icon">
					<img src="<?= $images_url ?>logo-step-2.svg" alt="Paso 2" style="max-width: 100%; height: auto;">
				</div>
				<div class="step-guide__content">
					<p class="step-guide__title">Guarda tu referencia de pago</p>
					<p class="step-guide__description">Descarga y guarda la referencia de pago, también la recibirás por
						correo.</p>
				</div>
			</div>

			<div class="step-guide__step">
				<div class="step-guide__icon">
					<img src="<?= $images_url ?>logo-step-3.svg" alt="Paso 3" style="max-width: 100%; height: auto;">
				</div>
				<div class="step-guide__content">
					<p class="step-guide__title">Completa el pago</p>
					<p id="step-3-description" class="step-guide__description">
						<?php if ($country === 'PE'): ?>
							Ingresa a tu banca móvil o web. Selecciona "Pago de servicio" y elige KASHIO PERÚ. Luego,
							ingresa el número de referencia, lo encontrarás en el PDF enviado a tu correo.
						<?php elseif ($country === 'CO'): ?>
							Ve a un punto Efecty y dile a la persona en caja que harás un pago en efectivo, proporciona el
							núm. de referencia.
						<?php else: ?>
							Ve a una de las tiendas aliadas y dile a la persona en caja que harás un pago en efectivo,
							proporciona el código de barras o núm. de referencia.
						<?php endif; ?>
					</p>
				</div>
			</div>

			<?php if (in_array($country, ['MX', 'PE'])): ?>
				<div class="step-guide__footer">
					<a href="#" id="openpay-modal-trigger" class="step-guide__link">¿En dónde puedo pagar?</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div> <?php if (isset($modal_stores[$country])): ?>
	<div id="stepGuideModal" class="step-guide__modal" style="display:none;">
		<div class="step-guide__modal-overlay"></div>
		<div class="step-guide__modal-content">
			<button type="button" class="step-guide__modal-close">&times;</button>
			<div class="step-guide__modal-header">
				<img src="<?= $images_url ?>icon-info.svg" alt="Info" class="step-guide__modal-icon"
					style="width: 24px; height: auto;">
				<p class="step-guide__modal-title">¿En dónde puedo pagar?</p>
			</div>
			<p class="step-guide__modal-text">
				Acude a cualquiera de las siguientes tiendas aliadas o
				<a href="<?= $modal_stores[$country]['link'] ?>" target="_blank" class="step-guide__modal-link">
					<?= $modal_stores[$country]['link_text'] ?>
				</a>
			</p>
			<div class="step-guide__modal-columns">
				<?php foreach ($modal_stores[$country]['cols'] as $col): ?>
					<ul class="store-list">
						<?php foreach ($col as $store): ?>
							<li><?= $store ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<script>
	(function ($) {
		'use strict';
		var initOpenpayClassic = function () {
			var $container = $('#openpay-classic-container');
			if (!$container.length) return;

			var $tabs = $('.openpay-tab-button');
			var $step3Desc = $('#step-3-description');
			var $peInput = $('#openpay_pe_type');

			$tabs.off('click').on('click', function (e) {
				e.preventDefault();
				$tabs.removeClass('active');
				$(this).addClass('active');
				var type = $(this).data('tab');
				if ($peInput.length) $peInput.val(type);

				if (type === 'webapp') {
					$step3Desc.text('Ingresa a tu banca móvil o web. Selecciona "Pago de servicio" y elige KASHIO PERÚ. Luego, ingresa el número de referencia, lo encontrarás en el PDF enviado a tu correo.');
				} else {
					$step3Desc.text('Acude a una agencia autorizada e indica al cajero que harás un pago de servicio KASHIO PERÚ, proporciona el numero de referencia. Antes de pagar, verifica que los datos coincidan.');
				}
			});

			var $modal = $('#stepGuideModal');
			var $openBtn = $('#openpay-modal-trigger');
			var $closeBtn = $('.step-guide__modal-close');
			var $overlay = $('.step-guide__modal-overlay');

			if ($openBtn.length) {
				$openBtn.off('click').on('click', function (e) {
					e.preventDefault();
					$modal.fadeIn(200).addClass('step-guide__modal--active');
				});
			}

			if ($modal.length) {
				$closeBtn.add($overlay).off('click').on('click', function () {
					$modal.fadeOut(200).removeClass('step-guide__modal--active');
				});
			}
		};

		$(document).ready(initOpenpayClassic);
		$(document.body).on('updated_checkout', initOpenpayClassic);
	})(jQuery);
</script>