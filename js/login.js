// This script is for the single-page login/register UI

// --- CSRF Token ---
// The token is read from a data attribute on the main scene element.
const CSRF_TOKEN = document.querySelector('.scene')?.dataset.csrfToken || '';

// --- UI Functions ---
const slider = document.getElementById('slider');
function showRegister() {
  if (slider) slider.classList.add('show-register');
}
function showLogin() {
  if (slider) slider.classList.remove('show-register');
}

// --- API Communication ---
async function apiCall(endpoint, data) {
  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json', 
        'Accept': 'application/json' 
      },
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

// --- Event Listeners ---
document.addEventListener('DOMContentLoaded', () => {
  // Switch buttons
  const switchToRegisterBtn = document.querySelector('button[onclick="showRegister()"]');
  const switchToLoginBtn = document.querySelector('button[onclick="showLogin()"]');
  if(switchToRegisterBtn) switchToRegisterBtn.addEventListener('click', showRegister);
  if(switchToLoginBtn) switchToLoginBtn.addEventListener('click', showLogin);
  
  // Login form
  const loginBtn = document.getElementById('login-btn');
  if(loginBtn) {
    loginBtn.addEventListener('click', async () => {
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
  }

  // Registration form
  const registerBtn = document.getElementById('register-btn');
  const regPassInput = document.getElementById('reg-pass');
  const regUserInput = document.getElementById('reg-user');
  const regEmailInput = document.getElementById('reg-email');

  if(registerBtn) {
    regPassInput.addEventListener('input', validatePasswordRules);
    regUserInput.addEventListener('input', validatePasswordRules);

    registerBtn.addEventListener('click', async () => {
      const pseudo = regUserInput.value.trim();
      const email = regEmailInput.value.trim();
      const mdp = regPassInput.value;
      const errDiv = document.getElementById('reg-error');
      errDiv.textContent = '';

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
        const result = await apiCall('/register', { pseudo, email, mdp, confirm_mdp: mdp });
        if (result.success) {
          const successMsgDiv = document.getElementById('login-success-msg');
          successMsgDiv.textContent = result.message;
          successMsgDiv.style.display = 'block';
          showLogin();
          regUserInput.value = '';
          regEmailInput.value = '';
          regPassInput.value = '';
        }
      } catch (error) {
        errDiv.textContent = error.message;
      }
    });
  }

  // --- Password Validation UI ---
  function checkRules(pass, username) {
    return {
      len: pass.length >= 12,
      upper: /[A-Z]/.test(pass),
      num: /[0-9]/.test(pass),
      special: /[^A-Za-z0-9]/.test(pass),
      nouser: username.length > 0 && pass.length > 0 && !pass.toLowerCase().includes(username.toLowerCase())
    };
  }

  function validatePasswordRules() {
    const pass = regPassInput.value;
    const username = regUserInput.value.trim();
    const rulesOk = checkRules(pass, username);
    setRule('r-len', rulesOk.len);
    setRule('r-upper', rulesOk.upper);
    setRule('r-num', rulesOk.num);
    setRule('r-special', rulesOk.special);
    setRule('r-nouser', rulesOk.nouser);
  }

  function setRule(id, ok) {
    const el = document.getElementById(id);
    if(el) el.classList.toggle('ok', ok);
  }
});
