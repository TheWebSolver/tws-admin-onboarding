/**
 * The Web Solver Onboarding scripts.
 *
 * -----------------------------------
 * DEVELOPED-MAINTAINED-SUPPPORTED BY
 * -----------------------------------
 * ███║     ███╗   ████████████████
 * ███║     ███║   ═════════██████╗
 * ███║     ███║        ╔══█████═╝
 *  ████████████║      ╚═█████
 * ███║═════███║      █████╗
 * ███║     ███║    █████═╝
 * ███║     ███║   ████████████████╗
 * ╚═╝      ╚═╝    ═══════════════╝
 */

jQuery(function ($) {
	/**
	 * Welcome to onboarding wizard.
	 */
	const tws = {
		/**
		 * Ajax dependency plugin installer button wrapper.
		 *
		 * @type {object} DOM Element `.hz_install_depl`.
		 */
		deplBtnWrapper: $('.hz_install_depl'),

		/**
		 * Ajax dependency plugin installer button.
		 *
		 * @type {object} Input field `#hz_install_depl`.
		 */
		deplBtn: $('#hz_install_depl'),

		/**
		 * Success text after dependency plugin installation.
		 *
		 * @type {string} From localized script.
		 */
		deplSuccess: tws_ob.successText,

		/**
		 * Success next step link's text after dependency plugin installation.
		 *
		 * @type {string} From localized script.
		 */
		deplSuccessNext: tws_ob.successNext,

		/**
		 * Buttons to disable.
		 *
		 * @type {object} Buttons to disable on installation. `.hz_dyn_btn`
		 */
		buttons: $('.hz_dyn_btn'),

		/**
		 * Button for next step.
		 *
		 * @type {object} `#hz_dyn_btnWrapper .hz_dyn_btn`
		 */
		deplNextBtn: $('#hz_dyn_btnWrapper .hz_dyn_btn'),

		/**
		 * Button for next step parent wrapper.
		 *
		 * @type {object} `#hz_dyn_btnWrapper`
		 */
		deplNextBtnParent: $('#hz_dyn_btnWrapper'),

		/**
		 * WordPress Admin AJAX URL.
		 *
		 * @type {string}
		 */
		ajaxurl: tws_ob.ajaxurl,

		/**
		 * Localized data.
		 *
		 * @type {object} `tws.ajax_data` passed from WordPress localized handler `onboarding_script`.
		 */
		data: tws_ob.ajaxdata,

		/**
		 * Recommended plugin checkbox field.
		 *
		 * @type {object} `.recommended-plugin input`.
		 */
		recommendedInput: $('.recommended-plugin input'),

		/**
		 * Recommended plugins name holder DOM element.
		 *
		 * @type {object} `.onboarding-recommended-names`.
		 */
		recommendedHolder: $('#onboarding-recommended-names'),

		/**
		 * Recommended plugin already active notice element.
		 *
		 * @type {object} `.hz_recommended_active_notice`.
		 */
		recommendedActive: $('.hz_recommended_active_notice'),

		/**
		 * Navigation buttons.
		 *
		 * @type {Object} `.hz_btn__prim`.
		 */
		navButtons: $('.hz_btn__prim'),

		/**
		 * Initialize.
		 */
		init: () => {
			tws.verifyInstall();
			tws.handleRecommended();
			tws.showActive();
			$('form').submit(tws.disableNav);
		},

		/**
		 * Verifies before installing dependency plugin plugin.
		 */
		verifyInstall: () => {
			$(tws.deplBtn).on('click', function (event) {
				event.preventDefault();
				tws.disableClick(tws.buttons);
				tws.handleClick(tws.deplBtnWrapper, this, tws.deplNextBtn);
			});
		},

		/**
		 * Handles after intsll button is clicked.
		 *
		 * @param {object} wrapper The button wrapper DOM element.
		 * @param {object} btn The install button.
		 * @param {object} nextBtn The next step button.
		 */
		handleClick: (wrapper, btn, nextBtn) => {
			$(wrapper).addClass('installing');
			$(btn).html('').prop('disabled', true);
			$(nextBtn).detach();
			setTimeout(tws.install(), 3000);
		},

		/**
		 * Silent dependency plugin plugin installer via Ajax.
		 *
		 * If dependency plugin is not installed yet,
		 * then it will be downloaded and installed.
		 * It will not be activated at this stage.
		 *
		 * @returns {boolean} True on success, false if no response.
		 */
		install: () => {
			$.ajax({
				url: tws.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: tws.data,

				/**
				 * Gets response back in JSON format.
				 *
				 * @param {JSON} response
				 */
				success: response => {
					console.info(response);
					if (response.success) {
						$(tws.deplBtn).html(tws.deplSuccess).remove();
						$(tws.deplNextBtnParent).append(tws.deplNextBtn);
						$(tws.deplBtnWrapper).removeClass('installing').addClass('installed');
						$('.hz_install_depl_msg').remove();
						$(tws.deplBtnWrapper).append(`<p id="hz_install_depl" class="hz_flx column center"><span>${tws_ob.successStar}</span><span>${response.data} ${tws.deplSuccess}</span></p>`).fadeIn(500);
						$('.button-next').html(`${tws.deplSuccessNext} →`);
						setTimeout(tws.enableClick(tws.buttons), 6000);
						return true;
					}
				},
			})

			return false;
		},

		/**
		 * Handles recommended plugins in a container (`tws.recommendedHolder`).
		 */
		handleRecommended: () => {
			tws.showCheckedStatus();
			$(tws.recommendedInput).each(function () {
				const rcdPlugin = $(this);
				tws.toggleRecommendedName(rcdPlugin);
				$(rcdPlugin).change(function () {
					tws.toggleRecommendedName(rcdPlugin);
					tws.showCheckedStatus();
				});
			});
		},

		/**
		 * Adds/removes recommended plugin name in a container when checked.
		 *
		 * @param {Object} Plugin The current recommended plugin checkbox input field.
		 */
		toggleRecommendedName: plugin => {
			const rcdHolder = '#' + $(tws.recommendedHolder).attr('id');
			const rcdData = plugin.data('plugin');
			const rcdName = rcdData.name;
			const rcdSlug = rcdData.slug;
			const rcdCheckIcon = tws_ob.recommended.check;

			if ($(plugin).is(':checked')) {
				$(rcdHolder).append(`<span class="${rcdSlug}">${rcdCheckIcon} ${rcdName}</span>`);
			} else {
				$(`${rcdHolder} .${rcdSlug}`).remove();
			}
		},

		/**
		 * Sets plugin is already active info for recommended plugin.
		 */
		showActive: () => {
			$(tws.recommendedActive).hover(function () {
				$(this).toggleClass('hz_recommended_active');
			})
		},

		showCheckedStatus: () => {
			const rcdInstall = $('input[data-active="false"]');
			const rcdTotal = rcdInstall.length;
			const rcdCheckedTotal = $('input[data-active="false"]:checked').length;
			const rcdCountText = rcdCheckedTotal === 1 ? tws_ob.recommended.single : tws_ob.recommended.plural;
			const rcdCountSuffix = tws_ob.recommended.suffix;
			const rcdMessageHolder = $(tws.recommendedHolder).parent().children('.label');
			const rcdCountHolder = $(rcdMessageHolder).children('.count');
			const rcdSuffixHolder = $(rcdMessageHolder).children('.suffix');
			if (rcdTotal > 0) {
				$(rcdCountHolder).text(rcdCheckedTotal);
				$(rcdSuffixHolder).text(`${rcdCountText} ${rcdCountSuffix}`);
				if (rcdCheckedTotal === 0) {
					$(rcdMessageHolder).append(`<p class="select option-notice desc">${tws_ob.recommended.select}</p>`);
				} else {
					$(rcdMessageHolder).find('p.select').remove();
				}
				console.info(`${rcdCheckedTotal} ${rcdCountText} ${rcdCountSuffix}`);
			}
		},

		/**
		 * Disables clicking on next previous button after click.
		 *
		 * @param {Object} event The submit event.
		 */
		disableNav: event => { // eslint-disable-line
			$(this).find('input[type="submit"]').prop('disabled', true);
			tws.disableClick($('.hz_btn__nav'));
			$(tws.navButtons).each(function () {
				$(this).addClass('disabled');
			});
			console.log('Current step data is being processed. Please wait...');
		},

		/**
		 * Disables link clicking behaviour.
		 *
		 * @param {object} link
		 */
		disableClick: link => {
			link.bind('click', function (event) {
				event.preventDefault();
			});
			link.addClass('disabled');
			link.attr('href', '');
		},

		/**
		 * Enables link clicking behaviour.
		 * @param {object} link
		 */
		enableClick: link => {
			link.unbind('click');
			link.removeClass('disabled');
		},
	}

	// Initialize.
	tws.init();
});
