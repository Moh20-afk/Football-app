function showUsername() {
  const username = document.getElementById("username").value;
  const welcome = document.getElementById("welcome-text");

  welcome.innerText = "Welcome " + username;
  welcome.classList.remove("hidden"); // make it visible

  // After 5 seconds, fade out
  setTimeout(() => {
    welcome.classList.add("fade");
  }, 5000);

  // After fade ends (2s later), hide completely
  setTimeout(() => {
    welcome.classList.add("hidden");
    welcome.classList.remove("fade"); // reset for next time
  }, 7000);
}