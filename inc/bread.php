<?php
/**
 * CPT bread とタクソノミー bread_category。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * 区分を seasonal か regular に揃える。それ以外は regular。
 */
function bkery_sanitize_bread_line( string $value ): string {
	$value = trim( $value );
	return 'seasonal' === $value ? 'seasonal' : 'regular';
}

/**
 * 添付 ID として使える正の整数。それ以外は 0。
 *
 * @param mixed $value 入力。
 */
function bkery_positive_int( $value ): int {
	if ( is_int( $value ) ) {
		return $value > 0 ? $value : 0;
	}
	if ( is_float( $value ) ) {
		$number = (int) $value;
		return $number > 0 ? $number : 0;
	}
	if ( is_string( $value ) && preg_match( '/^\d+$/', trim( $value ) ) ) {
		$number = (int) $value;
		return $number > 0 ? $number : 0;
	}

	return 0;
}

/**
 * 正の ID を出現順のまま上限まで残す。
 *
 * @param array<int, mixed> $ids   候補。
 * @param int               $limit 最大件数。
 * @return array<int, int>
 */
function bkery_positive_id_list( array $ids, int $limit ): array {
	if ( $limit < 1 ) {
		return array();
	}

	$clean = array();
	foreach ( $ids as $id ) {
		$id = bkery_positive_int( $id );
		if ( $id < 1 ) {
			continue;
		}
		$clean[] = $id;
		if ( count( $clean ) >= $limit ) {
			break;
		}
	}

	return $clean;
}

/**
 * シーン背景の添付 ID。自前が1件でもあればそれを使い、無ければ代替を順に探す。
 *
 * @param array<int, mixed>              $own_ids         シーン自身の添付 ID。
 * @param array<int, array<int, mixed>>  $fallback_groups 代替グループ。先のグループを優先する。
 * @param int                            $limit           最大件数。
 * @return array<int, int>
 */
function bkery_pick_scene_image_ids( array $own_ids, array $fallback_groups, int $limit = 3 ): array {
	$own = bkery_positive_id_list( $own_ids, $limit );
	if ( array() !== $own ) {
		return $own;
	}

	foreach ( $fallback_groups as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}
		$ids = bkery_positive_id_list( $group, $limit );
		if ( array() !== $ids ) {
			return $ids;
		}
	}

	return array();
}

/**
 * カード用にタグと余分な空白を除いた文字列。
 */
function bkery_bread_plain_text( string $html ): string {
	$stripped = preg_replace( '/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html );
	if ( ! is_string( $stripped ) ) {
		$stripped = $html;
	}

	$text = strip_tags( $stripped );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = str_replace( "\xc2\xa0", ' ', $text );
	$collapsed = preg_replace( '/\s+/u', ' ', $text );
	if ( ! is_string( $collapsed ) ) {
		$collapsed = $text;
	}

	return trim( $collapsed );
}

/**
 * 本文の最初の段落。空の段落は飛ばす。
 */
function bkery_bread_first_paragraph( string $content ): string {
	$content = trim( $content );
	if ( '' === $content ) {
		return '';
	}

	if ( preg_match_all( '/<p\b[^>]*>(.*?)<\/p>/isu', $content, $matches ) ) {
		foreach ( $matches[1] as $inner ) {
			$text = bkery_bread_plain_text( (string) $inner );
			if ( '' !== $text ) {
				return $text;
			}
		}
	}

	$plain = preg_replace( '/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $content );
	if ( ! is_string( $plain ) ) {
		$plain = $content;
	}
	$plain = html_entity_decode( strip_tags( $plain ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$parts = preg_split( '/\R[ \t]*\R/u', trim( $plain ) );
	if ( false === $parts ) {
		return bkery_bread_plain_text( $content );
	}

	foreach ( $parts as $part ) {
		$text = bkery_bread_plain_text( (string) $part );
		if ( '' !== $text ) {
			return $text;
		}
	}

	return '';
}

/**
 * カードの短い文。抜粋が空なら本文の最初の段落。
 */
function bkery_bread_card_excerpt_text( string $excerpt, string $content ): string {
	$excerpt = bkery_bread_plain_text( $excerpt );
	if ( '' !== $excerpt ) {
		return $excerpt;
	}

	return bkery_bread_first_paragraph( $content );
}

/**
 * 初期分類。オプションが無いときだけ作る。
 *
 * @return array<int, array{name:string, slug:string, order:int}>
 */
function bkery_default_bread_categories(): array {
	return array(
		array(
			'name'  => '食事パン',
			'slug'  => 'meal',
			'order' => 10,
		),
		array(
			'name'  => '甘いパン',
			'slug'  => 'sweet',
			'order' => 20,
		),
		array(
			'name'  => 'ドリンク',
			'slug'  => 'drink',
			'order' => 30,
		),
	);
}

/**
 * REST のパンメタを編集できるか。
 *
 * WordPress は権限チェック時に追加引数を渡す。
 *
 * @param mixed ...$args 未使用。
 */
function bkery_bread_meta_can_edit( ...$args ): bool {
	unset( $args );
	return function_exists( 'current_user_can' ) && current_user_can( 'edit_posts' );
}

/**
 * 分類の表示順メタを編集できるか。
 *
 * @param mixed ...$args 未使用。
 */
function bkery_bread_category_meta_can_edit( ...$args ): bool {
	unset( $args );
	return function_exists( 'current_user_can' ) && current_user_can( 'manage_categories' );
}

/**
 * CPT bread とタクソノミー bread_category を登録する。
 */
function bkery_register_bread(): void {
	if ( ! function_exists( 'register_post_type' ) || ! function_exists( 'register_taxonomy' ) ) {
		return;
	}

	if ( ! post_type_exists( 'bread' ) ) {
		register_post_type(
			'bread',
			array(
				'labels'              => array(
					'name'               => __( 'パン', 'bakery-s' ),
					'singular_name'      => __( 'パン', 'bakery-s' ),
					'add_new'            => __( '新規追加', 'bakery-s' ),
					'add_new_item'       => __( 'パンを追加', 'bakery-s' ),
					'edit_item'          => __( 'パンを編集', 'bakery-s' ),
					'new_item'           => __( '新しいパン', 'bakery-s' ),
					'view_item'          => __( 'パンを表示', 'bakery-s' ),
					'search_items'       => __( 'パンを検索', 'bakery-s' ),
					'not_found'          => __( 'パンが見つかりません', 'bakery-s' ),
					'not_found_in_trash' => __( 'ゴミ箱にパンはありません', 'bakery-s' ),
					'all_items'          => __( 'パン一覧', 'bakery-s' ),
					'menu_name'          => __( 'パン', 'bakery-s' ),
				),
				'public'              => true,
				'has_archive'         => false,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-carrot',
				'capability_type'     => 'post',
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'rewrite'             => array(
					'slug'       => 'bread',
					'with_front' => false,
				),
			)
		);
	}

	if ( ! taxonomy_exists( 'bread_category' ) ) {
		register_taxonomy(
			'bread_category',
			array( 'bread' ),
			array(
				'labels'             => array(
					'name'          => __( '分類', 'bakery-s' ),
					'singular_name' => __( '分類', 'bakery-s' ),
					'search_items'  => __( '分類を検索', 'bakery-s' ),
					'all_items'     => __( '分類一覧', 'bakery-s' ),
					'edit_item'     => __( '分類を編集', 'bakery-s' ),
					'update_item'   => __( '分類を更新', 'bakery-s' ),
					'add_new_item'  => __( '分類を追加', 'bakery-s' ),
					'new_item_name' => __( '新しい分類名', 'bakery-s' ),
					'menu_name'     => __( '分類', 'bakery-s' ),
				),
				'hierarchical'       => true,
				'public'             => true,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_rest'       => true,
				'rewrite'            => false,
			)
		);
	}

	bkery_register_bread_meta();
}

/**
 * パンの3メタと、分類の表示順メタを REST 公開する。
 */
function bkery_register_bread_meta(): void {
	if ( function_exists( 'register_post_meta' ) && ( ! function_exists( 'registered_meta_key_exists' ) || ! registered_meta_key_exists( 'post', 'bkery_bread_line', 'bread' ) ) ) {
		register_post_meta(
			'bread',
			'bkery_bread_line',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => 'regular',
				'sanitize_callback' => 'bkery_sanitize_bread_line',
				'auth_callback'     => 'bkery_bread_meta_can_edit',
			)
		);
		register_post_meta(
			'bread',
			'bkery_bread_price',
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'auth_callback'     => 'bkery_bread_meta_can_edit',
			)
		);
		register_post_meta(
			'bread',
			'bkery_bread_bake_times',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => 'bkery_sanitize_bake_times',
				'auth_callback'     => 'bkery_bread_meta_can_edit',
			)
		);
	}

	if ( function_exists( 'register_term_meta' ) && ( ! function_exists( 'registered_meta_key_exists' ) || ! registered_meta_key_exists( 'term', 'bkery_bread_category_order', 'bread_category' ) ) ) {
		register_term_meta(
			'bread_category',
			'bkery_bread_category_order',
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'auth_callback'     => 'bkery_bread_category_meta_can_edit',
			)
		);
	}
}

/**
 * 食事パン・甘いパン・ドリンクを一度だけ作る。商品サンプルは作らない。
 */
function bkery_seed_bread_categories(): void {
	if ( ! function_exists( 'get_option' ) || get_option( 'bkery_bread_categories_seeded' ) ) {
		return;
	}
	if ( function_exists( 'taxonomy_exists' ) && ! taxonomy_exists( 'bread_category' ) ) {
		bkery_register_bread();
	}

	$ready = true;
	foreach ( bkery_default_bread_categories() as $category ) {
		$existing = term_exists( $category['slug'], 'bread_category' );
		$term_id  = 0;
		if ( is_array( $existing ) && isset( $existing['term_id'] ) ) {
			$term_id = (int) $existing['term_id'];
		} elseif ( is_numeric( $existing ) ) {
			$term_id = (int) $existing;
		}

		if ( $term_id < 1 ) {
			$result = wp_insert_term(
				$category['name'],
				'bread_category',
				array( 'slug' => $category['slug'] )
			);
			if ( is_wp_error( $result ) ) {
				$ready = false;
				continue;
			}
			$term_id = isset( $result['term_id'] ) ? (int) $result['term_id'] : 0;
		}

		if ( $term_id < 1 ) {
			$ready = false;
			continue;
		}

		if ( ! metadata_exists( 'term', $term_id, 'bkery_bread_category_order' ) ) {
			update_term_meta( $term_id, 'bkery_bread_category_order', (int) $category['order'] );
		}
	}

	if ( $ready ) {
		update_option( 'bkery_bread_categories_seeded', '1', false );
	}
}

/**
 * 区分・価格・焼き上がりのメタボックス。
 */
function bkery_add_bread_metabox(): void {
	if ( ! function_exists( 'add_meta_box' ) ) {
		return;
	}

	add_meta_box(
		'bkery_bread_details',
		__( 'パンの情報', 'bakery-s' ),
		'bkery_render_bread_metabox',
		'bread',
		'side',
		'default'
	);
}

/**
 * メタボックスの入力欄。
 *
 * @param mixed $post 投稿。
 */
function bkery_render_bread_metabox( $post ): void {
	if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
		return;
	}

	wp_nonce_field( 'bkery_bread_meta', 'bkery_bread_meta_nonce' );

	$line  = bkery_sanitize_bread_line( (string) get_post_meta( (int) $post->ID, 'bkery_bread_line', true ) );
	$price = absint( get_post_meta( (int) $post->ID, 'bkery_bread_price', true ) );
	$times = get_post_meta( (int) $post->ID, 'bkery_bread_bake_times', true );
	$times = is_string( $times ) ? $times : '';
	?>
	<p>
		<label for="bkery_bread_line"><?php esc_html_e( '区分', 'bakery-s' ); ?></label>
		<select name="bkery_bread_line" id="bkery_bread_line" class="widefat">
			<option value="regular" <?php selected( $line, 'regular' ); ?>><?php esc_html_e( '通年', 'bakery-s' ); ?></option>
			<option value="seasonal" <?php selected( $line, 'seasonal' ); ?>><?php esc_html_e( '季節限定', 'bakery-s' ); ?></option>
		</select>
	</p>
	<p>
		<label for="bkery_bread_price"><?php esc_html_e( '価格（円）', 'bakery-s' ); ?></label>
		<input type="number" min="0" step="1" class="widefat" id="bkery_bread_price" name="bkery_bread_price" value="<?php echo esc_attr( (string) $price ); ?>" />
	</p>
	<p class="description"><?php esc_html_e( '0 のときはサイトに価格を出しません。', 'bakery-s' ); ?></p>
	<p>
		<label for="bkery_bread_bake_times"><?php esc_html_e( '焼き上がり時刻', 'bakery-s' ); ?></label>
		<input type="text" class="widefat" id="bkery_bread_bake_times" name="bkery_bread_bake_times" value="<?php echo esc_attr( $times ); ?>" placeholder="10:00,15:00" />
	</p>
	<p class="description"><?php esc_html_e( 'HH:MM をカンマ区切りで入力します。', 'bakery-s' ); ?></p>
	<?php
}

/**
 * メタボックスの保存。nonce と edit_post を確認する。
 *
 * @param mixed $post_id 投稿 ID。
 */
function bkery_save_bread_meta( $post_id ): void {
	$post_id = (int) $post_id;
	if ( $post_id < 1 ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( function_exists( 'wp_is_post_revision' ) && wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( function_exists( 'get_post_type' ) && 'bread' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['bkery_bread_meta_nonce'] ) || ! is_scalar( $_POST['bkery_bread_meta_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( (string) $_POST['bkery_bread_meta_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'bkery_bread_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$line  = bkery_bread_post_scalar( 'bkery_bread_line' );
	$price = bkery_bread_post_scalar( 'bkery_bread_price' );
	$times = bkery_bread_post_scalar( 'bkery_bread_bake_times' );

	update_post_meta( $post_id, 'bkery_bread_line', bkery_sanitize_bread_line( $line ) );
	update_post_meta( $post_id, 'bkery_bread_price', absint( $price ) );
	update_post_meta( $post_id, 'bkery_bread_bake_times', bkery_sanitize_bake_times( $times ) );
}

/**
 * メタボックスから来た単一の POST 値。配列や未送信は空文字。
 */
function bkery_bread_post_scalar( string $key ): string {
	if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
		return '';
	}

	return sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) );
}

/**
 * 一覧に区分と価格の列を足す。分類はタクソノミーの列に任せる。
 *
 * @param mixed $columns 既存の列。
 * @return array<string, string>
 */
function bkery_bread_admin_columns( $columns ) {
	if ( ! is_array( $columns ) ) {
		$columns = array();
	}

	$line  = __( '区分', 'bakery-s' );
	$price = __( '価格', 'bakery-s' );
	$next  = array();
	$added = false;
	foreach ( $columns as $key => $label ) {
		$next[ (string) $key ] = (string) $label;
		if ( 'title' === $key ) {
			$next['bkery_bread_line']  = $line;
			$next['bkery_bread_price'] = $price;
			$added                     = true;
		}
	}
	if ( ! $added ) {
		$next['bkery_bread_line']  = $line;
		$next['bkery_bread_price'] = $price;
	}

	return $next;
}

/**
 * 一覧の区分と価格。
 *
 * @param mixed $column  列キー。
 * @param mixed $post_id 投稿 ID。
 */
function bkery_render_bread_admin_column( $column, $post_id ): void {
	$post_id = (int) $post_id;
	if ( 'bkery_bread_line' === $column ) {
		$line  = bkery_sanitize_bread_line( (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
		$label = 'seasonal' === $line ? __( '季節限定', 'bakery-s' ) : __( '通年', 'bakery-s' );
		echo esc_html( $label );
		return;
	}
	if ( 'bkery_bread_price' === $column ) {
		echo esc_html( (string) absint( get_post_meta( $post_id, 'bkery_bread_price', true ) ) );
	}
}

/**
 * 公開中のパンを menu_order、同じときは ID の昇順で返す。
 *
 * @param array<string, mixed> $extra WP_Query に足す引数。
 * @return array<int, mixed>
 */
function bkery_query_published_breads( array $extra = array() ): array {
	if ( ! class_exists( 'WP_Query' ) ) {
		return array();
	}

	$args  = array_merge(
		array(
			'post_type'           => 'bread',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		),
		$extra
	);
	$query = new WP_Query( $args );
	$posts = $query->posts;

	return is_array( $posts ) ? $posts : array();
}

/**
 * 季節限定または通年の公開パン。seasonal 以外は通年（メタ無しを含む）。
 *
 * @param string $line seasonal または regular。
 * @return array<int, mixed>
 */
function bkery_get_breads_by_line( string $line ): array {
	$line = trim( $line );
	if ( 'seasonal' === $line ) {
		$meta_query = array(
			array(
				'key'   => 'bkery_bread_line',
				'value' => 'seasonal',
			),
		);
	} else {
		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'   => 'bkery_bread_line',
				'value' => 'regular',
			),
			array(
				'key'     => 'bkery_bread_line',
				'compare' => 'NOT EXISTS',
			),
		);
	}

	return bkery_query_published_breads(
		array(
			'meta_query' => $meta_query,
		)
	);
}

/**
 * カードに出す短い文。
 *
 * @param mixed $post 投稿または ID。
 */
function bkery_bread_card_excerpt( $post ): string {
	if ( is_numeric( $post ) && function_exists( 'get_post' ) ) {
		$post = get_post( (int) $post );
	}
	if ( ! is_object( $post ) || ! isset( $post->post_excerpt, $post->post_content ) ) {
		return '';
	}

	return bkery_bread_card_excerpt_text( (string) $post->post_excerpt, (string) $post->post_content );
}

/**
 * 通年メニューの分類。表示順メタの昇順。
 *
 * @return array<int, WP_Term>
 */
function bkery_get_bread_categories(): array {
	if ( ! function_exists( 'get_terms' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'bread_category',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	usort(
		$terms,
		static function ( $left, $right ) {
			$left_order  = (int) get_term_meta( $left->term_id, 'bkery_bread_category_order', true );
			$right_order = (int) get_term_meta( $right->term_id, 'bkery_bread_category_order', true );
			if ( $left_order === $right_order ) {
				return $left->term_id <=> $right->term_id;
			}
			return $left_order <=> $right_order;
		}
	);

	return $terms;
}

/**
 * 公開中の全パンから焼き上がり枠を作る。並びは menu_order 順。
 *
 * @return array<int, array{time:string, items:array<int, string>}>
 */
function bkery_get_bake_schedule(): array {
	if ( ! function_exists( 'bkery_build_bake_schedule' ) ) {
		return array();
	}

	$rows = array();
	foreach ( bkery_query_published_breads() as $post ) {
		if ( ! is_object( $post ) || ! isset( $post->ID, $post->post_name ) ) {
			continue;
		}
		$times = '';
		if ( function_exists( 'get_post_meta' ) ) {
			$stored = get_post_meta( (int) $post->ID, 'bkery_bread_bake_times', true );
			$times  = is_string( $stored ) ? $stored : '';
		}
		$rows[] = array(
			'id'    => (string) $post->post_name,
			'times' => $times,
		);
	}

	return bkery_build_bake_schedule( $rows );
}

/**
 * テーマ設定の文字列。未設定や数値は空文字。
 */
function bkery_theme_text( string $key ): string {
	if ( ! function_exists( 'bkery_get_theme_mod' ) ) {
		return '';
	}
	$value = bkery_get_theme_mod( $key );
	return is_string( $value ) ? $value : '';
}

/**
 * テーマ設定の添付 ID。
 */
function bkery_theme_image_id( string $key ): int {
	if ( ! function_exists( 'bkery_get_theme_mod' ) ) {
		return 0;
	}

	return bkery_positive_int( bkery_get_theme_mod( $key ) );
}

/**
 * 番号付きの添付 ID。
 *
 * @return array<int, int>
 */
function bkery_numbered_theme_image_ids( string $prefix, int $count ): array {
	$ids = array();
	for ( $index = 1; $index <= $count; $index++ ) {
		$ids[] = bkery_theme_image_id( $prefix . $index );
	}

	return $ids;
}

/**
 * 公開パンのアイキャッチ ID。menu_order 順。
 *
 * @return array<int, int>
 */
function bkery_bread_thumbnail_ids( string $line ): array {
	if ( ! function_exists( 'get_post_thumbnail_id' ) ) {
		return array();
	}

	$ids = array();
	foreach ( bkery_get_breads_by_line( $line ) as $post ) {
		$thumb = bkery_positive_int( get_post_thumbnail_id( $post ) );
		if ( $thumb > 0 ) {
			$ids[] = $thumb;
		}
	}

	return $ids;
}

/**
 * 背景の代替ソースを添付 ID の並びに変える。
 *
 * @return array<int, int>
 */
function bkery_fallback_image_ids( string $source ): array {
	if ( ! function_exists( 'bkery_slot_counts' ) ) {
		return array();
	}
	$slots = bkery_slot_counts();

	switch ( $source ) {
		case 'hero_slides':
			return bkery_numbered_theme_image_ids( 'bkery_hero_slide_', (int) $slots['hero'] );
		case 'concept_photo':
			return array( bkery_theme_image_id( 'bkery_concept_photo' ) );
		case 'gallery':
			return bkery_numbered_theme_image_ids( 'bkery_gallery_image_', (int) $slots['gallery'] );
		case 'instagram':
			return bkery_numbered_theme_image_ids( 'bkery_instagram_image_', (int) $slots['instagram'] );
		case 'owner_photo':
			return array( bkery_theme_image_id( 'bkery_owner_photo' ) );
		case 'seasonal_thumbs':
			return bkery_bread_thumbnail_ids( 'seasonal' );
		case 'regular_thumbs':
			return bkery_bread_thumbnail_ids( 'regular' );
		default:
			return array();
	}
}

/**
 * 添付画像の URL。無ければ空文字。
 */
function bkery_attachment_image_url( int $attachment_id ): string {
	if ( $attachment_id < 1 || ! function_exists( 'wp_get_attachment_image_url' ) ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, 'full' );
	return is_string( $url ) ? $url : '';
}

/**
 * 添付ファイルの URL。動画ファイル用。
 */
function bkery_attachment_file_url( int $attachment_id ): string {
	if ( $attachment_id < 1 || ! function_exists( 'wp_get_attachment_url' ) ) {
		return '';
	}
	$url = wp_get_attachment_url( $attachment_id );
	return is_string( $url ) ? $url : '';
}

/**
 * ID の並びを、解決できた画像 URL に変える。
 *
 * @param array<int, mixed> $ids   添付 ID。
 * @param int               $limit 最大件数。
 * @return array<int, string>
 */
function bkery_urls_for_ids( array $ids, int $limit ): array {
	$urls = array();
	foreach ( bkery_positive_id_list( $ids, $limit ) as $id ) {
		$url = bkery_attachment_image_url( $id );
		if ( '' !== $url ) {
			$urls[] = $url;
		}
	}

	return $urls;
}

/**
 * 最初に URL が得られたグループを返す。自前が空なら代替へ進む。
 *
 * @param array<int, array<int, mixed>> $groups 添付 ID のグループ。
 * @return array<int, string>
 */
function bkery_first_image_urls( array $groups ): array {
	$limit = function_exists( 'bkery_slot_counts' ) ? (int) bkery_slot_counts()['scene_images'] : 3;
	foreach ( $groups as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}
		$urls = bkery_urls_for_ids( $group, $limit );
		if ( array() !== $urls ) {
			return $urls;
		}
	}

	return array();
}

/**
 * シーン自身の背景スロット。
 *
 * @return array<int, int>
 */
function bkery_scene_own_image_ids( string $scene_id ): array {
	$count = function_exists( 'bkery_slot_counts' ) ? (int) bkery_slot_counts()['scene_images'] : 3;
	return bkery_numbered_theme_image_ids( 'bkery_scene_' . $scene_id . '_image_', $count );
}

/**
 * PC 背景用のシーン配列。
 *
 * @return array<string, array{image:array<int, string>, section:string, title:string, text:string}>
 */
function bkery_frontend_scenes(): array {
	$scene_ids = function_exists( 'bkery_scene_ids' ) ? bkery_scene_ids() : array();
	$fallbacks = function_exists( 'bkery_scene_fallback_sources' ) ? bkery_scene_fallback_sources() : array();
	$scenes    = array();

	foreach ( $scene_ids as $scene_id ) {
		$groups = array( bkery_scene_own_image_ids( $scene_id ) );
		foreach ( $fallbacks[ $scene_id ] ?? array() as $source ) {
			$groups[] = bkery_fallback_image_ids( (string) $source );
		}
		$scenes[ $scene_id ] = array(
			'image'   => bkery_first_image_urls( $groups ),
			'section' => bkery_theme_text( 'bkery_scene_' . $scene_id . '_label' ),
			'title'   => bkery_theme_text( 'bkery_scene_' . $scene_id . '_title' ),
			'text'    => bkery_theme_text( 'bkery_scene_' . $scene_id . '_text' ),
		);
	}

	return $scenes;
}

/**
 * フロントの bkeryTheme に渡す配列。商品カードの本文は含めない。
 *
 * @return array{
 *   shop: array{open:string, close:string, cafeClose:string, closedWeekdays:array<int, int>, addressText:string},
 *   links: array{instagram:string, instagramHandle:string, map:string, calendar:string},
 *   bakeSchedule: array<int, array{time:string, items:array<int, string>}>,
 *   media: array{video:string, videoPoster:string},
 *   scenes: array<string, array{image:array<int, string>, section:string, title:string, text:string}>,
 *   steam: array{enabled:int, density:int, speed:int, column:int, background:int}
 * }
 */
function bkery_get_frontend_data(): array {
	$closed_raw = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_shop_closed_weekdays' ) : '';
	if ( ! is_string( $closed_raw ) ) {
		$closed_raw = '';
	}

	return array(
		'shop'         => array(
			'open'           => bkery_theme_text( 'bkery_shop_open' ),
			'close'          => bkery_theme_text( 'bkery_shop_close' ),
			'cafeClose'      => bkery_theme_text( 'bkery_shop_cafe_close' ),
			'closedWeekdays' => bkery_parse_closed_weekdays( $closed_raw ),
			'addressText'    => bkery_theme_text( 'bkery_shop_address_text' ),
		),
		'links'        => array(
			'instagram'       => bkery_theme_text( 'bkery_link_instagram' ),
			'instagramHandle' => bkery_theme_text( 'bkery_link_instagram_handle' ),
			'map'             => bkery_theme_text( 'bkery_shop_map_url' ),
			'calendar'        => bkery_theme_text( 'bkery_link_calendar' ),
		),
		'bakeSchedule' => bkery_get_bake_schedule(),
		'media'        => array(
			'video'       => bkery_attachment_file_url( bkery_theme_image_id( 'bkery_video_id' ) ),
			'videoPoster' => bkery_attachment_image_url( bkery_theme_image_id( 'bkery_video_poster_id' ) ),
		),
		'scenes'       => bkery_frontend_scenes(),
		'steam'        => bkery_steam_frontend(),
	);
}

/**
 * WordPress 上でフックを登録する。単体テストでは add_action が無いので何もしない。
 */
function bkery_boot_bread(): void {
	if ( ! function_exists( 'add_action' ) || ! function_exists( 'add_filter' ) ) {
		return;
	}

	add_action( 'init', 'bkery_register_bread', 0 );
	add_action( 'init', 'bkery_register_bread_meta' );
	add_action( 'after_switch_theme', 'bkery_seed_bread_categories' );
	add_action( 'add_meta_boxes', 'bkery_add_bread_metabox' );
	add_action( 'save_post_bread', 'bkery_save_bread_meta' );
	add_filter( 'manage_bread_posts_columns', 'bkery_bread_admin_columns' );
	add_action( 'manage_bread_posts_custom_column', 'bkery_render_bread_admin_column', 10, 2 );
}
bkery_boot_bread();
