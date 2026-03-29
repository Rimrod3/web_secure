<?php require __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/login.css">

<?php
// Determine which panel to show based on URL. Defaults to 'login'.
// This allows the page to work without JavaScript.
$form_to_show = ($_GET['form'] ?? 'login') === 'register' ? 'register' : 'login';
?>

<div class="scene" data-csrf-token="<?= htmlspecialchars($csrf_token ?? '') ?>">
  <div class="slider <?= $form_to_show === 'register' ? 'show-register' : '' ?>" id="slider">

    <!-- ══ LOGIN PANEL ══ -->
    <div class="panel">
      <form id="login-form" method="POST" action="/login">
        <div class="brand">✦ Lumière</div>
        <h1>Welcome<br>back.</h1>
        <p class="subtitle">Sign in with your username &amp; password.</p>

        <div id="login-success-msg" class="success-msg" style="display:none;"></div>

        <div class="field">
          <label>Username</label>
          <input id="login-user" name="pseudo" type="text" placeholder="your_username" autocomplete="username">
        </div>
        <div class="field">
          <label>Password</label>
          <input id="login-pass" name="mdp" type="password" placeholder="••••••••" autocomplete="current-password">
          
        </div>
        <div class="error-msg" id="login-error"></div>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <button type="submit" class="btn-primary" id="login-btn">Sign In</button>

        <div class="switch-link">
          No account yet?
          <a href="?form=register" id="go-register">Create one</a>
        </div>
      </form>
    </div>

    <!-- ══ REGISTER PANEL ══ -->
    <div class="panel">
      <form id="register-form" method="POST" action="/register">
        <div class="brand">✦ Lumière</div>
        <h1>Join us<br>today.</h1>
        <p class="subtitle">Create your account in seconds.</p>

        <div class="field">
          <label>Username</label>
          <input id="reg-user" name="pseudo" type="text" placeholder="choose_a_username" autocomplete="username">
        </div>
        <div class="field">
          <label>Email</label>
          <input id="reg-email" name="email" type="email" placeholder="you@example.com" autocomplete="email">
          <div class="error-msg" id="email-error"></div>
        </div>
        <div class="field">
          <label>Password</label>
          <input id="reg-pass" name="mdp" type="password" placeholder="Strong password" autocomplete="new-password">
          <div class="rules">
            <span class="rule" id="r-len">12 characters min</span>
            <span class="rule" id="r-upper">Uppercase letter</span>
            <span class="rule" id="r-num">Number</span>
            <span class="rule" id="r-special">Special character (@$!%*?&amp;)</span>
            <span class="rule" id="r-nouser" style="grid-column:1/-1">Not contain username</span>
          </div>
        </div>
        <div class="error-msg" id="reg-error"></div>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <button type="submit" class="btn-primary" id="register-btn">Create Account</button>

        <div class="switch-link">
          Already have an account?
          <a href="?form=login" id="go-login">Sign in</a>
        </div>
      </form>
    </div>

  </div><!-- /slider -->
</div><!-- /scene -->

<script src="<?= BASE_URL ?>js/login.js"></script>

<?php require __DIR__ . '/../layout/footer.php'; ?>