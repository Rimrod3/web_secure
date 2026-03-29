<?php require __DIR__ . '/../layout/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auth</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/login.css">
</head>
<body>

<div class="scene" data-csrf-token="<?= htmlspecialchars($csrf_token ?? '') ?>">
  <div class="slider" id="slider">
    <div class="panel">
      <div class="brand">✦ Lumière</div><h1>Welcome<br>back.</h1><p class="subtitle">Sign in with your username &amp; password.</p>
      <div id="login-success-msg" class="success-msg" style="display:none;"></div>
      <div class="field"><label>Username</label><input id="login-user" type="text" placeholder="your_username" autocomplete="username"></div>
      <div class="field"><label>Password</label><input id="login-pass" type="password" placeholder="••••••••" autocomplete="current-password"><div class="forgot"><button type="button">Forgot password?</button></div></div>
      <div class="error-msg" id="login-error"></div>
      <button class="btn-primary" id="login-btn">Sign In</button>
      <div class="switch-link">No account yet? <button type="button" onclick="showRegister()">Create one</button></div>
    </div>
    <div class="panel">
      <div class="brand">✦ Lumière</div><h1>Join us<br>today.</h1><p class="subtitle">Create your account in seconds.</p>
      <div class="field"><label>Username</label><input id="reg-user" type="text" placeholder="choose_a_username" autocomplete="username"></div>
      <div class="field"><label>Email</label><input id="reg-email" type="email" placeholder="you@example.com" autocomplete="email"><div class="error-msg" id="email-error"></div></div>
      <div class="field"><label>Password</label><input id="reg-pass" type="password" placeholder="Strong password" autocomplete="new-password"><div class="rules"><span class="rule" id="r-len">12 characters min</span><span class="rule" id="r-upper">Uppercase letter</span><span class="rule" id="r-num">Number</span><span class="rule" id="r-special">Special character</span><span class="rule" id="r-nouser" style="grid-column:1/-1">Not contain username</span></div></div>
      <div class="error-msg" id="reg-error"></div>
      <button class="btn-primary" id="register-btn">Create Account</button>
      <div class="switch-link">Already have an account? <button type="button" onclick="showLogin()">Sign in</button></div>
    </div>
  </div>
</div>

<script>
  const CSRF_TOKEN = "<?= htmlspecialchars($csrf_token ?? '') ?>";

  // --- UI Functions ---
  const slider = document.getElementById('slider');
  function showRegister() { slider.classList.add('show-register'); }
  function showLogin() { slider.classList.remove('show-register'); }

  // --- API Communication ---
  async function apiCall(endpoint, data) {
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ ...data, csrf_token: CSRF_TOKEN })
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.message || 'An unknown error occurred.');
      }
      return result;
    } catch (error) {
      console.error(`API call to ${endpoint} failed:`, error);
      throw error;
    }
  }

  // --- Login ---
  document.getElementById('login-btn').addEventListener('click', async () => {
    const pseudo = document.getElementById('login-user').value.trim();
    const mdp = document.getElementById('login-pass').value;
    const errDiv = document.getElementById('login-error');
    errDiv.textContent = '';
    if (!pseudo || !mdp) {
      errDiv.textContent = 'Please fill in both fields.';
      return;
    }
    try {
      const result = await apiCall('/login', { pseudo, mdp });
      if (result.success && result.redirect) {
        window.location.href = result.redirect;
      }
    } catch (error) {
      errDiv.textContent = error.message;
    }
  });

  // --- Registration ---
  const regPassInput = document.getElementById('reg-pass');
  const regUserInput = document.getElementById('reg-user');
  const regEmailInput = document.getElementById('reg-email');

  regPassInput.addEventListener('input', validatePasswordRules);
  regUserInput.addEventListener('input', validatePasswordRules);

  function checkRules(pass, username) {
    return {
      len: pass.length >= 12,
      upper: /[A-Z]/.test(pass),
      num: /[0-9]/.test(pass),
      special: /[^A-Za-z0-9]/.test(pass),
      nouser: username.length > 0 && !pass.toLowerCase().includes(username.toLowerCase())
    };
  }

  function validatePasswordRules() {
    const pass = regPassInput.value;
    const username = regUserInput.value.trim();
    const rulesOk = checkRules(pass, username);
    document.getElementById('r-len').classList.toggle('ok', rulesOk.len);
    document.getElementById('r-upper').classList.toggle('ok', rulesOk.upper);
    document.getElementById('r-num').classList.toggle('ok', rulesOk.num);
    document.getElementById('r-special').classList.toggle('ok', rulesOk.special);
    document.getElementById('r-nouser').classList.toggle('ok', rulesOk.nouser);
  }

  document.getElementById('register-btn').addEventListener('click', async () => {
    const pseudo = regUserInput.value.trim();
    const email = regEmailInput.value.trim();
    const mdp = regPassInput.value;
    const errDiv = document.getElementById('reg-error');
    errDiv.textContent = '';

    // Frontend validation before sending
    if (!pseudo || !email || !mdp) {
      errDiv.textContent = 'Please fill in all fields.';
      return;
    }
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!emailOk) {
      errDiv.textContent = 'Please enter a valid email address.';
      return;
    }
    const rulesOk = checkRules(mdp, pseudo);
    if (!Object.values(rulesOk).every(v => v)) {
      errDiv.textContent = 'Please ensure all password requirements are met.';
      return;
    }
    
    try {
      const result = await apiCall('/register', { pseudo, email, mdp, confirm_mdp: mdp }); // We can assume confirmation if client-side validation is good
      if (result.success) {
        document.getElementById('login-success-msg').textContent = result.message;
        document.getElementById('login-success-msg').style.display = 'block';
        showLogin();
        // Clear registration form
        regUserInput.value = '';
        regEmailInput.value = '';
        regPassInput.value = '';
      }
    } catch (error) {
      errDiv.textContent = error.message;
    }
  });

</script>
</body>
</html>
<?php require __DIR__ . '/../layout/footer.php'; ?>
