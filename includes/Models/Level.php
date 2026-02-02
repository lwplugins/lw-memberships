<?php
/**
 * Level model.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Models;

/**
 * Represents a membership level.
 */
final class Level {

	/**
	 * Level ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Level name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Level slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Level description.
	 *
	 * @var string
	 */
	public string $description;

	/**
	 * Duration type (forever, days, months, years).
	 *
	 * @var string
	 */
	public string $duration_type;

	/**
	 * Duration value.
	 *
	 * @var int|null
	 */
	public ?int $duration_value;

	/**
	 * Priority for content access.
	 *
	 * @var int
	 */
	public int $priority;

	/**
	 * Level status (active, inactive).
	 *
	 * @var string
	 */
	public string $status;

	/**
	 * Created at timestamp.
	 *
	 * @var string
	 */
	public string $created_at;

	/**
	 * Updated at timestamp.
	 *
	 * @var string
	 */
	public string $updated_at;

	/**
	 * Create from database row.
	 *
	 * @param object $row Database row.
	 * @return self
	 */
	public static function from_row( object $row ): self {
		$level                 = new self();
		$level->id             = (int) $row->id;
		$level->name           = $row->name;
		$level->slug           = $row->slug;
		$level->description    = $row->description ?? '';
		$level->duration_type  = $row->duration_type;
		$level->duration_value = $row->duration_value ? (int) $row->duration_value : null;
		$level->priority       = (int) $row->priority;
		$level->status         = $row->status;
		$level->created_at     = $row->created_at;
		$level->updated_at     = $row->updated_at;

		return $level;
	}

	/**
	 * Check if level is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return 'active' === $this->status;
	}

	/**
	 * Check if level has unlimited duration.
	 *
	 * @return bool
	 */
	public function is_unlimited(): bool {
		return 'forever' === $this->duration_type;
	}

	/**
	 * Get expiration date from a start date.
	 *
	 * @param string $start_date Start date (Y-m-d H:i:s).
	 * @return string|null Expiration date or null if unlimited.
	 */
	public function get_expiration_date( string $start_date ): ?string {
		if ( $this->is_unlimited() || null === $this->duration_value ) {
			return null;
		}

		$date = new \DateTime( $start_date );

		switch ( $this->duration_type ) {
			case 'days':
				$date->modify( "+{$this->duration_value} days" );
				break;
			case 'months':
				$date->modify( "+{$this->duration_value} months" );
				break;
			case 'years':
				$date->modify( "+{$this->duration_value} years" );
				break;
		}

		return $date->format( 'Y-m-d H:i:s' );
	}
}
