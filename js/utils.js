/**
 * Funzione di basso livello per effettuare chiamate API al nostro backend.
 * @param {string} endpoint - L'URL dell'API da chiamare.
 * @param {FormData} formData - I dati da inviare.
 * @returns {Promise<object>} - Una Promise che si risolve con i dati JSON della risposta.
 */
async function apiCall(endpoint, formData) {
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Si è verificato un errore sconosciuto.');
        }
        return result;
    } catch (error) {
        console.error(`Errore nella chiamata API a ${endpoint}:`, error);
        throw error; // Rilancia l'errore per gestirlo nel file specifico
    }
}

/**
 * Funzione generica per gestire l'invio di un form tramite AJAX.
 * Si occupa di disabilitare il pulsante, effettuare la chiamata API,
 * gestire gli errori e chiamare una funzione di successo personalizzata.
 *
 * @param {Event} event - L'evento 'submit' del form.
 * @param {string} endpoint - L'URL dell'API a cui inviare i dati.
 * @param {string} action - L'azione specifica da inviare (es. 'login', 'register').
 * @param {function} onSuccessCallback - La funzione da eseguire in caso di successo. Riceve i dati dalla risposta.
 */
async function handleFormSubmit(event, endpoint, action, onSuccessCallback) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    // Trova l'ID del div di feedback che segue immediatamente il form
    const feedbackDiv = form.nextElementSibling;
    const loadingTextSpan = submitButton.querySelector('.button-text-loading');

    submitButton.disabled = true;
    submitButton.classList.add('is-loading');
    if (feedbackDiv) feedbackDiv.textContent = ''; // Pulisce i messaggi precedenti

    let dotCount = 0;
    const loadingInterval = setInterval(() => {
        dotCount = (dotCount + 1) % 4;
        const dots = '.'.repeat(dotCount);
        if (loadingTextSpan) {
            const text = (action === 'login') ? 'Accesso' : 'Registrazione';
            loadingTextSpan.textContent = `${text}${dots}`;
        }
    }, 400);

    const startTime = Date.now();
    let fetchResult = null;

    const formData = new FormData(form);
    formData.append('action', action);

    try {
        const result = await apiCall(endpoint, formData);

        // Se la chiamata ha successo, memorizzo il risultato
        fetchResult = { success: true, data: result };

    } catch (error) {
        // Se c'è un errore, memorizzo il messaggio
        fetchResult = { success: false, message: error.message };
    } finally {
        const elapsedTime = Date.now() - startTime;
        const minDuration = 1000; // 1 secondo
        const remainingTime = Math.max(0, minDuration - elapsedTime);

        setTimeout(() => {
            clearInterval(loadingInterval);
            submitButton.classList.remove('is-loading');

            if (fetchResult && fetchResult.success) {
                // Se tutto è andato bene, eseguo la funzione di successo personalizzata
                if (onSuccessCallback) {
                    onSuccessCallback(fetchResult.data);
                }
            } else if (fetchResult) {
                // Altrimenti, mostro l'errore e riabilito il pulsante
                showFeedback(fetchResult.message, 'error', feedbackDiv.id);
                submitButton.disabled = false;
            } else {
                // Fallback nel caso improbabile che non ci sia un risultato
                showFeedback('Errore imprevisto.', 'error', feedbackDiv.id);
                submitButton.disabled = false;
            }
        }, remainingTime);
    }
}

/**
 * Mostra un messaggio di feedback all'utente.
 * @param {string} message - Il messaggio da visualizzare.
 * @param {string} type - Il tipo di messaggio ('success', 'error', o 'info').
 * @param {string} elementId - L'ID dell'elemento dove mostrare il messaggio.
 */
function showFeedback(message, type, elementId = 'feedbackMessage') {
    const feedbackDiv = document.getElementById(elementId);
    if (feedbackDiv) {
        feedbackDiv.textContent = message;
        feedbackDiv.className = `feedback-message ${type}`;
    }
}
