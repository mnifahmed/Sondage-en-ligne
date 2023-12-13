function setupPollModificationPage() {
    const pollSelectElement = document.getElementById('poll');
    const titleContainerElement = document.getElementById('title-container');
    const titleInputElement = document.getElementById('title');
    const ordreInputElement = document.getElementById('ordre');
    const submitInputElement = document.getElementById('valider');

    function handlePollChange() {
        const showTitle = pollSelectElement.value !== '';

        titleContainerElement.style.display = showTitle ? 'block' : 'none';
        titleInputElement.hidden = !showTitle;
        if (ordreInputElement) {
            ordreInputElement.hidden = !showTitle;
        }
        if (submitInputElement) {
            submitInputElement.hidden = false;
        }
        

        const chooseOption = pollSelectElement.querySelector('option[value=""]');
        if (!showTitle && chooseOption) {
            submitInputElement.hidden = true;
        }
    }

    pollSelectElement.addEventListener('change', handlePollChange);
}

// Call the setupPollModificationPage function when the DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    setupPollModificationPage();
});

function handlePollChange() {
    // Empty the question and order fields
    document.getElementById('title').value = '';
    document.getElementById('ordre').value = '';
}