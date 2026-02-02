<?php
/**
 * Content rule model.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Models;

/**
 * Represents a content restriction rule.
 */
final class ContentRule {

	/**
	 * Rule ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Post ID.
	 *
	 * @var int
	 */
	public int $post_id;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	public string $post_type;

	/**
	 * Level ID.
	 *
	 * @var int
	 */
	public int $level_id;

	/**
	 * Created at timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Create from database row.
	 *
	 * @param object $row Database row.
	 * @return self
	 */
	public static function from_row( object $row ): self {
		$rule             = new self();
		$rule->id         = (int) $row->id;
		$rule->post_id    = (int) $row->post_id;
		$rule->post_type  = $row->post_type;
		$rule->level_id   = (int) $row->level_id;
		$rule->created_at = $row->created_at;

		return $rule;
	}
}
