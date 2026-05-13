Trustindex – Medior PHP Fejlesztői Tesztfeladat

Becsült idő: 4–6 óra Leadás: GitHub repository vagy ZIP archívum Stack: PHP 8.2+, Symfony

7.4, Doctrine ORM, PHPUnit



Előzetesen

● Kérdezz nyugodtan, bármit használhatsz a megoldáshoz – ahogy a való életben is.

● Nem csak a működést vizsgáljuk. Legalább ugyanolyan hangsúly van a kód

minőségén.

● Symfony kód szabványok betartása kötelező (tipp: php-cs-fixer).

● Clean Code és DRY elvek betartása elvárt.

● Teszteseteket ne felejts (backend oldalon PHPUnit elegendő).

● AI segítséget bátran használhatsz – de legyél felkészülve arra, hogy a megoldást élőben

is meg tudd magyarázni.



Kontextus

A Trustindex egy review-aggregátor platform: ügyfelek különböző forrásokból (Google,

Facebook, Trustpilot stb.) gyűjtik és jelenítik meg ügyfélvéleményeiket. A feladat egy

leegyszerűsített, de valós logikát tükröző modult kér tőled.



Feladat: Cégértékelő minialkalmazás

Készíts egy Symfony webalkalmazást, amelyben felhasználók véleményt írhatnak cégekről. A

vélemények nyilvánosak és listázva vannak a főoldalon.



1\. Adatmodell (Doctrine ORM)

Review entitás



Mező Típus Megjegyzés

id int auto increment

company\_nam

e



string(255)



rating int 1–5

review\_text text

author\_email string valid email

created\_at datetime automatikusan

beállítva

updated\_at datetime automatikusan

beállítva



Követelmények:

● PHP attribute alapú Doctrine mapping

● Migration generálva legyen (doctrine:migrations:diff)

● Külön ReviewRepository osztály a lekérdezési logikához



2\. Funkcionalitás

2.1 Új vélemény beküldése

● Symfony Form (ReviewType osztály) segítségével

● Validációs szabályok:

○ Minden mező kötelező

○ rating: csak 1–5 közötti egész szám

○ author\_email: valid email formátum

● Sikeres mentés után flash üzenet: „Köszönjük a véleményed!\&quot;

2.2 Vélemények listázása

Twig nézet, amely mutatja:

● Cégnév

● Értékelés (csillag ikonokkal)

● Vélemény szövege (csonkítva, ha hosszú)



● Dátum

2.3 Vélemény részletező oldal

● Külön route és Controller action egy adott véleményhez

2.4 Összesített cégstatisztika (kötelező)

A ReviewRepository-ban implementálj egy metódust, amely visszaadja cégenkénti bontásban:

● a beérkezett vélemények számát

● az átlagos értékelést

Jelenítsd meg ezeket egy külön /companies oldalon, átlagos értékelés szerint csökkenő

sorrendben.

2.5 Keresés cég neve alapján (bónusz)

2.6 Tegyél bele valami extrát, ami megkülönbözteti a megoldásodat a

többitől (bónusz)



3\. Technológiai követelmények

● Symfony 7.4

● Doctrine ORM + Migrations

● Twig (egységes base.html.twig layout)

● Symfony Forms + Validator

● Bootstrap vagy egyedi CSS

● PHP 8.2+



4\. Tesztelés

● Legalább 1 Funkcionális és 1 Unit teszt készítése

● Teszteld az átlagszámítási és a rendezési logikát

● A tesztek php bin/phpunit paranccsal fussanak le hibamentesen



5\. README elvárások

A README tartalmazza:

● Rövid leírás a projektről

● Szükséges parancsok (composer install, symfony serve, stb.)

● Adatbázis létrehozás és migrációk futtatása

● Munkaidő napló (feladatonként bontva)



Leadás

● GitHub vagy GitLab repository linkje vagy ZIP archívum

● A main branch tartalmazza a végleges, futtatható állapotot

● Határidő: a feladat megküldésétől számított 5 munkanap

