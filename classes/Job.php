<?php
class Job {
    private $conn;
    private $cache = [];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get job by its slug
     * @param string $slug
     * @return array|false
     * @throws PDOException
     */
    public function getJobBySlug($slug) {
        // Check cache first
        if (isset($this->cache[$slug])) {
            return $this->cache[$slug];
        }
        $query = "SELECT 
                    j.*, 
                    c.company_name,
                    c.company_logo,
                    c.company_website,
                    c.company_email,
                    c.company_description
                FROM 
                    jobs j
                LEFT JOIN 
                    companies c ON j.company_id = c.id
                WHERE 
                    j.slug = :slug
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createSlug($title) {
        // Convert the title to lowercase
        $slug = strtolower($title);
        
        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);
        
        // Remove any characters that aren't letters, numbers, or hyphens
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Add a random string to make the slug unique
        $random = substr(md5(rand()), 0, 6);
        $slug = $slug . '-' . $random;
        
        return $slug;
    }
}
?>
