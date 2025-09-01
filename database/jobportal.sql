-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS jobportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jobportal;

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    company_logo VARCHAR(255),
    website VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    description TEXT,
    industry VARCHAR(100),
    company_size VARCHAR(50),
    founded_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    requirements TEXT,
    qualifications TEXT,
    responsibilities TEXT,
    benefits TEXT,
    location VARCHAR(255),
    job_type ENUM('Full Time', 'Part Time', 'Contract', 'Freelance', 'Internship') NOT NULL,
    experience_level VARCHAR(100),
    salary_min DECIMAL(12,2),
    salary_max DECIMAL(12,2),
    salary_currency VARCHAR(10) DEFAULT 'USD',
    is_remote BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'expired', 'filled') DEFAULT 'published',
    vacancy INT DEFAULT 1,
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    resume_path VARCHAR(255),
    profile_picture VARCHAR(255),
    role ENUM('user', 'admin', 'company') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create job_applications table
CREATE TABLE IF NOT EXISTS job_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume_path VARCHAR(255),
    status ENUM('pending', 'reviewed', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create job_skills table (for required skills in jobs)
CREATE TABLE IF NOT EXISTS job_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user_skills table (for user profile skills)
CREATE TABLE IF NOT EXISTS user_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    years_experience INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create saved_jobs table (for job bookmarks)
CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved_job (user_id, job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create job_alerts table
CREATE TABLE IF NOT EXISTS job_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    keywords VARCHAR(255),
    location VARCHAR(255),
    job_type VARCHAR(100),
    frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'weekly',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create site_settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, password, role) 
VALUES ('Admin', 'User', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert some default site settings
INSERT INTO site_settings (setting_name, setting_value) VALUES
('site_name', 'Job Portal'),
('site_email', 'contact@jobportal.com'),
('jobs_per_page', '10'),
('allow_job_alerts', 'true'),
('auto_expire_days', '30'),
('default_currency', 'USD'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_encryption', 'tls');

-- Create indexes for better performance
CREATE INDEX idx_job_status ON jobs(status);
CREATE INDEX idx_job_location ON jobs(location);
CREATE INDEX idx_job_type ON jobs(job_type);
CREATE INDEX idx_job_created ON jobs(created_at);
CREATE INDEX idx_company_name ON companies(company_name);
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_application_status ON job_applications(status);

-- Add sample industries for companies
INSERT INTO site_settings (setting_name, setting_value) VALUES
('industries', 'Technology,Healthcare,Finance,Education,Manufacturing,Retail,Marketing,Construction,Hospitality,Transportation');
