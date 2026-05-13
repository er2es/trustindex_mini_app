# Trustindex Cégértékelő Mini-alkalmazás

Symfony 7.4 alapú cégértékelő alkalmazás – Trustindex medior PHP fejlesztői tesztfeladat.

## Indítás (fejlesztői mód)

```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony serve
# vagy
php -S localhost:8000 -t public/
```

## Indítás (éles / leadás)

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
APP_ENV=prod php -S localhost:8000 -t public/
```

## Tesztek

```bash
php bin/phpunit
```

## Kódminőség

```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff   # ellenőrzés
php vendor/bin/php-cs-fixer fix                     # javítás
```

## API dokumentáció

Az alkalmazás elindítása után: [http://localhost:8000/api/doc](http://localhost:8000/api/doc)

## Munkaidő napló

<!-- TODO: töltsd ki -->

| Feladat | Idő |
|---------|-----|
| ... | ... |
