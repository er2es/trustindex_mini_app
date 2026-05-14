# Trustindex Cégértékelő Mini-alkalmazás

Symfony 7.4 alapú cégértékelő alkalmazás – Trustindex medior PHP fejlesztői tesztfeladat.

Tesztelve: win 11 + php 8.4.16 + composer 2.8.2 + Symfony CLI version 5.5.6
(php 8.2 kompatibilis, nincsenek 8.3, 8.4-et megkövelető kódok)

Agent: Claude Code

## Indítás (prod) - javasolt DEMO telepítés

```bash

#telepítés
composer install --no-dev --optimize-autoloader --classmap-authoritative

#adatbázis (sqllite)
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

#env dump, egyszerűbb demo kedvéért az .env nem gitignore, ahogy kellene!
composer dump-env prod

#symfony local server indítása
symfony serve
```

## Indítás (fejlesztői mód - webprofiler bundle)

```bash
# edit: .env APP_ENV=dev APP_DEBUG=1
composer install
php bin/console doctrine:migrations:migrate
symfony serve
```

## Tesztek

```bash
php bin/phpunit
```

## Kódminőség

```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff   # ellenőrzés
php vendor/bin/php-cs-fixer fix                    # javítás
```

## Parancsok

```bash
php bin/console app:data:clear # minden értékelés és cég törlése
php bin/console app:data:load-demo --count=100 # demo adatok generálása, igazából fixture is lehetett volna
```

## Miért jobb / több :)

- Van autocomplete a review/cég neve és keresés cégre mezőn
- A review hozzáadásakor a cég nevet külön is mentem, ebből dolgozik az autocomplete
- A review adatmodellt bővítettem egy company_id oszloppal, hogy a statisztika kapon az adott átlagot meg lehesenn nézni, hogy miből tevődik össze
- Like/Dislike lehetőség
- Keres a review szövegében is (opcionális)
- Stopword / Profantiy filter, lehetőséggel hogy mikor legyen ékezet sensitive vagy sem (pl.: szar* => szar <> szár)
- NelmioApiBundle + docs
- (szerintem) átgondolt, szép FE :)
- Logikus vissza kezelések (pl statisztika -> cég review lista -> vissza a cégre)
- Pagination, külön env változó a reviewekre, companykra

## Ismert bugok
- A like/dislike oldalfrissítés (f5) után nem színezi a saját likejaim / dislikejaim, mert csak munkamenetben tárolom
Rövid leírás: Nem tudja az user, mire kattintott már, ezért refresh után ha ilyenre kattint, 
az zavart okozhat, mert lehet épp leveszi a korábbit stb.
Kieg: igazából nincs mivel azonosítanom (nem scope), egy sessionon belül marad meg csak, munkamenet session-id mentek, lehetett volna cookie is, azzal f5 megtartotta volna, a cookie lifetime alatt

## Refaktor, partial anti pattern
- A `/review` és a `/companies` webes végpontok saját AJAX-ágat tartalmaznak
  (`X-Requested-With` detektálás + JSON+HTML válasz), miközben a `/api/reviews`
  és `/api/companies` párhuzamosan ugyanazt az adatot adja vissza — a lekérdezési
  logika a service/repository rétegben közös, de a controller-szintű paginálás,
  szűrés és válasz-összerakás két helyen él, sérült a DRY, Clean Code - de van előnye is némi (SEO jobb a szerveren renderelt adatokkal, kevesebb js, nem kell fetch és render)


## API dokumentáció

Az alkalmazás elindítása után: [http://localhost:8000/api/doc](http://localhost:8000/api/doc)

## Munkaidő napló

| Feladat | Idő |
|---------|-----|
| ... | ... |
