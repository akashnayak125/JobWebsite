$(document).ready(function() {
    // Initialize variables
    let jobDomains = {};
    const jobDomainInput = document.getElementById('job_domain');
    const domainList = document.getElementById('domainList');
    const domainSearchInput = document.getElementById('domainSearchInput');
    
    // Load domains when page loads
    loadJobDomains();
    
    // Modal button click handler
    $('#showDomainBtn').click(function() {
        if (Object.keys(jobDomains).length === 0) {
            loadJobDomains();
        }
        $('#jobDomainModal').modal('show');
    });

    // Domain search in modal
    $('#domainSearchInput').on('input', function() {
        populateDomainModal($(this).val());
    });

    // Load job domains
    function loadJobDomains() {
        $.ajax({
            url: 'get_job_domains.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    jobDomains = response.domains;
                    populateDomainModal();
                } else {
                    showNotification('danger', 'Error loading job domains');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading domains:', error);
                showNotification('danger', 'Error loading job domains');
            }
        });
    }

    // Populate domain modal
    function populateDomainModal(searchTerm = '') {
        domainList.innerHTML = '';
        
        Object.entries(jobDomains).forEach(([domain, categories]) => {
            // Filter categories if there's a search term
            if (searchTerm) {
                const searchLower = searchTerm.toLowerCase();
                categories = categories.filter(category => 
                    category.toLowerCase().includes(searchLower) ||
                    domain.toLowerCase().includes(searchLower)
                );
                if (categories.length === 0) return;
            }

            const domainCol = document.createElement('div');
            domainCol.className = 'col-md-6 mb-4';
            
            const domainCard = document.createElement('div');
            domainCard.className = 'card h-100';
            
            const cardHeader = document.createElement('div');
            cardHeader.className = 'card-header';
            cardHeader.innerHTML = `<h6 class="mb-0 text-primary"><i class="fas fa-folder me-2"></i>${domain}</h6>`;
            
            const cardBody = document.createElement('div');
            cardBody.className = 'card-body p-0';
            
            const categoryList = document.createElement('div');
            categoryList.className = 'list-group list-group-flush';
            
            categories.forEach(category => {
                const categoryItem = document.createElement('a');
                categoryItem.href = '#';
                categoryItem.className = 'list-group-item list-group-item-action';
                categoryItem.innerHTML = `<i class="fas fa-tag me-2 text-secondary"></i>${category}`;
                categoryItem.onclick = (e) => {
                    e.preventDefault();
                    selectJobDomain(category);
                };
                categoryList.appendChild(categoryItem);
            });
            
            cardBody.appendChild(categoryList);
            domainCard.appendChild(cardHeader);
            domainCard.appendChild(cardBody);
            domainCol.appendChild(domainCard);
            domainList.appendChild(domainCol);
        });

        if (domainList.children.length === 0) {
            domainList.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-search fa-2x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No domains found matching your search.</p>
                </div>
            `;
        }
    }

    function selectJobDomain(domain) {
        jobDomainInput.value = domain;
        $('#jobDomainModal').modal('hide');
    }

    function showNotification(type, message) {
        const notification = $('#notification');
        notification.removeClass().addClass(`alert alert-${type}`);
        $('#notification-message').text(message);
        notification.show();
        
        setTimeout(() => {
            notification.hide();
        }, 5000);
    }
});
