# YouTube Playlist Gallery - Esempi di Utilizzo

Questa guida contiene esempi pratici per utilizzare il plugin in vari scenari.

## 📋 Sommario

1. [Esempi Base](#esempi-base)
2. [Layout Diversi](#layout-diversi)
3. [Personalizzazioni](#personalizzazioni)
4. [Casi d'Uso Comuni](#casi-duso-comuni)
5. [Widget](#widget)
6. [PHP Template](#php-template)

---

## Esempi Base

### Esempio Minimo
Lo shortcode più semplice possibile:

```
[youtube_playlist_gallery playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf"]
```

### Esempio con Più Parametri
Shortcode con configurazione personalizzata:

```
[youtube_playlist_gallery 
    playlist_id="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf" 
    max_results="20" 
    layout="grid" 
    columns="4"]
```

---

## Layout Diversi

### Layout Grid (Griglia)
Perfetto per gallery fotografiche:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="grid" 
    columns="3" 
    max_results="12"]
```

**Quando usarlo:**
- Home page
- Portfolio
- Gallery video

### Layout List (Lista)
Ideale per blog e articoli:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="list" 
    show_description="true" 
    max_results="5"]
```

**Quando usarlo:**
- Post del blog
- Pagine di tutorial
- Documentazione video

### Layout Masonry
Stile Pinterest dinamico:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="masonry" 
    max_results="15"]
```

**Quando usarlo:**
- Creative portfolio
- Gallery artistica
- Collezioni miste

### Layout Carousel
Slider orizzontale:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="carousel" 
    max_results="10"]
```

**Quando usarlo:**
- Above the fold
- Sezioni highlight
- Mobile-first design

---

## Personalizzazioni

### Gallery con Paginazione
Carica video on-demand:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    max_results="9" 
    pagination="true" 
    columns="3"]
```

### Solo Thumbnails (senza titoli)
Per un look minimalista:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    show_title="false" 
    columns="4"]
```

### Con Descrizioni Complete
Per contenuti educativi:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="list" 
    show_description="true" 
    max_results="5"]
```

### Senza Lightbox
Link diretti a YouTube:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    lightbox="false"]
```

### Gallery a 6 Colonne
Per schermi ultra-wide:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    columns="6" 
    max_results="24"]
```

---

## Casi d'Uso Comuni

### 1. Video Tutorial nella Sidebar

**Scenario:** Mostrare gli ultimi 3 tutorial nella sidebar

**Soluzione:** Usa il Widget
- Nome Widget: **YouTube Playlist Gallery**
- Playlist ID: `PLxxx...`
- Numero Video: `3`

### 2. Gallery Video per Corso Online

**Scenario:** Pagina corso con tutti i video in formato lista

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="list" 
    show_description="true" 
    max_results="50" 
    pagination="true"]
```

### 3. Portfolio Creativo

**Scenario:** Mostrare progetti video in stile masonry

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="masonry" 
    show_title="true" 
    max_results="20"]
```

### 4. Highlights sulla Home

**Scenario:** Slider dei video più importanti in alto

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="carousel" 
    max_results="8" 
    show_title="true"]
```

### 5. Recensioni Video Prodotti

**Scenario:** Pagina prodotto con video recensioni

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="grid" 
    columns="2" 
    max_results="4" 
    lightbox="true"]
```

### 6. Blog Post con Video Correlati

**Scenario:** Alla fine di un articolo, video correlati

```
<h2>Video Correlati</h2>
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="grid" 
    columns="3" 
    max_results="3"]
```

### 7. Galleria Mobile-First

**Scenario:** Ottimizzato per mobile

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="list" 
    max_results="10" 
    pagination="true"]
```

---

## Widget

### Configurazione Widget Sidebar

1. **Aspetto → Widget**
2. **YouTube Playlist Gallery** → Sidebar
3. Configurazione:
   - **Titolo:** Video Tutorial
   - **Playlist ID:** PLxxx...
   - **Numero Video:** 5

### Widget Footer

Perfetto per mostrare ultimi video nel footer:

- **Titolo:** Ultimi Video
- **Playlist ID:** PLxxx...
- **Numero Video:** 3

---

## PHP Template

### Usare in un Template Theme

```php
<?php
// In un file template del tuo tema
echo do_shortcode('[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="grid" 
    columns="3"]');
?>
```

### Condizionale per Categoria

```php
<?php
if (is_category('tutorial')) {
    echo do_shortcode('[youtube_playlist_gallery 
        playlist_id="PLxxx..." 
        layout="list" 
        max_results="5"]');
}
?>
```

### Loop Personalizzato

```php
<?php
// Mostra playlist diversa per ogni post
$playlist_id = get_post_meta(get_the_ID(), 'playlist_id', true);

if ($playlist_id) {
    echo do_shortcode('[youtube_playlist_gallery 
        playlist_id="' . esc_attr($playlist_id) . '" 
        layout="grid" 
        columns="3"]');
}
?>
```

### Page Builder (Elementor)

In un widget HTML/Shortcode:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="grid" 
    columns="4" 
    max_results="12"]
```

---

## Combinazioni Responsive

### Desktop: 4 colonne, Tablet: 2, Mobile: 1

Il plugin gestisce automaticamente il responsive, ma puoi influenzarlo:

```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    columns="4"]
```

Questo diventerà automaticamente:
- **Desktop (>1200px):** 4 colonne
- **Tablet (768-1200px):** 2 colonne
- **Mobile (<768px):** 1 colonna

---

## Esempi Avanzati

### Multiple Gallery nella Stessa Pagina

```html
<h2>Tutorial Base</h2>
[youtube_playlist_gallery playlist_id="PLxxx...base" columns="3"]

<h2>Tutorial Avanzati</h2>
[youtube_playlist_gallery playlist_id="PLxxx...advanced" columns="3"]
```

### Gallery in Tab/Accordion

Con plugin tab, ogni tab può avere una playlist diversa:

**Tab 1:**
```
[youtube_playlist_gallery playlist_id="PLxxx...tab1"]
```

**Tab 2:**
```
[youtube_playlist_gallery playlist_id="PLxxx...tab2"]
```

### Sezioni Diverse con Layout Diversi

```html
<section class="hero">
    <h1>Video in Evidenza</h1>
    [youtube_playlist_gallery 
        playlist_id="PLxxx..." 
        layout="carousel" 
        max_results="5"]
</section>

<section class="content">
    <h2>Tutti i Video</h2>
    [youtube_playlist_gallery 
        playlist_id="PLxxx..." 
        layout="grid" 
        columns="4" 
        pagination="true"]
</section>
```

---

## CSS Personalizzato

### Stile Custom per una Gallery Specifica

Aggiungi una classe wrapper al contenitore parent:

```html
<div class="my-custom-gallery">
    [youtube_playlist_gallery playlist_id="PLxxx..."]
</div>
```

Poi nel tuo CSS:

```css
.my-custom-gallery .ypg-item {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.my-custom-gallery .ypg-title {
    color: #your-brand-color;
    font-size: 18px;
}
```

---

## Tips & Tricks

### 💡 Tip 1: Playlist ID Veloce
L'ID playlist è dopo `list=` nell'URL:
```
https://www.youtube.com/playlist?list=PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf
                                        ↑ Questo è l'ID
```

### 💡 Tip 2: Cache per Performance
Abilita la cache nelle impostazioni per ridurre le chiamate API.

### 💡 Tip 3: Paginazione per Grandi Playlist
Se hai >20 video, usa `pagination="true"`.

### 💡 Tip 4: Layout Carousel per Mobile
Il carousel è perfetto per dispositivi touch.

### 💡 Tip 5: Lightbox per UX Migliore
Mantieni gli utenti sul tuo sito con `lightbox="true"`.

---

## Troubleshooting Esempi

### Problema: Video Non Visualizzati

**Soluzione:**
```
1. Verifica Playlist ID
2. Controlla API Key in Impostazioni
3. Svuota Cache
```

### Problema: Layout Rotto

**Soluzione:**
```css
/* Aggiungi al tuo tema */
.ypg-gallery-wrapper {
    clear: both;
}
```

### Problema: Lightbox Non Funziona

**Soluzione:**
Verifica che jQuery sia caricato. Aggiungi a functions.php:

```php
wp_enqueue_script('jquery');
```

---

## Risorse

- **Documentazione Completa:** README.md
- **API YouTube:** [Google Developers](https://developers.google.com/youtube/v3)
- **Supporto:** Contatta dway

---

**Buon utilizzo del plugin! 🎬**

