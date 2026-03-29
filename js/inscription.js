document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('mdp');
    const usernameInput = document.getElementById('pseudo');
    const strengthIndicator = document.getElementById('password-strength');

    if (!passwordInput || !usernameInput || !strengthIndicator) return;

    const checks = {
        length:     document.getElementById('length'),
        uppercase:  document.getElementById('uppercase'),
        lowercase:  document.getElementById('lowercase'),
        number:     document.getElementById('number'),
        symbol:     document.getElementById('symbol'),
        noUsername: document.getElementById('no-username')
    };

    const initialTexts = {};
    for (const key in checks) {
        initialTexts[key] = checks[key].textContent;
    }

    function updateCheck(element, key, isValid) {
        element.style.color = isValid ? 'green' : 'red';
        element.textContent = (isValid ? '👍 ' : '👎 ') + initialTexts[key];
    }

    function updateInputColor(password) {
        const len = password.length;
        if (len === 0) {
            passwordInput.style.borderColor = '#ccc'; // neutral when empty
        } else if (len >= 12) { // Harmonisé : Pas de limite à 20 ici
            passwordInput.style.borderColor = 'green'; 
        } else {
            passwordInput.style.borderColor = 'red';  
        }
    }

    function validatePassword() {
        const password = passwordInput.value;
        const username = usernameInput.value;

        if (password.length === 0 && document.activeElement !== passwordInput) {
            strengthIndicator.style.display = 'none';
            updateInputColor(password);
            return;
        }

        strengthIndicator.style.display = 'block';
        updateInputColor(password); //Color update

        // Harmonisé : Pas de limite à 20 ici
        updateCheck(checks.length,     'length',     password.length >= 12);
        updateCheck(checks.uppercase,  'uppercase',  /[A-Z]/.test(password));
        updateCheck(checks.lowercase,  'lowercase',  /[a-z]/.test(password));
        updateCheck(checks.number,     'number',     /\d/.test(password));
        updateCheck(checks.symbol,     'symbol',     /[@$!%*?&]/.test(password));

        const usernameExists = username && username.length > 0;
        updateCheck(checks.noUsername, 'noUsername',
            !usernameExists || !password.toLowerCase().includes(username.toLowerCase()));
    }

    passwordInput.addEventListener('focus', validatePassword);
    passwordInput.addEventListener('input', validatePassword);
    usernameInput.addEventListener('input', validatePassword);

    if (passwordInput.value) validatePassword();
});
