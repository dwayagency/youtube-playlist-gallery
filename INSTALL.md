# 📦 Guida all'Installazione - YouTube Playlist Gallery

Guida passo-passo per installare e configurare il plugin.

## 📋 Prerequisiti

Prima di iniziare, assicurati di avere:

- ✅ WordPress 5.0 o superiore
- ✅ PHP 7.2 o superiore
- ✅ Un account Google per ottenere la API Key

---

## 🚀 Installazione

### Metodo 1: Dashboard WordPress (Consigliato)

1. **Accedi al pannello WordPress**
   ```
   https://tuo-sito.com/wp-admin
   ```

2. **Vai su Plugin**
   - Clicca su "Plugin" nel menu laterale
   - Clicca su "Aggiungi Nuovo"

3. **Upload del Plugin**
   - Clicca su "Carica Plugin" in alto
   - Clicca su "Scegli file"
   - Seleziona il file `youtube-playlist-gallery.zip`
   - Clicca su "Installa Ora"

4. **Attivazione**
   - Clicca su "Attiva Plugin"
   - ✅ Il plugin è ora attivo!

### Metodo 2: FTP/SFTP

1. **Estrai il file ZIP**
   - Decomprimi `youtube-playlist-gallery.zip`
   - Otterrai la cartella `youtube-playlist-gallery`

2. **Upload via FTP**
   - Connettiti al tuo server via FTP
   - Vai su `/wp-content/plugins/`
   - Carica la cartella `youtube-playlist-gallery`

3. **Attiva il Plugin**
   - Vai su WordPress Admin → Plugin
   - Trova "YouTube Playlist Gallery"
   - Clicca su "Attiva"

### Metodo 3: SSH/Terminal

```bash
# Vai nella cartella plugins
cd /percorso/verso/wordpress/wp-content/plugins/

# Scarica il plugin (se disponibile online)
wget https://link-al-plugin.zip

# Oppure copia da locale
cp /percorso/locale/youtube-playlist-gallery.zip .

# Estrai
unzip youtube-playlist-gallery.zip

# Imposta i permessi
chmod -R 755 youtube-playlist-gallery

# Attiva il plugin via WP-CLI
wp plugin activate youtube-playlist-gallery
```

---

## 🔑 Configurazione YouTube API Key

### Passo 1: Crea un Progetto Google Cloud

1. Vai su [Google Cloud Console](https://console.developers.google.com/)
2. Clicca su "Select a Project" in alto
3. Clicca su "NEW PROJECT"
4. Inserisci un nome (es. "YouTube Gallery Plugin")
5. Clicca su "CREATE"

### Passo 2: Abilita YouTube Data API v3

1. Nel menu laterale, clicca su "APIs & Services" → "Library"
2. Cerca "YouTube Data API v3"
3. Clicca sul risultato
4. Clicca su "ENABLE"

### Passo 3: Crea le Credenziali

1. Nel menu laterale, clicca su "APIs & Services" → "Credentials"
2. Clicca su "CREATE CREDENTIALS" in alto
3. Seleziona "API Key"
4. Copia la chiave generata (es. `AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`)

### Passo 4: (Opzionale) Restrizioni API Key

Per sicurezza, è consigliato limitare la chiave:

1. Clicca su "Edit API Key"
2. **Application restrictions:**
   - Seleziona "HTTP referrers (web sites)"
   - Aggiungi: `tuo-sito.com/*`
3. **API restrictions:**
   - Seleziona "Restrict key"
   - Seleziona solo "YouTube Data API v3"
4. Clicca su "SAVE"

### Passo 5: Configura il Plugin

1. In WordPress, vai su **Impostazioni → YT Playlist Gallery**
2. Incolla la tua API Key nel campo "YouTube API Key"
3. Clicca su "Salva modifiche"
4. ✅ Configurazione completata!

---

## 🎬 Primo Utilizzo

### Trova l'ID della tua Playlist

1. Vai su YouTube
2. Apri la playlist che vuoi mostrare
3. Copia l'URL (es. `https://www.youtube.com/playlist?list=PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf`)
4. L'ID è la parte dopo `list=`:
   ```
   PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf
   ```

### Crea il tuo Primo Shortcode

#### Metodo 1: Shortcode Generator (Facile)

1. Vai su **Impostazioni → YT Playlist Gallery**
2. Nella sidebar destra, usa lo **Shortcode Generator**
3. Inserisci il Playlist ID
4. Configura le opzioni
5. Clicca su "Genera Shortcode"
6. Copia e incolla nel tuo contenuto

#### Metodo 2: Manuale

1. Crea o modifica una Pagina/Post
2. Aggiungi questo shortcode:
   ```
   [youtube_playlist_gallery playlist_id="IL_TUO_PLAYLIST_ID"]
   ```
3. Pubblica
4. Visualizza la pagina

### Primo Test

Usa questa playlist di esempio per testare:

```
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" max_results="6"]
```

---

## ⚙️ Configurazione Impostazioni

### Impostazioni Consigliate

#### Per Blog/Sito Personale
```
- Layout: Grid
- Colonne: 3
- Lightbox: Abilitato
- Mostra Titolo: Sì
- Mostra Descrizione: No
- Cache: Abilitata (1 ora)
```

#### Per Sito Aziendale
```
- Layout: List
- Lightbox: Abilitato
- Mostra Titolo: Sì
- Mostra Descrizione: Sì
- Cache: Abilitata (2 ore)
```

#### Per Portfolio Creativo
```
- Layout: Masonry
- Lightbox: Abilitato
- Mostra Titolo: Sì
- Cache: Abilitata (3 ore)
```

---

## 🧪 Test dell'Installazione

### Checklist Verifica

- [ ] Plugin attivato senza errori
- [ ] API Key inserita e salvata
- [ ] Shortcode inserito in una pagina test
- [ ] Video visualizzati correttamente
- [ ] Lightbox funzionante
- [ ] Layout responsive su mobile
- [ ] Nessun errore nella console browser

### Test Shortcode

Crea una pagina di test con questi shortcode:

```
<h2>Test Layout Grid</h2>
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" layout="grid" columns="3"]

<h2>Test Layout List</h2>
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" layout="list" max_results="3"]

<h2>Test Lightbox</h2>
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" lightbox="true" max_results="3"]
```

---

## 🐛 Troubleshooting Installazione

### Errore: "Plugin non può essere attivato"

**Causa:** Versione PHP non compatibile

**Soluzione:**
```bash
# Verifica versione PHP
php -v

# Se < 7.2, aggiorna PHP sul server
```

### Errore: "API key non configurata"

**Causa:** API Key non inserita

**Soluzione:**
1. Vai su Impostazioni → YT Playlist Gallery
2. Inserisci la API Key
3. Clicca "Salva modifiche"

### Errore: "Nessun video trovato"

**Possibili cause e soluzioni:**

1. **Playlist ID errato**
   - Verifica l'ID della playlist
   - Deve essere pubblico

2. **API Key non valida**
   - Verifica la chiave su Google Cloud Console
   - Assicurati che YouTube Data API v3 sia abilitata

3. **Quota API esaurita**
   - Controlla la quota su Google Cloud Console
   - Aspetta il reset (mezzanotte Pacific Time)

4. **Playlist privata**
   - Rendi la playlist pubblica su YouTube

### Errore: "Permessi file"

```bash
# Imposta i permessi corretti
cd /wp-content/plugins/youtube-playlist-gallery
chmod -R 755 .
chown -R www-data:www-data .
```

### Lightbox non funziona

**Soluzione 1:** Verifica jQuery

Aggiungi a `functions.php` del tuo tema:

```php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery');
}, 1);
```

**Soluzione 2:** Conflitto con altri plugin

- Disattiva temporaneamente altri plugin
- Testa uno ad uno per trovare il conflitto

---

## 🔄 Aggiornamento Plugin

### Aggiornamento Standard

1. **Backup del sito** (importante!)
2. Vai su Dashboard → Aggiornamenti
3. Seleziona "YouTube Playlist Gallery"
4. Clicca "Aggiorna Plugin"

### Aggiornamento Manuale

1. **Backup completo**
2. Disattiva il plugin
3. Elimina la vecchia cartella
4. Upload della nuova versione
5. Riattiva il plugin
6. Verifica le impostazioni

---

## 📊 Monitoraggio Quota API

### Controllare l'Utilizzo

1. Vai su [Google Cloud Console](https://console.developers.google.com/)
2. Seleziona il progetto
3. Menu → "APIs & Services" → "Dashboard"
4. Clicca su "YouTube Data API v3"
5. Visualizza il grafico delle richieste

### Ottimizzare l'Utilizzo

- ✅ Abilita la **Cache** nelle impostazioni
- ✅ Imposta durata cache di **almeno 1 ora**
- ✅ Non mostrare più di **50 video** per shortcode
- ✅ Usa `max_results` appropriato

### Calcolo Quota

```
1 richiesta = 3 unità
Limite gratuito = 10.000 unità/giorno
Richieste disponibili = 10.000 / 3 ≈ 3.333 richieste/giorno

Con cache 1 ora:
- 1.000 visite/giorno = ~42 richieste API (cache efficace)
```

---

## ✅ Prossimi Passi

Dopo l'installazione:

1. 📖 Leggi il [README.md](README.md) per la documentazione completa
2. 📝 Consulta [EXAMPLES.md](EXAMPLES.md) per esempi pratici
3. 🎨 Personalizza lo stile con CSS custom
4. 🧩 Aggiungi il Widget nella sidebar
5. ⚡ Ottimizza le performance con la cache

---

## 🆘 Supporto

Hai problemi? Contatta dway per assistenza.

**Buon utilizzo del plugin! 🎬**

