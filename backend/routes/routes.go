package routes

import (
	"dataImportDashboard/handlers"
	"dataImportDashboard/middleware"

	"github.com/gin-gonic/gin"
)

type Router struct {
	dataRecordHandler *handlers.DataRecordHandler
	documentHandler   *handlers.DocumentHandler
	importHandler     *handlers.ImportHandler
	exportHandler     *handlers.ExportHandler
}

func NewRouter(
	dataRecordHandler *handlers.DataRecordHandler,
	documentHandler *handlers.DocumentHandler,
	importHandler *handlers.ImportHandler,
	exportHandler *handlers.ExportHandler,
) *Router {
	return &Router{
		dataRecordHandler: dataRecordHandler,
		documentHandler:   documentHandler,
		importHandler:     importHandler,
		exportHandler:     exportHandler,
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
}
