package config

import (
	"fmt"
	"log"
	"os"

	"github.com/joho/godotenv"
)

type Config struct {
	Port           string
	Environment    string
	DBType         string
	DBHost         string
	DBPort         string
	DBUser         string
	DBPassword     string
	DBName         string
	UploadPath     string
	MaxUploadSize  int64
	AllowedOrigins string
	// Performance tuning for large files
	ImportWorkers    int   // Number of concurrent workers for import
	ImportBatchSize  int   // Batch size for database inserts
	ExportBatchSize  int   // Batch size for export queries
	StreamBufferSize int   // Buffer size for streaming operations (bytes)
	DBMaxOpenConns   int   // Maximum open database connections
	DBMaxIdleConns   int   // Maximum idle database connections
	ChunkSizeBytes   int64 // Chunk size for file operations
}

var AppConfig *Config

// LoadConfig loads environment variables and initializes the config
func LoadConfig() (*Config, error) {
	// Load .env file if it exists
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found, using environment variables")
	}

	config := &Config{
		Port:           getEnv("PORT", "8080"),
		Environment:    getEnv("ENV", "development"),
		DBType:         getEnv("DB_TYPE", "mysql"),
		DBHost:         getEnv("DB_HOST", "localhost"),
		DBPort:         getEnv("DB_PORT", "3306"),
		DBUser:         getEnv("DB_USER", "root"),
		DBPassword:     getEnv("DB_PASSWORD", ""),
		DBName:         getEnv("DB_NAME", "data_import_db"),
		UploadPath:     getEnv("UPLOAD_PATH", "./uploads"),
		MaxUploadSize:  1099511627776, // 1TB - theoretical max for large document handling
		AllowedOrigins: getEnv("ALLOWED_ORIGINS", "http://localhost,http://localhost:8000,http://127.0.0.1:8000"),
		// Performance configurations for handling 1 billion+ rows
		ImportWorkers:    32,                // Parallel workers for import processing
		ImportBatchSize:  50000,             // Optimized batch size for massive imports
		ExportBatchSize:  100000,            // Large batch reads for exports
		StreamBufferSize: 10 * 1024 * 1024,  // 10MB streaming buffer
		DBMaxOpenConns:   100,               // Max open database connections
		DBMaxIdleConns:   10,                // Max idle connections
		ChunkSizeBytes:   100 * 1024 * 1024, // 100MB chunks for file operations
	}

	AppConfig = config
	return config, nil
}

// GetDSN returns the database connection string
func (c *Config) GetDSN() string {
	switch c.DBType {
	case "postgres":
		return fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=%s sslmode=disable",
			c.DBHost, c.DBUser, c.DBPassword, c.DBName, c.DBPort)
	case "mysql":
		return fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
			c.DBUser, c.DBPassword, c.DBHost, c.DBPort, c.DBName)
	default:
		return ""
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
