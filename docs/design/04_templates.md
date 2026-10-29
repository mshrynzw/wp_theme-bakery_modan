# 04. テンプレート

> クラス名、`id`、`data-*` は `docs/ui_reference/index.html` に合わせます。
> 見た目の CSS はすでに `src/styles/theme.css` へ移してあります。テンプレート担当はクラスを別の名前にしないでください。

## 4.1 テンプレート階層

| ファイル | 役割 | 状態 |
|---|---|---|
| `front-page.php` | トップ。各セクションを `get_template_part` する | 未作成 |
| `header.php` | スキップリンク、SVG スプライト、`.stage`、`.rail`、`<body>` 開始 | 未作成 |
| `footer.php` | フッター、ダイアログ、`wp_footer()` | 未作成 |
| `template-parts/hero.php` | `#hero` | 未作成 |
| `template-parts/concept.php` | `#concept` | 未作成 |
| `template-parts/gallery.php` | `#gallery` | 未作成 |
| `template-parts/season.php` | `#season` | 未作成 |
| `template-parts/menu.php` | `#menu` | 未作成 |
| `template-parts/bake.php` | `#bake` | 未作成 |
| `template-parts/owner.php` | `#owner` | 未作成 |
| `template-parts/shop.php` | `#shop` | 未作成 |
| `template-parts/instagram.php` | `#instagram` | 未作成 |
| `template-parts/dock.php` | `.dock` | 未作成 |
| `template-parts/dialogs.php` | `#product-sheet`、`#lightbox`、`#video-dialog`、トースト | 未作成 |
| `single-bread.php` | パンの個別ページ | 未作成 |
| `index.php` | 固定ページなどのフォールバック | 作成済み |
| `404.php` | モックに画面が無いので、`index.php` と同じ簡素なメッセージでよい | 未作成 |

ナビゲーションメニュー（外観 > メニュー）は登録しません。目次と固定ナビのリンク先は次で固定です。

| 表示 | href | `data-nav` |
|---|---|---|
| トップ | `#hero` | `hero` |
| コンセプト | `#concept` | `concept` |
| 店内のようす | `#gallery` | `gallery` |
| 季節限定のパン | `#season` | `season` |
| いつものパン | `#menu` | `menu` |
| 焼き上がり時間 | `#bake` | `bake` |
| 店主・お店の想い | `#owner` | `owner` |
| お店について | `#shop` | `shop` |
| Instagram | `#instagram` | `instagram` |

固定ナビ（`.dock`）に出すのは、季節限定、いつものパン、焼き上がり、お店について、Instagram の 5 つです。ラベルはモックの短い文言（季節限定 / メニュー / 焼き上がり / アクセス / Instagram）をテンプレートに書きます。

## 4.2 マークアップで守ること

- `<html lang="ja">`。スクリプト有効時に `js` クラスを付ける処理は、モックと同じ1行を `header.php` に置いてよい（描画前に必要なため。外部ファイルの `<script src>` は禁止のまま）
- アイコンは `index.html` 先頭の SVG スプライトを `header.php` に移し、`<use href="#i-...">` を維持する
- ヒーローの先頭画像だけ `fetchpriority="high"`。他は `loading="lazy"` と `decoding="async"`
- 画像が未設定の枠は、モックの `.ph` に `data-label` だけを付け、`<img>` は出さない
- 商品ボタンは `type="button"`、`data-product="{post_name}"`、`aria-haspopup="dialog"`
- 季節限定のチップには `dot-season` と `aria-label="期間限定 {名前}"`
- 焼き上がりリストの各 `li` は `data-bake-slot="{HH:MM}"`
- 外部リンクは `target="_blank"` と `rel="noopener"`。URL は `bkery_link_*` と `bkery_shop_map_url`
- 住所コピーボタンは `data-copy-address`
- ダイアログはページに 1 組だけ
- 「サンプル情報」バッジは出さない
- フッターの店名はサイトのタイトル。注記は `bkery_footer_note`。空ならその段落を出さない
- 著作権は `© {サイトのタイトル} All Rights Reserved.`

## 4.3 商品カード

季節限定カルーセルの 1 枚と、通年グリッドの 1 件は、モックの `renderSeasonal` / `renderMenu` が作るクラスをサーバ側で出します。JS で `innerHTML` し直しません。

通年のカテゴリタブは `role="group"` と `data-menu-tabs` です。ボタンの `data-category` は `all` / `meal` / `sweet` / `drink` です。件数は公開中の通年パンから数えます。

価格は 0 より大きいときだけ、`number_format_i18n` と「円」を出します。モック同様、単位は `<small>円</small>` で包んでよいです。

## 4.4 個別ページ

`single-bread.php` はスマホ幅カラムの中に、写真、名前、価格、本文、そのパンの焼き上がり時刻、トップの `#shop` と `#bake` へのリンクを置きます。ダイアログ用のドラッグシートは個別ページには付けません。

## 4.5 フォールバック

`index.php` は固定ページ用の簡素なループです。トップのデザインは `front-page.php` にだけ載せてください。`header.php` が出来るまでは `get_header()` は空です。テンプレート担当が `header.php` と `footer.php` を追加したあと、このループもそれらの中で表示されます。
