// js/login.js — single-page login/register UI
// Now with graceful degradation if JS is disabled.

document.addEventListener('DOMContentLoaded', () => {

  // ── CSRF token (from data attribute — avoids inline script / CSP issues) ──
  const CSRF_TOKEN = document.querySelector('.scene')?.dataset.csrfToken || '';

  // ── DOM elements ──
  const slider = document.getElementById('slider');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');

  // ── Slider functions ──
  function showRegister(e) {
    if (e) e.preventDefault(); // prevent anchor link from reloading page
    if (slider) slider.classList.add('show-register');
  }

  function showLogin(e) {
    if (e) e.preventDefault(); // prevent anchor link from reloading page
    if (slider) slider.classList.remove('show-register');
  }

  // Hook into the new anchor links
  document.getElementById('go-register')?.addEventListener('click', showRegister);
  document.getElementById('go-login')?.addEventListener('click', showLogin);

  // ── API helper ──
  async function apiCall(endpoint, data) {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ ...data, csrf_token: CSRF_TOKEN }),
    });

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || 'An unknown error occurred.');
    }

    return result;
  }

  // ── Login form submission ──
  loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent traditional form submission

    const pseudo  = document.getElementById('login-user').value.trim();
    const mdp     = document.getElementById('login-pass').value;
    const errDiv  = document.getElementById('login-error');
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
    } catch (err) {
      errDiv.textContent = err.message;
    }
  });

  // ── Password rules ──
  const regPassInput  = document.getElementById('reg-pass');
  const regUserInput  = document.getElementById('reg-user');
  const regEmailInput = document.getElementById('reg-email');

  function checkRules(pass, username) {
    return {
      len:     pass.length >= 12,
      upper:   /[A-Z]/.test(pass),
      num:     /[0-9]/.test(pass),
      special: /[@$!%*?&]/.test(pass),   // matches server-side regex
      nouser:  username.length > 0 && pass.length > 0
               && !pass.toLowerCase().includes(username.toLowerCase()),
    };
  }

  function setRule(id, ok) {
    document.getElementById(id)?.classList.toggle('ok', ok);
  }

  function validatePasswordRules() {
    const pass     = regPassInput?.value || '';
    const username = regUserInput?.value.trim() || '';
    const r        = checkRules(pass, username);
    setRule('r-len',     r.len);
    setRule('r-upper',   r.upper);
    setRule('r-num',     r.num);
    setRule('r-special', r.special);
    setRule('r-nouser',  r.nouser);
  }

  regPassInput?.addEventListener('input', validatePasswordRules);
  regUserInput?.addEventListener('input', validatePasswordRules);

  // ── Register form submission ──
  registerForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent traditional form submission

    const pseudo  = regUserInput.value.trim();
    const email   = regEmailInput.value.trim();
    const mdp     = regPassInput.value;
    const errDiv  = document.getElementById('reg-error');
    errDiv.textContent = '';

    if (!pseudo || !email || !mdp) {
      errDiv.textContent = 'Please fill in all fields.';
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errDiv.textContent = 'Please enter a valid email address.';
      return;
    }

    const r = checkRules(mdp, pseudo);
    if (!Object.values(r).every(Boolean)) {
      errDiv.textContent = 'Please ensure all password requirements are met.';
      return;
    }

    try {
      const result = await apiCall('/register', { pseudo, email, mdp, confirm_mdp: mdp });
      if (result.success) {
        const successDiv = document.getElementById('login-success-msg');
        if (successDiv) {
          successDiv.textContent = result.message;
          successDiv.style.display = 'block';
        }
        showLogin();
        regUserInput.value  = '';
        regEmailInput.value = '';
        regPassInput.value  = '';
        validatePasswordRules(); // reset rule indicators
      }
    } catch (err) {
      errDiv.textContent = err.message;
    }
  });

});