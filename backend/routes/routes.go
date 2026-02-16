package routes

import (
	"dataImportDashboard/handlers"
	"dataImportDashboard/middleware"

	"github.com/gin-gonic/gin"
)

type Router struct {
	dataRecordHandler          *handlers.DataRecordHandler
	documentHandler            *handlers.DocumentHandler
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
}

func NewRouter(
	dataRecordHandler *handlers.DataRecordHandler,
	documentHandler *handlers.DocumentHandler,
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
) *Router {
	return &Router{
		dataRecordHandler:          dataRecordHandler,
		documentHandler:            documentHandler,
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

	// Multi-database management endpoints
	databases := engine.Group("/databases")
	{
		databases.POST("", r.dbConfigHandler.AddDatabaseConnection)      // POST /databases - Add new database connection
		databases.GET("", r.dbConfigHandler.ListDatabaseConnections)     // GET /databases - List all database connections
		databases.GET("/test", r.dbConfigHandler.TestDatabaseConnection) // GET /databases/test?name=db1 - Test connection
		databases.DELETE("", r.dbConfigHandler.RemoveDatabaseConnection) // DELETE /databases?name=db1 - Remove connection
	}

	// Table configuration management
	tables := engine.Group("/tables")
	{
		tables.POST("", r.tableConfigHandler.CreateTableConfig)       // POST /tables - Create table configuration
		tables.GET("", r.tableConfigHandler.ListTableConfigs)         // GET /tables - List all table configurations
		tables.GET("/:id", r.tableConfigHandler.GetTableConfig)       // GET /tables/1 - Get specific table config
		tables.PUT("/:id", r.tableConfigHandler.UpdateTableConfig)    // PUT /tables/1 - Update table config
		tables.DELETE("/:id", r.tableConfigHandler.DeleteTableConfig) // DELETE /tables/1 - Delete table config
	}

	// Table join configuration management
	joins := engine.Group("/joins")
	{
		joins.POST("", r.tableConfigHandler.CreateTableJoin)       // POST /joins - Create table join
		joins.GET("", r.tableConfigHandler.ListTableJoins)         // GET /joins - List all table joins
		joins.GET("/:id", r.tableConfigHandler.GetTableJoin)       // GET /joins/1 - Get specific join
		joins.PUT("/:id", r.tableConfigHandler.UpdateTableJoin)    // PUT /joins/1 - Update join
		joins.DELETE("/:id", r.tableConfigHandler.DeleteTableJoin) // DELETE /joins/1 - Delete join
	}

	// Multi-table import endpoints
	multiImport := engine.Group("/multi-import")
	{
		multiImport.POST("/table", r.multiTableImportHandler.ImportToTable)          // POST /multi-import/table - Import to configured table
		multiImport.GET("/mappings", r.multiTableImportHandler.ListImportMappings)   // GET /multi-import/mappings - List import mappings
		multiImport.POST("/mappings", r.multiTableImportHandler.CreateImportMapping) // POST /multi-import/mappings - Create mapping
	}

	// Multi-table export endpoints
	multiExport := engine.Group("/multi-export")
	{
		multiExport.GET("/table", r.multiTableExportHandler.ExportFromTable)                 // GET /multi-export/table?config_name=export1 - Export from table
		multiExport.GET("/join-to-table", r.multiTableExportHandler.ExportJoinedDataToTable) // GET /multi-export/join-to-table?join_name=join1 - Export joined data to table
		multiExport.GET("/configs", r.multiTableExportHandler.ListExportConfigs)             // GET /multi-export/configs - List export configs
		multiExport.POST("/configs", r.multiTableExportHandler.CreateExportConfig)           // POST /multi-export/configs - Create export config
	}

	// Simple multi-table operations (view, upload, export)
	simpleMulti := engine.Group("/simple-multi")
	{
		simpleMulti.GET("/tables", r.simpleMultiTableHandler.ListTables)                       // GET /simple-multi/tables - List all database tables
		simpleMulti.GET("/tables/:table", r.simpleMultiTableHandler.GetTableData)              // GET /simple-multi/tables/users?page=1&page_size=50 - View table data
		simpleMulti.GET("/tables/:table/columns", r.simpleMultiTableHandler.GetTableColumns)   // GET /simple-multi/tables/users/columns - Get table columns
		simpleMulti.POST("/upload-multiple", r.simpleMultiTableHandler.UploadToMultipleTables) // POST /simple-multi/upload-multiple - Upload to multiple tables

		// User table permissions management (admin only)
		permissions := engine.Group("/permissions")
		{
			permissions.GET("/users/:userId", r.userTablePermissionHandler.GetUserPermissions)                       // GET /permissions/users/1 - Get all permissions for a user
			permissions.GET("/users/:userId/tables", r.userTablePermissionHandler.GetAccessibleTables)               // GET /permissions/users/1/tables - Get accessible tables for a user
			permissions.POST("/assign", r.userTablePermissionHandler.AssignTablePermission)                          // POST /permissions/assign - Assign table permission to user
			permissions.POST("/bulk-assign", r.userTablePermissionHandler.BulkAssignTablePermissions)                // POST /permissions/bulk-assign - Bulk assign tables to user
			permissions.DELETE("/users/:userId/tables/:tableId", r.userTablePermissionHandler.RevokeTablePermission) // DELETE /permissions/users/1/tables/5 - Revoke specific table permission
			permissions.DELETE("/users/:userId/all", r.userTablePermissionHandler.RevokeAllUserPermissions)          // DELETE /permissions/users/1/all - Revoke all permissions for user
			permissions.GET("/check/:userId/:tableId", r.userTablePermissionHandler.CheckTableAccess)                // GET /permissions/check/1/5 - Check if user has access to table
		}
		simpleMulti.POST("/export-selected", r.simpleMultiTableHandler.ExportSelectedData) // POST /simple-multi/export-selected - Export selected data
	}
}
