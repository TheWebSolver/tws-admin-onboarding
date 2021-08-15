<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form select field template.
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

<div class="ob-field ob-select-field hz_control_field hz_select_control_wrapper<?php echo esc_attr( $class ); ?>">
	<label for="<?php echo esc_attr( $id ); ?>">
		<div class="label"><?php echo wp_kses_post( $label ); ?></div>
	</label>
	<?php if ( $desc ) : ?>
		<div class="desc"><?php echo wp_kses_post( $desc ); ?></div>
	<?php endif; ?>
	<select id="<?php echo esc_attr( $id ); ?>" class="hz_select_control" name="<?php echo esc_attr( $id ); ?>" data-control="select">
		<option value=""></option>
		<?php foreach ( $options as $key => $info ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
				<?php echo esc_html( $info ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
