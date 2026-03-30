# SHARE

Twitter 風 SNS アプリ

## 作成した目的

コメントを投稿して、他のユーザーとコミュニケーションを取れるアプリ

## 機能一覧

- ログイン/ログアウト/新規登録
- つぶやきの投稿/削除/編集
- コメントの投稿/削除/編集(つぶやきに対してのコメント)
- いいね機能(つぶやきのみ)
- 自分のつぶやきのみ表示する(絞り込み)
- 退会機能（ユーザーの削除機能）
- ユーザー設定変更(パスワード、ユーザー名)
- 画面背景色の変更機能
- ブロック機能(設定したユーザーには自分の投稿が表示されない)
- バリデーション(ログイン、新規登録、退会、投稿、コメント、パスワード変更)

## 実装予定

### 全体

- 可読性の向上／コメントアウトの見直し
- 不要ファイル、ディレクトリの削除
- readmeの追記(環境構築)

## 使用技術(実行環境)

- Laravel 8 系（`composer.json` / `composer.lock` 参照。Docker の PHP は `docker/php/Dockerfile` のベースイメージに準拠：**php:8.2-fpm**）
- Nuxt 4.3.1（Vue 3.5.28）
- Node.js（例: 24.x 前後。`frontend/nuxt/package.json` の動作確認に利用）
- MySQL 8.0.26
- Nginx 1.21.1
- Docker 28.2.2

### 認証まわりの技術スタック

- バックエンド: Laravel 8 + Laravel Sanctum（セッション / Cookie ベース SPA 認証）
  - `api/login` / `api/register` / `api/logout` / `api/user` などの API で実装
- フロントエンド: Nuxt 4（Composition API）
  - ページ: `app/pages/login.vue` / `app/pages/register.vue`
  - `ofetch`（`$fetch`）を用いて Laravel API を呼び出し、Sanctum の `/sanctum/csrf-cookie` を経由して CSRF 対策を実施

## テーブル設計・ER 図

DB のテーブル定義・関連・Mermaid による ER 図は次のファイルにまとめています。

- **[docs/table-design.md](./docs/table-design.md)**

GitHub 上では同ファイル内の Mermaid ブロックが図として表示されます。ローカルでは [Mermaid Live Editor](https://mermaid.live/) にコードを貼り付けて確認できます。

## 環境構築

**Docker ビルド**

1. https://github.com/youhonami/share.git

- ターミナルで git clone git@github.com:youhonami/share.git を実行
  - リモートリポジトリを作成
  - ターミナルで git remote set-url origin 新規リポジトリの紐付け先リンク　を実行
  - ターミナルで git remote -v を実行。変更を確認。
  - ローカルリポジトリの変更を新しいリモートリポジトリに反映

  ```
  git status
  git add .
  git commit -m "例：リモートリポジトリの変更"
  git push origin main
  ```

2. DockerDesktop アプリを立ち上げる
3. `docker-compose up -d --build`

> Mac（Apple Silicon）などで `no matching manifest for linux/arm64/v8` が出る場合は、`docker-compose.yml` の `mysql` サービスに `platform` を追加してください。

```yaml
mysql:
  platform: linux/amd64
  image: mysql:8.0.26
  environment:
    # ... 以下既存のまま
```

**Laravel 環境構築**

Laravel のプロジェクトルートは **`backend/src`** です（Docker 内では `/var/www/src`。Nginx の `root` もこの `public` を向いています）。

1. `docker-compose exec php bash`
2. アプリディレクトリへ移動: `cd src`
3. `composer install`  
   （失敗する場合はネットワークや PHP 拡張を確認。`composer.json` に `laravel/sail` は dev 依存として含まれています）
4. `backend/src/.env.example` を `backend/src/.env` にコピー（または同等の内容で `.env` を新規作成）  
   > リポジトリ付属の `.env.example` は `DB_HOST=127.0.0.1` など **ローカル直実行向け**の値です。**Docker で起動する場合**は次のとおり DB を書き換えてください（`docker-compose.yml` の `mysql` サービスと一致）。
5. `.env` で少なくとも以下を環境に合わせて設定

```
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_FROM_ADDRESS=認証メールの送信元となるメールアドレスを入力してください
```

6. アプリケーションキーの作成

```bash
php artisan key:generate
```

7. マイグレーションの実行

```bash
php artisan migrate
```

8. シーディングの実行

```bash
php artisan db:seed
```

9. ストレージのシンボリックリンクを作成

```bash
php artisan storage:link
```

（上記 `php artisan` はすべてコンテナ内の **`/var/www/src`** で実行してください。）

**Nuxt（フロントエンド）**

API はデフォルトで Nginx 経由の **`http://localhost`**（ポート 80）を想定しています。CORS は `backend/src/config/cors.php` で `http://localhost:3000` が許可されています。

1. `cd frontend/nuxt`
2. `npm install`
3. 必要に応じて `.env` で API のベース URL を指定（未設定時は `nuxt.config.ts` のデフォルト `http://localhost`）

```
NUXT_PUBLIC_API_BASE=http://localhost
```

4. 開発サーバー起動: `npm run dev`（通常 `http://localhost:3000`）

## URL

- フロント（開発）: http://localhost:3000/login
- API（Nginx 経由）: http://localhost （バックエンド `public`）
- phpMyAdmin: http://localhost:8080/
