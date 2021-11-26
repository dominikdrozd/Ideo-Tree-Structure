const toggleButtons = document.querySelectorAll('.toggle');

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
