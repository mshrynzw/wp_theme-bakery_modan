<?php
/**
 * カスタマイザー設定の正本（キー・初期値・型・セクション）。
 *
 * キーを足すときはこのファイルと docs/design/03_customizer.md を同じ変更で更新する。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * PC 背景ステージのセクション ID。HTML の data-section と一致させる。
 *
 * @return array<int, string>
 */
function bkery_scene_ids(): array {
	return array( 'hero', 'concept', 'gallery', 'season', 'menu', 'bake', 'owner', 'shop', 'instagram' );
}

/**
 * 色ロール。var はモックのカスタムプロパティ名。
 *
 * @return array<string, array{var:string, label:string}>
 */
function bkery_color_roles(): array {
	return array(
		'flour'      => array(
			'var'   => '--flour',
			'label' => '背景',
		),
		'paper'      => array(
			'var'   => '--paper',
			'label' => 'カード面',
		),
		'kraft'      => array(
			'var'   => '--kraft',
			'label' => 'サブ背景',
		),
		'kraft_deep' => array(
			'var'   => '--kraft-deep',
			'label' => 'サブ背景（濃）',
		),
		'crust'      => array(
			'var'   => '--crust',
			'label' => 'メインカラー',
		),
		'crust_soft' => array(
			'var'   => '--crust-soft',
			'label' => '装飾',
		),
		'sage'       => array(
			'var'   => '--sage',
			'label' => '期間限定ラベル',
		),
		'ink'        => array(
			'var'   => '--ink',
			'label' => '文字',
		),
		'rye'        => array(
			'var'   => '--rye',
			'label' => '補足文字',
		),
		'glow'       => array(
			'var'   => '--glow',
			'label' => '暗い面のアクセント',
		),
		'espresso'   => array(
			'var'   => '--espresso',
			'label' => 'PC 背景の下地',
		),
	);
}

/**
 * 番号付きスロットの個数。
 *
 * @return array{hero:int, gallery:int, instagram:int, scene_images:int}
 */
function bkery_slot_counts(): array {
	return array(
		'hero'         => 3,
		'gallery'      => 6,
		'instagram'    => 6,
		'scene_images' => 3,
	);
}

/**
 * シーン文言の初期値。改行はモックの縦書き見出しに合わせる。
 *
 * @return array<string, array{label:string, title:string, text:string}>
 */
function bkery_scene_copy(): array {
	return array(
		'hero'      => array(
			'label' => 'Bakery S',
			'title' => "日常に、\n焼きたての\nしあわせを。",
			'text'  => "国産小麦と自家製酵母のベーカリーカフェ。\n焼きたてのパンとコーヒーを、店内でも。",
		),
		'concept'   => array(
			'label' => 'お店のコンセプト',
			'title' => "特別すぎない\n贅沢",
			'text'  => "毎日の暮らしの延長に、\n自然と存在する美味しさを。",
		),
		'gallery'   => array(
			'label' => '店内のようす',
			'title' => "焼きたてと\nコーヒーを、\n店内で。",
			'text'  => "イートインスペースは20席。\nカフェのご利用は10:00〜17:00です。",
		),
		'season'    => array(
			'label' => '季節限定のパン',
			'title' => "今月だけの、\n焼きたて。",
			'text'  => "毎月、季節の素材を使った\n新しいパンが登場します。",
		),
		'menu'      => array(
			'label' => 'いつものパン',
			'title' => "毎日食べても\n飽きない味。",
			'text'  => "一年を通して焼いている\n定番のパンとコーヒー。",
		),
		'bake'      => array(
			'label' => '焼き上がり時間',
			'title' => "一番おいしい\n瞬間を、\nお知らせ。",
			'text'  => "できたての香りと温かさを\nお楽しみください。",
		),
		'owner'     => array(
			'label' => '店主・お店の想い',
			'title' => "この街の\n暮らしのそばに。",
			'text'  => '店主　山本 恒一',
		),
		'shop'      => array(
			'label' => 'お店について',
			'title' => "ふらっと、\n寄り道を。",
			'text'  => "10:00〜18:00\nカフェのご利用は17:00まで",
		),
		'instagram' => array(
			'label' => 'Instagram',
			'title' => "季節の新作を、\nInstagramで。",
			'text'  => "@bakery_s（仮）\n季節限定パンや焼き上がりのようすを発信中",
		),
	);
}

/**
 * 画像 ID が空のとき、シーン背景に使う代替の種類。
 *
 * @return array<string, array<int, string>>
 */
function bkery_scene_fallback_sources(): array {
	return array(
		'hero'      => array( 'hero_slides' ),
		'concept'   => array( 'concept_photo' ),
		'gallery'   => array( 'gallery' ),
		'season'    => array( 'seasonal_thumbs' ),
		'menu'      => array( 'regular_thumbs' ),
		'bake'      => array( 'hero_slides' ),
		'owner'     => array( 'owner_photo', 'hero_slides' ),
		'shop'      => array( 'hero_slides' ),
		'instagram' => array( 'instagram', 'hero_slides' ),
	);
}

/**
 * カスタマイザーのセクション。
 *
 * @return array<string, array{title:string, description:string, priority:int}>
 */
function bkery_customizer_sections(): array {
	return array(
		'bkery_colors'    => array(
			'title'       => '色',
			'description' => '配色を選ぶか、色を1つずつ変えられます。初期の色に戻すと、テーマの標準配色になります。',
			'priority'    => 20,
		),
		'bkery_hero'      => array(
			'title'       => 'メインビジュアル',
			'description' => 'スマホ幅カラムの先頭。写真は最大3枚です。',
			'priority'    => 30,
		),
		'bkery_concept'   => array(
			'title'       => 'お店のコンセプト',
			'description' => '見出し、本文、3つの魅力。',
			'priority'    => 40,
		),
		'bkery_gallery'   => array(
			'title'       => '店内のようす',
			'description' => '店内写真と、パンができるまでの動画。',
			'priority'    => 50,
		),
		'bkery_season'    => array(
			'title'       => '季節限定のパン',
			'description' => '商品そのものは「パン」の投稿で管理します。ここは見出しだけです。',
			'priority'    => 60,
		),
		'bkery_menu'      => array(
			'title'       => 'いつものパン',
			'description' => '商品そのものは「パン」の投稿で管理します。ここは見出しだけです。',
			'priority'    => 70,
		),
		'bkery_bake'      => array(
			'title'       => '焼き上がり時間',
			'description' => '時刻は各パンの投稿に入力します。ここは見出しと注記です。',
			'priority'    => 80,
		),
		'bkery_owner'     => array(
			'title'       => '店主',
			'description' => '店主紹介。ユーザーアカウントとは別です。',
			'priority'    => 90,
		),
		'bkery_shop'      => array(
			'title'       => 'お店について',
			'description' => '営業時間、定休日、住所、地図リンク。営業中の表示はここの時刻を使います。',
			'priority'    => 100,
		),
		'bkery_links'     => array(
			'title'       => '外部リンク',
			'description' => 'Instagram と Google カレンダー。',
			'priority'    => 110,
		),
		'bkery_instagram' => array(
			'title'       => 'Instagram と締め',
			'description' => '投稿イメージの写真と、ページ末尾の一言。',
			'priority'    => 120,
		),
		'bkery_scenes'    => array(
			'title'       => 'PC 背景',
			'description' => '幅 768px 以上の周囲に出る写真と縦書きです。空の写真は各セクションの画像で補います。',
			'priority'    => 130,
		),
		'bkery_footer'    => array(
			'title'       => 'フッター',
			'description' => '著作権表記の上に出す注記です。',
			'priority'    => 140,
		),
		'bkery_steam'     => array(
			'title'       => '湯気',
			'description' => '焼き上がりの湯気です。各項目の初期値が標準の見た目です。視差を減らす、または透明度を下げる端末では出しません。',
			'priority'    => 25,
		),
	);
}

/**
 * 設定1件を足す。
 *
 * @param array<string, array<string, mixed>> $items  蓄積先。
 * @param string                              $key    設定 ID。
 * @param string                              $type   color|text|textarea|html|url|clock|weekdays|image|video|checkbox|range。
 * @param string                              $section セクション ID。
 * @param string                              $label  管理画面のラベル。
 * @param string|int                          $default 初期値。
 * @param string                              $description 補足。空なら出さない。
 */
function bkery_register_theme_mod( array &$items, string $key, string $type, string $section, string $label, $default, string $description = '' ): void {
	$items[ $key ] = array(
		'type'        => $type,
		'section'     => $section,
		'label'       => $label,
		'default'     => $default,
		'description' => $description,
	);
}

/**
 * チェックボックスの保存値。オンだけ 1。
 *
 * @param mixed $value 入力値。
 */
function bkery_steam_flag( $value ): int {
	if ( true === $value || 1 === $value || '1' === $value || 'true' === $value || 'on' === $value ) {
		return 1;
	}

	return 0;
}

/**
 * 0 から 100 の整数。数値でなければ初期値。
 *
 * @param mixed $value    入力値。
 * @param int   $fallback 初期値。
 */
function bkery_steam_percent( $value, int $fallback ): int {
	if ( ! is_numeric( $value ) ) {
		return $fallback;
	}

	$number = (int) round( (float) $value );
	if ( $number < 0 ) {
		return 0;
	}
	if ( $number > 100 ) {
		return 100;
	}

	return $number;
}

/**
 * 公開画面へ渡す湯気の設定。未保存のときはレジストリの初期値。
 *
 * @return array{enabled:int, density:int, speed:int, column:int, background:int}
 */
function bkery_steam_frontend(): array {
	$enabled    = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_steam_enabled' ) : 1;
	$density    = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_steam_density' ) : 50;
	$speed      = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_steam_speed' ) : 50;
	$column     = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_steam_column' ) : 100;
	$background = function_exists( 'bkery_get_theme_mod' ) ? bkery_get_theme_mod( 'bkery_steam_background' ) : 100;

	return array(
		'enabled'    => bkery_steam_flag( $enabled ),
		'density'    => bkery_steam_percent( $density, 50 ),
		'speed'      => bkery_steam_percent( $speed, 50 ),
		'column'     => bkery_steam_percent( $column, 100 ),
		'background' => bkery_steam_percent( $background, 100 ),
	);
}

/**
 * 全設定。キーの追加はここだけ。
 *
 * @return array<string, array{type:string, section:string, label:string, default:string|int, description:string}>
 */
function bkery_theme_mod_registry(): array {
	$items = array();

	foreach ( bkery_color_roles() as $role => $meta ) {
		$defaults = array(
			'flour'      => '#F3F7FB',
			'paper'      => '#FFFFFF',
			'kraft'      => '#E4EEF7',
			'kraft_deep' => '#D3E2F1',
			'crust'      => '#18658F',
			'crust_soft' => '#6AA4C4',
			'sage'       => '#2A7584',
			'ink'        => '#152433',
			'rye'        => '#4E6174',
			'glow'       => '#C5DFF0',
			'espresso'   => '#0C1824',
		);
		bkery_register_theme_mod(
			$items,
			'bkery_color_' . $role,
			'color',
			'bkery_colors',
			$meta['label'],
			$defaults[ $role ]
		);
	}

	bkery_register_theme_mod(
		$items,
		'bkery_steam_enabled',
		'checkbox',
		'bkery_steam',
		'湯気を出す',
		1,
		'オフにすると湯気の描画を読み込みません。'
	);
	bkery_register_theme_mod(
		$items,
		'bkery_steam_density',
		'range',
		'bkery_steam',
		'濃さ',
		50,
		'50 が標準です。0 にするとほぼ見えなくなります。'
	);
	bkery_register_theme_mod(
		$items,
		'bkery_steam_speed',
		'range',
		'bkery_steam',
		'立ち上る速さ',
		50,
		'50 が標準です。0 で止まります。'
	);
	bkery_register_theme_mod(
		$items,
		'bkery_steam_column',
		'range',
		'bkery_steam',
		'画面内の濃さ',
		100,
		'幅 420px のカラムです。スマートフォンでは画面全体になります。100 が標準です。'
	);
	bkery_register_theme_mod(
		$items,
		'bkery_steam_background',
		'range',
		'bkery_steam',
		'背景の濃さ',
		100,
		'幅 768px 以上の周囲です。100 が標準です。'
	);

	bkery_register_theme_mod( $items, 'bkery_hero_catch', 'textarea', 'bkery_hero', 'キャッチコピー', "日常に、\n焼きたての\nしあわせを。" );
	bkery_register_theme_mod( $items, 'bkery_hero_lead', 'textarea', 'bkery_hero', 'リード文', "国産小麦と自家製酵母のベーカリーカフェ。\n焼きたてのパンとコーヒーを、店内でもどうぞ。" );
	$hero_count = bkery_slot_counts()['hero'];
	for ( $index = 1; $index <= $hero_count; $index++ ) {
		bkery_register_theme_mod( $items, 'bkery_hero_slide_' . $index, 'image', 'bkery_hero', '写真' . $index, 0 );
	}

	bkery_register_theme_mod( $items, 'bkery_concept_title', 'text', 'bkery_concept', '見出し', 'お店のコンセプト' );
	bkery_register_theme_mod( $items, 'bkery_concept_copy', 'textarea', 'bkery_concept', '大きなコピー', "特別すぎない\n贅沢" );
	bkery_register_theme_mod(
		$items,
		'bkery_concept_body',
		'html',
		'bkery_concept',
		'本文',
		<<<'TXT'
特別な日だけに味わう贅沢ではなく、毎日の暮らしの延長に自然と存在する美味しさを大切にしています。

<strong>国産小麦</strong>や<strong>自家製酵母</strong>など素材と製法には妥協せず、安心して食べ続けられるパンを一つひとつ丁寧に焼き上げています。

派手さや流行を追いかけるのではなく、何度食べても「<strong>やっぱり美味しい</strong>」と感じられる味を目指しています。

朝食やおやつ、仕事帰りのひとときなど、日常のさまざまな場面にそっと寄り添える存在でありたい。それが、Bakery Sが考える、背伸びをしない「特別すぎない贅沢」です。
TXT
	);
	bkery_register_theme_mod( $items, 'bkery_concept_photo', 'image', 'bkery_concept', '写真', 0 );
	bkery_register_theme_mod( $items, 'bkery_points_title', 'text', 'bkery_concept', '魅力の見出し', 'Bakery Sの３つの魅力' );

	$points = array(
		1 => array(
			'title' => '国産小麦と自家製酵母',
			'body'  => '国産小麦の風味を生かし、自家製酵母でゆっくり発酵。毎日食べても飽きのこない、<strong>やさしい味わい</strong>です。',
			'label' => '',
			'url'   => '',
		),
		2 => array(
			'title' => '焼きたての時間をお知らせ',
			'body'  => 'パンの焼き上がり時間を掲示し、<strong>一番おいしい瞬間</strong>をお知らせしています。できたての香りと温かさをお楽しみください。',
			'label' => '今日の焼き上がり時間を見る',
			'url'   => '#bake',
		),
		3 => array(
			'title' => '特別すぎない、毎日の贅沢',
			'body'  => '毎日の食卓に寄り添うパンだからこそ、<strong>背伸びしない美味しさ</strong>を大切に。日常の中で、ふっと気分が上がる存在でありたいと考えています。',
			'label' => '',
			'url'   => '',
		),
	);
	foreach ( $points as $index => $point ) {
		bkery_register_theme_mod( $items, 'bkery_point_' . $index . '_title', 'text', 'bkery_concept', '魅力' . $index . 'の見出し', $point['title'] );
		bkery_register_theme_mod( $items, 'bkery_point_' . $index . '_body', 'html', 'bkery_concept', '魅力' . $index . 'の本文', $point['body'] );
		bkery_register_theme_mod( $items, 'bkery_point_' . $index . '_link_label', 'text', 'bkery_concept', '魅力' . $index . 'のリンク文言', $point['label'] );
		bkery_register_theme_mod( $items, 'bkery_point_' . $index . '_link_url', 'text', 'bkery_concept', '魅力' . $index . 'のリンク先', $point['url'], 'ページ内は #bake のように書きます。' );
	}

	bkery_register_theme_mod( $items, 'bkery_gallery_title', 'text', 'bkery_gallery', '見出し', '店内のようす' );
	bkery_register_theme_mod( $items, 'bkery_gallery_lead', 'textarea', 'bkery_gallery', 'リード', 'イートインは20席。焼きたてのパンとコーヒーを、そのまま店内で楽しめます。' );
	$captions = array(
		1 => 'パンの棚とイートインのテーブル',
		2 => '対面のカウンター',
		3 => '窓ぎわのテーブル席',
		4 => '焼きたてが並ぶ棚',
		5 => '',
		6 => '',
	);
	$gallery_count = bkery_slot_counts()['gallery'];
	for ( $index = 1; $index <= $gallery_count; $index++ ) {
		bkery_register_theme_mod( $items, 'bkery_gallery_image_' . $index, 'image', 'bkery_gallery', '写真' . $index, 0 );
		bkery_register_theme_mod( $items, 'bkery_gallery_caption_' . $index, 'text', 'bkery_gallery', '写真' . $index . 'の説明', $captions[ $index ] );
	}
	bkery_register_theme_mod( $items, 'bkery_video_title', 'text', 'bkery_gallery', '動画の見出し', 'パンができるまで' );
	bkery_register_theme_mod( $items, 'bkery_video_sub', 'text', 'bkery_gallery', '動画の補足', '朝の仕込みから焼き上がりまでを動画で' );
	bkery_register_theme_mod( $items, 'bkery_video_id', 'video', 'bkery_gallery', '動画ファイル', 0, '未設定のときはプレースホルダーを出します。' );
	bkery_register_theme_mod( $items, 'bkery_video_poster_id', 'image', 'bkery_gallery', '動画のサムネイル', 0 );

	bkery_register_theme_mod( $items, 'bkery_season_title', 'text', 'bkery_season', '見出し', '季節限定のパン' );
	bkery_register_theme_mod( $items, 'bkery_season_lead', 'textarea', 'bkery_season', 'リード', '毎月、季節の素材を使った新しいパンが登場します。今月のラインナップはこちら。' );
	bkery_register_theme_mod( $items, 'bkery_menu_title', 'text', 'bkery_menu', '見出し', 'いつものパン' );
	bkery_register_theme_mod( $items, 'bkery_menu_lead', 'textarea', 'bkery_menu', 'リード', '一年を通して毎日焼いている定番メニューです。気になるパンをタップすると詳しく見られます。' );

	bkery_register_theme_mod( $items, 'bkery_bake_title', 'text', 'bkery_bake', '見出し', '焼き上がり時間' );
	bkery_register_theme_mod( $items, 'bkery_bake_lead', 'textarea', 'bkery_bake', 'リード', '一番おいしい焼きたての時間に合わせて、お店に寄ってみてください。' );
	bkery_register_theme_mod( $items, 'bkery_bake_note', 'textarea', 'bkery_bake', '注記', '※ 時間はモック用のサンプルです。実際の焼き上がり時間は店頭とGoogleカレンダーでお知らせする想定です。' );
	bkery_register_theme_mod( $items, 'bkery_bake_calendar_label', 'text', 'bkery_bake', 'カレンダーボタン', 'Googleカレンダーで見る' );

	bkery_register_theme_mod( $items, 'bkery_owner_title', 'text', 'bkery_owner', '見出し', '店主・お店の想い' );
	bkery_register_theme_mod( $items, 'bkery_owner_heading', 'text', 'bkery_owner', '紹介の小見出し', '店主紹介' );
	bkery_register_theme_mod( $items, 'bkery_owner_name', 'text', 'bkery_owner', '氏名', '山本 恒一' );
	bkery_register_theme_mod( $items, 'bkery_owner_kana', 'text', 'bkery_owner', 'ふりがな', 'やまもと こういち' );
	bkery_register_theme_mod( $items, 'bkery_owner_photo', 'image', 'bkery_owner', '写真', 0 );
	bkery_register_theme_mod(
		$items,
		'bkery_owner_bio',
		'textarea',
		'bkery_owner',
		'経歴',
		'製パン学校卒業後、都内のベーカリーやカフェで経験を積み、「毎日食べたくなるパンを、自分の手で届けたい」という想いから Bakery S をオープン。素材選びと発酵の時間を大切にしながら、一つひとつ丁寧にパンを焼いています。'
	);
	bkery_register_theme_mod( $items, 'bkery_owner_message_title', 'text', 'bkery_owner', 'メッセージの見出し', '店主メッセージ' );
	bkery_register_theme_mod(
		$items,
		'bkery_owner_message',
		'html',
		'bkery_owner',
		'メッセージ',
		<<<'TXT'
Bakery Sのパンは、特別な日のためのものではありません。<br>朝食やランチ、仕事帰りのひとときなど、日常の中で自然と手に取ってもらえる存在でありたいと考えています。

国産小麦や自家製酵母を使い、毎日食べても飽きない、やさしくて素直な味わいを目指しています。
TXT
	);
	bkery_register_theme_mod( $items, 'bkery_owner_sign', 'textarea', 'bkery_owner', '署名', 'この街の暮らしのそばに、そっと寄り添えるパン屋であれたら嬉しいです。' );

	bkery_register_theme_mod( $items, 'bkery_shop_title', 'text', 'bkery_shop', '見出し', 'お店について' );
	bkery_register_theme_mod( $items, 'bkery_shop_open', 'clock', 'bkery_shop', '開店', '10:00' );
	bkery_register_theme_mod( $items, 'bkery_shop_close', 'clock', 'bkery_shop', '閉店', '18:00' );
	bkery_register_theme_mod( $items, 'bkery_shop_cafe_open', 'clock', 'bkery_shop', 'カフェ開始', '10:00' );
	bkery_register_theme_mod( $items, 'bkery_shop_cafe_close', 'clock', 'bkery_shop', 'カフェ終了', '17:00' );
	bkery_register_theme_mod(
		$items,
		'bkery_shop_closed_weekdays',
		'weekdays',
		'bkery_shop',
		'定休日',
		'',
		'空欄なら毎日営業です。0=日曜、1=月曜、2=火曜、3=水曜、4=木曜、5=金曜、6=土曜。例: 2,3'
	);
	bkery_register_theme_mod( $items, 'bkery_shop_seats', 'text', 'bkery_shop', '座席', '20席（イートインスペース）' );
	bkery_register_theme_mod( $items, 'bkery_shop_parking', 'text', 'bkery_shop', '駐車場', '2台分あり' );
	bkery_register_theme_mod(
		$items,
		'bkery_shop_address',
		'textarea',
		'bkery_shop',
		'住所（表示）',
		"〒123-4567\n北海道札幌市○○区△△町1-2-3　1F",
		'公開前にサンプル住所を差し替えてください。'
	);
	bkery_register_theme_mod(
		$items,
		'bkery_shop_address_text',
		'text',
		'bkery_shop',
		'住所（コピー用）',
		'〒123-4567 北海道札幌市○○区△△町1-2-3 1F',
		'改行のない1行。住所コピーボタンがこの文字列を使います。'
	);
	bkery_register_theme_mod( $items, 'bkery_shop_access', 'textarea', 'bkery_shop', 'アクセス', '○○駅南口より徒歩5分。△△通りを直進し、カフェ□□の角を曲がってすぐです。' );
	bkery_register_theme_mod(
		$items,
		'bkery_shop_map_url',
		'url',
		'bkery_shop',
		'Googleマップの URL',
		'https://www.google.com/maps/search/?api=1&query=Bakery%20S%20%E3%83%99%E3%83%BC%E3%82%AB%E3%83%AA%E3%83%BC%20%E6%9C%AD%E5%B9%8C'
	);
	bkery_register_theme_mod( $items, 'bkery_shop_map_caption', 'text', 'bkery_shop', '地図のキャプション', '道順のイメージ図です（縮尺は正確ではありません）' );

	bkery_register_theme_mod( $items, 'bkery_link_instagram', 'url', 'bkery_links', 'Instagram URL', 'https://www.instagram.com/' );
	bkery_register_theme_mod( $items, 'bkery_link_instagram_handle', 'text', 'bkery_links', 'Instagram 表示名', '@bakery_s（仮）' );
	bkery_register_theme_mod( $items, 'bkery_link_calendar', 'url', 'bkery_links', 'Googleカレンダー URL', 'https://calendar.google.com/' );

	bkery_register_theme_mod( $items, 'bkery_instagram_title', 'text', 'bkery_instagram', '見出し', 'Instagram' );
	bkery_register_theme_mod( $items, 'bkery_instagram_lead', 'textarea', 'bkery_instagram', 'リード', '季節限定パンの入荷や、お店の日々のようすをお届けしています。' );
	$instagram_count = bkery_slot_counts()['instagram'];
	for ( $index = 1; $index <= $instagram_count; $index++ ) {
		bkery_register_theme_mod( $items, 'bkery_instagram_image_' . $index, 'image', 'bkery_instagram', '写真' . $index, 0 );
	}
	bkery_register_theme_mod( $items, 'bkery_closing_copy', 'textarea', 'bkery_instagram', '締めのコピー', '特別すぎない贅沢' );
	bkery_register_theme_mod( $items, 'bkery_closing_text', 'textarea', 'bkery_instagram', '締めの本文', "焼きたての時間に合わせて、\nふらっと寄ってみてください。" );

	$scene_labels = array(
		'hero'      => 'トップ',
		'concept'   => 'コンセプト',
		'gallery'   => '店内',
		'season'    => '季節限定',
		'menu'      => 'いつものパン',
		'bake'      => '焼き上がり',
		'owner'     => '店主',
		'shop'      => 'お店について',
		'instagram' => 'Instagram',
	);
	$scene_image_count = bkery_slot_counts()['scene_images'];
	foreach ( bkery_scene_copy() as $scene_id => $copy ) {
		$scene_label = $scene_labels[ $scene_id ];
		bkery_register_theme_mod( $items, 'bkery_scene_' . $scene_id . '_label', 'text', 'bkery_scenes', $scene_label . 'の区分名', $copy['label'] );
		bkery_register_theme_mod( $items, 'bkery_scene_' . $scene_id . '_title', 'textarea', 'bkery_scenes', $scene_label . 'の縦書き見出し', $copy['title'] );
		bkery_register_theme_mod( $items, 'bkery_scene_' . $scene_id . '_text', 'textarea', 'bkery_scenes', $scene_label . 'の補足', $copy['text'] );
		for ( $index = 1; $index <= $scene_image_count; $index++ ) {
			bkery_register_theme_mod( $items, 'bkery_scene_' . $scene_id . '_image_' . $index, 'image', 'bkery_scenes', $scene_label . 'の背景' . $index, 0 );
		}
	}

	bkery_register_theme_mod(
		$items,
		'bkery_footer_note',
		'textarea',
		'bkery_footer',
		'注記',
		'掲載している住所・価格・焼き上がり時間・SNSアカウントはデザインモック用のサンプルです。'
	);

	return $items;
}
