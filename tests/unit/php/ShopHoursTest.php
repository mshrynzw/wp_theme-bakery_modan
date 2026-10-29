<?php
/**
 * Tests for shop hours and bake schedule helpers.
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ShopHoursTest extends TestCase {
	public function test_parse_clock_accepts_two_digit_hours(): void {
		self::assertSame(600, bkery_parse_clock('10:00'));
		self::assertSame(545, bkery_parse_clock('09:05'));
		self::assertSame(0, bkery_parse_clock('00:00'));
		self::assertSame(1439, bkery_parse_clock('23:59'));
	}

	public function test_parse_clock_rejects_loose_formats(): void {
		self::assertNull(bkery_parse_clock('9:05'));
		self::assertNull(bkery_parse_clock('24:00'));
		self::assertNull(bkery_parse_clock('10:60'));
		self::assertNull(bkery_parse_clock(''));
	}

	public function test_sanitize_clock_keeps_valid_values_only(): void {
		self::assertSame('18:00', bkery_sanitize_clock(' 18:00 '));
		self::assertSame('', bkery_sanitize_clock('18時'));
	}

	public function test_closed_weekdays_are_unique_and_sorted(): void {
		self::assertSame(array(0, 3), bkery_parse_closed_weekdays('3, 0, 3'));
		self::assertSame(array(2, 6), bkery_parse_closed_weekdays("2、 6\n7"));
		self::assertSame('', bkery_format_closed_weekdays(array()));
		self::assertSame('0,3', bkery_format_closed_weekdays(bkery_parse_closed_weekdays('3,0')));
	}

	public function test_bake_times_keep_first_occurrence_order(): void {
		self::assertSame('11:00,10:00', bkery_sanitize_bake_times("11:00\n10:00、11:00 25:00"));
	}

	public function test_build_bake_schedule_groups_by_time_and_keeps_product_order(): void {
		$schedule = bkery_build_bake_schedule(
			array(
				array(
					'id'    => 'focaccia',
					'times' => '11:00,10:00',
				),
				array(
					'id'    => 'croissant',
					'times' => array('10:00', 'bad'),
				),
				array(
					'id'    => '',
					'times' => '12:00',
				),
			)
		);

		self::assertSame(
			array(
				array(
					'time'  => '10:00',
					'items' => array('focaccia', 'croissant'),
				),
				array(
					'time'  => '11:00',
					'items' => array('focaccia'),
				),
			),
			$schedule
		);
	}
}
