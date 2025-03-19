# 既存LaravelプロジェクトをDocker移行

## 1. WSLとDockerのインストール

以下のドキュメントを参考にWSLおよび、Dockerのインストールをしてください。  
[03_ローカルPCに開発環境構築.md](https://github.com/cw-develop/mypage-sample2/blob/test_docker/doc2/03_%E3%83%AD%E3%83%BC%E3%82%AB%E3%83%ABPC%E3%81%AB%E9%96%8B%E7%99%BA%E7%92%B0%E5%A2%83%E6%A7%8B%E7%AF%89.md)

-----
## 2. Laravel Sail

### Laravel Sailとは

Laravel Sailは、LaravelアプリケーションをDocker環境で簡単に実行できる軽量な開発環境です。  
Docker Composeを使用して、Linux(Ubuntu)・Nginx・Mysql・PHPの環境を一瞬で作り上げてくれる。  


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
インストールには時間がかかります。  

-----
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

-----
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

-----
## 5. ./vendor/bin/sailのエイリアス設定

今後、Laravel Sail環境下では以下のようにコマンドを実行していく、
```
./vendor/bin/sail [コマンド]
```

しかし、`./vendor/bin/sail`が長いため、エイリアス設定をする。  
※設定前に`./vendor/bin/sail down`や`./vendor/bin/sail stop`などでコンテナを落としてください。  

仮想空間のホームディレクトリを確認する。  
```
\\wsl$\Ubuntu\home\ユーザー名
```

ディレクトリの中に`.bashrc`ファイルがあることを確認し、ファイルを開く。  
※ない場合は隠しファイルの表示設定を確認してください。  

`.bashrc`ファイルに以下の記述を追加してください。  
```
alias sail="./vendor/bin/sail"
```

変更を反映するために以下のコマンドを実行
```
source ~/.bashrc
```

こうすることでコマンド実行は以下のように短縮して実行できる。
```
sail [コマンド]
```

例えば、コンテナの立ち上げと落とす時は以下のようになります。  
```
sail up -d
sail down
```

これより下ではエイリアス設定が済んでいて、`sail`でコマンド実行ができる前提で進めます。 

-----
## 6. npmコマンド

以下実行してください
```
sail npm install
```

```
sail npm run dev
```

-----
## 7. artisanコマンド

適宜実行してください
```
php artisan migrate
php artisan key:generate
```

-----
## 8. 動作確認

- マイページ [http://localhost:8000](http://localhost:8000)
- phpmyadmin http://localhost:8088

-----
## 9. コンテナを落とす

```
docker compose down
```

