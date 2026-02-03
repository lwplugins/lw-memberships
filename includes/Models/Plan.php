<?php
/**
 * Plan model.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Models;

/**
 * Represents a membership plan.
 */
final class Plan {

	/**
	 * Plan ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Plan name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Plan slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Plan description.
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
	 * Plan status (active, inactive).
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
		$plan                 = new self();
		$plan->id             = (int) $row->id;
		$plan->name           = $row->name;
		$plan->slug           = $row->slug;
		$plan->description    = $row->description ?? '';
		$plan->duration_type  = $row->duration_type;
		$plan->duration_value = $row->duration_value ? (int) $row->duration_value : null;
		$plan->priority       = (int) $row->priority;
		$plan->status         = $row->status;
		$plan->created_at     = $row->created_at;
		$plan->updated_at     = $row->updated_at;

		return $plan;
	}

	/**
	 * Check if plan is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return 'active' === $this->status;
	}

	/**
	 * Check if plan has unlimited duration.
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
