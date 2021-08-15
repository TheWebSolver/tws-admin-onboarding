<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form checkbox field template.
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
?>

<div class="ob-field ob-checkbox-field hz_control_field<?php echo esc_attr( $class ); ?>">
	<label for="<?php echo esc_attr( $id ); ?>" class="hz_switcher_control">
		<div class="label hz_switcher_label"><?php echo wp_kses_post( $label ); ?>
			<?php if ( $desc ) : ?>
				<div class="desc"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
		<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" data-control="switch" <?php checked( $value, 'on', true ); ?>>
		<span class="hz_switcher"></span>
	</label>
</div>
