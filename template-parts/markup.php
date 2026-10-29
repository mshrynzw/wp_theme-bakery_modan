<?php
/**
 * テンプレートの表示分岐。
 *
 * パンの取得は bkery_get_frontend_data() と bkery_get_breads_by_line() を
 * テンプレートから呼ぶ。ここには別名の取得関数を置かない。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * 翻訳関数が無い単体テストでも日本語のまま返す。
 */
function bkery_template_translate( string $text ): string {
	if ( function_exists( '__' ) ) {
		return __( $text, 'bakery-s' );
	}

	return $text;
}

/**
 * テキストノード用のエスケープ。
 */
function bkery_template_esc( string $text ): string {
	if ( function_exists( 'esc_html' ) ) {
		return esc_html( $text );
	}

	return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

/**
 * 属性用のエスケープ。
 */
function bkery_template_esc_attr( string $text ): string {
	if ( function_exists( 'esc_attr' ) ) {
		return esc_attr( $text );
	}

	return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

/**
 * 改行を <br> にしたテキスト。
 */
function bkery_template_nl2br( string $text ): string {
	$html = nl2br( bkery_template_esc( $text ), false );

	return str_replace( array( "<br>\r\n", "<br>\n", "<br>\r" ), '<br>', $html );
}

/**
 * 前後の空白を除いた表示文。空なら出さない。
 */
function bkery_template_visible_text( string $text ): string {
	return trim( $text );
}

/**
 * 装飾アイコン。モックの symbol id 以外は出さない。
 */
function bkery_template_icon( string $symbol, string $class = 'icon', bool $hidden = false ): string {
	$allowed = array(
		'i-pin',
		'i-clock',
		'i-insta',
		'i-loaf',
		'i-leaf',
		'i-wheat',
		'i-cup',
		'i-play',
		'i-prev',
		'i-next',
		'i-close',
		'i-copy',
		'i-out',
		'i-expand',
	);
	if ( ! in_array( $symbol, $allowed, true ) ) {
		return '';
	}

	$class = trim( $class );
	if ( '' === $class ) {
		$class = 'icon';
	}

	$hidden_attr = $hidden ? ' aria-hidden="true"' : '';

	return '<svg class="' . bkery_template_esc_attr( $class ) . '"' . $hidden_attr . '><use href="#' . bkery_template_esc_attr( $symbol ) . '"/></svg>';
}

/**
 * ページ内目次。表示文言は設計書 4.1 の固定ラベル。
 *
 * @return array<string, string>
 */
function bkery_template_rail_links(): array {
	return array(
		'hero'      => 'トップ',
		'concept'   => 'コンセプト',
		'gallery'   => '店内のようす',
		'season'    => '季節限定のパン',
		'menu'      => 'いつものパン',
		'bake'      => '焼き上がり時間',
		'owner'     => '店主・お店の想い',
		'shop'      => 'お店について',
		'instagram' => 'Instagram',
	);
}

/**
 * 固定ナビ。ラベルはモックの短い文言。
 *
 * @return array<int, array{id:string, icon:string, label:string}>
 */
function bkery_template_dock_links(): array {
	return array(
		array(
			'id'    => 'season',
			'icon'  => 'i-leaf',
			'label' => '季節限定',
		),
		array(
			'id'    => 'menu',
			'icon'  => 'i-loaf',
			'label' => 'メニュー',
		),
		array(
			'id'    => 'bake',
			'icon'  => 'i-clock',
			'label' => '焼き上がり',
		),
		array(
			'id'    => 'shop',
			'icon'  => 'i-pin',
			'label' => 'アクセス',
		),
		array(
			'id'    => 'instagram',
			'icon'  => 'i-insta',
			'label' => 'Instagram',
		),
	);
}

/**
 * セクション見出しのアイコン。
 */
function bkery_template_section_icon( string $section ): string {
	$icons = array(
		'concept'   => 'i-wheat',
		'gallery'   => 'i-cup',
		'season'    => 'i-leaf',
		'menu'      => 'i-loaf',
		'bake'      => 'i-clock',
		'owner'     => 'i-wheat',
		'shop'      => 'i-pin',
		'instagram' => 'i-insta',
	);

	return $icons[ $section ] ?? '';
}

/**
 * 魅力 1〜3 のアイコン。モックの並び。
 */
function bkery_template_point_icon( int $index ): string {
	$icons = array(
		1 => 'i-wheat',
		2 => 'i-clock',
		3 => 'i-cup',
	);

	return $icons[ $index ] ?? '';
}

/**
 * 季節限定の区分か。空や未知の値は通年。
 */
function bkery_template_is_seasonal( string $line ): bool {
	return 'seasonal' === $line;
}

/**
 * HH:MM として解釈できるか。
 */
function bkery_template_clock_is_valid( string $value ): bool {
	if ( function_exists( 'bkery_parse_clock' ) ) {
		return null !== bkery_parse_clock( $value );
	}

	return '' !== $value;
}

/**
 * 閉店が開店より後の営業時間か。
 */
function bkery_template_clocks_are_open_range( string $open, string $close ): bool {
	if ( function_exists( 'bkery_parse_clock' ) ) {
		$open_min  = bkery_parse_clock( $open );
		$close_min = bkery_parse_clock( $close );
		if ( null === $open_min || null === $close_min ) {
			return false;
		}

		return $close_min > $open_min;
	}

	return '' !== $open && '' !== $close;
}

/**
 * ステータスの初期表示。端末時刻の判定はフロントが上書きする。
 */
function bkery_template_hours_status_label( string $open, string $close ): string {
	if ( ! bkery_template_clocks_are_open_range( $open, $close ) ) {
		return bkery_template_translate( '営業時間を確認してください' );
	}

	return sprintf(
		bkery_template_translate( '営業時間 %1$s〜%2$s' ),
		$open,
		$close
	);
}

/**
 * ご利用時間の大きい表示。
 */
function bkery_template_hours_range( string $open, string $close ): string {
	return $open . '〜' . $close;
}

/**
 * カフェ利用時間の補足。
 */
function bkery_template_cafe_hours_sentence( string $open, string $close ): string {
	return sprintf(
		bkery_template_translate( 'カフェのご利用は%1$s〜%2$sまでとなります。' ),
		$open,
		$close
	);
}

/**
 * 0 円は空。それ以外は数値と「円」。
 */
function bkery_template_price_html( int $price ): string {
	if ( $price <= 0 ) {
		return '';
	}

	$formatted = function_exists( 'number_format_i18n' ) ? number_format_i18n( $price ) : number_format( $price );
	$number    = bkery_template_esc( (string) $formatted );

	return $number . '<small>' . bkery_template_esc( bkery_template_translate( '円' ) ) . '</small>';
}

/**
 * href に使える URL。ページ内アンカーと http(s) だけ。
 */
function bkery_template_href( string $url ): string {
	$url = trim( $url );
	if ( 1 === preg_match( '/^#[A-Za-z][\w-]*$/', $url ) ) {
		return $url;
	}
	if ( 1 !== preg_match( '#^https?://#i', $url ) ) {
		return '';
	}
	if ( 1 === preg_match( '/[\s"\'<>]/', $url ) ) {
		return '';
	}

	return $url;
}

/**
 * http(s) のリンクか。
 */
function bkery_template_is_external_href( string $href ): bool {
	return 1 === preg_match( '#^https?://#i', $href );
}

/**
 * 属性へ入れる href。
 */
function bkery_template_href_attr( string $url ): string {
	$href = bkery_template_href( $url );
	if ( '' === $href ) {
		return '';
	}
	if ( function_exists( 'esc_url' ) && bkery_template_is_external_href( $href ) ) {
		$safe = esc_url( $href );
		return is_string( $safe ) ? $safe : '';
	}

	return bkery_template_esc_attr( $href );
}

/**
 * トップのセクションへの href。トップ以外はフロントページの URL を付ける。
 */
function bkery_template_anchor( string $fragment, bool $on_front, string $front_url ): string {
	$fragment = ltrim( trim( $fragment ), '#' );
	if ( 1 !== preg_match( '/^[A-Za-z][\w-]*$/', $fragment ) ) {
		return '';
	}
	if ( $on_front ) {
		return '#' . $fragment;
	}

	$front_url = trim( $front_url );
	if ( '' === $front_url ) {
		return '#' . $fragment;
	}

	return rtrim( $front_url, '/' ) . '/#' . $fragment;
}

/**
 * 今のリクエストに合わせたアンカー。
 */
function bkery_template_current_anchor( string $fragment ): string {
	$on_front  = function_exists( 'is_front_page' ) && is_front_page();
	$front_url = '';
	if ( ! $on_front && function_exists( 'get_option' ) ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id > 0 && function_exists( 'get_permalink' ) ) {
			$permalink = get_permalink( $front_id );
			if ( is_string( $permalink ) ) {
				$front_url = $permalink;
			}
		}
		if ( '' === $front_url && function_exists( 'home_url' ) ) {
			$home      = home_url( '/' );
			$front_url = is_string( $home ) ? $home : '';
		}
	}

	return bkery_template_anchor( $fragment, $on_front, $front_url );
}

/**
 * 魅力のテキストリンク。ラベルか URL が空なら出さない。
 *
 * @return array{label:string, href:string, external:bool}
 */
function bkery_template_point_link( string $label, string $url ): array {
	$label = trim( $label );
	$href  = bkery_template_href( $url );
	if ( '' === $label || '' === $href ) {
		return array();
	}

	return array(
		'label'    => $label,
		'href'     => $href,
		'external' => bkery_template_is_external_href( $href ),
	);
}

/**
 * 添付が無いときの data-label。ファイル名が取れればそれを使う。
 */
function bkery_template_media_label( int $attachment_id, string $fallback ): string {
	if ( $attachment_id > 0 && function_exists( 'get_attached_file' ) ) {
		$file = get_attached_file( $attachment_id );
		if ( is_string( $file ) && '' !== $file ) {
			$name = pathinfo( $file, PATHINFO_FILENAME );
			if ( is_string( $name ) && '' !== $name ) {
				return $name;
			}
		}
	}

	return $fallback;
}

/**
 * 添付画像。未設定なら空文字で、呼び出し側は img を出さない。
 */
function bkery_template_image_html( int $attachment_id, string $size, bool $high_priority = false, ?string $alt = null ): string {
	if ( $attachment_id <= 0 || ! function_exists( 'wp_get_attachment_image' ) ) {
		return '';
	}

	$attr = array();
	if ( $high_priority ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = false;
	} else {
		$attr['loading']  = 'lazy';
		$attr['decoding'] = 'async';
	}

	if ( is_string( $alt ) && '' !== $alt ) {
		$stored = '';
		if ( function_exists( 'get_post_meta' ) ) {
			$stored = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
		}
		if ( '' === $stored ) {
			$attr['alt'] = $alt;
		}
	}

	$html = wp_get_attachment_image( $attachment_id, $size, false, $attr );

	return is_string( $html ) ? $html : '';
}

/**
 * 画像かキャプションがある店内写真だけ残す。
 *
 * @param array<int, array<string, mixed>> $slots
 * @return array<int, array{image_id:int, caption:string, label:string}>
 */
function bkery_template_visible_gallery_slots( array $slots ): array {
	$visible = array();
	foreach ( $slots as $slot ) {
		if ( ! is_array( $slot ) ) {
			continue;
		}
		$image_id = isset( $slot['image_id'] ) ? (int) $slot['image_id'] : 0;
		$caption  = isset( $slot['caption'] ) ? trim( (string) $slot['caption'] ) : '';
		if ( $image_id <= 0 && '' === $caption ) {
			continue;
		}
		$visible[] = array(
			'image_id' => max( 0, $image_id ),
			'caption'  => $caption,
			'label'    => isset( $slot['label'] ) ? (string) $slot['label'] : '',
		);
	}

	return $visible;
}

/**
 * 動画 URL が空のとき、準備中の枠を出す。
 */
function bkery_template_should_show_video_placeholder( string $video_url ): bool {
	return '' === bkery_template_href( $video_url );
}

/**
 * 先頭タームのスラッグ。
 *
 * @param array<int, mixed> $terms
 */
function bkery_template_primary_category_slug( array $terms ): string {
	if ( ! isset( $terms[0] ) ) {
		return '';
	}
	$term = $terms[0];
	if ( is_object( $term ) && isset( $term->slug ) ) {
		return (string) $term->slug;
	}
	if ( is_array( $term ) && isset( $term['slug'] ) ) {
		return (string) $term['slug'];
	}

	return '';
}

/**
 * スラッグごとの件数。空スラッグは数えない。
 *
 * @param array<int, string> $slugs
 * @return array<string, int>
 */
function bkery_template_count_by_slug( array $slugs ): array {
	$counts = array();
	foreach ( $slugs as $slug ) {
		$slug = (string) $slug;
		if ( '' === $slug ) {
			continue;
		}
		if ( ! isset( $counts[ $slug ] ) ) {
			$counts[ $slug ] = 0;
		}
		++$counts[ $slug ];
	}

	return $counts;
}

/**
 * 「すべて」を先頭にした件数。
 *
 * @param array<string, int> $by_slug
 * @return array<string, int>
 */
function bkery_template_menu_counts( int $total, array $by_slug ): array {
	$counts = array(
		'all' => max( 0, $total ),
	);
	foreach ( $by_slug as $slug => $count ) {
		$slug = (string) $slug;
		if ( '' === $slug || 'all' === $slug ) {
			continue;
		}
		$counts[ $slug ] = max( 0, (int) $count );
	}

	return $counts;
}

/**
 * 焼き上がり配列を {time, items} に揃える。並びは渡された順のまま。
 *
 * @param array<string, mixed> $frontend
 * @return array<int, array{time:string, items:array<int, string>}>
 */
function bkery_template_bake_schedule( array $frontend ): array {
	if ( ! isset( $frontend['bakeSchedule'] ) || ! is_array( $frontend['bakeSchedule'] ) ) {
		return array();
	}

	$slots = array();
	foreach ( $frontend['bakeSchedule'] as $slot ) {
		if ( ! is_array( $slot ) ) {
			continue;
		}
		$time = isset( $slot['time'] ) ? trim( (string) $slot['time'] ) : '';
		if ( '' === $time ) {
			continue;
		}
		$items = isset( $slot['items'] ) && is_array( $slot['items'] ) ? $slot['items'] : array();
		$ids   = array();
		foreach ( $items as $item ) {
			$item = trim( (string) $item );
			if ( '' !== $item ) {
				$ids[] = $item;
			}
		}
		$slots[] = array(
			'time'  => $time,
			'items' => $ids,
		);
	}

	return $slots;
}

/**
 * スケジュールに無い商品はチップにしない。
 *
 * @param array<int, string> $ids
 * @param array<string, array{name:string, seasonal:bool}> $products
 * @return array<int, array{id:string, name:string, seasonal:bool}>
 */
function bkery_template_bake_chips( array $ids, array $products ): array {
	$chips = array();
	foreach ( $ids as $id ) {
		$id = (string) $id;
		if ( ! isset( $products[ $id ] ) || ! is_array( $products[ $id ] ) ) {
			continue;
		}
		$name = trim( (string) ( $products[ $id ]['name'] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}
		$chips[] = array(
			'id'       => $id,
			'name'     => $name,
			'seasonal' => ! empty( $products[ $id ]['seasonal'] ),
		);
	}

	return $chips;
}

/**
 * 焼き上がりチップ。季節限定だけドットと aria-label を付ける。
 *
 * @param array{id:string, name:string, seasonal:bool} $chip
 */
function bkery_template_chip_html( array $chip ): string {
	$id       = bkery_template_esc_attr( (string) $chip['id'] );
	$name     = bkery_template_esc( (string) $chip['name'] );
	$seasonal = ! empty( $chip['seasonal'] );
	$aria     = '';
	if ( $seasonal ) {
		$aria = ' aria-label="' . bkery_template_esc_attr( bkery_template_translate( '期間限定' ) . ' ' . (string) $chip['name'] ) . '"';
	}
	$dot = $seasonal ? '<span class="dot-season" aria-hidden="true"></span>' : '';

	return '<button type="button" data-product="' . $id . '" aria-haspopup="dialog"' . $aria . '>' . $dot . $name . '</button>';
}

/**
 * 通年メニューのタブ。data-cat はモック、data-category は設計書 4.3。
 */
function bkery_template_menu_tab_html( string $slug, string $label, int $count, bool $pressed ): string {
	$slug = bkery_template_esc_attr( $slug );

	return '<button type="button" data-category="' . $slug . '" data-cat="' . $slug . '" aria-pressed="' . ( $pressed ? 'true' : 'false' ) . '">'
		. bkery_template_esc( $label )
		. '<span>' . bkery_template_esc( (string) max( 0, $count ) ) . '</span></button>';
}

/**
 * 季節限定カルーセルの 1 枚。クラスはモックの renderSeasonal に合わせる。
 *
 * @param array{slug:string, name:string, excerpt:string, price:int, image_html:string, label:string, index:int, total:int} $card
 */
function bkery_template_season_card_html( array $card ): string {
	$index    = (int) $card['index'];
	$total    = (int) $card['total'];
	$position = bkery_template_esc_attr( $index . ' / ' . $total );
	$slug     = bkery_template_esc_attr( (string) $card['slug'] );
	$name     = bkery_template_esc( (string) $card['name'] );
	$excerpt  = bkery_template_esc( (string) $card['excerpt'] );
	$label    = bkery_template_esc_attr( (string) $card['label'] );
	$price    = bkery_template_price_html( (int) $card['price'] );
	$price_html = '' !== $price ? '<span class="price">' . $price . '</span>' : '';
	$image    = (string) ( $card['image_html'] ?? '' );
	$leaf     = bkery_template_icon( 'i-leaf', 'icon', true );
	$next     = bkery_template_icon( 'i-next', 'icon', true );
	$limited  = bkery_template_esc( bkery_template_translate( '期間限定' ) );
	$more     = bkery_template_esc( bkery_template_translate( 'くわしく見る' ) );

	return '<li class="carousel__slide" role="group" aria-roledescription="' . bkery_template_esc_attr( bkery_template_translate( 'スライド' ) ) . '" aria-label="' . $position . '">'
		. '<button class="season-card" type="button" data-product="' . $slug . '" aria-haspopup="dialog">'
		. '<span class="season-card__media">'
		. '<span class="tag tag--season">' . $leaf . $limited . '</span>'
		. '<span class="ph" data-label="' . $label . '">' . $image . '</span>'
		. '</span>'
		. '<span class="season-card__body">'
		. '<span class="season-card__name">' . $name . '</span>'
		. '<span class="season-card__excerpt">' . $excerpt . '</span>'
		. '<span class="season-card__foot">'
		. $price_html
		. '<span class="season-card__more">' . $more . $next . '</span>'
		. '</span>'
		. '</span>'
		. '</button>'
		. '</li>';
}

/**
 * 通年グリッドの 1 件。クラスはモックの renderMenu に合わせる。
 *
 * @param array{slug:string, name:string, category:string, price:int, image_html:string, label:string} $card
 */
function bkery_template_menu_card_html( array $card ): string {
	$slug     = bkery_template_esc_attr( (string) $card['slug'] );
	$category = bkery_template_esc_attr( (string) $card['category'] );
	$name     = bkery_template_esc( (string) $card['name'] );
	$label    = bkery_template_esc_attr( (string) $card['label'] );
	$price    = bkery_template_price_html( (int) $card['price'] );
	$price_html = '' !== $price ? '<span class="price">' . $price . '</span>' : '';
	$image    = (string) ( $card['image_html'] ?? '' );
	$regular  = bkery_template_esc( bkery_template_translate( '定番' ) );

	return '<li data-cat="' . $category . '" data-category="' . $category . '">'
		. '<button class="menu-card" type="button" data-product="' . $slug . '" aria-haspopup="dialog">'
		. '<span class="ph" data-label="' . $label . '">' . $image . '</span>'
		. '<span class="menu-card__row">'
		. '<span class="menu-card__name">' . $name . '</span>'
		. '<span class="tag tag--regular">' . $regular . '</span>'
		. '</span>'
		. $price_html
		. '</button>'
		. '</li>';
}

/**
 * フロントデータからメディア URL を読む。
 *
 * @param array<string, mixed> $frontend
 */
function bkery_template_media_url( array $frontend, string $key ): string {
	if ( ! isset( $frontend['media'] ) || ! is_array( $frontend['media'] ) ) {
		return '';
	}
	if ( ! isset( $frontend['media'][ $key ] ) ) {
		return '';
	}

	return trim( (string) $frontend['media'][ $key ] );
}

/**
 * この商品の焼き上がり時刻。スケジュールにあればその順、無ければメタの出現順。
 *
 * @param array<int, array{time:string, items:array<int, string>}> $schedule
 * @return array<int, string>
 */
function bkery_template_product_bake_times( array $schedule, string $slug, string $meta_times ): array {
	$times = array();
	if ( '' !== $slug ) {
		foreach ( $schedule as $slot ) {
			if ( in_array( $slug, $slot['items'], true ) ) {
				$times[] = $slot['time'];
			}
		}
	}
	if ( array() !== $times ) {
		return $times;
	}

	$fallback = array();
	foreach ( explode( ',', $meta_times ) as $part ) {
		$part = trim( $part );
		if ( '' !== $part ) {
			$fallback[] = $part;
		}
	}

	return $fallback;
}
