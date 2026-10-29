<?php
/**
 * 営業時間・定休日・焼き上がり時刻の純粋関数。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * 「HH:MM」をその日の経過分に変換する。形式が違えば null。
 */
function bkery_parse_clock( string $value ): ?int {
	$value = trim( $value );
	if ( ! preg_match( '/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $value, $matches ) ) {
		return null;
	}

	return ( (int) $matches[1] ) * 60 + (int) $matches[2];
}

/**
 * 保存用に時刻を正規化する。不正な値は空文字。
 */
function bkery_sanitize_clock( string $value ): string {
	$value = trim( $value );
	if ( null === bkery_parse_clock( $value ) ) {
		return '';
	}

	return $value;
}

/**
 * 「0,3」や「2、 6」を 0〜6 の重複なし配列にする。0 は日曜。
 *
 * @return array<int, int>
 */
function bkery_parse_closed_weekdays( string $value ): array {
	$parts = preg_split( '/[\s,、]+/u', trim( $value ) );
	if ( false === $parts ) {
		return array();
	}

	$days = array();
	foreach ( $parts as $part ) {
		if ( '' === $part || ! preg_match( '/^[0-6]$/', $part ) ) {
			continue;
		}
		$days[] = (int) $part;
	}

	$days = array_values( array_unique( $days ) );
	sort( $days );

	return $days;
}

/**
 * 定休日の配列を設定値の文字列に戻す。
 *
 * @param array<int, int> $days 0〜6。
 */
function bkery_format_closed_weekdays( array $days ): string {
	$clean = array();
	foreach ( $days as $day ) {
		if ( is_int( $day ) && $day >= 0 && $day <= 6 ) {
			$clean[] = $day;
		}
	}
	$clean = array_values( array_unique( $clean ) );
	sort( $clean );

	return implode( ',', $clean );
}

/**
 * 焼き上がり時刻の入力を「HH:MM,HH:MM」に整える。出現順を保ち、不正と重複は捨てる。
 */
function bkery_sanitize_bake_times( string $value ): string {
	$parts = preg_split( '/[\s,、]+/u', trim( $value ) );
	if ( false === $parts ) {
		return '';
	}

	$times = array();
	foreach ( $parts as $part ) {
		if ( '' === $part || null === bkery_parse_clock( $part ) ) {
			continue;
		}
		if ( in_array( $part, $times, true ) ) {
			continue;
		}
		$times[] = $part;
	}

	return implode( ',', $times );
}

/**
 * 商品ごとの焼き上がり時刻を、時刻順の枠一覧にする。
 *
 * 各要素は array{id:string, times:array<int, string>|string}。
 * 同じ時刻の商品は、引数の並びを保つ。
 *
 * @param array<int, array<string, mixed>> $rows 商品行。
 * @return array<int, array{time:string, items:array<int, string>}>
 */
function bkery_build_bake_schedule( array $rows ): array {
	$buckets = array();

	foreach ( $rows as $row ) {
		$id = isset( $row['id'] ) ? trim( (string) $row['id'] ) : '';
		if ( '' === $id ) {
			continue;
		}

		$times = $row['times'] ?? array();
		if ( is_string( $times ) ) {
			$sanitized = bkery_sanitize_bake_times( $times );
			$times     = '' === $sanitized ? array() : explode( ',', $sanitized );
		}
		if ( ! is_array( $times ) ) {
			continue;
		}

		foreach ( $times as $time ) {
			$time = trim( (string) $time );
			if ( null === bkery_parse_clock( $time ) ) {
				continue;
			}
			if ( ! isset( $buckets[ $time ] ) ) {
				$buckets[ $time ] = array();
			}
			if ( ! in_array( $id, $buckets[ $time ], true ) ) {
				$buckets[ $time ][] = $id;
			}
		}
	}

	$order = array_keys( $buckets );
	usort(
		$order,
		static function ( $left, $right ) {
			return ( bkery_parse_clock( (string) $left ) ?? 0 ) <=> ( bkery_parse_clock( (string) $right ) ?? 0 );
		}
	);

	$schedule = array();
	foreach ( $order as $time ) {
		$schedule[] = array(
			'time'  => (string) $time,
			'items' => $buckets[ $time ],
		);
	}

	return $schedule;
}
