# Excursio - Documentazione Progetto

## Panoramica

Excursio è una piattaforma web per la gestione di eventi sociali di una community di Milano (cene, balli, cinema, gite, etc.). Il sito legacy è scritto in PHP procedurale ed è **ancora attivo in produzione**. Il nuovo sito Laravel lo sostituirà gradualmente, lavorando **direttamente sullo stesso database** (`excursi1_eventi`).

### Numeri del DB attuale
- 391 utenti registrati
- 1.650 eventi
- 19.836 partecipazioni
- 10.815 commenti
- 3.610 relazioni di amicizia
- 15 gruppi con 1.537 assegnazioni

---

## Requisiti implementati

### Connessione al DB legacy
Il sito Laravel punta al database `excursi1_eventi` e usa le tabelle legacy con i nomi originali:
- `utente` (invece di `users`)
- `evento` (invece di `events`)
- `partecipa` (invece di `event_user`)
- `post` (invece di `comments`)

I modelli Eloquent espongono nomi Laravel-friendly tramite accessor/mutator (es. `$user->name` legge `utente.nome`, `$event->title` legge `evento.nome`).

### Modelli adattati
- **User** → tabella `utente`, PK `userID`
- **Event** → tabella `evento`, PK `IDevento`
- **Comment** → tabella `post`, FK `id_utente`, `id_evento`
- **EventImage** → tabella nuova `event_images` (feature Laravel)

### Migrazione password
Il legacy usa MD5 senza salt. Il login Laravel implementa un dual-check: prova bcrypt, se fallisce prova MD5, e se MD5 matcha re-hasha in bcrypt. Migrazione trasparente e graduale.

### Tabelle aggiunte da Laravel
- `event_images` - galleria multi-immagine per evento
- `password_resets` - reset password
- `personal_access_tokens` - Sanctum
- `failed_jobs` - queue

### Colonne aggiunte da Laravel
- `evento.allow_guests` - abilita/disabilita ospiti per evento
- `evento.max_guests_per_user` - max ospiti per partecipante
- `post.edited_at` - tracking modifica commenti

---

## Funzionalita presenti in Laravel (nuove rispetto al legacy)

| # | Funzionalita | Note |
|---|---|---|
| 1 | Galleria immagini interna (multi-upload per evento) | Il legacy usa solo 1 immagine + URL galleria esterna |
| 2 | Commenti editabili con tracking modifica (`edited_at`) | Il legacy non permette la modifica dei commenti |
| 3 | Stato utente a 3 livelli (pending/approved/banned) | Il legacy ha solo abilitato si/no |
| 4 | Newsletter con targeting avanzato (per stato, per partecipazione, selezione manuale) | Il legacy ha newsletter base |
| 5 | Sistema ospiti configurabile per evento (allow_guests, max per user) | Il legacy ha campo "amici" piu semplice |

---

## Funzionalita presenti nel legacy MA MANCANTI in Laravel

### Priorita ALTA - COMPLETATE

| # | Funzionalita | Stato | Note implementazione |
|---|---|---|---|
| 1 | **Ruolo Organizzatore** | FATTO | Middleware `CheckCanManageEvents`, metodo `canManageEvents()`, badge ruolo nel profilo. Ruolo=0 Admin, Ruolo=1 Organizzatore, Ruolo=2 Utente. |
| 2 | **Costo evento** | FATTO | Accessor `cost`/`costo`, `formatted_cost` (es. "9,00 €"). Visibile in dettaglio evento e form creazione/modifica. |
| 3 | **Luogo evento (campo "dove")** | FATTO | Accessor `venue`/`dove`. Mostrato separatamente dall'indirizzo in dettaglio evento e form. |
| 4 | **Campi utente mancanti** | FATTO | Profilo mostra/modifica: cognome, sesso, data nascita, residenza, telefono, email (con toggle visibilita), descrizione. Registrazione include cognome, sesso, residenza. |
| 5 | **Creazione eventi da frontend** | FATTO | `EventManageController` con rotte `manage.events.*`. Organizzatori vedono solo i propri eventi, admin vedono tutti. View: index, create, edit con TinyMCE. Voce "I Miei Eventi" nella navbar. |

File aggiunti/modificati per priorita alta:
- `app/Http/Controllers/EventManageController.php` (nuovo)
- `app/Http/Middleware/CheckCanManageEvents.php` (nuovo)
- `resources/views/manage/events/index.blade.php` (nuovo)
- `resources/views/manage/events/create.blade.php` (nuovo)
- `resources/views/manage/events/edit.blade.php` (nuovo)
- `resources/lang/it/` - traduzioni italiane (validation, auth, passwords, pagination)

### Priorita MEDIA - COMPLETATE

| # | Funzionalita | Stato | Note implementazione |
|---|---|---|---|
| 6 | **Sistema amicizie** | FATTO | `FriendController` (add/remove/list), relazione `friends()` su User, pulsante aggiungi/rimuovi nel profilo, pagina lista amici, link "Amici" in navbar. Tabella legacy `amici` usata direttamente. |
| 7 | **Gruppi utente** | FATTO | Model `Group`, `Admin\GroupController` CRUD completo + gestione membri. View: index, create, edit, show con aggiunta/rimozione membri. Link "Gestione Gruppi" nel dropdown admin. |
| 8 | **Scadenza iscrizione evento** | FATTO | Accessor `deadline`, metodo `isRegistrationOpen()`. Badge scadenza in dettaglio evento, blocco iscrizione dopo scadenza (controller + view). Campo `deadline` nei form create/edit (admin e manage). |
| 9 | **Visibilita elenco partecipanti** | FATTO | Toggle `elenco_visibile` nei form create/edit. Lista partecipanti nascosta se `elenco_visibile=0` (visibile solo a organizzatore/admin). |
| 10 | **Ricerca utenti** | FATTO | `SearchController` con ricerca per username/nome/cognome. View con form e risultati, pulsante "Aggiungi amico" integrato. Link "Cerca Utenti" in navbar. |
| 11 | **Invito amici a evento** | FATTO | Metodo `inviteToEvent()` in `FriendController`. Select amici nella pagina evento (solo per partecipanti), invio email di notifica. |
| 12 | **Recupero password** | FATTO | `PasswordResetController` con form username/email. Genera password random, invia via email, salva in bcrypt. Link "Password dimenticata?" nella pagina login. |
| 13 | **Stampa lista partecipanti** | FATTO | Pagina print-friendly standalone (`events/print.blade.php`). Tabella con tutti i dati partecipanti + ospiti. Pulsante stampa nella sezione partecipanti (solo organizzatore/admin). |

File aggiunti per priorita media:
- `app/Models/Group.php` (nuovo)
- `app/Http/Controllers/FriendController.php` (nuovo)
- `app/Http/Controllers/SearchController.php` (nuovo)
- `app/Http/Controllers/PasswordResetController.php` (nuovo)
- `app/Http/Controllers/Admin/GroupController.php` (nuovo)
- `resources/views/friends/index.blade.php` (nuovo)
- `resources/views/search/users.blade.php` (nuovo)
- `resources/views/auth/forgot-password.blade.php` (nuovo)
- `resources/views/events/print.blade.php` (nuovo)
- `resources/views/admin/groups/index.blade.php` (nuovo)
- `resources/views/admin/groups/create.blade.php` (nuovo)
- `resources/views/admin/groups/edit.blade.php` (nuovo)
- `resources/views/admin/groups/show.blade.php` (nuovo)

### Priorita BASSA

| # | Funzionalita | Stato | Note implementazione | Tabella/campo legacy |
|---|---|---|---|---|
| 14 | **Incipit/riassunto evento** | FATTO | Campo textarea nei form create/edit (admin e manage). Se valorizzato, sostituisce la descrizione tronca nelle anteprime lista eventi. | `evento.incipit` (text) |
| 15 | **Sondaggio collegato all'evento** | DA FARE | URL esterno a un sondaggio associato all'evento. | `evento.sondaggio` (text) |
| 16 | **URL galleria esterna** | DA FARE | Link a galleria foto esterna (es. Google Photos, Flickr). | `evento.url_galleria` (text) |
| 17 | **Ultimo accesso utente** | FATTO | Aggiornato al login in AuthController. Mostrato nel profilo con `diffForHumans()`. | `utente.ultimo_accesso` (timestamp) |
| 18 | **Tracking utenti online** | FATTO | Middleware `TrackOnlineUsers` nel gruppo web. Upsert su `utentionline` ad ogni request, pulizia record >15 min. Lista utenti online nella sidebar. | tabella `utentionline` |
| 19 | **Statistiche visite** | DA FARE | Contatore visite giornaliere, rapporto visite/utenti. | tabella `visite` |
| 20 | **Annunci** | DA FARE | Bacheca annunci generali (non legati a eventi). Tabella esiste ma e vuota (0 record). | `annuncio` (0 record) |
| 21 | **Messaggi generali** | DA FARE | Commenti/messaggi non legati a un evento specifico. Quasi inutilizzata (1 solo record). | `commenti` (1 record) |
| 22 | **Storico eventi dedicato** | DA FARE | Vista separata per gli eventi passati con navigazione dedicata. Laravel ha `pastEvents` ma potrebbe essere meno completa. | `it/storico.php`, `it/storico_dettagli.php` |
| 23 | **Admin: tutti i partecipanti** | DA FARE | Vista admin che mostra tutti i partecipanti di tutti gli eventi in un'unica tabella. | `admin/tuttiipartecipanti.php` |
| 24 | **Admin: eventi comuni** | DA FARE | Tool admin per trovare eventi comuni tra due o piu utenti. | `admin/eventi_comuni.php` |
| 25 | **Voto evento** | DA FARE | Possibilita di votare/valutare un evento. | `it/voto_evento.php` |
| 26 | **Richiesta cancellazione account** | DA FARE | L'utente puo richiedere la cancellazione del proprio account via email all'admin. | `it/inviomailcancellazione.php` |
| 27 | **Integrazione Google Maps** | DA FARE | Mappa integrata nella pagina dettaglio evento per mostrare la posizione. | embedded in `evento_dettagli.php` |

---

## Altre modifiche e miglioramenti implementati

| Modifica | Dettagli | File coinvolti |
|---|---|---|
| **Fix Comment model** | Aggiunto `$table = 'post'`, `$timestamps = false`, `CREATED_AT = 'data'`, FK corrette in `user()` e `event()`, accessor `getContentAttribute()` e `getCreatedAtAttribute()`. | `app/Models/Comment.php` |
| **Accesso eventi solo per loggati** | Gli utenti non loggati vengono reindirizzati al login. Il pulsante "Dettagli Evento" diventa "Accedi per vedere i dettagli". | `EventController.php`, `events/index.blade.php` |
| **Sidebar sinistra** | Sempre visibile. Per guest: form di login con link a registrazione e password dimenticata. Per loggati: lista utenti online con punto verde e link al profilo. | `layouts/app.blade.php` |
| **"Benvenuto username" in navbar** | Testo di benvenuto accanto al logout per gli utenti loggati. | `layouts/app.blade.php` |
| **Hero + intro homepage** | Immagine hero (`upload_immagini/hero.jpeg`) con overlay testuale. Intro fedele al vecchio sito visibile solo ai guest, con CTA Registrati/Scrivici. | `events/index.blade.php` |
| **Doppia password per coesistenza legacy** | Aggiunta colonna `utente.password_laravel` (bcrypt). Laravel usa quella; il campo `password` (MD5) rimane intatto per il sito legacy. Al dismiss del legacy: `UPDATE utente SET password = password_laravel` + drop colonna. | `AuthController.php`, `PasswordResetController.php`, `User.php`, migrazione `2026_03_08_000001` |
| **TinyMCE API key** | Aggiunta voce `tinymce` in `config/services.php` per leggere `TINYMCE_API_KEY` dall'`.env`. | `config/services.php` |

---

## Problemi del sito legacy (cosa non funziona o e problematico)

### Problemi critici di sicurezza

| # | Problema | Dettagli | Impatto |
|---|---|---|---|
| 1 | **Funzioni `mysql_*` deprecate** | Il sito usa `mysql_connect()`, `mysql_query()`, etc. rimosse da PHP 7.0+. Il sito funziona solo su PHP 5.x, non piu supportato da anni. | Il sito non puo girare su hosting moderni. Nessun aggiornamento di sicurezza PHP. |
| 2 | **Password MD5 senza salt** | Tutte le 391 password sono hashate con MD5 puro, crackabili in secondi con rainbow tables. | Qualsiasi leak del DB comprometterebbe tutte le password. |
| 3 | **SQL injection potenziale** | Le query SQL sono costruite concatenando variabili PHP direttamente nella stringa SQL, senza prepared statements ne escaping. | Un attaccante potrebbe leggere/modificare/cancellare qualsiasi dato nel DB. |
| 4 | **Nessuna protezione CSRF** | I form non hanno token CSRF. Qualsiasi sito esterno potrebbe far eseguire azioni all'utente loggato. | Azioni non autorizzate (iscrizione eventi, modifica profilo, etc.) eseguibili da siti malevoli. |
| 5 | **Credenziali DB hardcoded** | Username, password e nome del database sono scritti direttamente nel codice sorgente PHP. | Se il codice viene esposto (es. errore server che mostra il sorgente), le credenziali DB sono compromesse. |
| 6 | **Master key hardcoded** | Esiste una password master (`!masterkey1426!`) che permette di accedere come qualsiasi utente. | Backdoor di sicurezza. Chiunque conosca questa chiave puo impersonare qualsiasi utente, admin inclusi. |

### Problemi tecnici

| # | Problema | Dettagli |
|---|---|---|
| 7 | **jQuery 1.3.2** | Versione del 2009, con vulnerabilita XSS note e incompatibilita con browser moderni. |
| 8 | **Nessun version control** | Il codebase contiene file di backup con date nel nome (es. `file_20140315.php`). Nessun uso di Git. |
| 9 | **Codice duplicato** | Logica ripetuta in piu file senza alcuna astrazione (es. connessione DB, header/footer, validazione). |
| 10 | **Nessuna gestione errori** | Gli errori PHP vengono mostrati direttamente all'utente, potenzialmente esponendo informazioni di sistema. |
| 11 | **Encoding inconsistente** | Mix di charset (ISO-8859-1 e UTF-8), problemi con caratteri accentati. |

### Feature legacy rotte o inutilizzate

| # | Problema | Dettagli |
|---|---|---|
| 12 | **Tabella `annuncio` vuota** | 0 record. La feature bacheca annunci non e mai stata usata o e stata abbandonata. |
| 13 | **Tabella `commenti` quasi vuota** | 1 solo record. Il sistema di messaggi generali (non legati a eventi) non e mai decollato. |
| 14 | **Forum esterno** | Il link "Forum" nel menu punta a un servizio esterno, non integrato nel sito. |

---

## Architettura attuale Laravel

### Stack
- **Framework:** Laravel 11
- **Ambiente:** DDEV (Docker)
- **DB:** MySQL - database condiviso `excursi1_eventi`
- **Frontend:** Blade + Bootstrap 5 + TinyMCE (editor rich text)
- **Auth:** Session-based con Sanctum

### Struttura modelli e mappatura

```
User.php      → tabella "utente"    (PK: userID)
Event.php     → tabella "evento"    (PK: IDevento)
Comment.php   → tabella "post"      (FK: id_utente, id_evento)
EventImage.php → tabella "event_images" (nuova, FK: event_id → IDevento)

Pivot partecipazione: tabella "partecipa" (id_utente, id_evento, amici, data_iscrizione)
```

### Sistema ruoli
```
utente.ruolo = 0 → Admin (accesso completo + pannello admin + gestione eventi)
utente.ruolo = 1 → Organizzatore (gestione propri eventi da frontend)
utente.ruolo = 2 → Utente normale

utente.abilitato = 0 → In attesa di approvazione
utente.abilitato = 1 → Approvato
```

### File principali
```
app/Models/User.php              - Modello utente con accessor legacy
app/Models/Event.php             - Modello evento con accessor legacy
app/Models/Comment.php           - Modello commenti (tabella "post")
app/Models/EventImage.php        - Galleria immagini (tabella nuova)
app/Http/Controllers/AuthController.php       - Login/registrazione (dual-hash MD5/bcrypt)
app/Http/Controllers/EventController.php      - Lista eventi, dettaglio, partecipazione
app/Http/Controllers/CommentController.php    - CRUD commenti
app/Http/Controllers/GuestController.php      - Gestione ospiti
app/Http/Controllers/ProfileController.php    - Profilo utente
app/Http/Controllers/EventManageController.php   - CRUD eventi organizzatori
app/Http/Middleware/CheckCanManageEvents.php      - Verifica ruolo admin/organizzatore
app/Http/Controllers/Admin/EventController.php  - CRUD eventi admin
app/Http/Controllers/Admin/UserController.php    - Gestione utenti admin
app/Http/Controllers/Admin/NewsletterController.php - Newsletter
app/Http/Middleware/CheckAdmin.php     - Verifica ruolo=0
app/Http/Middleware/CheckApproved.php  - Verifica abilitato=1
```
