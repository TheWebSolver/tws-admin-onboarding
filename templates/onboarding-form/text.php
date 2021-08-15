<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form text field template.
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

<div class="ob-field ob-text-field<?php echo esc_attr( $class ); ?>">
	<label for="<?php echo esc_attr( $id ); ?>">
		<p class="label"><?php echo wp_kses_post( $label ); ?></p>

		<?php if ( $desc ) : ?>
			<p class="desc"><?php echo wp_kses_post( $desc ); ?></p>
		<?php endif; ?>
		<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
	</label>
</div>
