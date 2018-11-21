
- Create database

```
echo "create database pintebox" | mysql -u username -p
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

- Configure cron

Edit crons:

```
crontab -e
```

Add this cron:

```
* * * * * php /var/www/pintebox/artisan schedule:run >> /dev/null 2>&1
```

- Start queue listener

```
php artisan queue:work
```