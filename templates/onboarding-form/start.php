<?php
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form start template.
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
<!-- Onboarding Form - <?php echo sanitize_key( $step ); ?> -->
<form method="POST" id="ob-<?php echo esc_attr( $step ); ?>-form" class="onboarding-form">
	<div class="ob-form-wrapper">
