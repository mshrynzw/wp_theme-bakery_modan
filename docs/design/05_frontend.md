# 05. フロントエンド

> CSS は `docs/ui_reference/style.css` を `src/styles/theme.css` へ移設済みです。
> 時刻計算は `src/shop-status.ts` に実装済みです。DOM の接続が残っています。

## 5.1 ビルド

- エントリ `src/main.ts`
- 出力 `dist/js/theme.js` と `dist/css/app.css`
- Tailwind は使いません。クラスはモックのままです
- 依存ライブラリは Vite と Vitest と TypeScript だけです

`src/main.ts` は現在、CSS の import だけです。各 `init` はここから呼びます。

## 5.2 CSS

守るもの:

- `:root` のカスタムプロパティ名（`--flour`、`--col-w: 420px`、`--crust` など）
- 768px 未満で `.stage` と `.rail` を出さないこと
- 768px 以上でカラム幅 420px、背景色 `--espresso`
- 1200px 以上の目次、1100px 以上の営業ステータス表示（モックのメディアクエリのまま）
- `prefers-reduced-motion: reduce` の既存ルール
- フォーカスリング（`--crust-soft`）

カスタマイザーの色は、`bkery_get_color_palette_css()` が同じ変数を `:root` に上書きします。`theme.css` の初期値は残します。

## 5.3 TypeScript の分割

| ファイル | 状態 | 役割 |
|---|---|---|
| `shop-status.ts` | 実装済み | `parseClock`、`isClosedWeekday`、`getShopStatus`、`getBakePlan`、`waitText` |
| `status.ts` | 未実装 | `[data-status-label]` などを更新。1 分ごと、タブ復帰時にも更新 |
| `hero.ts` | 未実装 | 6 秒間隔。ドット、スワイプ、マウスホバーと画面外と非表示タブで停止。扉の演出は初回だけ |
| `carousel.ts` | 未実装 | `[data-carousel]`。scroll-snap、前後ボタン、ドットまたは枚数、左右キー |
| `product-sheet.ts` | 未実装 | `[data-product]` で詳細を開く。下方向ドラッグ、外側、閉じる、Esc |
| `lightbox.ts` | 未実装 | `[data-lightbox]`。前後、スワイプ、左右キー |
| `video.ts` | 未実装 | 開くまで `<video>` を作らない。閉じたら一時停止。ファイルが無ければプレースホルダー |
| `stage.ts` | 未実装 | `matchMedia('(min-width: 768px)')` のときだけ画像を読む。複数枚は 7 秒。reduced-motion では切替しない |
| `reveal.ts` | 未実装 | `[data-reveal]` に `is-in`。reduced-motion または IntersectionObserver が無いときは最初から見せる |
| `shop-ui.ts` | 未実装 | `[data-link]`、`[data-insta-handle]`、住所コピー、目次と固定ナビの `aria-current`、ドックの `inert` |

商品一覧と季節限定カルーセルの HTML は PHP が出力します。これらのモジュールは、既存のノードにクラスやダイアログの中身を足すだけにします。

`window.bkeryTheme` が無いときは、各 `init` は return します。型は `src/vite-env.d.ts` です。

## 5.4 営業ステータスの接続

`status.ts` は次のように計算関数へ渡します。

1. `new Date()` の時・分を分に直す
2. `bkeryTheme.shop.closedWeekdays` と `date.getDay()` を `isClosedWeekday()` へ渡す
3. `getShopStatus(now, shop, closedDay)` の `label` を `[data-status-label]`、`sub` を `[data-status-sub]`、`state` を `[data-status-dot]` の `data-state` に入れる
4. `getBakePlan()` の `next` に当たる枠へ `is-next`、`isPast` が真の枠へ `is-past`
5. 次の枠があるとき、`[data-bake-next]` に時刻と `waitText(slot.min - now)` を出す。開店前（`soon`）の待ち文言はモックどおり「開店と同時に」
6. 次の枠が無いときは「本日の焼き上がりはすべて終了しました」。定休日（`holiday`）のときは、その文言の代わりに「本日は定休日です」
7. `[data-next-bake-short]` と `[data-next-bake-quick]` はモックの `updateStatus` と同じ文面にする

待ち時間の文言をこのファイルに複製しないでください。

## 5.5 表示速度と操作

依頼メモの「表示が遅くならない」「説明が要らない」は、次で満たします。

- ヒーロー先頭以外の画像は遅延読み込み
- 768px 未満では PC 背景の画像をリクエストしない
- 動画は再生操作まで読み込まない
- 自動で動くものは、ホバー・画面外・非表示タブ・reduced-motion で止める
- よく使う導線（アクセス、焼き上がり、Instagram）はヒーローのクイックリンクと下部ドックに置く
- 商品はタップで詳細、店内写真はタップで拡大、住所はボタンでコピーして結果をトーストで知らせる
- ダイアログは Esc、外側、閉じるボタンで閉じ、開いている間は `html.is-locked` で背景スクロールを止める

## 5.6 テスト

`tests/unit/js/shop-status.test.ts` が境界条件の正です。DOM モジュールを足すときは、jsdom で要素を置き、公開した `init` または純粋関数を検証します。時刻に依存するテストは `Date` を直接呼ばず、分の数値を引数で渡せる形を維持します。
