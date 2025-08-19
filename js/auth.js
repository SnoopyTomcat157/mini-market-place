document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');

    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Aggiunge/rimuove la classe per cambiare l'icona
            button.classList.toggle('is-showing');
            
            const passwordInput = button.previousElementSibling; // Prende l'input che precede il pulsante
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    });

    if(registerForm){
        registerForm.addEventListener('submit', (event) => {
            event.preventDefault();

            //sanifico gli input
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();


            //controllo approfondito degli input

            if(username.length < 3 || username.length > 20){
                showError('Username deve essere tra 3 e 20 caratteri.');
                return
            }

            if(!/^[a-zA-Z0-9_]+$/.test(username)){
                showError('Username può contenere solo lettere, numeri e underscore.');
                return;
            }

            if (!/^\S+@\S+\.\S+$/.test(email)) {
                showError('Inserisci un formato email valido.');
                return;
            }

            if(password.length < 8){
                showError('La password deve essere almeno di 8 caratteri.');
                return;
            }

            if (!/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/.test(password)) {
                showError('La password deve contenere almeno una lettera maiuscola, una minuscola e un numero.');
                return;
            }
            
            if(password !== confirmPassword){
                showError('Le password non corrispondono.');
                return;
            }

            //arrivato qui tutti i controlli sono passati
            
            handleFormSubmit(event, 'api/auth.php', 'register', (result) => {
                showSuccess(result.message);
                registerForm.reset();
                setTimeout(() => {
                    window.location.href = 'user_dashboard.php';
                }, 2000);
            });
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            handleFormSubmit(event, 'api/auth.php', 'login', (result) => {
                showSuccess(result.message);

                setTimeout(() => {
                    let redirectUrl = 'user_dashboard.php';
                    if (result.role === 'admin') {
                        redirectUrl = 'admin_dashboard.php';
                    }
                    window.location.href = redirectUrl;
                }, 1500);
            });
        });
    }

    function showError(message){
        const feedback = document.getElementById('feedbackMessage');
        feedback.textContent = message;
        feedback.className = 'feedback-message error';

    }

    function showSuccess(message){
        const feedback = document.getElementById('feedbackMessage');
        feedback.textContent = message;
        feedback.className = 'feedback-message success';
    }
})