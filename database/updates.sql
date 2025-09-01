-- Add missing columns to jobs table
ALTER TABLE jobs
    ADD COLUMN IF NOT EXISTS salary_range VARCHAR(100) AFTER salary_currency,
    ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) AFTER company_id,
    ADD COLUMN IF NOT EXISTS company_logo VARCHAR(255) AFTER company_name,
    ADD COLUMN IF NOT EXISTS company_website VARCHAR(255) AFTER company_logo,
    ADD COLUMN IF NOT EXISTS company_email VARCHAR(255) AFTER company_website,
    ADD COLUMN IF NOT EXISTS company_description TEXT AFTER company_email,
    ADD COLUMN IF NOT EXISTS education_experience TEXT AFTER requirements,
    ADD COLUMN IF NOT EXISTS posting_date DATE AFTER vacancy,
    ADD COLUMN IF NOT EXISTS job_nature VARCHAR(100) AFTER job_type,
    ADD COLUMN IF NOT EXISTS job_link VARCHAR(255) AFTER benefits;

-- Add indexes for improved performance
CREATE INDEX IF NOT EXISTS idx_job_title ON jobs(title);
CREATE INDEX IF NOT EXISTS idx_company_email ON companies(email);
CREATE INDEX IF NOT EXISTS idx_job_deadline ON jobs(deadline);
CREATE INDEX IF NOT EXISTS idx_job_salary ON jobs(salary_min, salary_max);

-- Add default values for status enums
ALTER TABLE jobs 
    MODIFY COLUMN status ENUM('draft', 'published', 'expired', 'filled', 'active', 'inactive') DEFAULT 'published';

-- Update companies table columns to match form fields
ALTER TABLE companies
    CHANGE COLUMN website company_website VARCHAR(255),
    CHANGE COLUMN email company_email VARCHAR(255) NOT NULL,
    CHANGE COLUMN phone company_phone VARCHAR(50),
    CHANGE COLUMN address company_address TEXT,
    CHANGE COLUMN description company_description TEXT,
    ADD COLUMN IF NOT EXISTS company_size VARCHAR(50) AFTER industry;

-- Add unique constraint for company email if not exists
ALTER TABLE companies
    ADD UNIQUE INDEX IF NOT EXISTS idx_unique_company_email (company_email);

-- Add column for storing user's last login
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL AFTER status;

-- Create table for job categories if not exists
CREATE TABLE IF NOT EXISTS job_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add category relationship to jobs if not exists
ALTER TABLE jobs
    ADD COLUMN IF NOT EXISTS category_id INT AFTER company_id,
    ADD FOREIGN KEY IF NOT EXISTS fk_job_category (category_id) REFERENCES job_categories(id) ON DELETE SET NULL;

-- Create table for contact form submissions if not exists
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add missing blog tables
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INT,
    author_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create table for newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add sample job categories
INSERT IGNORE INTO job_categories (name, slug, description) VALUES
('Information Technology', 'it', 'IT and software development jobs'),
('Sales & Marketing', 'sales-marketing', 'Sales, marketing, and advertising positions'),
('Healthcare', 'healthcare', 'Medical and healthcare related positions'),
('Education', 'education', 'Teaching and educational positions'),
('Finance', 'finance', 'Finance, banking, and accounting jobs'),
('Engineering', 'engineering', 'Engineering and technical positions'),
('Administrative', 'administrative', 'Administrative and clerical positions'),
('Creative', 'creative', 'Design, writing, and creative positions');

-- Add sample blog categories
INSERT IGNORE INTO blog_categories (name, slug, description) VALUES
('Career Advice', 'career-advice', 'Tips and guidance for job seekers'),
('Industry News', 'industry-news', 'Latest updates from various industries'),
('Job Search Tips', 'job-search-tips', 'How to find and apply for jobs effectively'),
('Interview Tips', 'interview-tips', 'Preparation and guidance for job interviews');
