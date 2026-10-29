# 02. コンテンツモデル

> 商品は CPT、それ以外のページ文言はカスタマイザーです。
> モックの `script.js` にある `DATA` は、この章と `03_customizer.md` に分割して移します。

## 2.1 パン（CPT `bread`）

| 設定 | 値 |
|---|---|
| 管理画面のラベル | パン |
| `public` | true |
| `has_archive` | false |
| `show_in_rest` | true |
| `rewrite.slug` | `bread`（`with_front` = false） |
| `supports` | title, editor, excerpt, thumbnail, page-attributes |
| `menu_icon` | `dashicons-carrot` |
| 登録フック | `init`（priority 0） |

スラッグ（`post_name`）が、焼き上がり表と `data-product` の ID です。モックの `focaccia` や `season-croque` に相当します。

本文（`post_content`）は商品詳細の段落です。抜粋が空のときは、カードには本文の最初の段落を出します。

表示順は `menu_order` の昇順、同じときは投稿 ID の昇順です。

### タクソノミー `bread_category`

| 設定 | 値 |
|---|---|
| 対象 | `bread` |
| `hierarchical` | true |
| `show_admin_column` | true |
| `show_in_rest` | true |
| `rewrite` | false（アーカイブ URL は使わない） |

初期タームは、オプション `bkery_bread_categories_seeded` が無いときだけ作ります。

| name | slug | タームメタ `bkery_bread_category_order` |
|---|---|---|
| 食事パン | meal | 10 |
| 甘いパン | sweet | 20 |
| ドリンク | drink | 30 |

通年メニューのタブは、この順に「すべて」を先頭へ足して出します。「すべて」はタームではありません。季節限定のパンにカテゴリは必須ではありません。

### 投稿メタ

| メタキー | 型 | 空のとき | sanitize | 用途 |
|---|---|---|---|---|
| `bkery_bread_line` | string | `regular` | `seasonal` または `regular` 以外は `regular` | 季節限定か通年か |
| `bkery_bread_price` | int | `0` | `absint` | 円。0 のときは価格を出さない |
| `bkery_bread_bake_times` | string | `''` | `bkery_sanitize_bake_times()` | `10:00,15:00` の形式 |

メタボックスはサイドに置き、nonce アクション名は `bkery_bread_meta` です。保存時は `edit_post` を確認します。`show_in_rest` 用の `auth_callback` は `edit_posts` です。

管理画面の一覧には、区分・価格・カテゴリの列を出します。

### クエリ

- 季節限定: `post_type=bread`、`bkery_bread_line=seasonal`、`post_status=publish`、`orderby=menu_order`、`order=ASC`、`posts_per_page=-1`
- 通年: 同じ条件で `bkery_bread_line=regular`
- 焼き上がり集約の入力は、公開中の全パンについて `{ id: post_name, times: bkery_bread_bake_times }` です。並びは `menu_order` 順に渡し、`bkery_build_bake_schedule()` の結果をそのまま表示します

個別ページ `single-bread.php` は、同じフィールドをダイアログではなく縦積みで出します。トップの該当セクション（季節限定なら `#season`、通年なら `#menu`）へのリンクを置きます。

デモ商品（999 円のサンプル）は自動投入しません。

## 2.2 カスタマイザーに置くもの

店名はサイトのタイトルです。ロゴ画像は WordPress 標準のカスタムロゴ（任意）です。モックは文字ロゴなので、画像が無いときはサイトのタイトルのみで構いません。日本語の副題が要る場合はキャッチフレーズを使い、テンプレートでは `get_bloginfo( 'description' )` が空なら出さない、で足ります。キャッチフレーズの初期値はサイト側の設定であり、テーマの強制デフォルトにはしません。

次はすべて `bkery_theme_mod_registry()` です。詳細なキー一覧の生成規則は `03_customizer.md` にあります。

- メインビジュアルの文言と写真（最大 3 枚）
- コンセプト本文、写真、3 つの魅力
- 店内写真（最大 6 枚）とキャプション、製造動画とサムネイル
- 各セクションの見出しとリード
- 店主の氏名、ふりがな、写真、経歴、メッセージ
- 開店・閉店・カフェ開始・カフェ終了、定休日、座席、駐車場、住所、アクセス、地図 URL
- Instagram URL と表示名、カレンダー URL
- Instagram 欄の写真（最大 6 枚）と締めのコピー
- PC 背景の区分名・縦書き・補足・写真（各セクション最大 3 枚）
- フッター注記

イラスト地図の SVG はテンプレートに残します。埋め込み地図への差し替えは今回の範囲にしません。

## 2.3 フロントへ渡す配列

`bkery_get_frontend_data()`（`inc/bread.php` に実装）の戻り値は、次の形にします。商品カードの本文はこの配列に入れません。

```php
array(
	'shop' => array(
		'open'            => '10:00',
		'close'           => '18:00',
		'cafeClose'       => '17:00',
		'closedWeekdays'  => array( 2, 3 ),
		'addressText'     => 'コピー用の1行',
	),
	'links' => array(
		'instagram'       => 'https://...',
		'instagramHandle' => '@bakery_s',
		'map'             => 'https://...',
		'calendar'        => 'https://...',
	),
	'bakeSchedule' => array(
		array(
			'time'  => '10:00',
			'items' => array( 'croissant', 'shokupan' ),
		),
	),
	'media' => array(
		'video'       => '',
		'videoPoster' => '',
	),
	'scenes' => array(
		'hero' => array(
			'image'   => array( 'https://.../photo.webp' ),
			'section' => 'Bakery S',
			'title'   => "日常に、\n焼きたての\nしあわせを。",
			'text'    => '...',
		),
	),
)
```

`closedWeekdays` は `bkery_parse_closed_weekdays()` の結果です。画像 URL がひとつも無いシーンは、`bkery_scene_fallback_sources()` の順で添付 ID を探します。それでも無ければ `image` は空配列にし、ステージはそのシーンの文言だけ更新します。

動画 URL が空のときは、モックと同じ「動画素材は準備中です」の枠を出します。

## 2.4 時刻の規則

PHP と TypeScript で同じ規則です。

- 時刻は `HH:MM`（時は必ず 2 桁、00:00〜23:59）
- 閉店は開店より後。日をまたぐ営業は扱わない
- 不正な営業時刻のときは「営業時間を確認してください」
- 定休日は端末の `getDay()`（0 = 日曜）。その日は時刻より先に「本日は定休日です」
- 焼き上がりの同じ時刻には、`menu_order` が先の商品から並べる

境界の文言は `src/shop-status.ts` と単体テストが正です。

| 端末時刻 | 状態 |
|---|---|
| 開店より前 | `soon`（開店前・10:00から） |
| 開店ちょうど〜カフェ終了より前 | `open`。補足はカフェ利用可 |
| カフェ終了ちょうど〜閉店より前 | `open`。補足はカフェ終了、パンの販売は閉店まで |
| 閉店ちょうど以降 | `closed` |
| 定休日 | `holiday`。焼き上がり枠はすべて過去扱いにし、次の枠は無し |
