<?php
/**
 * Restriction template: Membership expired.
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
<div class="lw-mship-restriction lw-mship-restriction--expired">
	<p><?php echo esc_html( $message ); ?></p>
</div>
