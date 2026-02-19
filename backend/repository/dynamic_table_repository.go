package repository

import (
	"gorm.io/gorm"
)

type DynamicTableRepository struct {
	db *gorm.DB
}

func NewDynamicTableRepository(db *gorm.DB) *DynamicTableRepository {
	return &DynamicTableRepository{db: db}
}

// SelectWithJoin executes a raw SELECT query with JOINs and returns results
func (r *DynamicTableRepository) SelectWithJoin(query string) ([]map[string]interface{}, error) {
	var results []map[string]interface{}
	err := r.db.Raw(query).Scan(&results).Error
	return results, err
}

// InsertBatch inserts multiple rows into a table
func (r *DynamicTableRepository) InsertBatch(tableName string, columns []string, values [][]interface{}) error {
	if len(values) == 0 {
		return nil
	}

	// Build placeholders for each row
	placeholders := make([]string, len(values))
	args := make([]interface{}, 0, len(values)*len(columns))

	for i, row := range values {
		placeholders[i] = "(?" + ",?"[1:len(columns)*2-1] + ")"
		for _, val := range row {
			args = append(args, val)
		}
	}

	// Build column list
	columnList := "`" + columns[0] + "`"
	for _, col := range columns[1:] {
		columnList += ",`" + col + "`"
	}

	sql := "INSERT INTO `" + tableName + "` (" + columnList + ") VALUES " +
		placeholders[0]
	for _, ph := range placeholders[1:] {
		sql += ", " + ph
	}

	return r.db.Exec(sql, args...).Error
}

// GetColumns retrieves column names for a table
func (r *DynamicTableRepository) GetColumns(tableName string) ([]string, error) {
	var columns []string
	rows, err := r.db.Raw("SHOW COLUMNS FROM `" + tableName + "`").Rows()
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	for rows.Next() {
		var field, colType, null, key, defaultVal, extra string
		if err := rows.Scan(&field, &colType, &null, &key, &defaultVal, &extra); err != nil {
			return nil, err
		}
		columns = append(columns, field)
	}
	return columns, nil
}
