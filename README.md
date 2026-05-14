# Trustindex Cégértékelő Mini-alkalmazás

Symfony 7.4 alapú cégértékelő alkalmazás – Trustindex medior PHP fejlesztői tesztfeladat.

Tesztelve: win 11 + php 8.4.16 + composer 2.8.2 + Symfony CLI version 5.5.6
(php 8.2 kompatibilis, nincsenek 8.3, 8.4-et megkövelető kódok)

Agent: Claude Code

Egyéb: .env, phpunit.xml szándékosan van commitolva a könyebb gyorsabb indítás miatt, a reqiure/require-dev szekció is ennek mentén van

## Indítás - javasolt DEMO telepítés

```bash

#telepítés
# .env APP_ENV=prod APP_DEBUG=0
# clone
git clone https://github.com/er2es/trustindex_mini_app.git
cd trustindex_mini_app

#install
composer install --optimize-autoloader --classmap-authoritative

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
# ellenőrzés
php vendor/bin/php-cs-fixer fix --dry-run --diff
# javítás
php vendor/bin/php-cs-fixer fix                  
```

## Parancsok

```bash
# minden értékelés és cég törlése
php bin/console app:data:clear

# demo adatok generálása, igazából fixture is lehetett volna
php bin/console app:data:load-demo --count=100 
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
- csrf védelem, csak a review beküldése formon van, like/dislike ajaxokon pl nincs
- Apikon nincs legalább egy api key védelem


## API dokumentáció

Az alkalmazás elindítása után: [http://localhost:8000/api/doc](http://localhost:8000/api/doc)

## Munkaidő napló

| Feladat | Idő |
|---------|-----|
| Projekt skeleton, entitások, migráció, repository réteg | ~1,5 ó |
| Controllerek (web + API), service réteg, DTO-k | ~2 ó |
| Twig sablonok, Bootstrap design, kártya layout, navbar | ~2 ó |
| AJAX form submit, inline validáció, csillagpicker, lapozás | ~3 ó |
| Autocomplete, multi-token keresés, like/dislike | ~2 ó |
| Profanity szűrő, XSS-védelem, PHPUnit tesztek | ~1,5 ó |
| Demo parancsok, prod mód, README, javítások | ~1 ó |
| **Összesen** | **~13 ó** |
