const form = document.getElementById("consultationForm");

const successModal = document.getElementById("successModal");
const successModalDialog = document.getElementById("successModalDialog");
const successModalTitle = document.getElementById("successModalTitle");
const successModalButton = document.getElementById("successModalButton");
const successModalClose = document.getElementById("successModalClose");
const successModalIconImage = document.getElementById("successModalIconImage");

function openSuccessModal() {
  successModalDialog.classList.remove("is-error");
  successModalTitle.innerHTML = "Заявка успешно<br>отправлена!";
  successModalButton.textContent = "Хорошо";
  successModalIconImage.src = "./assets/img/galochka.png";
  successModal.classList.add("is-open");
}

function openErrorModal() {
  successModalDialog.classList.add("is-error");
  successModalTitle.innerHTML = "Произошла<br>какая-то ошибка";
  successModalButton.textContent = "Повторить попытку";
  successModalIconImage.src = "./assets/img/cross.png";
  successModal.classList.add("is-open");
}

function closeSuccessModal() {
  successModal.classList.remove("is-open");
}

if (successModalClose) {
  successModalClose.addEventListener("click", closeSuccessModal);
}

if (successModalButton) {
  successModalButton.addEventListener("click", closeSuccessModal);
}

if (form) {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch("/back/submit.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        openSuccessModal();
      } else {
        openErrorModal();
      }
    } catch (error) {
      openErrorModal();
    }
  });
}