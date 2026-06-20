import sqlite3
import mysql.connector

try:
    conn = mysql.connector.connect(host='127.0.0.1', user='root', password='', database='southmart_central')
    cursor = conn.cursor()
    cursor.execute("SELECT id, name FROM products WHERE name LIKE '%\\n%' OR name LIKE '%\\r%' OR name LIKE '%\"%' OR name LIKE '%''%'")
    rows = cursor.fetchall()
    if rows:
        print("Found products with problematic characters in name:")
        for r in rows:
            print(f"- ID {r[0]}: {repr(r[1])}")
    else:
        print("No problematic characters found.")
except Exception as e:
    print("DB error:", e)
