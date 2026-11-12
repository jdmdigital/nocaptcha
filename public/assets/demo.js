(() => {
  const form = document.querySelector("form[data-nocaptcha]");
  const monitor = document.querySelector("#monitor");
  if (!form || !monitor || form.querySelector('button[type="submit"]').disabled) return;

  const title = document.querySelector("#inspection-title");
  const note = document.querySelector("#monitor-note");
  const verdict = document.querySelector("#result");
  const rows = new Map([...monitor.querySelectorAll("[data-layer]")].map(row => [row.dataset.layer, row]));
  const symbols = { pass: "✓", flag: "×", pending: "–" };
  const started = performance.now();
  const fastSeconds = Number(form.dataset.fastSeconds);
  const slowSeconds = Number(form.dataset.slowSeconds);
  const checkbox = form.elements.namedItem("nc_checkbox");
  const honeypot = form.elements.namedItem("nc_website");
  let timer;

  function setLayer(layer, state, detail) {
    const row = rows.get(layer);
    const description = row.querySelector("small");
    if (row.dataset.state === state && description.textContent === detail) return;
    row.dataset.state = state;
    row.querySelector(".layer-mark").textContent = symbols[state];
    description.textContent = detail;
  }

  function updateTiming() {
    const seconds = Math.floor((performance.now() - started) / 1000);
    const fast = seconds < fastSeconds;
    const slow = seconds >= slowSeconds;
    const automated = navigator.webdriver === true;
    const timing = fast ? "too fast" : slow ? "too slow" : "past the window";
    const browser = automated ? " · WebDriver reported" : "";
    setLayer("timing", fast || slow || automated ? "flag" : "pass", `${seconds}s elapsed · ${timing}${browser}`);
  }

  function updateTraps() {
    setLayer("checkbox", checkbox.checked ? "flag" : "pass",
      checkbox.checked ? "Checked · would flag this submission." : "Unchecked · trap clear.");
    setLayer("honeypot", honeypot.value ? "flag" : "pass",
      honeypot.value ? "Hidden field contains text." : "Hidden field is empty.");
  }

  function startLive() {
    monitor.dataset.phase = "live";
    title.textContent = "Layer monitor";
    note.textContent = "Live browser estimates. PHP verifies every submission.";
    verdict.hidden = true;
    setLayer("token", "pending", "PHP checks the token on submission.");
    setLayer("repeat", "pending", "PHP checks for a match on submission.");
    setLayer("rate", "pending", "PHP checks recent activity on submission.");
    updateTraps();
    updateTiming();
    clearInterval(timer);
    timer = setInterval(updateTiming, 250);
  }

  if (monitor.dataset.phase === "live") startLive();
  else note.textContent += " Edit the form to return to the live estimate.";

  form.addEventListener("input", () => {
    if (monitor.dataset.phase === "server") startLive();
    else updateTraps();
  });
})();
