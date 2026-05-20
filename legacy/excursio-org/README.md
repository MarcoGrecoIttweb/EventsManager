# Deploy sito vecchio excursio.org

## Login + tracciamento ingressi (user_login_events)

Carica su **excursio.org** (cartella `eventi/` sul server):

| File in repo | Destinazione su excursio.org |
|--------------|------------------------------|
| `include/log_user_login_event.php` | `/eventi/include/log_user_login_event.php` |
| `it/valida.php` | `/eventi/it/valida.php` |
| `italia/valida.php` | `/eventi/italia/valida.php` (solo se usi quel percorso di login) |

Al login il sito vecchio inserisce una riga in `user_login_events` con `source = legacy` e aggiorna `utente.ultimo_accesso`.

**Prerequisito:** sul database condiviso deve esistere la tabella `user_login_events` con colonna `source` (migration Laravel `2026_05_15_000001_add_source_to_user_login_events_table`).

## Sviluppo locale (XAMPP)

```powershell
.\deploy-to-local-xampp.ps1
```

Copia i file sopra in `c:\xampp\htdocs\eventi\`.
