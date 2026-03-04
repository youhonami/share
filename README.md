# share

Laravel8
PHP
nuxt.js
JavaScript

## 機能概要

- ログイン
- ログアウト
- 新規登録
- つぶやきの投稿
- つぶやきの削除
- コメントの投稿(つぶやきに対してのコメント)
- いいね機能

## 認証まわりの技術スタック

- バックエンド: Laravel 8 + Laravel Sanctum（セッション / Cookie ベース SPA 認証）
  - `api/login` / `api/register` / `api/logout` / `api/user` などの API で実装
- フロントエンド: Nuxt 3（Composition API）
  - ページ: `app/pages/login.vue` / `app/pages/register.vue`
  - `ofetch`（`$fetch`）を用いて Laravel API を呼び出し、Sanctum の `/sanctum/csrf-cookie` を経由して CSRF 対策を実施
