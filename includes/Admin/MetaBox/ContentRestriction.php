<?php
/**
 * Content restriction meta box.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\MetaBox;

use LightweightPlugins\Memberships\Database\LevelRepository;
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
		$levels          = LevelRepository::get_all( true );
		$selected_levels = RuleRepository::get_level_ids_by_post( $post->ID );

		wp_nonce_field( 'lw_mship_restriction', 'lw_mship_restriction_nonce' );

		if ( empty( $levels ) ) {
			$this->render_no_levels();
			return;
		}

		$this->render_level_checkboxes( $levels, $selected_levels );
	}

	/**
	 * Render no levels message.
	 *
	 * @return void
	 */
	private function render_no_levels(): void {
		?>
		<p>
			<?php esc_html_e( 'No membership levels found.', 'lw-memberships' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lw-memberships-levels&action=new' ) ); ?>">
				<?php esc_html_e( 'Create one', 'lw-memberships' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Render level checkboxes.
	 *
	 * @param array<\LightweightPlugins\Memberships\Models\Level> $levels          Levels.
	 * @param array<int>                                          $selected_levels Selected level IDs.
	 * @return void
	 */
	private function render_level_checkboxes( array $levels, array $selected_levels ): void {
		?>
		<p><?php esc_html_e( 'Restrict this content to:', 'lw-memberships' ); ?></p>

		<?php foreach ( $levels as $level ) : ?>
			<label style="display: block; margin-bottom: 5px;">
				<input
					type="checkbox"
					name="lw_mship_levels[]"
					value="<?php echo esc_attr( (string) $level->id ); ?>"
					<?php checked( in_array( $level->id, $selected_levels, true ) ); ?>
				>
				<?php echo esc_html( $level->name ); ?>
			</label>
		<?php endforeach; ?>

		<p class="description" style="margin-top: 10px;">
			<?php esc_html_e( 'Leave unchecked for public access.', 'lw-memberships' ); ?>
		</p>
		<?php
	}
}
