
- Create database

```
echo "create database pinbotbot" | mysql -u username -p
```

- Set storage folders permissions

```
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

- Configure .env file
- Run migration

```
php artisan migrate
```
