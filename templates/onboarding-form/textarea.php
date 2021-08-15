<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form textarea field template.
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

<div class="ob-field ob-textarea-field<?php echo esc_attr( $class ); ?>">
	<label for="<?php echo esc_attr( $id ); ?>">
		<div class="label"><?php echo wp_kses_post( $label ); ?></div>

		<?php if ( $desc ) : ?>
			<div class="desc"><?php echo wp_kses_post( $desc ); ?></div>
		<?php endif; ?>
		<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" rows="10" cols="50" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_html( $value ); ?></textarea>
	</label>
</div>
