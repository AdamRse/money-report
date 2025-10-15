Pour faire des commandes composer au conteneur, il faut ouvrir un terminal libre, et utiliser la commande :
docker compose exec money_report <commande>

Par exemple :
docker compose exec money_report php artisan migrate

money-report est le nom du service défini dans ./docker-compose.yml, ligne 2.