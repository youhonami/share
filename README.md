# SHARE

Twitter 風 SNS アプリ

## 作成した目的

コメントを投稿して、他のユーザーとコミュニケーションを取れるアプリ

## 機能一覧

- ログイン
- ログアウト
- 新規登録
- つぶやきの投稿
- つぶやきの削除
- コメントの投稿(つぶやきに対してのコメント)
- いいね機能
- 退会機能（ユーザーの削除機能）

## 使用技術(実行環境)

- Laravel 8.83.29 (PHP 8.5.3)
- Nuxt 4.3.1 (Vue 3.5.28)
- Node.js 24.4.1
- MySQL 8.0.26
- Nginx 1.21.1
- Docker 28.2.2

### 認証まわりの技術スタック

- バックエンド: Laravel 8 + Laravel Sanctum（セッション / Cookie ベース SPA 認証）
  - `api/login` / `api/register` / `api/logout` / `api/user` などの API で実装
- フロントエンド: Nuxt 3（Composition API）
  - ページ: `app/pages/login.vue` / `app/pages/register.vue`
  - `ofetch`（`$fetch`）を用いて Laravel API を呼び出し、Sanctum の `/sanctum/csrf-cookie` を経由して CSRF 対策を実施

## テーブル設計

## ER 図

## 環境構築

## URL

- 開発環境:http://localhost:3000/login
- phpMyAdmin:http://localhost:8080/
