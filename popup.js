document.addEventListener('DOMContentLoaded', function() {
  var statusMessage = document.getElementById('statusMessage');
  var signupForm = document.getElementById('signupForm');
  var signinForm = document.getElementById('signinForm');
  var toggleFormBtn = document.getElementById('toggleForm');
  var logoutBtn = document.getElementById('logout'); // Logout button element
  var isSignup = true;

  // Function to toggle between sign up and sign in forms
  function toggleForm() {
    if (isSignup) {
      signupForm.style.display = 'none';
      signinForm.style.display = 'block';
      toggleFormBtn.textContent = 'Switch to Sign Up';
    } else {
      signupForm.style.display = 'block';
      signinForm.style.display = 'none';
      toggleFormBtn.textContent = 'Switch to Sign In';
    }
    isSignup = !isSignup;
  }

  // Function to send authentication requests
  function sendAuthRequest(action, username, password, email = '') {
    var formData = new URLSearchParams({
      'action': action,
      'username': username,
      'password': password
    });

    if (email) {
      formData.append('email', email);
    }

    fetch('https://allohash.com/auth.php', { 
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === "success") {
        updateStatus(`${action.charAt(0).toUpperCase() + action.slice(1)} successful.`, true);
        if (action === 'signin') {
          saveSession(username);
        }
      } else {
        throw new Error(data.error || `${action.charAt(0).toUpperCase() + action.slice(1)} failed`);
      }
    })
    .catch((error) => {
      updateStatus(error.message, false);
    });
  }

  // Function to update the status message
  function updateStatus(message, isSuccess) {
    statusMessage.textContent = message;
    statusMessage.style.backgroundColor = isSuccess ? 'green' : 'red';
    statusMessage.style.display = 'block';
  }

  // Function to save session data
  function saveSession(username) {
    chrome.storage.local.set({ 'sessionUser': username }, function() {
      document.getElementById('auth').style.display = 'none';
      document.getElementById('smsSection').style.display = 'block';
      logoutBtn.style.display = 'block';
    });
  }

  // Function to clear session data
  function clearSession() {
    chrome.storage.local.remove('sessionUser', function() {
      checkSession();
    });
  }

  // Function to check session data
  function checkSession() {
    chrome.storage.local.get('sessionUser', function(data) {
      if (data.sessionUser) {
        document.getElementById('auth').style.display = 'none';
        document.getElementById('smsSection').style.display = 'block';
        logoutBtn.style.display = 'block';
      } else {
        document.getElementById('auth').style.display = 'block';
        document.getElementById('smsSection').style.display = 'none';
        logoutBtn.style.display = 'none';
      }
    });
  }

  // Check session on DOM load
  checkSession();

  // Event listeners for sign up and sign in
  document.getElementById('signup').addEventListener('click', function() {
    var username = document.getElementById('signupUsername').value;
    var email = document.getElementById('signupEmail').value;
    var password = document.getElementById('signupPassword').value;
    sendAuthRequest('signup', username, password, email);
  });

  document.getElementById('signin').addEventListener('click', function() {
    var username = document.getElementById('signinUsername').value;
    var password = document.getElementById('signinPassword').value;
    sendAuthRequest('signin', username, password);
  });

  // Event listener for toggling forms
  toggleFormBtn.addEventListener('click', toggleForm);

  // Event listener for sending SMS
  document.getElementById('send').onclick = function() {
    var phoneNumber = document.getElementById('phoneNumber').value;
    var message = document.getElementById('message').value;

    fetch('https://allohash.com/chrome.php', { 
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        'to': phoneNumber,
        'body': message
      })
    })
    .then(response => response.json())
    .then(data => {
      if(data.status === "success") {
        updateStatus('SMS sent successfully: ' + data.message_sid, true);
      } else {
        throw new Error(data.error || 'Failed to send SMS');
      }
    })
    .catch((error) => {
      updateStatus(error.message, false);
    });
  };

  // Event listener for logout
  logoutBtn.addEventListener('click', clearSession);
});
