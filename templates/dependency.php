<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard dependency plugin installation template.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Template
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

// Bail early if no need to show because for error
// due to plugin already installed (should never have happened in the first place).
if ( false === $show ) {
	return;
}
?>

<!-- Dependency Plugin Installation -->
<?php
// In case where dependency plugin needs to be installed/renistalled.
if ( 'pending' === $status || 'installed' === $status ) :
	if ( 'installed' === $status ) :
		// It seems like installed dependency plugin has now been deleted, prepare message and button text.
		$message = sprintf( '<p><b>%1$s</b> %2$s.</p>', $name, __( 'dependency plugin has been deleted and needs to be reinstalled', 'tws-onboarding' ) );
		$button  = __( 'Reinstall', 'tws-onboarding' );
	else :
		// It seems like dependency plugin is yet to be installed, prepare message and button text.
		$message = sprintf( '<b>%1$s</b> %2$s.', $name, __( 'dependency plugin does not seem to be installed. Click the button below to install it now', 'tws-onboarding' ) );
		$button  = __( 'Install', 'tws-onboarding' );
	endif;
	?>
	<div class="hz_install_depl_wrapper">
		<div class="hz_install_depl_msg"><?php echo wp_kses_post( $message ); ?></div>
		<div class="hz_install_depl hz_flx column center">
			<button id="hz_install_depl" class="button" type="submit">
				<?php echo esc_html( $button . ' ' . $name ); ?>
			</button>
			<div class="install_loader"></div>
		</div>
		<p id="hz_dyn_btnWrapper" class="hz_dyn_btnWrapper onboarding-actions">
			<a href="<?php echo esc_url( $next_step ); ?>" class="button button-next hz_dyn_btn hz_btn__prim"><?php echo esc_html( $button_text ); ?> →</a>
		</p>
	</div>
<?php else : // In case where dependency plugin can not be installed. ?>
	<div class="hz_install_depl_wrapper">
		<div class="hz_install_depl_msg">
			<?php echo wp_kses_post( $status ); ?>
			<br>
			<?php
			echo sprintf(
				'%1$s <b>%2$s</b>.',
				esc_html_e( 'Click the button below to download the compatible version of', 'tws-onboarding' ),
				esc_html( $name )
			);
			?>
		</div>
		<div class="hz_install_depl hz_flx column center">
		<?php $stat_link = "https://wordpress.org/plugins/{$slug}/advanced/#plugin-download-history-stats"; ?>
			<a href="<?php echo esc_url( $stat_link ); ?>" target="_blank">
				<?php esc_html_e( 'Download', 'tws-onboarding' ); ?> <?php echo esc_html( $name ); ?>
				<span class="newTab">↗</span>
			</a>
		</div>
		<p id="hz_dyn_btnWrapper" class="onboarding-actions">
			<a href="<?php echo esc_url( $next_step ); ?>" class="button button-next hz_dyn_btn"><?php echo esc_html( $button_text ); ?> →</a>
		</p>
	</div>
<?php endif; ?>
<!-- #Dependency Plugin Installation -->

