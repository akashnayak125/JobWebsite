<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$jobDomains = [
    'Technology' => [
        'Software Development',
        'Data Science & Analytics',
        'Cybersecurity',
        'Cloud Computing',
        'IT Support & Help Desk',
        'Network Administration',
        'DevOps Engineering',
        'AI & Machine Learning',
        'UI/UX Design',
        'Database Administration'
    ],
    'Finance & Business' => [
        'Accounting & Auditing',
        'Financial Analysis',
        'Investment Banking',
        'Human Resources (HR)',
        'Marketing & Sales',
        'Business Management',
        'Project Management'
    ],
    'Legal & Real Estate' => [
        'Law (Lawyer, Paralegal)',
        'Real Estate & Insurance'
    ],
    'Creative & Media' => [
        'Content Writing & Editing',
        'Graphic Design',
        'Journalism & Reporting',
        'Photography & Videography',
        'Animation & VFX',
        'Fashion Design',
        'Music Production',
        'Acting & Entertainment',
        'Public Relations (PR)'
    ],
    'Healthcare & Medical' => [
        'Doctor (Physician, Surgeon)',
        'Nursing',
        'Pharmacy',
        'Physical Therapy',
        'Medical Research',
        'Dentistry',
        'Veterinary Medicine',
        'Medical Technology',
        'Mental Health Counseling'
    ],
    'Construction & Trades' => [
        'Construction Management',
        'Electrician',
        'Plumbing & Carpentry',
        'Welding',
        'HVAC Technician',
        'Architecture',
        'Civil Engineering',
        'Heavy Equipment Operation'
    ],
    'Science & Research' => [
        'Biotechnology',
        'Environmental Science',
        'Chemistry & Physics',
        'Geology',
        'Academic Research',
        'Pharmaceutical R&D',
        'Astronomy'
    ],
    'Education & Training' => [
        'Teaching',
        'School Administration',
        'Librarianship',
        'Social Work',
        'Career Counseling',
        'Corporate Training',
        'Early Childhood Education'
    ],
    'Hospitality & Services' => [
        'Hotel Management',
        'Culinary Arts (Chef)',
        'Event Planning',
        'Travel & Tourism',
        'Restaurant Management',
        'Customer Service'
    ],
    'Engineering & Manufacturing' => [
        'Mechanical Engineering',
        'Electrical Engineering',
        'Chemical Engineering',
        'Aerospace Engineering',
        'Manufacturing & Production',
        'Quality Assurance',
        'Industrial Design'
    ],
    'Logistics & Transportation' => [
        'Supply Chain Management',
        'Logistics Coordination',
        'Aviation (Pilot, ATC)',
        'Public Transportation',
        'Trucking and Shipping',
        'Warehouse Management'
    ],
    'Others' => [
        'Other Professions',
        'Miscellaneous Jobs',
        'General Services',
        'Specialized Services'
    ]
];

try {
    $search = isset($_GET['term']) ? trim($_GET['term']) : '';
    
    if (empty($search)) {
        // Return all domains and categories
        echo json_encode(['status' => 'success', 'domains' => $jobDomains]);
        exit;
    }
    
    // Search through domains and categories
    $results = [];
    foreach ($jobDomains as $domain => $categories) {
        $matchingCategories = array_filter($categories, function($category) use ($search) {
            return stripos($category, $search) !== false;
        });
        
        if (!empty($matchingCategories)) {
            $results[$domain] = array_values($matchingCategories);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'domains' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching job domains'
    ]);
}
