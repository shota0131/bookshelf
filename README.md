# BookShelf

書籍の登録・管理・レビュー・お気に入りなどを行う書籍管理アプリです。

Laravelを使用して、書籍管理に必要な基本的なCRUD機能やAPIを実装しています。

---

## 使用技術

- PHP 8.5
- Laravel 10
- Laravel Sail
- MySQL
- Blade
- Tailwind CSS
- Laravel Sanctum（応用機能で使用予定）
- Docker
- Docker Compose
- phpMyAdmin
- Vite
- @tailwindcss/forms

---

## 主な機能

現在実装中の機能です。

### 書籍管理

- 書籍一覧表示
- 書籍詳細表示
- 書籍登録
- 書籍編集
- 書籍削除
- ジャンルとの紐付け
- 書籍の平均評価表示
- レビュー件数表示

### ジャンル管理

- ジャンル一覧表示
- ジャンル詳細表示
- ジャンル登録
- ジャンル編集
- ジャンル削除

### お気に入り

- お気に入り一覧表示
- 書籍のお気に入り登録・解除

### ランキング

- 書籍の平均評価を基準としたランキング表示

### マイ読書レポート

- 自分の読書状況を確認
- 読書した書籍数の表示
- 読書中・完了などの読書状況を表示
- 読書に関する統計情報を表示

### 読書計画

- 読書計画の一覧表示
- 読書計画の作成
- 読書計画の編集
- 読書計画の削除
- 読書計画のステータス管理
- 目標日の設定
- ユーザーごとの読書計画管理

---

## データベース

以下のテーブルを使用しています。

### users

ユーザー情報を管理します。

| カラム | 内容 |
|---|---|
| id | ユーザーID |
| name | ユーザー名 |
| email | メールアドレス |
| email_verified_at | メール認証日時 |
| password | パスワード |
| remember_token | ログイン保持用トークン |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### genres

書籍のジャンルを管理します。

| カラム | 内容 |
|---|---|
| id | ジャンルID |
| name | ジャンル名 |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### books

書籍情報を管理します。

| カラム | 内容 |
|---|---|
| id | 書籍ID |
| title | タイトル |
| author | 著者名 |
| isbn | ISBN |
| published_date | 出版日 |
| description | 書籍説明 |
| image_url | 書籍画像URL |
| user_id | 登録ユーザーID |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### reviews

書籍へのレビューを管理します。

| カラム | 内容 |
|---|---|
| id | レビューID |
| user_id | 投稿ユーザーID |
| book_id | 書籍ID |
| rating | 評価 |
| comment | コメント |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### book_genre

書籍とジャンルの多対多関係を管理します。

| カラム | 内容 |
|---|---|
| id | ID |
| book_id | 書籍ID |
| genre_id | ジャンルID |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### favorites

ユーザーのお気に入り書籍を管理します。

| カラム | 内容 |
|---|---|
| id | お気に入りID |
| user_id | ユーザーID |
| book_id | 書籍ID |
| created_at | 作成日時 |
| updated_at | 更新日時 |

### review_likes

レビューへのいいねを管理します。

| カラム | 内容 |
|---|---|
| id | いいねID |
| user_id | ユーザーID |
| review_id | レビューID |
| created_at | 作成日時 |
| updated_at | 更新日時 |


### reading_plans

ユーザーごとの読書計画を管理します。

| カラム | 内容 |
|---|---|
| id | 読書計画ID |
| user_id | ユーザーID |
| book_id | 書籍ID |
| target_date | 目標日 |
| status | 読書状態 |
| completed_at | 完了日時 |
| created_at | 作成日時 |
| updated_at | 更新日時 |

---

## テーブルのリレーション

- User hasMany Books
- User hasMany Reviews
- User belongsToMany Books through Favorites
- User belongsToMany Reviews through ReviewLikes
- User hasMany ReadingPlans
- Book belongsTo User
- Book belongsToMany Genres
- Book hasMany Reviews
- Book belongsToMany Users through Favorites
- Book hasMany ReadingPlans
- Genre belongsToMany Books
- Review belongsTo User
- Review belongsTo Book
- Review belongsToMany Users through ReviewLikes
- ReadingPlan belongsTo User
- ReadingPlan belongsTo Book
---

## Seeder

初期データをSeederで登録できるようにしています。

### UserSeeder

5人の初期ユーザーを登録します。

- 山田太郎
- 鈴木花子
- 田中一郎
- 佐藤美咲
- 高橋健太

メールアドレスを基準に `firstOrCreate()` を使用しています。

パスワードは `Hash::make()` を使用してハッシュ化しています。

### GenreSeeder

以下の10ジャンルを登録します。

- 小説
- ビジネス
- 技術書
- 自己啓発
- エッセイ
- 歴史
- 科学
- 芸術
- 料理
- 旅行

ジャンル名を基準に `firstOrCreate()` を使用しています。

### BookSeeder

11冊の初期書籍を登録します。

書籍には以下の情報を登録しています。

- タイトル
- 著者
- ISBN
- 出版日
- 説明
- 画像URL
- ジャンル

ISBNを基準に `firstOrCreate()` を使用し、ジャンルは `genres()->sync()` で紐付けています。

### ReviewSeeder

初期レビューを登録します。

- 5人のユーザー
- 11冊の書籍
- 評価
- コメント

を使用してレビューの初期データを作成しています。

### FavoriteSeeder

ユーザーごとにお気に入り書籍を登録します。

`syncWithoutDetaching()` を使用しています。

### ReviewLikeSeeder

レビューへのいいねデータを登録します。

`syncWithoutDetaching()` を使用しています。

### ReadingPlanSeeder

読書計画の初期データを登録します。

6件の読書計画を作成し、以下の状態を確認できるようにしています。

- 読書中の読書計画
- 目標日が当日の読書計画
- 目標日を過ぎた読書計画
- 目標日まで余裕がある読書計画
- 完了済みの読書計画
- 他ユーザーの読書計画

目標日は `Carbon::today()` を基準に設定しています。

### DatabaseSeeder

Seederの依存関係を考慮して、以下の順番で実行します。

1. UserSeeder
2. GenreSeeder
3. BookSeeder
4. ReviewSeeder
5. FavoriteSeeder
6. ReviewLikeSeeder
7. ReadingPlanSeeder

---

## バリデーション

FormRequestを使用してバリデーション処理をControllerから分離しています。

### 書籍

- `BookIndexRequest`
- `StoreBookRequest`
- `UpdateBookRequest`

### ジャンル

- `StoreGenreRequest`
- `UpdateGenreRequest`

### 読書計画

- `StoreReadingPlanRequest`
- `UpdateReadingPlanRequest`

バリデーションエラーメッセージは日本語で定義しています。

主なバリデーション内容：

### 書籍

- 必須項目チェック
- タイトル・著者・画像URLなどの最大文字数チェック
- ISBNの13桁チェック
- ISBNの一意性チェック
- 出版日の形式チェック
- ジャンル選択チェック
- URL形式チェック

### ジャンル

- 必須項目チェック
- ジャンル名の一意性チェック

### レビュー

- 評価の必須チェック
- 評価が1〜5の範囲かチェック
- コメントの必須チェック
- コメントが1000文字以内かチェック

### 読書計画

- 書籍の必須チェック
- 登録されている書籍かチェック
- 同一ユーザー・同一書籍で進行中の計画がないかチェック
- 目標日の必須チェック
- 目標日の形式チェック
- 目標日が今日以降かチェック

---

## 認可

読書計画はユーザーごとに管理しています。

- 自分の読書計画のみ編集可能
- 自分の読書計画のみ削除可能
- 他ユーザーの読書計画を編集・削除しようとした場合は403エラーとする

Policyを使用してユーザーごとの操作権限を制御しています。

---

## API

公開APIを `/api/v1` 配下に実装しています。

現在のAPIエンドポイント設計：

| Method | Endpoint | 内容 |
|---|---|---|
| GET | `/api/v1/books` | 書籍一覧 |
| GET | `/api/v1/books/{book}` | 書籍詳細 |
| POST | `/api/v1/books` | 書籍登録 |
| PUT | `/api/v1/books/{book}` | 書籍更新 |
| DELETE | `/api/v1/books/{book}` | 書籍削除 |

APIレスポンスにはLaravel API Resourceを使用しています。

現在作成しているResource：

- `BookResource`
- `BookDetailResource`
- `ReviewResource`



---

## API Controller

API用のBookControllerを作成しています。

```text
app/Http/Controllers/Api/V1/BookController.php

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


