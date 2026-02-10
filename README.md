composer dump-autoload
git reset --hard   
cd /var/www/gg
sudo rm -f public/hot
sudo -u www-data php artisan view:clear
sudo systemctl reload apache2


amjad@amjad:/var/www$ sudo chown -R amjad:amjad /var/www/gg
amjad@amjad:/var/www$ sudo chmod -R 777 /var/www/gg
amjad@amjad:/var/www$ sudo systemctl restart apache2

rm -f public/hot

git config --get remote.origin.url          # confirm you have an origin
git ls-remote --symref origin HEAD          # shows the default branch, e.g. "refs/heads/main"
