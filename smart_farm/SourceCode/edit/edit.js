const form = document.getElementById("editForm");
const message = document.getElementById("message");

form.addEventListener("submit", function (event) {
  event.preventDefault();

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  const formData = new FormData(form);

  fetch("../process.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((result) => {
      console.log("Server response:", result);
      message.classList.remove("d-none");
      setTimeout(() => message.classList.add("d-none"), 4000);
      form.classList.add("was-validated");
    })
    .catch((error) => console.error("Error:", error));
});

function cancelEdit() {
  if (confirm("Cancel changes and go back?")) {
    window.history.back();
  }
}
