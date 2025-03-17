# 既存LaravelプロジェクトをDocker移行

1. Dockerのインストール
### 公式サイト
[Docker](https://www.docker.com/products/docker-desktop/)

### インストール方法

👇こちらなど参考にインストールをしてください
[【入門】Docker Desktopとは何ができるの？インストールと使い方 - カゴヤのサーバー研究室](https://www.kagoya.jp/howto/cloud/container/dockerdesktop/)

インストール後Docker Desktopを起動して`docker -v`などでインストールされてることを確認してください

## 2. Laravel Sail
### Laravel Sailとは
LaravelをDockerで動かすのに便利
Linux(Ubuntu)・Nginx・Mysql・PHPの環境を一瞬で作り上げてくれる
#### 参考
[【Laravel入門】Laravel sailとは？Laravel sailで環境構築までしてみる](https://qiita.com/takegons/items/644dd262801244af769f)

### Laravel Sailのインストール
**※前提**
.envの作成が行われていること

既存プロジェクトの`composer.json`と同じ階層で以下コマンド実施
```
composer require laravel/sail --dev
```
`composer.json`にLaravel Sailが追加されていればOK  
![image](https://github.com/user-attachments/assets/0c6c1663-f9ba-4201-9a2c-fbef02403b81)


続いて以下コマンドを実施
```
php artisan sail:install
```
コンテナにインストールするミドルウェアを選択する. 
今回は`mysql`のみ
```
Which services would you like to install? [mysql]:
  [0] mysql
  [1] pgsql
  [2] mariadb
  [3] redis
  [4] memcached
  [5] meilisearch
  [6] minio
  [7] mailhog
  [8] selenium
```
インストールにはめちゃくちゃ時間がかかります

## 3. docker-compose.yml編集
### コンテナ名を追加
コンテナに名前をつけておく
名前がないとコンテナIDを指定しての操作など発生してしまう
```
container_name: "app"
```
![image 2](https://github.com/user-attachments/assets/e9a83187-6edf-4705-a9b4-a1ba12f8a935)


```
container_name: "mysql"
```
![image 3](https://github.com/user-attachments/assets/e6ac2ea8-e8ae-4aea-95c0-071a2c3de6e8)


### ポート番号を既存プロジェクトに合わせる
```
- '${APP_PORT:-8000}:80'
```
![image 4](https://github.com/user-attachments/assets/4c9fe469-795d-44f8-8c7e-7fb7daac833f)


### phpmyadmin追加
デフォルトのLaravel Sailだとphpmyadminがないため追加
```
phpmyadmin:
        image: phpmyadmin/phpmyadmin
        links:
            - mysql:mysql
        ports:
            - 8088:80
        environment:
            PMA_USER: "${DB_USERNAME}"
            PMA_PASSWORD: "${DB_PASSWORD}"
            PMA_HOST: mysql
        networks:
            - sail
```
![image 5](https://github.com/user-attachments/assets/2284d2bf-2caa-4823-8af0-21a4c9ebea52)


## 4. コンテナ起動
```
./vendor/bin/sail up -d
```
もしくは
```
docker compose up -d
```
でコンテナを起動できます。

`docker ps -a`コマンドで立ち上げたコマンドを確認できます。

## 5. npmコマンド
npmコマンドはそのまま実施できます
以下実行してください
```
npm install
```

```
npm run dev
```

## 6. artisanコマンド
artisanコマンドは普通に実行できないはず…
例えば以下コマンドなど
```
php artisan migrate
```
```
php artisan db:seed --class=MypageDummyDataSeeder
```


実行の仕方は２通りあります
#### 1. appコンテナ内で実施する

appコンテナへの入り方
```
docker exec -it app bash
```
コンテナへ入れたら以下のようになる  
![image 6](https://github.com/user-attachments/assets/284a1807-901b-4614-be61-47efe17af154)

この状態であれば`php artisan migrate`などのartisanコマンドが使える

#### 2. `./vendor/bin/sail`をつける
実行するartisanコマンドの前に以下をつける
```
./vendor/bin/sail
```

#### 必要コマンド実行
`php artisan key:generate`や`php artisan migrate`など必要コマンドを実行してください
`php artisan serve`はnginxで動くため今回は不要

## 7. 動作確認
- マイページ [http://localhost:8000](http://localhost:8000)
- phpmyadmin http://localhost:8088

## 8. コンテナを落とす
```
docker compose down
```

