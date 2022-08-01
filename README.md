# simple-quiz
Testa uzdevums no Vendon

## Testa vides uzstādīšana

### Manuāla uzstādīšana

* Serveri vajadzētu mapot uz /public mapi
* Nepieciešams izmantot aktuālo php versiju
* Datubāzes atlējums atrodas /sql/dump.sql failā
* Datubāzes pieslēguma datus var labot /config/database.php failā


### Lando

Izstrādātāju rīks [Lando](https://lando.dev/) piedāvā vienkāršu veidu (papildus abstrakciju darbam ar `docker` konteineriem) kā uzstādīt dažādas izstrādes vides. Ja datorā šis rīks ir uzstādīts, tad 
projektu var inicializēt balstoties uz .lando.yml saturu:

```
lando start

lando db-import sql/dump.sql

lando composer install
```