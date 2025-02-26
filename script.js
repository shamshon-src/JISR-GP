// script.js
document.addEventListener("DOMContentLoaded", function () {
    const roleInputs = document.querySelectorAll("input[name='role']");
    const descriptionField = document.getElementById("description");

    roleInputs.forEach(input => {
        input.addEventListener("change", function () {
            if (this.value === "Craftsman") {
                descriptionField.style.display = "block";
            } else {
                descriptionField.style.display = "none";
            }
        });
    });
});
function toggleDropdown() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}
