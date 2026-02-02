<?php
/**
 * Levels admin page.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Levels;

use LightweightPlugins\Memberships\Admin\ParentPage;

/**
 * Handles the levels admin page.
 */
final class LevelsPage {

	/**
	 * Page slug.
	 */
	public const SLUG = 'lw-memberships-levels';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ LevelSaveHandler::class, 'handle' ] );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			ParentPage::SLUG,
			__( 'Membership Levels', 'lw-memberships' ),
			__( 'Levels', 'lw-memberships' ),
			'manage_options',
			self::SLUG,
			[ $this, 'render' ]
		);
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
		$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		?>
		<div class="wrap">
			<?php
			if ( 'edit' === $action || 'new' === $action ) {
				$this->render_editor( $id );
			} else {
				$this->render_list();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the list view.
	 *
	 * @return void
	 */
	private function render_list(): void {
		$list_table = new LevelsListTable();
		$list_table->prepare_items();

		?>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Membership Levels', 'lw-memberships' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '&action=new' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'lw-memberships' ); ?>
		</a>
		<hr class="wp-header-end">

		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>">
			<?php $list_table->display(); ?>
		</form>
		<?php
	}

	/**
	 * Render the editor.
	 *
	 * @param int $id Level ID.
	 * @return void
	 */
	private function render_editor( int $id ): void {
		$editor = new LevelEditor( $id );
		$editor->render();
	}
}
