# Deploy sito vecchio excursio.org

## valida.php (login)

Carica su excursio.org:

| File | Destinazione |
|------|----------------|
| `it/valida.php` | `/eventi/it/valida.php` |
| `italia/valida.php` | `/eventi/italia/valida.php` (se usata) |

Al login aggiorna solo `utente.ultimo_accesso` (nessun tracciamento `source` / `user_login_events`).

Il file `include/log_user_login_event.php` **non serve più**: puoi rimuoverlo dal server se era stato caricato.

## Sviluppo locale

```powershell
.\deploy-to-local-xampp.ps1
```

Copia solo i `valida.php` in `c:\xampp\htdocs\eventi\`.
