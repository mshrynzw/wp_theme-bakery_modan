# 01. システム概要・全体構成

> Bakery S（ベーカリーカフェ）テーマの設計です。
> デザインの正本は `docs/ui_reference/`。依頼の意図は `docs/client/資料（リニューアルに伴う追加内容）.md` にあります。
> 画面がメモと違うときはモックを採用します。定休日の入力だけは、モックが毎日営業で計算していた点を補うために追加しています。

## 1.1 ドキュメント一覧

| ファイル | 内容 | 主な対象テスト |
|---|---|---|
| `01_overview.md`（本書） | 全体構成、担当分け、前提環境 | 総合 |
| `02_content-model.md` | パン CPT、店舗データの持ち方 | 単体・結合 |
| `03_customizer.md` | カスタマイザーのセクションとキーの規則 | 単体・結合 |
| `04_templates.md` | テンプレートとモック HTML の対応 | 結合・総合 |
| `05_frontend.md` | CSS 移設、TypeScript の分割、表示速度 | 単体 |
| `06_functions.md` | PHP 関数とフック。実装済みと未実装を分ける | 単体 |

## 1.2 プロダクト概要

- **名称**: Bakery S
- **テーマディレクトリ**: `wp_theme-bakery_modan`
- **テキストドメイン**: `bakery-s`
- **プレフィックス**: `bkery_`
- **バージョン**: 0.1.0（`style.css`）
- **目的**: スマホ幅のカラムを主画面にし、幅 768px 以上では周囲に背景ステージを出すベーカリーカフェのサイトを、管理画面から更新できるようにする

## 1.3 前提環境

| 項目 | 値 |
|---|---|
| WordPress | 6.5 以上（Tested up to 6.9） |
| PHP | 8.2 系 |
| Node.js | v20 以上 / pnpm 9 以上 |
| ローカル | Local by Flywheel（`bakerymodan`） |
| フロント | Vite 6 + TypeScript 5。Tailwind は使わない |

## 1.4 確定方針

| 項目 | 方針 |
|---|---|
| ページ構成 | トップのみを主画面にする。アンカーはモックの `id` を維持する |
| パン | CPT `bread`。アーカイブは作らない（`has_archive` = false） |
| 季節限定 / 通年 | メタ `bkery_bread_line` = `seasonal` または `regular` |
| 通年の分類 | タクソノミー `bread_category`（`meal` / `sweet` / `drink`）。「すべて」はタームにしない |
| 価格 | メタ `bkery_bread_price`（整数、円） |
| 焼き上がり | メタ `bkery_bread_bake_times`（`HH:MM` のカンマ区切り）。表示用の枠は実行時に集約する |
| 店主 | カスタマイザー。ユーザーにはしない |
| 写真・動画・PC 背景 | カスタマイザーの添付 ID |
| 営業状況 | 端末のローカル時刻。サーバー時刻では判定しない |
| 定休日 | 曜日番号。空なら毎日営業 |
| 標準投稿 | 使わない。管理メニューからは消さない |
| サンプルバッジ | モックの「サンプル情報」表示はテーマに出さない |

## 1.5 全体構成

```
[ブラウザ]
   │
   ▼
[front-page.php]
   ├─ header（ステージ、目次、SVG スプライト）
   ├─ template-parts（hero 〜 instagram、固定ナビ）
   ├─ dialogs（商品詳細、写真拡大、動画）
   └─ footer
        │ bkery_get_theme_mod / WP_Query(bread)
        ▼
[データ]
   ├─ CPT bread + bread_category + bkery_bread_* メタ
   ├─ theme_mod（inc/theme-mods.php）
   └─ 添付ファイル（画像・動画）
        ▲
[inc]
   ├─ shop-hours.php / theme-mods.php / helpers.php / colors.php
   ├─ enqueue.php / customizer.php
   └─ bread.php / setup-pages.php

[dist/js/theme.js]
   └─ shop-status.ts の計算結果を、status / hero / carousel /
      product-sheet / lightbox / video / stage / reveal / shop-ui が DOM に反映
```

商品カードの HTML は PHP が出力します。フロントは一覧を組み立て直しません。営業中の文言と「次の焼き上がり」の強調だけ、端末時刻で更新します。

## 1.6 ビルド

| コマンド | 動作 | 出力 |
|---|---|---|
| `pnpm install` | 依存導入 | `node_modules/` |
| `pnpm dev` | `vite build --watch` | `dist/` |
| `pnpm run build` | 本番ビルド | `dist/css/app.css`、`dist/js/theme.js` |
| `pnpm test` | Vitest | — |
| `composer test:unit` | PHPUnit（WordPress なし） | — |
| `composer test:integration` | PHPUnit（WordPress あり） | — |

PHP だけの編集はビルド不要です。

## 1.7 セットアップ（総合テストの前提）

1. `pnpm install` と `pnpm run build`
2. 外観 > テーマで bakery_modan を有効化
3. 有効化処理が固定ページ「ホーム」（スラッグ `home`）を用意し、ホームページ表示をその固定ページにする
4. 外観 > カスタマイズでサンプルの住所・SNS・注記を本番の値に変える
5. パンを投入する。カテゴリ `meal` / `sweet` / `drink` は初回有効化で作る
6. 個別のパン URL が 404 のときは、設定 > パーマリンクを一度保存する

## 1.8 並行作業の境界

`AGENTS.md` の担当表に従います。設定キーの正本は `bkery_theme_mod_registry()` です。
