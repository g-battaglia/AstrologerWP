document.addEventListener('DOMContentLoaded', function() {
    var nameField = document.getElementById('my_plugin_name');
    var dateField = document.getElementById('my_plugin_date');
    var previewName = document.getElementById('previewName');
    var previewDate = document.getElementById('previewDate');

    if (nameField && previewName) {
        nameField.addEventListener('input', function() {
            previewName.textContent = nameField.value;
        });
    }

    if (dateField && previewDate) {
        dateField.addEventListener('input', function() {
            previewDate.textContent = dateField.value;
        });
    }
});
