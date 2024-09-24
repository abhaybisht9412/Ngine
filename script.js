// document.getElementById('aws-btn').addEventListener('click', function() {
//     document.getElementById('service-list').classList.remove('hidden');
//     document.getElementById('aws-services').classList.remove('hidden');
//     document.getElementById('azure-services').classList.add('hidden');
//     document.getElementById('gcp-services').classList.add('hidden');
// });

document.getElementById('azure-btn').addEventListener('click', function() {
    document.getElementById('service-list').classList.remove('hidden');
    document.getElementById('azure-services').classList.remove('hidden');
    document.getElementById('aws-services').classList.add('hidden');
    document.getElementById('gcp-services').classList.add('hidden');
});

document.getElementById('gcp-btn').addEventListener('click', function() {
    document.getElementById('service-list').classList.remove('hidden');
    document.getElementById('gcp-services').classList.remove('hidden');
    document.getElementById('aws-services').classList.add('hidden');
    document.getElementById('azure-services').classList.add('hidden');
});

// Redirect to the service URL in a new tab when a service is clicked
document.querySelectorAll('.service-item').forEach(item => {
    item.addEventListener('click', function(event) {
        event.stopPropagation(); // Prevent event bubbling
        const serviceUrl = this.getAttribute('data-url');
        window.open(serviceUrl, '_blank'); // Open in a new tab
    });
});
