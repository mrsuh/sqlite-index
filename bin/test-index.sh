#!/usr/bin/env bash

set -ex

cd /app

rm -rfv data/index
mkdir data/index
mkdir data/index/database
mkdir data/index/dump
mkdir data/index/render

# index-1
php bin/console app:database-generate --type=index --count=1 --databasePath=data/index/database/index-1.sqlite --infoPath=data/index/database/index-1.txt
sh bin/dump-index.sh data/index/database/index-1.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-1.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-1.txt --outputImagePath=data/index/render/index-1.webp

#index-1000
php bin/console app:database-generate --type=index --count=1000 --databasePath=data/index/database/index-1000.sqlite --infoPath=data/index/database/index-1000.txt
sh bin/dump-index.sh data/index/database/index-1000.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-1000.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-1000.txt --outputImagePath=data/index/render/index-1000.webp

#index-1000000
php bin/console app:database-generate --type=index --count=1000000 --databasePath=data/index/database/index-1000000.sqlite --infoPath=data/index/database/index-1000000.txt
sh bin/dump-index.sh data/index/database/index-1000000.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-1000000.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-1000000.txt --outputImagePath=data/index/render/index-1000000.webp

#index-order
php bin/console app:database-generate --type=index-order --count=1000000 --databasePath=data/index/database/index-order.sqlite --infoPath=data/index/database/index-order.txt
sh bin/dump-index.sh data/index/database/index-order.sqlite "SELECT * FROM table_test INDEXED BY idx_asc WHERE column1 = 1;" data/index/dump/index-order-asc.txt
sh bin/dump-index.sh data/index/database/index-order.sqlite "SELECT * FROM table_test INDEXED BY idx_desc WHERE column1 = 1;" data/index/dump/index-order-desc.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-order-asc.txt --outputImagePath=data/index/render/index-order-asc.webp
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-order-desc.txt --outputImagePath=data/index/render/index-order-desc.webp

#index-expression
php bin/console app:database-generate --type=index-expression --count=1000000 --databasePath=data/index/database/index-expression.sqlite --infoPath=data/index/database/index-expression.txt
sh bin/dump-index.sh data/index/database/index-expression.sqlite "SELECT * FROM  table_test INDEXED BY idx WHERE strftime('%Y-%m-%d %H:%M:%S', json_extract(column1, '$.timestamp'), 'unixepoch') = '1970-01-01 00:00:01';" data/index/dump/index-expression.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-expression.txt --outputImagePath=data/index/render/index-expression.webp

#index-unique
php bin/console app:database-generate --type=index-unique --count=1000000 --databasePath=data/index/database/index-unique.sqlite --infoPath=data/index/database/index-unique.txt
sh bin/dump-index.sh data/index/database/index-unique.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-unique.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-unique.txt --outputImagePath=data/index/render/index-unique.webp

#index-partial
php bin/console app:database-generate --type=index-partial --count=1000000 --databasePath=data/index/database/index-partial.sqlite --infoPath=data/index/database/index-partial.txt
sh bin/dump-index.sh data/index/database/index-partial.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-partial.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-partial.txt --outputImagePath=data/index/render/index-partial.webp

#index-complex
php bin/console app:database-generate --type=index-complex --count=1000000 --databasePath=data/index/database/index-complex.sqlite --infoPath=data/index/database/index-complex.txt
sh bin/dump-index.sh data/index/database/index-complex.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1 AND column2=1;" data/index/dump/index-complex.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-complex.txt --outputImagePath=data/index/render/index-complex.webp

#index-time
php bin/console app:database-generate --type=index-time --count=1000000 --databasePath=data/index/database/index-time.sqlite --infoPath=data/index/database/index-time.txt
sh bin/dump-index.sh data/index/database/index-time.sqlite "SELECT * FROM table_test INDEXED BY idx_before WHERE column1 = 1;" data/index/dump/index-time-before.txt
sh bin/dump-index.sh data/index/database/index-time.sqlite "SELECT * FROM table_test INDEXED BY idx_after WHERE column1 = 1;" data/index/dump/index-time-after.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-time-before.txt --outputImagePath=data/index/render/index-time-before.webp
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-time-after.txt --outputImagePath=data/index/render/index-time-after.webp
php bin/console app:diff-index --dumpIndexPath=data/index/dump/index-time-before.txt  --dumpIndexPath=data/index/dump/index-time-after.txt --outputDiffPath=data/index/render/index-time.txt

#index-vacuum
php bin/console app:database-generate --type=index-vacuum --count=1000000 --databasePath=data/index/database/index-vacuum.sqlite --infoPath=data/index/database/index-vacuum.txt
sh bin/dump-index.sh data/index/database/index-vacuum.sqlite "SELECT * FROM table_test INDEXED BY idx_before WHERE column1 = 1;" data/index/dump/index-vacuum-before.txt
sqlite3 data/index/database/index-vacuum.sqlite "VACUUM;"
sh bin/dump-index.sh data/index/database/index-vacuum.sqlite "SELECT * FROM table_test INDEXED BY idx_before WHERE column1 = 1;" data/index/dump/index-vacuum-after.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-vacuum-before.txt --outputImagePath=data/index/render/index-vacuum-before.webp
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-vacuum-after.txt --outputImagePath=data/index/render/index-vacuum-after.webp
php bin/console app:diff-index --dumpIndexPath=data/index/dump/index-time-before.txt  --dumpIndexPath=data/index/dump/index-time-after.txt --outputDiffPath=data/index/render/index-vacuum.txt

#index-reindex
php bin/console app:database-generate --type=index-reindex --count=1000000 --databasePath=data/index/database/index-reindex.sqlite --infoPath=data/index/database/index-reindex.txt
sh bin/dump-index.sh data/index/database/index-reindex.sqlite "SELECT * FROM table_test INDEXED BY idx_before WHERE column1 = 1;" data/index/dump/index-reindex-before.txt
sqlite3 data/index/database/index-reindex.sqlite "REINDEX idx_before;"
sh bin/dump-index.sh data/index/database/index-reindex.sqlite "SELECT * FROM table_test INDEXED BY idx_before WHERE column1 = 1;" data/index/dump/index-reindex-after.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-reindex-before.txt --outputImagePath=data/index/render/index-reindex-before.webp
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-reindex-after.txt --outputImagePath=data/index/render/index-reindex-after.webp
php bin/console app:diff-index --dumpIndexPath=data/index/dump/index-reindex-before.txt  --dumpIndexPath=data/index/dump/index-reindex-after.txt --outputDiffPath=data/index/render/index-reindex.txt

#index-text
php bin/console app:database-generate --type=index-text --count=1000000 --databasePath=data/index/database/index-text.sqlite --infoPath=data/index/database/index-text.txt
sh bin/dump-index.sh data/index/database/index-text.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-text.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-text.txt --outputImagePath=data/index/render/index-text.webp

#index-real
php bin/console app:database-generate --type=index-real --count=1000000 --databasePath=data/index/database/index-real.sqlite --infoPath=data/index/database/index-real.txt
sh bin/dump-index.sh data/index/database/index-real.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-real.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-real.txt --outputImagePath=data/index/render/index-real.webp

#index-integer-text
php bin/console app:database-generate --type=index-integer-text --count=1000000 --databasePath=data/index/database/index-integer-text.sqlite --infoPath=data/index/database/index-integer-text.txt
sh bin/dump-index.sh data/index/database/index-integer-text.sqlite "SELECT * FROM table_test INDEXED BY idx WHERE column1 = 1;" data/index/dump/index-integer-text.txt
php bin/console app:render-index --dumpIndexPath=data/index/dump/index-integer-text.txt --outputImagePath=data/index/render/index-integer-text.webp
