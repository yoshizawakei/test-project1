# coachtechフリマ

## 環境構築
Dockerビルト
1. git clone git@github.com:yoshizawakei/test-project1.git
2. docker-compose up -d --build

※MySQLは、OSによって起動しない場合があるので、それぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

## 使用技術(実行環境)
- PHP 7.4.9
- Laravel 8.83.29
- MySQL 15.1

## ER図
![ER図](test-project1.png)

## URL
- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
