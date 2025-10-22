const notesListEl = document.getElementById('notes-list');
const noteTitleEl = document.getElementById('note-title');
const noteContentEl = document.getElementById('note-content');
const saveNoteBtn = document.getElementById('save-note');
const deleteNoteBtn = document.getElementById('delete-note');
const folderListEl = document.getElementById('folder-list');
const newFolderEl = document.getElementById('new-folder-name');
const addFolderBtn = document.getElementById('add-folder');
const newNoteBtn = document.getElementById('new-note');

let notes = [];
let folders = [];
let currentNoteId = null;
let currentFolderId = null;

// Fetch all data from the API
async function fetchData() {
    await fetchFolders();
    await fetchNotes();
}

// Fetch folders from the API
async function fetchFolders() {
    try {
        const response = await fetch('../backend/api/folders.php');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        folders = await response.json();
        renderFolders();
    } catch (error) {
        console.error("Error fetching folders:", error);
    }
}

// Fetch notes from the API
async function fetchNotes() {
    try {
        const url = currentFolderId ? `../backend/api/notes.php?folder_id=${currentFolderId}` : '../backend/api/notes.php';
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        notes = await response.json();
        renderNotes();
    } catch (error) {
        console.error("Error fetching notes:", error);
    }
}

// Render the list of folders
function renderFolders() {
    folderListEl.innerHTML = '';

    const allNotesEl = document.createElement('div');
    allNotesEl.classList.add('folder-item', 'all-notes');
    allNotesEl.textContent = 'All Notes';
    allNotesEl.addEventListener('click', () => {
        currentFolderId = null;
        document.querySelectorAll('.folder-item').forEach(el => el.classList.remove('active'));
        allNotesEl.classList.add('active');
        fetchNotes();
    });
    if (currentFolderId === null) {
        allNotesEl.classList.add('active');
    }
    folderListEl.appendChild(allNotesEl);

    folders.forEach(folder => {
        const folderEl = document.createElement('div');
        folderEl.classList.add('folder-item');
        folderEl.dataset.id = folder.id;

        const folderNameEl = document.createElement('span');
        folderNameEl.textContent = folder.name;
        folderNameEl.addEventListener('click', () => {
            currentFolderId = folder.id;
            document.querySelectorAll('.folder-item').forEach(el => el.classList.remove('active'));
            folderEl.classList.add('active');
            fetchNotes();
        });

        folderEl.appendChild(folderNameEl);

        if (folder.id) { // Only add buttons to folders with an ID
            const renameBtn = document.createElement('button');
            renameBtn.textContent = 'Rename';
            renameBtn.addEventListener('click', () => renameFolder(folder.id, folder.name));

            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = 'Delete';
            deleteBtn.classList.add('danger');
            deleteBtn.addEventListener('click', () => deleteFolder(folder.id));

            folderEl.appendChild(renameBtn);
            folderEl.appendChild(deleteBtn);
        }

        if (folder.id === currentFolderId) {
            folderEl.classList.add('active');
        }
        folderListEl.appendChild(folderEl);
    });
}

// Render the list of notes
function renderNotes() {
    notesListEl.innerHTML = '';
    notes.forEach(note => {
        const noteEl = document.createElement('div');
        noteEl.classList.add('note-item');
        noteEl.textContent = note.title;
        noteEl.dataset.id = note.id;
        if (note.id === currentNoteId) {
            noteEl.classList.add('active');
        }
        noteEl.addEventListener('click', () => {
            currentNoteId = note.id;
            noteTitleEl.value = note.title;
            noteContentEl.value = note.content;
            document.querySelectorAll('.note-item').forEach(el => el.classList.remove('active'));
            noteEl.classList.add('active');
        });
        notesListEl.appendChild(noteEl);
    });
}

// Save a new note or update an existing one
async function saveNote() {
    const title = noteTitleEl.value;
    const content = noteContentEl.value;
    const noteData = { title, content, folder_id: currentFolderId };

    try {
        let response;
        if (currentNoteId) {
            // Update existing note
            response = await fetch(`../backend/api/notes.php?id=${currentNoteId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(noteData)
            });
        } else {
            // Create new note
            response = await fetch('../backend/api/notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(noteData)
            });
        }
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        await fetchNotes(); // Re-fetch to update the list
        clearEditor();
    } catch (error) {
        console.error("Error saving note:", error);
    }
}


// Add a new folder
async function addFolder() {
    const name = newFolderEl.value.trim();
    if (!name) return;

    try {
        const response = await fetch('../backend/api/folders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        newFolderEl.value = '';
        await fetchFolders(); // Re-fetch folders to update the list
    } catch (error) {
        console.error("Error adding folder:", error);
    }
}

// Rename a folder
async function renameFolder(id, currentName) {
    const newName = prompt('Enter new folder name:', currentName);
    if (!newName || newName.trim() === '') return;

    try {
        const response = await fetch(`../backend/api/folders.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: newName.trim() })
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        await fetchFolders();
    } catch (error) {
        console.error("Error renaming folder:", error);
    }
}

// Delete a folder
async function deleteFolder(id) {
    if (!confirm('Are you sure you want to delete this folder? All notes within will be moved to "All Notes".')) return;

    try {
        const response = await fetch(`../backend/api/folders.php?id=${id}`, {
            method: 'DELETE'
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        currentFolderId = null; // Reset to all notes
        await fetchData();
    } catch (error) {
        console.error("Error deleting folder:", error);
    }
}


function clearEditor() {
    currentNoteId = null;
    noteTitleEl.value = '';
    noteContentEl.value = '';
    document.querySelectorAll('.note-item').forEach(el => el.classList.remove('active'));
}

// Delete a note
async function deleteNote() {
    if (!currentNoteId) return;

    try {
        const response = await fetch(`../backend/api/notes.php?id=${currentNoteId}`, {
            method: 'DELETE'
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        await fetchNotes(); // Re-fetch to update the list
        clearEditor();
    } catch (error) {
        console.error("Error deleting note:", error);
    }
}

// Event Listeners
saveNoteBtn.addEventListener('click', saveNote);
deleteNoteBtn.addEventListener('click', deleteNote);
addFolderBtn.addEventListener('click', addFolder);
newNoteBtn.addEventListener('click', clearEditor);
document.querySelector('.notes-list').addEventListener('click', (e) => {
    if (e.target.classList.contains('note-item')) {
       // logic handled in renderNotes
    } else {
        clearEditor();
    }
});


// Initial data fetch
fetchData();
