# 06. PHP 関数・フック

> 実装済みの関数は、仕様変更のときテストを先に直します。
> 未実装の関数は、この表の名前で追加します。

## 6.1 読み込み順（`functions.php`）

1. `inc/shop-hours.php`
2. `inc/theme-mods.php`
3. `inc/helpers.php`
4. `inc/colors.php`
5. `inc/enqueue.php`
6. `inc/customizer.php`
7. `inc/bread.php`
8. `inc/setup-pages.php`

## 6.2 実装済み

### 初期化

| 関数 | フック | 役割 |
|---|---|---|
| `bkery_setup()` | `after_setup_theme` | title-tag、アイキャッチ、カスタムロゴ、html5、テキストドメイン、画像サイズ `bkery-hero` / `bkery-card` / `bkery-gallery` / `bkery-owner` |

ナビゲーションメニューは登録しません。

### アセット（`inc/enqueue.php`）

| 関数 | フック | 役割 |
|---|---|---|
| `bkery_register_google_fonts()` | `wp_enqueue_scripts` (5) | Outfit と Zen Kaku Gothic New |
| `bkery_resource_hints()` | `wp_resource_hints` | Google Fonts への preconnect |
| `bkery_enqueue_assets()` | `wp_enqueue_scripts` (20) | `dist/css/app.css`、色のインライン CSS、`dist/js/theme.js`。`bkery_get_frontend_data` があれば `bkeryTheme` として渡す |
| `bkery_script_loader_tag()` | `script_loader_tag` | `bkery-main` に `type="module"` |

### 純粋関数（`inc/shop-hours.php`）

| 関数 | 役割 |
|---|---|
| `bkery_parse_clock( string $value ): ?int` | `HH:MM` を分へ。不正は null |
| `bkery_sanitize_clock( string $value ): string` | 正しければ同じ文字列、違えば空 |
| `bkery_parse_closed_weekdays( string $value ): array` | `0`〜`6` の重複なし昇順 |
| `bkery_format_closed_weekdays( array $days ): string` | `0,3` の形に戻す |
| `bkery_sanitize_bake_times( string $value ): string` | 出現順を保った `HH:MM,HH:MM` |
| `bkery_build_bake_schedule( array $rows ): array` | `{id, times}` の並びを、時刻順の `{time, items}` にする |

### 設定（`inc/theme-mods.php`、`inc/helpers.php`、`inc/colors.php`）

| 関数 | 役割 |
|---|---|
| `bkery_scene_ids()` | 9 セクション ID |
| `bkery_color_roles()` | 色ロールと CSS 変数名 |
| `bkery_slot_counts()` | 写真枚数の上限 |
| `bkery_scene_copy()` | PC 背景の初期文面 |
| `bkery_scene_fallback_sources()` | 背景画像が空のときの代替順 |
| `bkery_customizer_sections()` | カスタマイザーのセクション |
| `bkery_theme_mod_registry()` | 全設定の型・ラベル・初期値 |
| `bkery_default( string $key )` | 初期値。未知のキーは空文字 |
| `bkery_get_theme_mod( string $key )` | `get_theme_mod` と初期値。画像 ID は int |
| `bkery_color_declaration_block( array $colors ): string` | `:root{--flour:#......;}` |
| `bkery_get_color_palette_css(): string` | 現在の設定から色 CSS を作る |
| `bkery_default_color_palette(): array` | レジストリの 11 色。初期化の戻り先 |
| `bkery_color_presets(): array` | テーマ同梱の配色 |
| `bkery_color_customize_payload(): array` | カスタマイザー用のロール・配色・文言 |

### カスタマイザー（`inc/customizer.php`）

| 関数 | フック | 役割 |
|---|---|---|
| `bkery_sanitize_theme_mod( $value, $setting )` | 各 setting の sanitize | レジストリの型で保存値を整える |
| `bkery_customize_register( $wp_customize )` | `customize_register` | セクションとコントロールをループ登録。色セクションの先頭に配色コントロールを足す |
| `bkery_customize_controls_enqueue()` | `customize_controls_enqueue_scripts` | 配色の適用・初期化用 JS / CSS |

## 6.3 未実装（パン CPT、`inc/bread.php`）

| 関数 | フック | 役割 |
|---|---|---|
| `bkery_register_bread()` | `init` | CPT `bread` とタクソノミー `bread_category` |
| `bkery_register_bread_meta()` | `init` | 3 つのメタを REST 公開 |
| `bkery_seed_bread_categories()` | `after_switch_theme` | `meal` / `sweet` / `drink` を一度だけ作る |
| `bkery_add_bread_metabox()` | `add_meta_boxes` | 区分・価格・焼き上がり時刻 |
| `bkery_save_bread_meta( $post_id )` | `save_post_bread` | nonce と権限を確認して保存 |
| `bkery_bread_admin_columns()` | `manage_bread_posts_columns` | 区分と価格の列 |
| `bkery_get_breads_by_line( $line )` | — | 公開中の季節限定または通年を `menu_order` 順で返す |
| `bkery_bread_card_excerpt( $post )` | — | 抜粋、無ければ本文の最初の段落 |
| `bkery_get_frontend_data()` | — | `02_content-model.md` の配列。enqueue が `bkeryTheme` として渡す |

結合テストで、CPT の登録、タームのシードが二度走らないこと、権限の無い保存がメタを変えないこと、焼き上がり文字列が `bkery_sanitize_bake_times()` を通ることを見ます。

## 6.4 未実装（セットアップ、`inc/setup-pages.php`）

| 関数 | フック | 役割 |
|---|---|---|
| `bkery_setup_front_page()` | `after_switch_theme` | スラッグ `home` の固定ページが無ければ「ホーム」を作り、`show_on_front=page` と `page_on_front` をその ID にする。既存の `home` があればそれを使う。リライトルールを flush する |

投稿ページ（ブログ）は作りません。

## 6.5 テンプレートから使う想定の補助

CPT 担当が `inc/bread.php` に置きます。テンプレート担当は新しいクエリをテンプレートへ直書きしません。

| 関数 | 戻り |
|---|---|
| `bkery_get_breads_by_line( 'seasonal' \| 'regular' )` | `WP_Post[]` |
| `bkery_get_bread_categories()` | 表示順メタ順の `WP_Term[]` |
| `bkery_get_bake_schedule()` | `bkery_build_bake_schedule()` の結果 |

## 6.6 単体テストの置き場所

| 対象 | ファイル |
|---|---|
| 時刻・定休日・焼き上がり集約 | `tests/unit/php/ShopHoursTest.php` |
| レジストリのキーとセクション | `tests/unit/php/ThemeModRegistryTest.php` |
| 色の CSS | `tests/unit/php/ColorCssTest.php` |
| 配色プリセット | `tests/unit/php/ColorPaletteTest.php` |
| 配色コントロールの登録 | `tests/integration/php/ColorsCustomizerTest.php` |
| 営業ステータス | `tests/unit/js/shop-status.test.ts` |
