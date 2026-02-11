package main

import (
	"dataImportDashboard/config"
	"dataImportDashboard/handlers"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"dataImportDashboard/routes"
	"fmt"
	"log"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("=========================================")
	fmt.Println("Data Import Dashboard - Backend Server")
	fmt.Println("=========================================")
	fmt.Println()

	// Load configuration
	cfg, err := config.LoadConfig()
	if err != nil {
		log.Fatal("Failed to load configuration:", err)
	}

	log.Printf("Configuration loaded successfully")
	log.Printf("Server Port: %s", cfg.Port)
	log.Printf("Database Type: %s", cfg.DBType)
	log.Printf("Database Host: %s:%s", cfg.DBHost, cfg.DBPort)
	fmt.Println()

	// Initialize database connection
	fmt.Println("Connecting to database...")
	if err := config.InitDB(cfg); err != nil {
		fmt.Println()
		fmt.Println("╔════════════════════════════════════════════════════════════╗")
		fmt.Println("║  DATABASE CONNECTION FAILED                                ║")
		fmt.Println("╚════════════════════════════════════════════════════════════╝")
		fmt.Printf("Error: %v\n", err)
		fmt.Println()
		fmt.Println("Please ensure:")
		fmt.Println("  1. Database server is running")
		fmt.Println("  2. Database credentials in .env are correct")
		fmt.Printf("     - DB_HOST=%s\n", cfg.DBHost)
		fmt.Printf("     - DB_PORT=%s\n", cfg.DBPort)
		fmt.Printf("     - DB_USER=%s\n", cfg.DBUser)
		fmt.Printf("     - DB_NAME=%s\n", cfg.DBName)
		fmt.Println("  3. Database 'data_import_db' exists")
		fmt.Println("  4. User has proper permissions")
		fmt.Println()
		fmt.Println("To create the database, run:")
		if cfg.DBType == "mysql" {
			fmt.Println("  mysql -u root -p -e \"CREATE DATABASE data_import_db;\"")
		} else {
			fmt.Println("  psql -U postgres -c \"CREATE DATABASE data_import_db;\"")
		}
		fmt.Println()
		log.Fatal("Exiting due to database connection failure")
	}

	// Auto-migrate database schemas
	db := config.GetDB()
	fmt.Println("Running database migrations...")
	if err := db.AutoMigrate(
		&models.DataRecord{},
		&models.Document{},
		&models.ImportLog{},
	); err != nil {
		log.Fatal("Failed to migrate database:", err)
	}

	log.Println("✓ Database migration completed successfully")
	fmt.Println()

	// Initialize repositories
	dataRecordRepo := repository.NewDataRecordRepository(db)
	documentRepo := repository.NewDocumentRepository(db)
	importLogRepo := repository.NewImportLogRepository(db)

	// Initialize handlers
	dataRecordHandler := handlers.NewDataRecordHandler(dataRecordRepo)
	documentHandler := handlers.NewDocumentHandler(documentRepo)
	importHandler := handlers.NewImportHandler(dataRecordRepo, importLogRepo)
	exportHandler := handlers.NewExportHandler(dataRecordRepo)

	// Set Gin mode
	if cfg.Environment == "production" {
		gin.SetMode(gin.ReleaseMode)
	}

	// Initialize Gin engine
	engine := gin.Default()

	// Setup routes
	router := routes.NewRouter(
		dataRecordHandler,
		documentHandler,
		importHandler,
		exportHandler,
	)
	router.Setup(engine, cfg.AllowedOrigins)

	// Start server
	addr := fmt.Sprintf(":%s", cfg.Port)
	fmt.Println("=========================================")
	fmt.Printf("✓ Server is ready and running!\n")
	fmt.Println("=========================================")
	fmt.Printf("  URL:         http://localhost:%s\n", cfg.Port)
	fmt.Printf("  Health:      http://localhost:%s/health\n", cfg.Port)
	fmt.Printf("  API Docs:    See API_DOCUMENTATION.md\n")
	fmt.Printf("  Environment: %s\n", cfg.Environment)
	fmt.Println("=========================================")
	fmt.Println()
	log.Printf("Press Ctrl+C to stop the server")

	if err := engine.Run(addr); err != nil {
		log.Fatal("Failed to start server:", err)
	}
}
