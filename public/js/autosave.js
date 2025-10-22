document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('note-form');
    let noteIdInput = form.querySelector('input[name="note_id"]');
    let timeout;

    function autoSave() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            // Обновляем textarea контентом из TinyMCE
            tinymce.triggerSave();

            const formData = new FormData(form);

            fetch('api/save_note.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success' && data.note_id) {
                      if (!noteIdInput) {
                          noteIdInput = document.createElement('input');
                          noteIdInput.type = 'hidden';
                          noteIdInput.name = 'note_id';
                          form.appendChild(noteIdInput);
                      }
                      noteIdInput.value = data.note_id;

                      const currentUrl = new URL(window.location.href);
                      if (currentUrl.searchParams.get('id') !== data.note_id) {
                           history.pushState({note_id: data.note_id}, '', 'note.php?id=' + data.note_id);
                      }
                      console.log('Заметка сохранена');
                  } else {
                      alert('Ошибка сохранения: ' + (data ? data.message : 'Unknown error'));
                  }
              }).catch(error => {
                  alert('Ошибка сети: ' + error);
              });
        }, 1000);
    }

    form.addEventListener('input', autoSave);
});
