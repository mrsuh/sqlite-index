#!/usr/bin/env bash

sqlite3 "$1" "PRAGMA optimize"

echo "### QUERY" > "$3"
echo "$2" >> "$3"

echo "\n### SEEK COUNT" >> "$3"
sqlite3 "$1" "$2" ".testctrl seek_count" | tail -1 >> "$3"

echo "\n### TIMER" >> "$3"
echo ".timer on" > /tmp/timer.sql
echo "$2" >> /tmp/timer.sql
for i in 1 2 3 4 5 6 7 8 9 10
do
   sqlite3 "$1" < /tmp/timer.sql | tail -1 >> "$3"
done

echo "\n### EXPLAIN QUERY PLAN" >> "$3"
sqlite3 "$1" "EXPLAIN QUERY PLAN $2" | tail +2 >> "$3"

echo "\n### EXPLAIN QUERY" >> "$3"
sqlite3 "$1" "EXPLAIN $2" >> "$3"

echo "\n### SCANSTATS EST" >> "$3"
sqlite3 "$1" ".scanstats est" "$2" | tail -2 >> "$3"

echo "\n### SCANSTATS VM" >> "$3"
sqlite3 "$1" ".scanstats vm" "$2" >> "$3"

echo "\n### PRAGMA vdbe_addoptrace=ON;" >> "$3"
sqlite3 "$1" "PRAGMA vdbe_addoptrace=ON;" "$2" >> "$3"

echo "\n### PRAGMA vdbe_debug=ON;" >> "$3"
sqlite3 "$1" "PRAGMA vdbe_debug=ON;" "$2" >> "$3"

echo "\n### PRAGMA vdbe_listing=ON;" >> "$3"
sqlite3 "$1" "PRAGMA vdbe_listing=ON;" "$2" >> "$3"

echo "\n### PRAGMA vdbe_trace=ON;" >> "$3"
sqlite3 "$1" "PRAGMA vdbe_trace=ON;" "$2" >> "$3"

echo "\n### WHERETRACE" >> "$3"
sqlite3 "$1" ".wheretrace" "$2" >> "$3"

echo "\n### TREETRACE" >> "$3"
sqlite3 "$1" ".treetrace" "$2" >> "$3"

echo "\n### SEARCH DUMP" >> "$3"

export BTREE_INDEX_SEEK=1

sqlite3 "$1" "$2" >> "$3"

unset BTREE_INDEX_SEEK

echo "\n### RESULT" >> "$3"

sqlite3 "$1" ".mode column" ".headers on" "$2" >> "$3"
