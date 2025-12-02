# 🎬 Nuovo Layout FEATURED - Versione 2.1.0

## 📺 Cos'è il Layout Featured?

È il layout **perfetto per visualizzare playlist video** come nell'immagine che hai mostrato:

```
┌──────────────────────────────────────────┐
│                                          │
│        VIDEO PRINCIPALE (GRANDE)         │
│                                          │
└──────────────────────────────────────────┘

┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐
│ Video 1 │ │ Video 2 │ │ Video 3 │ │ Video 4 │
└─────────┘ └─────────┘ └─────────┘ └─────────┘
```

### ✨ Caratteristiche:

1. **Video Grande in Alto**: Il primo video (o quello selezionato) viene mostrato grande
2. **Miniature Sotto**: Tutti gli altri video sono mostrati come miniature in griglia
3. **Click per Cambiare**: Cliccando su una miniatura, quella diventa il video grande
4. **Animazione Fluida**: Transizione smooth tra i video
5. **Auto-scroll**: Quando cambi video, la pagina scrolla automaticamente al video grande

---

## 🎯 Come Funziona

### Interazione Utente:

1. **All'inizio**: Il primo video della playlist è visualizzato grande
2. **Click su miniatura**: 
   - La miniatura cliccata diventa il video grande
   - Il video grande precedente va nelle miniature
   - Animazione fade per il cambio
   - La pagina scrolla al video grande
3. **Bordo Rosso**: La miniatura attiva ha un bordo rosso
4. **Hover**: Le miniature hanno effetto hover con ingrandimento

---

## ⚙️ Configurazione

### Nelle Impostazioni Playlist:

Quando crei o modifichi una playlist, seleziona:

**Layout**: `Featured (Video Grande + Miniature)`

**Colonne Miniature**: 
- 2 colonne = miniature più grandi
- 3 colonne = equilibrato (consigliato)
- 4 colonne = più miniature visibili
- 5-6 colonne = massima densità

### Impostazioni Consigliate:

```
Nome: "Tutorial Homepage"
Layout: Featured (Video Grande + Miniature)
Colonne: 4
Numero Video: 12
☑ Mostra titolo
☐ Mostra descrizione
☑ Lightbox
☐ Paginazione
```

---

## 💡 Esempi Pratici

### 1. Homepage con Video in Evidenza

```
Layout: Featured
Colonne: 4
Video: 8
Mostra solo titoli nelle miniature
```

**Risultato**: 
- 1 video grande + 7 miniature sotto
- Perfetto per mostrare video highlight

### 2. Pagina Tutorial

```
Layout: Featured
Colonne: 3
Video: 15
Mostra titolo e descrizione nel video grande
```

**Risultato**:
- Video principale con descrizione completa
- 14 tutorial come miniature

### 3. Portfolio Video

```
Layout: Featured
Colonne: 4
Video: 20
Lightbox attivo
Paginazione: ON
```

**Risultato**:
- Esplora video facilmente
- Carica altri video quando necessario

---

## 🎨 Personalizzazione CSS

### Modificare Dimensioni Video Grande

```css
.ypg-layout-featured .ypg-featured-main {
    margin-bottom: 40px; /* Spazio sotto video grande */
}

.ypg-layout-featured .ypg-featured-video {
    max-width: 900px; /* Limita larghezza */
    margin: 0 auto; /* Centra */
}
```

### Modificare Aspetto Miniature

```css
.ypg-layout-featured .ypg-thumb-item {
    border-radius: 12px; /* Angoli più arrotondati */
}

.ypg-layout-featured .ypg-thumb-item.active {
    border-width: 4px; /* Bordo più spesso */
}
```

### Modificare Titolo Video Grande

```css
.ypg-layout-featured .ypg-featured-title {
    font-size: 28px; /* Più grande */
    color: #ff0000; /* Colore brand */
}
```

---

## 📱 Responsive

Il layout si adatta automaticamente:

**Desktop (>1200px)**:
- Video grande: larghezza piena
- Miniature: 4-6 colonne

**Tablet (768-1200px)**:
- Video grande: larghezza piena
- Miniature: 3 colonne

**Mobile (< 768px)**:
- Video grande: larghezza piena
- Miniature: 2 colonne

**Mobile Piccolo (< 480px)**:
- Video grande: larghezza piena
- Miniature: 1 colonna

---

## 🔄 Workflow Utente

### Scenario: Visitatore sulla Homepage

1. **Caricamento Pagina**
   - Vede immediatamente il primo video grande
   - Sotto vede 7 miniature

2. **Interesse per Video 3**
   - Click sulla 3° miniatura
   - Animazione smooth
   - Video 3 diventa grande
   - Miniatura 3 ha bordo rosso (attiva)

3. **Vuole Vedere Video in Fullscreen**
   - Click sul video grande
   - Si apre il lightbox
   - Video riprodotto in overlay

4. **Esplora Altri Video**
   - Chiude lightbox
   - Click su altre miniature
   - Ogni video diventa grande al click

---

## 🎯 Vantaggi del Layout Featured

### Per il Visitatore:
✅ **Immediatezza**: Vede subito il video principale
✅ **Facilità**: Click semplice per cambiare video
✅ **Preview**: Vede tutte le miniature disponibili
✅ **Navigazione Veloce**: Non deve scrollare molto

### Per il Sito:
✅ **Engagement**: Incoraggia l'esplorazione
✅ **User-Friendly**: Interfaccia intuitiva
✅ **Moderno**: Look professionale
✅ **Versatile**: Funziona per qualsiasi tipo di video

---

## 🆚 Featured vs Altri Layout

### Featured vs Grid
- **Featured**: Focus su un video + preview altri
- **Grid**: Tutti i video con stessa importanza

### Featured vs List
- **Featured**: Visual-first con video grande
- **List**: Informazioni-first con descrizioni

### Featured vs Carousel
- **Featured**: Video statico con selezione manuale
- **Carousel**: Scroll orizzontale continuo

---

## 🔧 Shortcode

### Nuovo Shortcode con Playlist Salvata:
```
[ypg_playlist id="1"]
```

### Vecchio Shortcode (ancora funzionante):
```
[youtube_playlist_gallery 
    playlist_id="PLxxx..." 
    layout="featured" 
    columns="4"]
```

---

## 🎨 Varianti di Stile

### Variante 1: Minimalista
```
Mostra solo titolo
Nessuna descrizione
Bordi sottili
Colori neutri
```

### Variante 2: Ricca
```
Mostra titolo e descrizione
Ombre marcate
Colori vivaci
Animazioni evidenti
```

### Variante 3: Compatta
```
6 colonne miniature
Video piccoli
Massima densità
```

---

## 📊 Best Practices

### 1. Numero Video Ottimale
- **Homepage**: 8-12 video
- **Pagina Categoria**: 15-20 video
- **Archivio Completo**: 20+ video con paginazione

### 2. Colonne Miniature
- **2 colonne**: Per video molto importanti
- **3-4 colonne**: Standard, equilibrato
- **5-6 colonne**: Per cataloghi estesi

### 3. Titoli e Descrizioni
- **Video Grande**: Mostra sempre titolo
- **Video Grande**: Mostra descrizione se rilevante
- **Miniature**: Solo titolo (più pulito)

### 4. Lightbox
- **ON**: Per siti content-focused
- **OFF**: Per siti che preferiscono YouTube

---

## 🚀 Migrare a Featured

### Se Hai Già Playlist con Grid:

1. **Vai su YT Playlists → Tutte le Playlist**
2. **Click su "Modifica"** della playlist
3. **Cambia Layout** da "Grid" a "Featured"
4. **Salva**
5. ✅ La pagina si aggiorna automaticamente!

### Nessun Cambio Shortcode Necessario!

Lo shortcode rimane uguale:
```
[ypg_playlist id="1"]
```

---

## 🎉 Esempio Completo

### Crea Playlist "Video in Evidenza"

**Step 1: Crea Playlist**
```
Nome: Video in Evidenza
Playlist ID: PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf
Layout: Featured (Video Grande + Miniature)
Colonne: 4
Max Video: 12
☑ Mostra titolo
☑ Mostra descrizione (solo video grande)
☑ Lightbox
☐ Paginazione
```

**Step 2: Inserisci in Homepage**
```
[ypg_playlist id="1"]
```

**Step 3: Risultato**
- Visitatore vede subito video principale
- 11 miniature sotto in griglia 4 colonne
- Click su miniatura → video si aggiorna
- Click su video grande → lightbox
- Esperienza utente premium! 🌟

---

## 💬 FAQ

### D: Le miniature vengono riordinate quando cambio video?
**R**: No, le miniature mantengono la posizione. Solo il video grande cambia.

### D: Posso avere più video grandi contemporaneamente?
**R**: No, il layout featured è progettato per avere UN solo video in evidenza.

### D: Il video grande parte automaticamente?
**R**: No, devi cliccare per riprodurlo (lightbox o YouTube).

### D: Posso nascondere le miniature?
**R**: No, il layout featured richiede le miniature. Usa "List" o "Grid" senza miniature.

### D: Funziona con paginazione?
**R**: Sì! I nuovi video caricati vengono aggiunti come miniature.

---

## 🎨 Screenshot Layout

```
┌────────────────────────────────────────────────────────┐
│                                                        │
│                 [▶] VIDEO PRINCIPALE                   │
│              (Click per riprodurre)                    │
│                                                        │
│ "Undicesima puntata - Mercoledì 19 novembre"          │
│ Ospiti in studio Marco Bellavia, Mario Adinolfi...    │
└────────────────────────────────────────────────────────┘

MINIATURE (4 colonne):
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│    [▶]    │ │    [▶]    │ │    [▶]    │ │    [▶]    │
│  Puntata  │ │  Puntata  │ │  Puntata  │ │  Puntata  │
│     1     │ │     2     │ │     3     │ │     4     │
└───────────┘ └───────────┘ └───────────┘ └───────────┘

┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│    [▶]    │ │    [▶]    │ │    [▶]    │ │    [▶]    │
│  Puntata  │ │  Puntata  │ │  Puntata  │ │  Puntata  │
│     5     │ │     6     │ │     7     │ │     8     │
└───────────┘ └───────────┘ └───────────┘ └───────────┘
```

---

## ✅ Checklist Implementazione

- [x] Layout Featured aggiunto
- [x] CSS responsive completo
- [x] JavaScript per switch video
- [x] Animazioni fluide
- [x] Bordo rosso per video attivo
- [x] Auto-scroll al cambio
- [x] Compatibilità lightbox
- [x] Supporto paginazione
- [x] Mobile-friendly
- [x] Accessibilità

---

## 🎉 Conclusione

Il **Layout Featured** è perfetto per:
- ✅ Homepage
- ✅ Landing page
- ✅ Pagine tutorial
- ✅ Portfolio video
- ✅ Archivi puntate
- ✅ Gallerie video professionali

**È ora il layout predefinito quando crei nuove playlist!**

---

**Versione**: 2.1.0  
**Data**: 2 Dicembre 2025  
**Autore**: DWAY AGENCY  
**Layout**: Featured (Video Grande + Miniature)

