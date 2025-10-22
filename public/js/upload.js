document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('drop-zone');
    const noteId = new URLSearchParams(window.location.search).get('id');

    if (dropZone && noteId) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            handleFiles(files);
        });
    }

    function handleFiles(files) {
        for (const file of files) {
            uploadFile(file);
        }
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('note_id', noteId);

        fetch('api/upload_file.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.status === 'success') {
                  addFileToList(data.file);
              } else {
                  alert('Ошибка загрузки: ' + data.message);
              }
          }).catch(error => {
              alert('Ошибка сети: ' + error);
          });
    }

    function addFileToList(file) {
        const fileList = document.getElementById('file-list');
        const fileElement = document.createElement('div');
        fileElement.innerHTML = `<a href="serve_file.php?id=${file.id}" target="_blank">${file.name}</a> (${file.size} bytes)`;
        fileList.appendChild(fileElement);
    }

    // Загрузка списка файлов при загрузке страницы
    if(noteId) {
        fetch(`api/get_files.php?note_id=${noteId}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    data.files.forEach(addFileToList);
                }
            });
    }
});
