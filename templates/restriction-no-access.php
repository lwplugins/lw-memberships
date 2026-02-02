<?php
/**
 * Restriction template: No access.
 *
 * @package LightweightPlugins\Memberships
 *
 * Available variables:
 * @var int    $post_id Post ID.
 * @var string $message Restriction message.
 * @var string $reason  Restriction reason.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="lw-mship-restriction lw-mship-restriction--no-access">
	<p><?php echo esc_html( $message ); ?></p>
</div>
