function QuestionModification() {
    const pollSelectElement = document.getElementById('poll');
    const questionSelectElement = document.getElementById('question');
    const questionContainer = document.getElementById('question-container');
    const titleContainer = document.getElementById('title-container');
    const modifyButton = document.getElementById('modifier');
    const titleInput = document.getElementById('title');
    const ordreInput = document.getElementById('ordre');

    // Store the placeholder option
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = '-- Choisir une question --';

    pollSelectElement.addEventListener('change', function () {
        resetFields();
        const pollId = this.value;

        questionSelectElement.innerHTML = '';
        questionContainer.style.display = pollId ? 'block' : 'none';
        titleContainer.style.display = 'none';
        modifyButton.style.display = 'none';

        if (pollId) {
            fetch(`/poll/${pollId}/questions`)
                .then(response => response.json())
                .then(questions => {
                    questionSelectElement.appendChild(placeholderOption);

                    if (questions.length > 0) {
                        questions.forEach(question => {
                            const option = document.createElement('option');
                            option.value = question.id;
                            option.textContent = question.title;
                            questionSelectElement.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error fetching questions:', error));
        }
    });

    questionSelectElement.addEventListener('change', function () {
        resetFields();
        if (this.value) {
            titleContainer.style.display = 'block';
            modifyButton.style.display = 'block';
        } else {
            titleContainer.style.display = 'none';
            modifyButton.style.display = 'none';
        }
    });

    function resetFields() {
        titleInput.value = '';
        ordreInput.value = '';
        modifyButton.disabled = true;
    }

    window.checkFields = function () {
        var titleValue = titleInput.value;
        var ordreValue = ordreInput.value;

        // Enable the submit button only if both title and ordre are not empty
        if (titleValue.trim() !== '' || ordreValue.trim() !== '') {
            modifyButton.disabled = false;
        } else {
            modifyButton.disabled = true;
        }
    }
}

// Call the function to set up the event listeners
QuestionModification();