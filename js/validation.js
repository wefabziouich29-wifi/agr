function showError(fieldId, msg) {
    var grp = document.getElementById(fieldId).closest('.form-group');
    grp.classList.add('invalid');
    grp.querySelector('.error-msg').textContent = msg;
}

function clearError(fieldId) {
    var grp = document.getElementById(fieldId).closest('.form-group');
    grp.classList.remove('invalid');
}

function validerCIN(cin)      { return /^\d{8}$/.test(cin); }
function validerPseudo(p)     { return /^[a-zA-Z]+$/.test(p); }
function validerMotDePasse(m) { return /^[a-zA-Z0-9]{7,}[$#]$/.test(m); }
function validerEmail(e)      { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }

function validerFormAgriculteur() {
    var ok = true;

    var nom = document.getElementById('nom');
    if (!nom.value.trim()) { showError('nom', 'Le nom est obligatoire.'); ok = false; }
    else clearError('nom');

    var prenom = document.getElementById('prenom');
    if (!prenom.value.trim()) { showError('prenom', 'Le prenom est obligatoire.'); ok = false; }
    else clearError('prenom');

    var cin = document.getElementById('CIN');
    if (!validerCIN(cin.value.trim())) { showError('CIN', 'Le CIN doit contenir exactement 8 chiffres.'); ok = false; }
    else clearError('CIN');

    var email = document.getElementById('email');
    if (!validerEmail(email.value.trim())) { showError('email', 'Adresse email invalide.'); ok = false; }
    else clearError('email');

    var pseudo = document.getElementById('pseudo');
    if (!validerPseudo(pseudo.value.trim())) { showError('pseudo', 'Le pseudo doit contenir uniquement des lettres.'); ok = false; }
    else clearError('pseudo');

    var mdp = document.getElementById('password');
    if (!validerMotDePasse(mdp.value)) { showError('password', 'Minimum 8 caracteres, finit par $ ou #'); ok = false; }
    else clearError('password');

    return ok;
}

function validerFormOuvrier() {
    var ok = validerFormAgriculteur();
    var photo = document.getElementById('photo');
    if (photo && photo.files.length === 0) { showError('photo', 'La photo est obligatoire.'); ok = false; }
    else if (photo) clearError('photo');
    return ok;
}

function apercuPhoto(input) {
    var preview = document.getElementById('photo-preview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
