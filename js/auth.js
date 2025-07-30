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
        registerForm.addEventListener('submit',(event) => {
            event.preventDefault();

            const feedback = document.getElementById('feedbackMessage');
            const submitButton = registerForm.querySelector('button[type="submit"]');
            const loadingTextSpan = submitButton.querySelector('.button-text-loading');

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
            
            submitButton.disabled = true;
            feedback.className = 'feedback-message';
            submitButton.classList.add('is-loading');

            const startTime = Date.now(); //memorizzo il momento di inizio animazione

            let count = 0;
            const interval = setInterval(() => {
                count = (count + 1) % 4;
                const anim = '.'.repeat(count);
                feedback.textContent = `Registrazione in corso${anim}`;
            }, 400);

            let fetchResult = null;

            const formData = new FormData();
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('action', 'register')

            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if(!ok)
                    throw new Error(data.message || 'Si è verificato un errore.');
                fetchResult = { success: true, message: data.message };
            })
            .catch(error => {
                fetchResult = { success: false, message: error.message };          
                console.error('Errore:', error);
            })
            .finally(() => {
                // fine animazione
                const tempoPassato = Date.now() - startTime;
                const durata = 5000; // 5 second1
                const tempoRimanente = Math.max(0, durata - tempoPassato);

                // aspetto che sia passato almeno 5 secondi
                // prima di fermare l'animazione
                 setTimeout(() => {
                    clearInterval(interval);
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false; 
                    
                    // Ora mostra il messaggio finale
                    if (fetchResult && fetchResult.success) {
                        showSuccess(fetchResult.message);
                        registerForm.reset();
                    } else if (fetchResult) {
                        showError(fetchResult.message);
                    }
                }, tempoRimanente);
            })
        })
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const feedbackDiv = document.getElementById('feedbackMessage');
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const loadingTextSpan = submitButton.querySelector('.button-text-loading');

            // Avvio animazione
            submitButton.disabled = true;
            submitButton.classList.add('is-loading');
            feedbackDiv.textContent = '';

            let count = 0;
            const interval = setInterval(() => {
                count = (count + 1) % 4;
                const dots = '.'.repeat(count);
                if (loadingTextSpan) loadingTextSpan.textContent = `Accesso${dots}`;
            }, 400);

            let fetchResult = null;
            const startTime = Date.now();
            const formData = new FormData(loginForm);
            formData.append('action', 'login');

            fetch('api/auth.php', { method: 'POST', body: formData })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (!ok) throw new Error(data.message || 'Errore.');
                    fetchResult = { success: true, message: data.message, role: data.role};
                })
                .catch(error => {
                    fetchResult = { success: false, message: error.message };
                })
                .finally(() => {
                    const tempoPassato = Date.now() - startTime;
                    const durata = 1000;
                    const tempoRimanente = Math.max(0, durata - tempoPassato);

                    setTimeout(() => {
                        clearInterval(interval);
                        submitButton.classList.remove('is-loading');

                        if (fetchResult && fetchResult.success) {
                            showSuccess(fetchResult.message);
                            setTimeout(() => { let redirectUrl = 'user_dashboard.php';
                                if(fetchResult.role === 'admin') redirectUrl = 'admin_dashboard.php';
                                window.location.href = redirectUrl;
                            }, 1500);
                        } else if (fetchResult) {
                            showError(fetchResult.message);
                            submitButton.disabled = false;
                        } else {
                            submitButton.disabled = false;
                        }
                    }, tempoRimanente);
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