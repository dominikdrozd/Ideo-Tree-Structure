const toggleButtons = document.querySelectorAll('.toggle');
const toggleAllButton = document.querySelector('.toggle-all');

toggleAllButton.addEventListener('click', ev => {
    const nodeElements = document.querySelectorAll('.hidden');
    nodeElements.forEach(el => {
        el.classList.remove('hidden');
    });
    toggleButtons.forEach(el => {
        el.innerHTML = '-';
    });
});

toggleButtons.forEach(el => {
    el.addEventListener('click', ev => {
        const nodeId = el.dataset.toggle;
        const nodeElements = document.querySelectorAll('.node-' + nodeId);
        if(el.innerHTML == '+') {
            nodeElements.forEach(nodeElement => {
                nodeElement.classList.remove('hidden');
                el.innerHTML = '-';
            });
        } else {
            nodeElements.forEach(nodeElement => {
                nodeElement.classList.add('hidden');
                el.innerHTML = '+';
            });
        }
    });
});
