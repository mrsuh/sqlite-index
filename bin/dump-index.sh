#!/usr/bin/env bash

export BTREE_INDEX_DUMP=1

sqlite3 "$1" "$2" > "$3"

unset BTREE_INDEX_DUMP
