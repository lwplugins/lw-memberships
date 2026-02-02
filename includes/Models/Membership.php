<?php
/**
 * Membership model.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Models;

/**
 * Represents a user membership.
 */
final class Membership {

	/**
	 * Membership ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public int $user_id;

	/**
	 * Level ID.
	 *
	 * @var int
	 */
	public int $level_id;

	/**
	 * Order ID.
	 *
	 * @var int|null
	 */
	public ?int $order_id;

	/**
	 * Subscription ID.
	 *
	 * @var int|null
	 */
	public ?int $subscription_id;

	/**
	 * Source (purchase, subscription, manual, import).
	 *
	 * @var string
	 */
	public string $source;

	/**
	 * Status (active, expired, cancelled, paused).
	 *
	 * @var string
	 */
	public string $status;

	/**
	 * Start date.
	 *
	 * @var string
	 */
	public string $start_date;

	/**
	 * End date.
	 *
	 * @var string|null
	 */
	public ?string $end_date;

	/**
	 * Cancelled at timestamp.
	 *
	 * @var string|null
	 */
	public ?string $cancelled_at;

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
		$membership                  = new self();
		$membership->id              = (int) $row->id;
		$membership->user_id         = (int) $row->user_id;
		$membership->level_id        = (int) $row->level_id;
		$membership->order_id        = $row->order_id ? (int) $row->order_id : null;
		$membership->subscription_id = $row->subscription_id ? (int) $row->subscription_id : null;
		$membership->source          = $row->source;
		$membership->status          = $row->status;
		$membership->start_date      = $row->start_date;
		$membership->end_date        = $row->end_date;
		$membership->cancelled_at    = $row->cancelled_at;
		$membership->created_at      = $row->created_at;
		$membership->updated_at      = $row->updated_at;

		return $membership;
	}

	/**
	 * Check if membership is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return 'active' === $this->status;
	}

	/**
	 * Check if membership is expired.
	 *
	 * @return bool
	 */
	public function is_expired(): bool {
		if ( 'expired' === $this->status ) {
			return true;
		}

		if ( null === $this->end_date ) {
			return false;
		}

		return strtotime( $this->end_date ) < time();
	}

	/**
	 * Check if membership is from subscription.
	 *
	 * @return bool
	 */
	public function is_from_subscription(): bool {
		return 'subscription' === $this->source && null !== $this->subscription_id;
	}

	/**
	 * Get remaining days.
	 *
	 * @return int|null Days remaining or null if unlimited.
	 */
	public function get_remaining_days(): ?int {
		if ( null === $this->end_date ) {
			return null;
		}

		$end  = strtotime( $this->end_date );
		$now  = time();
		$diff = $end - $now;

		if ( $diff <= 0 ) {
			return 0;
		}

		return (int) ceil( $diff / DAY_IN_SECONDS );
	}
}
