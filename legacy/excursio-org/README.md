# Deploy login legacy su excursio.org

File da caricare sul server del **sito vecchio** (stesso database del nuovo Laravel).

## Prerequisito (sito nuovo Laravel)

```bash
php artisan migrate
```

Serve la tabella `user_login_events` con colonna `source`.

## File da copiare (FTP / cPanel)

| Da (questo repo) | Su excursio.org |
|------------------|-----------------|
| `include/log_user_login_event.php` | `/eventi/include/log_user_login_event.php` |
| `it/valida.php` | `/eventi/it/valida.php` |
| `italia/valida.php` | `/eventi/italia/valida.php` (se la cartella è ancora in uso) |

## Verifica

1. Login su https://www.excursio.org/eventi/it/
2. In admin Laravel: **Utenti → Logins** → colonna «Sito vecchio» con data/ora aggiornata.

## Sviluppo locale (XAMPP)

Copia gli stessi file in `c:\xampp\htdocs\eventi\` (già allineati se lavori dalla stessa macchina).
