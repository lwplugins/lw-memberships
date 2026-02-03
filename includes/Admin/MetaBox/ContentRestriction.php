<?php
/**
 * Content restriction meta box.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\MetaBox;

use LightweightPlugins\Memberships\Database\PlanRepository;
use LightweightPlugins\Memberships\Database\RuleRepository;

/**
 * Content restriction meta box.
 */
final class ContentRestriction {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ MetaBoxSaveHandler::class, 'save' ], 10, 2 );
	}

	/**
	 * Add meta box.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		$post_types = $this->get_supported_post_types();

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'lw_mship_restriction',
				__( 'Membership Restriction', 'lw-memberships' ),
				[ $this, 'render' ],
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Get supported post types.
	 *
	 * @return array<string>
	 */
	private function get_supported_post_types(): array {
		$types = get_post_types( [ 'public' => true ], 'names' );

		unset( $types['attachment'] );

		/**
		 * Filter supported post types for membership restriction.
		 *
		 * @param array<string> $types Post types.
		 */
		return apply_filters( 'lw_mship_supported_post_types', array_values( $types ) );
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render( \WP_Post $post ): void {
		$plans          = PlanRepository::get_all( true );
		$selected_plans = RuleRepository::get_plan_ids_by_post( $post->ID );

		wp_nonce_field( 'lw_mship_restriction', 'lw_mship_restriction_nonce' );

		if ( empty( $plans ) ) {
			$this->render_no_plans();
			return;
		}

		$this->render_plan_checkboxes( $plans, $selected_plans );
	}

	/**
	 * Render no plans message.
	 *
	 * @return void
	 */
	private function render_no_plans(): void {
		?>
		<p>
			<?php esc_html_e( 'No membership plans found.', 'lw-memberships' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lw-memberships-plans&action=new' ) ); ?>">
				<?php esc_html_e( 'Create one', 'lw-memberships' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Render plan checkboxes.
	 *
	 * @param array<\LightweightPlugins\Memberships\Models\Plan> $plans          Plans.
	 * @param array<int>                                         $selected_plans Selected plan IDs.
	 * @return void
	 */
	private function render_plan_checkboxes( array $plans, array $selected_plans ): void {
		?>
		<p><?php esc_html_e( 'Restrict this content to:', 'lw-memberships' ); ?></p>

		<?php foreach ( $plans as $plan ) : ?>
			<label style="display: block; margin-bottom: 5px;">
				<input
					type="checkbox"
					name="lw_mship_plans[]"
					value="<?php echo esc_attr( (string) $plan->id ); ?>"
					<?php checked( in_array( $plan->id, $selected_plans, true ) ); ?>
				>
				<?php echo esc_html( $plan->name ); ?>
			</label>
		<?php endforeach; ?>

		<p class="description" style="margin-top: 10px;">
			<?php esc_html_e( 'Leave unchecked for public access.', 'lw-memberships' ); ?>
		</p>
		<?php
	}
}
