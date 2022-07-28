
a2enmod rewrite
a2enmod headers

#composer create-project --prefer-dist laravel/laravel ./proyecto "8.*"
#mv ./proyecto/* .
composer install

cd /app && chgrp -R www-data storage bootstrap/cache public
cd /app && chmod -R ug+rwx storage bootstrap/cache public

# chown -R www-data:www-data /public
# chmod -R 755 /public
/usr/sbin/apache2ctl -DFOREGROUND
