document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('note-form');
    const titleInput = form.querySelector('input[name="title"]');
    const contentInput = form.querySelector('textarea[name="content"]');
    let noteId = form.querySelector('input[name="note_id"]');
    let timeout;

    function autoSave() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const formData = new FormData(form);
            fetch('api/save_note.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success' && data.note_id) {
                      if (!noteId) {
                          noteId = document.createElement('input');
                          noteId.type = 'hidden';
                          noteId.name = 'note_id';
                          form.appendChild(noteId);
                      }
                      noteId.value = data.note_id;
                      history.pushState(null, '', 'note.php?id=' + data.note_id);
                      console.log('Заметка сохранена');
                  }
              });
        }, 1000);
    }

    titleInput.addEventListener('input', autoSave);
    contentInput.addEventListener('input', autoSave);
});
