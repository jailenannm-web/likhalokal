/* Seller module front-end */
document.querySelectorAll("[data-json-payments]").forEach((hidden) => {
  const box = document.getElementById("paymentsBox");
  if (!box) return;
  try {
    const arr = JSON.parse(hidden.value || "[]");
    box.querySelectorAll("input[type=checkbox]").forEach((cb) => {
      cb.checked = arr.indexOf(cb.value) >= 0;
    });
  } catch (e) {}
  box.addEventListener("change", function () {
    const vals = [];
    box.querySelectorAll("input[type=checkbox]:checked").forEach((cb) => vals.push(cb.value));
    hidden.value = JSON.stringify(vals);
  });
});
