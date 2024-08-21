#!/bin/bash

# explicit error handling rather than set -e (https://mywiki.wooledge.org/BashFAQ/105)

# make sure the modules are up
git submodule init || exit 1
git submodule update || exit 1

# download local LLM model
if [ ! -e download/bling-stable-lm-3b-4e1t-v0.Q4_K_M.gguf ]; then
    cd download
    wget https://huggingface.co/TheBloke/bling-stable-lm-3b-4e1t-v0-GGUF/resolve/main/bling-stable-lm-3b-4e1t-v0.Q4_K_M.gguf || exit 2
    cd ..
fi

# download sqlite3 from testing
if [ ! -e download/sqlite3_3.46.0-1.dsc ]; then
    cd download
    for part in "-1.debian.tar.xz" ".orig.tar.xz" ".orig-www.tar.xz" "-1.dsc" 
    do
        wget http://deb.debian.org/debian/pool/main/s/sqlite3/sqlite3_3.46.0$part || exit 3
    done
    cd ..
fi

# fetch sqlite vss dependencies
cd sqlite-vss
git submodule init || exit 4
git submodule update || exit 4
if [ ! -d ./vendor/sqlite ]; then
    ./vendor/get_sqlite.sh || exit 5
fi
cd ..

