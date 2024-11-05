#!/usr/bin/env bash

set -ex

cd /app

rm -rfv data/search
mkdir data/search
mkdir data/search/database
mkdir data/search/dump
mkdir data/search/render

#search-equal
php bin/console app:database-generate --type=search-equal --count=1000000 --databasePath=data/search/database/search-equal.sqlite --infoPath=data/search/database/search-equal.txt
sh bin/dump-index.sh data/search/database/search-equal.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-equal-index.txt
sh bin/dump-search.sh data/search/database/search-equal.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-equal.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-equal-index.txt --dumpSearchPath=data/search/dump/search-equal.txt --outputImagePath=data/search/render/search-equal.webp --outputInfoPath=data/search/render/search-equal.txt

#search-equal-1000
php bin/console app:database-generate --type=search-equal --count=1000 --databasePath=data/search/database/search-equal-1000.sqlite --infoPath=data/search/database/search-equal-1000.txt
sh bin/dump-index.sh data/search/database/search-equal-1000.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-equal-index-1000.txt
sh bin/dump-search.sh data/search/database/search-equal-1000.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-equal-1000.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-equal-index-1000.txt --dumpSearchPath=data/search/dump/search-equal-1000.txt --outputImagePath=data/search/render/search-equal-1000.webp --outputInfoPath=data/search/render/search-equal-1000.txt

#search-range
php bin/console app:database-generate --type=search-range --count=1000000 --databasePath=data/search/database/search-range.sqlite --infoPath=data/search/database/search-range.txt
sh bin/dump-index.sh data/search/database/search-range.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-range-index.txt
sh bin/dump-search.sh data/search/database/search-range.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 IN (1,1000000);" data/search/dump/search-range.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-range-index.txt --dumpSearchPath=data/search/dump/search-range.txt --outputImagePath=data/search/render/search-range-1.webp --traceKey=1 --outputInfoPath=data/search/render/search-range-1.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-range-index.txt --dumpSearchPath=data/search/dump/search-range.txt --outputImagePath=data/search/render/search-range-1000000.webp --traceKey=1000000 --outputInfoPath=data/search/render/search-range-1000000.txt

#search-order
php bin/console app:database-generate --type=search-order --count=1000000 --databasePath=data/search/database/search-order.sqlite --infoPath=data/search/database/search-order.txt
sh bin/dump-index.sh data/search/database/search-order.sqlite "SELECT * FROM table_test INDEXED BY idx_asc WHERE column1 = 1;" data/search/dump/search-order-asc-index.txt
sh bin/dump-search.sh data/search/database/search-order.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx_asc WHERE column1 IN (1,500000,1000000);" data/search/dump/search-order-asc.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-order-asc-index.txt --dumpSearchPath=data/search/dump/search-order-asc.txt --outputImagePath=data/search/render/search-order-asc.webp --outputInfoPath=data/search/render/search-order-asc.txt

sh bin/dump-index.sh data/search/database/search-order.sqlite "SELECT * FROM table_test INDEXED BY idx_desc WHERE column1 = 1;" data/search/dump/search-order-desc-index.txt
sh bin/dump-search.sh data/search/database/search-order.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx_desc WHERE column1 IN (1,500000,1000000);" data/search/dump/search-order-desc.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-order-desc-index.txt --dumpSearchPath=data/search/dump/search-order-desc.txt --outputImagePath=data/search/render/search-order-desc.webp --outputInfoPath=data/search/render/search-order-desc.txt

#search-greater-than
php bin/console app:database-generate --type=search-greater-than --count=1000000 --databasePath=data/search/database/search-greater-than.sqlite --infoPath=data/search/database/search-greater-than.txt
sh bin/dump-index.sh data/search/database/search-greater-than.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-greater-than-index.txt
sh bin/dump-search.sh data/search/database/search-greater-than.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 >= 500000 LIMIT 5;" data/search/dump/search-greater-than.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-greater-than-index.txt --dumpSearchPath=data/search/dump/search-greater-than.txt --outputImagePath=data/search/render/search-greater-than.webp --outputInfoPath=data/search/render/search-greater-than.txt

#search-expression
php bin/console app:database-generate --type=search-expression --count=1000000 --databasePath=data/search/database/search-expression.sqlite --infoPath=data/search/database/search-expression.txt
sh bin/dump-index.sh data/search/database/search-expression.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE strftime('%Y-%m-%d %H:%M:%S',json_extract(column1, '$.timestamp'), 'unixepoch') = '1970-01-01 00:00:01';" data/search/dump/search-expression-index.txt
sh bin/dump-search.sh data/search/database/search-expression.sqlite "SELECT rowId, strftime('%Y-%m-%d %H:%M:%S',json_extract(column1, '$.timestamp'), 'unixepoch') AS date FROM table_test INDEXED BY idx WHERE strftime('%Y-%m-%d %H:%M:%S',json_extract(column1, '$.timestamp'), 'unixepoch') = '1970-01-01 00:00:01';" data/search/dump/search-expression.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-expression-index.txt --dumpSearchPath=data/search/dump/search-expression.txt --outputImagePath=data/search/render/search-expression.webp --outputInfoPath=data/search/render/search-expression.txt

#search-unique
php bin/console app:database-generate --type=search-unique --count=1000000 --databasePath=data/search/database/search-unique.sqlite --infoPath=data/search/database/search-unique.txt
sh bin/dump-index.sh data/search/database/search-unique.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-unique-index.txt
sh bin/dump-search.sh data/search/database/search-unique.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-unique.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-unique-index.txt --dumpSearchPath=data/search/dump/search-unique.txt --outputImagePath=data/search/render/search-unique.webp --outputInfoPath=data/search/render/search-unique.txt

#search-partial
php bin/console app:database-generate --type=search-partial --count=1000000 --databasePath=data/search/database/search-partial.sqlite --infoPath=data/search/database/search-partial.txt
sh bin/dump-index.sh data/search/database/search-partial.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-partial-index.txt
sh bin/dump-search.sh data/search/database/search-partial.sqlite "SELECT rowId, column1 FROM table_test INDEXED BY idx WHERE column1 = 1;" data/search/dump/search-partial.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-partial-index.txt --dumpSearchPath=data/search/dump/search-partial.txt --outputImagePath=data/search/render/search-partial.webp --outputInfoPath=data/search/render/search-partial.txt

#search-complex-equal
php bin/console app:database-generate --type=search-complex-equal --count=1000000 --databasePath=data/search/database/search-complex-equal.sqlite --infoPath=data/search/database/search-complex-equal.txt
sh bin/dump-index.sh data/search/database/search-complex-equal.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-equal-index.txt
sh bin/dump-search.sh data/search/database/search-complex-equal.sqlite "SELECT rowId, column1, column2 FROM table_test INDEXED BY idx WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-equal.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-complex-equal-index.txt --dumpSearchPath=data/search/dump/search-complex-equal.txt --outputImagePath=data/search/render/search-complex-equal.webp --outputInfoPath=data/search/render/search-complex-equal.txt

#search-complex-cardinality-equal
php bin/console app:database-generate --type=search-complex-cardinality --count=1000000 --databasePath=data/search/database/search-complex-cardinality-equal.sqlite --infoPath=data/search/database/search-complex-cardinality-equal.txt
sh bin/dump-index.sh data/search/database/search-complex-cardinality-equal.sqlite "SELECT * FROM table_test INDEXED BY idx_column1_column2 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-column1-equal-index.txt
sh bin/dump-search.sh data/search/database/search-complex-cardinality-equal.sqlite "SELECT rowId, column1, column2 FROM table_test INDEXED BY idx_column1_column2 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-equal-column1.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-complex-cardinality-column1-equal-index.txt --dumpSearchPath=data/search/dump/search-complex-cardinality-equal-column1.txt --outputImagePath=data/search/render/search-complex-cardinality-equal-column1.webp --outputInfoPath=data/search/render/search-complex-cardinality-equal-column1.txt

sh bin/dump-index.sh data/search/database/search-complex-cardinality-equal.sqlite "SELECT * FROM table_test INDEXED BY idx_column2_column1 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-column2-equal-index.txt
sh bin/dump-search.sh data/search/database/search-complex-cardinality-equal.sqlite "SELECT rowId, column1 column2 FROM table_test INDEXED BY idx_column2_column1 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-equal-column2.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-complex-cardinality-column2-equal-index.txt --dumpSearchPath=data/search/dump/search-complex-cardinality-equal-column2.txt --outputImagePath=data/search/render/search-complex-cardinality-equal-column2.webp --outputInfoPath=data/search/render/search-complex-cardinality-equal-column2.txt

#search-complex-cardinality-greater-than
php bin/console app:database-generate --type=search-complex-cardinality --count=1000000 --databasePath=data/search/database/search-complex-cardinality-greater-than.sqlite --infoPath=data/search/database/search-complex-cardinality-greater-than.txt
sh bin/dump-index.sh data/search/database/search-complex-cardinality-greater-than.sqlite "SELECT * FROM table_test INDEXED BY idx_column1_column2 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-greater-than-column1-index.txt
sh bin/dump-search.sh data/search/database/search-complex-cardinality-greater-than.sqlite "SELECT rowId, column1, column2 FROM table_test INDEXED BY idx_column1_column2 WHERE column1 >= 500000 AND column2 = 2  LIMIT 10;" data/search/dump/search-complex-cardinality-greater-than-column1.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-complex-cardinality-greater-than-column1-index.txt --dumpSearchPath=data/search/dump/search-complex-cardinality-greater-than-column1.txt --outputImagePath=data/search/render/search-complex-cardinality-greater-than-column1.webp --outputInfoPath=data/search/render/search-complex-cardinality-greater-than-column1.txt

sh bin/dump-index.sh data/search/database/search-complex-cardinality-greater-than.sqlite "SELECT * FROM table_test INDEXED BY idx_column2_column1 WHERE column1 = 1 AND column2 = 1;" data/search/dump/search-complex-cardinality-greater-than-column2-index.txt
sh bin/dump-search.sh data/search/database/search-complex-cardinality-greater-than.sqlite "SELECT rowId, column1, column2 FROM table_test INDEXED BY idx_column2_column1 WHERE column1 >= 500000 AND column2 = 2 LIMIT 10;" data/search/dump/search-complex-cardinality-greater-than-column2.txt
php bin/console app:render-search --dumpIndexPath=data/search/dump/search-complex-cardinality-greater-than-column2-index.txt --dumpSearchPath=data/search/dump/search-complex-cardinality-greater-than-column2.txt --outputImagePath=data/search/render/search-complex-cardinality-greater-than-column2.webp --outputInfoPath=data/search/render/search-complex-cardinality-greater-than-column2.txt
