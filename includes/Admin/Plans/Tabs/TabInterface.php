<?php
/**
 * Tab interface for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans\Tabs;

/**
 * Interface for plan editor tabs.
 */
interface TabInterface {

	/**
	 * Get tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Get tab label.
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Render tab content.
	 *
	 * @return void
	 */
	public function render(): void;
}
