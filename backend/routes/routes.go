package routes

import (
	"dataImportDashboard/handlers"
	"dataImportDashboard/middleware"

	"github.com/gin-gonic/gin"
)

type Router struct {
	dataRecordHandler          *handlers.DataRecordHandler
	documentHandler            *handlers.DocumentHandler
	documentCategoryHandler    *handlers.DocumentCategoryHandler
	importHandler              *handlers.ImportHandler
	exportHandler              *handlers.ExportHandler
	authHandler                *handlers.AuthHandler
	userHandler                *handlers.UserHandler
	dbConfigHandler            *handlers.DatabaseConfigHandler
	tableConfigHandler         *handlers.TableConfigHandler
	multiTableImportHandler    *handlers.MultiTableImportHandler
	multiTableExportHandler    *handlers.MultiTableExportHandler
	simpleMultiTableHandler    *handlers.SimpleMultiTableHandler
	userTablePermissionHandler *handlers.UserTablePermissionHandler
	databaseDiscoveryHandler   *handlers.DatabaseDiscoveryHandler
	unifiedExportImportHandler *handlers.UnifiedExportImportHandler
}

func NewRouter(
	dataRecordHandler *handlers.DataRecordHandler,
	documentHandler *handlers.DocumentHandler,
	documentCategoryHandler *handlers.DocumentCategoryHandler,
	importHandler *handlers.ImportHandler,
	exportHandler *handlers.ExportHandler,
	authHandler *handlers.AuthHandler,
	userHandler *handlers.UserHandler,
	dbConfigHandler *handlers.DatabaseConfigHandler,
	tableConfigHandler *handlers.TableConfigHandler,
	multiTableImportHandler *handlers.MultiTableImportHandler,
	multiTableExportHandler *handlers.MultiTableExportHandler,
	simpleMultiTableHandler *handlers.SimpleMultiTableHandler,
	userTablePermissionHandler *handlers.UserTablePermissionHandler,
	databaseDiscoveryHandler *handlers.DatabaseDiscoveryHandler,
	unifiedExportImportHandler *handlers.UnifiedExportImportHandler,
) *Router {
	return &Router{
		dataRecordHandler:          dataRecordHandler,
		documentHandler:            documentHandler,
		documentCategoryHandler:    documentCategoryHandler,
		importHandler:              importHandler,
		exportHandler:              exportHandler,
		authHandler:                authHandler,
		userHandler:                userHandler,
		dbConfigHandler:            dbConfigHandler,
		tableConfigHandler:         tableConfigHandler,
		multiTableImportHandler:    multiTableImportHandler,
		multiTableExportHandler:    multiTableExportHandler,
		simpleMultiTableHandler:    simpleMultiTableHandler,
		userTablePermissionHandler: userTablePermissionHandler,
		databaseDiscoveryHandler:   databaseDiscoveryHandler,
		unifiedExportImportHandler: unifiedExportImportHandler,
	}
}

func (r *Router) Setup(engine *gin.Engine, allowedOrigins string) {
	// Apply CORS middleware
	engine.Use(middleware.CORSMiddleware(allowedOrigins))

	// Health check endpoint
	engine.GET("/health", func(c *gin.Context) {
		c.JSON(200, gin.H{"status": "ok", "message": "Server is running"})
	})

	// Human-readable routes - Simple and intuitive

	// Authentication endpoints (for Laravel frontend)
	auth := engine.Group("/auth")
	{
		auth.POST("/login", r.authHandler.Login)         // POST /auth/login - User login
		auth.POST("/register", r.authHandler.Register)   // POST /auth/register - User registration
		auth.POST("/logout", r.authHandler.Logout)       // POST /auth/logout - User logout
		auth.GET("/verify", r.authHandler.VerifySession) // GET /auth/verify?user_id=1 - Verify session
	}

	// User management endpoints
	users := engine.Group("/users")
	{
		users.GET("", r.userHandler.GetAll)         // GET /users - List all users (with pagination)
		users.GET("/stats", r.userHandler.GetStats) // GET /users/stats - Get user statistics
		users.GET("/:id", r.userHandler.GetByID)    // GET /users/1 - Get specific user
		users.POST("", r.userHandler.Create)        // POST /users - Create new user
		users.PUT("/:id", r.userHandler.Update)     // PUT /users/1 - Update user
		users.DELETE("/:id", r.userHandler.Delete)  // DELETE /users/1 - Delete user
	}

	// Data records management
	data := engine.Group("/data")
	{
		data.GET("", r.dataRecordHandler.GetAll)                           // GET /data - List all data records
		data.GET("/:id", r.dataRecordHandler.GetByID)                      // GET /data/1 - Get specific record
		data.POST("", r.dataRecordHandler.Create)                          // POST /data - Create new record
		data.PUT("/:id", r.dataRecordHandler.Update)                       // PUT /data/1 - Update record
		data.DELETE("/:id", r.dataRecordHandler.Delete)                    // DELETE /data/1 - Delete record
		data.GET("/category/:category", r.dataRecordHandler.GetByCategory) // GET /data/category/electronics
	}

	// Document/file management
	documents := engine.Group("/documents")
	{
		documents.GET("", r.documentHandler.GetAll)                           // GET /documents - List all documents
		documents.GET("/:id", r.documentHandler.GetByID)                      // GET /documents/1 - Get document info
		documents.POST("", r.documentHandler.Upload)                          // POST /documents - Upload new document
		documents.GET("/:id/download", r.documentHandler.Download)            // GET /documents/1/download - Download file
		documents.DELETE("/:id", r.documentHandler.Delete)                    // DELETE /documents/1 - Delete document
		documents.GET("/category/:category", r.documentHandler.GetByCategory) // GET /documents/category/reports
	}

	// Document categories management
	documentCategories := engine.Group("/document-categories")
	{
		documentCategories.GET("", r.documentCategoryHandler.GetAll)                                // GET /document-categories
		documentCategories.POST("", middleware.AdminOnly(), r.documentCategoryHandler.Create)       // POST /document-categories (ADMIN ONLY)
		documentCategories.PUT("/:id", middleware.AdminOnly(), r.documentCategoryHandler.Update)    // PUT /document-categories/1 (ADMIN ONLY)
		documentCategories.DELETE("/:id", middleware.AdminOnly(), r.documentCategoryHandler.Delete) // DELETE /document-categories/1 (ADMIN ONLY)
	}

	// Upload/import data from files
	upload := engine.Group("/upload")
	{
		upload.POST("/csv", r.importHandler.ImportCSV)               // POST /upload/csv - Import CSV file
		upload.POST("/json", r.importHandler.ImportJSON)             // POST /upload/json - Import JSON file
		upload.GET("/history", r.importHandler.GetImportLogs)        // GET /upload/history - View upload history
		upload.GET("/history/:id", r.importHandler.GetImportLogByID) // GET /upload/history/1 - Get specific log
	}

	// Download/export data
	download := engine.Group("/download")
	{
		download.GET("/csv", r.exportHandler.ExportCSV)     // GET /download/csv - Export to CSV
		download.GET("/json", r.exportHandler.ExportJSON)   // GET /download/json - Export to JSON
		download.GET("/excel", r.exportHandler.ExportExcel) // GET /download/excel - Export to Excel
	}

	// Multi-database management endpoints (ADMIN ONLY)
	databases := engine.Group("/databases")
	databases.Use(middleware.AdminOnly()) // Restrict to admins only
	{
		databases.POST("", r.dbConfigHandler.AddDatabaseConnection)      // POST /databases - Add new database connection
		databases.GET("", r.dbConfigHandler.ListDatabaseConnections)     // GET /databases - List all database connections
		databases.GET("/test", r.dbConfigHandler.TestDatabaseConnection) // GET /databases/test?name=db1 - Test connection
		databases.DELETE("", r.dbConfigHandler.RemoveDatabaseConnection) // DELETE /databases?name=db1 - Remove connection
	}

	// Database discovery endpoints for auto-sync (ADMIN ONLY)
	discovery := engine.Group("/discovery")
	discovery.Use(middleware.AdminOnly()) // Restrict to admins only
	{
		discovery.GET("/databases", r.databaseDiscoveryHandler.ListDatabases) // GET /discovery/databases - List available databases
		discovery.GET("/tables", r.databaseDiscoveryHandler.DiscoverTables)   // GET /discovery/tables?database=db1 - Discover tables
		discovery.POST("/sync", r.databaseDiscoveryHandler.SyncTables)        // POST /discovery/sync - Sync tables to config
	}

	// Table configuration management (ADMIN ONLY for modifications)
	tables := engine.Group("/tables")
	{
		tables.GET("", r.tableConfigHandler.ListTableConfigs)                                 // GET /tables - List all table configurations
		tables.GET("/:id", r.tableConfigHandler.GetTableConfig)                               // GET /tables/1 - Get specific table config
		tables.POST("", middleware.AdminOnly(), r.tableConfigHandler.CreateTableConfig)       // POST /tables - Create table configuration (ADMIN ONLY)
		tables.PUT("/:id", middleware.AdminOnly(), r.tableConfigHandler.UpdateTableConfig)    // PUT /tables/1 - Update table config (ADMIN ONLY)
		tables.DELETE("/:id", middleware.AdminOnly(), r.tableConfigHandler.DeleteTableConfig) // DELETE /tables/1 - Delete table config (ADMIN ONLY)
	}

	// Table join configuration management (ADMIN ONLY for modifications)
	joins := engine.Group("/joins")
	{
		joins.GET("", r.tableConfigHandler.ListTableJoins)                                 // GET /joins - List all table joins
		joins.GET("/:id", r.tableConfigHandler.GetTableJoin)                               // GET /joins/1 - Get specific join
		joins.POST("", middleware.AdminOnly(), r.tableConfigHandler.CreateTableJoin)       // POST /joins - Create table join (ADMIN ONLY)
		joins.PUT("/:id", middleware.AdminOnly(), r.tableConfigHandler.UpdateTableJoin)    // PUT /joins/1 - Update join (ADMIN ONLY)
		joins.DELETE("/:id", middleware.AdminOnly(), r.tableConfigHandler.DeleteTableJoin) // DELETE /joins/1 - Delete join (ADMIN ONLY)
	}

	// Multi-table import endpoints
	multiImport := engine.Group("/multi-import")
	{
		multiImport.POST("/table", r.multiTableImportHandler.ImportToTable)                // POST /multi-import/table - Import to configured table
		multiImport.GET("/mappings", r.multiTableImportHandler.ListImportMappings)         // GET /multi-import/mappings - List import mappings
		multiImport.POST("/mappings", r.multiTableImportHandler.CreateImportMapping)       // POST /multi-import/mappings - Create mapping
		multiImport.PUT("/mappings/:id", r.multiTableImportHandler.UpdateImportMapping)    // PUT /multi-import/mappings/1
		multiImport.DELETE("/mappings/:id", r.multiTableImportHandler.DeleteImportMapping) // DELETE /multi-import/mappings/1
	}

	// Multi-table export endpoints
	multiExport := engine.Group("/multi-export")
	{
		multiExport.GET("/table", r.multiTableExportHandler.ExportFromTable)                 // GET /multi-export/table?config_name=export1 - Export from table
		multiExport.GET("/join-to-table", r.multiTableExportHandler.ExportJoinedDataToTable) // GET /multi-export/join-to-table?join_name=join1 - Export joined data to table
		multiExport.GET("/configs", r.multiTableExportHandler.ListExportConfigs)             // GET /multi-export/configs - List export configs
		multiExport.POST("/configs", r.multiTableExportHandler.CreateExportConfig)           // POST /multi-export/configs - Create export config
		multiExport.PUT("/configs/:id", r.multiTableExportHandler.UpdateExportConfig)        // PUT /multi-export/configs/1 - Update export config
		multiExport.DELETE("/configs/:id", r.multiTableExportHandler.DeleteExportConfig)     // DELETE /multi-export/configs/1 - Delete export config
	}

	// Unified Export/Import - Simple, single endpoint for import and export
	unified := engine.Group("/unified")
	{
		unified.POST("/import", r.unifiedExportImportHandler.SimpleImport)  // POST /unified/import - Simple import to a table
		unified.POST("/export", r.unifiedExportImportHandler.UnifiedExport) // POST /unified/export - Unified export from multiple tables
		unified.GET("/export", r.unifiedExportImportHandler.SimpleExport)   // GET /unified/export?tables[]=users&tables[]=products&format=csv - Quick export
	}

	// Simple multi-table operations (view, upload, export)
	// Simple multi-table operations - NOW WITH AUTH
	simpleMulti := engine.Group("/simple-multi")
	simpleMulti.Use(middleware.AuthRequired()) // ← Add this line
	{
		simpleMulti.GET("/tables", r.simpleMultiTableHandler.ListTables)
		simpleMulti.GET("/tables/:table", r.simpleMultiTableHandler.GetTableData)
		simpleMulti.GET("/tables/:table/columns", r.simpleMultiTableHandler.GetTableColumns)
		simpleMulti.POST("/tables/:table/rows", r.simpleMultiTableHandler.CreateTableRow)
		simpleMulti.PUT("/tables/:table/rows", r.simpleMultiTableHandler.UpdateTableRow)
		simpleMulti.DELETE("/tables/:table/rows", r.simpleMultiTableHandler.DeleteTableRow)
		simpleMulti.POST("/upload-multiple", r.simpleMultiTableHandler.UploadToMultipleTables)
		simpleMulti.POST("/export-selected", r.simpleMultiTableHandler.ExportSelectedData)

		// Permissions stay admin-only (nested group inherits AuthRequired + adds AdminOnly)
		permissions := simpleMulti.Group("/permissions")
		permissions.Use(middleware.AdminOnly()) // ← Already in your code, keep it
		{
			permissions.GET("/users/:userId", r.userTablePermissionHandler.GetUserPermissions)
			permissions.GET("/users/:userId/tables", r.userTablePermissionHandler.GetAccessibleTables)
			permissions.POST("/assign", r.userTablePermissionHandler.AssignTablePermission)
			permissions.POST("/bulk-assign", r.userTablePermissionHandler.BulkAssignTablePermissions)
			permissions.DELETE("/users/:userId/tables/:tableId", r.userTablePermissionHandler.RevokeTablePermission)
			permissions.DELETE("/users/:userId/all", r.userTablePermissionHandler.RevokeAllUserPermissions)
			permissions.GET("/check/:userId/:tableId", r.userTablePermissionHandler.CheckTableAccess)
		}
	}
}
