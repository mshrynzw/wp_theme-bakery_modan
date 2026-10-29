# wp_theme-bakery_modan

ベーカリーカフェ **Bakery S** の WordPress テーマです。
`docs/ui_reference/` のスマホ特化型デザインを WordPress で更新できるようにします。

<img width="330" height="717" alt="image" src="https://github.com/user-attachments/assets/c4a5560e-6a53-474f-b2cb-1447cda1aa75" />

モバイル表示の場合

<img width="1920" height="911" alt="image" src="https://github.com/user-attachments/assets/aa78e961-2ef0-428f-a007-2892c04e4301" />

PC表示の場合

設計書は `docs/design/`、実装エージェント向けの指示は `AGENTS.md` を参照してください。

## 前提環境

- WordPress: 6.9 系（Local by Flywheel のサイト `bakerymodan`）
- PHP: 8.2 系
- Node.js: v20 以上 / pnpm 9 以上
- 開発環境: VS Code または Cursor

## 画面方針

| 項目 | 方針 |
|---|---|
| 公開ページ | トップ 1 ページ（メインビジュアル、コンセプト、店内、季節限定、いつものパン、焼き上がり、店主、店舗、Instagram） |
| パン | 管理画面の「パン」（CPT `bread`）。価格・季節限定か通年か・焼き上がり時刻を各投稿に入れる |
| 店舗情報 | 外観 > カスタマイズ |
| 個別 URL | 各パンの投稿ページ（共有用） |
| お知らせ / 検索 / 予約 / スタッフ一覧 | なし |

PC では中央付近にスマホ幅のカラムを置き、周囲の背景がセクションに合わせて切り替わります。スマートフォンではその背景を読み込みません。

## インストール / 有効化

1. テーマの場所は `wp-content/themes/wp_theme-bakery_modan` です
2. 依存関係とビルド

   ```bash
   pnpm install
   pnpm run build
   composer install
   ```

3. WordPress 管理画面の **外観 > テーマ** から **bakery_modan** を有効化します
4. **外観 > カスタマイズ** で、色、メインビジュアル、コンセプト、店内写真、店主、営業時間、住所、Instagram を設定します
5. 表示設定（有効化時の自動設定は `inc/setup-pages.php` の実装後）
   - ホームページの表示: 固定ページ
   - ホームページ: `ホーム`
6. **パン** に商品を追加します
   - 区分: 季節限定 / 通年
   - 通年の分類: 食事パン / 甘いパン / ドリンク
   - 価格（円の整数）
   - 焼き上がり時刻（例: `10:00, 15:00`）
   - アイキャッチ

サイトのタイトルが店名（Bakery S）です。キャッチフレーズはタグラインとして使えます。ロゴ画像は任意です。モックのロゴは文字です。

住所・価格・Instagram・焼き上がり時刻の初期文面には、モックのサンプルが残っています。公開前にカスタマイザーと投稿で差し替えてください。

## 開発

```bash
pnpm install
pnpm dev
```

PHP だけの編集はビルド不要です。`src/` を変えたあとは `pnpm dev` または `pnpm run build` で `dist/` を更新します。

## テスト

| 種別 | コマンド | 前提 |
|---|---|---|
| PHP 単体 | `composer test:unit` | `composer install` |
| PHP 結合 | `composer test:integration` | `composer install`、`tests/integration/.env`、テスト専用 DB |
| TS 単体 | `pnpm test` | `pnpm install` |

結合テストの `.env` は `.env.example` をコピーし、Local の Database タブに出ているホストとポートに合わせます。開発用データベースは指さないでください。

`php-unit.ini` はマシンごとの PHP 拡張パスです。必要なら兄弟テーマの同名ファイルを参考に、このテーマのルートへ置きます（Git には入れません）。

## ディレクトリの要点

- `docs/ui_reference/` … デザイン正本（編集禁止）
- `docs/client/` … 依頼メモ（編集禁止）
- `docs/design/` … 設計書
- `src/styles/theme.css` … モックの CSS を移したもの
- `dist/` … ビルド成果物（手動編集禁止）
- `inc/theme-mods.php` … カスタマイザー項目の一覧

## ライセンス

GNU General Public License v3.0。本文は `LICENSE` です。
