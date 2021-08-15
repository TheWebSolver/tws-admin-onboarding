<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form radio field template.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Template
 * @version 1.1
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Bail if no radio options set.
if ( empty( $options ) ) {
	return;
}
?>

<div class="ob-field ob-radio-field hz_control_field hz_radio_field<?php echo esc_attr( $class ); ?>">
	<div class="title"><?php echo wp_kses_post( $label ); ?></div>
	<?php if ( $desc ) : ?>
		<div class="desc"><?php echo wp_kses_post( $desc ); ?></div>
	<?php endif; ?>
	<ul class="hz_card_control">
		<?php foreach ( $options as $key => $info ) : ?>
			<li class="hz_card_control_wrapper">
				<label for="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" class="hz_card_control">
					<input type="radio" class="hz_radio_input hz_card_control" id="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" name="<?php echo esc_attr( $id ); ?>" data-control="card" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key, true ); ?>>
					<div class="hz_card_info hz_flx row center">
						<div class="option_desc"><?php echo wp_kses_post( $info ); ?></div>
					</div>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
