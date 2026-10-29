# 03. カスタマイザー

> キーの正本は `inc/theme-mods.php` の `bkery_theme_mod_registry()` です。
> この章はセクションの意味と、キー名の規則です。項目を足すときはレジストリとこの章を同じ変更で更新します。
> 登録処理は `inc/customizer.php` のループが行い、設定ごとに `add_setting` を書きません。

## 3.1 セクション

| ID | タイトル | 置くもの |
|---|---|---|
| `bkery_colors` | 色 | テーマ同梱の配色、初期化、清潔な青の 11 色（変数名はモックと同じ） |
| `bkery_hero` | メインビジュアル | キャッチ、リード、写真 1〜3 |
| `bkery_concept` | お店のコンセプト | 見出し、大きなコピー、本文、写真、魅力 1〜3 |
| `bkery_gallery` | 店内のようす | 見出し、リード、写真 1〜6 とキャプション、動画 |
| `bkery_season` | 季節限定のパン | 見出しとリードだけ |
| `bkery_menu` | いつものパン | 見出しとリードだけ |
| `bkery_bake` | 焼き上がり時間 | 見出し、リード、注記、ボタン文言 |
| `bkery_owner` | 店主 | 氏名、ふりがな、写真、経歴、メッセージ |
| `bkery_shop` | お店について | 営業時間、定休日、住所、地図 URL |
| `bkery_links` | 外部リンク | Instagram、表示名、カレンダー |
| `bkery_instagram` | Instagram と締め | 写真 1〜6、締めのコピー |
| `bkery_scenes` | PC 背景 | 9 セクション ×（区分名、縦書き、補足、背景 1〜3） |
| `bkery_footer` | フッター | 注記 |
| `bkery_steam` | 湯気 | 湯気の有無、濃さ、速さ、画面内と背景の濃さ |

サイト名、キャッチフレーズ、サイトアイコン、カスタムロゴは WordPress 標準のセクションを使います。

## 3.2 型

| type | コントロール | sanitize |
|---|---|---|
| `color` | カラー | `sanitize_hex_color`。失敗時は初期値 |
| `text` | 1 行テキスト | `sanitize_text_field` |
| `textarea` | 複数行 | `sanitize_textarea_field` |
| `html` | 複数行 | `wp_kses_post` |
| `url` | URL | `esc_url_raw` |
| `clock` | 1 行テキスト | `bkery_sanitize_clock`。失敗時は初期値 |
| `weekdays` | 1 行テキスト | `bkery_format_closed_weekdays( bkery_parse_closed_weekdays() )` |
| `image` | 画像 | `absint` |
| `video` | 動画 | `absint` |
| `checkbox` | チェックボックス | `bkery_steam_flag()`。オンだけ `1` |
| `range` | 0〜100 のスライダー | `bkery_steam_percent()`。数値でなければ初期値 |

ページ内リンク（`#bake`）は `url` にせず `text` にします。`esc_url_raw` がハッシュを落とすのを避けるためです。

## 3.3 キーの命名

| パターン | 例 | 個数の正本 |
|---|---|---|
| `bkery_color_{role}` | `bkery_color_flour` | `bkery_color_roles()` |
| `bkery_hero_slide_{n}` | `bkery_hero_slide_1` | `bkery_slot_counts()['hero']`（3） |
| `bkery_gallery_image_{n}` / `bkery_gallery_caption_{n}` | `bkery_gallery_image_1` | 6 |
| `bkery_instagram_image_{n}` | `bkery_instagram_image_1` | 6 |
| `bkery_point_{n}_title` / `_body` / `_link_label` / `_link_url` | `bkery_point_2_link_url` | 3。初期リンクは 2 番だけ `#bake` |
| `bkery_scene_{id}_label` / `_title` / `_text` / `_image_{n}` | `bkery_scene_hero_title` | `bkery_scene_ids()` と画像 3 枚 |

`{id}` は `hero`、`concept`、`gallery`、`season`、`menu`、`bake`、`owner`、`shop`、`instagram` です。HTML の `data-section` と一致させます。

色の CSS 変数はロール定義の `var` です。例: `flour` → `--flour`、`kraft_deep` → `--kraft-deep`。

配色の選択と「初期の色に戻す」は theme_mod ではありません。選ぶと、既存の `bkery_color_{role}` へ色をまとめて入れます。定義は `bkery_color_presets()`、戻り先は `bkery_default_color_palette()`（レジストリの初期値、プリセット「標準」と同じ）です。公開するまで保存されません。同梱の配色は `standard`（標準）、`morning`（朝の光）、`rye`（ライ麦）、`matcha`（抹茶）です。

## 3.4 営業時間と定休日

| キー | 初期値 | 意味 |
|---|---|---|
| `bkery_shop_open` | `10:00` | 開店 |
| `bkery_shop_close` | `18:00` | 閉店（パンの販売終了） |
| `bkery_shop_cafe_open` | `10:00` | 表示文のカフェ開始 |
| `bkery_shop_cafe_close` | `17:00` | カフェ終了。営業ステータスの分岐に使う |
| `bkery_shop_closed_weekdays` | 空 | `0` が日曜。空欄は毎日営業。例: `2,3` |

テンプレートの「ご利用時間」は、この 4 つの時刻から組み立てます。自由入力の営業時間文は持ちません。

- 大きい表示: `{open}〜{close}`
- 補足: `カフェのご利用は{cafe_open}〜{cafe_close}までとなります。`

## 3.5 画像が空のときの PC 背景

`bkery_scene_fallback_sources()` の順です。実装は `bkery_get_frontend_data()` 側で行います。

| シーン | 代替 |
|---|---|
| hero | メインビジュアルの写真 |
| concept | コンセプトの写真 |
| gallery | 店内写真 |
| season | 季節限定パンのアイキャッチ（最大 3） |
| menu | 通年パンのアイキャッチ（最大 3） |
| bake | メインビジュアルの写真 |
| owner | 店主の写真、無ければメインビジュアル |
| shop | メインビジュアルの写真 |
| instagram | Instagram 欄の写真、無ければメインビジュアル |

複数枚あるシーン（モックでは gallery / season / menu）は、背景 1〜3 に画像が入っていれば 7 秒間隔で切り替えます。1 枚だけなら切り替えません。

## 3.6 初期文面

レジストリの `default` は、モックの文面です。住所、価格の注記、Instagram の「（仮）」、フッター注記はサンプルのままなので、公開前にカスタマイザーで消すか書き換えてください。テーマ側でサンプルバッジは出しません。

取得はテンプレートから `bkery_get_theme_mod( $key )` だけを使います。

## 3.7 湯気

公開画面の `bkeryTheme.steam` は `bkery_steam_frontend()` です。未保存のときは次の初期値で、今の見た目のままです。

| キー | 初期値 | 意味 |
|---|---|---|
| `bkery_steam_enabled` | `1` | 湯気を出す。`0` では Three.js を読み込まない |
| `bkery_steam_density` | `50` | 全体の濃さ。`0` でほぼ見えない |
| `bkery_steam_speed` | `50` | 立ち上る速さ。`0` で静止 |
| `bkery_steam_column` | `100` | 幅 420px のカラム。スマートフォンでは画面全体 |
| `bkery_steam_background` | `100` | 幅 768px 以上の周囲 |

`range` はすべて 0〜100 です。端末が視差を減らす、または透明度を下げるときは、オンでも出しません。
