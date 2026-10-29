# wp_theme-bakery_modan

ベーカリーカフェ **Bakery S** の WordPress テーマです。
デザインの区間構成は `docs/ui_reference/` に合わせます。色と書体はそこから更新済みで、標準は清潔な青、書体は Outfit と Zen Kaku Gothic New、面は Liquid Glass です。画面の表情は光のパビリオン（光環、展示番号、浮いたガラス）で、モックのブラウン、丸ゴシック、紙袋のギザギザへ戻さないでください。クライアントからの依頼メモは `docs/client/` にあり、画面の構成がメモと違うときはモックの区間を優先します。

このファイルは Cursor などの実装エージェント向けの共通指示です。人間向けの手順は `README.md` を見てください。

## 1. 識別子

| 項目 | 値 |
|---|---|
| テーマディレクトリ | `wp_theme-bakery_modan` |
| 表示名 | bakery_modan |
| テキストドメイン | `bakery-s` |
| 関数・設定・メタのプレフィックス | `bkery_` |
| WordPress | 6.5 以上（Tested up to 6.9） |
| PHP | 8.2 系 |
| ローカル環境 | Local by Flywheel（サイト `bakerymodan`） |
| 参考テーマ | `wp_theme-restaurant_ember`（構成とテストの型だけを借りる） |

## 2. 画面の確定方針

公開画面はトップ 1 ページです。モックの区間（hero / concept / gallery / season / menu / bake / owner / shop / instagram）を `front-page.php` と `template-parts/` に分けます。

| 項目 | 方針 |
|---|---|
| パン | CPT `bread`。季節限定と通年はメタ `bkery_bread_line`。通年の分類はタクソノミー `bread_category` |
| 焼き上がり | 各パンの `bkery_bread_bake_times` を `bkery_build_bake_schedule()` で時刻順にまとめる |
| 営業中表示 | カスタマイザーの開店・閉店・カフェ終了と、閲覧者の端末時刻。計算は `src/shop-status.ts` |
| 定休日 | `bkery_shop_closed_weekdays`。空欄なら毎日営業（モックと同じ） |
| 店主・コンセプト・店舗・外部リンク・PC 背景 | カスタマイザー。キーの正本は `inc/theme-mods.php` |
| 店内写真・製造動画 | カスタマイザーの添付ファイル。ギャラリー CPT は作らない |
| 個別のパン URL | `single-bread.php`（共有用）。一覧の本体はトップのカルーセルとグリッド |
| お知らせ・サイト内検索・予約・電話・メール・Instagram 以外の SNS・STAFF ユーザー | 作らない |

PC・タブレット（幅 768px 以上）では、幅 420px のカラムの周囲に背景ステージを出します。スマートフォンでは背景ステージを出さず、その画像も読み込みません。

## 3. ディレクトリ

```
wp_theme-bakery_modan/
├── style.css / functions.php / index.php
├── front-page.php          ← 未作成（テンプレート担当）
├── single-bread.php        ← 未作成
├── header.php / footer.php ← 未作成
├── inc/
│   ├── shop-hours.php      ← 実装済み
│   ├── theme-mods.php      ← 実装済み（設定キーの正本）
│   ├── helpers.php         ← 取得ヘルパーのみ実装済み
│   ├── colors.php          ← 実装済み
│   ├── enqueue.php         ← 実装済み
│   ├── customizer.php      ← 実装済み
│   ├── bread.php           ← 未実装
│   └── setup-pages.php     ← 未実装
├── template-parts/         ← 未作成
├── src/                    ← theme.css へモック CSS を移設済み。shop-status.ts 以外の TS は未実装
├── dist/                   ← Vite の出力。手で編集しない
├── docs/ui_reference/      ← デザイン正本。編集禁止
├── docs/client/            ← 依頼メモ。編集禁止
├── docs/design/            ← 設計書 01〜06
└── tests/
```

## 4. 並行実装の担当

同じファイルを複数人で編集しないこと。

| 担当 | 触ってよいファイル | 内容 |
|---|---|---|
| パン CPT | `inc/bread.php`、`tests/integration/php/*Bread*`、`tests/unit/php/*Bread*` | 投稿タイプ、タクソノミー、メタ、シード、`bkery_get_frontend_data()` |
| テンプレート | `front-page.php`、`header.php`、`footer.php`、`single-bread.php`、`template-parts/`、`index.php` の文言以外 | モックの HTML を移す。クラス名は変えない |
| フロント | `src/*.ts`（`shop-status.ts` の計算式は変えない）、`src/main.ts`、`tests/unit/js/` | DOM の初期化。CSS のクラス名は維持 |
| 共通入口 | `inc/setup-pages.php`、`functions.php` の require 追加が必要なときだけ | 固定ページ「ホーム」とフロントページ設定 |

次のファイルは実装済みです。キー追加以外の書き換えはしないでください。

- `inc/shop-hours.php`
- `inc/theme-mods.php`
- `inc/colors.php`
- `inc/enqueue.php`
- `inc/customizer.php`
- `src/shop-status.ts`
- `src/styles/theme.css`（見た目の差分がモックに必要なときだけ）

設定キーを足すときは `bkery_theme_mod_registry()` と `docs/design/03_customizer.md` を同じ変更で更新し、レジストリの単体テストを通します。

## 5. 実装ルール（短縮）

- 文字コード UTF-8（BOM なし）、改行 LF、末尾改行あり
- PHP のインデントはタブ。TS / CSS / JSON / MD はスペース 2
- 出力は `esc_html` / `esc_attr` / `esc_url` / `wp_kses_post`
- テンプレートの文言は `bkery_get_theme_mod()` か投稿から取る
- `<script>` と `<link>` の直書きは禁止
- jQuery も Tailwind も足さない。湯気の演出だけ Three.js のシェーダーを使う
- ロジックを変えたら、同じ変更で単体テストを追加する。WordPress の保存や権限は結合テストにする

## 6. コマンド

```bash
pnpm install
pnpm dev
pnpm run build
pnpm test
composer install
composer test:unit
composer test:integration
```

## 7. やってはいけないこと

- `docs/ui_reference/` と `docs/client/` の編集
- `dist/` の手動編集
- パンを標準投稿で作ること
- 店主をユーザーや CPT で作ること
- ギャラリー CPT、お知らせ、サイト内検索、予約導線を足すこと
- モックに無い電話番号・メール・Instagram 以外の SNS 欄を足すこと
- `ember_` プレフィックスや居酒屋テーマの画面構成を残すこと
- `src/shop-status.ts` の文言や境界（開店ちょうどは営業中、閉店ちょうどは終了、カフェ終了ちょうどはカフェ終了）を、テストを更新せずに変えること
