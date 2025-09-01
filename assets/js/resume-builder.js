$(document).ready(function() {
    let currentStep = 1;
    let selectedSector = '';
    let experienceLevel = '';
    let resumeData = {};

    function goToStep(step) {
        $('.step-container').removeClass('active');
        $('.step-item').removeClass('active');

        if (step === 1) {
            $('#sectorStep').addClass('active');
            $('.step-item[data-step="1"]').addClass('active');
        } else if (step === 2) {
            $('#experienceStep').addClass('active');
            $('.step-item[data-step="2"]').addClass('active');
        } else if (step === 3) {
            $('#detailsStep').addClass('active');
            $('.step-item[data-step="3"]').addClass('active');
            if (experienceLevel === 'experienced') {
                $('#experienceForm').show();
            } else {
                $('#experienceForm').hide();
            }
            getAIKeywords();
        } else if (step === 4) {
            $('#templateStep').addClass('active');
            $('.step-item[data-step="4"]').addClass('active');
            generateResumeTemplates();
        }
        currentStep = step;
    }

    $('.sector-card').on('click', function() {
        selectedSector = $(this).data('sector');
        $('.sector-card').removeClass('selected');
        $(this).addClass('selected');
        goToStep(2);
    });

    $('.experience-card').on('click', function() {
        experienceLevel = $(this).data('experience');
        $('.experience-card').removeClass('selected');
        $(this).addClass('selected');
        goToStep(3);
    });

    $('.btn-prev').on('click', function() {
        goToStep(currentStep - 1);
    });
    
    $('#detailsStep .btn-prev').on('click', function() {
        goToStep(2);
    });

    $('#templateStep .btn-prev').on('click', function() {
        goToStep(3);
    });

    $('#includePhoto').on('change', function() {
        if ($(this).is(':checked')) {
            $('#photoSection').show();
        } else {
            $('#photoSection').hide();
        }
    });

    $('#photoInput').on('change', function(event) {
        const [file] = this.files;
        if (file) {
            $('#photoPreview').attr('src', URL.createObjectURL(file)).show();
        }
    });

    function addDynamicSection(container, template) {
        const content = $(template).html();
        $(container).append(content);
    }

    $('#addExperience').on('click', () => addDynamicSection('#experienceContainer', '#experienceTemplate'));
    $('#addEducation').on('click', () => addDynamicSection('#educationContainer', '#educationTemplate'));

    $(document).on('click', '.remove-section', function() {
        $(this).closest('.form-section').remove();
    });

    $('#resumeForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            resumeData = $(this).serializeObject();
            goToStep(4);
        } else {
            $(this).addClass('was-validated');
        }
    });

    function getAIKeywords() {
        const keywords = {
            it: ['Agile', 'DevOps', 'Cloud Computing', 'Cybersecurity', 'Machine Learning', 'Data Structures'],
            marketing: ['SEO', 'SEM', 'Content Marketing', 'Social Media Marketing', 'Email Marketing', 'Google Analytics'],
            finance: ['Financial Modeling', 'Risk Management', 'Investment Banking', 'Corporate Finance', 'Accounting'],
            healthcare: ['Patient Care', 'EMR', 'HIPAA', 'Medical Terminology', 'Clinical Research'],
            engineering: ['AutoCAD', 'Project Management', 'MATLAB', 'Quality Assurance', 'Prototyping'],
            education: ['Curriculum Development', 'Classroom Management', 'E-Learning', 'Special Education']
        };
        const sectorKeywords = keywords[selectedSector] || [];
        $('#aiKeywords').empty();
        sectorKeywords.forEach(k => {
            $('#aiKeywords').append(`<span class="keyword-pill">${k}</span>`);
        });
    }

    $(document).on('click', '.keyword-pill', function() {
        const skillsTextarea = $('textarea[name="skills"]');
        const currentSkills = skillsTextarea.val();
        const newSkill = $(this).text();
        skillsTextarea.val(currentSkills ? `${currentSkills}, ${newSkill}` : newSkill);
    });

    function generateResumeTemplates() {
        const templateGrid = $('.template-grid');
        templateGrid.empty();
        for (let i = 1; i <= 12; i++) {
            const template = `
                <div class="template-card" data-template="template${i}">
                    <div class="template-preview">Template ${i}</div>
                    <div class="template-info">
                        <h4>Template ${i}</h4>
                        <div class="template-actions">
                            <button class="btn head-btn2 btn-sm btn-preview">Preview</button>
                            <button class="btn head-btn1 btn-sm btn-use">Use & Download</button>
                        </div>
                    </div>
                </div>`;
            templateGrid.append(template);
        }
    }

    $(document).on('click', '.btn-preview', function() {
        const templateId = $(this).closest('.template-card').data('template');
        const previewContent = generatePreviewContent(templateId);
        $('#resumePreview').html(previewContent);
        $('#previewModal').modal('show');
    });
    
    $(document).on('click', '.btn-use', function() {
        const templateId = $(this).closest('.template-card').data('template');
        const previewContent = generatePreviewContent(templateId);
        $('#resumePreview').html(previewContent);
        $('#previewModal').modal('show');
        // In a real app, this would trigger a PDF download
    });

    function generatePreviewContent(templateId) {
        // This is a simplified preview. A real implementation would use a library like jsPDF or a server-side solution.
        let content = `<h1>${resumeData.firstName} ${resumeData.lastName}</h1>`;
        content += `<p>${resumeData.email} | ${resumeData.phone}</p>`;
        
        content += `<h2>Skills</h2><p>${resumeData.skills}</p>`;
        
        // Add more sections based on resumeData
        
        return `<div class="p-4 border"><h3>${templateId}</h3>${content}</div>`;
    }

    // Helper to serialize form to object
    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    
    // Initialize first step
    goToStep(1);
});
