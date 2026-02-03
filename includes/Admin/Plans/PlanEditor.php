<?php
/**
 * Plan editor with tabs.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

use LightweightPlugins\Memberships\Database\PlanRepository;
use LightweightPlugins\Memberships\Models\Plan;
use LightweightPlugins\Memberships\Admin\Plans\Tabs\TabGeneral;
use LightweightPlugins\Memberships\Admin\Plans\Tabs\TabContent;
use LightweightPlugins\Memberships\Admin\Plans\Tabs\TabWooCommerce;
use LightweightPlugins\Memberships\Admin\Plans\Tabs\TabMembers;
use LightweightPlugins\Memberships\Plugin;

/**
 * Plan editor form with tabbed interface.
 */
final class PlanEditor {

	/**
	 * Plan ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Plan data.
	 *
	 * @var Plan|null
	 */
	private ?Plan $plan;

	/**
	 * Tabs.
	 *
	 * @var array<\LightweightPlugins\Memberships\Admin\Plans\Tabs\TabInterface>
	 */
	private array $tabs;

	/**
	 * Constructor.
	 *
	 * @param int $id Plan ID.
	 */
	public function __construct( int $id ) {
		$this->id   = $id;
		$this->plan = $id > 0 ? PlanRepository::get_by_id( $id ) : null;
		$this->tabs = $this->register_tabs();
	}

	/**
	 * Register tabs.
	 *
	 * @return array<\LightweightPlugins\Memberships\Admin\Plans\Tabs\TabInterface>
	 */
	private function register_tabs(): array {
		$tabs = [
			new TabGeneral( $this->plan ),
			new TabContent( $this->id ),
		];

		if ( Plugin::is_woocommerce_active() ) {
			$tabs[] = new TabWooCommerce( $this->id );
		}

		$tabs[] = new TabMembers( $this->id );

		return $tabs;
	}

	/**
	 * Render the editor.
	 *
	 * @return void
	 */
	public function render(): void {
		$is_new     = null === $this->plan;
		$title      = $is_new ? __( 'Add New Plan', 'lw-memberships' ) : __( 'Edit Plan', 'lw-memberships' );
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		?>
		<h1><?php echo esc_html( $title ); ?></h1>

		<?php $this->render_tab_navigation( $active_tab ); ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'save_plan', 'lw_mship_nonce' ); ?>
			<input type="hidden" name="lw_mship_action" value="save_plan">
			<input type="hidden" name="plan_id" value="<?php echo esc_attr( (string) $this->id ); ?>">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<?php $this->render_active_tab( $active_tab ); ?>

			<?php
			$btn_label = $is_new
				? __( 'Create Plan', 'lw-memberships' )
				: __( 'Update Plan', 'lw-memberships' );
			submit_button( $btn_label );
			?>
		</form>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . PlansPage::SLUG ) ); ?>">
				&larr; <?php esc_html_e( 'Back to Plans', 'lw-memberships' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Render tab navigation.
	 *
	 * @param string $active_tab Active tab slug.
	 * @return void
	 */
	private function render_tab_navigation( string $active_tab ): void {
		$base_url = admin_url( 'admin.php?page=' . PlansPage::SLUG );
		$action   = $this->id > 0 ? 'edit' : 'new';

		?>
		<nav class="nav-tab-wrapper lw-mship-plan-tabs">
			<?php foreach ( $this->tabs as $tab ) : ?>
				<?php
				$url   = add_query_arg(
					[
						'action' => $action,
						'id'     => $this->id,
						'tab'    => $tab->get_slug(),
					],
					$base_url
				);
				$class = $active_tab === $tab->get_slug() ? 'nav-tab nav-tab-active' : 'nav-tab';
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
					<?php echo esc_html( $tab->get_label() ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Render active tab content.
	 *
	 * @param string $active_tab Active tab slug.
	 * @return void
	 */
	private function render_active_tab( string $active_tab ): void {
		foreach ( $this->tabs as $tab ) {
			if ( $tab->get_slug() === $active_tab ) {
				$tab->render();
				return;
			}
		}

		// Default to first tab.
		if ( ! empty( $this->tabs ) ) {
			$this->tabs[0]->render();
		}
	}
}
