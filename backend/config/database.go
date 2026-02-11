package config

import (
	"fmt"
	"log"

	"gorm.io/driver/mysql"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

var DB *gorm.DB

// InitDB initializes the database connection
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
	})

	if err != nil {
		return fmt.Errorf("failed to connect to database: %v", err)
	}

	log.Println("Database connection established successfully")
	return nil
}

// GetDB returns the database instance
func GetDB() *gorm.DB {
	return DB
}
