# League of Fantasy

## Developement

The dev setup is based on the following article [here](https://medium.com/@chewysalmon/laravel-docker-development-setup-an-updated-guide-72842dfe8bdf)

Launch the project: 
```
make dev
```

Then run the migrations if needed:
```
docker compose exec app php artisan migrate
```
