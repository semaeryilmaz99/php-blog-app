(function () {
  const btn = document.getElementById("hamburger");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const body = document.body;

  if (!btn || !sidebar) return;

  function setState(open) {
    sidebar.classList.toggle("is-open", open);
    body.classList.toggle("sidebar-open", open);
    sidebar.setAttribute("aria-hidden", String(!open));
    btn.setAttribute("aria-expanded", String(open));
    if (overlay) overlay.hidden = !open;
  }

  setState(false);

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    setState(!sidebar.classList.contains("is-open"));
  });

  if (overlay) overlay.addEventListener("click", () => setState(false));
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setState(false);
  });
})();
