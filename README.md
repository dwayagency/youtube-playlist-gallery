# YouTube Playlist Gallery

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

Un plugin WordPress completo e altamente personalizzabile per visualizzare le playlist di YouTube in modo elegante e responsive.

## ✨ Caratteristiche Principali

- 🎨 **Multiple Layout**: Grid, List, Masonry, Carousel
- 🖼️ **Lightbox Integrato**: Visualizza video in overlay senza lasciare la pagina
- ⚡ **Sistema di Cache Avanzato**: Riduce le chiamate API e migliora le performance
- 📱 **Completamente Responsive**: Ottimizzato per tutti i dispositivi
- 🎯 **Shortcode Personalizzabile**: Controllo totale su ogni aspetto
- 🔄 **Paginazione AJAX**: Carica altri video senza ricaricare la pagina
- 🎛️ **Pannello Admin Intuitivo**: Configurazione facile e shortcode generator
- ♿ **Accessibile**: Supporto completo per screen reader e navigazione da tastiera
- 🌙 **Dark Mode**: Supporto automatico per tema scuro
- 🧩 **Widget Sidebar**: Aggiungi playlist nella sidebar

## 📋 Requisiti

- WordPress 5.0 o superiore
- PHP 7.2 o superiore
- YouTube Data API v3 Key

## 🚀 Installazione

### Metodo 1: Upload Manuale

1. Scarica il plugin
2. Vai su **WordPress Admin → Plugin → Aggiungi Nuovo → Carica Plugin**
3. Seleziona il file ZIP e clicca su **Installa**
4. Attiva il plugin
5. Vai su **Impostazioni → YT Playlist Gallery**
6. Inserisci la tua YouTube API Key

### Metodo 2: FTP

1. Estrai il file ZIP
2. Carica la cartella `youtube-playlist-gallery` in `/wp-content/plugins/`
3. Attiva il plugin dal menu Plugin di WordPress
4. Configura la API Key nelle impostazioni

## 🔑 Ottenere la YouTube API Key

1. Vai su [Google Cloud Console](https://console.developers.google.com/)
2. Crea un nuovo progetto (o selezionane uno esistente)
3. Abilita la **YouTube Data API v3**
4. Vai su **Credenziali → Crea Credenziali → Chiave API**
5. Copia la chiave e incollala nelle impostazioni del plugin

## 📖 Utilizzo

### Shortcode di Base

```
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf"]
```

### Shortcode con Tutti i Parametri

```
[youtube_playlist_gallery 
    playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" 
    max_results="12" 
    layout="grid" 
    columns="4" 
    pagination="true"
    show_title="true"
    show_description="false"
    lightbox="true"]
```

### Parametri Disponibili

| Parametro | Tipo | Default | Descrizione |
|-----------|------|---------|-------------|
| `playlist_id` | string | - | **Obbligatorio**. ID della playlist YouTube |
| `max_results` | int | 10 | Numero massimo di video (1-50) |
| `layout` | string | grid | Layout: `grid`, `list`, `masonry`, `carousel` |
| `columns` | int | 3 | Numero di colonne per layout grid (1-6) |
| `pagination` | bool | false | Abilita paginazione con "Carica Altri" |
| `show_title` | bool | true | Mostra titolo video |
| `show_description` | bool | false | Mostra descrizione video |
| `lightbox` | bool | true | Apri video in lightbox |

## 🎨 Layout Disponibili

### Grid (Griglia)
Layout a griglia responsive con colonne personalizzabili.

```
[youtube_playlist_gallery playlist_id="PLxxx..." layout="grid" columns="3"]
```

### List (Lista)
Layout a lista con thumbnail grande e contenuto affiancato.

```
[youtube_playlist_gallery playlist_id="PLxxx..." layout="list"]
```

### Masonry
Layout stile Pinterest con distribuzione dinamica.

```
[youtube_playlist_gallery playlist_id="PLxxx..." layout="masonry"]
```

### Carousel
Layout a carosello scorrevole orizzontalmente.

```
[youtube_playlist_gallery playlist_id="PLxxx..." layout="carousel"]
```

## 🧩 Widget

Il plugin include un widget per visualizzare playlist nella sidebar:

1. Vai su **Aspetto → Widget**
2. Cerca **YouTube Playlist Gallery**
3. Trascinalo in una sidebar
4. Configura Playlist ID e numero di video
5. Salva

## 🛠️ Shortcode Generator

Il plugin include un generatore di shortcode nell'area admin:

1. Vai su **Impostazioni → YT Playlist Gallery**
2. Usa lo **Shortcode Generator** nella sidebar destra
3. Configura i parametri desiderati
4. Clicca su **Genera Shortcode**
5. Copia e incolla nel tuo contenuto

## ⚙️ Impostazioni

### API YouTube
- **YouTube API Key**: La tua chiave API per accedere ai dati di YouTube

### Visualizzazione
- **Layout Predefinito**: Grid, List, Masonry o Carousel
- **Colonne Predefinite**: Numero di colonne per il layout grid (1-6)
- **Abilita Lightbox**: Apri video in overlay invece di nuova finestra
- **Mostra Titolo Video**: Visualizza il titolo sotto la thumbnail
- **Mostra Descrizione**: Visualizza la descrizione del video

### Cache
- **Abilita Cache**: Attiva il caching dei risultati API
- **Durata Cache**: Imposta la durata in ore (1-168)
- **Svuota Cache**: Bottone per svuotare manualmente la cache

## 🎯 Trovare l'ID della Playlist

### Metodo 1: URL della Playlist
Se l'URL è: `https://www.youtube.com/playlist?list=PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf`

L'ID è: `PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf`

### Metodo 2: YouTube Studio
1. Vai su YouTube Studio
2. Seleziona **Playlist** nel menu laterale
3. Clicca sulla playlist desiderata
4. L'ID è nell'URL

## 🎨 Personalizzazione CSS

Puoi personalizzare lo stile aggiungendo CSS personalizzato nel tuo tema:

```css
/* Cambia colore del bottone */
.ypg-load-more {
    background: #your-color !important;
}

/* Personalizza titoli */
.ypg-title {
    font-family: 'Your Font', sans-serif;
    color: #your-color;
}

/* Modifica dimensioni thumbnail */
.ypg-thumbnail {
    border-radius: 10px;
    overflow: hidden;
}
```

### Classi CSS Principali

- `.ypg-gallery-wrapper` - Contenitore principale
- `.ypg-gallery` - Griglia dei video
- `.ypg-item` - Singolo video
- `.ypg-thumbnail` - Area thumbnail
- `.ypg-title` - Titolo video
- `.ypg-description` - Descrizione video
- `.ypg-load-more` - Bottone carica altri
- `.ypg-lightbox` - Overlay lightbox

## 🔧 Hook per Sviluppatori

Il plugin è estensibile tramite filtri e azioni WordPress standard.

### Filtri Disponibili

```php
// Modifica i parametri della richiesta API
add_filter('ypg_api_params', function($params, $playlist_id) {
    return $params;
}, 10, 2);

// Modifica il markup HTML di un video
add_filter('ypg_video_html', function($html, $video_data) {
    return $html;
}, 10, 2);
```

## 🐛 Risoluzione Problemi

### I video non vengono visualizzati

1. **Verifica l'API Key**: Assicurati che sia valida e attiva
2. **Controlla le Quote**: Verifica di non aver esaurito la quota API giornaliera
3. **Playlist ID Corretto**: Verifica che l'ID della playlist sia corretto
4. **Playlist Pubblica**: Assicurati che la playlist sia pubblica

### Errore "API key non configurata"

Vai su **Impostazioni → YT Playlist Gallery** e inserisci la tua API key.

### Cache non si aggiorna

Usa il bottone **Svuota Cache** nelle impostazioni del plugin.

### Lightbox non funziona

1. Verifica che jQuery sia caricato
2. Controlla la console del browser per errori JavaScript
3. Disattiva altri plugin che potrebbero interferire

## 📊 Limitazioni API YouTube

- **Quota giornaliera**: 10.000 unità/giorno (account gratuito)
- **Costo per richiesta**: ~3 unità per richiesta playlist
- **Consiglio**: Abilita la cache per ridurre le chiamate API

## 🔒 Privacy e Sicurezza

- Il plugin non raccoglie dati personali degli utenti
- Le richieste API sono server-side per proteggere la chiave
- Tutte le input vengono sanitizzate
- Supporto per Content Security Policy (CSP)

## 📝 Changelog

### Version 2.0.0
- ✨ Sistema di cache avanzato con transient API
- 🎨 Quattro layout: Grid, List, Masonry, Carousel
- 🖼️ Lightbox integrato per visualizzare video
- 🔄 Paginazione AJAX con "Carica Altri"
- 🎛️ Pannello admin completamente ridisegnato
- 🎯 Shortcode generator interattivo
- 🧩 Widget per sidebar
- 📱 Design responsive migliorato
- ♿ Migliore accessibilità
- 🌙 Supporto dark mode
- 🎨 Stili CSS ottimizzati
- ⚡ Performance migliorate

### Version 1.0.0
- 🎉 Rilascio iniziale

## 👨‍💻 Autore

**dway**

## 📄 Licenza

Questo plugin è rilasciato sotto licenza GPL v2 o successiva.

## 🆘 Supporto

Per supporto, bug report o richieste di funzionalità, contatta dway.

## 🙏 Ringraziamenti

Grazie a tutti coloro che hanno contribuito al testing e al feedback del plugin.

---

**Se ti piace questo plugin, lascia una recensione! ⭐⭐⭐⭐⭐**

