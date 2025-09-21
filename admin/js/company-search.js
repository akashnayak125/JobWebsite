$(document).ready(function() {
    const companySearch = document.getElementById('company_search');
    const searchResults = document.getElementById('company_search_results');
    let searchTimeout;

    // Company search input handler
    $('#company_search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length < 2) {
            $('#company_search_results').hide();
            return;
        }

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: 'search_companies_ajax.php',
                method: 'GET',
                data: { term: query },
                dataType: 'json',
                success: function(response) {
                    searchResults.innerHTML = '';
                    
                    if (response.error) {
                        searchResults.innerHTML = `<div class="p-3 text-danger">${response.error}</div>`;
                        $(searchResults).show();
                        return;
                    }
                    
                    if (!response.length) {
                        searchResults.innerHTML = `
                            <div class="p-3 text-center">
                                <i class="fas fa-info-circle text-muted"></i> No companies found
                            </div>`;
                        $(searchResults).show();
                        return;
                    }

                    response.forEach(item => {
                        const company = item.company;
                        const element = document.createElement('a');
                        element.className = 'dropdown-item';
                        element.href = '#';
                        element.innerHTML = `
                            <div class="company-suggestion">
                                <img src="../${company.company_logo || 'assets/img/company_logos/default.png'}" 
                                     alt="${company.company_name}" 
                                     class="company-logo-small"
                                     onerror="this.src='../assets/img/company_logos/default.png'">
                                <div>
                                    <strong>${company.company_name}</strong><br>
                                    <small class="text-muted">${company.industry || 'Industry not specified'}</small>
                                </div>
                            </div>
                        `;
                        element.addEventListener('click', (e) => {
                            e.preventDefault();
                            selectCompany(company);
                        });
                        searchResults.appendChild(element);
                    });
                    $(searchResults).show();
                },
                error: function(xhr, status, error) {
                    console.error('Error searching companies:', error);
                    searchResults.innerHTML = `
                        <div class="p-3 text-danger">
                            <i class="fas fa-exclamation-circle"></i> Error searching companies
                        </div>`;
                    $(searchResults).show();
                }
            });
        }, 300);
    });

    // Select company handler
    function selectCompany(company) {
        $('#company_id').val(company.id);
        $('#company_search').val(company.company_name);
        
        $('#selected_company_logo').attr('src', '../' + (company.company_logo || 'assets/img/company_logos/default.png'));
        $('#selected_company_name').text(company.company_name);
        $('#selected_company_industry').text(company.industry || 'Industry not specified');
        $('#selected_company_website').text(company.company_website || 'Not specified');
        $('#selected_company_email').text(company.company_email || 'Not specified');
        $('#selected_company_description').html(company.company_description || '');
        
        $('#company_details').show();
        $(searchResults).hide();
    }

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#company_search, #company_search_results').length) {
            $('#company_search_results').hide();
        }
    });
});
