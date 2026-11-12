(() => {
  for (const form of document.querySelectorAll("form[data-nocaptcha]")) {
    const message = form.querySelector("[data-nc-message]");
    const edit = form.querySelector("[data-nc-edit-ms]");
    const checkbox = form.querySelector("[data-nc-checkbox]");
    const webdriver = form.querySelector("[data-nc-webdriver]");
    const started = performance.now();

    if (message && edit) {
      message.addEventListener("input", () => {
        edit.value = String(Math.floor(performance.now() - started));
      }, { once: true });
    }

    if (checkbox) {
      checkbox.checked = false;
      checkbox.disabled = false;
    }

    if (webdriver && navigator.webdriver === true) webdriver.value = "1";
  }
})();
