/**
 * Poznámky modul - JavaScript funkcionalita s databází
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Poznámky modul - JavaScript načten');
    
    // Inicializace modulu
    initNotesModule();
});

/**
 * Inicializace modulu poznámek
 */
function initNotesModule() {
    console.log('🟡 Inicializace modulu poznámek...');
    
    // Najdeme tlačítka
    const addNoteBtn = document.getElementById('addNoteBtn');
    const searchNotesBtn = document.getElementById('searchNotesBtn');
    
    console.log('🔍 Hledám tlačítka:', {
        addNoteBtn: !!addNoteBtn,
        searchNotesBtn: !!searchNotesBtn
    });
    
    // Event listenery pro tlačítka
    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', function() {
            console.log('🖱️ Kliknuto na "Přidat poznámku"');
            showAddNoteForm();
        });
    }
    
    if (searchNotesBtn) {
        searchNotesBtn.addEventListener('click', function() {
            console.log('🖱️ Kliknuto na "Hledat v poznámkách"');
            showSearchForm();
        });
    }
    
    // Načteme poznámky z databáze při startu
    loadNotesFromDatabase();
    
    console.log('✅ Poznámky modul je připraven k použití');
}

/**
 * Načte poznámky z databáze
 */
function loadNotesFromDatabase() {
    console.log('📥 Načítám poznámky z databáze...');
    
    // Zobrazíme loading indikátor
    showLoadingState();
    
    // AJAX volání pro načtení dat
    makeAjaxCall('getAllData')
        .then(data => {
            console.log('📊 Data z databáze načtena:', data);
            
            // Aktualizujeme UI s daty z databáze
            displayNotes(data.notes || []);
            updateStatisticsFromServer(data.statistics || {});
            
            console.log('✅ Poznámky úspěšně načteny z databáze');
        })
        .catch(error => {
            console.error('❌ Chyba při načítání poznámek:', error);
            showError('Nepodařilo se načíst poznámky z databáze: ' + error.message);
        })
        .finally(() => {
            hideLoadingState();
        });
}

/**
 * Zobrazí loading stav
 */
function showLoadingState() {
    const mainCardBody = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    if (mainCardBody) {
        mainCardBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Načítám...</span>
                </div>
                <p class="mt-2 text-muted">Načítám poznámky...</p>
            </div>
        `;
    }
}

/**
 * Skryje loading stav
 */
function hideLoadingState() {
    // Loading se skryje automaticky při zobrazení poznámek
}

/**
 * Zobrazí seznam poznámek
 */
function displayNotes(notes) {
    console.log('📋 Zobrazuji poznámky:', notes.length);
    
    const mainCardBody = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    if (!mainCardBody) {
        console.error('❌ Nepodařilo se najít kontejner pro poznámky');
        return;
    }
    
    // Pokud nejsou žádné poznámky, zobrazíme empty state
    if (notes.length === 0) {
        showEmptyState();
        return;
    }
    
    // Vytvoříme HTML pro všechny poznámky
    let notesHtml = '<div id="notesContainer">';
    
    notes.forEach(note => {
        notesHtml += createNoteHtml(note);
    });
    
    notesHtml += '</div>';
    
    // Zobrazíme poznámky
    mainCardBody.innerHTML = notesHtml;
    
    console.log('✅ Poznámky zobrazeny v UI');
}

/**
 * Vytvoří HTML pro jednu poznámku
 */
function createNoteHtml(note) {
    const priorityClass = {
        'low': 'text-secondary',
        'normal': 'text-primary', 
        'high': 'text-danger'
    };
    
    const priorityIcon = {
        'low': 'bi-arrow-down',
        'normal': 'bi-dash',
        'high': 'bi-arrow-up'
    };
    
    return `
        <div class="note-item" data-note-id="${note.id}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="note-title">${escapeHtml(note.title)}</div>
                <div class="note-priority ${priorityClass[note.priority] || 'text-primary'}">
                    <i class="bi ${priorityIcon[note.priority] || 'bi-dash'}"></i>
                    ${note.priority}
                </div>
            </div>
            
            ${note.category ? `<div class="note-category mb-2">
                <span class="badge bg-secondary">${escapeHtml(note.category)}</span>
            </div>` : ''}
            
            <div class="note-content">${escapeHtml(note.content)}</div>
            
            ${note.tags && note.tags.length > 0 ? `<div class="note-tags mb-2">
                ${note.tags.map(tag => `<span class="badge bg-light text-dark">#${escapeHtml(tag.trim())}</span>`).join(' ')}
            </div>` : ''}
            
            <div class="note-meta">
                <div class="note-date">
                    Vytvořeno: ${note.formatted_date}
                    ${note.formatted_updated ? `<br>Upraveno: ${note.formatted_updated}` : ''}
                </div>
                <div class="note-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="editNote(${note.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Zobrazí formulář pro přidání nové poznámky (s kategoriemi a prioritami)
 */
function showAddNoteForm() {
    console.log('📝 Zobrazuji formulář pro novou poznámku');
    
    // Najdeme místo, kam vložíme formulář
    const mainCard = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    
    if (!mainCard) {
        console.error('❌ Nepodařilo se najít místo pro formulář');
        return;
    }
    
    // HTML pro formulář s kategoriemi a prioritami
    const formHtml = `
        <div class="note-form show" id="addNoteForm">
            <h6><i class="bi bi-plus-circle me-2"></i>Nová poznámka</h6>
            <form>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="noteTitle" class="form-label">Název poznámky</label>
                        <input type="text" class="form-control" id="noteTitle" placeholder="Zadejte název poznámky">
                    </div>
                    <div class="col-md-4">
                        <label for="notePriority" class="form-label">Priorita</label>
                        <select class="form-select" id="notePriority">
                            <option value="low">Nízká</option>
                            <option value="normal" selected>Normální</option>
                            <option value="high">Vysoká</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="noteCategory" class="form-label">Kategorie</label>
                        <input type="text" class="form-control" id="noteCategory" placeholder="Např: Práce, Osobní...">
                    </div>
                    <div class="col-md-6">
                        <label for="noteTags" class="form-label">Štítky</label>
                        <input type="text" class="form-control" id="noteTags" placeholder="Štítky oddělené čárkami">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="noteContent" class="form-label">Obsah</label>
                    <textarea class="form-control" id="noteContent" rows="4" placeholder="Napište svou poznámku..."></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="saveNoteBtn">
                        <i class="bi bi-check"></i> Uložit poznámku
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="cancelNoteBtn">
                        <i class="bi bi-x"></i> Zrušit
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Vložíme formulář na začátek
    mainCard.insertAdjacentHTML('afterbegin', formHtml);
    
    // Přidáme event listenery pro tlačítka formuláře
    setupFormEventListeners();
    
    // Fokus na první pole
    document.getElementById('noteTitle').focus();
}

/**
 * Nastavení event listenerů pro formulář
 */
function setupFormEventListeners() {
    const saveBtn = document.getElementById('saveNoteBtn');
    const cancelBtn = document.getElementById('cancelNoteBtn');
    
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            console.log('💾 Ukládání poznámky...');
            saveNoteToDatabase();
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            console.log('❌ Zrušení formuláře');
            hideAddNoteForm();
        });
    }
}

/**
 * Uloží poznámku do databáze
 */
function saveNoteToDatabase() {
    const titleInput = document.getElementById('noteTitle');
    const contentInput = document.getElementById('noteContent');
    const categoryInput = document.getElementById('noteCategory');
    const priorityInput = document.getElementById('notePriority');
    const tagsInput = document.getElementById('noteTags');
    
    if (!titleInput || !contentInput) {
        console.error('❌ Nepodařilo se najít pole formuláře');
        return;
    }
    
    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    const category = categoryInput.value.trim();
    const priority = priorityInput.value;
    const tags = tagsInput.value.trim();
    
    if (!title) {
        alert('Zadejte prosím název poznámky');
        titleInput.focus();
        return;
    }
    
    if (!content) {
        alert('Zadejte prosím obsah poznámky');
        contentInput.focus();
        return;
    }
    
    console.log('📝 Ukládám poznámku do databáze:', { title, content, category, priority, tags });
    
    // Disable tlačítko během ukládání
    const saveBtn = document.getElementById('saveNoteBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Ukládám...';
    }
    
    // AJAX volání pro uložení
    const noteData = {
        title: title,
        content: content,
        category: category || null,
        priority: priority,
        tags: tags || null
    };
    
    makeAjaxCall('addNote', noteData)
        .then(note => {
            console.log('✅ Poznámka uložena do databáze:', note);
            
            // Skryjeme formulář
            hideAddNoteForm();
            
            // Znovu načteme všechny poznámky
            loadNotesFromDatabase();
            
        })
        .catch(error => {
            console.error('❌ Chyba při ukládání poznámky:', error);
            showError('Nepodařilo se uložit poznámku: ' + error.message);
            
            // Obnovíme tlačítko
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check"></i> Uložit poznámku';
            }
        });
}

/**
 * Obecné AJAX volání
 */
function makeAjaxCall(action, parameters = {}) {
    console.log(`🔗 AJAX volání: ${action}`, parameters);
    
    // Vytvoříme URL pro AJAX
    const currentLocation = window.location;
    const baseUrl = currentLocation.protocol + '//' + currentLocation.host + currentLocation.pathname;
    const ajaxUrl = baseUrl + '?do=moduleData&moduleId=notes&action=' + encodeURIComponent(action);
    
    // Přidáme parametry jako POST data
    const formData = new FormData();
    for (const [key, value] of Object.entries(parameters)) {
        if (value !== null && value !== undefined) {
            formData.append(key, value);
        }
    }
    
    return fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        body: formData
    })
    .then(response => {
        console.log('📥 AJAX odpověď:', response.status, response.statusText);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('❌ Server error response:', text.substring(0, 500));
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('📊 AJAX data parsed:', data);
        
        if (data.success === false) {
            throw new Error(data.error || 'Neznámá chyba serveru');
        }
        
        return data.data || data;
    });
}

/**
 * Smaže poznámku z databáze
 */
function deleteNote(noteId) {
    console.log('🗑️ Mazání poznámky ID:', noteId);
    
    if (confirm('Opravdu chcete smazat tuto poznámku?')) {
        makeAjaxCall('deleteNote', { id: noteId })
            .then(result => {
                console.log('✅ Poznámka smazána z databáze:', result);
                
                // Znovu načteme poznámky
                loadNotesFromDatabase();
            })
            .catch(error => {
                console.error('❌ Chyba při mazání poznámky:', error);
                showError('Nepodařilo se smazat poznámku: ' + error.message);
            });
    }
}

/**
 * Upravit poznámku
 */
function editNote(noteId) {
    console.log('✏️ Načítám poznámku pro editaci ID:', noteId);
    
    // Najdeme poznámku v DOM
    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
    if (!noteElement) {
        console.error('❌ Nepodařilo se najít poznámku v DOM');
        return;
    }
    
    // Získáme data poznámky z DOM (z HTML)
    const currentTitle = noteElement.querySelector('.note-title').textContent.trim();
    const currentContent = noteElement.querySelector('.note-content').textContent.trim();
    
    // Získáme kategorii a prioritu
    const categoryElement = noteElement.querySelector('.note-category .badge');
    const currentCategory = categoryElement ? categoryElement.textContent.trim() : '';
    
    const priorityElement = noteElement.querySelector('.note-priority');
    const currentPriority = priorityElement ? priorityElement.textContent.trim() : 'normal';
    
    // Získáme štítky
    const tagsElements = noteElement.querySelectorAll('.note-tags .badge');
    const currentTags = Array.from(tagsElements).map(tag => tag.textContent.replace('#', '').trim()).join(', ');
    
    console.log('📋 Data poznámky pro editaci:', {
        title: currentTitle,
        content: currentContent,
        category: currentCategory,
        priority: currentPriority,
        tags: currentTags
    });
    
    // Zobrazíme editační formulář
    showEditNoteForm(noteId, {
        title: currentTitle,
        content: currentContent,
        category: currentCategory,
        priority: currentPriority,
        tags: currentTags
    });
}

/**
 * Zobrazí formulář pro editaci poznámky
 */
function showEditNoteForm(noteId, noteData) {
    console.log('📝 Zobrazuji formulář pro editaci poznámky ID:', noteId);
    
    // Najdeme místo, kam vložíme formulář
    const mainCard = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    
    if (!mainCard) {
        console.error('❌ Nepodařilo se najít místo pro formulář');
        return;
    }
    
    // Odstranit případný existující formulář
    const existingForm = document.getElementById('editNoteForm');
    if (existingForm) {
        existingForm.remove();
    }
    
    // HTML pro editační formulář (podobný jako přidávání, ale s předvyplněnými daty)
    const formHtml = `
        <div class="note-form show" id="editNoteForm">
            <h6><i class="bi bi-pencil me-2"></i>Upravit poznámku</h6>
            <form>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="editNoteTitle" class="form-label">Název poznámky</label>
                        <input type="text" class="form-control" id="editNoteTitle" value="${escapeHtml(noteData.title)}" placeholder="Zadejte název poznámky">
                    </div>
                    <div class="col-md-4">
                        <label for="editNotePriority" class="form-label">Priorita</label>
                        <select class="form-select" id="editNotePriority">
                            <option value="low" ${noteData.priority === 'low' ? 'selected' : ''}>Nízká</option>
                            <option value="normal" ${noteData.priority === 'normal' ? 'selected' : ''}>Normální</option>
                            <option value="high" ${noteData.priority === 'high' ? 'selected' : ''}>Vysoká</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="editNoteCategory" class="form-label">Kategorie</label>
                        <input type="text" class="form-control" id="editNoteCategory" value="${escapeHtml(noteData.category)}" placeholder="Např: Práce, Osobní...">
                    </div>
                    <div class="col-md-6">
                        <label for="editNoteTags" class="form-label">Štítky</label>
                        <input type="text" class="form-control" id="editNoteTags" value="${escapeHtml(noteData.tags)}" placeholder="Štítky oddělené čárkami">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="editNoteContent" class="form-label">Obsah</label>
                    <textarea class="form-control" id="editNoteContent" rows="4" placeholder="Napište svou poznámku...">${escapeHtml(noteData.content)}</textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="updateNoteBtn" data-note-id="${noteId}">
                        <i class="bi bi-check"></i> Uložit změny
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
                        <i class="bi bi-x"></i> Zrušit
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Vložíme formulář na začátek
    mainCard.insertAdjacentHTML('afterbegin', formHtml);
    
    // Přidáme event listenery pro tlačítka formuláře
    setupEditFormEventListeners(noteId);
    
    // Fokus na první pole
    document.getElementById('editNoteTitle').focus();
    
    // Scroll nahoru k formuláři
    document.getElementById('editNoteForm').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Nastavení event listenerů pro editační formulář
 */
function setupEditFormEventListeners(noteId) {
    const updateBtn = document.getElementById('updateNoteBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            console.log('💾 Aktualizování poznámky...');
            updateNoteInDatabase(noteId);
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            console.log('❌ Zrušení editace');
            hideEditNoteForm();
        });
    }
}

/**
 * Aktualizuje poznámku v databázi
 */
function updateNoteInDatabase(noteId) {
    const titleInput = document.getElementById('editNoteTitle');
    const contentInput = document.getElementById('editNoteContent');
    const categoryInput = document.getElementById('editNoteCategory');
    const priorityInput = document.getElementById('editNotePriority');
    const tagsInput = document.getElementById('editNoteTags');
    
    if (!titleInput || !contentInput) {
        console.error('❌ Nepodařilo se najít pole formuláře');
        return;
    }
    
    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    const category = categoryInput.value.trim();
    const priority = priorityInput.value;
    const tags = tagsInput.value.trim();
    
    if (!title) {
        alert('Zadejte prosím název poznámky');
        titleInput.focus();
        return;
    }
    
    if (!content) {
        alert('Zadejte prosím obsah poznámky');
        contentInput.focus();
        return;
    }
    
    console.log('📝 Aktualizuji poznámku v databázi:', { noteId, title, content, category, priority, tags });
    
    // Disable tlačítko během aktualizace
    const updateBtn = document.getElementById('updateNoteBtn');
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Ukládám...';
    }
    
    // AJAX volání pro aktualizaci
    const noteData = {
        id: noteId,
        title: title,
        content: content,
        category: category || null,
        priority: priority,
        tags: tags || null
    };
    
    makeAjaxCall('updateNote', noteData)
        .then(updatedNote => {
            console.log('✅ Poznámka aktualizována v databázi:', updatedNote);
            
            // Skryjeme formulář
            hideEditNoteForm();
            
            // Znovu načteme všechny poznámky
            loadNotesFromDatabase();
            
        })
        .catch(error => {
            console.error('❌ Chyba při aktualizaci poznámky:', error);
            showError('Nepodařilo se aktualizovat poznámku: ' + error.message);
            
            // Obnovíme tlačítko
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="bi bi-check"></i> Uložit změny';
            }
        });
}

/**
 * Skryje editační formulář
 */
function hideEditNoteForm() {
    const form = document.getElementById('editNoteForm');
    if (form) {
        form.remove();
    }
}

/**
 * Zobrazí funkční vyhledávací formulář
 */
function showSearchForm() {
    console.log('🔍 Zobrazuji vyhledávání');
    
    // Najdeme místo, kam vložíme formulář
    const mainCard = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    
    if (!mainCard) {
        console.error('❌ Nepodařilo se najít místo pro formulář');
        return;
    }
    
    // Odstranit případný existující formulář
    const existingForm = document.getElementById('searchNoteForm');
    if (existingForm) {
        existingForm.remove();
    }
    
    // HTML pro vyhledávací formulář
    const searchFormHtml = `
        <div class="note-form show" id="searchNoteForm">
            <h6><i class="bi bi-search me-2"></i>Vyhledat v poznámkách</h6>
            <form>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="searchQuery" class="form-label">Hledaný výraz</label>
                        <input type="text" class="form-control" id="searchQuery" placeholder="Hledejte v názvech, obsahu nebo štítcích...">
                    </div>
                    <div class="col-md-4">
                        <label for="searchCategory" class="form-label">Kategorie</label>
                        <select class="form-select" id="searchCategory">
                            <option value="">Všechny kategorie</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-primary" id="searchBtn">
                        <i class="bi bi-search"></i> Hledat
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn">
                        <i class="bi bi-x-circle"></i> Vymazat formulář
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="cancelSearchBtn">
                        <i class="bi bi-x"></i> Zrušit
                    </button>
                </div>
                
                <div id="searchResults" class="mt-3" style="display: none;">
                    <h6 class="text-muted">Výsledky vyhledávání:</h6>
                    <div id="searchResultsContainer"></div>
                </div>
            </form>
        </div>
    `;
    
    // Vložíme formulář na začátek
    mainCard.insertAdjacentHTML('afterbegin', searchFormHtml);
    
    // Načteme kategorie pro select
    loadCategoriesForSearch();
    
    // Přidáme event listenery
    setupSearchFormEventListeners();
    
    // Fokus na vyhledávací pole
    document.getElementById('searchQuery').focus();
    
    // Scroll nahoru k formuláři
    document.getElementById('searchNoteForm').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Načte kategorie pro vyhledávací formulář
 */
function loadCategoriesForSearch() {
    console.log('📂 Načítám kategorie pro vyhledávání');
    
    makeAjaxCall('getCategories')
        .then(categories => {
            console.log('📂 Kategorie načteny:', categories);
            
            const categorySelect = document.getElementById('searchCategory');
            if (categorySelect && categories.length > 0) {
                // Přidáme kategorie do selectu
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categorySelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('❌ Chyba při načítání kategorií:', error);
        });
}

/**
 * Nastavení event listenerů pro vyhledávací formulář (s lepšími popisky)
 */
function setupSearchFormEventListeners() {
    const searchBtn = document.getElementById('searchBtn');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const cancelSearchBtn = document.getElementById('cancelSearchBtn');
    const searchQuery = document.getElementById('searchQuery');
    
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            console.log('🔍 Spouštím vyhledávání');
            performSearch();
        });
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            console.log('🗑️ Mažu obsah vyhledávacích polí');
            clearSearch();
        });
    }
    
    if (cancelSearchBtn) {
        cancelSearchBtn.addEventListener('click', function() {
            console.log('❌ Zavírám vyhledávání');
            hideSearchForm();
        });
    }
    
    // Vyhledávání při stisku Enter
    if (searchQuery) {
        searchQuery.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
}

/**
 * Provede vyhledávání
 */
function performSearch() {
    const searchQuery = document.getElementById('searchQuery');
    const searchCategory = document.getElementById('searchCategory');
    
    if (!searchQuery) {
        console.error('❌ Nepodařilo se najít vyhledávací pole');
        return;
    }
    
    const query = searchQuery.value.trim();
    const category = searchCategory ? searchCategory.value.trim() : '';
    
    if (!query && !category) {
        alert('Zadejte prosím hledaný výraz nebo vyberte kategorii');
        searchQuery.focus();
        return;
    }
    
    console.log('🔍 Provádím vyhledávání:', { query, category });
    
    // Disable tlačítko během vyhledávání
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Hledám...';
    }
    
    // Rozhodneme, jakou AJAX akci použít
    let ajaxAction, ajaxParams;
    
    if (category && !query) {
        // Hledání pouze podle kategorie
        ajaxAction = 'getNotesByCategory';
        ajaxParams = { category: category };
    } else {
        // Hledání podle textu
        ajaxAction = 'searchNotes';
        ajaxParams = { query: query };
    }
    
    makeAjaxCall(ajaxAction, ajaxParams)
        .then(results => {
            console.log('✅ Výsledky vyhledávání:', results);
            
            displaySearchResults(results, query, category);
            
        })
        .catch(error => {
            console.error('❌ Chyba při vyhledávání:', error);
            showError('Nepodařilo se provést vyhledávání: ' + error.message);
        })
        .finally(() => {
            // Obnovíme tlačítko
            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="bi bi-search"></i> Hledat';
            }
        });
}

/**
 * Zobrazí výsledky vyhledávání (skryje původní seznam)
 */
function displaySearchResults(results, query, category) {
    console.log('📋 Zobrazuji výsledky vyhledávání:', results.length);
    
    const searchResults = document.getElementById('searchResults');
    const searchResultsContainer = document.getElementById('searchResultsContainer');
    
    if (!searchResults || !searchResultsContainer) {
        console.error('❌ Nepodařilo se najít kontejner pro výsledky');
        return;
    }
    
    // Skryjeme původní seznam poznámek
    const originalNotesContainer = document.getElementById('notesContainer');
    const emptyState = document.querySelector('.empty-state-small');
    
    if (originalNotesContainer) {
        originalNotesContainer.style.display = 'none';
        console.log('🙈 Skryl jsem původní seznam poznámek');
    }
    
    if (emptyState) {
        emptyState.style.display = 'none';
        console.log('🙈 Skryl jsem empty state');
    }
    
    // Zobrazíme sekci výsledků
    searchResults.style.display = 'block';
    
    // Aktualizujeme nadpis s informací o vyhledávání
    const resultsHeader = searchResults.querySelector('h6');
    if (resultsHeader) {
        let headerText = `Výsledky vyhledávání (${results.length} ${results.length === 1 ? 'výsledek' : results.length <= 4 ? 'výsledky' : 'výsledků'})`;
        
        if (query && category) {
            headerText += ` pro "${query}" v kategorii "${category}"`;
        } else if (query) {
            headerText += ` pro "${query}"`;
        } else if (category) {
            headerText += ` v kategorii "${category}"`;
        }
        
        resultsHeader.textContent = headerText;
    }
    
    // Pokud nejsou žádné výsledky
    if (results.length === 0) {
        searchResultsContainer.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Nebyly nalezeny žádné poznámky odpovídající vašemu vyhledávání.
                <br><small class="text-muted">Zkuste změnit hledaný výraz nebo kategorii.</small>
            </div>
        `;
        return;
    }
    
    // Vytvoříme HTML pro výsledky
    let resultsHtml = '';
    results.forEach(note => {
        resultsHtml += createNoteHtml(note);
    });
    
    searchResultsContainer.innerHTML = resultsHtml;
    
    // Scroll k výsledkům
    searchResults.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Vymaže jen obsah polí (neruší vyhledávání)
 */
function clearSearch() {
    console.log('🗑️ Mažu obsah vyhledávacích polí');
    
    // Vymažeme jen obsah formuláře
    const searchQuery = document.getElementById('searchQuery');
    const searchCategory = document.getElementById('searchCategory');
    
    if (searchQuery) {
        searchQuery.value = '';
        searchQuery.focus(); // Vrátíme fokus na pole
    }
    
    if (searchCategory) {
        searchCategory.value = '';
    }
    
    // Skryjeme výsledky (ale necháme formulář otevřený)
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        searchResults.style.display = 'none';
    }
    
    // Zobrazíme zpět původní seznam poznámek
    showOriginalNotesList();
    
    console.log('✅ Vyhledávací pole vymazána, formulář zůstává otevřený');
}

/**
 * Zobrazí zpět původní seznam poznámek
 */
function showOriginalNotesList() {
    console.log('👁️ Zobrazuji zpět původní seznam poznámek');
    
    const originalNotesContainer = document.getElementById('notesContainer');
    const emptyState = document.querySelector('.empty-state-small');
    
    if (originalNotesContainer) {
        originalNotesContainer.style.display = 'block';
        console.log('👁️ Zobrazil jsem původní seznam poznámek');
    } else if (emptyState) {
        emptyState.style.display = 'block';
        console.log('👁️ Zobrazil jsem empty state');
    } else {
        // Pokud není ani seznam ani empty state, znovu načteme
        console.log('🔄 Znovu načítám poznámky');
        loadNotesFromDatabase();
    }
}

/**
 * Skryje vyhledávací formulář a vrátí původní seznam
 */
function hideSearchForm() {
    console.log('❌ Zavírám vyhledávací formulář');
    
    // Zobrazíme zpět původní seznam poznámek
    showOriginalNotesList();
    
    // Odstraníme formulář
    const form = document.getElementById('searchNoteForm');
    if (form) {
        form.remove();
    }
    
    console.log('✅ Vyhledávací formulář zavřen, původní seznam obnoven');
}

/**
 * Aktualizuje statistiky ze serveru
 */
function updateStatisticsFromServer(stats) {
    console.log('📊 Aktualizuji statistiky ze serveru:', stats);
    
    const totalElement = document.querySelector('.stat-number.text-primary');
    const weekElement = document.querySelector('.stat-number.text-success');
    
    if (totalElement) {
        totalElement.textContent = stats.total || 0;
    }
    
    if (weekElement) {
        weekElement.textContent = stats.thisWeek || 0;
    }
    
    console.log('✅ Statistiky aktualizovány ze serveru');
}

/**
 * Zobrazí chybovou zprávu
 */
function showError(message) {
    // Vytvoříme alert pro chybovou zprávu
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Najdeme místo pro alert
    const container = document.querySelector('.notes-dashboard');
    if (container) {
        container.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

/**
 * Escape HTML pro bezpečnost
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Skryje formulář pro přidání poznámky
 */
function hideAddNoteForm() {
    const form = document.getElementById('addNoteForm');
    if (form) {
        form.remove();
    }
}

/**
 * Zobrazí empty state když nejsou poznámky
 */
function showEmptyState() {
    const mainCardBody = document.querySelector('.notes-dashboard .col-md-8 .card-body');
    if (mainCardBody) {
        const emptyStateHtml = `
            <div class="empty-state-small">
                <i class="bi bi-sticky text-muted"></i>
                <p class="mb-0">Zatím nemáte žádné poznámky</p>
                <small class="text-muted">Klikněte na "Přidat poznámku" pro vytvoření první poznámky</small>
            </div>
        `;
        mainCardBody.innerHTML = emptyStateHtml;
    }
}

/**
 * Veřejné API modulu
 */
window.NotesModule = {
    version: '1.0.0',
    
    getInfo: function() {
        return {
            name: 'Poznámky',
            version: this.version,
            status: 'active'
        };
    },
    
    addNote: showAddNoteForm,
    editNote: editNote,
    search: showSearchForm,
    refresh: loadNotesFromDatabase
};

console.log('🌟 NotesModule API je dostupné:', window.NotesModule);