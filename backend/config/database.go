package config

import (
	"fmt"
	"log"
	"time"

	"gorm.io/driver/mysql"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

var DB *gorm.DB

// InitDB initializes the database connection with optimized pool settings
func InitDB(config *Config) error {
	var err error
	var dialector gorm.Dialector

	dsn := config.GetDSN()

	switch config.DBType {
	case "postgres":
		dialector = postgres.Open(dsn)
	case "mysql":
		dialector = mysql.Open(dsn)
	default:
		return fmt.Errorf("unsupported database type: %s", config.DBType)
	}

	// Set logger level based on environment
	logLevel := logger.Silent
	if config.Environment == "development" {
		logLevel = logger.Info
	}

	DB, err = gorm.Open(dialector, &gorm.Config{
		Logger: logger.Default.LogMode(logLevel),
		// Optimize for bulk operations
		PrepareStmt:            true,                   // Cache prepared statements
		SkipDefaultTransaction: true,                   // Skip default transactions for better performance
		CreateBatchSize:        config.ImportBatchSize, // Default batch size
	})

	if err != nil {
		return fmt.Errorf("failed to connect to database: %v", err)
	}

	// Configure connection pool for handling massive concurrent operations
	sqlDB, err := DB.DB()
	if err != nil {
		return fmt.Errorf("failed to get database instance: %v", err)
	}

	// Set connection pool parameters optimized for large data operations
	sqlDB.SetMaxOpenConns(config.DBMaxOpenConns) // Maximum open connections
	sqlDB.SetMaxIdleConns(config.DBMaxIdleConns) // Maximum idle connections
	sqlDB.SetConnMaxLifetime(time.Hour)          // Connection max lifetime
	sqlDB.SetConnMaxIdleTime(10 * time.Minute)   // Idle connection timeout

	log.Printf("Database connection established with %d max connections", config.DBMaxOpenConns)
	return nil
}

// GetDB returns the database instance
func GetDB() *gorm.DB {
	return DB
}
