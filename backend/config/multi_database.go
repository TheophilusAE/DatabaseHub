package config

import (
	"fmt"
	"log"
	"sync"
	"time"

	"gorm.io/driver/mysql"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

// DatabaseConnection represents a database connection configuration
type DatabaseConnection struct {
	Name       string
	Type       string // postgres, mysql, sqlite, sqlserver
	Host       string
	Port       string
	User       string
	Password   string
	DBName     string
	SSLMode    string
	Connection *gorm.DB
}

// MultiDatabaseManager manages multiple database connections
type MultiDatabaseManager struct {
	connections map[string]*DatabaseConnection
	mu          sync.RWMutex
}

var multiDBManager *MultiDatabaseManager

// InitMultiDatabaseManager initializes the multi-database manager
func InitMultiDatabaseManager() *MultiDatabaseManager {
	if multiDBManager == nil {
		multiDBManager = &MultiDatabaseManager{
			connections: make(map[string]*DatabaseConnection),
		}
	}
	return multiDBManager
}

// GetMultiDatabaseManager returns the singleton instance
func GetMultiDatabaseManager() *MultiDatabaseManager {
	if multiDBManager == nil {
		return InitMultiDatabaseManager()
	}
	return multiDBManager
}

// AddConnection adds a new database connection
func (m *MultiDatabaseManager) AddConnection(conn *DatabaseConnection) error {
	m.mu.Lock()
	defer m.mu.Unlock()

	var dialector gorm.Dialector
	var dsn string

	// Build DSN based on database type
	switch conn.Type {
	case "postgres":
		sslMode := conn.SSLMode
		if sslMode == "" {
			sslMode = "disable"
		}
		dsn = fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=%s sslmode=%s",
			conn.Host, conn.User, conn.Password, conn.DBName, conn.Port, sslMode)
		dialector = postgres.Open(dsn)

	case "mysql":
		if conn.Port == "" {
			conn.Port = "3306"
		}
		dsn = fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
			conn.User, conn.Password, conn.Host, conn.Port, conn.DBName)
		dialector = mysql.Open(dsn)

	default:
		return fmt.Errorf("unsupported database type: %s", conn.Type)
	}

	// Set logger level
	logLevel := logger.Silent
	if AppConfig != nil && AppConfig.Environment == "development" {
		logLevel = logger.Info
	}

	// Open connection
	db, err := gorm.Open(dialector, &gorm.Config{
		Logger:                 logger.Default.LogMode(logLevel),
		PrepareStmt:            true,
		SkipDefaultTransaction: true,
	})

	if err != nil {
		return fmt.Errorf("failed to connect to database %s: %v", conn.Name, err)
	}

	// Configure connection pool
	sqlDB, err := db.DB()
	if err != nil {
		return fmt.Errorf("failed to get database instance for %s: %v", conn.Name, err)
	}

	maxOpenConns := 100
	maxIdleConns := 10
	if AppConfig != nil {
		maxOpenConns = AppConfig.DBMaxOpenConns
		maxIdleConns = AppConfig.DBMaxIdleConns
	}

	sqlDB.SetMaxOpenConns(maxOpenConns)
	sqlDB.SetMaxIdleConns(maxIdleConns)
	sqlDB.SetConnMaxLifetime(time.Hour)
	sqlDB.SetConnMaxIdleTime(10 * time.Minute)

	conn.Connection = db
	m.connections[conn.Name] = conn

	log.Printf("Added database connection: %s (%s)", conn.Name, conn.Type)
	return nil
}

// GetConnection retrieves a database connection by name
func (m *MultiDatabaseManager) GetConnection(name string) (*gorm.DB, error) {
	m.mu.RLock()
	defer m.mu.RUnlock()

	conn, exists := m.connections[name]
	if !exists {
		return nil, fmt.Errorf("database connection '%s' not found", name)
	}

	return conn.Connection, nil
}

// GetConnectionInfo retrieves connection information (returns error if not found)
func (m *MultiDatabaseManager) GetConnectionInfo(name string) (*DatabaseConnection, error) {
	m.mu.RLock()
	defer m.mu.RUnlock()

	conn, exists := m.connections[name]
	if !exists {
		return nil, fmt.Errorf("database connection '%s' not found", name)
	}

	return conn, nil
}

// GetConnectionInfoSafe retrieves connection information (returns nil if not found, no error)
func (m *MultiDatabaseManager) GetConnectionInfoSafe(name string) *DatabaseConnection {
	m.mu.RLock()
	defer m.mu.RUnlock()

	conn, exists := m.connections[name]
	if !exists {
		return nil
	}

	return conn
}

// ListConnections returns all connection names
func (m *MultiDatabaseManager) ListConnections() []string {
	m.mu.RLock()
	defer m.mu.RUnlock()

	names := make([]string, 0, len(m.connections))
	for name := range m.connections {
		names = append(names, name)
	}
	return names
}

// ListConnectionDetails returns all connection details
func (m *MultiDatabaseManager) ListConnectionDetails() []*DatabaseConnection {
	m.mu.RLock()
	defer m.mu.RUnlock()

	connections := make([]*DatabaseConnection, 0, len(m.connections))
	for _, conn := range m.connections {
		// Don't expose password in the list
		sanitized := &DatabaseConnection{
			Name:     conn.Name,
			Type:     conn.Type,
			Host:     conn.Host,
			Port:     conn.Port,
			User:     conn.User,
			Password: "", // Don't expose password
			DBName:   conn.DBName,
			SSLMode:  conn.SSLMode,
		}
		connections = append(connections, sanitized)
	}
	return connections
}

// RemoveConnection removes a database connection
func (m *MultiDatabaseManager) RemoveConnection(name string) error {
	m.mu.Lock()
	defer m.mu.Unlock()

	conn, exists := m.connections[name]
	if !exists {
		return fmt.Errorf("database connection '%s' not found", name)
	}

	// Close the connection
	if conn.Connection != nil {
		sqlDB, err := conn.Connection.DB()
		if err == nil {
			sqlDB.Close()
		}
	}

	delete(m.connections, name)
	log.Printf("Removed database connection: %s", name)
	return nil
}

// TestConnection tests if a connection is alive
func (m *MultiDatabaseManager) TestConnection(name string) error {
	db, err := m.GetConnection(name)
	if err != nil {
		return err
	}

	sqlDB, err := db.DB()
	if err != nil {
		return err
	}

	return sqlDB.Ping()
}
