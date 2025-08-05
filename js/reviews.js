document.addEventListener('DOMContentLoaded', () => {
    const reviewForm = document.getElementById('reviewForm');
    const reviewsContainer = document.querySelector('.reviews-container');

    if(reviewForm){
        reviewForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = reviewForm.querySelector('button[type="submit"]');
            const feedBackDiv = document.getElementById('reviewFeedback');

            submitButton.disabled = true;
            feedBackDiv.textContent = 'Invio in corso...';
            feedBackDiv.className = 'feedback-message';

            const formData = new FormData();
            //aggiunta recensione
            formData.append('action', 'create');

            try {
                const response = await fetch('api/reviews.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if(!response.ok) {
                    throw new Error(result.message);

                }
                feedBackDiv.textContent = result.message;
                feedBackDiv.className = 'feedback-message success';
                addNewReview(formData);
                reviewForm.reset();
            } catch (error) {
                feedBackDiv.textContent = error.message;
                feedBackDiv.className = 'feedback-message error';
            } finally {
                submitButton.disabled = false;
            }
        });
    }

    //modifica/elimina recensione
    if(reviewsContainer){
        reviewsContainer.addEventListener('click', async (event) => {
            const target = event.target;
            //eliminazione
            if(target.classList.contains('btn-delete-review')){
                const reviewId = target.dataset.reviewId;
                const reviewCard = target.closest('.review-card');
                if(confirm('Sei sicuro di voler eliminare questa recensione?')) {
                    await ReviewActions('delete', {review_id: reviewId});
                }
            }
            if(target.classList.contains('btn-edit-review')){
                const reviewCard = target.closest('.review-card');
                showEditForm(reviewCard);
            }
        });
    }

    function showEditForm(reviewCard) {
        const ratingDiv = reviewCard.querySelector('.review-rating');
        const bodyDiv = reviewCard.querySelector('.review-body');
        const actionsDiv = reviewCard.querySelector('.review-actions');

        const currentRating = ratingDiv.dataset.currentRating;
        const currentText = bodyDiv.querySelector('p').innerText;

        ratingDiv.style.display = 'none';
        bodyDiv.style.display = 'none';
        actionsDiv.style.display = 'none';

        const editForm = document.createElement('form');
        editForm.className = 'edit-review-form';
        
        //valutazioni
        const ratingGroup = document.createElement('div');
        ratingGroup.className = 'form-group';
        const ratingLabel = document.createElement('label');
        ratingLabel.textContent = 'Nuova Valutazione:';
        const ratingSelect = document.createElement('select');
        ratingSelect.required = true;
        const ratings = {5: 'Eccellente', 4: 'Buono', 3: 'Discreto', 2: 'Scarso', 1: 'Pessimo'};

        for(const value in ratings) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent =`${value} - ${ratings[value]}`;
            if(value == currentRating) {
                option.selected = true;
            }
            ratingSelect.appendChild(option);
        }
        ratingGroup.appendChild(ratingLabel);
        ratingGroup.appendChild(ratingSelect);
        editForm.appendChild(ratingGroup);

        //testo
        const textGroup = document.createElement('div');
        textGroup.className = 'form-group';
        const textLabel = document.createElement('label');
        const textArea = document.createElement('textarea');
        textArea.name = 'testo_recensione';
        textArea.required = true;
        textArea.rows = 4;
        textArea.textContent = currentText;
        textGroup.appendChild(textLabel);
        textGroup.appendChild(textArea);
        editForm.appendChild(textGroup);

        //azioni
        const actionsGroup = document.createElement('div');
        actionsGroup.className = 'edit-actions';
        const saveButton = document.createElement('button');
        saveButton.type = 'submit';
        saveButton.className = 'button-primary';
        saveButton.textContent = 'Salva';
        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'button-secondary btn-cancel-edit';
        cancelButton.textContent = 'Annulla';
        actionsGroup.appendChild(saveButton);
        actionsGroup.appendChild(cancelButton);
        editForm.appendChild(actionsGroup);

        reviewCard.appendChild(editForm);

        editForm.querySelector('.btn-cancel-edit').addEventListener('click', () => {
            editForm.remove();
            ratingDiv.style.display = 'block';
            bodyDiv.style.display = 'block';
            actionsDiv.style.display = 'block';
        });

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const reviewId = reviewCard.dataset.reviewId;
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('review_id', reviewId);

            try {
                const response = await fetch('api/reviews.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if(!response.ok) {
                    throw new Error(result.message);
                }
                const newRating = formData.get('valutazione');
                const newText = formData.get('testo_recensione');
                ratingDiv.dataset.currentRating = newRating;
                const strongTag = ratingDiv.querySelector('strong');
                strongTag.textContent = 'Valutazione';
                ratingDiv.innerHTML = '';
                ratingDiv.appendChild(strongTag);
                ratingDiv.appendChild(document.createTextNode(`: ${newRating}/5`));

                bodyDiv.querySelector('p').textContent = newText;

                editForm.remove();
                ratingDiv.style.display = 'block';
                bodyDiv.style.display = 'block';
                actionsDiv.style.display = 'block';
                alert(result.message);
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        });
    }

    async function ReviewActions(action, data, element) {
        const formData = new FormData();
        formData.append('action', action);
        for(const key in data) {
            formData.append(key, data[key]);
        }

        try {
            const response = await fetch('api/reviews.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if(!response.ok) {
                throw new Error(result.message);
            }

            if(action === 'delete') {
                element.remove();
            }
            alert(result.message);

        } catch (error) {
            alert('Errore: ' + error.message);
        }
    }  
    
    

    function addNewReview(formData) {
        const reviewsList = document.querySelector('.reviews-list');
        if(!reviewsList) return;
        
        const noReviewMessage = reviewsList.querySelector('p');
        if(noReviewMessage) {
            noReviewMessage.remove(); // rimuove il messaggio "Nessuna recensione"
        }

        const reviewCard = docuemtn.createElement('div');
        reviewCard.className = 'review-card';

        const rating = formData.get('valutazione');
        const reviewText = formData.get('testo_recensione');
        const username = 'Tu';
        const reviewDate = new Date().toLocaleDateString('it-IT');

        const header = document.createElement('div');
        header.className = 'review-header';
        const strongUser = document.createElement('strong');
        strongUser.textContent = username;
        const spanDate = docuemnt.createElement('span');
        spanDate.className = 'review-date'; 
        spanDate
        header.appendChild(strongUser);
        header.appendChild(spanDate);

        const ratingDiv = createElement('div');
        ratingDiv.className = 'review-rating';
        const strongRating = document.createElement('strong');
        strongRating.textContent = 'Valutazione:';
        ratingDiv.appendChild(strongRating);
        ratingDiv.appendChild(document.createTextNode(` ${rating}/5`));

        const bodyDiv = document.createElement('div');
        bodyDiv.className = 'review-body';
        bodyDiv.textContent = reviewText;
        reviewCard.appendChild(header);
        reviewCard.appendChild(ratingDiv);
        reviewCard.appendChild(bodyDiv);
        reviewsList.appendChild(reviewCard);
    }
});