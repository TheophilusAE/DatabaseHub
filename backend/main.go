package main

import (
	"context"
	"dataImportDashboard/config"
	"dataImportDashboard/handlers"
	"dataImportDashboard/models"
	"dataImportDashboard/repository"
	"dataImportDashboard/routes"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("=========================================")
	fmt.Println("DataBridge - Backend Server")
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
		&models.User{},
		&models.DataRecord{},
		&models.Document{},
		&models.DocumentCategory{},
		&models.ImportLog{},
		&models.DatabaseConnectionConfig{},
		&models.TableConfig{},
		&models.TableJoin{},
		&models.ImportMapping{},
		&models.ExportConfig{},
		&models.UserTablePermission{},
	); err != nil {
		log.Fatal("Failed to migrate database:", err)
	}

	log.Println("✓ Database migration completed successfully")
	fmt.Println()

	// Initialize multi-database manager
	dbManager := config.InitMultiDatabaseManager()

	// Add default connection
	defaultConn := &config.DatabaseConnection{
		Name:     "default",
		Type:     cfg.DBType,
		Host:     cfg.DBHost,
		Port:     cfg.DBPort,
		User:     cfg.DBUser,
		Password: cfg.DBPassword,
		DBName:   cfg.DBName,
	}
	if err := dbManager.AddConnection(defaultConn); err != nil {
		log.Printf("Warning: Failed to add default connection to multi-database manager: %v", err)
	}

	// Initialize repositories
	userRepo := repository.NewUserRepository(db)
	dataRecordRepo := repository.NewDataRecordRepository(db)
	documentRepo := repository.NewDocumentRepository(db)
	documentCategoryRepo := repository.NewDocumentCategoryRepository(db)
	if err := documentCategoryRepo.EnsureDefaults(); err != nil {
		log.Fatalf("Failed to initialize document categories: %v", err)
	}
	importLogRepo := repository.NewImportLogRepository(db)
	tableConfigRepo := repository.NewTableConfigRepository(db)
	tableJoinRepo := repository.NewTableJoinRepository(db)
	importMappingRepo := repository.NewImportMappingRepository(db)
	exportConfigRepo := repository.NewExportConfigRepository(db)
	userTablePermissionRepo := repository.NewUserTablePermissionRepository(db)
	databaseConnectionRepo := repository.NewDatabaseConnectionRepository(db)

	if err := databaseConnectionRepo.Upsert(defaultConn); err != nil {
		log.Printf("Warning: Failed to persist default database connection: %v", err)
	}

	persistedConnections, err := databaseConnectionRepo.FindAllActive()
	if err != nil {
		log.Printf("Warning: Failed to load persisted database connections: %v", err)
	} else {
		for _, persisted := range persistedConnections {
			if persisted.Name == "default" {
				continue
			}

			conn := &config.DatabaseConnection{
				Name:     persisted.Name,
				Type:     persisted.Type,
				Host:     persisted.Host,
				Port:     persisted.Port,
				User:     persisted.User,
				Password: persisted.Password,
				DBName:   persisted.DBName,
				SSLMode:  persisted.SSLMode,
			}

			if addErr := dbManager.AddConnection(conn); addErr != nil {
				log.Printf("Warning: Failed to restore persisted connection '%s': %v", persisted.Name, addErr)
			}
		}
	}

	// Initialize handlers
	authHandler := handlers.NewAuthHandler(userRepo)
	userHandler := handlers.NewUserHandler(userRepo)
	dataRecordHandler := handlers.NewDataRecordHandler(dataRecordRepo)
	documentHandler := handlers.NewDocumentHandler(documentRepo)
	documentCategoryHandler := handlers.NewDocumentCategoryHandler(documentCategoryRepo)
	importHandler := handlers.NewImportHandler(dataRecordRepo, importLogRepo)
	exportHandler := handlers.NewExportHandler(dataRecordRepo)

	// Initialize multi-table handlers
	dbConfigHandler := handlers.NewDatabaseConfigHandler(dbManager, databaseConnectionRepo)
	tableConfigHandler := handlers.NewTableConfigHandler(tableConfigRepo, tableJoinRepo, userTablePermissionRepo)
	multiTableImportHandler := handlers.NewMultiTableImportHandler(tableConfigRepo, importMappingRepo, importLogRepo, dbManager)
	multiTableExportHandler := handlers.NewMultiTableExportHandler(tableConfigRepo, tableJoinRepo, exportConfigRepo, dbManager)
	simpleMultiTableHandler := handlers.NewSimpleMultiTableHandler(db, dbManager, userTablePermissionRepo, tableConfigRepo)
	userTablePermissionHandler := handlers.NewUserTablePermissionHandler(userTablePermissionRepo, tableConfigRepo, dbManager)
	databaseDiscoveryHandler := handlers.NewDatabaseDiscoveryHandler(dbManager, tableConfigRepo)

	// Initialize unified export/import handler
	unifiedExportImportHandler := handlers.NewUnifiedExportImportHandler(db)

	// Set Gin mode
	if cfg.Environment == "production" {
		gin.SetMode(gin.ReleaseMode)
	}

	// Initialize Gin engine with increased limits
	engine := gin.Default()

	// Configure max multipart memory (for file uploads)
	engine.MaxMultipartMemory = 500 << 20 // 500MB

	// Setup routes
	router := routes.NewRouter(
		dataRecordHandler,
		documentHandler,
		documentCategoryHandler,
		importHandler,
		exportHandler,
		authHandler,
		userHandler,
		dbConfigHandler,
		tableConfigHandler,
		multiTableImportHandler,
		multiTableExportHandler,
		simpleMultiTableHandler,
		userTablePermissionHandler,
		databaseDiscoveryHandler,
		unifiedExportImportHandler,
	)
	router.Setup(engine, cfg.AllowedOrigins)

	// Create HTTP server with extended timeouts for large uploads
	addr := fmt.Sprintf(":%s", cfg.Port)
	srv := &http.Server{
		Addr:           addr,
		Handler:        engine,
		ReadTimeout:    15 * time.Minute, // Extended for very large file uploads
		WriteTimeout:   15 * time.Minute, // Extended for large data processing
		IdleTimeout:    300 * time.Second,
		MaxHeaderBytes: 2 << 20, // 2 MB
	}

	// Start server
	fmt.Println("=========================================")
	fmt.Printf("✓ Server is ready and running!\n")
	fmt.Println("=========================================")
	fmt.Printf("  URL:         http://localhost:%s\n", cfg.Port)
	fmt.Printf("  Health:      http://localhost:%s/health\n", cfg.Port)
	fmt.Printf("  API Docs:    See API_DOCUMENTATION.md\n")
	fmt.Printf("  Environment: %s\n", cfg.Environment)
	fmt.Printf("  Upload Limit: 500MB\n")
	fmt.Printf("  Timeout:     15 minutes\n")
	fmt.Println("=========================================")
	fmt.Println()
	log.Printf("Press Ctrl+C to stop the server")

	// Graceful shutdown handling
	go func() {
		if err := srv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("Failed to start server: %v", err)
		}
	}()

	// Wait for interrupt signal
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	log.Println("Shutting down server...")

	// Graceful shutdown with timeout
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()
	if err := srv.Shutdown(ctx); err != nil {
		log.Printf("Server forced to shutdown: %v", err)
	}

	log.Println("Server stopped gracefully")
}
